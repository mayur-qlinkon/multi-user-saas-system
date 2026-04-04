<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // 🌟 THE ENTERPRISE PERMISSION MATRIX
        $modules = [
            // ── Core & Dashboard ──
            'dashboard' => ['view'],
            'settings'  => ['view', 'update', 'clear_cache'],
            'reports'   => ['view', 'export'],
            'audit_logs'=> ['view'],

            // ── POS & Sales ──
            'pos'           => ['access', 'create_quick_product', 'apply_discount'],
            'orders'        => ['view', 'create', 'update', 'delete', 'change_status'],
            'quotations'    => ['view', 'create', 'update', 'delete', 'convert', 'download_pdf'],
            
            // ── Accounting & Billing ──
            'invoices'         => ['view', 'create', 'update', 'delete', 'add_payment', 'download_pdf'],
            'invoice_returns'  => ['view', 'create', 'update', 'delete'],
            'purchases'        => ['view', 'create', 'update', 'delete', 'add_payment'],
            'purchase_returns' => ['view', 'create', 'update', 'delete'],
            'expenses'         => ['view', 'create', 'update', 'delete'],
            
            // ── Catalog & Inventory ──
            'products'   => ['view', 'create', 'update', 'delete', 'toggle_status'],
            'categories' => ['view', 'create', 'update', 'delete'],
            'attributes' => ['view', 'create', 'update', 'delete'],
            'units'      => ['view', 'create', 'update', 'delete'],
            'labels'     => ['view', 'print'],
            
            // ── CRM Module ──
            'crm_leads'     => ['view', 'create', 'update', 'delete', 'convert', 'change_stage'],
            'crm_tasks'     => ['view', 'create', 'update', 'delete', 'complete'],
            'crm_pipelines' => ['view', 'create', 'update', 'delete'],
            'crm_sources'   => ['view', 'create', 'update', 'delete'],
            'crm_tags'      => ['view', 'create', 'update', 'delete'],

            // ── Operations & Logistics ──
            'transport_receipts' => ['view', 'create', 'update', 'delete'],
            'production'         => ['view', 'create', 'update', 'delete'],

            // ── Storefront & Marketing ──
            'merchandising'       => ['view', 'update'],
            'storefront_sections' => ['view', 'create', 'update', 'delete', 'toggle_status'],
            'banners'             => ['view', 'create', 'update', 'delete', 'toggle_status'],

            // ── People & HR ──
            'clients'   => ['view', 'create', 'update', 'delete'],
            'suppliers' => ['view', 'create', 'update', 'delete','export'],
            'users'     => ['view', 'create', 'update', 'delete'],
            'hrm'       => ['view', 'manage_attendance', 'manage_payroll'],

            // ── System Architecture (Super Admin Level) ──
            'roles'           => ['view', 'create', 'update', 'delete'],
            'stores'          => ['view', 'create', 'update', 'delete', 'switch'],
            'warehouses'      => ['view', 'create', 'update', 'delete'],
            'payment_methods' => ['view', 'create', 'update', 'delete'],
            // ── Pre-Sales ──
            'inquiries' => ['view', 'create', 'update', 'delete', 'convert_to_quotation'],

            // ── Dispatch & Compliance ──
            'challans'  => ['view', 'create', 'update', 'delete', 'mark_delivered', 'download_pdf'],
                    
        ];

        DB::beginTransaction();

        try {
            foreach ($modules as $module => $actions) {
                // Format module name beautifully for the UI (e.g., "crm_leads" -> "CRM Leads", "invoice_returns" -> "Invoice Returns")
                $moduleGroupName = Str::of($module)->replace('_', ' ')->title()->replace('Crm', 'CRM')->replace('Hrm', 'HRM')->replace('Pos', 'POS');

                foreach ($actions as $action) {
                    // Format action beautifully (e.g., "download_pdf" -> "Download Pdf", "change_stage" -> "Change Stage")
                    $actionName = Str::of($action)->replace('_', ' ')->title()->replace('Pdf', 'PDF');
                    
                    $name = "{$actionName} {$moduleGroupName}"; // e.g., "Download PDF Quotations"
                    $slug = "{$module}.{$action}";              // e.g., "quotations.download_pdf"

                    // Insert if it doesn't exist
                    DB::table('permissions')->updateOrInsert(
                        ['slug' => $slug],
                        [
                            'name'         => $name,
                            'module_group' => $moduleGroupName,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]
                    );
                }
            }

            DB::commit();
            if (isset($this->command)) {
                $this->command->info('✅ Enterprise Permissions seeded successfully!');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($this->command)) {
                $this->command->error('❌ Failed to seed permissions: ' . $e->getMessage());
            }
        }
    }
}