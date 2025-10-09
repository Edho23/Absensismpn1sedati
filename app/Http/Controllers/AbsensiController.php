<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index()
    {
        $absensi = Absensi::with('siswa.kelas')
            ->whereDate('tanggal', Carbon::today())
            ->orderBy('jam_masuk','desc')
            ->paginate(20);
        $siswa = Siswa::with('kelas')->where('status_aktif', true)->get();
        return view('absensi.index', compact('absensi','siswa'));
    }

    public function storeManual(Request $req)
    {
        $data = $req->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'status_harian' => 'required|in:HADIR,SAKIT,ALPA',
            'catatan' => 'nullable|string'
        ]);
        $record = Absensi::firstOrCreate(
            ['id_siswa'=>$data['id_siswa'], 'tanggal'=>now()->toDateString()],
            ['sumber' => 'MANUAL']
        );
        $record->fill($data);
        if ($data['status_harian']==='HADIR' && !$record->jam_masuk) {
            $record->jam_masuk = now();
        }
        $record->save();

        return back()->with('ok','Input manual tersimpan.');
    }
}
