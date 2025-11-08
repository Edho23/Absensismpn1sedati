<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void
    {
        Schema::table('kartu_rfid', function (Blueprint $table) {
            // Hapus FK lama (nama default laravel: kartu_rfid_nis_foreign)
            $table->dropForeign('kartu_rfid_nis_foreign');

            // Buat ulang dengan cascade
            $table->foreign('nis')
                ->references('nis')->on('siswa')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('kartu_rfid', function (Blueprint $table) {
            $table->dropForeign('kartu_rfid_nis_foreign');

            $table->foreign('nis')
                ->references('nis')->on('siswa');
            // tanpa cascade (kembali seperti semula)
        });
    }
};
