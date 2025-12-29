<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\{Absensi, Siswa, Kelas};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AbsensiController extends Controller
{
    public function index(Request $req)
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $absensi = Absensi::with('siswa.kelas')
            ->whereDate('tanggal', $today)
            ->orderByDesc('jam_masuk')
            ->paginate(20);

        return view('absensi.index', [
            'absensi' => $absensi,
            'tanggal' => $today,
        ]);
    }

    public function storeManual(Request $req)
    {
        $req->merge(['nis' => strtoupper(trim((string) $req->input('nis')))]);

        $data = $req->validate([
            'nis' => [
                'required',
                Rule::exists('siswa', 'nis')->where(fn ($q) => $q->where('status', 'A')),
            ],
            'status_harian'  => ['required', Rule::in(['HADIR','SAKIT','ALPA','IZIN'])],
            'catatan'        => ['nullable', 'string'],
            'kode_perangkat' => ['nullable', 'string', 'max:100'],
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'nis.exists'   => 'NIS tidak ditemukan atau siswa non-aktif.',
        ]);

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        try {
            DB::beginTransaction();

            $absen = Absensi::firstOrNew([
                'nis'     => $data['nis'],
                'tanggal' => $today,
            ]);

            $absen->sumber         = 'MANUAL';
            $absen->status_harian  = $data['status_harian'];
            $absen->catatan        = $data['catatan']        ?? $absen->catatan;
            $absen->kode_perangkat = $data['kode_perangkat'] ?? ($absen->kode_perangkat ?: 'MANUAL-ADMIN');

            if ($data['status_harian'] === 'HADIR' && empty($absen->jam_masuk)) {
                $absen->jam_masuk = Carbon::now('Asia/Jakarta');
            }

            $absen->save();
            DB::commit();

            return back()->with('ok', 'Presensi manual tersimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['gagal' => 'Terjadi kesalahan saat menyimpan.'])->withInput();
        }
    }

    /**
     * Halaman Edit: ada filter Tanggal, Status, Kelas, Paralel, Gender, NIS/Nama (q)
     */
    public function edit(Request $req)
    {
        $tanggal     = $req->query('tanggal', Carbon::now('Asia/Jakarta')->toDateString());
        $status      = $req->query('status');           // HADIR/SAKIT/IZIN/ALPA
        $namaKelas   = $req->query('kelas');            // VII/VIII/IX
        $paralel     = $req->query('kelas_paralel');    // 1..n
        $gender      = $req->query('gender');           // L/P
        $qterm       = trim((string) $req->query('q'));

        $q = Absensi::with('siswa.kelas')
            ->whereDate('tanggal', $tanggal)
            ->when($status,   fn($qq) => $qq->where('status_harian', $status))
            ->when($namaKelas || $paralel, function ($qq) use ($namaKelas, $paralel) {
                $qq->whereHas('siswa.kelas', function ($kq) use ($namaKelas, $paralel) {
                    if ($namaKelas) $kq->where('nama_kelas', $namaKelas);
                    if ($paralel)   $kq->where('kelas_paralel', (int)$paralel);
                });
            })
            ->when(in_array($gender, ['L','P'], true), fn($qq) =>
                $qq->whereHas('siswa', fn($sq) => $sq->where('gender', $gender))
            )
            ->when($qterm !== '', function ($qq) use ($qterm) {
                $kw = '%'.str_replace(['%','_'],['\\%','\\_'],$qterm).'%';
                $qq->where(function ($zzz) use ($kw) {
                    $zzz->whereHas('siswa', fn($sq) => $sq->where('nis', 'like', $kw)
                                                         ->orWhere('nama','like',$kw));
                });
            })
            ->orderBy('nis');

        $absensi = $q->paginate(25)->withQueryString();

        $daftarKelas = Kelas::select('nama_kelas')
        ->distinct()
        ->get()
        ->sortBy(function ($row) {
            // Urutan khusus: VII, VIII, IX, lainnya setelah itu
            $order = [
                'VII'  => 1,
                'VIII' => 2,
                'IX'   => 3,
            ];
            return $order[$row->nama_kelas] ?? 4;
        })
        ->pluck('nama_kelas');
        $daftarParalel = Kelas::select('kelas_paralel')->distinct()->orderBy('kelas_paralel')->pluck('kelas_paralel');

        return view('absensi.edit', [
            'tanggal'       => $tanggal,
            'filter_status' => $status,
            'kelas'         => $namaKelas,
            'kelasParalel'  => $paralel,
            'gender'        => $gender,
            'qterm'         => $qterm,
            'absensi'       => $absensi,
            'daftarKelas'   => $daftarKelas,
            'daftarParalel' => $daftarParalel,
        ]);
    }

    /**
     * LOG ABSENSI + FILTER
     * - tanggal (default: hari ini jika kosong)
     * - kelas  : dikirim sebagai "nama_kelas|kelas_paralel" (contoh "VIII|2")
     * - status : HADIR/SAKIT/IZIN/ALPA
     */
    public function log(Request $req)
    {
        $tanggal = $req->query('tanggal');
        if (!$tanggal) {
            $tanggal = Carbon::now('Asia/Jakarta')->toDateString();
        }

        // "VII|1", "VIII|2", dst
        $kelasParam = $req->query('kelas');
        $status     = $req->query('status');

        $q = Absensi::with('siswa.kelas')
            ->whereDate('tanggal', $tanggal);

        // Filter status harian
        if (in_array($status, ['HADIR','SAKIT','IZIN','ALPA'], true)) {
            $q->where('status_harian', $status);
        }

        // Filter kelas (nama_kelas + kelas_paralel)
        if ($kelasParam) {
            [$namaKelas, $paralel] = array_pad(explode('|', $kelasParam, 2), 2, null);

            if ($namaKelas) {
                $q->whereHas('siswa.kelas', function ($qq) use ($namaKelas, $paralel) {
                    $qq->where('nama_kelas', $namaKelas);
                    if ($paralel !== null && $paralel !== '') {
                        $qq->where('kelas_paralel', $paralel);
                    }
                });
            }
        }

        $absensi = $q->orderByDesc('tanggal')
            ->orderByDesc('jam_masuk')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // ===== Dropdown Kelas: gabungan nama_kelas + kelas_paralel =====
        $kelasRows = Kelas::select('nama_kelas', 'kelas_paralel')
            ->when(
                Schema::hasColumn('kelas', 'grade'),
                fn($qq) => $qq->orderBy('grade')
            )
            ->when(
                !Schema::hasColumn('kelas', 'grade') && Schema::hasColumn('kelas', 'tingkat'),
                fn($qq) => $qq->orderBy('tingkat')
            )
            ->orderBy('nama_kelas')
            ->orderBy('kelas_paralel')
            ->get()
            ->unique(function ($row) {
                return $row->nama_kelas . '|' . $row->kelas_paralel;
            })
            ->values();

        // Bentuk: [ ['value' => 'VII|1', 'label' => 'VII - 1'], ... ]
        $daftarKelas = $kelasRows->map(function ($row) {
            return [
                'value' => $row->nama_kelas . '|' . $row->kelas_paralel,
                'label' => $row->nama_kelas . ' - ' . $row->kelas_paralel,
            ];
        });

        return view('absensi.log', [
            'absensi'     => $absensi,
            'tanggal'     => $tanggal,
            'kelas'       => $kelasParam,
            'status'      => $status,
            'daftarKelas' => $daftarKelas,
        ]);
    }

    /**
     * ✅ PING untuk auto refresh halaman LOG (polling)
     * GET /absensi/log/ping?tanggal=...&kelas=...&status=...
     * kelas di sini juga format "nama_kelas|kelas_paralel"
     */
    public function logPing(Request $req)
    {
        $tanggal = $req->query('tanggal');
        if (!$tanggal) {
            $tanggal = Carbon::now('Asia/Jakarta')->toDateString();
        }

        $kelasParam = $req->query('kelas');
        $status     = $req->query('status');

        $q = Absensi::query()
            ->whereDate('tanggal', $tanggal);

        if (in_array($status, ['HADIR','SAKIT','IZIN','ALPA'], true)) {
            $q->where('status_harian', $status);
        }

        if ($kelasParam) {
            [$namaKelas, $paralel] = array_pad(explode('|', $kelasParam, 2), 2, null);

            if ($namaKelas) {
                $q->whereHas('siswa.kelas', function ($qq) use ($namaKelas, $paralel) {
                    $qq->where('nama_kelas', $namaKelas);
                    if ($paralel !== null && $paralel !== '') {
                        $qq->where('kelas_paralel', $paralel);
                    }
                });
            }
        }

        // pakai updated_at supaya detect perubahan status/catatan/jam
        $latest = $q->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->first(['id', 'updated_at']);

        return response()->json([
            'latest_id'         => $latest?->id,
            'latest_updated_at' => $latest?->updated_at?->toIso8601String(),
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
        ]);
    }

    public function update(Request $req, $id)
    {
        $data = $req->validate([
            'jam_masuk'      => 'nullable|date_format:H:i',
            'jam_pulang'     => 'nullable|date_format:H:i',
            'status_harian'  => 'nullable|in:HADIR,SAKIT,ALPA,IZIN',
            'catatan'        => 'nullable|string',
        ]);

        $absen = Absensi::findOrFail($id);

        $baseDate = Carbon::parse($absen->tanggal, 'Asia/Jakarta')->startOfDay();

        if (array_key_exists('jam_masuk', $data)) {
            $absen->jam_masuk = $data['jam_masuk']
                ? (clone $baseDate)->setTimeFromTimeString($data['jam_masuk'])
                : null;
        }

        if (array_key_exists('jam_pulang', $data)) {
            $absen->jam_pulang = $data['jam_pulang']
                ? (clone $baseDate)->setTimeFromTimeString($data['jam_pulang'])
                : null;
        }

        if (array_key_exists('status_harian', $data)) {
            $absen->status_harian = $data['status_harian'];
        }

        if (array_key_exists('catatan', $data)) {
            $absen->catatan = $data['catatan'];
        }

        $absen->save();

        return back()->with('ok', 'Absensi berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        Absensi::findOrFail($id)->delete();
        return back()->with('ok', 'Absensi dihapus.');
    }

    public function bulkUpdate(Request $req)
    {
        $data = $req->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:absensi,id',
            'status' => 'required|in:HADIR,SAKIT,IZIN,ALPA',
        ]);

        Absensi::whereIn('id', $data['ids'])->update([
            'status_harian' => $data['status'],
            'updated_at'    => now(),
        ]);

        return back()->with('ok', 'Status '.$data['status'].' diterapkan ke '.count($data['ids']).' baris.');
    }

    public function inlineUpdate(Request $req)
    {
        $data = $req->validate([
            'id'     => 'required|integer|exists:absensi,id',
            'status' => 'required|in:HADIR,SAKIT,IZIN,ALPA',
        ]);

        $absen = Absensi::findOrFail($data['id']);
        $absen->status_harian = $data['status'];
        $absen->save();

        return response()->json(['ok' => true]);
    }
}
