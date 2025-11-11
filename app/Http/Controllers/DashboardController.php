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

        // Label Senin–Jumat minggu berjalan (tetap seperti semula)
        $start = $today->copy()->startOfWeek(Carbon::MONDAY);
        $labels = [];
        $series = [];
        foreach (range(0,4) as $i) {
            $d = $start->copy()->addDays($i)->toDateString();
            $labels[] = $start->copy()->addDays($i)->translatedFormat('l'); // Senin, Selasa, ...
            $series[] = Absensi::whereDate('tanggal', $d)->count();
        }

        // Log hari ini (terbaru)
        $logs = Absensi::with(['siswa.kelas'])
            ->whereDate('tanggal', $today->toDateString())
            ->latest('updated_at')
            ->limit(10)
            ->get();

        // ==========================
        // Tambahan: Jumlah Siswa Belum Tapping Hari Ini
        // ==========================
        $todayDate = $today->toDateString();
        $sudahAbsenNis = Absensi::whereDate('tanggal', $todayDate)->pluck('nis');
        $belumTapping = Siswa::whereNotIn('nis', $sudahAbsenNis)->count();

        // ==========================
        // Tambahan: Periode Mingguan untuk judul chart
        // (Senin s/d Minggu minggu berjalan)
        // ==========================
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $today->copy()->endOfWeek(Carbon::SUNDAY);
        $periodeMingguan = $startOfWeek->translatedFormat('d M Y') . ' - ' . $endOfWeek->translatedFormat('d M Y');

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
