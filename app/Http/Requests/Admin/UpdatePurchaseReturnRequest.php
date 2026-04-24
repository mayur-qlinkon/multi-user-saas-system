<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (empty($this->store_id)) {
            $this->merge([
                'store_id' => session('store_id'),
            ]);
        }
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            // Note: We typically DO NOT allow changing the underlying 'purchase_id' or 'supplier_id' on an update
            // for data integrity, but we validate them if passed.
            'purchase_id' => ['required', Rule::exists('purchases', 'id')->where('company_id', $companyId)],
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('company_id', $companyId)],
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'store_id' => ['required', Rule::exists('stores', 'id')->where('company_id', $companyId)],

            'return_date' => ['required', 'date'],
            'supplier_credit_note_number' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:draft,returned'],

            'tax_type' => ['required', 'string', 'in:cgst_sgst,igst,none'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],

            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],

            // Allow ID for existing return items to update them rather than recreate
            'items.*.id' => ['nullable', Rule::exists('purchase_return_items', 'id')],
            'items.*.purchase_item_id' => ['required', Rule::exists('purchase_items', 'id')],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'items.*.product_sku_id' => ['required', Rule::exists('product_skus', 'id')],
            'items.*.unit_id' => ['required', Rule::exists('units', 'id')],

            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.tax_percent' => ['required', 'numeric', 'min:0'],

            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
            'items.*.return_reason' => ['nullable', 'string', 'in:damaged,wrong_item,excess_quantity,quality_issue,expired,other'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'You must select at least one product to return.',
            'items.*.quantity.min' => 'Return quantity must be greater than zero.',
            'items.*.purchase_item_id.*' => 'Invalid original purchase item reference.',
        ];
    }
}
