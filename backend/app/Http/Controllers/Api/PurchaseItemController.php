<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseItem;
use App\Models\Purchase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PurchaseItemController extends Controller
{
    public function index()
    {
        $items = PurchaseItem::with(['purchase','product'])->get();
        return response()->json($items);
    }

 public function store(Request $request)
{
    $request->validate([
        'purchase_id' => 'required|exists:purchases,id',
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();

    try {

        // 1. CREATE PURCHASE ITEM
        $item = PurchaseItem::create([
            'purchase_id' => $request->purchase_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'price' => $request->price,
        ]);

        // 2. UPDATE PURCHASE TOTAL
        $this->updatePurchaseTotal($request->purchase_id);

        // 3. UPDATE PRODUCT STOCK (+ add quantity)
        $product = \App\Models\Product::find($request->product_id);

        if ($product) {
            $product->stock = ($product->stock ?? 0) + $request->quantity;
            $product->save();
        }

        DB::commit();

        return response()->json([
            'message' => 'Purchase item saved + stock updated',
            'data' => $item
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function show($id)
    {
        $item = PurchaseItem::with(['purchase','product'])->find($id);
        if (!$item) {
            return response()->json(['message' => 'Purchase item not found'], 404);
        }

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = PurchaseItem::find($id);
        if (!$item) {
            return response()->json(['message' => 'Purchase item not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'purchase_id' => 'required|exists:purchases,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $item->update($request->all());

        // 🔥 FIX: UPDATE TOTAL AFTER UPDATE
        $this->updatePurchaseTotal($item->purchase_id);

        return response()->json([
            'message' => 'Purchase item updated successfully',
            'item' => $item
        ]);
    }

    public function destroy($id)
    {
        $item = PurchaseItem::find($id);
        if (!$item) {
            return response()->json(['message' => 'Purchase item not found'], 404);
        }

        $purchase_id = $item->purchase_id;

        $item->delete();

        // 🔥 FIX: UPDATE TOTAL AFTER DELETE
        $this->updatePurchaseTotal($purchase_id);

        return response()->json(['message' => 'Purchase item deleted successfully']);
    }

    // 🔥 CORE FIX FUNCTION
    private function updatePurchaseTotal($purchase_id)
    {
        $total = PurchaseItem::where('purchase_id', $purchase_id)
            ->sum(DB::raw('quantity * price'));

        Purchase::where('id', $purchase_id)
            ->update([
                'total_amount' => $total
            ]);
    }
}
