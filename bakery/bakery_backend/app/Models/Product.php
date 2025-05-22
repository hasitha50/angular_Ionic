<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'rating',
        'image',
        'category_id',
        'seller_id',
        'default_price',
        'cut_price',
        'type',
    ];

    protected $casts = [
        'rating' => 'float',
        'default_price' => 'float',
        'cut_price' => 'float',
    ];

   
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function varieties()
    {
        return $this->hasMany(Variety::class);
    }
}
