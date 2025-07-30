<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BodyTypeRequest;
use App\Models\BodyTarget;
use App\Models\Equipment;
use App\Models\Meal;
use App\Models\Package;
use App\Models\Trainer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BodyTargetController extends Controller
{
    public function storeBodyTarget(BodyTypeRequest $request)
    {
        $jina = $request->name;
        $picha = $request->image;

        if (empty($jina) || empty($picha)) {
            return response()->json(['message' => 'Fill in all empty fields'], 400);
        }

        $existingBodyTarget = BodyTarget::where('jina', $jina)->first();

        if ($existingBodyTarget) {
            return response()->json(['message' => 'Body Part already available.'], 409);
        }

        $bodyTarget = BodyTarget::create([
            'jina' => $jina,
            'picha' => $picha,
        ]);

        return response()->json(['message' => 'Body Part has been successfully saved to database.'], 200);
    }

    public function getBodyListAndEquipments(BodyTypeRequest $request)
    {
        $bodyTarget = BodyTarget::all();
        $equipment = Equipment::all();
        return response()->json(['bodyTarget' => $bodyTarget, 'equipment' => $equipment], 200);
    }

    public function getBodyListWithExercise(BodyTypeRequest $request)
    {
        $userId = Auth::id();

        // Load only active BodyTargets that have at least one exercise from a super trainer
        $bodyTarget = BodyTarget::with(['exercises' => function ($query) {
                $query->where('active', true)
                    ->latest()
                    ->take(30);
            }])
            ->where('active', true)
            ->whereHas('exercises.trainer', function ($query) {
                $query->where('is_super', true); // Only from super trainers
            })
            ->get();

        // Helper query to fetch packages by target
        $basePackageQuery = function ($target) use ($userId) {
            return Package::where('target', $target)
                ->where('active', true)
                ->whereDoesntHave('purchases', function ($query) use ($userId) {
                    $query->where('user_id', $userId); // Not already purchased
                })
                ->latest()
                ->take(30)
                ->get();
        };

        $dietary = $basePackageQuery('Dietary');
        $transformation = $basePackageQuery('Transformation');

        return response()->json([
            'bodyTarget' => $bodyTarget,
            'dietary' => $dietary,
            'transformation' => $transformation,
        ], 200);
    }


    public function getBodyListWithExerciseForPicking(BodyTypeRequest $request)
    {
        $trainer = Trainer::where('user_id', Auth::id())->first();

        if (!$trainer) {
            return response()->json(['message' => 'Trainer not found'], 404);
        }

        $bodyTarget = BodyTarget::with(['exercises' => function ($query) use ($trainer) {
            $query->where('trainer_id', $trainer->id)->latest();
        }])->get();

        return response()->json([
            'bodyTarget' => $bodyTarget,
        ], 200);
    }


    public function getBodyList(BodyTypeRequest $request)
    {
        $bodyTarget = BodyTarget::all();
        return response()->json(['bodyTarget' => $bodyTarget,], 200);
    }

    public function getBodyPartWithExerciseTrainerSpecific(BodyTypeRequest $request)
    {
        $bodyPartId = $request->id;

        // Retrieve the authenticated trainer
        $trainer = Trainer::where('user_id', Auth::id())->first();

        if($trainer){
            $bodyTarget = BodyTarget::with([
                'exercises' => function ($query) use ($trainer) {
                    $query->where('trainer_id', $trainer->id)
                        ->latest()
                        ->take(5);
                    }])
                ->where('id', $bodyPartId)
                ->first();
            return response()->json([
                'bodyTarget' => $bodyTarget,
            ], 200);
        }

        return response()->json([
                'message' => 'Your not a trainer',
            ], 500);
    }

    public function editBodyTarget(BodyTypeRequest $request)
    {
        

         $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }

        $name = $request->input('name');
        $picha = $request->input('image');
        $id = $request->input('id');

        $body = BodyTarget::findOrFail($id);

        if (!$body) {
            return response()->json(['message' => 'Body part not found.'], 404);
        }

        $body->jina = $name;
        $body->picha = $picha;
        $body->save();

        return response()->json(['message' => 'Body part details changed.'], 200);
    }

    public function changeBodyActivation(BodyTypeRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }


        $id = $request->input('id');

        $body = BodyTarget::find($id);

        if (!$body) {
            return response()->json(['message' => 'Body part not found.'], 404);
        }

        $body->active = !$body->active;
        $body->save();

        return response()->json(['message' => 'Body part active status changed.'], 200);
    }

    public function deleteBodyPart(BodyTypeRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }


        $id = $request->input('id');

        $body = BodyTarget::find($id);

        if (!$body) {
            return response()->json(['message' => 'Body part not found.'], 404);
        }

        $body->delete();

        return response()->json(['message' => 'Body part has been deleted.'], 200);
    }
}
