<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kartu_rfid', function (Blueprint $t) {
            $t->id();
            $t->string('uid')->unique();
            $t->string('nis')->nullable()->index();
            $t->boolean('aktif')->default(true)->index();
            $t->timestampsTz();

            $t->foreign('nis')->references('nis')->on('siswa')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('kartu_rfid'); }
};
