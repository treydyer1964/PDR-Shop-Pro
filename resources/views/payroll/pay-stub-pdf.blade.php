<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay Stub – {{ $user->name }}</title>
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
        .shop-address { font-size: 10px; color: #64748b; margin-top: 3px; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .stub-title { font-size: 16px; font-weight: bold; color: #1d4ed8; letter-spacing: 0.04em; text-transform: uppercase; }

        /* ── Meta block ── */
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { padding: 3px 0; font-size: 11px; }
        .meta-label { color: #64748b; width: 100px; }
        .meta-value { font-weight: 600; color: #1e293b; }
        .meta-right { text-align: right; }

        /* ── Commission table ── */
        .commissions {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .commissions thead tr {
            background: #1e293b;
            color: #fff;
        }
        .commissions thead th {
            padding: 7px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .commissions thead th.right { text-align: right; }

        .commissions tbody tr { border-bottom: 1px solid #e2e8f0; }
        .commissions tbody tr:nth-child(even) { background: #f8fafc; }
        .commissions tbody td { padding: 9px 10px; vertical-align: top; }

        .vehicle-name { font-weight: 600; font-size: 11px; color: #1e293b; }
        .vehicle-sub  { font-size: 10px; color: #94a3b8; margin-top: 2px; }
        .role-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 9999px;
            font-size: 9px;
            font-weight: 600;
            background: #e0e7ff;
            color: #3730a3;
            margin-top: 3px;
        }
        .calc-note { font-size: 9px; color: #94a3b8; margin-top: 3px; font-style: italic; }

        .money { text-align: right; font-variant-numeric: tabular-nums; }
        .pay-amount { font-weight: 700; font-size: 12px; color: #15803d; }

        /* ── Total row ── */
        .total-row td {
            padding: 10px 10px;
            background: #f1f5f9;
            border-top: 2px solid #1e293b;
        }
        .total-label {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            text-align: right;
        }
        .total-amount {
            text-align: right;
            font-size: 14px;
            font-weight: 700;
            color: #15803d;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 28px;
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
            <div class="shop-address">
                @if($tenant->remit_address){{ $tenant->remit_address }}
                @elseif($tenant->address){{ $tenant->address }}@if($tenant->city), {{ $tenant->city }}@endif@if($tenant->state), {{ $tenant->state }}@endif @if($tenant->zip){{ $tenant->zip }}@endif
                @endif
                @if($tenant->phone)<br>{{ $tenant->phone }}@endif
            </div>
        </div>
        <div class="header-right">
            <div class="stub-title">Earnings Statement</div>
        </div>
    </div>

    {{-- ── Meta info ── --}}
    <table class="meta-table">
        <tr>
            <td class="meta-label">To:</td>
            <td class="meta-value">{{ $user->name }}</td>
            <td style="width:40px"></td>
            <td class="meta-label meta-right">Pay Run:</td>
            <td class="meta-value" style="text-align:right">{{ $payRun->name }}</td>
        </tr>
        @if($payRun->period_start || $payRun->period_end)
        <tr>
            <td class="meta-label">Period:</td>
            <td class="meta-value">
                {{ $payRun->period_start?->format('M j, Y') ?? '—' }}
                &ndash;
                {{ $payRun->period_end?->format('M j, Y') ?? '—' }}
            </td>
            <td></td>
            <td class="meta-label meta-right">Issued:</td>
            <td class="meta-value" style="text-align:right">{{ now()->format('M j, Y') }}</td>
        </tr>
        @else
        <tr>
            <td class="meta-label">Issued:</td>
            <td class="meta-value">{{ now()->format('M j, Y') }}</td>
        </tr>
        @endif
    </table>

    {{-- ── Commission table ── --}}
    <table class="commissions">
        <thead>
            <tr>
                <th style="width:30%">Vehicle</th>
                <th style="width:14%">Customer</th>
                <th style="width:10%">RO #</th>
                <th class="right" style="width:13%">Invoice</th>
                <th class="right" style="width:13%">Expenses</th>
                <th class="right" style="width:20%">Your Pay</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commissions as $commission)
                @php
                    $wo      = $commission->workOrder;
                    $vehicle = $wo->vehicle;
                    $invoice = (float) ($wo->invoice_total ?? 0);
                    $expenses = $wo->expenses->sum('amount');
                @endphp
                <tr>
                    {{-- Vehicle --}}
                    <td>
                        <div class="vehicle-name">
                            {{ $vehicle?->year }} {{ $vehicle?->make }} {{ $vehicle?->model }}
                            @if($vehicle?->color)<span style="font-weight:400; color:#64748b"> · {{ $vehicle->color }}</span>@endif
                        </div>
                        <div class="vehicle-sub">{{ $wo->ro_number }}</div>
                        <div>
                            <span class="role-badge">{{ $commission->role->label() }}</span>
                        </div>
                        @if($commission->notes)
                            <div class="calc-note">{{ $commission->notes }}</div>
                        @endif
                    </td>

                    {{-- Customer --}}
                    <td>{{ $wo->customer?->last_name }}</td>

                    {{-- RO # --}}
                    <td style="font-size:10px; color:#64748b">{{ $wo->ro_number }}</td>

                    {{-- Invoice --}}
                    <td class="money">
                        @if($wo->invoice_total !== null)
                            ${{ number_format($invoice, 2) }}
                        @else
                            <span style="color:#94a3b8">—</span>
                        @endif
                    </td>

                    {{-- Expenses --}}
                    <td class="money" style="color:#dc2626">
                        @if($expenses > 0)
                            (${{ number_format($expenses, 2) }})
                        @else
                            —
                        @endif
                    </td>

                    {{-- Pay --}}
                    <td class="money pay-amount">
                        ${{ number_format((float) $commission->amount, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>

        {{-- Total row --}}
        <tr class="total-row">
            <td colspan="5" class="total-label">Total Earnings</td>
            <td class="total-amount">${{ number_format((float) $total, 2) }}</td>
        </tr>
    </table>

    {{-- ── Footer ── --}}
    <div class="footer">
        This is an internal earnings statement for informational purposes. {{ $tenant->name }} &bull; Generated {{ now()->format('M j, Y \a\t g:i A') }}
    </div>

</body>
</html>
