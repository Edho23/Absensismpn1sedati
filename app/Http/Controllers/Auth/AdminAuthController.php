<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        // pakai view kamu: resources/views/auth/login.blade.php
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $data = $r->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $data['username'])->first();

        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            return back()
                ->withErrors(['username' => 'Username atau password salah'])
                ->withInput();
        }

        Auth::guard('admin')->login($admin, $r->boolean('remember'));
        $admin->update(['last_login_at' => now()]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $r)
    {
        Auth::guard('admin')->logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login');
    }
}
