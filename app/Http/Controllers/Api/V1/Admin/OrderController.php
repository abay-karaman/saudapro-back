<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\StoreOrderRequest;
use App\Http\Requests\Admin\Order\UpdateOrderRequest;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::all();
        return OrderResource::collection($orders);
    }


    public function show($orderId)
    {
        $order = Order::where('parent_id', $orderId)
            ->firstOrFail();
        return OrderResource::collection($order);
    }

    public function store(StoreOrderRequest $request)
    {
        return new OrderResource(Order::create($request->validated()));
    }

    public function update(UpdateOrderRequest $request, $orderId)
    {
        $order = Order::where('id', $orderId)->firstOrFail();
        $order->update($request->validated());
        return new OrderResource($order);
    }

    public function destroy($orderId)
    {
        $order = Order::where('id', $orderId)->firstOrFail();
        $order->delete();
        return response()->json([
            'message' => 'Пользователь удален'
        ]);
    }
}
