<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $table = 'admin';
    protected $fillable = ['username', 'password', 'last_login_at'];
    protected $hidden = ['password'];
    protected $casts = ['last_login_at' => 'datetime'];
}
