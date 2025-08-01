<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrainerRequest;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    public function getUsersWhoAreTrainers(TrainerRequest $request)
    {
        $users = User::with(['trainer', 'customer'])
            ->whereHas('trainer')
            ->latest()
            ->get();

        return response()->json([
            'trainers' => $users,
        ], 200);
    }


    public function toggleActiveStatus(TrainerRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'trainerId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Trainer ID not provided',
                'errors' => $validator->errors()->first()
            ], 400);
        }

        try {
            $trainer = Trainer::findOrFail($request->input('trainerId'));
            $trainer->active = !$trainer->active;
            $trainer->save();

            return response()->json([
                'message' => $trainer->active ? 'Trainer activated successfully.' : 'Trainer deactivated successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Trainer not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while toggling trainer status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleSuperStatus(TrainerRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'trainerId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Trainer ID not provided',
                'errors' => $validator->errors()->first()
            ], 400);
        }

        try {
            $trainer = Trainer::findOrFail($request->input('trainerId'));
            $trainer->is_super = !$trainer->is_super;
            $trainer->save();

            return response()->json([
                'message' => $trainer->is_super ? 'Super Trainer activated successfully.' : 'Super Trainer deactivated successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Trainer not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while toggling trainer status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
