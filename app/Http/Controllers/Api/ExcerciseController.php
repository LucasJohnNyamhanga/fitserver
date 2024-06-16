<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExcerciseRequest;
use App\Models\Exercise;
use App\Models\Instruction;
use Illuminate\Support\Facades\Validator;

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
        'instructions' => 'required|array',
        'muscleName' => 'required|string',
        'bodyPartIds' => 'required|array',
        'equipmentIds' => 'required|array',
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
            'picha' => $image,
            'muscleName' => $muscleName,
        ]);

        // Attach body parts and equipment
        foreach ($bodyPartIds as $bodyPartId) {
            $exercise->bodyTarget()->attach($bodyPartId);
        }

        foreach ($equipmentIds as $equipmentId) {
            $exercise->equipment()->attach($equipmentId);
        }

        // Create instructions
        foreach ($instructions as $instruction) {
            Instruction::create([
                'maelezo' => $instruction,
                'exercise_id' => $exercise->id,
            ]);
        }

        return response()->json(['message' => 'Exercise has been successfully saved to database.'], 200);
    }
}