<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\BulkStoreCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->get();
        return CategoryResource::collection($categories);
    }


    public function show($categoryId)
    {
        $children = Category::where('parent_id', $categoryId)
            ->where('is_active', true)
            ->get();
        return CategoryResource::collection($children);
    }

    public function bulkStore(BulkStoreCategoryRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $createdCount = 0;
            $skippedCount = 0;

            // 1. Сохраняем все категории без parent_id
            foreach ($data['categories'] as $item) {
                $category = Category::firstOrCreate(
                    ['code' => $item['code']],
                    [
                        'name' => $item['name'],
                        'icon' => $item['icon'] ?? null,
                        'parent_code' => $item['parent_code'] ?? null,
                        'is_active' => $item['is_active'] ?? true,
                    ]);
                if ($category->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $skippedCount++;
                }
            }

            // 2. Одним запросом получаем всех родителей
            $codeToId = Category::pluck('id', 'code')->toArray();

            // 3. Массовое обновление parent_id
            foreach ($codeToId as $code => $id) {
                Category::where('parent_code', $code)
                    ->update(['parent_id' => $id]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Категории успешно добавлены',
                'status' => 'success',
                'created' => $createdCount,
                'skipped' => $skippedCount,
                'total' => $createdCount + $skippedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Произошла ошибка при добавлении категорий',
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        return new CategoryResource(Category::create($request->all()));
    }

    public function update(UpdateCategoryRequest $request, $categoryId)
    {
        $category = Category::where('id', $categoryId)->firstOrFail();
        $category->update($request->all());
        return new CategoryResource($category);
    }

    public function destroy($categoryId)
    {
        $category = Category::where('id', $categoryId)->firstOrFail();
        $category->delete();
        return response()->json([
            'message' => 'Category deleted'
        ]);
    }
}
