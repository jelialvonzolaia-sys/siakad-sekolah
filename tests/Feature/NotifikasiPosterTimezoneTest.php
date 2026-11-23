<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Notifikasi;
use App\Models\Pengumuman;
use App\Models\PengumpulanTugas;
use App\Models\Presensi;
use App\Models\SesiPresensi;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotifikasiPosterTimezoneTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $guru;
    private User $siswa;
    private Kelas $kelas;
    private MataPelajaran $mapel;
    private Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin TU SMANSA',
            'role' => 'admin',
            'nis_nip' => 'ADM-001',
        ]);

        $this->guru = User::factory()->create([
            'name' => 'Guru Biologi SMANSA',
            'role' => 'guru',
            'nis_nip' => '198001012005011001',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'XI MIPA 1',
            'tingkat' => 'XI',
            'wali_kelas_id' => $this->guru->id,
        ]);

        $this->siswa = User::factory()->create([
            'name' => 'Siswa Test Gunungsitoli',
            'role' => 'siswa',
            'nis_nip' => '260123',
            'kelas_id' => $this->kelas->id,
        ]);

        $this->mapel = MataPelajaran::create([
            'kode_mapel' => 'BIO-XI',
            'nama_mapel' => 'Biologi',
            'guru_id' => $this->guru->id,
        ]);

        $this->jadwal = Jadwal::create([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $this->mapel->id,
            'guru_id' => $this->guru->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
        ]);
    }

    public function test_aplikasi_menggunakan_timezone_asia_jakarta_dan_locale_id(): void
    {
        $this->assertEquals('Asia/Jakarta', config('app.timezone'));
        $this->assertEquals('id', config('app.locale'));
        $this->assertEquals('Asia/Jakarta', date_default_timezone_get());
    }

    public function test_presensi_belum_absen_status_tombol_dan_otomatis_alpha_saat_tutup(): void
    {
        // Guru membuka sesi presensi baru
        $this->actingAs($this->guru)->post(route('guru.presensi.store', $this->jadwal->id), [
            'pertemuan_ke' => 1,
            'materi' => 'Struktur Sel',
            'metode' => 'mandiri',
            'tanggal' => now('Asia/Jakarta')->format('Y-m-d'),
            'jam_mulai' => '00:00',
            'jam_selesai' => '23:59',
        ])->assertRedirect();

        $sesi = SesiPresensi::firstOrFail();
        $presensi = Presensi::where('sesi_presensi_id', $sesi->id)
            ->where('siswa_id', $this->siswa->id)
            ->firstOrFail();

        // Default status wajib 'Belum Absen' & siswa mendapat notifikasi
        $this->assertEquals('Belum Absen', $presensi->status);
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->siswa->id,
            'kategori' => 'presensi',
            'is_read' => false,
        ]);

        // Halaman pertemuan siswa menampilkan 'Belum Absen' & tombol aktif bg-[#1F3B2E]
        $showRes = $this->actingAs($this->siswa)->get(route('siswa.presensi.show', $this->jadwal->id));
        $showRes->assertStatus(200);
        $showRes->assertSee('Belum Absen');
        $showRes->assertSee('bg-[#1F3B2E] hover:bg-[#3C5A3A]', false);
        $showRes->assertSee("bukaModalPresensi({$sesi->id})", false);

        // Guru menutup sesi -> status 'Belum Absen' otomatis berubah menjadi 'Alpha'
        $this->actingAs($this->guru)->post(route('guru.presensi.tutup', $sesi->id))
            ->assertRedirect(route('guru.presensi.show', $sesi->id));

        $presensi->refresh();
        $this->assertEquals('Alpha', $presensi->status);

        // Tombol pada halaman siswa berubah menjadi disabled abu-abu
        $closedRes = $this->actingAs($this->siswa)->get(route('siswa.presensi.show', $this->jadwal->id));
        $closedRes->assertStatus(200);
        $closedRes->assertSee('Alpha');
        $closedRes->assertSee('bg-slate-200 text-slate-400 cursor-not-allowed pointer-events-none', false);
        $closedRes->assertDontSee("bukaModalPresensi({$sesi->id})", false);
    }

    public function test_admin_pengumuman_poster_upload_replace_delete_dan_notifikasi(): void
    {
        Storage::fake('public');

        $poster1 = UploadedFile::fake()->image('poster-awal.jpg', 800, 600);

        $storeRes = $this->actingAs($this->admin)->post(route('admin.pengumuman.store'), [
            'judul' => 'Libur Semester Ganjil',
            'isi' => '<p>Pengumuman resmi libur semester.</p>',
            'gambar' => $poster1,
            'target_role' => 'semua',
            'is_published' => '1',
        ]);

        $storeRes->assertRedirect(route('admin.pengumuman.index'));

        $pengumuman = Pengumuman::firstOrFail();
        $this->assertNotNull($pengumuman->gambar_path);
        Storage::disk('public')->assertExists($pengumuman->gambar_path);

        // Notifikasi terkirim ke Guru dan Siswa
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->siswa->id,
            'kategori' => 'pengumuman',
        ]);
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->guru->id,
            'kategori' => 'pengumuman',
        ]);

        // Siswa dapat melihat poster pengumuman di portal
        $portalShow = $this->actingAs($this->siswa)->get(route('siswa.pengumuman.show', $pengumuman->id));
        $portalShow->assertStatus(200);
        $portalShow->assertSee('storage/' . $pengumuman->gambar_path, false);

        // Update dengan poster baru -> poster lama terhapus
        $oldPath = $pengumuman->gambar_path;
        $poster2 = UploadedFile::fake()->image('poster-baru.png', 800, 600);

        $this->actingAs($this->admin)->put(route('admin.pengumuman.update', $pengumuman->id), [
            'judul' => 'Libur Semester Ganjil Revisi',
            'isi' => '<p>Revisi tanggal libur.</p>',
            'gambar' => $poster2,
            'target_role' => 'semua',
            'is_published' => '1',
        ])->assertRedirect(route('admin.pengumuman.index'));

        $pengumuman->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($pengumuman->gambar_path);

        // Destroy -> file poster ikut terhapus dari storage
        $currentPath = $pengumuman->gambar_path;
        $this->actingAs($this->admin)->delete(route('admin.pengumuman.destroy', $pengumuman->id))
            ->assertRedirect(route('admin.pengumuman.index'));

        Storage::disk('public')->assertMissing($currentPath);
    }

    public function test_notifikasi_tugas_ujian_topbar_baca_dan_tandai_semua(): void
    {
        // 1. Admin terbitkan jadwal ujian -> notif siswa kategori 'ujian'
        $this->actingAs($this->admin)->post(route('admin.jadwal-ujian.store'), [
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $this->mapel->id,
            'jenis_ujian' => 'PAS Ganjil 2026/2027',
            'tanggal' => '2026-12-10',
            'waktu_mulai' => '08:00',
            'ruangan' => 'Ruang 02',
        ])->assertRedirect(route('admin.jadwal-ujian.index'));

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->siswa->id,
            'kategori' => 'ujian',
        ]);

        // 2. Guru buat tugas baru -> notif siswa kategori 'tugas'
        $this->actingAs($this->guru)->post(route('guru.tugas.store'), [
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $this->mapel->id,
            'judul' => 'Laporan Praktikum Enzim',
            'deskripsi_html' => '<p>Unggah laporan dalam format PDF.</p>',
            'waktu_buka' => now('Asia/Jakarta')->subHour()->format('Y-m-d H:i'),
            'batas_waktu' => now('Asia/Jakarta')->addDays(3)->format('Y-m-d H:i'),
            'tipe_file_diizinkan' => ['pdf'],
            'max_ukuran_mb' => 5,
            'max_jumlah_file' => 2,
        ])->assertRedirect(route('guru.tugas.index'));

        $tugas = Tugas::firstOrFail();
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->siswa->id,
            'kategori' => 'tugas',
            'judul' => 'Tugas Baru: Laporan Praktikum Enzim',
        ]);

        // 3. Siswa kirim berkas & komentar -> notif guru kategori 'tugas' + komentar format WIB
        Storage::fake('public');
        $pdfFile = UploadedFile::fake()->create('laporan.pdf', 200, 'application/pdf');
        $this->actingAs($this->siswa)->post(route('siswa.tugas.submit', $tugas->id), [
            'files' => [$pdfFile],
        ])->assertRedirect(route('siswa.tugas.show', $tugas->id));

        $pengumpulan = PengumpulanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $this->siswa->id)
            ->firstOrFail();

        $commentRes = $this->actingAs($this->siswa)->postJson(route('siswa.tugas.comment', $pengumpulan->id), [
            'komentar' => 'Mohon diperiksa Bu Guru.',
        ]);
        $commentRes->assertStatus(200);
        $this->assertStringEndsWith(' WIB', $commentRes->json('komentar.time'));

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->guru->id,
            'kategori' => 'tugas',
            'is_read' => false,
        ]);

        // 4. Klik notifikasi siswa -> is_read = true & redirect ke tautan
        $notifSiswa = Notifikasi::where('user_id', $this->siswa->id)->firstOrFail();
        $this->actingAs($this->siswa)
            ->get(route('notifikasi.baca', $notifSiswa->id))
            ->assertRedirect($notifSiswa->tautan);

        $notifSiswa->refresh();
        $this->assertTrue($notifSiswa->is_read);

        // 5. Tandai semua sudah dibaca via AJAX
        $markAllRes = $this->actingAs($this->siswa)->postJson(route('notifikasi.baca-semua'));
        $markAllRes->assertStatus(200);
        $markAllRes->assertJson(['success' => true, 'unread_count' => 0]);

        $this->assertEquals(
            0,
            Notifikasi::where('user_id', $this->siswa->id)->where('is_read', false)->count()
        );
    }
}
