<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = ['nama_kelas', 'wali_kelas'];

    public $timestamps = false; // kalau tabel kamu tidak punya created_at & updated_at
}
