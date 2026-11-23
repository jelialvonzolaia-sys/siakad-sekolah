<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_is_redirected_to_admin_dashboard_after_login(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->post('/login', [
            'login' => $admin->email,
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_guru_is_redirected_to_guru_dashboard_after_login(): void
    {
        $guru = User::where('role', 'guru')->first();

        $response = $this->post('/login', [
            'login' => $guru->email,
            'password' => 'password123',
            'role' => 'guru',
        ]);

        $response->assertRedirect(route('guru.dashboard'));
    }

    public function test_siswa_is_redirected_to_siswa_dashboard_after_login(): void
    {
        $siswa = User::where('role', 'siswa')->first();

        $response = $this->post('/login', [
            'login' => $siswa->email,
            'password' => 'password123',
            'role' => 'siswa',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
    }

    public function test_dashboard_route_redirects_according_to_role(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));

        $guru = User::where('role', 'guru')->first();
        $this->actingAs($guru)->get('/dashboard')->assertRedirect(route('guru.dashboard'));

        $siswa = User::where('role', 'siswa')->first();
        $this->actingAs($siswa)->get('/dashboard')->assertRedirect(route('siswa.dashboard'));
    }

    public function test_role_middleware_guards_dashboards(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $guru = User::where('role', 'guru')->first();

        // Siswa accessing admin dashboard -> 403
        $this->actingAs($siswa)->get('/admin/dashboard')->assertStatus(403);

        // Siswa accessing guru dashboard -> 403
        $this->actingAs($siswa)->get('/guru/dashboard')->assertStatus(403);

        // Guru accessing admin dashboard -> 403
        $this->actingAs($guru)->get('/admin/dashboard')->assertStatus(403);
    }

    public function test_siswa_dashboard_renders_with_real_database_data(): void
    {
        $siswa = User::where('role', 'siswa')->first();

        $response = $this->actingAs($siswa)->get('/siswa/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Beranda Siswa');
        $response->assertSee('Selamat Datang, '.$siswa->name);
        $response->assertSee($siswa->kelas->nama_kelas);
        $response->assertSee('Persentase Kehadiran');
        $response->assertSee('Jadwal Hari Ini');
        $response->assertSee('Materi Tersedia');
        $response->assertSee('SMA NEGERI 1 GUNUNGSITOLI');
    }

    public function test_admin_dashboard_renders_with_real_database_data(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Panel Utama Administrator SIAKAD');
        $response->assertSee('Total Siswa Aktif');
        $response->assertSee('Total Tenaga Guru');
        $response->assertSee('Rombongan Belajar');
        $response->assertSee('Mata Pelajaran');
    }

    public function test_guru_dashboard_renders_with_real_database_data(): void
    {
        $guru = User::where('role', 'guru')->first();

        $response = $this->actingAs($guru)->get('/guru/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Guru');
        $response->assertSee('Selamat Datang, '.$guru->name);
        $response->assertSee('Rombel Diajar');
        $response->assertSee('Jadwal Mengajar Anda');
    }
}
