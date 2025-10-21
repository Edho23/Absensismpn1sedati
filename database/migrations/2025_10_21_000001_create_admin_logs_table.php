<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_logs', function (Blueprint $t) {
            $t->id();
            // referensi ke tabel 'admin' (username/password) — pastikan tabel 'admin' sudah ada
            $t->foreignId('admin_id')->constrained('admin')->cascadeOnDelete();
            $t->string('action')->nullable();       // ex: absensi.manual.store, siswa.destroy
            $t->string('route')->nullable();        // path atau route name
            $t->string('method', 10)->nullable();   // GET/POST/PUT/DELETE
            $t->string('ip', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->json('payload')->nullable();        // body tanpa field sensitif
            $t->timestampsTz();
            $t->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};
