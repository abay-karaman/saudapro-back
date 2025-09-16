<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Counterparty\BulkStoreCounterpartyRequest;
use App\Http\Requests\Counterparty\StoreCounterpartyRequest;
use App\Http\Resources\V1\CounterpartyResource;
use App\Models\Counterparty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CounterpartyController extends Controller
{
    public function bulkStore(BulkStoreCounterpartyRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $count = 0;
            // 1. Сохраняем все категории без parent_id
            foreach ($data['counterparties'] as $item) {
                Counterparty::updateOrCreate(
                    [
                        'code' => $item['code'],
                    ],
                    [
                    'name' => $item['name'],
                    'uid' => $item['UID'],
                    'bin_iin' => $item['bin_iin'] ?? null,
                    'phone' => $item['phone'] ?? null,
                    'reps_phone' => $item['reps_phone'] ?? null,
                ]);
                $count++;
            }

            DB::commit();

            return response()->json([
                'message' => 'Контрагенты успешно добавлены',
                'status' => 'success',
                'updated' => $count,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Произошла ошибка при добавлении категорий',
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($counterpartyId)
    {
        $counterparty = Counterparty::where('id', $counterpartyId)->with('stores')->first();
        if (!$counterparty) {
            return response()->json([
                'message' => 'Контрагент не найден',
                'data' => [],
            ], 404);
        }
        return new CounterpartyResource($counterparty);
    }

    public function index()
    {
        $rep = Auth::user();
        // $rep — текущий торговый представитель
        $counterparties = $rep->counterparties()->with('stores')->get();
        return CounterpartyResource::collection($counterparties);
    }
}
