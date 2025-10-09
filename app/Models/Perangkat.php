<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Perangkat extends Model
{
    protected $table = 'perangkat';
    protected $fillable = ['kode_perangkat','nama','lokasi','aktif'];
}
