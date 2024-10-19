<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExcerciseRequest;
use App\Models\Exercise;
use App\Models\Instruction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ExcerciseController extends Controller
{
    public function storeExcercise(ExcerciseRequest $request)
    {
        $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'details' => 'required|string',
        'difficulty' => 'required|string',
        'time' => 'required|string',
        'image' => 'required|string',
        'instructions' => 'required|string',
        'muscleName' => 'required|string',
        'bodyPartIds' => 'required|array',
        'equipmentIds' => 'required|array',
        'seti' => 'required|integer',
        'repitition' => 'required|integer',
        'videoLink' => 'string',
    ]);


    if ($validator->fails()) {
        return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
    }

        $name = $request->input('name');
        $details = $request->input('details');
        $difficulty = $request->input('difficulty');
        $time = $request->input('time');
        $image = $request->input('image');
        $instructions = $request->input('instructions');
        $muscleName = $request->input('muscleName');
        $bodyPartIds = $request->input('bodyPartIds');
        $equipmentIds = $request->input('equipmentIds');
        $seti = $request->input('seti');
        $repitition = $request->input('repitition');
        $videoLink = $request->input('videoLink');

        // Check for existing exercise
        $existingExercise = Exercise::where('jina', $name)->first();
        if ($existingExercise) {
            return response()->json(['message' => 'Exercise already available.'], 409);
        }

        // Create the exercise
        $exercise = Exercise::create([
            'jina' => $name,
            'maelezo' => $details,
            'ugumu' => $difficulty,
            'muda' => $time,
            'instructions' => $instructions,
            'picha' => $image,
            'muscleName' => $muscleName,
            'trainer_id'=>Auth::id(),
            'active'=>false,
            'seti' => $seti,
            'repetition' => $repitition,
            'video' => $videoLink,
        ]);

        // Attach body parts and equipment
        foreach ($bodyPartIds as $bodyPartId) {
            $exercise->bodyTarget()->attach($bodyPartId);
        }

        foreach ($equipmentIds as $equipmentId) {
            $exercise->equipment()->attach($equipmentId);
        }

        return response()->json(['message' => 'Exercise has been successfully saved to database.'], 200);
    }

    public function getExercises(ExcerciseRequest $request)
    {
        $exercises = Exercise::all();
        return response()->json([
            'exercises' => $exercises,
        ], 200);
    }

    public function getExerciseWithBodyPartAndEquipment(ExcerciseRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }

        $id = $request->input('id');

        $exercise = Exercise::with(['bodyTarget' => function ($query) {
                $query->latest()->take(10);
            },'equipment' => function ($query) {
                $query->latest()->take(10);
            }])->where('id', $id)
            ->first();
        return response()->json([
            'exercise' => $exercise,
        ], 200);
    }

    public function editExcercise(ExcerciseRequest $request)
    {
        $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'details' => 'required|string',
        'difficulty' => 'required|string',
        'time' => 'required|string',
        'image' => 'required|string',
        'instructions' => 'required|string',
        'muscleName' => 'required|string',
        'bodyPartIds' => 'required|array',
        'equipmentIds' => 'required|array',
        'seti' => 'required|integer',
        'repitition' => 'required|integer',
        'videoLink' => 'string',
        'id' => 'required|integer',
    ]);


    if ($validator->fails()) {
        return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
    }

        $name = $request->input('name');
        $details = $request->input('details');
        $difficulty = $request->input('difficulty');
        $time = $request->input('time');
        $image = $request->input('image');
        $instructions = $request->input('instructions');
        $muscleName = $request->input('muscleName');
        $bodyPartIds = $request->input('bodyPartIds');
        $equipmentIds = $request->input('equipmentIds');
        $seti = $request->input('seti');
        $repitition = $request->input('repitition');
        $videoLink = $request->input('videoLink');
        $id = $request->input('id');

        $existingExercise = Exercise::where('jina', $name)
        ->where('id', '!=', $id)
        ->first();

        if ($existingExercise) {
            return response()->json(['message' => 'Another exercise is using the same name.'], 409);
        }

        // Retrieve the exercise by ID or fail if not found
        $exercise = Exercise::findOrFail($id);

        // Update the exercise fields
        $exercise->update([
            'jina' => $name,
            'maelezo' => $details,
            'ugumu' => $difficulty,
            'muda' => $time,
            'instructions' => $instructions,
            'picha' => $image,
            'muscleName' => $muscleName,
            'trainer_id' => Auth::id(),
            'active' => false,
            'seti' => $seti,
            'repetition' => $repitition,
            'video' => $videoLink,
        ]);

        // Sync relationships with body parts and equipment
        $exercise->bodyTarget()->sync($bodyPartIds);
        $exercise->equipment()->sync($equipmentIds);

        return response()->json(['message' => 'Exercise has been successfully updated.'], 200);
    }

    public function changeExerciseActivation(ExcerciseRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }


        $id = $request->input('id');

        $exercise = Exercise::find($id);

        if (!$exercise) {
            return response()->json(['message' => 'Exercise not found.'], 404);
        }

        $exercise->active = !$exercise->active;
        $exercise->save();

        return response()->json(['message' => 'Exercise active status changed.'], 200);
    }

    public function deleteExercise(ExcerciseRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }


        $id = $request->input('id');

        $exercise = Exercise::find($id);

        if (!$exercise) {
            return response()->json(['message' => 'Exercise not found.'], 404);
        }

        $exercise->delete();

        return response()->json(['message' => 'Exercise has been deleted.'], 200);
    }
}