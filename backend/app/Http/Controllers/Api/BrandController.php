<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    // 📌 GET ALL BRANDS
    public function index()
    {
        $brands = Brand::latest()->get();

        return response()->json([
            'status' => true,
            'data' => $brands
        ], 200);
    }

    // 📌 CREATE BRAND
    public function store(Request $request)
    {
        $validator = Validator::make($request->only('name'), [
            'name' => 'required|string|max:255|unique:brands,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $brand = Brand::create([
            'name' => $request->name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Brand created successfully',
            'data' => $brand
        ], 201);
    }

    // 📌 SHOW SINGLE BRAND
    public function show($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'status' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $brand
        ], 200);
    }

    // 📌 UPDATE BRAND
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'status' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        $validator = Validator::make($request->only('name'), [
            'name' => 'required|string|max:255|unique:brands,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $brand->update([
            'name' => $request->name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Brand updated successfully',
            'data' => $brand
        ], 200);
    }

    // 📌 DELETE BRAND
    public function destroy($id)
{
    try {
        $brand = Brand::findOrFail($id); // ye safe hai

        $brand->delete();

        return response()->json([
            'status' => true,
            'message' => 'Brand deleted successfully'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Delete failed',
            'error' => $e->getMessage()
        ], 500);
    }
}
}