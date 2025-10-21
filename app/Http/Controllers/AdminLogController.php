<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminLog;
use Carbon\Carbon;

class AdminLogController extends Controller
{
    public function index(Request $r)
    {
        $tanggal = $r->query('tanggal');  // format YYYY-MM-DD
        $aksi    = $r->query('aksi');     // filter action/route name
        $q       = $r->query('q');        // keyword bebas (route, user_agent)

        $logs = AdminLog::with('admin')
            ->when($tanggal, fn($qq) => $qq->whereDate('created_at', $tanggal))
            ->when($aksi, fn($qq) => $qq->where(function($w) use ($aksi) {
                $w->where('action', 'ilike', "%{$aksi}%")
                  ->orWhere('route', 'ilike', "%{$aksi}%");
            }))
            ->when($q, fn($qq) => $qq->where(function($w) use ($q) {
                $w->where('route', 'ilike', "%{$q}%")
                  ->orWhere('method', 'ilike', "%{$q}%")
                  ->orWhere('user_agent', 'ilike', "%{$q}%")
                  ->orWhere('action', 'ilike', "%{$q}%");
            }))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('adminlog.index', compact('logs', 'tanggal', 'aksi', 'q'));
    }
}
