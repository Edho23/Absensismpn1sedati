<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaSearchController extends Controller
{
    /**
     * GET /api/siswa/search?term=...
     * Balikkan max 10 siswa aktif: [{nis, nama, kelas}]
     */
    public function __invoke(Request $req)
    {
        $term = trim((string)$req->query('term', ''));
        if ($term === '') return response()->json([]);

        $q = Siswa::with('kelas')
            ->where('status_aktif', 1)
            ->where(function($qq) use ($term) {
                $qq->where('nis', 'ILIKE', "%{$term}%")
                   ->orWhere('nama', 'ILIKE', "%{$term}%");
            })
            ->orderBy('nama')
            ->limit(10)
            ->get();

        $out = $q->map(fn($s) => [
            'nis'   => $s->nis,
            'nama'  => $s->nama,
            'kelas' => optional($s->kelas)->nama_kelas ?? '-',
        ]);

        return response()->json($out);
    }
}
