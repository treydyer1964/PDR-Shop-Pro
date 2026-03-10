<x-app-layout>
    @php $isMapView = request()->query('mapview') || ($forceMapView ?? false); @endphp
    <x-slot name="headScripts">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <style>
            @keyframes loc-pulse {
                0%   { transform: scale(0.5); opacity: 0.6; }
                100% { transform: scale(3);   opacity: 0; }
            }
        </style>
    </x-slot>
    <x-slot name="footerScripts">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
        window.initLeadMap = function (el) {
            var leads       = JSON.parse(el.dataset.leads       || '[]');
            var territories = JSON.parse(el.dataset.territories || '[]');

            // Guard: destroy any existing Leaflet instance on this container
            var container = document.getElementById('lead-map-container');
            if (container && container._leaflet_id) {
                window._leadMap && window._leadMap.remove();
            }

            var map = L.map('lead-map-container', { maxZoom: 19 });
            window._leadMap = map;

            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                maxZoom: 19
            }).addTo(map);

            territories.forEach(function (t) {
                if (!t.boundary) return;
                try {
                    L.geoJSON(t.boundary, {
                        style: {
                            color:       t.color || '#3b82f6',
                            fillColor:   t.color || '#3b82f6',
                            fillOpacity: 0.08,
                            weight:      2
                        }
                    }).bindTooltip(
                        '<strong>' + t.name + '</strong>' + (t.rep ? '<br>' + t.rep : ''),
                        { sticky: true }
                    ).addTo(map);
                } catch (e) {}
            });

            function makePinIcon(color, stroke) {
                var strokeColor = stroke || 'white';
                var dotFill     = (strokeColor === 'white') ? 'white' : '#334155';
                var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 40" width="28" height="40">'
                    + '<path d="M14 0C6.268 0 0 6.268 0 14c0 10.5 14 26 14 26S28 24.5 28 14C28 6.268 21.732 0 14 0z" fill="' + color + '" stroke="' + strokeColor + '" stroke-width="2"/>'
                    + '<circle cx="14" cy="14" r="5" fill="' + dotFill + '" opacity="0.85"/>'
                    + '</svg>';
                return L.divIcon({
                    html:        svg,
                    className:   '',
                    iconSize:    [28, 40],
                    iconAnchor:  [14, 40],
                    popupAnchor: [0, -38]
                });
            }

            function makePopup(lead) {
                var dot = '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:' + lead.color + ';margin-right:4px;vertical-align:middle"></span>';
                var html = '<div style="min-width:160px">';
                html += '<div style="font-weight:600;margin-bottom:3px">' + (lead.name || '<em style="color:#94a3b8">No name</em>') + '</div>';
                html += '<div style="font-size:12px;margin-bottom:4px">' + dot + lead.statusLabel + '</div>';
                if (lead.damageLabel) html += '<div style="font-size:12px;color:#c2410c;margin-bottom:2px">&#9651; ' + lead.damageLabel + '</div>';
                if (lead.phone)   html += '<div style="font-size:12px">' + lead.phone + '</div>';
                if (lead.address) html += '<div style="font-size:11px;color:#94a3b8">' + lead.address + '</div>';
                if (lead.rep)     html += '<div style="font-size:12px;margin-top:2px">Rep: ' + lead.rep + '</div>';
                html += '<a href="' + lead.url + '" onclick="event.stopPropagation()" style="display:inline-block;margin-top:6px;font-size:12px;color:#2563eb">View Lead &#x2192;</a>';
                html += '</div>';
                return html;
            }

            var allMarkers = [];

            function renderMarkers(filter) {
                allMarkers.forEach(function (m) { m.remove(); });
                allMarkers = [];
                var visible = filter ? leads.filter(function (l) { return l.status === filter; }) : leads;
                visible.forEach(function (lead) {
                    var m = L.marker([lead.lat, lead.lng], { icon: makePinIcon(lead.color, lead.stroke) });
                    m.bindPopup(makePopup(lead));
                    // Stop click from propagating to map (prevents create-lead trigger)
                    m.on('click', function (e) { L.DomEvent.stopPropagation(e); });
                    m.addTo(map);
                    allMarkers.push(m);
                });
            }

            // Set initial view first so map has a valid state
            if (leads.length === 1) {
                map.setView([leads[0].lat, leads[0].lng], 15);
            } else if (leads.length > 1) {
                var bounds = leads.map(function (l) { return [l.lat, l.lng]; });
                map.fitBounds(L.latLngBounds(bounds), { padding: [40, 40] });
            } else {
                map.setView([32.45, -99.73], 12);
            }

            renderMarkers('');

            // Tap empty map space to create a new lead at that location
            map.on('click', function (e) {
                var lat = e.latlng.lat.toFixed(6);
                var lng = e.latlng.lng.toFixed(6);
                window.location.href = '/leads/create?lat=' + lat + '&lng=' + lng;
            });

            map.getContainer().style.cursor = 'crosshair';

            window.leadMapSetFilter = function (val) { renderMarkers(val); };
            window.leadMapCount = function (filter) {
                var n = filter ? leads.filter(function (l) { return l.status === filter; }).length : leads.length;
                return n + ' lead' + (n !== 1 ? 's' : '') + ' on map';
            };

            setTimeout(function () { map.invalidateSize(); }, 150);

            // Current location — pulsing blue dot
            if (navigator.geolocation) {
                var _locMarker   = null;
                var _locAccuracy = null;

                function updateLocationDot(lat, lng, accuracy) {
                    if (_locAccuracy) { _locAccuracy.remove(); _locAccuracy = null; }
                    if (_locMarker)   { _locMarker.remove();   _locMarker   = null; }

                    // Soft accuracy ring
                    _locAccuracy = L.circle([lat, lng], {
                        radius:      accuracy,
                        color:       '#3b82f6',
                        fillColor:   '#3b82f6',
                        fillOpacity: 0.08,
                        weight:      1,
                        interactive: false
                    }).addTo(map);

                    // Pulsing dot icon
                    var dotHtml = '<div style="position:relative;width:16px;height:16px;">'
                        + '<div style="position:absolute;inset:0;border-radius:50%;background:#3b82f6;border:2.5px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.35);z-index:1;"></div>'
                        + '<div style="position:absolute;inset:0;border-radius:50%;background:#3b82f6;animation:loc-pulse 2s ease-out infinite;"></div>'
                        + '</div>';

                    _locMarker = L.marker([lat, lng], {
                        icon: L.divIcon({ html: dotHtml, className: '', iconSize: [16, 16], iconAnchor: [8, 8] }),
                        zIndexOffset: 1000
                    }).addTo(map);
                    _locMarker.bindPopup('<strong>Your Location</strong>');
                }

                navigator.geolocation.watchPosition(
                    function (pos) {
                        updateLocationDot(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                    },
                    function (err) { console.warn('Geolocation:', err.message); },
                    { enableHighAccuracy: true, maximumAge: 5000, timeout: 20000 }
                );
            }
        };
        </script>
    </x-slot>

    <x-slot name="header">Leads</x-slot>
    <x-slot name="headerActions">
        {{-- List / Map toggle --}}
        <div class="flex overflow-hidden rounded-lg border border-slate-200 bg-white text-sm">
            <a href="{{ route('leads.index') }}" wire:navigate
               @class([
                   'px-3 py-1.5 font-medium transition-colors flex items-center gap-1.5',
                   'bg-slate-900 text-white' => ! $isMapView,
                   'text-slate-500 hover:bg-slate-50' => $isMapView,
               ])>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                </svg>
                List
            </a>
            <a href="{{ route('leads.map') }}" wire:navigate
               @class([
                   'px-3 py-1.5 font-medium transition-colors flex items-center gap-1.5 border-l border-slate-200',
                   'bg-slate-900 text-white' => $isMapView,
                   'text-slate-500 hover:bg-slate-50' => ! $isMapView,
               ])>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.159.69.159 1.006 0z" />
                </svg>
                Map
            </a>
        </div>

        @if(auth()->user()->canCreateWorkOrders())
            <a href="{{ route('leads.create') }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Lead
            </a>
        @endif
    </x-slot>

    @if($isMapView)
        <livewire:leads.lead-map />
    @else
        <livewire:leads.lead-list />
    @endif
</x-app-layout>
