<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrainerRequest;
use App\Models\Trainer;
use Illuminate\Support\Facades\Auth;

class TrainerController extends Controller
{
    public function createTrainer(TrainerRequest $request)
    {
        $address = $request->address;
        $bio = $request->bio;
        $services = $request->services;

        if (empty($address) || empty($bio)|| empty($services)) {
            return response()->json(['message' => 'Fill in all empty fields'], 400);
        }

        Trainer::create([
            'location' => $address,
            'bio' => $bio,
            'services' => $services,
            'active' => true,
            'is_super' => false,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'You have successful become a trainer.'], 200);
    }
}
