<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice – {{ $workOrder->ro_number }}</title>
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
        .invoice-title { font-size: 22px; font-weight: bold; color: #1d4ed8; letter-spacing: 0.04em; text-transform: uppercase; }
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

        /* ── Insurance block ── */
        .insurance-block {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            padding: 10px 12px;
            margin-bottom: 20px;
        }
        .insurance-block .block-label { color: #1d4ed8; }

        /* ── Payments table ── */
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .payments-table thead tr {
            background: #1e293b;
            color: #fff;
        }
        .payments-table thead th {
            padding: 7px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .payments-table thead th.right { text-align: right; }
        .payments-table tbody tr { border-bottom: 1px solid #e2e8f0; }
        .payments-table tbody tr:nth-child(even) { background: #f8fafc; }
        .payments-table tbody td { padding: 8px 10px; vertical-align: middle; }

        /* ── Summary rows ── */
        .summary-row td {
            padding: 8px 10px;
            border-top: 1px solid #e2e8f0;
        }
        .summary-label { text-align: right; color: #64748b; font-size: 11px; }
        .summary-value { text-align: right; font-size: 11px; font-variant-numeric: tabular-nums; width: 120px; }

        .total-row td {
            padding: 10px 10px;
            background: #f1f5f9;
            border-top: 2px solid #1e293b;
        }
        .total-label { text-align: right; font-size: 12px; font-weight: 700; color: #1e293b; }
        .total-value { text-align: right; font-size: 14px; font-weight: 700; width: 120px; }
        .total-value.owed { color: #dc2626; }
        .total-value.paid { color: #15803d; }

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

        /* ── Footer ── */
        .footer {
            margin-top: 32px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }

        .no-payments {
            padding: 14px 10px;
            color: #94a3b8;
            font-style: italic;
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
            <div class="invoice-title">Invoice</div>
            <div class="invoice-meta">
                <strong>{{ $workOrder->ro_number }}</strong><br>
                @if($workOrder->invoice_date)
                    Date: {{ $workOrder->invoice_date->format('M j, Y') }}
                @else
                    Date: {{ now()->format('M j, Y') }}
                @endif
            </div>
        </div>
    </div>

    {{-- ── Bill To + Vehicle ── --}}
    <div class="two-col">
        <div class="col-left">
            <div class="block-label">Bill To</div>
            <div class="block-value">
                <strong>{{ $workOrder->customer->first_name }} {{ $workOrder->customer->last_name }}</strong><br>
                @if($workOrder->customer->phone){{ $workOrder->customer->phone }}<br>@endif
                @if($workOrder->customer->email){{ $workOrder->customer->email }}<br>@endif
                @if($workOrder->customer->address){{ $workOrder->customer->address }}<br>@endif
                @if($workOrder->customer->city || $workOrder->customer->state || $workOrder->customer->zip)
                    {{ $workOrder->customer->city }}@if($workOrder->customer->city && $workOrder->customer->state), @endif{{ $workOrder->customer->state }} {{ $workOrder->customer->zip }}
                @endif
            </div>
        </div>
        <div class="col-right">
            <div class="block-label">Vehicle</div>
            <div class="block-value">
                <strong>{{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}</strong>
                @if($workOrder->vehicle->color) · {{ $workOrder->vehicle->color }}@endif<br>
                @if($workOrder->vehicle->vin)VIN: {{ $workOrder->vehicle->vin }}<br>@endif
                @if($workOrder->vehicle->trim){{ $workOrder->vehicle->trim }}@endif
            </div>
        </div>
    </div>

    {{-- ── Insurance Block (insurance jobs only) ── --}}
    @if($workOrder->job_type->value === 'insurance')
    <div class="insurance-block">
        <div class="block-label">Insurance Information</div>
        <div class="block-value">
            @if($workOrder->insuranceCompany){{ $workOrder->insuranceCompany->name }}&nbsp;&nbsp;@endif
            @if($workOrder->claim_number)Claim #: <strong>{{ $workOrder->claim_number }}</strong>&nbsp;&nbsp;@endif
            @if($workOrder->adjuster_name)Adjuster: {{ $workOrder->adjuster_name }}@endif
        </div>
    </div>
    @endif

    {{-- ── Invoice Total ── --}}
    <div class="section-header">Invoice Summary</div>
    <table class="payments-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Source</th>
                <th>Method</th>
                <th>Date</th>
                <th>Reference #</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            {{-- Invoice total row --}}
            <tr>
                <td><strong>Invoice Total</strong></td>
                <td>—</td>
                <td>—</td>
                <td>—</td>
                <td>—</td>
                <td style="text-align:right; font-weight:700; font-variant-numeric:tabular-nums;">
                    ${{ number_format((float)($workOrder->invoice_total ?? 0), 2) }}
                </td>
            </tr>

            {{-- Payment rows --}}
            @if($workOrder->payments->count() > 0)
                @foreach($workOrder->payments as $payment)
                <tr>
                    <td>Payment Received</td>
                    <td>{{ $payment->source->label() }}</td>
                    <td>{{ $payment->method->label() }}</td>
                    <td>{{ $payment->received_on?->format('M j, Y') ?? '—' }}</td>
                    <td>{{ $payment->reference ?? '—' }}</td>
                    <td style="text-align:right; color:#15803d; font-variant-numeric:tabular-nums;">
                        (${{ number_format((float)$payment->amount, 2) }})
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="no-payments">No payments recorded</td>
                </tr>
            @endif
        </tbody>

        {{-- Balance Due row --}}
        @php
            $balance = (float)($workOrder->invoice_total ?? 0) - $workOrder->payments->sum('amount');
        @endphp
        <tr class="total-row">
            <td colspan="5" class="total-label">Balance Due</td>
            <td class="total-value {{ $balance > 0 ? 'owed' : 'paid' }}">
                ${{ number_format(max($balance, 0), 2) }}
                @if($balance <= 0 && ($workOrder->invoice_total ?? 0) > 0)
                    <br><span style="font-size:9px; font-weight:400; color:#15803d;">Paid in Full</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- ── Footer ── --}}
    <div class="footer">
        Thank you for your business! &bull; {{ $tenant->name }} &bull; Generated {{ now()->format('M j, Y') }}
    </div>

</body>
</html>
