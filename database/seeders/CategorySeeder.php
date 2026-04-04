<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $companyId = 1;

        $categories = [

            'Indoor Plants',
            'Outdoor Plants',
            'Flowering Plants',
            'Fruit Plants',
            'Succulents & Cactus',
            'Bonsai Plants',
            'Medicinal Plants',
            'Seeds',
            'Pots & Planters',
            'Soil & Potting Mix',
            'Fertilizers',
            'Garden Tools',
            'Garden Decor',

        ];

        foreach ($categories as $category) {

            DB::table('categories')->insert([

                'company_id' => $companyId,
                'name' => $category,
                'slug' => Str::slug($category),
                'default_gst_rate' => 5,
                'parent_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()

            ]);

        }
    }
}