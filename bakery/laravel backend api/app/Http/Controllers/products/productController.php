<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // Get all products
    public function index()
    {
        $products = Product::latest()->get();
        return response()->json([
            'data' => $products
        ], 200);
    }

    // Create a new product
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rating' => 'nullable|numeric|min:0|max:5',
            'image' => 'required|string',  // Image URL or base64 encoded string
            'category_id' => 'required|exists:categories,id',
            'default_price' => 'required|numeric',
            'cut_price' => 'required|numeric',
            'type' => 'required|string',
        ]);
    
        // Get the authenticated user's ID
        $sellerId = Auth::user()->id;
    
        // Create the product and set the seller_id as the authenticated user's ID
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'rating' => $request->rating,
            'image' => $request->image,
            'category_id' => $request->category_id,
            'seller_id' => $sellerId,  // Set seller_id as authenticated user's ID
            'default_price' => $request->default_price,
            'cut_price' => $request->cut_price,
            'type' => $request->type,
        ]);
    
        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product
        ], 201);
    }
    
    // Get a specific product by ID
    public function show($id)
    {
        $product = Product::with('seller', 'category')->find($id);
    
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 422);
        }
    
        return response()->json([
            'data' => $product
        ], 200);
    }
    

    // Update an existing product
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 422);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rating' => 'nullable|numeric|min:0|max:5',
            'image' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'default_price' => 'required|numeric',
            'cut_price' => 'required|numeric',
            'type' => 'required|string',
        ]);

        $product->update($request->all());

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product
        ], 200);
    }

    // Delete a product
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 422);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.'
        ], 200);
    }
}
