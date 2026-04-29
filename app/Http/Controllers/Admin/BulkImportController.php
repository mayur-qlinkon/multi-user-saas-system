<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductImageGuideExport;
use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\ImportLog;
use App\Models\Product;
use App\Services\Import\CategoryImporter;
use App\Services\Import\ClientImporter;
use App\Services\Import\ProductImageImporter;
use App\Services\Import\ProductImporter;
use App\Services\Import\SkuImporter;
use App\Services\Import\SupplierImporter;
use App\Services\Import\UnitImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BulkImportController extends Controller
{
    private const CHUNK_SIZE = 50;

    public function index()
    {
        $imports = Import::with('logs')
            ->latest()
            ->limit(20)
            ->get();

        $companyId = Auth::user()->company_id;
        $productCount = Product::withoutGlobalScopes()->where('company_id', $companyId)->count();
        $plan = Auth::user()->company->subscription?->plan;
        $productLimit = $plan?->product_limit; // null = no plan / unlimited
        $productLimitReached = $productLimit !== null && $productCount >= $productLimit;

        return view('admin.bulk-import.index', compact('imports', 'productCount', 'productLimit', 'productLimitReached'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  Per-entity upload + process endpoints (thin wrappers)
    // ══════════════════════════════════════════════════════════════════════════

    public function uploadCategories(Request $request): JsonResponse
    {
        return $this->handleUpload($request, 'categories');
    }

    public function processCategories(Request $request): JsonResponse
    {
        return $this->handleProcess($request, 'categories');
    }

    public function uploadUnits(Request $request): JsonResponse
    {
        return $this->handleUpload($request, 'units');
    }

    public function processUnits(Request $request): JsonResponse
    {
        return $this->handleProcess($request, 'units');
    }

    public function uploadProducts(Request $request): JsonResponse
    {
        return $this->handleUpload($request, 'products');
    }

    public function processProducts(Request $request): JsonResponse
    {
        return $this->handleProcess($request, 'products');
    }

    public function uploadSkus(Request $request): JsonResponse
    {
        return $this->handleUpload($request, 'skus');
    }

    public function processSkus(Request $request): JsonResponse
    {
        return $this->handleProcess($request, 'skus');
    }

    public function uploadClients(Request $request): JsonResponse
    {
        return $this->handleUpload($request, 'clients');
    }

    public function processClients(Request $request): JsonResponse
    {
        return $this->handleProcess($request, 'clients');
    }

    public function uploadSuppliers(Request $request): JsonResponse
    {
        return $this->handleUpload($request, 'suppliers');
    }

    public function processSuppliers(Request $request): JsonResponse
    {
        return $this->handleProcess($request, 'suppliers');
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  Product Images — ZIP upload flow (non-CSV)
    // ══════════════════════════════════════════════════════════════════════════

    public function uploadProductImages(Request $request, ProductImageImporter $importer): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:zip', 'mimetypes:application/zip,application/x-zip-compressed,multipart/x-zip', 'max:20480'],
            'import_mode' => ['nullable', 'in:create_only,update_only,create_or_update'],
        ]);

        $importMode = $request->input('import_mode', 'create_or_update');

        $file = $request->file('file');
        $storedPath = $file->store('imports/product-images', 'local');
        $absoluteZip = Storage::disk('local')->path($storedPath);

        $result = $importer->extractZip($absoluteZip);

        if (! $result['valid']) {
            // Cleanup uploaded ZIP on rejection
            Storage::disk('local')->delete($storedPath);

            return response()->json(['error' => $result['message']], 422);
        }

        $import = Import::create([
            'user_id' => $request->user()->id,
            'type' => 'product_images',
            'file_path' => $storedPath,
            'temp_path' => $result['temp_path'],
            'total_rows' => $result['total'],
            'status' => 'pending',
            'duplicate_mode' => 'skip',
            'import_mode' => $importMode,
            'is_dry_run' => false,
        ]);

        return response()->json([
            'import_id' => $import->id,
            'total_rows' => $result['total'],
            'import_mode' => $importMode,
            'is_dry_run' => false,
            'message' => "ZIP extracted. {$result['total']} image(s) ready to import.",
        ]);
    }

    public function processProductImages(Request $request, ProductImageImporter $importer): JsonResponse
    {
        $import = Import::find($request->input('import_id'));

        if (! $import) {
            return response()->json(['error' => 'Import session not found. Please start a new import.'], 404);
        }

        if ($import->company_id !== $request->user()->company_id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if ($import->type !== 'product_images') {
            return response()->json(['error' => 'Invalid import type for this endpoint.'], 422);
        }

        $tempDir = $import->temp_path;
        if (! $tempDir || ! is_dir($tempDir)) {
            $import->markFailed();

            return response()->json(['error' => 'Temporary extraction directory missing. Please re-upload the ZIP.'], 422);
        }

        $companyId = $request->user()->company_id;
        $offset = (int) $request->input('offset', 0);

        if ($offset === 0 && $import->isPending()) {
            $import->markProcessing();
        }

        $allFiles = $importer->listFiles($tempDir);
        $chunk = array_slice($allFiles, $offset, self::CHUNK_SIZE);

        if (empty($chunk)) {
            $import->markCompleted();
            $importer->cleanup($tempDir);

            return response()->json([
                'done' => true,
                'processed' => $import->processed_rows,
                'success' => $import->success_rows,
                'created' => $import->created_rows,
                'updated' => $import->updated_rows,
                'failed' => $import->failed_rows,
                'skipped' => $import->skipped_rows,
                'total' => $import->total_rows,
                'is_dry_run' => false,
            ]);
        }

        $result = $importer->processChunk($import, $tempDir, $chunk, $companyId);

        $chunkCount = count($chunk);
        $import->increment('processed_rows', $chunkCount);
        $import->increment('success_rows', $result['success']);
        $import->increment('failed_rows', $result['failed']);
        if (! empty($result['created'])) {
            $import->increment('created_rows', $result['created']);
        }
        if (! empty($result['updated'])) {
            $import->increment('updated_rows', $result['updated']);
        }
        if (! empty($result['skipped'])) {
            $import->increment('skipped_rows', $result['skipped']);
        }

        $nextOffset = $offset + self::CHUNK_SIZE;
        $done = $nextOffset >= $import->total_rows;

        if ($done) {
            $import->markCompleted();
            // Cleanup temp dir only after final chunk
            $importer->cleanup($tempDir);
            // Also remove the uploaded ZIP — we no longer need it
            if ($import->file_path && Storage::disk('local')->exists($import->file_path)) {
                Storage::disk('local')->delete($import->file_path);
            }
        }

        return response()->json([
            'done' => $done,
            'next_offset' => $done ? null : $nextOffset,
            'processed' => $import->processed_rows,
            'success' => $import->success_rows,
            'created' => $import->created_rows,
            'updated' => $import->updated_rows,
            'failed' => $import->failed_rows,
            'skipped' => $import->skipped_rows,
            'total' => $import->total_rows,
            'is_dry_run' => false,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  Shared: Upload + Process + CSV helpers
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Generic chunk processor — resolves the correct importer by type.
     */
    private function handleProcess(Request $request, string $type): JsonResponse
    {
        $import = Import::find($request->input('import_id'));

        if (! $import) {
            return response()->json(['error' => 'Import session not found. Please start a new import.'], 404);
        }

        if ($import->company_id !== $request->user()->company_id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $companyId = $request->user()->company_id;
        $storeId = session('store_id') ?? $request->user()->store_id; // Extracted safely in the HTTP context
        $offset = (int) $request->input('offset', 0);

        $filePath = Storage::disk('local')->path($import->file_path);
        if (! file_exists($filePath)) {
            return response()->json(['error' => 'Import file not found.'], 404);
        }

        // Mark as processing on first chunk
        if ($offset === 0 && $import->isPending()) {
            $import->markProcessing();
        }

        $rows = $this->readCsvChunk($filePath, $offset, self::CHUNK_SIZE);

        if (empty($rows['data'])) {
            $import->markCompleted();

            return response()->json([
                'done' => true,
                'processed' => $import->processed_rows,
                'success' => $import->success_rows,
                'created' => $import->created_rows,
                'updated' => $import->updated_rows,
                'failed' => $import->failed_rows,
                'skipped' => $import->skipped_rows,
                'total' => $import->total_rows,
                'is_dry_run' => (bool) $import->is_dry_run,
            ]);
        }

        // Apply duplicate-detection metadata: silently skip or log-error duplicates
        // before sending remaining rows to the importer.
        $meta = $import->duplicate_meta ?? [];
        $skipSet = array_flip($meta['skip_rows'] ?? []);
        $errorSet = array_flip($meta['error_rows'] ?? []);

        $rowsToProcess = [];
        $skippedInChunk = 0;
        $duplicateErrorsInChunk = 0;

        foreach ($rows['data'] as $row) {
            $rowNumber = (int) ($row['_row_number'] ?? 0);

            if (isset($skipSet[$rowNumber])) {
                $skippedInChunk++;

                continue;
            }

            if (isset($errorSet[$rowNumber])) {
                ImportLog::create([
                    'import_id' => $import->id,
                    'row_number' => $rowNumber,
                    'row_data' => $this->stripInternalKeys($row),
                    'error_message' => 'Duplicate row in file (duplicate_mode = error).',
                ]);
                $duplicateErrorsInChunk++;

                continue;
            }

            $rowsToProcess[] = $row;
        }

        $importer = $this->resolveImporter($type);
        $result = ['success' => 0, 'failed' => 0, 'skipped' => 0, 'created' => 0, 'updated' => 0, 'limit_skipped' => 0];

        if (! empty($rowsToProcess)) {
            if ($type === 'products') {
                // Compute how many new product slots remain under the tenant's plan.
                // For real runs the DB count grows naturally each chunk.
                // For dry runs nothing is written, so we offset by already-simulated creates.
                $plan = $request->user()->company->subscription?->plan;
                $productLimit = $plan?->product_limit ?? PHP_INT_MAX;
                $existingCount = Product::withoutGlobalScopes()->where('company_id', $companyId)->count();
                $dryRunOffset = $import->is_dry_run ? ($import->created_rows ?? 0) : 0;
                $remainingSlots = max(0, $productLimit - $existingCount - $dryRunOffset);

                $result = $importer->processChunk($import, $rowsToProcess, $rows['start_row'], $companyId, $remainingSlots);
            } elseif ($type === 'skus') {
                // Pass the extracted store_id to the SkuImporter
                $result = $importer->processChunk($import, $rowsToProcess, $rows['start_row'], $companyId, $storeId);
            } else {
                $result = $importer->processChunk($import, $rowsToProcess, $rows['start_row'], $companyId);
            }
        }

        $chunkCount = count($rows['data']);
        $totalSkipped = $skippedInChunk + ($result['skipped'] ?? 0);
        $import->increment('processed_rows', $chunkCount);
        $import->increment('success_rows', $result['success']);
        $import->increment('failed_rows', $result['failed'] + $duplicateErrorsInChunk);
        if (! empty($result['created'])) {
            $import->increment('created_rows', $result['created']);
        }
        if (! empty($result['updated'])) {
            $import->increment('updated_rows', $result['updated']);
        }
        if ($totalSkipped > 0) {
            $import->increment('skipped_rows', $totalSkipped);
        }
        if (! empty($result['limit_skipped'])) {
            $import->increment('limit_skipped_rows', $result['limit_skipped']);
        }

        $nextOffset = $offset + self::CHUNK_SIZE;
        $done = $nextOffset >= $import->total_rows;

        if ($done) {
            $import->markCompleted();
        }

        return response()->json([
            'done' => $done,
            'next_offset' => $done ? null : $nextOffset,
            'processed' => $import->processed_rows,
            'success' => $import->success_rows,
            'created' => $import->created_rows,
            'updated' => $import->updated_rows,
            'failed' => $import->failed_rows,
            'skipped' => $import->skipped_rows,
            'limit_skipped' => $import->limit_skipped_rows,
            'total' => $import->total_rows,
            'is_dry_run' => (bool) $import->is_dry_run,
        ]);
    }

    /**
     * Strip keys prefixed with _ (internal metadata like _row_number) before persisting row_data.
     */
    private function stripInternalKeys(array $row): array
    {
        return array_filter($row, fn ($v, $k) => ! str_starts_with((string) $k, '_'), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Handle CSV upload, validate headers, create Import record.
     */
    private function handleUpload(Request $request, string $type): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'], // 10 MB
            'duplicate_mode' => ['nullable', 'in:skip,update,error'],
            'import_mode' => ['nullable', 'in:create_only,update_only,create_or_update'],
            'is_dry_run' => ['nullable'],
        ]);

        $duplicateMode = $request->input('duplicate_mode', 'skip');
        $importMode = $request->input('import_mode', 'create_or_update');
        $isDryRun = filter_var($request->input('is_dry_run', false), FILTER_VALIDATE_BOOLEAN);

        $file = $request->file('file');
        $path = $file->store("imports/{$type}", 'local');

        $fullPath = Storage::disk('local')->path($path);

        // Read and validate headers
        $handle = fopen($fullPath, 'r');
        if (! $handle) {
            Storage::disk('local')->delete($path);

            return response()->json(['error' => 'Could not read uploaded file.'], 422);
        }

        $headerRow = fgetcsv($handle);
        if (! $headerRow) {
            fclose($handle);
            Storage::disk('local')->delete($path);

            return response()->json(['error' => 'CSV file is empty.'], 422);
        }

        // Clean BOM and whitespace from headers
        $headers = array_map(fn ($h) => strtolower(trim(preg_replace('/\x{FEFF}/u', '', $h))), $headerRow);

        $importer = $this->resolveImporter($type);

        // Validate headers per importer type
        $headerCheck = $importer->validateHeaders($headers);
        if (! $headerCheck['valid']) {
            fclose($handle);
            Storage::disk('local')->delete($path);

            return response()->json(['error' => $headerCheck['message']], 422);
        }

        // Walk data rows: count total + build duplicate metadata based on selected mode.
        $totalRows = 0;
        $dataRowIndex = 0; // 0-based data row index
        $keyFirstSeen = [];        // unique_key => csv row_number of first occurrence
        $keyLastSeen = [];         // unique_key => csv row_number of last occurrence
        $duplicateOccurrences = []; // unique_key => [row_numbers of all occurrences in order]

        // Product-specific: track explicit slugs and name-only rows for limit enforcement.
        $productExplicitSlugs = []; // [canonicalSlug => true] for rows with an explicit slug column
        $productNoSlugNames = [];   // [slugifiedName => true] for rows without slug (always a create)

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($v) => trim($v ?? '') !== '')) === 0) {
                $dataRowIndex++;

                continue;
            }

            $totalRows++;
            $csvRowNumber = $dataRowIndex + 2; // +1 for header, +1 for 1-based

            $mapped = [];
            foreach ($headers as $i => $header) {
                $mapped[$header] = $row[$i] ?? '';
            }

            // Track product slugs for strict plan-limit check
            if ($type === 'products') {
                $rowSlug = trim($mapped['slug'] ?? '');
                if ($rowSlug !== '') {
                    $productExplicitSlugs[Str::slug($rowSlug)] = true;
                } else {
                    $rowName = trim($mapped['name'] ?? '');
                    if ($rowName !== '') {
                        $productNoSlugNames[Str::slug($rowName)] = true;
                    }
                }
            }

            $key = $importer->extractUniqueKey($mapped);
            if ($key !== null && $key !== '') {
                if (! isset($keyFirstSeen[$key])) {
                    $keyFirstSeen[$key] = $csvRowNumber;
                }
                $keyLastSeen[$key] = $csvRowNumber;
                $duplicateOccurrences[$key][] = $csvRowNumber;
            }

            $dataRowIndex++;
        }
        fclose($handle);

        if ($totalRows === 0) {
            Storage::disk('local')->delete($path);

            return response()->json(['error' => 'CSV has no data rows.'], 422);
        }

        // ── Strict product-limit enforcement (before any record is created) ────────
        if ($type === 'products' && $importMode !== 'update_only') {
            $plan = $request->user()->company->subscription?->plan;
            $productLimit = $plan?->product_limit; // null = unlimited

            if ($productLimit !== null) {
                $cId = $request->user()->company_id;
                $existingCount = Product::withoutGlobalScopes()->where('company_id', $cId)->count();

                // Explicit slugs: check which already exist in DB (updates, not creates)
                $existingExplicitCount = 0;
                if (! empty($productExplicitSlugs)) {
                    $existingExplicitCount = Product::withoutGlobalScopes()
                        ->where('company_id', $cId)
                        ->whereIn('slug', array_keys($productExplicitSlugs))
                        ->count();
                }

                // New = (explicit not in DB) + (no-slug rows → random suffix → always create)
                $incomingNewCount = (count($productExplicitSlugs) - $existingExplicitCount) + count($productNoSlugNames);
                $availableSlots = max(0, $productLimit - $existingCount);

                if ($incomingNewCount > $availableSlots) {
                    Storage::disk('local')->delete($path);

                    return response()->json([
                        'error' => "Product limit exceeded. Your plan allows {$productLimit} products. You have {$existingCount} existing and only {$availableSlots} slot(s) available, but this file would add {$incomingNewCount} new product(s).",
                        'limit_exceeded' => true,
                        'product_limit' => $productLimit,
                        'existing_count' => $existingCount,
                        'incoming_new_count' => $incomingNewCount,
                        'available_slots' => $availableSlots,
                    ], 422);
                }
            }
        }

        // Build skip_rows and error_rows from duplicate occurrences per mode.
        $skipRows = [];
        $errorRows = [];
        foreach ($duplicateOccurrences as $key => $occurrences) {
            if (count($occurrences) < 2) {
                continue;
            }

            if ($duplicateMode === 'skip') {
                // Keep first, skip the rest silently
                $skipRows = array_merge($skipRows, array_slice($occurrences, 1));
            } elseif ($duplicateMode === 'update') {
                // Keep last (most recent wins), skip earlier ones silently
                $skipRows = array_merge($skipRows, array_slice($occurrences, 0, -1));
            } else { // error
                // Keep first, log error for the rest
                $errorRows = array_merge($errorRows, array_slice($occurrences, 1));
            }
        }

        $duplicateMeta = [
            'skip_rows' => array_values(array_unique($skipRows)),
            'error_rows' => array_values(array_unique($errorRows)),
            'duplicate_groups' => count(array_filter($duplicateOccurrences, fn ($o) => count($o) > 1)),
        ];

        $import = Import::create([
            'user_id' => $request->user()->id,
            'type' => $type,
            'file_path' => $path,
            'total_rows' => $totalRows,
            'status' => 'pending',
            'duplicate_mode' => $duplicateMode,
            'duplicate_meta' => $duplicateMeta,
            'import_mode' => $importMode,
            'is_dry_run' => $isDryRun,
        ]);

        $dupCount = count($duplicateMeta['skip_rows']) + count($duplicateMeta['error_rows']);
        $message = "File uploaded. {$totalRows} rows ready to import.";
        if ($isDryRun) {
            $message = "Dry run ready. {$totalRows} rows will be validated without saving.";
        }
        if ($dupCount > 0) {
            $message .= " Detected {$dupCount} duplicate row(s) (mode: {$duplicateMode}).";
        }

        return response()->json([
            'import_id' => $import->id,
            'total_rows' => $totalRows,
            'duplicate_mode' => $duplicateMode,
            'duplicate_count' => $dupCount,
            'import_mode' => $importMode,
            'is_dry_run' => $isDryRun,
            'message' => $message,
        ]);
    }

    /**
     * Read a chunk of CSV rows starting at the given offset (0-based, data rows only, skips header).
     *
     * @return array{data: array, start_row: int}
     */
    private function readCsvChunk(string $filePath, int $offset, int $limit): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return ['data' => [], 'start_row' => 0];
        }

        // Read header row
        $headerRow = fgetcsv($handle);
        if (! $headerRow) {
            fclose($handle);

            return ['data' => [], 'start_row' => 0];
        }

        $headers = array_map(fn ($h) => strtolower(trim(preg_replace('/\x{FEFF}/u', '', $h))), $headerRow);

        // $fileRow  — every row read after the header (includes empty rows), used for _row_number.
        // $dataRow  — only non-empty rows, used to honour the data-row offset correctly.
        // Previously the skip loop advanced $currentRow for every file row (including empties),
        // which caused the offset to drift when the CSV contained blank lines.
        $fileRow = 0;
        $dataRow = 0;

        // Skip exactly $offset DATA rows
        while ($dataRow < $offset) {
            $row = fgetcsv($handle);
            if ($row === false) {
                break;
            }
            $fileRow++;
            if (count(array_filter($row, fn ($v) => trim($v ?? '') !== '')) > 0) {
                $dataRow++;
            }
        }

        // Read up to $limit DATA rows
        $data = [];
        $read = 0;
        while ($read < $limit && ($row = fgetcsv($handle)) !== false) {
            $fileRow++;

            // Skip completely empty rows (don't count toward the chunk limit)
            if (count(array_filter($row, fn ($v) => trim($v ?? '') !== '')) === 0) {
                continue;
            }

            // Map columns to headers
            $mapped = [];
            foreach ($headers as $i => $header) {
                $mapped[$header] = $row[$i] ?? '';
            }

            // 1-based row number: header = 1, so first data row = fileRow + 1.
            // Using the real file position keeps _row_number consistent with the
            // duplicate-detection row numbers calculated during handleUpload.
            $mapped['_row_number'] = $fileRow + 1;

            $data[] = $mapped;
            $read++;
        }

        fclose($handle);

        // start_row is 1-based (row 1 = header, row 2 = first data row)
        return [
            'data' => $data,
            'start_row' => $offset + 2, // +1 for header, +1 for 1-based
        ];
    }

    private function resolveImporter(string $type): CategoryImporter|UnitImporter|ProductImporter|SkuImporter|ClientImporter|SupplierImporter
    {
        return match ($type) {
            'categories' => new CategoryImporter,
            'units' => new UnitImporter,
            'products' => new ProductImporter,
            'skus' => new SkuImporter,
            'clients' => new ClientImporter,
            'suppliers' => new SupplierImporter,
            default => throw new \InvalidArgumentException("Unknown import type: {$type}"),
        };
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  Error report download
    // ══════════════════════════════════════════════════════════════════════════

    public function downloadErrors(Request $request, Import $import)
    {
        if ($import->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $logs = $import->logs()->orderBy('row_number')->get();

        if ($logs->isEmpty()) {
            return back()->with('info', 'No errors to download.');
        }

        $filename = "import-{$import->type}-errors-{$import->id}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // Header row: original columns + error column
            $firstRowData = $logs->first()->row_data ?? [];
            $csvHeaders = array_merge(array_keys($firstRowData), ['error_message']);
            fputcsv($handle, $csvHeaders);

            foreach ($logs as $log) {
                $rowData = $log->row_data ?? [];
                $rowData['error_message'] = $log->error_message;
                fputcsv($handle, $rowData);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  Sample CSV download
    // ══════════════════════════════════════════════════════════════════════════

    public function downloadSample(string $type)
    {
        $samples = [
            'categories' => [
                'headers' => ['name', 'slug', 'parent_slug'],
                'rows' => [
                    ['Indoor Plants', 'indoor-plants', ''],
                    ['Succulents', 'succulents', 'indoor-plants'],
                    ['Outdoor Plants', 'outdoor-plants', ''],
                ],
            ],
            'units' => [
                'headers' => ['name', 'short_name'],
                'rows' => [
                    ['Kilogram', 'kg'],
                    ['Piece', 'pcs'],
                    ['Litre', 'ltr'],
                    ['Meter', 'm'],
                    ['Box', 'box'],
                ],
            ],
            'products' => [
                'headers' => ['name', 'slug', 'category_slug', 'unit', 'product_type', 'description', 'product_guide'],
                'rows' => [
                    [
                        'Aloe Vera Plant',
                        'aloe-vera',
                        'indoor-plants',
                        'pcs',
                        'sellable',
                        'Medicinal indoor plant',
                        'Watering:Once every 2 weeks|Light:Bright, indirect sunlight|Care Level:Easy',
                    ],
                    [
                        'Ceramic Pot Large',
                        'ceramic-pot-large',
                        'outdoor-plants',
                        'pcs',
                        'sellable',
                        '',
                        'Material:Ceramic|Features:Includes drainage hole',
                    ],
                    [
                        'Garden Soil 5kg',
                        'garden-soil-5kg',
                        'outdoor-plants',
                        'kg',
                        'sellable',
                        'Premium potting mix',
                        '',
                    ],
                ],
            ],
            'skus' => [
                'headers' => ['product_slug', 'sku', 'price', 'cost', 'mrp', 'barcode', 'stock_alert', 'warehouse_name', 'stock_qty', 'attribute_1_name', 'attribute_1_value', 'attribute_2_name', 'attribute_2_value'],
                'rows' => [
                    ['aloe-vera', '', '299', '150', '349', '', '5', 'Main Warehouse', '50', 'Size', 'Small', 'Pot', 'Plastic'],
                    ['aloe-vera', '', '499', '250', '599', '', '3', 'Secondary Store', '25', 'Size', 'Large', 'Pot', 'Ceramic'],
                    ['aloe-vera', 'ALO-RED-M', '399', '200', '449', '', '4', '', '', 'Size', 'Medium', 'Pot', 'Clay'],
                ],
            ],
            'clients' => [
                'headers' => ['name', 'company_name', 'phone', 'email', 'gst_number', 'registration_type', 'address', 'city', 'state', 'zip_code', 'country', 'notes'],
                'rows' => [
                    ['Rahul Sharma', 'Sharma Nursery', '9876543210', 'rahul@example.com', '', 'unregistered', '12 Park Lane', 'Ahmedabad', 'Gujarat', '380001', 'India', 'Regular customer'],
                    ['Priya Patel', '', '9123456780', 'priya@example.com', '', 'unregistered', '', 'Surat', 'Gujarat', '', 'India', ''],
                    ['Green Thumb Pvt Ltd', 'Green Thumb Pvt Ltd', '02212345678', 'sales@greenthumb.in', '27AABCU9603R1ZM', 'regular', 'Plot 45, MIDC', 'Mumbai', 'Maharashtra', '400093', 'India', ''],
                ],
            ],
            'suppliers' => [
                'headers' => ['name', 'phone', 'email', 'gstin', 'pan', 'registration_type', 'address', 'city', 'state', 'pincode', 'credit_days', 'credit_limit', 'notes'],
                'rows' => [
                    ['Kisan Seeds Co.', '9012345678', 'orders@kisanseeds.in', '24AABCK1234N1ZP', 'AABCK1234N', 'regular', 'Shop 7, Market Road', 'Ahmedabad', 'Gujarat', '380004', '30', '50000', 'Seed supplier'],
                    ['Local Pot Maker', '9876501234', '', '', '', 'unregistered', '', 'Rajkot', 'Gujarat', '360001', '0', '0', 'Cash basis'],
                    ['Mumbai Fertilizers', '02266778899', 'info@mumfert.in', '27AABCM5678E1ZR', 'AABCM5678E', 'regular', 'Unit 12, Andheri East', 'Mumbai', 'Maharashtra', '400069', '15', '100000', ''],
                ],
            ],
        ];

        if (! isset($samples[$type])) {
            abort(404);
        }

        $sample = $samples[$type];
        $filename = "sample-{$type}.csv";

        $callback = function () use ($sample) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $sample['headers']);
            foreach ($sample['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Download a pre-filled Excel guide that lists every product slug with
     * example image filenames — helps non-technical users rename their photos
     * correctly before zipping and uploading.
     */
    public function downloadImageGuide(): BinaryFileResponse
    {
        $companyId = Auth::user()->company_id;

        $products = Product::with('category:id,name')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->orderBy('category_id')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'category_id']);

        $export = new ProductImageGuideExport($products);
        $filename = 'product-image-naming-guide-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download($export, $filename);
    }

     public function exportExistingData(string $type)
    {
        $companyId = Auth::user()->company_id;

        $exports = [
            'products' => [
                'filename' => 'existing-product-slugs.csv',
                'headers'  => ['Product Name', 'slug'],
                'query'    => Product::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->select(['name', 'slug'])
                    ->orderBy('name'),
                'map'      => function ($row) {
                    return [
                        $row->name ?? '',
                        $row->slug ?? '',
                    ];
                },
            ],

            'categories' => [
                'filename' => 'existing-category-slugs.csv',
                'headers'  => ['Category Name', 'slug'],
                'query'    => \App\Models\Category::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->select(['name', 'slug'])
                    ->orderBy('name'),
                'map'      => function ($row) {
                    return [
                        $row->name ?? '',
                        $row->slug ?? '',
                    ];
                },
            ],

            'units' => [
                'filename' => 'existing-unit-codes.csv',
                'headers'  => ['Unit Name', 'short_name'],
                'query'    => \App\Models\Unit::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->select(['name', 'short_name'])
                    ->orderBy('name'),
                'map'      => function ($row) {
                    return [
                        $row->name ?? '',
                        $row->short_name ?? '',
                    ];
                },
            ],

            'warehouses' => [
                'filename' => 'existing-warehouses.csv',
                'headers'  => ['Warehouse Name'],
                'query'    => \App\Models\Warehouse::where('company_id', $companyId)
                    ->select(['name'])
                    ->orderBy('name'),
                'map'      => function ($row) {
                    return [
                        $row->name ?? '',
                    ];
                },
            ],
        ];

        if (! isset($exports[$type])) {
            abort(404);
        }

        $export = $exports[$type];

        return response()->streamDownload(function () use ($export) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, $export['headers']);

            // Chunked export for production safety
            $export['query']->chunk(500, function ($rows) use ($handle, $export) {
                foreach ($rows as $row) {
                    fputcsv($handle, ($export['map'])($row));
                }
            });

            fclose($handle);
        }, $export['filename'], [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }
    public function exportAllData()
{
    $companyId = Auth::user()->company_id;

    $zip = new \ZipArchive();
    $fileName = 'existing-data.zip';
    $filePath = storage_path($fileName);

    if ($zip->open($filePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {

        // Products
        $products = Product::where('company_id', $companyId)
            ->select('name', 'slug')->get();

        $productsCsv = $this->arrayToCsv([
            ['Product Name', 'slug'],
            ...$products->map(fn($p) => [$p->name, $p->slug])->toArray()
        ]);

        $zip->addFromString('products.csv', $productsCsv);

        // Categories
        $categories = \App\Models\Category::where('company_id', $companyId)
            ->select('name', 'slug')->get();

        $categoriesCsv = $this->arrayToCsv([
            ['Category Name', 'slug'],
            ...$categories->map(fn($c) => [$c->name, $c->slug])->toArray()
        ]);

        $zip->addFromString('categories.csv', $categoriesCsv);

        // Units
        $units = \App\Models\Unit::where('company_id', $companyId)
            ->select('name', 'short_name')->get();

        $unitsCsv = $this->arrayToCsv([
            ['Unit Name', 'short_name'],
            ...$units->map(fn($u) => [$u->name, $u->short_name])->toArray()
        ]);

        $zip->addFromString('units.csv', $unitsCsv);

        // Warehouses
        $warehouses = \App\Models\Warehouse::where('company_id', $companyId)
            ->select('name')->get();

        $warehousesCsv = $this->arrayToCsv([
            ['Warehouse Name'],
            ...$warehouses->map(fn($w) => [$w->name])->toArray()
        ]);

        $zip->addFromString('warehouses.csv', $warehousesCsv);

        $zip->close();
    }

    return response()->download($filePath)->deleteFileAfterSend(true);
}
private function arrayToCsv(array $data)
{
    $handle = fopen('php://temp', 'r+');

    foreach ($data as $row) {
        fputcsv($handle, $row);
    }

    rewind($handle);
    $csv = stream_get_contents($handle);
    fclose($handle);

    return "\xEF\xBB\xBF" . $csv;
}
    
}
