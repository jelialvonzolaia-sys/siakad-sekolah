<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminJadwalController;
use App\Http\Controllers\AdminJadwalUjianController;
use App\Http\Controllers\AdminKelasController;
use App\Http\Controllers\AdminMapelController;
use App\Http\Controllers\AdminPengumumanController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\GuruDashboardController;
use App\Http\Controllers\GuruMateriController;
use App\Http\Controllers\GuruPresensiController;
use App\Http\Controllers\GuruTugasController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PortalPengumumanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiswaDashboardController;
use App\Http\Controllers\SiswaJadwalUjianController;
use App\Http\Controllers\SiswaMateriController;
use App\Http\Controllers\SiswaPresensiController;
use App\Http\Controllers\SiswaTugasController;
use Illuminate\Support\Facades\Route;

// Halaman utama: jika belum login, tampilkan form login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : app(AuthenticatedSessionController::class)->create();
});

// Redirector otomatis berdasarkan peran pengguna
Route::get('/dashboard', function () {
    $role = auth()->user()->role;

    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'guru' => redirect()->route('guru.dashboard'),
        'siswa' => redirect()->route('siswa.dashboard'),
        default => view('dashboard'),
    };
})->middleware(['auth'])->name('dashboard');

// Rute khusus Administrator SMAN 1 Gunungsitoli
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Data Kelas & Rekap Anggota Kelas
    Route::get('/kelas', [AdminKelasController::class, 'index'])->name('kelas.index');
    Route::post('/kelas', [AdminKelasController::class, 'store'])->name('kelas.store');
    Route::get('/kelas/{kelas}', [AdminKelasController::class, 'show'])->name('kelas.show');
    Route::put('/kelas/{kelas}', [AdminKelasController::class, 'update'])->name('kelas.update');
    Route::delete('/kelas/{kelas}', [AdminKelasController::class, 'destroy'])->name('kelas.destroy');

    // Mata Pelajaran
    Route::get('/mapel', [AdminMapelController::class, 'index'])->name('mapel.index');
    Route::post('/mapel', [AdminMapelController::class, 'store'])->name('mapel.store');
    Route::put('/mapel/{mapel}', [AdminMapelController::class, 'update'])->name('mapel.update');
    Route::delete('/mapel/{mapel}', [AdminMapelController::class, 'destroy'])->name('mapel.destroy');

    // Data Guru & Siswa
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/guru', [AdminUserController::class, 'storeGuru'])->name('users.guru.store');
    Route::get('/users/guru/{guru}', [AdminUserController::class, 'showGuru'])->name('users.guru.show');
    Route::put('/users/guru/{guru}', [AdminUserController::class, 'updateGuru'])->name('users.guru.update');
    Route::put('/users/guru/{guru}/password', [AdminUserController::class, 'resetPasswordGuru'])->name('users.guru.reset-password');
    Route::delete('/users/guru/{guru}', [AdminUserController::class, 'destroyGuru'])->name('users.guru.destroy');

    Route::post('/users/siswa', [AdminUserController::class, 'storeSiswa'])->name('users.siswa.store');
    Route::get('/users/siswa/{siswa}', [AdminUserController::class, 'showSiswa'])->name('users.siswa.show');
    Route::put('/users/siswa/{siswa}', [AdminUserController::class, 'updateSiswa'])->name('users.siswa.update');
    Route::put('/users/siswa/{siswa}/password', [AdminUserController::class, 'resetPasswordSiswa'])->name('users.siswa.reset-password');
    Route::delete('/users/siswa/{siswa}', [AdminUserController::class, 'destroySiswa'])->name('users.siswa.destroy');

    // Jadwal Pelajaran
    Route::get('/jadwal', [AdminJadwalController::class, 'index'])->name('jadwal.index');
    Route::post('/jadwal', [AdminJadwalController::class, 'store'])->name('jadwal.store');
    Route::put('/jadwal/{jadwal}', [AdminJadwalController::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{jadwal}', [AdminJadwalController::class, 'destroy'])->name('jadwal.destroy');

    // Jadwal Ujian
    Route::get('/jadwal-ujian', [AdminJadwalUjianController::class, 'index'])->name('jadwal-ujian.index');
    Route::post('/jadwal-ujian', [AdminJadwalUjianController::class, 'store'])->name('jadwal-ujian.store');
    Route::put('/jadwal-ujian/{jadwalUjian}', [AdminJadwalUjianController::class, 'update'])->name('jadwal-ujian.update');
    Route::delete('/jadwal-ujian/{jadwalUjian}', [AdminJadwalUjianController::class, 'destroy'])->name('jadwal-ujian.destroy');

    // Pengumuman Sekolah
    Route::get('/pengumuman', [AdminPengumumanController::class, 'index'])->name('pengumuman.index');
    Route::get('/pengumuman/create', [AdminPengumumanController::class, 'create'])->name('pengumuman.create');
    Route::post('/pengumuman', [AdminPengumumanController::class, 'store'])->name('pengumuman.store');
    Route::get('/pengumuman/{pengumuman}/edit', [AdminPengumumanController::class, 'edit'])->name('pengumuman.edit');
    Route::put('/pengumuman/{pengumuman}', [AdminPengumumanController::class, 'update'])->name('pengumuman.update');
    Route::delete('/pengumuman/{pengumuman}', [AdminPengumumanController::class, 'destroy'])->name('pengumuman.destroy');
});

// Rute khusus Guru SMAN 1 Gunungsitoli
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');

    // Presensi Terintegrasi Guru
    Route::get('/presensi', [GuruPresensiController::class, 'index'])->name('presensi.index');
    Route::get('/presensi/buka/{jadwal}', [GuruPresensiController::class, 'create'])->name('presensi.create');
    Route::post('/presensi/buka/{jadwal}', [GuruPresensiController::class, 'store'])->name('presensi.store');
    Route::get('/presensi/sesi/{sesi}', [GuruPresensiController::class, 'show'])->name('presensi.show');
    Route::get('/presensi/sesi/{sesi}/qr-token', [GuruPresensiController::class, 'refreshQrToken'])->name('presensi.qr-token');
    Route::get('/presensi/sesi/{sesi}/status-live', [GuruPresensiController::class, 'statusLive'])->name('presensi.status-live');
    Route::post('/presensi/sesi/{sesi}/override', [GuruPresensiController::class, 'override'])->name('presensi.override');
    Route::post('/presensi/sesi/{sesi}/tutup', [GuruPresensiController::class, 'closeSession'])->name('presensi.tutup');

    // CRUD Materi Pelajaran Guru
    Route::resource('materi', GuruMateriController::class);
    Route::get('/materi/{materi}/download', [GuruMateriController::class, 'download'])->name('materi.download');

    // Manajemen Tugas & Evaluasi Guru
    Route::resource('tugas', GuruTugasController::class);
    Route::post('/tugas/pengumpulan/{pengumpulan}/grade', [GuruTugasController::class, 'quickGrade'])->name('tugas.grade');
    Route::post('/tugas/pengumpulan/{pengumpulan}/comment', [GuruTugasController::class, 'addComment'])->name('tugas.comment');
    Route::get('/tugas/{tuga}/download-zip', [GuruTugasController::class, 'downloadAllZip'])->name('tugas.download-zip');
    Route::get('/tugas/{tuga}/export-csv', [GuruTugasController::class, 'exportCsv'])->name('tugas.export-csv');

    // Pengumuman Sekolah untuk Guru
    Route::get('/pengumuman', [PortalPengumumanController::class, 'indexGuru'])->name('pengumuman.index');
    Route::get('/pengumuman/{pengumuman}', [PortalPengumumanController::class, 'showGuru'])->name('pengumuman.show');
});

// Rute khusus Siswa SMAN 1 Gunungsitoli
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');

    // Presensi Siswa
    Route::get('/presensi', [SiswaPresensiController::class, 'index'])->name('presensi.index');
    Route::get('/presensi/sesi/{sesi}/detail', [SiswaPresensiController::class, 'detail'])->name('presensi.detail');
    Route::post('/presensi/sesi/{sesi}/submit', [SiswaPresensiController::class, 'submit'])->name('presensi.submit');
    Route::get('/presensi/{jadwal}', [SiswaPresensiController::class, 'show'])->whereNumber('jadwal')->name('presensi.show');

    // Jadwal Ujian Siswa
    Route::get('/jadwal-ujian', [SiswaJadwalUjianController::class, 'index'])->name('jadwal-ujian.index');

    // Materi Pelajaran Siswa
    Route::get('/materi', [SiswaMateriController::class, 'index'])->name('materi.index');
    Route::get('/materi/{materi}/download', [SiswaMateriController::class, 'download'])->name('materi.download');

    // Tugas & Pengumpulan Berkas Siswa
    Route::get('/tugas', [SiswaTugasController::class, 'index'])->name('tugas.index');
    Route::get('/tugas/{tuga}', [SiswaTugasController::class, 'show'])->name('tugas.show');
    Route::post('/tugas/{tuga}/submit', [SiswaTugasController::class, 'submit'])->name('tugas.submit');
    Route::delete('/tugas/file/{file}', [SiswaTugasController::class, 'deleteFile'])->name('tugas.delete-file');
    Route::post('/tugas/pengumpulan/{pengumpulan}/comment', [SiswaTugasController::class, 'addComment'])->name('tugas.comment');
    Route::get('/tugas/{tuga}/lampiran-guru', [SiswaTugasController::class, 'downloadLampiranGuru'])->name('tugas.download-lampiran');
    Route::get('/tugas/file/{file}/download', [SiswaTugasController::class, 'downloadFile'])->name('tugas.download-file');

    // Pengumuman Sekolah untuk Siswa
    Route::get('/pengumuman', [PortalPengumumanController::class, 'indexSiswa'])->name('pengumuman.index');
    Route::get('/pengumuman/{pengumuman}', [PortalPengumumanController::class, 'showSiswa'])->name('pengumuman.show');
});

// Rute Profil Pengguna & Notifikasi Terintegrasi
Route::middleware('auth')->group(function () {
    Route::get('/notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
