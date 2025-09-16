<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageController extends Controller
{

    // Массовая загрузка (multiple files)
    public function store(Request $request, $productId, $phone)
    {
        $product = Product::where('id', $productId)->first();
        $request->validate([
            'media' => 'required|array',
            'media.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,mp4,mov,webm',
        ]);

        $uploaded = [];

        foreach ($request->file('media') as $file) {
            $isVideo = Str::startsWith($file->getClientMimeType(), 'video/');
            $type = $isVideo ? 'video' : 'image';

            $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $folder = "products/{$phone}/{$product->code}";
            // Относительный путь
            $path = "{$folder}/{$filename}";

            Storage::disk('s3')->putFileAs($folder, $file, $filename, 'public');

            $url = Storage::disk('s3')->url($path);

            $maxSort = ProductImage::where('product_id', $product->id)->max('sort_order') ?? 0;

            $media = ProductImage::create([
                'product_id' => $product->id,
                'type' => $type,
                'image_path' => $url,
                'sort_order' => $maxSort + 1,
            ]);

            $uploaded[] = $media;
        }

        return response()->json(['uploaded' => $uploaded], 201);
    }

    // Сделать главным
    public function setMain($productId, $mediaId)
    {
        $product = Product::where('id', $productId)->first();
        $media = ProductImage::where('id', $mediaId)->first();
        if ($media->product_id !== $product->id) {
            return response()->json(['message' => 'Media not belongs to this product'], 422);
        }

        ProductImage::where('product_id', $product->id)->update(['is_main' => false]);
        $media->update(['is_main' => true]);

        return response()->json(['ok' => true]);
    }

    // Переупорядочивание: ожидаем array order => [mediaId,...]
    public function reorder(Request $request, $productId)
    {
        $product = Product::where('id', $productId)->first();
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $index => $id) {
            ProductImage::where('id', $id)->where('product_id', $product->id)->update(['sort_order' => $index]);
        }

        return response()->json(['ok' => true]);
    }

    // Удаление
    public function destroy($productId, $mediaId)
    {
        $product = Product::where('id', $productId)->first();
        $media = ProductImage::where('id', $mediaId)->first();

        if ($media->product_id !== $product->id) {
            return response()->json(['message' => 'Media not belongs to this product'], 422);
        }

        // Удалим из S3
        if (Storage::disk('s3')->exists($media->image_path)) {
            Storage::disk('s3')->delete($media->image_path);
        }

        $wasMain = $media->is_main;
        $media->delete();

        // Если удалили главное — назначим первое по sort_order
        if ($wasMain) {
            $first = ProductImage::where('product_id', $product->id)->orderBy('sort_order')->first();
            if ($first) $first->update(['is_main' => true]);
        }

        return response()->json(['ok' => true]);
    }
}
