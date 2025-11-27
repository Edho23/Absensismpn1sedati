<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hari_libur', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');        // tanggal referensi (tahun boleh dipakai atau diabaikan kalau berulang)
            $table->string('nama');         // nama hari libur
            $table->boolean('berulang')->default(false); // jika true → berlaku tiap tahun (match dd-mm)
            $table->timestamps();
        });

        // index bantu
        Schema::table('hari_libur', function (Blueprint $table) {
            $table->index(['tanggal','berulang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hari_libur');
    }
};
