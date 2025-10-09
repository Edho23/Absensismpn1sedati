<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KartuRfid;
use App\Models\Perangkat;
use App\Models\Absensi;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function hit(Request $req)
    {
        $data = $req->validate([
            'uid' => 'required|string',
            'device_code' => 'nullable|string',
            'timestamp' => 'nullable|date' // opsional, pakai server time jika tidak ada
        ]);

        $kartu = KartuRfid::with('siswa.kelas')->where(['uid'=>$data['uid'], 'aktif'=>true])->first();
        if (!$kartu || !$kartu->siswa || !$kartu->siswa->status_aktif) {
            return response()->json(['ok'=>false,'message'=>'Kartu tidak dikenal / non-aktif'], 404);
        }

        $now = isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : now();
        $tanggal = $now->toDateString();
        $jamMasukAturan = Carbon::createFromTime(7,0,0, $now->timezone);
        $grace = 5; // menit
        $terlambat = $now->greaterThan($jamMasukAturan->copy()->addMinutes($grace));

        $absen = Absensi::firstOrCreate(
            ['id_siswa'=>$kartu->siswa->id, 'tanggal'=>$tanggal],
            ['sumber'=>'RFID']
        );

        $deviceIdMasuk = null; $deviceIdPulang = null;
        if (!empty($data['device_code'])) {
            $dev = Perangkat::where('kode_perangkat', $data['device_code'])->first();
            $deviceIdMasuk = $dev?->id;
            $deviceIdPulang = $dev?->id;
        }

        if (is_null($absen->jam_masuk)) {
            $absen->jam_masuk = $now;
            $absen->terlambat = $terlambat;
            $absen->status_harian = 'HADIR';
            $absen->id_perangkat_masuk = $deviceIdMasuk;
            $status = $terlambat ? 'TERLAMBAT' : 'HADIR';
            $phrase = "Selamat datang";
            $type = 'masuk';
        } else {
            // checkout
            $absen->jam_pulang = $now;
            $absen->id_perangkat_pulang = $deviceIdPulang;
            $status = 'PULANG';
            $phrase = "Checkout";
            $type = 'pulang';
        }
        $absen->save();

        return response()->json([
            'ok'=>true,
            'type'=>$type,
            'nama'=>$kartu->siswa->nama,
            'kelas'=>$kartu->siswa->kelas?->nama_kelas,
            'status'=>$status,
            'time'=>$now->toDateTimeString(),
        ]);
    }
}
