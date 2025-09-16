<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles = null)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        if ($roles) {
            $allowedRoles = explode(',', $roles);
            if (!in_array($user->role_id, $allowedRoles)) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }
        }

        return $next($request);
    }
}
