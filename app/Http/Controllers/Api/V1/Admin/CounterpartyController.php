<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Counterparty\StoreCounterpartyRequest;
use App\Http\Requests\Admin\Counterparty\UpdateCounterpartyRequest;
use App\Http\Resources\Admin\CounterpartyResource;
use App\Models\Counterparty;

class CounterpartyController extends Controller
{
    public function index()
    {
        $counterparties = Counterparty::all();
        return CounterpartyResource::collection($counterparties);
    }


    public function show($counterpartyId)
    {
        $counterparty = Counterparty::where('id', $counterpartyId)
            ->firstOrFail();
        return CounterpartyResource::collection($counterparty);
    }

    public function store(StoreCounterpartyRequest $request)
    {
        return new CounterpartyResource(Counterparty::create($request->validated()));
    }

    public function update(UpdateCounterpartyRequest $request, $counterpartyId)
    {
        $counterparty = Counterparty::where('id', $counterpartyId)->firstOrFail();
        $counterparty->update($request->validated());
        return new CounterpartyResource($counterparty);
    }

    public function destroy($counterpartyId)
    {
        $counterparty = Counterparty::where('id', $counterpartyId)->firstOrFail();
        $counterparty->delete();
        return response()->json([
            'message' => 'Контрагент удален'
        ]);
    }
}
