<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $fillable = ['nis','nama','kelas_id','status_aktif'];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function kartu(): HasOne
    {
        return $this->hasOne(KartuRfid::class, 'nis', 'nis');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'nis', 'nis');
    }
}
