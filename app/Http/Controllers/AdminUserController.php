<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Tampilkan halaman Data Guru & Siswa dengan 2 tab utama.
     */
    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'guru');
        if (!in_array($activeTab, ['guru', 'siswa'], true)) {
            $activeTab = 'guru';
        }

        $selectedKelasId = $request->get('kelas_id');
        $searchQuery = trim((string) $request->get('q', ''));

        $totalGuru = User::where('role', 'guru')->count();
        $totalSiswa = User::where('role', 'siswa')->count();
        $totalKelas = Kelas::count();

        $gurus = User::where('role', 'guru')
            ->with(['kelasWali', 'mataPelajarans', 'jadwals.mataPelajaran'])
            ->when($activeTab === 'guru' && $searchQuery !== '', function ($query) use ($searchQuery) {
                $query->where(function ($sub) use ($searchQuery) {
                    $sub->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('nis_nip', 'like', "%{$searchQuery}%")
                        ->orWhere('email', 'like', "%{$searchQuery}%");
                });
            })
            ->orderBy('name')
            ->get();

        $kelasCards = Kelas::with('waliKelas')
            ->withCount(['siswas' => function ($query) {
                $query->where('role', 'siswa');
            }])
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $unassignedSiswaCount = User::where('role', 'siswa')
            ->whereNull('kelas_id')
            ->count();

        $selectedKelas = null;
        $siswas = collect();

        if ($activeTab === 'siswa') {
            if ($selectedKelasId === 'unassigned') {
                $siswas = User::where('role', 'siswa')
                    ->whereNull('kelas_id')
                    ->when($searchQuery !== '', function ($query) use ($searchQuery) {
                        $query->where(function ($sub) use ($searchQuery) {
                            $sub->where('name', 'like', "%{$searchQuery}%")
                                ->orWhere('nis_nip', 'like', "%{$searchQuery}%")
                                ->orWhere('email', 'like', "%{$searchQuery}%");
                        });
                    })
                    ->orderBy('nis_nip')
                    ->orderBy('name')
                    ->get();
            } elseif ($selectedKelasId) {
                $selectedKelas = Kelas::with('waliKelas')->find($selectedKelasId);
                if ($selectedKelas) {
                    $siswas = User::where('role', 'siswa')
                        ->where('kelas_id', $selectedKelas->id)
                        ->when($searchQuery !== '', function ($query) use ($searchQuery) {
                            $query->where(function ($sub) use ($searchQuery) {
                                $sub->where('name', 'like', "%{$searchQuery}%")
                                    ->orWhere('nis_nip', 'like', "%{$searchQuery}%")
                                    ->orWhere('email', 'like', "%{$searchQuery}%");
                            });
                        })
                        ->orderBy('nis_nip')
                        ->orderBy('name')
                        ->get();
                }
            } elseif ($searchQuery !== '') {
                $siswas = User::where('role', 'siswa')
                    ->with('kelas')
                    ->where(function ($sub) use ($searchQuery) {
                        $sub->where('name', 'like', "%{$searchQuery}%")
                            ->orWhere('nis_nip', 'like', "%{$searchQuery}%")
                            ->orWhere('email', 'like', "%{$searchQuery}%");
                    })
                    ->orderBy('nis_nip')
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('admin.users.index', compact(
            'activeTab',
            'selectedKelasId',
            'selectedKelas',
            'searchQuery',
            'totalGuru',
            'totalSiswa',
            'totalKelas',
            'gurus',
            'kelasCards',
            'unassignedSiswaCount',
            'siswas'
        ));
    }

    /**
     * Simpan akun Guru baru.
     */
    public function storeGuru(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis_nip' => ['required', 'string', 'max:50', 'unique:users,nis_nip'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap guru wajib diisi.',
            'nis_nip.required' => 'NIP guru wajib diisi.',
            'nis_nip.unique' => 'NIP sudah terdaftar.',
            'email.required' => 'Email guru wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'password.required' => 'Kata sandi awal wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $guru = User::create([
            'name' => trim($validated['name']),
            'nis_nip' => trim($validated['nis_nip']),
            'email' => trim($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => 'guru',
            'kelas_id' => null,
        ]);

        return redirect()
            ->route('admin.users.index', ['tab' => 'guru'])
            ->with('success', "Akun guru {$guru->name} berhasil dibuat.");
    }

    /**
     * Tampilkan halaman detail satu layar untuk Guru.
     */
    public function showGuru(User $guru): View
    {
        abort_unless($guru->role === 'guru', 404);

        $guru->load([
            'kelasWali',
            'mataPelajarans',
            'jadwals.kelas',
            'jadwals.mataPelajaran',
        ]);

        return view('admin.users.guru-show', compact('guru'));
    }

    /**
     * Perbarui profil Guru.
     */
    public function updateGuru(Request $request, User $guru): RedirectResponse
    {
        abort_unless($guru->role === 'guru', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis_nip' => ['required', 'string', 'max:50', Rule::unique('users', 'nis_nip')->ignore($guru->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($guru->id)],
        ], [
            'name.required' => 'Nama lengkap guru wajib diisi.',
            'nis_nip.required' => 'NIP guru wajib diisi.',
            'nis_nip.unique' => 'NIP sudah terdaftar.',
            'email.required' => 'Email guru wajib diisi.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        $guru->update([
            'name' => trim($validated['name']),
            'nis_nip' => trim($validated['nis_nip']),
            'email' => trim($validated['email']),
        ]);

        return redirect()
            ->route('admin.users.guru.show', $guru->id)
            ->with('success', "Data guru {$guru->name} berhasil diperbarui.");
    }

    /**
     * Reset / ganti kata sandi Guru secara langsung oleh Admin TU.
     */
    public function resetPasswordGuru(Request $request, User $guru): RedirectResponse
    {
        abort_unless($guru->role === 'guru', 404);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $guru->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.users.guru.show', $guru->id)
            ->with('success', "Kata sandi guru {$guru->name} berhasil diatur ulang.");
    }

    /**
     * Hapus akun Guru.
     */
    public function destroyGuru(User $guru): RedirectResponse
    {
        abort_unless($guru->role === 'guru', 404);

        $nama = $guru->name;
        $guru->delete();

        return redirect()
            ->route('admin.users.index', ['tab' => 'guru'])
            ->with('success', "Akun guru {$nama} berhasil dihapus.");
    }

    /**
     * Simpan akun Siswa baru.
     */
    public function storeSiswa(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis_nip' => ['required', 'string', 'max:50', 'unique:users,nis_nip'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap siswa wajib diisi.',
            'nis_nip.required' => 'NIS siswa wajib diisi.',
            'nis_nip.unique' => 'NIS sudah terdaftar.',
            'email.required' => 'Email siswa wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',
            'password.required' => 'Kata sandi awal wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $siswa = User::create([
            'name' => trim($validated['name']),
            'nis_nip' => trim($validated['nis_nip']),
            'email' => trim($validated['email']),
            'kelas_id' => $validated['kelas_id'] ?: null,
            'password' => Hash::make($validated['password']),
            'role' => 'siswa',
        ]);

        return redirect()
            ->route('admin.users.index', [
                'tab' => 'siswa',
                'kelas_id' => $siswa->kelas_id ?? 'unassigned',
            ])
            ->with('success', "Akun siswa {$siswa->name} berhasil ditambahkan.");
    }

    /**
     * Tampilkan halaman detail satu layar untuk Siswa.
     */
    public function showSiswa(User $siswa): View
    {
        abort_unless($siswa->role === 'siswa', 404);

        $siswa->load('kelas.waliKelas');

        $totalPresensi = $siswa->presensis()->count();
        $totalHadir = $siswa->presensis()->where('status', 'Hadir')->count();
        $totalSakit = $siswa->presensis()->where('status', 'Sakit')->count();
        $totalIzin = $siswa->presensis()->where('status', 'Izin')->count();
        $totalAlpha = $siswa->presensis()->where('status', 'Alpha')->count();

        $persentaseKehadiran = $totalPresensi > 0
            ? round(($totalHadir / $totalPresensi) * 100)
            : 100;

        $kelasList = Kelas::orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.users.siswa-show', compact(
            'siswa',
            'kelasList',
            'totalPresensi',
            'totalHadir',
            'totalSakit',
            'totalIzin',
            'totalAlpha',
            'persentaseKehadiran'
        ));
    }

    /**
     * Perbarui biodata dan penempatan kelas Siswa.
     */
    public function updateSiswa(Request $request, User $siswa): RedirectResponse
    {
        abort_unless($siswa->role === 'siswa', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis_nip' => ['required', 'string', 'max:50', Rule::unique('users', 'nis_nip')->ignore($siswa->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($siswa->id)],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
        ], [
            'name.required' => 'Nama lengkap siswa wajib diisi.',
            'nis_nip.required' => 'NIS siswa wajib diisi.',
            'nis_nip.unique' => 'NIS sudah terdaftar.',
            'email.required' => 'Email siswa wajib diisi.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',
        ]);

        $siswa->update([
            'name' => trim($validated['name']),
            'nis_nip' => trim($validated['nis_nip']),
            'email' => trim($validated['email']),
            'kelas_id' => $validated['kelas_id'] ?: null,
        ]);

        return redirect()
            ->route('admin.users.siswa.show', $siswa->id)
            ->with('success', "Data siswa {$siswa->name} berhasil diperbarui.");
    }

    /**
     * Reset / ganti kata sandi Siswa secara langsung oleh Admin TU.
     */
    public function resetPasswordSiswa(Request $request, User $siswa): RedirectResponse
    {
        abort_unless($siswa->role === 'siswa', 404);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $siswa->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.users.siswa.show', $siswa->id)
            ->with('success', "Kata sandi siswa {$siswa->name} berhasil diatur ulang.");
    }

    /**
     * Hapus akun Siswa.
     */
    public function destroySiswa(User $siswa): RedirectResponse
    {
        abort_unless($siswa->role === 'siswa', 404);

        $nama = $siswa->name;
        $kelasId = $siswa->kelas_id;
        $siswa->delete();

        return redirect()
            ->route('admin.users.index', [
                'tab' => 'siswa',
                'kelas_id' => $kelasId ?? 'unassigned',
            ])
            ->with('success', "Akun siswa {$nama} berhasil dihapus.");
    }
}
