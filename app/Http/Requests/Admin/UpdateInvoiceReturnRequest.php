<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateInvoiceReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'restock' => $this->has('restock') ? filter_var($this->restock, FILTER_VALIDATE_BOOLEAN) : true,
        ]);
    }

    public function rules(): array
    {
        // The rules for updating a return are virtually identical to storing it.
        // We ensure the basic integrity is maintained. We do NOT allow changing the 'invoice_id' 
        // on an update, so we exclude it from the update rules to prevent manipulation.

        return [
            'store_id'         => ['required', 'exists:stores,id'],
            'warehouse_id'     => ['required', 'exists:warehouses,id'],
            'customer_id'      => ['nullable', 'exists:clients,id'],
            'customer_name'    => ['nullable', 'string', 'max:255'],
            
            'return_date'      => ['required', 'date'],
            'return_type'      => ['required', 'in:refund,credit_note,replacement'],
            'return_reason'    => ['nullable', 'in:damaged,expired,wrong_item,customer_return,quality_issue,other'],
            'restock'          => ['boolean'],
            
            'supply_state'     => ['required', 'string', 'max:100'],
            'gst_treatment'    => ['required', 'in:registered,unregistered,composition,overseas,sez'],
            
            'discount_type'    => ['required', 'in:fixed,percentage,percent'],
            'discount_amount'  => ['nullable', 'numeric', 'min:0'],
            'shipping_charge'  => ['nullable', 'numeric', 'min:0'],
            'other_charges'    => ['nullable', 'numeric', 'min:0'],
            
            'notes'            => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],

            'items'                     => ['required', 'array', 'min:1'],
            'items.*.invoice_item_id'   => ['required', 'exists:invoice_items,id'],
            'items.*.product_id'        => ['nullable', 'exists:products,id'],
            'items.*.product_sku_id'    => ['nullable', 'exists:product_skus,id'],
            'items.*.unit_id'           => ['nullable', 'exists:units,id'],
            'items.*.product_name'      => ['required', 'string', 'max:255'],
            'items.*.sku_code'          => ['nullable', 'string', 'max:100'],
            'items.*.hsn_code'          => ['nullable', 'string', 'max:50'],
            
            'items.*.quantity'          => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price'        => ['required', 'numeric', 'min:0'],
            'items.*.tax_percent'       => ['required', 'numeric', 'min:0'],
            'items.*.tax_type'          => ['required', 'in:inclusive,exclusive'],
            'items.*.discount_type'     => ['required', 'in:fixed,percentage,percent'],
            'items.*.discount_amount'   => ['nullable', 'numeric', 'min:0'],
        ];
    }
}