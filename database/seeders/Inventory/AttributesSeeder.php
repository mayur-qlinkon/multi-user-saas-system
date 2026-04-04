<?php

namespace Database\Seeders\Inventory;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributesSeeder extends Seeder
{
    public function run()
    {
        $companyId = 1;

        $attributes = [

            [
                'name' => 'Color',
                'type' => 'color',
                'values' => [
                    ['value' => 'Red', 'color_code' => '#FF0000'],
                    ['value' => 'Blue', 'color_code' => '#0000FF'],
                    ['value' => 'Green', 'color_code' => '#008000'],
                    ['value' => 'Black', 'color_code' => '#000000'],
                    ['value' => 'White', 'color_code' => '#FFFFFF'],
                    ['value' => 'Yellow', 'color_code' => '#FFFF00'],
                ]
            ],

            [
                'name' => 'Size',
                'type' => 'button',
                'values' => [
                    ['value' => 'XS'],
                    ['value' => 'S'],
                    ['value' => 'M'],
                    ['value' => 'L'],
                    ['value' => 'XL'],
                ]
            ],

            [
                'name' => 'Material',
                'type' => 'text',
                'values' => [
                    ['value' => 'Plastic'],
                    ['value' => 'Ceramic'],
                    ['value' => 'Steel'],
                    ['value' => 'Wood'],
                ]
            ],

            [
                'name' => 'Pot Size',
                'type' => 'button',
                'values' => [
                    ['value' => '4 inch'],
                    ['value' => '6 inch'],
                    ['value' => '8 inch'],
                    ['value' => '10 inch'],
                    ['value' => '12 inch'],
                ]
            ],

        ];

        foreach ($attributes as $attr) {

            // Create attribute only if not exists
            $attribute = DB::table('attributes')
                ->where('company_id', $companyId)
                ->where('name', $attr['name'])
                ->first();

            if (!$attribute) {

                $attributeId = DB::table('attributes')->insertGetId([
                    'company_id' => $companyId,
                    'name' => $attr['name'],
                    'type' => $attr['type'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

            } else {

                $attributeId = $attribute->id;

            }

            // Insert attribute values
            foreach ($attr['values'] as $position => $value) {

                $exists = DB::table('attribute_values')
                    ->where('attribute_id', $attributeId)
                    ->where('value', $value['value'])
                    ->exists();

                if (!$exists) {

                    DB::table('attribute_values')->insert([
                        'company_id' => $companyId,
                        'attribute_id' => $attributeId,
                        'value' => $value['value'],
                        'color_code' => $value['color_code'] ?? null,
                        'position' => $position,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                }
            }
        }
    }
}