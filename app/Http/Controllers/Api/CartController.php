<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function storeToCart(CartRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'packageId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }

        $package_id = $request->input('packageId');

        // Check for existing package
        $existingPackage = Cart::where('package_id', $package_id)->first();
        if ($existingPackage) {
            return response()->json(['message' => 'Package already exist in cart.'], 409);
        }

        // Create the new package
        Cart::create([
            'package_id' => $package_id,
            'user_id' => Auth::id(), 
        ]);

        return response()->json(['message' => 'Package has been added to cart.'], 200);
    }

    public function getCart(CartRequest $request)
    {
        $cart = Cart::with('package')
        ->latest()
        ->where('user_id', Auth::id())
        ->get();
        return response()->json(['cart' => $cart, ], 200);
    }
}
