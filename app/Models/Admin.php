<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin'; // sesuai migrasi sebelumnya
    protected $fillable = ['username','password','last_login_at'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['last_login_at' => 'datetime'];
}
