<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Variety;
use App\Models\Product;
use Illuminate\Http\Request;

class VarietyController extends Controller
{
    // Get all varieties
    public function index()
    {
        $varieties = Variety::latest()->get();
        return response()->json([
            'data' => $varieties
        ], 200);
    }

    // Store a new variety
    public function store(Request $request)
    {
        $request->validate([
            'unit' => 'required|string|max:255'
        ]);

        $variety = Variety::create($request->all());

        return response()->json([
            'message' => 'Variety created successfully.',
            'data' => $variety
        ], 201);
    }

    // Get variety details by id
    public function show($id)
    {
        $variety = Variety::find($id);
        if (!$variety) {
            return response()->json(['message' => 'Variety not found'], 422);
        }

        return response()->json([
            'data' => $variety
        ], 200);
    }

    // Update an existing variety
    public function update(Request $request, $id)
    {
        $variety = Variety::find($id);

        if (!$variety) {
            return response()->json(['message' => 'Variety not found'], 422);
        }

        $request->validate([
            'unit' => 'required|string|max:255',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'is_default' => 'required|boolean',
            'product_id' => 'required|exists:products,id',
        ]);

        $variety->update($request->all());

        return response()->json([
            'message' => 'Variety updated successfully.',
            'data' => $variety
        ], 200);
    }

    // Delete a variety
    public function destroy($id)
    {
        $variety = Variety::find($id);

        if (!$variety) {
            return response()->json(['message' => 'Variety not found'], 422);
        }

        $variety->delete();

        return response()->json([
            'message' => 'Variety deleted successfully.'
        ], 200);
    }
}
