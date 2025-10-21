<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminLog;
use Carbon\Carbon;

class LogAdminActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $admin = Auth::guard('admin')->user();
        if (!$admin) return $response;

        // Deteksi hanya aksi penting
        $method = strtoupper($request->method());
        $route  = optional($request->route())->getName() ?? $request->path();

        // Skip log untuk request GET (navigasi/menu) dan static assets
        if (
            $method === 'GET' ||
            Str::startsWith($request->path(), ['storage','css','js','images','img','vendor','favicon.ico'])
        ) {
            return $response;
        }

        // Skip log untuk route login/logout agar tidak spam
        if (Str::contains($route, ['login', 'logout'])) {
            return $response;
        }

        // Catat hanya untuk method POST, PUT, PATCH, DELETE
        $payload = $request->except(['password','password_confirmation','_token']);

        AdminLog::create([
            'admin_id'   => $admin->id,
            'action'     => $route,
            'route'      => $request->path(),
            'method'     => $method,
            'ip'         => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'payload'    => empty($payload) ? null : $payload,
            'created_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);

        return $response;
    }
}
