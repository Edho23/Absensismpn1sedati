<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('absensi', function (Blueprint $t) {
            $t->id();

            $t->string('nis')->index();                 // refer ke siswa.nis
            $t->date('tanggal');
            $t->dateTimeTz('jam_masuk')->nullable();
            $t->dateTimeTz('jam_pulang')->nullable();

            $t->boolean('terlambat')->default(false)->index();
            $t->enum('status_harian', ['HADIR','ALPA','SAKIT'])->nullable();
            $t->enum('sumber', ['RFID','MANUAL'])->default('RFID');
            $t->string('catatan')->nullable();

            // simplified: satu kolom string untuk identitas perangkat
            $t->string('kode_perangkat')->nullable();

            $t->timestampsTz();

            $t->unique(['nis','tanggal']);
            $t->foreign('nis')->references('nis')->on('siswa')->cascadeOnDelete();
            $t->index(['tanggal']);
        });
    }
    public function down(): void { Schema::dropIfExists('absensi'); }
};
