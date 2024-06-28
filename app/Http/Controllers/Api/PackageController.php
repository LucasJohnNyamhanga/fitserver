<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PackageRequest;
use App\Models\Package;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    public function storePackage(PackageRequest $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'required|string',
            'type' => 'required|string',
            'price' => 'required|string',
            
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }

        $title = $request->input('title');
        $image = $request->input('image');
        $target = $request->input('type');
        $price = $request->input('price');

        // Check for existing exercise
        $existingExercise = Package::where('title', $title)->first();
        if ($existingExercise) {
            return response()->json(['message' => 'Package already available.'], 409);
        }

        // Create the exercise
        $exercise = Package::create([
            'title' => $title,
            'image' => $image,
            'target' => $target,
            'price' => $price,
        ]);

        return response()->json(['message' => 'Package has been successfully saved to database.'], 200);
    }

    public function getPackagesForSelection(PackageRequest $request)
    {
        $package = Package::latest()->get();
        return response()->json(['packages' => $package, ], 200);
    }

    public function getPackageWithDetails(PackageRequest $request)
    {
        $id = $request->id;
        try {
            $package = Package::with(['meals', 'exercises'])->where('id', $id)->first();
            if (!$package) {
                return response()->json(['message' => 'Package not found.'], 404);
            }

            return response()->json(['package' => $package], 200);
        } catch (\Exception $e) {

            return response()->json(['message' => 'An error occurred while fetching the package. '. $e->getMessage()], 500);
        }
    }

    public function addExerciseToPackage(PackageRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'packageId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }

        $ids = $request->input('ids');
        $packageId = $request->input('packageId');

        $package = Package::find($packageId);

        if (!$package) {
            return response()->json(['message' => 'Package not found.'], 404);
        }

        foreach ($ids as $exerciseId) {
            if ($package->exercises()->where('exercise_id', $exerciseId)->doesntExist()) {
                $package->exercises()->attach($exerciseId);
            }
        }

        return response()->json(['message' => 'Exercises have been successfully added to the package.'], 200);
    }

}
