module.exports = {
    apps: [
        {
            name: "absensi-scheduler",
            cwd: "C:/laragon/www/Absensismpn1sedati", // path absolut project
            script: "artisan", // jalankan artisan langsung
            args: "schedule:work", // daemon scheduler Laravel
            interpreter: "C:/laragon/bin/php/php-8.2.29-Win32-vs16-x64/php.exe", // atau isi full path php.exe (lihat catatan)
            watch: false,
            autorestart: true,
            max_restarts: 10,
            windowsHide: true, // ⬅️ cegah jendela cmd muncul
            env: {
                APP_ENV: "production",
                APP_DEBUG: "false",
                // optional: sembunyikan warning Node lama
                NODE_NO_WARNINGS: "1",
            },
            error_file: "storage/logs/pm2-scheduler-err.log",
            out_file: "storage/logs/pm2-scheduler-out.log",
            merge_logs: true,
            max_memory_restart: "256M",
        },
    ],
};
