<?php

namespace Modules\Business\App\Http\Controllers;

use AgeekDev\Barcode\Facades\Barcode;
use AgeekDev\Barcode\Enums\Type;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;

class BarcodeGeneratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:barcodes.read')->only(['index']);
        $this->middleware('check.permission:barcodes.create')->only(['store']);
    }

    public function index()
    {
        $barcode_types = array_map(
            fn($case) => ['value' => $case->value],
            Type::cases()
        );

        $products = Product::where('business_id', auth()->user()->business_id)->latest()->get();
        return view('business::barcode-generators.index', compact('products', 'barcode_types'));
    }

    public function fetchProducts(Request $request)
    {
        $products = Product::with('stocks')->where('business_id', auth()->user()->business_id)
            ->when(!empty($request->search), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('productName', 'like', '%' . $request->search . '%')
                        ->orWhere('productCode', 'like', '%' . $request->search . '%');
                });
            })
            ->withSum('stocks', 'productStock')
            ->limit(5)
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode_setting' => 'required|in:1,2,3',
        ]);

        $barcodeType = $request->input('barcode_type', Type::TYPE_CODE_128->value);
        $stockIds = $request->input('stock_ids', []);
        $quantities = $request->input('qty', []);
        $previewDates = $request->input('preview_date', []);
        $selectedVatType = $request->vat_type;

        if (empty($stockIds)) {
            return response()->json(['message' => __('Please select at least one product.')], 400);
        }

        $stocks = Stock::with('product.vat')->whereIn('id', $stockIds)->get();

        $generatedBarcodes = [];

        foreach ($stockIds as $index => $stockId) {
            $stock = $stocks->firstWhere('id', $stockId);

            if (!$stock || !$stock->product) {
                continue; // Skip if stock or product not found
            }

            $product = $stock->product;
            $qty = $quantities[$index] ?? 1;
            $previewDate = $previewDates[$index] ?? null;

            // VAT Logic
            $basePrice = $stock->productSalePrice ?? 0;

            $currentVatType = $product->vat_type;

            if ($request->product_price && $selectedVatType !== $currentVatType) {
                $vatRate = optional($product->vat)->rate ?? 0;

                if ($selectedVatType === 'inclusive') {
                    // Convert from exclusive to inclusive
                    $basePrice = $basePrice + (($vatRate / 100) * $basePrice);
                } elseif ($selectedVatType === 'exclusive') {
                    // Convert from inclusive to exclusive
                    $basePrice = $basePrice / (1 + ($vatRate / 100));
                }
            }
            $barcodeSvg = Barcode::imageType("png")
                ->type(Type::from($barcodeType))
                ->generate($product->productCode);

            for ($copyIndex = 0; $copyIndex < $qty; $copyIndex++) {
                $generatedBarcodes[] = [
                    'barcode_svg' => $barcodeSvg,
                    'packing_date' => $previewDate,
                    'product_name' => $product->productName,
                    'business_name' => $product->business->companyName ?? '',
                    'product_code' => $product->productCode,
                    'product_price' => $basePrice,
                    'product_stock' => $stock->productStock ?? 0,
                    'show_product_name' => $request->product_name,
                    'product_name_size' => $request->product_name_size,
                    'show_business_name' => $request->business_name,
                    'business_name_size' => $request->business_name_size,
                    'show_product_price' => $request->product_price,
                    'product_price_size' => $request->product_price_size,
                    'show_product_code' => $request->product_code,
                    'product_code_size' => $request->product_code_size,
                    'show_pack_date' => $request->pack_date,
                    'pack_date_size' => $request->pack_date_size,
                ];
            }
        }

        session(['generatedBarcodes' => $generatedBarcodes]);
        session()->put('printer', $request->barcode_setting);

        return response()->json([
            'redirect' => route('business.barcodes.index'),
            'secondary_redirect_url' => route('business.barcodes.preview'),
        ]);
    }

    public function preview()
    {
        $printer = session('printer');
        $generatedBarcodes = session('generatedBarcodes');

        session()->forget('printer');
        session()->forget('generatedBarcodes');

        return view('business::barcode-generators.print', compact('generatedBarcodes', 'printer'));
    }
}
