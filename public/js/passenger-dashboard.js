document.addEventListener('DOMContentLoaded', function() {
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
        });
    });

    // Load initial data
    // If the map is present, request a map refresh; otherwise fall back to loading list view
    if (document.getElementById('passengerMap')) {
        // Ask passenger-map.js to refresh
        window.dispatchEvent(new Event('mapRefresh'));
    } else {
        loadAvailableRides();
    }
    loadReservations();
    loadHistory();
    loadStats();
});

// Small helper to escape HTML used when rendering reservation items
function escapeHtml(s) {
    if (!s) return '';
    return String(s).replace(/[&<>"']/g, function(c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c];
    });
}

function loadAvailableRides() {
    const container = document.getElementById('ridesContainer');
    
    // Show loading
    container.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    
    fetch(`${BASE_URL}/api/rides/available`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.rides.length > 0) {
                container.innerHTML = data.rides.map(ride => createRideCard(ride)).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="bi bi-car-front display-4"></i>
                        <p class="mt-2">No hay viajes disponibles en este momento</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error al cargar los viajes
                </div>
            `;
        });
}

function createRideCard(ride) {
    return `
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-sm-6">
                                <h6><i class="bi bi-geo-alt me-1"></i>Desde: ${ride.origin}</h6>
                                <h6><i class="bi bi-geo-alt-fill me-1"></i>Hasta: ${ride.destination}</h6>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1"><i class="bi bi-calendar me-1"></i>${ride.date}</p>
                                <p class="mb-1"><i class="bi bi-clock me-1"></i>${ride.time}</p>
                                <p class="mb-1"><i class="bi bi-people me-1"></i>${ride.available_seats} asientos</p>
                            </div>
                        </div>
                        <small class="text-muted">Conductor: ${ride.driver_name}</small>
                    </div>
                    <div class="col-md-4 text-end">
                        <h5 class="text-primary mb-2">₡${ride.price_per_seat}</h5>
                        <button class="btn btn-primary" onclick="reserveRide(${ride.id})">
                            <i class="bi bi-plus-circle me-1"></i>Reservar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function reserveRide(rideId) {
    if (confirm('¿Confirmas que deseas reservar este viaje?')) {
        fetch(`${BASE_URL}/api/reservations`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                ride_id: rideId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('¡Reserva realizada exitosamente!');
                loadAvailableRides();
                loadReservations();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function searchRides() {
    const origin = document.getElementById('origin').value;
    const destination = document.getElementById('destination').value;
    const date = document.getElementById('date').value;
    
    const params = new URLSearchParams();
    if (origin) params.append('origin', origin);
    if (destination) params.append('destination', destination);
    if (date) params.append('date', date);
    
    const container = document.getElementById('ridesContainer');
    container.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    
    fetch(`${BASE_URL}/api/rides/search?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.rides.length > 0) {
                container.innerHTML = data.rides.map(ride => createRideCard(ride)).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="bi bi-search display-4"></i>
                        <p class="mt-2">No se encontraron viajes con esos criterios</p>
                    </div>
                `;
            }
        });
}

function clearFilters() {
    document.getElementById('searchForm').reset();
    loadAvailableRides();
}

function loadReservations() {
    const container = document.getElementById('reservationsContainer');

    // Show loader
    container.innerHTML = '<div class="col-12 text-center"><div class="spinner-border" role="status"></div></div>';

    fetch(`${BASE_URL}/passenger/reservations`, {
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.text())
    .then(text => {
        // Try to parse JSON but log raw response if it fails
        let data;
        try {
            data = JSON.parse(text);
        } catch (err) {
            console.error('loadReservations: response is not valid JSON', err);
            console.debug('loadReservations raw response:', text);
            container.innerHTML = `<div class="col-12 text-center text-muted"><p>Respuesta inesperada del servidor al cargar reservas</p></div>`;
            return;
        }

        if (!data.success) {
            console.debug('loadReservations: server returned success=false', data);
            container.innerHTML = `<div class="col-12 text-center text-muted"><p>${data.message || 'No se pudieron cargar las reservas'}</p></div>`;
            return;
        }

        console.debug('loadReservations: parsed response', data);
        const reservations = data.reservations || [];
        console.debug('loadReservations: reservations count =', reservations.length);
        if (reservations.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center text-muted">
                    <i class="bi bi-calendar-x display-4"></i>
                    <p class="mt-2">No tienes reservas activas</p>
                </div>
            `;
            return;
        }

        container.innerHTML = reservations.map(res => {
            const rideName = res.ride_name || (res.origin + ' → ' + res.destination);
            const statusBadge = res.status === 'pending' ? 'badge bg-warning' : (res.status === 'accepted' ? 'badge bg-success' : 'badge bg-secondary');
            const cancelBtn = (res.status === 'pending' || res.status === 'accepted') ? `<button class="btn btn-sm btn-outline-danger ms-2" onclick="cancelReservationAjax(${res.id})">Cancelar</button>` : '';

            return `
                <div class="col-12">
                    <div class="card mb-2">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${escapeHtml(rideName)}</h6>
                                <small class="text-muted">${escapeHtml(res.ride_date)} ${escapeHtml(res.ride_time)} • ${res.seats_requested} asiento(s)</small>
                            </div>
                            <div class="text-end">
                                <span class="${statusBadge}">${escapeHtml(res.status)}</span>
                                ${cancelBtn}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    })
    .catch(err => {
        console.error('Error loading reservations', err);
        container.innerHTML = `<div class="col-12 text-center text-muted"><p>Error cargando reservas</p></div>`;
    });
}

// Cancel reservation via AJAX
function cancelReservationAjax(reservationId) {
    if (!confirm('¿Deseas cancelar esta reserva?')) return;

    fetch(`${BASE_URL}/passenger/reservations/${reservationId}/cancel`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                alert(data.message || 'Reserva cancelada');
                loadReservations();
                // Ask map to refresh (in case seats freed)
                window.dispatchEvent(new Event('mapRefresh'));
            } else {
                alert('Error: ' + (data.message || 'No se pudo cancelar la reserva'));
            }
        } catch (err) {
            console.error('cancelReservationAjax: response not JSON', err);
            console.debug('cancelReservationAjax raw response:', text);
            alert('Error al cancelar la reserva (respuesta inválida del servidor)');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error al cancelar la reserva');
    });
}

function loadHistory() {
    // TODO: Implement history loading
}

function loadStats() {
    // TODO: Implement stats loading
}