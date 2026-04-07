<?php

use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AnnouncementPopupController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AttributeValueController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChallanController;
use App\Http\Controllers\Admin\ChallanReturnController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\Crm\CrmDashboardController;
use App\Http\Controllers\Admin\Crm\CrmImportExportController;
use App\Http\Controllers\Admin\Crm\CrmLeadController;
use App\Http\Controllers\Admin\Crm\CrmLeadSourceController;
use App\Http\Controllers\Admin\Crm\CrmPipelineController;
use App\Http\Controllers\Admin\Crm\CrmStageController;
use App\Http\Controllers\Admin\Crm\CrmTagController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\Hrm\AnnouncementController as HrmAnnouncementController;
use App\Http\Controllers\Admin\Hrm\AttendanceController as HrmAttendanceController;
use App\Http\Controllers\Admin\Hrm\AttendanceRuleController as HrmAttendanceRuleController;
use App\Http\Controllers\Admin\Hrm\DepartmentController as HrmDepartmentController;
use App\Http\Controllers\Admin\Hrm\DesignationController as HrmDesignationController;
use App\Http\Controllers\Admin\Hrm\EmployeeController as HrmEmployeeController;
use App\Http\Controllers\Admin\Hrm\EmployeeDashboardController as HrmEmployeeDashboardController;
use App\Http\Controllers\Admin\Hrm\HolidayController as HrmHolidayController;
use App\Http\Controllers\Admin\Hrm\HrmTaskController;
use App\Http\Controllers\Admin\Hrm\LeaveBalanceController as HrmLeaveBalanceController;
use App\Http\Controllers\Admin\Hrm\LeaveController as HrmLeaveController;
use App\Http\Controllers\Admin\Hrm\LeaveTypeController as HrmLeaveTypeController;
use App\Http\Controllers\Admin\Hrm\MobileScanController as HrmMobileScanController;
use App\Http\Controllers\Admin\Hrm\MyAttendanceController as HrmMyAttendanceController;
use App\Http\Controllers\Admin\Hrm\MyLeaveController as HrmMyLeaveController;
use App\Http\Controllers\Admin\Hrm\MySalarySlipController as HrmMySalarySlipController;
// CRM Controllers
use App\Http\Controllers\Admin\Hrm\MyTaskController as HrmMyTaskController;
use App\Http\Controllers\Admin\Hrm\MyWorkLogController as HrmMyWorkLogController;
use App\Http\Controllers\Admin\Hrm\OfficeLocationController as HrmOfficeLocationController;
use App\Http\Controllers\Admin\Hrm\SalaryComponentController as HrmSalaryComponentController;
use App\Http\Controllers\Admin\Hrm\SalarySlipController as HrmSalarySlipController;
use App\Http\Controllers\Admin\Hrm\ShiftController as HrmShiftController;
use App\Http\Controllers\Admin\Hrm\WorkLogController as HrmWorkLogController;
// HRM Controllers
use App\Http\Controllers\Admin\InventoryReportController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\InvoiceReturnController;
use App\Http\Controllers\Admin\LabelController;
use App\Http\Controllers\Admin\MerchandisingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\PurchaseReturnController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\StorefrontSectionController;
use App\Http\Controllers\Admin\StorefrontSectionProductController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Platform\OnboardingController;
use Illuminate\Support\Facades\Route;

/*
|==========================================================================
| PHASE 2 IN ACTION: TENANT (BUSINESS OWNER) ROUTES
|==========================================================================
| 1. 'auth'         -> Must be logged in.
| 2. 'role:owner'   -> Must be the business owner (or have owner permissions).
| 3. 'subscription' -> THE BOUNCER! Kicks them out if they haven't paid or plan expired.
|==========================================================================
*/
Route::middleware(['auth', 'subscription', 'store.session', 'announcements'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ════════════════════════════════════════════════
        // SETTINGS
        // ════════════════════════════════════════════════
        Route::prefix('settings')->name('settings.')->controller(SettingController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'update')->name('update');
            Route::post('/notifications', 'updateNotifications')->name('notifications.update');
            Route::post('/clear-cache', 'clearCache')->name('clear-cache');
            Route::post('/reset', 'resetAll')->name('reset');
            Route::get('/audit', 'auditTrail')->name('audit');
        });
        // ── Storefront Pages (CMS) ──
        Route::prefix('pages')->name('pages.')->controller(PageController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{page}/edit', 'edit')->name('edit');
            Route::put('/{page}', 'update')->name('update');
            Route::delete('/{page}', 'destroy')->name('destroy');

            // AJAX Quick Toggles
            Route::post('/{page}/toggle', 'togglePublish')->name('toggle');
        });

        // ── Notifications ──
        Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/fetch-recent', 'fetchRecent')->name('fetch-recent');
            Route::post('/{id}/read', 'markAsRead')->name('read');
            Route::post('/mark-all-read', 'markAllRead')->name('mark-all-read');
        });

        Route::prefix('banners')->name('banners.')->controller(BannerController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{banner}', 'show')->name('show');
            Route::get('/{banner}/edit', 'edit')->name('edit');
            Route::put('/{banner}', 'update')->name('update');
            Route::delete('/{banner}', 'destroy')->name('destroy');
            Route::post('/{banner}/toggle', 'toggleActive')->name('toggle');
            Route::post('/{banner}/duplicate', 'duplicate')->name('duplicate');
            Route::post('/reorder', 'reorder')->name('reorder');
            Route::post('/{id}/restore', 'restore')->name('restore');
            Route::post('/{banner}/click', 'trackClick')->name('track-click');
        });

        Route::prefix('merchandising')
            ->name('merchandising.')
            ->controller(MerchandisingController::class)
            ->group(function () {

                // ── Main page ──
                Route::get('/', 'index')->name('index');

                // ── AJAX: load products for a category ──
                Route::get('/{categoryId}/products', 'loadCategory')->name('load-category');

                // ── AJAX: search unassigned products ──
                Route::get('/{categoryId}/search', 'searchProducts')->name('search');

                // ── AJAX: add a product to category ──
                Route::post('/{categoryId}/add', 'addProduct')->name('add-product');

                // ── AJAX: remove a product from category ──
                Route::delete('/{categoryId}/products/{productId}', 'removeProduct')->name('remove-product');

                // ── AJAX: save drag-drop order ──
                Route::post('/{categoryId}/reorder', 'reorder')->name('reorder');

                // ── AJAX: toggle featured star ──
                Route::post('/{categoryId}/products/{productId}/toggle-featured', 'toggleFeatured')->name('toggle-featured');

                // ── AJAX: toggle per-category visibility ──
                Route::post('/{categoryId}/products/{productId}/toggle-active', 'toggleActive')->name('toggle-active');

            });

        Route::prefix('storefront-sections')
            ->name('storefront-sections.')
            ->controller(StorefrontSectionController::class)
            ->group(function () {

                // ── Standard CRUD ──
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{storefrontSection}/edit', 'edit')->name('edit');
                Route::put('/{storefrontSection}', 'update')->name('update');
                Route::delete('/{storefrontSection}', 'destroy')->name('destroy');

                // ── AJAX ──
                Route::post('/reorder', 'reorder')->name('reorder');
                Route::post('/{storefrontSection}/toggle', 'toggleActive')->name('toggle');
                Route::post('/{storefrontSection}/duplicate', 'duplicate')->name('duplicate');
            });
        Route::prefix('storefront-sections/{storefrontSection}/products')
            ->name('storefront-sections.products.')
            ->controller(StorefrontSectionProductController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/load', 'load')->name('load');
                Route::get('/search', 'search')->name('search');
                Route::post('/', 'add')->name('add');
                Route::delete('/{productId}', 'remove')->name('remove');
                Route::post('/reorder', 'reorder')->name('reorder');
            });
        Route::prefix('orders')
            ->name('orders.')
            ->controller(AdminOrderController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{order}/edit', 'edit')->name('edit');
                Route::post('/', 'store')->name('store');
                Route::get('/{order}', 'show')->name('show');
                Route::put('/{order}', 'update')->name('update');
                Route::patch('/{order}/logistics', 'updateLogistics')->name('logistics'); // 🌟 NEW: Quick Tracking Update
                Route::post('/{order}/status', 'updateStatus')->name('status');
                Route::post('/{order}/cancel', 'cancel')->name('cancel');
                Route::post('/{order}/note', 'addNote')->name('note');
                Route::post('/{order}/mark-paid', 'markPaid')->name('mark-paid');
                Route::get('/{order}/receipt', 'downloadReceipt')->name('receipt');
            });

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/inventory/reports', [InventoryReportController::class, 'index'])
            ->name('inventory.reports.index');

        // Everything below here REQUIRES a store to exist!
        Route::middleware('store.exists')->group(function () {

            // --- ONBOARDING ROUTES ---
            Route::get('/welcome', [OnboardingController::class, 'index'])->name('onboarding.index');
            Route::post('/welcome', [OnboardingController::class, 'store'])->name('onboarding.store');

            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/', [DashboardController::class, 'index']);

            // ── Announcement Popup (employee-facing AJAX) ──
            Route::prefix('announcements-popup')->name('announcements-popup.')->controller(AnnouncementPopupController::class)->group(function () {
                Route::get('/pending', 'pending')->name('pending');
                Route::post('/{announcement}/read', 'markRead')->name('read');
                Route::post('/{announcement}/acknowledge', 'acknowledge')->name('acknowledge');
                Route::post('/{announcement}/dismiss', 'dismiss')->name('dismiss');
            });

            Route::post('/switch-store', [StoreController::class, 'switch'])->name('store.switch');

            Route::resource('/users', UserController::class);
            Route::resource('/stores', StoreController::class);
            Route::resource('/clients', ClientController::class)->except(['create', 'edit', 'show']);
            Route::resource('/suppliers', SupplierController::class)->except(['create', 'show', 'edit']);
            Route::resource('/warehouses', WarehouseController::class);
            Route::resource('/purchases', PurchaseController::class);
            // 1. Standard Resource
            Route::resource('purchase-returns', PurchaseReturnController::class);
            Route::resource('invoices', InvoiceController::class);

            // ==========================================
            // INVOICE RETURNS (CREDIT NOTES)
            // ==========================================

            // 1. Custom Create & Store (Requires the original invoice ID)
            Route::get('invoices/{invoice}/returns/create', [InvoiceReturnController::class, 'create'])
                ->name('invoice-returns.create');

            Route::post('invoices/{invoice}/returns', [InvoiceReturnController::class, 'store'])
                ->name('invoice-returns.store');

            // 2. Custom Action (Confirm Return)
            Route::post('invoice-returns/{invoiceReturn}/confirm', [InvoiceReturnController::class, 'confirm'])
                ->name('invoice-returns.confirm');

            // 3. Standard Resource (Handles index, show, edit, update, destroy)
            Route::resource('invoice-returns', InvoiceReturnController::class)
                ->except(['create', 'store'])
                ->names('invoice-returns'); // 🌟 CRITICAL FIX: Forces the 'admin.' prefix!

            Route::resource('quotations', QuotationController::class);
            Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convertToInvoice'])->name('quotations.convert');
            Route::post('quotations/{quotation}/mark-sent', [QuotationController::class, 'markAsSent'])->name('quotations.mark_sent');
            Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'downloadPdf'])
                ->name('quotations.pdf');

            // ==========================================
            // POS MODULE ROUTING
            // ==========================================
            Route::prefix('pos')->name('pos.')->group(function () {
                Route::get('/', [PosController::class, 'index'])->name('index');
                Route::post('/store', [PosController::class, 'store'])->name('store');
                Route::get('/scan', [PosController::class, 'scanItem'])->name('scan');
                Route::post('/quick-product', [PosController::class, 'storeQuickProduct'])->name('quick-product');
                Route::get('/receipt/{id}', [PosController::class, 'receipt'])->name('receipt');
            });

            // Endpoint for the POS Product Grid (Infinite Scroll & Search)
            Route::get('api/products', [PosController::class, 'fetchProducts'])->name('api.products');

            // 2. API Endpoint for Alpine to fetch Purchase Order details dynamically
            Route::patch('/purchases/{purchase}/payment', [PurchaseController::class, 'updatePayment'])
                ->name('purchases.payment');
            Route::patch('/purchase-returns/{purchase_return}/payment', [PurchaseReturnController::class, 'updatePayment'])
                ->name('purchase-returns.payment');
            Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'addPayment'])->name('admin.invoices.pay');

            // Purchase Specific API & Actions
            Route::get('api/purchases/{id}/for-return', [PurchaseController::class, 'getForReturn'])
                ->name('api.purchases.for-return');

            // 🌟 NEW SEARCH ROUTE
            Route::get('api/purchases/search', [SearchController::class, 'searchPurchases'])
                ->name('api.purchases.search');

            // Download PDFs
            Route::get('/purchases/{purchase}/pdf', [PurchaseController::class, 'downloadPdf'])
                ->name('purchases.pdf');

            Route::get('/purchase-returns/{purchase_return}/pdf', [PurchaseReturnController::class, 'downloadPdf'])
                ->name('purchase-returns.pdf');
            Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

        });
        // Label Printing Module
        Route::get('/labels', [LabelController::class, 'index'])->name('labels.index');
        Route::get('/labels/render', [LabelController::class, 'renderImage'])->name('labels.render-image');
        Route::get('/api/labels/search', [LabelController::class, 'fetchProducts'])->name('labels.fetch-products');
        Route::post('/api/labels/selected', [LabelController::class, 'fetchSelectedSkus'])->name('api.labels.selected');
        // Core Product Resource
        Route::resource('/products', ProductController::class);
        Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::post('/products/{product}/duplicate', [ProductController::class, 'duplicate'])
            ->name('products.duplicate');

        Route::resource('/roles', RoleController::class);

        Route::resource('/attributes', AttributeController::class)->except(['create', 'show', 'edit']);
        Route::post('/attributes/{attribute}/values', [AttributeValueController::class, 'store'])->name('attribute-values.store');
        Route::put('/attribute-values/{attributeValue}', [AttributeValueController::class, 'update'])->name('attribute-values.update');
        Route::delete('/attribute-values/{attributeValue}', [AttributeValueController::class, 'destroy'])->name('attribute-values.destroy');

        Route::resource('/categories', CategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('/units', UnitController::class)->except(['create', 'show', 'edit']);

        Route::post('/payment-methods/reorder', [PaymentMethodController::class, 'reorder'])->name('payment_methods.reorder');
        Route::resource('/payment-methods', PaymentMethodController::class)->except(['create', 'edit', 'show']);

        // API LEVEL
        Route::get('/api/search-skus', [SearchController::class, 'searchSkus'])->name('api.search-skus');

        Route::prefix('crm')->name('crm.')->group(function () {

            Route::get('/dashboard', [CrmDashboardController::class, 'index'])
                ->name('dashboard');
            // ── Pipelines ──
            Route::prefix('pipelines')->name('pipelines.')->controller(CrmPipelineController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                // Route::get('/create',               'create')      ->name('create');
                // Route::get('/{pipeline}/edit',      'edit')        ->name('edit');
                Route::put('/{pipeline}', 'update')->name('update');
                Route::delete('/{pipeline}', 'destroy')->name('destroy');
                Route::post('/{pipeline}/default', 'setDefault')->name('default');
            });

            // ── Stages (nested under pipeline) ──
            Route::prefix('pipelines/{pipeline}/stages')->name('stages.')->controller(CrmStageController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{stage}', 'update')->name('update');
                Route::delete('/{stage}', 'destroy')->name('destroy');
                Route::post('/reorder', 'reorder')->name('reorder'); // SortableJS
            });

            // ── Lead Sources ──
            Route::prefix('sources')->name('sources.')->controller(CrmLeadSourceController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{source}', 'update')->name('update');
                Route::delete('/{source}', 'destroy')->name('destroy');
            });

            // ── Tags ──
            Route::prefix('tags')->name('tags.')->controller(CrmTagController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{tag}', 'update')->name('update');
                Route::delete('/{tag}', 'destroy')->name('destroy');
            });

            // ── Import / Export ──
            Route::get('/leads/import', [CrmImportExportController::class, 'importPage'])->name('leads.import');
            Route::post('/leads/import', [CrmImportExportController::class, 'import'])->name('leads.import.store');
            Route::get('/leads/import/template', [CrmImportExportController::class, 'template'])->name('leads.import.template');
            Route::get('/leads/export', [CrmImportExportController::class, 'export'])->name('leads.export');

            // ── Leads ──
            Route::prefix('leads')->name('leads.')->controller(CrmLeadController::class)->group(function () {

                // ── Core CRUD ──
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{lead}', 'show')->name('show');
                Route::get('/{lead}/edit', 'edit')->name('edit');
                Route::put('/{lead}', 'update')->name('update');
                Route::delete('/{lead}', 'destroy')->name('destroy');

                // ── Stage move — AJAX ──
                Route::post('/{lead}/stage', 'moveStage')->name('stage');

                // ── Activity log — AJAX ──
                Route::post('/{lead}/activity', 'logActivity')->name('activity');

                // ── Tasks — AJAX ──
                Route::post('/{lead}/tasks', 'storeTask')->name('tasks.store');
                Route::put('/{lead}/tasks/{task}', 'updateTask')->name('tasks.update');
                Route::post('/{lead}/tasks/{task}/complete', 'completeTask')->name('tasks.complete');
                Route::delete('/{lead}/tasks/{task}', 'destroyTask')->name('tasks.destroy');

                // ── Convert lead → client ──
                Route::post('/{lead}/convert', 'convert')->name('convert');

                // ── Score update ──
                Route::post('/{lead}/score', 'updateScore')->name('score');

            });

        });

        // ── EXPENSE MODULE ──
        Route::prefix('expenses')->name('expenses.')->group(function () {

            // Core CRUD
            Route::get('/', [ExpenseController::class, 'index'])->name('index');
            Route::get('/create', [ExpenseController::class, 'create'])->name('create');
            Route::post('/', [ExpenseController::class, 'store'])->name('store');
            Route::get('/{expense}', [ExpenseController::class, 'show'])->name('show');
            Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->name('edit');
            Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
            Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');

            // Workflow / Status Update (AJAX)
            Route::patch('/{expense}/status', [ExpenseController::class, 'updateStatus'])->name('status.update');
        });

        // Expense Categories (Single Page CRUD)
        Route::patch('expense-categories/{expense_category}/toggle-status', [ExpenseCategoryController::class, 'toggleStatus'])
            ->name('expense-categories.toggle-status');

        Route::resource('expense-categories', ExpenseCategoryController::class)
            ->except(['create', 'show', 'edit'])
            ->parameters([
                'expense-categories' => 'expense_category', // Ensures the route model binding matches our controller variable
            ]);

        // ── Custom Challan Routes ──
        Route::get('challans/{challan}/pdf', [ChallanController::class, 'downloadPdf'])->name('challans.pdf');
        Route::patch('challans/{challan}/status', [ChallanController::class, 'updateStatus'])->name('challans.status.update');

        // ── Standard Resource Routes ──
        Route::resource('challans', ChallanController::class);

        // ──────────────────────────────────────────────────────────────
        // CHALLAN RETURNS
        // ──────────────────────────────────────────────────────────────
        Route::get('challan-returns', [ChallanReturnController::class, 'index'])->name('challan-returns.index');
        Route::get('challan-returns/create/{challan}', [ChallanReturnController::class, 'create'])->name('challan-returns.create');
        Route::post('challan-returns', [ChallanReturnController::class, 'store'])->name('challan-returns.store');
        Route::get('challan-returns/{challanReturn}', [ChallanReturnController::class, 'show'])->name('challan-returns.show');
        Route::get('challan-returns/{challanReturn}/edit', [ChallanReturnController::class, 'edit'])->name('challan-returns.edit');
        Route::put('challan-returns/{challanReturn}', [ChallanReturnController::class, 'update'])->name('challan-returns.update');
        Route::get('challan-returns/{challanReturn}/pdf', [ChallanReturnController::class, 'downloadPdf'])->name('challan-returns.pdf');

        // ════════════════════════════════════════════════
        // HRM MODULE
        // ════════════════════════════════════════════════
        Route::prefix('hrm')->name('hrm.')->group(function () {

            // ── Employee Self-Service Dashboard ──
            Route::get('employee/dashboard', [HrmEmployeeDashboardController::class, 'index'])->name('employee.dashboard');

            // ── My Leaves (employee self-service) ──
            Route::get('my-leaves', [HrmMyLeaveController::class, 'index'])->name('my-leaves.index');
            Route::post('my-leaves', [HrmMyLeaveController::class, 'store'])->name('my-leaves.store');
            Route::get('my-leaves/{leave}', [HrmMyLeaveController::class, 'show'])->name('my-leaves.show');
            Route::put('my-leaves/{leave}', [HrmMyLeaveController::class, 'update'])->name('my-leaves.update');
            Route::delete('my-leaves/{leave}', [HrmMyLeaveController::class, 'destroy'])->name('my-leaves.destroy');

            // ── My Attendance (employee self-service) ──
            Route::get('my-attendance', [HrmMyAttendanceController::class, 'index'])->name('my-attendance.index');

            // ── My Tasks (employee self-service) ──
            Route::get('my-tasks', [HrmMyTaskController::class, 'index'])->name('my-tasks.index');
            Route::get('my-tasks/{task}', [HrmMyTaskController::class, 'show'])->name('my-tasks.show');
            Route::patch('my-tasks/{task}/progress', [HrmMyTaskController::class, 'updateProgress'])->name('my-tasks.progress');
            Route::post('my-tasks/{task}/comments', [HrmMyTaskController::class, 'addComment'])->name('my-tasks.comments.store');
            Route::post('my-tasks/{task}/attachments', [HrmMyTaskController::class, 'uploadAttachment'])->name('my-tasks.attachments.store');
            Route::get('my-tasks/attachments/{attachment}/download', [HrmMyTaskController::class, 'downloadAttachment'])->name('my-tasks.attachments.download');

            // ── My Work Logs (employee self-service) ──
            Route::prefix('my-work-logs')->name('my-work-logs.')->controller(HrmMyWorkLogController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{workLog}', 'update')->name('update');
                Route::delete('/{workLog}', 'destroy')->name('destroy');
            });

            // ── My Salary Slips (employee self-service) ──
            Route::get('my-salary-slips', [HrmMySalarySlipController::class, 'index'])->name('my-salary-slips.index');
            Route::get('my-salary-slips/{salarySlip}/pdf', [HrmMySalarySlipController::class, 'downloadPdf'])->name('my-salary-slips.pdf');

            // ── Departments (Single Page CRUD) ──
            Route::resource('departments', HrmDepartmentController::class)->except(['create', 'show', 'edit']);

            // ── Designations (Single Page CRUD) ──
            Route::resource('designations', HrmDesignationController::class)->except(['create', 'show', 'edit']);

            // ── Shifts (Single Page CRUD) ──
            Route::resource('shifts', HrmShiftController::class)->except(['create', 'show', 'edit']);

            // ── Holidays (Single Page CRUD) ──
            Route::resource('holidays', HrmHolidayController::class)->except(['create', 'show', 'edit']);

            // ── Employees ──
            Route::resource('employees', HrmEmployeeController::class);
            // Salary Structure management per employee
            Route::get('employees/{employee}/salary-structures', [HrmEmployeeController::class, 'salaryStructures'])->name('employees.salary-structures.index');
            Route::post('employees/{employee}/salary-structures', [HrmEmployeeController::class, 'storeSalaryStructure'])->name('employees.salary-structures.store');
            Route::delete('employees/{employee}/salary-structures/{structure}', [HrmEmployeeController::class, 'destroySalaryStructure'])->name('employees.salary-structures.destroy');

            // ── Attendance ──
            Route::prefix('attendance')->name('attendance.')->controller(HrmAttendanceController::class)->group(function () {
                Route::post('/scan', 'scan')->name('scan')->middleware('throttle:10,1');
                Route::get('/today', 'today')->name('today');
                Route::get('/report', 'report')->name('report');
                Route::post('/{attendance}/override', [HrmAttendanceController::class, 'override'])->name('override');
            });

            // ── Attendance Rules (Single Page CRUD) ──
            Route::resource('attendance-rules', HrmAttendanceRuleController::class)->except(['create', 'show', 'edit']);

            // ── Attendance Settings ──

            // ── Office Locations (GPS + per-store QR) ──
            Route::prefix('office-locations')->name('office-locations.')->controller(HrmOfficeLocationController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::put('/{store}', 'update')->name('update');
                Route::post('/{store}/generate-qr', 'generateQr')->name('generate-qr');
                Route::get('/{store}/poster', 'poster')->name('poster');
            });

            // ── Mobile Attendance Scan (printed QR poster → phone) ──
            Route::get('attend/{store}', [HrmMobileScanController::class, 'show'])->name('attend');

            // ── Leave Types (Single Page CRUD) ──
            Route::resource('leave-types', HrmLeaveTypeController::class)->except(['create', 'show', 'edit']);

            // ── Leave Balances ──
            Route::prefix('leave-balances')->name('leave-balances.')->controller(HrmLeaveBalanceController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/initialize', 'initialize')->name('initialize');
                Route::post('/carry-forward', 'carryForward')->name('carry-forward');
                Route::put('/{leaveBalance}', 'update')->name('update');
            });

            // ── Leaves ──
            Route::prefix('leaves')->name('leaves.')->controller(HrmLeaveController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{leave}', 'show')->name('show');
                Route::patch('/{leave}/approve', 'approve')->name('approve');
                Route::patch('/{leave}/reject', 'reject')->name('reject');
                Route::patch('/{leave}/cancel', 'cancel')->name('cancel');
            });
            Route::get('/leaves/balances/{employee}', [HrmLeaveController::class, 'balances'])->name('leaves.employee-balances');

            // ── Salary Components (Single Page CRUD) ──
            Route::resource('salary-components', HrmSalaryComponentController::class)->except(['create', 'show', 'edit']);

            // ── Salary Slips ──
            Route::prefix('salary-slips')->name('salary-slips.')->controller(HrmSalarySlipController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{salarySlip}', 'show')->name('show');
                Route::post('/generate', 'generate')->name('generate');
                Route::patch('/{salarySlip}/approve', 'approve')->name('approve');
                Route::patch('/{salarySlip}/pay', 'markPaid')->name('pay');
                Route::get('/{salarySlip}/pdf', 'downloadPdf')->name('pdf');
                Route::delete('/{salarySlip}', 'destroy')->name('destroy');
            });

            // ── Tasks ──
            Route::prefix('tasks')->name('tasks.')->controller(HrmTaskController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{task}', 'show')->name('show');
                Route::get('/{task}/edit', 'edit')->name('edit');
                Route::put('/{task}', 'update')->name('update');
                Route::delete('/{task}', 'destroy')->name('destroy');
                Route::patch('/{task}/status', 'updateStatus')->name('status');
                Route::post('/{task}/comments', 'addComment')->name('comments.store');
                Route::post('/{task}/attachments', 'addAttachment')->name('attachments.store');
                Route::get('/attachments/{attachment}/download', 'downloadAttachment')->name('attachments.download');
                Route::delete('/attachments/{attachment}', 'deleteAttachment')->name('attachments.destroy');
            });

            // ── Announcements ──
            Route::prefix('announcements')->name('announcements.')->controller(HrmAnnouncementController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                // 👉 Download must be here
                Route::get('/{announcement}/download', 'downloadAttachment')->name('download');

                // 👉 Add ->whereNumber() so it doesn't accidentally catch the download route!
                Route::get('/{announcement}', 'show')->name('show')->whereNumber('announcement');
                Route::get('/{announcement}/edit', 'edit')->name('edit')->whereNumber('announcement');
                Route::put('/{announcement}', 'update')->name('update')->whereNumber('announcement');
                Route::delete('/{announcement}', 'destroy')->name('destroy')->whereNumber('announcement');

                // Status actions
                Route::patch('/{announcement}/publish', 'publish')->name('publish');
                Route::patch('/{announcement}/unpublish', 'unpublish')->name('unpublish');
                Route::patch('/{announcement}/schedule', 'schedule')->name('schedule');

                // Utilities
                Route::post('/{announcement}/duplicate', 'duplicate')->name('duplicate');
                Route::post('/{id}/restore', 'restore')->name('restore');
            });

            // ── Work Logs ──
            Route::prefix('work-logs')->name('work-logs.')->controller(HrmWorkLogController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{workLog}', 'update')->name('update');
                Route::delete('/{workLog}', 'destroy')->name('destroy');
                Route::patch('/{workLog}/approve', 'approve')->name('approve');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Module-Specific Routes (Protected by the Second Bouncer!)
        |--------------------------------------------------------------------------
        | Only owners whose active Subscription Plan includes these specific
        | modules will be allowed to access these routes.
        */

        // Example: Point of Sale Module
        // Route::middleware(['module:pos'])->group(function () {
        //     Route::view('/pos',               'admin.pos')->name('pos');
        // });

        // Example: Advanced Accounting Module
        // Route::middleware(['module:accounting'])->group(function () {
        //     Route::resource('/invoices', InvoiceController::class);
        // });

    });
