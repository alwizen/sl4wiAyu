<?php

use App\Filament\Resources\StockIssueResource\Pages\ProcessStockIssue;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::get('/tracking', [\App\Http\Controllers\TrackingController::class, 'showForm'])->name('tracking.form');
Route::get('/tracking/check', [\App\Http\Controllers\TrackingController::class, 'check'])->name('tracking.check');
//Route::get('/tracking/{delivery_number}', [TrackingController::class, 'show']);

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::middleware([
//     'auth',
//     config('filament.middleware.base'),
// ])
// ->name('filament.resources.stock-issues.')
//     ->prefix(config('filament.path'))
//     ->group(function () {
//         // Rute untuk halaman proses penyiapan bahan
//         Route::get('/stock-issues/{record}/process', ProcessStockIssue::class)
//             ->name('process');
//     });
