<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Kelas;

class KelasController extends Controller
{
    /**
     * TAMPILKAN DAFTAR KELAS (+ filter opsional)
     */
    public function index(Request $r)
    {
        $grade   = $r->query('grade');         // 7/8/9
        $paralel = $r->query('kelas_paralel'); // 1..11

        $q = Kelas::query();

        if (in_array((int)$grade, [7,8,9], true)) {
            $q->where('grade', (int)$grade);
        }
        if ($paralel !== null && $paralel !== '') {
            $q->where('kelas_paralel', (int)$paralel);
        }

        $kelas = $q->sorted()->paginate(10)->appends([
            'grade'         => $grade,
            'kelas_paralel' => $paralel,
        ]);

        $daftarGrade   = [7,8,9];
        $daftarParalel = range(1,11);

        return view('kelas.index', compact('kelas','daftarGrade','daftarParalel','grade','paralel'));
    }

    /**
     * SIMPAN KELAS BARU
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kelas'    => 'required|string|max:100',
            'wali_kelas'    => 'required|string|max:100',
            'kelas_paralel' => ['required','integer','min:1','max:11'],
            'grade'         => ['required','integer', Rule::in([7,8,9])],
        ], [
            'kelas_paralel.required' => 'Kelas paralel wajib diisi (1–11).',
            'grade.required'         => 'Grade wajib diisi (7/8/9).',
        ]);

        // Cegah duplikat kombinasi
        $exists = Kelas::where('grade', $data['grade'])
            ->where('kelas_paralel', $data['kelas_paralel'])
            ->where('nama_kelas', $data['nama_kelas'])
            ->exists();
        if ($exists) {
            return back()
                ->withErrors(['nama_kelas' => 'Kombinasi Grade, Paralel, dan Nama Kelas sudah ada.'])
                ->withInput();
        }

        Kelas::create($data);

        return redirect()->route('kelas.index')->with('ok', '✅ Kelas baru berhasil ditambahkan.');
    }

    /**
     * FORM EDIT KELAS
     */
    public function edit($id)
    {
        $kelas = Kelas::findOrFail($id);
        $daftarGrade   = [7,8,9];
        $daftarParalel = range(1,11);

        return view('kelas.edit', compact('kelas','daftarGrade','daftarParalel'));
    }

    /**
     * UPDATE DATA KELAS
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama_kelas'    => 'required|string|max:100',
            'wali_kelas'    => 'required|string|max:100',
            'kelas_paralel' => ['required','integer','min:1','max:11'],
            'grade'         => ['required','integer', Rule::in([7,8,9])],
        ]);

        $exists = Kelas::where('grade', $data['grade'])
            ->where('kelas_paralel', $data['kelas_paralel'])
            ->where('nama_kelas', $data['nama_kelas'])
            ->where('id', '!=', $id)
            ->exists();
        if ($exists) {
            return back()
                ->withErrors(['nama_kelas' => 'Kombinasi Grade, Paralel, dan Nama Kelas sudah ada.'])
                ->withInput();
        }

        $kelas = Kelas::findOrFail($id);
        $kelas->update($data);

        return redirect()->route('kelas.index')->with('ok', '✏️ Data kelas berhasil diperbarui.');
    }

    /**
     * HAPUS DATA KELAS
     */
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();

        return redirect()->route('kelas.index')->with('ok', '🗑️ Data kelas berhasil dihapus.');
    }
}
