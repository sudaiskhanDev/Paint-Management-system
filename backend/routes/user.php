<?php



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::prefix('user')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

});


 

use App\Http\Controllers\Api\UserCrudController;

Route::middleware('auth:api')->group(function () {

    Route::get('/users', [UserCrudController::class, 'index']);
    Route::get('/users/{id}', [UserCrudController::class, 'show']);
    Route::post('/users', [UserCrudController::class, 'store']);
    Route::put('/users/{id}', [UserCrudController::class, 'update']);
    Route::delete('/users/{id}', [UserCrudController::class, 'destroy']);

});