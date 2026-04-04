<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip — {{ $salarySlip->slip_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background: #fff;
            padding: 32px;
        }

        /* A4 print safety */
        @page { size: A4; margin: 20mm; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }

        /* ---- Header ---- */
        .slip-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
            border-bottom: 2px solid #1f2937;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.3px;
        }

        .company-tagline {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }

        .slip-title-box {
            text-align: right;
        }

        .slip-title {
            font-size: 16px;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .slip-number {
            font-size: 11px;
            color: #6b7280;
            margin-top: 3px;
            font-family: monospace;
        }

        .period-badge {
            display: inline-block;
            margin-top: 5px;
            background: #f3f4f6;
            color: #374151;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 4px;
        }

        /* ---- Employee Info Box ---- */
        .emp-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .emp-box-left {
            padding: 14px 16px;
            border-right: 1px solid #e5e7eb;
        }

        .emp-box-right {
            padding: 14px 16px;
        }

        .emp-name {
            font-size: 15px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 6px;
        }

        .emp-field {
            display: flex;
            gap: 6px;
            margin-bottom: 4px;
            font-size: 11.5px;
        }

        .emp-field-label {
            color: #9ca3af;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            min-width: 90px;
        }

        .emp-field-value {
            color: #374151;
            font-weight: 600;
        }

        /* ---- Section heading ---- */
        .section-heading {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #f3f4f6;
        }

        /* ---- Earnings / Deductions tables side by side ---- */
        .comp-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .comp-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .comp-table thead tr {
            background: #f9fafb;
        }

        .comp-table th {
            padding: 8px 10px;
            font-size: 10px;
            font-weight: 800;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        .comp-table th:last-child,
        .comp-table td:last-child {
            text-align: right;
        }

        .comp-table td {
            padding: 7px 10px;
            font-size: 12px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }

        .comp-table tr:last-child td {
            border-bottom: none;
        }

        .comp-table tfoot tr {
            background: #f9fafb;
            border-top: 1.5px solid #e5e7eb;
        }

        .comp-table tfoot td {
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 800;
            color: #1f2937;
            border-bottom: none;
        }

        .amount-earn { color: #065f46; font-weight: 700; }
        .amount-ded  { color: #991b1b; font-weight: 700; }

        /* ---- Summary Box ---- */
        .summary-box {
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 12px;
        }

        .summary-row:last-child { border-bottom: none; }

        .summary-label {
            color: #6b7280;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.05em;
        }

        .summary-value {
            font-weight: 700;
            color: #1f2937;
        }

        .net-row {
            background: #1f2937;
            padding: 14px 16px;
        }

        .net-label {
            color: #d1d5db;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .net-amount {
            color: #fff;
            font-size: 20px;
            font-weight: 900;
        }

        /* ---- Status Bar ---- */
        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .status-pill {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 3px 10px;
            border-radius: 4px;
        }

        /* ---- Footer ---- */
        .slip-footer {
            border-top: 1px dashed #e5e7eb;
            padding-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #9ca3af;
        }

        .footer-note {
            font-style: italic;
        }

        /* ---- Print button (no-print) ---- */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 24px;
            background: #1f2937;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.18);
        }

        .print-btn:hover { background: #374151; }
    </style>
</head>
<body>

@php
    use Illuminate\Support\Facades\Auth;
    $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
    $companyName = Auth::user()?->company?->name ?? 'Your Company';
    $empName     = $salarySlip->employee->user?->name ?? '—';
    $sc          = \App\Models\Hrm\SalarySlip::STATUS_COLORS[$salarySlip->status] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
    $earnings    = $salarySlip->items->where('type', 'earning');
    $deductions  = $salarySlip->items->where('type', 'deduction');
@endphp

{{-- Print Button (hidden on print) --}}
<button class="print-btn no-print" onclick="window.print()">
    &#x2399; Print / Save PDF
</button>

{{-- ============================================================
     HEADER
     ============================================================ --}}
<div class="slip-header">
    <div>
        <div class="company-name">{{ $companyName }}</div>
        <div class="company-tagline">Payroll Department</div>
    </div>
    <div class="slip-title-box">
        <div class="slip-title">Salary Slip</div>
        <div class="slip-number">{{ $salarySlip->slip_number }}</div>
        <div class="period-badge">
            {{ $months[$salarySlip->month] ?? $salarySlip->month }} {{ $salarySlip->year }}
        </div>
    </div>
</div>

{{-- ============================================================
     EMPLOYEE INFO
     ============================================================ --}}
<div class="emp-box">
    <div class="emp-box-left">
        <div class="emp-name">{{ $empName }}</div>
        <div class="emp-field">
            <span class="emp-field-label">Employee Code</span>
            <span class="emp-field-value">{{ $salarySlip->employee->employee_code ?? '—' }}</span>
        </div>
        <div class="emp-field">
            <span class="emp-field-label">Department</span>
            <span class="emp-field-value">{{ $salarySlip->employee->department?->name ?? '—' }}</span>
        </div>
        <div class="emp-field">
            <span class="emp-field-label">Designation</span>
            <span class="emp-field-value">{{ $salarySlip->employee->designation?->name ?? '—' }}</span>
        </div>
    </div>
    <div class="emp-box-right">
        <div class="emp-field" style="margin-top:4px">
            <span class="emp-field-label">PAN Number</span>
            <span class="emp-field-value" style="font-family:monospace">{{ $salarySlip->employee->pan_number ?? '—' }}</span>
        </div>
        <div class="emp-field">
            <span class="emp-field-label">Bank Account</span>
            <span class="emp-field-value" style="font-family:monospace">
                @if($salarySlip->employee->bank_account_number)
                    ****{{ substr($salarySlip->employee->bank_account_number, -4) }}
                @else
                    —
                @endif
            </span>
        </div>
        <div class="emp-field">
            <span class="emp-field-label">Working Days</span>
            <span class="emp-field-value">{{ $salarySlip->working_days ?? '—' }}</span>
        </div>
        <div class="emp-field">
            <span class="emp-field-label">Present Days</span>
            <span class="emp-field-value">{{ $salarySlip->present_days ?? '—' }}</span>
        </div>
        <div class="emp-field">
            <span class="emp-field-label">Absent Days</span>
            <span class="emp-field-value">{{ $salarySlip->absent_days ?? '—' }}</span>
        </div>
    </div>
</div>

{{-- ============================================================
     EARNINGS & DEDUCTIONS SIDE BY SIDE
     ============================================================ --}}
<div class="comp-section">
    {{-- Earnings --}}
    <div>
        <div class="section-heading">Earnings</div>
        <table class="comp-table">
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($earnings as $item)
                <tr>
                    <td>{{ $item->component_name }}</td>
                    <td class="amount-earn">{{ number_format($item->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="color:#9ca3af;text-align:center;padding:10px">No earnings</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>Gross Earnings</td>
                    <td class="amount-earn">{{ number_format($salarySlip->gross_salary, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Deductions --}}
    <div>
        <div class="section-heading">Deductions</div>
        <table class="comp-table">
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deductions as $item)
                <tr>
                    <td>{{ $item->component_name }}</td>
                    <td class="amount-ded">{{ number_format($item->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="color:#9ca3af;text-align:center;padding:10px">No deductions</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Deductions</td>
                    <td class="amount-ded">{{ number_format($salarySlip->total_deductions, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ============================================================
     SUMMARY
     ============================================================ --}}
<div class="summary-box">
    <div class="summary-row">
        <span class="summary-label">Gross Earnings</span>
        <span class="summary-value">₹{{ number_format($salarySlip->gross_salary, 2) }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Total Deductions</span>
        <span class="summary-value" style="color:#991b1b">— ₹{{ number_format($salarySlip->total_deductions, 2) }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Round Off</span>
        <span class="summary-value" style="color:#6b7280">
            {{ ($salarySlip->round_off ?? 0) >= 0 ? '+' : '' }}₹{{ number_format($salarySlip->round_off ?? 0, 2) }}
        </span>
    </div>
    <div class="summary-row net-row">
        <span class="net-label">Net Salary</span>
        <span class="net-amount">₹{{ number_format($salarySlip->net_salary, 2) }}</span>
    </div>
</div>

{{-- ============================================================
     STATUS BAR
     ============================================================ --}}
<div class="status-bar">
    <div style="display:flex;align-items:center;gap:10px">
        <span style="color:#6b7280;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.05em">Status</span>
        <span class="status-pill" style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }}">
            {{ \App\Models\Hrm\SalarySlip::STATUS_LABELS[$salarySlip->status] }}
        </span>
    </div>
    @if($salarySlip->payment_mode)
    <div style="display:flex;align-items:center;gap:8px">
        <span style="color:#6b7280;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.05em">Payment Mode</span>
        <span style="font-weight:700;color:#374151;text-transform:capitalize">{{ str_replace('_', ' ', $salarySlip->payment_mode) }}</span>
    </div>
    @endif
    @if($salarySlip->payment_date)
    <div style="display:flex;align-items:center;gap:8px">
        <span style="color:#6b7280;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.05em">Paid On</span>
        <span style="font-weight:700;color:#374151">{{ \Carbon\Carbon::parse($salarySlip->payment_date)->format('d M Y') }}</span>
    </div>
    @endif
</div>

{{-- ============================================================
     FOOTER
     ============================================================ --}}
<div class="slip-footer">
    <span class="footer-note">This is a computer-generated salary slip and does not require a signature.</span>
    <span>Generated on {{ now()->format('d M Y') }}</span>
</div>

</body>
</html>
