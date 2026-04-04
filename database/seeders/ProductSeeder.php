<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $companyId = 1;
        $storeId = 1;
        $unitId = 1;
        $stateId = 12;

        /*
        |--------------------------------------------------------------------------
        | Warehouses
        |--------------------------------------------------------------------------
        */

        $warehouses = [

            [
                'name' => 'Main Nursery Warehouse',
                'code' => 'WH-001',
                'city' => 'Ahmedabad'
            ],

            [
                'name' => 'Plant Storage Godown',
                'code' => 'WH-002',
                'city' => 'Surat'
            ],

            [
                'name' => 'Retail Store Warehouse',
                'code' => 'WH-003',
                'city' => 'Vadodara'
            ]

        ];

        foreach ($warehouses as $index => $warehouse) {

            DB::table('warehouses')->insert([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'name' => $warehouse['name'],
                'code' => $warehouse['code'],
                'contact_person' => 'Manager',
                'phone' => '9876543210',
                'email' => 'warehouse@example.com',
                'address' => 'Industrial Area',
                'city' => $warehouse['city'],
                'state_id' => $stateId,
                'zip_code' => '380001',
                'country' => 'India',
                'is_default' => $index == 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------------------------------
        */

        $products = [

            [
                'name' => 'Money Plant',
                'category_id' => 1,
                'supplier_id' => 1,
                'cost' => 40,
                'price' => 99
            ],

            [
                'name' => 'Snake Plant',
                'category_id' => 1,
                'supplier_id' => 1,
                'cost' => 60,
                'price' => 149
            ],

            [
                'name' => 'Aloe Vera Plant',
                'category_id' => 7,
                'supplier_id' => 2,
                'cost' => 35,
                'price' => 89
            ],

            [
                'name' => 'Rose Plant',
                'category_id' => 3,
                'supplier_id' => 2,
                'cost' => 70,
                'price' => 199
            ],

            [
                'name' => 'Ceramic Plant Pot',
                'category_id' => 9,
                'supplier_id' => 3,
                'cost' => 120,
                'price' => 299
            ],

        ];

        foreach ($products as $product) {

            $productId = DB::table('products')->insertGetId([

                'company_id' => $companyId,
                'category_id' => $product['category_id'],
                'supplier_id' => $product['supplier_id'],

                'name' => $product['name'],
                'slug' => Str::slug($product['name']),
                'type' => 'single',

                'barcode_symbology' => 'CODE128',
                'hsn_code' => '060290',

                'product_unit_id' => $unitId,
                'sale_unit_id' => $unitId,
                'purchase_unit_id' => $unitId,

                'quantity_limitation' => null,

                'description' => 'Healthy nursery plant suitable for indoor and outdoor gardening.',
                'specifications' => json_encode([
                    'Sunlight' => 'Partial Sun',
                    'Watering' => 'Moderate',
                    'Maintenance' => 'Low'
                ]),

                'product_guide' => json_encode([
                    'step1' => 'Place plant in bright location',
                    'step2' => 'Water twice a week',
                    'step3' => 'Use organic fertilizer monthly'
                ]),

                'is_active' => true,

                'created_at' => now(),
                'updated_at' => now()

            ]);


            /*
            |--------------------------------------------------------------------------
            | SKU
            |--------------------------------------------------------------------------
            */

            DB::table('product_skus')->insert([

                'company_id' => $companyId,
                'product_id' => $productId,
                'unit_id' => $unitId,

                'sku' => 'SKU-' . strtoupper(Str::random(6)),
                'barcode' => rand(100000000000,999999999999),

                'cost' => $product['cost'],
                'price' => $product['price'],

                'order_tax' => 5,
                'tax_type' => 'exclusive',

                'stock_alert' => 5,

                'is_active' => true,

                'created_at' => now(),
                'updated_at' => now()

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | VARIABLE PRODUCT EXAMPLE (SIZE VARIANTS)
        |--------------------------------------------------------------------------
        */

        $productId = DB::table('products')->insertGetId([

            'company_id' => $companyId,
            'category_id' => 1,
            'supplier_id' => 1,

            'name' => 'Areca Palm Plant',
            'slug' => 'areca-palm-plant',

            'type' => 'variable',

            'barcode_symbology' => 'CODE128',
            'hsn_code' => '060290',

            'product_unit_id' => $unitId,
            'sale_unit_id' => $unitId,
            'purchase_unit_id' => $unitId,

            'description' => 'Popular indoor air purifying plant.',

            'is_active' => true,

            'created_at' => now(),
            'updated_at' => now()

        ]);


        $sizes = [

            ['value_id' => 1, 'price' => 199],
            ['value_id' => 2, 'price' => 299],
            ['value_id' => 3, 'price' => 399]

        ];

        foreach ($sizes as $size) {

            $skuId = DB::table('product_skus')->insertGetId([

                'company_id' => $companyId,
                'product_id' => $productId,
                'unit_id' => $unitId,

                'sku' => 'SKU-' . strtoupper(Str::random(6)),
                'barcode' => rand(100000000000,999999999999),

                'cost' => 120,
                'price' => $size['price'],

                'order_tax' => 5,
                'tax_type' => 'exclusive',

                'stock_alert' => 5,

                'is_active' => true,

                'created_at' => now(),
                'updated_at' => now()

            ]);


            DB::table('product_sku_values')->insert([

                'product_sku_id' => $skuId,
                'attribute_id' => 2, // Size
                'attribute_value_id' => $size['value_id']

            ]);

        }

    }
}