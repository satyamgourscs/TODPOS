<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin as ADMIN;

Route::group(['as' => 'admin.', 'prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {
    Route::get('/', [ADMIN\DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/get-dashboard', [ADMIN\DashboardController::class, 'getDashboardData'])->name('dashboard.data');
    Route::get('/yearly-subscriptions', [ADMIN\DashboardController::class, 'yearlySubscriptions'])->name('dashboard.subscriptions');
    Route::get('/plans-overview', [ADMIN\DashboardController::class, 'plansOverview'])->name('dashboard.plans-overview');

    // Website settings
    Route::resource('website-settings',Admin\TryonedigitalWebSettingController::class)->only('index', 'update');

    // Features
    Route::resource('features',Admin\TryonedigitalFeatureController::class);
    Route::post('features/filter', [ADMIN\TryonedigitalFeatureController::class, 'tryonedigitalFilter'])->name('features.filter');
    Route::post('features/status/{id}', [ADMIN\TryonedigitalFeatureController::class,'status'])->name('features.status');
    Route::post('features/delete-all', [ADMIN\TryonedigitalFeatureController::class, 'deleteAll'])->name('features.delete-all');

    // Interface
    Route::resource('interfaces',Admin\TryonedigitalInterfaceController::class);
    Route::post('interfaces/filter', [ADMIN\TryonedigitalInterfaceController::class, 'tryonedigitalFilter'])->name('interfaces.filter');
    Route::put('admin/interfaces/{interface}', [ADMIN\TryonedigitalInterfaceController::class, 'update'])->name('interfaces.update');
    Route::post('interfaces/status/{id}', [ADMIN\TryonedigitalInterfaceController::class,'status'])->name('interfaces.status');
    Route::post('interfaces/delete-all', [ADMIN\TryonedigitalInterfaceController::class, 'deleteAll'])->name('interfaces.delete-all');

    // Testimonial
    Route::resource('testimonials',Admin\TryonedigitalTestimonialController::class);
    Route::post('testimonials/filter', [ADMIN\TryonedigitalTestimonialController::class, 'tryonedigitalFilter'])->name('testimonials.filter');
    Route::post('testimonials/delete-all', [ADMIN\TryonedigitalTestimonialController::class, 'deleteAll'])->name('testimonials.delete-all');

    // Term And Condition Controller
    Route::resource('term-conditions', ADMIN\TryonedigitalTermConditionController::class)->only('index', 'store');

    // Message
    Route::resource('messages', Admin\TryonedigitalMessageController::class)->only('index', 'destroy');
    Route::post('messages/filter', [ADMIN\TryonedigitalMessageController::class, 'tryonedigitalFilter'])->name('messages.filter');
    Route::post('messages/delete-all', [ADMIN\TryonedigitalMessageController::class, 'deleteAll'])->name('messages.delete-all');
    Route::post('messages/filter', [Admin\TryonedigitalMessageController::class, 'tryonedigitalFilter'])->name('messages.filter');

    // Privacy And Policy Controller
    Route::resource('privacy-policy', ADMIN\TryonedigitalPrivacyPloicyController::class)->only('index', 'store');

    // Blog Controller
    Route::resource('blogs', Admin\TryonedigitalBlogController::class);
    Route::post('blogs/filter', [ADMIN\TryonedigitalBlogController::class, 'tryonedigitalFilter'])->name('blogs.filter');
    Route::post('blogs/status/{id}', [ADMIN\TryonedigitalBlogController::class,'status'])->name('blogs.status');
    Route::post('blogs/delete-all', [ADMIN\TryonedigitalBlogController::class, 'deleteAll'])->name('blogs.delete-all');
    Route::get('blogs/comments/{id}', [ADMIN\TryonedigitalBlogController::class, 'filterComment'])->name('blogs.filter.comment');

    //Comment Controller
    Route::resource('comments', Admin\TryonedigitalCommentController::class);
    Route::post('comments/delete-all', [ADMIN\TryonedigitalCommentController::class, 'deleteAll'])->name('comments.delete-all');

    Route::resource('users', ADMIN\UserController::class);
    Route::post('users/filter', [ADMIN\UserController::class, 'tryonedigitalFilter'])->name('users.filter');
    Route::post('users/status/{id}', [ADMIN\UserController::class,'status'])->name('users.status');
    Route::post('users/delete-all', [ADMIN\UserController::class,'deleteAll'])->name('users.delete-all');

    Route::resource('banners', ADMIN\TryonedigitalBannerController::class)->except('show', 'edit', 'create');
    Route::post('banners/filter', [ADMIN\TryonedigitalBannerController::class, 'tryonedigitalFilter'])->name('banners.filter');
    Route::post('banners/status/{id}', [ADMIN\TryonedigitalBannerController::class,'status'])->name('banners.status');
    Route::post('banners/delete-all', [ADMIN\TryonedigitalBannerController::class,'deleteAll'])->name('banners.delete-all');

    //Subscription Plans
    Route::resource('plans', ADMIN\TryonedigitalPlanController::class)->except('show');
    Route::post('plans/filter', [ADMIN\TryonedigitalPlanController::class, 'tryonedigitalFilter'])->name('plans.filter');
    Route::post('plans/status/{id}', [ADMIN\TryonedigitalPlanController::class,'status'])->name('plans.status');
    Route::post('plans/delete-all', [ADMIN\TryonedigitalPlanController::class, 'deleteAll'])->name('plans.delete-all');

    // Business
    Route::resource('business',ADMIN\TryonedigitalBusinessController::class);
    Route::put('business/upgrade-plan/{id}', [ADMIN\TryonedigitalBusinessController::class, 'upgradePlan'])->name('business.upgrade.plan');
    Route::post('business/status/{id}',[ADMIN\TryonedigitalBusinessController::class,'status'])->name('business.status');
    Route::post('business/filter', [ADMIN\TryonedigitalBusinessController::class, 'tryonedigitalFilter'])->name('business.filter');
    Route::post('business/delete-all', [ADMIN\TryonedigitalBusinessController::class,'deleteAll'])->name('business.delete-all');

    // Business Categories
    Route::resource('business-categories',ADMIN\TryonedigitalBusinessCategoryController::class)->except('show');
    Route::post('business-category/filter', [ADMIN\TryonedigitalBusinessCategoryController::class, 'tryonedigitalFilter'])->name('business-categories.filter');
    Route::post('business-categories/status/{id}',[ADMIN\TryonedigitalBusinessCategoryController::class,'status'])->name('business-categories.status');
    Route::post('business-categories/delete-all', [ADMIN\TryonedigitalBusinessCategoryController::class,'deleteAll'])->name('business-categories.delete-all');

    Route::resource('profiles', ADMIN\ProfileController::class)->only('index', 'update');

    Route::resource('subscription-reports', ADMIN\SubscriptionReport::class)->only('index');
    Route::post('subscription-reports/filter', [ADMIN\SubscriptionReport::class, 'tryonedigitalFilter'])->name('subscription-reports.filter');


    Route::resource('subscription-orders', ADMIN\TryonedigitalSubscriptionController::class)->only('index');
    Route::post('subscription-orders/filter', [ADMIN\TryonedigitalSubscriptionController::class, 'tryonedigitalFilter'])->name('subscription-orders.filter');
    Route::post('subscription-orders/reject/{id}',[ADMIN\TryonedigitalSubscriptionController::class,'reject'])->name('subscription-orders.reject');
    Route::post('subscription-orders/paid/{id}',[ADMIN\TryonedigitalSubscriptionController::class,'paid'])->name('subscription-orders.paid');
    Route::get('subscription-orders/get-invoice/{id}', [ADMIN\TryonedigitalSubscriptionController::class, 'getInvoice'])->name('subscription-orders.invoice');


    // Affiliates
    Route::resource('affiliates',ADMIN\TryonedigitalAffiliateController::class);
    Route::post('affiliates/filter', [ADMIN\TryonedigitalAffiliateController::class, 'tryonedigitalFilter'])->name('affiliates.filter');
    Route::post('affiliates/status/{id}',[ADMIN\TryonedigitalAffiliateController::class,'status'])->name('affiliates.status');
    Route::post('affiliates/delete-all', [ADMIN\TryonedigitalAffiliateController::class,'deleteAll'])->name('affiliates.delete-all');

    // Affiliates Withdraw Request
    Route::resource('affiliate-withdrawals',ADMIN\TryonedigitalWithdrawRequestController::class)->only('index');
    Route::post('affiliate-withdrawals/reject/{id}',[ADMIN\TryonedigitalWithdrawRequestController::class,'reject'])->name('affiliate-withdrawals.reject');
    Route::post('affiliate-withdrawals/paid/{id}',[ADMIN\TryonedigitalWithdrawRequestController::class,'paid'])->name('affiliate-withdrawals.paid');
    Route::post('affiliate-withdrawals/filter', [ADMIN\TryonedigitalWithdrawRequestController::class, 'tryonedigitalFilter'])->name('affiliate-withdrawals.filter');
    Route::post('affiliate-withdrawals/status/{id}',[ADMIN\TryonedigitalWithdrawRequestController::class,'status'])->name('affiliate-withdrawals.status');
    Route::post('affiliate-withdrawals/delete-all', [ADMIN\TryonedigitalWithdrawRequestController::class,'deleteAll'])->name('affiliate-withdrawals.delete-all');

    // Affiliates Report
    Route::resource('affiliate-reports',ADMIN\TryonedigitalAffiliateReportController::class)->only('index');
    Route::post('affiliate-reports/filter', [ADMIN\TryonedigitalAffiliateReportController::class, 'tryonedigitalFilter'])->name('affiliate-reports.filter');

    // Roles & Permissions
    Route::resource('roles', ADMIN\RoleController::class)->except('show');
    Route::resource('permissions', ADMIN\PermissionController::class)->only('index', 'store');

    // Settings
    Route::resource('addons', ADMIN\AddonController::class)->only('index', 'store', 'show');
    Route::resource('settings', ADMIN\SettingController::class)->only('index', 'update');
    Route::resource('system-settings', ADMIN\SystemSettingController::class)->only('index', 'store');

    // Gateway
    Route::resource('gateways', ADMIN\GatewayController::class)->only('index', 'update');

    Route::resource('manage-settings', ADMIN\TryonedigitalSettingsManagerController::class)->only('index', 'store');
    Route::post('manage-setting/domain', [ADMIN\TryonedigitalSettingsManagerController::class,'domain'])->name('domain.setting');

    Route::resource('currencies', ADMIN\TryonedigitalCurrencyController::class)->except('show');
    Route::post('currencies/filter', [ADMIN\TryonedigitalCurrencyController::class, 'tryonedigitalFilter'])->name('currencies.filter');
    Route::match(['get', 'post'], 'currencies/default/{id}', [ADMIN\TryonedigitalCurrencyController::class, 'default'])->name('currencies.default');
    Route::post('currencies/delete-all', [ADMIN\TryonedigitalCurrencyController::class,'deleteAll'])->name('currencies.delete-all');

    // Notifications manager
    Route::prefix('notifications')->controller(ADMIN\NotificationController::class)->name('notifications.')->group(function () {
        Route::get('/', 'mtIndex')->name('index');
        Route::get('/{id}', 'mtView')->name('mtView');
        Route::get('view/all/', 'mtReadAll')->name('mtReadAll');
    });
});
