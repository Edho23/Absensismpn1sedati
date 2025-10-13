<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;

class SiswaController extends Controller
{
    /**
     * ======================
     * TAMPILKAN DATA SISWA
     * ======================
     */
    public function index()
    {
        $siswa = Siswa::with('kelas')->orderBy('nama', 'asc')->paginate(10);
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas', 'asc')->get();

        return view('siswa.index', compact('siswa', 'kelas'));
    }

    /**
     * ======================
     * TAMBAH DATA SISWA BARU
     * ======================
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nis'          => 'required|string|max:50|unique:siswa,nis',
            'nama'         => 'required|string|max:100',
            'id_kelas'     => 'required|exists:kelas,id',
            'status_aktif' => 'required|boolean',
        ], [
            'nis.unique'       => 'NIS sudah terdaftar!',
            'id_kelas.required'=> 'Kelas wajib dipilih.',
        ]);

        Siswa::create($data);

        return redirect()->route('siswa.index')->with('ok', '✅ Siswa baru berhasil ditambahkan.');
    }

    /**
     * ======================
     * FORM EDIT SISWA
     * ======================
     */
    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    /**
     * ======================
     * UPDATE DATA SISWA
     * ======================
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nis'          => 'required|string|max:50|unique:siswa,nis,' . $id,
            'nama'         => 'required|string|max:100',
            'id_kelas'     => 'required|exists:kelas,id',
            'status_aktif' => 'required|boolean',
        ]);

        $siswa = Siswa::findOrFail($id);
        $siswa->update($data);

        return redirect()->route('siswa.index')->with('ok', '✏️ Data siswa berhasil diperbarui.');
    }

    /**
     * ======================
     * HAPUS SISWA
     * ======================
     */
    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        $siswa->delete();

        return redirect()->route('siswa.index')->with('ok', '🗑️ Data siswa berhasil dihapus.');
    }
}
