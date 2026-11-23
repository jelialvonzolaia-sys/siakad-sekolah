<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Presensi;
use App\Models\SesiPresensi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiswaPresensiController extends Controller
{
    /**
     * Tahap 1: Tampilkan detail identitas siswa dan daftar mata pelajaran kelas.
     */
    public function index(): View
    {
        $siswa = auth()->user();
        $siswa->load(['kelas.waliKelas']);
        $kelasId = $siswa->kelas_id;

        if (!$kelasId) {
            abort(403, 'Akun siswa belum terdaftar pada kelas manapun.');
        }

        // Ambil seluruh mata pelajaran kelas siswa dari jadwal resmi
        $jadwals = Jadwal::where('kelas_id', $kelasId)
            ->with(['mataPelajaran', 'guru', 'kelas'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('siswa.presensi.index', [
            'siswa' => $siswa,
            'jadwals' => $jadwals,
        ]);
    }

    /**
     * Tahap 2: Tampilkan daftar sesi pertemuan kelas untuk satu mata pelajaran tertentu.
     */
    public function show(Jadwal $jadwal): View
    {
        $siswa = auth()->user();

        if (!$siswa->kelas_id || $jadwal->kelas_id !== $siswa->kelas_id) {
            abort(403, 'Jadwal mata pelajaran ini bukan untuk kelas Anda.');
        }

        $jadwal->load(['mataPelajaran', 'kelas', 'guru']);

        SesiPresensi::where('jadwal_id', $jadwal->id)
            ->get()
            ->each(function (SesiPresensi $sesi): void {
                $sesi->syncExpiredStatus();
            });

        $sesis = SesiPresensi::where('jadwal_id', $jadwal->id)
            ->with(['presensis' => function ($query) use ($siswa): void {
                $query->where('siswa_id', $siswa->id);
            }])
            ->orderBy('pertemuan_ke')
            ->orderBy('tanggal')
            ->get();

        return view('siswa.presensi.show', [
            'siswa' => $siswa,
            'jadwal' => $jadwal,
            'sesis' => $sesis,
        ]);
    }

    /**
     * Detail sesi presensi untuk modal dialog kehadiran siswa.
     */
    public function detail(SesiPresensi $sesi): JsonResponse
    {
        $siswa = auth()->user();

        // Pastikan sesi ini milik kelas siswa yang bersangkutan
        if ($sesi->jadwal->kelas_id !== $siswa->kelas_id) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi presensi ini bukan untuk kelas Anda.',
            ], 403);
        }

        $presensi = Presensi::where('sesi_presensi_id', $sesi->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        $carbonTgl = Carbon::parse($sesi->tanggal)->locale('id');
        $carbonTgl->settings(['formatFunction' => 'translatedFormat']);

        // Hitung perkiraan sisa waktu jika sesi dibuka dalam WIB (Asia/Jakarta)
        $now = Carbon::now('Asia/Jakarta');
        $jamSelesai = Carbon::parse($sesi->tanggal->format('Y-m-d') . ' ' . $sesi->jam_selesai, 'Asia/Jakarta');
        $sisaMenit = max(0, (int) $now->diffInMinutes($jamSelesai, false));

        return response()->json([
            'success' => true,
            'id' => $sesi->id,
            'mapel' => $sesi->jadwal->mataPelajaran->nama_mapel,
            'kode_mapel' => $sesi->jadwal->mataPelajaran->kode_mapel,
            'kelas' => $sesi->jadwal->kelas->nama_kelas,
            'guru' => $sesi->jadwal->guru->name,
            'pertemuan_ke' => $sesi->pertemuan_ke,
            'deskripsi' => 'Pertemuan ' . $sesi->pertemuan_ke . ' - ' . $sesi->materi,
            'materi' => $sesi->materi,
            'tanggal' => $carbonTgl->translatedFormat('d F Y'),
            'jam_presensi' => substr($sesi->jam_mulai, 0, 5) . ' - ' . substr($sesi->jam_selesai, 0, 5) . ' WIB',
            'metode' => $sesi->metode,
            'status_sesi' => $sesi->status_sesi,
            'is_aktif' => $sesi->isAktif(),
            'sisa_menit' => $sisaMenit,
            'sudah_hadir' => $presensi && $presensi->status === 'Hadir',
            'status_saat_ini' => $presensi ? $presensi->status : 'Belum Presensi',
            'waktu_presensi' => $presensi && $presensi->waktu_presensi ? substr($presensi->waktu_presensi, 0, 5) . ' WIB' : null,
        ]);
    }

    /**
     * Submit kehadiran siswa dengan validasi WAJIB Geolocation GPS & QR Token (jika metode QR).
     */
    public function submit(Request $request, SesiPresensi $sesi): JsonResponse
    {
        $siswa = auth()->user();

        // 1. Validasi hak akses kelas siswa
        if ($sesi->jadwal->kelas_id !== $siswa->kelas_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar dalam kelas mata pelajaran ini.',
            ], 403);
        }

        // 2. Validasi status sesi & rentang waktu WIB
        if ($sesi->status_sesi !== 'dibuka') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi presensi ini telah ditutup oleh Guru Pengampu.',
            ], 422);
        }

        if (!$sesi->isAktif()) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu presensi untuk sesi pertemuan ini belum dimulai atau telah berakhir.',
            ], 422);
        }

        // 3. Validasi WAJIB GPS Lokasi
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'alamat_lokasi' => 'required|string|min:5|max:500',
            'keterangan' => 'nullable|string|max:255',
            'qr_token' => $sesi->metode === 'qr' ? 'required|string' : 'nullable|string',
        ], [
            'latitude.required' => 'Koordinat Latitude GPS wajib terdeteksi.',
            'longitude.required' => 'Koordinat Longitude GPS wajib terdeteksi.',
            'alamat_lokasi.required' => 'Hasil geocoding alamat lokasi wajib tersedia.',
            'qr_token.required' => 'Token QR Code wajib diisi atau dipindai.',
        ]);

        // 4. Jika metode sesi adalah QR, validasi kesesuaian & kedaluwarsa token QR
        if ($sesi->metode === 'qr') {
            if (!$sesi->isQrValid($validated['qr_token'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode QR tidak valid atau telah kedaluwarsa. Silakan scan ulang kode QR terbaru yang ditampilkan Guru.',
                ], 422);
            }
        }

        // 5. Simpan catatan kehadiran siswa (WIB Asia/Jakarta)
        $waktuSekarang = Carbon::now('Asia/Jakarta')->format('H:i:s');
        $presensi = Presensi::updateOrCreate(
            [
                'sesi_presensi_id' => $sesi->id,
                'siswa_id' => $siswa->id,
            ],
            [
                'jadwal_id' => $sesi->jadwal_id,
                'tanggal' => $sesi->tanggal,
                'waktu_presensi' => $waktuSekarang,
                'status' => 'Hadir',
                'keterangan' => $validated['keterangan'] ?? ('Presensi ' . ($sesi->metode === 'qr' ? 'QR Code' : 'Mandiri') . ' berhasil diverifikasi'),
                'latitude' => (string) $validated['latitude'],
                'longitude' => (string) $validated['longitude'],
                'alamat_lokasi' => $validated['alamat_lokasi'],
                'metode_absen' => $sesi->metode,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil dicatat! Status: Hadir.',
            'waktu' => substr($waktuSekarang, 0, 5) . ' WIB',
            'status' => 'Hadir',
            'alamat' => $presensi->alamat_lokasi,
        ]);
    }
}
