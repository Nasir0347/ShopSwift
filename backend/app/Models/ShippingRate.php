<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_zone_id',
        'name',
        'type',
        'price',
        'min_limit',
        'max_limit',
    ];

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class);
    }
}
