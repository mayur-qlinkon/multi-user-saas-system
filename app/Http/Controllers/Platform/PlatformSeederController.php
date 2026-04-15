<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Database\Seeders\ClientsSeeder;
use Database\Seeders\HRM\DepartmentSeeder;
use Database\Seeders\HRM\DesignationSeeder;
use Database\Seeders\HRM\LeaveTypeSeeder;
use Database\Seeders\Inventory\AttributesSeeder;
use Database\Seeders\Inventory\CategoriesSeeder;
use Database\Seeders\Inventory\ProductsSeeder;
use Database\Seeders\Inventory\UnitsSeeder;
use Database\Seeders\Inventory\WarehousesSeeder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\StateSeeder;
use Database\Seeders\SuppliersSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PlatformSeederController extends Controller
{
    /**
     * THE SECURE REGISTRY
     * Only seeders explicitly listed here can be executed via the UI.
     */
    private array $seeders = [
        'units' => [
            'name' => 'Units',
            'description' => 'Basic measurement units like pcs, kg, ltr.',
            'class' => UnitsSeeder::class,
            'requires_company' => true,
            'icon' => 'circle',
            'color' => 'teal',
        ],

        'categories' => [
            'name' => 'Categories',
            'description' => 'Product categories for classification.',
            'class' => CategoriesSeeder::class,
            'requires_company' => true,
            'icon' => 'folder',
            'color' => 'orange',
        ],

        'attributes' => [
            'name' => 'Attributes & Values',
            'description' => 'Product attributes with their possible values (Size, Color, etc.).',
            'class' => AttributesSeeder::class,
            'requires_company' => true,
            'icon' => 'archive',
            'color' => 'emerald',
        ],

        'warehouses' => [
            'name' => 'Warehouses',
            'description' => 'Dummy warehouses with codes and locations.',
            'class' => WarehousesSeeder::class,
            'requires_company' => true,
            'icon' => 'warehouse',
            'color' => 'blue',
        ],

        'products' => [
            'name' => 'Products',
            'description' => 'Dummy products with SKUs and variants.',
            'class' => ProductsSeeder::class,
            'requires_company' => true,
            'icon' => 'archive',
            'color' => 'purple',
        ],
        'clients' => [
            'name' => 'Customers',
            'description' => 'Dummy Clients.',
            'class' => ClientsSeeder::class,
            'requires_company' => true,
            'icon' => 'briefcase',
            'color' => 'purple',
        ],
        'suppliers' => [
            'name' => 'Suppliers',
            'description' => 'Dummy Suppliers',
            'class' => SuppliersSeeder::class,
            'requires_company' => true,
            'icon' => 'briefcase',
            'color' => 'purple',
        ],
        'payment_methods' => [
            'name' => 'Payment Methods',
            'description' => 'Generates default offline and online payment gateways (Cash, UPI, Razorpay).',
            'class' => PaymentMethodSeeder::class,
            'requires_company' => true,
            'icon' => 'credit-card',
            'color' => 'blue',
        ],
        'states' => [
            'name' => 'Indian States',
            'description' => 'Seeds the global list of Indian States and Union Territories.',
            'class' => StateSeeder::class,
            'requires_company' => false, // 🌟 False because this is global data
            'icon' => 'map',
            'color' => 'emerald',
        ],

        'saas_modules' => [
            'name' => 'SaaS Modules',
            'description' => 'Seeds the master list of billable platform modules.',
            'class' => ModuleSeeder::class,
            'requires_company' => false, // 🌟 False because this is global data
            'icon' => 'boxes',
            'color' => 'emerald',
        ],
        'permissions' => [
            'name' => 'System Permissions',
            'description' => 'Seeds the global enterprise permission matrix for access control.',
            'class' => PermissionSeeder::class,
            'requires_company' => false,
            'icon' => 'shield-check',
            'color' => 'emerald',
        ],
        'hrm_departments' => [
            'name' => 'HRM Departments',
            'description' => 'Seeds standard corporate departments (HR, IT, Sales, etc).',
            'class' => DepartmentSeeder::class,
            'requires_company' => true,
            'icon' => 'briefcase',
            'color' => 'blue',
        ],
        'hrm_designations' => [
            'name' => 'HRM Designations',
            'description' => 'Seeds a corporate hierarchy of designations from Trainee to CEO.',
            'class' => DesignationSeeder::class,
            'requires_company' => true,
            'icon' => 'award',
            'color' => 'purple',
        ],
        'hrm_leave_types' => [
            'name' => 'HRM Leave Policies',
            'description' => 'Seeds standard Paid, Sick, Casual, and statutory Maternity/Paternity leaves.',
            'class' => LeaveTypeSeeder::class,
            'requires_company' => true,
            'icon' => 'calendar-off',
            'color' => 'blue',
        ],
    ];

    public function index()
    {
        $companies = Company::orderBy('name')->get(['id', 'name', 'slug']);
        $seeders = $this->seeders;

        return view('platform.seeders', compact('companies', 'seeders'));
    }

    public function execute(Request $request)
    {
        // 1. Basic validation
        $request->validate([
            'seeder_key' => ['required', 'string'],
            'company_id' => ['nullable', 'exists:companies,id'],
        ]);

        // 2. Registry Security Check
        $seederConfig = $this->seeders[$request->seeder_key] ?? null;
        if (! $seederConfig) {
            return response()->json(['success' => false, 'message' => 'Invalid or unauthorized seeder.'], 403);
        }

        // 3. Context Requirement Check
        if ($seederConfig['requires_company'] && empty($request->company_id)) {
            return response()->json(['success' => false, 'message' => 'Please select a Target Company first.'], 422);
        }

        try {
            DB::beginTransaction();

            // 4. Inject the Company ID securely into the request lifecycle
            // This allows your Seeder files to dynamically grab the targeted company
            if ($request->company_id) {
                request()->attributes->set('seeder_company_id', $request->company_id);
            }

            // 5. Instantiate and Run
            app()->make($seederConfig['class'])->run();

            DB::commit();

            Log::info('[Visual Seeder] Executed successfully', [
                'seeder' => $seederConfig['name'],
                'company_id' => $request->company_id,
                'admin_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$seederConfig['name']} deployed successfully!",
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('[Visual Seeder] Execution Failed', [
                'seeder' => $seederConfig['name'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // 👈 ADD THIS
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(), // 👈 show real error for debugging
            ], 500);
        }
    }
}
