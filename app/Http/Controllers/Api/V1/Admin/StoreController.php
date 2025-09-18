<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Store\StoreStoreRequest;
use App\Http\Requests\Admin\Store\UpdateStoreRequest;
use App\Http\Resources\Admin\StoreResource;
use App\Models\Store;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::all();
        return StoreResource::collection($stores);
    }


    public function show($storeId)
    {
        $store = Store::where('id', $storeId)
            ->firstOrFail();
        return new StoreResource($store);
    }

    public function store(StoreStoreRequest $request)
    {
        return new StoreResource(Store::create($request->validated()));
    }

    public function update(UpdateStoreRequest $request, $storeId)
    {
        $store = Store::where('id', $storeId)->firstOrFail();
        $store->update($request->validated());
        return new StoreResource($store);
    }

    public function destroy($storeId)
    {
        $store = Store::where('id', $storeId)->firstOrFail();
        $store->delete();
        return response()->json([
            'message' => 'Производитель удален'
        ]);
    }
}
