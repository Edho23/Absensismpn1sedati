<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas');                 // contoh: 7A
            $table->unsignedTinyInteger('tingkat');       // 7/8/9
            $table->string('wali_kelas')->nullable();     // opsional
            $table->timestampsTz();

            $table->unique(['nama_kelas','tingkat']);     // kombinasi unik
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
