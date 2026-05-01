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

            // Order Conversion (optional) — prefill invoice from an existing order
            'order_id' => ['nullable', 'exists:orders,id'],

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
     * Custom error messages for specific, complex rules.
     */
    public function messages(): array
    {
        return [
            // Header Messages
            'customer_id.required_without' => 'Please select an existing customer or enter a guest name.',
            'customer_name.required_without' => 'Please provide a customer name or select an existing customer.',
            'due_date.after_or_equal' => 'The due date cannot be earlier than the invoice date.',
            
            // Payment Messages
            'payment_method_id.required_if' => 'Please select a payment method since an amount is being paid.',
            
            // Item Array Messages
            'items.required' => 'You must add at least one product to create an invoice.',
            'items.min' => 'You must add at least one product to create an invoice.',
            
            // Specific Item Field Messages
            'items.*.quantity.min' => 'The quantity must be greater than zero.',
            'items.*.unit_price.min' => 'The unit price cannot be negative.',
            'items.*.tax_percent.max' => 'The tax percentage cannot exceed 100%.',
        ];
    }

    /**
     * Map technical field names to friendly names.
     * This makes Laravel's default error messages read perfectly.
     */
    public function attributes(): array
    {
        return [
            'store_id' => 'store',
            'warehouse_id' => 'warehouse',
            'customer_id' => 'customer',
            'payment_method_id' => 'payment method',
            
            // Array mappings
            'items.*.product_sku_id' => 'product',
            'items.*.unit_id' => 'unit',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'price',
            'items.*.tax_percent' => 'tax percentage',
            'items.*.tax_type' => 'tax type',
            'items.*.discount_type' => 'discount type',
            'items.*.discount_value' => 'discount value',
            'items.*.batch_number' => 'batch number',
        ];
    }
}