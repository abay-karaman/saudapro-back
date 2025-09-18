<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banner\StoreBannerRequest;
use App\Http\Requests\Admin\Banner\UpdateBannerRequest;
use App\Http\Resources\Admin\BannerResource;
use App\Models\Banner;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::all();
        return BannerResource::collection($banners);
    }


    public function show($bannerId)
    {
        $banner = Banner::where('id', $bannerId)
            ->firstOrFail();
        return new BannerResource($banner);
    }

    public function store(StoreBannerRequest $request)
    {
        return new BannerResource(Banner::create($request->validated()));
    }

    public function update(UpdateBannerRequest $request, $bannerId)
    {
        $banner = Banner::where('id', $bannerId)->firstOrFail();
        $banner->update($request->validated());
        return new BannerResource($banner);
    }

    public function destroy($bannerId)
    {
        $banner = Banner::where('id', $bannerId)->firstOrFail();
        $banner->delete();
        return response()->json([
            'message' => 'Пользователь удален'
        ]);
    }
}
