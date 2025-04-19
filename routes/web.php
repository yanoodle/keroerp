<?php

use App\Http\Controllers\InventoriesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SalesController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        // 'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::resource('sales', SalesController::class);
    Route::get('/sales', [SalesController::class, 'index'])->name('sales');
    Route::resource('inventories', InventoriesController::class);
    Route::get('/inventories', [InventoriesController::class, 'index'])->name('inventories');
    Route::resource('purchase', PurchasingController::class);
    Route::get('/purchase', [PurchasingController::class, 'index'])->name('purchase');
});

require __DIR__.'/auth.php';
