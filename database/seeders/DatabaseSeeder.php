<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\CRM\CrmStageSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // SuperAdminSeeder::class,
            // ModuleSeeder::class,
            // StateSeeder::class,
            // UnitSeeder::class,
            CrmStageSeeder::class,
            // PermissionSeeder::class,
            // AttributeSeeder::class,
            // CategorySeeder::class,
            // CategoryProductSeeder::class,
            // PeopleSeeder::class,
            // ProductSeeder::class,
            // RoleSeeder::class,
            // SystemSettingsSeeder::class,
        ]);
    }
}
