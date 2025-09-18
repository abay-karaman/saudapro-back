<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Producer\StoreProducerRequest;
use App\Http\Requests\Admin\Producer\UpdateProducerRequest;
use App\Http\Resources\Admin\ProducerResource;
use App\Models\Producer;

class ProducerController extends Controller
{
    public function index()
    {
        $producers = Producer::all();
        return ProducerResource::collection($producers);
    }


    public function show($producerId)
    {
        $producer = Producer::where('id', $producerId)
            ->firstOrFail();
        return new ProducerResource($producer);
    }

    public function store(StoreProducerRequest $request)
    {
        return new ProducerResource(Producer::create($request->validated()));
    }

    public function update(UpdateProducerRequest $request, $producerId)
    {
        $producer = Producer::where('id', $producerId)->firstOrFail();
        $producer->update($request->validated());
        return new ProducerResource($producer);
    }

    public function destroy($producerId)
    {
        $producer = Producer::where('id', $producerId)->firstOrFail();
        $producer->delete();
        return response()->json([
            'message' => 'Производитель удален'
        ]);
    }
}
