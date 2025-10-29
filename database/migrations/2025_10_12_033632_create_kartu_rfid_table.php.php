<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kartu_rfid', function (Blueprint $table) {
            $table->boolean('status_aktif')->default(true)->after('nis');
        });

        // index unik uid
        Schema::table('kartu_rfid', function (Blueprint $table) {
            $table->unique('uid');
            // $table->unique('nis'); // aktifkan jika 1 siswa hanya boleh 1 kartu
        });
    }

    public function down(): void
    {
        Schema::table('kartu_rfid', function (Blueprint $table) {
            // Hapus index unik dulu kalau mau rollback
            $table->dropUnique(['uid']);
            // $table->dropUnique(['nis']);
            $table->dropColumn('status_aktif');
        });
    }
};
