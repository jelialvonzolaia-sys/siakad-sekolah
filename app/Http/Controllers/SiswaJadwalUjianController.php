<?php

namespace App\Http\Controllers;

use App\Models\JadwalUjian;
use Illuminate\View\View;

class SiswaJadwalUjianController extends Controller
{
    /**
     * Menampilkan daftar jadwal ujian resmi sekolah untuk kelas siswa yang login.
     */
    public function index(): View
    {
        $siswa = auth()->user();
        $siswa->load('kelas');

        $jadwalUjians = collect();
        if ($siswa->kelas_id) {
            $jadwalUjians = JadwalUjian::where('kelas_id', $siswa->kelas_id)
                ->with(['kelas', 'mataPelajaran'])
                ->orderBy('tanggal')
                ->orderBy('waktu_mulai')
                ->get();
        }

        $jenisUjianAktif = $jadwalUjians->first()?->jenis_ujian ?? 'Ujian Sekolah';

        return view('siswa.jadwal-ujian.index', compact(
            'siswa',
            'jadwalUjians',
            'jenisUjianAktif'
        ));
    }
}
