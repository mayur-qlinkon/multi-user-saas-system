<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Company;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    // ════════════════════════════════════════════════════
    //  PLACE ORDER — POST /{slug}/orders
    //  Called via AJAX from cart drawer checkout panel
    //  Returns JSON — Alpine handles success/error state
    // ════════════════════════════════════════════════════

    public function store(StoreOrderRequest $request, string $slug): JsonResponse
    {
        // ── Resolve company ──
        $company = Company::where('slug', $slug)->firstOrFail();

        // ── Set tenant context for get_setting() ──
        request()->attributes->set('current_company_id', $company->id);

        try {
            // ── Merge company context into validated data ──
            $data = array_merge($request->validated(), [
                'source'     => 'storefront',
                'order_type' => 'retail',

                // Map the single text area to the main delivery address
                'delivery_address' => $request->address, 
                
                // Track registered customer ID if they are logged in
                'customer_id'      =>Auth::check() ?Auth::id() : null,
            ]);

            // ── Place order via service ──
            $order = $this->orderService->placeOrder($data, $company->id);

            Log::info('[OrderController] Order placed successfully', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'company'      => $company->slug,
                'customer'     => $order->customer_phone,
                'total'        => $order->total_amount,
            ]);

            // ── Build WhatsApp URL for owner notification ──
            // Triggered from frontend — opens WhatsApp on customer device
            $whatsapp = get_setting('whatsapp', null, $company->id);
            $waUrl    = null;
            if ($whatsapp) {
                $waUrl = 'https://wa.me/'
                    . preg_replace('/[^0-9]/', '', $whatsapp)
                    . '?text=' . $order->whatsapp_message;
            }

            return response()->json([
                'success'      => true,
                'message'      => 'Order placed successfully!',
                'order_number' => $order->order_number,
                'total'        => '₹' . number_format($order->total_amount, 2),
                'whatsapp_url' => $waUrl,
                'receipt_url'  => route('storefront.orders.receipt', [
                        'slug'        => $company->slug,
                        'orderNumber' => $order->order_number,
                    ]),
                'items_count'  => $order->items_count,
            ], 201);

        } catch (\InvalidArgumentException $e) {
            // Cart item validation failure — show to customer
            Log::warning('[OrderController] Order validation failed', [
                'company' => $slug,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (Throwable $e) {
            Log::error('[OrderController] Order placement failed', [
                'company' => $slug,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again or contact us on WhatsApp.',
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  ORDER CONFIRMATION — GET /{slug}/orders/{orderNumber}
    //  Optional standalone page — useful for sharing
    //  order confirmation link via WhatsApp/SMS
    // ════════════════════════════════════════════════════

    public function show(string $slug, string $orderNumber): \Illuminate\View\View
    {
        $company = Company::where('slug', $slug)->firstOrFail();
        request()->attributes->set('current_company_id', $company->id);

        $order = Order::where('company_id', $company->id)
            ->where('order_number', $orderNumber)
            ->with(['items'])
            ->firstOrFail();

        $navCategories = app(\App\Http\Controllers\StorefrontController::class)
            ->getNavCategories($company->id);

        Log::info('[OrderController] Order confirmation viewed', [
            'order_number' => $orderNumber,
            'company'      => $slug,
        ]);

        return view('storefront.order-confirmation', compact(
            'company',
            'order',
            'navCategories',
        ));
    }
    // ════════════════════════════════════════════════════
    //  DOWNLOAD RECEIPT PDF
    //  GET /admin/orders/{order}/receipt  (admin)
    //  GET /{slug}/orders/{number}/receipt (customer)
    // ════════════════════════════════════════════════════

    public function downloadReceipt(string $slug, string $orderNumber)
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        $order = Order::where('company_id', $company->id)
            ->where('order_number', $orderNumber)
            ->with(['items', 'payments'])
            ->firstOrFail();

        $pdf = Pdf::loadView('storefront.receipt', compact('company', 'order'))
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'helvetica',
            ]);

        $safeNumber = str_replace(['/', '\\'], '-', $order->order_number);

        return $pdf->download('Receipt-' . $safeNumber . '.pdf');
    }

}