<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'brand_id',
        'size',
        'price',
        'image', // NEW
        'stock'
    ];

    // Relations
    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(\App\Models\Brand::class);
    }
}