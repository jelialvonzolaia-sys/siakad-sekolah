<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\View\View;

class GuruDashboardController extends Controller
{
    /**
     * Tampilkan dasbor Tenaga Pendidik (Guru) SMAN 1 Gunungsitoli.
     */
    public function index(): View
    {
        $guru = auth()->user();

        // Jumlah kelas unik yang diajar oleh guru ini
        $jumlahKelasDiajar = $guru->jadwals()->distinct('kelas_id')->count('kelas_id');
        $totalJadwal = $guru->jadwals()->count();
        $totalMateri = $guru->materis()->count();

        $hariIni = match (now()->dayOfWeekIso) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Senin',
        };

        // Jadwal mengajar guru pada hari ini
        $jadwalHariIni = $guru->jadwals()
            ->where('hari', $hariIni)
            ->with(['kelas', 'mataPelajaran'])
            ->orderBy('jam_mulai')
            ->get();

        // Seluruh jadwal mengajar guru
        $semuaJadwal = $guru->jadwals()
            ->with(['kelas', 'mataPelajaran'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        // Materi yang pernah diunggah oleh guru ini
        $materis = $guru->materis()
            ->with(['kelas', 'mataPelajaran'])
            ->latest()
            ->take(6)
            ->get();

        // Pengumuman dinas/sekolah untuk guru
        $pengumumans = Pengumuman::where('is_published', true)
            ->whereIn('target_role', ['semua', 'guru'])
            ->with('penulis')
            ->latest()
            ->take(3)
            ->get();

        return view('guru.dashboard', compact(
            'guru',
            'jumlahKelasDiajar',
            'totalJadwal',
            'totalMateri',
            'hariIni',
            'jadwalHariIni',
            'semuaJadwal',
            'materis',
            'pengumumans'
        ));
    }
}
