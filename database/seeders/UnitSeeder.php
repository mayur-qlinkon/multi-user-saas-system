<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run()
    {
        $companyId = 1;

        $units = [

            ['name' => 'Piece', 'short_name' => 'pcs'],
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Gram', 'short_name' => 'g'],
            ['name' => 'Litre', 'short_name' => 'ltr'],
            ['name' => 'Millilitre', 'short_name' => 'ml'],
            ['name' => 'Meter', 'short_name' => 'm'],
            ['name' => 'Centimeter', 'short_name' => 'cm'],
            ['name' => 'Inch', 'short_name' => 'in'],
            ['name' => 'Pack', 'short_name' => 'pack'],
            ['name' => 'Bag', 'short_name' => 'bag'],
            ['name' => 'Tray', 'short_name' => 'tray'],
            ['name' => 'Pot', 'short_name' => 'pot'],

        ];

        foreach ($units as $unit) {

            $exists = DB::table('units')
                ->where('company_id', $companyId)
                ->where('name', $unit['name'])
                ->exists();

            if (!$exists) {

                DB::table('units')->insert([
                    'company_id' => $companyId,
                    'name' => $unit['name'],
                    'short_name' => $unit['short_name'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

            }
        }
    }
}