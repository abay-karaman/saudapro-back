<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function approve(Request $request, $userId)
    {
        $request->validate([
            'status' => 'required',
        ]);

        $user = User::where('id', $userId)->firstOrFail();         //Почему Route Model Binding не работает???

        $user->update([
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'User approved and activated', 'user' => $user]);
    }
}
