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
    Route::resource('branches', Multibranch\AcnooBranchController::class)->except('show');
    Route::post('branches/filter', [Multibranch\AcnooBranchController::class, 'acnooFilter'])->name('branches.filter');
    Route::post('branches/delete-all', [Multibranch\AcnooBranchController::class, 'deleteAll'])->name('branches.delete-all');
    Route::post('branches/status/{id}', [Multibranch\AcnooBranchController::class, 'status'])->name('branches.status');

    Route::get('branches/overview', [Multibranch\AcnooBranchController::class, 'overview'])->name('branches.overview');
    Route::get('/incomes-expenses', [Multibranch\AcnooBranchController::class, 'incomeExpense'])->name('charts.income-expense');
    Route::get('/sales-purchases', [Multibranch\AcnooBranchController::class, 'earningData'])->name('charts.sale-purchase');
    Route::get('/branch-wise-sales', [Multibranch\AcnooBranchController::class, 'branchWiseSales'])->name('branch.wise.sales');
    Route::get('/branch-wise-purchases', [Multibranch\AcnooBranchController::class, 'branchWisePurchases'])->name('branch.wise.purchases');
    Route::get('/switch-branch/{id}', [Multibranch\AcnooBranchController::class, 'switchBranch'])->name('switch-branch');
    Route::get('/exit-branch/{id}', [Multibranch\AcnooBranchController::class, 'exitBranch'])->name('exit-branch');
});
