<?php

namespace App\Http\Controllers\Api\V1\OneC;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\BulkStoreProductRequest;
use App\Http\Requests\Product\ProductStockBulkRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\V1\ProductResource;
use App\Models\Category;
use App\Models\Price;
use App\Models\PriceType;
use App\Models\Producer;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductStock1CController extends Controller
{

    public function bulkStore(ProductStockBulkRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $productMap = Product::pluck('id', 'code')->toArray();

            $stats = [
                'stocks_updated' => 0,
            ];

            foreach ($data['stocks'] as $stockData) {
                $productId = $productMap[$stockData['product_code']] ?? null;
                if (!$productId) {
                    // Можно логировать или кидать исключение
                    error_log("Товар не найден с кодом: " . $stockData['product_code']);
                    continue;
                }

                // 2. Сохранение остатков
                ProductStock::updateOrCreate(
                    [
                        'product_id' => $productId,
                    ],
                    [
                        'unit_id' => $stockData['unit_id'] ?? null,
                        'stock' => $stockData['stock'],
                        'warehouse' => $stockData['warehouse'] ?? null,
                    ]);
                $stats['stocks_updated']++;

            }

            DB::commit();

            return response()->json([
                'message' => 'Остатки успешно добавлены',
                'status' => 'success',
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Произошла ошибка при добавлении остатков',
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
