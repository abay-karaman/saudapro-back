<?php

namespace App\Http\Controllers\Api\V1\OneC;

use App\Http\Controllers\Controller;
use App\Http\Requests\Producer\ProducerBulkRequest;
use App\Http\Requests\Product\BulkStoreProductRequest;
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

class Producer1CController extends Controller
{

    public function bulkStore(ProducerBulkRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $createdCount = 0;
            $skippedCount = 0;

            foreach ($data['producers'] as $item) {
                // 1. Создание производителя
                $producer = Producer::updateOrCreate(
                    ['code' => $item['code']],
                    [
                        'name' => $item['name'],
                        'country' => $item['country'] ?? null,
                    ]);
                if ($producer->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $skippedCount++;
                }

            }

            DB::commit();

            return response()->json([
                'message' => 'Производители успешно добавлены',
                'status' => 'success',
                'created' => $createdCount,
                'skipped' => $skippedCount,
                'total' => $createdCount + $skippedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Произошла ошибка при добавлении производителей',
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
