<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        // Получаем все избранные записи пользователя
        $favorites = Auth::user()->favorites()
            ->with(['category', 'images']) // если нужно
            ->get();

        // Возвращаем через ProductResource
        return ProductResource::collection($favorites);
    }

    public function toggle(Request $request, $productId)
    {
        $user = $request->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['message' => 'Removed from favorites', 'is_favorite' => false]);
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'product_id' => $productId
            ]);
            return response()->json(['message' => 'Added to favorites', 'is_favorite' => true]);
        }
    }
}
