<?php

namespace App\Http\Controllers\Api\V1\Courier;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TtnItemResource;
use App\Http\Resources\V1\TtnResource;
use App\Models\Ttn;
use App\Models\TtnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TtnController extends Controller
{
    public function index(Request $request)
    {
        $courier = $request->user();
        $query = Ttn::query()
            ->where('courier_id', $courier->id);
        // Status filter
        if ($request->filled('courier_id')) {
            $query->where('courier_id', $request->get('courier_id'));
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->get('date'));
        }
        $ttns = $query->with('truck')->get();
        return TtnResource::collection($ttns);
    }

    public function show(Request $request, $ttnId)
    {
        $ttn = Ttn::with([
            'items' => function ($query) {
                $query->join('orders', 'orders.id', '=', 'ttn_items.order_id')
                    ->orderByRaw("
                FIELD(
                    orders.status,
                    'on_way',
                    'new', 'in_progress', 'collected', 'loaded',
                    'completed', 'delivered'
                )
            ")
                    ->select('ttn_items.*'); // важно, чтобы не перетёрлись колонки
            },
            'items.order',
            'items.order.counterparty',
        ])->findOrFail($ttnId);
        return TtnItemResource::collection($ttn->items);
    }

    // Переупорядочивание: ожидаем array position => [mediaId,...]
    public function reorderItems(Request $request, $ttnId)
    {
        try {
            $ttn = Ttn::findOrFail($ttnId);

            $request->validate([
                'position' => 'required|array|min:1'
            ]);

            $ids = $request->position;

            // Проверка: все ли items принадлежат этому ttn
            $count = TtnItem::where('ttn_id', $ttn->id)
                ->whereIn('id', $ids)
                ->count();

            if ($count !== count($ids)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Некоторые позиции не принадлежат этой ТТН'
                ], 422);
            }

            // Формируем CASE WHEN
            $caseSql = "CASE id ";
            foreach ($ids as $index => $itemId) {
                $caseSql .= "WHEN {$itemId} THEN {$index} ";
            }
            $caseSql .= "END";

            TtnItem::where('ttn_id', $ttn->id)
                ->whereIn('id', $ids)
                ->update([
                    'position' => DB::raw($caseSql)
                ]);

            return response()->json(['ok' => true]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'ТТН не найдена'
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Ошибка при сортировке',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
