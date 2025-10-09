<?php
namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\KartuRfid;
use App\Models\Absensi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $cards = [
            'siswa' => Siswa::count(),
            'kelas' => Kelas::count(),
            'kartu' => KartuRfid::count(),
            'user'  => 1, // hanya BK (superadmin) untuk versi ini
        ];

        // Data sederhana untuk grafik Senin–Jumat
        $labels = ['Senin','Selasa','Rabu','Kamis','Jumat'];
        $series = [];
        foreach (range(0,4) as $i) {
            $d = $today->copy()->startOfWeek()->addDays($i);
            $series[] = Absensi::whereDate('tanggal', $d)->count();
        }

        $logs = Absensi::with(['siswa.kelas'])
            ->whereDate('tanggal', Carbon::today())
            ->latest('updated_at')
            ->limit(10)->get();

        return view('dashboard.index', compact('cards','labels','series','logs'));
    }
}
