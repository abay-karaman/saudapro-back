<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Counterparty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function requestCode(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^(?:\+7|8|7)7\d{9}$/']
        ]);

        $phone = $this->normalizePhone($request->phone);
        $code = rand(1000, 9999);

        $user = User::where('phone', $phone)->first();
        $appType = (int)$request->header('app-type');

        // 1) Пользователя нет → новый -> отправляем код и переводим в register
        if (!$user) {
            // Новый пользователь
            // Только кэшируем, в БД не пишем
            Cache::put("verify_code_{$phone}", $code, now()->addMinutes(5));

            $this->sendCodeWhatsApp($phone, $code);

            return response()->json([
                'message' => 'Пользователь новый. Код отправлен в WhatsApp.',
                'status' => 'new',
                'next_step' => 'register'
            ]);
        }

        // 2) Пользователь есть, но не активен
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Аккаунт не активен. Ожидайте подтверждения.',
                'status' => $user->status,
            ]);
        }

        // 3) Пользователь есть, активен, но роль не совпадает с app_type
        if ($user->role_id != 1 && $user->role_id !== $appType) {
            return response()->json([
                'message' => 'Доступ запрещён для этого приложения.',
                'status' => 'forbidden'
            ], 403);
        }

        // Обновляем verification_code для активных пользователей
        Cache::put("verify_code_{$phone}", $code, now()->addMinutes(5));

        $this->sendCodeWhatsApp($phone, $code);

        return response()->json([
            'message' => 'Код отправлен в WhatsApp.',
            'status' => 'active',
            'next_step' => 'verify_code'
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'name' => 'required|string|max:255',
            'role_id' => 'nullable|integer|exists:roles,id',
            'code' => 'required|string'
        ]);

        $phone = $this->normalizePhone($request->phone);
        $appType = (int)$request->header('X-Role');
        $codeFromCache = Cache::get("verify_code_{$phone}");

        if (User::where('phone', $phone)->exists()) {
            return response()->json(['message' => 'Пользователь уже существует'], 400);
        }

        if ($codeFromCache != $request->code) {
            return response()->json(['message' => 'Неверный код или номер'], 400);
        }

        // Проверка role ↔ app_type
        if ((int)$request->role_id !== $appType) {
            return response()->json([
                'message' => 'Роль не соответствует приложению.',
                'status' => 'forbidden'
            ], 403);
        }

        // Обновим данные
        $user = User::firstOrCreate([
            'name' => $request->name,
            'phone' => $phone,
            'role_id' => $request->role_id,
            'status' => 'waiting',
        ]);

//        // Если регистрируется 2-торговый, то привязываем ему его контрагенты по номеру тел
//        if ($user->role_id === 2) {
//            Counterparty::where('reps_phone', $user->phone)
//                ->update(['representative_id' => $user->id]);
//        }

        return response()->json([
            'message' => 'Вы успешно зарегистрированы. Ожидайте подтверждения администратора.',
            'user' => $user,
            'token' => $user->createToken("user: {$user->phone}")->plainTextToken
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string'
        ]);

        $phone = $this->normalizePhone($request->phone);
        $appType = (int)$request->header('app-type');
        $codeFromCache = Cache::get("verify_code_{$phone}");

        $user = User::where('phone', $phone)->first();

        //Если пользователь не найден
        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }
        // Проверка кода
        if (!$codeFromCache || $codeFromCache != $request->code) {
            return response()->json(['message' => 'Неверный код или номер'], 400);
        }
        // Проверка статуса
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Аккаунт не активен. Ожидайте подтверждения.',
                'status' => $user->status
            ], 403);
        }
        // Проверка role ↔ app_type
        if ((int)$user->role_id !== $appType) {
            return response()->json([
                'message' => 'Доступ запрещён для этого приложения.',
                'status' => 'forbidden'
            ], 403);
        }

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Успешный вход',
            'user' => $user,
            'token' => $user->createToken("user: {$user->phone}")->plainTextToken
        ]);
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out'
        ]);
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        $phone = str_starts_with($phone, '8') ? '7' . substr($phone, 1) : $phone;
        return $phone;
    }

    private function sendCodeWhatsApp($phone, $code)
    {
        app(WhatsAppService::class)->sendVerificationCode($phone, $code);
    }

}
