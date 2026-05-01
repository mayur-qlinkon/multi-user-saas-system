<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Store;
use App\Models\Client;
use App\Models\Warehouse;

use App\Services\OrderService;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminOrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    // ════════════════════════════════════════════════════
    //  INDEX — list all orders with filters
    //  GET /admin/orders
    // ════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $query = Order::forCompany($companyId)
            ->with(['items:id,order_id,product_name,qty,unit_price,line_total'])
            ->recent();

        // ── Filters ──

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->byStatus($request->status);
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Source filter
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Search — order number, customer name, phone
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($inner) => $inner->where('order_number', 'like', "%{$q}%")
                ->orWhere('customer_name', 'like', "%{$q}%")
                ->orWhere('customer_phone', 'like', "%{$q}%")
                ->orWhere('customer_email', 'like', "%{$q}%")
            );
        }

        $orders = $query->paginate(20)->withQueryString();
        $stats = Order::getStats($companyId);
        $statusColors = array_keys(Order::STATUS_COLORS);

        Log::info('[AdminOrder] Index loaded', [
            'company_id' => $companyId,
            'filters' => $request->only(['status', 'payment_status', 'source', 'from', 'to', 'q']),
            'count' => $orders->total(),
        ]);

        return view('admin.orders.index', compact('orders', 'stats', 'statusColors'));
    }

    // ════════════════════════════════════════════════════
    //  CREATE — show offline order form
    //  GET /admin/orders/create
    // ════════════════════════════════════════════════════

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $clients = Client::with('state')->where('is_active', true)->get();
        $stores = auth_stores()->get();
        $storeIds = auth_stores()->pluck('id');
        $warehouses = Warehouse::whereIn('store_id', $storeIds)->get();

        Log::info('[AdminOrder] Create form loaded', [
            'company_id' => $companyId,
            'by' => Auth::id(),
        ]);

        return view('admin.orders.create', compact('stores', 'warehouses','clients'));
    }

    // ════════════════════════════════════════════════════
    //  STORE — process offline order creation
    //  POST /admin/orders
    // ════════════════════════════════════════════════════

    public function store(StoreOrderRequest $request)
    {
        $companyId = Auth::user()->company_id;

        // The Request handles the validation. We just extract the payload.
        $validated = $request->validated();

        // Force the source to be admin for tracking
        $validated['source'] = 'admin';

        try {
            // DB Transaction is handled safely inside the Service
            $order = $this->orderService->createOfflineOrder($validated, $companyId);

            Log::info('[AdminOrder] Offline order created', [
                'order_id' => $order->id,
                'by' => Auth::id(),
            ]);

            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'Offline order created successfully.');

        } catch (Throwable $e) {
            Log::error('[AdminOrder] Offline order creation failed', [
                'error' => $e->getMessage(),
                'by' => Auth::id(),
            ]);

            return back()->withInput()->with('error', 'Failed to create order: '.$e->getMessage());
        }
    }

    // ════════════════════════════════════════════════════
    //  EDIT — load offline order form with existing data
    //  GET /admin/orders/{order}/edit
    // ════════════════════════════════════════════════════

    public function edit(Order $order)
    {
        $this->authorizeOrder($order);

        // Guardrail: Prevent loading the edit form for fulfilled/cancelled orders
        if (! in_array($order->status, ['inquiry', 'confirmed', 'processing'])) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', "Cannot edit order details at status: {$order->status}");
        }

        // Eager load items to populate the cart UI
        $order->load(['items.product', 'items.sku']);

        // 🌟 "Iron Wall" in action: Global scopes automatically filter by the tenant's company!
        $stores = Store::where('is_active', true)->get();
        $clients = Client::with('state')->where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();    
        Log::info('[AdminOrder] Edit form loaded', [
            'order_id' => $order->id,
            'by' => Auth::id(),
        ]);

        return view('admin.orders.edit', compact('order', 'stores', 'warehouses','clients'));
    }

    // ════════════════════════════════════════════════════
    //  SHOW — order detail with items + status history
    //  GET /admin/orders/{order}
    // ════════════════════════════════════════════════════

    public function show(Order $order)
    {
        $this->authorizeOrder($order);

        $order->load([
            'items',
            'statusHistory',
            'creator:id,name',
            'invoice:id,invoice_number,status,payment_status,grand_total',
        ]);

        Log::info('[AdminOrder] Show viewed', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'by' => Auth::id(),
        ]);

        return view('admin.orders.show', compact('order'));
    }

    // ════════════════════════════════════════════════════
    //  UPDATE STATUS — AJAX
    //  POST /admin/orders/{order}/status
    // ════════════════════════════════════════════════════

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($order);

        $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(Order::STATUS_COLORS))],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $updatedOrder = $this->orderService->updateStatus(
                $order,
                $request->status,
                $request->notes,
                'admin'
            );

            Log::info('[AdminOrder] Status updated via AJAX', [
                'order_id' => $order->id,
                'new_status' => $request->status,
                'by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated to '.$updatedOrder->status_label,
                'status' => $updatedOrder->status,
                'status_label' => $updatedOrder->status_label,
                'status_color' => $updatedOrder->status_color,
            ]);

        } catch (Throwable $e) {
            Log::error('[AdminOrder] Status update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ════════════════════════════════════════════════════
    //  UPDATE LOGISTICS — Quick tracking update from Show page
    //  PATCH /admin/orders/{order}/logistics
    // ════════════════════════════════════════════════════

    public function updateLogistics(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        $validated = $request->validate([
            'courier_name' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        try {
            // Simple Eloquent update because this doesn't change math or items
            $order->update($validated);

            Log::info('[AdminOrder] Logistics updated', [
                'order_id' => $order->id,
                'by' => Auth::id(),
            ]);

            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('success', 'Tracking details updated successfully.');

        } catch (Throwable $e) {
            Log::error('[AdminOrder] Logistics update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update tracking details.');
        }
    }

    // ════════════════════════════════════════════════════
    //  UPDATE DETAILS — tracking, notes
    //  PUT /admin/orders/{order}
    // ════════════════════════════════════════════════════

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $this->authorizeOrder($order);

        try {
            $this->orderService->updateOrder($order, $request->validated());

            Log::info('[AdminOrder] Details updated', [
                'order_id' => $order->id,
                'fields' => array_keys($request->validated()),
                'by' => Auth::id(),
            ]);

            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('success', 'Order updated successfully.');

        } catch (Throwable $e) {
            Log::error('[AdminOrder] Update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update order: '.$e->getMessage());
        }
    }

    // ════════════════════════════════════════════════════
    //  CANCEL — with reason
    //  POST /admin/orders/{order}/cancel
    // ════════════════════════════════════════════════════

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($order);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->orderService->cancelOrder($order, $request->reason, 'admin');

            Log::info('[AdminOrder] Cancelled', [
                'order_id' => $order->id,
                'reason' => $request->reason,
                'by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Order #{$order->order_number} cancelled.",
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (Throwable $e) {
            Log::error('[AdminOrder] Cancel failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order.',
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  ADD NOTE — quick admin note AJAX
    //  POST /admin/orders/{order}/note
    // ════════════════════════════════════════════════════

    public function addNote(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($order);

        $request->validate([
            'admin_notes' => ['required', 'string', 'max:1500'],
        ]);

        try {
            $order->update(['admin_notes' => $request->admin_notes]);

            Log::info('[AdminOrder] Note added', [
                'order_id' => $order->id,
                'by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note saved.',
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save note.',
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  DOWNLOAD RECEIPT — storefront-originated orders
    //  GET /admin/orders/{order}/receipt
    // ════════════════════════════════════════════════════

    public function downloadReceipt(Order $order)
    {
        $this->authorizeOrder($order);

        $order->load(['items', 'payments', 'company']);

        $pdf = Pdf::loadView(
            'storefront.receipt',
            ['company' => $order->company, 'order' => $order]
        )
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'helvetica',
            ]);

        $safeNumber = str_replace(['/', '\\'], '-', $order->order_number);

        return $pdf->download('Receipt-'.$safeNumber.'.pdf');
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — Tenant isolation check
    // ════════════════════════════════════════════════════

    private function authorizeOrder(Order $order): void
    {
        if ($order->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }
    }
}