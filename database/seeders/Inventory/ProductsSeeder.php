<?php

namespace Database\Seeders\Inventory;

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

            /*
            |--------------------------------------------------------------------------
            | ASSUMPTIONS (IMPORTANT)
            |--------------------------------------------------------------------------
            | - units table has at least ID = 1 (e.g., "Nos")
            | - categories exist (or nullable)
            | - suppliers exist (or nullable)
            | - attributes + values exist for variant mapping
            */

            $unitId = 1;

            /*
            |--------------------------------------------------------------------------
            | PRODUCT 1 → VARIABLE PRODUCT (2 SKUs)
            |--------------------------------------------------------------------------
            */

            $product1Id = DB::table('products')->insertGetId([
                'company_id' => $companyId,
                'category_id' => null,
                'supplier_id' => null,
                'name' => 'Cotton T-Shirt',
                'slug' => 'cotton-tshirt-' . $companyId,
                'type' => 'variable',
                'barcode_symbology' => 'CODE128',
                'hsn_code' => '6109',
                'product_unit_id' => $unitId,
                'sale_unit_id' => $unitId,
                'purchase_unit_id' => $unitId,
                'description' => 'Premium cotton t-shirt',
                'is_active' => true,
                'show_in_storefront' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // SKU 1 (Size M)
            $sku1Id = DB::table('product_skus')->insertGetId([
                'company_id' => $companyId,
                'product_id' => $product1Id,
                'unit_id' => $unitId,
                'sku' => 'TSHIRT-M-' . $companyId,
                'barcode' => '1111111111',
                'cost' => 300,
                'price' => 500,
                'mrp' => 599,
                'gst_rate' => 5,
                'stock_alert' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // SKU 2 (Size L)
            $sku2Id = DB::table('product_skus')->insertGetId([
                'company_id' => $companyId,
                'product_id' => $product1Id,
                'unit_id' => $unitId,
                'sku' => 'TSHIRT-L-' . $companyId,
                'barcode' => '2222222222',
                'cost' => 320,
                'price' => 550,
                'mrp' => 649,
                'gst_rate' => 5,
                'stock_alert' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | OPTIONAL: ATTRIBUTE MAPPING (ONLY IF EXISTS)
            |--------------------------------------------------------------------------
            | Example assumes:
            | attribute_id = 1 (Size)
            | values: M=1, L=2
            */

            DB::table('product_sku_values')->insert([
                [
                    'product_sku_id' => $sku1Id,
                    'attribute_id' => 1,
                    'attribute_value_id' => 1, // M
                ],
                [
                    'product_sku_id' => $sku2Id,
                    'attribute_id' => 1,
                    'attribute_value_id' => 2, // L
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | PRODUCT 2 → SINGLE PRODUCT (1 SKU)
            |--------------------------------------------------------------------------
            */

            $product2Id = DB::table('products')->insertGetId([
                'company_id' => $companyId,
                'category_id' => null,
                'supplier_id' => null,
                'name' => 'Wireless Mouse',
                'slug' => 'wireless-mouse-' . $companyId,
                'type' => 'single',
                'barcode_symbology' => 'CODE128',
                'hsn_code' => '8471',
                'product_unit_id' => $unitId,
                'sale_unit_id' => $unitId,
                'purchase_unit_id' => $unitId,
                'description' => '2.4GHz wireless optical mouse',
                'is_active' => true,
                'show_in_storefront' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('product_skus')->insert([
                'company_id' => $companyId,
                'product_id' => $product2Id,
                'unit_id' => $unitId,
                'sku' => 'MOUSE-001-' . $companyId,
                'barcode' => '3333333333',
                'cost' => 200,
                'price' => 350,
                'mrp' => 399,
                'gst_rate' => 18,
                'stock_alert' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            if (isset($this->command)) {
                $this->command->info("✅ Products & SKUs seeded successfully for Company ID: {$companyId}");
            }

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($this->command)) {
                $this->command->error("❌ Failed to seed products: " . $e->getMessage());
            }
        }
    }
}