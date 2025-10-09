<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Tampilkan halaman absensi (input manual / edit data)
     */
    public function index(Request $request)
    {
        $mode = $request->query('mode', 'input'); // default = input manual

        if ($mode === 'edit') {
            // MODE EDIT DATA
            $absensi = Absensi::with('siswa.kelas')
                ->orderBy('tanggal', 'desc')
                ->paginate(20);

            return view('absensi.edit', compact('absensi', 'mode'));
        }

        // MODE INPUT MANUAL (default)
        $absensi = Absensi::with('siswa.kelas')
            ->whereDate('tanggal', Carbon::today())
            ->orderBy('jam_masuk', 'desc')
            ->paginate(20);

        $siswa = Siswa::with('kelas')
            ->where('status_aktif', true)
            ->get();

        return view('absensi.index', compact('absensi', 'siswa', 'mode'));
    }

    /**
     * Simpan data absensi manual
     */
    public function storeManual(Request $req)
    {
        $data = $req->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'status_harian' => 'required|in:HADIR,SAKIT,ALPA',
            'catatan' => 'nullable|string|max:255'
        ]);

        // Cek apakah siswa sudah absen hari ini
        $record = Absensi::firstOrCreate(
            [
                'id_siswa' => $data['id_siswa'],
                'tanggal' => now()->toDateString(),
            ],
            [
                'sumber' => 'MANUAL',
            ]
        );

        // Update status & waktu masuk jika hadir
        $record->fill($data);
        if ($data['status_harian'] === 'HADIR' && !$record->jam_masuk) {
            $record->jam_masuk = now();
        }
        $record->save();

        return back()->with('ok', 'Input manual tersimpan.');
    }

    /**
     * Update data absensi dari halaman edit
     */
    public function update(Request $req, $id)
    {
        $data = $req->validate([
            'status_harian' => 'required|in:HADIR,SAKIT,ALPA',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'catatan' => 'nullable|string|max:255',
        ]);

        $absensi = Absensi::findOrFail($id);

        // Format jam masuk/pulang ke format datetime jika diisi
        if ($data['jam_masuk']) {
            $absensi->jam_masuk = Carbon::parse($absensi->tanggal . ' ' . $data['jam_masuk']);
        }

        if ($data['jam_pulang']) {
            $absensi->jam_pulang = Carbon::parse($absensi->tanggal . ' ' . $data['jam_pulang']);
        }

        $absensi->status_harian = $data['status_harian'];
        $absensi->catatan = $data['catatan'];
        $absensi->save();

        return back()->with('ok', 'Data absensi berhasil diperbarui.');
    }

    /**
     * Hapus data absensi
     */
    public function destroy($id)
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        return back()->with('ok', 'Data absensi berhasil dihapus.');
    }

    public function log(Request $request)
{
    // Filter opsional
    $kelas = $request->query('kelas');
    $status = $request->query('status');
    $tanggal = $request->query('tanggal');

    // Query dasar
    $query = Absensi::with('siswa.kelas')->orderBy('tanggal', 'desc');

    // Filter berdasarkan input pengguna
    if ($kelas) {
        $query->whereHas('siswa.kelas', function ($q) use ($kelas) {
            $q->where('nama_kelas', $kelas);
        });
    }

    if ($status) {
        $query->where('status_harian', $status);
    }

    if ($tanggal) {
        $query->whereDate('tanggal', $tanggal);
    }

    // Ambil data
    $absensi = $query->paginate(20);
    $daftarKelas = \App\Models\Kelas::pluck('nama_kelas'); // untuk filter kelas

    return view('absensi.log', compact('absensi', 'daftarKelas', 'kelas', 'status', 'tanggal'));
}





}
