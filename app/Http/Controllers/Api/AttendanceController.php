<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{KartuRfid,Siswa,Absensi};
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * POST /api/attendances/hit
     * Body JSON: { "uid": "A1 B2 C3 D4", "device_code": "GERBANG-1", "timestamp": "optional-ISO" }
     */
    public function hit(Request $req)
    {
        $data = $req->validate([
            'uid'         => 'required|string',
            'device_code' => 'nullable|string',
            'timestamp'   => 'nullable|date'
        ]);

        // 1) Temukan kartu aktif
        $kartu = KartuRfid::where(['uid'=>$data['uid'], 'aktif'=>true])->first();
        if (!$kartu || !$kartu->nis) {
            return response()->json(['ok'=>false,'message'=>'Kartu tidak dikenal / belum ditautkan ke NIS'], 404);
        }

        // 2) Ambil siswa aktif
        $siswa = Siswa::with('kelas')->where('nis',$kartu->nis)->where('status_aktif',true)->first();
        if (!$siswa) {
            return response()->json(['ok'=>false,'message'=>'Siswa non-aktif / tidak ditemukan'], 404);
        }

        // 3) Waktu & aturan terlambat
        $now      = $data['timestamp'] ? Carbon::parse($data['timestamp']) : now();
        $tanggal  = $now->toDateString();
        $lateAt   = Carbon::createFromTime(7,0,0,$now->timezone)->addMinutes(5);
        $terlambat= $now->greaterThan($lateAt);

        // 4) Buat/ambil record absensi hari ini (unik: nis + tanggal)
        $absen = Absensi::firstOrCreate(
            ['nis' => $siswa->nis, 'tanggal' => $tanggal],
            ['sumber' => 'RFID']
        );

        // 5) Masuk atau Pulang
        if (is_null($absen->jam_masuk)) {
            $absen->jam_masuk     = $now;
            $absen->terlambat     = $terlambat;
            $absen->status_harian = 'HADIR';
            $absen->kode_perangkat= $data['device_code'] ?? $absen->kode_perangkat;
            $type   = 'masuk';
            $status = $terlambat ? 'TERLAMBAT' : 'HADIR';
        } else {
            $absen->jam_pulang     = $now;
            $absen->kode_perangkat = $data['device_code'] ?? $absen->kode_perangkat;
            $type   = 'pulang';
            $status = 'PULANG';
        }

        $absen->save();

        return response()->json([
            'ok'    => true,
            'type'  => $type,
            'nis'   => $siswa->nis,
            'nama'  => $siswa->nama,
            'kelas' => $siswa->kelas?->nama_kelas,
            'status'=> $status,
            'time'  => $now->toDateTimeString(),
        ]);
    }
}
