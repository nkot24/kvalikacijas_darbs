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
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\WorkLogController;
use App\Http\Controllers\MaterialScanController;
use App\Models\WorkLog;
use Illuminate\Support\Facades\Auth;


    Route::get('/', function () {
        return view('/auth/login');
    });

    // ✅ Fixed Dashboard route with $today and $log variables
    Route::get('/dashboard', function () {
        $today = now()->toDateString();
        $log = WorkLog::where('user_id', Auth::id())
                    ->whereDate('created_at', $today)
                    ->first();

        return view('dashboard', compact('today', 'log'));
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
    Route::get('/orders/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::resource('orders', OrderController::class);
    Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
   
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
    Route::get('/order-list/completed', [OrderListController::class, 'completed'])->name('orderList.completed');

    Route::get('/inventory/scan', [InventoryController::class, 'scanView'])->name('inventory.scan');
    Route::post('/inventory/scan', [InventoryController::class, 'handleScan'])->name('inventory.scan.handle');
    Route::post('/inventory/scan/transfer', [InventoryController::class, 'storeTransfer'])->name('inventory.scan.storeTransfer');
    Route::get('/inventory/transfers', [InventoryController::class, 'transferIndex'])->name('inventory.transfers.index');
    Route::patch('/inventory/transfers/account', [InventoryController::class, 'transferBulkAccount'])->name('inventory.transfers.account');
    Route::delete('/inventory/transfers', [InventoryController::class, 'transferBulkDelete'])->name('inventory.transfers.delete');

    Route::get('/darbs', [WorkLogController::class, 'index'])->name('work.index');
    Route::post('/darbs/sakt', [WorkLogController::class, 'startWork'])->name('work.start');
    Route::post('/darbs/beigt', [WorkLogController::class, 'endWork'])->name('work.end');
    Route::get('/darbs/stundas', [WorkLogController::class, 'workHoursView'])->name('work.hours');

    Route::prefix('inventory/materials')->name('inventory.materials.')->group(function () {
        Route::get('/scan', [MaterialScanController::class, 'scanView'])->name('scan');
        Route::post('/store', [MaterialScanController::class, 'storeScan'])->name('store');
        Route::get('/', [MaterialScanController::class, 'index'])->name('index');
        Route::patch('/account', [MaterialScanController::class, 'bulkAccount'])->name('account');
        Route::delete('/delete', [MaterialScanController::class, 'bulkDelete'])->name('delete');
    });



    

    

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
