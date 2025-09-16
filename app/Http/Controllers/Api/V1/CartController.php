<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show($storeId)
    {
        $cart = Cart::firstOrCreate(['store_id' => $storeId]);
        $cart->load(['items.product.priceType', 'items.product.images']);

        return new CartResource($cart);
    }

    public function addItem(Request $request, $storeId)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'integer|min:1',
        ]);

        $cart = Cart::firstOrCreate(['store_id' => $storeId]);

        $item = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $request->product_id,
        ]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $request->quantity;
        $item->save();

        $cart->load(['items.product.images']);

        return new CartResource($cart);
    }

    public function updateItem(Request $request, $storeId, $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('store_id', $storeId)->firstOrFail();
        $item = $cart->items()->where('product_id', $productId)->firstOrFail();

        $item->quantity = $request->quantity;
        $item->save();

        $cart->load(['items.product.priceType', 'items.product.images']);

        return new CartResource($cart);
    }

    public function removeItem($storeId, $productId)
    {
        $cart = Cart::where('store_id', $storeId)->firstOrFail();
        $item = $cart->items()->where('product_id', $productId)->delete();

        $cart->load(['items.product.priceType', 'items.product.images']);

        return new CartResource($cart);
    }

    public function clear($storeId)
    {
        $cart = Cart::where('store_id', $storeId)->firstOrFail();
        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json(['message' => 'Корзина очищена']);
    }
}
