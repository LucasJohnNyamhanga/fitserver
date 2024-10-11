<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUserProfile(UserRequest $request)
    {
        $userId = Auth::id();
        if($userId){
            $userData = User::with(['customer','trainer'])
            ->where('id', Auth::id())
            ->first();

            return response()->json([
                'user' => $userData,
            ], 200);
        }

        return response()->json([
                    'message' => 'User not found.'
                ], 404);
        
    }
}
