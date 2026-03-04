<x-app-layout>
    <x-slot name="headScripts">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
        <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" crossorigin="anonymous">
    </x-slot>
    <x-slot name="footerScripts">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/sp38=" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js" crossorigin="anonymous"></script>
        <script>
        window.initTerritoryDrawMap = function (el, wire) {
            var boundaryStr      = el.dataset.boundary  || '';
            var existingStr      = el.dataset.existing  || '[]';
            var editingId        = el.dataset.editing   ? parseInt(el.dataset.editing) : null;

            var initBoundary = null;
            try { if (boundaryStr) initBoundary = JSON.parse(boundaryStr); } catch (e) {}

            var existingTerritories = [];
            try { existingTerritories = JSON.parse(existingStr); } catch (e) {}

            var map = L.map('territory-draw-map');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            var drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            var drawControl = new L.Control.Draw({
                draw: {
                    polygon:      { allowIntersection: false, showArea: true },
                    polyline:     false,
                    rectangle:    false,
                    circle:       false,
                    marker:       false,
                    circlemarker: false
                },
                edit: { featureGroup: drawnItems }
            });
            map.addControl(drawControl);

            map.on(L.Draw.Event.CREATED, function (e) {
                drawnItems.clearLayers();
                drawnItems.addLayer(e.layer);
                wire.set('boundary', JSON.stringify(e.layer.toGeoJSON()));
            });

            map.on(L.Draw.Event.EDITED, function (e) {
                e.layers.eachLayer(function (layer) {
                    wire.set('boundary', JSON.stringify(layer.toGeoJSON()));
                });
            });

            map.on(L.Draw.Event.DELETED, function () {
                wire.set('boundary', '');
            });

            if (initBoundary) {
                try {
                    var geoLayer = L.geoJSON(initBoundary);
                    geoLayer.getLayers().forEach(function (l) { drawnItems.addLayer(l); });
                    map.fitBounds(drawnItems.getBounds(), { padding: [20, 20] });
                } catch (e) {
                    map.setView([32.45, -99.73], 11);
                }
            } else {
                map.setView([32.45, -99.73], 11);
            }

            existingTerritories
                .filter(function (t) { return t.id !== editingId; })
                .forEach(function (t) {
                    if (!t.boundary) return;
                    try {
                        L.geoJSON(t.boundary, {
                            style: {
                                color:       t.color || '#64748b',
                                fillColor:   t.color || '#64748b',
                                fillOpacity: 0.06,
                                weight:      1.5,
                                dashArray:   '5,5'
                            }
                        }).bindTooltip(t.name, { sticky: true }).addTo(map);
                    } catch (e) {}
                });

            // Force map to recalculate size after CSS has fully applied
            setTimeout(function () { map.invalidateSize(); }, 100);
        };
        </script>
    </x-slot>

    <x-slot name="header">Settings</x-slot>

    <div class="flex gap-6 lg:flex-row flex-col">
        @include('settings.partials.subnav')
        <div class="flex-1 min-w-0">
            <livewire:settings.territory-manager />
        </div>
    </div>
</x-app-layout>
