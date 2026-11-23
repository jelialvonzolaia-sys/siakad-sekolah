<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Pengumuman;
use App\Models\Presensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SiakadStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_pengumuman_model_has_pengumumans_table(): void
    {
        $pengumuman = new Pengumuman();
        $this->assertSame('pengumumans', $pengumuman->getTable());
    }

    public function test_database_seeder_has_correct_counts_and_structure(): void
    {
        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame('admin@sman1gst.sch.id', User::where('role', 'admin')->first()->email);

        $this->assertSame(6, User::where('role', 'guru')->count());
        $guruEmails = [
            'rosmina.zebua@sman1gst.sch.id',
            'yosua.laia@sman1gst.sch.id',
            'febriani.hulu@sman1gst.sch.id',
            'berkat.daeli@sman1gst.sch.id',
            'kristina.lase@sman1gst.sch.id',
            'arofah.harefa@sman1gst.sch.id',
        ];
        foreach ($guruEmails as $email) {
            $this->assertDatabaseHas('users', ['email' => $email, 'role' => 'guru']);
        }

        $this->assertSame(6, Kelas::count());
        $expectedKelas = ['X MIPA 1', 'X IPS 1', 'XI MIPA 1', 'XI IPS 1', 'XII MIPA 1', 'XII IPS 1'];
        foreach ($expectedKelas as $namaKelas) {
            $this->assertDatabaseHas('kelas', ['nama_kelas' => $namaKelas]);
        }

        $this->assertSame(6, MataPelajaran::count());
        $expectedMapel = ['Kimia', 'Matematika', 'Fisika', 'Informatika', 'Bahasa Inggris', 'Sejarah Indonesia'];
        foreach ($expectedMapel as $mapel) {
            $this->assertDatabaseHas('mata_pelajarans', ['nama_mapel' => $mapel]);
        }

        $this->assertSame(100, User::where('role', 'siswa')->count());
        $this->assertDatabaseHas('users', ['email' => 'siswa001@sman1gst.sch.id', 'nis_nip' => '260001']);
        $this->assertDatabaseHas('users', ['email' => 'siswa100@sman1gst.sch.id', 'nis_nip' => '260100']);

        $admin = User::where('role', 'admin')->first();
        $this->assertTrue(Hash::check('password123', $admin->password));
    }

    public function test_eloquent_relationships(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $this->assertInstanceOf(Kelas::class, $siswa->kelas);

        $guru = User::where('role', 'guru')->first();
        $this->assertTrue($guru->kelasWali()->exists() || $guru->jadwals()->exists());

        $kelas = Kelas::first();
        $this->assertInstanceOf(User::class, $kelas->waliKelas);
        $this->assertTrue($kelas->siswas()->count() > 0);
        $this->assertTrue($kelas->jadwals()->count() > 0);
        $this->assertTrue($kelas->jadwalUjians()->count() > 0);

        $mapel = MataPelajaran::first();
        $this->assertTrue($mapel->jadwals()->count() > 0);
        $this->assertTrue($mapel->jadwalUjians()->count() > 0);

        $jadwal = Jadwal::first();
        $this->assertInstanceOf(Kelas::class, $jadwal->kelas);
        $this->assertInstanceOf(MataPelajaran::class, $jadwal->mataPelajaran);
        $this->assertInstanceOf(User::class, $jadwal->guru);

        $jadwalUjian = JadwalUjian::first();
        $this->assertInstanceOf(Kelas::class, $jadwalUjian->kelas);
        $this->assertInstanceOf(MataPelajaran::class, $jadwalUjian->mataPelajaran);

        $materi = Materi::first();
        $this->assertInstanceOf(MataPelajaran::class, $materi->mataPelajaran);
        $this->assertInstanceOf(User::class, $materi->guru);
        $this->assertInstanceOf(Kelas::class, $materi->kelas);

        $pengumuman = Pengumuman::first();
        $this->assertInstanceOf(User::class, $pengumuman->penulis);

        $presensi = Presensi::first();
        $this->assertInstanceOf(Jadwal::class, $presensi->jadwal);
        $this->assertInstanceOf(User::class, $presensi->siswa);
    }

    public function test_role_middleware(): void
    {
        Route::get('/test-admin-only', function () {
            return response()->json(['message' => 'admin_ok']);
        })->middleware('role:admin');

        Route::get('/test-guru-siswa', function () {
            return response()->json(['message' => 'guru_siswa_ok']);
        })->middleware('role:guru,siswa');

        // Unauthenticated access
        $response = $this->getJson('/test-admin-only');
        $response->assertStatus(403);

        // Siswa accessing admin route
        $siswa = User::where('role', 'siswa')->first();
        $response = $this->actingAs($siswa)->getJson('/test-admin-only');
        $response->assertStatus(403);

        // Admin accessing admin route
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->getJson('/test-admin-only');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'admin_ok']);

        // Guru accessing guru,siswa route
        $guru = User::where('role', 'guru')->first();
        $response = $this->actingAs($guru)->getJson('/test-guru-siswa');
        $response->assertStatus(200);

        // Siswa accessing guru,siswa route
        $response = $this->actingAs($siswa)->getJson('/test-guru-siswa');
        $response->assertStatus(200);
    }
}
