<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\Materi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SiswaMateriController extends Controller
{
    /**
     * Tampilkan daftar materi pelajaran untuk kelas siswa yang sedang login.
     */
    public function index(Request $request): View
    {
        $siswa = auth()->user();
        $kelasId = $siswa->kelas_id;

        if (!$kelasId) {
            abort(403, 'Akun siswa belum terhubung dengan rombel kelas manapun.');
        }

        // Query materi otomatis terfilter ke kelas siswa yang login
        $query = Materi::where('kelas_id', $kelasId)
            ->with(['guru', 'mataPelajaran', 'kelas'])
            ->latest();

        // Filter tab/dropdown berdasarkan Mata Pelajaran
        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        // Kolom pencarian judul / materi
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                  ->orWhere('deskripsi', 'like', "%{$keyword}%");
            });
        }

        $materis = $query->paginate(12)->withQueryString();

        // Ambil seluruh mata pelajaran yang diajarkan pada kelas siswa ini
        $mapelList = MataPelajaran::whereHas('jadwals', function ($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })->orderBy('nama_mapel')->get();

        return view('siswa.materi.index', [
            'pageTitle' => 'Materi Pelajaran',
            'pageSubtitle' => 'Kelas ' . ($siswa->kelas->nama_kelas ?? '-') . ' • SMAN 1 Gunungsitoli',
            'siswa' => $siswa,
            'materis' => $materis,
            'mapelList' => $mapelList,
            'selectedMapelId' => $request->mapel_id,
            'searchQuery' => $request->q,
        ]);
    }

    /**
     * Unduh / lihat berkas materi pembelajaran oleh siswa kelas bersangkutan.
     */
    public function download(Materi $materi): BinaryFileResponse|RedirectResponse
    {
        $siswa = auth()->user();

        // Pastikan materi diperuntukkan bagi kelas siswa yang bersangkutan
        if ($materi->kelas_id !== $siswa->kelas_id) {
            abort(403, 'Akses Ditolak. Materi ini tidak dialokasikan untuk kelas Anda.');
        }

        if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
            $extension = pathinfo($materi->file_path, PATHINFO_EXTENSION);
            $filename = Str::slug($materi->judul) . '.' . $extension;

            return Storage::disk('public')->download($materi->file_path, $filename);
        }

        // Jika berkas adalah URL publik atau belum tersedia fisik di disk
        $publicUrl = Storage::url($materi->file_path);

        return redirect()->away($publicUrl);
    }
}
