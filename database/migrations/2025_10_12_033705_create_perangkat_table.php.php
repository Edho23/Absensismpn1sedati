<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('perangkat', function (Blueprint $t) {
            $t->id();
            $t->string('kode_perangkat')->unique(); // contoh: GERBANG-1
            $t->string('nama')->nullable();
            $t->string('lokasi')->nullable();
            $t->boolean('aktif')->default(true)->index();
            $t->timestampsTz();
        });
    }
    public function down(): void { Schema::dropIfExists('perangkat'); }
};
