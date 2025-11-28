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
     * Query params:
     * - q            : cari nama/nis
     * - kelas_id     : id kelas
     * - kelas_paralel: dari tabel kelas (relasi)
     * - gender       : L/P
     * - angkatan     : tahun
     * - status       : A/N
     */
    public function index(Request $r)
    {
        $filters = [
            'q'             => trim((string)$r->query('q')),
            'kelas_id'      => $r->query('kelas_id'),
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
                    $qq->where('nis', 'like', $kw)->orWhere('nama', 'like', $kw);
                });
            }
        }

        // Filter kelas langsung
        if ($filters['kelas_id']) {
            $q->where('kelas_id', $filters['kelas_id']);
        }

        // Filter kelas_paralel via relasi kelas
        if ($filters['kelas_paralel']) {
            $q->whereHas('kelas', fn($qq) => $qq->where('kelas_paralel', $filters['kelas_paralel']));
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

        // Dropdown helper
        $kelas = Kelas::orderBy('kelas_paralel')->orderBy('nama_kelas', 'asc')->get();
        $daftarParalel = Kelas::select('kelas_paralel')->distinct()->orderBy('kelas_paralel')->pluck('kelas_paralel');

        return view('siswa.index', [
            'siswa'         => $siswa,
            'kelas'         => $kelas,
            'filters'       => $filters,
            'daftarParalel' => $daftarParalel,
        ]);
    }

    /**
     * TAMBAH DATA SISWA BARU
     */
    public function store(Request $request)
    {
        // Normalisasi NIS
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

        // Default status A
        $data['status'] = $data['status'] ?? 'A';

        Siswa::create($data);

        return redirect()->route('siswa.index')->with('ok', '✅ Siswa baru berhasil ditambahkan.');
    }

    /**
     * (Opsional) FORM EDIT SISWA bila pakai view terpisah
     */
    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        $kelas = Kelas::orderBy('kelas_paralel')->orderBy('nama_kelas')->get();

        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    /**
     * UPDATE DATA SISWA
     */
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

        // Pertahankan status lama bila tidak dikirim
        if (!array_key_exists('status', $data) || $data['status'] === null || $data['status'] === '') {
            $data['status'] = $siswa->status ?? 'A';
        }

        $siswa->update($data);

        return redirect()->route('siswa.index')->with('ok', '✏️ Data siswa berhasil diperbarui.');
    }

    /**
     * HAPUS SISWA
     */
    public function destroy($id)
    {
        Siswa::findOrFail($id)->delete();
        return redirect()->route('siswa.index')->with('ok', '🗑️ Data siswa berhasil dihapus.');
    }

    /**
     * HAPUS BANYAK SISWA SEKALIGUS (BULK DELETE)
     */
    public function bulkDestroy(Request $request)
    {
        // Ambil array id siswa dari checkbox
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return redirect()
                ->route('siswa.index')
                ->with('error', 'Tidak ada siswa yang dipilih untuk dihapus.');
        }

        // Pastikan semua id berupa integer
        $ids = array_map('intval', $ids);

        // Hapus semua siswa yang id-nya ada di array
        $deleted = Siswa::whereIn('id', $ids)->delete();

        return redirect()
            ->route('siswa.index')
            ->with('ok', "🗑️ {$deleted} data siswa berhasil dihapus.");
    }
    
    /**
     * SEARCH Typeahead NIS/Nama (default hanya status A)
     * GET /siswa/search?term=...&status=A
     * Optional: kelas_id, kelas_paralel, gender, angkatan
     */
    public function search(Request $r)
    {
        $term          = trim((string)$r->query('term', ''));
        $kelasId       = $r->query('kelas_id');
        $kelasParalel  = $r->query('kelas_paralel');
        $gender        = $r->query('gender');
        $angkatan      = $r->query('angkatan');
        $status        = $r->query('status', 'A'); // default aktif

        if ($term === '') return response()->json([]);

        $driver = DB::connection()->getDriverName();
        $kw = '%'.str_replace(['%','_'], ['\\%','\\_'], $term).'%';

        $q = Siswa::with('kelas');

        if (in_array($status, ['A','N'], true)) {
            $q->where('status', $status);
        }

        if ($kelasId)      $q->where('kelas_id', $kelasId);
        if ($kelasParalel) $q->whereHas('kelas', fn($qq) => $qq->where('kelas_paralel', $kelasParalel));
        if (in_array($gender, ['L','P'], true)) $q->where('gender', $gender);
        if ($angkatan)     $q->where('angkatan', (int)$angkatan);

        if ($driver === 'pgsql') {
            $q->whereRaw('(nis ILIKE ? OR nama ILIKE ?)', [$kw, $kw]);
        } else {
            $q->where(function ($qq) use ($kw) {
                $qq->where('nis', 'like', $kw)->orWhere('nama', 'like', $kw);
            });
        }

        $rows = $q->orderBy('nama', 'asc')->limit(10)->get(['id','nis','nama','kelas_id']);

        return response()->json($rows->map(fn($s) => [
            'nis'   => $s->nis,
            'nama'  => $s->nama,
            'kelas' => $s->kelas->nama_kelas ?? '-',
            'label' => "{$s->nis} — {$s->nama} (" . ($s->kelas->nama_kelas ?? '-') . ")",
        ]));
    }

    /**
     * PROMOSI NAIK KELAS / LULUS
     */
    public function promote(SiswaPromotion $svc)
    {
        $res = $svc->promoteAll();
        return back()->with(
            'ok',
            "✅ Promosi selesai — Naik: {$res['moved']}, Lulus: {$res['graduated']}, Dilewati: {$res['skipped']}."
        );
    }
}
