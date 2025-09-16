<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user(); // текущий пользователь
            $perPage = min($request->get('per_page', 20), 100);

            $query = Order::query()
                ->where('representative_id', $user->id)   // заказы только текущего пользователя
                ->with(['limitedItems.product', 'counterparty', 'store'])
                ->withCount('items');

            // фильтр по контрагенту
            if ($request->filled('counterparty_id')) {
                $query->where('counterparty_id', $request->counterparty_id);
            }

            // фильтр по статусу
            if ($request->filled('status')) {
                $query->where('orders.status', $request->get('status'));
            }

            $orders = $query->latest()->paginate($perPage);

            if (!$orders) {
                return response()->json([
                    'message' => 'Заказов не существует',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'data' => OrderResource::collection($orders),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                    'has_more' => $orders->hasMorePages(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при получении заказов',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $orderId)
    {
        $user = $request->user();
        $order = Order::where('id', $orderId)->where('representative_id', $user->id)->with(['items.product', 'counterparty'])->withCount('items')->first();

        if (!$order) {
            return response()->json([
                'message' => 'Заказ не существует или нет доступа',
                'data' => [],
            ], 404);
        }

        return new OrderResource($order);
    }

    public function createOrder(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.comment' => 'nullable|string|max:500',
            'comment' => 'nullable|string|max:500',
            'store_id' => 'required|integer|exists:stores,id',
            'counterparty_id' => 'required|integer|exists:counterparties,id',
            'payment_method' => 'required|string|in:cash,card,debt',
        ]);

        try {
            DB::beginTransaction();

            // Получаем клиента и его тип цен
            $priceTypeId = $user->price_type_id;

            // Создаём сам заказ
            $order = Order::create([
                'representative_id' => $user->id,
                'store_id' => $data['store_id'],
                'counterparty_id' => $data['counterparty_id'],
                'payment_method' => $data['payment_method'],
                'comment' => $data['comment'] ?? null,
                'total_price' => 0,
                'status' => 'new',
            ]);

            $totalPrice = 0;

            // Добавляем товары в заказ
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                // Находим цену для нужного price_type
                $price = Price::where('product_id', $product->id)
                    ->where('price_type_id', $priceTypeId)
                    ->value('price');

                if (!$product) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Товар с ID {$item['product_id']} не найден",
                    ], 404);
                }

                if (!$price || $price <= 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "У товара '{$product->name}' отсутствует корректная цена",
                    ], 404);
                }



                $totalPrice += $price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'comment' => $item['comment'] ?? null,
                ]);
            }
            $order->update(['total_price' => $totalPrice]);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'order_id' => $order->id,
                'message' => 'Заказ успешно оформлен',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка при создании заказа',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::with('counterparty')->findOrFail($id);

        // Проверка прав: торговый может редактировать только заказы своих контрагентов
        if ($order->representative_id !== $user->id) {
            return response()->json(['message' => 'Нет доступа к этому заказу'], 403);
        }
        // Проверка статуса заказа
        if ($order->status !== 'new') {
            return response()->json(['message' => 'Редактирование или отмена возможны только для заказа со статусом "новый"'], 403);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.comment' => 'nullable|string|max:500',
        ]);

        // Обновляем товары
        DB::beginTransaction();
        try {
            $priceTypeId = $user->price_type_id ?? null;

            $order->items()->delete();
            $totalPrice = 0;

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                $price = Price::where('product_id', $product->id)
                    ->where('price_type_id', $priceTypeId)
                    ->value('price') ?? 0;

                if (!$product) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Товар с ID {$item['product_id']} не найден",
                    ], 404);
                }

                if (!$price || $price <= 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "У товара '{$product->name}' отсутствует корректная цена",
                    ], 404);
                }

                $totalPrice += $price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'comment' => $item['comment'] ?? null,
                ]);
            }

            $order->update(['total_price' => $totalPrice]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'order_id' => $order->id,
                'message' => 'Заказ успешно отредактирован!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка при редактировании заказа',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::with('counterparty')->findOrFail($id);

        // Можно удалять только если заказ ещё не доставлен
        if ($order->status !== 'new') {
            return response()->json(['message' => 'Невозможно отменить заказ!'], 400);
        }
        if ($order->representative_id !== $user->id) {
            return response()->json(['message' => 'Нет доступа к этому заказу'], 403);
        }

        $order->delete();

        return response()->json(['message' => 'Заказ отменен']);
    }

    public function cancel($orderId, Request $request)
    {
        $user = $request->user();

        $order = Order::where('id', $orderId)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Заказ не найден',
                'data' => [],
            ], 404);
        }

        if ($order->representative_id !== $user->id) {
            return response()->json(['message' => 'Нет доступа'], 403);
        }

        if ($order->status !== 'new') {
            return response()->json([
                'message' => 'Отменить можно только заказы в статусе "Новый"'
            ], 422);
        }

        $order->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Заказ успешно отменён',
        ]);
    }
}
