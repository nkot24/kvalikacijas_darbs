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
use App\Http\Controllers\AvansaRekinsController;
use App\Http\Controllers\ProcessProgressController;
use App\Http\Controllers\ProcessFileController;
use App\Http\Controllers\OrderListController;


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
    Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print'); // print sheet
    Route::resource('clients', ClientController::class);
    Route::resource('products', ProductController::class);
    Route::resource('users', UserController::class);
    Route::resource('processes', ProcessController::class);
    Route::resource('productions', ProductionController::class);
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::get('/avansa-rekini/create', [AvansaRekinsController::class, 'create'])->name('avansa_rekini.create');
    Route::post('/avanss', [AvansaRekinsController::class, 'store'])->name('avanss.store');
    Route::post('/avansa-rekini/get-orders', [AvansaRekinsController::class, 'getOrders'])->name('avansa_rekini.getOrders');
    Route::post('/avansa-rekini/generate', [AvansaRekinsController::class, 'generate'])->name('avansa_rekini.generate');
    Route::get('/api/orders/by-client/{client_id}', [AvansaRekinsController::class, 'getOrders']);
    Route::post('/process-progress', [ProcessProgressController::class, 'store'])->name('process-progress.store');
    Route::put('/process-progress/{progress}', [ProcessProgressController::class, 'update'])->name('process-progress.update');
    Route::delete('/process-progress/{progress}', [ProcessProgressController::class, 'destroy'])->name('process-progress.destroy');
    Route::post('/process-files', [ProcessFileController::class, 'store'])->name('process-files.store');
    Route::get('/process-files/{file}/download', [ProcessFileController::class, 'download'])->name('process-files.download');
    Route::get('/process-files/{file}/view',     [ProcessFileController::class, 'view'])->name('process-files.view');
    Route::delete('/process-files/{file}',       [ProcessFileController::class, 'destroy'])->name('process-files.destroy');
    Route::resource('orderList', OrderListController::class);


    

    

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
