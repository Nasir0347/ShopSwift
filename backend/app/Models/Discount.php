<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = ['code', 'type', 'value', 'usage_limit', 'used_count', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
