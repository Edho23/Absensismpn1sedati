<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\Kelas;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter dari query string (kalau ada)
        $tanggal = $request->query('tanggal');
        $kelasId = $request->query('kelas_id');

        // Query dasar absensi
        $query = Absensi::with('siswa.kelas')->orderByDesc('tanggal');

        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        if ($kelasId) {
            $query->whereHas('siswa', fn($q) => $q->where('id_kelas', $kelasId));
        }

        $laporan = $query->paginate(20);
        $kelas   = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('laporan.index', compact('laporan', 'kelas', 'tanggal', 'kelasId'));
    }

    public function export(Request $request)
    {
        // (opsional) logic export bisa ditulis nanti
        return back()->with('ok', 'Fitur export laporan belum diaktifkan.');
    }
}
