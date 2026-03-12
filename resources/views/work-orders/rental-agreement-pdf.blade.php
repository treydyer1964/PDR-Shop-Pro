<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Courtesy Vehicle Agreement{{ !$isBlank ? ' – ' . $workOrder->ro_number : '' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #000;
            background: #fff;
            padding: 28px 32px;
        }

        /* ── Header ── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        .header-left  { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; width: 50%; }
        .shop-name    { font-size: 16px; font-weight: bold; line-height: 1.2; }
        .shop-sub     { font-size: 9px; margin-top: 2px; line-height: 1.6; }
        .doc-title    { font-size: 20px; font-weight: bold; text-transform: uppercase; line-height: 1.2; letter-spacing: 0.03em; }
        .doc-meta     { font-size: 10px; margin-top: 6px; font-weight: bold; }
        .doc-meta-line { margin-top: 3px; }

        /* ── Intro paragraph ── */
        .intro-box {
            border: 1px solid #000;
            padding: 8px 10px;
            margin-bottom: 10px;
            font-size: 9px;
            line-height: 1.5;
        }

        /* ── Two-column layout ── */
        .two-col { display: table; width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .col-half { display: table-cell; width: 50%; vertical-align: top; border: 1px solid #000; padding: 0; }
        .col-half + .col-half { border-left: none; }

        /* ── Info blocks ── */
        .info-block-label {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 4px 8px;
            border-bottom: 1px solid #000;
            background: #e8e8e8;
        }
        .info-row { display: table; width: 100%; border-bottom: 1px solid #ccc; }
        .info-row:last-child { border-bottom: none; }
        .info-label { display: table-cell; font-size: 8px; color: #444; width: 100px; padding: 3px 8px; vertical-align: middle; }
        .info-value { display: table-cell; font-size: 9px; font-weight: bold; padding: 3px 8px; vertical-align: middle; }
        .info-value.blank { border-bottom: 1px solid #555; min-width: 100px; }

        /* ── Date/Mileage row ── */
        .dm-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .dm-cell {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 5px 6px 8px 6px;
            border-right: 1px solid #000;
            vertical-align: top;
        }
        .dm-cell:last-child { border-right: none; }
        .dm-label {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 18px;
            display: block;
        }

        /* ── Important Policies ── */
        .policies-section { margin-bottom: 10px; border: 1px solid #000; }
        .policies-header {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 4px 8px;
            background: #e8e8e8;
            border-bottom: 1px solid #000;
        }
        .policy-row { display: table; width: 100%; border-bottom: 1px solid #ccc; }
        .policy-row:last-child { border-bottom: none; }
        .policy-key {
            display: table-cell;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 8px;
            width: 120px;
            vertical-align: top;
            line-height: 1.4;
        }
        .policy-val {
            display: table-cell;
            font-size: 8.5px;
            padding: 4px 8px;
            vertical-align: top;
            line-height: 1.4;
        }

        /* ── Damage diagram ── */
        .damage-section { margin-bottom: 10px; border: 1px solid #000; }
        .damage-header {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            padding: 4px 8px;
            background: #e8e8e8;
            border-bottom: 1px solid #000;
        }
        .damage-grid { display: table; width: 100%; border-collapse: collapse; }
        .damage-cell {
            display: table-cell;
            width: 25%;
            border-right: 1px solid #ccc;
            padding: 5px 6px;
            text-align: center;
            height: 45px;
            vertical-align: top;
        }
        .damage-cell:last-child { border-right: none; }
        .damage-cell-label { font-size: 8px; font-weight: bold; }

        /* ── Signature block ── */
        .sig-notice { font-size: 8.5px; font-style: italic; text-align: center; margin-bottom: 10px; }
        .sig-block  { display: table; width: 100%; margin-bottom: 6px; }
        .sig-cell   { display: table-cell; width: 50%; vertical-align: bottom; padding-right: 20px; }
        .sig-cell:last-child { padding-right: 0; padding-left: 20px; }
        .sig-inner  { display: table; width: 100%; }
        .sig-line-cell { display: table-cell; border-bottom: 1px solid #000; }
        .sig-date-cell { display: table-cell; border-bottom: 1px solid #000; width: 80px; padding-left: 8px; }
        .sig-label  { display: table; width: 100%; margin-top: 2px; }
        .sig-label-sig  { display: table-cell; font-size: 8px; color: #444; }
        .sig-label-date { display: table-cell; font-size: 8px; color: #444; width: 80px; padding-left: 8px; }

        /* ── Footer ── */
        .footer {
            margin-top: 14px;
            padding-top: 6px;
            border-top: 1px solid #ccc;
            font-size: 8px;
            color: #555;
            text-align: center;
        }

        /* ── Page break ── */
        .page-break { page-break-after: always; }

        /* ── Terms page ── */
        .terms-header {
            margin-bottom: 14px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }
        .terms-title { font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.06em; }
        .clause { margin-bottom: 9px; }
        .clause-num  { font-size: 9px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .clause-text { font-size: 8.5px; line-height: 1.6; }
    </style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- PAGE 1 — AGREEMENT                                                          --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}

{{-- Header --}}
<div class="header">
    <div class="header-left">
        <div class="shop-name">{{ $tenant->name }}</div>
        <div class="shop-sub">
            @if($tenant->address){{ $tenant->address }}@if($tenant->city), {{ $tenant->city }}@endif@if($tenant->state) {{ $tenant->state }}@endif @if($tenant->zip){{ $tenant->zip }}@endif<br>@endif
            @if($tenant->phone){{ $tenant->phone }}@endif
        </div>
    </div>
    <div class="header-right">
        <div class="doc-title">Courtesy Vehicle<br>Agreement</div>
        <div class="doc-meta">
            <div class="doc-meta-line">CVA# {{ !$isBlank ? $workOrder->ro_number : '_______________' }}</div>
            <div class="doc-meta-line">Date: {{ !$isBlank ? \Carbon\Carbon::now()->format('M j, Y') : '_______________' }}</div>
        </div>
    </div>
</div>

{{-- Intro paragraph --}}
<div class="intro-box">
    This vehicle is provided as a <strong>courtesy to the Customer</strong> in connection with their vehicle repair at {{ $tenant->name }}.
    {{ $tenant->name }} reserves the right to seek reimbursement from the Customer's insurance carrier for applicable rental coverage benefits under the Customer's policy where such coverage exists.
    The Customer authorizes {{ $tenant->name }} to communicate with their insurance carrier for this purpose.
</div>

{{-- Customer + Vehicle two-col --}}
<div class="two-col">
    <div class="col-half">
        <div class="info-block-label">Customer Information</div>
        <div class="info-row">
            <div class="info-label">Name</div>
            <div class="info-value {{ (!$isBlank && $workOrder->customer->first_name) ? '' : 'blank' }}">
                {{ !$isBlank ? trim($workOrder->customer->first_name . ' ' . $workOrder->customer->last_name) : '' }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Address</div>
            <div class="info-value {{ (!$isBlank && $workOrder->customer->address) ? '' : 'blank' }}">{{ !$isBlank ? ($workOrder->customer->address ?? '') : '' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">City / State / Zip</div>
            <div class="info-value {{ (!$isBlank && ($workOrder->customer->city || $workOrder->customer->state)) ? '' : 'blank' }}">
                @if(!$isBlank)
                    @if($workOrder->customer->city){{ $workOrder->customer->city }}@endif@if($workOrder->customer->city && $workOrder->customer->state), @endif{{ $workOrder->customer->state ?? '' }} {{ $workOrder->customer->zip ?? '' }}
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Phone</div>
            <div class="info-value {{ (!$isBlank && $workOrder->customer->phone) ? '' : 'blank' }}">{{ !$isBlank ? ($workOrder->customer->phone ?? '') : '' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Date of Birth</div>
            <div class="info-value blank">
                @if(!$isBlank && $workOrder->customer->birthdate){{ \Carbon\Carbon::parse($workOrder->customer->birthdate)->format('m/d/Y') }}@endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Driver's License #</div>
            <div class="info-value blank">
                @if(!$isBlank){{ $workOrder->customer->drivers_license ?? '' }}@endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">DL State</div>
            <div class="info-value blank">
                @if(!$isBlank){{ $workOrder->customer->drivers_license_state ?? '' }}@endif
            </div>
        </div>
    </div>
    <div class="col-half">
        <div class="info-block-label">Courtesy Vehicle</div>
        <div class="info-row">
            <div class="info-label">Year / Make /<br>Model</div>
            <div class="info-value {{ (!$isBlank && $rentalVehicle) ? '' : 'blank' }}">
                @if(!$isBlank && $rentalVehicle){{ $rentalVehicle->year }} {{ $rentalVehicle->make }} {{ $rentalVehicle->model }}@endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Color</div>
            <div class="info-value {{ (!$isBlank && $rentalVehicle && $rentalVehicle->color) ? '' : 'blank' }}">
                @if(!$isBlank && $rentalVehicle){{ $rentalVehicle->color ?? '' }}@endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">License Plate</div>
            <div class="info-value {{ (!$isBlank && $rentalVehicle && $rentalVehicle->plate_number) ? '' : 'blank' }}">
                @if(!$isBlank && $rentalVehicle){{ strtoupper($rentalVehicle->plate_number ?? '') }}@endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">VIN</div>
            <div class="info-value blank" style="font-family: monospace; font-size: 9px;">
                @if(!$isBlank && $rentalVehicle){{ $rentalVehicle->vin ?? '' }}@endif
            </div>
        </div>
    </div>
</div>

{{-- Date / Mileage row --}}
<div class="dm-row">
    <div class="dm-cell">
        <span class="dm-label">Date Out</span>
        <span style="font-size:9px; font-weight:bold;">
            @if(!$isBlank && $segment && $segment->start_date){{ \Carbon\Carbon::parse($segment->start_date)->format('m/d/Y') }}@endif
        </span>
    </div>
    <div class="dm-cell">
        <span class="dm-label">Date In</span>
        <span style="font-size:9px; font-weight:bold;">
            @if(!$isBlank && $segment && $segment->end_date){{ \Carbon\Carbon::parse($segment->end_date)->format('m/d/Y') }}@endif
        </span>
    </div>
    <div class="dm-cell">
        <span class="dm-label">Mileage Out</span>
        <span style="font-size:9px; font-weight:bold;">
            @if(!$isBlank && $segment && $segment->odometer_out !== null){{ number_format($segment->odometer_out) }}@endif
        </span>
    </div>
    <div class="dm-cell">
        <span class="dm-label">Mileage In</span>
        <span style="font-size:9px; font-weight:bold;">
            @if(!$isBlank && $segment && $segment->odometer_in !== null){{ number_format($segment->odometer_in) }}@endif
        </span>
    </div>
</div>

{{-- Important Policies --}}
@php $shopState = $tenant->state ?? 'Texas'; @endphp
<div class="policies-section">
    <div class="policies-header">Important Policies</div>
    <div class="policy-row">
        <div class="policy-key">Coverage:</div>
        <div class="policy-val">NO insurance or damage coverage is provided with this vehicle. Verify your personal auto policy or credit card benefits for loaner vehicle coverage.</div>
    </div>
    <div class="policy-row">
        <div class="policy-key">Authorized Drivers:</div>
        <div class="policy-val">Only the Customer listed above is authorized to operate this vehicle.</div>
    </div>
    <div class="policy-row">
        <div class="policy-key">Smoking:</div>
        <div class="policy-val">NO SMOKING. A minimum $200 cleaning fee will be assessed for any evidence of smoking.</div>
    </div>
    <div class="policy-row">
        <div class="policy-key">Geographic Limit:</div>
        <div class="policy-val">Authorized for use in {{ $shopState }} only. Use outside {{ $shopState }} voids all agreement protections.</div>
    </div>
    <div class="policy-row">
        <div class="policy-key">Insurance Reimbursement:</div>
        <div class="policy-val">{{ $tenant->name }} reserves the right to seek reimbursement from the Customer's insurance carrier for applicable rental coverage benefits where such coverage exists. The Customer authorizes {{ $tenant->name }} to communicate with their carrier for this purpose.</div>
    </div>
    <div class="policy-row">
        <div class="policy-key">Courtesy Period:</div>
        <div class="policy-val">This vehicle is provided for the duration of the Customer's repair. Return is required promptly upon completion. Extended use requires written authorization.</div>
    </div>
</div>

{{-- Damage Condition Grid --}}
<div class="damage-section">
    <div class="damage-header">Vehicle Condition at Check-Out — Mark Any Pre-Existing Damage</div>
    <div class="damage-grid">
        <div class="damage-cell"><div class="damage-cell-label">FRONT</div></div>
        <div class="damage-cell"><div class="damage-cell-label">BACK</div></div>
        <div class="damage-cell"><div class="damage-cell-label">LEFT SIDE</div></div>
        <div class="damage-cell"><div class="damage-cell-label">RIGHT SIDE</div></div>
    </div>
</div>

{{-- Signature Block --}}
<p class="sig-notice">By signing below, Customer acknowledges reading and agreeing to all terms on the reverse side of this Agreement.</p>
<div class="sig-block">
    <div class="sig-cell">
        <div class="sig-inner">
            <div class="sig-line-cell">&nbsp;</div>
            <div class="sig-date-cell">&nbsp;</div>
        </div>
        <div class="sig-label">
            <div class="sig-label-sig">Customer Signature</div>
            <div class="sig-label-date">Date</div>
        </div>
    </div>
    <div class="sig-cell">
        <div class="sig-inner">
            <div class="sig-line-cell">&nbsp;</div>
            <div class="sig-date-cell">&nbsp;</div>
        </div>
        <div class="sig-label">
            <div class="sig-label-sig">Shop Representative</div>
            <div class="sig-label-date">Date</div>
        </div>
    </div>
</div>

<div class="footer">
    {{ $tenant->name }} &bull; Courtesy Vehicle Agreement &bull; {{ !$isBlank ? $workOrder->ro_number : '' }} &bull; See reverse for Terms and Conditions
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- PAGE BREAK                                                                   --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page-break"></div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- PAGE 2 — TERMS & CONDITIONS                                                  --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}

<div class="terms-header">
    <div class="terms-title">Terms and Conditions of Courtesy Vehicle Use</div>
</div>

<div class="clause">
    <div class="clause-num">1. Authorized Drivers</div>
    <div class="clause-text">Only the Customer listed on the face of this agreement is authorized to operate the courtesy vehicle. Any unauthorized operator relieves {{ $tenant->name }} of all liability and the Customer accepts full personal responsibility for any resulting loss or damage.</div>
</div>

<div class="clause">
    <div class="clause-num">2. Customer Responsibilities</div>
    <div class="clause-text">The Customer is responsible for all damage to or loss of the courtesy vehicle during the courtesy period, including damage caused by weather, animals, road hazards, or other conditions. The Customer is also responsible for any consequential damages arising from loss of use of the vehicle during repair or replacement.</div>
</div>

<div class="clause">
    <div class="clause-num">3. Damage and Loss</div>
    <div class="clause-text">The Customer agrees to pay for any physical damage, theft, or destruction of the vehicle. In the event of an accident, the Customer must immediately notify {{ $tenant->name }} and cooperate fully in any investigation or insurance claim. Failure to report damage before returning the vehicle forfeits any right to dispute damage charges.</div>
</div>

<div class="clause">
    <div class="clause-num">4. No Coverage Provided</div>
    <div class="clause-text">No insurance or damage coverage is provided with this courtesy vehicle. The Customer is advised to verify whether their personal auto insurance policy or credit card benefits provide coverage for loaner vehicles. {{ $tenant->name }} assumes no liability for uninsured losses sustained by the Customer.</div>
</div>

<div class="clause">
    <div class="clause-num">5. Insurance Reimbursement</div>
    <div class="clause-text">{{ $tenant->name }} reserves the right to submit a Rental Reimbursement Request to the Customer's insurance carrier for applicable rental coverage benefits under the Customer's policy where such coverage exists. The Customer authorizes {{ $tenant->name }} to communicate with their insurance carrier for this purpose.</div>
</div>

<div class="clause">
    <div class="clause-num">6. Courtesy Period and Return of Vehicle</div>
    <div class="clause-text">This vehicle is provided for the duration of the Customer's repair at {{ $tenant->name }}. The vehicle must be returned promptly upon completion of the repair, or as otherwise agreed in writing. Failure to return the vehicle following completion of repair may result in the vehicle being reported as unauthorized use.</div>
</div>

<div class="clause">
    <div class="clause-num">7. Smoking Policy</div>
    <div class="clause-text">This is a no-smoking vehicle. Any evidence of smoking in the vehicle will result in a minimum $200.00 cleaning charge billed to the Customer, in addition to any other applicable charges.</div>
</div>

<div class="clause">
    <div class="clause-num">8. Geographic Restrictions</div>
    <div class="clause-text">This vehicle is authorized for use within the State of {{ $shopState }} only. Use outside this boundary is a violation of this agreement and voids all protections. The Customer accepts full liability for any incidents, fines, towing, or other costs incurred outside the authorized area.</div>
</div>

<div class="clause">
    <div class="clause-num">9. Traffic Violations and Fines</div>
    <div class="clause-text">The Customer is solely responsible for all parking tickets, traffic citations, toll charges, and any other fines or fees incurred during the courtesy period. {{ $tenant->name }} is authorized to charge the Customer for any such amounts assessed to the vehicle owner.</div>
</div>

<div class="clause">
    <div class="clause-num">10. Prohibited Uses</div>
    <div class="clause-text">The vehicle may not be used: (a) for any illegal purpose; (b) while the driver is under the influence of alcohol or controlled substances; (c) to push or tow any other vehicle; (d) off paved roads; (e) in any race, speed test, or contest; (f) by any person not listed on this agreement.</div>
</div>

<div class="clause">
    <div class="clause-num">11. Authorization for Damage Charges</div>
    <div class="clause-text">By signing this agreement, the Customer authorizes {{ $tenant->name }} to charge the Customer for any amounts owed arising from damage to the vehicle, cleaning charges, traffic fines, or any other amounts incurred during the courtesy period that are not otherwise covered or reimbursed.</div>
</div>

<div class="clause">
    <div class="clause-num">12. Limitation of Liability</div>
    <div class="clause-text">{{ $tenant->name }} shall not be liable for any personal property left in the vehicle, any personal injury not caused by the direct negligence of {{ $tenant->name }}, or any incidental or consequential damages of any kind.</div>
</div>

<div class="clause">
    <div class="clause-num">13. Governing Law</div>
    <div class="clause-text">This agreement shall be governed by the laws of the State of {{ $shopState }}. Any disputes arising from this agreement shall be resolved in the courts of {{ $shopState }}.</div>
</div>

<div class="clause">
    <div class="clause-num">14. Entire Agreement</div>
    <div class="clause-text">This agreement, including both sides of this form, constitutes the entire agreement between the parties. No verbal representations alter the terms of this written agreement.</div>
</div>

<div class="footer">
    {{ $tenant->name }} &bull; Courtesy Vehicle Agreement &bull; {{ !$isBlank ? $workOrder->ro_number . ' &bull; ' : '' }}Terms and Conditions
</div>

</body>
</html>
