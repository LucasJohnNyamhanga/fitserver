<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MealRequest;
use App\Models\Meal;
use Illuminate\Support\Facades\Validator;

class MealController extends Controller
{
     public function storeMeal(MealRequest $request)
    {


        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'detail' => 'required|string',
            'packageId' => 'required|integer',
            
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }

        $title = $request->input('title');
        $detail = $request->input('detail');
        $packageId = $request->input('packageId');

        // Check for existing exercise
        $existingExercise = Meal::where('title', $title)->first();
        if ($existingExercise) {
            return response()->json(['message' => 'Meal already available.'], 409);
        }

        // Create the exercise
        $meal = Meal::create([
            'title' => $title,
            'detail' => $detail,
            'package_id' => $packageId,
        ]);

        return response()->json(['message' => 'Meal has been successfully saved to database.'], 200);
    }
}