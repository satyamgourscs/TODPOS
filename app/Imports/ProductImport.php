<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Vat;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ProductImport implements ToCollection
{
    protected $businessId;
    protected $categories = [];
    protected $brands = [];
    protected $units = [];
    protected $vats = [];
    protected $models = [];
    protected $productsToInsert = [];
    protected $existingProductCodes = [];
    protected $excelProductCodes = [];

    public function __construct($businessId)
    {
        $this->businessId = $businessId;

        $this->existingProductCodes = Product::where('business_id', $businessId)
            ->pluck('productCode')
            ->toArray();
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip the header

                // Read Excel fields
                $productName = trim($row[0]);
                $categoryName = trim($row[1]);
                $unitName = trim($row[2]);
                $brandName = trim($row[3]);
                $stockQty = $row[4] ?? 0;
                $productCode = trim($row[5]);
                $purchasePrice = (float)($row[6] ?? 0);
                $salePrice = (float)($row[7] ?? 0);
                $dealerPrice = (float)($row[8] ?? $salePrice);
                $wholesalePrice = (float)($row[9] ?? $salePrice);
                $vatName = trim($row[10]);
                $vatPercent = (float)($row[11] ?? 0);
                $vatType = $row[12] ?? 'exclusive';
                $alertQty = (int)($row[13] ?? 0);
                $expireDate = $this->parseExcelDate($row[14]);
                $batchNo = $row[15] ?? null;
                $model = trim($row[16]);

                $manufacturingDate = $row[17] ?? null;

                if (!$productName || !$productCode || !$categoryName) {
                    continue;
                }

                // Skip duplicates
                if (in_array($productCode, $this->existingProductCodes)) continue;
                if (in_array($productCode, $this->excelProductCodes)) continue;

                // VAT & profit calculation
                $vatAmount = ($purchasePrice * $vatPercent) / 100;
                $grandPurchasePrice = $vatType === 'inclusive'
                    ? $purchasePrice + $vatAmount
                    : $purchasePrice;

                $profitPercent = $purchasePrice > 0
                    ? round((($salePrice - $purchasePrice) / $purchasePrice) * 100, 3)
                    : 0.0;

                $this->excelProductCodes[] = $productCode;

                // Create or get related IDs
                $categoryId = $this->categories[$categoryName] ??= Category::firstOrCreate(
                    ['categoryName' => $categoryName, 'business_id' => $this->businessId],
                    ['categoryName' => $categoryName]
                )->id;

                $brandId = $this->brands[$brandName] ??= Brand::firstOrCreate(
                    ['brandName' => $brandName, 'business_id' => $this->businessId],
                    ['brandName' => $brandName]
                )->id;

                $unitId = $this->units[$unitName] ??= Unit::firstOrCreate(
                    ['unitName' => $unitName, 'business_id' => $this->businessId],
                    ['unitName' => $unitName]
                )->id;

                $vatId = $this->vats[$vatName] ??= Vat::firstOrCreate(
                    ['name' => $vatName, 'business_id' => $this->businessId],
                    ['name' => $vatName, 'rate' => $vatPercent]
                )->id;

                $modelId = $this->models[$model] ??= ProductModel::firstOrCreate(
                    ['name' => $model, 'business_id' => $this->businessId],
                    ['name' => $model]
                )->id;

                // Create product
                $product = Product::create([
                    'productName' => $productName,
                    'business_id' => $this->businessId,
                    'unit_id' => $unitId,
                    'brand_id' => $brandId,
                    'category_id' => $categoryId,
                    'model_id' => $modelId,
                    'productCode' => $productCode,
                    'vat_id' => $vatId,
                    'vat_type' => $vatType,
                    'vat_amount' => $vatAmount,
                    'alert_qty' => $alertQty,
                    'expire_date' => $expireDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Stock::updateOrCreate(
                    [
                        'batch_no'    => $batchNo,
                        'business_id' => $this->businessId,
                        'product_id'  => $product->id,
                    ],
                    [
                        'expire_date' => $expireDate,
                        'productStock' => $stockQty,
                        'productPurchasePrice' => $grandPurchasePrice,
                        'profit_percent' => $profitPercent,
                        'productSalePrice' => $salePrice,
                        'productWholeSalePrice' => $wholesalePrice,
                        'productDealerPrice' => $dealerPrice,
                        'mfg_date' => $manufacturingDate,
                    ]
                );
            }
        });
    }

    function parseExcelDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // If it is numeric (Excel timestamp)
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Remove extra spaces
        $value = trim($value);

        // Try MM/DD/YYYY first
        try {
            return Carbon::createFromFormat('m/d/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            // Try DD/MM/YYYY
            try {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            } catch (\Exception $e2) {
                // Try default parse (YYYY-MM-DD etc.)
                try {
                    return Carbon::parse($value)->format('Y-m-d');
                } catch (\Exception $e3) {
                    return null;
                }
            }
        }
    }
}
