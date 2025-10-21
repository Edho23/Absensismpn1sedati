<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengaturan;

class PengaturanController extends Controller
{
    public function index()
    {
        // ambil nilai atau default
        $jamMasukWeekday  = data_get(Pengaturan::get('jam_masuk_weekday', ['time' => '07:00']), 'time', '07:00');
        $graceMinutes     = data_get(Pengaturan::get('grace_minutes', ['minutes' => 5]), 'minutes', 5);
        $autoPulangWeekday= data_get(Pengaturan::get('auto_pulang_weekday', ['time' => '14:30']), 'time', '14:30');
        $autoPulangFriday = data_get(Pengaturan::get('auto_pulang_friday', ['time' => '11:00']), 'time', '11:00');

        return view('pengaturan.index', compact(
            'jamMasukWeekday', 'graceMinutes', 'autoPulangWeekday', 'autoPulangFriday'
        ));
    }

    public function update(Request $r)
    {
        $data = $r->validate([
            'jam_masuk_weekday'   => 'required|date_format:H:i',
            'grace_minutes'       => 'required|integer|min:0|max:120',
            'auto_pulang_weekday' => 'required|date_format:H:i',
            'auto_pulang_friday'  => 'required|date_format:H:i',
        ]);

        Pengaturan::put('jam_masuk_weekday',   ['time' => $data['jam_masuk_weekday']]);
        Pengaturan::put('grace_minutes',       ['minutes' => (int) $data['grace_minutes']]);
        Pengaturan::put('auto_pulang_weekday', ['time' => $data['auto_pulang_weekday']]);
        Pengaturan::put('auto_pulang_friday',  ['time' => $data['auto_pulang_friday']]);

        return back()->with('ok', 'Pengaturan disimpan.');
    }
}
