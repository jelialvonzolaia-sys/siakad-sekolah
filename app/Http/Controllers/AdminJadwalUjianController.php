<?php

namespace App\Http\Controllers;

use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminJadwalUjianController extends Controller
{
    /**
     * Tampilkan daftar jadwal ujian dengan filter per kelas.
     */
    public function index(Request $request): View
    {
        $selectedKelasId = $request->get('kelas_id');

        $jadwalUjians = JadwalUjian::with(['kelas', 'mataPelajaran'])
            ->when($selectedKelasId !== null && $selectedKelasId !== '', function ($query) use ($selectedKelasId) {
                $query->where('kelas_id', $selectedKelasId);
            })
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->get();

        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $mapelList = MataPelajaran::orderBy('nama_mapel')->get();

        return view('admin.jadwal-ujian.index', compact(
            'jadwalUjians',
            'kelasList',
            'mapelList',
            'selectedKelasId'
        ));
    }

    /**
     * Simpan jadwal ujian baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $ujian = JadwalUjian::create($validated);
        $ujian->load(['mataPelajaran', 'kelas']);

        $siswaIds = User::where('role', 'siswa')
            ->where('kelas_id', $ujian->kelas_id)
            ->pluck('id');

        Notifikasi::kirimUntukBanyak(
            $siswaIds,
            'Jadwal Ujian Baru: ' . ($ujian->mataPelajaran?->nama_mapel ?? 'Mata Pelajaran'),
            'Jadwal ' . $ujian->jenis_ujian . ' kelas ' . ($ujian->kelas?->nama_kelas ?? '') . ' telah diterbitkan (' . \Carbon\Carbon::parse($ujian->tanggal)->timezone('Asia/Jakarta')->translatedFormat('d M Y') . ', ' . substr($ujian->waktu_mulai, 0, 5) . ' WIB).',
            'ujian',
            route('siswa.jadwal-ujian.index')
        );

        return redirect()
            ->route('admin.jadwal-ujian.index')
            ->with('success', 'Jadwal ujian berhasil ditambahkan.');
    }

    /**
     * Perbarui jadwal ujian.
     */
    public function update(Request $request, JadwalUjian $jadwalUjian): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $jadwalUjian->update($validated);

        return redirect()
            ->route('admin.jadwal-ujian.index')
            ->with('success', 'Jadwal ujian berhasil diperbarui.');
    }

    /**
     * Hapus jadwal ujian.
     */
    public function destroy(JadwalUjian $jadwalUjian): RedirectResponse
    {
        $jadwalUjian->delete();

        return redirect()
            ->route('admin.jadwal-ujian.index')
            ->with('success', 'Jadwal ujian berhasil dihapus.');
    }

    /**
     * Validasi input jadwal ujian.
     *
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mata_pelajarans,id'],
            'jenis_ujian' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'ruangan' => ['required', 'string', 'max:100'],
        ], [
            'kelas_id.required' => 'Kelas peserta ujian wajib dipilih.',
            'mapel_id.required' => 'Mata pelajaran wajib dipilih.',
            'jenis_ujian.required' => 'Jenis ujian wajib diisi.',
            'tanggal.required' => 'Tanggal ujian wajib diisi.',
            'waktu_mulai.required' => 'Waktu mulai wajib diisi.',
            'ruangan.required' => 'Ruangan ujian wajib diisi.',
        ]);
    }
}
