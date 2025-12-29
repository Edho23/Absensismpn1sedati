<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Services\SiswaPromotion;

class SiswaController extends Controller
{
    /**
     * LIST + FILTER SISWA
     */
    public function index(Request $r)
    {
        $filters = [
            'q'             => trim((string)$r->query('q')),
            'nama_kelas'    => $r->query('nama_kelas') ?? $r->query('grade'),
            'kelas_paralel' => $r->query('kelas_paralel'),
            'gender'        => $r->query('gender'),
            'angkatan'      => $r->query('angkatan'),
            'status'        => $r->query('status'),
        ];

        $q = Siswa::with('kelas');

        // Cari NIS/Nama
        if ($filters['q'] !== '') {
            $kw = '%'.str_replace(['%','_'], ['\\%','\\_'], $filters['q']).'%';
            if (DB::connection()->getDriverName() === 'pgsql') {
                $q->whereRaw('(nis ILIKE ? OR nama ILIKE ?)', [$kw, $kw]);
            } else {
                $q->where(function($qq) use ($kw){
                    $qq->where('nis', 'like', $kw)
                       ->orWhere('nama', 'like', $kw);
                });
            }
        }

        // Filter nama_kelas (VII/VIII/IX) via relasi kelas
        if ($filters['nama_kelas']) {
            $q->whereHas('kelas', fn($qq) =>
                $qq->where('nama_kelas', $filters['nama_kelas'])
            );
        }

        // Filter kelas_paralel via relasi kelas
        if ($filters['kelas_paralel']) {
            $q->whereHas('kelas', fn($qq) =>
                $qq->where('kelas_paralel', $filters['kelas_paralel'])
            );
        }

        // Filter gender
        if (in_array($filters['gender'], ['L','P'], true)) {
            $q->where('gender', $filters['gender']);
        }

        // Filter angkatan
        if ($filters['angkatan']) {
            $q->where('angkatan', (int)$filters['angkatan']);
        }

        // Filter status (A/N)
        if (in_array($filters['status'], ['A','N'], true)) {
            $q->where('status', $filters['status']);
        }

        // === Urutkan agar data baru ada di BAWAH ===
        $q->orderBy('created_at', 'asc')->orderBy('id', 'asc');

        $siswa = $q->paginate(10)->appends($filters);

        // Semua record kelas (untuk row edit)
        $kelas = Kelas::orderBy('kelas_paralel')
            ->orderBy('nama_kelas', 'asc')
            ->get();

        // Grades unik (VII/VIII/IX) — diambil dari kolom nama_kelas
        $grades = Kelas::select('nama_kelas')
            ->distinct()
            ->orderBy('nama_kelas', 'asc')
            ->pluck('nama_kelas');

        // Daftar paralel unik (untuk form tambah/edit)
        $daftarParalel = Kelas::select('kelas_paralel')
            ->distinct()
            ->orderBy('kelas_paralel', 'asc')
            ->pluck('kelas_paralel');

        // Map: nama_kelas => [paralel...]
        $paralelMap = Kelas::select('nama_kelas','kelas_paralel')
            ->orderBy('nama_kelas', 'asc')
            ->orderBy('kelas_paralel', 'asc')
            ->get()
            ->groupBy('nama_kelas')
            ->map(function($rows){
                return $rows->pluck('kelas_paralel')->unique()->values();
            })->toArray();

        return view('siswa.index', [
            'siswa'        => $siswa,
            'kelas'        => $kelas,          // untuk row edit
            'filters'      => $filters,
            'grades'       => $grades,         // VII/VIII/IX
            'daftarParalel'=> $daftarParalel,  // paralel unik (1/2/3 dst)
            'paralelMap'   => $paralelMap,     // untuk filter dinamis grade -> paralel
        ]);
    }

    /** TAMBAH DATA SISWA BARU */
    public function store(Request $request)
    {
        $request->merge([
            'nis' => strtoupper(trim((string)$request->input('nis'))),
        ]);

        // sekarang kita terima nama_kelas + kelas_paralel,
        // lalu cari kelas_id yang sesuai di tabel kelas.
        $data = $request->validate([
            'nis'           => 'required|string|max:50|unique:siswa,nis',
            'nama'          => 'required|string|max:100',
            'nama_kelas'    => ['required', 'string', 'max:20'],
            'kelas_paralel' => ['required', 'string', 'max:20'],
            'status'        => ['nullable', Rule::in(['A','N'])],
            'gender'        => ['nullable', Rule::in(['L','P'])],
            'angkatan'      => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ], [
            'nis.unique'           => 'NIS sudah terdaftar!',
            'nama_kelas.required'  => 'Kelas (VII/VIII/IX) wajib dipilih.',
            'kelas_paralel.required'=> 'Kelas paralel wajib dipilih.',
        ]);

        // cari kelas_id berdasarkan kombinasi nama_kelas + kelas_paralel
        $kelas = Kelas::where('nama_kelas', $data['nama_kelas'])
            ->where('kelas_paralel', $data['kelas_paralel'])
            ->first();

        if (!$kelas) {
            return back()
                ->withErrors(['kelas_id' => 'Kombinasi kelas dan paralel tidak ditemukan di master kelas.'])
                ->withInput();
        }

        $payload = [
            'nis'       => $data['nis'],
            'nama'      => $data['nama'],
            'kelas_id'  => $kelas->id,
            'status'    => $data['status'] ?? 'A',
            'gender'    => $data['gender'] ?? null,
            'angkatan'  => $data['angkatan'] ?? null,
        ];

        Siswa::create($payload);

        return redirect()->route('siswa.index')
            ->with('ok', '✅ Siswa baru berhasil ditambahkan.');
    }

    /** (Opsional) FORM EDIT SISWA */
    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        $kelas = Kelas::orderBy('kelas_paralel')
            ->orderBy('nama_kelas')
            ->get();

        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    /** UPDATE DATA SISWA */
    public function update(Request $request, $id)
    {
        $request->merge([
            'nis' => strtoupper(trim((string)$request->input('nis'))),
        ]);

        // UPDATE tetap pakai kelas_id seperti sebelumnya (row edit tidak diubah)
        $data = $request->validate([
            'nis'       => 'required|string|max:50|unique:siswa,nis,' . $id,
            'nama'      => 'required|string|max:100',
            'kelas_id'  => 'required|exists:kelas,id',
            'status'    => ['nullable', Rule::in(['A','N'])],
            'gender'    => ['nullable', Rule::in(['L','P'])],
            'angkatan'  => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $siswa = Siswa::findOrFail($id);

        if (!array_key_exists('status', $data) || $data['status'] === null || $data['status'] === '') {
            $data['status'] = $siswa->status ?? 'A';
        }

        $siswa->update($data);

        return redirect()->route('siswa.index')
            ->with('ok', '✏️ Data siswa berhasil diperbarui.');
    }

    /** HAPUS SISWA */
    public function destroy($id)
    {
        Siswa::findOrFail($id)->delete();
        return redirect()->route('siswa.index')
            ->with('ok', '🗑️ Data siswa berhasil dihapus.');
    }

    /** BULK DELETE */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return redirect()->route('siswa.index')
                ->with('error', 'Tidak ada siswa yang dipilih untuk dihapus.');
        }

        $ids = array_map('intval', $ids);
        $deleted = Siswa::whereIn('id', $ids)->delete();

        return redirect()->route('siswa.index')
            ->with('ok', "🗑️ {$deleted} data siswa berhasil dihapus.");
    }
    
    /** SEARCH Typeahead NIS/Nama (default hanya status A) */
    public function search(Request $r)
    {
        $term          = trim((string)$r->query('term', ''));
        $kelasParalel  = $r->query('kelas_paralel');
        $namaKelas     = $r->query('nama_kelas') ?? $r->query('grade');
        $gender        = $r->query('gender');
        $angkatan      = $r->query('angkatan');
        $status        = $r->query('status', 'A');

        if ($term === '') return response()->json([]);

        $driver = DB::connection()->getDriverName();
        $kw = '%'.str_replace(['%','_'], ['\\%','\\_'], $term).'%';

        $q = Siswa::with('kelas');

        if (in_array($status, ['A','N'], true)) {
            $q->where('status', $status);
        }
        if ($namaKelas) {
            $q->whereHas('kelas', fn($qq) =>
                $qq->where('nama_kelas', $namaKelas)
            );
        }
        if ($kelasParalel) {
            $q->whereHas('kelas', fn($qq) =>
                $qq->where('kelas_paralel', $kelasParalel)
            );
        }
        if (in_array($gender, ['L','P'], true)) {
            $q->where('gender', $gender);
        }
        if ($angkatan) {
            $q->where('angkatan', (int)$angkatan);
        }

        if ($driver === 'pgsql') {
            $q->whereRaw('(nis ILIKE ? OR nama ILIKE ?)', [$kw, $kw]);
        } else {
            $q->where(function ($qq) use ($kw) {
                $qq->where('nis', 'like', $kw)
                   ->orWhere('nama', 'like', $kw);
            });
        }

        $rows = $q->orderBy('nama', 'asc')
            ->limit(10)
            ->get(['id','nis','nama','kelas_id']);

        return response()->json($rows->map(fn($s) => [
            'nis'   => $s->nis,
            'nama'  => $s->nama,
            'kelas' => $s->kelas->nama_kelas ?? '-',
            'label' => "{$s->nis} — {$s->nama} (" . ($s->kelas->nama_kelas ?? '-') . ")",
        ]));
    }

    /** PROMOSI NAIK KELAS / LULUS */
    public function promote(SiswaPromotion $svc)
    {
        $res = $svc->promoteAll();
        return back()->with(
            'ok',
            "✅ Promosi selesai — Naik: {$res['moved']}, Lulus: {$res['graduated']}, Dilewati: {$res['skipped']}."
        );
    }
}
