<?php

use Illuminate\Support\Facades\Route;
use Modules\MultiBranchAddon\App\Http\Controllers as Multibranch;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['as' => 'multibranch.', 'prefix' => 'multibranch', 'middleware' => ['users', 'expired']], function () {
    Route::resource('branches', Multibranch\TryonedigitalBranchController::class)->except('show');
    Route::post('branches/filter', [Multibranch\TryonedigitalBranchController::class, 'tryonedigitalFilter'])->name('branches.filter');
    Route::post('branches/delete-all', [Multibranch\TryonedigitalBranchController::class, 'deleteAll'])->name('branches.delete-all');
    Route::post('branches/status/{id}', [Multibranch\TryonedigitalBranchController::class, 'status'])->name('branches.status');

    Route::get('branches/overview', [Multibranch\TryonedigitalBranchController::class, 'overview'])->name('branches.overview');
    Route::get('/incomes-expenses', [Multibranch\TryonedigitalBranchController::class, 'incomeExpense'])->name('charts.income-expense');
    Route::get('/sales-purchases', [Multibranch\TryonedigitalBranchController::class, 'earningData'])->name('charts.sale-purchase');
    Route::get('/branch-wise-sales', [Multibranch\TryonedigitalBranchController::class, 'branchWiseSales'])->name('branch.wise.sales');
    Route::get('/branch-wise-purchases', [Multibranch\TryonedigitalBranchController::class, 'branchWisePurchases'])->name('branch.wise.purchases');
    Route::get('/switch-branch/{id}', [Multibranch\TryonedigitalBranchController::class, 'switchBranch'])->name('switch-branch');
    Route::get('/exit-branch/{id}', [Multibranch\TryonedigitalBranchController::class, 'exitBranch'])->name('exit-branch');
});
