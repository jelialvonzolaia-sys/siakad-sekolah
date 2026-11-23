<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $pengumumanTerbaru = null;

        try {
            if (Schema::hasTable('pengumumans')) {
                $pengumumanTerbaru = Pengumuman::latest()->first();
            }
        } catch (Throwable) {
            $pengumumanTerbaru = null;
        }

        return view('auth.login', compact('pengumumanTerbaru'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $role = $request->user()->role;
        $targetRoute = match ($role) {
            'admin' => 'admin.dashboard',
            'guru' => 'guru.dashboard',
            'siswa' => 'siswa.dashboard',
            default => 'dashboard',
        };

        return redirect()->intended(route($targetRoute));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
