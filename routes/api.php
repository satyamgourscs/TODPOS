<?php

use App\Http\Controllers\Api as Api;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/module-check', [Api\Auth\AuthController::class, 'moduleCheck']);
    Route::post('/sign-in', [Api\Auth\AuthController::class, 'login']);
    Route::post('/submit-otp', [Api\Auth\AuthController::class, 'submitOtp']);
    Route::post('/sign-up', [Api\Auth\AuthController::class, 'signUp']);
    Route::post('/resend-otp', [Api\Auth\AuthController::class, 'resendOtp']);
    Route::get('/otp-settings', [Api\Auth\AuthController::class, 'otpSettings']);

    Route::post('/send-reset-code', [Api\Auth\TryonedigitalForgotPasswordController::class, 'sendResetCode']);
    Route::post('/verify-reset-code', [Api\Auth\TryonedigitalForgotPasswordController::class, 'verifyResetCode']);
    Route::post('/password-reset', [Api\Auth\TryonedigitalForgotPasswordController::class, 'resetPassword']);

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('update-expire-date', [Api\BusinessController::class, 'updateExpireDate']);

        Route::get('summary', [Api\StatisticsController::class, 'summary']);
        Route::get('dashboard', [Api\StatisticsController::class, 'dashboard']);

        Route::apiResource('parties', Api\PartyController::class);
        Route::apiResource('users', Api\TryonedigitalUserController::class)->except('show');
        Route::apiResource('units', Api\UnitController::class)->except('show');
        Route::apiResource('brands', Api\TryonedigitalBrandController::class)->except('show');
        Route::apiResource('categories', Api\TryonedigitalCategoryController::class)->except('show');
        Route::apiResource('products', Api\TryonedigitalProductController::class);
        Route::apiResource('stocks', Api\StockController::class)->except('index', 'show');
        Route::apiResource('business', Api\BusinessController::class)->only('index', 'store', 'update');
        Route::apiResource('business-categories', Api\BusinessCategoryController::class)->only('index');
        Route::apiResource('purchase', Api\PurchaseController::class)->except('show');
        Route::apiResource('sales', Api\TryonedigitalSaleController::class)->except('show');
        Route::apiResource('sales-return', Api\SaleReturnController::class)->only('index', 'store', 'show');
        Route::apiResource('purchases-return', Api\PurchaseReturnController::class)->only('index', 'store', 'show');
        Route::apiResource('invoices', Api\TryonedigitalInvoiceController::class)->only('index');
        Route::apiResource('dues', Api\TryonedigitalDueController::class)->only('index', 'store');
        Route::apiResource('expense-categories', Api\ExpenseCategoryController::class)->except('show');
        Route::apiResource('expenses', Api\TryonedigitalExpenseController::class)->only('index', 'store');
        Route::apiResource('income-categories', Api\TryonedigitalIncomeCategoryController::class)->except('show');
        Route::apiResource('incomes', Api\TryonedigitalIncomeController::class)->only('index', 'store');

        Route::apiResource('banners', Api\TryonedigitalBannerController::class)->only('index');
        Route::apiResource('lang', Api\TryonedigitalLanguageController::class)->only('index', 'store');
        Route::apiResource('profile', Api\TryonedigitalProfileController::class)->only('index', 'store');
        Route::apiResource('plans', Api\TryonedigitalSubscriptionsController::class)->only('index');
        Route::apiResource('subscribes', Api\TryonedigitalSubscribesController::class)->only('index');
        Route::apiResource('currencies', Api\TryonedigitalCurrencyController::class)->only('index', 'show');
        Route::apiResource('vats', Api\TryonedigitalVatController::class)->except('show');
        Route::apiResource('payment-types', Api\PaymentTypeController::class)->except('show');
        Route::apiResource('warehouses', Api\WarehouseController::class)->except('show');
        Route::apiResource('product-models', Api\ProducModelController::class)->except('show');
        Route::apiResource('product-settings', Api\ProductSettingsController::class)->only('index', 'store');

        Route::get('business-settings', [Api\BusinessSettingController::class, 'index']);

        Route::post('bulk-uploads', [Api\BulkUploadControler::class, 'store']);
        Route::post('change-password', [Api\TryonedigitalProfileController::class, 'changePassword'])->name('api.change-password');

        Route::get('new-invoice', [Api\TryonedigitalInvoiceController::class, 'newInvoice']);

        Route::get('invoice-settings', [Api\BusinessInvoiceSettingController::class, 'index']);
        Route::post('/invoice-settings/update', [Api\BusinessInvoiceSettingController::class, 'updateInvoice'])->name('invoice.update');
        Route::post('/business-delete', [Api\BusinessController::class, 'deleteBusiness']);

        Route::get('/sign-out', [Api\Auth\AuthController::class, 'signOut']);
        Route::get('/refresh-token', [Api\Auth\AuthController::class, 'refreshToken']);
    });
});
