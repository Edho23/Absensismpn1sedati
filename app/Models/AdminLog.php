<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLog extends Model
{
    protected $table = 'admin_logs';
    protected $fillable = ['admin_id','action','route','method','ip','user_agent','payload'];
    protected $casts = ['payload' => 'array', 'created_at' => 'datetime'];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
