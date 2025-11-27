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
    /** Deteksi kolom gender di tabel siswa */
    private function genderColumnName(): ?string
    {
        $candidates = ['gender', 'jenis_kelamin', 'jk', 'kelamin', 'jns_kelamin'];
        foreach ($candidates as $col) {
            if (Schema::hasColumn('siswa', $col)) {
                return $col;
            }
        }
        return null;
    }

    /** Terapkan filter gender ke relasi siswa */
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

    /** Deteksi kolom foreign key antara siswa dan kelas */
    private function kelasRelasiColumn(): ?string
    {
        foreach (['kelas_id', 'id_kelas', 'kelas'] as $col) {
            if (Schema::hasColumn('siswa', $col)) {
                return $col;
            }
        }
        return null;
    }

    /** =========================== INDEX =========================== */
    public function index(Request $request)
    {
        $tanggalMulai    = $request->query('tanggal_mulai');
        $tanggalSelesai  = $request->query('tanggal_selesai');
        $kelas           = $request->query('kelas');           // VII/VIII/IX (huruf saja, dari nama_kelas)
        $kelasParalel    = $request->query('kelas_paralel');   // 1..11
        $status          = $request->query('status');          // HADIR/SAKIT/IZIN/ALPA (untuk mode detail)
        $gender          = $request->query('gender');          // L/P
        $mode            = $request->query('mode', 'detail');  // detail | rekap

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

        // Filter kelas + paralel
        if ($kelas || $kelasParalel) {
            $query->whereHas('siswa.kelas', function ($q) use ($kelas, $kelasParalel) {
                if ($kelas) {
                    $q->where('nama_kelas', $kelas);
                }
                if ($kelasParalel) {
                    $q->where('kelas_paralel', $kelasParalel);
                }
            });
        }

        // Filter status (khusus mode detail)
        if ($status && $mode === 'detail') {
            $query->where('status_harian', $status);
        }

        // Filter gender
        if ($gender) {
            $query->whereHas('siswa', function ($q) use ($gender) {
                $this->applyGenderFilterToSiswaRelation($q, $gender);
            });
        }

        // Daftar pilihan filter
        $daftarKelas = Kelas::select('nama_kelas')->distinct()->orderBy('nama_kelas')->pluck('nama_kelas');
        $daftarParalel = Kelas::select('kelas_paralel')->distinct()->orderBy('kelas_paralel')->pluck('kelas_paralel');

        if ($mode === 'rekap') {
            $kelasRelasiCol = $this->kelasRelasiColumn() ?? 'kelas_id';

            $rekap = (clone $query)
                ->reorder()
                ->select([
                    'absensi.nis',
                    DB::raw("MAX((SELECT nama FROM siswa s WHERE s.nis = absensi.nis)) AS nama"),
                    DB::raw("MAX((
                        SELECT nama_kelas
                        FROM kelas k
                        JOIN siswa s2 ON s2.$kelasRelasiCol = k.id
                        WHERE s2.nis = absensi.nis
                    )) AS nama_kelas"),
                    DB::raw("SUM(CASE WHEN status_harian='HADIR' THEN 1 ELSE 0 END) AS hadir"),
                    DB::raw("SUM(CASE WHEN status_harian='SAKIT' THEN 1 ELSE 0 END) AS sakit"),
                    DB::raw("SUM(CASE WHEN status_harian='IZIN'  THEN 1 ELSE 0 END) AS izin"),
                    DB::raw("SUM(CASE WHEN status_harian='ALPA'  THEN 1 ELSE 0 END) AS alpa"),
                    DB::raw("COUNT(*) AS total"),
                ])
                ->groupBy('absensi.nis')
                ->orderBy('nama_kelas')
                ->orderBy('nama')
                ->paginate(20);

            return view('laporan.index', [
                'mode'           => 'rekap',
                'rekap'          => $rekap,
                'absensi'        => null,
                'daftarKelas'    => $daftarKelas,
                'daftarParalel'  => $daftarParalel,
                'tanggalMulai'   => $tanggalMulai,
                'tanggalSelesai' => $tanggalSelesai,
                'kelas'          => $kelas,
                'kelasParalel'   => $kelasParalel,
                'status'         => $status,
                'gender'         => $gender,
            ]);
        }

        // MODE DETAIL
        $absensi = $query->paginate(20);

        return view('laporan.index', [
            'mode'           => 'detail',
            'absensi'        => $absensi,
            'rekap'          => null,
            'daftarKelas'    => $daftarKelas,
            'daftarParalel'  => $daftarParalel,
            'tanggalMulai'   => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'kelas'          => $kelas,
            'kelasParalel'   => $kelasParalel,
            'status'         => $status,
            'gender'         => $gender,
        ]);
    }

    /** =========================== EXPORT =========================== */
    public function export(Request $request)
    {
        // ===== ambil & sanitasi parameter =====
        $namaKelas   = $request->query('kelas');           // 'VII' | 'VIII' | 'IX' | null
        $paralel     = $request->query('kelas_paralel');   // 1..11 | null
        $gender      = $request->query('gender');          // 'L' | 'P' | null

        // Normalisasi 'jenis' biar TIDAK pernah jatuh ke else → all_rekap
        $rawJenis = strtolower((string)$request->query('jenis', 'bulan'));
        $jenisMap = [
            // 1 bulan
            'bulan' => 'bulan',
            'absen_bulan' => 'bulan',
            '1_bulan' => 'bulan',
            'satu_bulan' => 'bulan',

            // 1 bulan + rekap
            'bulan_rekap' => 'bulan_rekap',
            'absen_bulan_rekap' => 'bulan_rekap',
            'bulan+rekap' => 'bulan_rekap',

            // semua bulan + rekap
            'all_rekap' => 'all_rekap',
            'semua_bulan_rekap' => 'all_rekap',
            'jan-des_rekap' => 'all_rekap',
            'jan_des_rekap' => 'all_rekap',
            '12_bulan' => 'all_rekap',
        ];
        $jenis = $jenisMap[$rawJenis] ?? 'bulan';

        $bulan = (int) ($request->query('bulan') ?? 0); // 1..12
        $tahun = (int) ($request->query('tahun') ?? 0); // YYYY

        // fallback aman untuk bulan/tahun
        $now = now('Asia/Jakarta');
        if ($bulan < 1 || $bulan > 12) $bulan = (int)$now->format('n');
        if ($tahun < 2000)            $tahun = (int)$now->format('Y');

        // wali kelas (opsional)
        $waliKelas = null;
        if ($namaKelas && $paralel) {
            $waliKelas = \App\Models\Kelas::where('nama_kelas', $namaKelas)
                ->where('kelas_paralel', (int)$paralel)
                ->value('wali_kelas');
        }

        // nama file
        $kelasSlug   = $namaKelas ?: 'semua';
        $paralelSlug = $paralel ?: 'all';
        $filename    = "laporan_{$jenis}_{$kelasSlug}_{$paralelSlug}_{$bulan}-{$tahun}.xlsx";

        return Excel::download(
            new LaporanRekapExport(
                bulan: $bulan,
                tahun: $tahun,
                namaKelas: $namaKelas,
                paralel: $paralel ? (int)$paralel : null,
                gender: $gender,
                jenis: $jenis, // ← sudah dipastikan valid
                waliKelas: $waliKelas
            ),
            $filename
        );
    }
}
