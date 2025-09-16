<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CounterpartyResource;
use App\Models\Counterparty;
use Illuminate\Http\Request;

class ClCounterpartyController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $counterpartyID = $user->counterparty->id;
        $counterparty = Counterparty::where('id', $counterpartyID)->first();
        return new CounterpartyResource($counterparty);
    }
}
