<?php

use App\Filament\Resources\SppgPurchaseOrderResource\Api\SppgPurchaseOrderApiService;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KitchenPOController;
use App\Http\Controllers\Api\SppgPurchaseOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/kitchen-pos', [KitchenPOController::class, 'store']);
    Route::get('/kitchen-pos/{source}/{externalId}', [KitchenPOController::class, 'show']); // cek status
});

Route::post('/login', [AuthController::class, 'login']);

// SppgPurchaseOrderApiService::routes();

Route::apiResource('sppg-purchase-orders', SppgPurchaseOrderController::class);

// Custom actions
Route::post('sppg-purchase-orders/{sppg_purchase_order}/submit', [SppgPurchaseOrderController::class, 'submit']);
Route::post('sppg-purchase-orders/{sppg_purchase_order}/reopen', [SppgPurchaseOrderController::class, 'reopen']);

// Stats endpoint
Route::get('sppg-purchase-orders-stats', [SppgPurchaseOrderController::class, 'stats']);
