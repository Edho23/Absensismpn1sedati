<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Siswa extends Model
{
    protected $table = 'siswa';

    // status = 'A'|'N', gender = 'L'|'P'
    protected $fillable = [
        'nis',
        'nama',
        'kelas_id',
        'status',    // 'A' | 'N'
        'gender',    // 'L' | 'P'
        'angkatan',  // YYYY (nullable)
    ];

    protected $casts = [
        'angkatan' => 'integer',
    ];

    // ===== Relasi =====
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

    // ===== Scopes bantu =====
    public function scopeAktif($q)
    {
        return $q->where('status', 'A');
    }

    public function scopeFilter($q, array $f = [])
    {
        if (!empty($f['q'])) {
            $term   = trim($f['q']);
            $kw     = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
            $driver = DB::connection()->getDriverName();
            if ($driver === 'pgsql') {
                $q->where(function ($qq) use ($kw) {
                    $qq->whereRaw('nis ILIKE ?', [$kw])
                       ->orWhereRaw('nama ILIKE ?', [$kw]);
                });
            } else {
                $q->where(function ($qq) use ($kw) {
                    $qq->where('nis', 'like', $kw)
                       ->orWhere('nama', 'like', $kw);
                });
            }
        }

        if (!empty($f['kelas_id'])) $q->where('kelas_id', $f['kelas_id']);
        if (!empty($f['gender']) && in_array($f['gender'], ['L','P'], true)) $q->where('gender', $f['gender']);
        if (!empty($f['angkatan'])) $q->where('angkatan', (int) $f['angkatan']);
        if (!empty($f['status']) && in_array($f['status'], ['A','N'], true)) $q->where('status', $f['status']);

        return $q;
    }

    // ===== Cascade: ketika status siswa berubah, kartu ikut diset =====
    protected static function booted()
    {
        static::updated(function (Siswa $s) {
            if ($s->wasChanged('status')) {
                // Jika siswa 'N' (lulus/nonaktif), kartu ikut 'N'. Jika 'A', kartu boleh aktif lagi.
                DB::table('kartu_rfid')
                    ->where('nis', $s->nis)
                    ->update([
                        'status'     => $s->status,     // 'A' | 'N'
                        'updated_at' => now(),
                    ]);
            }
        });
    }
}
