<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Tampilkan dasbor utama Administrator SMAN 1 Gunungsitoli.
     */
    public function index(): View
    {
        $totalSiswa = User::where('role', 'siswa')->count();
        $totalGuru = User::where('role', 'guru')->count();
        $totalKelas = Kelas::count();
        $totalMapel = MataPelajaran::count();

        $hariIni = match (now()->dayOfWeekIso) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Senin',
        };

        $jadwalHariIni = Jadwal::where('hari', $hariIni)
            ->with(['kelas', 'mataPelajaran', 'guru'])
            ->orderBy('jam_mulai')
            ->get();

        $pengumumanTerbaru = Pengumuman::with('penulis')
            ->where('is_published', true)
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalSiswa',
            'totalGuru',
            'totalKelas',
            'totalMapel',
            'hariIni',
            'jadwalHariIni',
            'pengumumanTerbaru'
        ));
    }
}
