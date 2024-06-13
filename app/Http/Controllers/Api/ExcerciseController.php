<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExcerciseRequest;
use App\Models\Exercises;
use Illuminate\Http\Request;

class ExcerciseController extends Controller
{
     public function storeExcercise(ExcerciseRequest $request)
    {
        $jina = $request->jina;
        $maelezo = $request->maelezo;
        $ugumu = $request->ugumu;
        $muda = $request->muda;
        $picha = $request->picha;
        $muscleName = $request->muscleName;

        if (empty($muscleName)  || empty($jina)|| empty($picha)) {
            return response()->json(['message' => 'Fill in all empty fields'], 400);
        }

        $existingExcercise = Exercises::where('jina', $jina)->first();

        if ($existingExcercise) {
            return response()->json(['message' => 'Excercise already available.'], 409);
        }

        $excercise = Exercises::create([
            'jina' => $jina,
            'maelezo' => $maelezo,
            'ugumu' => $ugumu,
            'muda' => $muda,
            'picha' => $picha,
            'muscleName' => $muscleName,
        ]);

        return response()->json(['message' => 'Excercise has been successfully saved to database.'], 200);
    }
}