<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTuManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin TU SMANSA',
            'email' => 'admin.tu@sman1gst.sch.id',
            'role' => 'admin',
            'nis_nip' => '198001012005011001',
        ]);
    }

    private function createGuru(string $nip = '198501012010011001', string $name = 'Guru SMANSA'): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'guru',
            'nis_nip' => $nip,
        ]);
    }

    private function createSiswa(string $nis = '260001', ?int $kelasId = null, string $name = 'Siswa SMANSA'): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'siswa',
            'nis_nip' => $nis,
            'kelas_id' => $kelasId,
        ]);
    }

    public function test_non_admin_cannot_access_admin_tu_crud_routes(): void
    {
        $guru = $this->createGuru();
        $siswa = $this->createSiswa();

        $this->actingAs($guru)->get(route('admin.kelas.index'))->assertForbidden();
        $this->actingAs($siswa)->get(route('admin.mapel.index'))->assertForbidden();
        $this->actingAs($siswa)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($guru)->get(route('admin.jadwal.index'))->assertForbidden();
        $this->actingAs($siswa)->get(route('admin.jadwal-ujian.index'))->assertForbidden();
        $this->actingAs($guru)->get(route('admin.pengumuman.index'))->assertForbidden();
    }

    public function test_admin_can_crud_kelas_and_view_rekap_anggota(): void
    {
        $admin = $this->createAdmin();
        $guru = $this->createGuru();

        $this->actingAs($admin)
            ->post(route('admin.kelas.store'), [
                'nama_kelas' => 'X MIPA 1',
                'tingkat' => '10',
                'wali_kelas_id' => $guru->id,
            ])
            ->assertRedirect(route('admin.kelas.index'))
            ->assertSessionHas('success');

        $kelas = Kelas::where('nama_kelas', 'X MIPA 1')->firstOrFail();
        $this->assertEquals($guru->id, $kelas->wali_kelas_id);

        $siswa = $this->createSiswa('260111', $kelas->id, 'Budi Harefa');

        $this->actingAs($admin)
            ->get(route('admin.kelas.show', $kelas->id))
            ->assertOk()
            ->assertSee('X MIPA 1')
            ->assertSee('Budi Harefa')
            ->assertSee('260111')
            ->assertSee('Lihat Profil Siswa');

        $this->actingAs($admin)
            ->put(route('admin.kelas.update', $kelas->id), [
                'nama_kelas' => 'X MIPA 1 Unggulan',
                'tingkat' => '10',
                'wali_kelas_id' => $guru->id,
            ])
            ->assertRedirect(route('admin.kelas.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kelas', ['id' => $kelas->id, 'nama_kelas' => 'X MIPA 1 Unggulan']);

        $this->actingAs($admin)
            ->delete(route('admin.kelas.destroy', $kelas->id))
            ->assertRedirect(route('admin.kelas.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kelas', ['id' => $kelas->id]);
    }

    public function test_admin_can_crud_mata_pelajaran_with_guru_and_kkm(): void
    {
        $admin = $this->createAdmin();
        $guru = $this->createGuru();

        $this->actingAs($admin)
            ->post(route('admin.mapel.store'), [
                'kode_mapel' => 'kim-01',
                'nama_mapel' => 'Kimia Dasar',
                'kkm' => 78,
                'guru_id' => $guru->id,
            ])
            ->assertRedirect(route('admin.mapel.index'))
            ->assertSessionHas('success');

        $mapel = MataPelajaran::where('kode_mapel', 'KIM-01')->firstOrFail();
        $this->assertEquals(78, $mapel->kkm);
        $this->assertEquals($guru->id, $mapel->guru_id);

        $this->actingAs($admin)
            ->put(route('admin.mapel.update', $mapel->id), [
                'kode_mapel' => 'KIM-02',
                'nama_mapel' => 'Kimia Peminatan',
                'kkm' => 80,
                'guru_id' => $guru->id,
            ])
            ->assertRedirect(route('admin.mapel.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mata_pelajarans', [
            'id' => $mapel->id,
            'kode_mapel' => 'KIM-02',
            'nama_mapel' => 'Kimia Peminatan',
            'kkm' => 80,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.mapel.destroy', $mapel->id))
            ->assertRedirect(route('admin.mapel.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('mata_pelajarans', ['id' => $mapel->id]);
    }

    public function test_admin_can_manage_guru_and_reset_password_without_old_password(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.users.guru.store'), [
                'name' => 'Drs. Yosua Laia',
                'nis_nip' => '197505052000011005',
                'email' => 'yosua.baru@sman1gst.sch.id',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->assertRedirect(route('admin.users.index', ['tab' => 'guru']))
            ->assertSessionHas('success');

        $guru = User::where('nis_nip', '197505052000011005')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.users.guru.show', $guru->id))
            ->assertOk()
            ->assertSee('Drs. Yosua Laia')
            ->assertSee('Reset / Ganti Password Guru');

        $this->actingAs($admin)
            ->put(route('admin.users.guru.reset-password', $guru->id), [
                'password' => 'passwordbaru99',
                'password_confirmation' => 'passwordbaru99',
            ])
            ->assertRedirect(route('admin.users.guru.show', $guru->id))
            ->assertSessionHas('success');

        $guru->refresh();
        $this->assertTrue(Hash::check('passwordbaru99', $guru->password));

        $this->actingAs($admin)
            ->delete(route('admin.users.guru.destroy', $guru->id))
            ->assertRedirect(route('admin.users.index', ['tab' => 'guru']))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $guru->id]);
    }

    public function test_admin_can_manage_siswa_grouped_by_kelas_and_move_kelas(): void
    {
        $admin = $this->createAdmin();
        $kelasA = Kelas::create(['nama_kelas' => 'X MIPA 1', 'tingkat' => '10']);
        $kelasB = Kelas::create(['nama_kelas' => 'X IPS 1', 'tingkat' => '10']);

        $this->actingAs($admin)
            ->post(route('admin.users.siswa.store'), [
                'name' => 'Alvaro Zai',
                'nis_nip' => '260999',
                'email' => 'alvaro.zai@sman1gst.sch.id',
                'kelas_id' => $kelasA->id,
                'password' => 'siswa1234',
                'password_confirmation' => 'siswa1234',
            ])
            ->assertSessionHas('success');

        $siswa = User::where('nis_nip', '260999')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['tab' => 'siswa', 'kelas_id' => $kelasA->id]))
            ->assertOk()
            ->assertSee('Daftar Siswa Kelas X MIPA 1')
            ->assertSee('Alvaro Zai');

        $this->actingAs($admin)
            ->put(route('admin.users.siswa.update', $siswa->id), [
                'name' => 'Alvaro Zai Updated',
                'nis_nip' => '260999',
                'email' => 'alvaro.zai@sman1gst.sch.id',
                'kelas_id' => $kelasB->id,
            ])
            ->assertRedirect(route('admin.users.siswa.show', $siswa->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $siswa->id, 'kelas_id' => $kelasB->id]);
    }

    public function test_admin_jadwal_prevents_kelas_and_guru_conflicts(): void
    {
        $admin = $this->createAdmin();
        $guru1 = $this->createGuru('19800001', 'Guru Satu');
        $guru2 = $this->createGuru('19800002', 'Guru Dua');
        $kelas1 = Kelas::create(['nama_kelas' => 'X MIPA 1', 'tingkat' => '10']);
        $kelas2 = Kelas::create(['nama_kelas' => 'X IPS 1', 'tingkat' => '10']);
        $mapel1 = MataPelajaran::create(['kode_mapel' => 'MAT', 'nama_mapel' => 'Matematika', 'kkm' => 75]);
        $mapel2 = MataPelajaran::create(['kode_mapel' => 'FIS', 'nama_mapel' => 'Fisika', 'kkm' => 75]);

        // Tambah jadwal pertama: Senin 08:00 - 09:30 di X MIPA 1 oleh Guru Satu
        $this->actingAs($admin)
            ->post(route('admin.jadwal.store'), [
                'kelas_id' => $kelas1->id,
                'mapel_id' => $mapel1->id,
                'guru_id' => $guru1->id,
                'hari' => 'Senin',
                'jam_mulai' => '08:00',
                'jam_selesai' => '09:30',
            ])
            ->assertRedirect(route('admin.jadwal.index'))
            ->assertSessionHas('success');

        // Bentrok kelas: kelas sama, jam bertabrakan (09:00 - 10:00)
        $this->actingAs($admin)
            ->post(route('admin.jadwal.store'), [
                'kelas_id' => $kelas1->id,
                'mapel_id' => $mapel2->id,
                'guru_id' => $guru2->id,
                'hari' => 'Senin',
                'jam_mulai' => '09:00',
                'jam_selesai' => '10:00',
            ])
            ->assertSessionHas('error');

        // Bentrok guru: guru sama di kelas berbeda pada jam bertabrakan (08:30 - 09:45)
        $this->actingAs($admin)
            ->post(route('admin.jadwal.store'), [
                'kelas_id' => $kelas2->id,
                'mapel_id' => $mapel1->id,
                'guru_id' => $guru1->id,
                'hari' => 'Senin',
                'jam_mulai' => '08:30',
                'jam_selesai' => '09:45',
            ])
            ->assertSessionHas('error');

        // Jadwal valid: tidak bentrok (09:30 - 11:00)
        $this->actingAs($admin)
            ->post(route('admin.jadwal.store'), [
                'kelas_id' => $kelas1->id,
                'mapel_id' => $mapel2->id,
                'guru_id' => $guru2->id,
                'hari' => 'Senin',
                'jam_mulai' => '09:30',
                'jam_selesai' => '11:00',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(2, Jadwal::count());
    }

    public function test_admin_can_crud_jadwal_ujian(): void
    {
        $admin = $this->createAdmin();
        $kelas = Kelas::create(['nama_kelas' => 'XI MIPA 1', 'tingkat' => '11']);
        $mapel = MataPelajaran::create(['kode_mapel' => 'BIO', 'nama_mapel' => 'Biologi', 'kkm' => 75]);

        $this->actingAs($admin)
            ->post(route('admin.jadwal-ujian.store'), [
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'jenis_ujian' => 'PAS Ganjil 2026/2027',
                'tanggal' => '2026-12-05',
                'waktu_mulai' => '08:00',
                'ruangan' => 'Ruang 01 - Gedung Utama',
            ])
            ->assertRedirect(route('admin.jadwal-ujian.index'))
            ->assertSessionHas('success');

        $ujian = JadwalUjian::firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.jadwal-ujian.update', $ujian->id), [
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'jenis_ujian' => 'Ujian Sekolah',
                'tanggal' => '2026-12-06',
                'waktu_mulai' => '09:00',
                'ruangan' => 'Lab Komputer SMANSA',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_ujians', [
            'id' => $ujian->id,
            'ruangan' => 'Lab Komputer SMANSA',
        ]);
    }

    public function test_pengumuman_rich_text_publish_draft_and_role_integration(): void
    {
        $admin = $this->createAdmin();
        $guru = $this->createGuru();
        $kelas = Kelas::create(['nama_kelas' => 'X MIPA 1', 'tingkat' => '10']);
        $siswa = $this->createSiswa('260001', $kelas->id);

        // 1. Pengumuman Published untuk Semua
        $this->actingAs($admin)->post(route('admin.pengumuman.store'), [
            'judul' => 'Libur Semester Ganjil 2026',
            'isi' => '<p>Pengumuman <strong>resmi</strong> libur semester untuk semua warga sekolah.</p>',
            'target_role' => 'semua',
            'is_published' => '1',
        ])->assertRedirect(route('admin.pengumuman.index'))->assertSessionHas('success');

        // 2. Pengumuman Published Khusus Guru
        $this->actingAs($admin)->post(route('admin.pengumuman.store'), [
            'judul' => 'Rapat Dewan Guru',
            'isi' => '<p>Khusus bapak ibu guru harap hadir di aula.</p>',
            'target_role' => 'guru',
            'is_published' => '1',
        ])->assertSessionHas('success');

        // 3. Pengumuman Draft (tidak boleh tampil di Guru/Siswa)
        $this->actingAs($admin)->post(route('admin.pengumuman.store'), [
            'judul' => 'Draf Rahasia',
            'isi' => '<p>Belum boleh diterbitkan.</p>',
            'target_role' => 'semua',
            'is_published' => '0',
        ])->assertSessionHas('success');

        $pSemua = Pengumuman::where('judul', 'Libur Semester Ganjil 2026')->firstOrFail();
        $pGuru = Pengumuman::where('judul', 'Rapat Dewan Guru')->firstOrFail();
        $pDraft = Pengumuman::where('judul', 'Draf Rahasia')->firstOrFail();

        // Konsumsi flash session Admin sebelum berpindah ke sesi Guru
        $this->actingAs($admin)->get(route('admin.pengumuman.index'))->assertOk();
        session()->flush();

        // Guru melihat pSemua dan pGuru, tapi tidak melihat pDraft
        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertSee('Libur Semester Ganjil 2026')
            ->assertSee('Rapat Dewan Guru')
            ->assertDontSee('Draf Rahasia')
            ->assertSee('Lihat Semua Pengumuman');

        $this->actingAs($guru)
            ->get(route('guru.pengumuman.show', $pSemua->id))
            ->assertOk()
            ->assertSee('Pengumuman <strong>resmi</strong> libur semester', false);

        // Siswa melihat pSemua, tapi TIDAK melihat pGuru maupun pDraft
        $this->actingAs($siswa)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSee('Libur Semester Ganjil 2026')
            ->assertDontSee('Rapat Dewan Guru')
            ->assertDontSee('Draf Rahasia')
            ->assertSee('Lihat Semua Pengumuman');

        // Siswa forbidden mengakses pengumuman khusus guru atau draft
        $this->actingAs($siswa)->get(route('siswa.pengumuman.show', $pGuru->id))->assertForbidden();
        $this->actingAs($siswa)->get(route('siswa.pengumuman.show', $pDraft->id))->assertForbidden();
    }
}
