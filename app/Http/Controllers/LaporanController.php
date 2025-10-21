<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Kelas;
use Carbon\Carbon;

class LaporanController extends Controller
{
    /**
     * Halaman utama laporan kehadiran
     */
    public function index(Request $request)
    {
        // Ambil parameter filter
        $tanggal = $request->query('tanggal');
        $kelas   = $request->query('kelas');
        $status  = $request->query('status');

        // Query utama: ambil absensi + relasi siswa dan kelas
        $query = Absensi::with('siswa.kelas')->orderByDesc('tanggal')->orderByDesc('jam_masuk');

        // Filter tanggal
        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        // Filter kelas (berdasarkan nama kelas)
        if ($kelas) {
            $query->whereHas('siswa.kelas', fn($q) => $q->where('nama_kelas', $kelas));
        }

        // Filter status
        if ($status) {
            $query->where('status_harian', $status);
        }

        // Ambil hasilnya
        $absensi = $query->paginate(20);

        // Ambil daftar kelas untuk dropdown filter
        $daftarKelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->pluck('nama_kelas');

        return view('laporan.index', [
            'absensi'     => $absensi,
            'daftarKelas' => $daftarKelas,
            'tanggal'     => $tanggal,
            'kelas'       => $kelas,
            'status'      => $status,
        ]);
    }

    /**
     * Export laporan ke file (nanti bisa diaktifkan)
     */
    public function export(Request $request)
    {
        // Catatan: fitur export (Excel/PDF) bisa ditambahkan nanti.
        return back()->with('ok', 'Fitur export laporan belum diaktifkan.');
    }
}
