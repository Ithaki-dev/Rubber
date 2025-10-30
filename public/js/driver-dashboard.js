// driver-dashboard script

function initDriverDashboard() {
    // Navigation
    const navLinks = document.querySelectorAll('.nav-link[data-section]');
    const sections = document.querySelectorAll('.content-section');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active nav
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide sections
            const targetSection = this.getAttribute('data-section');
            sections.forEach(section => {
                if (section.id === targetSection + '-section') {
                    section.classList.remove('d-none');
                } else {
                    section.classList.add('d-none');
                }
            });
            
            // Load section-specific data
            loadSectionData(targetSection);
        });
    });

    // Load initial data
    loadSectionData('rides');
    loadStats();
    loadVehicles();

    // Create ride form
    const createRideForm = document.getElementById('createRideForm');
    if (createRideForm) {
        createRideForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (this.checkValidity()) {
                createRide();
            }
            this.classList.add('was-validated');
        });
    }
    // Handle any dashboard URL flags (open modals/sections)
    try { handleDashboardFlags(); } catch (e) { /* ignore */ }
}

// If DOM already loaded, init immediately; otherwise wait for DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDriverDashboard);
} else {
    initDriverDashboard();
}

// Toast helper
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toastId = 'toast-' + Date.now();
    const toastEl = document.createElement('div');
    toastEl.className = 'toast align-items-center text-bg-' + (type === 'error' ? 'danger' : (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'info'))) + ' border-0';
    toastEl.role = 'alert';
    toastEl.ariaLive = 'assertive';
    toastEl.ariaAtomic = 'true';
    toastEl.id = toastId;
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${escapeHtml(message)}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    container.appendChild(toastEl);
    const b = new bootstrap.Toast(toastEl, { delay: 4000 });
    b.show();
    // remove after hidden
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

// Ensure escapeHtml exists (some pages define it globally). Fallback here to be safe.
if (typeof escapeHtml === 'undefined') {
    function escapeHtml(s) {
        if (!s) return '';
        return String(s).replace(/[&<>"']/g, function(c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c];
        });
    }
}

function loadSectionData(section) {
    switch(section) {
        case 'rides':
            loadRides();
            break;
        case 'reservations':
            loadReservations();
            break;
        case 'earnings':
            loadEarnings();
            break;
    }
}

function loadStats() {
    fetch(`${BASE_URL}/api/driver/stats`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalRides').textContent = data.stats.totalRides;
                document.getElementById('totalPassengers').textContent = data.stats.totalPassengers;
                document.getElementById('totalEarnings').textContent = '₡' + data.stats.totalEarnings;
                document.getElementById('averageRating').textContent = data.stats.averageRating;
            }
        });
}

function loadRides() {
    // Load upcoming rides
    fetch(`${BASE_URL}/api/driver/rides?status=upcoming`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('upcomingRidesContainer');
            if (data.success && data.rides.length > 0) {
                container.innerHTML = data.rides.map(ride => createRideCard(ride)).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="bi bi-calendar-x display-4"></i>
                        <p class="mt-2">No tienes viajes programados</p>
                    </div>
                `;
            }
        });

    // Load completed rides
    fetch(`${BASE_URL}/api/driver/rides?status=completed`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('completedRidesContainer');
            if (data.success && data.rides.length > 0) {
                container.innerHTML = data.rides.map(ride => createRideCard(ride, true)).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="bi bi-check-circle display-4"></i>
                        <p class="mt-2">No tienes viajes completados</p>
                    </div>
                `;
            }
        });
}

function loadVehicles() {
    fetch(`${BASE_URL}/api/driver/vehicles`)
        .then(response => response.json())
        .then(data => {
            // Update vehicle select
            const select = document.getElementById('vehicle_id');
            select.innerHTML = '<option value="">Selecciona un vehículo</option>';

            const container = document.getElementById('vehiclesContainer');
            if (data.success && data.vehicles && data.vehicles.length > 0) {
                data.vehicles.forEach(vehicle => {
                    const option = document.createElement('option');
                    option.value = vehicle.id;
                    const brand = vehicle.brand || vehicle.make || '';
                    const model = vehicle.model || '';
                    const plate = vehicle.plate_number || vehicle.license_plate || '';
                    option.textContent = `${brand} ${model} (${plate})`;
                    select.appendChild(option);
                });

                // Render table of vehicles
                container.innerHTML = `
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="vehiclesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Marca</th>
                                            <th>Modelo</th>
                                            <th>Año</th>
                                            <th>Placa</th>
                                            <th>Color</th>
                                            <th>Asientos</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    ${data.vehicles.map(v => {
                                        const brand = v.brand || v.make || '';
                                        const model = v.model || '';
                                        const year = v.year || '';
                                        const plate = v.plate_number || v.license_plate || '';
                                        const color = v.color || '';
                                        const seats = v.seats_capacity || v.seats || '';
                                        return `
                                        <tr>
                                            <td>${escapeHtml(brand)}</td>
                                            <td>${escapeHtml(model)}</td>
                                            <td>${escapeHtml(year)}</td>
                                            <td>${escapeHtml(plate)}</td>
                                            <td>${escapeHtml(color)}</td>
                                            <td>${escapeHtml(String(seats))}</td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-secondary" onclick="editVehicle(${v.id})">Editar</button>
                                                <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteVehicle(${v.id})">Eliminar</button>
                                            </td>
                                        </tr>
                                    `}).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="bi bi-car-front display-4"></i>
                        <p class="mt-2">No tienes vehículos registrados</p>
                    </div>
                `;
            }
        });
}

function createRideCard(ride, isCompleted = false) {
    return `
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6><i class="bi bi-geo-alt me-1"></i>De: ${escapeHtml(ride.departure_location || ride.origin || '')}</h6>
                        <h6><i class="bi bi-geo-alt-fill me-1"></i>A: ${escapeHtml(ride.arrival_location || ride.destination || '')}</h6>
                        <p class="mb-1"><i class="bi bi-calendar me-1"></i>${escapeHtml(ride.ride_date || ride.date || '')} <i class="bi bi-clock me-1"></i>${escapeHtml(ride.ride_time || ride.departure_time || '')}</p>
                        <p class="mb-1"><i class="bi bi-people me-1"></i>${escapeHtml(String(ride.occupied_seats || ride.occupied || 0))}/${escapeHtml(String(ride.available_seats ?? ride.total_seats ?? 0))} asientos</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <h6 class="text-primary">₡${escapeHtml(String(ride.cost_per_seat || ride.price_per_seat || '0'))}/asiento</h6>
                        ${!isCompleted ? `
                            <button class="btn btn-sm btn-primary me-1" onclick="viewRideDetails(${ride.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="cancelRide(${ride.id})">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        ` : `
                            <span class="badge bg-success">Completado</span>
                        `}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createRide() {
    // Validate coordinates (must be present and in valid ranges) before submitting
    const depLat = parseFloat(document.getElementById('departureLat').value || '');
    const depLng = parseFloat(document.getElementById('departureLng').value || '');
    const arrLat = parseFloat(document.getElementById('arrivalLat').value || '');
    const arrLng = parseFloat(document.getElementById('arrivalLng').value || '');

    function validLat(lat) { return !Number.isNaN(lat) && lat >= -90 && lat <= 90; }
    function validLng(lng) { return !Number.isNaN(lng) && lng >= -180 && lng <= 180; }

    if (!validLat(depLat) || !validLng(depLng)) {
        showToast('Por favor selecciona un punto de origen válido en el mapa.', 'error');
        return;
    }
    if (!validLat(arrLat) || !validLng(arrLng)) {
        showToast('Por favor selecciona un punto de destino válido en el mapa.', 'error');
        return;
    }

    // Submit the form to the driver's ride store route so server-side
    // validation and flashes work as expected.
    const form = document.getElementById('createRideForm');
    form.action = `${BASE_URL}/driver/rides`;
    form.method = 'POST';
    form.submit();
}

function showCreateRide() {
    const modalEl = document.getElementById('createRideModal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);
    // reset form
    const form = document.getElementById('createRideForm');
    if (form) { form.reset(); form.classList.remove('was-validated'); }
    modal.show();
}

function showRides() {
    document.querySelector('[data-section="rides"]').click();
}

function showAddVehicle() {
    // Show inline Add Vehicle modal
    const modalEl = document.getElementById('addVehicleModal');
    if (!modalEl) {
        // Fallback: redirect if modal not present
        window.location.href = `${BASE_URL}/driver/vehicles/create`;
        return;
    }
    const modal = new bootstrap.Modal(modalEl);
    // reset form
    const form = document.getElementById('addVehicleForm');
    form.reset();
    form.classList.remove('was-validated');
    modal.show();
}

function loadReservations() {
    const container = document.getElementById('reservationsContainer');
    container.innerHTML = '<div class="text-center"><div class="spinner" role="status"></div></div>';

    fetch(`${BASE_URL}/driver/reservations`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        if (data.success && data.reservations.length > 0) {
            container.innerHTML = data.reservations.map(r => reservationCard(r)).join('');
        } else {
            container.innerHTML = `
                <div class="text-center text-muted">
                    <i class="bi bi-people display-4"></i>
                    <p class="mt-2">No tienes reservas pendientes</p>
                </div>
            `;
        }
    })
    .catch(err => {
        console.error('Error loading reservations', err);
        container.innerHTML = `<div class="text-danger">Error cargando reservas</div>`;
    });
}

function reservationCard(r) {
    const passenger = `${escapeHtml(r.passenger_first_name)} ${escapeHtml(r.passenger_last_name)}`;
    const rideInfo = `${escapeHtml(r.departure_location)} → ${escapeHtml(r.arrival_location)} (${escapeHtml(r.ride_date)} ${escapeHtml(r.ride_time)})`;
    const controls = r.status === 'pending' ? `
        <button class="btn btn-sm btn-success me-1" onclick="acceptReservationAjax(${r.id})">Aceptar</button>
        <button class="btn btn-sm btn-danger" onclick="rejectReservationAjax(${r.id})">Rechazar</button>
    ` : `<span class="badge bg-secondary">${escapeHtml(r.status)}</span>`;

    return `
        <div class="card mb-2">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-1">${passenger}</h6>
                        <small class="text-muted d-block">${rideInfo}</small>
                        <small class="text-muted">Asientos: ${r.seats_requested}</small>
                    </div>
                    <div class="text-end">
                        ${controls}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function acceptReservationAjax(id) {
    if (!confirm('Aceptar esta reserva?')) return;
    fetch(`${BASE_URL}/driver/reservations/${id}/accept`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            loadReservations();
            loadStats();
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error procesando la petición', 'error');
    });
}

function rejectReservationAjax(id) {
    if (!confirm('Rechazar esta reserva?')) return;
    fetch(`${BASE_URL}/driver/reservations/${id}/reject`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            loadReservations();
            loadStats();
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error procesando la petición', 'error');
    });
}

function loadEarnings() {
    // TODO: Load earnings data
}

function viewRideDetails(rideId) {
    // TODO: Show ride details modal
}

function cancelRide(rideId) {
    if (!confirm('¿Estás seguro de que deseas cancelar este viaje?')) return;
    fetch(`${BASE_URL}/driver/rides/${rideId}/delete`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Viaje cancelado', 'success');
            loadRides();
            loadStats();
        } else {
            showToast('Error: ' + (data.message || 'No se pudo cancelar'), 'error');
        }
    })
    .catch(err => {
        console.error('cancelRide error', err);
        showToast('Error procesando la petición', 'error');
    });
}

/**
 * Vehicle management (AJAX)
 */
function submitVehicleAjax(e) {
    e.preventDefault();
    const form = document.getElementById('addVehicleForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    // Build FormData using the backend's expected field names
    const raw = new FormData(form);
    const formData = new FormData();
    // Map frontend names to backend names
    // frontend -> backend: make -> brand, model -> model, year -> year,
    // license_plate -> plate_number, color -> color, seats -> seats_capacity
    formData.append('brand', raw.get('make') || '');
    formData.append('model', raw.get('model') || '');
    formData.append('year', raw.get('year') || '');
    formData.append('plate_number', raw.get('license_plate') || '');
    formData.append('color', raw.get('color') || '');
    formData.append('seats_capacity', raw.get('seats') || '');

    // If the form includes a file input named 'photo', forward it
    const photo = form.querySelector('input[type="file"][name="photo"]');
    if (photo && photo.files && photo.files[0]) {
        formData.append('photo', photo.files[0]);
    }

    fetch(`${BASE_URL}/driver/vehicles`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Vehículo agregado', 'success');
            // hide modal
            const modalEl = document.getElementById('addVehicleModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            // refresh list and vehicle select
            loadVehicles();
        } else {
            showToast(data.message || 'Error creando vehículo', 'error');
        }
    })
    .catch(err => {
        console.error('submitVehicleAjax error', err);
        showToast('Error procesando la petición', 'error');
    });
}

function deleteVehicle(id) {
    if (!confirm('¿Eliminar este vehículo?')) return;
    fetch(`${BASE_URL}/driver/vehicles/${id}/delete`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Vehículo eliminado', 'success');
            loadVehicles();
        } else {
            showToast(data.message || 'Error eliminando vehículo', 'error');
        }
    })
    .catch(err => {
        console.error('deleteVehicle error', err);
        showToast('Error procesando la petición', 'error');
    });
}

function editVehicle(id) {
    openEditVehicleModal(id);
}

// After initialization flag handling is called inside initDriverDashboard

// -------------------------
// Dashboard flag handlers
// -------------------------
function handleDashboardFlags() {
    const qs = new URLSearchParams(window.location.search);
    if (qs.get('open_rides') === '1') {
        showRides();
    }
    if (qs.get('open_vehicles') === '1') {
        document.querySelector('[data-section="vehicles"]').click();
    }
    if (qs.get('open_reservations') === '1') {
        document.querySelector('[data-section="reservations"]').click();
    }
    if (qs.get('open_add_vehicle') === '1') {
        showAddVehicle();
    }
    if (qs.get('open_create') === '1') {
        showCreateRide();
    }
    const editVehicleId = qs.get('edit_vehicle');
    if (editVehicleId) openEditVehicleModal(parseInt(editVehicleId, 10));
    const editRideId = qs.get('edit_ride');
    if (editRideId) openEditRideModal(parseInt(editRideId, 10));
    const showRideId = qs.get('show_ride');
    if (showRideId) openShowRideModal(parseInt(showRideId, 10));
}

// -------------------------
// Edit Vehicle modal flow
// -------------------------
function openEditVehicleModal(id) {
    fetch(`${BASE_URL}/api/driver/vehicles`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error('No vehicles');
            const v = (data.vehicles || []).find(x => String(x.id) === String(id));
            if (!v) throw new Error('Vehículo no encontrado');

            document.getElementById('edit_vehicle_brand').value = v.brand || v.make || '';
            document.getElementById('edit_vehicle_model').value = v.model || '';
            document.getElementById('edit_vehicle_year').value = v.year || '';
            document.getElementById('edit_vehicle_plate').value = v.plate_number || v.license_plate || '';
            document.getElementById('edit_vehicle_color').value = v.color || '';
            document.getElementById('edit_vehicle_seats').value = v.seats_capacity || v.seats || '';

            const form = document.getElementById('editVehicleForm');
            form.action = `${BASE_URL}/driver/vehicles/${id}`;
            form.method = 'POST';
            form.classList.remove('was-validated');

            const modalEl = document.getElementById('editVehicleModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        })
        .catch(err => {
            console.error('openEditVehicleModal', err);
            showToast('No se pudo cargar el vehículo', 'error');
        });
}

const editVehicleForm = document.getElementById('editVehicleForm');
if (editVehicleForm) {
    editVehicleForm.addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            this.classList.add('was-validated');
        }
    });
}

// -------------------------
// Edit Ride modal flow
// -------------------------
function fetchAllRides() {
    return Promise.all([
        fetch(`${BASE_URL}/api/driver/rides?status=upcoming`).then(r => r.json()).then(d => d.rides || []),
        fetch(`${BASE_URL}/api/driver/rides?status=completed`).then(r => r.json()).then(d => d.rides || [])
    ]).then(([a,b]) => a.concat(b));
}

function openEditRideModal(id) {
    fetchAllRides()
        .then(rides => {
            const ride = rides.find(r => String(r.id) === String(id));
            if (!ride) throw new Error('Viaje no encontrado');

            document.getElementById('edit_ride_name').value = ride.ride_name || '';
            document.getElementById('edit_departureLocation').value = ride.departure_location || '';
            document.getElementById('edit_arrivalLocation').value = ride.arrival_location || '';
            document.getElementById('edit_ride_date').value = ride.ride_date || '';
            document.getElementById('edit_ride_time').value = ride.ride_time || '';
            document.getElementById('edit_cost_per_seat').value = ride.cost_per_seat || '';
            document.getElementById('edit_total_seats').value = ride.total_seats || '';

            document.getElementById('edit_departureLat').value = ride.departure_lat || '';
            document.getElementById('edit_departureLng').value = ride.departure_lng || '';
            document.getElementById('edit_arrivalLat').value = ride.arrival_lat || '';
            document.getElementById('edit_arrivalLng').value = ride.arrival_lng || '';

            return fetch(`${BASE_URL}/api/driver/vehicles`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('edit_vehicle_id');
                    sel.innerHTML = '<option value="">Selecciona un vehículo</option>';
                    (data.vehicles || []).forEach(v => {
                        const opt = document.createElement('option');
                        opt.value = v.id;
                        opt.textContent = `${v.brand || v.make || ''} ${v.model || ''} (${v.plate_number || v.license_plate || ''})`;
                        sel.appendChild(opt);
                    });
                    sel.value = ride.vehicle_id || '';

                    const form = document.getElementById('editRideForm');
                    form.action = `${BASE_URL}/driver/rides/${id}`;
                    form.method = 'POST';
                    form.classList.remove('was-validated');

                    const depLat = document.getElementById('edit_departureLat').value;
                    const depLng = document.getElementById('edit_departureLng').value;
                    const arrLat = document.getElementById('edit_arrivalLat').value;
                    const arrLng = document.getElementById('edit_arrivalLng').value;
                    const globalDepLat = document.getElementById('departureLat');
                    const globalDepLng = document.getElementById('departureLng');
                    const globalArrLat = document.getElementById('arrivalLat');
                    const globalArrLng = document.getElementById('arrivalLng');
                    if (globalDepLat) globalDepLat.value = depLat;
                    if (globalDepLng) globalDepLng.value = depLng;
                    if (globalArrLat) globalArrLat.value = arrLat;
                    if (globalArrLng) globalArrLng.value = arrLng;

                    const acceptBtn = document.getElementById('rideMapAccept');
                    const copyBack = function(){
                        const gd = document.getElementById('departureLat');
                        const gg = document.getElementById('departureLng');
                        const ad = document.getElementById('arrivalLat');
                        const ag = document.getElementById('arrivalLng');
                        if (gd && gg && ad && ag) {
                            document.getElementById('edit_departureLat').value = gd.value;
                            document.getElementById('edit_departureLng').value = gg.value;
                            document.getElementById('edit_arrivalLat').value = ad.value;
                            document.getElementById('edit_arrivalLng').value = ag.value;
                            document.getElementById('edit_departureLocation').value = (gd.value && gg.value) ? parseFloat(gd.value).toFixed(6) + ', ' + parseFloat(gg.value).toFixed(6) : '';
                            document.getElementById('edit_arrivalLocation').value = (ad.value && ag.value) ? parseFloat(ad.value).toFixed(6) + ', ' + parseFloat(ag.value).toFixed(6) : '';
                        }
                    };
                    if (acceptBtn) {
                        acceptBtn.addEventListener('click', copyBack, { once: true });
                    }

                    const modalEl = document.getElementById('editRideModal');
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                });
        })
        .catch(err => {
            console.error('openEditRideModal', err);
            showToast('No se pudo cargar el viaje', 'error');
        });
}

const editRideForm = document.getElementById('editRideForm');
if (editRideForm) {
    editRideForm.addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            this.classList.add('was-validated');
        }
    });
}

function fetchAllRides() {
    return Promise.all([
        fetch(`${BASE_URL}/api/driver/rides?status=upcoming`).then(r => r.json()).then(d => d.rides || []),
        fetch(`${BASE_URL}/api/driver/rides?status=completed`).then(r => r.json()).then(d => d.rides || [])
    ]).then(([a,b]) => a.concat(b));
}

function openShowRideModal(id) {
    fetchAllRides()
        .then(rides => {
            const ride = rides.find(r => String(r.id) === String(id));
            if (!ride) throw new Error('Viaje no encontrado');
            const body = document.getElementById('showRideBody');
            body.innerHTML = `
                <h5>${escapeHtml(ride.ride_name || '')}</h5>
                <p><strong>Origen:</strong> ${escapeHtml(ride.departure_location || '')}</p>
                <p><strong>Destino:</strong> ${escapeHtml(ride.arrival_location || '')}</p>
                <p><strong>Fecha:</strong> ${escapeHtml(ride.ride_date || '')} <strong>Hora:</strong> ${escapeHtml(ride.ride_time || '')}</p>
                <p><strong>Precio:</strong> ₡${escapeHtml(String(ride.cost_per_seat || ''))} | <strong>Asientos:</strong> ${escapeHtml(String(ride.total_seats || ''))}</p>
                <p><strong>Asientos ocupados:</strong> ${escapeHtml(String(ride.occupied_seats || 0))}</p>
            `;
            const modalEl = document.getElementById('showRideModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        })
        .catch(err => {
            console.error('openShowRideModal', err);
            showToast('No se pudo cargar el detalle del viaje', 'error');
        });
}
