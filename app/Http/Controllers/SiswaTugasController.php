<?php

namespace App\Http\Controllers;

use App\Models\KomentarTugas;
use App\Models\MataPelajaran;
use App\Models\Notifikasi;
use App\Models\PengumpulanFile;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SiswaTugasController extends Controller
{
    /**
     * Tampilkan daftar penugasan kelas siswa.
     */
    public function index(Request $request): View
    {
        $siswa = auth()->user();
        $kelasId = $siswa->kelas_id;

        if (!$kelasId) {
            abort(403, 'Akun siswa belum terdaftar pada rombel kelas manapun.');
        }

        $query = Tugas::where('kelas_id', $kelasId)
            ->with(['guru', 'mataPelajaran', 'pengumpulanTugas' => function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            }])
            ->latest('waktu_buka');

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                  ->orWhere('deskripsi_html', 'like', "%{$keyword}%");
            });
        }

        $tugasList = $query->paginate(12)->withQueryString();

        $mapelList = MataPelajaran::whereHas('jadwals', function ($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })->orderBy('nama_mapel')->get();

        return view('siswa.tugas.index', [
            'pageTitle' => 'Tugas & Evaluasi Pembelajaran',
            'pageSubtitle' => 'Kelas ' . ($siswa->kelas->nama_kelas ?? '-') . ' • SMAN 1 Gunungsitoli',
            'siswa' => $siswa,
            'tugasList' => $tugasList,
            'mapelList' => $mapelList,
            'selectedMapelId' => $request->mapel_id,
            'searchQuery' => $request->q,
        ]);
    }

    /**
     * Tampilkan detail tugas dan formulir / tabel status pengumpulan berkas (sesuai spesifikasi akademik).
     */
    public function show(Tugas $tuga): View
    {
        $siswa = auth()->user();

        if ($tuga->kelas_id !== $siswa->kelas_id) {
            abort(403, 'Akses Ditolak. Tugas ini tidak diperuntukkan bagi kelas Anda.');
        }

        $tuga->load(['guru', 'mataPelajaran', 'kelas']);

        $pengumpulan = PengumpulanTugas::where('tugas_id', $tuga->id)
            ->where('siswa_id', $siswa->id)
            ->with(['files', 'komentars.user'])
            ->first();

        return view('siswa.tugas.show', [
            'pageTitle' => 'Detail & Pengumpulan Tugas',
            'pageSubtitle' => $tuga->judul . ' • ' . $tuga->mataPelajaran->nama_mapel,
            'tugas' => $tuga,
            'siswa' => $siswa,
            'pengumpulan' => $pengumpulan,
        ]);
    }

    /**
     * Submit pengumpulan multi-file tugas oleh siswa dengan validasi ketat.
     */
    public function submit(Request $request, Tugas $tuga): RedirectResponse
    {
        $siswa = auth()->user();

        if ($tuga->kelas_id !== $siswa->kelas_id) {
            abort(403, 'Akses Ditolak.');
        }

        // Cek izin batas waktu
        if (!$tuga->bisaKumpul()) {
            return redirect()->back()->with('error', 'Waktu pengumpulan tugas ini telah ditutup oleh sistem.');
        }

        $pengumpulan = PengumpulanTugas::firstOrCreate(
            [
                'tugas_id' => $tuga->id,
                'siswa_id' => $siswa->id,
            ],
            [
                'status_pengajuan' => 'Terkirim',
                'status_penilaian' => 'Belum dinilai',
            ]
        );

        // Jika sudah dinilai, tidak boleh diubah
        if ($pengumpulan->status_penilaian === 'Sudah dinilai') {
            return redirect()->back()->with('error', 'Tugas ini sudah dinilai oleh Guru dan tidak dapat diubah lagi.');
        }

        $maxSizeKb = $tuga->max_ukuran_mb * 1024;
        $allowedExtensions = array_map('strtolower', $tuga->tipe_file_diizinkan ?? []);

        // Hitung sisa slot file
        $existingFileCount = $pengumpulan->files()->count();
        $slotTersedia = max(0, $tuga->max_jumlah_file - $existingFileCount);

        $request->validate([
            'files' => "required|array|min:1|max:{$slotTersedia}",
            'files.*' => "file|max:{$maxSizeKb}",
        ], [
            'files.required' => 'Pilih minimal satu berkas tugas untuk dikumpulkan.',
            'files.max' => "Anda hanya dapat mengunggah maksimal {$tuga->max_jumlah_file} berkas secara keseluruhan.",
            'files.*.max' => "Ukuran setiap berkas tidak boleh melebihi {$tuga->max_ukuran_mb} MB.",
        ]);

        // Validasi ekstensi kustom sesuai aturan guru
        foreach ($request->file('files') as $file) {
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowedExtensions)) {
                return redirect()->back()->with('error', "Ekstensi berkas .{$ext} tidak diizinkan. Ekstensi yang diperbolehkan: " . implode(', ', $allowedExtensions));
            }
        }

        // Simpan file-file yang diunggah
        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());
            $sizeKb = (int) round($file->getSize() / 1024);

            $storedPath = $file->store('tugas_submissions', 'public');

            PengumpulanFile::create([
                'pengumpulan_tugas_id' => $pengumpulan->id,
                'nama_file_asli' => $originalName,
                'file_path' => $storedPath,
                'ukuran_kb' => $sizeKb,
                'ekstensi' => $ext,
            ]);
        }

        // Update waktu submit dan status
        $pengumpulan->update([
            'status_pengajuan' => 'Terkirim',
            'submitted_at' => Carbon::now('Asia/Jakarta'),
        ]);

        Notifikasi::kirimUntukBanyak(
            [$tuga->guru_id],
            'Pengumpulan Tugas: ' . $tuga->judul,
            $siswa->name . ' mengumpulkan berkas tugas pada ' . Carbon::now('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB.',
            'tugas',
            route('guru.tugas.show', $tuga->id)
        );

        return redirect()->route('siswa.tugas.show', $tuga->id)
            ->with('status', 'Berkas tugas berhasil dikirim dan dicatat oleh sistem SIAKAD.');
    }

    /**
     * Hapus salah satu berkas yang pernah diunggah (sebelum deadline & belum dinilai).
     */
    public function deleteFile(PengumpulanFile $file): RedirectResponse
    {
        $pengumpulan = $file->pengumpulanTugas;
        $siswa = auth()->user();

        if ($pengumpulan->siswa_id !== $siswa->id) {
            abort(403, 'Akses Ditolak.');
        }

        if ($pengumpulan->status_penilaian === 'Sudah dinilai') {
            return redirect()->back()->with('error', 'Berkas tidak dapat dihapus karena tugas sudah dinilai.');
        }

        $tuga = $pengumpulan->tugas;
        if (!$tuga->bisaKumpul()) {
            return redirect()->back()->with('error', 'Waktu pengumpulan tugas telah berakhir.');
        }

        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        // Perbarui timestamp pengumpulan jika masih ada file, atau reset jika kosong
        $sisaFile = $pengumpulan->files()->count();
        if ($sisaFile === 0) {
            $pengumpulan->update([
                'status_pengajuan' => 'Draft',
            ]);
        } else {
            $pengumpulan->update([
                'submitted_at' => Carbon::now(),
            ]);
        }

        return redirect()->back()->with('status', 'Berkas berhasil dihapus dari pengajuan.');
    }

    /**
     * Tambah komentar diskusi pribadi pada pengumpulan tugas dari siswa.
     */
    public function addComment(Request $request, PengumpulanTugas $pengumpulan): JsonResponse|RedirectResponse
    {
        if ($pengumpulan->siswa_id !== auth()->id()) {
            abort(403, 'Akses Ditolak.');
        }

        $validated = $request->validate([
            'komentar' => 'required|string|max:1000',
        ]);

        $komentar = KomentarTugas::create([
            'pengumpulan_tugas_id' => $pengumpulan->id,
            'user_id' => auth()->id(),
            'komentar' => $validated['komentar'],
        ]);

        $pengumpulan->loadMissing('tugas');
        Notifikasi::kirimUntukBanyak(
            [$pengumpulan->tugas->guru_id],
            'Komentar Siswa pada Tugas: ' . $pengumpulan->tugas->judul,
            auth()->user()->name . ': "' . Str::limit($komentar->komentar, 90) . '"',
            'tugas',
            route('guru.tugas.show', $pengumpulan->tugas_id)
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

        return redirect()->back()->with('status', 'Komentar pengajuan berhasil dikirim.');
    }

    /**
     * Unduh lampiran soal/panduan dari guru.
     */
    public function downloadLampiranGuru(Tugas $tuga): BinaryFileResponse|RedirectResponse
    {
        $siswa = auth()->user();

        if ($tuga->kelas_id !== $siswa->kelas_id) {
            abort(403, 'Akses Ditolak.');
        }

        if ($tuga->lampiran_guru && Storage::disk('public')->exists($tuga->lampiran_guru)) {
            $ext = pathinfo($tuga->lampiran_guru, PATHINFO_EXTENSION);
            $filename = 'Soal_' . Str::slug($tuga->judul) . '.' . $ext;
            return Storage::disk('public')->download($tuga->lampiran_guru, $filename);
        }

        return redirect()->back()->with('error', 'Berkas lampiran guru tidak ditemukan di server.');
    }

    /**
     * Unduh berkas yang diunggah siswa.
     */
    public function downloadFile(PengumpulanFile $file): BinaryFileResponse|RedirectResponse
    {
        $pengumpulan = $file->pengumpulanTugas;
        $siswa = auth()->user();

        if ($pengumpulan->siswa_id !== $siswa->id) {
            abort(403, 'Akses Ditolak.');
        }

        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            return Storage::disk('public')->download($file->file_path, $file->nama_file_asli);
        }

        return redirect()->back()->with('error', 'Berkas tidak ditemukan di server.');
    }
}
