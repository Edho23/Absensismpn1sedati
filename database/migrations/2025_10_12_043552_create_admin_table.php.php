<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $t) {
            $t->id();
            $t->string('username')->unique();
            $t->string('password');                 // hash bcrypt
            $t->timestampTz('last_login_at')->nullable();
            $t->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
