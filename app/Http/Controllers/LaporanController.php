<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // ✅ untuk cek kolom tabel
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanRekapExport;

class LaporanController extends Controller
{
    /**
     * Tentukan nama kolom gender di tabel siswa secara dinamis.
     * Mengembalikan null bila tidak ada kolom yang cocok.
     */
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

    /**
     * Terapkan filter gender ke relasi siswa jika kolomnya tersedia.
     * $gender: 'L' atau 'P'
     */
    private function applyGenderFilterToSiswaRelation($relationQuery, ?string $gender): void
    {
        if (!$gender) return;

        $col = $this->genderColumnName();
        if (!$col) return; // kolom gender tidak ada → jangan terapkan filter

        if ($gender === 'L') {
            $relationQuery->whereIn(
                DB::raw("UPPER($col)"),
                ['L', 'LAKI', 'LAKI-LAKI']
            );
        } elseif ($gender === 'P') {
            $relationQuery->whereIn(
                DB::raw("UPPER($col)"),
                ['P', 'PEREMPUAN']
            );
        }
    }

    /**
     * Halaman utama laporan kehadiran (dengan filter rentang tanggal + kelas + status + gender)
     */
    public function index(Request $request)
    {
        // Ambil parameter filter
        $tanggalMulai   = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');
        $kelas          = $request->query('kelas');
        $status         = $request->query('status');
        $gender         = $request->query('gender'); // 'L' / 'P' (opsional)
        $mode           = $request->query('mode', 'detail'); // 'detail' atau 'rekap'

        // Query utama
        $query = Absensi::with('siswa.kelas')->orderByDesc('tanggal')->orderByDesc('jam_masuk');

        // Filter tanggal range / single
        if ($tanggalMulai && $tanggalSelesai) {
            $query->whereBetween(DB::raw('DATE(tanggal)'), [$tanggalMulai, $tanggalSelesai]);
        } elseif ($tanggalMulai) {
            $query->whereDate('tanggal', $tanggalMulai);
        } elseif ($tanggalSelesai) {
            $query->whereDate('tanggal', $tanggalSelesai);
        }

        // Filter kelas (nama kelas)
        if ($kelas) {
            $query->whereHas('siswa.kelas', fn($q) => $q->where('nama_kelas', $kelas));
        }

        // Filter status (mode detail saja, tapi aman jika terisi)
        if ($status) {
            $query->where('status_harian', $status);
        }

        // ✅ Filter gender (hanya jika kolom gender ada)
        if ($gender) {
            $query->whereHas('siswa', function ($q) use ($gender) {
                $this->applyGenderFilterToSiswaRelation($q, $gender);
            });
        }

        if ($mode === 'rekap') {
            // Rekap per siswa dalam rentang/filter
            $rekap = $query->clone()
                ->select([
                    'nis',
                    DB::raw("MAX((SELECT nama FROM siswa s WHERE s.nis = absensi.nis)) AS nama"),
                    DB::raw("MAX((SELECT nama_kelas FROM kelas k JOIN siswa s2 ON s2.id_kelas=k.id WHERE s2.nis = absensi.nis)) AS nama_kelas"),
                    DB::raw("SUM(CASE WHEN status_harian='HADIR' THEN 1 ELSE 0 END) AS hadir"),
                    DB::raw("SUM(CASE WHEN status_harian='SAKIT' THEN 1 ELSE 0 END) AS sakit"),
                    DB::raw("SUM(CASE WHEN status_harian='IZIN'  THEN 1 ELSE 0 END) AS izin"),
                    DB::raw("SUM(CASE WHEN status_harian='ALPA'  THEN 1 ELSE 0 END) AS alpa"),
                    DB::raw("COUNT(*) AS total"),
                ])
                ->groupBy('nis')
                ->orderBy('nama_kelas')
                ->orderBy('nama')
                ->paginate(20);

            // Daftar kelas untuk dropdown (tanpa 'tingkat' agar aman di skema apa pun)
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

        // Mode detail
        $absensi = $query->paginate(20);

        // Daftar kelas untuk dropdown
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

    /**
     * Export rekap (menghormati filter: rentang tanggal, kelas, gender)
     */
    public function export(Request $request)
    {
        $mulai   = $request->query('tanggal_mulai');
        $selesai = $request->query('tanggal_selesai');
        $kelas   = $request->query('kelas');   // nama_kelas
        $gender  = $request->query('gender');  // 'L' / 'P' (opsional)

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

        // ✅ Filter gender (hanya jika kolom gender ada)
        if ($gender) {
            $base->whereHas('siswa', function ($q) use ($gender) {
                $this->applyGenderFilterToSiswaRelation($q, $gender);
            });
            $kelasText .= $gender === 'L' ? ' | Gender: Laki-laki' : ' | Gender: Perempuan';
        }

        // Rekap per status
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
