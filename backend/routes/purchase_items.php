<?php

use App\Http\Controllers\Api\PurchaseItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('purchase-items')->group(function () {
    Route::get('/', [PurchaseItemController::class, 'index']);
    Route::post('/', [PurchaseItemController::class, 'store']);
    Route::get('/{id}', [PurchaseItemController::class, 'show']);
    Route::put('/{id}', [PurchaseItemController::class, 'update']);
    Route::delete('/{id}', [PurchaseItemController::class, 'destroy']);
});