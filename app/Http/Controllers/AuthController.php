<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\LogKeamanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function form(): View
    {
        return view('auth.login');
    }

    /**
     * Autentikasi ditangani LoginRequest: validasi, rate limit, dan log keamanan.
     */
    public function masuk(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route(auth()->user()->role->beranda()))
            ->with('sukses', 'Selamat datang kembali, '.auth()->user()->nama.'.');
    }

    public function keluar(Request $request): RedirectResponse
    {
        LogKeamanan::catat('logout', $request->user(), null, 'Keluar melalui menu.');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('sukses', 'Anda telah keluar dari aplikasi.');
    }
}
