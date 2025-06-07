<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivePackageRequest;
use App\Models\ActivePackage;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ActivePackageController extends Controller
{
    public function getActivePackage(ActivePackageRequest $request)
    {
        $activePackage = ActivePackage::with('package', 'package.meals',
    'package.exercises')
            ->latest()
            ->where('user_id', Auth::id())
            ->first();

        $purchases = Purchase::with('package')
            ->latest()
            ->where('user_id', Auth::id())
            ->get();

        return response()->json([
            'activePackage' => $activePackage,
            'purchases' => $purchases
        ], 200);
    }

    
    public function activatePackage(ActivePackageRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'packageId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }

        $package_id = $request->input('packageId');

        // Check for existing package
        $existingPackage = ActivePackage::where('user_id', Auth::id())
        ->where('package_id', $package_id)
        ->first();

        if ($existingPackage) {
            return response()->json(['message' => 'Package already active.'], 409);
        }

        // Create or update ative package
        DB::table('active_packages')->updateOrInsert(
            ['user_id' => Auth::id()], 
            ['package_id' => $package_id, 'user_id' => Auth::id(), 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['message' => 'Package has been activated.'], 200);
    }
}
