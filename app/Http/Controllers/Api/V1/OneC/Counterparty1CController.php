<?php

namespace App\Http\Controllers\Api\V1\OneC;

use App\Http\Controllers\Controller;
use App\Http\Requests\Counterparty\BulkStoreCounterpartyRequest;
use App\Models\Counterparty;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Counterparty1CController extends Controller
{
    public function bulkStore(BulkStoreCounterpartyRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $count = 0;
            // 1. Сохраняем все категории без parent_id
            foreach ($data['counterparties'] as $item) {
                $counterparty = Counterparty::updateOrCreate(
                    [
                        'code' => $item['code'],
                    ],
                    [
                        'name' => $item['name'],
                        'uid' => $item['UID'],
                        'bin_iin' => $item['bin_iin'] ?? null,
                        'phone' => $item['phone'] ?? null,
                    ]);

                $repPhones = $item['rep_phones'] ?? [];
                if (is_array($repPhones) && !empty($repPhones)) {
                    $representativeIds = User::whereIn('phone', $repPhones)
                        ->where('role_id', 2)   //роль торгового
                        ->pluck('id');
                    if ($representativeIds->isNotEmpty()){
                        $counterparty->representatives()->syncWithoutDetaching($representativeIds);
                    }
                }
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
}
