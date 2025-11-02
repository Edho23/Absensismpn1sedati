<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RfidRegisterController extends Controller
{
    /**
     * POST /api/rfid/scan
     * Body: { "uid": "04:A1:1C:L1", "kode_perangkat": "REG-1" (ops), "token": "..." (ops) }
     * Simpan UID terakhir untuk didaftarkan via UI web.
     */
    public function scan(Request $r)
    {
        $data = $r->validate([
            'uid'            => 'required|string|max:100',
            'kode_perangkat' => 'nullable|string|max:100',
            'token'          => 'nullable|string|max:200', // kalau mau pakai shared secret
        ]);

        // (Opsional) shared secret sangat sederhana
        // if (config('app.uid_register_token') && $data['token'] !== config('app.uid_register_token')) {
        //     return response()->json(['ok'=>false,'message'=>'Unauthorized'], 401);
        // }

        $device = $data['kode_perangkat'] ?: 'REG-DEFAULT';

        $payload = [
            'uid'       => strtoupper(trim($data['uid'])),
            'device'    => $device,
            'received'  => Carbon::now('Asia/Jakarta')->toIso8601String(),
        ];

        // simpan 30 detik saja (cukup buat 1–2 kali polling)
        Cache::put($this->key($device), $payload, now()->addSeconds(30));

        return response()->json([
            'ok'      => true,
            'message' => 'UID captured',
            'data'    => $payload,
        ]);
    }

    /**
     * GET /api/rfid/last?device=REG-1&consume=1
     * Ambil UID terakhir untuk form tambah kartu. Jika consume=1 → sekaligus hapus dari cache.
     */
    public function last(Request $r)
    {
        $device  = $r->query('device', 'REG-DEFAULT');
        $consume = (int) $r->query('consume', 0) === 1;

        $payload = Cache::get($this->key($device));
        if (!$payload) {
            return response()->json([
                'ok'      => true,
                'message' => 'No UID',
                'data'    => null,
            ]);
        }

        if ($consume) {
            Cache::forget($this->key($device));
        }

        return response()->json([
            'ok'      => true,
            'message' => 'OK',
            'data'    => $payload,
        ]);
    }

    private function key(string $device): string
    {
        return 'rfid:last:' . Str::upper($device);
    }
}
