<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email') && ! $this->has('login')) {
            $this->merge(['login' => $this->input('email')]);
        }
        if ($this->has('login') && ! $this->has('email') && filter_var($this->input('login'), FILTER_VALIDATE_EMAIL)) {
            $this->merge(['email' => $this->input('login')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role' => ['nullable', 'string', 'in:admin,guru,siswa'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = (string) ($this->input('login') ?? $this->input('email'));
        $password = (string) $this->input('password');
        $remember = $this->boolean('remember');
        $selectedRole = $this->input('role');

        $isEmail = (bool) filter_var($login, FILTER_VALIDATE_EMAIL);

        $authenticated = false;

        if ($isEmail) {
            $authenticated = Auth::attempt(['email' => $login, 'password' => $password], $remember);
        } else {
            // Coba autentikasi menggunakan NIS/NIP
            $authenticated = Auth::attempt(['nis_nip' => $login, 'password' => $password], $remember);

            // Fallback ke email jika bukan format standar
            if (! $authenticated) {
                $authenticated = Auth::attempt(['email' => $login, 'password' => $password], $remember);
            }
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        // Verifikasi kesesuaian peran jika tab peran dipilih spesifik
        if ($selectedRole && in_array($selectedRole, ['admin', 'guru', 'siswa'], true)) {
            $user = Auth::user();

            if ($user && $user->role !== $selectedRole) {
                Auth::logout();
                RateLimiter::hit($this->throttleKey());

                $roleLabels = [
                    'admin' => 'Tendik / Admin',
                    'guru' => 'Guru / Pendidik',
                    'siswa' => 'Siswa',
                ];

                throw ValidationException::withMessages([
                    'login' => 'Akun Anda terdaftar dengan peran ' . ($roleLabels[$user->role] ?? $user->role) . '. Silakan pilih tab portal yang sesuai untuk melanjutkan.',
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $identifier = (string) ($this->input('login') ?? $this->input('email') ?? '');

        return Str::transliterate(Str::lower($identifier).'|'.$this->ip());
    }
}
