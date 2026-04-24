<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExpenseService
{
    // ════════════════════════════════════════════════════
    //  STORE (Create new expense)
    // ════════════════════════════════════════════════════
    public function store(array $data, ?UploadedFile $receipt = null): Expense
    {
        return DB::transaction(function () use ($data, $receipt) {
            try {
                $companyId = Auth::user()->company_id;

                // 1. Enforce ownership and defaults
                $data['company_id'] = $companyId;
                $data['user_id'] = $data['user_id'] ?? Auth::id();

                // 2. Generate unique sequential expense number (e.g. EXP-202403-0001)
                $data['expense_number'] = $this->generateExpenseNumber($companyId);

                // 3. Process Tax Math (CGST / SGST / IGST)
                $this->calculateTaxes($data);

                // 4. Insert Record
                $expense = Expense::create($data);

                // 5. Handle Spatie Media Upload
                if ($receipt) {
                    $expense->addMedia($receipt)->toMediaCollection('receipts');
                }

                Log::info('[ExpenseService] Expense logged successfully', [
                    'expense_id' => $expense->id,
                    'expense_number' => $expense->expense_number,
                    'user_id' => Auth::id(),
                ]);

                return $expense;

            } catch (Throwable $e) {
                Log::error('[ExpenseService] Failed to store expense', [
                    'error' => $e->getMessage(),
                    'payload' => collect($data)->except(['attachment', 'receipt'])->toArray(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e; // Bubble up to controller to trigger 500/422 response
            }
        });
    }

    // ════════════════════════════════════════════════════
    //  UPDATE
    // ════════════════════════════════════════════════════
    public function update(Expense $expense, array $data, ?UploadedFile $receipt = null): Expense
    {
        return DB::transaction(function () use ($expense, $data, $receipt) {
            try {
                // If financial data is updated, recalculate the taxes
                if (isset($data['base_amount']) || isset($data['tax_percent']) || isset($data['tax_type'])) {
                    // Merge existing state with new data to ensure accurate math
                    $calculationData = array_merge($expense->toArray(), $data);
                    $this->calculateTaxes($calculationData);

                    // Pull the newly calculated tax fields back into the update payload
                    $data['cgst_amount'] = $calculationData['cgst_amount'];
                    $data['sgst_amount'] = $calculationData['sgst_amount'];
                    $data['igst_amount'] = $calculationData['igst_amount'];
                    $data['total_amount'] = $calculationData['total_amount'];
                    $data['round_off'] = $calculationData['round_off'];
                }

                $expense->update($data);

                // Handle receipt replacement
                if ($receipt) {
                    // Spatie makes replacing files easy. Clear the old collection, add the new one.
                    $expense->clearMediaCollection('receipts');
                    $expense->addMedia($receipt)->toMediaCollection('receipts');
                }

                Log::info('[ExpenseService] Expense updated', [
                    'expense_id' => $expense->id,
                    'updated_by' => Auth::id(),
                ]);

                return $expense->fresh();

            } catch (Throwable $e) {
                Log::error('[ExpenseService] Failed to update expense', [
                    'expense_id' => $expense->id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    // ════════════════════════════════════════════════════
    //  STATUS / WORKFLOW ENGINE
    // ════════════════════════════════════════════════════
    public function updateStatus(Expense $expense, string $newStatus): bool
    {
        $validStatuses = ['draft', 'pending_approval', 'approved', 'rejected', 'reimbursed'];

        if (! in_array($newStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid expense status: {$newStatus}");
        }

        $payload = ['status' => $newStatus];

        // Audit trail: Track exactly who approved it and when
        if ($newStatus === 'approved') {
            $payload['approved_by'] = Auth::id();
            $payload['approved_at'] = now();
        }

        $success = $expense->update($payload);

        if ($success) {
            Log::info('[ExpenseService] Status transitioned', [
                'expense_id' => $expense->id,
                'new_status' => $newStatus,
                'changed_by' => Auth::id(),
            ]);
        }

        return $success;
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE HELPERS (The heavy lifting)
    // ════════════════════════════════════════════════════

    /**
     * Pass-by-reference tax calculator.
     * Accurately splits Indian GST into CGST/SGST or IGST.
     */
    private function calculateTaxes(array &$data): void
    {
        $base = (float) ($data['base_amount'] ?? 0);
        $percent = (float) ($data['tax_percent'] ?? 0);
        $type = $data['tax_type'] ?? 'none';

        // Reset taxes
        $data['cgst_amount'] = 0.00;
        $data['sgst_amount'] = 0.00;
        $data['igst_amount'] = 0.00;

        if ($type === 'igst' && $percent > 0) {
            $data['igst_amount'] = round(($base * $percent) / 100, 2);
        } elseif ($type === 'cgst_sgst' && $percent > 0) {
            $halfTax = round(($base * ($percent / 2)) / 100, 2);
            $data['cgst_amount'] = $halfTax;
            $data['sgst_amount'] = $halfTax;
        }

        // Calculate exact total
        $exactTotal = $base + $data['cgst_amount'] + $data['sgst_amount'] + $data['igst_amount'];

        // Round off to nearest integer (Standard Indian Accounting Practice for final invoices)
        $roundedTotal = round($exactTotal);

        $data['total_amount'] = $roundedTotal;
        $data['round_off'] = round($roundedTotal - $exactTotal, 2);
    }

    /**
     * Generate a sequential expense number tightly scoped to the company.
     * Uses pessimistic locking (lockForUpdate) to prevent duplicate IDs during concurrent requests.
     */
    private function generateExpenseNumber(int $companyId): string
    {
        $prefix = 'EXP-'.date('Ym').'-';

        // 🌟 ADD withTrashed() HERE
        $latestExpense = Expense::withTrashed()
            ->where('company_id', $companyId)
            ->where('expense_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        if (! $latestExpense) {
            $sequence = 1;
        } else {
            // Extract the last 4 digits and increment
            $lastSequence = (int) substr($latestExpense->expense_number, -4);
            $sequence = $lastSequence + 1;
        }

        return $prefix.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
