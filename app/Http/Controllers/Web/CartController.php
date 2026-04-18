<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $items = CartItem::with(['user', 'product.category'])->get();

        return view('cart.index', [
            'items' => $items,
        ]);
    }

    public function add(Request $request)
    {
        $user = User::where('email', 'user@test.com')->first();

        $product = Product::findOrFail($request->input('product_id'));

        CartItem::updateOrCreate(
          [
              'user_id' => $user->id,
              'product_id' => $product->id,
          ],
          [
              'quantity' => 1,
          ]
        );

        return redirect('/');
    }
}
