<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuRfid extends Model
{
    protected $table = 'kartu_rfid';
    protected $fillable = ['uid','id_siswa','aktif'];
    public function siswa(): BelongsTo { return $this->belongsTo(Siswa::class, 'id_siswa'); }
}
