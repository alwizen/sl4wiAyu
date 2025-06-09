<?php

use App\Exports\NutritionPlanItemsExport;
use App\Filament\Resources\StockIssueResource\Pages\ProcessStockIssue;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PurchaseOrderPdfController;
use App\Http\Controllers\StockReceivingController;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;


Route::get('/tracking', [\App\Http\Controllers\TrackingController::class, 'showForm'])->name('tracking.form');
Route::get('/tracking/check', [\App\Http\Controllers\TrackingController::class, 'check'])->name('tracking.check');

Route::get('/s/{code}', function ($code) {
    $delivery = \App\Models\Delivery::where('short_code', $code)->first();

    if (!$delivery) {
        // Bisa redirect ke halaman 404 custom atau homepage
        return redirect('/')->with('error', 'Link tracking tidak ditemukan');
    }

    // Redirect ke halaman tracking
    return redirect("/tracking/check?delivery_number=" . urlencode($delivery->delivery_number));
})->name('tracking.short');

//print pdf Nutrition Plan
Route::get('/nutrition-plans/{record}/print', [\App\Http\Controllers\NutritionPlanPrintController::class, 'print'])
    ->name('nutrition-plans.print');

//print pdf PO
Route::get('/purchase-orders/{purchaseOrder}/print', function (PurchaseOrder $purchaseOrder) {
    $pdf = Pdf::loadView('pdf.purchase-order', [
        'purchaseOrder' => $purchaseOrder,
    ]);

    return $pdf->stream('Nota_Pesanan_' . $purchaseOrder->order_number . '.pdf');
})->name('purchase-orders.print');

Route::get('/payroll/{payroll}/slip', [PayrollController::class, 'cetakSlip'])->name('payroll.slip');

Route::get('/stock-receiving/{record}/print', [StockReceivingController::class, 'print'])
    ->name('stock-receiving.print');

// Route::get('/export-nutrition-plan-items', function () {
//     return Excel::download(new NutritionPlanItemsExport, 'nutrition-plan-items.xlsx');
// });





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
