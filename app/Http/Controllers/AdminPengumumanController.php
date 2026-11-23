<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Pengumuman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPengumumanController extends Controller
{
    /**
     * Tampilkan daftar pengumuman sekolah untuk Admin TU.
     */
    public function index(Request $request): View
    {
        $searchQuery = trim((string) $request->get('q', ''));
        $targetFilter = trim((string) $request->get('target_role', ''));
        $statusFilter = trim((string) $request->get('status', ''));

        $pengumumans = Pengumuman::with('penulis')
            ->when($searchQuery !== '', function ($query) use ($searchQuery) {
                $query->where('judul', 'like', "%{$searchQuery}%");
            })
            ->when($targetFilter !== '', function ($query) use ($targetFilter) {
                $query->where('target_role', $targetFilter);
            })
            ->when($statusFilter === 'published', function ($query) {
                $query->where('is_published', true);
            })
            ->when($statusFilter === 'draft', function ($query) {
                $query->where('is_published', false);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pengumuman.index', compact(
            'pengumumans',
            'searchQuery',
            'targetFilter',
            'statusFilter'
        ));
    }

    /**
     * Tampilkan form buat pengumuman baru dengan Rich Text Editor.
     */
    public function create(): View
    {
        return view('admin.pengumuman.create');
    }

    /**
     * Simpan pengumuman sekolah baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'target_role' => ['required', Rule::in(['semua', 'guru', 'siswa'])],
            'is_published' => ['required', 'boolean'],
        ], [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
            'gambar.image' => 'Poster pengumuman harus berupa file gambar.',
            'gambar.mimes' => 'Format poster harus JPG, PNG, atau WEBP.',
            'gambar.max' => 'Ukuran poster maksimal 5 MB.',
            'target_role.required' => 'Target penerima wajib dipilih.',
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('pengumuman', 'public');
        }

        $now = Carbon::now('Asia/Jakarta');
        $pengumuman = Pengumuman::create([
            'judul' => trim($validated['judul']),
            'isi' => $validated['isi'],
            'gambar_path' => $gambarPath,
            'target_role' => $validated['target_role'],
            'is_published' => (bool) $validated['is_published'],
            'penulis_id' => auth()->id(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($pengumuman->is_published) {
            $this->dispatchPengumumanNotifikasi($pengumuman);
        }

        $statusLabel = $pengumuman->is_published ? 'diterbitkan' : 'disimpan sebagai draf';

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', "Pengumuman \"{$pengumuman->judul}\" berhasil {$statusLabel}.");
    }

    /**
     * Tampilkan form edit pengumuman sekolah.
     */
    public function edit(Pengumuman $pengumuman): View
    {
        return view('admin.pengumuman.edit', compact('pengumuman'));
    }

    /**
     * Perbarui pengumuman sekolah.
     */
    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'hapus_gambar' => ['nullable', 'boolean'],
            'target_role' => ['required', Rule::in(['semua', 'guru', 'siswa'])],
            'is_published' => ['required', 'boolean'],
        ], [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
            'gambar.image' => 'Poster pengumuman harus berupa file gambar.',
            'gambar.mimes' => 'Format poster harus JPG, PNG, atau WEBP.',
            'gambar.max' => 'Ukuran poster maksimal 5 MB.',
            'target_role.required' => 'Target penerima wajib dipilih.',
        ]);

        $wasPublished = (bool) $pengumuman->is_published;
        $gambarPath = $pengumuman->gambar_path;

        if ($request->boolean('hapus_gambar') && $gambarPath) {
            if (Storage::disk('public')->exists($gambarPath)) {
                Storage::disk('public')->delete($gambarPath);
            }
            $gambarPath = null;
        }

        if ($request->hasFile('gambar')) {
            if ($gambarPath && Storage::disk('public')->exists($gambarPath)) {
                Storage::disk('public')->delete($gambarPath);
            }
            $gambarPath = $request->file('gambar')->store('pengumuman', 'public');
        }

        $pengumuman->update([
            'judul' => trim($validated['judul']),
            'isi' => $validated['isi'],
            'gambar_path' => $gambarPath,
            'target_role' => $validated['target_role'],
            'is_published' => (bool) $validated['is_published'],
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);

        if (!$wasPublished && $pengumuman->is_published) {
            $this->dispatchPengumumanNotifikasi($pengumuman);
        }

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', "Pengumuman \"{$pengumuman->judul}\" berhasil diperbarui.");
    }

    /**
     * Hapus pengumuman sekolah.
     */
    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        $judul = $pengumuman->judul;

        if ($pengumuman->gambar_path && Storage::disk('public')->exists($pengumuman->gambar_path)) {
            Storage::disk('public')->delete($pengumuman->gambar_path);
        }

        $pengumuman->delete();

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', "Pengumuman \"{$judul}\" berhasil dihapus.");
    }

    /**
     * Kirim notifikasi ke target guru dan/atau siswa saat pengumuman diterbitkan.
     */
    private function dispatchPengumumanNotifikasi(Pengumuman $pengumuman): void
    {
        $pesanRingkas = 'Pengumuman resmi sekolah baru diterbitkan pada ' . Carbon::now('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB.';

        if (in_array($pengumuman->target_role, ['semua', 'siswa'], true)) {
            $siswaIds = User::where('role', 'siswa')->pluck('id');
            Notifikasi::kirimUntukBanyak(
                $siswaIds,
                'Pengumuman: ' . $pengumuman->judul,
                $pesanRingkas,
                'pengumuman',
                route('siswa.pengumuman.show', $pengumuman->id)
            );
        }

        if (in_array($pengumuman->target_role, ['semua', 'guru'], true)) {
            $guruIds = User::where('role', 'guru')->pluck('id');
            Notifikasi::kirimUntukBanyak(
                $guruIds,
                'Pengumuman: ' . $pengumuman->judul,
                $pesanRingkas,
                'pengumuman',
                route('guru.pengumuman.show', $pengumuman->id)
            );
        }
    }
}
