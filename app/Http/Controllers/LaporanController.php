<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanRekapExport;

class LaporanController extends Controller
{
    /** 🔹 Deteksi kolom gender di tabel siswa */
    private function genderColumnName(): ?string
    {
        $candidates = ['jenis_kelamin', 'jk', 'gender', 'kelamin', 'jns_kelamin'];
        foreach ($candidates as $col) {
            if (Schema::hasColumn('siswa', $col)) {
                return $col;
            }
        }
        return null;
    }

    /** 🔹 Terapkan filter gender ke relasi siswa */
    private function applyGenderFilterToSiswaRelation($relationQuery, ?string $gender): void
    {
        if (!$gender) return;
        $col = $this->genderColumnName();
        if (!$col) return;

        if ($gender === 'L') {
            $relationQuery->whereIn(DB::raw("UPPER($col)"), ['L', 'LAKI', 'LAKI-LAKI']);
        } elseif ($gender === 'P') {
            $relationQuery->whereIn(DB::raw("UPPER($col)"), ['P', 'PEREMPUAN']);
        }
    }

    /** 🔹 Deteksi kolom foreign key antara siswa dan kelas */
    private function kelasRelasiColumn(): ?string
    {
        foreach (['id_kelas', 'kelas_id', 'kelas'] as $col) {
            if (Schema::hasColumn('siswa', $col)) {
                return $col;
            }
        }
        return null;
    }

    /** =========================== INDEX =========================== */
    public function index(Request $request)
    {
        $tanggalMulai   = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');
        $kelas          = $request->query('kelas');
        $status         = $request->query('status');
        $gender         = $request->query('gender');
        $mode           = $request->query('mode', 'detail');

        $query = Absensi::with('siswa.kelas')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_masuk');

        // Filter tanggal
        if ($tanggalMulai && $tanggalSelesai) {
            $query->whereBetween(DB::raw('DATE(tanggal)'), [$tanggalMulai, $tanggalSelesai]);
        } elseif ($tanggalMulai) {
            $query->whereDate('tanggal', $tanggalMulai);
        } elseif ($tanggalSelesai) {
            $query->whereDate('tanggal', $tanggalSelesai);
        }

        // Filter kelas
        if ($kelas) {
            $query->whereHas('siswa.kelas', fn($q) => $q->where('nama_kelas', $kelas));
        }

        // Filter status
        if ($status) {
            $query->where('status_harian', $status);
        }

        // Filter gender
        if ($gender) {
            $query->whereHas('siswa', function ($q) use ($gender) {
                $this->applyGenderFilterToSiswaRelation($q, $gender);
            });
        }

        /** =========================== MODE REKAP =========================== */
        if ($mode === 'rekap') {
            $kelasRelasiCol = $this->kelasRelasiColumn() ?? 'kelas_id';

            // Penting: hapus orderBy bawaan (tanggal, jam_masuk) sebelum agregasi
            $rekap = (clone $query)
                ->reorder()
                ->select([
                    'nis',
                    DB::raw("MAX((SELECT nama FROM siswa s WHERE s.nis = absensi.nis)) AS nama"),
                    DB::raw("
                        MAX((
                            SELECT nama_kelas 
                            FROM kelas k 
                            JOIN siswa s2 ON s2.$kelasRelasiCol = k.id
                            WHERE s2.nis = absensi.nis
                        )) AS nama_kelas
                    "),
                    DB::raw("SUM(CASE WHEN status_harian='HADIR' THEN 1 ELSE 0 END) AS hadir"),
                    DB::raw("SUM(CASE WHEN status_harian='SAKIT' THEN 1 ELSE 0 END) AS sakit"),
                    DB::raw("SUM(CASE WHEN status_harian='IZIN'  THEN 1 ELSE 0 END) AS izin"),
                    DB::raw("SUM(CASE WHEN status_harian='ALPA'  THEN 1 ELSE 0 END) AS alpa"),
                    DB::raw("COUNT(*) AS total"),
                ])
                ->groupBy('nis')
                // Urutkan pakai alias yang memang ada di SELECT
                ->orderBy('nama_kelas')
                ->orderBy('nama')
                ->paginate(20);

            $daftarKelas = Kelas::orderBy('nama_kelas')->pluck('nama_kelas');

            return view('laporan.index', [
                'mode'           => 'rekap',
                'rekap'          => $rekap,
                'absensi'        => null,
                'daftarKelas'    => $daftarKelas,
                'tanggalMulai'   => $tanggalMulai,
                'tanggalSelesai' => $tanggalSelesai,
                'kelas'          => $kelas,
                'status'         => $status,
                'gender'         => $gender,
            ]);
        }

        /** =========================== MODE DETAIL =========================== */
        $absensi = $query->paginate(20);
        $daftarKelas = Kelas::orderBy('nama_kelas')->pluck('nama_kelas');

        return view('laporan.index', [
            'mode'           => 'detail',
            'absensi'        => $absensi,
            'rekap'          => null,
            'daftarKelas'    => $daftarKelas,
            'tanggalMulai'   => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'kelas'          => $kelas,
            'status'         => $status,
            'gender'         => $gender,
        ]);
    }

    /** =========================== EXPORT =========================== */
    public function export(Request $request)
    {
        $mulai   = $request->query('tanggal_mulai');
        $selesai = $request->query('tanggal_selesai');
        $kelas   = $request->query('kelas');
        $gender  = $request->query('gender');

        $base = Absensi::query()->with('siswa.kelas');

        // Filter tanggal
        if ($mulai && $selesai) {
            $base->whereBetween(DB::raw('DATE(tanggal)'), [$mulai, $selesai]);
            $periodeText = Carbon::parse($mulai)->format('d/m/Y') . ' s/d ' . Carbon::parse($selesai)->format('d/m/Y');
        } elseif ($mulai) {
            $base->whereDate('tanggal', $mulai);
            $periodeText = Carbon::parse($mulai)->format('d/m/Y');
        } elseif ($selesai) {
            $base->whereDate('tanggal', $selesai);
            $periodeText = Carbon::parse($selesai)->format('d/m/Y');
        } else {
            $today = Carbon::now('Asia/Jakarta')->toDateString();
            $base->whereDate('tanggal', $today);
            $periodeText = Carbon::parse($today)->format('d/m/Y');
        }

        // Filter kelas
        $kelasText = 'Semua Kelas';
        if ($kelas) {
            $kelasText = $kelas;
            $base->whereHas('siswa.kelas', fn($q) => $q->where('nama_kelas', $kelas));
        }

        // Filter gender
        if ($gender) {
            $base->whereHas('siswa', function ($q) use ($gender) {
                $this->applyGenderFilterToSiswaRelation($q, $gender);
            });
            $kelasText .= $gender === 'L' ? ' | Gender: Laki-laki' : ' | Gender: Perempuan';
        }

        // Rekap per status (untuk Excel)
        $rekap = (clone $base)
            ->select('status_harian', DB::raw('COUNT(*) as total'))
            ->groupBy('status_harian')
            ->pluck('total', 'status_harian');

        $hadir = (int) ($rekap['HADIR'] ?? 0);
        $sakit = (int) ($rekap['SAKIT'] ?? 0);
        $izin  = (int) ($rekap['IZIN']  ?? 0);
        $alpa  = (int) ($rekap['ALPA']  ?? 0);

        $filename = 'rekap-kehadiran_' . str_replace(['/', ' '], ['-', '_'], $periodeText) . '.xlsx';

        return Excel::download(
            new LaporanRekapExport($periodeText, $kelasText, $hadir, $sakit, $izin, $alpa),
            $filename
        );
    }
}
