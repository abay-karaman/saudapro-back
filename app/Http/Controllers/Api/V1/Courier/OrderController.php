<?php

namespace App\Http\Controllers\Api\V1\Courier;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CourierOrderResource;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    public function show(Request $request, $orderId)
    {
        $user = $request->user();
        $order = Order::where('id', $orderId)->with(['items.product', 'counterparty'])->withCount('items')->first();

        if (!$order) {
            return response()->json([
                'message' => 'Заказ не существует или нет доступа',
                'data' => [],
            ], 404);
        }

        return new CourierOrderResource($order);
    }

    public function updateOrder(Request $request, $orderId)
    {
        $user = $request->user();
        $order = Order::where('id', $orderId)->first();

        if (!$order) {
            return response()->json([
                'message' => 'Заказ не существует или нет доступа',
                'data' => [],
            ], 404);
        }
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty_delivered' => 'required|integer|min:0',
            'items.*.delivery_comment' => 'nullable|string',
        ]);

        // Обновляем товары
        DB::beginTransaction();
        try {
            $totalDelivered = 0;

            foreach ($validated['items'] as $item) {
                $orderItem = OrderItem::where('order_id', $orderId)
                    ->where('product_id', $item['product_id'])
                    ->first();
                if (!$orderItem) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Товар с ID {$item['product_id']} отсутствует в заказе",
                    ], 404);
                }
                if (is_null($orderItem->price) || $orderItem->price <= 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "У товара '{$orderItem->product->name}' отсутствует корректная цена в заказе",
                    ], 422);
                }

                $totalDelivered += $orderItem->price * $item['qty_delivered'];

                $orderItem->update([
                    'qty_delivered' => $item['qty_delivered'],
                    'delivery_comment' => $item['delivery_comment'] ?? null,
                ]);
            }

            $order->update(['total_delivered' => $totalDelivered]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'order_id' => $order->id,
                'total_delivered' => $totalDelivered,
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


    public function updateStatus($orderId, Request $request)
    {
        $order = Order::where('id', $orderId)
            ->first();

        $validated = $request->validate([
            'status' => 'required|string|in:on_way,delivered,cancelled',
        ]);

        if (!$order) {
            return response()->json([
                'message' => 'Заказ не найден',
                'data' => [],
            ], 404);
        }

        $order->update([
            'status' => $validated['status'],
        ]);

        // пересчёт ТТН
        $order->ttn?->refreshStatus();

        return response()->json([
            'status' => 'success',
            'message' => "Статус заказа #{$order->id} изменён на {$order->status}",
            'ttn_status' => $order->ttn?->status, // всегда актуален
        ]);
    }

    public function courierDailyReport(Request $request)
    {
        $courier = $request->user();
        $date = $request->input('date', now()->toDateString());

        // заказы за день
        // Берём заказы, которые входят в ТТН курьера за день
        $orders = Order::with(['payments'])
            ->whereIn('id', function ($q) use ($courier, $date) {
                $q->select('ttn_items.order_id')
                    ->from('ttn_items')
                    ->join('ttns', 'ttns.id', '=', 'ttn_items.ttn_id')
                    ->where('ttns.courier_id', $courier->id)
                    ->whereDate('ttns.date', $date);
            })
            ->get();

        // количество ТТН за день
        $ttnCount = DB::table('ttns')
            ->where('courier_id', $courier->id)
            ->whereDate('date', $date)
            ->count();

        // количество ТТН items за день
        $ttnItemCount = DB::table('ttn_items')
            ->join('ttns', 'ttns.id', '=', 'ttn_items.ttn_id')
            ->where('ttns.courier_id', $courier->id)
            ->whereDate('ttns.date', $date)
            ->count();

        $ordersCount = $orders->count();
        $visitedCount = $orders->where('status', 'delivered')->count();
        $remainingCount = $ordersCount - $visitedCount;

        $totalCollected = $orders->sum('total_collected');
        $totalDelivered = $orders->where('status', 'delivered')->sum('total_delivered');

        $totalPaid = $orders->flatMap->payments->sum('paid_amount');
        $totalDebt = $orders->flatMap->payments->sum('debt_amount'); // берем последний долг по каждому заказу
        $totalDebt = $totalDebt ?? 0;

        return response()->json([
            'courier' => $courier->name,
            'date' => $date,
            'summary' => [
                'ttn_count' => $ttnCount,
                'orders_count' => $ordersCount,
                'orders_visited' => $visitedCount,
                'orders_remaining' => $remainingCount,
                'total_sum' => $totalCollected,
                'total_delivered' => $totalDelivered,
                'total_paid' => $totalPaid,
                'total_debt' => $totalDebt,
            ],
        ]);
    }

}
