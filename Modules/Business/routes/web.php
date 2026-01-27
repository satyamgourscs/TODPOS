<?php

use Illuminate\Support\Facades\Route;
use Modules\Business\App\Http\Controllers as Business;

Route::group(['as' => 'business.', 'prefix' => 'business', 'middleware' => ['users', 'expired']], function () {

    Route::get('update-expire-date', [Business\DashboardController::class, 'updateExpireDate']);

    Route::get('dashboard', [Business\DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/get-dashboard', [Business\DashboardController::class, 'getDashboardData'])->name('dashboard.data');
    Route::get('/overall-report', [Business\DashboardController::class, 'overall_report'])->name('dashboard.overall-report');
    Route::get('/revenue-statistic', [Business\DashboardController::class, 'revenue'])->name('dashboard.revenue');

    Route::resource('profiles', Business\ProfileController::class)->only('index', 'update');

    // Pos Sale
    Route::resource('sales', Business\TryonedigitalSaleController::class)->except(['show']);
    Route::get('sales/create-simple', [Business\TryonedigitalSaleController::class, 'createSimple'])->name('sales.create-simple');
    Route::post('sales/filter', [Business\TryonedigitalSaleController::class, 'tryonedigitalFilter'])->name('sales.filter');
    Route::post('sales/delete-all', [Business\TryonedigitalSaleController::class, 'deleteAll'])->name('sales.delete-all');
    Route::get('/get-product-prices', [Business\TryonedigitalSaleController::class, 'getProductPrices'])->name('products.prices');
    Route::get('/sale-cart-data', [Business\TryonedigitalSaleController::class, 'getCartData'])->name('sales.cart-data');
    Route::get('/get-invoice/{id}', [Business\TryonedigitalSaleController::class, 'getInvoice'])->name('sales.invoice');
    Route::post('sale/product-filter', [Business\TryonedigitalSaleController::class, 'productFilter'])->name('sales.product-filter');
    Route::post('sale/category-filter', [Business\TryonedigitalSaleController::class, 'categoryFilter'])->name('sales.category-filter');
    Route::post('sale/brand-filter', [Business\TryonedigitalSaleController::class, 'brandFilter'])->name('sales.brand-filter');
    Route::get('sale/{sale_id}/pdf', [Business\TryonedigitalSaleController::class, 'generatePDF'])->name('sales.pdf');
    Route::post('sale/mail/{sale_id}', [Business\TryonedigitalSaleController::class, 'sendMail'])->name('sales.mail');
    Route::post('create-customer', [Business\TryonedigitalSaleController::class, 'createCustomer'])->name('sales.store.customer');
    Route::get('sales/create-invoice', [Business\TryonedigitalSaleController::class, 'createInventory'])->name('sales.create-invoice');
    Route::get('/get-stock-prices', [Business\TryonedigitalSaleController::class, 'getStockPrices'])->name('products.stocks-prices');

    Route::resource('sale-returns', Business\SaleReturnController::class)->only('index', 'create', 'store');
    Route::post('sale-return/filter', [Business\SaleReturnController::class, 'tryonedigitalFilter'])->name('sale-returns.filter');

    // Purchase
    Route::resource('purchases', Business\TryonedigitalPurchaseController::class)->except('show');
    Route::post('purchases/filter', [Business\TryonedigitalPurchaseController::class, 'tryonedigitalFilter'])->name('purchases.filter');
    Route::post('purchases/delete-all', [Business\TryonedigitalPurchaseController::class, 'deleteAll'])->name('purchases.delete-all');
    Route::get('/purchase-cart', [Business\TryonedigitalPurchaseController::class, 'showPurchaseCart'])->name('purchases.cart');
    Route::get('/purchase-cart-data', [Business\TryonedigitalPurchaseController::class, 'getCartData'])->name('purchases.cart-data');
    Route::get('/purchase/get-invoice/{id}', [Business\TryonedigitalPurchaseController::class, 'getInvoice'])->name('purchases.invoice');
    Route::post('purchase/product-filter', [Business\TryonedigitalPurchaseController::class, 'productFilter'])->name('purchases.product-filter');
    Route::post('purchase/category-filter', [Business\TryonedigitalPurchaseController::class, 'categoryFilter'])->name('purchases.category-filter');
    Route::post('purchase/brand-filter', [Business\TryonedigitalPurchaseController::class, 'brandFilter'])->name('purchases.brand-filter');
    Route::get('purchase/pdf/{purchase_id}', [Business\TryonedigitalPurchaseController::class, 'generatePDF'])->name('purchases.pdf');
    Route::post('purchase/mail/{purchase_id}', [Business\TryonedigitalPurchaseController::class, 'sendMail'])->name('purchases.mail');
    Route::post('create-supplier', [Business\TryonedigitalPurchaseController::class, 'createSupplier'])->name('purchases.store.supplier');
    // Route::post('purchase/bulk-store', [Business\TryonedigitalPurchaseController::class, 'bulkStore'])->name('purchases.bulk-store');
    Route::post('purchase/bulk-cart-store', [Business\TryonedigitalPurchaseController::class, 'bulkCartStore'])->name('purchases.bulk-cart-store');

    Route::resource('purchase-returns', Business\PurchaseReturnController::class)->only('index', 'create', 'store');
    Route::post('purchase-return/filter', [Business\PurchaseReturnController::class, 'tryonedigitalFilter'])->name('purchase-returns.filter');

    Route::resource('carts', Business\CartController::class);
    Route::post('cart/remove-all', [Business\CartController::class, 'removeAllCart'])->name('carts.remove-all');

    //Transfers
    Route::resource('transfers', Business\TryonedigitalTransferController::class)->except('show');
    Route::post('transfers/filter', [Business\TryonedigitalTransferController::class, 'tryonedigitalFilter'])->name('transfers.filter');
    Route::post('transfers/status/{id}', [Business\TryonedigitalTransferController::class, 'status'])->name('transfers.status');
    Route::post('transfers/delete-all', [Business\TryonedigitalTransferController::class, 'deleteAll'])->name('transfers.delete-all');
    Route::get('transfers-excel', [Business\TryonedigitalTransferController::class, 'exportExcel'])->name('transfers.excel');
    Route::get('transfers-csv', [Business\TryonedigitalTransferController::class, 'exportCsv'])->name('transfers.csv');

    Route::resource('stocks', Business\TryonedigitalStockController::class)->only('index');
    Route::post('stocks/filter', [Business\TryonedigitalStockController::class, 'tryonedigitalFilter'])->name('stocks.filter');
    Route::get('stocks/excel', [Business\TryonedigitalStockController::class, 'exportExcel'])->name('stocks.excel');
    Route::get('stocks/csv', [Business\TryonedigitalStockController::class, 'exportCsv'])->name('stocks.csv');

    Route::resource('expired-products', Business\TryonedigitalExpireProductController::class)->only('index');
    Route::post('expired-products/filter', [Business\TryonedigitalExpireProductController::class, 'tryonedigitalFilter'])->name('expired.products.filter');
    Route::get('expired-products/excel', [Business\TryonedigitalExpireProductController::class, 'exportExcel'])->name('expired.products.excel');
    Route::get('expired-products/csv', [Business\TryonedigitalExpireProductController::class, 'exportCsv'])->name('expired.products.csv');

    Route::resource('loss-profits', Business\TryonedigitalLossProfitController::class)->only('index', 'show');
    Route::post('loss-profits/filter', [Business\TryonedigitalLossProfitController::class, 'tryonedigitalFilter'])->name('loss-profits.filter');
    Route::get('loss-profits/pdf', [Business\TryonedigitalLossProfitController::class, 'generatePDF'])->name('loss-profits.pdf');
    Route::get('loss-profits/excel', [Business\TryonedigitalLossProfitController::class, 'exportExcel'])->name('loss-profits.excel');
    Route::get('loss-profits/csv', [Business\TryonedigitalLossProfitController::class, 'exportCsv'])->name('loss-profits.csv');

    Route::resource('stock-reports', Business\TryonedigitalStockReportController::class)->only('index');
    Route::post('stock-reports/filter', [Business\TryonedigitalStockReportController::class, 'tryonedigitalFilter'])->name('stock-reports.filter');
    Route::get('stock-reports/excel', [Business\TryonedigitalStockReportController::class, 'exportExcel'])->name('stock-reports.excel');
    Route::get('stock-reports/csv', [Business\TryonedigitalStockReportController::class, 'exportCsv'])->name('stock-reports.csv');

    Route::resource('due-reports', Business\TryonedigitalDueReportController::class)->only('index');
    Route::post('due-reports/filter', [Business\TryonedigitalDueReportController::class, 'tryonedigitalFilter'])->name('due-reports.filter');
    Route::get('due-reports/pdf', [Business\TryonedigitalDueReportController::class, 'generatePDF'])->name('due-reports.pdf');
    Route::get('due-reports/excel', [Business\TryonedigitalDueReportController::class, 'exportExcel'])->name('due-reports.excel');
    Route::get('due-reports/csv', [Business\TryonedigitalDueReportController::class, 'exportCsv'])->name('due-reports.csv');

    Route::resource('supplier-due-reports', Business\TryonedigitalSupplierDueReportController::class)->only('index');
    Route::post('supplier-due-reports/filter', [Business\TryonedigitalSupplierDueReportController::class, 'tryonedigitalFilter'])->name('supplier-due-reports.filter');
    Route::get('supplier-due-reports/pdf', [Business\TryonedigitalSupplierDueReportController::class, 'generatePDF'])->name('supplier-due-reports.pdf');
    Route::get('supplier-due-reports/excel', [Business\TryonedigitalSupplierDueReportController::class, 'exportExcel'])->name('supplier-due-reports.excel');
    Route::get('supplier-due-reports/csv', [Business\TryonedigitalSupplierDueReportController::class, 'exportCsv'])->name('supplier-due-reports.csv');

    Route::resource('loss-profit-reports', Business\TryonedigitalLossProfitReportController::class)->only('index');
    Route::post('loss-profit-reports/filter', [Business\TryonedigitalLossProfitReportController::class, 'tryonedigitalFilter'])->name('loss-profit-reports.filter');
    Route::get('loss-profit-reports/pdf', [Business\TryonedigitalLossProfitReportController::class, 'generatePDF'])->name('loss-profit-reports.pdf');
    Route::get('loss-profit-reports/excel', [Business\TryonedigitalLossProfitReportController::class, 'exportExcel'])->name('loss-profit-reports.excel');
    Route::get('loss-profit-reports/csv', [Business\TryonedigitalLossProfitReportController::class, 'exportCsv'])->name('loss-profit-reports.csv');
    Route::get('loss-profits-details', [Business\TryonedigitalLossProfitReportController::class, 'lossProfitDetails'])->name('loss-profit-reports.details');
    Route::post('loss-profits-details/filter', [Business\TryonedigitalLossProfitReportController::class, 'lossProfitFilter'])->name('loss-profit.details.reports.filter');

    Route::resource('sale-reports', Business\TryonedigitalSaleReportController::class)->only('index');
    Route::post('sale-reports/filter', [Business\TryonedigitalSaleReportController::class, 'tryonedigitalFilter'])->name('sale-reports.filter');
    Route::get('/sales-reports/pdf', [Business\TryonedigitalSaleReportController::class, 'generatePDF'])->name('sales.reports.pdf');
    Route::get('/sales-reports/excel', [Business\TryonedigitalSaleReportController::class, 'exportExcel'])->name('sales.reports.excel');
    Route::get('/sales-reports/csv', [Business\TryonedigitalSaleReportController::class, 'exportCsv'])->name('sales.reports.csv');

    Route::resource('sale-return-reports', Business\TryonedigitalSaleReturnReportController::class)->only('index');
    Route::post('sale-return-reports/filter', [Business\TryonedigitalSaleReturnReportController::class, 'tryonedigitalFilter'])->name('sale-return-reports.filter');
    Route::get('/sales-return-report/pdf', [Business\TryonedigitalSaleReturnReportController::class, 'generatePDF'])->name('sales.return.pdf');
    Route::get('sales-return-report/excel', [Business\TryonedigitalSaleReturnReportController::class, 'exportExcel'])->name('sales-return-report.excel');
    Route::get('sales-return-report/csv', [Business\TryonedigitalSaleReturnReportController::class, 'exportCsv'])->name('sales-return-report.csv');

    Route::resource('purchase-reports', Business\TryonedigitalPurchaseReportController::class)->only('index');
    Route::post('purchase-reports/filter', [Business\TryonedigitalPurchaseReportController::class, 'tryonedigitalFilter'])->name('purchase-reports.filter');
    Route::get('purchase-reports/pdf', [Business\TryonedigitalPurchaseReportController::class, 'generatePDF'])->name('purchase-reports.pdf');
    Route::get('purchase-reports/excel', [Business\TryonedigitalPurchaseReportController::class, 'exportExcel'])->name('purchase-reports.excel');
    Route::get('purchase-reports/csv', [Business\TryonedigitalPurchaseReportController::class, 'exportCsv'])->name('purchase-reports.csv');

    Route::resource('purchase-return-reports', Business\TryonedigitalPurchaseReturnReportController::class)->only('index');
    Route::post('purchase-return-reports/filter', [Business\TryonedigitalPurchaseReturnReportController::class, 'tryonedigitalFilter'])->name('purchase-return-reports.filter');
    Route::get('purchase-return-reports/pdf', [Business\TryonedigitalPurchaseReturnReportController::class, 'generatePDF'])->name('purchase-return-reports.pdf');
    Route::get('purchase-return-reports/excel', [Business\TryonedigitalPurchaseReturnReportController::class, 'exportExcel'])->name('purchase-return-reports.excel');
    Route::get('purchase-return-reports/csv', [Business\TryonedigitalPurchaseReturnReportController::class, 'exportCsv'])->name('purchase-return-reports.csv');

    Route::resource('products', Business\TryonedigitalProductController::class);
    Route::post('products/filter', [Business\TryonedigitalProductController::class, 'tryonedigitalFilter'])->name('products.filter');
    Route::post('products/status/{id}', [Business\TryonedigitalProductController::class, 'status'])->name('products.status');
    Route::post('products/delete-all', [Business\TryonedigitalProductController::class, 'deleteAll'])->name('products.delete-all');
    Route::get('products/pdf', [Business\TryonedigitalProductController::class, 'generatePDF'])->name('products.pdf');
    Route::get('products-excel', [Business\TryonedigitalProductController::class, 'exportExcel'])->name('products.excel');
    Route::get('products-csv', [Business\TryonedigitalProductController::class, 'exportCsv'])->name('products.csv');
    Route::get('/all-products', [Business\TryonedigitalProductController::class, 'getAllProduct'])->name('products.all-product');
    Route::get('/products-by-category/{category_id?}', [Business\TryonedigitalProductController::class, 'getByCategory'])->name('products.get-by-category');
    Route::get('products-expired', [Business\TryonedigitalProductController::class, 'expiredProduct'])->name('products.expired');
    Route::post('products-expired/filter', [Business\TryonedigitalProductController::class, 'tryonedigitalExpireProductFilter'])->name('products.expired.filter');
    Route::get('products-expired/excel', [Business\TryonedigitalProductController::class, 'exportExpireProductExcel'])->name('products.expired.excel');
    Route::get('products-expired/csv', [Business\TryonedigitalProductController::class, 'exportExpireProductCsv'])->name('products.expired.csv');
    Route::post('/create-stock/{id}', [Business\TryonedigitalProductController::class, 'CreateStock'])->name('products.create-stock');
    Route::get('/product/get-shelf', [Business\TryonedigitalProductController::class, 'getShelf'])->name('product.get.shelf');

    Route::get('product/stocks/edit/{id}', [Business\TryonedigitalProductController::class, 'StockEdit'])->name('products.stocks.edit');
    Route::put('product/stocks/update/{id}', [Business\TryonedigitalProductController::class, 'StockUpdate'])->name('products.update.stock');

    Route::resource('bulk-uploads', Business\BulkUploadController::class)->only('index', 'store');

    Route::resource('expired-product-reports', Business\TryonedigitalExpireProductReportController::class)->only('index');
    Route::post('expired-product-reports/filter', [Business\TryonedigitalExpireProductReportController::class, 'tryonedigitalFilter'])->name('expired.product.reports.filter');
    Route::get('expired-product-reports/excel', [Business\TryonedigitalExpireProductReportController::class, 'exportExcel'])->name('expired.product.reports.excel');
    Route::get('expired-product-reports/csv', [Business\TryonedigitalExpireProductReportController::class, 'exportCsv'])->name('expired.product.reports.csv');

    Route::resource('barcodes', Business\BarcodeGeneratorController::class)->only('index', 'store');
    Route::get('barcodes-products', [Business\BarcodeGeneratorController::class, 'fetchProducts'])->name('barcodes.products');
    Route::get('/barcodes-preview', [Business\BarcodeGeneratorController::class, 'preview'])->name('barcodes.preview');

    Route::resource('brands', Business\TryonedigitalBrandController::class);
    Route::post('brands/filter', [Business\TryonedigitalBrandController::class, 'tryonedigitalFilter'])->name('brands.filter');
    Route::post('brands/status/{id}', [Business\TryonedigitalBrandController::class, 'status'])->name('brands.status');
    Route::post('brands/delete-all', [Business\TryonedigitalBrandController::class, 'deleteAll'])->name('brands.delete-all');

    // Payment Types
    Route::resource('payment-types', Business\TryonedigitalPaymentTypeController::class)->except('show');
    Route::post('payment-types/filter', [Business\TryonedigitalPaymentTypeController::class, 'tryonedigitalFilter'])->name('payment-types.filter');
    Route::post('payment-types/status/{id}', [Business\TryonedigitalPaymentTypeController::class, 'status'])->name('payment-types.status');
    Route::post('payment-types/delete-all', [Business\TryonedigitalPaymentTypeController::class, 'deleteAll'])->name('payment-types.delete-all');

    // units
    Route::resource('units', Business\TryonedigitalUnitController::class)->except('show');
    Route::post('units/filter', [Business\TryonedigitalUnitController::class, 'tryonedigitalFilter'])->name('units.filter');
    Route::post('units/status/{id}', [Business\TryonedigitalUnitController::class, 'status'])->name('units.status');
    Route::post('units/delete-all', [Business\TryonedigitalUnitController::class, 'deleteAll'])->name('units.delete-all');

    // product model
    Route::resource('product-models', Business\TryonedigitalProductModelController::class)->except('show');
    Route::post('product-models/filter', [Business\TryonedigitalProductModelController::class, 'tryonedigitalFilter'])->name('product-models.filter');
    Route::post('product-models/status/{id}', [Business\TryonedigitalProductModelController::class, 'status'])->name('product-models.status');
    Route::post('product-models/delete-all', [Business\TryonedigitalProductModelController::class, 'deleteAll'])->name('product-models.delete-all');

    // variations
    Route::resource('variations', Business\TryonedigitalVariationController::class)->except('show');
    Route::post('variations/filter', [Business\TryonedigitalVariationController::class, 'tryonedigitalFilter'])->name('variations.filter');
    Route::post('variations/status/{id}', [Business\TryonedigitalVariationController::class, 'status'])->name('variations.status');
    Route::post('variations/delete-all', [Business\TryonedigitalVariationController::class, 'deleteAll'])->name('variations.delete-all');

    Route::resource('categories', Business\TryonedigitalCategoryController::class);
    Route::post('categories/status/{id}', [Business\TryonedigitalCategoryController::class, 'status'])->name('categories.status');
    Route::post('categories/delete-all', [Business\TryonedigitalCategoryController::class, 'deleteAll'])->name('categories.deleteAll');
    Route::post('categories/delete-all', [Business\TryonedigitalCategoryController::class, 'deleteAll'])->name('categories.delete-all');
    Route::post('categories/filter', [Business\TryonedigitalCategoryController::class, 'tryonedigitalFilter'])->name('categories.filter');

    //Parties
    Route::resource('parties', Business\TryonedigitalPartyController::class)->except('show');
    Route::post('parties/filter', [Business\TryonedigitalPartyController::class, 'tryonedigitalFilter'])->name('parties.filter');
    Route::post('parties/status/{id}', [Business\TryonedigitalPartyController::class, 'status'])->name('parties.status');
    Route::post('parties/delete-all', [Business\TryonedigitalPartyController::class, 'deleteAll'])->name('parties.delete-all');

    //Income Category
    Route::resource('income-categories', Business\TryonedigitalIncomeCategoryController::class)->except('show');
    Route::post('income-categories/filter', [Business\TryonedigitalIncomeCategoryController::class, 'tryonedigitalFilter'])->name('income-categories.filter');
    Route::post('income-categories/status/{id}', [Business\TryonedigitalIncomeCategoryController::class, 'status'])->name('income-categories.status');
    Route::post('income-categories/delete-all', [Business\TryonedigitalIncomeCategoryController::class, 'deleteAll'])->name('income-categories.delete-all');

    //Income
    Route::resource('incomes', Business\TryonedigitalIncomeController::class)->except('show');
    Route::post('incomes/filter', [Business\TryonedigitalIncomeController::class, 'tryonedigitalFilter'])->name('incomes.filter');
    Route::post('incomes/status/{id}', [Business\TryonedigitalIncomeController::class, 'status'])->name('incomes.status');
    Route::post('incomes/delete-all', [Business\TryonedigitalIncomeController::class, 'deleteAll'])->name('incomes.delete-all');

    //Expense Category
    Route::resource('expense-categories', Business\TryonedigitalExpenseCategoryController::class)->except('show');
    Route::post('expense-categories/filter', [Business\TryonedigitalExpenseCategoryController::class, 'tryonedigitalFilter'])->name('expense-categories.filter');
    Route::post('expense-categories/status/{id}', [Business\TryonedigitalExpenseCategoryController::class, 'status'])->name('expense-categories.status');
    Route::post('expense-categories/delete-all', [Business\TryonedigitalExpenseCategoryController::class, 'deleteAll'])->name('expense-categories.delete-all');

    //Expense
    Route::resource('expenses', Business\TryonedigitalExpenseController::class)->except('show');
    Route::post('expenses/filter', [Business\TryonedigitalExpenseController::class, 'tryonedigitalFilter'])->name('expenses.filter');
    Route::post('expenses/status/{id}', [Business\TryonedigitalExpenseController::class, 'status'])->name('expenses.status');
    Route::post('expenses/delete-all', [Business\TryonedigitalExpenseController::class, 'deleteAll'])->name('expenses.delete-all');

    Route::resource('racks', Business\TryonedigitalRackController::class);
    Route::post('rack/filter', [Business\TryonedigitalRackController::class, 'tryonedigitalFilter'])->name('racks.filter');
    Route::post('rack/status/{id}', [Business\TryonedigitalRackController::class, 'status'])->name('racks.status');
    Route::post('rack/delete-all', [Business\TryonedigitalRackController::class, 'deleteAll'])->name('racks.delete-all');

    Route::resource('shelfs', Business\TryonedigitalShelfController::class);
    Route::post('shelf/filter', [Business\TryonedigitalShelfController::class, 'tryonedigitalFilter'])->name('shelfs.filter');
    Route::post('shelf/status/{id}', [Business\TryonedigitalShelfController::class, 'status'])->name('shelfs.status');
    Route::post('shelf/delete-all', [Business\TryonedigitalShelfController::class, 'deleteAll'])->name('shelfs.delete-all');


    //Reports
    Route::resource('income-reports', Business\TryonedigitalIncomeReportController::class)->only('index');
    Route::post('income-reports/filter', [Business\TryonedigitalIncomeReportController::class, 'tryonedigitalFilter'])->name('income-reports.filter');
    Route::get('income-reports/pdf', [Business\TryonedigitalIncomeReportController::class, 'generatePDF'])->name('income-reports.pdf');
    Route::get('income-reports/excel', [Business\TryonedigitalIncomeReportController::class, 'exportExcel'])->name('income-reports.excel');
    Route::get('income-reports/csv', [Business\TryonedigitalIncomeReportController::class, 'exportCsv'])->name('income-reports.csv');

    Route::resource('expense-reports', Business\TryonedigitalExpenseReportController::class)->only('index');
    Route::post('expense-reports/filter', [Business\TryonedigitalExpenseReportController::class, 'tryonedigitalFilter'])->name('expense-reports.filter');
    Route::get('expense-reports/pdf', [Business\TryonedigitalExpenseReportController::class, 'generatePDF'])->name('expense-reports.pdf');
    Route::get('expense-reports/excel', [Business\TryonedigitalExpenseReportController::class, 'exportExcel'])->name('expense-reports.excel');
    Route::get('expense-reports/csv', [Business\TryonedigitalExpenseReportController::class, 'exportCsv'])->name('expense-reports.csv');

    Route::resource('transaction-history-reports', Business\TryonedigitalTransactionHistoryReportController::class)->only('index');
    Route::post('transaction-history-reports/filter', [Business\TryonedigitalTransactionHistoryReportController::class, 'tryonedigitalFilter'])->name('transaction-history-reports.filter');
    Route::get('transaction-history-reports/pdf', [Business\TryonedigitalTransactionHistoryReportController::class, 'generatePDF'])->name('transaction-history-reports.pdf');
    Route::get('transaction-history-reports/excel', [Business\TryonedigitalTransactionHistoryReportController::class, 'exportExcel'])->name('transaction-history-reports.excel');
    Route::get('transaction-history-reports/csv', [Business\TryonedigitalTransactionHistoryReportController::class, 'exportCsv'])->name('transaction-history-reports.csv');

    Route::resource('subscription-reports', Business\TryonedigitalSubscriptionReportController::class)->only('index');
    Route::post('subscription-reports/filter', [Business\TryonedigitalSubscriptionReportController::class, 'tryonedigitalFilter'])->name('subscription-reports.filter');
    Route::get('subscription-reports/pdf', [Business\TryonedigitalSubscriptionReportController::class, 'generatePDF'])->name('subscription-reports.pdf');
    Route::get('subscription-reports/excel', [Business\TryonedigitalSubscriptionReportController::class, 'exportExcel'])->name('subscription-reports.excel');
    Route::get('subscription-reports/csv', [Business\TryonedigitalSubscriptionReportController::class, 'exportCsv'])->name('subscription-reports.csv');
    Route::get('subscription-reports/get-invoice/{id}', [Business\TryonedigitalSubscriptionReportController::class, 'getInvoice'])->name('subscription-reports.invoice');

    // Vat Reports
    Route::resource('vat-reports', Business\TryonedigitalVatReportController::class)->only('index');
    Route::get('vat-reports/excel{type?}', [Business\TryonedigitalVatReportController::class, 'exportExcel'])->name('vat.reports.excel');
    Route::get('vat-reports/csv{type?}', [Business\TryonedigitalVatReportController::class, 'exportCsv'])->name('vat.reports.csv');

    Route::resource('dues', Business\TryonedigitalDueController::class)->only('index');
    Route::post('dues/filter', [Business\TryonedigitalDueController::class, 'tryonedigitalFilter'])->name('dues.filter');
    Route::get('collect-dues/{id}', [Business\TryonedigitalDueController::class, 'collectDue'])->name('collect.dues');
    Route::post('collect-dues/store', [Business\TryonedigitalDueController::class, 'collectDueStore'])->name('collect.dues.store');
    Route::get('/collect-dues-invoice/{id}', [Business\TryonedigitalDueController::class, 'getInvoice'])->name('collect.dues.invoice');
    Route::get('collect-dues/pdf/{due_id}', [Business\TryonedigitalDueController::class, 'generatePDF'])->name('collect.dues.pdf');
    Route::post('collect-dues/mail/{id}', [Business\TryonedigitalDueController::class, 'sendMail'])->name('collect.dues.mail');

    Route::get('party-dues', [Business\TryonedigitalDueController::class, 'partyDue'])->name('party.dues');
    Route::post('party-dues/filter', [Business\TryonedigitalDueController::class, 'partyDueFilter'])->name('party.dues.filter');

    Route::resource('roles', Business\UserRoleController::class)->except('show');
    Route::post('roles/filter', [Business\UserRoleController::class, 'tryonedigitalFilter'])->name('roles.filter');
    Route::post('roles/delete-all', [Business\UserRoleController::class, 'deleteAll'])->name('roles.delete-all');

    Route::resource('settings', Business\SettingController::class)->only('index', 'update');
    Route::resource('subscriptions', Business\TryonedigitalSubscriptionController::class)->withoutMiddleware('expired')->only('index');

    Route::resource('manage-settings', Business\TryonedigitalSettingsManagerController::class);
    Route::post('/invoice-settings', [Business\TryonedigitalSettingsManagerController::class, 'updateInvoice'])->name('invoice.update');
    Route::post('/product-settings', [Business\TryonedigitalSettingsManagerController::class, 'updateProductSetting'])->name('product.settings.update');

    Route::resource('currencies', Business\TryonedigitalCurrencyController::class)->only('index');
    Route::post('currencies/filter', [Business\TryonedigitalCurrencyController::class, 'tryonedigitalFilter'])->name('currencies.filter');
    Route::match(['get', 'post'], 'currencies/default/{id}', [Business\TryonedigitalCurrencyController::class, 'default'])->name('currencies.default');

    Route::resource('vats', Business\TryonedigitalVatController::class);
    Route::post('vats/status/{id}', [Business\TryonedigitalVatController::class, 'status'])->name('vats.status');
    Route::post('vats/delete-all', [Business\TryonedigitalVatController::class, 'deleteAll'])->name('vats.deleteAll');
    Route::post('vat/filter', [Business\TryonedigitalVatController::class, 'tryonedigitalFilter'])->name('vats.filter');
    Route::post('vat-group/filter', [Business\TryonedigitalVatController::class, 'VatGroupFilter'])->name('vat-groups.filter');

    Route::prefix('notifications')->controller(Business\TryonedigitalNotificationController::class)->name('notifications.')->group(function () {
        Route::get('/', 'mtIndex')->name('index');
        Route::post('/filter', 'maanFilter')->name('filter');
        Route::get('/{id}', 'mtView')->name('mtView');
        Route::get('view/all/', 'mtReadAll')->name('mtReadAll');
    });
});
