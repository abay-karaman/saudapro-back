<?php

namespace App\Http\Controllers\Api\V1\OneC;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Order1CController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['counterparty', 'representative'])->withCount('items');

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('orders.created_at', $request->get('date'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('orders.status', $request->get('status'));
        }

        // is_reps filter
        if ($request->filled('is_reps')) {
            $isReps = filter_var($request->get('is_reps'), FILTER_VALIDATE_BOOLEAN);
            if ($isReps) {
                $query->whereNotNull('orders.representative_id');
            } else {
                $query->whereNull('orders.representative_id');
            }
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'Заказы не найдены',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'data' => OrderResource::collection($orders),
        ]);
    }


    public function show($orderId)
    {
        $order = Order::with(['items.product', 'counterparty'])->withCount('items')->findOrFail($orderId);

        if ($order)
            return new OrderResource($order);
        else
            return response()->json([
                'message' => 'Заказ не существует',
            ]);
    }

    public function updateStatus($orderId, Request $request)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2,3,4,5,6',
        ]);

        $order = Order::findOrFail($orderId);

        $oldStatus = $order->status;
        $newStatus = $this->statuses[$request->status];
        $order->status = $newStatus;
        $order->save();

        return response()->json([
            'message' => "Статус заказа #{$order->id} изменён с {$oldStatus} на {$newStatus}",
            'order' => $order,
        ], 200);
    }

    private $statuses = [
        0 => 'new',
        1 => 'in_progress',
        2 => 'collected',
        3 => 'loaded',
        4 => 'on_way',
        5 => 'delivered',
        6 => 'cancelled',
    ];
}
