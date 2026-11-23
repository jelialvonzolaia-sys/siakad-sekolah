<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\KomentarTugas;
use App\Models\Notifikasi;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class GuruTugasController extends Controller
{
    /**
     * Tampilkan daftar tugas yang dibuat oleh guru yang sedang login.
     */
    public function index(Request $request): View
    {
        $guruId = auth()->id();

        $query = Tugas::where('guru_id', $guruId)
            ->with(['kelas', 'mataPelajaran', 'pengumpulanTugas'])
            ->latest();

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                  ->orWhere('deskripsi_html', 'like', "%{$keyword}%");
            });
        }

        $tugasList = $query->paginate(10)->withQueryString();

        $kelasList = Kelas::whereHas('jadwals', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->orderBy('nama_kelas')->get();

        return view('guru.tugas.index', [
            'pageTitle' => 'Manajemen Tugas & Evaluasi',
            'pageSubtitle' => 'SMA Negeri 1 Gunungsitoli',
            'tugasList' => $tugasList,
            'kelasList' => $kelasList,
            'selectedKelasId' => $request->kelas_id,
            'searchQuery' => $request->q,
        ]);
    }

    /**
     * Formulir pembuatan penugasan baru dengan Rich Text Editor.
     */
    public function create(): View
    {
        $guruId = auth()->id();

        $jadwals = Jadwal::where('guru_id', $guruId)
            ->with(['kelas', 'mataPelajaran'])
            ->get();

        $kelasList = $jadwals->pluck('kelas')->unique('id')->values();
        $mapelList = $jadwals->pluck('mataPelajaran')->unique('id')->values();

        $now = Carbon::now();
        $defaultBuka = $now->format('Y-m-d\TH:i');
        $defaultBatas = $now->copy()->addDays(7)->setTime(23, 59)->format('Y-m-d\TH:i');

        return view('guru.tugas.create', [
            'pageTitle' => 'Buat Tugas Baru',
            'pageSubtitle' => 'Konfigurasikan instruksi soal, batas waktu, dan aturan berkas',
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'defaultBuka' => $defaultBuka,
            'defaultBatas' => $defaultBatas,
        ]);
    }

    /**
     * Simpan penugasan baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $guruId = auth()->id();

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajarans,id',
            'judul' => 'required|string|max:255',
            'deskripsi_html' => 'required|string',
            'lampiran_guru' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar|max:15360',
            'waktu_buka' => 'required|date',
            'batas_waktu' => 'required|date|after_or_equal:waktu_buka',
            'tipe_file_diizinkan' => 'required|array|min:1',
            'max_ukuran_mb' => 'required|integer|min:1|max:50',
            'max_jumlah_file' => 'required|integer|min:1|max:10',
            'izinkan_terlambat' => 'nullable|boolean',
        ], [
            'kelas_id.required' => 'Pilih kelas target penugasan.',
            'mapel_id.required' => 'Pilih mata pelajaran.',
            'judul.required' => 'Judul tugas wajib diisi.',
            'deskripsi_html.required' => 'Instruksi / deskripsi tugas wajib diisi.',
            'batas_waktu.after_or_equal' => 'Batas waktu pengumpulan harus sama atau setelah waktu buka.',
            'tipe_file_diizinkan.required' => 'Pilih minimal satu tipe berkas yang diizinkan untuk siswa.',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran_guru')) {
            $lampiranPath = $request->file('lampiran_guru')->store('tugas', 'public');
        }

        $tugas = Tugas::create([
            'kelas_id' => $validated['kelas_id'],
            'mapel_id' => $validated['mapel_id'],
            'guru_id' => $guruId,
            'judul' => $validated['judul'],
            'deskripsi_html' => $validated['deskripsi_html'],
            'lampiran_guru' => $lampiranPath,
            'waktu_buka' => $validated['waktu_buka'],
            'batas_waktu' => $validated['batas_waktu'],
            'tipe_file_diizinkan' => $validated['tipe_file_diizinkan'],
            'max_ukuran_mb' => $validated['max_ukuran_mb'],
            'max_jumlah_file' => $validated['max_jumlah_file'],
            'izinkan_terlambat' => $request->boolean('izinkan_terlambat'),
        ]);
        $tugas->load(['mataPelajaran', 'kelas']);

        $siswaIds = User::where('role', 'siswa')
            ->where('kelas_id', $tugas->kelas_id)
            ->pluck('id');

        Notifikasi::kirimUntukBanyak(
            $siswaIds,
            'Tugas Baru: ' . $tugas->judul,
            ($tugas->mataPelajaran?->nama_mapel ?? 'Mata Pelajaran') . ' • Tenggat: ' . Carbon::parse($tugas->batas_waktu)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB.',
            'tugas',
            route('siswa.tugas.show', $tugas->id)
        );

        return redirect()->route('guru.tugas.index')
            ->with('status', 'Tugas "' . $tugas->judul . '" berhasil dipublikasikan untuk kelas sasaran.');
    }

    /**
     * Halaman Rekap & Penilaian Tugas (Report & Grading Center).
     */
    public function show(Tugas $tuga): View
    {
        $this->authorizeOwner($tuga);

        $tuga->load(['kelas', 'mataPelajaran']);

        // Ambil seluruh siswa di kelas target
        $daftarSiswa = User::where('role', 'siswa')
            ->where('kelas_id', $tuga->kelas_id)
            ->orderBy('name')
            ->get();

        // Ambil seluruh pengumpulan tugas untuk tugas ini
        $pengumpulanMap = PengumpulanTugas::where('tugas_id', $tuga->id)
            ->with(['files', 'komentars.user'])
            ->get()
            ->keyBy('siswa_id');

        $totalSiswa = $daftarSiswa->count();
        $totalKumpul = $pengumpulanMap->where('status_pengajuan', 'Terkirim')->count();
        $totalDinilai = $pengumpulanMap->where('status_penilaian', 'Sudah dinilai')->count();
        
        $totalTerlambat = $pengumpulanMap->filter(function ($item) use ($tuga) {
            return $item->submitted_at && $item->submitted_at->greaterThan($tuga->batas_waktu);
        })->count();

        return view('guru.tugas.report', [
            'pageTitle' => 'Rekap Pengumpulan & Penilaian Tugas',
            'pageSubtitle' => $tuga->judul . ' • Kelas ' . $tuga->kelas->nama_kelas,
            'tugas' => $tuga,
            'daftarSiswa' => $daftarSiswa,
            'pengumpulanMap' => $pengumpulanMap,
            'totalSiswa' => $totalSiswa,
            'totalKumpul' => $totalKumpul,
            'totalDinilai' => $totalDinilai,
            'totalTerlambat' => $totalTerlambat,
        ]);
    }

    /**
     * Tampilkan formulir edit tugas.
     */
    public function edit(Tugas $tuga): View
    {
        $this->authorizeOwner($tuga);

        $guruId = auth()->id();
        $jadwals = Jadwal::where('guru_id', $guruId)
            ->with(['kelas', 'mataPelajaran'])
            ->get();

        $kelasList = $jadwals->pluck('kelas')->unique('id')->values();
        $mapelList = $jadwals->pluck('mataPelajaran')->unique('id')->values();

        return view('guru.tugas.edit', [
            'pageTitle' => 'Edit Tugas',
            'pageSubtitle' => $tuga->judul,
            'tugas' => $tuga,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
        ]);
    }

    /**
     * Perbarui data tugas.
     */
    public function update(Request $request, Tugas $tuga): RedirectResponse
    {
        $this->authorizeOwner($tuga);

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajarans,id',
            'judul' => 'required|string|max:255',
            'deskripsi_html' => 'required|string',
            'lampiran_guru' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar|max:15360',
            'waktu_buka' => 'required|date',
            'batas_waktu' => 'required|date|after_or_equal:waktu_buka',
            'tipe_file_diizinkan' => 'required|array|min:1',
            'max_ukuran_mb' => 'required|integer|min:1|max:50',
            'max_jumlah_file' => 'required|integer|min:1|max:10',
            'izinkan_terlambat' => 'nullable|boolean',
        ]);

        if ($request->hasFile('lampiran_guru')) {
            if ($tuga->lampiran_guru && Storage::disk('public')->exists($tuga->lampiran_guru)) {
                Storage::disk('public')->delete($tuga->lampiran_guru);
            }
            $tuga->lampiran_guru = $request->file('lampiran_guru')->store('tugas', 'public');
        }

        $tuga->update([
            'kelas_id' => $validated['kelas_id'],
            'mapel_id' => $validated['mapel_id'],
            'judul' => $validated['judul'],
            'deskripsi_html' => $validated['deskripsi_html'],
            'waktu_buka' => $validated['waktu_buka'],
            'batas_waktu' => $validated['batas_waktu'],
            'tipe_file_diizinkan' => $validated['tipe_file_diizinkan'],
            'max_ukuran_mb' => $validated['max_ukuran_mb'],
            'max_jumlah_file' => $validated['max_jumlah_file'],
            'izinkan_terlambat' => $request->boolean('izinkan_terlambat'),
        ]);

        return redirect()->route('guru.tugas.index')
            ->with('status', 'Tugas "' . $tuga->judul . '" berhasil diperbarui.');
    }

    /**
     * Hapus penugasan dan seluruh berkas siswa terkait.
     */
    public function destroy(Tugas $tuga): RedirectResponse
    {
        $this->authorizeOwner($tuga);

        $judul = $tuga->judul;

        // Hapus lampiran guru jika ada
        if ($tuga->lampiran_guru && Storage::disk('public')->exists($tuga->lampiran_guru)) {
            Storage::disk('public')->delete($tuga->lampiran_guru);
        }

        // Hapus seluruh file yang dikumpulkan siswa
        foreach ($tuga->pengumpulanTugas as $pengumpulan) {
            foreach ($pengumpulan->files as $file) {
                if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }
            }
        }

        $tuga->delete();

        return redirect()->route('guru.tugas.index')
            ->with('status', 'Tugas "' . $judul . '" beserta seluruh arsip pengumpulan siswa berhasil dihapus.');
    }

    /**
     * Penilaian Cepat (Quick Grading) & Feedback Guru.
     */
    public function quickGrade(Request $request, PengumpulanTugas $pengumpulan): JsonResponse|RedirectResponse
    {
        $this->authorizeOwner($pengumpulan->tugas);

        $validated = $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'feedback_guru' => 'nullable|string|max:2000',
        ]);

        $pengumpulan->update([
            'nilai' => $validated['nilai'],
            'feedback_guru' => $validated['feedback_guru'],
            'status_penilaian' => 'Sudah dinilai',
        ]);

        Notifikasi::kirimUntukBanyak(
            [$pengumpulan->siswa_id],
            'Nilai Tugas Diterbitkan: ' . $pengumpulan->tugas->judul,
            'Tugas Anda telah dinilai dengan skor ' . $validated['nilai'] . '/100.',
            'tugas',
            route('siswa.tugas.show', $pengumpulan->tugas_id)
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nilai dan feedback berhasil disimpan.',
                'nilai' => $pengumpulan->nilai,
                'feedback' => $pengumpulan->feedback_guru,
            ]);
        }

        return redirect()->back()->with('status', 'Nilai dan catatan penilaian berhasil disimpan.');
    }

    /**
     * Kirim komentar diskusi dari guru pada pengumpulan siswa.
     */
    public function addComment(Request $request, PengumpulanTugas $pengumpulan): JsonResponse|RedirectResponse
    {
        $this->authorizeOwner($pengumpulan->tugas);

        $validated = $request->validate([
            'komentar' => 'required|string|max:1000',
        ]);

        $komentar = KomentarTugas::create([
            'pengumpulan_tugas_id' => $pengumpulan->id,
            'user_id' => auth()->id(),
            'komentar' => $validated['komentar'],
        ]);

        Notifikasi::kirimUntukBanyak(
            [$pengumpulan->siswa_id],
            'Komentar Guru pada Tugas: ' . $pengumpulan->tugas->judul,
            auth()->user()->name . ': "' . Str::limit($komentar->komentar, 90) . '"',
            'tugas',
            route('siswa.tugas.show', $pengumpulan->tugas_id)
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'komentar' => [
                    'id' => $komentar->id,
                    'user_name' => auth()->user()->name,
                    'user_role' => auth()->user()->role,
                    'text' => $komentar->komentar,
                    'time' => $komentar->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB',
                ],
            ]);
        }

        return redirect()->back()->with('status', 'Komentar berhasil dikirim.');
    }

    /**
     * Fitur Magic — "Download Semua Berkas (ZIP)":
     * Mengunduh seluruh file jawaban siswa satu kelas sekaligus dalam satu arsip .zip
     * dengan penamaan file rapi: NIS_NamaSiswa_NamaFileAsli
     */
    public function downloadAllZip(Tugas $tuga): BinaryFileResponse|RedirectResponse
    {
        $this->authorizeOwner($tuga);

        $tuga->load(['kelas', 'pengumpulanTugas.siswa', 'pengumpulanTugas.files']);

        $zipFileName = 'Tugas_' . Str::slug($tuga->judul) . '_' . Str::slug($tuga->kelas->nama_kelas) . '.zip';
        $tempZipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Gagal membuat arsip ZIP di server.');
        }

        $fileCount = 0;
        foreach ($tuga->pengumpulanTugas as $pengumpulan) {
            $siswa = $pengumpulan->siswa;
            $nis = $siswa->nis_nip ?? 'NONIS';
            $namaSiswa = Str::slug($siswa->name, '_');

            foreach ($pengumpulan->files as $file) {
                if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                    $realPath = Storage::disk('public')->path($file->file_path);
                    $entryName = "{$nis}_{$namaSiswa}_{$file->nama_file_asli}";
                    $zip->addFile($realPath, $entryName);
                    $fileCount++;
                }
            }
        }

        $zip->close();

        if ($fileCount === 0) {
            if (file_exists($tempZipPath)) {
                unlink($tempZipPath);
            }
            return redirect()->back()->with('error', 'Belum ada berkas siswa yang terkumpul untuk diunduh.');
        }

        return response()->download($tempZipPath)->deleteFileAfterSend(true);
    }

    /**
     * Fitur Magic — "Export Rekap Nilai (CSV)":
     * Mengekspor rekapitulasi nilai dan status pengajuan satu kelas ke format CSV/Excel.
     */
    public function exportCsv(Tugas $tuga): StreamedResponse
    {
        $this->authorizeOwner($tuga);

        $tuga->load(['kelas', 'mataPelajaran']);

        $daftarSiswa = User::where('role', 'siswa')
            ->where('kelas_id', $tuga->kelas_id)
            ->orderBy('name')
            ->get();

        $pengumpulanMap = PengumpulanTugas::where('tugas_id', $tuga->id)
            ->get()
            ->keyBy('siswa_id');

        $fileName = 'Rekap_Nilai_' . Str::slug($tuga->judul) . '_' . Str::slug($tuga->kelas->nama_kelas) . '.csv';

        return response()->streamDownload(function () use ($daftarSiswa, $pengumpulanMap, $tuga) {
            $handle = fopen('php://output', 'w');
            
            // BOM UTF-8 agar karakter bahasa Indonesia terbaca rapi di Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header Baris
            fputcsv($handle, [
                'No',
                'NIS',
                'Nama Siswa',
                'Kelas',
                'Mata Pelajaran',
                'Judul Tugas',
                'Status Pengajuan',
                'Waktu Pengumpulan',
                'Keterangan Keterlambatan',
                'Nilai (0-100)',
                'Catatan / Feedback Guru',
            ]);

            foreach ($daftarSiswa as $idx => $siswa) {
                $pengumpulan = $pengumpulanMap->get($siswa->id);

                $status = $pengumpulan ? $pengumpulan->status_pengajuan : 'Belum Mengumpulkan';
                $waktu = $pengumpulan && $pengumpulan->submitted_at ? $pengumpulan->submitted_at->format('Y-m-d H:i:s') : '-';
                $selisih = $pengumpulan ? $pengumpulan->selisih_waktu_label : '-';
                $nilai = $pengumpulan && $pengumpulan->nilai !== null ? $pengumpulan->nilai : 'Belum dinilai';
                $feedback = $pengumpulan ? $pengumpulan->feedback_guru : '';

                fputcsv($handle, [
                    $idx + 1,
                    $siswa->nis_nip ?? '-',
                    $siswa->name,
                    $tuga->kelas->nama_kelas,
                    $tuga->mataPelajaran->nama_mapel,
                    $tuga->judul,
                    $status,
                    $waktu,
                    $selisih,
                    $nilai,
                    $feedback,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    private function authorizeOwner(Tugas $tugas): void
    {
        if ($tugas->guru_id !== auth()->id()) {
            abort(403, 'Akses Ditolak. Anda bukan pembuat penugasan ini.');
        }
    }
}
