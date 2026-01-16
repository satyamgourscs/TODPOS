<?php

return [

    // =========================
    // Sales Report
    // =========================
    'sales' => [
        'invoiceNumber'    => ['label' => 'Invoice Number', 'type' => 'string'],
        'invoiceNumber'    => ['label' => 'Invoice Number', 'type' => 'string'],
        'invoiceNumber'    => ['label' => 'Invoice Number', 'type' => 'string'],
        'saleDate'         => ['label' => 'Sale Date', 'type' => 'date'],
        'totalAmount'      => ['label' => 'Total Amount', 'type' => 'amount'],
        'paidAmount'       => ['label' => 'Paid Amount', 'type' => 'amount'],
        'dueAmount'        => ['label' => 'Due Amount', 'type' => 'amount'],
        'discountAmount'   => ['label' => 'Discount Amount', 'type' => 'amount'],
        'discount_percent' => ['label' => 'Discount Percent', 'type' => 'percentage'],
        'discount_type'    => ['label' => 'Discount Type', 'type' => 'string'],
        'shipping_charge'  => ['label' => 'Shipping Charge', 'type' => 'amount'],
        'isPaid'           => ['label' => 'Payment Status', 'type' => 'boolean'],
        'vat_amount'       => ['label' => 'VAT Amount', 'type' => 'amount'],
        'vat_percent'      => ['label' => 'VAT Percent', 'type' => 'percentage'],
        'lossProfit'       => ['label' => 'Loss/Profit', 'type' => 'amount'],
        'created_at'       => ['label' => 'Created At', 'type' => 'date'],
        'updated_at'       => ['label' => 'Updated At', 'type' => 'date'],

        'branch' => [
            'name'      => ['label' => 'Branch Name', 'type' => 'string'],
            'phone'     => ['label' => 'Branch Phone', 'type' => 'string'],
            'address'   => ['label' => 'Branch Address', 'type' => 'string'],
        ],

        'party' => [
            'name'                 => ['label' => 'Customer Name', 'type' => 'string'],
            'email'                => ['label' => 'Customer Email', 'type' => 'string'],
            'phone'                => ['label' => 'Customer Phone', 'type' => 'number'],
            'due'                  => ['label' => 'Customer Due Amount', 'type' => 'amount'],
            'address'              => ['label' => 'Customer Address', 'type' => 'string'],
            'credit_limit'         => ['label' => 'Customer Credit Limit', 'type' => 'amount'],
            'wallet'               => ['label' => 'Customer Wallet Balance', 'type' => 'amount'],
            'opening_balance'      => ['label' => 'Customer Opening Balance', 'type' => 'amount'],
            'opening_balance_type' => ['label' => 'Customer Opening Balance Type', 'type' => 'string'],
            'billing_address'      => ['label' => 'Customer Billing Address', 'type' => 'string'],
            'shipping_address'     => ['label' => 'Customer Shipping Address', 'type' => 'string'],
        ],
    ],

    // =========================
    // Purchases Report
    // =========================
    'purchases' => [
        'invoiceNumber'  => ['label' => 'Invoice Number', 'type' => 'string'],
        'purchaseDate'   => ['label' => 'Purchase Date', 'type' => 'date'],
        'totalAmount'    => ['label' => 'Total Amount', 'type' => 'amount'],
        'paidAmount'     => ['label' => 'Paid Amount', 'type' => 'amount'],
        'dueAmount'      => ['label' => 'Due Amount', 'type' => 'amount'],
        'vat_amount'     => ['label' => 'VAT Amount', 'type' => 'amount'],
        'created_at'     => ['label' => 'Created At', 'type' => 'date'],
        'updated_at'     => ['label' => 'Updated At', 'type' => 'date'],

        'branch' => [
            'name'      => ['label' => 'Branch Name', 'type' => 'string'],
            'phone'     => ['label' => 'Branch Phone', 'type' => 'string'],
            'address'   => ['label' => 'Branch Address', 'type' => 'string'],
        ],

        'party' => [
            'name'                 => ['label' => 'Customer Name', 'type' => 'string'],
            'email'                => ['label' => 'Customer Email', 'type' => 'string'],
            'phone'                => ['label' => 'Customer Phone', 'type' => 'number'],
            'due'                  => ['label' => 'Customer Due Amount', 'type' => 'amount'],
            'address'              => ['label' => 'Customer Address', 'type' => 'string'],
            'credit_limit'         => ['label' => 'Customer Credit Limit', 'type' => 'amount'],
            'wallet'               => ['label' => 'Customer Wallet Balance', 'type' => 'amount'],
            'opening_balance'      => ['label' => 'Customer Opening Balance', 'type' => 'amount'],
            'opening_balance_type' => ['label' => 'Customer Opening Balance Type', 'type' => 'string'],
            'billing_address'      => ['label' => 'Customer Billing Address', 'type' => 'string'],
            'shipping_address'     => ['label' => 'Customer Shipping Address', 'type' => 'string'],
        ],
    ],

    // =========================
    // Products Report
    // =========================
    'products' => [
        'productName'     => ['label' => 'Product Name', 'type' => 'string'],
        'productPicture'  => ['label' => 'Product Image', 'type' => 'string'],
        'productCode'     => ['label' => 'Product Code', 'type' => 'string'],
        'product_type'    => ['label' => 'Product Type', 'type' => 'string'],
        'alert_qty'       => ['label' => 'Alert Qty', 'type' => 'number'],
        'productManufacturer' => ['label' => 'Manufacturer', 'type' => 'string'],

        'stocks' => [
            'productPurchasePrice' => ['label' => 'Purchase Price', 'type' => 'amount'],
            'productSalePrice'     => ['label' => 'Sale Price', 'type' => 'amount'],
            'productWholeSalePrice' => ['label' => 'Wholesale Price', 'type' => 'amount'],
            'productDealerPrice'   => ['label' => 'Dealer Price', 'type' => 'amount'],
            'batch_no'             => ['label' => 'Batch No', 'type' => 'string'],
            'mfg_date'             => ['label' => 'Manufacture Date', 'type' => 'date'],
            'expire_date'          => ['label' => 'Stock Expire Date', 'type' => 'date'],
        ],

        // Virtual field
        'total_stock' => ['label' => 'Total Stock (All Batches)', 'type' => 'number'],
    ],

    // =========================
    // Customers
    // =========================
    'customers' => [
        'name' => ['label' => 'Name', 'type' => 'string'],
        'email' => ['label' => 'Email', 'type' => 'string'],
        'phone' => ['label' => 'Phone', 'type' => 'string'],
        'due' => ['label' => 'Due', 'type' => 'amount'],
        'image' => ['label' => 'Image', 'type' => 'string'],
        'status' => ['label' => 'Status', 'type' => 'string'],
        'address' => ['label' => 'Address', 'type' => 'string'],
        'credit_limit' => ['label' => 'Credit Limit', 'type' => 'amount'],
        'wallet' => ['label' => 'Wallet', 'type' => 'amount'],
        'opening_balance' => ['label' => 'Opening Balance', 'type' => 'amount'],
        'opening_balance_type' => ['label' => 'Opening Balance Type', 'type' => 'string'],
        'billing_address' => ['label' => 'Billing Address', 'type' => 'string'],
        'shipping_address' => ['label' => 'Shipping Address', 'type' => 'string'],
    ],

    // =========================
    // Suppliers
    // =========================
    'suppliers' => [
        'name' => ['label' => 'Name', 'type' => 'string'],
        'email' => ['label' => 'Email', 'type' => 'string'],
        'phone' => ['label' => 'Phone', 'type' => 'string'],
        'due' => ['label' => 'Due', 'type' => 'amount'],
        'image' => ['label' => 'Image', 'type' => 'string'],
        'status' => ['label' => 'Status', 'type' => 'string'],
        'address' => ['label' => 'Address', 'type' => 'string'],
        'credit_limit' => ['label' => 'Credit Limit', 'type' => 'amount'],
        'wallet' => ['label' => 'Wallet', 'type' => 'amount'],
        'opening_balance' => ['label' => 'Opening Balance', 'type' => 'amount'],
        'opening_balance_type' => ['label' => 'Opening Balance Type', 'type' => 'string'],
        'billing_address' => ['label' => 'Billing Address', 'type' => 'string'],
        'shipping_address' => ['label' => 'Shipping Address', 'type' => 'string'],
    ],

    // =========================
    // Expenses
    // =========================
    'expenses' => [
        'amount' => ['label' => 'Amount', 'type' => 'amount'],
        'expanseFor' => ['label' => 'Expense For', 'type' => 'string'],
        'referenceNo' => ['label' => 'Reference No', 'type' => 'string'],
        'note' => ['label' => 'Note', 'type' => 'string'],
        'expenseDate' => ['label' => 'Expense Date', 'type' => 'date'],

        'branch' => [
            'name' => ['label' => 'Branch Name', 'type' => 'string'],
            'phone' => ['label' => 'Branch Phone', 'type' => 'string'],
            'address' => ['label' => 'Branch Address', 'type' => 'string'],
        ],

        'user' => [
            'name' => ['label' => 'User Name', 'type' => 'string'],
            'email' => ['label' => 'User Email', 'type' => 'string'],
            'phone' => ['label' => 'User Phone', 'type' => 'number'],
        ],

        'category' => [
            'categoryName' => ['label' => 'Category Name', 'type' => 'string'],
        ],

        'payment_type' => [
            'name' => ['label' => 'Payment Type Name', 'type' => 'string'],
        ],
    ],

    // =========================
    // Taxes
    // =========================
    'vats' => [
        'name' => ['label' => 'Name', 'type' => 'string'],
        'rate' => ['label' => 'Rate', 'type' => 'percentage'],
        'sub_vat' => ['label' => 'Sub Vat', 'type' => 'string'],
    ],
];
