<?php

namespace App\Http\Controllers\Api\V1\OneC;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ttn\TtnBulkRequest;
use App\Models\Counterparty;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Truck;
use App\Models\Ttn;
use App\Models\TtnItem;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Ttn1CController extends Controller
{
    public function bulkStore(TtnBulkRequest $request)
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $courierMap = User::pluck('id', 'phone')->toArray();

            $ttn = Ttn::updateOrCreate(
                ['code' => $data['code']],
                [
                    'uid' => $data['uid'],
                    'name' => $data['name'],
                    'date' => $data['date'],
                    'courier_id' => $courierMap[$data['courier_phone']] ?? null,
                    'status' => $data['status'] ?? null,
                ]
            );

            $counterpartyMap = Counterparty::pluck('id', 'code')->toArray();
            $representativeMap = User::pluck('id', 'phone')->toArray();

            foreach ($data['orders'] as $orderData) {
                if (empty($orderData['uid'])) {
                    // если вдруг пришёл заказ без uuid — лучше сразу отбрасывать
                    continue;
                }
                $counterpartyId = $counterpartyMap[$orderData['counterparty_code']] ?? null;
                if (!$counterpartyId) {
                    throw new Exception("Контрагент с кодом {$orderData['counterparty_code']} не найден");
                }
                $representativeId = $representativeMap[$orderData['reps_phone'] ?? ''] ?? null;
//                if (!$representativeId) {
//                    throw new Exception("Торговый с номером {$orderData['reps_phone']} не найден");
//                }

                // проверяем: есть ли заказ с таким uuid
                $order = Order::updateOrCreate(
                    ['uid' => $orderData['uid']],
                    [
                        'uid' => $orderData['uid'],
                        'counterparty_id' => $counterpartyId,
                        'representative_id' => $representativeId,
                        'total_collected' => $orderData['total_collected'] ?? 0,
                        'status' => $orderData['status'] ?? 'new',
                        'comment' => $orderData['comment'] ?? null,
                        'payment_method' => $orderData['payment_method'] ?? null,
                    ]
                );
                $productMap = Product::pluck('id', 'code')->toArray();
                // позиции заказа
                if (!empty($orderData['items'])) {
                    foreach ($orderData['items'] as $item) {
                        $productId = $productMap[$item['product_code']] ?? null;
                        $order->items()->updateOrCreate(
                            [
                                'product_id' => $productId
                            ],
                            [
                                'qty_collected' => $item['qty_collected'] ?? null,
                                'price' => $item['price'],
                            ]);
                    }
                }

                // запись в ttn_items
                TtnItem::updateOrCreate(
                    [
                        'ttn_id' => $ttn->id,
                        'order_id' => $order->id],
                    [
                        'name' => $orderData['name'] ?? "Заказ {$order->uid}",
                        'status' => $orderData['status'] ?? 'new'
                    ]
                );
            }
            DB::commit();

            return response()->json(['message' => 'TTN saved', 'ttn_id' => $ttn->id]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при сохранении TTN: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Ошибка при сохранении TTN',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
