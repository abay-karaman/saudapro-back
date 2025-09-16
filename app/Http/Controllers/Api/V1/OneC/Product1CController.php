<?php

namespace App\Http\Controllers\Api\V1\OneC;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductBulkRequest;
use App\Models\Category;
use App\Models\Producer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Product1CController extends Controller
{
    public function bulkStore(ProductBulkRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Загружаем справочники
            $categoryMap = Category::pluck('id', 'code')->toArray();
            $producersMap = Producer::pluck('id', 'code')->toArray();

            $productsToUpsert = [];

            foreach ($data['products'] as $productData) {
                // Категория
                $categoryId = $categoryMap[$productData['category_code']] ?? null;
                if (!$categoryId) {
                    Log::warning("Category not found", [
                        'code' => $productData['category_code']
                    ]);
                    continue;
                }

                // Производитель (только связываем, не добавляем!)
                $producerId = null;
                if (!empty($productData['producer_code'])) {
                    $producerId = $producersMap[$productData['producer_code']] ?? null;
                }

                // Подготовка товара
                $productsToUpsert[] = [
                    'code' => $productData['code'],
                    'name' => $productData['name'],
                    'description' => $productData['description'] ?? null,
                    'producer_id' => $producerId,
                    'category_id' => $categoryId,
                    'category_code' => $productData['category_code'],
                    'unit_id' => $productData['unit_id'],
                    'is_active' => $productData['is_active'] ?? true,
                    'status' => $productData['status'] ?? 'old',
                    'unit_coefficient' => $productData['unit_coefficient'] ?? null,
                ];
            }

            // Массовый UPSERT товаров
            if (!empty($productsToUpsert)) {
                Product::upsert(
                    $productsToUpsert,
                    ['code'], // уникальный ключ
                    [
                        'name',
                        'description',
                        'producer_id',
                        'category_id',
                        'category_code',
                        'unit_id',
                        'is_active',
                        'status',
                        'unit_coefficient',
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Товары успешно добавлены/обновлены',
                'status' => 'success',
                'stats' => [
                    'products_processed' => count($productsToUpsert),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Ошибка при добавлении товаров',
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
