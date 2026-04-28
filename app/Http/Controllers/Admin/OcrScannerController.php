<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OcrScan;
use App\Services\OcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * OcrScannerController
 * --------------------
 * Handles the standalone OCR Scanner module.
 *
 * Routes (all gated by module:ocr_scanner middleware):
 *   GET  /ocr-scanner              → scan page (camera + upload UI)
 *   POST /ocr-scanner/process      → receive image, run OCR, return JSON
 *   POST /ocr-scanner/save         → save confirmed edited data to ocr_scans
 *   GET  /ocr-scanner/history      → history / previous scans list
 *   GET  /ocr-scanner/{scan}       → view single scan detail
 *   DELETE /ocr-scanner/{scan}     → soft-delete (archive) a scan
 */
class OcrScannerController extends Controller
{
    public function __construct(protected OcrService $ocrService) {}

    // ════════════════════════════════════════════════════════════════════
    //  SCAN PAGE  (GET /ocr-scanner)
    // ════════════════════════════════════════════════════════════════════

    public function index(): View
    {
        return view('admin.ocr-scanner.scan');
    }

    // ════════════════════════════════════════════════════════════════════
    //  PROCESS IMAGE  (POST /ocr-scanner/process)
    //  Called via AJAX. Returns JSON.
    // ════════════════════════════════════════════════════════════════════

    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'image'     => ['required', 'file', 'mimes:jpeg,jpg,png,webp', 'max:1024'], // 1 MB
            'scan_type' => ['nullable', 'string', 'in:business_card,invoice,receipt,general'],
        ]);

        $file     = $request->file('image');
        $scanType = $request->input('scan_type', 'business_card');
        $user     = auth()->user();

        try {
            // 1. Run OCR
            $result = $this->ocrService->scan($file, $scanType);

            if (! $result['success']) {
                return response()->json(['success' => false, 'message' => $result['error']], 422);
            }

            // 2. Optionally store image (optional — saves storage costs for free tier)
            $imagePath = null;
            if ($request->boolean('store_image', true)) {
                $imagePath = $this->ocrService->storeImage($file, $user->company_id);
            }

            // 3. Create a pending OCR scan record
            $scan = OcrScan::create([
                'company_id'        => $user->company_id,
                'user_id'           => $user->id,
                'scan_type'         => $scanType,
                'original_filename' => $file->getClientOriginalName(),
                'image_path'        => $imagePath,
                'raw_ocr_text'      => $result['raw_text'],
                'extracted_data'    => $result['extracted_data'],
                'status'            => 'completed',
                'ocr_engine'        => 'OCRSpace',
            ]);

            return response()->json([
                'success'        => true,
                'scan_id'        => $scan->id,
                'raw_text'       => $result['raw_text'],
                'extracted_data' => $result['extracted_data'],
                'image_url'      => $scan->image_url,
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error during OCR processing.',
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════════════════════
    //  SAVE CONFIRMED DATA  (POST /ocr-scanner/save)
    //  Called after the user reviews & edits extracted fields.
    // ════════════════════════════════════════════════════════════════════

    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'scan_id'      => ['required', 'integer', 'exists:ocr_scans,id'],
            'edited_data'  => ['required', 'array'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $user = auth()->user();

        $scan = OcrScan::where('id', $request->scan_id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $scan->update([
            'edited_data' => $request->edited_data,
            'notes'       => $request->notes,
            'status'      => 'saved',
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Scan saved successfully.',
            'scan_id'  => $scan->id,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    //  HISTORY  (GET /ocr-scanner/history)
    // ════════════════════════════════════════════════════════════════════

    public function history(Request $request): View
    {
        $user  = auth()->user();
        $query = OcrScan::forCompany($user->company_id)
            ->active()
            ->latest();

        if ($request->filled('scan_type')) {
            $query->ofType($request->scan_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('raw_ocr_text', 'like', "%{$search}%")
                  ->orWhere('extracted_data', 'like', "%{$search}%")
                  ->orWhere('edited_data', 'like', "%{$search}%");
            });
        }

        $scans = $query->paginate(20)->withQueryString();

        return view('admin.ocr-scanner.history', compact('scans'));
    }

    // ════════════════════════════════════════════════════════════════════
    //  SHOW SINGLE  (GET /ocr-scanner/{scan})
    // ════════════════════════════════════════════════════════════════════

    public function show(int $id): JsonResponse
    {
        $user = auth()->user();

        $scan = OcrScan::where('id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'scan'    => [
                'id'             => $scan->id,
                'scan_type'      => $scan->scan_type,
                'raw_text'       => $scan->raw_ocr_text,
                'extracted_data' => $scan->extracted_data,
                'edited_data'    => $scan->edited_data,
                'final_data'     => $scan->final_data,
                'image_url'      => $scan->image_url,
                'status'         => $scan->status,
                'notes'          => $scan->notes,
                'created_at'     => $scan->created_at->diffForHumans(),
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    //  ARCHIVE  (DELETE /ocr-scanner/{id})
    // ════════════════════════════════════════════════════════════════════

    public function destroy(int $id): JsonResponse
    {
        $user = auth()->user();

        $scan = OcrScan::where('id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $scan->update(['is_archived' => true]);

        return response()->json(['success' => true, 'message' => 'Scan archived.']);
    }
}
