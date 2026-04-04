<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentMethodController extends Controller
{
    /**
     * Display the single-page CRUD view with all payment methods.
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.payment_methods', compact('paymentMethods'));
    }

    /**
     * Store a newly created payment method in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:100',
            'slug'       => 'nullable|string|max:50|unique:payment_methods,slug',
            'gateway'    => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Auto-generate slug from label if not provided
        $validated['slug'] = $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['label']);
        
        // Handle checkboxes safely (works for both JSON and standard forms)
        $validated['is_online'] = $request->boolean('is_online');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $paymentMethod = PaymentMethod::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'Payment method created successfully.',
                'data'    => $paymentMethod
            ]);
        }

        return back()->with('success', 'Payment method created successfully.');
    }

    /**
     * Update the specified payment method in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:100',
            'slug'       => 'required|string|max:50|unique:payment_methods,slug,' . $paymentMethod->id,
            'gateway'    => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_online'] = $request->boolean('is_online');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $paymentMethod->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'Payment method updated successfully.',
                'data'    => $paymentMethod
            ]);
        }

        return back()->with('success', 'Payment method updated successfully.');
    }
    /**
     * Save the drag-and-drop new sort order.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:payment_methods,id',
            'order.*.sort_order' => 'required|integer'
        ]);

        foreach ($request->order as $item) {
            PaymentMethod::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Sort order updated successfully.']);
    }

    /**
     * Remove the specified payment method from storage.
     */
    public function destroy(Request $request, PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'Payment method deleted successfully.'
            ]);
        }

        return back()->with('success', 'Payment method deleted successfully.');
    }
}