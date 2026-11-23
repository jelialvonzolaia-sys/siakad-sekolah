<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminMapelController extends Controller
{
    /**
     * Tampilkan daftar mata pelajaran beserta guru pengampu dan KKM.
     */
    public function index(Request $request): View
    {
        $searchQuery = trim((string) $request->get('q', ''));
        $selectedGuruId = $request->get('guru_id');

        $mapels = MataPelajaran::with('guru')
            ->when($searchQuery !== '', function ($query) use ($searchQuery) {
                $query->where(function ($sub) use ($searchQuery) {
                    $sub->where('kode_mapel', 'like', "%{$searchQuery}%")
                        ->orWhere('nama_mapel', 'like', "%{$searchQuery}%");
                });
            })
            ->when($selectedGuruId !== null && $selectedGuruId !== '', function ($query) use ($selectedGuruId) {
                $query->where('guru_id', $selectedGuruId);
            })
            ->orderBy('nama_mapel')
            ->get();

        $gurus = User::where('role', 'guru')
            ->orderBy('name')
            ->get();

        return view('admin.mapel.index', compact(
            'mapels',
            'gurus',
            'searchQuery',
            'selectedGuruId'
        ));
    }

    /**
     * Simpan mata pelajaran baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_mapel' => ['required', 'string', 'max:30', 'unique:mata_pelajarans,kode_mapel'],
            'nama_mapel' => ['required', 'string', 'max:150'],
            'kkm' => ['required', 'integer', 'min:1', 'max:100'],
            'guru_id' => ['nullable', 'exists:users,id'],
        ], [
            'kode_mapel.required' => 'Kode mata pelajaran wajib diisi.',
            'kode_mapel.unique' => 'Kode mata pelajaran sudah digunakan.',
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
            'kkm.required' => 'Nilai KKM wajib diisi.',
            'kkm.min' => 'KKM minimal bernilai 1.',
            'kkm.max' => 'KKM maksimal bernilai 100.',
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => strtoupper(trim($validated['kode_mapel'])),
            'nama_mapel' => trim($validated['nama_mapel']),
            'kkm' => (int) $validated['kkm'],
            'guru_id' => $validated['guru_id'] ?: null,
        ]);

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', "Mata pelajaran {$mapel->nama_mapel} berhasil ditambahkan.");
    }

    /**
     * Perbarui data mata pelajaran.
     */
    public function update(Request $request, MataPelajaran $mapel): RedirectResponse
    {
        $validated = $request->validate([
            'kode_mapel' => ['required', 'string', 'max:30', Rule::unique('mata_pelajarans', 'kode_mapel')->ignore($mapel->id)],
            'nama_mapel' => ['required', 'string', 'max:150'],
            'kkm' => ['required', 'integer', 'min:1', 'max:100'],
            'guru_id' => ['nullable', 'exists:users,id'],
        ], [
            'kode_mapel.required' => 'Kode mata pelajaran wajib diisi.',
            'kode_mapel.unique' => 'Kode mata pelajaran sudah digunakan.',
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
            'kkm.required' => 'Nilai KKM wajib diisi.',
        ]);

        $mapel->update([
            'kode_mapel' => strtoupper(trim($validated['kode_mapel'])),
            'nama_mapel' => trim($validated['nama_mapel']),
            'kkm' => (int) $validated['kkm'],
            'guru_id' => $validated['guru_id'] ?: null,
        ]);

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', "Mata pelajaran {$mapel->nama_mapel} berhasil diperbarui.");
    }

    /**
     * Hapus mata pelajaran dari database.
     */
    public function destroy(MataPelajaran $mapel): RedirectResponse
    {
        $namaMapel = $mapel->nama_mapel;
        $mapel->delete();

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', "Mata pelajaran {$namaMapel} berhasil dihapus.");
    }
}
