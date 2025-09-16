<?php

namespace App\Http\Controllers\Api\V1\OneC;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\BulkStoreProductRequest;
use App\Http\Requests\Product\ProductPriceBulkRequest;
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

class ProductPrice1CController extends Controller
{
    public function bulkStore(ProductPriceBulkRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $productMap = Product::pluck('id', 'code')->toArray();
            $priceTypeMap = PriceType::pluck('id', 'code')->toArray();

            $stats = [
                'prices_updated' => 0,
                'price_types_created' => 0,
                'discounts_applied' => 0,
                'discounts_reset' => 0,
            ];

            foreach ($data['products'] as $productData) {
                $productId = $productMap[$productData['product_code']] ?? null;
                if (!$productId) {
                    // Можно логировать или кидать исключение
                    error_log("Товар не найден с кодом: " . $productData['product_code']);
                    continue;
                }

                // 3. Сохранение цен (price_types)
                foreach ($productData['prices'] as $priceData) {
                    $priceTypeId = $priceTypeMap[$priceData['type_code']] ?? null;
                    if (!$priceTypeId) {
                        $priceType = PriceType::create([
                            'name' => $priceData['type'],
                            'code' => $priceData['type_code']
                        ]);
                        $priceTypeId = $priceType->id;
                        $priceTypeMap[$priceData['type_code']] = $priceTypeId;
                        $stats['price_types_created']++;
                    }

                    // Текущая запись цены
                    $price = Price::where('product_id', $productId)
                        ->where('price_type_id', $priceTypeId)
                        ->first();

                    $oldPrice = $price?->price;
                    $newPrice = (float) $priceData['price'];

                    $discount = $price?->discount;
                    $discountExpiresAt = $price?->discount_expires_at;

                    if($oldPrice && $newPrice < $oldPrice){
                        // Цена снизилась → новая скидка на неделю
                        $discount = round((($oldPrice - $newPrice) / $oldPrice) * 100, 1);
                        $discountExpiresAt = now()->addWeek();
                        $stats['discounts_applied']++;
                    }
                    elseif ($oldPrice && $newPrice > $oldPrice) {
                        // Цена выросла → скидку сбрасываем
                        $discount = null;
                        $discountExpiresAt = null;
                        $stats['discounts_reset']++;
                    }

                    // Создаём запись цены
                    Price::updateOrCreate(
                        [
                            'product_id' => $productId,
                            'price_type_id' => $priceTypeId,
                        ],
                        [
                            'price' => $priceData['price'],
                            'discount' => $discount,
                            'discount_expires_at' => $discountExpiresAt,
                        ]);
                    $stats['prices_updated']++;
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Цены успешно добавлены',
                'status' => 'success',
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Произошла ошибка при добавлении цен для товаров',
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
