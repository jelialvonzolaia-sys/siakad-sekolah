<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\View\View;

class SiswaDashboardController extends Controller
{
    /**
     * Tampilkan Beranda Siswa SMAN 1 Gunungsitoli.
     */
    public function index(): View
    {
        $siswa = auth()->user()->load(['kelas.waliKelas']);
        $kelas = $siswa->kelas;

        // 1. Perhitungan Persentase Kehadiran Siswa
        $totalPresensi = $siswa->presensis()->count();
        $totalHadir = $siswa->presensis()->where('status', 'Hadir')->count();
        $totalSakit = $siswa->presensis()->where('status', 'Sakit')->count();
        $totalIzin = $siswa->presensis()->where('status', 'Izin')->count();
        $totalAlpha = $siswa->presensis()->where('status', 'Alpha')->count();

        $persentaseKehadiran = $totalPresensi > 0
            ? round(($totalHadir / $totalPresensi) * 100)
            : 100;

        // 2. Hari Ini dan Jadwal Hari Ini
        $hariIni = match (now()->dayOfWeekIso) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Senin',
        };

        $jadwalHariIniCount = 0;
        $jadwalSemuaHari = collect();
        $totalMateri = 0;
        $materiTerbaru = collect();
        $jadwalUjian = collect();

        if ($kelas) {
            $jadwalHariIniCount = $kelas->jadwals()->where('hari', $hariIni)->count();

            // Jadwal lengkap dikelompokkan berdasarkan hari
            $jadwalSemuaHari = $kelas->jadwals()
                ->with(['mataPelajaran', 'guru'])
                ->orderBy('jam_mulai')
                ->get()
                ->groupBy('hari');

            // Total materi dan materi terbaru
            $totalMateri = $kelas->materis()->count();
            $materiTerbaru = $kelas->materis()
                ->with(['mataPelajaran', 'guru'])
                ->latest()
                ->take(3)
                ->get();

            // Jadwal Ujian Terdekat
            $jadwalUjian = $kelas->jadwalUjians()
                ->with('mataPelajaran')
                ->orderBy('tanggal')
                ->orderBy('waktu_mulai')
                ->take(6)
                ->get();
        }

        // 3. Pengumuman untuk Siswa & Semua
        $pengumumans = Pengumuman::where('is_published', true)
            ->whereIn('target_role', ['semua', 'siswa'])
            ->with('penulis')
            ->latest()
            ->take(3)
            ->get();

        return view('siswa.dashboard', compact(
            'siswa',
            'kelas',
            'persentaseKehadiran',
            'totalPresensi',
            'totalHadir',
            'totalSakit',
            'totalIzin',
            'totalAlpha',
            'hariIni',
            'jadwalHariIniCount',
            'jadwalSemuaHari',
            'totalMateri',
            'materiTerbaru',
            'jadwalUjian',
            'pengumumans'
        ));
    }
}
