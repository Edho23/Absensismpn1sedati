<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\KartuRfid;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Waktu lokal
        $today = Carbon::now('Asia/Jakarta');

        // Kartu info (kotak atas dashboard)
        $cards = [
            'siswa' => Siswa::count(),
            'kelas' => Kelas::count(),
            'kartu' => KartuRfid::count(),
            'user'  => 1, // admin tunggal (versi ini)
        ];

        // ==========================
        // Label Senin–Sabtu minggu berjalan
        // ==========================
        $start  = $today->copy()->startOfWeek(Carbon::MONDAY);
        $labels = [];
        $series = [];

        // 0 = Senin, 5 = Sabtu
        foreach (range(0, 5) as $i) {
            $currentDate = $start->copy()->addDays($i);     // Senin + i hari
            $tanggal     = $currentDate->toDateString();    // YYYY-MM-DD

            // Nama hari (di locale Indo akan jadi: Senin, Selasa, ...)
            $labels[] = $currentDate->translatedFormat('l');

            // Hitung jumlah absensi pada tanggal ini
            $series[] = Absensi::whereDate('tanggal', $tanggal)->count();
        }

        // Log hari ini (terbaru)
        $logs = Absensi::with(['siswa.kelas'])
            ->whereDate('tanggal', $today->toDateString())
            ->latest('updated_at')
            ->limit(20)
            ->get();

        // ==========================
        // Jumlah Siswa Belum Tapping Hari Ini
        // ==========================
        $todayDate     = $today->toDateString();
        $sudahAbsenNis = Absensi::whereDate('tanggal', $todayDate)->pluck('nis');
        $belumTapping  = Siswa::whereNotIn('nis', $sudahAbsenNis)->count();

        // ==========================
        // Periode Mingguan untuk judul chart
        // (Senin s/d Minggu minggu berjalan)
        // ==========================
        $startOfWeek     = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek       = $today->copy()->endOfWeek(Carbon::SUNDAY);
        $periodeMingguan = $startOfWeek->translatedFormat('d M Y')
                            . ' - ' .
                            $endOfWeek->translatedFormat('d M Y');

        // Kirim ke view
        return view('dashboard.index', compact(
            'cards',
            'labels',
            'series',
            'logs',
            'belumTapping',
            'periodeMingguan'
        ));
    }
}
