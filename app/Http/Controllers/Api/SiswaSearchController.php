<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaSearchController extends Controller
{
    // GET /api/siswa/search?term=...&status=A
    public function __invoke(Request $req)
    {
        $term   = trim((string)$req->query('term',''));
        $status = $req->query('status','A');

        if ($term === '') return response()->json([]);

        $driver = DB::connection()->getDriverName();
        $kw     = '%'.str_replace(['%','_'], ['\\%','\\_'], $term).'%';

        $q = Siswa::with('kelas');
        if (in_array($status, ['A','N'], true)) {
            $q->where('status', $status);
        }

        if ($driver === 'pgsql') {
            $q->whereRaw('(nis ILIKE ? OR nama ILIKE ?)', [$kw, $kw]);
        } else {
            $q->where(function($qq) use ($kw){
                $qq->where('nis','like',$kw)->orWhere('nama','like',$kw);
            });
        }

        return response()->json(
            $q->orderBy('nama')
              ->limit(10)
              ->get()
              ->map(fn($s)=>[
                'nis'   => $s->nis,
                'nama'  => $s->nama,
                'kelas' => $s->kelas->nama_kelas ?? '-',
              ])
        );
    }
}
