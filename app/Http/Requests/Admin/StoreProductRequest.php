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
            'category_id'   => ['nullable', Rule::exists('categories', 'id')->where('company_id', $companyId)],
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

            'product_guide'               => ['nullable', 'array', 'max:15'], 
            'product_guide.*.title'       => ['required_with:product_guide', 'string', 'max:255'],
            'product_guide.*.description' => ['required_with:product_guide', 'string'],
            
            // Dynamic Media Validation
            'media'               => ['nullable', 'array', 'max:10'],
            'media.*.type'        => ['required', 'in:image,youtube'],
            'media.*.file'        => ['exclude_if:media.*.type,youtube', 'required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'media.*.url'         => ['exclude_if:media.*.type,image', 'required', 'url', 'max:255'],
            'media.*.sku_index'   => ['nullable', 'integer', 'min:0'],
            'primary_media_index' => ['nullable', 'integer'],

            // ════════════════════════════════════════════════════
            // SINGLE PRODUCT VALIDATION
            // ════════════════════════════════════════════════════
            'single_sku'          => ['exclude_if:type,variable', 'required', 'string', Rule::unique('product_skus', 'sku')->where('company_id', $companyId)],
            
            // 🌟 NEW: Optional, unique barcode for single products
            'single_barcode'      => [
                'exclude_if:type,variable', 
                'nullable', 
                'string', 
                'max:255', 
                Rule::unique('product_skus', 'barcode')->where('company_id', $companyId)
            ],
            
            'single_price'        => ['exclude_if:type,variable', 'required', 'numeric', 'min:0'],
            'single_cost'         => ['exclude_if:type,variable', 'required', 'numeric', 'min:0'],
            'single_mrp'          => ['exclude_if:type,variable', 'nullable', 'numeric', 'min:0'], 
            'single_stock_alert'  => ['nullable', 'integer', 'min:0'],
            'single_stock'        => ['nullable', 'array'], 
            'single_stock.*.warehouse_id' => ['required_with:single_stock', 'exists:warehouses,id'],
            'single_stock.*.qty'          => ['required_with:single_stock', 'integer', 'min:1'],
            'single_order_tax'    => ['exclude_if:type,variable', 'nullable', 'numeric', 'min:0'], 
            'single_tax_type'     => ['exclude_if:type,variable', 'required', 'in:inclusive,exclusive'], 
            
            // ════════════════════════════════════════════════════
            // VARIABLE PRODUCT VALIDATION
            // ════════════════════════════════════════════════════
            'variations'          => ['exclude_if:type,single', 'required', 'array', 'min:1'],
            
            'variations.*.sku'    => [
                'nullable', 
                'string', 
                Rule::unique('product_skus', 'sku')->where('company_id', $companyId)
            ],
            
            // 🌟 NEW: Optional, unique barcode for variations
            'variations.*.barcode' => [
                'nullable', 
                'string', 
                'max:255',
                Rule::unique('product_skus', 'barcode')->where('company_id', $companyId)
            ],
            
            'variations.*.price'  => ['required_with:variations', 'numeric', 'min:0'],
            'variations.*.cost'   => ['required_with:variations', 'numeric', 'min:0'],
            'variations.*.mrp'    => ['nullable', 'numeric', 'min:0'],
            'variations.*.attrs'  => ['nullable', 'array'],            
            'variations.*.order_tax' => ['nullable', 'numeric', 'min:0'],
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
            'single_sku.unique'           => 'This SKU is already in use in your inventory.',
            'single_barcode.unique'       => 'This Barcode is already assigned to another product.',
            'variations.*.sku.unique'     => 'One of your variation SKUs is already in use.',
            'variations.*.barcode.unique' => 'One of your variation Barcodes is already in use.',
            'variations.*.price.min'      => 'Variation prices cannot be negative.',
        ];
    }
}