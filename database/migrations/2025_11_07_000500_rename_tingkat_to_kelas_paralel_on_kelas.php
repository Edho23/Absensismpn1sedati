<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('kelas', 'tingkat')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->renameColumn('tingkat', 'kelas_paralel');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('kelas', 'kelas_paralel')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->renameColumn('kelas_paralel', 'tingkat');
            });
        }
    }
};
