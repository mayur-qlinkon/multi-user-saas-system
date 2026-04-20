<?php

namespace Database\Seeders\Platform;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 🌟 THE ENTERPRISE PERMISSION MATRIX
        $modules = [
            // ── Core & Dashboard ──
            'dashboard' => ['view'],
            'settings' => ['view', 'update', 'clear_cache', 'update_notifications', 'reset', 'audit'],
            'reports' => ['view', 'export'],
            'audit_logs' => ['view'],
            'notifications' => ['view'],

            // ── POS & Sales ──
            'pos' => ['access', 'create_sale', 'create_quick_product', 'create_quick_client', 'apply_discount'],
            'orders' => ['view', 'create', 'update', 'delete', 'change_status', 'cancel', 'add_note', 'record_payment', 'download_receipt'],
            'quotations' => ['view', 'create', 'update', 'delete', 'convert', 'mark_sent', 'download_pdf'],

            // ── Accounting & Billing ──
            'invoices' => ['view', 'create', 'update', 'delete', 'add_payment', 'download_pdf'],
            'invoice_returns' => ['view', 'create', 'update', 'delete', 'confirm'],
            'purchases' => ['view', 'create', 'update', 'delete', 'add_payment', 'download_pdf'],
            'purchase_returns' => ['view', 'create', 'update', 'delete', 'add_payment', 'download_pdf'],
            'expenses' => ['view', 'create', 'update', 'delete', 'approve', 'reimburse'],
            'expense_categories' => ['view', 'create', 'update', 'delete'],

            // ── Catalog & Inventory ──
            'products' => ['view', 'create', 'update', 'delete', 'duplicate'],
            'categories' => ['view', 'create', 'update', 'delete'],
            'attributes' => ['view', 'create', 'update', 'delete'],
            'units' => ['view', 'create', 'update', 'delete'],
            'labels' => ['view', 'print'],

            // ── CRM Module ──
            'crm_dashboard' => ['view'],
            'crm_leads' => ['view', 'create', 'update', 'delete', 'convert', 'change_stage', 'import', 'export'],
            'crm_tasks' => ['view', 'create', 'complete'],
            'crm_pipelines' => ['view', 'create', 'update', 'delete'],
            'crm_stages' => ['view', 'create', 'update', 'delete', 'reorder'],
            'crm_sources' => ['view', 'create', 'update', 'delete'],
            'crm_tags' => ['view', 'create', 'update', 'delete'],

            // ── Storefront & Marketing ──
            'storefront_sections' => ['view', 'create', 'update', 'delete', 'toggle_status', 'reorder', 'duplicate'],
            'pages' => ['view', 'create', 'update', 'delete', 'toggle_publish'],
            'banners' => ['view', 'create', 'update', 'delete', 'toggle_status', 'duplicate', 'reorder'],

            // ── People & HR ──
            'clients' => ['view', 'create', 'update', 'delete', 'export'],
            'suppliers' => ['view', 'create', 'update', 'delete', 'export'],
            'users' => ['view', 'create', 'update', 'delete'],
            'hrm' => ['view', 'manage_attendance', 'manage_payroll'],
            'employee_dashboard' => ['view'],
            'employees' => ['view', 'create', 'update', 'delete'],
            'departments' => ['view', 'create', 'update', 'delete'],
            'designations' => ['view', 'create', 'update', 'delete'],
            'shifts' => ['view', 'create', 'update', 'delete'],
            'holidays' => ['view', 'create', 'update', 'delete'],
            'attendance' => ['view', 'scan', 'report', 'override'],
            'attendance_rules' => ['view', 'create', 'update', 'delete'],
            'office_locations' => ['view', 'update', 'generate_qr'],
            'leaves' => ['view', 'create', 'approve', 'reject', 'cancel'],
            'salary_components' => ['view', 'create', 'update', 'delete'],
            'salary_slips' => ['view', 'generate', 'edit', 'approve', 'mark_paid', 'download_pdf', 'delete'],
            'hrm_tasks' => ['view', 'create', 'update', 'delete', 'change_status', 'add_comment', 'add_attachment', 'download_attachment', 'delete_attachment'],
            'announcements' => ['view', 'create', 'update', 'delete', 'publish', 'duplicate'],
            'work_logs' => ['view', 'approve'],

            // ── System Architecture (Super Admin Level) ──
            'roles' => ['view', 'create', 'update', 'delete'],
            'stores' => ['view', 'create', 'update', 'delete', 'switch'],
            'warehouses' => ['view', 'create', 'update', 'delete'],
            'payment_methods' => ['view', 'create', 'update', 'delete'],

            // ── Pre-Sales ──
            'inquiries' => ['view', 'create', 'update', 'delete', 'convert_to_quotation'],

            // ── Dispatch & Compliance ──
            'challans' => ['view', 'create', 'update', 'delete', 'change_status', 'download_pdf'],
            'challan_returns' => ['view', 'create', 'update', 'download_pdf'],
            'bulk_import' => ['view', 'products', 'images_import'],
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
                            'name' => $name,
                            'module_group' => $moduleGroupName,
                            'created_at' => now(),
                            'updated_at' => now(),
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
                $this->command->error('❌ Failed to seed permissions: '.$e->getMessage());
            }
        }
    }
}
