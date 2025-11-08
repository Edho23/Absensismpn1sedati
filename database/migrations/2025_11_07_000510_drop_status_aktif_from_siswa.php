<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('siswa', 'status_aktif')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->dropColumn('status_aktif');
            });
        }
    }

    public function down(): void
    {
        // Jika ingin rollback, kembalikan sebagai boolean default true
        if (!Schema::hasColumn('siswa', 'status_aktif')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->boolean('status_aktif')->default(true)->after('kelas_id');
            });
        }
    }
};
