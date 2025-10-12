<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Absensi,Siswa};
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // daftar absensi hari ini (tanpa view/frontend detail—hanya logic)
    public function index()
    {
        $today   = Carbon::today()->toDateString();
        $records = Absensi::with('siswa.kelas')
                    ->whereDate('tanggal', $today)
                    ->orderByDesc('jam_masuk')
                    ->paginate(20);

        // return data saja dulu (frontend ditunda)
        return response()->json($records);
    }

    // input manual HADIR/SAKIT/ALPA by NIS
    public function storeManual(Request $req)
    {
        $data = $req->validate([
            'nis'            => 'required|exists:siswa,nis',
            'status_harian'  => 'required|in:HADIR,SAKIT,ALPA',
            'catatan'        => 'nullable|string',
            'kode_perangkat' => 'nullable|string',
        ]);

        $today = Carbon::today()->toDateString();

        $absen = Absensi::firstOrCreate(
            ['nis'=>$data['nis'], 'tanggal'=>$today],
            ['sumber'=>'MANUAL']
        );

        $absen->status_harian  = $data['status_harian'];
        $absen->catatan        = $data['catatan'] ?? $absen->catatan;
        $absen->kode_perangkat = $data['kode_perangkat'] ?? $absen->kode_perangkat;

        if ($data['status_harian'] === 'HADIR' && !$absen->jam_masuk) {
            $absen->jam_masuk = now();
        }

        $absen->save();

        return response()->json(['ok'=>true, 'message'=>'Input manual tersimpan', 'data'=>$absen]);
    }
}
