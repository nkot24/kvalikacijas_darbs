<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\TaskController;

Route::get('/', function () {
    return view('/auth/login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/clients/full-export', [ClientController::class, 'fullExport'])->name('clients.fullExport');
    Route::post('/clients/full-import', [ClientController::class, 'fullImport'])->name('clients.fullImport');
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('users/export', [UserController::class, 'export'])->name('users.export');
    Route::post('users/import', [UserController::class, 'import'])->name('users.import');
    Route::get('/orders/export', [OrderController::class, 'fullExport'])->name('orders.fullExport');
    Route::post('/orders/import', [OrderController::class, 'fullImport'])->name('orders.fullImport');

    Route::resource('orders', OrderController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('products', ProductController::class);
    Route::resource('users', UserController::class);
    Route::resource('processes', ProcessController::class);
    Route::resource('productions', ProductionController::class);
     Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

    

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
