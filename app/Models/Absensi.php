<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = [
        'id_siswa','tanggal','jam_masuk','jam_pulang','terlambat','status_harian',
        'sumber','catatan','id_perangkat_masuk','id_perangkat_pulang'
    ];
    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
        'terlambat' => 'boolean',
    ];
    public function siswa(): BelongsTo { return $this->belongsTo(Siswa::class, 'id_siswa'); }
}
