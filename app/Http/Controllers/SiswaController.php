<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;

class SiswaController extends Controller
{
    /**
     * Tampilkan daftar siswa
     */
    public function index()
    {
        $siswa = Siswa::with('kelas')->orderBy('nama', 'asc')->paginate(10);
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('siswa.index', compact('siswa', 'kelas'));
    }

    /**
     * Simpan siswa baru
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'nis' => 'required|string|max:50|unique:siswa,nis',
            'id_kelas' => 'required|exists:kelas,id',
            'status_aktif' => 'required|boolean',
        ]);

        Siswa::create($data);

        return redirect()->route('siswa.index')->with('ok', 'Siswa baru berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit siswa
     */
    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    /**
     * Update data siswa
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'nis' => 'required|string|max:50|unique:siswa,nis,' . $id,
            'id_kelas' => 'required|exists:kelas,id',
            'status_aktif' => 'required|boolean',
        ]);

        $siswa = Siswa::findOrFail($id);
        $siswa->update($data);

        return redirect()->route('siswa.index')->with('ok', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Hapus siswa
     */
    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        $siswa->delete();

        return redirect()->route('siswa.index')->with('ok', 'Siswa berhasil dihapus.');
    }
}
