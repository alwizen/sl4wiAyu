<?php

use App\Filament\Resources\StockIssueResource\Pages\ProcessStockIssue;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

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