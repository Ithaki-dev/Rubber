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
                    <?php
                    $user = Session::getCurrentUser();
                    // Try common keys used in the app for the uploaded photo                   
                    $profileImg = $user['photo_path'] ?? $user['photo'] ?? $user['pr'] ?? '';
                    ?>
                    <?php if (!empty($profileImg)): ?>
                        <?php
                            // Build a full URL for the profile image. If $profileImg is already absolute (http...), use it as-is.
                            if (preg_match('#^https?://#i', $profileImg)) {
                                $imgUrl = $profileImg;
                            } else {
                                $imgUrl = rtrim(BASE_URL, '/') . '/' . ltrim($profileImg, '/');
                            }
                        ?>
                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Avatar de <?= htmlspecialchars($user['first_name'] ?? '') ?>" class="user-profile rounded-circle" width="96" height="96">
                    <?php else: ?>
                        <i class="bi bi-person-circle display-4 text-primary"></i>
                    <?php endif; ?>
                    <h5 class="mt-2">¡Hola <?= htmlspecialchars($user['first_name'] ?? '') ?>!</h5>
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

                    <!-- Map-based Available Rides -->
                    <div class="card mb-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Mapa de Viajes</h5>
                            <div>
                                <button id="centerMapBtn" class="btn btn-sm btn-outline-secondary me-2">Centrar Quesada</button>
                                <button id="refreshMapBtn" class="btn btn-sm btn-outline-primary">Actualizar</button>
                            </div>
                        </div>
                        <div class="card-body p-0" style="height:70vh;">
                            <div id="passengerMap" style="width:100%; height:100%;"></div>
                        </div>
                    </div>

                    <!-- Reservation modal (accessibility-ready) -->
                    <div class="modal fade" id="reserveModal" tabindex="-1" aria-labelledby="reserveModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="reserveModalLabel">Confirmar Reserva</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="reserveDetails"></div>

                                    <div class="mb-3">
                                        <label for="reserveSeats" class="form-label">Número de asientos</label>
                                        <input type="number" disabled="true" id="reserveSeats" class="form-control" min="1" value="1" aria-label="Número de asientos">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" id="confirmReserveBtn" class="btn btn-primary" aria-label="Confirmar reserva">Reservar</button>
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

<script src="<?= BASE_URL ?>/js/passenger-dashboard.js"></script>

<!-- Leaflet CSS/JS and passenger map script -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= BASE_URL ?>/js/passenger-map.js"></script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>