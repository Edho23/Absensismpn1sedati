<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            // kolom grade: level kelas 7/8/9
            $table->unsignedSmallInteger('grade')->nullable()->after('kelas_paralel');
        });

        // --- OPSIONAL: Isi awal grade dari nama_kelas bila ada angka 7/8/9 di depan ---
        // Misal nama_kelas = "7A" -> grade = 7
        try {
            DB::statement("
                UPDATE kelas
                SET grade = CAST(NULLIF(REGEXP_REPLACE(nama_kelas, '^([0-9]+).*$', '\\1'), nama_kelas) AS INTEGER)
                WHERE grade IS NULL
            ");
        } catch (\Throwable $e) {
            // abaikan kalau tidak didukung PgSQL; nanti admin bisa isi manual
        }
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('grade');
        });
    }
};
