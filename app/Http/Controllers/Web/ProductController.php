<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();

        return view('products.index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product)
    {
        return view('products.show', [
            'product' => $product,
        ]);
    }
}
