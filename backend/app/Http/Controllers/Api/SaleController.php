<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    // LIST
    public function index()
    {
        $sales = Sale::with(['customer','user'])->get();
        return response()->json($sales);
    }

    // STORE (FIXED)
    public function store(Request $request)
{
    DB::beginTransaction();

    try {

        // ======================
        // VALIDATION
        // ======================
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_status' => 'required|in:pending,paid,partial',
            'sale_date' => 'required|date',
        ]);

        // ======================
        // CUSTOMER HANDLING
        // ======================
        $customer_id = $request->customer_id;

        if (!$customer_id && $request->new_customer_name) {

            $customer = \App\Models\Customer::create([
                'name' => $request->new_customer_name,
                'phone' => $request->new_customer_phone ?? null,
                'address' => $request->new_customer_address ?? null,
            ]);

            $customer_id = $customer->id;
        }

        if (!$customer_id) {
            return response()->json([
                'message' => 'Customer is required'
            ], 422);
        }

        // ======================
        // CREATE SALE (TEMP)
        // ======================
        $sale = \App\Models\Sale::create([
            'customer_id' => $customer_id,
            'user_id' => auth()->id(),
            'total_amount' => 0,
            'discount' => $request->discount ?? 0,
            'payment_status' => $request->payment_status,
            'sale_date' => $request->sale_date,
        ]);

        $total = 0;

        // ======================
        // PROCESS ITEMS
        // ======================
        foreach ($request->items as $item) {

            // 🔥 GET PRODUCT
            $product = \App\Models\Product::find($item['product_id']);

            if (!$product) {
                throw new \Exception("Product not found: " . $item['product_id']);
            }

            // ❗ STOCK CHECK
            if ($product->stock < $item['quantity']) {
                throw new \Exception(
                    "Not enough stock for Product ID {$item['product_id']} | Available: {$product->stock}"
                );
            }

            // ======================
            // CREATE SALE ITEM
            // ======================
            \App\Models\SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);

            // ======================
            // TOTAL CALCULATION
            // ======================
            $total += $item['quantity'] * $item['price'];

            // ======================
            // STOCK MINUS (IMPORTANT)
            // ======================
            $product->stock -= $item['quantity'];
            $product->save();
        }

        // ======================
        // DISCOUNT APPLY
        // ======================
        $discount = $request->discount ?? 0;
        $finalTotal = $total - ($total * $discount / 100);

        // ======================
        // UPDATE SALE TOTAL
        // ======================
        $sale->update([
            'total_amount' => $finalTotal
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'total' => $finalTotal,
            'message' => 'Sale created successfully'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
// public function store(Request $request)
// {
//     DB::beginTransaction();

//     try {

//         // ======================
//         // CUSTOMER HANDLING
//         // ======================
//         $customer_id = $request->customer_id;

//         if (!$customer_id && $request->new_customer_name) {

//             $customer = \App\Models\Customer::create([
//                 'name' => $request->new_customer_name,
//                 'phone' => $request->new_customer_phone ?? 'N/A',
//                 'address' => $request->new_customer_address ?? 'N/A',
//             ]);

//             $customer_id = $customer->id;
//         }

//         if (!$customer_id) {
//             return response()->json([
//                 'message' => 'Customer is required'
//             ], 422);
//         }

//         // ======================
//         // CREATE SALE FIRST (TEMP total = 0)
//         // ======================
//         $sale = \App\Models\Sale::create([
//             'customer_id' => $customer_id,
//             'user_id' => auth()->id() ?? null,
//             'total_amount' => 0, // 👈 FIX: temporary safe value
//             'discount' => $request->discount ?? 0,
//             'payment_status' => $request->payment_status,
//             'sale_date' => $request->sale_date,
//         ]);

//         // ======================
//         // CALCULATE TOTAL
//         // ======================
//         $total = 0;

//         foreach ($request->items as $item) {

//             // Save Sale Items
//             \App\Models\SaleItem::create([
//                 'sale_id' => $sale->id,
//                 'product_id' => $item['product_id'],
//                 'quantity' => $item['quantity'],
//                 'price' => $item['price'],
//             ]);

//             $total += $item['quantity'] * $item['price'];

//             // ======================
//             // STOCK REDUCTION
//             // ======================
//             $inventory = \App\Models\Inventory::where('product_id', $item['product_id'])->first();

//             if ($inventory) {

//                 if ($inventory->quantity < $item['quantity']) {
//                     throw new \Exception("Stock not enough for product ID: " . $item['product_id']);
//                 }

//                 $inventory->quantity -= $item['quantity'];
//                 $inventory->save();
//             }
//         }

//         // ======================
//         // APPLY DISCOUNT
//         // ======================
//         $discount = $request->discount ?? 0;
//         $finalTotal = $total - ($total * $discount / 100);

//         // ======================
//         // UPDATE SALE TOTAL
//         // ======================
//         $sale->update([
//             'total_amount' => $finalTotal
//         ]);

//         DB::commit();

//         return response()->json([
//             'success' => true,
//             'sale_id' => $sale->id,
//             'total' => $finalTotal
//         ]);

//     } catch (\Exception $e) {

//         DB::rollBack();

//         return response()->json([
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }
    // SHOW
    public function show($id)
    {
        $sale = Sale::with(['customer','user'])->find($id);

        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }

        return response()->json($sale);
    }

    // UPDATE (FIXED)
    public function update(Request $request, $id)
{
    $sale = Sale::find($id);

    if (!$sale) {
        return response()->json(['message' => 'Sale not found'], 404);
    }

    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'total_amount' => 'required|numeric|min:0',
        'discount' => 'nullable|numeric|min:0',
        'payment_status' => 'required|in:pending,paid,partial',
        'sale_date' => 'required|date',
    ]);

    $sale->update([
        'customer_id' => $request->customer_id,
        'total_amount' => $request->total_amount,
        'discount' => $request->discount ?? 0,
        'payment_status' => $request->payment_status,
        'sale_date' => $request->sale_date,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Sale updated successfully'
    ]);
}
    // public function update(Request $request, $id)
    // {
    //     $sale = Sale::find($id);

    //     if (!$sale) {
    //         return response()->json(['message' => 'Sale not found'], 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'customer_id' => 'required|exists:customers,id',
    //         'total_amount' => 'required|numeric|min:0',
    //         'discount' => 'nullable|numeric|min:0',
    //         'payment_status' => 'required|in:pending,paid,partial',
    //         'sale_date' => 'required|date',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $sale->update([
    //         'customer_id' => $request->customer_id,
    //         'total_amount' => $request->total_amount,
    //         'discount' => $request->discount ?? 0,
    //         'payment_status' => $request->payment_status,
    //         'sale_date' => $request->sale_date,
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Sale updated successfully'
    //     ]);
    // }

    // DELETE
    public function destroy($id)
    {
        $sale = Sale::find($id);

        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }

        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sale deleted successfully'
        ]);
    }
}