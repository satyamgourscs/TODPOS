<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SaaS\DashboardController;
use App\Http\Controllers\Admin\SaaS\StoreManagementController;
use App\Http\Controllers\Admin\SaaS\PlanManagementController;

// Super Admin SaaS Routes
Route::middleware(['auth', 'role:superadmin'])->prefix('admin/saas')->name('admin.saas.')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Store Management
    Route::resource('stores', StoreManagementController::class);
    Route::patch('stores/{business}/toggle-status', [StoreManagementController::class, 'toggleStatus'])->name('stores.toggle-status');
    Route::post('stores/{business}/upgrade-plan', [StoreManagementController::class, 'upgradePlan'])->name('stores.upgrade-plan');

    // Plan Management
    Route::resource('plans', PlanManagementController::class);
    Route::patch('plans/{plan}/toggle-status', [PlanManagementController::class, 'toggleStatus'])->name('plans.toggle-status');
});
