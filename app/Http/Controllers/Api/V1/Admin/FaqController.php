<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Faq\StoreFaqRequest;
use App\Http\Requests\Admin\Faq\UpdateFaqRequest;
use App\Http\Resources\Admin\FaqResource;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::all();
        return FaqResource::collection($faqs);
    }


    public function show($faqId)
    {
        $faq = Faq::where('parent_id', $faqId)
            ->firstOrFail();
        return new FaqResource($faq);
    }

    public function store(StoreFaqRequest $request)
    {
        return new FaqResource(Faq::create($request->validated()));
    }

    public function update(UpdateFaqRequest $request, $faqId)
    {
        $faq = Faq::where('id', $faqId)->firstOrFail();
        $faq->update($request->validated());
        return new FaqResource($faq);
    }

    public function destroy($faqId)
    {
        $faq = Faq::where('id', $faqId)->firstOrFail();
        $faq->delete();
        return response()->json([
            'message' => 'Пользователь удален'
        ]);
    }
}
