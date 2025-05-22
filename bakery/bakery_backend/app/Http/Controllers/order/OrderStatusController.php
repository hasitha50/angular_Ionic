<?php

namespace App\Http\Controllers\order;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function index(Request $request)
    {
        $status_type = OrderStatus::get();
        return response()->json(['data' => $status_type]);
    }
}
