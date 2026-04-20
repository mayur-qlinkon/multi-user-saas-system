<?php

namespace Database\Seeders\Platform;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        // 🌟 THE HIGH-LEVEL BILLABLE MODULES
        // We do not include "Settings" or "Users" here, as those are Core ERP features everyone gets.
        $modules = [
            [
                'name' => 'Inventory & Catalog',
                'slug' => 'inventory', // Includes: Products, Categories, Attributes, Units, Labels, Warehouses
                'is_active' => 1,
            ],
            [
                'name' => 'Point of Sale (POS)',
                'slug' => 'pos', // Includes: POS Interface, Transport Receipts
                'is_active' => 1,
            ],
            [
                'name' => 'Invoicing & Billing',
                'slug' => 'invoicing', // Includes: Quotations, Invoices, Invoice Returns
                'is_active' => 1,
            ],
            [
                'name' => 'Purchasing & Supply Chain',
                'slug' => 'purchases', // Includes: Purchase Orders, Purchase Returns, Suppliers
                'is_active' => 1,
            ],
            [
                'name' => 'CRM & Lead Management',
                'slug' => 'crm', // Includes: Leads, Pipelines, Tasks, Activities
                'is_active' => 1,
            ],
            [
                'name' => 'HR & Payroll Management',
                'slug' => 'hrm', // Includes: Employees, Attendance, Payroll
                'is_active' => 1,
            ],
            [
                'name' => 'Expense Tracking',
                'slug' => 'expenses', // Includes: Expenses, Expense Categories
                'is_active' => 1,
            ],
            [
                'name' => 'Advanced Reports & Analytics',
                'slug' => 'reports', // Includes: Advanced filtering, Exporting
                'is_active' => 1,
            ],
            [
                'name' => 'B2B/B2C Storefront',
                'slug' => 'storefront', // Includes: Storefront Sections, Banners, Merchandising
                'is_active' => 1,
            ],
            [
                'name' => 'Manufacturing & Production',
                'slug' => 'production', // Includes: Bills of Material, Work Orders
                'is_active' => 1,
            ],
            [
                'name' => 'Inquiries & Pre-Sales',
                'slug' => 'inquiry', // Includes: Product Inquiries, Web Forms, Custom Requests
                'is_active' => 1,
            ],
            [
                'name' => 'Delivery Challans & Dispatch',
                'slug' => 'challan', // Includes: Delivery Challans, Returnable Challans, Job Work
                'is_active' => 1,
            ],
            [
                'name' => 'Plant Education',
                'slug' => 'plant_education', // Includes: Delivery Challans, Returnable Challans, Job Work
                'is_active' => 1,
            ],
            [
                'name' => 'Bulk Uploaders',
                'slug' => 'bulk_import', // Includes: Delivery Challans, Returnable Challans, Job Work
                'is_active' => 1,
            ],
        ];

        DB::beginTransaction();

        try {
            foreach ($modules as $module) {
                // updateOrInsert prevents duplicate rows if you run the seeder multiple times
                DB::table('modules')->updateOrInsert(
                    ['slug' => $module['slug']],
                    [
                        'name' => $module['name'],
                        'is_active' => $module['is_active'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            DB::commit();
            if (isset($this->command)) {
                $this->command->info('✅ High-level SaaS Modules seeded successfully!');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($this->command)) {
                $this->command->error('❌ Failed to seed modules: '.$e->getMessage());
            }
            throw $e;
        }
    }
}
