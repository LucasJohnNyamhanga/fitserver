<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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

    public function getEquipmentWithExercises(EquipmentRequest $request)
    {
         $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }


        $id = $request->input('id');
        $bodyTarget = Equipment::with(['exercises' => function ($query) {
                $query->latest()->take(10);
            }])
            ->where('id', $id)
            ->first();
        return response()->json([
            'equipments' => $bodyTarget,
        ], 200);
    }

    public function getEquipments(EquipmentRequest $request)
    {
        $bodyTarget = Equipment::all();
        return response()->json([
            'equipments' => $bodyTarget,
        ], 200);
    }

    public function editEquipment(EquipmentRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'jina' => 'required|string|max:255',
            'picha' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }

        $id = $request->input('id');
        $jina = $request->input('jina');
        $picha = $request->input('picha');

        // Retrieve the package by its ID
        $equipment = Equipment::find($id);

        if (!$equipment) {
            return response()->json(['message' => 'Equipment not found.'], 404);
        }

        // Check for existing package with the same title (excluding the current package being edited)
        $existingEquipment = Equipment::where('jina', $jina)->where('id', '!=', $id)->first();
        if ($existingEquipment) {
            return response()->json(['message' => 'Another equipment using this name already exists.'], 409);
        }

        // Update the existing package
        $equipment->update([
            'jina' => $jina,
            'picha' => $picha,
        ]);

        return response()->json(['message' => 'Equipment has been successfully updated.'], 200);
    }

    public function deleteEquipment(EquipmentRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }


        $id = $request->input('id');

        $equipment = Equipment::find($id);

        if (!$equipment) {
            return response()->json(['message' => 'Equipment not found.'], 404);
        }

        $equipment->delete();

        return response()->json(['message' => 'Equipment has been deleted.'], 200);
    }
}
