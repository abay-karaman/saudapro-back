<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id'  => 'nullable|integer|exists:roles,id',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role_id'  => $request->role_id,
            'status'   => 'waiting', // по умолчанию ждёт подтверждения
        ]);

        return response()->json([
            'message' => 'Вы успешно зарегистрированы. Ожидайте подтверждения администратора.',
            'user'    => $user,
            'token'   => $user->createToken("user: {$user->email}")->plainTextToken
        ]);
    }

    /**
     * Логин (email + пароль)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Неверный email или пароль'
            ], 401);
        }

        // проверка статуса
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Аккаунт не активен. Ожидайте подтверждения.',
                'status'  => $user->status
            ], 403);
        }

        // сбрасываем старые токены
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Успешный вход',
            'user'    => $user,
            'token'   => $user->createToken("user: {$user->email}")->plainTextToken
        ]);
    }

    /**
     * Выход
     */
    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Вы вышли из системы'
        ]);
    }
}
