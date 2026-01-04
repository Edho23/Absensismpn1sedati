<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Services\SiswaPromotion;

class SiswaController extends Controller
{
    /**
     * LIST + FILTER SISWA
     * Query params:
     * - q             : cari nama/nis
     * - nama_kelas    : VII/VIII/IX (alias grade)
     * - kelas_paralel : angka/label paralel
     * - gender        : L/P
     * - angkatan      : tahun
     * - status        : A/N
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

        // Filter nama_kelas via relasi kelas
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

        // Filter status
        if (in_array($filters['status'], ['A','N'], true)) {
            $q->where('status', $filters['status']);
        }

        // Urutkan agar data baru ada di bawah
        $q->orderBy('created_at', 'asc')->orderBy('id', 'asc');

        $siswa = $q->paginate(10)->appends($filters);

        // Semua record kelas (untuk mapping)
        $kelas = Kelas::orderBy('nama_kelas', 'asc')
            ->orderBy('kelas_paralel', 'asc')
            ->get();

        // Grades unik lalu urutkan manual: VII, VIII, IX
        $gradesRaw = Kelas::select('nama_kelas')
            ->distinct()
            ->pluck('nama_kelas')
            ->values();

        $order = ['VII', 'VIII', 'IX'];
        $grades = $gradesRaw->sortBy(function($g) use ($order){
            $idx = array_search($g, $order, true);
            return $idx === false ? 999 : $idx;
        })->values();

        // Daftar paralel unik
        $daftarParalel = Kelas::select('kelas_paralel')
            ->distinct()
            ->orderBy('kelas_paralel', 'asc')
            ->pluck('kelas_paralel');

        // Map: nama_kelas => [paralel...]
        $paralelMap = $kelas
            ->groupBy('nama_kelas')
            ->map(fn($rows) => $rows->pluck('kelas_paralel')->unique()->values())
            ->toArray();

        // Map: nama_kelas => [paralel => kelas_id]
        $kelasIdMap = $kelas
            ->groupBy('nama_kelas')
            ->map(function($rows){
                $m = [];
                foreach ($rows as $row) {
                    $m[(string)$row->kelas_paralel] = $row->id;
                }
                return $m;
            })
            ->toArray();

        return view('siswa.index', [
            'siswa'         => $siswa,
            'kelas'         => $kelas,
            'filters'       => $filters,
            'grades'        => $grades,
            'daftarParalel' => $daftarParalel,
            'paralelMap'    => $paralelMap,
            'kelasIdMap'    => $kelasIdMap, // dipakai JS untuk set hidden kelas_id
        ]);
    }

    /** TAMBAH DATA SISWA BARU */
    public function store(Request $request)
    {
        $request->merge([
            'nis' => strtoupper(trim((string)$request->input('nis'))),
        ]);

        $data = $request->validate([
            'nis'       => 'required|string|max:50|unique:siswa,nis',
            'nama'      => 'required|string|max:100',
            'kelas_id'  => 'required|exists:kelas,id',
            'status'    => ['nullable', Rule::in(['A','N'])],
            'gender'    => ['nullable', Rule::in(['L','P'])],
            'angkatan'  => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ], [
            'nis.unique'        => 'NIS sudah terdaftar!',
            'kelas_id.required' => 'Kelas wajib dipilih.',
        ]);

        $data['status'] = $data['status'] ?? 'A';
        Siswa::create($data);

        return redirect()->route('siswa.index')
            ->with('ok', '✅ Siswa baru berhasil ditambahkan.');
    }

    /** FORM EDIT SISWA (opsional kalau pakai halaman terpisah) */
    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        $kelas = Kelas::orderBy('nama_kelas', 'asc')
            ->orderBy('kelas_paralel', 'asc')
            ->get();

        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    /** UPDATE DATA SISWA */
    public function update(Request $request, $id)
    {
        $request->merge([
            'nis' => strtoupper(trim((string)$request->input('nis'))),
        ]);

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
        if (!is_array($ids)) $ids = [];

        // ambil hanya angka
        $ids = array_values(array_filter($ids, fn($v) => is_numeric($v)));
        $ids = array_map('intval', $ids);

        if (empty($ids)) {
            return redirect()->route('siswa.index')
                ->with('error', 'Tidak ada siswa yang dipilih untuk dihapus.');
        }

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
            $q->whereHas('kelas', fn($qq) => $qq->where('nama_kelas', $namaKelas));
        }
        if ($kelasParalel) {
            $q->whereHas('kelas', fn($qq) => $qq->where('kelas_paralel', $kelasParalel));
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

    /**
     * =========================
     * IMPORT CSV SISWA (BARU)
     * =========================
     * Kolom CSV yang disarankan:
     * nis,nama,angkatan,gender,status,nama_kelas,kelas_paralel
     *
     * kelas_id akan dicari otomatis dari tabel kelas:
     * (nama_kelas + kelas_paralel) => kelas.id
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'file'   => 'required|file|mimes:csv,txt|max:5120',
            'mode'   => ['nullable', Rule::in(['insert_only','upsert'])],
        ]);

        $mode = $request->input('mode', 'upsert'); // default update jika NIS sudah ada
        $file = $request->file('file');

        // build lookup kelas: "VII|7" => kelas_id
        $kelasLookup = Kelas::select('id','nama_kelas','kelas_paralel')->get()
            ->mapWithKeys(function($k){
                $key = strtoupper(trim((string)$k->nama_kelas)).'|'.trim((string)$k->kelas_paralel);
                return [$key => $k->id];
            })
            ->toArray();

        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'File CSV tidak bisa dibaca.');
        }

        // baca header
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'CSV kosong / header tidak ditemukan.');
        }

        $header = array_map(fn($h) => strtolower(trim((string)$h)), $header);

        // minimal kolom yang wajib ada
        $requiredCols = ['nis','nama','nama_kelas','kelas_paralel'];
        foreach ($requiredCols as $col) {
            if (!in_array($col, $header, true)) {
                fclose($handle);
                return back()->with('error', "Kolom wajib '{$col}' tidak ada di CSV.");
            }
        }

        $idx = array_flip($header);

        $rows = [];
        $line = 1; // header = 1
        $errors = [];

        while (($csv = fgetcsv($handle)) !== false) {
            $line++;

            // skip baris kosong
            if (count(array_filter($csv, fn($v)=> trim((string)$v) !== '')) === 0) continue;

            $nis   = strtoupper(trim((string)($csv[$idx['nis']] ?? '')));
            $nama  = trim((string)($csv[$idx['nama']] ?? ''));
            $angk  = trim((string)($csv[$idx['angkatan']] ?? ''));
            $gend  = strtoupper(trim((string)($csv[$idx['gender']] ?? '')));
            $stat  = strtoupper(trim((string)($csv[$idx['status']] ?? 'A')));
            $grade = strtoupper(trim((string)($csv[$idx['nama_kelas']] ?? '')));
            $par   = trim((string)($csv[$idx['kelas_paralel']] ?? ''));

            if ($nis === '' || $nama === '' || $grade === '' || $par === '') {
                $errors[] = "Baris {$line}: nis/nama/nama_kelas/kelas_paralel wajib diisi.";
                continue;
            }

            if ($gend !== '' && !in_array($gend, ['L','P'], true)) {
                $errors[] = "Baris {$line}: gender harus L/P.";
                continue;
            }

            if ($stat !== '' && !in_array($stat, ['A','N'], true)) {
                $errors[] = "Baris {$line}: status harus A/N.";
                continue;
            }

            $kelasKey = $grade.'|'.$par;
            $kelasId = $kelasLookup[$kelasKey] ?? null;
            if (!$kelasId) {
                $errors[] = "Baris {$line}: kelas tidak ditemukan (nama_kelas={$grade}, paralel={$par}). Pastikan sudah ada di tabel kelas.";
                continue;
            }

            $angkatan = null;
            if ($angk !== '') {
                if (!ctype_digit($angk)) {
                    $errors[] = "Baris {$line}: angkatan harus angka (contoh 2024).";
                    continue;
                }
                $angkatan = (int)$angk;
            }

            $rows[] = [
                'nis'      => $nis,
                'nama'     => $nama,
                'kelas_id' => $kelasId,
                'angkatan' => $angkatan,
                'gender'   => $gend !== '' ? $gend : null,
                'status'   => $stat !== '' ? $stat : 'A',
            ];
        }

        fclose($handle);

        if (!empty($errors)) {
            // tampilkan max 8 error biar ga kepanjangan
            $msg = "Gagal import. Contoh error:\n- ".implode("\n- ", array_slice($errors, 0, 8));
            return back()->with('error', $msg);
        }

        if (empty($rows)) {
            return back()->with('error', 'Tidak ada data valid untuk diimport.');
        }

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        DB::transaction(function() use ($rows, $mode, &$inserted, &$updated, &$skipped){
            foreach ($rows as $r) {
                $exists = Siswa::where('nis', $r['nis'])->exists();

                if ($exists && $mode === 'insert_only') {
                    $skipped++;
                    continue;
                }

                if ($exists) {
                    Siswa::where('nis', $r['nis'])->update([
                        'nama'      => $r['nama'],
                        'kelas_id'  => $r['kelas_id'],
                        'angkatan'  => $r['angkatan'],
                        'gender'    => $r['gender'],
                        'status'    => $r['status'],
                    ]);
                    $updated++;
                } else {
                    Siswa::create($r);
                    $inserted++;
                }
            }
        });

        return back()->with('ok', "✅ Import selesai. Insert: {$inserted}, Update: {$updated}, Skip: {$skipped}.");
    }

    /** Download template CSV */
    public function downloadTemplateCsv()
    {
        $content = "nis,nama,angkatan,gender,status,nama_kelas,kelas_paralel\n"
                 . "14733,Ali Rifky Aditya,2024,L,A,VIII,7\n";

        return Response::make($content, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_siswa.csv"',
        ]);
    }
}
