<?php

namespace Database\Seeders\Inventory;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = request()->attributes->get('seeder_company_id', 1);

        $categories = [
            ['name' => 'Clothing', 'gst' => 5],
            ['name' => 'Electronics', 'gst' => 18],
            ['name' => 'Groceries', 'gst' => 0],
        ];

        DB::beginTransaction();

        try {
            foreach ($categories as $cat) {
                DB::table('categories')->updateOrInsert(
                    [
                        'company_id' => $companyId,
                        'slug' => Str::slug($cat['name']),
                    ],
                    [
                        'company_id' => $companyId,
                        'name' => $cat['name'],
                        'slug' => Str::slug($cat['name']),
                        'default_gst_rate' => $cat['gst'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}