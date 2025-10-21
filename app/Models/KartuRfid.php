<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuRfid extends Model
{
    protected $table = 'kartu_rfid';

    protected $fillable = [
        'uid',
        'nis',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }
}
