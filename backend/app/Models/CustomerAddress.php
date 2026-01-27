<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $fillable = ['user_id', 'name', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country', 'is_default'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
