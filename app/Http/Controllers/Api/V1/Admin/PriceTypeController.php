<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PriceType\StoreUnitRequest;
use App\Http\Requests\Admin\PriceType\UpdateUnitRequest;
use App\Http\Resources\Admin\PriceTypeResource;
use App\Models\PriceType;

class PriceTypeController extends Controller
{
    public function index()
    {
        $priceTypes = PriceType::all();
        return PriceTypeResource::collection($priceTypes);
    }


    public function show($priceTypeId)
    {
        $priceType = PriceType::where('id', $priceTypeId)
            ->firstOrFail();
        return new PriceTypeResource($priceType);
    }

    public function store(StoreUnitRequest $request)
    {
        return new PriceTypeResource(PriceType::create($request->validated()));
    }

    public function update(UpdateUnitRequest $request, $priceTypeId)
    {
        $priceType = PriceType::where('id', $priceTypeId)->firstOrFail();
        $priceType->update($request->validated());
        return new PriceTypeResource($priceType);
    }

    public function destroy($priceTypeId)
    {
        $priceType = PriceType::where('id', $priceTypeId)->firstOrFail();
        $priceType->delete();
        return response()->json([
            'message' => 'Товар удален'
        ]);
    }
}
