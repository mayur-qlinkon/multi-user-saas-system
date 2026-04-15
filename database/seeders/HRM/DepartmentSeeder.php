<?php

namespace Database\Seeders\HRM;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Dynamically grab the company ID injected by the Visual Seeder Platform
        // Fallback to 1 if run manually via artisan command
        $companyId = request()->attributes->get('seeder_company_id', 1);

        $departments = [
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Handles recruitment, payroll, and employee relations.', 'sort_order' => 1],
            ['name' => 'Information Technology', 'code' => 'IT', 'description' => 'Manages technical infrastructure and software development.', 'sort_order' => 2],
            ['name' => 'Sales & Marketing', 'code' => 'SALES', 'description' => 'Drives revenue, customer acquisition, and brand awareness.', 'sort_order' => 3],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Oversees day-to-day business functions and logistics.', 'sort_order' => 4],
            ['name' => 'Finance & Accounting', 'code' => 'FIN', 'description' => 'Manages company finances, billing, and accounting.', 'sort_order' => 5],
            ['name' => 'Customer Support', 'code' => 'CS', 'description' => 'Handles customer inquiries, tickets, and assistance.', 'sort_order' => 6],
        ];

        DB::beginTransaction();

        try {
            foreach ($departments as $dept) {
                DB::table('departments')->updateOrInsert(
                    [
                        'company_id' => $companyId,
                        'name' => $dept['name'], // Unique constraint is company_id + name
                    ],
                    array_merge($dept, [
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }

            DB::commit();
            if (isset($this->command)) {
                $this->command->info("✅ HRM Departments seeded successfully for Company ID: {$companyId}");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($this->command)) {
                $this->command->error('❌ Failed to seed departments: '.$e->getMessage());
            }
        }
    }
}
