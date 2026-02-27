<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rental Invoice – {{ $workOrder->ro_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
            padding: 32px 36px;
        }

        /* ── Header ── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 24px;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 16px;
        }
        .header-logo { display: table-cell; vertical-align: middle; width: 70px; }
        .header-logo img { max-width: 64px; max-height: 64px; }
        .header-info { display: table-cell; vertical-align: middle; padding-left: 12px; }
        .shop-name { font-size: 18px; font-weight: bold; color: #1e293b; line-height: 1.2; }
        .shop-sub { font-size: 10px; color: #64748b; margin-top: 3px; line-height: 1.5; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .invoice-title { font-size: 20px; font-weight: bold; color: #1d4ed8; letter-spacing: 0.04em; text-transform: uppercase; }
        .invoice-meta { font-size: 10px; color: #64748b; margin-top: 4px; }
        .invoice-meta strong { color: #1e293b; }

        /* ── Two-column layout ── */
        .two-col { display: table; width: 100%; margin-bottom: 20px; }
        .col-left  { display: table-cell; width: 50%; vertical-align: top; padding-right: 20px; }
        .col-right { display: table-cell; width: 50%; vertical-align: top; }

        /* ── Block labels ── */
        .block-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            margin-bottom: 5px;
        }
        .block-value { font-size: 11px; color: #1e293b; line-height: 1.6; }
        .block-value strong { font-size: 12px; }

        /* ── Section header ── */
        .section-header {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin: 16px 0 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* ── Segments table ── */
        .segments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .segments-table thead tr {
            background: #1e293b;
            color: #fff;
        }
        .segments-table thead th {
            padding: 7px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .segments-table thead th.right { text-align: right; }
        .segments-table tbody tr { border-bottom: 1px solid #e2e8f0; }
        .segments-table tbody tr:nth-child(even) { background: #f8fafc; }
        .segments-table tbody td { padding: 9px 10px; vertical-align: middle; }

        .in-progress {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 9999px;
            font-size: 9px;
            font-weight: 600;
            background: #fef9c3;
            color: #854d0e;
        }

        /* ── Totals ── */
        .totals-table { width: 100%; border-collapse: collapse; margin-top: 0; }
        .totals-table td { padding: 7px 10px; border-top: 1px solid #e2e8f0; }
        .totals-label { text-align: right; color: #64748b; font-size: 11px; }
        .totals-value { text-align: right; font-size: 11px; font-variant-numeric: tabular-nums; width: 140px; }

        .grand-total td {
            padding: 10px 10px;
            background: #eff6ff;
            border-top: 2px solid #1d4ed8;
        }
        .grand-label { text-align: right; font-size: 13px; font-weight: 700; color: #1e293b; }
        .grand-value { text-align: right; font-size: 16px; font-weight: 700; color: #1d4ed8; width: 140px; font-variant-numeric: tabular-nums; }

        /* ── Footer ── */
        .footer {
            margin-top: 32px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ── Header ── --}}
    <div class="header">
        @if($tenant->logo_path)
            <div class="header-logo">
                <img src="{{ public_path('storage/' . $tenant->logo_path) }}" alt="Logo">
            </div>
        @endif
        <div class="header-info">
            <div class="shop-name">{{ $tenant->name }}</div>
            <div class="shop-sub">
                @if($tenant->remit_address)
                    {{ $tenant->remit_address }}
                @elseif($tenant->address)
                    {{ $tenant->address }}@if($tenant->city), {{ $tenant->city }}@endif@if($tenant->state), {{ $tenant->state }}@endif @if($tenant->zip){{ $tenant->zip }}@endif
                @endif
                @if($tenant->phone)<br>{{ $tenant->phone }}@endif
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-title">Rental Invoice</div>
            <div class="invoice-meta">
                Date: <strong>{{ now()->format('M j, Y') }}</strong>
            </div>
        </div>
    </div>

    {{-- ── Bill To + Customer/Vehicle ── --}}
    <div class="two-col">
        <div class="col-left">
            <div class="block-label">Bill To</div>
            <div class="block-value">
                @if($workOrder->insuranceCompany)
                    <strong>{{ $workOrder->insuranceCompany->name }}</strong><br>
                @endif
                @if($workOrder->claim_number)Claim #: {{ $workOrder->claim_number }}<br>@endif
                @if($workOrder->policy_number)Policy #: {{ $workOrder->policy_number }}<br>@endif
            </div>
        </div>
        <div class="col-right">
            <div class="block-label">Customer &amp; Vehicle</div>
            <div class="block-value">
                <strong>{{ $workOrder->customer->first_name }} {{ $workOrder->customer->last_name }}</strong><br>
                @if($rental->vehicle)
                    Rental: {{ $rental->vehicle->year }} {{ $rental->vehicle->make }} {{ $rental->vehicle->model }}
                    @if($rental->vehicle->color) · {{ $rental->vehicle->color }}@endif<br>
                    @if($rental->vehicle->vin)VIN: {{ $rental->vehicle->vin }}@endif
                @endif
            </div>
        </div>
    </div>

    {{-- ── RO Reference ── --}}
    <div style="margin-bottom: 16px; font-size: 10px; color: #64748b;">
        Work Order: <strong style="color: #1e293b;">{{ $workOrder->ro_number }}</strong>
        @if($workOrder->vehicle)
            &nbsp;&bull;&nbsp; Vehicle: {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
            @if($workOrder->vehicle->vin) &nbsp;&bull;&nbsp; VIN: {{ $workOrder->vehicle->vin }}@endif
        @endif
    </div>

    {{-- ── Rental Segments ── --}}
    <div class="section-header">Rental Periods</div>
    <table class="segments-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th class="right">Days</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rental->segments as $i => $segment)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($segment->start_date)->format('M j, Y') }}</td>
                <td>
                    @if($segment->end_date)
                        {{ \Carbon\Carbon::parse($segment->end_date)->format('M j, Y') }}
                    @else
                        <span class="in-progress">In Progress</span>
                    @endif
                </td>
                <td style="text-align:right; font-variant-numeric:tabular-nums;">
                    @if($segment->end_date)
                        {{ $segment->days }}
                    @else
                        {{ (int) \Carbon\Carbon::parse($segment->start_date)->diffInDays(now()) }}+
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Totals ── --}}
    @php
        $totalDays = $rental->totalDays();
        $dailyRate = (float) $rental->insurance_daily_rate;
        $grandTotal = $dailyRate * $totalDays;
    @endphp
    <table class="totals-table">
        <tr>
            <td class="totals-label">Total Rental Days</td>
            <td class="totals-value">{{ $totalDays }} days</td>
        </tr>
        <tr>
            <td class="totals-label">Daily Rate</td>
            <td class="totals-value">${{ number_format($dailyRate, 2) }} / day</td>
        </tr>
        <tr class="grand-total">
            <td class="grand-label">Total Due</td>
            <td class="grand-value">${{ number_format($grandTotal, 2) }}</td>
        </tr>
    </table>

    {{-- ── Footer ── --}}
    <div class="footer">
        {{ $tenant->name }} &bull; {{ $workOrder->ro_number }} &bull; Generated {{ now()->format('M j, Y \a\t g:i A') }}
    </div>

</body>
</html>
