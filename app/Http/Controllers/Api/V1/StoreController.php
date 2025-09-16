<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CreateStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Resources\V1\StoreResource;
use App\Models\Counterparty;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Получаем магазины, у которых контрагент привязан к текущему торговому
        $stores = Store::with('counterparty') // загружаем контрагента сразу, чтобы не было N+1
        ->whereHas('counterparty.representatives', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })
            ->get();

        return StoreResource::collection($stores);
    }

    public function storeByCounterparty($counterpartyId, Request $request)
    {
        $user = $request->user();

        // Проверяем, что контрагент принадлежит торговому
        // Получаем контрагента с магазинами, только если он привязан к текущему торговому
        $counterparty = Counterparty::with('stores')
            ->where('id', $counterpartyId)
            ->whereHas('representatives', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->first();
        if (!$counterparty) {
            return response()->json([
                'message' => 'Контрагент не найден или не принадлежит текущему торговому',
                'data' => [],
            ]);
        }
        // Загружаем магазины этого контрагента
        $stores = $counterparty->stores()->get();

        return StoreResource::collection($stores);
    }

    public function store(CreateStoreRequest $request)
    {
        $data = $request->validated();
        $store = Store::create($data);
        return new StoreResource($store);
    }


    public function show(Request $request, $storeId)
    {
        $user = $request->user();

        $store = Store::where('id', $storeId)
            ->whereHas('counterparty.representatives', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->first();
        if (!$store) {
            return response()->json([
                'message' => 'Контрагент не найден или не принадлежит текущему торговому',
                'data' => [],
            ]);
        }
        return new StoreResource($store);
    }

    public function update(UpdateStoreRequest $request, $storeId)
    {
        $user = $request->user();
        $store = Store::where('id', $storeId)
            ->whereHas('counterparty.representatives', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->first();
        if (!$store) {
            return response()->json([
                'message' => 'Контрагент не найден или не принадлежит текущему торговому',
                'data' => [],
            ]);
        }
        $data = $request->validated();
        $store->update($data);

        return new StoreResource($store);
    }

    public function destroy(Request $request, $storeId)
    {
        $user = $request->user();
        $store = Store::where('id', $storeId)
            ->whereHas('counterparty.representatives', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->first();
        if (!$store) {
            return response()->json([
                'message' => 'Контрагент не найден или не принадлежит текущему торговому',
                'data' => [],
            ]);
        }

        DB::transaction(function () use ($store) {
            $store->orders()->delete();
            $store->delete();
        });


        return response()->json(['message' => 'Магазин удачно удален']);
    }
}
