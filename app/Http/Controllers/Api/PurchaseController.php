<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequest;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    public function getPurchases(PurchaseRequest $request)
    {
        $purchase = Purchase::with('package')
        ->latest()
        ->where('user_id', Auth::id())
        ->get();
        return response()->json(['purchase' => $purchase, ], 200);
    }

    public function makePurchase(PurchaseRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'packageId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }

        $package_id = $request->input('packageId');

        // Check for existing package
        $existingPackage = Purchase::where('user_id', Auth::id())
        ->where('package_id', $package_id)
        ->first();
        if ($existingPackage) {
            return response()->json(['message' => 'Package already exist in purchase list.'], 409);
        }

        // Create the new package
        Purchase::create([
            'package_id' => $package_id,
            'user_id' => Auth::id(), 
        ]);

        return response()->json(['message' => 'Package has been purchased.'], 200);
    }
}
