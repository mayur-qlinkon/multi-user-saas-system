<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeopleSeeder extends Seeder
{
    public function run()
    {
        $companyId = 7;
        $storeId = 1;
        $stateId = 12; // Gujarat example

        /*
        |--------------------------------------------------------------------------
        | SUPPLIERS
        |--------------------------------------------------------------------------
        */

        $suppliers = [

            [
                'name' => 'Green Grow Nursery',
                'email' => 'greengrow@example.com',
                'phone' => '9876543210',
                'city' => 'Ahmedabad',
                'gstin' => '24ABCDE1234F1Z5',
                'pan' => 'ABCDE1234F',
                'bank_name' => 'HDFC Bank',
                'account_number' => '50200011223344',
                'ifsc_code' => 'HDFC0001234'
            ],

            [
                'name' => 'Nature Plant Suppliers',
                'email' => 'natureplants@example.com',
                'phone' => '9825012345',
                'city' => 'Surat',
                'gstin' => '24AAACN5678K1Z2',
                'pan' => 'AAACN5678K',
                'bank_name' => 'ICICI Bank',
                'account_number' => '123456789012',
                'ifsc_code' => 'ICIC0001234'
            ],

            [
                'name' => 'Urban Garden Traders',
                'email' => 'urbangarden@example.com',
                'phone' => '9811122233',
                'city' => 'Vadodara',
                'gstin' => '24AAABU9876L1Z1',
                'pan' => 'AAABU9876L',
                'bank_name' => 'Axis Bank',
                'account_number' => '912345678901',
                'ifsc_code' => 'UTIB0001234'
            ],

        ];

        foreach ($suppliers as $supplier) {

            DB::table('suppliers')->insert([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'name' => $supplier['name'],
                'email' => $supplier['email'],
                'phone' => $supplier['phone'],
                'address' => 'Industrial Area',
                'city' => $supplier['city'],
                'pincode' => '380001',
                'state_id' => $stateId,

                'gstin' => $supplier['gstin'],
                'pan' => $supplier['pan'],
                'registration_type' => 'regular',

                'bank_name' => $supplier['bank_name'],
                'account_number' => $supplier['account_number'],
                'ifsc_code' => $supplier['ifsc_code'],
                'branch' => 'Main Branch',

                'opening_balance' => 0,
                'balance_type' => 'payable',
                'current_balance' => 0,

                'credit_days' => 30,
                'credit_limit' => 100000,

                'is_active' => true,
                'notes' => 'Plant and gardening supplier',

                'created_at' => now(),
                'updated_at' => now()
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | CLIENTS
        |--------------------------------------------------------------------------
        */

        $clients = [

            [
                'name' => 'Amit Patel',
                'phone' => '9871112233',
                'email' => 'amit@example.com',
                'city' => 'Ahmedabad'
            ],

            [
                'name' => 'Riya Shah',
                'phone' => '9825009988',
                'email' => 'riya@example.com',
                'city' => 'Surat'
            ],

            [
                'name' => 'Green Valley Resort',
                'phone' => '9898989898',
                'email' => 'resort@example.com',
                'company_name' => 'Green Valley Resort',
                'city' => 'Udaipur'
            ],

            [
                'name' => 'Urban Landscape Pvt Ltd',
                'phone' => '9810001122',
                'email' => 'urbanland@example.com',
                'company_name' => 'Urban Landscape Pvt Ltd',
                'city' => 'Mumbai',
                'gst_number' => '27ABCDE1234F1Z5'
            ],

            [
                'name' => 'Priya Mehta',
                'phone' => '9876501234',
                'email' => 'priya@example.com',
                'city' => 'Vadodara'
            ],

        ];

        foreach ($clients as $client) {

            DB::table('clients')->insert([
                'company_id' => $companyId,
                'store_id' => $storeId,

                'name' => $client['name'],
                'client_code' => 'CL' . rand(1000,9999),
                'company_name' => $client['company_name'] ?? null,

                'email' => $client['email'],
                'phone' => $client['phone'],
                'gst_number' => $client['gst_number'] ?? null,
                'registration_type' => 'unregistered',

                'address' => 'Residential Area',
                'city' => $client['city'],
                'state_id' => $stateId,
                'zip_code' => '380001',
                'country' => 'India',

                'notes' => 'Customer for plant purchases',

                'is_active' => true,

                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}