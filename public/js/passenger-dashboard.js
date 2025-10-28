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
    // TODO: Implement reservations loading
}

function loadHistory() {
    // TODO: Implement history loading
}

function loadStats() {
    // TODO: Implement stats loading
}