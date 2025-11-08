<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;

class SiswaApiController extends Controller
{
    public function search(Request $r)
    {
        $term = trim((string) $r->query('q', ''));
        if ($term === '') {
            return response()->json(['ok' => true, 'data' => []]);
        }

        $rows = Siswa::with('kelas')
            ->where('nama', 'ILIKE', "%{$term}%")
            ->orWhere('nis', 'ILIKE', "%{$term}%")
            ->orderBy('nama')
            ->limit(10)
            ->get()
            ->map(function ($s) {
                return [
                    'id'          => $s->id,
                    'nis'         => $s->nis,
                    'nama'        => $s->nama,
                    'kelas'       => $s->kelas->nama_kelas ?? '-',
                    'kelas_id'    => $s->kelas_id,
                    'status'      => $s->status,        // A / N
                    'gender'      => $s->gender,        // L / P
                    'angkatan'    => $s->angkatan,
                    'paralel'     => $s->kelas_paralel,
                ];
            });

        return response()->json(['ok' => true, 'data' => $rows]);
    }
}
