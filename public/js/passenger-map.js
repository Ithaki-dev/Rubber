// passenger-map.js
// Initializes Leaflet map for passenger dashboard, loads rides within map bounds,
// creates markers with popups and handles reservation modal and polling.

(function(){
    if (typeof L === 'undefined') return; // Leaflet not loaded

    const MAP_CENTER = [9.9538, -84.1316]; // Costa Rica approx
    const MAP_ZOOM = 8;
    const POLL_INTERVAL = 20000; // 20s

    let map = L.map('passengerMap').setView(MAP_CENTER, MAP_ZOOM);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const markersLayer = L.layerGroup().addTo(map);

    let currentMarkers = {};
    let ridesCache = {};
    let pollTimer = null;
    let lastParams = {};
    let selectedRide = null;

    function boundsToString(bounds) {
        // Leaflet LatLngBounds: getSouthWest, getNorthEast
        const sw = bounds.getSouthWest();
        const ne = bounds.getNorthEast();
        return [sw.lat, sw.lng, ne.lat, ne.lng].join(',');
    }

    function fetchRidesForBounds() {
        const b = map.getBounds();
        const boundsStr = boundsToString(b);

        // Read filters from UI
        const date = document.getElementById('date')?.value || '';
        const origin = document.getElementById('origin')?.value || '';
        const destination = document.getElementById('destination')?.value || '';
        const seats = document.getElementById('seats')?.value || '';
        const max_cost = document.getElementById('max_cost')?.value || '';

        const params = new URLSearchParams();
        params.append('bounds', boundsStr);
    if (date) params.append('date', date);
    if (origin) params.append('departure', origin);
    if (destination) params.append('arrival', destination);
        if (seats) params.append('seats', seats);
        if (max_cost) params.append('max_cost', max_cost);

        const url = `${BASE_URL}/api/rides?${params.toString()}`;

        // Fetch and parse response defensively: some server errors return HTML/text (not JSON)
        fetch(url, { credentials: 'include' })
            .then(r => r.text())
            .then(text => {
                // Try to parse JSON, but log the raw response if it fails
                try {
                    const data = JSON.parse(text);
                    if (!data.success) return;
                    updateMarkers(data.rides || []);
                } catch (err) {
                    console.error('Error fetching rides - response was not valid JSON:', err);
                    console.error('Raw response:', text);
                }
            })
            .catch(err => console.error('Network error fetching rides', err));
    }

    function updateMarkers(rides) {
        // Remove markers not present, update or add new ones
        const newIds = {};
        // refresh cache
        ridesCache = {};

        rides.forEach(ride => {
            ridesCache[ride.id] = ride;
            newIds[ride.id] = true;
            const lat = parseFloat(ride.departure_lat) || parseFloat(ride.arrival_lat);
            const lng = parseFloat(ride.departure_lng) || parseFloat(ride.arrival_lng);
            if (!lat || !lng) return;

            if (currentMarkers[ride.id]) {
                // Optionally update popup content
                currentMarkers[ride.id].setPopupContent(popupHtml(ride));
            } else {
                const m = L.marker([lat, lng]).addTo(markersLayer);
                m.bindPopup(popupHtml(ride), {maxWidth: 300});
                m.on('popupopen', () => {
                    // attach event to reserve button as a fallback
                    const btn = document.getElementById('reserve-btn-' + ride.id);
                    if (btn && !btn._reserveBound) {
                        btn.addEventListener('click', () => openReserveModal(ride));
                        btn._reserveBound = true;
                    }
                });
                currentMarkers[ride.id] = m;
            }
        });

        // Remove old markers
        Object.keys(currentMarkers).forEach(id => {
            if (!newIds[id]) {
                markersLayer.removeLayer(currentMarkers[id]);
                delete currentMarkers[id];
            }
        });
    }

    function popupHtml(ride) {
        const driverName = ride.driver_first_name ? `${ride.driver_first_name} ${ride.driver_last_name}` : 'Conductor';
        const date = ride.ride_date;
        const time = ride.ride_time;
        const seats = ride.available_seats;
        const price = ride.cost_per_seat;

        return `
            <div class="popup-ride" tabindex="0">
                <strong>${escapeHtml(ride.ride_name || '')}</strong><br>
                <small class="text-muted">${escapeHtml(driverName)}</small><br>
                <div class="mt-2">
                    <div><i class="bi bi-calendar me-1"></i>${escapeHtml(date)} <i class="bi bi-clock ms-2 me-1"></i>${escapeHtml(time)}</div>
                    <div><i class="bi bi-people me-1"></i>${seats} asientos</div>
                    <div><i class="bi bi-cash-stack me-1"></i>₡${price}</div>
                </div>
                <div class="mt-2 text-end">
                    <button id="reserve-btn-${ride.id}" data-reserve-ride="${ride.id}" class="btn btn-sm btn-primary" aria-label="Reservar viaje ${escapeHtml(ride.ride_name || '')}">Reservar</button>
                </div>
            </div>
        `;
    }

    function openReserveModal(ride) {
        selectedRide = ride;
        const details = document.getElementById('reserveDetails');
        details.innerHTML = `
            <p><strong>${escapeHtml(ride.ride_name || '')}</strong></p>
            <p>${escapeHtml(ride.departure_location || '')} → ${escapeHtml(ride.arrival_location || '')}</p>
            <p><small class="text-muted">${escapeHtml(ride.ride_date)} ${escapeHtml(ride.ride_time)}</small></p>
            <p><strong>${ride.available_seats}</strong> asientos disponibles</p>
        `;
        const seatsInput = document.getElementById('reserveSeats');
        seatsInput.max = ride.available_seats || 1;
        seatsInput.value = 1;

        const reserveModal = new bootstrap.Modal(document.getElementById('reserveModal'));
        reserveModal.show();

        document.getElementById('confirmReserveBtn').onclick = confirmReserve;
    }

    function confirmReserve() {
        if (!selectedRide) return;
        const seats = parseInt(document.getElementById('reserveSeats').value) || 1;
        if (seats <= 0 || seats > selectedRide.available_seats) {
            alert('Número de asientos inválido');
            return;
        }

        // POST form-encoded to /passenger/reservations (matching PHP controller expectations)
        const body = new URLSearchParams();
        body.append('ride_id', selectedRide.id);
        body.append('seats_requested', seats);

        fetch(`${BASE_URL}/passenger/reservations`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: body.toString()
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Close modal and refresh markers
                const reserveModalEl = document.getElementById('reserveModal');
                const reserveModal = bootstrap.Modal.getInstance(reserveModalEl);
                reserveModal.hide();
                setTimeout(() => fetchRidesForBounds(), 800);
                showToast('Reserva creada correctamente');
            } else {
                alert('Error: ' + (data.message || 'No se pudo crear la reserva'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error creando reserva');
        });
    }

    function showToast(msg) {
        // Simple fallback alert for now
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-bg-primary border-0 position-fixed bottom-0 end-0 m-3';
        toast.setAttribute('role','alert');
        toast.setAttribute('aria-live','assertive');
        toast.innerHTML = `<div class="d-flex"><div class="toast-body">${escapeHtml(msg)}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, {delay:3000});
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }

    function escapeHtml(s) {
        if (!s) return '';
        return String(s).replace(/[&<>"']/g, function(c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c]; });
    }

    // Attach controls
    document.getElementById('refreshMapBtn').addEventListener('click', fetchRidesForBounds);
    document.getElementById('centerMapBtn').addEventListener('click', () => map.setView(MAP_CENTER, MAP_ZOOM));

    // Delegated click handler for popup reserve buttons. This avoids fragile bindings
    // when Leaflet moves popup DOM nodes. Uses ridesCache populated by updateMarkers.
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-reserve-ride]');
        if (!btn) return;
        const rideId = btn.getAttribute('data-reserve-ride');
        if (!rideId) return;
        const ride = ridesCache[rideId];
        if (!ride) {
            console.warn('Ride not found in cache for id', rideId);
            return;
        }
        openReserveModal(ride);
    });

    map.on('moveend', () => {
        fetchRidesForBounds();
    });

    // Listen for external requests to refresh the map (e.g., from dashboard controls)
    window.addEventListener('mapRefresh', () => {
        fetchRidesForBounds();
    });

    // Initial load
    fetchRidesForBounds();

    // Polling
    pollTimer = setInterval(fetchRidesForBounds, POLL_INTERVAL);

})();
