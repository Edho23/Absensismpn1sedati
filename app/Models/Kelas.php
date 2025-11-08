<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas';

    // Kolom yang dipakai aplikasi
    protected $fillable = [
        'nama_kelas',
        'wali_kelas',
        'kelas_paralel', // 1..11
        'grade',         // 7/8/9
    ];

    // Tabelmu punya created_at & updated_at → biarkan timestamps aktif (default = true)

    protected $casts = [
        'kelas_paralel' => 'integer',
        'grade'         => 'integer',
    ];

    /** Relasi: satu kelas memiliki banyak siswa */
    public function siswa(): HasMany
    {
        return $this->hasMany(\App\Models\Siswa::class, 'kelas_id');
    }

    /** Scopes bantu (opsional) */
    public function scopeWithGrade($q, int $g)   { return $q->where('grade', $g); }
    public function scopeWithParalel($q, int $p) { return $q->where('kelas_paralel', $p); }
    public function scopeSorted($q)
    {
        return $q->orderBy('grade')
                 ->orderBy('kelas_paralel')
                 ->orderBy('nama_kelas');
    }
}
