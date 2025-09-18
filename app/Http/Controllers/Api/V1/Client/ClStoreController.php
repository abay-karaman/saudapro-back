<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Resources\V1\StoreResource;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClStoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $counterpartyID = $user->counterparty?->id;
        $stores = Store::where('counterparty_id', $counterpartyID)->get();
        return StoreResource::collection($stores);
    }

    public function show($storeId)
    {
        $store = Store::where('id', $storeId)->firstOrFail();
        return new StoreResource($store);
    }

    public function store(StoreStoreRequest $request)
    {
        $user = $request->user();
        // Проверка наличия контрагента
        if (!$user->counterparty) {
            return response()->json([
                'message' => 'К вам не прикреплен контрагент, свяжитесь с администрацией.',
            ], 400);
        }
        $counterpartyID = $user->counterparty?->id;
        $data = $request->validated();
        $data['counterparty_id'] = $counterpartyID;
        $store = Store::create($data);
        return new StoreResource($store);
    }

    public function update(UpdateStoreRequest $request, $storeId)
    {
        $store = Store::where('id', $storeId)->firstOrFail();
        $data = $request->validated();

        $store->update($data);
        return new StoreResource($store);
    }

    public function destroy($storeId)
    {
        $store = Store::where('id', $storeId)->firstOrFail();
        DB::transaction(function () use ($store) {
            $store->orders()->delete();
            $store->delete();
        });

        return response()->json(['message' => 'Магазин удачно удален']);
    }
}
