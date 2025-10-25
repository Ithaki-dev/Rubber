<?php
$pageTitle = "Panel de Conductor - Carpooling UTN";
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4 bg-light border-end vh-100 position-sticky top-0 pt-5">
            <div class="p-3">
                <div class="text-center mb-4">
                    <i class="bi bi-car-front-fill display-4 text-primary"></i>
                    <h5 class="mt-2">¡Hola <?= htmlspecialchars(Session::getCurrentUser()['first_name']) ?>!</h5>
                    <small class="text-muted">Conductor</small>
                </div>

                <nav class="nav nav-pills flex-column">
                    <a class="nav-link active" href="#rides" data-section="rides">
                        <i class="bi bi-car-front me-2"></i>Mis Viajes
                    </a>
                    <a class="nav-link" href="#create-ride" data-section="create-ride">
                        <i class="bi bi-plus-circle me-2"></i>Crear Viaje
                    </a>
                    <a class="nav-link" href="#vehicles" data-section="vehicles">
                        <i class="bi bi-car-front-fill me-2"></i>Mis Vehículos
                    </a>
                    <a class="nav-link" href="#reservations" data-section="reservations">
                        <i class="bi bi-people me-2"></i>Reservas
                    </a>
                    <a class="nav-link" href="#earnings" data-section="earnings">
                        <i class="bi bi-cash-coin me-2"></i>Ganancias
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
                <!-- My Rides Section -->
                <div id="rides-section" class="content-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-car-front me-2"></i>Mis Viajes</h2>
                        <button class="btn btn-primary" onclick="showCreateRide()">
                            <i class="bi bi-plus-circle me-2"></i>Crear Viaje
                        </button>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-car-front display-6 me-3"></i>
                                        <div>
                                            <h4 id="totalRides">0</h4>
                                            <small>Viajes Totales</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-people display-6 me-3"></i>
                                        <div>
                                            <h4 id="totalPassengers">0</h4>
                                            <small>Pasajeros Transportados</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-cash-coin display-6 me-3"></i>
                                        <div>
                                            <h4 id="totalEarnings">₡0</h4>
                                            <small>Ganancias Totales</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-star display-6 me-3"></i>
                                        <div>
                                            <h4 id="averageRating">0.0</h4>
                                            <small>Calificación Promedio</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rides List -->
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="ridesTab">
                                <li class="nav-item">
                                    <a class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" href="#upcoming">
                                        Próximos Viajes
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="completed-tab" data-bs-toggle="tab" href="#completed">
                                        Completados
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="upcoming">
                                    <div id="upcomingRidesContainer">
                                        <div class="text-center text-muted">
                                            <i class="bi bi-calendar-x display-4"></i>
                                            <p class="mt-2">No tienes viajes programados</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="completed">
                                    <div id="completedRidesContainer">
                                        <div class="text-center text-muted">
                                            <i class="bi bi-check-circle display-4"></i>
                                            <p class="mt-2">No tienes viajes completados</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Create Ride Section -->
                <div id="create-ride-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-plus-circle me-2"></i>Crear Viaje</h2>
                        <button class="btn btn-outline-secondary" onclick="showRides()">
                            <i class="bi bi-arrow-left me-2"></i>Volver
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form id="createRideForm" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Vehículo</label>
                                        <select class="form-select" id="vehicle_id" name="vehicle_id" required>
                                            <option value="">Selecciona un vehículo</option>
                                        </select>
                                        <div class="invalid-feedback">Selecciona un vehículo.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Asientos Disponibles</label>
                                        <input type="number" class="form-control" id="available_seats" name="available_seats" min="1" max="8" required>
                                        <div class="invalid-feedback">Ingresa el número de asientos.</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Origen</label>
                                        <input type="text" class="form-control" id="origin" name="origin" required>
                                        <div class="invalid-feedback">Ingresa el punto de origen.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Destino</label>
                                        <input type="text" class="form-control" id="destination" name="destination" required>
                                        <div class="invalid-feedback">Ingresa el destino.</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Fecha</label>
                                        <input type="date" class="form-control" id="date" name="date" min="<?= date('Y-m-d') ?>" required>
                                        <div class="invalid-feedback">Selecciona una fecha.</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Hora de Salida</label>
                                        <input type="time" class="form-control" id="departure_time" name="departure_time" required>
                                        <div class="invalid-feedback">Selecciona la hora.</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Precio por Asiento (₡)</label>
                                        <input type="number" class="form-control" id="price_per_seat" name="price_per_seat" min="500" step="100" required>
                                        <div class="invalid-feedback">Ingresa un precio válido.</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Descripción/Comentarios</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Información adicional sobre el viaje (opcional)"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Punto de Encuentro</label>
                                    <input type="text" class="form-control" id="meeting_point" name="meeting_point" placeholder="Ubicación específica para recoger pasajeros" required>
                                    <div class="invalid-feedback">Especifica el punto de encuentro.</div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Crear Viaje
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Vehicles Section -->
                <div id="vehicles-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-car-front-fill me-2"></i>Mis Vehículos</h2>
                        <button class="btn btn-primary" onclick="showAddVehicle()">
                            <i class="bi bi-plus-circle me-2"></i>Agregar Vehículo
                        </button>
                    </div>

                    <div id="vehiclesContainer">
                        <div class="text-center text-muted">
                            <i class="bi bi-car-front display-4"></i>
                            <p class="mt-2">No tienes vehículos registrados</p>
                        </div>
                    </div>
                </div>

                <!-- Reservations Section -->
                <div id="reservations-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-people me-2"></i>Reservas de Pasajeros</h2>
                    </div>

                    <div id="reservationsContainer">
                        <div class="text-center text-muted">
                            <i class="bi bi-people display-4"></i>
                            <p class="mt-2">No tienes reservas pendientes</p>
                        </div>
                    </div>
                </div>

                <!-- Earnings Section -->
                <div id="earnings-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-cash-coin me-2"></i>Ganancias</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Resumen de Ganancias</h5>
                                </div>
                                <div class="card-body">
                                    <div id="earningsChart">
                                        <div class="text-center text-muted">
                                            <i class="bi bi-bar-chart display-4"></i>
                                            <p class="mt-2">Gráfico de ganancias</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Este Mes</h5>
                                </div>
                                <div class="card-body text-center">
                                    <h3 class="text-success" id="monthlyEarnings">₡0</h3>
                                    <p class="text-muted">Ganancia del mes</p>
                                    
                                    <hr>
                                    
                                    <div class="mb-2">
                                        <strong id="monthlyTrips">0</strong>
                                        <small class="text-muted d-block">Viajes realizados</small>
                                    </div>
                                    
                                    <div>
                                        <strong id="monthlyPassengers">0</strong>
                                        <small class="text-muted d-block">Pasajeros transportados</small>
                                    </div>
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
                                    <h5 class="mb-0">Calificaciones</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <h3 class="text-warning" id="driverRating">0.0</h3>
                                        <div class="text-warning" id="ratingStars">
                                            <i class="bi bi-star"></i>
                                            <i class="bi bi-star"></i>
                                            <i class="bi bi-star"></i>
                                            <i class="bi bi-star"></i>
                                            <i class="bi bi-star"></i>
                                        </div>
                                        <small class="text-muted d-block">Calificación promedio</small>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div>
                                        <strong id="totalReviews">0</strong>
                                        <small class="text-muted d-block">Reseñas totales</small>
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
    createRideForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (this.checkValidity()) {
            createRide();
        }
        this.classList.add('was-validated');
    });
});

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
    fetch(`${BASE_URL}/api/vehicles`)
        .then(response => response.json())
        .then(data => {
            // Update vehicle select
            const select = document.getElementById('vehicle_id');
            select.innerHTML = '<option value="">Selecciona un vehículo</option>';
            
            if (data.success && data.vehicles.length > 0) {
                data.vehicles.forEach(vehicle => {
                    const option = document.createElement('option');
                    option.value = vehicle.id;
                    option.textContent = `${vehicle.make} ${vehicle.model} (${vehicle.license_plate})`;
                    select.appendChild(option);
                });
            }
        });
}

function createRideCard(ride, isCompleted = false) {
    return `
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6><i class="bi bi-geo-alt me-1"></i>De: ${ride.origin}</h6>
                        <h6><i class="bi bi-geo-alt-fill me-1"></i>A: ${ride.destination}</h6>
                        <p class="mb-1"><i class="bi bi-calendar me-1"></i>${ride.date} <i class="bi bi-clock me-1"></i>${ride.departure_time}</p>
                        <p class="mb-1"><i class="bi bi-people me-1"></i>${ride.occupied_seats}/${ride.available_seats} asientos</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <h6 class="text-primary">₡${ride.price_per_seat}/asiento</h6>
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
    const formData = new FormData(document.getElementById('createRideForm'));
    
    fetch(`${BASE_URL}/api/rides`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('¡Viaje creado exitosamente!');
            document.getElementById('createRideForm').reset();
            showRides();
            loadRides();
            loadStats();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function showCreateRide() {
    document.querySelector('[data-section="create-ride"]').click();
}

function showRides() {
    document.querySelector('[data-section="rides"]').click();
}

function showAddVehicle() {
    // TODO: Show add vehicle modal
    alert('Función en desarrollo');
}

function loadReservations() {
    // TODO: Load reservations
}

function loadEarnings() {
    // TODO: Load earnings data
}

function viewRideDetails(rideId) {
    // TODO: Show ride details modal
}

function cancelRide(rideId) {
    if (confirm('¿Estás seguro de que deseas cancelar este viaje?')) {
        fetch(`${BASE_URL}/api/rides/${rideId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Viaje cancelado');
                loadRides();
                loadStats();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>