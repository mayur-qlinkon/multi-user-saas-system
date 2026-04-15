<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\ImportLog;
use App\Services\Import\CategoryImporter;
use App\Services\Import\ProductImporter;
use App\Services\Import\SkuImporter;
use App\Services\Import\UnitImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BulkImportController extends Controller
{
    private const CHUNK_SIZE = 50;

    public function index()
    {
        $imports = Import::with('logs')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.bulk-import.index', compact('imports'));
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

    // ══════════════════════════════════════════════════════════════════════════
    //  Shared: Upload + Process + CSV helpers
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Generic chunk processor — resolves the correct importer by type.
     */
    private function handleProcess(Request $request, string $type): JsonResponse
    {
        $import = Import::findOrFail($request->input('import_id'));

        if ($import->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $companyId = $request->user()->company_id;
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
        $result = ['success' => 0, 'failed' => 0, 'skipped' => 0, 'created' => 0, 'updated' => 0];
        if (! empty($rowsToProcess)) {
            $result = $importer->processChunk($import, $rowsToProcess, $rows['start_row'], $companyId);
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

        // Skip to offset
        $currentRow = 0;
        while ($currentRow < $offset && fgetcsv($handle) !== false) {
            $currentRow++;
        }

        // Read chunk
        $data = [];
        $read = 0;
        while ($read < $limit && ($row = fgetcsv($handle)) !== false) {
            // Skip completely empty rows
            if (count(array_filter($row, fn ($v) => trim($v ?? '') !== '')) === 0) {
                $currentRow++;

                continue;
            }

            // Map columns to headers
            $mapped = [];
            foreach ($headers as $i => $header) {
                $mapped[$header] = $row[$i] ?? '';
            }

            // Attach 1-based CSV row number (header is row 1, first data row is row 2).
            // $currentRow is 0-based among data rows at the start of this iteration.
            $mapped['_row_number'] = $currentRow + 2;

            $data[] = $mapped;
            $currentRow++;
            $read++;
        }

        fclose($handle);

        // start_row is 1-based (row 1 = header, row 2 = first data row)
        return [
            'data' => $data,
            'start_row' => $offset + 2, // +1 for header, +1 for 1-based
        ];
    }

    private function resolveImporter(string $type): CategoryImporter|UnitImporter|ProductImporter|SkuImporter
    {
        return match ($type) {
            'categories' => new CategoryImporter,
            'units' => new UnitImporter,
            'products' => new ProductImporter,
            'skus' => new SkuImporter,
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
                'headers' => ['name', 'slug', 'category_slug', 'unit', 'product_type', 'description'],
                'rows' => [
                    ['Aloe Vera Plant', 'aloe-vera', 'indoor-plants', 'pcs', 'sellable', 'Medicinal indoor plant'],
                    ['Ceramic Pot Large', 'ceramic-pot-large', 'outdoor-plants', 'pcs', 'sellable', ''],
                    ['Garden Soil 5kg', 'garden-soil-5kg', 'outdoor-plants', 'kg', 'sellable', 'Premium potting mix'],
                ],
            ],
            'skus' => [
                'headers' => ['product_slug', 'sku', 'price', 'cost', 'mrp', 'barcode', 'stock_alert'],
                'rows' => [
                    ['aloe-vera', 'ALO-001', '299', '150', '349', '8901234567890', '5'],
                    ['aloe-vera', 'ALO-002', '499', '250', '599', '', '3'],
                    ['ceramic-pot-large', 'POT-LG-001', '899', '450', '999', '', '10'],
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
}
