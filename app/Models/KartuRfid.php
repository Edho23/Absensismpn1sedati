<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuRfid extends Model
{
    protected $table = 'kartu_rfid';

    // Sudah bersih: hanya pakai 'status'
    protected $fillable = [
        'uid',
        'nis',
        'status',   // 'A' | 'N'
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    /** Accessor label untuk badge di view */
    public function getStatusTextAttribute(): string
    {
        return $this->status === 'A' ? 'Aktif' : 'Nonaktif';
    }

    /** Scope cepat ambil kartu aktif */
    public function scopeAktif($q)
    {
        return $q->where('status', 'A');
    }
}
