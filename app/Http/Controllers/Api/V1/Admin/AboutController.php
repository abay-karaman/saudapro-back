<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\About\StoreAboutRequest;
use App\Http\Requests\Admin\About\UpdateAboutRequest;
use App\Http\Resources\Admin\AboutResource;
use App\Models\About;

class AboutController extends Controller
{
    public function index()
    {
        $abouts = About::all();
        return AboutResource::collection($abouts);
    }


    public function show($aboutId)
    {
        $about = About::where('id', $aboutId)
            ->firstOrFail();
        return new AboutResource($about);
    }

    public function store(StoreAboutRequest $request)
    {
        return new AboutResource(About::create($request->validated()));
    }

    public function update(UpdateAboutRequest $request, $aboutId)
    {
        $about = About::where('id', $aboutId)->firstOrFail();
        $about->update($request->validated());
        return new AboutResource($about);
    }

    public function destroy($aboutId)
    {
        $about = About::where('id', $aboutId)->firstOrFail();
        $about->delete();
        return response()->json([
            'message' => 'Пользователь удален'
        ]);
    }
}
