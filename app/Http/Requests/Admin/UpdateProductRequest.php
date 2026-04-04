<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The Tenantable trait handles cross-company protection, 
        // but we double-check the product belongs to this company just to be ironclad.
        return $this->product->company_id === auth()->user()->company_id;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            // Core Data
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
            'quantity_limitation' => ['nullable', 'integer', 'min:1'], // 🌟 NEW
            'type'                => ['required', 'in:single,variable'],
            'barcode_symbology'   => ['required', 'string'],
            'description'         => ['nullable', 'string'],
            'is_active'           => ['boolean'],
            'product_guide'       => ['nullable', 'array', 'max:15'],
            'product_guide.*.title'       => ['required_with:product_guide', 'string'],
            'product_guide.*.description' => ['required_with:product_guide', 'string'],

            // Single Product Validation (Note the complex ignore rule)
            'single_sku'          => [
                'exclude_if:type,variable', 
                'required', 
                'string', 
                // Ignore uniqueness if the SKU belongs to the current product being edited
                Rule::unique('product_skus', 'sku')
                    ->where('company_id', $companyId)
                    ->whereNot('product_id', $this->product->id)
            ],
            'single_price'        => ['exclude_if:type,variable', 'required', 'numeric', 'min:0'],
            'single_cost'         => ['exclude_if:type,variable', 'required', 'numeric', 'min:0'],
            'single_mrp'          => ['exclude_if:type,variable', 'nullable', 'numeric', 'min:0'], // 🌟 NEW
            'single_order_tax'    => ['exclude_if:type,variable', 'nullable', 'numeric', 'min:0'], // 🌟 NEW
            'single_tax_type'     => ['exclude_if:type,variable', 'required', 'in:inclusive,exclusive'], // 🌟 NEW
            'single_stock_alert'  => ['nullable', 'integer', 'min:0'],
            'single_stock'        => ['nullable', 'array'], 
            'single_stock.*.warehouse_id'   => ['required_with:single_stock', 'exists:warehouses,id'],
            'single_stock.*.qty'            => ['required_with:single_stock', 'integer', 'min:1'],
            
            // 2. 🌟 NEW: Media rules tailored for Updates
            'media'               => ['nullable', 'array', 'max:10'],
            'media.*.id'          => ['nullable', 'integer'], // Identifies existing media
            'media.*.type'        => ['required_with:media', 'in:image,youtube'],
            
            // File is only required if it's an image AND it's a brand new upload (no ID)
            // If there is NO ID (meaning it's a new upload) and the type is 'image', the file is REQUIRED.
            'media.*.file' => [
                'exclude_if:media.*.type,youtube',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $id = $this->input("media.{$index}.id");
                    
                    if (empty($id) && empty($value)) {
                        $fail('An image file is required for new uploads.');
                    }
                },
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120' // Allow up to 5MB (let your ImageService compress it!)
            ],
            'media.*.url'         => ['nullable', 'url', 'max:255'],
            'media.*.sku_index'   => ['nullable', 'integer', 'min:0'],
            
            'primary_media_index' => ['nullable', 'integer'],

            // Variable Product Validation
            'variations'          => ['exclude_if:type,single', 'required', 'array', 'min:1'],
            'variations.*.id'     => ['nullable', 'exists:product_skus,id'], // Used to track existing variations
            'variations.*.sku'    => [
                'nullable', 
                'string', 
                Rule::unique('product_skus', 'sku')
                    ->where('company_id', $companyId)
                    ->whereNot('product_id', $this->product->id)
            ],
            'variations.*.price'  => ['required_with:variations', 'numeric', 'min:0'],
            'variations.*.cost'   => ['required_with:variations', 'numeric', 'min:0'],
            'variations.*.mrp'    => ['nullable', 'numeric', 'min:0'], // 🌟 NEW
            'variations.*.attrs'  => ['nullable', 'array'],
            'variations.*.order_tax' => ['nullable', 'numeric', 'min:0'], // 🌟 NEW
            'variations.*.tax_type'  => ['required_with:variations', 'in:inclusive,exclusive'], // 🌟 NEW
            'variations.*.stock_alert'          => ['nullable', 'integer', 'min:0'],
            'variations.*.stock'                => ['nullable', 'array'],
            'variations.*.stock.*.warehouse_id' => ['required_with:variations.*.stock', 'exists:warehouses,id'],
            'variations.*.stock.*.qty'          => ['required_with:variations.*.stock', 'integer', 'min:1'],
        ];
    }
}
