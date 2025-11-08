<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
{
    Schema::table('siswa', function (Blueprint $table) {
        $table->char('status', 1)->default('A')->comment('A=Aktif, N=Nonaktif')->after('nama');
        $table->char('gender', 1)->nullable()->comment('L / P')->after('status');
        $table->unsignedSmallInteger('kelas_paralel')->nullable()->comment('1..11')->after('kelas_id');
        $table->unsignedInteger('angkatan')->nullable()->comment('tahun masuk')->after('kelas_paralel');
    });

    // Jika ada kolom status_aktif lama, migrasikan ke status (A/N).
    if (Schema::hasColumn('siswa', 'status_aktif')) {
        // Postgres: boolean dibandingkan dengan IS TRUE / IS FALSE
        // Baris yang status masih NULL/empty akan diisi:
        //  - TRUE  -> 'A'
        //  - FALSE -> 'N'
        //  - NULL  -> 'A' (default dianggap aktif)
        \Illuminate\Support\Facades\DB::statement("
            UPDATE siswa
            SET status = CASE
                WHEN status_aktif IS TRUE  THEN 'A'
                WHEN status_aktif IS FALSE THEN 'N'
                ELSE 'A'
            END
            WHERE status IS NULL OR status = ''
        ");
    }
}


    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn(['status','gender','kelas_paralel','angkatan']);
        });
    }
};
