<?php

namespace App\Http\Controllers\Api\V1\Courier;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Price;
use App\Models\Product;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderPaymentController extends Controller
{
    public function createPayment(Request $request, $orderId)
    {
        $courier = $request->user();
        $courierId = $courier->id;
        $request->validate([
            'debt_amount' => 'required|numeric|min:0',
        ]);
        try {
            DB::beginTransaction();
            $order = Order::with('payments', 'counterparty')->findOrFail($orderId);

            $deliveredTotal = $order->total_delivered ?? 0;
            $debt = min($request->debt_amount, $deliveredTotal);
            $paidAmount = max($deliveredTotal - $debt, 0);

            // По умолчанию долг подтверждён
            $confirmationCode = null;
            $debtConfirmed    = true;

            if ($debt > 0) {
                $confirmationCode = mt_rand(1000, 9999);
                $debtConfirmed    = false;
                $message = "Bismo.kz: Оплата заказа №{$order->id} частично принята.\n"
                    . "Остаток долга: {$debt} ₸.\n"
                    . "Для подтверждения назовите курьеру код: {$confirmationCode}";

                // Отправляем код клиенту через WhatsApp
                $phone = $order->counterparty->phone ?? null;
                if ($phone) {
                    $this->sendMessage($phone, $message);
                }
            }

            $payment = OrderPayment::create([
                'order_id' => $order->id,
                'counterparty_id' => $order->counterparty_id,
                'courier_id' => $courierId,
                'paid_amount' => $paidAmount,
                'debt_amount' => $debt,
                'paid_at' => now(),
                'confirm_code' => $confirmationCode,
                'debt_confirmed' => $debtConfirmed,
            ]);

            //Обновляем статус заказа
            $order->update(['status' => 'delivered']);

            DB::commit();

            return response()->json([
                'message' => 'Оплата добавлена',
                'payment' => $payment,
                'debt' => $debt,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Ошибка при добавлении оплаты: " . $e->getMessage());

            return response()->json([
                'message' => 'Ошибка при добавлении оплаты',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyDebtCode(Request $request, $orderId)
    {
        $request->validate([
            'code' => 'required|digits:4',
        ]);
        try {
            $payment = OrderPayment::where('order_id', $orderId)
                ->latest()->first();

            if ($payment->debt_confirmed) {
                return response()->json([
                    'message' => 'Долг уже подтвержден ранее',
                ], 400);
            }

            if ($payment->confirm_code !== $request->code) {
                return response()->json([
                    'message' => 'Неверный код',
                ], 400);
            }

            $payment->update([
                'debt_confirmed' => true,
                'confirm_code' => null, // код одноразовый, обнуляем
            ]);

            return response()->json([
                'message' => 'Долг подтвержден',
                'payment' => $payment,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Ошибка при подтверждении долга: " . $e->getMessage());

            return response()->json([
                'message' => 'Ошибка при подтверждении долга',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function sendMessage($phone, $message)
    {
        app(WhatsAppService::class)->sendMessage($phone, $message);
    }
}
