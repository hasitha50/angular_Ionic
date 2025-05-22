<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Variety;

class DashboardController extends Controller
{
    public function getCounts()
    {
        return response()->json([
            'products' => Product::count(),
            'categories' => Category::count(),
            'varieties' => Variety::count(),
        ]);
    }
}
