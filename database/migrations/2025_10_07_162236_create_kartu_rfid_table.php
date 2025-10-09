<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kartu_rfid', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();                          // UID RC522
            $table->foreignId('id_siswa')->nullable()
                  ->constrained('siswa')->nullOnDelete();             // kartu bisa belum ditautkan
            $table->boolean('aktif')->default(true)->index();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_rfid');
    }
};
