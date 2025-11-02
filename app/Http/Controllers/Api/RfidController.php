<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\KartuRfid;
use App\Models\Siswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class RfidController extends Controller
{
    /**
     * GET/POST /api/rfid/register-hit?uid=XXXX
     * Simpan UID terakhir di cache selama 15 detik (untuk ditarik oleh UI Kartu).
     */
    public function registerHit(Request $r)
    {
        $uid = strtoupper(trim((string) $r->query('uid', $r->input('uid', ''))));
        if ($uid === '') {
            return response('UID kosong', 400);
        }

        // Normalisasi format (A1:B2:C3:D4)
        $norm = Str::of($uid)
            ->replace(['-',' '], '')
            ->upper();

        // Jika sudah ada ":" anggap sudah normal; jika tidak, tambahkan tiap 2
        if (!$norm->contains(':') && $norm->length() % 2 === 0) {
            $pairs = [];
            for ($i=0; $i < $norm->length(); $i+=2) {
                $pairs[] = substr((string) $norm, $i, 2);
            }
            $norm = implode(':', $pairs);
        } else {
            $norm = (string) $norm;
        }

        Cache::put('rfid_last_uid', $norm, now()->addSeconds(15));

        // Log ringan (opsional)
        \Log::info('RFID REGISTER HIT', ['uid' => $norm, 'ip' => $r->ip()]);

        return response("OK {$norm}", 200);
    }

    /**
     * GET /api/rfid/register-last
     * Ambil UID terakhir dari cache.
     */
    public function registerLast()
    {
        $uid = Cache::get('rfid_last_uid');
        if (!$uid) {
            return response()->json(['status' => 'EMPTY'], 204);
        }
        return response()->json(['status' => 'OK', 'uid' => $uid]);
    }

    /**
     * (Opsional) POST /api/rfid/presence
     * Body JSON: { "uid": "AA:BB:CC:DD" }
     * Contoh untuk presensi langsung by UID (bukan register kartu).
     */
    public function presenceHit(Request $r)
    {
        $data = $r->validate([
            'uid' => ['required', 'string'],
        ]);

        $uid = strtoupper(trim($data['uid']));

        $kartu = KartuRfid::where('uid', $uid)->where('status_aktif', 1)->first();
        if (!$kartu) {
            return response()->json(['ok' => false, 'msg' => 'Kartu tidak terdaftar / nonaktif'], 404);
        }

        $siswa = Siswa::where('nis', $kartu->nis)->where('status_aktif', 1)->first();
        if (!$siswa) {
            return response()->json(['ok' => false, 'msg' => 'Siswa tidak aktif / tidak ada'], 404);
        }

        $wib   = Carbon::now('Asia/Jakarta');
        $today = $wib->toDateString();

        $absen = Absensi::firstOrNew(['nis' => $siswa->nis, 'tanggal' => $today]);
        if (!$absen->exists) {
            $absen->sumber = 'RFID';
            $absen->status_harian = 'HADIR';
            $absen->jam_masuk = $wib;
        } else {
            // toggle jam_pulang jika belum
            if (empty($absen->jam_pulang)) {
                $absen->jam_pulang = $wib;
            }
        }
        $absen->save();

        return response()->json(['ok' => true, 'msg' => 'Presensi tersimpan', 'nis' => $siswa->nis]);
    }
}
