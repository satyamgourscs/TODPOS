<?php

namespace Modules\Business\App\Http\Controllers;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    use HasUploader;

    public function index()
    {
        $setting = Option::where('key', 'business-settings')
            ->whereJsonContains('value->business_id', auth()->user()->business_id)
            ->first();

        $business_categories = BusinessCategory::whereStatus(1)->latest()->get();
        $business = Business::findOrFail(auth()->user()->business_id);

        return view('business::settings.general', compact('setting', 'business_categories', 'business'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'address' => 'nullable|max:250',
            'email' => 'nullable|email|max:255',
            'companyName' => 'required|max:250',
            'business_category_id' => 'required|exists:business_categories,id',
            'phoneNumber' => 'nullable|min:5|max:15',
            'vat_name' => 'nullable|max:250',
            'vat_no' => 'nullable|max:250|required_with:vat_name',
            'invoice_logo' => 'nullable|image',
            'invoice_scanner_logo' => 'nullable|image',
            'sale_rounding_option' => 'nullable|in:none,round_up,nearest_whole_number,nearest_0.05,nearest_0.1,nearest_0.5',
            'product_profit_option' => 'nullable|in:markup,margin',
            'note' => 'nullable|string|max:250',
            'note_label' => 'nullable|string|max:250',
            'gratitude_message' => 'nullable|string|max:250'
        ]);

        DB::beginTransaction();

        try {
            $business = Business::findOrFail(auth()->user()->business_id);

            $business->update([
                'address' => $request->address,
                'companyName' => $request->companyName,
                'business_category_id' => $request->business_category_id,
                'phoneNumber' => $request->phoneNumber,
                'email' => $request->email,
                'vat_name' => $request->vat_name,
                'vat_no' => $request->vat_no,
            ]);

            $data = $request->except('_token', '_method', 'logo', 'favicon', 'invoice_logo', 'invoice_scanner_logo', 'address', 'companyName', 'business_category_id', 'phoneNumber');

            $setting = Option::find($id);

            if ($setting) {
                $setting->update($request->except($data) + [
                    'value' => $request->except('_token', '_method', 'invoice_logo', 'invoice_scanner_logo', 'address', 'companyName', 'business_category_id', 'phoneNumber', 'email') + [
                        'business_id' => $business->id,
                        'invoice_logo' => $request->invoice_logo ? $this->upload($request, 'invoice_logo', $setting->value['invoice_logo'] ?? null) : ($setting->value['invoice_logo'] ?? null),
                        'invoice_scanner_logo' => $request->invoice_scanner_logo ? $this->upload($request, 'invoice_scanner_logo', $setting->value['invoice_scanner_logo'] ?? null) : ($setting->value['invoice_scanner_logo'] ?? null),
                        'sale_rounding_option' => $request->sale_rounding_option ?? 'none',
                        'product_profit_option' => $request->product_profit_option,
                        'note' => $request->note,
                        'note_label' => $request->note_label,
                        'gratitude_message' => $request->gratitude_message,
                        'vat_name' => $request->vat_name,
                        'vat_no' => $request->vat_no,
                    ],
                ]);
            } else {
                Option::insert([
                    'key' => 'business-settings',
                    'value' => json_encode([
                        'business_id' => $business->id,
                        'invoice_logo' => $request->invoice_logo ? $this->upload($request, 'invoice_logo') : null,
                        'invoice_scanner_logo' => $request->invoice_scanner_logo ? $this->upload($request, 'invoice_scanner_logo') : null,
                        'sale_rounding_option' => $request->sale_rounding_option ?? 'none',
                        'product_profit_option' => $request->product_profit_option,
                        'note' => $request->note,
                        'note_label' => $request->note_label,
                        'gratitude_message' => $request->gratitude_message,
                        'vat_name' => $request->vat_name,
                        'vat_no' => $request->vat_no,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Cache::forget("business_setting_{$business->id}");
            Cache::forget("business_sale_rounding_{$business->id}");

            DB::commit();

            return response()->json([
                'message' => __('Business General Setting updated successfully'),
                'redirect' => route('business.settings.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(__('Something was wrong.'), 400);
        }
    }
}
