<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\{Absensi, Siswa, Kelas};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    /**
     * ================== /absensi (Input manual + tabel hari ini) ==================
     * View form input manual (pakai NIS ketik + typeahead) dan tabel absensi hari ini.
     */
    public function index(Request $req)
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Tabel absensi hari ini
        $absensi = Absensi::with('siswa.kelas')
            ->whereDate('tanggal', $today)
            ->orderByDesc('jam_masuk')
            ->paginate(20);

        return view('absensi.index', [
            'absensi' => $absensi,
            'tanggal' => $today,
        ]);
    }

    /**
     * ================== POST /absensi/manual ==================
     * Simpan presensi manual berbasis NIS.
     * - Validasi: NIS wajib & siswa harus AKTIF
     * - Idempotent per hari: (nis, tanggal) unik → update or create
     * - Jika status "HADIR" dan jam_masuk kosong → set jam_masuk = now(Asia/Jakarta)
     */
    public function storeManual(Request $req)
    {
        // Normalisasi input (trim & uppercase NIS)
        $req->merge([
            'nis' => strtoupper(trim((string) $req->input('nis'))),
        ]);

        // Validasi: siswa aktif saja
        $data = $req->validate([
            'nis' => [
                'required',
                Rule::exists('siswa', 'nis')->where(fn ($q) => $q->where('status_aktif', 1)),
            ],
            'status_harian'  => ['required', Rule::in(['HADIR','SAKIT','ALPA'])],
            'catatan'        => ['nullable', 'string'],
            'kode_perangkat' => ['nullable', 'string', 'max:100'],
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'nis.exists'   => 'NIS tidak ditemukan atau siswa non-aktif.',
        ]);

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        try {
            DB::beginTransaction();

            // Ambil/buat baris absensi hari ini untuk NIS tsb
            $absen = Absensi::firstOrNew([
                'nis'     => $data['nis'],
                'tanggal' => $today,
            ]);

            // Set sumber manual & field lain
            $absen->sumber         = 'MANUAL';
            $absen->status_harian  = $data['status_harian'];
            $absen->catatan        = $data['catatan']        ?? $absen->catatan;
            $absen->kode_perangkat = $data['kode_perangkat'] ?? ($absen->kode_perangkat ?: 'MANUAL-ADMIN');

            // Jika HADIR & belum ada jam_masuk → set sekarang (WIB)
            if ($data['status_harian'] === 'HADIR' && empty($absen->jam_masuk)) {
                $absen->jam_masuk = Carbon::now('Asia/Jakarta');
            }

            // Flag terlambat TIDAK dihitung di backend (sesuai keputusan: dihitung di ESP)
            // $absen->terlambat = ... (biarkan null atau sesuai data dari perangkat nanti)

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
     * ================== /absensi/edit ==================
     * Halaman edit absensi berdasarkan tanggal/nis (opsional filter).
     */
    public function edit(Request $req)
    {
        $tanggal = $req->query('tanggal', Carbon::now('Asia/Jakarta')->toDateString());
        $nis     = $req->query('nis');

        $q = Absensi::with('siswa.kelas')->whereDate('tanggal', $tanggal);
        if ($nis) $q->where('nis', $nis);

        $absensi = $q->orderBy('nis')->paginate(20);

        return view('absensi.edit', [
            'tanggal'    => $tanggal,
            'filter_nis' => $nis,
            'absensi'    => $absensi,
        ]);
    }

    /**
     * ================== /absensi/log ==================
     * Riwayat absensi dengan filter tanggal/kelas/status.
     */
    public function log(Request $req)
    {
        $tanggal = $req->query('tanggal');     // yyyy-mm-dd
        $kelas   = $req->query('kelas');       // nama kelas
        $status  = $req->query('status');      // HADIR/SAKIT/ALPA

        $q = Absensi::with('siswa.kelas')->orderByDesc('tanggal')->orderByDesc('jam_masuk');

        if ($tanggal) $q->whereDate('tanggal', $tanggal);
        if ($status)  $q->where('status_harian', $status);
        if ($kelas) {
            $q->whereHas('siswa.kelas', fn($qq) => $qq->where('nama_kelas', $kelas));
        }

        $absensi = $q->paginate(20);

        $daftarKelas = Kelas::orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas');

        return view('absensi.log', [
            'absensi'     => $absensi,
            'tanggal'     => $tanggal,
            'kelas'       => $kelas,
            'status'      => $status,
            'daftarKelas' => $daftarKelas,
        ]);
    }

    /**
     * ================== Update data ==================
     * PUT /absensi/{id}
     * - jam_masuk/jam_pulang diinput format HH:mm → digabung dengan tanggal
     */
    public function update(Request $req, int $id)
{
    $data = $req->validate([
        'jam_masuk'      => 'nullable|date_format:H:i',
        'jam_pulang'     => 'nullable|date_format:H:i',
        'status_harian'  => 'nullable|in:HADIR,SAKIT,ALPA',
        'catatan'        => 'nullable|string',
    ]);

    $absen = Absensi::findOrFail($id);

    // Ambil tanggal base (pastikan date-only), lalu set jamnya
    $baseDate = \Carbon\Carbon::parse($absen->tanggal, 'Asia/Jakarta')->startOfDay();

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

    if (array_key_exists('status_harian', $data)) $absen->status_harian = $data['status_harian'];
    if (array_key_exists('catatan', $data))       $absen->catatan       = $data['catatan'];

    $absen->save();

    return back()->with('ok','Absensi diupdate.');
}


    /**
     * ================== Hapus data ==================
     * DELETE /absensi/{id}
     */
    public function destroy(int $id)
    {
        Absensi::findOrFail($id)->delete();
        return back()->with('ok', 'Absensi dihapus.');
    }
}
