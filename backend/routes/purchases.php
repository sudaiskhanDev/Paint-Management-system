<?php

use App\Http\Controllers\Api\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::prefix('purchases')->group(function () {
    Route::get('/', [PurchaseController::class, 'index']);
    Route::post('/', [PurchaseController::class, 'store']);
    Route::get('/{id}', [PurchaseController::class, 'show']);
    Route::put('/{id}', [PurchaseController::class, 'update']);
    Route::delete('/{id}', [PurchaseController::class, 'destroy']);
});