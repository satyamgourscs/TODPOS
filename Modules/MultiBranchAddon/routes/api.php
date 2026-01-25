<?php

use Illuminate\Support\Facades\Route;
use Modules\MultiBranchAddon\App\Http\Controllers\Api as Multibranch;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
    Route::apiResource('branches', Multibranch\AcnooBranchController::class)->except('show');
    Route::get('/switch-branch/{id}', [Multibranch\AcnooBranchController::class, 'switchBranch']);
    Route::get('/exit-branch/{id}', [Multibranch\AcnooBranchController::class, 'exitBranch']);
});
