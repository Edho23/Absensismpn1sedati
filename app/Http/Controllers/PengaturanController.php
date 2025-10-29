<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PengaturanController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth('admin')->user();

        return view('pengaturan.index', [
            'admin' => $admin,
        ]);
    }

    public function update(Request $request)
    {
        $admin = auth('admin')->user();

        $request->validate([
            'username'             => ['required','string','max:100', Rule::unique('admin','username')->ignore($admin->id)],
            'current_password'     => ['nullable','string'],
            'password'             => ['nullable','string','min:8','confirmed'], // needs password_confirmation
        ]);

        // Update username jika berubah
        if ($admin->username !== $request->username) {
            $admin->username = $request->username;
        }

        // Jika user mengisi field password baru → wajib isi current_password valid
        if ($request->filled('password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak cocok.'])->withInput();
            }
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return back()->with('ok', 'Profil berhasil diperbarui.');
    }
}
