<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kelas', function (Blueprint $t) {
            $t->id();
            $t->string('nama_kelas');
            $t->unsignedTinyInteger('tingkat');
            $t->string('wali_kelas')->nullable();
            $t->timestampsTz();
            $t->unique(['nama_kelas','tingkat']);
        });
    }
    public function down(): void { Schema::dropIfExists('kelas'); }
};
