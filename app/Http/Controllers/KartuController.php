<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\{KartuRfid, Siswa};

class KartuController extends Controller
{
    /**
     * LIST: urut dari yang paling lama ke terbaru (id ASC)
     */
    public function index()
    {
        $kartu = KartuRfid::with(['siswa.kelas'])
            ->orderBy('id', 'asc')               // ← urutan lama → baru
            ->paginate(10);

        return view('kartu.index', compact('kartu'));
    }

    /**
     * CREATE: register kartu baru
     * - UID disanitasi (hapus ':', spasi, non-hex; uppercase)
     * - UID unik
     * - NIS harus ada di tabel siswa & belum punya kartu
     */
    public function store(Request $request)
    {
        // normalisasi UID: buang non-hex, uppercase
        $uidRaw = (string) $request->input('uid', '');
        $uid    = strtoupper(preg_replace('/[^0-9A-F]/', '', $uidRaw));
        $request->merge(['uid' => $uid]);

        $data = $request->validate([
            'uid' => [
                'required',
                'string',
                'max:32',
                // unik di tabel kartu_rfid
                Rule::unique('kartu_rfid', 'uid'),
            ],
            'nis' => [
                'required',
                'string',
                // siswa harus aktif
                Rule::exists('siswa', 'nis')->where(fn($q) => $q->where('status_aktif', 1)),
                // tiap NIS hanya boleh punya 1 kartu
                Rule::unique('kartu_rfid', 'nis'),
            ],
        ], [
            'uid.unique' => 'UID sudah terdaftar.',
            'nis.exists' => 'NIS tidak ditemukan / non-aktif.',
            'nis.unique' => 'Siswa ini sudah memiliki kartu.',
        ]);

        // (opsional) double guard cek manual — berguna jika index belum terpasang di DB
        if (KartuRfid::where('uid', $data['uid'])->exists()) {
            return back()->withErrors(['uid' => 'UID sudah terdaftar.'])->withInput();
        }
        if (KartuRfid::where('nis', $data['nis'])->exists()) {
            return back()->withErrors(['nis' => 'Siswa ini sudah memiliki kartu.'])->withInput();
        }

        // simpan
        KartuRfid::create([
            'uid'          => $data['uid'],
            'nis'          => $data['nis'],
            'status_aktif' => 1,
        ]);

        return back()->with('ok', 'Kartu berhasil ditambahkan.');
    }

    /**
     * DELETE: hapus kartu
     */
    public function destroy(int $id)
    {
        $k = KartuRfid::findOrFail($id);
        $k->delete();

        return back()->with('ok', 'Kartu dihapus.');
    }
}
