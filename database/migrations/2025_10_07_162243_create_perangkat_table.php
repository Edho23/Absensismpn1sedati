<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('perangkat', function (Blueprint $table) {
            $table->id();
            $table->string('kode_perangkat')->unique();   // ex: GERBANG-1
            $table->string('nama')->nullable();           // ex: Gerbang Utama
            $table->string('lokasi')->nullable();
            $table->boolean('aktif')->default(true)->index();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perangkat');
    }
};
