<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemSettingController extends Controller
{
    protected ImageUploadService $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display the system settings dashboard.
     */
    public function index()
    {
        // Fetch all settings grouped by category for tabbed UI navigation
        $settingsGroups = SystemSetting::all()->groupBy('group');

        // Pluck a flat key-value array. This makes it incredibly easy to map 
        // values into your Blade form inputs using: $flatSettings['app_name'] ?? ''
        $flatSettings = SystemSetting::pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settingsGroups', 'flatSettings'));
    }

    /**
     * Bulk update system settings dynamically.
     */
    public function update(Request $request)
    {
        // 🌟 SAFETY 1: Define expected boolean keys. 
        // If they aren't in the request payload, we force them to false.
        $booleanKeys = [
            'maintenance_mode',
            'allow_public_registration',
            'force_email_verification',
            'enable_2fa'
        ];

        try {
            DB::beginTransaction();

            $data = $request->except(['_token', '_method']);

            // 1. Process Standard Text / Number Inputs
            foreach ($data as $key => $value) {
                // Skip file uploads and booleans in this loop
                if ($request->hasFile($key) || in_array($key, $booleanKeys)) {
                    continue;
                }

                $group = $this->determineGroup($key);
                SystemSetting::setSetting($key, $value, $group);
            }

            // 2. Process Booleans (Toggles / Checkboxes)
            foreach ($booleanKeys as $boolKey) {
                $group = $this->determineGroup($boolKey);
                $value = $request->has($boolKey) ? true : false;
                
                SystemSetting::setSetting($boolKey, $value, $group, 'boolean');
            }

            // 3. Process File Uploads (Logos, Favicons)
            $fileKeys = ['app_logo_light', 'app_logo_dark', 'app_favicon'];

            foreach ($fileKeys as $fileKey) {
                if ($request->hasFile($fileKey)) {
                    
                    // Safely delete the old asset from storage to save space
                    $oldPath = SystemSetting::getSetting($fileKey);
                    if ($oldPath && method_exists($this->imageService, 'delete')) {
                        $this->imageService->delete($oldPath);
                    }

                    // Upload new asset (Force WebP for lightning-fast loading)
                    $path = $this->imageService->upload($request->file($fileKey), 'settings', [
                        'format' => 'webp',
                        'quality' => 90,
                        'crop' => false // Don't crop logos!
                    ]);

                    SystemSetting::setSetting($fileKey, $path, 'branding', 'string');
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'System settings updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[SystemSettingController] Update Failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update settings. Please check system logs.'])
                ->withInput();
        }
    }

    /**
     * 🌟 SAFETY 2: Auto-categorize settings to keep the DB organized.
     * Maps input field names to their logical database group.
     */
    private function determineGroup(string $key): string
    {
        if (str_starts_with($key, 'stripe_') || str_starts_with($key, 'razorpay_') || str_starts_with($key, 'currency_')) {
            return 'billing';
        }
        if (str_starts_with($key, 'mail_') || str_starts_with($key, 'smtp_')) {
            return 'mail';
        }
        if (str_starts_with($key, 'aws_') || str_starts_with($key, 'pusher_')) {
            return 'cloud';
        }
        if (str_starts_with($key, 'app_logo') || str_starts_with($key, 'app_color') || $key === 'app_favicon') {
            return 'branding';
        }
        if (in_array($key, ['allow_public_registration', 'force_email_verification', 'enable_2fa'])) {
            return 'security';
        }

        return 'general';
    }
}