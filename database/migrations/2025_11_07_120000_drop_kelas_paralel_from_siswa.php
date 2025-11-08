<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('siswa', 'kelas_paralel')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->dropColumn('kelas_paralel');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('siswa', 'kelas_paralel')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->unsignedTinyInteger('kelas_paralel')->nullable()->after('kelas_id');
            });
        }
    }
};
