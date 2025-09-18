<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\UserResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->loadCount('orders')->loadCount('counterparties');

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user);
    }

    public function destroy()
    {
        $user = Auth::user();

        // Если есть связанные данные — можно удалить
        $user->favorites()->detach();
        //$user->orders()->delete();

        $user->delete();

        return response()->json([
            'message' => 'Аккаунт успешно удалён'
        ]);
    }
}
