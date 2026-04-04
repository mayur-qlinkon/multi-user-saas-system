@extends('layouts.admin')

@section('title', 'My Dashboard')

@section('header-title')
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">My Dashboard</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $employee->employee_code }} · {{ $employee->department?->name }}</p>
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .stat-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        padding: 18px 20px;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        border-radius: 4px 0 0 4px;
    }
    .stat-card.blue::before  { background: #3b82f6; }
    .stat-card.green::before { background: #10b981; }
    .stat-card.amber::before { background: #f59e0b; }
    .stat-card.purple::before{ background: #8b5cf6; }

    .quick-action-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        padding: 28px 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color 180ms ease, box-shadow 180ms ease, transform 120ms ease;
        text-decoration: none;
        display: block;
    }
    .quick-action-card:hover {
        border-color: var(--brand-600);
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .quick-action-card.featured {
        border-color: var(--brand-600);
        background: linear-gradient(135deg, color-mix(in srgb, var(--brand-600) 6%, #fff), #fff);
    }
    .qa-icon {
        width: 56px; height: 56px;
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
    }

    /* QR Scanner Modal */
    .scan-modal-backdrop {
        position: fixed; inset: 0;
        background: rgba(10, 15, 30, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex; align-items: center; justify-content: center;
        padding: 16px;
    }
    .scan-modal-box {
        background: #fff;
        border-radius: 24px;
        width: 100%; max-width: 420px;
        box-shadow: 0 24px 80px rgba(0,0,0,0.22);
        overflow: hidden;
    }
    #qr-reader { width: 100%; }
    #qr-reader video { border-radius: 0; }
    #qr-reader__scan_region { background: transparent !important; }
    #qr-reader__dashboard_section_swaplink { display: none !important; }

    .section-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        overflow: hidden;
    }
    .section-header {
        padding: 14px 18px;
        border-bottom: 1px solid #f8fafc;
        display: flex; align-items: center; gap: 10px;
    }
    .section-icon {
        width: 30px; height: 30px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')

@php
    $empName   = $employee->user?->name ?? 'Employee';
    $firstName = explode(' ', $empName)[0];
    $checkedIn  = $todayAttendance?->check_in_time;
    $checkedOut = $todayAttendance?->check_out_time;

    if ($checkedOut)     { $todayStatus = 'Checked Out'; $todayTime = $todayAttendance->check_out_time->format('h:i A'); $statusColor = 'text-gray-500'; }
    elseif ($checkedIn)  { $todayStatus = 'Checked In';  $todayTime = $todayAttendance->check_in_time->format('h:i A'); $statusColor = 'text-green-600'; }
    else                 { $todayStatus = 'Not Checked In'; $todayTime = null; $statusColor = 'text-gray-400'; }
@endphp

<div x-data="empDashboard()" x-init="init()" class="w-full pb-10 space-y-6">

    {{-- ── Greeting ── --}}
    <div class="flex items-end justify-between">
        <div>
            <p class="text-[22px] font-black text-gray-900">Welcome back, {{ $firstName }}!</p>
            <p class="text-[13px] text-gray-400 mt-0.5">{{ $employee->designation?->name }}
                @if($employee->department) · {{ $employee->department->name }} @endif
            </p>
        </div>
        <p class="text-[13px] font-semibold text-gray-400 hidden sm:block">{{ now()->format('l, d M Y') }}</p>
    </div>

    {{-- ── 4 Stat Cards ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="stat-card blue">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Today's Status</p>
            <p class="text-[17px] font-black {{ $statusColor }}">{{ $todayStatus }}</p>
            @if($todayTime)
            <p class="text-[12px] text-gray-400 mt-1">at {{ $todayTime }}</p>
            @endif
            <div class="absolute right-4 top-4 w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                <i data-lucide="clock" class="w-5 h-5 text-blue-400"></i>
            </div>
        </div>

        <div class="stat-card green">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Assigned Tasks</p>
            <p class="text-[26px] font-black text-gray-900 leading-none">{{ $assignedTaskCount }}</p>
            <p class="text-[12px] text-gray-400 mt-1">tasks assigned</p>
            <div class="absolute right-4 top-4 w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-green-400"></i>
            </div>
        </div>

        <div class="stat-card amber">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Pending Leaves</p>
            <p class="text-[26px] font-black text-gray-900 leading-none">{{ $pendingLeaveCount }}</p>
            <p class="text-[12px] text-gray-400 mt-1">awaiting approval</p>
            <div class="absolute right-4 top-4 w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <i data-lucide="hourglass" class="w-5 h-5 text-amber-400"></i>
            </div>
        </div>

        <div class="stat-card purple">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Present this Month</p>
            <p class="text-[26px] font-black text-gray-900 leading-none">{{ $presentThisMonth }}</p>
            <p class="text-[12px] text-gray-400 mt-1">days in {{ now()->format('M') }}</p>
            <div class="absolute right-4 top-4 w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center">
                <i data-lucide="trending-up" class="w-5 h-5 text-purple-400"></i>
            </div>
        </div>

    </div>

    {{-- ── Quick Actions ── --}}
    <div>
        <p class="text-[13px] font-black text-gray-800 mb-3">Quick Actions</p>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Mark Attendance --}}
            <button @click="openScanner()" class="quick-action-card featured text-left w-full">
                <div class="qa-icon" style="background: color-mix(in srgb, var(--brand-600) 12%, #fff)">
                    <i data-lucide="qr-code" class="w-7 h-7" style="color: var(--brand-600)"></i>
                </div>
                <p class="text-[14px] font-black text-gray-900">Mark Attendance</p>
                <p class="text-[12px] text-gray-400 mt-1">Scan QR code to check-in/out</p>
                <p class="text-[12px] font-bold mt-3" style="color: var(--brand-600)">Go →</p>
            </button>

            {{-- Leave Requests --}}
            <a href="{{ route('admin.hrm.my-leaves.index') }}" class="quick-action-card">
                <div class="qa-icon bg-green-50">
                    <i data-lucide="calendar-off" class="w-7 h-7 text-green-500"></i>
                </div>
                <p class="text-[14px] font-black text-gray-900">Leave Requests</p>
                <p class="text-[12px] text-gray-400 mt-1">Apply for leave, check status</p>
            </a>

            {{-- Salary Slips --}}
            <a href="{{ route('admin.hrm.my-salary-slips.index') }}" class="quick-action-card">
                <div class="qa-icon bg-amber-50">
                    <i data-lucide="banknote" class="w-7 h-7 text-amber-500"></i>
                </div>
                <p class="text-[14px] font-black text-gray-900">Salary Slips</p>
                <p class="text-[12px] text-gray-400 mt-1">Download monthly payslips</p>
            </a>

            {{-- My Tasks --}}
            <a href="{{ route('admin.hrm.my-tasks.index') }}" class="quick-action-card">
                <div class="qa-icon bg-purple-50">
                    <i data-lucide="clipboard-list" class="w-7 h-7 text-purple-500"></i>
                </div>
                <p class="text-[14px] font-black text-gray-900">My Tasks</p>
                <p class="text-[12px] text-gray-400 mt-1">View & manage assigned tasks</p>
            </a>

        </div>
    </div>

    {{-- ── Recent Attendance + Recent Leaves ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Recent Attendance --}}
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon bg-blue-50">
                    <i data-lucide="history" class="w-4 h-4 text-blue-500"></i>
                </div>
                <p class="text-[13px] font-black text-gray-800">Recent Attendance</p>
                <a href="{{ route('admin.hrm.my-attendance.index') }}"
                    class="ml-auto text-[11px] font-bold text-blue-600 hover:text-blue-800">
                    View Full History
                </a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentAttendance as $att)
                @php $sc = \App\Models\Hrm\Attendance::STATUS_COLORS[$att->status] ?? ['bg'=>'#f3f4f6','text'=>'#374151','dot'=>'#9ca3af']; @endphp
                <div class="flex items-center justify-between px-4 py-3">
                    <div>
                        <p class="text-[13px] font-bold text-gray-700">{{ $att->date->format('M d, Y') }}</p>
                        <p class="text-[11px] text-gray-400">{{ $att->date->format('l') }}</p>
                    </div>
                    <div class="text-right flex items-center gap-3">
                        @if($att->check_in_time)
                        <p class="text-[11px] text-gray-400">{{ $att->check_in_time->format('h:i A') }}</p>
                        @endif
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-lg"
                            style="background: {{ $sc['bg'] }}; color: {{ $sc['text'] }}">
                            {{ \App\Models\Hrm\Attendance::STATUS_LABELS[$att->status] ?? $att->status }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="px-4 py-8 text-center text-[13px] text-gray-400">No attendance records yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Leaves --}}
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon bg-green-50">
                    <i data-lucide="plane-takeoff" class="w-4 h-4 text-green-500"></i>
                </div>
                <p class="text-[13px] font-black text-gray-800">Recent Leaves</p>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentLeaves as $leave)
                @php $sc = \App\Models\Hrm\Leave::STATUS_COLORS[$leave->status]; @endphp
                <div class="flex items-center justify-between px-4 py-3">
                    <div>
                        <p class="text-[13px] font-bold text-gray-800">{{ $leave->leaveType?->name }}</p>
                        <p class="text-[11px] text-gray-400">
                            {{ $leave->from_date->format('M d') }} – {{ $leave->to_date->format('M d') }}
                        </p>
                    </div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-lg"
                        style="background: {{ $sc['bg'] }}; color: {{ $sc['text'] }}">
                        {{ \App\Models\Hrm\Leave::STATUS_LABELS[$leave->status] }}
                    </span>
                </div>
                @empty
                <p class="px-4 py-8 text-center text-[13px] text-gray-400">No leave requests yet.</p>
                @endforelse
            </div>
            <div class="px-4 py-3 border-t border-gray-50">
                <a href="{{ route('admin.hrm.my-leaves.index') }}"
                    class="text-[12px] font-black" style="color: var(--brand-600)">
                    Manage Leaves →
                </a>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════
         QR SCANNER MODAL — must stay INSIDE x-data wrapper
    ══════════════════════════════════════════════════════ --}}
    <template x-teleport="body">
    <div x-show="showScanner" x-cloak class="scan-modal-backdrop" @click.self="closeScanner()">
        <div class="scan-modal-box" @click.stop>

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <p class="text-[15px] font-black text-gray-900">Mark Attendance</p>
                    <p class="text-[12px] text-gray-400 mt-0.5" x-text="scanStatus"></p>
                </div>
                <button @click="closeScanner()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Scanner area --}}
            <div class="p-4">

                {{-- Camera / QR Scanner --}}
                <div x-show="scanStep === 'scan'">
                    <p class="text-[12px] text-gray-400 text-center mb-3">Point your camera at the attendance QR code</p>
                    <div id="qr-reader" class="rounded-xl overflow-hidden" style="min-height: 280px;"></div>
                </div>

                {{-- Processing --}}
                <div x-show="scanStep === 'processing'" class="text-center py-8">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-4 animate-pulse">
                        <i data-lucide="loader" class="w-7 h-7 text-blue-500"></i>
                    </div>
                    <p class="text-[14px] font-black text-gray-800">Processing...</p>
                    <p class="text-[12px] text-gray-400 mt-1">Marking your attendance</p>
                </div>

                {{-- Success --}}
                <div x-show="scanStep === 'success'" class="text-center py-6">
                    <div class="w-16 h-16 rounded-2xl bg-green-50 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check-circle-2" class="w-8 h-8 text-green-500"></i>
                    </div>
                    <p class="text-[16px] font-black text-gray-900 mb-1" x-text="successMessage"></p>
                    <p class="text-[12px] text-gray-400">Attendance has been recorded successfully.</p>
                    <button @click="closeScanner()"
                        class="mt-5 px-6 py-2.5 text-[13px] font-black text-white rounded-xl border-none cursor-pointer"
                        style="background: var(--brand-600)">
                        Done
                    </button>
                </div>

                {{-- Error --}}
                <div x-show="scanStep === 'error'" class="text-center py-6">
                    <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-circle" class="w-7 h-7 text-red-500"></i>
                    </div>
                    <p class="text-[14px] font-black text-gray-800 mb-1">Scan Failed</p>
                    <p class="text-[12px] text-gray-400 mb-5" x-text="errorMessage"></p>
                    <button @click="retryScanner()"
                        class="px-6 py-2.5 text-[13px] font-black text-white rounded-xl border-none cursor-pointer"
                        style="background: var(--brand-600)">
                        Try Again
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

</div>{{-- /x-data="empDashboard()" --}}

@endsection

@push('scripts')
{{-- html5-qrcode — production-grade QR scanner library --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
function empDashboard() {
    // 🌟 CRITICAL FIX: Store the scanner instance OUTSIDE the reactive Alpine object.
    // This prevents Alpine's Proxy system from breaking the camera's internal feed.
    let scannerInstance = null;

    return {
        showScanner: false,
        scanStep: 'location',   // location | scan | processing | success | error
        scanStatus: 'Allow location access to continue',
        successMessage: '',
        errorMessage: '',
        latitude: null,
        longitude: null,    
        isProcessingScan: false,        
        forceCheckout: false,

        init() {
            if (window.lucide) lucide.createIcons();
        },

        async openScanner() {
            // Check for mandatory announcements before allowing scan
            try {
                const res = await fetch('{{ route("admin.announcements-popup.pending") }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.mandatory_count > 0) {
                        window.dispatchEvent(new CustomEvent('announcements:recheck'));
                        if (typeof BizAlert !== 'undefined') {
                            BizAlert.toast('Please acknowledge all mandatory announcements before marking attendance.', 'error');
                        }
                        return;
                    }
                }
            } catch (e) {
                console.warn('Announcement check failed, proceeding with scanner:', e);
            }

            // Reset states
            this.showScanner = true;
            this.errorMessage = '';
            this.successMessage = '';
            
            // ── NEW LOGIC: Check permissions silently ──
            if (navigator.permissions) {
                try {
                    const permission = await navigator.permissions.query({ name: 'geolocation' });
                    
                    if (permission.state === 'granted') {
                        // Permission already given! Skip the button and fetch location directly
                        this.scanStatus = 'Getting your location...';
                        this.requestLocation();
                        return; 
                    } else if (permission.state === 'denied') {
                        // Permission blocked in browser settings
                        this.scanStep = 'error';
                        this.errorMessage = 'Location access is blocked. Please enable it in your browser settings and try again.';
                        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                        return;
                    }
                    // If state is 'prompt', it will fall through to show the UI button
                } catch (e) {
                    console.warn("Permissions API not fully supported, falling back to UI prompt.");
                }
            }

            // Fallback: Show the UI button for first-time users
            this.scanStep = 'location';
            this.scanStatus = 'Allow location access to continue';
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        closeScanner() {
            this.stopCamera();
            this.showScanner = false;
        },

        stopCamera() {
            if (scannerInstance) {
                try {
                    // Stop the hardware feed and clear the canvas safely
                    scannerInstance.stop().catch(() => {});
                    scannerInstance.clear();
                } catch(e) {}
                scannerInstance = null;
            }
        },

        requestLocation() {
            if (!navigator.geolocation) {
                this.scanStep = 'error';
                this.errorMessage = 'Geolocation is not supported by your browser.';
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                return;
            }
            this.scanStatus = 'Getting your location...';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.latitude  = pos.coords.latitude;
                    this.longitude = pos.coords.longitude;
                    this.startCamera();
                },
                (err) => {
                    this.scanStep = 'error';
                    if (err.code === err.PERMISSION_DENIED) {
                        this.errorMessage = 'Location access denied. Please enable GPS in your browser settings and try again.';
                    } else if (err.code === err.TIMEOUT) {
                        this.errorMessage = 'Location request timed out. Please check your GPS signal and try again.';
                    } else {
                        this.errorMessage = 'Unable to get your location. Please enable GPS and try again.';
                    }
                    this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        },

       startCamera() {
            this.stopCamera();
            this.scanStep = 'scan';
            this.scanStatus = 'Scan the QR code shown at reception';
            // 🌟 RESET the lock when camera starts
            this.isProcessingScan = false; 
            
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
                if (typeof Html5Qrcode === 'undefined') { /* error handling */ return; }

                scannerInstance = new Html5Qrcode('qr-reader');
                const config = {
                    fps: 10,
                    qrbox: (vw, vh) => {
                        const minEdge = Math.min(vw, vh);
                        return { width: minEdge * 0.7, height: minEdge * 0.7 };
                    }
                };

                scannerInstance.start(
                    { facingMode: 'environment' },
                    config,
                    (decodedText) => {
                        // 🌟 THE FIX: If we are already processing a scan, ignore everything else!
                        if (this.isProcessingScan) return; 
                        
                        this.isProcessingScan = true; // Lock the door
                        this.stopCamera();
                        this.submitScan(decodedText);
                    },
                    (errorMessage) => {} 
                ).catch((err) => { /* error handling */ });
            });
        },

        /**
         * Extract store_id from QR content.
         * Supports: URL with /attend/{id}, ?store_id={id}, or plain number.
         */
        parseStoreId(qrText) {
            // URL pattern: /attend/{id}
            const attendMatch = qrText.match(/\/attend\/(\d+)/);
            if (attendMatch) return parseInt(attendMatch[1]);

            // Query param: ?store_id={id}
            try {
                const url = new URL(qrText);
                const sid = url.searchParams.get('store_id');
                if (sid) return parseInt(sid);
            } catch {}

            // Plain number
            if (/^\d+$/.test(qrText.trim())) return parseInt(qrText.trim());

            return null;
        },

       async submitScan(qrData) {
            const storeId = this.parseStoreId(qrData);
            if (!storeId) {
                this.scanStep = 'error';
                this.errorMessage = 'Invalid QR code. Please scan the attendance QR at your office.';
                this.isProcessingScan = false; // 🌟 Unlock if invalid
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                return;
            }

            this.scanStep = 'processing';
            this.scanStatus = 'Recording attendance...';
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });

            try {
                const res = await fetch('{{ route("admin.hrm.attendance.scan-store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'X-Device-Info': navigator.userAgent.substring(0, 200),
                    },
                    body: JSON.stringify({
                        store_id:  storeId,
                        latitude:  this.latitude,
                        longitude: this.longitude,
                        force_checkout: this.forceCheckout // 🌟 Pass the flag to Laravel
                    }),
                });

                if (res.status === 429) {
                    throw new Error('Too many attempts. Please wait a minute and try again.');
                }

                const data = await res.json();

                // 🌟 SCENARIO E: Backend says "Hold up, leaving early?"
                if (data.requires_confirmation) {
                    this.scanStep = 'location'; // Reset view in background
                    this.closeScanner();
                    this.isProcessingScan = false;
                    
                    Swal.fire({
                        title: 'Leave Early?', 
                        text: data.message, 
                        icon: 'warning', 
                        showCancelButton: true, 
                        confirmButtonColor: '#ef4444', 
                        cancelButtonColor: '#6c757d', 
                        confirmButtonText: 'Yes, Checkout Now'
                    }).then((result) => { 
                        if (result.isConfirmed) {
                            // User clicked Yes. Set flag and re-submit bypassing the camera!
                            this.forceCheckout = true;
                            this.showScanner = true; 
                            this.submitScan(qrData); 
                        }
                    });
                    return;
                }

                if (data.success) {
                    this.scanStep = 'success';
                    this.successMessage = data.message;
                    this.scanStatus = 'Attendance recorded!';
                    
                    // 🌟 Optional: Pop a SweetAlert to show the specific warning/success colors
                    Swal.fire({
                        icon: data.type === 'error' ? 'error' : (data.type === 'warning' ? 'warning' : 'success'),
                        title: data.type === 'warning' ? 'Warning' : 'Success',
                        text: data.message,
                        timer: 3000,
                        showConfirmButton: false
                    });

                    setTimeout(() => window.location.reload(), 2500);
                } else {
                    this.scanStep = 'error';
                    this.errorMessage = data.message || 'Something went wrong.';
                    this.scanStatus = 'Scan failed';
                    this.isProcessingScan = false;
                }
            } catch (e) {
                this.scanStep = 'error';
                this.errorMessage = e.message === 'Too many attempts. Please wait a minute and try again.' ? e.message : 'Network error. Please check your connection.';
                this.scanStatus = 'Scan failed';
                this.isProcessingScan = false; // 🌟 Unlock on network error
            }
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        retryScanner() {
            this.stopCamera();
            this.latitude  = null;
            this.longitude = null;
            this.isProcessingScan = false; // 🌟 Ensure unlock when retrying
            this.openScanner();
        },
    };
}
</script>
@endpush
