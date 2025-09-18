<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\About;
use App\Models\Banner;
use App\Models\Faq;

class InfoController extends Controller
{
    public function faqs()
    {
        return response()->json(Faq::all());
    }

    public function about()
    {
        return response()->json(About::first());
    }

    public function banners()
    {
        return response()->json(Banner::where('is_active', true)
                ->orderBy('order')
                ->get()
        );
    }
}
