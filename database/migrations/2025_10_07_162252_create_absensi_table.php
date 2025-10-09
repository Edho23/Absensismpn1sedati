<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_siswa')->constrained('siswa')->cascadeOnDelete();

            $table->date('tanggal');                             // unik per siswa per tanggal
            $table->dateTimeTz('jam_masuk')->nullable();
            $table->dateTimeTz('jam_pulang')->nullable();

            $table->boolean('terlambat')->default(false)->index();

            // PostgreSQL: enum di Laravel akan jadi CHECK constraint — aman dipakai
            $table->enum('status_harian', ['HADIR','ALPA','SAKIT'])->nullable();
            $table->enum('sumber', ['RFID','MANUAL'])->default('RFID');

            $table->string('catatan')->nullable();

            $table->foreignId('id_perangkat_masuk')->nullable()
                  ->constrained('perangkat')->nullOnDelete();
            $table->foreignId('id_perangkat_pulang')->nullable()
                  ->constrained('perangkat')->nullOnDelete();

            $table->timestampsTz();

            $table->unique(['id_siswa','tanggal']);
            $table->index(['tanggal']);                         // query per-hari lebih cepat
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
