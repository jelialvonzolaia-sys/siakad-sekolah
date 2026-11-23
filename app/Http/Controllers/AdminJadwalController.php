<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminJadwalController extends Controller
{
    /**
     * Tampilkan halaman Jadwal Pelajaran dengan filter per Kelas dan per Hari.
     */
    public function index(Request $request): View
    {
        $selectedKelasId = $request->get('kelas_id');
        $selectedHari = $request->get('hari');

        $hariOrder = "CASE hari
            WHEN 'Senin' THEN 1
            WHEN 'Selasa' THEN 2
            WHEN 'Rabu' THEN 3
            WHEN 'Kamis' THEN 4
            WHEN 'Jumat' THEN 5
            WHEN 'Sabtu' THEN 6
            ELSE 7 END";

        $jadwals = Jadwal::with(['kelas', 'mataPelajaran', 'guru'])
            ->when($selectedKelasId !== null && $selectedKelasId !== '', function ($query) use ($selectedKelasId) {
                $query->where('kelas_id', $selectedKelasId);
            })
            ->when($selectedHari !== null && $selectedHari !== '', function ($query) use ($selectedHari) {
                $query->where('hari', $selectedHari);
            })
            ->orderByRaw($hariOrder)
            ->orderBy('jam_mulai')
            ->get();

        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $mapelList = MataPelajaran::orderBy('nama_mapel')->get();
        $guruList = User::where('role', 'guru')->orderBy('name')->get();
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return view('admin.jadwal.index', compact(
            'jadwals',
            'kelasList',
            'mapelList',
            'guruList',
            'hariList',
            'selectedKelasId',
            'selectedHari'
        ));
    }

    /**
     * Simpan jadwal pelajaran baru dengan pengecekan bentrok kelas dan guru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $conflictMessage = $this->detectScheduleConflict(
            (int) $validated['kelas_id'],
            (int) $validated['guru_id'],
            $validated['hari'],
            $validated['jam_mulai'],
            $validated['jam_selesai']
        );

        if ($conflictMessage !== null) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $conflictMessage);
        }

        Jadwal::create($validated);

        return redirect()
            ->route('admin.jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    /**
     * Perbarui jadwal pelajaran dengan pengecekan bentrok.
     */
    public function update(Request $request, Jadwal $jadwal): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $conflictMessage = $this->detectScheduleConflict(
            (int) $validated['kelas_id'],
            (int) $validated['guru_id'],
            $validated['hari'],
            $validated['jam_mulai'],
            $validated['jam_selesai'],
            $jadwal->id
        );

        if ($conflictMessage !== null) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $conflictMessage);
        }

        $jadwal->update($validated);

        return redirect()
            ->route('admin.jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    /**
     * Hapus jadwal pelajaran.
     */
    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return redirect()
            ->route('admin.jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    /**
     * Validasi payload jadwal pelajaran.
     *
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mata_pelajarans,id'],
            'guru_id' => ['required', 'exists:users,id'],
            'hari' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ], [
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'mapel_id.required' => 'Mata pelajaran wajib dipilih.',
            'guru_id.required' => 'Guru pengampu wajib dipilih.',
            'hari.required' => 'Hari wajib dipilih.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.after' => 'Jam selesai harus lebih akhir daripada jam mulai.',
        ]);
    }

    /**
     * Cek bentrok jadwal untuk kelas yang sama atau guru yang sama pada hari & rentang jam bertabrakan.
     */
    private function detectScheduleConflict(
        int $kelasId,
        int $guruId,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        ?int $ignoreId = null
    ): ?string {
        $start = strlen($jamMulai) === 5 ? $jamMulai . ':00' : $jamMulai;
        $end = strlen($jamSelesai) === 5 ? $jamSelesai . ':00' : $jamSelesai;

        $kelasConflict = Jadwal::with(['kelas', 'mataPelajaran'])
            ->where('hari', $hari)
            ->where('kelas_id', $kelasId)
            ->where('jam_mulai', '<', $end)
            ->where('jam_selesai', '>', $start)
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->first();

        if ($kelasConflict) {
            $mulai = substr((string) $kelasConflict->jam_mulai, 0, 5);
            $selesai = substr((string) $kelasConflict->jam_selesai, 0, 5);
            $mapel = $kelasConflict->mataPelajaran->nama_mapel ?? 'Mata Pelajaran lain';
            $kelasNama = $kelasConflict->kelas->nama_kelas ?? '';

            return "Jadwal bentrok! Kelas {$kelasNama} sudah memiliki jadwal {$mapel} pada hari {$hari} pukul {$mulai} - {$selesai} WIB.";
        }

        $guruConflict = Jadwal::with(['kelas', 'guru'])
            ->where('hari', $hari)
            ->where('guru_id', $guruId)
            ->where('jam_mulai', '<', $end)
            ->where('jam_selesai', '>', $start)
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->first();

        if ($guruConflict) {
            $mulai = substr((string) $guruConflict->jam_mulai, 0, 5);
            $selesai = substr((string) $guruConflict->jam_selesai, 0, 5);
            $guruNama = $guruConflict->guru->name ?? 'Guru';
            $kelasNama = $guruConflict->kelas->nama_kelas ?? '';

            return "Jadwal bentrok! {$guruNama} sudah mengajar di kelas {$kelasNama} pada hari {$hari} pukul {$mulai} - {$selesai} WIB.";
        }

        return null;
    }
}
