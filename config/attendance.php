<?php
// config/attendance.php

return [

    // Zona waktu server Laravel (pastikan APP_TIMEZONE di .env sudah benar, ex: Asia/Jakarta)
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    // Durasi anti-duplicate tap (detik). Kalau tap beruntun < window ini, dianggap duplikat.
    'duplicate_window' => env('ATT_DUPLICATE_WINDOW', 8),

    // Jadwal default (boleh diubah dari .env khususnya jam)
    // Format jam "HH:MM" (24 jam)
    'schedule' => [
        // Senin–Kamis
        'mon_thu' => [
            'jam_masuk'      => env('ATT_MONTHU_START', '07:00'),
            'grace_minutes'  => env('ATT_MONTHU_GRACE', 5),   // telat kalau lewat start + grace
            'jam_pulang'     => env('ATT_MONTHU_END', '14:15'),
            'auto_pulang_at' => env('ATT_MONTHU_AUTO', '14:30'),
        ],
        // Jumat–Sabtu
        'fri_sat' => [
            'jam_masuk'      => env('ATT_FRISAT_START', '07:00'),
            'grace_minutes'  => env('ATT_FRISAT_GRACE', 5),
            'jam_pulang'     => env('ATT_FRISAT_END', '10:50'),
            'auto_pulang_at' => env('ATT_FRISAT_AUTO', '11:00'),
        ],
    ],

    // Apakah izinkan checkout manual (tap kedua) di perangkat?
    'allow_checkout' => env('ATT_ALLOW_CHECKOUT', true),
];
