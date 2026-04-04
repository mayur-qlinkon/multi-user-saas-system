<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 🌟 Prevent updating if the invoice is cancelled
        $invoice = $this->route('invoice');
        if ($invoice && $invoice->status === 'cancelled') {
            return false;
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Transaction Header
            'store_id'              => ['required', 'exists:stores,id'],
            'warehouse_id'          => ['required', 'exists:warehouses,id'],
            'customer_id'           => ['nullable', 'exists:clients,id'],
            'customer_name'         => ['nullable', 'string', 'max:255'],
            'supply_state'          => ['required', 'string', 'max:100'],
            'invoice_date'          => ['required', 'date'],
            'due_date'              => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'notes'                 => ['nullable', 'string'],
            'terms_conditions'      => ['nullable', 'string'],

            // Global Financials
            'shipping_charge'       => ['nullable', 'numeric', 'min:0'],
            'global_discount_type' => ['nullable', 'in:fixed,percent,percentage'],
            'global_discount_value' => ['nullable', 'numeric', 'min:0'],
            
            // Payment Receipt Data
            'payment_method_id'     => ['nullable', 'exists:payment_methods,id'],
            'amount_paid'           => ['nullable', 'numeric', 'min:0'],

            // Items Array Validation
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.product_sku_id'    => ['required', 'exists:product_skus,id'],
            'items.*.unit_id'           => ['required', 'exists:units,id'],
            'items.*.quantity'          => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price'        => ['required', 'numeric', 'min:0'],
            'items.*.tax_percent'       => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_type'          => ['required', 'in:inclusive,exclusive'],
            'items.*.discount_type' => ['required', 'in:fixed,percent,percentage'],
            'items.*.discount_value'    => ['required', 'numeric', 'min:0'],
        ];
    }
}