<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PackageRequest;
use App\Models\Package;
use App\Models\Trainer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    public function storePackage(PackageRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'required|string',
            'description' => 'required|string',
            'expectation' => 'required|string',
            'type' => 'required|string',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Fill in all empty fields', 
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $title = $request->input('title');
            $description = $request->input('description');
            $expectation = $request->input('expectation');
            $image = $request->input('image');
            $target = $request->input('type');
            $price = $request->input('price');

            // Check for existing package
            $existingPackage = Package::where('title', $title)->first();
            if ($existingPackage) {
                return response()->json(['message' => 'Package already available.'], 409);
            }

            // Retrieve the authenticated trainer
            $trainer = Trainer::where('user_id', Auth::id())->first();
            if (!$trainer) {
                return response()->json(['message' => 'Trainer not found.'], 404);
            }

            // Create the new package
            Package::create([
                'title' => $title,
                'description' => $description,
                'expectation' => $expectation,
                'image' => $image,
                'target' => $target,
                'price' => $price,
                'trainer_id' => $trainer->id,
            ]);

            return response()->json([
                'message' => 'Package has been successfully saved to database.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function getPackagesForSelection(PackageRequest $request)
    {
        try {
            // Get the trainer associated with the logged-in user
            $trainer = Trainer::where('user_id', Auth::id())->first();

            if (!$trainer) {
                return response()->json([
                    'message' => 'Trainer profile not found for the logged-in user.'
                ], 404);
            }

            // Get all packages created by this trainer
            $packages = $trainer->package()
                ->latest()
                ->get();

            return response()->json([
                'packages' => $packages
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' =>$e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPackageWithDetails(PackageRequest $request)
    {
        $id = $request->id;
        try {
            $package = Package::with(['meals', 'exercises','trainer.user'])->where('id', $id)->first();
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

    public function changePackageActivation(PackageRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'packageId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }


        $packageId = $request->input('packageId');

        $package = Package::find($packageId);

        if (!$package) {
            return response()->json(['message' => 'Package not found.'], 404);
        }

        $package->active = !$package->active;
        $package->save();

        return response()->json(['message' => 'Package active status changed.'], 200);
    }

    public function deletePackageActivation(PackageRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'packageId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }


        $packageId = $request->input('packageId');

        $package = Package::find($packageId);

        if (!$package) {
            return response()->json(['message' => 'Package not found.'], 404);
        }

        $package->delete();

        return response()->json(['message' => 'Package has been deleted.'], 200);
    }

    public function editPackage(PackageRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'packageId' => 'required|integer',
            'title' => 'required|string|max:255',
            'image' => 'required|string',
            'description' => 'required|string',
            'expectation' => 'required|string',
            'type' => 'required|string',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }

        $packageId = $request->input('packageId');
        $title = $request->input('title');
        $description = $request->input('description');
        $expectation = $request->input('expectation');
        $image = $request->input('image');
        $target = $request->input('type');
        $price = $request->input('price');

        // Retrieve the package by its ID
        $package = Package::find($packageId);

        if (!$package) {
            return response()->json(['message' => 'Package not found.'], 404);
        }

        // Check for existing package with the same title (excluding the current package being edited)
        $existingPackage = Package::where('title', $title)->where('id', '!=', $packageId)->first();
        if ($existingPackage) {
            return response()->json(['message' => 'Another package with this title already exists.'], 409);
        }

        // Retrieve the authenticated trainer
        $trainer = Trainer::where('user_id', Auth::id())->first();

        if (!$trainer) {
            return response()->json(['message' => 'Trainer not found.'], 404);
        }

        // Update the existing package
        $package->update([
            'title' => $title,
            'description' => $description,
            'expectation' => $expectation,
            'image' => $image,
            'target' => $target,
            'price' => $price,
            'trainer_id' => $trainer->id, // Corrected this part
        ]);

        return response()->json(['message' => 'Package has been successfully updated.'], 200);
    }
}
