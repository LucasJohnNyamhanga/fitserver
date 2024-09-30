<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{

    public function signup(SignupRequest $request)
    {
        $fullname = $request->fullname;
        $mobile = $request->mobile;
        $username = $request->username;
        $password = $request->password;
        $retypePassword = $request->retypePassword;
        $gender = $request->gender;
        $goal = $request->goal;
        $age = $request->age;
        $height = $request->height;
        $weight = $request->weight;
        $targetWeight = $request->targetWeight;
        $health = $request->health;
        $selectedLevel = $request->selectedLevel;
        $strength = $request->strength;
        $fatStatus = $request->fatStatus;

        // Check for missing required fields
        if (empty($fullname) || empty($mobile) || empty($username) || empty($password) || empty($retypePassword)) {
            return response()->json(['message' => 'Please, fill in all available fields'], 401);
        }

        // Check if passwords match
        if ($password !== $retypePassword) {
            return response()->json(['message' => 'Passwords do not match'], 401);
        }

        // Check if username already exists
        $user = User::where("username", $username)->first();

        if ($user) {
            return response()->json(['message' => 'Sorry, Username has already been taken.'], 401);
        }

        // Use a database transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // Create new user with hashed password
            $newUser = User::create([
                'fullname' => $fullname,
                'mobile' => $mobile,
                'username' => $username,
                'active' => true,
                'is_trainer' => false,
                'password' => Hash::make($password),  // Hash the password
            ]);

            // Create associated customer record
            $newCustomer = Customer::create([
                'user_id' => $newUser->id,  // Make sure you store the user_id in the customer table
                'gender' => $gender,
                'goal' => $goal,
                'age' => $age,
                'height' => $height,
                'weight' => $weight,
                'targetWeight' => $targetWeight,
                'health' => $health,
                'fitnessLevel' => $selectedLevel,
                'strength' => $strength,
                'fatStatus' => $fatStatus,
            ]);

            // Commit the transaction if everything is successful
            DB::commit();

            return response()->json(['message' => 'Account has been successfully created'], 200);

        } catch (\Exception $ex) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to create user',
                'message' => $ex->getMessage()
            ], 500);
        }
    }


    public function login(LoginRequest $request)
    {
        // Validate input fields
        $username = $request->username;
        $password = $request->password;

        if (empty($username) || empty($password)) {
            return response()->json(['message' => 'Jaza nafasi zote zilizo wazi.'], 401);
        }

        // Find the user by username
        $user = User::query()->where("username", $username)->first();

        if (!$user) {
            // Return error if user is not found
            return response()->json(['message' => 'Tumia jina au password halisi'], 401);
        }

        // Check if the password matches using Hash::check
        if (Hash::check($password, $user->password)) {
            // Generate an authentication token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            // Return user and token if successful
            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 200);
        } else {
            // Return error if the password is incorrect
            return response()->json(['message' => 'Umekosea jina au password'], 401);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json(['message'=> 'logout'],200);
    }
}
