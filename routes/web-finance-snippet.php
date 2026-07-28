<?php

/*
|--------------------------------------------------------------------------
| Tambahkan baris berikut ke routes/web.php milikmu
|--------------------------------------------------------------------------
| (jangan copy file ini utuh; cukup salin bagian use + Route di bawah)
*/

use App\Http\Controllers\FinanceController;

Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
Route::post('/finance/import', [FinanceController::class, 'import'])->name('finance.import');

// Jika ingin dilindungi login, bungkus dengan middleware:
// Route::middleware('auth')->group(function () {
//     Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
//     Route::post('/finance/import', [FinanceController::class, 'import'])->name('finance.import');
// });
