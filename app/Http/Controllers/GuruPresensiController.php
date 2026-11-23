<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Notifikasi;
use App\Models\Presensi;
use App\Models\SesiPresensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuruPresensiController extends Controller
{
    /**
     * Tampilkan daftar jadwal mengajar dan riwayat sesi presensi guru.
     */
    public function index(): View
    {
        $guruId = auth()->id();

        // Ambil seluruh jadwal mengajar yang diampu guru ini
        $jadwals = Jadwal::where('guru_id', $guruId)
            ->with(['kelas', 'mataPelajaran', 'sesiPresensis'])
            ->get();

        // Ambil sesi presensi yang pernah dibuka oleh guru ini
        $sesis = SesiPresensi::whereHas('jadwal', function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        })
            ->with(['jadwal.kelas', 'jadwal.mataPelajaran', 'presensis'])
            ->latest('tanggal')
            ->latest('jam_mulai')
            ->get();

        return view('guru.presensi.index', [
            'pageTitle' => 'Kelola Presensi Siswa',
            'pageSubtitle' => 'SMA Negeri 1 Gunungsitoli',
            'jadwals' => $jadwals,
            'sesis' => $sesis,
        ]);
    }

    /**
     * Tampilkan form pembukaan sesi presensi untuk jadwal tertentu.
     */
    public function create(Jadwal $jadwal): View
    {
        if ($jadwal->guru_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki hak akses membuka presensi untuk jadwal ini.');
        }

        $jadwal->load(['kelas', 'mataPelajaran']);

        // Sarankan nomor pertemuan berikutnya
        $suggestedPertemuan = SesiPresensi::where('jadwal_id', $jadwal->id)->count() + 1;

        $now = Carbon::now();
        $defaultJamMulai = $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : $now->format('H:i');
        $defaultJamSelesai = $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : $now->copy()->addMinutes(90)->format('H:i');

        return view('guru.presensi.create', [
            'pageTitle' => 'Buka Sesi Presensi',
            'pageSubtitle' => $jadwal->mataPelajaran->nama_mapel . ' - ' . $jadwal->kelas->nama_kelas,
            'jadwal' => $jadwal,
            'suggestedPertemuan' => $suggestedPertemuan,
            'defaultJamMulai' => $defaultJamMulai,
            'defaultJamSelesai' => $defaultJamSelesai,
        ]);
    }

    /**
     * Simpan pembukaan sesi presensi dan inisialisasi presensi siswa.
     */
    public function store(Request $request, Jadwal $jadwal): RedirectResponse
    {
        if ($jadwal->guru_id !== auth()->id()) {
            abort(403, 'Akses Ditolak.');
        }

        $validated = $request->validate([
            'pertemuan_ke' => 'required|integer|min:1',
            'materi' => 'required|string|max:255',
            'metode' => 'required|in:mandiri,qr',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        $sesi = SesiPresensi::create([
            'jadwal_id' => $jadwal->id,
            'pertemuan_ke' => $validated['pertemuan_ke'],
            'materi' => $validated['materi'],
            'metode' => $validated['metode'],
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'status_sesi' => 'dibuka',
        ]);

        // Jika metode QR, generate token awal
        if ($sesi->metode === 'qr') {
            $sesi->generateQrToken();
        }

        // Ambil seluruh siswa di kelas tersebut dan buat record default 'Belum Absen'
        $daftarSiswa = User::where('role', 'siswa')
            ->where('kelas_id', $jadwal->kelas_id)
            ->get();

        foreach ($daftarSiswa as $siswa) {
            Presensi::firstOrCreate(
                [
                    'sesi_presensi_id' => $sesi->id,
                    'siswa_id' => $siswa->id,
                ],
                [
                    'jadwal_id' => $jadwal->id,
                    'tanggal' => $sesi->tanggal,
                    'status' => 'Belum Absen',
                    'keterangan' => 'Belum melakukan presensi',
                ]
            );
        }

        $jadwal->loadMissing(['mataPelajaran', 'kelas']);
        Notifikasi::kirimUntukBanyak(
            $daftarSiswa->pluck('id'),
            'Sesi Presensi Dibuka: ' . ($jadwal->mataPelajaran?->nama_mapel ?? 'Mata Pelajaran'),
            'Pertemuan ' . $sesi->pertemuan_ke . ' (' . $sesi->materi . ') dibuka pukul ' . substr($sesi->jam_mulai, 0, 5) . ' - ' . substr($sesi->jam_selesai, 0, 5) . ' WIB.',
            'presensi',
            route('siswa.presensi.show', $jadwal->id)
        );

        return redirect()->route('guru.presensi.show', $sesi->id)
            ->with('status', 'Sesi presensi Pertemuan ' . $sesi->pertemuan_ke . ' berhasil dibuka.');
    }

    /**
     * Tampilkan monitor sesi presensi: QR code aktif & tabel rekap siswa realtime.
     */
    public function show(SesiPresensi $sesi): View
    {
        $this->authorizeTeacherAccess($sesi);
        $sesi->syncExpiredStatus();

        $sesi->load([
            'jadwal.kelas',
            'jadwal.mataPelajaran',
            'presensis.siswa' => function ($query) {
                $query->orderBy('name', 'asc');
            },
        ]);

        // Jika QR token belum ada atau sudah kedaluwarsa, generate baru
        if ($sesi->metode === 'qr' && (!$sesi->qr_token || ($sesi->qr_expires_at && Carbon::now('Asia/Jakarta')->greaterThan($sesi->qr_expires_at)))) {
            $sesi->generateQrToken();
        }

        return view('guru.presensi.show', [
            'pageTitle' => 'Monitor Presensi Kelas',
            'pageSubtitle' => $sesi->jadwal->mataPelajaran->nama_mapel . ' - ' . $sesi->jadwal->kelas->nama_kelas,
            'sesi' => $sesi,
        ]);
    }

    /**
     * Endpoint AJAX untuk merefresh QR Token setiap 20 detik.
     */
    public function refreshQrToken(SesiPresensi $sesi): JsonResponse
    {
        $this->authorizeTeacherAccess($sesi);

        if ($sesi->status_sesi !== 'dibuka') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi presensi sudah ditutup.',
            ], 400);
        }

        $newToken = $sesi->generateQrToken();

        return response()->json([
            'success' => true,
            'token' => $newToken,
            'expires_at' => $sesi->qr_expires_at->toIso8601String(),
            'seconds_remaining' => 20,
        ]);
    }

    /**
     * Endpoint AJAX untuk mengambil data terkini kehadiran seluruh siswa (Polling Realtime).
     */
    public function statusLive(SesiPresensi $sesi): JsonResponse
    {
        $this->authorizeTeacherAccess($sesi);
        $sesi->syncExpiredStatus();

        $presensis = Presensi::where('sesi_presensi_id', $sesi->id)
            ->with('siswa')
            ->get()
            ->sortBy('siswa.name')
            ->values()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'siswa_id' => $p->siswa_id,
                    'nama_siswa' => $p->siswa->name,
                    'nis' => $p->siswa->nis_nip ?? '-',
                    'status' => $p->status,
                    'waktu_presensi' => $p->waktu_presensi ? substr($p->waktu_presensi, 0, 5) . ' WIB' : '-',
                    'lokasi' => $p->alamat_lokasi ?? ($p->latitude ? $p->latitude . ', ' . $p->longitude : '-'),
                    'keterangan' => $p->keterangan ?? '-',
                ];
            });

        return response()->json([
            'success' => true,
            'status_sesi' => $sesi->status_sesi,
            'total' => $presensis->count(),
            'hadir_count' => $presensis->where('status', 'Hadir')->count(),
            'sakit_count' => $presensis->where('status', 'Sakit')->count(),
            'izin_count' => $presensis->where('status', 'Izin')->count(),
            'belum_absen_count' => $presensis->where('status', 'Belum Absen')->count(),
            'alpha_count' => $presensis->whereIn('status', ['Alpha', 'Belum Absen'])->count(),
            'presensis' => $presensis,
        ]);
    }

    /**
     * Simpan perubahan manual / override rekap presensi oleh guru.
     */
    public function override(Request $request, SesiPresensi $sesi): RedirectResponse|JsonResponse
    {
        $this->authorizeTeacherAccess($sesi);

        $presensiData = $request->input('presensi', []);

        foreach ($presensiData as $siswaId => $status) {
            if (in_array($status, ['Hadir', 'Sakit', 'Izin', 'Alpha'])) {
                Presensi::where('sesi_presensi_id', $sesi->id)
                    ->where('siswa_id', $siswaId)
                    ->update([
                        'status' => $status,
                        'keterangan' => 'Diverifikasi manual oleh Guru Pengampu',
                        'waktu_presensi' => $status === 'Hadir' ? (Carbon::now('Asia/Jakarta')->format('H:i:s')) : null,
                    ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rekap kehadiran kelas berhasil diperbarui oleh guru.',
            ]);
        }

        return redirect()->route('guru.presensi.show', $sesi->id)
            ->with('status', 'Rekapitulasi status kehadiran kelas berhasil disimpan.');
    }

    /**
     * Tutup sesi presensi dan ubah siswa yang belum absen menjadi Alpha.
     */
    public function closeSession(SesiPresensi $sesi): RedirectResponse
    {
        $this->authorizeTeacherAccess($sesi);

        $sesi->update([
            'status_sesi' => 'ditutup',
            'qr_token' => null,
            'qr_expires_at' => null,
        ]);

        Presensi::where('sesi_presensi_id', $sesi->id)
            ->where('status', 'Belum Absen')
            ->update([
                'status' => 'Alpha',
                'keterangan' => 'Tidak melakukan presensi hingga sesi ditutup oleh Guru',
            ]);

        return redirect()->route('guru.presensi.show', $sesi->id)
            ->with('status', 'Sesi presensi Pertemuan ' . $sesi->pertemuan_ke . ' telah resmi ditutup.');
    }

    /**
     * Pastikan guru yang mengakses adalah guru pengampu jadwal ini.
     */
    private function authorizeTeacherAccess(SesiPresensi $sesi): void
    {
        if ($sesi->jadwal->guru_id !== auth()->id()) {
            abort(403, 'Akses Ditolak. Anda bukan pengampu jadwal pelajaran ini.');
        }
    }
}
