<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman (pintu) login
    public function showLoginForm()
    {
        // Jika sudah login, jangan boleh buka halaman login lagi
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    // Proses pengecekan oleh satpam
    public function login(Request $request)
    {
        // 1. Pastikan pengguna mengisi form dengan benar
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Coba cocokkan dengan database (tanpa cek is_active dulu)
        if (!Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            return back()->withErrors([
                'username' => 'Username atau password salah.',
            ])->onlyInput('username');
        }

        // 3. Cek apakah akun aktif
        if (!Auth::user()->is_active) {
            Auth::logout();
            return back()->withErrors([
                'username' => 'Akun tidak aktif. Hubungi administrator.',
            ])->onlyInput('username');
        }

        // 4. Sesi aman dibuat
        $request->session()->regenerate();
        
        // 5. Arahkan ke dalam aplikasi
        return redirect()->route('dashboard');
    }

    // Proses keluar gedung (Logout)
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}