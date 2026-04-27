<?php

use App\Http\Controllers\{
    AvansaRekinsController,
    ClientController,
    InventoryController,
    MaterialScanController,
    OrderController,
    OrderListController,
    ProcessController,
    ProcessFileController,
    ProcessProgressController,
    ProductController,
    ProductionController,
    ProfileController,
    TaskController,
    UserController,
    WorkLogController
};
use Illuminate\Support\Facades\{Auth, DB, Route, Schema};
use Carbon\Carbon;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return redirect()->route('work.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/orders/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
    Route::resource('orders', OrderController::class);

    Route::resource('orderList', OrderListController::class);
    Route::get('/order-list/completed', [OrderListController::class, 'completed'])->name('orderList.completed');

    Route::resource('clients', ClientController::class);
    Route::resource('products', ProductController::class);
    Route::resource('users', UserController::class);
    Route::resource('processes', ProcessController::class);
    Route::resource('productions', ProductionController::class);

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

    Route::prefix('avansa-rekini')->name('avansa_rekini.')->group(function () {
        Route::get('/create', [AvansaRekinsController::class, 'create'])->name('create');
        Route::post('/get-orders', [AvansaRekinsController::class, 'getOrders'])->name('getOrders');
        Route::post('/generate', [AvansaRekinsController::class, 'generate'])->name('generate');
    });

    Route::post('/avanss', [AvansaRekinsController::class, 'store'])->name('avanss.store');
    Route::get('/api/orders/by-client/{client_id}', [AvansaRekinsController::class, 'getOrders']);

    Route::prefix('process-progress')->name('process-progress.')->group(function () {
        Route::post('/', [ProcessProgressController::class, 'store'])->name('store');
        Route::put('/{progress}', [ProcessProgressController::class, 'update'])->name('update');
        Route::delete('/{progress}', [ProcessProgressController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('process-files')->name('process-files.')->group(function () {
        Route::post('/', [ProcessFileController::class, 'store'])->name('store');
        Route::get('/{file}/download', [ProcessFileController::class, 'download'])->name('download');
        Route::get('/{file}/view', [ProcessFileController::class, 'view'])->name('view');
        Route::delete('/{file}', [ProcessFileController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/scan', [InventoryController::class, 'scanView'])->name('scan');
        Route::post('/scan', [InventoryController::class, 'handleScan'])->name('scan.handle');
        Route::post('/scan/transfer', [InventoryController::class, 'storeTransfer'])->name('scan.storeTransfer');

        Route::get('/transfers', [InventoryController::class, 'transferIndex'])->name('transfers.index');
        Route::patch('/transfers/account', [InventoryController::class, 'transferBulkAccount'])->name('transfers.account');
        Route::delete('/transfers', [InventoryController::class, 'transferBulkDelete'])->name('transfers.delete');

        Route::prefix('materials')->name('materials.')->group(function () {
            Route::get('/', [MaterialScanController::class, 'index'])->name('index');
            Route::get('/scan', [MaterialScanController::class, 'scanView'])->name('scan');
            Route::post('/store', [MaterialScanController::class, 'storeScan'])->name('store');
            Route::patch('/account', [MaterialScanController::class, 'bulkAccount'])->name('account');
            Route::delete('/delete', [MaterialScanController::class, 'bulkDelete'])->name('delete');
        });
    });

    Route::prefix('darbs')->name('work.')->group(function () {
        Route::get('/', [WorkLogController::class, 'index'])->name('index');
        Route::post('/sakt', [WorkLogController::class, 'startWork'])->name('start');
        Route::post('/beigt', [WorkLogController::class, 'endWork'])->name('end');
        Route::get('/stundas', [WorkLogController::class, 'workHoursView'])->name('hours');
    });

    Route::patch('/work-log/update-time/{id}', [WorkLogController::class, 'updateTime'])->name('work.updateTime');
    Route::patch('/work-log/update-field/{id}', [WorkLogController::class, 'updateField']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/check-updates', function () {
    if (!request()->ajax()) {
        abort(403);
    }

    $latest = 0;

    foreach (DB::select('SHOW TABLES') as $table) {
        $tableName = array_values((array) $table)[0];

        if (!Schema::hasColumn($tableName, 'updated_at')) {
            continue;
        }

        try {
            $updatedAt = DB::table($tableName)->latest('updated_at')->value('updated_at');

            if ($updatedAt && strtotime($updatedAt) > $latest) {
                $latest = strtotime($updatedAt);
            }
        } catch (Throwable $e) {}
    }

    return response()->json(['last_update' => $latest]);
})->middleware('auth');

require __DIR__ . '/auth.php';