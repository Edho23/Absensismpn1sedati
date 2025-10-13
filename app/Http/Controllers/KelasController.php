<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;

class KelasController extends Controller
{
    /**
     * ======================
     * TAMPILKAN DAFTAR KELAS
     * ======================
     */
    public function index()
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->paginate(10);
        return view('kelas.index', compact('kelas'));
    }

    /**
     * ======================
     * SIMPAN KELAS BARU
     * ======================
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kelas' => 'required|string|max:100|unique:kelas,nama_kelas',
            'wali_kelas' => 'required|string|max:100',
        ], [
            'nama_kelas.unique' => 'Nama kelas sudah ada!',
        ]);

        Kelas::create($data);

        return redirect()->route('kelas.index')->with('ok', '✅ Kelas baru berhasil ditambahkan.');
    }

    /**
     * ======================
     * FORM EDIT KELAS
     * ======================
     */
    public function edit($id)
    {
        $kelas = Kelas::findOrFail($id);
        return view('kelas.edit', compact('kelas'));
    }

    /**
     * ======================
     * UPDATE DATA KELAS
     * ======================
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama_kelas' => 'required|string|max:100|unique:kelas,nama_kelas,' . $id,
            'wali_kelas' => 'required|string|max:100',
        ]);

        $kelas = Kelas::findOrFail($id);
        $kelas->update($data);

        return redirect()->route('kelas.index')->with('ok', '✏️ Data kelas berhasil diperbarui.');
    }

    /**
     * ======================
     * HAPUS DATA KELAS
     * ======================
     */
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();

        return redirect()->route('kelas.index')->with('ok', '🗑️ Data kelas berhasil dihapus.');
    }
}
