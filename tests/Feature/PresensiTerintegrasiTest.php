<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Presensi;
use App\Models\SesiPresensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiTerintegrasiTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;
    private User $siswa1;
    private User $siswa2;
    private Kelas $kelas;
    private MataPelajaran $mapel;
    private Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = User::factory()->create([
            'name' => 'Dra. Rosmina Zebua, M.Pd',
            'role' => 'guru',
            'nis_nip' => '197903122005012003',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'X MIPA 1',
            'tingkat' => 'X',
            'wali_kelas_id' => $this->guru->id,
        ]);

        $this->siswa1 = User::factory()->create([
            'name' => 'Alvino Putra Harefa',
            'role' => 'siswa',
            'nis_nip' => '260001',
            'kelas_id' => $this->kelas->id,
        ]);

        $this->siswa2 = User::factory()->create([
            'name' => 'Berkat Emanuel Lase',
            'role' => 'siswa',
            'nis_nip' => '260002',
            'kelas_id' => $this->kelas->id,
        ]);

        $this->mapel = MataPelajaran::create([
            'kode_mapel' => 'MAT-X',
            'nama_mapel' => 'Matematika Peminatan',
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

    public function test_topbar_layout_contains_realtime_date_and_clock(): void
    {
        $response = $this->actingAs($this->guru)->get(route('guru.presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('id="realtime-header-date"', false);
        $response->assertSee('id="realtime-header-clock"', false);
        $response->assertSee('tabular-nums', false);
        $response->assertSee('visibilitychange', false);
        $response->assertSee('SMA NEGERI 1 GUNUNGSITOLI');
    }

    public function test_guru_can_view_presensi_management_page(): void
    {
        $response = $this->actingAs($this->guru)->get(route('guru.presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Matematika Peminatan');
        $response->assertSee('X MIPA 1');
        $response->assertSee('Buka Presensi Pertemuan 1');
    }

    public function test_guru_can_open_sesi_presensi_mandiri(): void
    {
        $response = $this->actingAs($this->guru)->post(route('guru.presensi.store', $this->jadwal->id), [
            'pertemuan_ke' => 1,
            'materi' => 'Persamaan dan Pertidaksamaan Nilai Mutlak',
            'metode' => 'mandiri',
            'tanggal' => now()->format('Y-m-d'),
            'jam_mulai' => '07:30',
            'jam_selesai' => '09:00',
        ]);

        $sesi = SesiPresensi::first();
        $this->assertNotNull($sesi);
        $this->assertEquals(1, $sesi->pertemuan_ke);
        $this->assertEquals('mandiri', $sesi->metode);
        $this->assertEquals('dibuka', $sesi->status_sesi);

        $response->assertRedirect(route('guru.presensi.show', $sesi->id));

        // Verifikasi seluruh siswa di kelas mendapatkan status default 'Belum Absen'
        $presensis = Presensi::where('sesi_presensi_id', $sesi->id)->get();
        $this->assertCount(2, $presensis);
        $this->assertEquals('Belum Absen', $presensis->first()->status);
    }

    public function test_guru_can_open_sesi_presensi_qr_and_refresh_token(): void
    {
        $response = $this->actingAs($this->guru)->post(route('guru.presensi.store', $this->jadwal->id), [
            'pertemuan_ke' => 2,
            'materi' => 'Pertidaksamaan Rasional dan Irasional',
            'metode' => 'qr',
            'tanggal' => now()->format('Y-m-d'),
            'jam_mulai' => '07:30',
            'jam_selesai' => '09:00',
        ]);

        $sesi = SesiPresensi::where('pertemuan_ke', 2)->first();
        $this->assertNotNull($sesi);
        $this->assertEquals('qr', $sesi->metode);
        $this->assertNotNull($sesi->qr_token);
        $this->assertNotNull($sesi->qr_expires_at);

        // Uji refresh endpoint QR Token
        $ajaxResponse = $this->actingAs($this->guru)->getJson(route('guru.presensi.qr-token', $sesi->id));
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertJsonStructure(['success', 'token', 'expires_at', 'seconds_remaining']);
        $this->assertTrue($ajaxResponse->json('success'));
        $this->assertNotEquals('', $ajaxResponse->json('token'));
    }

    public function test_guru_can_override_student_attendance(): void
    {
        $sesi = SesiPresensi::create([
            'jadwal_id' => $this->jadwal->id,
            'pertemuan_ke' => 1,
            'materi' => 'Materi Uji',
            'metode' => 'mandiri',
            'tanggal' => now()->format('Y-m-d'),
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
            'status_sesi' => 'dibuka',
        ]);

        Presensi::create([
            'jadwal_id' => $this->jadwal->id,
            'sesi_presensi_id' => $sesi->id,
            'siswa_id' => $this->siswa1->id,
            'tanggal' => $sesi->tanggal,
            'status' => 'Alpha',
        ]);

        Presensi::create([
            'jadwal_id' => $this->jadwal->id,
            'sesi_presensi_id' => $sesi->id,
            'siswa_id' => $this->siswa2->id,
            'tanggal' => $sesi->tanggal,
            'status' => 'Alpha',
        ]);

        // Guru melakukan override: siswa1 Hadir, siswa2 Sakit
        $overrideResponse = $this->actingAs($this->guru)->post(route('guru.presensi.override', $sesi->id), [
            'presensi' => [
                $this->siswa1->id => 'Hadir',
                $this->siswa2->id => 'Sakit',
            ],
        ]);

        $overrideResponse->assertRedirect(route('guru.presensi.show', $sesi->id));

        $p1 = Presensi::where('sesi_presensi_id', $sesi->id)->where('siswa_id', $this->siswa1->id)->first();
        $p2 = Presensi::where('sesi_presensi_id', $sesi->id)->where('siswa_id', $this->siswa2->id)->first();

        $this->assertEquals('Hadir', $p1->status);
        $this->assertEquals('Sakit', $p2->status);
    }

    public function test_siswa_cannot_submit_presensi_without_gps_coordinates(): void
    {
        $sesi = SesiPresensi::create([
            'jadwal_id' => $this->jadwal->id,
            'pertemuan_ke' => 1,
            'materi' => 'Uji GPS',
            'metode' => 'mandiri',
            'tanggal' => now()->format('Y-m-d'),
            'jam_mulai' => '00:00:00',
            'jam_selesai' => '23:59:59',
            'status_sesi' => 'dibuka',
        ]);

        // Siswa mencoba submit tanpa latitude dan longitude
        $response = $this->actingAs($this->siswa1)->postJson(route('siswa.presensi.submit', $sesi->id), [
            'keterangan' => 'Mencoba absen tanpa GPS',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['latitude', 'longitude', 'alamat_lokasi']);
    }

    public function test_siswa_can_submit_presensi_mandiri_with_valid_gps(): void
    {
        $sesi = SesiPresensi::create([
            'jadwal_id' => $this->jadwal->id,
            'pertemuan_ke' => 1,
            'materi' => 'Uji GPS Berhasil',
            'metode' => 'mandiri',
            'tanggal' => now()->format('Y-m-d'),
            'jam_mulai' => '00:00:00',
            'jam_selesai' => '23:59:59',
            'status_sesi' => 'dibuka',
        ]);

        $response = $this->actingAs($this->siswa1)->postJson(route('siswa.presensi.submit', $sesi->id), [
            'latitude' => 1.282912,
            'longitude' => 97.618645,
            'alamat_lokasi' => 'Jl. Pendidikan No. 3, Ilir, Kec. Gunungsitoli, Kota Gunungsitoli, Sumatera Utara 22815',
            'keterangan' => 'Presensi mandiri berhasil',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'status' => 'Hadir']);

        $presensi = Presensi::where('sesi_presensi_id', $sesi->id)
            ->where('siswa_id', $this->siswa1->id)
            ->first();

        $this->assertNotNull($presensi);
        $this->assertEquals('Hadir', $presensi->status);
        $this->assertEquals('1.282912', $presensi->latitude);
        $this->assertEquals('97.618645', $presensi->longitude);
        $this->assertStringContainsString('Gunungsitoli', $presensi->alamat_lokasi);
    }

    public function test_siswa_qr_presensi_validates_token(): void
    {
        $sesi = SesiPresensi::create([
            'jadwal_id' => $this->jadwal->id,
            'pertemuan_ke' => 3,
            'materi' => 'Uji QR Sesi',
            'metode' => 'qr',
            'qr_token' => 'SMAN1GST-TEST1234',
            'qr_expires_at' => now()->addSeconds(30),
            'tanggal' => now()->format('Y-m-d'),
            'jam_mulai' => '00:00:00',
            'jam_selesai' => '23:59:59',
            'status_sesi' => 'dibuka',
        ]);

        // 1. Submit dengan token salah -> gagal 422
        $failResponse = $this->actingAs($this->siswa1)->postJson(route('siswa.presensi.submit', $sesi->id), [
            'latitude' => 1.282912,
            'longitude' => 97.618645,
            'alamat_lokasi' => 'SMA Negeri 1 Gunungsitoli',
            'qr_token' => 'TOKEN-SALAH-1234',
        ]);

        $failResponse->assertStatus(422);
        $failResponse->assertJson(['success' => false]);

        // 2. Submit dengan token benar -> berhasil 200
        $successResponse = $this->actingAs($this->siswa1)->postJson(route('siswa.presensi.submit', $sesi->id), [
            'latitude' => 1.282912,
            'longitude' => 97.618645,
            'alamat_lokasi' => 'SMA Negeri 1 Gunungsitoli',
            'qr_token' => 'SMAN1GST-TEST1234',
        ]);

        $successResponse->assertStatus(200);
        $successResponse->assertJson(['success' => true, 'status' => 'Hadir']);
    }

    public function test_siswa_presensi_tahap_1_displays_detail_siswa_and_mapel_list_without_university_terms(): void
    {
        $response = $this->actingAs($this->siswa1)->get(route('siswa.presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Detail Siswa');
        $response->assertSee('Daftar Mata Pelajaran Kelas');
        $response->assertSee($this->siswa1->name);
        $response->assertSee($this->mapel->kode_mapel);
        $response->assertSee($this->mapel->nama_mapel);
        $response->assertSee($this->guru->name);
        $response->assertSee(route('siswa.presensi.show', $this->jadwal->id));
        $response->assertDontSee('Status KRS');
        $response->assertDontSee('Tipe Kelas');
        $response->assertDontSee('SKS Maksimum');
    }

    public function test_siswa_presensi_tahap_2_displays_sessions_and_button_states_correctly(): void
    {
        $activeSesi = SesiPresensi::create([
            'jadwal_id' => $this->jadwal->id,
            'pertemuan_ke' => 1,
            'materi' => 'Ikatan Ion dan Kovalen',
            'metode' => 'mandiri',
            'tanggal' => now()->format('Y-m-d'),
            'jam_mulai' => '00:00:00',
            'jam_selesai' => '23:59:59',
            'status_sesi' => 'dibuka',
        ]);

        $closedSesi = SesiPresensi::create([
            'jadwal_id' => $this->jadwal->id,
            'pertemuan_ke' => 2,
            'materi' => 'Sesi Ditutup Guru',
            'metode' => 'qr',
            'tanggal' => now()->format('Y-m-d'),
            'jam_mulai' => '00:00:00',
            'jam_selesai' => '23:59:59',
            'status_sesi' => 'ditutup',
        ]);

        $pastSesi = SesiPresensi::create([
            'jadwal_id' => $this->jadwal->id,
            'pertemuan_ke' => 3,
            'materi' => 'Sesi Kemarin Lewat Waktu',
            'metode' => 'mandiri',
            'tanggal' => now()->subDay()->format('Y-m-d'),
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
            'status_sesi' => 'dibuka',
        ]);

        $hadirSesi = SesiPresensi::create([
            'jadwal_id' => $this->jadwal->id,
            'pertemuan_ke' => 4,
            'materi' => 'Sesi Sudah Hadir',
            'metode' => 'mandiri',
            'tanggal' => now()->format('Y-m-d'),
            'jam_mulai' => '00:00:00',
            'jam_selesai' => '23:59:59',
            'status_sesi' => 'dibuka',
        ]);

        Presensi::create([
            'sesi_presensi_id' => $hadirSesi->id,
            'jadwal_id' => $this->jadwal->id,
            'siswa_id' => $this->siswa1->id,
            'tanggal' => now()->format('Y-m-d'),
            'status' => 'Hadir',
            'metode_absen' => 'mandiri',
        ]);

        $response = $this->actingAs($this->siswa1)->get(route('siswa.presensi.show', $this->jadwal->id));

        $response->assertStatus(200);
        $response->assertSee('Pertemuan Kelas');
        $response->assertSee('Ikatan Ion dan Kovalen');
        $response->assertSee("bukaModalPresensi({$activeSesi->id})", false);
        $response->assertDontSee("bukaModalPresensi({$closedSesi->id})", false);
        $response->assertDontSee("bukaModalPresensi({$pastSesi->id})", false);
        $response->assertDontSee("bukaModalPresensi({$hadirSesi->id})", false);

        // Pastikan jadwal milik kelas lain ditolak (403)
        $otherKelas = Kelas::create([
            'nama_kelas' => 'X MIPA 2',
            'tingkat' => 'X',
        ]);
        $otherJadwal = Jadwal::create([
            'kelas_id' => $otherKelas->id,
            'mapel_id' => $this->mapel->id,
            'guru_id' => $this->guru->id,
            'hari' => 'Selasa',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:30',
        ]);

        $this->actingAs($this->siswa1)
            ->get(route('siswa.presensi.show', $otherJadwal->id))
            ->assertStatus(403);
    }

    public function test_siswa_jadwal_ujian_displays_filtered_exams_and_empty_state(): void
    {
        $emptyResponse = $this->actingAs($this->siswa1)->get(route('siswa.jadwal-ujian.index'));
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertSee('Jadwal Ujian Sekolah');
        $emptyResponse->assertSee('Belum ada jadwal ujian yang diterbitkan untuk kelas ini.');

        $otherKelas = Kelas::create([
            'nama_kelas' => 'XI IPS 1',
            'tingkat' => 'XI',
        ]);

        JadwalUjian::create([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $this->mapel->id,
            'jenis_ujian' => 'PAS Ganjil 2026/2027',
            'tanggal' => '2026-10-07',
            'waktu_mulai' => '08:00:00',
            'ruangan' => 'Ruang 01',
        ]);

        JadwalUjian::create([
            'kelas_id' => $otherKelas->id,
            'mapel_id' => $this->mapel->id,
            'jenis_ujian' => 'PAS Ganjil 2026/2027',
            'tanggal' => '2026-10-08',
            'waktu_mulai' => '10:00:00',
            'ruangan' => 'Ruang Khusus Lain',
        ]);

        $filledResponse = $this->actingAs($this->siswa1)->get(route('siswa.jadwal-ujian.index'));
        $filledResponse->assertStatus(200);
        $filledResponse->assertSee('Jadwal PAS Ganjil 2026/2027');
        $filledResponse->assertSee('Ruang 01');
        $filledResponse->assertSee('08:00 - 09:30');
        $filledResponse->assertDontSee('Ruang Khusus Lain');
    }
}
