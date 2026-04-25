<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * OcrService
 * ----------
 * Reusable OCR engine wrapper built around the OCR.space free API.
 *
 * Design goals:
 * ✅  Single place to swap the OCR provider later (AWS Textract, Google Vision …)
 * ✅  Client-side image compression keeps files under the 1 MB OCR.space limit
 * ✅  Structured field extraction is scan-type aware (business_card, invoice, receipt …)
 * ✅  Zero coupling to any model — callers decide what to persist
 *
 * Usage from a controller:
 *
 * $result = $this->ocrService->scan($request->file('image'), 'business_card');
 * // $result['raw_text']       — full OCR output string
 * // $result['extracted_data'] — parsed key/value fields
 * // $result['success']        — bool
 * // $result['error']          — string|null
 */
class OcrService
{    
    private const MAX_BYTES   = 900_000;              // stay under 1 MB hard limit

    // ═══════════════════════════════════════════════════════════════════════
    //  PUBLIC API
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Main entry point.
     *
     * @param  UploadedFile  $file
     * @param  string  $scanType   business_card | invoice | receipt | general
     * @return array{success:bool, raw_text:string|null, extracted_data:array, error:string|null}
     */
    public function scan(UploadedFile $file, string $scanType = 'business_card'): array
    {
        try {
            // 1. Validate file size (client should compress, but server double-checks)
            if ($file->getSize() > self::MAX_BYTES) {
                return $this->error('Image too large. Please use the in-browser compression before uploading (max 900 KB).');
            }

            // 2. Call OCR.space
            $rawText = $this->callOcrSpaceApi($file);

            if ($rawText === null) {
                return $this->error('OCR provider returned no text. Try a clearer image.');
            }

            // 3. Extract structured fields based on scan type
            $extractedData = $this->extractFields($rawText, $scanType);

            return [
                'success'        => true,
                'raw_text'       => $rawText,
                'extracted_data' => $extractedData,
                'error'          => null,
            ];

        } catch (Throwable $e) {
            Log::error('OcrService::scan failed', [
                'error'     => $e->getMessage(),
                'scan_type' => $scanType,
            ]);

            return $this->error('OCR processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Store an uploaded OCR image to disk.
     * Returns the relative storage path (e.g. ocr/2024/05/xyz.jpg).
     */
    public function storeImage(UploadedFile $file, int $companyId): string
    {
        $folder = 'ocr/' . $companyId . '/' . now()->format('Y/m');

        return $file->store($folder, 'public');
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  OCR PROVIDER
    // ═══════════════════════════════════════════════════════════════════════

    private function callOcrSpaceApi(UploadedFile $file): ?string
    {
        $apiKey   = config('ocr.api_key', 'helloworld');
        $endpoint = config('ocr.endpoint', 'https://api.ocr.space/parse/image');
        $language = config('ocr.language', 'eng');

        if (empty($apiKey) || $apiKey === 'helloworld') {
            $apiKey = 'helloworld';
            Log::warning('[OCR] Using default "helloworld" API key. OCR.space will rate limit quickly.');
        }

        $response = Http::timeout(30)
            ->attach('file', $file->getContent(), $file->getClientOriginalName())
            ->post($endpoint, [
                'apikey'                       => $apiKey,
                'language'                     => $language,
                'isOverlayRequired'            => 'false',
                'detectOrientation'            => 'true',
                'scale'                        => 'true',
                'OCREngine'                    => '2', // Engine 2 = more accurate
                'isCreateSearchablePdf'        => 'false',
                'isSearchablePdfHideTextLayer' => 'false',
            ]);

        if (! $response->successful()) {
            Log::warning('OCR.space HTTP error', ['status' => $response->status()]);

            return null;
        }

        $body = $response->json();

        // OCR.space error codes
        if (($body['OCRExitCode'] ?? 0) >= 4) {
            Log::warning('OCR.space returned error exit code', ['body' => $body]);

            return null;
        }

        $pages = $body['ParsedResults'] ?? [];
        if (empty($pages)) {
            return null;
        }

        // Merge text from all pages (usually 1 for a photo)
        return collect($pages)
            ->pluck('ParsedText')
            ->filter()
            ->implode("\n");
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  FIELD EXTRACTION  (extend per scan type as needed)
    // ═══════════════════════════════════════════════════════════════════════

    private function extractFields(string $text, string $scanType): array
    {
        return match ($scanType) {
            'business_card' => $this->extractBusinessCardFields($text),
            'invoice'       => $this->extractInvoiceFields($text),
            'receipt'       => $this->extractReceiptFields($text),
            default         => ['raw' => $text],
        };
    }

    // ── Business Card ────────────────────────────────────────────────────

    private function extractBusinessCardFields(string $text): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $lines = array_values($lines);

        return [
            'name'      => $this->extractName($lines),
            'company'   => $this->extractCompany($lines),
            'job_title' => $this->extractJobTitle($lines),
            'email'     => $this->extractEmail($text),
            'phone'     => $this->extractPhone($text),
            'website'   => $this->extractWebsite($text),
            'address'   => $this->extractAddress($lines),
        ];
    }

    // ── Invoice ──────────────────────────────────────────────────────────

    private function extractInvoiceFields(string $text): array
    {
        return [
            'invoice_number' => $this->extractPattern($text, '/(?:invoice\s*#?|inv\s*no\.?)\s*([A-Z0-9\-\/]+)/i'),
            'date'           => $this->extractDate($text),
            'total_amount'   => $this->extractAmount($text),
            'vendor_name'    => $this->extractName(array_values(array_filter(array_map('trim', explode("\n", $text))))),
            'email'          => $this->extractEmail($text),
            'phone'          => $this->extractPhone($text),
        ];
    }

    // ── Receipt ──────────────────────────────────────────────────────────

    private function extractReceiptFields(string $text): array
    {
        return [
            'merchant_name' => $this->extractName(array_values(array_filter(array_map('trim', explode("\n", $text))))),
            'date'          => $this->extractDate($text),
            'total_amount'  => $this->extractAmount($text),
            'phone'         => $this->extractPhone($text),
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  REGEX HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    private function extractEmail(string $text): ?string
    {
        preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $text, $m);

        return $m[0] ?? null;
    }

    private function extractPhone(string $text): ?string
    {
        // Matches: +91 98765 43210, (022) 1234-5678, 9876543210, etc.
        preg_match('/(?:\+?\d[\d\s\-().]{7,}\d)/', $text, $m);

        return isset($m[0]) ? preg_replace('/\s+/', ' ', trim($m[0])) : null;
    }

    private function extractWebsite(string $text): ?string
    {
        preg_match('/(?:https?:\/\/)?(?:www\.)?[a-zA-Z0-9\-]+\.[a-zA-Z]{2,}(?:\/\S*)?/', $text, $m);
        $url = $m[0] ?? null;

        // Exclude email domains
        if ($url && str_contains($url, '@')) {
            return null;
        }

        return $url;
    }

    private function extractDate(string $text): ?string
    {
        // DD/MM/YYYY, MM-DD-YYYY, Month DD YYYY variants
        preg_match('/\b(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}|\d{4}[\/\-]\d{2}[\/\-]\d{2}|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\.?\s+\d{1,2},?\s+\d{4})\b/i', $text, $m);

        return $m[0] ?? null;
    }

    private function extractAmount(string $text): ?string
    {
        // Matches: ₹1,234.56  $999  Rs. 500  1234.00
        preg_match('/(?:total|amount|grand total|net)[\s:]*(?:[₹$€£Rs\.]*\s*)(\d[\d,]*\.?\d*)/i', $text, $m);
        if (isset($m[1])) {
            return $m[1];
        }

        // Fallback: largest currency amount in text
        preg_match_all('/[₹$€£]?\s*(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)/', $text, $all);
        if (! empty($all[1])) {
            $amounts = array_map(fn ($v) => (float) str_replace(',', '', $v), $all[1]);
            arsort($amounts);

            return (string) reset($amounts);
        }

        return null;
    }

    private function extractPattern(string $text, string $pattern): ?string
    {
        preg_match($pattern, $text, $m);

        return $m[1] ?? null;
    }

    /**
     * Heuristic: The first non-URL, non-phone, non-email short line is likely a person/company name.
     */
    private function extractName(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (
                strlen($line) < 60
                && ! str_contains($line, '@')
                && ! preg_match('/\d{5,}/', $line)
                && ! preg_match('/^https?/i', $line)
                && preg_match('/[A-Za-z]{2,}/', $line)
            ) {
                return $line;
            }
        }

        return null;
    }

    private function extractCompany(array $lines): ?string
    {
        // Usually the 2nd short non-empty line after the name
        $candidates = array_slice($lines, 1, 4);

        foreach ($candidates as $line) {
            if (
                strlen($line) > 3
                && strlen($line) < 80
                && ! str_contains($line, '@')
                && ! preg_match('/\d{7,}/', $line)
                && preg_match('/[A-Za-z]{2,}/', $line)
            ) {
                return $line;
            }
        }

        return null;
    }

    private function extractJobTitle(array $lines): ?string
    {
        $titleKeywords = ['manager', 'director', 'ceo', 'cto', 'cfo', 'founder', 'engineer',
            'developer', 'designer', 'consultant', 'executive', 'officer',
            'head', 'lead', 'analyst', 'accountant', 'sales', 'hr', 'admin'];

        foreach ($lines as $line) {
            $lower = strtolower($line);
            foreach ($titleKeywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return $line;
                }
            }
        }

        return null;
    }

    private function extractAddress(array $lines): ?string
    {
        $addrKeywords = ['street', 'road', 'avenue', 'lane', 'nagar', 'sector', 'floor',
            'building', 'plot', 'block', 'phase', 'district', 'pin', 'zip',
            'city', 'state', 'india', 'gujarat'];

        foreach ($lines as $line) {
            $lower = strtolower($line);
            foreach ($addrKeywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return $line;
                }
            }
        }

        return null;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    private function error(string $message): array
    {
        return [
            'success'        => false,
            'raw_text'       => null,
            'extracted_data' => [],
            'error'          => $message,
        ];
    }
}