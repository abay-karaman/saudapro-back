<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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

class ProductController extends Controller
{

    public function index(Request $request)
    {
        try {
            // Validate per_page (max 100 items per page)
            $perPage = min($request->get('per_page', 20), 100);
            $query = Product::where('is_active', true)
                ->with(['category', 'stock', 'images', 'prices', 'producer']);

            // Status filter
            if ($request->filled('status')) {
                $query->where('products.status', $request->get('status'));
            }

            // Только товары со скидкой
            if ($request->filled('with_discount')) {
                $query->whereHas('prices', function ($q) use ($request) {
                    $q->where('price_type_id', $request->user()->price_type_id)
                        ->whereNotNull('discount')
                        ->whereNotNull('discount_expires_at')
                        ->where('discount_expires_at', '>', now());
                });
            }

            // Category filter (with children)
            if ($request->filled('category_id')) {
                $categoryId = $request->category_id;
                $category = Category::find($categoryId);
                $childIds = Category::where('parent_id', $category->id)->pluck('id')->toArray();

                if ($childIds) {
                    // если есть дети - ищем по ним
                    $query->whereIn('products.category_id', $childIds);
                } else {
                    // если детей нет - значит категория конечная
                    $query->where('products.category_id', $category->id);
                }
            }

            // Producer filter
            if ($request->filled('producer')) {
                $query->when($request->filled('producer'), function ($q) use ($request) {
                    $q->whereHas('producer', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%' . $request->producer . '%');
                    });
                });
            }

            // Search (name, description)
            if ($request->filled('search')) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('products.name', 'like', $searchTerm)
                        ->orWhere('products.description', 'like', $searchTerm);
                });
            }

            $products = $query->paginate($perPage);

            if (!$products) {
                return response()->json([
                    'message' => 'Товаров не существует',
                    'data' => [],
                ], 404);
            }

            // Формируем ответ вручную, без links
            return response()->json([
                'data' => ProductResource::collection($products),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                    'has_more' => $products->hasMorePages(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $product = Product::with(['category', 'stock', 'images', 'prices', 'producer'])->findOrFail($id);
        if (!$product) {
            return response()->json([
                'message' => 'Товар не существет',
                'data' => [],
            ], 404);
        }
        return new ProductResource($product);
    }

    public function bulkStore(BulkStoreProductRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $categoryMap = Category::pluck('id', 'code')->toArray();

            $stats = [
                'new_products' => 0,
                'stocks_updated' => 0,
                'prices_updated' => 0,
            ];

            foreach ($data['products'] as $productData) {
                $categoryId = $categoryMap[$productData['category_code']] ?? null;
                if (!$categoryId) {
                    // Можно логировать или кидать исключение
                    error_log("Category not found for code: " . $productData['category_code']);
                    continue;
                }

                // 🔹 Проверка или создание производителя
                $producerId = null;
                if (!empty($productData['producer'])) {
                    $producer = Producer::firstOrCreate(
                        ['name' => $productData['producer']['name']], // ищем по имени
                        [
                            'name' => $productData['producer']['name'],
                            'code' => $productData['producer']['code'] ?? null,
                        ]);
                    $producerId = $producer->id;
                }

                // 1. Создание товара
                $product = Product::firstOrCreate(
                    ['code' => $productData['code']],
                    [
                        'name' => $productData['name'],
                        'description' => $productData['description'] ?? null,
                        'producer_id' => $producerId ?? null,
                        'category_id' => $categoryId,
                        'category_code' => $productData['category_code'],
                        'unit_id' => $productData['unit_id'],
                        'is_active' => $productData['is_active'] ?? true,
                        'status' => $productData['status'] ?? null,
                        'unit_coefficient' => $productData['unit_coefficient'] ?? null,
                    ]);
                if ($product->wasRecentlyCreated) {
                    $stats['new_products']++;
                }

                // 2. Сохранение остатков
                if (isset($productData['stock'])) {
                    ProductStock::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'warehouse' => $productData['warehouse'] ?? null,
                        ],
                        [
                            'unit_id' => $productData['unit_id'] ?? null,
                            'stock' => $productData['stock'],
                        ]);
                    $stats['stocks_updated']++;
                }

                // 3. Сохранение цен (price_types)
                foreach ($productData['prices'] as $priceData) {

                    // Проверяем или создаём PriceType
                    $priceType = PriceType::firstOrCreate(
                        [
                            'code' => $priceData['type_code']
                        ],
                        [
                            'name' => $priceData['type'],
                            'code' => $priceData['type_code']
                        ]);

                    // Создаём запись цены
                    Price::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'price_type_id' => $priceType->id,
                        ],
                        [
                            'price' => $priceData['price'],
                        ]);
                    $stats['prices_updated']++;
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Товары успешно добавлены',
                'status' => 'success',
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Произошла ошибка при добавлении товаров',
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
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
            "message" => "Product deleted"
        ]);
    }
}
