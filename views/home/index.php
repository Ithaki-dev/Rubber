<?php
$pageTitle = 'Carpooling UTN - Buscar viajes';
ob_start();
?>

<style>
    #map { height: 80vh; width: 100%; }
    .map-container { position: relative; }
    .rides-panel {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(255,255,255,0.95);
        border-radius: 8px;
        padding: 12px;
        width: 320px;
        max-height: 75vh;
        overflow: auto;
        box-shadow: 0 6px 24px rgba(0,0,0,0.15);
        z-index: 1000;
    }
    .ride-item { border-bottom: 1px solid #eee; padding: 8px 0; }
    .ride-item:last-child { border-bottom: none; }
    .ride-title { font-weight: 600; color: #333; }
    .ride-meta { color: #666; font-size: 0.9rem; }
    .btn-reserve { margin-top: 6px; }
</style>

<div class="map-container">
    <div id="map"></div>

    <div class="rides-panel">
        <h5>Viajes disponibles</h5>
        <div id="ridesList">Cargando viajes...</div>
        <div class="mt-2 text-end">
            <button class="btn btn-sm btn-outline-secondary" onclick="loadAvailableRides()">Actualizar</button>
        </div>
    </div>
</div>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const USER_LOGGED = <?= Session::isLoggedIn() ? 'true' : 'false' ?>;

    let map, markersLayer;

    function initMap(){
        map = L.map('map').setView([9.935, -84.087], 9); // Costa Rica approx
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        markersLayer = L.layerGroup().addTo(map);
        loadAvailableRides();
    }

    function renderRideCard(ride){
        const dep = ride.departure_location || '';
        const arr = ride.arrival_location || '';
        const date = ride.ride_date || '';
        const time = ride.ride_time || '';
        const price = ride.cost_per_seat || ride.price_per_seat || '0';
        const seats = ride.available_seats ?? ride.total_seats ?? 0;

        const div = document.createElement('div');
        div.className = 'ride-item';
        div.innerHTML = `
            <div class="ride-title">${escapeHtml(ride.ride_name || (dep + ' → ' + arr))}</div>
            <div class="ride-meta">${escapeHtml(dep)} → ${escapeHtml(arr)} ${escapeHtml(date)} ${escapeHtml(time)} • ₡${escapeHtml(String(price))} • ${escapeHtml(String(seats))} asientos</div>
        `;

        const btn = document.createElement('div');
        if (USER_LOGGED) {
            btn.innerHTML = `<form method="POST" action="${BASE_URL}/passenger/reservations" onsubmit="return confirm('Confirmar reserva?');">
                <input type="hidden" name="ride_id" value="${escapeHtml(String(ride.id))}" />
                <input type="hidden" name="seats_requested" value="1" />
                <button class="btn btn-sm btn-primary btn-reserve">Reservar</button>
            </form>`;
        } else {
            btn.innerHTML = `<div class="text-muted"></div>`;
        }

        div.appendChild(btn);
        return div;
    }

    function escapeHtml(s){
        if (!s) return '';
        return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c]; });
    }

    function loadAvailableRides(){
        const list = document.getElementById('ridesList');
        list.innerHTML = '<div class="text-center py-3">Cargando...</div>';
        fetch(`${BASE_URL}/api/rides`)
            .then(r=>r.json())
            .then(data => {
                markersLayer.clearLayers();
                list.innerHTML = '';
                if (data.success && data.rides && data.rides.length){
                    data.rides.forEach(ride => {
                        // add marker if coords available
                        const dlat = ride.departure_lat; const dlng = ride.departure_lng;
                        if (dlat && dlng) {
                            const m = L.marker([parseFloat(dlat), parseFloat(dlng)]).addTo(markersLayer);
                            m.bindPopup(`<strong>${escapeHtml(ride.ride_name || '')}</strong><br>${escapeHtml(ride.departure_location || '')} → ${escapeHtml(ride.arrival_location || '')}`);
                        }
                        list.appendChild(renderRideCard(ride));
                    });
                    // fit bounds if markers exist
                    const group = markersLayer.getLayers();
                    if (group.length) {
                        const g = L.featureGroup(group);
                        map.fitBounds(g.getBounds().pad(0.25));
                    }
                } else {
                    list.innerHTML = '<div class="text-center text-muted py-3">No hay viajes disponibles</div>';
                }
            }).catch(err => {
                console.error('Error loading rides', err);
                list.innerHTML = '<div class="text-danger py-3">Error cargando viajes</div>';
            });
    }

    document.addEventListener('DOMContentLoaded', initMap);
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
