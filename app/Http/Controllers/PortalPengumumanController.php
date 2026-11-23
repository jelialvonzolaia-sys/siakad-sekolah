<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalPengumumanController extends Controller
{
    /**
     * Tampilkan daftar pengumuman resmi untuk Guru.
     */
    public function indexGuru(Request $request): View
    {
        return $this->renderIndex($request, 'guru');
    }

    /**
     * Tampilkan detail isi lengkap pengumuman untuk Guru.
     */
    public function showGuru(Pengumuman $pengumuman): View
    {
        return $this->renderShow($pengumuman, 'guru');
    }

    /**
     * Tampilkan daftar pengumuman resmi untuk Siswa.
     */
    public function indexSiswa(Request $request): View
    {
        return $this->renderIndex($request, 'siswa');
    }

    /**
     * Tampilkan detail isi lengkap pengumuman untuk Siswa.
     */
    public function showSiswa(Pengumuman $pengumuman): View
    {
        return $this->renderShow($pengumuman, 'siswa');
    }

    /**
     * Helper daftar pengumuman terfilter per role.
     */
    private function renderIndex(Request $request, string $role): View
    {
        $searchQuery = trim((string) $request->get('q', ''));

        $pengumumans = Pengumuman::with('penulis')
            ->where('is_published', true)
            ->whereIn('target_role', ['semua', $role])
            ->when($searchQuery !== '', function ($query) use ($searchQuery) {
                $query->where('judul', 'like', "%{$searchQuery}%");
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('portal.pengumuman.index', compact('pengumumans', 'searchQuery', 'role'));
    }

    /**
     * Helper detail pengumuman terfilter per role.
     */
    private function renderShow(Pengumuman $pengumuman, string $role): View
    {
        abort_unless(
            $pengumuman->is_published && in_array($pengumuman->target_role, ['semua', $role], true),
            403
        );

        $pengumuman->load('penulis');

        return view('portal.pengumuman.show', compact('pengumuman', 'role'));
    }
}
