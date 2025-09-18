<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return ProductResource::collection($products);
    }


    public function show($productId)
    {
        $product = Product::where('id', $productId)
            ->firstOrFail();
        return new ProductResource($product);
    }

    public function store(StoreProductRequest $request)
    {
        return new ProductResource(Product::create($request->validated()));
    }

    public function update(UpdateProductRequest $request, $productId)
    {
        $product = Product::where('id', $productId)->firstOrFail();
        $product->update($request->validated());
        return new ProductResource($product);
    }

    public function destroy($productId)
    {
        $product = Product::where('id', $productId)->firstOrFail();
        $product->delete();
        return response()->json([
            'message' => 'Товар удален'
        ]);
    }
}
