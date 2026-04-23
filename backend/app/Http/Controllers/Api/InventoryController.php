<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseItem;

class InventoryController extends Controller
{
  public function index()
{
    $products = Product::all();

    $inventory = $products->map(function ($product) {

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock ?? 0,   // 🔥 FIX HERE
            'image' => $product->image, // ✅ IMPORTANT
        ];
    });

    return response()->json($inventory);
}


    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'batch_number' => 'nullable|string',
        ]);

        $inventory = Inventory::create($request->all());

        return response()->json([
            'message' => 'Inventory created successfully',
            'data' => $inventory
        ]);
    }

    // SHOW
    public function show($id)
    {
        return Inventory::with(['product.category', 'product.brand'])->findOrFail($id);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'batch_number' => 'nullable|string',
        ]);

        $inventory->update($request->all());

        return response()->json([
            'message' => 'Inventory updated successfully',
            'data' => $inventory
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        Inventory::destroy($id);

        return response()->json([
            'message' => 'Inventory deleted successfully'
        ]);
    }
}