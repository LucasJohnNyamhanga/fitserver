<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExcerciseRequest;
use App\Models\Exercise;
use App\Models\Instruction;

class ExcerciseController extends Controller
{
    public function storeExcercise(ExcerciseRequest $request)
    {
        $jina = $request->name;
        $maelezo = $request->details;
        $ugumu = $request->difficulty;
        $muda = $request->time;
        $picha = $request->image;
        $maelekezo = $request->instructions; // array
        $muscleName = $request->muscleName;
        $bodyPartIds = $request->bodyPartIds; // array
        $equipmentIds = $request->equipmentIds; // array

        // Check for empty fields
        if (empty($jina) || empty($maelezo) || empty($ugumu) || empty($muda) || empty($picha) || empty($maelekezo) || empty($muscleName) || empty($bodyPartIds) || empty($equipmentIds)) {
            return response()->json(['message' => 'Fill in all empty fields'], 400);
        }

        // Check for existing exercise
        $existingExcercise = Exercise::where('jina', $jina)->first();
        if ($existingExcercise) {
            return response()->json(['message' => 'Exercise already available.'], 409);
        }

        // Create the exercise
        $excercise = Exercise::create([
            'jina' => $jina,
            'maelezo' => $maelezo,
            'ugumu' => $ugumu,
            'muda' => $muda,
            'picha' => $picha,
            'muscleName' => $muscleName,
        ]);

        // Attach body parts
        $excercise->bodyTarget()->attach($bodyPartIds);

        // Attach equipment
        $excercise->equipment()->attach($equipmentIds);

        // Create instructions
        foreach ($maelekezo as $inst) {
            Instruction::create([
                'maelezo' => $inst,
                'exercise_id' => $excercise->id,
            ]);
        }

        return response()->json(['message' => 'Exercise has been successfully saved to database.'], 200);
    }

}