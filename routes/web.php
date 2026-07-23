<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use Livewire\Volt\Volt; 

/*
|--------------------------------------------------------------------------
| HALAMAN PUBLIK (Tanpa Login)
|--------------------------------------------------------------------------
*/

// Route::get('/', function () {
//     return view('traffic'); 
// })->name('traffic');

Route::get('/', [DashboardController::class, 'index'])->name('traffic'); 

Route::get('/finance', [FinanceController::class, 'index'])->name('finance');

Route::get('/personnel', function () {
    return view('personnel');
})->name('personnel');

Route::get('/dashboard/finance', [FinanceController::class, 'index'])->name('dashboard.finance');
/*
|--------------------------------------------------------------------------
| HALAMAN ADMIN (Harus Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    // 1. Dashboard Overview
    // Menggunakan rute tunggal agar sinkron dengan sidebar request()->routeIs('dashboard')
    Route::get('/dashboard', [DashboardController::class, 'adminOverview'])->name('dashboard');

    // 2. Master Data Group
    Route::prefix('admin')->name('admin.')->group(function () {
        
        Volt::route('airlines', 'admin.airlines.index')->name('airlines.index');
        Volt::route('/', 'admin.index')->name('index');

        Volt::route('traffic', 'admin.traffic.index')->name('traffic.index');
        Volt::route('/', 'admin.index')->name('index');
        
        Volt::route('enroutes', 'admin.enroutes.index')->name('enroutes.index');
        Volt::route('/', 'admin.index')->name('index');

        Volt::route('terminals', 'admin.terminals.index')->name('terminals.index');
        Volt::route('/', 'admin.index')->name('index');

        Volt::route('finances', 'admin.finances.index')->name('finances.index');

    });

    // 3. User Profile Management
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
