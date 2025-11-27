<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\HariLibur;

class PengaturanController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth('admin')->user();

        // ambil list hari libur untuk ditampilkan
        $libur = HariLibur::orderBy('berulang','desc')
                 ->orderBy('tanggal','asc')->get();

        return view('pengaturan.index', [
            'admin' => $admin,
            'libur' => $libur,
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

        if ($admin->username !== $request->username) {
            $admin->username = $request->username;
        }

        if ($request->filled('password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak cocok.'])->withInput();
            }
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return back()->with('ok', 'Profil berhasil diperbarui.');
    }

    // ====================== HARI LIBUR ======================

    public function storeHoliday(Request $request)
    {
        $request->validate([
            'tanggal'  => ['required','date'],
            'nama'     => ['required','string','max:100'],
            'berulang' => ['required','in:0,1'],
        ]);

        HariLibur::create([
            'tanggal'  => $request->tanggal,
            'nama'     => $request->nama,
            'berulang' => (bool)$request->berulang,
        ]);

        return back()->with('ok', 'Hari libur berhasil ditambahkan.');
    }

    public function destroyHoliday($id)
    {
        HariLibur::whereKey($id)->delete();
        return back()->with('ok', 'Hari libur dihapus.');
    }
}
