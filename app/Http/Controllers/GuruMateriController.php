<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Materi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GuruMateriController extends Controller
{
    /**
     * Tampilkan daftar materi yang diunggah oleh guru yang sedang login.
     */
    public function index(Request $request): View
    {
        $guruId = auth()->id();

        $query = Materi::where('guru_id', $guruId)
            ->with(['kelas', 'mataPelajaran'])
            ->latest();

        // Filter berdasarkan kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Pencarian judul / deskripsi
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                  ->orWhere('deskripsi', 'like', "%{$keyword}%");
            });
        }

        $materis = $query->paginate(10)->withQueryString();

        // Daftar kelas yang diajar oleh guru ini untuk filter dropdown
        $kelasList = Kelas::whereHas('jadwals', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->orderBy('nama_kelas')->get();

        return view('guru.materi.index', [
            'pageTitle' => 'Kelola Materi Pelajaran',
            'pageSubtitle' => 'SMA Negeri 1 Gunungsitoli',
            'materis' => $materis,
            'kelasList' => $kelasList,
            'selectedKelasId' => $request->kelas_id,
            'searchQuery' => $request->q,
        ]);
    }

    /**
     * Tampilkan formulir unggah materi baru.
     */
    public function create(): View
    {
        $guruId = auth()->id();

        // Ambil daftar rombel kelas dan mata pelajaran yang diampu oleh guru ini
        $jadwals = Jadwal::where('guru_id', $guruId)
            ->with(['kelas', 'mataPelajaran'])
            ->get();

        $kelasList = $jadwals->pluck('kelas')->unique('id')->values();
        $mapelList = $jadwals->pluck('mataPelajaran')->unique('id')->values();

        return view('guru.materi.create', [
            'pageTitle' => 'Unggah Materi Pelajaran',
            'pageSubtitle' => 'Publikasikan modul dan bahan ajar siswa',
            'jadwals' => $jadwals,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
        ]);
    }

    /**
     * Simpan berkas materi pelajaran baru ke storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $guruId = auth()->id();

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajarans,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'file_materi' => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
        ], [
            'kelas_id.required' => 'Pilih kelas target materi.',
            'mapel_id.required' => 'Pilih mata pelajaran materi.',
            'judul.required' => 'Judul materi wajib diisi.',
            'file_materi.required' => 'Berkas materi wajib diunggah.',
            'file_materi.mimes' => 'Format berkas harus berupa PDF, DOC, DOCX, PPT, atau PPTX.',
            'file_materi.max' => 'Ukuran berkas maksimal adalah 10 MB.',
        ]);

        // Simpan file ke storage/app/public/materi
        $filePath = $request->file('file_materi')->store('materi', 'public');

        Materi::create([
            'mapel_id' => $validated['mapel_id'],
            'guru_id' => $guruId,
            'kelas_id' => $validated['kelas_id'],
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'file_path' => $filePath,
        ]);

        return redirect()->route('guru.materi.index')
            ->with('status', 'Materi "' . $validated['judul'] . '" berhasil diunggah.');
    }

    /**
     * Tampilkan formulir edit materi.
     */
    public function edit(Materi $materi): View
    {
        $this->authorizeOwner($materi);

        $guruId = auth()->id();
        $jadwals = Jadwal::where('guru_id', $guruId)
            ->with(['kelas', 'mataPelajaran'])
            ->get();

        $kelasList = $jadwals->pluck('kelas')->unique('id')->values();
        $mapelList = $jadwals->pluck('mataPelajaran')->unique('id')->values();

        return view('guru.materi.edit', [
            'pageTitle' => 'Edit Materi Pelajaran',
            'pageSubtitle' => $materi->judul,
            'materi' => $materi,
            'jadwals' => $jadwals,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
        ]);
    }

    /**
     * Perbarui data dan/atau berkas materi pelajaran.
     */
    public function update(Request $request, Materi $materi): RedirectResponse
    {
        $this->authorizeOwner($materi);

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajarans,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'file_materi' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
        ], [
            'kelas_id.required' => 'Pilih kelas target materi.',
            'mapel_id.required' => 'Pilih mata pelajaran materi.',
            'judul.required' => 'Judul materi wajib diisi.',
            'file_materi.mimes' => 'Format berkas harus berupa PDF, DOC, DOCX, PPT, atau PPTX.',
            'file_materi.max' => 'Ukuran berkas maksimal adalah 10 MB.',
        ]);

        // Jika ada unggahan file baru, hapus file lama dan simpan yang baru
        if ($request->hasFile('file_materi')) {
            if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
                Storage::disk('public')->delete($materi->file_path);
            }
            $materi->file_path = $request->file('file_materi')->store('materi', 'public');
        }

        $materi->update([
            'kelas_id' => $validated['kelas_id'],
            'mapel_id' => $validated['mapel_id'],
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
        ]);

        return redirect()->route('guru.materi.index')
            ->with('status', 'Materi "' . $materi->judul . '" berhasil diperbarui.');
    }

    /**
     * Hapus materi beserta berkas fisiknya dari storage.
     */
    public function destroy(Materi $materi): RedirectResponse
    {
        $this->authorizeOwner($materi);

        $judul = $materi->judul;

        // Hapus file fisik dari storage
        if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
            Storage::disk('public')->delete($materi->file_path);
        }

        // Hapus rekaman dari database
        $materi->delete();

        return redirect()->route('guru.materi.index')
            ->with('status', 'Materi "' . $judul . '" beserta berkasnya berhasil dihapus.');
    }

    /**
     * Unduh / buka berkas materi oleh guru pemilik.
     */
    public function download(Materi $materi): BinaryFileResponse|RedirectResponse
    {
        $this->authorizeOwner($materi);

        if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
            return Storage::disk('public')->download($materi->file_path);
        }

        return redirect()->back()->with('error', 'Berkas fisik materi belum tersedia di penyimpanan server.');
    }

    /**
     * Pastikan hanya guru pengunggah yang dapat mengedit atau menghapus materi.
     */
    private function authorizeOwner(Materi $materi): void
    {
        if ($materi->guru_id !== auth()->id()) {
            abort(403, 'Akses Ditolak. Anda bukan pengunggah materi ini.');
        }
    }
}
