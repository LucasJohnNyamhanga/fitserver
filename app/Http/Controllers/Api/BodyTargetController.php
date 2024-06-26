<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BodyTypeRequest;
use App\Models\BodyTarget;
use App\Models\Equipment;
use App\Models\Meal;
use App\Models\Package;
use Illuminate\Http\Request;

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
        $bodyTarget = BodyTarget::with(['exercises' => function ($query) {
                $query->latest()->take(5);
            }])
            ->get();

            $dietary = Package::where('target', 'Dietary')
            ->latest()->take(4)
            ->get();

            $transformation = Package::where('target', 'Transformation')
            ->latest()->take(4)
            ->get();


        return response()->json([
            'bodyTarget' => $bodyTarget,
            'dietary'=> $dietary,
            'transformation'=> $transformation,
        ], 200);
    }


    public function getBodyListWithExerciseForPicking(BodyTypeRequest $request)
    {
        $bodyTarget = BodyTarget::with(['exercises' => function ($query) {
                $query->latest();
            }])
            ->get();
        return response()->json([
            'bodyTarget' => $bodyTarget,
        ], 200);
    }
}
