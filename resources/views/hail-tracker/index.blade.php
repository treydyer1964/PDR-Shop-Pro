<x-app-layout>
    <x-slot name="headScripts">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <style>
            .mesh-pixelated { image-rendering: pixelated; }
        </style>
    </x-slot>

    <x-slot name="footerScripts">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
        window.hailMapFlyTo = null;

        window.initHailMap = function (el) {
            var reports      = JSON.parse(el.dataset.reports      || '[]');
            var events       = JSON.parse(el.dataset.events       || '[]');
            var subscription = JSON.parse(el.dataset.subscription || 'null');
            var selectedDate = el.dataset.selectedDate || '';
            var showRadar    = el.dataset.showRadar    === '1';
            var showWarnings = el.dataset.showWarnings === '1';
            var showMesh     = el.dataset.showMesh     === '1';
            var meshUrl      = el.dataset.meshUrl       || '';
            var meshCellsUrl = el.dataset.meshCellsUrl  || '';
            // isToday is set server-side using SPC convective day (now()->subHours(12))
            // to avoid the UTC midnight mismatch when selectedDate is still "yesterday" in UTC
            var isToday      = el.dataset.isToday === '1';

            // Destroy previous Leaflet instance on the container if reinitializing
            var container = document.getElementById('hail-map-container');
            if (!container) return;
            if (window._hailMap) {
                window._hailMap.remove();
                window._hailMap = null;
            }

            // Clean up any lingering MESH info control from previous render
            if (window._meshInfoControl) {
                try { window._meshInfoControl.remove(); } catch(e) {}
                window._meshInfoControl = null;
            }

            // Default center: continental US
            var map = L.map('hail-map-container').setView([37.5, -96], 4);
            window._hailMap = map;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // ── NEXRAD radar overlay (IEM) ────────────────────────────────────────

            if (showRadar) {
                var radarTileUrl;

                if (isToday) {
                    // Live composite reflectivity (updates ~every 5 min on IEM)
                    radarTileUrl = 'https://mesonet.agron.iastate.edu/cache/tile.py/1.0.0/nexrad-n0q-900913/{z}/{x}/{y}.png';
                } else {
                    // Historical: use 23:00 UTC frame for the selected date
                    // YYMMDD + HHMM format, e.g. "2505152300"
                    var parts = selectedDate.split('-'); // ['2025','05','15']
                    var yy    = parts[0].substring(2);   // '25'
                    var mm    = parts[1];                // '05'
                    var dd    = parts[2];                // '15'
                    radarTileUrl = 'https://mesonet.agron.iastate.edu/cache/tile.py/1.0.0/nexrad-n0q-' + yy + mm + dd + '2300-900913/{z}/{x}/{y}.png';
                }

                L.tileLayer(radarTileUrl, {
                    attribution: 'Radar: IEM / NEXRAD',
                    opacity:     0.60,
                    zIndex:      5
                }).addTo(map);
            }

            // ── SPC cluster coverage circles (always visible alongside pins) ──────
            // Drawn regardless of the MESH toggle — gives an immediate visual sense
            // of how large each hail swath was, directly from SPC report clusters.

            events.forEach(function (e) {
                if (!e.lat || !e.lng || !e.coverageRadiusM) return;

                L.circle([e.lat, e.lng], {
                    radius:      e.coverageRadiusM,
                    color:       e.color,
                    weight:      1.5,
                    fillColor:   e.color,
                    fillOpacity: 0.15,
                    opacity:     0.50,
                    interactive: false   // pass clicks through to MESH layer / map
                }).addTo(map);
            });

            // ── MESH hail swath overlay (NOAA MRMS 24-h max, captured locally) ──
            // Rendered from MRMS_MESH_Max_1440min — the 24-hour rolling maximum.
            // Captured and saved by mesh:process (runs every 30 min via scheduler).
            // Only available for dates where the pipeline captured data in time.

            if (showMesh && meshUrl) {
                var meshBounds = [[20.005, -129.995], [54.995, -60.005]];
                L.imageOverlay(meshUrl, meshBounds, {
                    opacity:     0.95,
                    zIndex:      4,
                    interactive: false,
                    attribution: 'MESH: NOAA MRMS'
                }).addTo(map);

                if (meshCellsUrl) {
                    fetch(meshCellsUrl)
                        .then(function(r) { return r.json(); })
                        .then(function(cells) {
                            if (!cells || !cells.length) return;

                            var meshLookup = {};
                            cells.forEach(function(cell) {
                                meshLookup[cell.r + ',' + cell.c] = cell.v;
                            });

                            function lookupMesh(lat, lng) {
                                var r0 = Math.round((54.995 - lat)  / 0.1);
                                var c0 = Math.round((lng + 129.995) / 0.1);
                                var best = null, bestD = Infinity;
                                for (var dr = -15; dr <= 15; dr++) {
                                    for (var dc = -15; dc <= 15; dc++) {
                                        var val = meshLookup[(r0 + dr) + ',' + (c0 + dc)];
                                        if (val !== undefined) {
                                            var dist = dr * dr + dc * dc;
                                            if (dist < bestD) { bestD = dist; best = val; }
                                        }
                                    }
                                }
                                return best;
                            }

                            var meshInfo = L.control({ position: 'bottomright' });
                            meshInfo.onAdd = function() {
                                this._div = L.DomUtil.create('div', '');
                                this._div.style.cssText =
                                    'background:rgba(15,23,42,0.90);color:#f8fafc;' +
                                    'padding:8px 12px;border-radius:8px;font-size:13px;' +
                                    'line-height:1.5;min-width:130px;display:none;' +
                                    'pointer-events:none;box-shadow:0 2px 10px rgba(0,0,0,0.35);' +
                                    'border:1px solid rgba(255,255,255,0.10)';
                                return this._div;
                            };
                            meshInfo.addTo(map);
                            window._meshInfoControl = meshInfo;

                            map.on('mousemove', function(e) {
                                var v = lookupMesh(e.latlng.lat, e.latlng.lng);
                                if (v === null) {
                                    meshInfo._div.style.display = 'none';
                                } else {
                                    meshInfo._div.style.display = 'block';
                                    meshInfo._div.innerHTML =
                                        '<div style="font-size:10px;color:#94a3b8;letter-spacing:.04em;text-transform:uppercase;margin-bottom:2px">MESH Swath</div>' +
                                        '<div style="font-size:18px;font-weight:700;line-height:1">' + v.toFixed(2) + '"</div>' +
                                        '<div style="font-size:11px;color:#94a3b8;margin-top:2px">' + meshSizeLabel(v) + '</div>';
                                }
                            });

                            map.on('mouseout', function() { meshInfo._div.style.display = 'none'; });

                            map.on('click', function(e) {
                                var v = lookupMesh(e.latlng.lat, e.latlng.lng);
                                if (v === null) return;
                                L.popup({ maxWidth: 220 })
                                    .setLatLng(e.latlng)
                                    .setContent(
                                        '<div style="font-size:15px;font-weight:700;margin-bottom:3px">' + v.toFixed(2) + '" MESH</div>' +
                                        '<div style="font-size:12px;color:#64748b">' + meshSizeLabel(v) + ' &bull; NOAA MRMS 24-h max</div>'
                                    )
                                    .openOn(map);
                            });
                        })
                        .catch(function() {});
                }
            }

            // ── NWS active warnings overlay ───────────────────────────────────────

            if (showWarnings) {
                var warnUrl;

                if (isToday) {
                    // NWS no longer supports .geojson suffix — use /alerts/active with Accept header
                    warnUrl = 'https://api.weather.gov/alerts/active' +
                              '?event=Tornado+Warning,Severe+Thunderstorm+Warning,Tornado+Watch,Severe+Thunderstorm+Watch';
                } else {
                    // Historical: NWS alerts API with date range
                    warnUrl = 'https://api.weather.gov/alerts' +
                              '?start=' + selectedDate + 'T00:00:00Z' +
                              '&end='   + selectedDate + 'T23:59:59Z' +
                              '&event=Tornado+Warning,Severe+Thunderstorm+Warning,Tornado+Watch,Severe+Thunderstorm+Watch' +
                              '&limit=500';
                }

                fetch(warnUrl, { headers: { 'Accept': 'application/geo+json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        var features = data.features || [];
                        features.forEach(function (f) {
                            if (!f.geometry) return;

                            var event = (f.properties && f.properties.event) ? f.properties.event : '';
                            var headline = (f.properties && f.properties.headline) ? f.properties.headline : event;

                            var isTornado = event.toLowerCase().includes('tornado');
                            var isWatch   = event.toLowerCase().includes('watch');
                            var color     = isTornado ? '#dc2626' : (isWatch ? '#f97316' : '#f59e0b');

                            L.geoJSON(f, {
                                style: {
                                    color:       color,
                                    weight:      2,
                                    fillColor:   color,
                                    fillOpacity: 0.12,
                                    dashArray:   isWatch ? '6, 4' : null
                                }
                            }).addTo(map).bindPopup(
                                '<div style="min-width:180px">' +
                                '<div style="font-weight:700;font-size:13px;margin-bottom:2px;color:' + color + '">' + event + '</div>' +
                                '<div style="font-size:11px;color:#64748b">' + headline + '</div>' +
                                '</div>'
                            );
                        });
                    })
                    .catch(function () {
                        // NWS API may be unavailable — fail silently
                    });
            }

            // ── Size helpers ──────────────────────────────────────────────────────

            function meshSizeLabel(v) {
                if (v >= 4.00) return 'Softball+';
                if (v >= 3.50) return 'Baseball+';
                if (v >= 3.00) return 'Baseball';
                if (v >= 2.75) return 'Baseball−';
                if (v >= 2.50) return 'Egg';
                if (v >= 2.25) return 'Hen Egg';
                if (v >= 2.00) return 'Lime';
                if (v >= 1.75) return 'Golf Ball';
                if (v >= 1.50) return 'Ping Pong';
                if (v >= 1.25) return 'Half Dollar';
                if (v >= 1.00) return 'Quarter';
                if (v >= 0.75) return 'Penny';
                return 'Marble';
            }

            function sizeColor(inches) {
                if (inches >= 2.5)  return '#ef4444';
                if (inches >= 1.75) return '#f97316';
                if (inches >= 1.0)  return '#eab308';
                return '#22c55e';
            }

            function sizeRadius(inches) {
                // Circle radius in pixels: scale 6–18 based on size
                return Math.min(18, Math.max(6, Math.round(inches * 5)));
            }

            // ── Plot hail report pins ─────────────────────────────────────────────

            var bounds = [];

            reports.forEach(function (r) {
                var marker = L.circleMarker([r.lat, r.lng], {
                    radius:      sizeRadius(r.size),
                    fillColor:   r.color,
                    color:       '#ffffff',
                    weight:      1.5,
                    opacity:     1,
                    fillOpacity: 0.85
                }).addTo(map);

                marker.bindPopup(
                    '<div style="min-width:160px">' +
                    '<div style="font-weight:700;font-size:14px;margin-bottom:4px">' +
                        r.size + '" hail' +
                    '</div>' +
                    (r.location ? '<div style="color:#64748b;font-size:12px">' + r.location + '</div>' : '') +
                    (r.time     ? '<div style="color:#94a3b8;font-size:11px;margin-top:2px">' + r.time + '</div>' : '') +
                    '</div>'
                );

                bounds.push([r.lat, r.lng]);
            });

            // ── Home base marker + radius ring ────────────────────────────────────

            if (subscription && subscription.lat && subscription.lng) {
                var homeIcon = L.divIcon({
                    html: '<div style="width:14px;height:14px;border-radius:50%;background:#1d4ed8;border:3px solid #fff;box-shadow:0 0 0 2px #1d4ed8"></div>',
                    className:   '',
                    iconSize:    [14, 14],
                    iconAnchor:  [7, 7],
                    popupAnchor: [0, -10]
                });

                L.marker([subscription.lat, subscription.lng], { icon: homeIcon })
                    .addTo(map)
                    .bindPopup(
                        '<strong>Home Base</strong>' +
                        (subscription.address ? '<br><span style="font-size:12px;color:#64748b">' + subscription.address + '</span>' : '')
                    );

                L.circle([subscription.lat, subscription.lng], {
                    radius:      subscription.radiusMiles * 1609.34,
                    color:       '#1d4ed8',
                    fillColor:   '#1d4ed8',
                    fillOpacity: 0.04,
                    weight:      1.5,
                    dashArray:   '6, 4'
                }).addTo(map);

                bounds.push([subscription.lat, subscription.lng]);
            }

            // ── Fit map to data ───────────────────────────────────────────────────

            if (bounds.length > 1) {
                map.fitBounds(L.latLngBounds(bounds), { padding: [40, 40] });
            } else if (bounds.length === 1) {
                map.setView(bounds[0], 10);
            }

            setTimeout(function () { map.invalidateSize(); }, 150);

            // ── Expose fly-to for event list clicks ───────────────────────────────

            window.hailMapFlyTo = function (lat, lng) {
                map.flyTo([lat, lng], 10, { duration: 0.8 });
            };
        };
        </script>
    </x-slot>

    <x-slot name="header">Hail Tracker</x-slot>

    <livewire:hail-tracker.hail-dashboard />
</x-app-layout>
