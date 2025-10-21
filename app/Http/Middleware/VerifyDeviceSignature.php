<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyDeviceSignature
{
    public function handle(Request $request, Closure $next)
    {
        $key = config('app.device_key'); // set di .env APP_DEVICE_KEY=xxxxxxxx
        if (!$key) return $next($request); // jika belum di-set, lewati (dev only)

        $provided = $request->header('X-Device-Key');
        if ($provided !== $key) {
            return response()->json(['ok'=>false, 'code'=>'UNAUTHORIZED_DEVICE', 'message'=>'Kunci perangkat salah'], 401);
        }
        return $next($request);
    }
}
