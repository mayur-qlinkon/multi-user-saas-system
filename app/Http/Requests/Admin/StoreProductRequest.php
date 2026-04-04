<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            // Core Product Data
            'name'                => ['required', 'string', 'max:255'],
            'hsn_code'            => ['nullable', 'string', 'max:50'],
            // Single category (legacy support)
            'category_id'   => ['nullable', Rule::exists('categories', 'id')->where('company_id', $companyId)],

            // Multi-category array (new)
            'categories'    => ['nullable', 'array'],
            'categories.*'  => ['integer', Rule::exists('categories', 'id')->where('company_id', $companyId)],
            'supplier_id'         => ['nullable', Rule::exists('suppliers', 'id')->where('company_id', $companyId)],
            'product_unit_id'     => ['required', Rule::exists('units', 'id')->where('company_id', $companyId)],
            'sale_unit_id'        => ['required', Rule::exists('units', 'id')->where('company_id', $companyId)],
            'purchase_unit_id'    => ['required', Rule::exists('units', 'id')->where('company_id', $companyId)],
            'quantity_limitation' => ['nullable', 'integer', 'min:1'],
            'type'                => ['required', 'in:single,variable'],
            'barcode_symbology'   => ['required', 'string'],
            'description'         => ['nullable', 'string'],
            'is_active'           => ['boolean'],

            'product_guide'               => ['nullable', 'array', 'max:15'], // Limit to 15 guide sections to prevent abuse
            'product_guide.*.title'       => ['required_with:product_guide', 'string', 'max:255'],
            'product_guide.*.description' => ['required_with:product_guide', 'string'],
            
            // 🌟 NEW: Dynamic Media Validation
            'media'               => ['nullable', 'array', 'max:10'],
            'media.*.type'        => ['required', 'in:image,youtube'],
            
            // If it's an image, require a file
            'media.*.file'        => ['exclude_if:media.*.type,youtube', 'required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            
            // If it's a youtube link, require a URL
            'media.*.url'         => ['exclude_if:media.*.type,image', 'required', 'url', 'max:255'],
            'media.*.sku_index'   => ['nullable', 'integer', 'min:0'],
            
            'primary_media_index' => ['nullable', 'integer'],

            // Single Product Validation (Ignored if variable)
            'single_sku'          => ['exclude_if:type,variable', 'required', 'string', Rule::unique('product_skus', 'sku')->where('company_id', $companyId)],
            'single_price'        => ['exclude_if:type,variable', 'required', 'numeric', 'min:0'],
            'single_cost'         => ['exclude_if:type,variable', 'required', 'numeric', 'min:0'],
            'single_mrp'          => ['exclude_if:type,variable', 'nullable', 'numeric', 'min:0'], // 🌟 NEW
            'single_stock_alert'  => ['nullable', 'integer', 'min:0'],
            'single_stock'        => ['nullable', 'array'], 
            'single_stock.*.warehouse_id'   => ['required_with:single_stock', 'exists:warehouses,id'],
            'single_stock.*.qty'            => ['required_with:single_stock', 'integer', 'min:1'],
            'single_order_tax'    => ['exclude_if:type,variable', 'nullable', 'numeric', 'min:0'], // 🌟 NEW
            'single_tax_type'     => ['exclude_if:type,variable', 'required', 'in:inclusive,exclusive'], // 🌟 NEW
            
            // Variable Product Validation
            'variations'          => ['exclude_if:type,single', 'required', 'array', 'min:1'],
            
            // 🌟 CHANGED: Made nullable. Uniqueness is only checked IF the user typed one manually.
            'variations.*.sku'    => [
                'nullable', 
                'string', 
                Rule::unique('product_skus', 'sku')->where('company_id', $companyId)
            ],
            
            'variations.*.price'  => ['required_with:variations', 'numeric', 'min:0'],
            'variations.*.cost'   => ['required_with:variations', 'numeric', 'min:0'],
            'variations.*.mrp'    => ['nullable', 'numeric', 'min:0'], // 🌟 NEW
            'variations.*.attrs'  => ['nullable', 'array'],            
            'variations.*.order_tax' => ['nullable', 'numeric', 'min:0'], // 🌟 NEW
            'variations.*.tax_type'  => ['required_with:variations', 'in:inclusive,exclusive'],
            'variations.*.stock_alert'          => ['nullable', 'integer', 'min:0'],
            'variations.*.stock'                => ['nullable', 'array'],
            'variations.*.stock.*.warehouse_id' => ['required_with:variations.*.stock', 'exists:warehouses,id'],
            'variations.*.stock.*.qty'          => ['required_with:variations.*.stock', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'single_sku.unique'       => 'This SKU is already in use in your inventory.',
            'variations.*.sku.unique' => 'One of your variation SKUs is already in use.',
            'variations.*.price.min'  => 'Variation prices cannot be negative.',
        ];
    }
}
