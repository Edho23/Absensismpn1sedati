<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $fillable = ['nisn','nama','id_kelas','status_aktif'];

    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class, 'id_kelas'); }
    public function kartu(): HasOne { return $this->hasOne(KartuRfid::class, 'id_siswa'); }
    public function absensi(): HasMany { return $this->hasMany(Absensi::class, 'id_siswa'); }
}
