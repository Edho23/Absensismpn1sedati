<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
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
            'nis'      => 'required|string|max:50|unique:siswa,nis',
            'nama'     => 'required|string|max:100',
            'kelas_id' => 'required|exists:kelas,id',
        ], [
            'nis.unique'        => 'NIS sudah terdaftar!',
            'kelas_id.required' => 'Kelas wajib dipilih.',
        ]);

        $data['status_aktif'] = 1; // otomatis aktif
        Siswa::create($data);

        return redirect()->route('siswa.index')->with('ok', '✅ Siswa baru berhasil ditambahkan dan otomatis aktif.');
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
            'nis'      => 'required|string|max:50|unique:siswa,nis,' . $id,
            'nama'     => 'required|string|max:100',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $siswa = Siswa::findOrFail($id);
        $data['status_aktif'] = 1; // tetap aktif
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

    /**
     * ======================
     * SEARCH untuk Typeahead NIS/Nama
     * GET /siswa/search?term=...
     * ======================
     */
    public function search(Request $r)
    {
        $term = trim((string) $r->query('term', ''));
        if ($term === '') {
            return response()->json([]);
        }

        $driver = DB::connection()->getDriverName();
        $kw = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';

        if ($driver === 'pgsql') {
            // Supabase/Postgres: pakai ILIKE agar case-insensitive
            $rows = Siswa::with('kelas')
                ->whereRaw('(nis ILIKE ? OR nama ILIKE ?)', [$kw, $kw])
                ->orderBy('nama')
                ->limit(10)
                ->get(['id','nis','nama','kelas_id']);
        } else {
            // MySQL/MariaDB
            $rows = Siswa::with('kelas')
                ->where(function ($q) use ($kw) {
                    $q->where('nis', 'like', $kw)
                      ->orWhere('nama', 'like', $kw);
                })
                ->orderBy('nama')
                ->limit(10)
                ->get(['id','nis','nama','kelas_id']);
        }

        $payload = $rows->map(fn($s) => [
            'nis'   => $s->nis,
            'nama'  => $s->nama,
            'kelas' => $s->kelas->nama_kelas ?? '-',
            'label' => "{$s->nis} — {$s->nama} (" . ($s->kelas->nama_kelas ?? '-') . ")",
        ]);

        return response()->json($payload);
    }
}
