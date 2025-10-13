<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuRfid extends Model
{
    protected $table = 'kartu_rfid';
    protected $fillable = ['uid', 'nis', 'status_aktif'];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }
}
