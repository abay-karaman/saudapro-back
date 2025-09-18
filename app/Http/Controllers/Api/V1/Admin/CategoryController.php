<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreOrderRequest;
use App\Http\Requests\Admin\Category\UpdateOrderRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return CategoryResource::collection($categories);
    }


    public function show($categoryId)
    {
        $children = Category::where('parent_id', $categoryId)
            ->get();
        return CategoryResource::collection($children);
    }

    public function store(StoreOrderRequest $request)
    {
        return new CategoryResource(Category::create($request->validated()));
    }

    public function update(UpdateOrderRequest $request, $categoryId)
    {
        $category = Category::where('id', $categoryId)->firstOrFail();
        $category->update($request->validated());
        return new CategoryResource($category);
    }

    public function destroy($categoryId)
    {
        $category = Category::where('id', $categoryId)->firstOrFail();
        $category->delete();
        return response()->json([
            'message' => 'Категория удалена'
        ]);
    }
}
