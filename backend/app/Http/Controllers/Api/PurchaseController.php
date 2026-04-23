<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    // List all purchases
    public function index()
    {
        $purchases = Purchase::with('supplier')->get();
        return response()->json($purchases);
    }

    // Store a new purchase
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!Supplier::find($value)) {
                        $fail('Supplier not found.');
                    }
                }
            ],
            'total_amount' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $purchase = Purchase::create($request->all());

        return response()->json([
            'message' => 'Purchase created successfully',
            'purchase' => $purchase
        ], 201);
    }

    // Show a single purchase
    public function show($id)
    {
        $purchase = Purchase::with('supplier')->find($id);
        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        return response()->json($purchase);
    }

    // Update a purchase
    public function update(Request $request, $id)
    {
        $purchase = Purchase::find($id);
        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!Supplier::find($value)) {
                        $fail('Supplier not found.');
                    }
                }
            ],
            'total_amount' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $purchase->update($request->all());

        return response()->json([
            'message' => 'Purchase updated successfully',
            'purchase' => $purchase
        ]);
    }

    // Delete a purchase
    public function destroy($id)
    {
        $purchase = Purchase::find($id);
        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        $purchase->delete();

        return response()->json(['message' => 'Purchase deleted successfully']);
    }
}