<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstructionRequest;
use App\Models\Instruction;
use Illuminate\Http\Request;

class InstructionController extends Controller
{
    public function storeInstruction(InstructionRequest $request)
    {
        $maelezo = $request->maelezo;
        $exercise_id = $request->exerciseId;

        if (empty($maelezo)) {
            return response()->json(['message' => 'Fill in all empty fields'], 400);
        }

        $bodyTarget = Instruction::create([
            'maelezo' => $maelezo,
            'exercise_id' => $exercise_id,
        ]);

        return response()->json(['message' => 'Body Part has been successfully saved to database.'], 200);
    }
}
