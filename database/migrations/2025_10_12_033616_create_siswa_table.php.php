<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('siswa', function (Blueprint $t) {
            $t->id();
            $t->string('nis')->unique();
            $t->string('nama');
            $t->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $t->boolean('status_aktif')->default(true)->index();
            $t->timestampsTz();
        });
    }
    public function down(): void { Schema::dropIfExists('siswa'); }
};
