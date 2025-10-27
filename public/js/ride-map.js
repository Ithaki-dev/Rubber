// ride-map-fixed.js
// Minimal Leaflet modal to pick departure/arrival coordinates and fill hidden inputs

console.log('[ride-map] script loaded');
let rideMap = null;
let departureMarker = null;
let arrivalMarker = null;
let activePick = null; // 'departure' or 'arrival'

function ensureRideMapInitialized() {
    if (rideMap) return;

    const mapDiv = document.getElementById('rideMap');
    if (!mapDiv) return;

    rideMap = L.map('rideMap').setView([0,0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(rideMap);

    rideMap.on('click', function(e) {
        const {lat, lng} = e.latlng;
        placeMarkerForActive(lat, lng);
    });
}

function placeMarkerForActive(lat, lng) {
    if (activePick === 'departure') {
        if (departureMarker) departureMarker.setLatLng([lat,lng]);
        else departureMarker = L.marker([lat,lng], {draggable:true}).addTo(rideMap).on('dragend', function(ev){
            const p = ev.target.getLatLng();
            updateHiddenCoords('departure', p.lat, p.lng);
        });
        updateHiddenCoords('departure', lat, lng);
    } else if (activePick === 'arrival') {
        if (arrivalMarker) arrivalMarker.setLatLng([lat,lng]);
        else arrivalMarker = L.marker([lat,lng], {draggable:true}).addTo(rideMap).on('dragend', function(ev){
            const p = ev.target.getLatLng();
            updateHiddenCoords('arrival', p.lat, p.lng);
        });
        updateHiddenCoords('arrival', lat, lng);
    }
}

function updateHiddenCoords(which, lat, lng) {
    const depLatEl = document.getElementById('departureLat');
    const depLngEl = document.getElementById('departureLng');
    const arrLatEl = document.getElementById('arrivalLat');
    const arrLngEl = document.getElementById('arrivalLng');
    const depTextEl = document.getElementById('departureLocation');
    const arrTextEl = document.getElementById('arrivalLocation');

    if (which === 'departure') {
        if (depLatEl) depLatEl.value = lat;
        if (depLngEl) depLngEl.value = lng;
        if (depTextEl) depTextEl.value = lat.toFixed(6) + ', ' + lng.toFixed(6);
    } else {
        if (arrLatEl) arrLatEl.value = lat;
        if (arrLngEl) arrLngEl.value = lng;
        if (arrTextEl) arrTextEl.value = lat.toFixed(6) + ', ' + lng.toFixed(6);
    }
}

function openRideMapModal(which) {
    console.log('[ride-map] openRideMapModal called for', which);
    activePick = which; // 'departure' or 'arrival'
    const modalEl = document.getElementById('rideMapModal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);

    ensureRideMapInitialized();

    const onShown = () => {
        try { rideMap.invalidateSize(); } catch(e) {}

        const latEl = document.getElementById(which === 'departure' ? 'departureLat' : 'arrivalLat');
        const lngEl = document.getElementById(which === 'departure' ? 'departureLng' : 'arrivalLng');
        const lat = latEl && latEl.value !== '' ? parseFloat(latEl.value) : null;
        const lng = lngEl && lngEl.value !== '' ? parseFloat(lngEl.value) : null;
        if (lat && lng) {
            rideMap.setView([lat,lng], 13);
            placeMarkerForActive(lat,lng);
        } else {
            rideMap.setView([0,0], 2);
        }
    };

    modalEl.addEventListener('shown.bs.modal', onShown, { once: true });
    modal.show();
}

function setupRideMapBindings() {
    console.log('[ride-map] setupRideMapBindings');
    const pickDeparture = document.getElementById('pickDepartureBtn');
    const pickArrival = document.getElementById('pickArrivalBtn');
    if (pickDeparture) pickDeparture.addEventListener('click', () => openRideMapModal('departure'));
    if (pickArrival) pickArrival.addEventListener('click', () => openRideMapModal('arrival'));

    const acceptBtn = document.getElementById('rideMapAccept');
    if (acceptBtn) acceptBtn.addEventListener('click', () => {
        const modalEl = document.getElementById('rideMapModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setupRideMapBindings();
});
// If script is loaded after DOMContentLoaded fired, bind immediately
if (document.readyState !== 'loading') {
    try { setupRideMapBindings(); } catch (e) { /* ignore */ }
}