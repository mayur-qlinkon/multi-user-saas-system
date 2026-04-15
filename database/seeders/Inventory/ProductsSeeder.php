<?php

namespace Database\Seeders\Inventory;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = request()->attributes->get('seeder_company_id', 1);

        DB::beginTransaction();

        try {
            // 1. Ensure Company Exists
            $company = Company::firstOrCreate(
                ['id' => $companyId],
                ['name' => 'Test Company ' . $companyId, 'email' => 'company'.$companyId.'@test.com']
            );

            // 2. Fetch or Create Dependencies (The Fallback Mechanism)
            $unit = Unit::firstOrCreate(
                ['name' => 'Pieces'], 
                ['short_name' => 'pcs', 'is_active' => true]
            );

            $clothingCategory = Category::firstOrCreate(
                ['name' => 'Clothing', 'company_id' => $company->id],
                ['slug' => 'clothing-'.$company->id, 'is_active' => true]
            );

            $electronicsCategory = Category::firstOrCreate(
                ['name' => 'Electronics', 'company_id' => $company->id],
                ['slug' => 'electronics-'.$company->id, 'is_active' => true]
            );

            $supplier = Supplier::firstOrCreate(
                ['name' => 'Global Tech Supplies', 'company_id' => $company->id],
                ['email' => 'contact@globaltech.com', 'is_active' => true]
            );

            /*
            |--------------------------------------------------------------------------
            | PRODUCT 1 → VARIABLE PRODUCT (Using Factories)
            |--------------------------------------------------------------------------
            */
            // Ensure Attribute and Values exist for "Size"
            $sizeAttribute = Attribute::firstOrCreate(['name' => 'Size', 'company_id' => $company->id]);
            $sizeM = AttributeValue::firstOrCreate(['attribute_id' => $sizeAttribute->id, 'value' => 'M']);
            $sizeL = AttributeValue::firstOrCreate(['attribute_id' => $sizeAttribute->id, 'value' => 'L']);

            $variableProduct = Product::factory()
                ->variable()
                ->for($company)
                ->create([
                    'category_id' => $clothingCategory->id,
                    'supplier_id' => $supplier->id,
                    'name' => 'Premium Cotton T-Shirt',
                    'slug' => 'premium-cotton-tshirt-' . $company->id,
                    'product_unit_id' => $unit->id,
                    'sale_unit_id' => $unit->id,
                    'purchase_unit_id' => $unit->id,
                ]);

            // Create SKU for Size M
            $skuM = ProductSku::factory()->for($company)->for($variableProduct, 'product')->create([
                'unit_id' => $unit->id,
                'sku' => 'TSHIRT-M-' . $company->id,
                'barcode' => '1111111111' . $company->id,
            ]);
            
            // Create SKU for Size L
            $skuL = ProductSku::factory()->for($company)->for($variableProduct, 'product')->create([
                'unit_id' => $unit->id,
                'sku' => 'TSHIRT-L-' . $company->id,
                'barcode' => '2222222222' . $company->id,
            ]);

            // Map Attributes to SKUs
            DB::table('product_sku_values')->insert([
                ['product_sku_id' => $skuM->id, 'attribute_id' => $sizeAttribute->id, 'attribute_value_id' => $sizeM->id],
                ['product_sku_id' => $skuL->id, 'attribute_id' => $sizeAttribute->id, 'attribute_value_id' => $sizeL->id],
            ]);

            /*
            |--------------------------------------------------------------------------
            | PRODUCT 2 → SINGLE PRODUCT (Using Factories)
            |--------------------------------------------------------------------------
            */
            $singleProduct = Product::factory()
                ->for($company)
                ->create([
                    'category_id' => $electronicsCategory->id,
                    'supplier_id' => $supplier->id,
                    'name' => 'Wireless Mouse Pro',
                    'slug' => 'wireless-mouse-pro-' . $company->id,
                    'type' => 'single',
                    'product_unit_id' => $unit->id,
                    'sale_unit_id' => $unit->id,
                    'purchase_unit_id' => $unit->id,
                ]);

            ProductSku::factory()->for($company)->for($singleProduct, 'product')->create([
                'unit_id' => $unit->id,
                'sku' => 'MOUSE-PRO-' . $company->id,
                'barcode' => '3333333333' . $company->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | BULK FAKE DATA (Optional: Generates 10 random single products)
            |--------------------------------------------------------------------------
            */
            Product::factory()
                ->count(10)
                ->for($company)
                ->create([
                    'category_id' => $electronicsCategory->id,
                    'product_unit_id' => $unit->id,
                    'sale_unit_id' => $unit->id,
                    'purchase_unit_id' => $unit->id,
                ])->each(function ($product) use ($company, $unit) {
                    ProductSku::factory()->for($company)->for($product, 'product')->create([
                        'unit_id' => $unit->id,
                    ]);
                });

            DB::commit();

            if (isset($this->command)) {
                $this->command->info("✅ Robust Products seeded successfully for Company ID: {$companyId}");
            }

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($this->command)) {
                $this->command->error('❌ Failed to seed products: ' . $e->getMessage());
            }
        }
    }
}