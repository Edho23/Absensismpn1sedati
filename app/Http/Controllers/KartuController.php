<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KartuController extends Controller
{
    public function index()
    {
        // nanti data kartu bisa diambil dari model, untuk sekarang dummy dulu
        $kartu = [
            ['id' => 1, 'uid' => 'RFID-00123', 'nama_siswa' => 'Yogi Aditya', 'status' => 'Aktif'],
            ['id' => 2, 'uid' => 'RFID-00456', 'nama_siswa' => 'Dimas Arif', 'status' => 'Nonaktif'],
        ];

        return view('kartu.index', compact('kartu'));
    }

    public function store(Request $request)
    {
        // nanti diisi backend insert kartu RFID
    }

    public function destroy($id)
    {
        // nanti diisi backend delete kartu RFID
    }
}
