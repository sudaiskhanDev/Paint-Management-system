<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'total_amount',
        'discount',
        'payment_status',
        'sale_date',
    ];

    // Relationships
   public function user()
{
    return $this->belongsTo(\App\Models\User::class, 'user_id');
}

public function customer()
{
    return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
}
}