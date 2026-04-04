

<?php $__env->startSection('title', 'Payment Methods'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        [x-cloak] {
            display: none !important;
        }

        /* ── Root Variables ── */                
        :root {
            /* Map the custom CSS to your global Tailwind theme variables */
            --brand: var(--brand-500);
            --brand-dark: var(--brand-600);
            --brand-light: #e6f4ea; /* Keep these static or map them to a lighter global tint if you have one */
            --brand-mid: #d0edda;
            
            --surface: #f7f8fc;
            --card: #ffffff;
            --border: #eaecf0;
            --text-head: #101828;
            --text-body: #344054;
            --text-muted: #667085;
            --text-faint: #98a2b3;
            --radius: 14px;
            --shadow-sm: 0 1px 3px rgba(16, 24, 40, .06), 0 1px 2px rgba(16, 24, 40, .04);
            --shadow-md: 0 4px 16px rgba(16, 24, 40, .10), 0 2px 6px rgba(16, 24, 40, .06);
            --shadow-xl: 0 20px 60px rgba(16, 24, 40, .18), 0 8px 24px rgba(16, 24, 40, .10);
            --transition: all .2s cubic-bezier(.4, 0, .2, 1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--surface);
        }

        /* ── Page Header ── */
        .pm-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 28px;
        }

        .pm-page-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-head);
            letter-spacing: -.4px;
            line-height: 1.2;
        }

        .pm-page-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 3px;
            font-weight: 500;
        }

        /* ── Stats Strip ── */
        .pm-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }

        @media (max-width: 900px) {
            .pm-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .pm-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
        }

        .pm-stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 14px;
            transition: var(--transition);
        }

        .pm-stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .pm-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pm-stat-icon svg {
            width: 20px;
            height: 20px;
        }

        .pm-stat-num {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-head);
            line-height: 1;
        }

        .pm-stat-lbl {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ── Main Card ── */
        .pm-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .pm-card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            background: #fcfcfd;
        }

        .pm-card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-head);
        }

        /* ── Search ── */
        .pm-search-wrap {
            position: relative;
        }

        .pm-search-wrap svg {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            color: var(--text-faint);
            pointer-events: none;
        }

        .pm-search {
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: 8px 12px 8px 34px;
            font-size: 13px;
            font-family: inherit;
            color: var(--text-body);
            background: #fff;
            width: 220px;
            transition: var(--transition);
            outline: none;
        }

        .pm-search:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(16, 140, 42, .12);
            width: 260px;
        }

        @media (max-width: 480px) {

            .pm-search,
            .pm-search:focus {
                width: 100%;
            }
        }

        /* ── Add Button ── */
        .pm-btn-add {
            background: var(--brand);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: var(--transition);
            box-shadow: 0 1px 4px rgba(16, 140, 42, .25);
            white-space: nowrap;
        }

        .pm-btn-add:hover {
            background: var(--brand-dark);
            box-shadow: 0 4px 12px rgba(16, 140, 42, .35);
            transform: translateY(-1px);
        }

        .pm-btn-add svg {
            width: 15px;
            height: 15px;
        }

        /* ── Table ── */
        .pm-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .pm-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        .pm-table thead tr {
            background: #f9fafb;
            border-bottom: 1px solid var(--border);
        }

        .pm-table th {
            padding: 11px 20px;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--text-faint);
            text-transform: uppercase;
            letter-spacing: .7px;
            white-space: nowrap;
        }

        .pm-table th.center {
            text-align: center;
        }

        .pm-table th.right {
            text-align: right;
        }

        .pm-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }

        .pm-table tbody tr:last-child {
            border-bottom: none;
        }

        .pm-table tbody tr:hover {
            background: #fafbff;
        }

        .pm-table td {
            padding: 14px 20px;
            vertical-align: middle;
            font-size: 13.5px;
            color: var(--text-body);
        }

        .pm-table td.center {
            text-align: center;
        }

        .pm-table td.right {
            text-align: right;
        }

        /* ── Drag Handle ── */
        .drag-handle {
            cursor: grab;
            color: var(--text-faint);
            transition: color .15s;
            display: inline-flex;
        }

        .drag-handle:hover {
            color: var(--brand);
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .drag-disabled {
            cursor: not-allowed;
            opacity: .3;
            display: inline-flex;
        }

        .sortable-ghost {
            opacity: .35;
            background: var(--brand-light) !important;
        }

        .pm-table tbody {
            display: table-row-group;
        }

        /* ── Method Name Cell ── */
        .pm-method-name {
            font-weight: 700;
            color: var(--text-head);
            font-size: 13.5px;
        }

        .pm-method-slug {
            font-size: 11px;
            color: var(--text-faint);
            margin-top: 2px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .pm-method-slug span {
            background: #f0f1f3;
            border-radius: 4px;
            padding: 1px 6px;
            font-family: 'Courier New', monospace;
            font-size: 10.5px;
        }

        /* ── Gateway Badge ── */
        .pm-gateway {
            background: #f0f9ff;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 3px 9px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .4px;
            text-transform: uppercase;
            display: inline-block;
        }

        .pm-gateway.na {
            background: #f9fafb;
            color: var(--text-faint);
            border-color: var(--border);
        }

        /* ── Status Badges ── */
        .pm-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .5px;
            white-space: nowrap;
        }

        .pm-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .badge-online {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .badge-online .dot {
            background: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, .3);
        }

        .badge-offline {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-offline .dot {
            background: #f59e0b;
        }

        .badge-active {
            background: var(--brand-light);
            color: var(--brand);
            border: 1px solid var(--brand-mid);
        }

        .badge-active .dot {
            background: var(--brand);
            box-shadow: 0 0 0 2px rgba(16, 140, 42, .25);
        }

        .badge-inactive {
            background: #f9fafb;
            color: #6b7280;
            border: 1px solid #e5e7eb;
        }

        .badge-inactive .dot {
            background: #9ca3af;
        }

        /* ── Action Buttons ── */
        .pm-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            border: 1.5px solid var(--border);
            background: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-family: inherit;
        }

        .pm-action-btn svg {
            width: 14px;
            height: 14px;
        }

        .pm-action-btn.edit {
            color: var(--brand);
        }

        .pm-action-btn.edit:hover {
            background: var(--brand);
            color: #fff;
            border-color: var(--brand);
            box-shadow: 0 2px 8px rgba(16, 140, 42, .3);
        }

        .pm-action-btn.del {
            color: #ef4444;
        }

        .pm-action-btn.del:hover {
            background: #ef4444;
            color: #fff;
            border-color: #ef4444;
            box-shadow: 0 2px 8px rgba(239, 68, 68, .3);
        }

        /* ── Empty State ── */
        .pm-empty {
            padding: 64px 24px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        .pm-empty-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: var(--brand-light);
            border: 1px solid var(--brand-mid);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pm-empty-icon svg {
            width: 26px;
            height: 26px;
            color: var(--brand);
        }

        .pm-empty-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-head);
        }

        .pm-empty-sub {
            font-size: 13px;
            color: var(--text-muted);
            max-width: 320px;
        }

        /* ── Modal Overlay ── */
        .pm-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(10, 14, 26, .5);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        /* ── Modal Panel ── */
        .pm-modal {
            position: relative;
            background: var(--card);
            border-radius: 18px;
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalIn .22s cubic-bezier(.34, 1.4, .64, 1);
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: translateY(24px) scale(.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .pm-modal-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 2;
        }

        .pm-modal-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pm-modal-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--brand-light);
            border: 1px solid var(--brand-mid);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pm-modal-icon svg {
            width: 18px;
            height: 18px;
            color: var(--brand);
        }

        .pm-modal-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-head);
            letter-spacing: -.3px;
        }

        .pm-modal-sub {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .pm-modal-close {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1.5px solid var(--border);
            background: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            transition: var(--transition);
        }

        .pm-modal-close:hover {
            background: #fee2e2;
            color: #ef4444;
            border-color: #fca5a5;
        }

        .pm-modal-close svg {
            width: 15px;
            height: 15px;
        }

        .pm-modal-body {
            padding: 24px;
        }

        .pm-modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            background: #fcfcfd;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        /* ── Form Elements ── */
        .pm-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .pm-form-grid .span2 {
            grid-column: span 2;
        }

        @media (max-width: 480px) {
            .pm-form-grid {
                grid-template-columns: 1fr;
            }

            .pm-form-grid .span2 {
                grid-column: span 1;
            }
        }

        .pm-field label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-body);
            margin-bottom: 6px;
            letter-spacing: .2px;
        }

        .pm-field label .req {
            color: #ef4444;
            margin-left: 2px;
        }

        .pm-field label .opt {
            color: var(--text-faint);
            font-weight: 500;
            font-size: 11px;
            margin-left: 4px;
        }

        .pm-input {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            padding: 10px 13px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-body);
            background: #fff;
            outline: none;
            transition: var(--transition);
            box-sizing: border-box;
        }

        .pm-input::placeholder {
            color: var(--text-faint);
        }

        .pm-input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(16, 140, 42, .12);
        }

        .pm-input.muted {
            background: #f9fafb;
        }

        /* ── Toggle ── */
        .pm-toggle-wrap {
            display: flex;
            align-items: center;
            gap: 11px;
            background: #f9fafb;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            padding: 10px 14px;
            cursor: pointer;
            transition: var(--transition);
        }

        .pm-toggle-wrap:hover {
            border-color: var(--brand);
            background: var(--brand-light);
        }

        .pm-toggle-sr {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        .pm-toggle-track {
            position: relative;
            width: 40px;
            height: 22px;
            border-radius: 100px;
            background: #d1d5db;
            transition: background .2s;
            flex-shrink: 0;
        }

        .pm-toggle-track.on {
            background: var(--brand);
        }

        .pm-toggle-thumb {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
            transition: transform .2s cubic-bezier(.34, 1.56, .64, 1);
        }

        .pm-toggle-thumb.on {
            transform: translateX(18px);
        }

        .pm-toggle-lbl {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-body);
            user-select: none;
        }

        .pm-toggle-hint {
            font-size: 11px;
            color: var(--text-faint);
            margin-top: 1px;
        }

        /* ── Buttons ── */
        .pm-btn {
            border-radius: 9px;
            padding: 10px 20px;
            font-size: 13.5px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: var(--transition);
            border: none;
            white-space: nowrap;
        }

        .pm-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none !important;
        }

        .pm-btn-ghost {
            background: #fff;
            border: 1.5px solid var(--border);
            color: var(--text-body);
        }

        .pm-btn-ghost:hover:not(:disabled) {
            background: #f9fafb;
        }

        .pm-btn-primary {
            background: var(--brand);
            color: #fff;
            box-shadow: 0 1px 4px rgba(16, 140, 42, .3);
        }

        .pm-btn-primary:hover:not(:disabled) {
            background: var(--brand-dark);
            box-shadow: 0 4px 12px rgba(16, 140, 42, .4);
            transform: translateY(-1px);
        }

        .pm-btn svg {
            width: 15px;
            height: 15px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spin {
            animation: spin .7s linear infinite;
        }

        /* ── Separator ── */
        .pm-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 6px 0;
        }

        /* ── Responsive Table ── */
        @media (max-width: 768px) {
            .pm-table-wrap {
                margin: 0 -1px;
            }

            .pm-table {
                min-width: 600px;
            }

            .pm-card-header {
                padding: 14px 16px;
            }

            .pm-modal-body {
                padding: 18px 16px;
            }

            .pm-modal-footer {
                padding: 14px 16px;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">List / Payment Methods</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div x-data="paymentMethodApp()" x-init="boot()" class="pb-12">


        
        <div class="pm-card">

            
            <div class="bg-white p-5 border-b border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between rounded-t-xl">
                <span class="text-[16px] font-bold text-gray-900">All Methods</span>
                
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <input type="text" x-model="search" placeholder="Search methods…"
                            class="block w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors bg-gray-50 placeholder-gray-400">
                    </div>

                    
                    <button @click="openModal()"
                        class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 transition-all shadow-md active:scale-95 whitespace-nowrap">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Method
                    </button>
                </div>
            </div>

            
            <div class="pm-table-wrap">
                <table id="sortable-table" class="pm-table">
                    <thead>
                        <tr>
                            <th style="width:52px" class="center">Move</th>
                            <th>Method Name</th>
                            <th>Gateway</th>
                            <th class="center">Type</th>
                            <th class="center">Status</th>
                            <th class="right" style="padding-right:24px">Actions</th>
                        </tr>
                    </thead>

                    
                    <template x-for="row in filteredMethods" :key="row.id">
                        <tbody class="sortable-row" :data-id="row.id">
                            <tr>
                                
                                <td class="center">
                                    <span :class="search === '' ? 'drag-handle' : 'drag-disabled'"
                                        :title="search === '' ? 'Drag to reorder' : 'Clear search to reorder'">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.8" stroke="currentColor" style="width:18px;height:18px">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 9h16.5M3.75 15h16.5" />
                                        </svg>
                                    </span>
                                </td>

                                
                                <td>
                                    <div class="pm-method-name" x-text="row.label"></div>
                                    <div class="pm-method-slug">
                                        <span x-text="row.slug"></span>
                                    </div>
                                </td>

                                
                                <td>
                                    <template x-if="row.gateway">
                                        <span class="pm-gateway" x-text="row.gateway.toUpperCase()"></span>
                                    </template>
                                    <template x-if="!row.gateway">
                                        <span class="pm-gateway na">N/A</span>
                                    </template>
                                </td>

                                
                                <td class="center">
                                    <template x-if="row.is_online">
                                        <span class="pm-badge badge-online">
                                            <span class="dot"></span> Online
                                        </span>
                                    </template>
                                    <template x-if="!row.is_online">
                                        <span class="pm-badge badge-offline">
                                            <span class="dot"></span> Offline
                                        </span>
                                    </template>
                                </td>

                                
                                <td class="center">
                                    <template x-if="row.is_active">
                                        <span class="pm-badge badge-active">
                                            <span class="dot"></span> Active
                                        </span>
                                    </template>
                                    <template x-if="!row.is_active">
                                        <span class="pm-badge badge-inactive">
                                            <span class="dot"></span> Inactive
                                        </span>
                                    </template>
                                </td>

                                
                                <td class="right" style="padding-right:20px">
                                    <div style="display:inline-flex;align-items:center;gap:7px;">
                                        <button class="pm-action-btn edit" @click="openModal(row)" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button class="pm-action-btn del" @click="deleteMethod(row.id)" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </template>

                    
                    <tbody x-show="filteredMethods.length === 0">
                        <tr>
                            <td colspan="6">
                                <div class="pm-empty">
                                    <div class="pm-empty-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="pm-empty-title"
                                            x-text="search ? 'No results found' : 'No payment methods yet'"></div>
                                        <div class="pm-empty-sub"
                                            x-text="search ? 'Try a different search term.' : 'Click \'Add Method\' to create your first payment gateway.'">
                                        </div>
                                    </div>
                                    <button x-show="!search" class="pm-btn-add" @click="openModal()">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Add First Method
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div x-cloak x-show="showModal" class="pm-overlay" @click.self="closeModal()">
            <div class="pm-modal" @click.stop>

                
                <div class="pm-modal-header">
                    <div class="pm-modal-title-wrap">
                        <div class="pm-modal-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                            </svg>
                        </div>
                        <div>
                            <div class="pm-modal-title" x-text="isEdit ? 'Edit Payment Method' : 'Add Payment Method'">
                            </div>
                            <div class="pm-modal-sub"
                                x-text="isEdit ? 'Update gateway details and settings' : 'Configure a new payment gateway'">
                            </div>
                        </div>
                    </div>
                    <button class="pm-modal-close" @click="closeModal()" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                
                <div class="pm-modal-body">
                    <div class="pm-form-grid">

                        
                        <div class="pm-field span2">
                            <label>Display Label <span class="req">*</span></label>
                            <input type="text" x-model="form.label" class="pm-input"
                                placeholder="e.g. Credit / Debit Card" required @keydown.enter.prevent="saveMethod()">
                        </div>

                        
                        <div class="pm-field">
                            <label>URL Slug <span class="opt">(optional)</span></label>
                            <input type="text" x-model="form.slug" class="pm-input muted"
                                placeholder="Auto-generated if empty">
                        </div>

                        
                        <div class="pm-field">
                            <label>Payment Gateway <span class="opt">(optional)</span></label>
                            <input type="text" x-model="form.gateway" class="pm-input"
                                placeholder="e.g. razorpay, stripe">
                        </div>
                    </div>

                    <hr class="pm-divider" style="margin:20px 0 18px">

                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                        
                        <label class="pm-toggle-wrap">
                            <input type="checkbox" x-model="form.is_online" class="pm-toggle-sr">
                            <div class="pm-toggle-track" :class="form.is_online ? 'on' : ''">
                                <div class="pm-toggle-thumb" :class="form.is_online ? 'on' : ''"></div>
                            </div>
                            <div>
                                <div class="pm-toggle-lbl">Online Gateway</div>
                                <div class="pm-toggle-hint">Processes via internet</div>
                            </div>
                        </label>

                        
                        <label class="pm-toggle-wrap">
                            <input type="checkbox" x-model="form.is_active" class="pm-toggle-sr">
                            <div class="pm-toggle-track" :class="form.is_active ? 'on' : ''">
                                <div class="pm-toggle-thumb" :class="form.is_active ? 'on' : ''"></div>
                            </div>
                            <div>
                                <div class="pm-toggle-lbl">Active Status</div>
                                <div class="pm-toggle-hint">Visible to customers</div>
                            </div>
                        </label>
                    </div>
                </div>

               
                <div class="pm-modal-footer">
                    <button type="button" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold text-sm rounded-xl hover:bg-gray-50 transition-colors" @click="closeModal()" :disabled="isSaving">
                        Cancel
                    </button>
                    <button type="button" class="px-6 py-2.5 bg-brand-500 text-white font-bold text-sm rounded-xl hover:bg-brand-600 transition-all shadow-md active:scale-95 flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed" @click="saveMethod()" :disabled="isSaving">
                        <i data-lucide="loader-2" x-show="isSaving" class="w-4 h-4 animate-spin" style="display: none;"></i>
                        <span x-text="isSaving ? 'Saving…' : (isEdit ? 'Update Method' : 'Save Method')"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        function paymentMethodApp() {
            return {
                methods: <?php echo json_encode($paymentMethods ?? [], 15, 512) ?>,
                search: '',
                showModal: false,
                isEdit: false,
                isSaving: false,

                form: {
                    id: null,
                    label: '',
                    slug: '',
                    gateway: '',
                    is_online: false,
                    is_active: true
                },

                /* ── Computed filtered + sorted list ── */
                get filteredMethods() {
                    const sorted = this.methods.slice().sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
                    if (!this.search.trim()) return sorted;
                    const q = this.search.toLowerCase();
                    return sorted.filter(m =>
                        (m.label && m.label.toLowerCase().includes(q)) ||
                        (m.slug && m.slug.toLowerCase().includes(q)) ||
                        (m.gateway && m.gateway.toLowerCase().includes(q))
                    );
                },

                /* ── Init ── */
                boot() {
                    this.$nextTick(() => this.initSortable());
                },

                /* ── Drag-and-drop ── */
                initSortable() {
                    const table = document.getElementById('sortable-table');
                    if (!table) return;
                    const _this = this;

                    new Sortable(table, {
                        draggable: 'tbody.sortable-row',
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'sortable-ghost',

                        /* Prevent drag while searching */
                        onMove() {
                            return _this.search === '';
                        },

                        onEnd: async function(evt) {
                            if (evt.oldIndex === evt.newIndex) return;

                            /* Re-sync Alpine array to match new DOM order */
                            const rows = [...table.querySelectorAll('tbody.sortable-row[data-id]')];
                            const idOrder = rows.map(r => parseInt(r.dataset.id));

                            const reordered = idOrder
                                .map(id => _this.methods.find(m => m.id === id))
                                .filter(Boolean);

                            /* Fill in any items not currently rendered (e.g. if filtered) */
                            const renderedIds = new Set(idOrder);
                            _this.methods.filter(m => !renderedIds.has(m.id)).forEach(m => reordered.push(m));

                            /* Update sort_order values */
                            const payload = [];
                            reordered.forEach((m, i) => {
                                m.sort_order = i + 1;
                                payload.push({
                                    id: m.id,
                                    sort_order: m.sort_order
                                });
                            });
                            _this.methods = reordered;

                            /* Persist to backend */
                            try {
                                const res = await fetch("<?php echo e(route('admin.payment_methods.reorder')); ?>", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                                    },
                                    body: JSON.stringify({
                                        order: payload
                                    })
                                });
                                const result = await res.json();
                                result.success ?
                                    BizAlert.toast('Sort order saved!', 'success') :
                                    BizAlert.toast('Failed to save order.', 'error');
                            } catch (err) {
                                console.error('Reorder error:', err);
                                BizAlert.toast('Network error saving order.', 'error');
                            }
                        }
                    });
                },

                /* ── Modal ── */
                openModal(row = null) {
                    this.isEdit = !!row;
                    this.form = row ? {
                        ...row
                    } : {
                        id: null,
                        label: '',
                        slug: '',
                        gateway: '',
                        is_online: false,
                        is_active: true
                    };
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    setTimeout(() => {
                        this.isSaving = false;
                    }, 300);
                },

                /* ── Save (create / update) ── */
                async saveMethod() {
                    if (!this.form.label.trim()) {
                        BizAlert.toast('Display label is required.', 'error');
                        return;
                    }
                    this.isSaving = true;
                    const method = this.isEdit ? 'PUT' : 'POST';
                    const url = this.isEdit ?
                        `/admin/payment-methods/${this.form.id}` :
                        `/admin/payment-methods`;

                    try {
                        const res = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                            },
                            body: JSON.stringify(this.form)
                        });
                        const result = await res.json();

                        if (res.ok && result.success) {
                            if (this.isEdit) {
                                const idx = this.methods.findIndex(m => m.id === this.form.id);
                                if (idx !== -1) {
                                    result.data.sort_order = this.methods[idx].sort_order;
                                    this.methods.splice(idx, 1, result.data);
                                }
                            } else {
                                result.data.sort_order = this.methods.length ?
                                    Math.max(...this.methods.map(m => m.sort_order ?? 0)) + 1 :
                                    1;
                                this.methods.push(result.data);
                            }
                            BizAlert.toast(result.message, 'success');
                            this.closeModal();
                        } else {
                            BizAlert.toast(result.message || 'Validation failed.', 'error');
                        }
                    } catch (err) {
                        console.error('Save error:', err);
                        BizAlert.toast('Network error occurred.', 'error');
                    } finally {
                        this.isSaving = false;
                    }
                },

                /* ── Delete ── */
                async deleteMethod(id) {
                    const confirm = await BizAlert.confirm(
                        'Delete Payment Method?',
                        'This action cannot be undone.',
                        'Yes, Delete It',
                        'warning'
                    );
                    if (!confirm.isConfirmed) return;

                    try {
                        const res = await fetch(`/admin/payment-methods/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                            }
                        });
                        const result = await res.json();

                        if (res.ok && result.success) {
                            this.methods = this.methods.filter(m => m.id !== id);
                            BizAlert.toast(result.message, 'success');
                        } else {
                            BizAlert.toast(result.message || 'Failed to delete.', 'error');
                        }
                    } catch (err) {
                        BizAlert.toast('Network error occurred.', 'error');
                    }
                }
            };
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/payment_methods.blade.php ENDPATH**/ ?>