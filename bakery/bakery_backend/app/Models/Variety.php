<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variety extends Model
{
    protected $fillable = [
        'unit',
        'quantity',
        'price',
        'is_default',
        'product_id', 
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'quantity' => 'float',
        'price' => 'float',
    ];

   
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
