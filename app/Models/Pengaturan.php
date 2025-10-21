<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = ['kunci', 'nilai'];

    protected $casts = [
        'nilai' => 'array',
    ];

    public static function get(string $key, $default = null)
    {
        $row = static::where('kunci', $key)->first();
        return $row?->nilai ?? $default;
    }

    public static function put(string $key, $value): self
    {
        return static::updateOrCreate(
            ['kunci' => $key],
            ['nilai' => is_array($value) ? $value : (array) $value]
        );
    }
}
