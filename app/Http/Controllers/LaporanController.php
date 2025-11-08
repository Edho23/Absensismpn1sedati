<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // ===== FILTER =====
        $mode            = $request->query('mode', 'detail'); // 'detail' | 'rekap'
        $tanggal         = $request->query('tanggal');        // legacy (opsional)
        $tanggalMulai    = $request->query('tanggal_mulai');
        $tanggalSelesai  = $request->query('tanggal_selesai');
        $kelas           = $request->query('kelas');          // nama_kelas
        $status          = $request->query('status');         // detail-only

        // ===== DROPDOWN KELAS (urut: grade → paralel → nama) =====
        $daftarKelas = Kelas::orderBy('grade')
            ->orderBy('kelas_paralel')
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas');

        if ($mode === 'rekap') {
            // =========================
            // REKAP PER SISWA (H/S/I/A)
            // =========================
            $q = DB::table('absensi as a')
                ->join('siswa as s', 's.nis', '=', 'a.nis')
                ->leftJoin('kelas as k', 'k.id', '=', 's.kelas_id')
                ->select([
                    's.nis',
                    's.nama',
                    DB::raw('COALESCE(k.nama_kelas, \'-\') as nama_kelas'),
                    DB::raw("SUM(CASE WHEN a.status_harian = 'HADIR' THEN 1 ELSE 0 END) AS hadir"),
                    DB::raw("SUM(CASE WHEN a.status_harian = 'SAKIT' THEN 1 ELSE 0 END) AS sakit"),
                    DB::raw("SUM(CASE WHEN a.status_harian = 'IZIN'  THEN 1 ELSE 0 END) AS izin"),
                    DB::raw("SUM(CASE WHEN a.status_harian = 'ALPA'  THEN 1 ELSE 0 END) AS alpa"),
                    DB::raw("COUNT(*) AS total")
                ]);

            // filter tanggal tunggal (legacy)
            if ($tanggal) {
                $q->whereDate('a.tanggal', $tanggal);
            }

            // filter rentang tanggal
            if ($tanggalMulai && $tanggalSelesai) {
                $q->whereBetween('a.tanggal', [$tanggalMulai, $tanggalSelesai]);
            } elseif ($tanggalMulai) {
                $q->whereDate('a.tanggal', '>=', $tanggalMulai);
            } elseif ($tanggalSelesai) {
                $q->whereDate('a.tanggal', '<=', $tanggalSelesai);
            }

            // filter kelas (nama_kelas)
            if ($kelas) {
                $q->where('k.nama_kelas', $kelas);
            }

            $rekap = $q->groupBy('s.nis', 's.nama', 'k.nama_kelas')
                       ->orderBy('s.nama')
                       ->paginate(20)
                       ->withQueryString();

            return view('laporan.index', [
                'mode'            => 'rekap',
                'rekap'           => $rekap,
                'absensi'         => null, // tidak dipakai di rekap
                'daftarKelas'     => $daftarKelas,
                'tanggal'         => $tanggal,
                'kelas'           => $kelas,
                'status'          => $status, // diabaikan pada rekap
                'tanggalMulai'    => $tanggalMulai,
                'tanggalSelesai'  => $tanggalSelesai,
            ]);
        }

        // ==============
        // MODE: DETAIL
        // ==============
        $query = Absensi::with('siswa.kelas')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_masuk');

        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }
        if ($tanggalMulai && $tanggalSelesai) {
            $query->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
        } elseif ($tanggalMulai) {
            $query->whereDate('tanggal', '>=', $tanggalMulai);
        } elseif ($tanggalSelesai) {
            $query->whereDate('tanggal', '<=', $tanggalSelesai);
        }

        if ($kelas) {
            $query->whereHas('siswa.kelas', fn($q) => $q->where('nama_kelas', $kelas));
        }
        if ($status) {
            $query->where('status_harian', $status);
        }

        $absensi = $query->paginate(20)->withQueryString();

        return view('laporan.index', [
            'mode'            => 'detail',
            'absensi'         => $absensi,
            'rekap'           => null,
            'daftarKelas'     => $daftarKelas,
            'tanggal'         => $tanggal,
            'kelas'           => $kelas,
            'status'          => $status,
            'tanggalMulai'    => $tanggalMulai,
            'tanggalSelesai'  => $tanggalSelesai,
        ]);
    }

    public function export(Request $request)
    {
        return back()->with('ok', 'Fitur export laporan belum diaktifkan.');
    }
}
