<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Absensi,Siswa,Kelas};
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // ================== /absensi (Input manual + tabel hari ini) ==================
    public function index(Request $req)
    {
        $today = Carbon::today()->toDateString();

        // data siswa aktif utk dropdown
        $siswa = Siswa::with('kelas')
            ->where('status_aktif', true)
            ->orderBy('nama')
            ->get(['id','nis','nama','id_kelas']);

        // data absensi hari ini
        $absensi = Absensi::with('siswa.kelas')
            ->whereDate('tanggal', $today)
            ->orderByDesc('jam_masuk')
            ->paginate(20);

        // daftar kelas (kalau nanti perlu filter)
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('absensi.index', [
            'siswa'   => $siswa,
            'absensi' => $absensi,   // <- Blade kamu pakai $absensi
            'kelas'   => $kelas,
            'tanggal' => $today,
        ]);
    }

    // ================== POST /absensi/manual ==================
    public function storeManual(Request $req)
    {
        // Blade lama pakai name="id_siswa", tapi skema terbaru pakai NIS.
        // Aku dukung KEDUANYA agar lancar dipakai sekarang.
        $req->merge([
            'nis' => $req->input('nis') ?: Siswa::find($req->input('id_siswa'))?->nis
        ]);

        $data = $req->validate([
            'nis'            => 'required|exists:siswa,nis',
            'status_harian'  => 'required|in:HADIR,SAKIT,ALPA',
            'catatan'        => 'nullable|string',
            'kode_perangkat' => 'nullable|string',
        ]);

        $today = Carbon::today()->toDateString();

        $absen = Absensi::firstOrCreate(
            ['nis'=>$data['nis'], 'tanggal'=>$today],
            ['sumber'=>'MANUAL']
        );

        $absen->status_harian  = $data['status_harian'];
        $absen->catatan        = $data['catatan']        ?? $absen->catatan;
        $absen->kode_perangkat = $data['kode_perangkat'] ?? $absen->kode_perangkat;
        if ($data['status_harian'] === 'HADIR' && !$absen->jam_masuk) $absen->jam_masuk = now();
        $absen->save();

        return back()->with('ok','Input manual tersimpan');
    }

    // ================== /absensi/edit (halaman edit data) ==================
    public function edit(Request $req)
    {
        $tanggal = $req->query('tanggal', Carbon::today()->toDateString());
        $nis     = $req->query('nis');

        $q = Absensi::with('siswa.kelas')->whereDate('tanggal', $tanggal);
        if ($nis) $q->where('nis', $nis);

        $absensi = $q->orderBy('nis')->paginate(20);

        return view('absensi.edit', [
            'tanggal' => $tanggal,
            'filter_nis' => $nis,
            'absensi' => $absensi,   // <- Blade kamu pakai $absensi
        ]);
    }

    // ================== /absensi/log (log riwayat) ==================
    public function log(Request $req)
    {
        // Blade-mu menerima: $tanggal, $kelas (nama kelas), $status, $daftarKelas, $absensi
        $tanggal = $req->query('tanggal');     // jika kosong, tampilkan semua
        $kelas   = $req->query('kelas');       // nama kelas (contoh: "7A")
        $status  = $req->query('status');      // HADIR/SAKIT/ALPA

        $q = Absensi::with('siswa.kelas')->orderByDesc('tanggal')->orderByDesc('jam_masuk');

        if ($tanggal) $q->whereDate('tanggal', $tanggal);
        if ($status)  $q->where('status_harian', $status);
        if ($kelas) {
            $q->whereHas('siswa.kelas', fn($qq) => $qq->where('nama_kelas', $kelas));
        }

        $absensi = $q->paginate(20);

        $daftarKelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->pluck('nama_kelas');

        return view('absensi.log', [
            'absensi'     => $absensi,
            'tanggal'     => $tanggal,
            'kelas'       => $kelas,
            'status'      => $status,
            'daftarKelas' => $daftarKelas,
        ]);
    }

    // ================== Update & Delete untuk halaman edit ==================
    public function update(Request $req, int $id)
    {
        $data = $req->validate([
            'jam_masuk'      => 'nullable|date_format:H:i',
            'jam_pulang'     => 'nullable|date_format:H:i',
            'status_harian'  => 'nullable|in:HADIR,SAKIT,ALPA',
            'catatan'        => 'nullable|string',
        ]);

        $absen = Absensi::findOrFail($id);

        // gabungkan tanggal + jam (Blade kirim HH:mm)
        if (isset($data['jam_masuk'])) {
            $absen->jam_masuk  = $data['jam_masuk'] ? Carbon::parse($absen->tanggal.' '.$data['jam_masuk']) : null;
        }
        if (isset($data['jam_pulang'])) {
            $absen->jam_pulang = $data['jam_pulang'] ? Carbon::parse($absen->tanggal.' '.$data['jam_pulang']) : null;
        }

        if (array_key_exists('status_harian', $data)) $absen->status_harian = $data['status_harian'];
        if (array_key_exists('catatan', $data))       $absen->catatan       = $data['catatan'];

        $absen->save();

        return back()->with('ok','Absensi diupdate');
    }

    public function destroy(int $id)
    {
        Absensi::findOrFail($id)->delete();
        return back()->with('ok','Absensi dihapus');
    }
}