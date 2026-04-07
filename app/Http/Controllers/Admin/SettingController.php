<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Role;
use App\Models\Setting;
use App\Models\State;
use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class SettingController extends Controller
{
    // ── Fields stored in the companies table (not settings key-value) ──
    private const COMPANY_FIELDS = [
        'company_name', // → companies.name
        'company_email',
        'company_phone',
        'gst_number',
        'currency',
        'address',      // public address (storefront)
        'city',
        'zip_code',
        'state_id',
        'country',
    ];

    // ── File upload fields with their config ──
    private const FILE_FIELDS = [
        'logo' => ['path' => 'settings/logos',      'width' => 600,  'format' => 'webp', 'quality' => 90],
        'icon' => ['path' => 'settings/logos',      'width' => 800,  'format' => 'webp', 'quality' => 90],
        'favicon' => ['path' => 'settings/favicons',   'width' => 64,   'format' => 'webp', 'quality' => 90],
        'signature' => ['path' => 'settings/signatures', 'width' => 400,  'format' => 'webp', 'quality' => 85],
    ];

    // ── Boolean/checkbox fields (unchecked = not in request = false) ──
    private const BOOLEAN_FIELDS = [
        'storefront_online',
        'round_off',
        'enable_batch_tracking',
    ];

    // ── Fields to skip entirely (never save to DB) ──
    private const SKIP_FIELDS = [
        '_token',
        '_method',
    ];

    public function __construct(protected ImageUploadService $imageService) {}

    // ════════════════════════════════════════════════════
    //  INDEX
    // ════════════════════════════════════════════════════
    public function index(): View
    {
        try {
            $companyId = Auth::user()->company_id;
            $company = Company::find($companyId);
            $states = State::orderBy('name')->get();

            $roles = Role::where('company_id', $companyId)->orderBy('name')->get();

            $users = User::where('company_id', $companyId)
                ->with('roles:id,name')
                ->orderBy('name')
                ->get(['id', 'name']);

            $raw = get_setting('notify_new_order', null, $companyId);
            $notificationConfig = [
                'notify_new_order' => $raw
                    ? (json_decode($raw, true) ?: ['roles' => ['owner'], 'users' => []])
                    : ['roles' => ['owner'], 'users' => []],
            ];

            return view('admin.settings', compact('company', 'states', 'roles', 'users', 'notificationConfig'));

        } catch (\Throwable $e) {
            Log::error('[Settings] Failed to load settings page', [
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('admin.settings', [
                'company' => null,
                'states' => collect(),
                'roles' => collect(),
                'users' => collect(),
                'notificationConfig' => ['notify_new_order' => ['roles' => ['owner'], 'users' => []]],
            ]);
        }
    }

    // ════════════════════════════════════════════════════
    //  UPDATE
    // ════════════════════════════════════════════════════
    public function update(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id;

        // ── Basic validation ──
        $request->validate([
            'gst_number' => 'nullable|string|max:15',
            'pan_number' => 'nullable|string|max:10',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|digits:10',
            'upi_id' => 'nullable|string|max:100',
            'invoice_start_number' => 'nullable|integer|min:1',
            'ifsc' => 'nullable|string|max:11',
            'logo' => 'nullable|file|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'icon' => 'nullable|file|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'favicon' => 'nullable|file|image|mimes:png,ico,svg,webp|max:512',
            'signature' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:1024',
        ]);

        DB::beginTransaction();

        try {
            $company = Company::findOrFail($companyId);
            $allInput = $request->except(self::SKIP_FIELDS);
            $companyData = [];
            $settingsData = [];

            // ── Ensure boolean fields default to 0 when unchecked ──
            foreach (self::BOOLEAN_FIELDS as $boolField) {
                $allInput[$boolField] = $request->has($boolField) ? 1 : 0;
            }

            foreach ($allInput as $key => $value) {

                // ── Handle file uploads ──
                if ($request->hasFile($key) && isset(self::FILE_FIELDS[$key])) {
                    try {
                        $config = self::FILE_FIELDS[$key];
                        $oldValue = Setting::where('company_id', $companyId)
                            ->where('key', $key)
                            ->value('value');

                        $value = $this->imageService->upload(
                            file: $request->file($key),
                            path: $config['path'],
                            options: [
                                'old_file' => $oldValue,
                                'width' => $config['width'],
                                'format' => $config['format'],
                                'quality' => $config['quality'],
                            ]
                        );

                        Log::info("[Settings] File uploaded for key '{$key}'", [
                            'company_id' => $companyId,
                            'path' => $value,
                        ]);

                    } catch (\Throwable $e) {
                        Log::error("[Settings] File upload failed for key '{$key}'", [
                            'company_id' => $companyId,
                            'error' => $e->getMessage(),
                        ]);

                        // Skip this field — don't overwrite existing file with null
                        continue;
                    }
                }

                // ── Route to company table or settings table ──
                if (in_array($key, self::COMPANY_FIELDS)) {
                    // Map blade field name → company column name
                    $column = match ($key) {
                        'company_name' => 'name',
                        'company_email' => 'email',
                        'company_phone' => 'phone',
                        default => $key,
                    };
                    $companyData[$column] = $value ?: null;
                } else {
                    // Everything else → settings key-value table
                    $settingsData[$key] = $value;
                }
            }

            // ── Update company table ──
            if (! empty($companyData)) {
                $company->update($companyData);

                Log::info('[Settings] Company record updated', [
                    'company_id' => $companyId,
                    'fields' => array_keys($companyData),
                ]);
            }

            // ── Bulk upsert settings ──
            if (! empty($settingsData)) {
                $this->upsertSettings($companyId, $settingsData);
            }

            // ── Track last saved timestamp ──
            $this->upsertSettings($companyId, [
                '_last_saved' => now()->format('d M Y, h:i A'),
            ]);

            DB::commit();

            // ── Clear settings cache ──
            Cache::forget("company_settings_{$companyId}");

            Log::info('[Settings] Settings saved successfully', [
                'company_id' => $companyId,
                'user_id' => Auth::id(),
                'setting_count' => count($settingsData),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully!',
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('[Settings] Update failed', [
                'company_id' => $companyId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while saving. Please try again.',
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  UPDATE NOTIFICATION SETTINGS
    // ════════════════════════════════════════════════════
    public function updateNotifications(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id;

        // Add new event keys here as the system grows.
        $events = ['notify_new_order'];

        DB::transaction(function () use ($request, $companyId, $events) {
            foreach ($events as $eventKey) {
                $roles = array_values(array_filter((array) $request->input("{$eventKey}_roles", [])));
                $users = array_values(array_map('intval', array_filter((array) $request->input("{$eventKey}_users", []))));

                Setting::updateOrCreate(
                    ['company_id' => $companyId, 'key' => $eventKey],
                    [
                        'value' => json_encode(['roles' => $roles, 'users' => $users]),
                        'group' => 'notifications',
                        'type' => 'json',
                    ]
                );
            }
        });

        Cache::forget("company_settings_{$companyId}");

        return response()->json(['success' => true, 'message' => 'Notification preferences saved.']);
    }

    // ════════════════════════════════════════════════════
    //  CLEAR CACHE
    // ════════════════════════════════════════════════════
    public function clearCache(): JsonResponse
    {
        $companyId = Auth::user()->company_id;

        try {
            // Clear company-specific settings cache
            Cache::forget("company_settings_{$companyId}");

            // Clear Laravel view + config cache if needed
            // \Artisan::call('view:clear');
            // \Artisan::call('config:clear');

            Log::info('[Settings] Cache cleared', [
                'company_id' => $companyId,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cache purged! Changes are now live.',
            ]);

        } catch (\Throwable $e) {
            Log::error('[Settings] Cache clear failed', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache. Please try again.',
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ════════════════════════════════════════════════════

    /**
     * Bulk upsert settings rows — one query per key to respect updateOrCreate logic.
     * For very large sets consider chunked inserts, but settings are small.
     */
    private function upsertSettings(int $companyId, array $data): void
    {
        foreach ($data as $key => $value) {
            // Don't save null/empty for non-boolean fields to avoid wiping existing values
            if ($value === null || $value === '') {
                // Only wipe if key already exists (user intentionally cleared it)
                Setting::where('company_id', $companyId)
                    ->where('key', $key)
                    ->update(['value' => null]);

                continue;
            }

            Setting::updateOrCreate(
                ['company_id' => $companyId, 'key' => $key],
                ['value' => $value]
            );
        }
    }

    // ════════════════════════════════════════════════════
    //  RESET ALL SETTINGS
    // ════════════════════════════════════════════════════
    public function resetAll(): JsonResponse
    {
        $companyId = Auth::user()->company_id;

        try {
            // Wipe all key-value settings for this company
            Setting::where('company_id', $companyId)->delete();

            // Re-seed default settings
            $defaults = [
                'primary_color' => '#008a62',
                'primary_hover_color' => '#007050',
                'storefront_online' => '1',
                'enable_batch_tracking' => '0',
                'invoice_prefix' => 'INV-',
                'quotation_prefix' => 'QTN-',
                'invoice_start_number' => '1',
                'default_tax_type' => 'cgst_sgst',
                'payment_terms' => 'immediate',
                'round_off' => '1',
                'currency' => 'INR',
                'fy_start' => 'april',
                'registration_type' => 'regular',
                '_last_saved' => now()->format('d M Y, h:i A'),
            ];

            $this->upsertSettings($companyId, $defaults);

            // Clear cache
            Cache::forget("company_settings_{$companyId}");

            Log::warning('[Settings] All settings reset to defaults', [
                'company_id' => $companyId,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All settings reset to factory defaults.',
            ]);

        } catch (\Throwable $e) {
            Log::error('[Settings] Reset failed', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Reset failed. Please try again.',
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  SETTINGS AUDIT TRAIL
    // ════════════════════════════════════════════════════
    public function auditTrail(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        try {
            $logs = Activity::with('causer')
                ->where('subject_type', Setting::class)
                ->whereHasMorph('subject', [Setting::class], function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->latest()
                ->paginate(30);

            return view('admin.audit-logs.audit', compact('logs'));

        } catch (\Throwable $e) {
            Log::error('[Settings] Audit trail load failed', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            return view('admin.audit-logs.audit', ['logs' => new LengthAwarePaginator([], 0, 30)]);
        }
    }
}
