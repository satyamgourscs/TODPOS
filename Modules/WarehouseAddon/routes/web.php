<?php

use Illuminate\Support\Facades\Route;
use Modules\WarehouseAddon\App\Http\Controllers as Warehouse;

Route::group(['as' => 'warehouse.', 'prefix' => 'warehouse', 'middleware' => ['users', 'expired']], function () {

    //Warehouse
    Route::resource('warehouses', Warehouse\AcnooWarehouseController::class)->except('show');
    Route::post('warehouses/filter', [Warehouse\AcnooWarehouseController::class, 'acnooFilter'])->name('warehouses.filter');
    Route::post('warehouses/status/{id}', [Warehouse\AcnooWarehouseController::class, 'status'])->name('warehouses.status');
    Route::post('warehouses/delete-all', [Warehouse\AcnooWarehouseController::class, 'deleteAll'])->name('warehouses.delete-all');
    Route::get('/warehouses-by-branch/{branch_id?}', [Warehouse\AcnooWarehouseController::class, 'branchWiseWarehouses'])->name('warehouses.branch');

    //products
    Route::get('/warehouse/products', [Warehouse\AcnooWarehouseController::class, 'warehouseProduct'])->name('warehouses.product');
    Route::post('warehouses/products/filter', [Warehouse\AcnooWarehouseController::class, 'acnooProductFilter'])->name('product.filter');

});
