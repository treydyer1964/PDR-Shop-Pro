<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rental Agreement – {{ $workOrder->ro_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            background: #fff;
            padding: 28px 32px;
        }

        /* ── Header ── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            border-bottom: 3px solid #1e293b;
            padding-bottom: 12px;
        }
        .header-logo { display: table-cell; vertical-align: middle; width: 70px; }
        .header-logo img { max-width: 64px; max-height: 64px; }
        .header-info { display: table-cell; vertical-align: middle; padding-left: 10px; }
        .shop-name { font-size: 16px; font-weight: bold; color: #1e293b; line-height: 1.2; }
        .shop-sub { font-size: 9px; color: #475569; margin-top: 2px; line-height: 1.6; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .doc-title { font-size: 18px; font-weight: bold; color: #1d4ed8; letter-spacing: 0.06em; text-transform: uppercase; }
        .doc-meta { font-size: 9px; color: #64748b; margin-top: 3px; }
        .doc-meta strong { color: #1a1a1a; }

        /* ── Two-column layout ── */
        .two-col { display: table; width: 100%; margin-bottom: 10px; }
        .col-half { display: table-cell; width: 50%; vertical-align: top; padding-right: 14px; }
        .col-half:last-child { padding-right: 0; padding-left: 14px; }

        /* ── Info blocks ── */
        .info-block {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 10px;
        }
        .info-block-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #1d4ed8;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-row { display: table; width: 100%; margin-bottom: 3px; }
        .info-label { display: table-cell; font-size: 8px; color: #64748b; width: 90px; }
        .info-value { display: table-cell; font-size: 9px; color: #1a1a1a; font-weight: 600; }
        .info-value.blank { border-bottom: 1px solid #64748b; min-width: 120px; }

        /* ── Date/time row ── */
        .date-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 10px;
        }
        .date-cell { display: table-cell; text-align: center; padding: 0 8px; border-right: 1px solid #e2e8f0; }
        .date-cell:last-child { border-right: none; }
        .date-cell-label { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
        .date-cell-value { font-size: 9px; font-weight: 600; border-bottom: 1px solid #1a1a1a; min-height: 14px; min-width: 80px; display: block; }

        /* ── Odometer / fuel row ── */
        .odo-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 10px;
        }
        .odo-cell { display: table-cell; padding: 0 8px; border-right: 1px solid #e2e8f0; vertical-align: top; }
        .odo-cell:last-child { border-right: none; }
        .odo-label { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
        .odo-value { font-size: 9px; font-weight: 600; border-bottom: 1px solid #1a1a1a; min-height: 14px; display: block; }

        .fuel-boxes { display: table; margin-top: 3px; }
        .fuel-box { display: table-cell; font-size: 8px; padding: 2px 5px; border: 1px solid #94a3b8; margin-right: 2px; text-align: center; min-width: 22px; }
        .fuel-box.checked { background: #1e293b; color: #fff; }

        /* ── Initials blocks ── */
        .initials-row { display: table; width: 100%; margin-bottom: 10px; }
        .initials-block {
            display: table-cell;
            border: 2px solid #1e293b;
            border-radius: 4px;
            padding: 8px;
            vertical-align: top;
            width: 33%;
        }
        .initials-block + .initials-block { margin-left: 6px; }
        .initials-title { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; color: #1e293b; margin-bottom: 5px; border-bottom: 1px solid #1e293b; padding-bottom: 3px; }
        .initials-text { font-size: 8px; color: #374151; line-height: 1.5; margin-bottom: 6px; }
        .initials-line { display: table; width: 100%; margin-top: 4px; }
        .initials-blank { display: table-cell; border-bottom: 2px solid #1e293b; width: 50px; }
        .initials-word { display: table-cell; font-size: 8px; color: #64748b; padding-left: 4px; vertical-align: bottom; }

        /* ── Policy box ── */
        .policy-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 10px;
            background: #f8fafc;
        }
        .policy-box-title { font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.07em; color: #dc2626; margin-bottom: 5px; }
        .policy-item { font-size: 8px; color: #374151; margin-bottom: 3px; line-height: 1.4; }
        .policy-item strong { color: #1a1a1a; }

        /* ── Damage diagram ── */
        .damage-section { margin-bottom: 10px; }
        .damage-title { font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.07em; color: #64748b; margin-bottom: 5px; }
        .damage-grid { display: table; width: 100%; border-collapse: collapse; }
        .damage-cell {
            display: table-cell;
            width: 25%;
            border: 1px solid #cbd5e1;
            padding: 6px;
            text-align: center;
            height: 55px;
            vertical-align: top;
        }
        .damage-cell-label { font-size: 8px; font-weight: bold; color: #475569; }

        /* ── Signature block ── */
        .sig-block { display: table; width: 100%; margin-bottom: 6px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .sig-cell { display: table-cell; width: 50%; vertical-align: bottom; padding-right: 20px; }
        .sig-cell:last-child { padding-right: 0; padding-left: 20px; }
        .sig-line { border-bottom: 1px solid #1a1a1a; margin-bottom: 2px; min-height: 24px; }
        .sig-label { font-size: 8px; color: #64748b; }
        .sig-notice { font-size: 8px; color: #374151; font-style: italic; margin-bottom: 8px; text-align: center; }

        /* ── Page break ── */
        .page-break { page-break-after: always; }

        /* ── Terms page ── */
        .terms-header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 10px;
        }
        .terms-title { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; color: #1e293b; }
        .terms-sub { font-size: 9px; color: #64748b; margin-top: 2px; }
        .clause { margin-bottom: 9px; }
        .clause-num { font-size: 9px; font-weight: bold; color: #1e293b; }
        .clause-text { font-size: 8.5px; color: #374151; line-height: 1.6; margin-top: 1px; }

        /* ── Footer ── */
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- PAGE 1 — AGREEMENT                                                          --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}

{{-- Header --}}
<div class="header">
    @if($tenant->logo_path)
        <div class="header-logo">
            <img src="{{ public_path('storage/' . $tenant->logo_path) }}" alt="Logo">
        </div>
    @endif
    <div class="header-info">
        <div class="shop-name">{{ $tenant->name }}</div>
        <div class="shop-sub">
            @if($tenant->address){{ $tenant->address }}@if($tenant->city), {{ $tenant->city }}@endif@if($tenant->state) {{ $tenant->state }}@endif @if($tenant->zip){{ $tenant->zip }}@endif<br>@endif
            @if($tenant->phone){{ $tenant->phone }}@endif
        </div>
    </div>
    <div class="header-right">
        <div class="doc-title">Rental Agreement</div>
        <div class="doc-meta">
            RA# <strong>{{ $workOrder->ro_number }}</strong><br>
            Date: <strong>{{ now()->format('M j, Y') }}</strong>
        </div>
    </div>
</div>

{{-- Renter + Vehicle two-col --}}
<div class="two-col">
    <div class="col-half">
        <div class="info-block">
            <div class="info-block-label">Renter Information</div>
            <div class="info-row">
                <div class="info-label">Name</div>
                <div class="info-value">{{ $workOrder->customer->first_name }} {{ $workOrder->customer->last_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Address</div>
                <div class="info-value {{ $workOrder->customer->address ? '' : 'blank' }}">{{ $workOrder->customer->address ?? '' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">City / State / Zip</div>
                <div class="info-value {{ ($workOrder->customer->city || $workOrder->customer->state) ? '' : 'blank' }}">
                    @if($workOrder->customer->city){{ $workOrder->customer->city }}@endif@if($workOrder->customer->city && $workOrder->customer->state), @endif{{ $workOrder->customer->state ?? '' }}
                    {{ $workOrder->customer->zip ?? '' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone</div>
                <div class="info-value {{ $workOrder->customer->phone ? '' : 'blank' }}">{{ $workOrder->customer->phone ?? '' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date of Birth</div>
                <div class="info-value {{ $workOrder->customer->birthdate ? '' : 'blank' }}">
                    {{ $workOrder->customer->birthdate ? \Carbon\Carbon::parse($workOrder->customer->birthdate)->format('m/d/Y') : '' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Driver's License #</div>
                <div class="info-value {{ $workOrder->customer->drivers_license ? '' : 'blank' }}">{{ $workOrder->customer->drivers_license ?? '' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">DL State</div>
                <div class="info-value {{ $workOrder->customer->drivers_license_state ? '' : 'blank' }}">{{ $workOrder->customer->drivers_license_state ?? '' }}</div>
            </div>
        </div>
    </div>
    <div class="col-half">
        <div class="info-block">
            <div class="info-block-label">Rental Vehicle</div>
            @if($rentalVehicle)
                <div class="info-row">
                    <div class="info-label">Year / Make / Model</div>
                    <div class="info-value">{{ $rentalVehicle->year }} {{ $rentalVehicle->make }} {{ $rentalVehicle->model }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Color</div>
                    <div class="info-value {{ $rentalVehicle->color ? '' : 'blank' }}">{{ $rentalVehicle->color ?? '' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">License Plate</div>
                    <div class="info-value {{ $rentalVehicle->plate_number ? '' : 'blank' }}">{{ strtoupper($rentalVehicle->plate_number ?? '') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">VIN</div>
                    <div class="info-value {{ $rentalVehicle->vin ? '' : 'blank' }}" style="font-family: monospace; font-size: 9px;">{{ $rentalVehicle->vin ?? '' }}</div>
                </div>
            @else
                <div class="info-row">
                    <div class="info-label">Vehicle</div>
                    <div class="info-value blank"></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Color</div>
                    <div class="info-value blank"></div>
                </div>
                <div class="info-row">
                    <div class="info-label">License Plate</div>
                    <div class="info-value blank"></div>
                </div>
                <div class="info-row">
                    <div class="info-label">VIN</div>
                    <div class="info-value blank"></div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Date / Time row --}}
<div class="date-row">
    <div class="date-cell">
        <div class="date-cell-label">Date Out</div>
        <div class="date-cell-value">{{ $segment ? \Carbon\Carbon::parse($segment->start_date)->format('m/d/Y') : '' }}</div>
    </div>
    <div class="date-cell">
        <div class="date-cell-label">Time Out</div>
        <div class="date-cell-value"></div>
    </div>
    <div class="date-cell">
        <div class="date-cell-label">Date Due</div>
        <div class="date-cell-value"></div>
    </div>
    <div class="date-cell">
        <div class="date-cell-label">Date In</div>
        <div class="date-cell-value">{{ ($segment && $segment->end_date) ? \Carbon\Carbon::parse($segment->end_date)->format('m/d/Y') : '' }}</div>
    </div>
    <div class="date-cell">
        <div class="date-cell-label">Time In</div>
        <div class="date-cell-value"></div>
    </div>
</div>

{{-- Odometer + Fuel row --}}
<div class="odo-row">
    <div class="odo-cell">
        <div class="odo-label">Miles Out</div>
        <div class="odo-value">{{ ($segment && $segment->odometer_out !== null) ? number_format($segment->odometer_out) : '' }}</div>
    </div>
    <div class="odo-cell">
        <div class="odo-label">Miles In</div>
        <div class="odo-value">{{ ($segment && $segment->odometer_in !== null) ? number_format($segment->odometer_in) : '' }}</div>
    </div>
    <div class="odo-cell">
        <div class="odo-label">Miles Driven</div>
        <div class="odo-value">{{ ($segment && $segment->miles_driven !== null) ? number_format($segment->miles_driven) : '' }}</div>
    </div>
    <div class="odo-cell">
        <div class="odo-label">Fuel Level Out</div>
        @php $fuelOut = $segment ? $segment->fuel_level_out : null; @endphp
        <div class="fuel-boxes">
            @foreach(['E', '1/4', '1/2', '3/4', 'F'] as $fl)
                <div class="fuel-box {{ $fuelOut === $fl ? 'checked' : '' }}">{{ $fl }}</div>
            @endforeach
        </div>
    </div>
    <div class="odo-cell">
        <div class="odo-label">Fuel Level In</div>
        @php $fuelIn = $segment ? $segment->fuel_level_in : null; @endphp
        <div class="fuel-boxes">
            @foreach(['E', '1/4', '1/2', '3/4', 'F'] as $fl)
                <div class="fuel-box {{ $fuelIn === $fl ? 'checked' : '' }}">{{ $fl }}</div>
            @endforeach
        </div>
    </div>
</div>

{{-- Three Initials Blocks --}}
<div class="initials-row">
    <div class="initials-block">
        <div class="initials-title">Authorized Driver</div>
        <div class="initials-text">
            I understand that ONLY the person(s) listed on this agreement are authorized to drive this vehicle. I agree that no other person shall operate this vehicle. Unauthorized use voids all coverage and I accept full liability for any resulting damages.
        </div>
        <div class="initials-line">
            <div class="initials-blank"></div>
            <div class="initials-word">Initials</div>
        </div>
    </div>
    <div class="initials-block">
        <div class="initials-title">Coverage Declined</div>
        <div class="initials-text">
            I am DECLINING optional Comprehensive and Collision Damage coverage. I understand I am fully responsible for any damage to, loss of, or theft of the vehicle for the entire rental period, regardless of fault.
        </div>
        <div class="initials-line">
            <div class="initials-blank"></div>
            <div class="initials-word">Initials</div>
        </div>
    </div>
    <div class="initials-block">
        <div class="initials-title">Condition Accepted</div>
        <div class="initials-text">
            I have inspected this vehicle and accept it in its present condition. I agree to return the vehicle in the same condition, subject to normal wear. I understand any new damage will be assessed and billed to me.
        </div>
        <div class="initials-line">
            <div class="initials-blank"></div>
            <div class="initials-word">Initials</div>
        </div>
    </div>
</div>

{{-- Policy Notices --}}
<div class="policy-box">
    <div class="policy-box-title">Important Policies</div>
    <div class="policy-item"><strong>NO SMOKING IN VEHICLES</strong> — $200 minimum cleaning charge if violated.</div>
    <div class="policy-item"><strong>VEHICLES MUST REMAIN WITHIN THE STATE OF TEXAS.</strong> Use outside Texas voids all protections.</div>
    <div class="policy-item"><strong>VEHICLE MUST BE RETURNED TO:</strong> {{ $tenant->name }}{{ $tenant->address ? ', ' . $tenant->address : '' }}{{ $tenant->city ? ', ' . $tenant->city : '' }}{{ $tenant->state ? ', ' . $tenant->state : '' }}{{ $tenant->zip ? ' ' . $tenant->zip : '' }}</div>
    <div class="policy-item">This agreement shall not exceed <strong>30 days</strong> unless renewed in writing.</div>
</div>

{{-- Damage Condition Grid --}}
<div class="damage-section">
    <div class="damage-title">Vehicle Condition at Check-Out — Mark Any Pre-Existing Damage</div>
    <div class="damage-grid">
        <div class="damage-cell"><div class="damage-cell-label">FRONT</div></div>
        <div class="damage-cell"><div class="damage-cell-label">BACK</div></div>
        <div class="damage-cell"><div class="damage-cell-label">LEFT SIDE</div></div>
        <div class="damage-cell"><div class="damage-cell-label">RIGHT SIDE</div></div>
    </div>
</div>

{{-- Signature Block --}}
<p class="sig-notice">By signing below, Renter acknowledges reading and agreeing to all terms on both sides of this Agreement.</p>
<div class="sig-block">
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">Renter Signature &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Date</div>
    </div>
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">Shop Representative &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Date</div>
    </div>
</div>

<div class="footer">
    {{ $tenant->name }} &bull; Rental Agreement &bull; {{ $workOrder->ro_number }} &bull; Page 1 of 2
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- PAGE BREAK                                                                   --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page-break"></div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- PAGE 2 — TERMS & CONDITIONS                                                  --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}

<div class="terms-header">
    <div class="terms-title">Terms and Conditions of Rental</div>
    <div class="terms-sub">{{ $tenant->name }} &bull; Rental Agreement Terms &bull; Page 2 &bull; RA# {{ $workOrder->ro_number }}</div>
</div>

<div class="clause">
    <div class="clause-num">1. AUTHORIZED DRIVERS</div>
    <div class="clause-text">Only persons listed on the face of this agreement are authorized to operate the vehicle. Any person not listed who operates this vehicle is doing so without authorization, and the Renter accepts full personal liability for any resulting loss or damage.</div>
</div>

<div class="clause">
    <div class="clause-num">2. RENTER RESPONSIBILITIES</div>
    <div class="clause-text">The Renter is responsible for all damage to or loss of the vehicle, including damage caused by weather, animals, road hazards, or other conditions, unless a signed damage waiver is in effect. The Renter is also responsible for all consequential damages arising from the loss of use of the vehicle during repair or replacement.</div>
</div>

<div class="clause">
    <div class="clause-num">3. DAMAGE AND LOSS</div>
    <div class="clause-text">The Renter agrees to pay for any physical damage, theft, or destruction of the vehicle during the rental period. In the event of an accident, the Renter must immediately notify {{ $tenant->name }} and cooperate fully in any investigation or insurance claim. Failure to report damage before returning the vehicle forfeits any right to dispute damage claims.</div>
</div>

<div class="clause">
    <div class="clause-num">4. COVERAGE AND WAIVER</div>
    <div class="clause-text">No insurance or coverage is provided with this rental. The Renter is advised to verify whether personal auto insurance or credit card benefits provide coverage for rental vehicles. The Damage Waiver initialed on the face of this agreement (if declined) does not limit the Renter's liability in any way.</div>
</div>

<div class="clause">
    <div class="clause-num">5. RETURN OF VEHICLE</div>
    <div class="clause-text">The vehicle must be returned to {{ $tenant->name }}{{ $tenant->address ? ' at ' . $tenant->address : '' }} on or before the date indicated, in the same condition in which it was received, with the same fuel level. Late returns may be subject to additional daily charges. Failure to return the vehicle on time may result in the vehicle being reported stolen.</div>
</div>

<div class="clause">
    <div class="clause-num">6. FUEL POLICY</div>
    <div class="clause-text">The vehicle is provided with the fuel level shown at the time of rental. The Renter agrees to return the vehicle with the same fuel level. Failure to do so may result in a fuel surcharge at rates above retail pump price.</div>
</div>

<div class="clause">
    <div class="clause-num">7. SMOKING POLICY</div>
    <div class="clause-text">This is a no-smoking vehicle. Any evidence of smoking in the vehicle will result in a minimum $200.00 cleaning charge billed to the Renter, in addition to any other charges.</div>
</div>

<div class="clause">
    <div class="clause-num">8. GEOGRAPHIC RESTRICTIONS</div>
    <div class="clause-text">This vehicle is not authorized for use outside the State of Texas. Any use outside this boundary is a violation of this agreement and voids all protections. The Renter accepts full liability for any incidents, fines, towing, or other costs incurred outside the authorized area.</div>
</div>

<div class="clause">
    <div class="clause-num">9. TRAFFIC VIOLATIONS AND FINES</div>
    <div class="clause-text">The Renter is solely responsible for all parking tickets, traffic citations, toll charges, and any other fines or fees incurred during the rental period. {{ $tenant->name }} is authorized to charge the Renter for any such amounts that are assessed to the vehicle owner.</div>
</div>

<div class="clause">
    <div class="clause-num">10. PROHIBITED USES</div>
    <div class="clause-text">The vehicle may not be used: (a) for any illegal purpose; (b) while the driver is under the influence of alcohol or controlled substances; (c) to push or tow any other vehicle; (d) off paved roads; (e) in any race, speed test, or contest; (f) by any person not listed on this agreement.</div>
</div>

<div class="clause">
    <div class="clause-num">11. CREDIT CARD AUTHORIZATION</div>
    <div class="clause-text">By signing this agreement, the Renter authorizes {{ $tenant->name }} to charge the credit card on file for any amounts owed under this agreement that are not otherwise paid, including damage, fuel, cleaning charges, late fees, fines, or any other amounts incurred during the rental period.</div>
</div>

<div class="clause">
    <div class="clause-num">12. LIMITATION OF LIABILITY</div>
    <div class="clause-text">{{ $tenant->name }} shall not be liable for any personal property left in the vehicle, any personal injury not caused by direct negligence of {{ $tenant->name }}, or any incidental or consequential damages of any kind.</div>
</div>

<div class="clause">
    <div class="clause-num">13. GOVERNING LAW</div>
    <div class="clause-text">This agreement shall be governed by the laws of the State of Texas. Any disputes arising from this agreement shall be resolved in the courts of Texas.</div>
</div>

<div class="clause">
    <div class="clause-num">14. ENTIRE AGREEMENT</div>
    <div class="clause-text">This agreement, including both pages of this form, constitutes the entire agreement between the parties. No verbal representations alter the terms of this written agreement.</div>
</div>

<div class="footer">
    {{ $tenant->name }} &bull; Rental Agreement &bull; {{ $workOrder->ro_number }} &bull; Page 2 of 2
</div>

</body>
</html>
