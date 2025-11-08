<?php

return [
    // Aturan jam produksi
    'masuk_min'            => '07:00', // minimal jam masuk
    'telat_at'             => '07:20', // mulai dianggap terlambat
    'pulang_min_weekday'   => '15:30', // Senin–Kamis
    'pulang_min_fri_sat'   => '12:00', // Jumat–Sabtu

    // Mode uji global (env). Jika true: selalu abaikan aturan jam.
    'bypass_time'          => env('ATTENDANCE_BYPASS_TIME', false),
];
