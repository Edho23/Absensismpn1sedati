<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\KartuRfid;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::now('Asia/Jakarta');

        $cards = [
            'siswa' => Siswa::count(),
            'kelas' => Kelas::count(),
            'kartu' => KartuRfid::count(),
            'user'  => 1,
        ];

        $start  = $today->copy()->startOfWeek(Carbon::MONDAY);
        $labels = [];
        $series = [];

        foreach (range(0, 5) as $i) {
            $currentDate = $start->copy()->addDays($i);
            $tanggal     = $currentDate->toDateString();

            $labels[] = $currentDate->translatedFormat('l');
            $series[] = Absensi::whereDate('tanggal', $tanggal)->count();
        }

        $logs = Absensi::with(['siswa.kelas'])
            ->whereDate('tanggal', $today->toDateString())
            ->latest('updated_at')
            ->limit(20)
            ->get();

        $todayDate     = $today->toDateString();
        $sudahAbsenNis = Absensi::whereDate('tanggal', $todayDate)->pluck('nis');
        $belumTapping  = Siswa::whereNotIn('nis', $sudahAbsenNis)->count();

        $startOfWeek     = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek       = $today->copy()->endOfWeek(Carbon::SUNDAY);
        $periodeMingguan = $startOfWeek->translatedFormat('d M Y') . ' - ' . $endOfWeek->translatedFormat('d M Y');

        return view('dashboard.index', compact(
            'cards',
            'labels',
            'series',
            'logs',
            'belumTapping',
            'periodeMingguan'
        ));
    }

    // ✅ Endpoint ringan untuk cek ada data baru atau tidak
    public function ping(Request $request)
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $latest = Absensi::whereDate('tanggal', $today)
            ->latest('updated_at')
            ->first(['id', 'updated_at']);

        return response()->json([
            'latest_id'         => $latest?->id,
            'latest_updated_at' => $latest?->updated_at?->toIso8601String(),
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
        ]);
    }
}
