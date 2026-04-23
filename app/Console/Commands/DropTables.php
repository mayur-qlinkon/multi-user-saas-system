<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropTables extends Command
{
    /**
     * The name and signature of the console command.
     * You can pass tables directly, OR pass nothing to trigger the interactive list.
     * php artisan drop:tables users orders
     */
    protected $signature = 'drop:tables {tables?*}';

    /**
     * The console command description.
     */
    protected $description = 'Interactively list and drop database tables without foreign key constraints';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = $this->argument('tables');

        // If no tables were provided in the command line, launch interactive mode
        if (empty($tables)) {
            
            // Fetch all tables from the database (MySQL compatible)
            $tablesInDb = array_map('current', DB::select('SHOW TABLES'));

            if (empty($tablesInDb)) {
                $this->info('No tables found in the database.');
                return;
            }

            // Display interactive list allowing multiple selections
            $tables = $this->choice(
                'Which tables would you like to drop? (Separate multiple choices with commas, e.g., 1,3,4)',
                $tablesInDb,
                null,
                null,
                true // This 'true' allows multiple selections
            );
        }

        if (empty($tables)) {
            $this->warn('No tables selected. Exiting.');
            return;
        }

        // Display selected tables back to the user
        $this->warn('You have selected the following tables to DROP:');
        foreach ($tables as $table) {
            $this->line("<fg=red>- $table</>");
        }

        // Final Confirmation for safety
        if (! $this->confirm('⚠️ WARNING: This will PERMANENTLY DROP these tables and their structure. Are you sure?')) {
            $this->info('Operation cancelled.');
            return;
        }

        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tables as $table) {
                // Drop the table if it exists
                Schema::dropIfExists($table);
                $this->info("✅ Dropped: {$table}");
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('🎉 All selected tables dropped successfully!');
            
        } catch (\Exception $e) {
            // Ensure foreign key checks are re-enabled even if an error occurs
            DB::statement('SET FOREIGN_KEY_CHECKS=1;'); 
            $this->error('❌ Error: '.$e->getMessage());
        }
    }
}