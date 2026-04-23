<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We handle authorization via middleware/gates usually
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Transaction Header
            'store_id' => ['required', 'exists:stores,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'customer_id' => ['nullable', 'exists:clients,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'supply_state' => ['required', 'string', 'max:100'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'source' => ['required', 'in:pos,direct,online'],
            'status' => ['nullable', 'in:draft,confirmed'],
            'notes' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],

            // Global Financials
            'shipping_charge' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],

            // Payment Receipt Data
            'payment_method_id' => [
                'required_if:amount_paid,>0', // 🌟 Required only if amount_paid is added
                'nullable',
                'exists:payment_methods,id',
            ],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],

            // Challan Conversion (optional)
            'challan_id' => ['nullable', 'exists:challans,id'],

            // Items Array Validation
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_sku_id' => ['required', 'exists:product_skus,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_type' => ['required', 'in:inclusive,exclusive'],
            'items.*.discount_type' => ['required', 'in:fixed,percentage'],
            'items.*.discount_value' => ['required', 'numeric', 'min:0'],
            // Challan item reference (populated when converting from a challan)
            'items.*.challan_item_id' => ['nullable', 'exists:challan_items,id'],
            // Batch fields (populated when batch tracking is enabled)
            'items.*.batch_id' => ['nullable', 'exists:product_batches,id'],
            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Custom error messages for better UX
     */
    public function messages(): array
    {
        return [
            'items.required' => 'You must add at least one product to the invoice.',
            'items.*.unit_price.min' => 'The unit price cannot be negative.',
            'items.*.quantity.min' => 'The quantity must be greater than zero.',
            'customer_id.required_without' => 'Please select a customer or provide a guest name.',
        ];
    }
}
