<?php

use App\Http\Controllers\Api\SaleItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('sale-items')->group(function () {
    Route::get('/', [SaleItemController::class, 'index']);
    Route::post('/', [SaleItemController::class, 'store']);
    Route::get('/{id}', [SaleItemController::class, 'show']);
    Route::put('/{id}', [SaleItemController::class, 'update']);
    Route::delete('/{id}', [SaleItemController::class, 'destroy']);
});