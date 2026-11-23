<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminKelasController extends Controller
{
    /**
     * Tampilkan daftar seluruh rombongan belajar (kelas).
     */
    public function index(Request $request): View
    {
        $searchQuery = trim((string) $request->get('q', ''));
        $tingkatFilter = trim((string) $request->get('tingkat', ''));

        $kelasList = Kelas::with('waliKelas')
            ->withCount(['siswas' => function ($query) {
                $query->where('role', 'siswa');
            }])
            ->when($searchQuery !== '', function ($query) use ($searchQuery) {
                $query->where('nama_kelas', 'like', "%{$searchQuery}%");
            })
            ->when($tingkatFilter !== '', function ($query) use ($tingkatFilter) {
                $query->where('tingkat', $tingkatFilter);
            })
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $gurus = User::where('role', 'guru')
            ->orderBy('name')
            ->get();

        return view('admin.kelas.index', compact(
            'kelasList',
            'gurus',
            'searchQuery',
            'tingkatFilter'
        ));
    }

    /**
     * Tampilkan halaman rekap anggota siswa dalam satu kelas.
     */
    public function show(Kelas $kelas): View
    {
        $kelas->load('waliKelas');

        $siswas = User::where('role', 'siswa')
            ->where('kelas_id', $kelas->id)
            ->orderBy('nis_nip')
            ->orderBy('name')
            ->get();

        return view('admin.kelas.show', compact('kelas', 'siswas'));
    }

    /**
     * Simpan data kelas baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:100', 'unique:kelas,nama_kelas'],
            'tingkat' => ['required', 'string', Rule::in(['10', '11', '12', 'X', 'XI', 'XII'])],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.unique' => 'Nama kelas sudah terdaftar.',
            'tingkat.required' => 'Tingkat kelas wajib dipilih.',
            'wali_kelas_id.exists' => 'Wali kelas yang dipilih tidak valid.',
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => $validated['nama_kelas'],
            'tingkat' => $validated['tingkat'],
            'wali_kelas_id' => $validated['wali_kelas_id'] ?: null,
        ]);

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', "Data kelas {$kelas->nama_kelas} berhasil ditambahkan.");
    }

    /**
     * Perbarui data kelas yang sudah ada.
     */
    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:100', Rule::unique('kelas', 'nama_kelas')->ignore($kelas->id)],
            'tingkat' => ['required', 'string', Rule::in(['10', '11', '12', 'X', 'XI', 'XII'])],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.unique' => 'Nama kelas sudah terdaftar.',
            'tingkat.required' => 'Tingkat kelas wajib dipilih.',
            'wali_kelas_id.exists' => 'Wali kelas yang dipilih tidak valid.',
        ]);

        $kelas->update([
            'nama_kelas' => $validated['nama_kelas'],
            'tingkat' => $validated['tingkat'],
            'wali_kelas_id' => $validated['wali_kelas_id'] ?: null,
        ]);

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', "Data kelas {$kelas->nama_kelas} berhasil diperbarui.");
    }

    /**
     * Hapus data kelas dari database.
     */
    public function destroy(Kelas $kelas): RedirectResponse
    {
        $namaKelas = $kelas->nama_kelas;
        $kelas->delete();

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', "Data kelas {$namaKelas} berhasil dihapus.");
    }
}
