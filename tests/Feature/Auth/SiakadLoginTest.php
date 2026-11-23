<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiakadLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_renders_with_school_name_and_reset_password_modal(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('SMA NEGERI 1 GUNUNGSITOLI');
        $response->assertSee('Masuk ke Akun Anda');
        $response->assertSee('Reset Password');
        $response->assertSee('SILAHKAN HUBUNGI ADMIN UNTUK MELAKUKAN RESET PASSWORD');
        $response->assertDontSee('Daftar Akun Baru');
    }

    public function test_root_url_renders_login_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('SMA NEGERI 1 GUNUNGSITOLI');
    }

    public function test_admin_can_login_with_email(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin@sman1gst.sch.id',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $this->assertAuthenticated();
        $this->assertSame('admin', auth()->user()->role);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_guru_can_login_with_nip(): void
    {
        $guru = User::where('role', 'guru')->first();

        $response = $this->post('/login', [
            'login' => $guru->nis_nip,
            'password' => 'password123',
            'role' => 'guru',
        ]);

        $this->assertAuthenticated();
        $this->assertSame($guru->id, auth()->id());
        $response->assertRedirect(route('guru.dashboard'));
    }

    public function test_guru_can_login_with_email(): void
    {
        $guru = User::where('role', 'guru')->first();

        $response = $this->post('/login', [
            'login' => $guru->email,
            'password' => 'password123',
            'role' => 'guru',
        ]);

        $this->assertAuthenticated();
        $this->assertSame($guru->id, auth()->id());
        $response->assertRedirect(route('guru.dashboard'));
    }

    public function test_siswa_can_login_with_nis(): void
    {
        $siswa = User::where('role', 'siswa')->first();

        $response = $this->post('/login', [
            'login' => $siswa->nis_nip,
            'password' => 'password123',
            'role' => 'siswa',
        ]);

        $this->assertAuthenticated();
        $this->assertSame($siswa->id, auth()->id());
        $response->assertRedirect(route('siswa.dashboard'));
    }

    public function test_siswa_can_login_with_email(): void
    {
        $siswa = User::where('role', 'siswa')->first();

        $response = $this->post('/login', [
            'login' => $siswa->email,
            'password' => 'password123',
            'role' => 'siswa',
        ]);

        $this->assertAuthenticated();
        $this->assertSame($siswa->id, auth()->id());
        $response->assertRedirect(route('siswa.dashboard'));
    }

    public function test_user_cannot_login_with_mismatched_role(): void
    {
        $siswa = User::where('role', 'siswa')->first();

        $response = $this->post('/login', [
            'login' => $siswa->email,
            'password' => 'password123',
            'role' => 'guru',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin@sman1gst.sch.id',
            'password' => 'wrong-password',
            'role' => 'admin',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }
}
