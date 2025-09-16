<?php

namespace App\Http\Controllers\Api\V1\Courier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourierDebtorsController extends Controller
{
    public function index(Request $request)
    {
        $courier = $request->user(); // текущий авторизованный курьер

        $orders = Order::with([
            'counterparty',
            'payments' => function ($q) use ($courier) {
                $q->where('courier_id', $courier->id)
                    ->where('debt_amount', '>', 0)
                    ->latest(); // берём только оплаты этого курьера
            }
        ])
            ->whereHas('payments', function ($q) use ($courier) {
                $q->where('courier_id', $courier->id)
                    ->where('debt_amount', '>', 0);
            })
            ->get()
            ->map(function ($order) {
                $lastPayment = $order->payments->first();
                return [
                    'order_id'        => $order->id,
                    'counterparty'    => $order->counterparty?->name,
                    'total_delivered' => $order->total_delivered,
                    'paid_amount'     => $lastPayment->paid_amount ?? 0,
                    'debt_amount'     => $lastPayment->debt_amount ?? 0,
                    'debt_confirmed'  => $lastPayment->debt_confirmed ?? 0,
                    'delivered_at'    => optional($lastPayment->created_at)->format('d-m-Y'),
                ];
            })
            ->values();

        return response()->json([
            'data' => $orders,
        ]);
    }

    public function payDebt(Request $request, $orderId)
    {
        $courier = $request->user();

        $order = Order::with(['payments' => function ($q) use ($courier) {
            $q->where('courier_id', $courier->id);
        }])
            ->findOrFail($orderId);

        $payment = $order->payments->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Долг для этого заказа и курьера не найдена',
            ], 404);
        }

        if ($payment->debt_amount <= 0) {
            return response()->json([
                'message' => 'У этого заказа нет долгов',
            ], 400);
        }

        DB::transaction(function () use ($payment) {
            $debt = $payment->debt_amount;

            $payment->paid_amount += $debt;
            $payment->debt_amount = 0;
            $payment->debt_confirmed = true;
            $payment->save();
        });

        return response()->json([
            'message' => 'Долг по заказу #' . $order->id . ' полностью погашен',
            'payment' => $payment->fresh(),
        ]);
    }
}
