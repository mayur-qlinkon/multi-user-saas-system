<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\Hrm\QrTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeLocationController extends Controller
{
    public function __construct(
        protected QrTokenService $qrTokenService
    ) {}

    public function index()
    {
        $stores = Store::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.hrm.office-locations.index', compact('stores'));
    }

    public function update(Request $request, Store $store)
    {
        $this->authorizeStore($store);

        $validated = $request->validate([
            'office_lat'        => ['nullable', 'numeric', 'between:-90,90'],
            'office_lng'        => ['nullable', 'numeric', 'between:-180,180'],
            'gps_radius_meters' => ['nullable', 'integer', 'min:10', 'max:5000'],
        ]);

        foreach (['office_lat', 'office_lng', 'gps_radius_meters'] as $field) {
            if (($validated[$field] ?? '') === '') {
                $validated[$field] = null;
            }
        }

        $store->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Location updated for {$store->name}.",
            'data'    => $store->fresh(),
        ]);
    }

    /**
     * Return the QR SVG (encodes a URL to the mobile scan page) for inline preview.
     */
    public function generateQr(Store $store)
    {
        $this->authorizeStore($store);

        $scanUrl = route('admin.hrm.attend', $store);
        $qrSvg   = $this->qrTokenService->generatePrintable($scanUrl);

        return response()->json([
            'success' => true,
            'data'    => [
                'qr_svg'   => $qrSvg,
                'scan_url' => $scanUrl,
                'store_id' => $store->id,
            ],
        ]);
    }

    /**
     * Open a printable poster page for this store.
     */
    public function poster(Store $store)
    {
        $this->authorizeStore($store);

        $scanUrl = route('admin.hrm.attend', $store);
        $qrSvg   = $this->qrTokenService->generatePrintable($scanUrl);

        return view('admin.hrm.attendance.poster', compact('store', 'qrSvg'));
    }

    protected function authorizeStore(Store $store): void
    {
        abort_if($store->company_id !== Auth::user()->company_id, 403);
    }
}
