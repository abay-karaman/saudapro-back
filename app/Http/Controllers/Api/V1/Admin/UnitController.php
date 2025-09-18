<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Unit\StoreUnitRequest;
use App\Http\Requests\Admin\Unit\UpdateUnitRequest;
use App\Http\Resources\Admin\UnitResource;
use App\Models\Unit;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::all();
        return UnitResource::collection($units);
    }


    public function show($unitId)
    {
        $unit = Unit::where('id', $unitId)
            ->firstOrFail();
        return new UnitResource($unit);
    }

    public function store(StoreUnitRequest $request)
    {
        return new UnitResource(Unit::create($request->validated()));
    }

    public function update(UpdateUnitRequest $request, $unitId)
    {
        $unit = Unit::where('id', $unitId)->firstOrFail();
        $unit->update($request->validated());
        return new UnitResource($unit);
    }

    public function destroy($unitId)
    {
        $unit = Unit::where('id', $unitId)->firstOrFail();
        $unit->delete();
        return response()->json([
            'message' => 'Товар удален'
        ]);
    }
}
