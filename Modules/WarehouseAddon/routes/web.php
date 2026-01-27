<?php

use Illuminate\Support\Facades\Route;
use Modules\WarehouseAddon\App\Http\Controllers as Warehouse;

Route::group(['as' => 'warehouse.', 'prefix' => 'warehouse', 'middleware' => ['users', 'expired']], function () {

    //Warehouse
    Route::resource('warehouses', Warehouse\TryonedigitalWarehouseController::class)->except('show');
    Route::post('warehouses/filter', [Warehouse\TryonedigitalWarehouseController::class, 'tryonedigitalFilter'])->name('warehouses.filter');
    Route::post('warehouses/status/{id}', [Warehouse\TryonedigitalWarehouseController::class, 'status'])->name('warehouses.status');
    Route::post('warehouses/delete-all', [Warehouse\TryonedigitalWarehouseController::class, 'deleteAll'])->name('warehouses.delete-all');
    Route::get('/warehouses-by-branch/{branch_id?}', [Warehouse\TryonedigitalWarehouseController::class, 'branchWiseWarehouses'])->name('warehouses.branch');

    //products
    Route::get('/warehouse/products', [Warehouse\TryonedigitalWarehouseController::class, 'warehouseProduct'])->name('warehouses.product');
    Route::post('warehouses/products/filter', [Warehouse\TryonedigitalWarehouseController::class, 'tryonedigitalProductFilter'])->name('product.filter');

});
