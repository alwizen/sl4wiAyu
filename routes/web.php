<?php

use App\Filament\Resources\StockIssueResource\Pages\ProcessStockIssue;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::get('/tracking', [\App\Http\Controllers\TrackingController::class, 'showForm'])->name('tracking.form');
Route::get('/tracking/check', [\App\Http\Controllers\TrackingController::class, 'check'])->name('tracking.check');

Route::get('/s/{code}', function($code) {
    $delivery = \App\Models\Delivery::where('short_code', $code)->first();

    if (!$delivery) {
        // Bisa redirect ke halaman 404 custom atau homepage
        return redirect('/')->with('error', 'Link tracking tidak ditemukan');
    }

    // Redirect ke halaman tracking
    return redirect("/tracking/check?delivery_number=" . urlencode($delivery->delivery_number));
})->name('tracking.short');
///Route::get('/tracking/{delivery_number}', [TrackingController::class, 'show']);

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
