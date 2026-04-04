<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════╗');
        $this->command->info('║   Category Products Pivot Seeder         ║');
        $this->command->info('╚══════════════════════════════════════════╝');
        $this->command->info('');

        // ── Safety check ──
        $existing = CategoryProduct::count();
        if ($existing > 0) {
            $this->command->warn("⚠  Pivot table already has {$existing} records.");
            if (!$this->command->confirm('Do you want to continue and add missing records?', true)) {
                $this->command->info('Seeder cancelled.');
                return;
            }
        }

        // ── Get all products that have a category_id set ──
        $products = Product::whereNotNull('category_id')
            ->with('category')
            ->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products with category_id found. Nothing to seed.');
            $this->command->info('→ Assign category_id to your products first, then re-run this seeder.');
            return;
        }

        $this->command->info("Found {$products->count()} products with categories. Processing...");
        $this->command->newLine();

        $created  = 0;
        $skipped  = 0;
        $failed   = 0;

        // ── Track sort order per category ──
        $sortOrderMap = [];

        $bar = $this->command->getOutput()->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            try {
                $categoryId = $product->category_id;

                // Verify category exists and belongs to same company
                if (!$product->category || $product->category->company_id !== $product->company_id) {
                    Log::warning('[CategoryProductSeeder] Mismatched company or missing category', [
                        'product_id'  => $product->id,
                        'category_id' => $categoryId,
                    ]);
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Auto-increment sort order per category
                if (!isset($sortOrderMap[$categoryId])) {
                    $sortOrderMap[$categoryId] = 0;
                }

                $result = CategoryProduct::firstOrCreate(
                    [
                        'category_id' => $categoryId,
                        'product_id'  => $product->id,
                    ],
                    [
                        'is_active'   => $product->is_active,   // inherit product active state
                        'is_featured' => false,                  // nothing featured by default
                        'sort_order'  => $sortOrderMap[$categoryId],
                        'added_by'    => null,                   // system seeder
                    ]
                );

                if ($result->wasRecentlyCreated) {
                    $sortOrderMap[$categoryId]++;
                    $created++;
                } else {
                    $skipped++;
                }

            } catch (\Throwable $e) {
                $failed++;
                Log::error('[CategoryProductSeeder] Failed for product', [
                    'product_id' => $product->id ?? null,
                    'error'      => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);

        // ── Summary ──
        $total = CategoryProduct::count();

        $this->command->info('┌─────────────────────────────────┐');
        $this->command->info("│  ✅ Created  : {$created}");
        $this->command->info("│  ⏭  Skipped  : {$skipped} (already existed)");
        $this->command->info("│  ❌ Failed   : {$failed}");
        $this->command->info("│  📊 Total    : {$total} pivot records");
        $this->command->info('└─────────────────────────────────┘');

        if ($failed > 0) {
            $this->command->warn("⚠  {$failed} records failed. Check storage/logs/laravel.log for details.");
        }

        $this->command->newLine();
        $this->command->info('✅ Done! Open /admin/merchandising to see your products.');
        $this->command->info('   You can now reorder, feature, and manage per-category visibility.');
    }
}