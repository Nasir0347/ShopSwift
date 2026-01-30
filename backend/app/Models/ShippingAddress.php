<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    protected $fillable = [
        'order_id', 'first_name', 'last_name', 'company',
        'address1', 'address2', 'city', 'province', 
        'country', 'zip', 'phone'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
    
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address1,
            $this->address2,
            $this->city,
            $this->province,
            $this->zip,
            $this->country,
        ]);
        
        return implode(', ', $parts);
    }
}
