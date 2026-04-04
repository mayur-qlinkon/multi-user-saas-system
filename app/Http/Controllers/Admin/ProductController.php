<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\ProductService;
use App\Models\{Category, Unit, Supplier, Warehouse, Attribute, Product};

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        // Get all filters from the URL query string (e.g., ?search=shirt&category_id=2)
        $filters = $request->only(['search', 'category_id', 'status']);
        
        // Fetch products using the Service
        $products = $this->productService->getProductsList($filters);
        
        // Fetch categories for the filter dropdown
        $categories = Category::where('is_active', true)->get();

        return view('admin.products.index', compact('products', 'categories', 'filters'));
    }

    /**
     * Toggle Product Status (AJAX Request)
     */
    public function toggleStatus(Product $product)
    {
        $this->productService->toggleStatus($product);

        return response()->json([
            'success' => true,
            'message' => 'Product status updated successfully.',
            'is_active' => $product->is_active
        ]);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $this->productService->deleteProduct($product);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product deleted successfully.');
    }

    public function create()
    {
        // Fetch all necessary dropdown data for the UI
        $categories = Category::where('is_active', true)->get();
        $units      = Unit::where('is_active', true)->get();
        $suppliers  = Supplier::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $attributes = Attribute::with('values')->where('is_active', true)->get();

        return view('admin.products.create', compact(
            'categories', 'units', 'suppliers', 'warehouses', 'attributes'
        ));
    }

    public function store(StoreProductRequest $request)
    {
        // We pass the fully validated data and the company ID to the Service layer
        $this->productService->createProduct(
            $request->validated(), 
            Auth::user()->company_id
        );

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product created successfully!');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        // Eager load all related data so the Edit view can pre-fill the form
        $product->load(['skus.skuValues', 'media']);

        $categories = Category::where('is_active', true)->get();
        $units      = Unit::where('is_active', true)->get();
        $suppliers  = Supplier::where('is_active', true)->get();
        $attributes = Attribute::with('values')->where('is_active', true)->get();

        return view('admin.products.edit', compact(
            'product', 'categories', 'units', 'suppliers', 'attributes'
        ));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->productService->updateProduct(
            $product, 
            $request->validated(), 
            Auth::user()->company_id
        );

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product updated successfully!');
    }
    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        // Eager load EVERYTHING we need for the view
        $product->load([
            'category', 
            'supplier', 
            'media',
            'skus.skuValues.attribute', 
            'skus.skuValues.attributeValue',
            'skus.stocks.warehouse' // Gets live stock grouped by warehouse
        ]);

        // Load units separately (since they are referenced directly on the product)
        $product->load(['productUnit', 'saleUnit', 'purchaseUnit']);

        return view('admin.products.show', compact('product'));
    }
    public function duplicate(Product $product)
    {
        $newProduct = $this->productService->duplicateProduct($product);

        return redirect()
            ->route('admin.products.edit', $newProduct->id)
            ->with('success', "'{$product->name}' duplicated. Review and activate when ready.");
    }
    
}