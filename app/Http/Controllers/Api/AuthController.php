<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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


        if (empty($fullname) || empty($mobile) || empty($username) || empty($password)|| empty($retypePassword)) {
            return response()->json(['message' => 'Please, fill in all available fields'], 401);
        } else {

            if ($password == $retypePassword) {
                $user = User::query()->where("username", $username)->first();

           
                
                if (!$user) {
                    $user = User::create([
                        'fullname' => $fullname,
                        'mobile' => $mobile,
                        'username' => $username,
                        'active' => true,
                        'role' => 'normal',
                        'password' => $password,
                    ]);

                    return response()->json(['message' => 'Account has been successful created'], 200);
                } else {
                    return response()->json(['message' => 'Sorry, Username has already been taken.'], 401);
                }
            }else{
                return response()->json(['message' => 'Passwords does not match'], 401);
            }
        }
    }

    public function login(LoginRequest $request)
    {
        $username = $request->username;
        $password = $request->password;

        if (empty($username) || empty($password)) {
            return response()->json(['message' => 'Jaza nafasi zote zilizo wazi.'], 401);
        }
        
        $user = User::query()->where("username", $username)->first();
        
        if ($user) {
            $passwordDatabase = $user->password;
            if ($password == $passwordDatabase) {
                // Password matches, do something (e.g., log in the user)
                // $token = $user->createToken('main')->plainTextToken;
                $token = $user->createToken('auth_token')->plainTextToken;

                //return response()->json(['message' => 'Hii ni ya ndani kabisa'], 401);
                return response((compact('user', 'token')));
            } else {
                // Invalid username or password
                return response()->json(['message' => 'Umekosea jina au password'], 401);
            }
        }else{
            return response()->json(['message' => 'Tumia jina au password halisi'], 401);
        }

    }

   

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json(['message'=> 'logout'],200);
    }
}
