<?php $__env->startSection('title', 'Order #' . $order->order_number); ?>

<?php $__env->startSection('header-title'); ?>
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Order #<?php echo e($order->order_number); ?></h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5"><?php echo e($order->created_at->format('d M Y, h:i A')); ?></p>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] { display: none !important; }

    .detail-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
    }

    .card-title {
        font-size: 11px; font-weight: 800; color: #374151;
        text-transform: uppercase; letter-spacing: 0.08em;
        padding-bottom: 12px; margin-bottom: 16px;
        border-bottom: 1.5px solid #f3f4f6;
        display: flex; align-items: center; gap: 8px;
    }

    .card-title i { color: var(--brand-600); }

    .info-row {
        display: flex; justify-content: space-between;
        align-items: flex-start; gap: 12px;
        padding: 6px 0;
        border-bottom: 1px solid #f9fafb;
    }

    .info-row:last-child { border-bottom: none; }

    .info-label {
        font-size: 11px; font-weight: 700; color: #9ca3af;
        text-transform: uppercase; letter-spacing: 0.05em;
        white-space: nowrap; flex-shrink: 0;
    }

    .info-value {
        font-size: 13px; font-weight: 600; color: #1f2937;
        text-align: right;
    }

    .status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 20px;
        font-size: 12px; font-weight: 700;
    }

    .status-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

    .action-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 10px;
        font-size: 13px; font-weight: 700;
        border: none; cursor: pointer;
        transition: opacity 150ms ease, transform 80ms ease;
    }

    .action-btn:active { transform: scale(0.97); }
    .action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .action-btn-primary { background: var(--brand-600); color: #fff; }
    .action-btn-primary:hover:not(:disabled) { opacity: 0.9; }

    .action-btn-outline {
        background: #fff; color: #374151;
        border: 1.5px solid #e5e7eb;
    }

    .action-btn-outline:hover { background: #f9fafb; }

    .action-btn-danger { background: #fef2f2; color: #dc2626; }
    .action-btn-danger:hover { background: #fee2e2; }

    .field-input {
        width: 100%; border: 1.5px solid #e5e7eb;
        border-radius: 10px; padding: 9px 13px;
        font-size: 13px; color: #1f2937;
        outline: none; font-family: inherit; background: #fff;
        transition: border-color 150ms ease;
    }

    .field-input:focus { border-color: var(--brand-600); }

    .timeline-item {
        display: flex; gap: 12px; position: relative;
        padding-bottom: 16px;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 15px; top: 32px;
        width: 2px; bottom: 0;
        background: #f1f5f9;
    }

    .timeline-dot {
        width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: 12px; font-weight: 700;
        border: 2px solid transparent;
    }

    /* Mobile responsive table */
    @media (max-width: 640px) {
        .items-table-header { display: none; }
        .items-table-row {
            display: flex; flex-direction: column; gap: 4px;
            padding: 12px; border-bottom: 1px solid #f3f4f6;
        }
        .items-table-row .col-product { font-weight: 600; }
        .items-table-row .col-price::before { content: 'Price: '; color: #9ca3af; font-size: 11px; }
        .items-table-row .col-qty::before  { content: 'Qty: ';   color: #9ca3af; font-size: 11px; }
        .items-table-row .col-total::before{ content: 'Total: '; color: #9ca3af; font-size: 11px; font-weight: 700; }
    }

    @media (min-width: 641px) {
            .items-table-header, .items-table-row {
                display: grid;
                grid-template-columns: 2fr 1fr 60px 1fr;
                gap: 16px;
                align-items: center;
                padding: 10px 16px;
            }
            .items-table-header {
                background: #f8fafc;
                border-bottom: 1px solid #f1f5f9;
            }
            .items-table-row .col-qty {
                text-align: center;
            }
            .items-table-row .col-total,
            .items-table-header span:last-child {
                text-align: right;
            }
            .items-table-header span:nth-child(3) {
                text-align: center;
            }
        }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $sc = $order->status_color;
    $pc = $order->payment_status_color;
?>

<div class="pb-10" x-data="orderShow()">

    
    <div class="mb-5 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-2 text-sm text-gray-400 font-medium">
            <a href="<?php echo e(route('admin.orders.index')); ?>" class="hover:text-brand-600 transition-colors">Orders</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-gray-700 font-semibold font-mono"><?php echo e($order->order_number); ?></span>
        </div>
        <a href="<?php echo e(route('admin.orders.index')); ?>"
            class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-gray-800 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Orders
        </a>
    </div>

    
    <?php if(session('success')): ?>
        <div class="mb-4 bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex items-center gap-3">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-600 flex-shrink-0"></i>
            <p class="text-sm font-semibold text-green-800"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 flex-shrink-0"></i>
            <p class="text-sm font-semibold text-red-800"><?php echo e(session('error')); ?></p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        
        <div class="lg:col-span-2 space-y-4">

            
            <div class="detail-card">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                    <div>
                        <p class="font-mono text-lg font-bold text-gray-900"><?php echo e($order->order_number); ?></p>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">
                            Placed <?php echo e($order->created_at->diffForHumans()); ?>

                            · via <?php echo e(ucfirst($order->source)); ?>

                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="status-badge"
                            id="status-badge-display"
                            style="background: <?php echo e($sc['bg']); ?>; color: <?php echo e($sc['text']); ?>">
                            <span class="status-dot" style="background: <?php echo e($sc['dot']); ?>"></span>
                            <span><?php echo e($order->status_label); ?></span>
                        </span>
                        <span class="status-badge"
                            style="background: <?php echo e($pc['bg']); ?>; color: <?php echo e($pc['text']); ?>">
                            <?php echo e(ucfirst($order->payment_status)); ?>

                        </span>
                    </div>
                </div>

                
                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">

                    
                    <?php if(in_array($order->status, ['inquiry']) && has_permission('orders.change_status')): ?>
                        <button @click="updateStatus('confirmed')"
                            :disabled="loading"
                            class="action-btn action-btn-primary">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Confirm Order
                        </button>
                    <?php endif; ?>

                    
                    <?php if(in_array($order->status, ['confirmed']) && has_permission('orders.change_status')): ?>
                        <button @click="updateStatus('processing')"
                            :disabled="loading"
                            class="action-btn action-btn-outline">
                            <i data-lucide="loader-2" class="w-4 h-4"></i>
                            Mark Processing
                        </button>
                    <?php endif; ?>

                    
                    <?php if(in_array($order->status, ['confirmed', 'processing']) && has_permission('orders.change_status')): ?>
                        <button @click="updateStatus('shipped')"
                            :disabled="loading"
                            class="action-btn action-btn-outline">
                            <i data-lucide="truck" class="w-4 h-4"></i>
                            Mark Shipped
                        </button>
                    <?php endif; ?>

                    
                    <?php if(in_array($order->status, ['shipped', 'out_for_delivery']) && has_permission('orders.change_status')): ?>
                        <button @click="updateStatus('delivered')"
                            :disabled="loading"
                            class="action-btn action-btn-outline">
                            <i data-lucide="package-check" class="w-4 h-4"></i>
                            Mark Delivered
                        </button>
                    <?php endif; ?>

                    <?php if(has_permission('orders.download_receipt')): ?>
                   <a href="<?php echo e(route('admin.orders.receipt', $order->id)); ?>"
                        target="_blank"
                        class="action-btn action-btn-outline">
                        <i data-lucide="file-down" class="w-4 h-4 text-gray-500"></i>
                        Download Receipt
                    </a>
                    <?php endif; ?>

                    
                    <?php if(has_permission('orders.record_payment')): ?>
                        <?php if($order->payment_status !== 'paid'): ?>
                            <button @click="paymentModal = true"
                                class="action-btn action-btn-outline"
                                style="color: #15803d; border-color: #86efac;">
                                <i data-lucide="indian-rupee" class="w-4 h-4"></i>
                                Record Payment
                            </button>
                        <?php else: ?>
                            <span class="action-btn text-green-700 bg-green-50 border border-green-200 cursor-default">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                Paid
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>

                    
                    <?php if($order->is_cancellable && has_permission('orders.cancel')): ?>                        
                        <button @click="cancelModal = true"
                            class="action-btn action-btn-danger ml-auto">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            Cancel
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="detail-card !p-0 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <p class="card-title !border-0 !pb-0 !mb-0">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        Order Items
                        <span class="text-gray-400 font-normal normal-case ml-1">(<?php echo e($order->items_count); ?> items · <?php echo e($order->items_qty); ?> qty)</span>
                    </p>
                </div>

                
                <div class="items-table-header text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <span>Product</span>
                    <span>Unit Price</span>
                    <span class="text-center">Qty</span>
                    <span class="text-right">Total</span>
                </div>

                
                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="items-table-row border-b border-gray-50">
                        <div class="col-product min-w-0">
                            <p class="text-[13px] font-semibold text-gray-800 truncate"><?php echo e($item->product_name); ?></p>
                            <?php if($item->sku_label): ?>
                                <p class="text-[11px] text-gray-400 font-medium"><?php echo e($item->sku_label); ?></p>
                            <?php endif; ?>
                            <?php if($item->sku_code): ?>
                                <p class="text-[10px] text-gray-300 font-mono"><?php echo e($item->sku_code); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-price text-[13px] text-gray-600 font-medium">
                            ₹<?php echo e(number_format($item->unit_price, 2)); ?>

                        </div>
                        <div class="col-qty text-[13px] font-bold text-gray-900 sm:text-center">
                            <?php echo e($item->qty); ?>

                        </div>
                        <div class="col-total text-[13px] font-bold text-gray-900 sm:text-right">
                            ₹<?php echo e(number_format($item->line_total, 2)); ?>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                
                <div class="px-5 py-4 bg-gray-50/50 space-y-2">
                    <div class="flex justify-between text-[13px] text-gray-500">
                        <span>Subtotal</span>
                        <span class="font-semibold text-gray-700 font-mono">₹<?php echo e(number_format($order->subtotal, 2)); ?></span>
                    </div>
                    <?php if($order->discount_amount > 0): ?>
                        <div class="flex justify-between text-[13px] text-green-600">
                            <span>Discount</span>
                            <span class="font-semibold font-mono">−₹<?php echo e(number_format($order->discount_amount, 2)); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($order->tax_amount > 0): ?>
                        <div class="flex justify-between text-[12px] text-gray-400">
                            <span>
                                Tax
                                <?php if($order->cgst_amount > 0): ?>
                                    (CGST ₹<?php echo e(number_format($order->cgst_amount, 2)); ?> + SGST ₹<?php echo e(number_format($order->sgst_amount, 2)); ?>)
                                <?php elseif($order->igst_amount > 0): ?>
                                    (IGST)
                                <?php endif; ?>
                            </span>
                            <span class="font-mono">₹<?php echo e(number_format($order->tax_amount, 2)); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($order->shipping_amount > 0): ?>
                        <div class="flex justify-between text-[13px] text-gray-500">
                            <span>Shipping</span>
                            <span class="font-semibold font-mono">₹<?php echo e(number_format($order->shipping_amount, 2)); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($order->round_off != 0): ?>
                        <div class="flex justify-between text-[12px] text-gray-400">
                            <span>Round Off</span>
                            <span class="font-mono"><?php echo e($order->round_off > 0 ? '+' : ''); ?>₹<?php echo e(number_format($order->round_off, 2)); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-[15px] font-bold text-gray-900 border-t border-gray-200 pt-2 mt-2">
                        <span>Total</span>
                        <span class="font-mono" style="color: var(--brand-600)">₹<?php echo e(number_format($order->total_amount, 2)); ?></span>
                    </div>
                </div>
            </div>

            
            <?php if($order->customer_notes): ?>
                <div class="detail-card">
                    <p class="card-title">
                        <i data-lucide="message-square" class="w-4 h-4"></i>
                        Customer Notes
                    </p>
                    <p class="text-[13px] text-gray-600 leading-relaxed bg-amber-50 border border-amber-100 rounded-xl px-4 py-3">
                        <?php echo e($order->customer_notes); ?>

                    </p>
                </div>
            <?php endif; ?>

            
            <?php if(has_permission('orders.add_note')): ?>
            <div class="detail-card" x-data="{ editing: false, note: '<?php echo e(addslashes($order->admin_notes ?? '')); ?>', saving: false }">
                <p class="card-title">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    Admin Notes
                    <button @click="editing = !editing"
                        class="ml-auto text-[11px] font-bold px-2.5 py-1 rounded-lg transition-colors"
                        :class="editing ? 'bg-gray-100 text-gray-600' : 'text-brand-600 hover:bg-brand-50'"
                        style="color: var(--brand-600)">
                        <span x-text="editing ? 'Cancel' : 'Edit'"></span>
                    </button>
                </p>

                <template x-if="!editing">
                    <p class="text-[13px] text-gray-600 leading-relaxed"
                        x-text="note || 'No notes added yet.'">
                    </p>
                </template>

                <template x-if="editing">
                    <div class="space-y-3">
                        <textarea x-model="note" rows="3"
                            class="field-input resize-none"
                            placeholder="Add internal notes about this order..."></textarea>
                        <button @click="saveNote()"
                            :disabled="saving"
                            class="action-btn action-btn-primary">
                            <i data-lucide="loader-2" x-show="saving" class="w-3.5 h-3.5 animate-spin"></i>
                            <i data-lucide="save" x-show="!saving" class="w-3.5 h-3.5"></i>
                            <span x-text="saving ? 'Saving...' : 'Save Note'"></span>
                        </button>
                    </div>
                </template>
            </div>
            <?php endif; ?>

            
            <?php if($order->statusHistory->isNotEmpty()): ?>
                <div class="detail-card">
                    <p class="card-title">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                        Status History
                    </p>
                    <div class="space-y-0">
                        <?php $__currentLoopData = $order->statusHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $toColor = \App\Models\Order::STATUS_COLORS[$history->to_status] ?? ['bg' => '#f8fafc', 'text' => '#6b7280', 'dot' => '#94a3b8'];
                            ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"
                                    style="background: <?php echo e($toColor['bg']); ?>; border-color: <?php echo e($toColor['dot']); ?>">
                                    <span style="color: <?php echo e($toColor['dot']); ?>; font-size: 9px;">●</span>
                                </div>
                                <div class="flex-1 min-w-0 pt-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-[12px] font-bold text-gray-900">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $history->to_status))); ?>

                                        </span>
                                        <?php if($history->from_status): ?>
                                            <span class="text-[11px] text-gray-400">
                                                from <?php echo e(ucfirst(str_replace('_', ' ', $history->from_status))); ?>

                                            </span>
                                        <?php endif; ?>
                                        <span class="text-[10px] text-gray-400 ml-auto font-mono">
                                            <?php echo e($history->created_at->format('d M Y, h:i A')); ?>

                                        </span>
                                    </div>
                                    <?php if($history->notes): ?>
                                        <p class="text-[12px] text-gray-500 mt-0.5"><?php echo e($history->notes); ?></p>
                                    <?php endif; ?>
                                    <?php
                                        $byType = $history->changed_by_type ?? 'system';
                                        // Format: "owner:John Doe" → role = owner, name = John Doe
                                        // Or just: "system", "razorpay", "customer"
                                        $byParts = str_contains($byType, ':') ? explode(':', $byType, 2) : null;
                                        $byRole  = $byParts ? $byParts[0] : $byType;
                                        $byName  = $byParts ? $byParts[1] : null;
                                    ?>
                                    <p class="text-[10px] text-gray-400 mt-0.5 flex items-center gap-1">
                                        <span class="capitalize"><?php echo e($byRole); ?></span>
                                        <?php if($byName): ?>
                                            <span class="text-gray-300">·</span>
                                            <span class="font-semibold text-gray-500"><?php echo e($byName); ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        
        <div class="space-y-4">

            
            <?php if(has_permission('orders.change_status')): ?>
            <div class="detail-card">
                <p class="card-title">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Update Status
                </p>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">New Status</label>
                        <select x-model="newStatus" class="field-input">
                            <?php $__currentLoopData = \App\Models\Order::STATUS_COLORS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $colors): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status); ?>"
                                    <?php echo e($order->status === $status ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst(str_replace('_', ' ', $status))); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Notes (optional)</label>
                        <textarea x-model="statusNotes" rows="2"
                            class="field-input resize-none"
                            placeholder="Reason for status change..."></textarea>
                    </div>
                    <button @click="updateStatus(newStatus)"
                        :disabled="loading || newStatus === '<?php echo e($order->status); ?>'"
                        class="action-btn action-btn-primary w-full justify-center">
                        <i data-lucide="loader-2" x-show="loading" class="w-4 h-4 animate-spin"></i>
                        <i data-lucide="check" x-show="!loading" class="w-4 h-4"></i>
                        <span x-text="loading ? 'Updating...' : 'Update Status'"></span>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            
            <div class="detail-card">
                <p class="card-title">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    Customer
                </p>
                <div class="space-y-0">
                    <div class="info-row">
                        <span class="info-label">Name</span>
                        <span class="info-value"><?php echo e($order->customer_name); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <a href="tel:<?php echo e($order->customer_phone); ?>"
                            class="info-value font-mono"
                            style="color: var(--brand-600)"><?php echo e($order->customer_phone); ?></a>
                    </div>
                    <?php if($order->customer_email): ?>
                        <div class="info-row">
                            <span class="info-label">Email</span>
                            <span class="info-value text-xs"><?php echo e($order->customer_email); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <?php if($order->delivery_address): ?>
                <div class="detail-card">
                    <p class="card-title">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                        Delivery Address
                    </p>
                    <address class="not-italic text-[13px] text-gray-600 leading-relaxed">
                        <?php echo e($order->delivery_address); ?>

                        <?php if($order->delivery_city): ?>
                            <br><?php echo e($order->delivery_city); ?>

                        <?php endif; ?>
                        <?php if($order->delivery_state): ?>
                            , <?php echo e($order->delivery_state); ?>

                        <?php endif; ?>
                        <?php if($order->delivery_pincode): ?>
                            — <?php echo e($order->delivery_pincode); ?>

                        <?php endif; ?>
                        <br><?php echo e($order->delivery_country ?? 'India'); ?>

                    </address>
                    <?php if($order->supply_state): ?>
                        <p class="text-[11px] text-gray-400 font-medium mt-2">
                            GST: <?php echo e($order->cgst_amount > 0 ? 'Intra-state (CGST + SGST)' : 'Inter-state (IGST)'); ?>

                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            
            <div class="detail-card">
                <p class="card-title">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                    Payment
                </p>
                <div class="space-y-0">
                    <div class="info-row">
                        <span class="info-label">Method</span>
                        <span class="info-value uppercase"><?php echo e($order->payment_method ?? 'COD'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="status-badge text-[11px] px-2 py-0.5"
                                style="background: <?php echo e($pc['bg']); ?>; color: <?php echo e($pc['text']); ?>">
                                <?php echo e(ucfirst($order->payment_status)); ?>

                            </span>
                        </span>
                    </div>
                    <?php if($order->paid_at): ?>
                        <div class="info-row">
                            <span class="info-label">Paid At</span>
                            <span class="info-value text-xs"><?php echo e($order->paid_at->format('d M Y, h:i A')); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($order->razorpay_payment_id): ?>
                        <div class="info-row">
                            <span class="info-label">Razorpay ID</span>
                            <span class="info-value font-mono text-xs"><?php echo e($order->razorpay_payment_id); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="detail-card" x-data="{ editTracking: false }">
                <div class="card-title">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                    Shipping
                    <button @click="editTracking = !editTracking"
                        class="ml-auto text-[11px] font-bold px-2.5 py-1 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                        <span x-text="editTracking ? 'Cancel' : 'Edit'"></span>
                    </button>
                </div>

                <template x-if="!editTracking">
                    <div class="space-y-0">
                        <div class="info-row">
                            <span class="info-label">Type</span>
                            <span class="info-value capitalize"><?php echo e($order->delivery_type ?? 'Delivery'); ?></span>
                        </div>
                        <?php if($order->courier_name): ?>
                            <div class="info-row">
                                <span class="info-label">Courier</span>
                                <span class="info-value"><?php echo e($order->courier_name); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if($order->tracking_number): ?>
                            <div class="info-row">
                                <span class="info-label">Tracking</span>
                                <span class="info-value font-mono text-xs"><?php echo e($order->tracking_number); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if($order->expected_delivery_date): ?>
                            <div class="info-row">
                                <span class="info-label">Expected</span>
                                <span class="info-value"><?php echo e($order->expected_delivery_date->format('d M Y')); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if(!$order->courier_name && !$order->tracking_number): ?>
                            <p class="text-[12px] text-gray-400">No tracking info yet.</p>
                        <?php endif; ?>
                    </div>
                </template>

                <template x-if="editTracking">
                    <form method="POST" action="<?php echo e(route('admin.orders.logistics', $order->id)); ?>" class="space-y-3">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Courier</label>
                            <input type="text" name="courier_name" value="<?php echo e($order->courier_name); ?>"
                                placeholder="e.g. Delhivery, BlueDart" class="field-input">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tracking #</label>
                            <input type="text" name="tracking_number" value="<?php echo e($order->tracking_number); ?>"
                                placeholder="Tracking number" class="field-input">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Expected Delivery</label>
                            <input type="date" name="expected_delivery_date"
                                value="<?php echo e($order->expected_delivery_date?->format('Y-m-d')); ?>"
                                class="field-input">
                        </div>
                        <button type="submit" class="action-btn action-btn-primary w-full justify-center">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                            Save Tracking
                        </button>
                    </form>
                </template>
            </div>

            
            <div class="detail-card">
                <p class="card-title">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    Order Info
                </p>
                <div class="space-y-0">
                    <div class="info-row">
                        <span class="info-label">Order ID</span>
                        <span class="info-value font-mono">#<?php echo e($order->id); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Type</span>
                        <span class="info-value capitalize"><?php echo e($order->order_type); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Source</span>
                        <span class="info-value capitalize"><?php echo e($order->source); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Created</span>
                        <span class="info-value text-xs"><?php echo e($order->created_at->format('d M Y, h:i A')); ?></span>
                    </div>
                    <?php if($order->creator): ?>
                        <div class="info-row">
                            <span class="info-label">Created by</span>
                            <span class="info-value"><?php echo e($order->creator->name); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($order->confirmed_at): ?>
                        <div class="info-row">
                            <span class="info-label">Confirmed</span>
                            <span class="info-value text-xs"><?php echo e($order->confirmed_at->format('d M Y')); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($order->cancelled_at): ?>
                        <div class="info-row">
                            <span class="info-label">Cancelled</span>
                            <span class="info-value text-xs text-red-500"><?php echo e($order->cancelled_at->format('d M Y')); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($order->cancellation_reason): ?>
                        <div class="info-row">
                            <span class="info-label">Reason</span>
                            <span class="info-value text-xs text-red-400"><?php echo e($order->cancellation_reason); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    
    <div x-show="cancelModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background: rgba(0,0,0,0.45)">
        <div @click.away="cancelModal = false"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Cancel Order</h3>
                    <p class="text-xs text-gray-400">Order #<?php echo e($order->order_number); ?></p>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                    Cancellation Reason <span class="text-red-500">*</span>
                </label>
                <textarea x-model="cancelReason" rows="3"
                    class="field-input resize-none"
                    placeholder="Why is this order being cancelled?"></textarea>
            </div>

            <div class="flex gap-3">
                <button @click="cancelModal = false"
                    class="action-btn action-btn-outline flex-1 justify-center">
                    Keep Order
                </button>
                <button @click="cancelOrder()"
                    :disabled="!cancelReason.trim() || loading"
                    class="action-btn action-btn-danger flex-1 justify-center">
                    <i data-lucide="loader-2" x-show="loading" class="w-4 h-4 animate-spin"></i>
                    <span x-text="loading ? 'Cancelling...' : 'Confirm Cancel'"></span>
                </button>
            </div>
        </div>
    </div>



<div x-show="paymentModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background: rgba(0,0,0,0.45)">
    <div @click.away="paymentModal = false"
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100">

        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="indian-rupee" class="w-5 h-5 text-green-600"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-900">Record Payment</h3>
                <p class="text-xs text-gray-400">Order #<?php echo e($order->order_number); ?> · Total ₹<?php echo e(number_format($order->total_amount, 2)); ?></p>
            </div>
        </div>

        <div class="space-y-4">
            
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                    Amount Received <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden focus-within:border-brand-600 transition-colors"
                    style="--tw-border-opacity:1;">
                    <span class="px-3 py-2.5 bg-gray-50 border-r border-gray-200 text-gray-500 font-bold text-sm flex-shrink-0">₹</span>
                    <input type="number" x-model="payAmount" step="0.01"
                        placeholder="<?php echo e(number_format($order->total_amount, 2)); ?>"
                        class="flex-1 px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none bg-white">
                </div>
                <p class="text-[11px] text-gray-400 mt-1">Default = order total. Enter less for partial payment.</p>
            </div>
            
            
            <?php if (isset($component)) { $__componentOriginal6074b4137b8006ef4d1b8340d0976388 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6074b4137b8006ef4d1b8340d0976388 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.payment-method-select','data' => ['name' => 'pay_method_id','label' => 'Payment Method','xModel' => 'payMethodId','dataPaymentSelector' => true,'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('payment-method-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pay_method_id','label' => 'Payment Method','x-model' => 'payMethodId','data-payment-selector' => true,'required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6074b4137b8006ef4d1b8340d0976388)): ?>
<?php $attributes = $__attributesOriginal6074b4137b8006ef4d1b8340d0976388; ?>
<?php unset($__attributesOriginal6074b4137b8006ef4d1b8340d0976388); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6074b4137b8006ef4d1b8340d0976388)): ?>
<?php $component = $__componentOriginal6074b4137b8006ef4d1b8340d0976388; ?>
<?php unset($__componentOriginal6074b4137b8006ef4d1b8340d0976388); ?>
<?php endif; ?>

            
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                    Transaction Reference <span class="text-gray-400 normal-case font-normal">(optional)</span>
                </label>
                <input type="text" x-model="payReference"
                    placeholder="UPI ref, cheque no, cash memo..."
                    class="field-input">
            </div>

            
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                    Notes <span class="text-gray-400 normal-case font-normal">(optional)</span>
                </label>
                <textarea x-model="payNotes" rows="2"
                    class="field-input resize-none"
                    placeholder="Any additional payment notes..."></textarea>
            </div>
        </div>

        <div class="flex gap-3 mt-5">
            <button @click="paymentModal = false"
                class="action-btn action-btn-outline flex-1 justify-center">
                Cancel
            </button>
            <button @click="recordPayment()"
                :disabled="!payAmount || !payMethodId || loading"
                class="action-btn flex-1 justify-center text-white"
                style="background: #16a34a;"
                :class="(!payAmount || !payMethodId || loading) ? 'opacity-50 cursor-not-allowed' : ''">
                <i data-lucide="loader-2" x-show="loading" class="w-4 h-4 animate-spin"></i>
                <i data-lucide="check" x-show="!loading" class="w-4 h-4"></i>
                <span x-text="loading ? 'Saving...' : 'Record Payment'"></span>
            </button>
        </div>
    </div>
</div>


</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function orderShow() {
    return {
        loading:       false,
        newStatus:     '<?php echo e($order->status); ?>',
        statusNotes:   '',
        cancelModal:   false,
        cancelReason:  '',
        paymentModal:  false,
        payAmount:     '<?php echo e(number_format($order->total_amount, 2)); ?>',
        payMethodId:   '',
        payReference:  '',
        payNotes:      '',

        // ── Update status via AJAX ──
        async updateStatus(status) {
            if (this.loading) return;
            if (status === '<?php echo e($order->status); ?>' && !this.statusNotes) {
                return;
            }

            this.loading = true;

            try {
                const res  = await fetch('<?php echo e(route('admin.orders.status', $order->id)); ?>', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        status: status,
                        notes:  this.statusNotes,
                    }),
                });

                const data = await res.json();

                if (data.success) {
                    // Update badge in DOM
                    const badge = document.getElementById('status-badge-display');
                    if (badge && data.status_color) {
                        badge.style.background = data.status_color.bg;
                        badge.style.color      = data.status_color.text;
                        badge.querySelector('span:last-child').textContent = data.status_label;
                        badge.querySelector('.status-dot').style.background = data.status_color.dot;
                    }

                    this.newStatus   = status;
                    this.statusNotes = '';

                    if (typeof BizAlert !== 'undefined') {
                        BizAlert.toast(data.message, 'success');
                    }

                    // Reload page after short delay to refresh history
                    setTimeout(() => location.reload(), 1200);
                } else {
                    alert(data.message || 'Failed to update status.');
                }

            } catch (e) {
                console.error('[OrderShow] Status update error:', e);
                alert('Network error. Please try again.');
            } finally {
                this.loading = false;
            }
        },

        // ── Cancel order ──
        async cancelOrder() {
            if (!this.cancelReason.trim() || this.loading) return;
            this.loading = true;

            try {
                const res  = await fetch('<?php echo e(route('admin.orders.cancel', $order->id)); ?>', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ reason: this.cancelReason }),
                });

                const data = await res.json();

                if (data.success) {
                    this.cancelModal = false;
                    if (typeof BizAlert !== 'undefined') {
                        BizAlert.toast(data.message, 'success');
                    }
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert(data.message || 'Failed to cancel order.');
                }

            } catch (e) {
                console.error('[OrderShow] Cancel error:', e);
                alert('Network error. Please try again.');
            } finally {
                this.loading = false;
            }
        },
        async recordPayment() {
                if (!this.payAmount || !this.payMethodId || this.loading) return;
                this.loading = true;

                try {
                    const res  = await fetch('<?php echo e(route('admin.orders.mark-paid', $order->id)); ?>', {
                        method:  'POST',
                        headers: {
                            'Content-Type':     'application/json',
                            'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            amount:            parseFloat(this.payAmount),
                            payment_method_id: parseInt(this.payMethodId),
                            reference:         this.payReference || null,
                            notes:             this.payNotes     || null,
                        }),
                    });

                    const data = await res.json();

                    if (data.success) {
                        this.paymentModal = false;
                        if (typeof BizAlert !== 'undefined') {
                            BizAlert.toast(data.message, 'success');
                        }
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        alert(data.message || 'Failed to record payment.');
                    }

                } catch (e) {
                    console.error('[Payment] Record error:', e);
                    alert('Network error. Please try again.');
                } finally {
                    this.loading = false;
                }
            },
    }
}

// ── Admin note save (outside orderShow — needs own Alpine scope) ──
document.addEventListener('alpine:init', () => {
    Alpine.data('noteEditor', () => ({
        editing: false,
        note:    '<?php echo e(addslashes($order->admin_notes ?? '')); ?>',
        saving:  false,

        async saveNote() {
            this.saving = true;
            try {
                const res  = await fetch('<?php echo e(route('admin.orders.note', $order->id)); ?>', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ admin_notes: this.note }),
                });
                const data = await res.json();
                if (data.success) {
                    this.editing = false;
                    if (typeof BizAlert !== 'undefined') BizAlert.toast('Note saved.', 'success');
                }
            } catch (e) {
                console.error('[Note] Save error:', e);
            } finally {
                this.saving = false;
            }
        }
    }));
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/orders/show.blade.php ENDPATH**/ ?>