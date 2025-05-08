<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Get all categories
    public function index()
    {
        $categories = Category::latest()->get();
        return response()->json([
            'data' => $categories
        ], 200);
    }

    // Create a new category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|string', 
        ]);

        $category = Category::create($request->all());

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category
        ], 201);
    }

    // Get a specific category by ID
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 422);
        }

        return response()->json([
            'data' => $category
        ], 200);
    }

    // Update an existing category
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 422);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|string', 
        ]);

        $category->update($request->all());

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $category
        ], 200);
    }

    // Delete a category
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.'
        ], 200);
    }
}
