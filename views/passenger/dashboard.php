<?php
$pageTitle = "Panel de Pasajero - Carpooling UTN";
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4 bg-light border-end vh-100 position-sticky top-0 pt-5">
            <div class="p-3">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle display-4 text-primary"></i>
                    <h5 class="mt-2">¡Hola <?= htmlspecialchars(Session::getCurrentUser()['first_name']) ?>!</h5>
                    <small class="text-muted">Pasajero</small>
                </div>

                <nav class="nav nav-pills flex-column">
                    <a class="nav-link active" href="#search" data-section="search">
                        <i class="bi bi-search me-2"></i>Buscar Viajes
                    </a>
                    <a class="nav-link" href="#reservations" data-section="reservations">
                        <i class="bi bi-calendar-check me-2"></i>Mis Reservas
                    </a>
                    <a class="nav-link" href="#history" data-section="history">
                        <i class="bi bi-clock-history me-2"></i>Historial
                    </a>
                    <a class="nav-link" href="#profile" data-section="profile">
                        <i class="bi bi-person-gear me-2"></i>Mi Perfil
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">
            <div class="p-4">
                <!-- Search Section -->
                <div id="search-section" class="content-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-search me-2"></i>Buscar Viajes</h2>
                        <button class="btn btn-outline-primary" onclick="loadAvailableRides()">
                            <i class="bi bi-arrow-clockwise"></i> Actualizar
                        </button>
                    </div>

                    <!-- Search Filters -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros de Búsqueda</h5>
                        </div>
                        <div class="card-body">
                            <form id="searchForm" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Origen</label>
                                    <input type="text" class="form-control" id="origin" placeholder="Ej: San José">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Destino</label>
                                    <input type="text" class="form-control" id="destination" placeholder="Ej: UTN Campus Central">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" class="form-control" id="date" min="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" onclick="searchRides()">
                                        <i class="bi bi-search me-2"></i>Buscar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                        <i class="bi bi-x-lg me-2"></i>Limpiar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Available Rides -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-car-front me-2"></i>Viajes Disponibles</h5>
                        </div>
                        <div class="card-body">
                            <div id="ridesContainer">
                                <div class="text-center text-muted">
                                    <i class="bi bi-search display-4"></i>
                                    <p class="mt-2">Usa los filtros para buscar viajes disponibles</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reservations Section -->
                <div id="reservations-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-calendar-check me-2"></i>Mis Reservas</h2>
                    </div>

                    <div class="row" id="reservationsContainer">
                        <div class="col-12 text-center text-muted">
                            <i class="bi bi-calendar-x display-4"></i>
                            <p class="mt-2">No tienes reservas activas</p>
                        </div>
                    </div>
                </div>

                <!-- History Section -->
                <div id="history-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-clock-history me-2"></i>Historial de Viajes</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div id="historyContainer">
                                <div class="text-center text-muted">
                                    <i class="bi bi-clock-history display-4"></i>
                                    <p class="mt-2">No tienes viajes en tu historial</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Section -->
                <div id="profile-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-person-gear me-2"></i>Mi Perfil</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Información Personal</h5>
                                </div>
                                <div class="card-body">
                                    <form id="profileForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars(Session::getCurrentUser()['first_name']) ?>" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Apellido</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars(Session::getCurrentUser()['last_name']) ?>" readonly>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" value="<?= htmlspecialchars(Session::getCurrentUser()['email']) ?>" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Teléfono</label>
                                                <input type="tel" class="form-control" value="<?= htmlspecialchars(Session::getCurrentUser()['phone'] ?? '') ?>" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Carné</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars(Session::getCurrentUser()['student_id'] ?? 'No especificado') ?>" readonly>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary">
                                            <i class="bi bi-pencil me-2"></i>Editar Perfil
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Estadísticas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <h3 class="text-primary" id="totalReservations">0</h3>
                                            <small class="text-muted">Reservas Totales</small>
                                        </div>
                                        <div class="mb-3">
                                            <h3 class="text-success" id="completedTrips">0</h3>
                                            <small class="text-muted">Viajes Completados</small>
                                        </div>
                                        <div class="mb-3">
                                            <h3 class="text-info" id="savedMoney">₡0</h3>
                                            <small class="text-muted">Dinero Ahorrado</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
    loadAvailableRides();
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
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>