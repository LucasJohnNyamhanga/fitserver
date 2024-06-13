<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;

class EquipmentController extends Controller
{
    public function storeEquipment(EquipmentRequest $request)
    {
        $jina = $request->name;
        $image = $request->image;

        if (empty($jina) || empty($image)) {
            return response()->json(['message' => 'Fill in all empty fields'], 400);
        }

        $existingBodyTarget = Equipment::where('jina', $jina)->first();

        if ($existingBodyTarget) {
            return response()->json(['message' => 'Equipment name already available.'], 409);
        }

        $bodyTarget = Equipment::create([
            'jina' => $jina,
            'picha'=> $image,
        ]);

        return response()->json(['message' => 'Body Part has been successfully saved to database.'], 200);
    }
}
