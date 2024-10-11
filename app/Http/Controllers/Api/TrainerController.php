<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrainerRequest;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

         // Use a database transaction to ensure data consistency
        DB::beginTransaction();

        try {
            Trainer::create([
            'location' => $address,
            'bio' => $bio,
            'services' => $services,
            'active' => true,
            'is_super' => false,
            'user_id' => Auth::id(),
            ]);

            $user = User::find(Auth::id());
            if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
            }

            $user->is_trainer = !$user->is_trainer;
            $user->save();

            // Commit the transaction if everything is successful
            DB::commit();

            return response()->json(['message' => 'You have successful become a trainer.'], 200);

        } catch (\Exception $ex) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to register trainer',
                'message' => $ex->getMessage()
            ], 500);
        }

        

        
    }
}
