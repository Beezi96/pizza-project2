<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::query()->with('category')->get();
        return response()->json([
            'data' => $products,
        ]);
//        $query = Product::query()->with('category'); // Подготовка запроса
//        return $query->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::query()->create($request->validated());

        return response()->json([
            'data' => $product,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::query()->with('category')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json([
            'data' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::query()->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->update($request->validated());

        return response()->json([
            'data' => $product,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::query()->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();


        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
