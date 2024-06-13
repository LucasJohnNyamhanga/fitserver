<?php

use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\ExcerciseController;
use App\Http\Controllers\Api\InstructionController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BodyTargetController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', [AuthController::class, 'logout']); 
    Route::post('storeBodyTarget', [BodyTargetController::class, 'storeBodyTarget']);
    Route::post("upload", [UploadController::class, 'upload']);
    Route::post('storeInstruction', [InstructionController::class, 'storeInstruction']);
    Route::post('storeExcercise', [ExcerciseController::class, 'storeExcercise']);
    Route::post('storeEquipment', [EquipmentController::class, 'storeEquipment']);
    Route::get('getBodyListAndEquipments', [BodyTargetController::class, 'getBodyListAndEquipments']);
    
});

Route::post('login', [AuthController::class, 'login']);
Route::post('signup', [AuthController::class, 'signup']);