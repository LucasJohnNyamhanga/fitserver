<?php

use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\ExcerciseController;
use App\Http\Controllers\Api\InstructionController;
use App\Http\Controllers\Api\MealController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\TrainerController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BodyTargetController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', [AuthController::class, 'logout']); 
    Route::post('storeBodyTarget', [BodyTargetController::class, 'storeBodyTarget']);
    Route::post("upload", [UploadController::class, 'upload']);
    Route::post('storeExcercise', [ExcerciseController::class, 'storeExcercise']);
    Route::post('storeEquipment', [EquipmentController::class, 'storeEquipment']);
    Route::get('getBodyListAndEquipments', [BodyTargetController::class, 'getBodyListAndEquipments']);
    Route::post('storeExcercise', [ExcerciseController::class, 'storeExcercise']);
    Route::get('getBodyListWithExercise', [BodyTargetController::class, 'getBodyListWithExercise']);
    Route::post('storePackage', [PackageController::class, 'storePackage']);
    Route::get('getPackagesForSelection', [PackageController::class, 'getPackagesForSelection']);
    Route::get('getPackageWithDetails', [PackageController::class, 'getPackageWithDetails']);
    Route::post('storeMeal', [MealController::class, 'storeMeal']);
    Route::get('getBodyListWithExerciseForPicking', [BodyTargetController::class, 'getBodyListWithExerciseForPicking']);
    Route::post('addExerciseToPackage', [PackageController::class, 'addExerciseToPackage']);
    Route::post('changePackageActivation', [PackageController::class, 'changePackageActivation']);
    Route::post('deletePackageActivation', [PackageController::class, 'deletePackageActivation']);
    Route::post('editPackage', [PackageController::class, 'editPackage']);
    Route::get('getBodyList', [BodyTargetController::class, 'getBodyList']);
    Route::get('getBodyPartWithExerciseTrainerSpecific', [BodyTargetController::class, 'getBodyPartWithExerciseTrainerSpecific']);
    Route::post('createTrainer', [TrainerController::class, 'createTrainer']);
    Route::get('getUserProfile', [UserController::class, 'getUserProfile']);
    Route::post('editBodyTarget', [BodyTargetController::class, 'editBodyTarget']);
    Route::post('changeBodyActivation', [BodyTargetController::class, 'changeBodyActivation']);
    Route::post('deleteBodyPart', [BodyTargetController::class, 'deleteBodyPart']);
    Route::get('getEquipmentWithExercises', [EquipmentController::class, 'getEquipmentWithExercises']);
    Route::get('getEquipments', [EquipmentController::class, 'getEquipments']);
    Route::post('editEquipment', [EquipmentController::class, 'editEquipment']);
    Route::post('deleteEquipment', [EquipmentController::class, 'deleteEquipment']);
    Route::get('getExercises', [ExcerciseController::class, 'getExercises']);
    Route::get('getExerciseWithBodyPartAndEquipment', [ExcerciseController::class, 'getExerciseWithBodyPartAndEquipment']);
    Route::post('editExcercise', [ExcerciseController::class, 'editExcercise']);
    Route::post('changeExerciseActivation', [ExcerciseController::class, 'changeExerciseActivation']);
    Route::post('deleteExercise', [ExcerciseController::class, 'deleteExercise']);
    Route::post('storeToCart', [CartController::class, 'storeToCart']);
    Route::get('getCart', [CartController::class, 'getCart']);
    Route::post('deleteCart', [CartController::class, 'deleteCart']);
    Route::get('getPurchases', [PurchaseController::class, 'getPurchases']);
    Route::post('makePurchase', [PurchaseController::class, 'makePurchase']);
});

Route::post('login', [AuthController::class, 'login']);
Route::post('signup', [AuthController::class, 'signup']);