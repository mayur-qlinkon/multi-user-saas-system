<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreInvoiceReturnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert checkbox/toggle values to strict booleans
        $this->merge([
            'restock' => $this->has('restock') ? filter_var($this->restock, FILTER_VALIDATE_BOOLEAN) : true,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // 🌟 Core Linkage
            'invoice_id' => ['required', 'exists:invoices,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'customer_id' => ['nullable', 'exists:clients,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],

            // 🌟 Return Specifics
            'return_date' => ['required', 'date'],
            'return_type' => ['required', 'in:refund,credit_note,replacement'],
            'return_reason' => ['nullable', 'in:damaged,expired,wrong_item,customer_return,quality_issue,other'],
            'restock' => ['boolean'],

            // 🌟 GST & Taxes
            'supply_state' => ['required', 'string', 'max:100'],
            'gst_treatment' => ['required', 'in:registered,unregistered,composition,overseas,sez'],
            'currency_code' => ['nullable', 'string', 'max:3'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0.0001'],

            // 🌟 Global Financials
            'discount_type' => ['required', 'in:fixed,percentage,percent'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_charge' => ['nullable', 'numeric', 'min:0'],
            'other_charges' => ['nullable', 'numeric', 'min:0'],

            // 🌟 Notes
            'notes' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],

            // 🌟 The Returned Items Array
            'items' => ['required', 'array', 'min:1'],
            'items.*.invoice_item_id' => ['required', 'exists:invoice_items,id'], // Must link to original line
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_sku_id' => ['nullable', 'exists:product_skus,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.sku_code' => ['nullable', 'string', 'max:100'],
            'items.*.hsn_code' => ['nullable', 'string', 'max:50'],

            // 🌟 Item Math
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'], // The QTY being returned
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_percent' => ['required', 'numeric', 'min:0'],
            'items.*.tax_type' => ['required', 'in:inclusive,exclusive'],
            'items.*.discount_type' => ['required', 'in:fixed,percentage,percent'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'You must select at least one item to return.',
            'items.*.quantity.min' => 'Return quantity must be greater than zero.',
            'invoice_id.required' => 'A valid original invoice must be referenced.',
        ];
    }
}
