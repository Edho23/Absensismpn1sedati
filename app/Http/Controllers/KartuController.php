<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{KartuRfid, Siswa};

class KartuController extends Controller
{
    /**
     * Tampilkan daftar kartu RFID + form tambah di atas tabel.
     */
    public function index()
    {
        // ambil semua kartu beserta relasi siswa dan kelas
        $kartu = KartuRfid::with('siswa.kelas')->orderBy('id', 'desc')->paginate(10);

        // ambil daftar siswa aktif untuk dropdown
        $siswa = Siswa::with('kelas')
            ->where('status_aktif', true)
            ->orderBy('nama')
            ->get();

        return view('kartu.index', compact('kartu', 'siswa'));
    }

    /**
     * Simpan kartu baru dari form.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'uid'          => 'required|string|unique:kartu_rfid,uid',
            'nis'          => 'required|exists:siswa,nis',
            'status_aktif' => 'required|boolean',
        ], [
            'uid.required' => 'ID Kartu harus diisi.',
            'uid.unique'   => 'ID Kartu sudah terdaftar.',
            'nis.required' => 'Pilih siswa pemilik kartu.',
            'nis.exists'   => 'Siswa dengan NIS tersebut tidak ditemukan.',
        ]);

        KartuRfid::create($data);

        return back()->with('ok', 'Kartu RFID berhasil ditambahkan.');
    }

    /**
     * Hapus kartu RFID.
     */
    public function destroy($id)
    {
        $kartu = KartuRfid::findOrFail($id);
        $kartu->delete();

        return back()->with('ok', 'Kartu RFID berhasil dihapus.');
    }
}
