<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\{KartuRfid, Siswa};

class KartuController extends Controller
{
    /** LIST */
    public function index()
    {
        $kartu = KartuRfid::with(['siswa.kelas'])
            ->orderBy('id', 'asc')
            ->paginate(10);

        return view('kartu.index', compact('kartu'));
    }

    /** CREATE */
    public function store(Request $request)
    {
        // Normalisasi UID: buang non-hex, uppercase
        $uidRaw = (string) $request->input('uid', '');
        $uid    = strtoupper(preg_replace('/[^0-9A-F]/', '', $uidRaw));
        $request->merge(['uid' => $uid]);

        $data = $request->validate([
            'uid' => [
                'required',
                'string',
                'max:32',
                Rule::unique('kartu_rfid', 'uid'),
            ],
            'nis' => [
                'required',
                'string',
                // Siswa harus aktif (status = 'A')
                Rule::exists('siswa', 'nis')->where(fn($q) => $q->where('status', 'A')),
                // Tiap NIS hanya boleh punya 1 kartu aktif (atau 1 kartu total jika kebijakan 1 kartu)
                Rule::unique('kartu_rfid', 'nis'),
            ],
        ], [
            'uid.unique' => 'UID sudah terdaftar.',
            'nis.exists' => 'NIS tidak ditemukan / non-aktif.',
            'nis.unique' => 'Siswa ini sudah memiliki kartu.',
        ]);

        KartuRfid::create([
            'uid'      => $data['uid'],
            'nis'      => $data['nis'],
            'status'   => 'A', // aktif
        ]);

        return back()->with('ok', 'Kartu berhasil ditambahkan.');
    }

    /** DELETE */
    public function destroy(int $id)
    {
        $k = KartuRfid::findOrFail($id);
        $k->delete();

        return back()->with('ok', 'Kartu dihapus.');
    }
}
