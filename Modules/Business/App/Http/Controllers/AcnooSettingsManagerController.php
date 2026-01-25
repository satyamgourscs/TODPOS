<?php

namespace Modules\Business\App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;
use App\Models\ProductSetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class AcnooSettingsManagerController extends Controller
{
    public function index()
    {
        $businessId = auth()->user()->business_id;
        $invoiceSettingKey = 'invoice_setting_' . $businessId;
        $invoice_setting = Option::where('key', $invoiceSettingKey)->first();
        $product_setting = ProductSetting::where('business_id', auth()->user()->business_id)->first();

        return view('business::manage-settings.index', compact('invoice_setting', 'product_setting'));
    }

    public function updateInvoice(Request $request)
    {
        $request->validate([
            'invoice_size' => 'required|string|max:100|in:a4,3_inch_80mm',
        ]);

        $key = 'invoice_setting_' . auth()->user()->business_id;

        Option::updateOrCreate(
            ['key' => $key],
            ['value' => $request->invoice_size]
        );

        Cache::forget($key);

        return response()->json(__('Invoice setting updated successfully.'));
    }

    public function updateProductSetting(Request $request)
    {
        $request->validate([
            'show_product_price' => 'nullable|boolean',
            'show_product_code' => 'nullable|boolean',
            'show_product_stock' => 'nullable|boolean',
            'show_product_sale_price' => 'nullable|boolean',
            'show_product_dealer_price' => 'nullable|boolean',
            'show_product_wholesale_price' => 'nullable|boolean',
            'show_product_unit' => 'nullable|boolean',
            'show_product_brand' => 'nullable|boolean',
            'show_product_category' => 'nullable|boolean',
            'show_product_manufacturer' => 'nullable|boolean',
            'show_product_image' => 'nullable|boolean',
            'show_expire_date' => 'nullable|boolean',
            'show_alert_qty' => 'nullable|boolean',
            'show_vat_id' => 'nullable|boolean',
            'show_vat_type' => 'nullable|boolean',
            'show_exclusive_price' => 'nullable|boolean',
            'show_inclusive_price' => 'nullable|boolean',
            'show_profit_percent' => 'nullable|boolean',
            'show_capacity' => 'nullable|boolean',
            'show_weight' => 'nullable|boolean',
            'show_color' => 'nullable|boolean',
            'show_size' => 'nullable|boolean',
            'show_type' => 'nullable|boolean',
            'show_batch_no' => 'nullable|boolean',
            'show_mfg_date' => 'nullable|boolean',
            'show_model_no' => 'nullable|boolean',
            'show_product_batch_no' => 'nullable|boolean',
            'show_product_expire_date' => 'nullable|boolean',
            'default_batch_no' => 'nullable|string|max:255',
            'default_expired_date' => 'nullable|date',
            'default_mfg_date' => 'nullable|date',
            'default_sale_price' => 'nullable|numeric|min:0',
            'default_wholesale_price' => 'nullable|numeric|min:0',
            'default_dealer_price' => 'nullable|numeric|min:0',
            'expire_date_type' => 'nullable|in:dmy,my',
            'mfg_date_type' => 'nullable|in:dmy,my',
            'show_product_type_single' => 'nullable|boolean',
            'show_product_type_variant' => 'nullable|boolean',
            'show_warehouse' => 'nullable|boolean',
            'show_action' => 'nullable|boolean',
            'show_rack' => 'nullable|boolean',
            'show_shelf' => 'nullable|boolean',
        ]);

        if (
            !$request->boolean('show_product_type_single') &&
            !$request->boolean('show_product_type_variant')
        ) {
            throw ValidationException::withMessages([
                'product_type' => ['At least one product type must be selected: Single or Variant.'],
            ]);
        }

        $modules = $request->except([
            '_token',
            '_method',
            'default_expired_date_dmy',
            'default_expired_date_my',
            'default_mfg_date_dmy',
            'default_mfg_date_my',
        ]);

        // Set default_expired_date based on date type
        $modules['default_expired_date'] = $request->expire_date_type === 'dmy'
            ? $request->default_expired_date_dmy
            : ($request->expire_date_type === 'my'
                ? $request->default_expired_date_my
                : null);

        // Set default_mfg_date based on date type
        $modules['default_mfg_date'] = $request->mfg_date_type === 'dmy'
            ? $request->default_mfg_date_dmy
            : ($request->mfg_date_type === 'my'
                ? $request->default_mfg_date_my
                : null);

        $businessId = auth()->user()->business_id;

        ProductSetting::updateOrCreate(
            ['business_id' => $businessId],
            ['modules' => $modules]
        );

        Cache::forget('product_setting_' . $businessId);

        return response()->json(__('Product setting updated successfully.'));
    }
}
