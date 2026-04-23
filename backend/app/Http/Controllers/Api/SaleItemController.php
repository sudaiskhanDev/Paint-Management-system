<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Validator;

class SaleItemController extends Controller
{
    // List all sale items
  public function index()
{
    $items = SaleItem::with([
        'product',
        'sale.customer',
        'sale.user'
    ])->get();

    return response()->json([
        'success' => true,
        'data' => $items
    ]);
}

    // Store new sale item
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sale_id' => 'required|exists:sales,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $item = SaleItem::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Sale item created successfully',
            'data' => $item
        ], 201);
    }

    // Show single item
    public function show($id)
{
    $item = SaleItem::with([
        'product',
        'sale.customer',
        'sale.user'
    ])->find($id);

    if (!$item) {
        return response()->json([
            'success' => false,
            'message' => 'Sale item not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $item
    ]);
}

    // Update item
    public function update(Request $request, $id)
    {
        $item = SaleItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Sale item not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'sale_id' => 'required|exists:sales,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $item->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Sale item updated successfully',
            'data' => $item
        ]);
    }

    // Delete item
    public function destroy($id)
    {
        $item = SaleItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Sale item not found'
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sale item deleted successfully'
        ]);
    }
}