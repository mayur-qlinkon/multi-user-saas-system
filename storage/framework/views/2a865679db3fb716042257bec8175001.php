<?php $__env->startSection('title', 'My Dashboard'); ?>

<?php $__env->startSection('header-title'); ?>
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">My Dashboard</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5"><?php echo e($employee->employee_code); ?> · <?php echo e($employee->department?->name); ?></p>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] { display: none !important; }

    /* Minimal App Grid Card (Replaces Stat & Quick Action Cards) */
    .app-grid-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px 8px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        aspect-ratio: 1 / 1; /* Makes them perfectly square */
        position: relative;
        transition: transform 100ms ease, background 150ms ease;
    }
    .app-grid-card:active { background: #f9fafb; transform: scale(0.97); }
    .app-grid-icon {
        color: #4b5563; /* Slate outline icon color like screenshot */
        margin-bottom: 12px;
        stroke-width: 1.5px;
    }
    /* Horizontal Text Scrolling Animation */
    .marquee-wrapper {
        width: 100%;
        overflow: hidden;
        padding: 0 4px;
    }
    .marquee-text {
        display: inline-block;
        white-space: nowrap;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
    }
    .animate-marquee {
        animation: marquee 4s linear infinite;
    }
    @keyframes marquee {
        0%, 20% { transform: translateX(0); }
        80%, 100% { transform: translateX(calc(-100% + 70px)); } /* 70px is approx visible text width */
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php
    $empName   = $employee->user?->name ?? 'Employee';
    $firstName = explode(' ', $empName)[0];
    $checkedIn  = $todayAttendance?->check_in_time;
    $checkedOut = $todayAttendance?->check_out_time;

    if ($checkedOut)     { $todayStatus = 'Checked Out'; $todayTime = $todayAttendance->check_out_time->format('h:i A'); $statusColor = 'text-gray-500'; }
    elseif ($checkedIn)  { $todayStatus = 'Checked In';  $todayTime = $todayAttendance->check_in_time->format('h:i A'); $statusColor = 'text-green-600'; }
    else                 { $todayStatus = 'Not Checked In'; $todayTime = null; $statusColor = 'text-gray-400'; }
?>

<div x-data="empDashboard()" x-init="init()" class="w-full pb-10 space-y-6">

    
    <div class="flex items-end justify-between">
        <div>
            <p class="text-[22px] font-black text-gray-900">Welcome back, <?php echo e($firstName); ?>!</p>
            <p class="text-[13px] text-gray-400 mt-0.5"><?php echo e($employee->designation?->name); ?>

                <?php if($employee->department): ?> · <?php echo e($employee->department->name); ?> <?php endif; ?>
            </p>
        </div>
        <p class="text-[13px] font-semibold text-gray-400 hidden sm:block"><?php echo e(now()->format('l, d M Y')); ?></p>
    </div>

    
    <div>
        <p class="text-[13px] font-black text-gray-800 mb-3">Dashboard Menu</p>
        <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            
            <div class="app-grid-card relative">
                <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full <?php echo e($checkedIn && !$checkedOut ? 'bg-green-500' : 'bg-gray-300'); ?>"></span>
                <i data-lucide="clock" class="w-7 h-7 app-grid-icon"></i>
                <div class="marquee-wrapper mt-1">
                    <span class="marquee-text <?php echo e(strlen($todayStatus) > 10 ? 'animate-marquee' : ''); ?>"><?php echo e($todayStatus); ?></span>
                </div>
            </div>
            
            <div class="app-grid-card relative">
                <span class="absolute top-2 right-2 text-[9px] font-black text-white bg-blue-500 px-1.5 py-0.5 rounded-full shadow-sm"><?php echo e($assignedTaskCount); ?></span>
                <i data-lucide="clipboard-list" class="w-7 h-7 app-grid-icon"></i>
                <div class="marquee-wrapper mt-1">
                    <span class="marquee-text">Tasks</span>
                </div>
            </div>
            
            <div class="app-grid-card relative">
                <?php if($pendingLeaveCount > 0): ?>
                <span class="absolute top-2 right-2 text-[9px] font-black text-white bg-amber-500 px-1.5 py-0.5 rounded-full shadow-sm"><?php echo e($pendingLeaveCount); ?></span>
                <?php endif; ?>
                <i data-lucide="hourglass" class="w-7 h-7 app-grid-icon"></i>
                <div class="marquee-wrapper mt-1">
                    <span class="marquee-text <?php echo e(strlen('Pending Leaves') > 10 ? 'animate-marquee' : ''); ?>">Pending Leaves</span>
                </div>
            </div>
            
            <div class="app-grid-card relative">
                <span class="absolute top-2 right-2 text-[9px] font-black text-white bg-purple-500 px-1.5 py-0.5 rounded-full shadow-sm"><?php echo e($presentThisMonth); ?></span>
                <i data-lucide="trending-up" class="w-7 h-7 app-grid-icon"></i>
                <div class="marquee-wrapper mt-1">
                    <span class="marquee-text <?php echo e(strlen('Days Present') > 10 ? 'animate-marquee' : ''); ?>">Days Present</span>
                </div>
            </div>
            
            <button @click="openScanner()" class="app-grid-card relative w-full border-brand-200" style="background: color-mix(in srgb, var(--brand-600) 4%, #fff)">
                <span class="absolute top-2 right-2 w-2 h-2 rounded-full animate-pulse" style="background: var(--brand-600)"></span>
                <i data-lucide="qr-code" class="w-7 h-7 app-grid-icon" style="color: var(--brand-600)"></i>
                <div class="marquee-wrapper mt-1">
                    <span class="marquee-text" style="color: var(--brand-700)">Scan QR</span>
                </div>
            </button>
            
            <a href="<?php echo e(route('admin.hrm.my-leaves.index')); ?>" class="app-grid-card relative">
                <i data-lucide="calendar-off" class="w-7 h-7 app-grid-icon"></i>
                <div class="marquee-wrapper mt-1">
                    <span class="marquee-text <?php echo e(strlen('Apply Leave') > 10 ? 'animate-marquee' : ''); ?>">Apply Leave</span>
                </div>
            </a>
            
            <a href="<?php echo e(route('admin.hrm.my-salary-slips.index')); ?>" class="app-grid-card relative">
                <i data-lucide="banknote" class="w-7 h-7 app-grid-icon"></i>
                <div class="marquee-wrapper mt-1">
                    <span class="marquee-text">Payslips</span>
                </div>
            </a>
            
            <a href="<?php echo e(route('admin.hrm.my-tasks.index')); ?>" class="app-grid-card relative">
                <i data-lucide="check-square" class="w-7 h-7 app-grid-icon"></i>
                <div class="marquee-wrapper mt-1">
                    <span class="marquee-text">My Tasks</span>
                </div>
            </a>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon bg-blue-50">
                    <i data-lucide="history" class="w-4 h-4 text-blue-500"></i>
                </div>
                <p class="text-[13px] font-black text-gray-800">Recent Attendance</p>
                <a href="<?php echo e(route('admin.hrm.my-attendance.index')); ?>"
                    class="ml-auto text-[11px] font-bold text-blue-600 hover:text-blue-800">
                    View Full History
                </a>
            </div>
            <div class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $recentAttendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $sc = \App\Models\Hrm\Attendance::STATUS_COLORS[$att->status] ?? ['bg'=>'#f3f4f6','text'=>'#374151','dot'=>'#9ca3af']; ?>
                <div class="flex items-center justify-between px-4 py-3">
                    <div>
                        <p class="text-[13px] font-bold text-gray-700"><?php echo e($att->date->format('M d, Y')); ?></p>
                        <p class="text-[11px] text-gray-400"><?php echo e($att->date->format('l')); ?></p>
                    </div>
                    <div class="text-right flex items-center gap-3">
                        <?php if($att->check_in_time): ?>
                        <p class="text-[11px] text-gray-400"><?php echo e($att->check_in_time->format('h:i A')); ?></p>
                        <?php endif; ?>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-lg"
                            style="background: <?php echo e($sc['bg']); ?>; color: <?php echo e($sc['text']); ?>">
                            <?php echo e(\App\Models\Hrm\Attendance::STATUS_LABELS[$att->status] ?? $att->status); ?>

                        </span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="px-4 py-8 text-center text-[13px] text-gray-400">No attendance records yet.</p>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon bg-green-50">
                    <i data-lucide="plane-takeoff" class="w-4 h-4 text-green-500"></i>
                </div>
                <p class="text-[13px] font-black text-gray-800">Recent Leaves</p>
            </div>
            <div class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $recentLeaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $sc = \App\Models\Hrm\Leave::STATUS_COLORS[$leave->status]; ?>
                <div class="flex items-center justify-between px-4 py-3">
                    <div>
                        <p class="text-[13px] font-bold text-gray-800"><?php echo e($leave->leaveType?->name); ?></p>
                        <p class="text-[11px] text-gray-400">
                            <?php echo e($leave->from_date->format('M d')); ?> – <?php echo e($leave->to_date->format('M d')); ?>

                        </p>
                    </div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-lg"
                        style="background: <?php echo e($sc['bg']); ?>; color: <?php echo e($sc['text']); ?>">
                        <?php echo e(\App\Models\Hrm\Leave::STATUS_LABELS[$leave->status]); ?>

                    </span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="px-4 py-8 text-center text-[13px] text-gray-400">No leave requests yet.</p>
                <?php endif; ?>
            </div>
            <div class="px-4 py-3 border-t border-gray-50">
                <a href="<?php echo e(route('admin.hrm.my-leaves.index')); ?>"
                    class="text-[12px] font-black" style="color: var(--brand-600)">
                    Manage Leaves →
                </a>
            </div>
        </div>

    </div>

    
    <template x-teleport="body">
    <div x-show="showScanner" x-cloak class="scan-modal-backdrop" @click.self="closeScanner()">
        <div class="scan-modal-box" @click.stop>

            
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

            
            <div class="p-4">

                
                <div x-show="scanStep === 'scan'">
                    <p class="text-[12px] text-gray-400 text-center mb-3">Point your camera at the attendance QR code</p>
                    <div id="qr-reader" class="rounded-xl overflow-hidden" style="min-height: 280px;"></div>
                </div>

                
                <div x-show="scanStep === 'processing'" class="text-center py-8">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-4 animate-pulse">
                        <i data-lucide="loader" class="w-7 h-7 text-blue-500"></i>
                    </div>
                    <p class="text-[14px] font-black text-gray-800">Processing...</p>
                    <p class="text-[12px] text-gray-400 mt-1">Marking your attendance</p>
                </div>

                
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

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>


<script src="<?php echo e(asset('assets/js/html5-qrcode.min.js')); ?>"></script>

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
                const res = await fetch('<?php echo e(route("admin.announcements-popup.pending")); ?>', {
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

            // Reset states and go directly to location → camera
            this.showScanner = true;
            this.errorMessage = '';
            this.successMessage = '';
            this.forceCheckout = false;
            this.scanStatus = 'Getting your location...';
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            this.requestLocation();
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
         * Only supports the standard format: /attend/{id}
         */
        parseStoreId(qrText) {
            const attendMatch = qrText.match(/\/attend\/(\d+)/);
            if (attendMatch) return parseInt(attendMatch[1]);
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
                const res = await fetch('<?php echo e(route("admin.hrm.attendance.scan")); ?>', {
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

                // Backend says "leaving early?" — ask for confirmation
                if (data.requires_confirmation) {
                    this.isProcessingScan = false;
                    this.closeScanner();

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
                            // Set flag then restart scanner for a fresh scan
                            this.forceCheckout = true;
                            this.showScanner = true;
                            this.startCamera();
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/hrm/employee/dashboard.blade.php ENDPATH**/ ?>