<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kartu_rfid', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 64)->unique();      // UID kartu, unik
            $table->string('nis', 50)->unique();      // NIS siswa, unik (satu kartu untuk satu siswa)
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            // (Opsional) Kalau mau relasi ke siswa.nis dan kolom 'nis' di siswa bertipe string & unique:
            $table->foreign('nis')->references('nis')->on('siswa')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_rfid');
    }
};
