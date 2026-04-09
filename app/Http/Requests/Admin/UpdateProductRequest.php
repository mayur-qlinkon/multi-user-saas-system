<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ensure the product exists and belongs to the user's company
        return $this->product && $this->product->company_id === auth()->user()->company_id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $companyId = auth()->user()->company_id;
        $isCatalog = $this->input('product_type') === 'catalog';
        $isVariable = $this->input('type') === 'variable';

        // ── 1. Common Rules ──
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['nullable', 'in:sellable,catalog'],
            'hsn_code' => ['nullable', 'string', 'max:50'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('company_id', $companyId)],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')->where('company_id', $companyId)],
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')->where('company_id', $companyId)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'show_in_storefront' => ['boolean'],

            'product_guide' => ['nullable', 'array', 'max:15'],
            'product_guide.*.title' => ['required_with:product_guide', 'string'],
            'product_guide.*.description' => ['required_with:product_guide', 'string'],

            'media' => ['nullable', 'array', 'max:10'],
            'media.*.id' => ['nullable', 'integer'],
            'media.*.type' => ['required_with:media', 'in:image,youtube'],
            'media.*.file' => [
                'exclude_if:media.*.type,youtube',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $id = $this->input("media.{$index}.id");
                    if (empty($id) && empty($value) && $this->hasFile($attribute)) {
                        $fail('An image file is required for new uploads.');
                    }
                },
                'image', 'mimes:jpeg,png,jpg,webp', 'max:5120',
            ],
            'media.*.url' => ['nullable', 'url', 'max:255'],
            'media.*.sku_index' => ['nullable'],
            'primary_media_index' => ['nullable', 'integer'],
        ];

        // ── 2. Catalog Bypass ──
        if ($isCatalog) {
            $rules['product_unit_id'] = ['nullable'];
            $rules['sale_unit_id'] = ['nullable'];
            $rules['purchase_unit_id'] = ['nullable'];
            $rules['type'] = ['nullable', 'in:single,variable'];
            $rules['barcode_symbology'] = ['nullable', 'string'];

            return $rules;
        }

        // ── 3. Sellable Requirements ──
        $rules['product_unit_id'] = ['required', Rule::exists('units', 'id')->where('company_id', $companyId)];
        $rules['sale_unit_id'] = ['required', Rule::exists('units', 'id')->where('company_id', $companyId)];
        $rules['purchase_unit_id'] = ['required', Rule::exists('units', 'id')->where('company_id', $companyId)];
        $rules['quantity_limitation'] = ['nullable', 'integer', 'min:1'];
        $rules['type'] = ['required', 'in:single,variable'];
        $rules['barcode_symbology'] = ['required', 'string'];

        // ── 4. Single Product Uniqueness Logic ──
        if (! $isVariable) {
            // Get the ID of the existing SKU for the single product to ignore it
            $existingSkuId = $this->product->skus()->first()?->id;

            $rules['single_sku'] = [
                'nullable', 'string',
                Rule::unique('product_skus', 'sku')
                    ->where('company_id', $companyId)
                    ->ignore($existingSkuId),
            ];
            $rules['single_barcode'] = [
                'nullable', 'string', 'max:255',
                Rule::unique('product_skus', 'barcode')
                    ->where('company_id', $companyId)
                    ->ignore($existingSkuId),
            ];
            $rules['single_price'] = ['required', 'numeric', 'min:0'];
            $rules['single_cost'] = ['required', 'numeric', 'min:0'];
            $rules['single_mrp'] = ['nullable', 'numeric', 'min:0'];
            $rules['single_order_tax'] = ['nullable', 'numeric', 'min:0'];
            $rules['single_tax_type'] = ['required', 'in:inclusive,exclusive'];
            $rules['single_stock_alert'] = ['nullable', 'integer', 'min:0'];
            $rules['single_stock'] = ['nullable', 'array'];
            $rules['single_stock.*.warehouse_id'] = ['required_with:single_stock', 'exists:warehouses,id'];
            $rules['single_stock.*.qty'] = ['required_with:single_stock', 'integer', 'min:1'];
        }

        // ── 5. Variable Product Uniqueness Logic (The "Root" Fix) ──
        if ($isVariable) {
            $rules['variations'] = ['required', 'array', 'min:1'];

            foreach ($this->input('variations', []) as $index => $variation) {
                $variationId = $variation['id'] ?? null;

                $rules["variations.{$index}.id"] = ['nullable', 'exists:product_skus,id'];

                $rules["variations.{$index}.sku"] = [
                    'nullable', 'string',
                    Rule::unique('product_skus', 'sku')
                        ->where('company_id', $companyId)
                        ->ignore($variationId),
                ];

                $rules["variations.{$index}.barcode"] = [
                    'nullable', 'string', 'max:255',
                    Rule::unique('product_skus', 'barcode')
                        ->where('company_id', $companyId)
                        ->ignore($variationId),
                ];

                $rules["variations.{$index}.price"] = ['required', 'numeric', 'min:0'];
                $rules["variations.{$index}.cost"] = ['required', 'numeric', 'min:0'];
                $rules["variations.{$index}.mrp"] = ['nullable', 'numeric', 'min:0'];
                $rules["variations.{$index}.attrs"] = ['nullable', 'array'];
                $rules["variations.{$index}.order_tax"] = ['nullable', 'numeric', 'min:0'];
                $rules["variations.{$index}.tax_type"] = ['required', 'in:inclusive,exclusive'];
                $rules["variations.{$index}.stock_alert"] = ['nullable', 'integer', 'min:0'];
                $rules["variations.{$index}.stock"] = ['nullable', 'array'];
                $rules["variations.{$index}.stock.*.warehouse_id"] = ['required_with:variations.{$index}.stock', 'exists:warehouses,id'];
                $rules["variations.{$index}.stock.*.qty"] = ['required_with:variations.{$index}.stock', 'integer', 'min:1'];
            }
        }

        return $rules;
    }
}
