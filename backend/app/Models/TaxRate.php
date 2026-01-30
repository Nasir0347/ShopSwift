<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rate',
        'country_code',
        'region_code',
        'is_compound',
        'is_shipping_tax',
        'priority',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_compound' => 'boolean',
        'is_shipping_tax' => 'boolean',
    ];
}
