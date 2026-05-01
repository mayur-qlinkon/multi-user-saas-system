<?php

namespace App\Services;

use App\Events\Orders\OrderPlaced;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ProductSku;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}
    // ════════════════════════════════════════════════════
    //  PLACE ORDER (storefront guest checkout)
    //  - Validates cart items against DB (never trust frontend prices)
    //  - Calculates GST: CGST+SGST (intra) or IGST (inter)
    //  - Applies round-off (Indian accounting)
    //  - Saves order + items in one transaction
    //  - Logs initial status history
    //  - Fires WhatsApp notification (fire-and-forget)
    // ════════════════════════════════════════════════════

    public function placeOrder(array $data, int $companyId): Order
    {
        return DB::transaction(function () use ($data, $companyId) {

            Log::info('[OrderService] Placing order', [
                'company_id' => $companyId,
                'customer_phone' => $data['customer_phone'] ?? null,
                'items_count' => count($data['items'] ?? []),
                'source' => $data['source'] ?? 'storefront',
            ]);

            // ── Step 1: Resolve and validate all cart items from DB ──
            $resolvedItems = $this->resolveCartItems($data['items'], $companyId);

            // ── Step 2: Determine GST type ──
            // Intra-state (same state) → CGST + SGST
            // Inter-state (different state) → IGST
            $supplyState = $data['supply_state'] ?? $data['delivery_state'] ?? null;
            $companyState = $this->getCompanyState($companyId);
            $isInterState = $this->isInterState($supplyState, $companyState);

            Log::info('[OrderService] GST determination', [
                'supply_state' => $supplyState,
                'company_state' => $companyState,
                'is_inter_state' => $isInterState,
            ]);

            // ── Step 3: Calculate all amounts ──
            $calculation = $this->calculateTotals($resolvedItems, $isInterState);

            // ── Step 4: Build order payload ──
            $orderData = [
                'company_id' => $companyId,
                'order_type' => $data['order_type'] ?? 'retail',
                'source' => $data['source'] ?? 'storefront',
                'status' => 'inquiry',

                // Customer
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,

                // Address
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_city' => $data['delivery_city'] ?? null,
                'delivery_state' => $data['delivery_state'] ?? null,
                'delivery_pincode' => $data['delivery_pincode'] ?? null,
                'delivery_country' => $data['delivery_country'] ?? 'India',
                'supply_state' => $supplyState,

                // Pricing
                'subtotal' => $calculation['subtotal'],
                'discount_amount' => $calculation['discount_amount'],
                'cgst_amount' => $calculation['cgst_amount'],
                'sgst_amount' => $calculation['sgst_amount'],
                'igst_amount' => $calculation['igst_amount'],
                'tax_amount' => $calculation['tax_amount'],
                'shipping_amount' => $data['shipping_amount'] ?? 0,
                'round_off' => $calculation['round_off'],
                'total_amount' => $calculation['total_amount'],
                'currency' => 'INR',

                // Coupon
                'coupon_code' => $data['coupon_code'] ?? null,
                'coupon_discount' => $data['coupon_discount'] ?? 0,

                // Payment
                'payment_method' => $data['payment_method'] ?? 'cod',
                'payment_status' => 'pending',

                // Fulfillment
                'delivery_type' => $data['delivery_type'] ?? 'delivery',
                'store_id' => $data['store_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,

                // Notes
                'customer_notes' => $data['customer_notes'] ?? null,
                'admin_notes' => $data['admin_notes'] ?? null,

                // Denormalized counts
                'items_count' => count($resolvedItems),
                'items_qty' => array_sum(array_column($resolvedItems, 'qty')),

                // Audit
                'created_by' => Auth::id() ?? null,
            ];

            // ── Step 5: Create order ──
            $order = Order::create($orderData);

            Log::info('[OrderService] Order created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total_amount,
            ]);

            // ── Step 6: Create order items ──
            foreach ($resolvedItems as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }

            // ── Step 7: Log initial status history ──
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'inquiry',
                'notes' => 'Order placed via '.($data['source'] ?? 'storefront'),
                'changed_by_type' => 'system',
                'changed_by' => null,
            ]);

            // ── Step 8: Fire WhatsApp notification to owner (fire-and-forget) ──
            $this->notifyOwnerWhatsApp($order);

            // ── Step 9: Auto-create/update CRM lead (fire-and-forget) ──
            app(CrmLeadService::class)
                ->createOrUpdateFromOrder($order);

            event(new OrderPlaced($order));

            return $order->load('items');
        });
    }

    // ════════════════════════════════════════════════════
    //  CREATE OFFLINE ORDER (Admin / POS)
    //  - Bypasses storefront hardcodes (inquiry/pending)
    //  - Allows instant confirmation and payment marking
    //  - Defaults to Walk-in customer if no data provided
    //  - Safely calculates GST while allowing manual discounts
    // ════════════════════════════════════════════════════

    public function createOfflineOrder(array $data, int $companyId): Order
    {
        return DB::transaction(function () use ($data, $companyId) {

            Log::info('[OrderService] Creating offline order', [
                'company_id' => $companyId,
                'source' => $data['source'] ?? 'admin',
                'admin_id' => Auth::id(),
            ]);

            // ── Step 1: Resolve Items & Calculate GST ──
            $resolvedItems = $this->resolveCartItems($data['items'], $companyId);

            $supplyState = $data['supply_state'] ?? $data['delivery_state'] ?? null;
            $companyState = $this->getCompanyState($companyId);
            $isInterState = $this->isInterState($supplyState, $companyState);

            $calculation = $this->calculateTotals($resolvedItems, $isInterState);

            // ── Step 2: Admin Overrides & Defaults ──
            $orderStatus = $data['status'] ?? 'confirmed'; // Offline orders usually skip 'inquiry'
            $paymentStatus = $data['payment_status'] ?? 'paid'; // Assumed paid if at counter

            // Process manual admin discount and shipping
            $manualDiscount = (float) ($data['discount_amount'] ?? 0);
            $shippingAmount = (float) ($data['shipping_amount'] ?? 0);

            // Apply to grand total (ensuring it never drops below 0)
            $finalTotal = max(0, $calculation['total_amount'] - $manualDiscount + $shippingAmount);

            // ── Step 3: Build Order Payload ──
            $orderData = [
                'company_id' => $companyId,
                'order_type' => $data['order_type'] ?? 'retail',
                'source' => $data['source'] ?? 'admin',
                'status' => $orderStatus,

                // Walk-in Customer Fallbacks (Prevents DB crash if left blank)
                'customer_name' => ! empty($data['customer_name']) ? $data['customer_name'] : 'Walk-in Customer',
                'customer_phone' => ! empty($data['customer_phone']) ? $data['customer_phone'] : '0000000000',
                'customer_email' => $data['customer_email'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,

                // Delivery Data
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_city' => $data['delivery_city'] ?? null,
                'delivery_state' => $data['delivery_state'] ?? null,
                'delivery_pincode' => $data['delivery_pincode'] ?? null,
                'delivery_country' => $data['delivery_country'] ?? 'India',
                'supply_state' => $supplyState,

                // Pricing (Merged calculated GST with manual discount/shipping)
                'subtotal' => $calculation['subtotal'],
                'discount_amount' => $manualDiscount,
                'cgst_amount' => $calculation['cgst_amount'],
                'sgst_amount' => $calculation['sgst_amount'],
                'igst_amount' => $calculation['igst_amount'],
                'tax_amount' => $calculation['tax_amount'],
                'shipping_amount' => $shippingAmount,
                'round_off' => $calculation['round_off'],
                'total_amount' => $finalTotal,
                'currency' => 'INR',

                // Payment
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_status' => $paymentStatus,
                'paid_at' => $paymentStatus === 'paid' ? now() : null,

                // Fulfillment (Offline usually defaults to pickup/POS)
                'delivery_type' => $data['delivery_type'] ?? 'pickup',
                'store_id' => $data['store_id'] ?? session('store_id'), // Assign to current admin branch
                'warehouse_id' => $data['warehouse_id'] ?? null,

                // Notes
                'customer_notes' => $data['customer_notes'] ?? null,
                'admin_notes' => $data['admin_notes'] ?? null,

                // Counts
                'items_count' => count($resolvedItems),
                'items_qty' => array_sum(array_column($resolvedItems, 'qty')),

                // Audit
                'created_by' => Auth::id(),
                'confirmed_by' => $orderStatus === 'confirmed' ? Auth::id() : null,
                'confirmed_at' => $orderStatus === 'confirmed' ? now() : null,
            ];

            // ── Step 4: Insert Data ──
            $order = Order::create($orderData);

            foreach ($resolvedItems as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }

            // ── Step 5: Log History securely attributed to Admin ──
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => $orderStatus,
                'notes' => 'Order created manually by Admin/Staff.',
                'changed_by_type' => 'admin',
                'changed_by' => Auth::id(),
            ]);

            Log::info('[OrderService] Offline order successfully generated', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total_amount,
            ]);

            return $order->load('items');
        });
    }

    // ════════════════════════════════════════════════════
    //  UPDATE ORDER STATUS
    // ════════════════════════════════════════════════════

    public function updateStatus(
        Order $order,
        string $newStatus,
        ?string $notes = null,
        string $changedByType = 'admin'
    ): Order {
        try {
            $order->transitionTo($newStatus, $notes, $changedByType);

            Log::info('[OrderService] Status updated', [
                'order_id' => $order->id,
                'new_status' => $newStatus,
                'by' => Auth::id(),
            ]);

            return $order->fresh();

        } catch (Throwable $e) {
            Log::error('[OrderService] Status update failed', [
                'order_id' => $order->id,
                'to' => $newStatus,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // ════════════════════════════════════════════════════
    //  UPDATE ORDER DETAILS (admin edits)
    // ════════════════════════════════════════════════════

    // ════════════════════════════════════════════════════
    //  UPDATE ORDER DETAILS (admin edits)
    // ════════════════════════════════════════════════════

    public function updateOrder(Order $order, array $data): Order
    {
        DB::beginTransaction();

        try {
            // 1. Separate the items array from the main order data
            $items = $data['items'] ?? null;
            unset($data['items']);

            // 2. Update the main order record (totals, status, customer info)
            $order->update($data);

            // 3. Process the Items (if provided)
            if ($items !== null) {

                // ⚠️ NOTE: If you are tracking inventory, you need to restore stock
                // for the old items here before deleting them!

                // Wipe the old items clean
                $order->items()->delete();

                // Build the new items array
                $newOrderItems = [];
                foreach ($items as $item) {

                    // You may need to fetch the specific Product/SKU here from the DB
                    // to accurately set the 'unit_price' and 'total' if your frontend
                    // doesn't pass them in the $data array securely.

                    $newOrderItems[] = new OrderItem([
                        'product_id' => $item['product_id'],
                        'sku_id' => $item['sku_id'],
                        'qty' => $item['qty'],
                        'unit_price' => $item['unit_price'],
                        'line_total' => $item['unit_price'] * $item['qty'],

                        // 🌟 MAP THE TEXT FIELDS HERE:
                        'product_name' => $item['product_name'],
                        'sku_code' => $item['sku_code'] === '-' ? null : $item['sku_code'],
                    ]);
                }

                // Save all new items to the order
                $order->items()->saveMany($newOrderItems);
            }

            DB::commit();

            Log::info('[OrderService] Order updated', [
                'order_id' => $order->id,
                'fields' => array_keys($data),
                'by' => Auth::id(),
            ]);

            // Return fresh order with the newly attached items
            return $order->fresh(['items']);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('[OrderService] Update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    // ════════════════════════════════════════════════════
    //  CANCEL ORDER
    // ════════════════════════════════════════════════════

    public function cancelOrder(Order $order, string $reason, string $cancelledByType = 'admin'): Order
    {
        if (! $order->is_cancellable) {
            throw new \InvalidArgumentException(
                "Order #{$order->order_number} cannot be cancelled at status: {$order->status}"
            );
        }

        $order->cancellation_reason = $reason;
        $order->save();

        $order->transitionTo('cancelled', $reason, $cancelledByType);

        Log::info('[OrderService] Order cancelled', [
            'order_id' => $order->id,
            'reason' => $reason,
            'by' => Auth::id(),
        ]);

        return $order->fresh();
    }

    // ════════════════════════════════════════════════════
    //  RAZORPAY PAYMENT CONFIRMATION (future hook)
    //  Call this from Razorpay webhook controller
    //  Zero changes needed to placeOrder() flow
    // ════════════════════════════════════════════════════

    public function confirmRazorpayPayment(
        Order $order,
        string $razorpayOrderId,
        string $razorpayPaymentId,
        string $razorpaySignature
    ): Order {
        DB::transaction(function () use ($order, $razorpayOrderId, $razorpayPaymentId, $razorpaySignature) {
            $order->update([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            // Auto-confirm on successful payment
            $order->transitionTo('confirmed', 'Payment confirmed via Razorpay', 'razorpay');

            Log::info('[OrderService] Razorpay payment confirmed', [
                'order_id' => $order->id,
                'razorpay_payment_id' => $razorpayPaymentId,
            ]);
        });

        return $order->fresh();
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — Resolve cart items from DB
    //  Never trust prices from frontend
    // ════════════════════════════════════════════════════

    private function resolveCartItems(array $cartItems, int $companyId): array
    {
        $resolved = [];

        foreach ($cartItems as $index => $item) {

            // Fetch SKU from DB — validates it belongs to this company
            $sku = ProductSku::whereHas('product', fn ($q) => $q->where('company_id', $companyId)
                ->where('is_active', true)
            )
                ->where('id', $item['sku_id'])
                ->where('is_active', true)
                ->with('product')
                ->first();

            if (! $sku) {
                Log::warning('[OrderService] SKU not found or inactive', [
                    'sku_id' => $item['sku_id'],
                    'company_id' => $companyId,
                    'index' => $index,
                ]);
                throw new \InvalidArgumentException(
                    "Product variant (SKU ID: {$item['sku_id']}) is not available."
                );
            }

            $product = $sku->product;
            $qty = (int) $item['qty'];
            $unitPrice = (float) $sku->price;   // always from DB
            $costPrice = (float) $sku->cost;    // always from DB
            $lineTotal = $unitPrice * $qty;

            // Build variant label from sku values if available
            $skuLabel = $item['variant'] ?? null;
            if (! $skuLabel && $sku->relationLoaded('skuValues')) {
                $skuLabel = $sku->skuValues
                    ->map(fn ($sv) => $sv->attributeValue?->value)
                    ->filter()
                    ->join(' / ');
            }

            $resolved[] = [
                'product_id' => $product->id,
                'sku_id' => $sku->id,
                'product_name' => $product->name,
                'sku_label' => $skuLabel,
                'sku_code' => $sku->sku ?? null,
                'product_image' => $item['image'] ?? $product->primary_image_url ?? null,
                'hsn_code' => $sku->hsn_code ?? $product->hsn_code ?? null,
                'unit_price' => $unitPrice,
                'cost_price' => $costPrice,
                'qty' => $qty,
                'discount_amount' => 0,
                'tax_rate' => (float) ($product->tax_rate ?? 0),
                'cgst_rate' => 0, // filled in calculateTotals
                'sgst_rate' => 0,
                'igst_rate' => 0,
                'cgst_amount' => 0,
                'sgst_amount' => 0,
                'igst_amount' => 0,
                'tax_amount' => 0,
                'line_total' => $lineTotal,
                'status' => 'pending',
            ];

            Log::info('[OrderService] Item resolved', [
                'product' => $product->name,
                'sku_id' => $sku->id,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
        }

        return $resolved;
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — Calculate totals + GST + round-off
    // ════════════════════════════════════════════════════

    private function calculateTotals(array &$items, bool $isInterState): array
    {
        $subtotal = 0;
        $cgstTotal = 0;
        $sgstTotal = 0;
        $igstTotal = 0;

        foreach ($items as &$item) {
            $lineBase = $item['unit_price'] * $item['qty'] - $item['discount_amount'];
            $taxRate = $item['tax_rate'];

            if ($isInterState) {
                // Inter-state: full tax as IGST
                $igstRate = $taxRate;
                $igstAmt = round($lineBase * $igstRate / 100, 2);
                $item['igst_rate'] = $igstRate;
                $item['igst_amount'] = $igstAmt;
                $item['tax_amount'] = $igstAmt;
                $igstTotal += $igstAmt;
            } else {
                // Intra-state: split tax as CGST + SGST (equal halves)
                $halfRate = $taxRate / 2;
                $cgstAmt = round($lineBase * $halfRate / 100, 2);
                $sgstAmt = round($lineBase * $halfRate / 100, 2);
                $item['cgst_rate'] = $halfRate;
                $item['sgst_rate'] = $halfRate;
                $item['cgst_amount'] = $cgstAmt;
                $item['sgst_amount'] = $sgstAmt;
                $item['tax_amount'] = $cgstAmt + $sgstAmt;
                $cgstTotal += $cgstAmt;
                $sgstTotal += $sgstAmt;
            }

            $item['line_total'] = round($lineBase + $item['tax_amount'], 2);
            $subtotal += $lineBase;
        }
        unset($item); // break reference

        $taxAmount = round($cgstTotal + $sgstTotal + $igstTotal, 2);
        $rawTotal = round($subtotal + $taxAmount, 2);

        // Round-off — nearest rupee (Indian accounting)
        $roundedTotal = round($rawTotal);
        $roundOff = round($roundedTotal - $rawTotal, 2);

        Log::info('[OrderService] Totals calculated', [
            'subtotal' => $subtotal,
            'cgst' => $cgstTotal,
            'sgst' => $sgstTotal,
            'igst' => $igstTotal,
            'tax_total' => $taxAmount,
            'raw_total' => $rawTotal,
            'round_off' => $roundOff,
            'final_total' => $roundedTotal,
            'inter_state' => $isInterState,
        ]);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => 0,
            'cgst_amount' => round($cgstTotal, 2),
            'sgst_amount' => round($sgstTotal, 2),
            'igst_amount' => round($igstTotal, 2),
            'tax_amount' => $taxAmount,
            'round_off' => $roundOff,
            'total_amount' => (float) $roundedTotal,
        ];
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — Get company's registered state for GST
    // ════════════════════════════════════════════════════

    private function getCompanyState(int $companyId): ?string
    {
        try {
            return Company::find($companyId)?->state?->name
                ?? Company::find($companyId)?->city
                ?? null;
        } catch (Throwable $e) {
            Log::warning('[OrderService] Could not fetch company state', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — Inter-state check
    // ════════════════════════════════════════════════════

    private function isInterState(?string $supplyState, ?string $companyState): bool
    {
        // If either state is unknown, default to intra-state (safer for small businesses)
        if (! $supplyState || ! $companyState) {
            Log::warning('[OrderService] State unknown — defaulting to intra-state GST', [
                'supply_state' => $supplyState,
                'company_state' => $companyState,
            ]);

            return false;
        }

        return strtolower(trim($supplyState)) !== strtolower(trim($companyState));
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — WhatsApp notification to owner
    //  Fire-and-forget — never crashes the order
    // ════════════════════════════════════════════════════

    private function notifyOwnerWhatsApp(Order $order): void
    {
        try {
            $whatsapp = get_setting('whatsapp', null, $order->company_id);

            if (! $whatsapp) {
                Log::info('[OrderService] WhatsApp skipped — not configured', [
                    'order_id' => $order->id,
                ]);

                return;
            }

            // Mark as sent — actual wa.me link is triggered from frontend
            // confirmation page. Server-side we just flag it.
            $order->update(['whatsapp_sent' => true, 'last_notified_at' => now()]);

            Log::info('[OrderService] WhatsApp notification flagged', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

        } catch (Throwable $e) {
            // Never crash an order because of notification failure
            Log::warning('[OrderService] WhatsApp notify failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
}
