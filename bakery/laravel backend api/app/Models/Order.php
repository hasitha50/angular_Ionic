<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['product_id','qty', 'inv_no','paid_amount','total_amount', 'order_status_id','user_id'];


    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function customer(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function product(){
        return $this->belongsTo(product::class,'product_id');
    }


}
