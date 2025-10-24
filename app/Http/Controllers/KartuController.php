<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{KartuRfid, Siswa};

class KartuController extends Controller
{
    public function index()
    {
        // ambil kartu sebagai MODEL (bukan array), lengkap dengan relasi siswa->kelas
        $kartu = KartuRfid::with('siswa.kelas')
            ->orderByDesc('id')
            ->paginate(10);

        // dropdown siswa aktif
        $siswa = Siswa::with('kelas')
            ->where('status_aktif', true)
            ->orderBy('nama')
            ->get(['id','nis','nama','kelas_id']);

        return view('kartu.index', compact('kartu', 'siswa'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'uid' => 'required|string|unique:kartu_rfid,uid',
            'nis' => 'required|exists:siswa,nis',
        ], [
            'uid.required' => 'UID Kartu harus diisi.',
            'uid.unique'   => 'UID Kartu sudah terdaftar.',
            'nis.required' => 'NIS siswa wajib diisi.',
            'nis.exists'   => 'Siswa dengan NIS tersebut tidak ditemukan.',
        ]);

        // default aktif (karena form tidak mengirim status)
        $data['status_aktif'] = 1;

        KartuRfid::create($data);

        return back()->with('ok', 'Kartu RFID berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $kartu = KartuRfid::findOrFail($id);
        $kartu->delete();

        return back()->with('ok', 'Kartu RFID berhasil dihapus.');
    }
}
