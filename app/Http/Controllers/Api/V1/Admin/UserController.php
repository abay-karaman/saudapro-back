<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return UserResource::collection($users);
    }


    public function show($userId)
    {
        $user = User::where('id', $userId)
            ->firstOrFail();
        return new UserResource($user);
    }

    public function store(StoreUserRequest $request)
    {
        return new UserResource(User::create($request->validated()));
    }

    public function update(UpdateUserRequest $request, $userId)
    {
        $user = User::where('id', $userId)->firstOrFail();
        $user->update($request->validated());
        return new UserResource($user);
    }

    public function destroy($userId)
    {
        $user = User::where('id', $userId)->firstOrFail();
        $user->delete();
        return response()->json([
            'message' => 'Пользователь удален'
        ]);
    }
}
