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
                    <h5 class="mt-2"><?php echo htmlspecialchars(Session::getCurrentUser()['first_name'] ?? 'Conductor') ?>!</h5>
                    <small class="text-muted">Conductor</small>
                </div>

                <nav class="nav nav-pills flex-column">
                    <a class="nav-link active" href="#rides" data-section="rides">Mis Viajes</a>
                    <a class="nav-link" href="#vehicles" data-section="vehicles">Mis Vehículos</a>
                    <a class="nav-link" href="#reservations" data-section="reservations">Reservas</a>
                    <a class="nav-link" href="#earnings" data-section="earnings">Ganancias</a>
                    <a class="nav-link" href="#profile" data-section="profile">Mi Perfil</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">
            <div class="p-4">
                <!-- Rides Section (minimal placeholder) -->
                <div id="rides-section" class="content-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-car-front me-2"></i>Mis Viajes</h2>
                        <button class="btn btn-primary" onclick="showCreateRide()"><i class="bi bi-plus-circle me-2"></i>Crear Viaje</button>
                    </div>
                    <div id="upcomingRidesContainer" class="mb-3 text-muted">Cargando viajes...</div>
                </div>

                <!-- (Removed separate Create Ride page) Create flow handled via modal in Mis Viajes -->

                <!-- Vehicles Section -->
                <div id="vehicles-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-car-front-fill me-2"></i>Mis Vehículos</h2>
                        <button class="btn btn-primary" onclick="showAddVehicle()"><i class="bi bi-plus-circle me-2"></i>Agregar Vehículo</button>
                    </div>

                    <!-- Server-rendered vehicles (fallback) -->
                    <div id="vehiclesContainer">
                        <?php if (!empty($vehicles) && is_array($vehicles)): ?>
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Placa</th>
                                                    <th>Marca / Modelo</th>
                                                    <th>Año</th>
                                                    <th>Color</th>
                                                    <th>Asientos</th>
                                                    <th class="text-end">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($vehicles as $v): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($v['plate_number'] ?? '') ?></td>
                                                        <td><?= htmlspecialchars(trim(($v['brand'] ?? '') . ' ' . ($v['model'] ?? ''))) ?></td>
                                                        <td><?= htmlspecialchars($v['year'] ?? '') ?></td>
                                                        <td><?= htmlspecialchars($v['color'] ?? '') ?></td>
                                                        <td><?= htmlspecialchars($v['seats_capacity'] ?? '') ?></td>
                                                        <td class="text-end">
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-secondary" onclick="editVehicle(<?= (int)($v['id'] ?? 0) ?>)">Editar</button>
                                                                <button class="btn btn-outline-danger ms-1" onclick="deleteVehicle(<?= (int)($v['id'] ?? 0) ?>)">Eliminar</button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="bi bi-car-front display-4"></i>
                                <p class="mt-2">No tienes vehículos registrados</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reservations / Earnings / Profile placeholders -->
                <div id="reservations-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2><i class="bi bi-people me-2"></i>Reservas</h2>
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadReservations()">Actualizar</button>
                    </div>
                    <div id="reservationsContainer">
                        <div class="text-center text-muted py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-3">Cargando reservas...</p>
                        </div>
                    </div>
                </div>
                <div id="earnings-section" class="content-section d-none"><div class="card"><div class="card-body">Ganancias</div></div></div>
                <div id="profile-section" class="content-section d-none"><div class="card"><div class="card-body">Perfil</div></div></div>

            </div>
        </div>
    </div>
</div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Agregar Vehículo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addVehicleForm" class="needs-validation" novalidate onsubmit="submitVehicleAjax(event)">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Marca</label><input name="make" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Modelo</label><input name="model" class="form-control" required></div>
                    <div class="row"><div class="col-6 mb-3"><label>Año</label><input type="number" name="year" class="form-control"></div>
                    <div class="col-6 mb-3"><label>Placa</label><input name="license_plate" class="form-control" required></div></div>
                    <div class="row"><div class="col-6 mb-3"><label>Color</label><input name="color" class="form-control"></div>
                    <div class="col-6 mb-3"><label>Asientos</label><input type="number" name="seats" class="form-control" min="1" max="9" required></div></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Agregar</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Toast container -->
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index:1080"></div>

<!-- Create Ride Modal -->
<div class="modal fade" id="createRideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Viaje</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?php
                // Prefill create form from previous submission if available
                $old_input = Session::get('old_input', []);
                // Remove after reading so it doesn't persist
                Session::remove('old_input');
            ?>
            <form id="createRideForm" class="needs-validation" novalidate>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vehículo</label>
                            <select id="vehicle_id" name="vehicle_id" class="form-select" required>
                                <option value="">Cargando vehículos...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del viaje</label>
                            <input name="ride_name" id="ride_name" class="form-control" placeholder="Ej: San José → Alajuela" value="<?= htmlspecialchars($old_input['ride_name'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Origen</label>
                            <div class="input-group">
                                <input type="text" id="departureLocation" name="departure_location" class="form-control" required value="<?= htmlspecialchars($old_input['departure_location'] ?? '') ?>">
                                <button type="button" id="pickDepartureBtn" class="btn btn-outline-secondary">Seleccionar</button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Destino</label>
                            <div class="input-group">
                                <input type="text" id="arrivalLocation" name="arrival_location" class="form-control" required value="<?= htmlspecialchars($old_input['arrival_location'] ?? '') ?>">
                                <button type="button" id="pickArrivalBtn" class="btn btn-outline-secondary">Seleccionar</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="ride_date" id="ride_date" class="form-control" required value="<?= htmlspecialchars($old_input['ride_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hora</label>
                            <input type="time" name="ride_time" id="ride_time" class="form-control" required value="<?= htmlspecialchars($old_input['ride_time'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Precio por asiento (₡)</label>
                            <input type="number" name="cost_per_seat" id="cost_per_seat" class="form-control" min="0" step="0.01" required value="<?= htmlspecialchars($old_input['cost_per_seat'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Asientos totales</label>
                            <input type="number" name="total_seats" id="total_seats" class="form-control" min="1" required value="<?= htmlspecialchars($old_input['total_seats'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Hidden coordinate inputs (used by ride-map.js) -->
                    <input type="hidden" id="departureLat" name="departure_lat" value="<?= htmlspecialchars($old_input['departure_lat'] ?? '') ?>" />
                    <input type="hidden" id="departureLng" name="departure_lng" value="<?= htmlspecialchars($old_input['departure_lng'] ?? '') ?>" />
                    <input type="hidden" id="arrivalLat" name="arrival_lat" value="<?= htmlspecialchars($old_input['arrival_lat'] ?? '') ?>" />
                    <input type="hidden" id="arrivalLng" name="arrival_lng" value="<?= htmlspecialchars($old_input['arrival_lng'] ?? '') ?>" />

                    <div class="mt-2 text-muted"><small>Utiliza los botones "Seleccionar" para escoger el punto en el mapa.</small></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Viaje</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ride Map Modal (used by ride-map.js) -->
<div class="modal fade" id="rideMapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Seleccionar ubicación</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0">
                <div id="rideMap" style="height:500px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" id="rideMapAccept" class="btn btn-primary">Aceptar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="<?= BASE_URL ?>/js/driver-dashboard.js"></script>
<!-- Leaflet for map picker (optional) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= BASE_URL ?>/js/ride-map.js"></script>

<?php if (isset($_GET['open_create']) && $_GET['open_create'] === '1'): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    // small timeout to ensure driver-dashboard.js has initialized
    setTimeout(function(){ if (typeof showCreateRide === 'function') showCreateRide(); }, 200);
});
</script>
<?php endif; ?>

<!-- Edit Vehicle Modal -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar Vehículo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="editVehicleForm" class="needs-validation" novalidate>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Marca</label><input name="brand" id="edit_vehicle_brand" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Modelo</label><input name="model" id="edit_vehicle_model" class="form-control" required></div>
                    <div class="row"><div class="col-6 mb-3"><label>Año</label><input type="number" name="year" id="edit_vehicle_year" class="form-control"></div>
                    <div class="col-6 mb-3"><label>Placa</label><input name="plate_number" id="edit_vehicle_plate" class="form-control" required></div></div>
                    <div class="row"><div class="col-6 mb-3"><label>Color</label><input name="color" id="edit_vehicle_color" class="form-control"></div>
                    <div class="col-6 mb-3"><label>Asientos</label><input type="number" name="seats_capacity" id="edit_vehicle_seats" class="form-control" min="1" max="9" required></div></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Ride Modal -->
<div class="modal fade" id="editRideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar Viaje</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="editRideForm" class="needs-validation" novalidate>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vehículo</label>
                            <select id="edit_vehicle_id" name="vehicle_id" class="form-select" required>
                                <option value="">Cargando vehículos...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del viaje</label>
                            <input name="ride_name" id="edit_ride_name" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Origen</label>
                            <div class="input-group">
                                <input type="text" id="edit_departureLocation" name="departure_location" class="form-control" required>
                                <button type="button" id="edit_pickDepartureBtn" class="btn btn-outline-secondary">Seleccionar</button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Destino</label>
                            <div class="input-group">
                                <input type="text" id="edit_arrivalLocation" name="arrival_location" class="form-control" required>
                                <button type="button" id="edit_pickArrivalBtn" class="btn btn-outline-secondary">Seleccionar</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="ride_date" id="edit_ride_date" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hora</label>
                            <input type="time" name="ride_time" id="edit_ride_time" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Precio por asiento (₡)</label>
                            <input type="number" name="cost_per_seat" id="edit_cost_per_seat" class="form-control" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Asientos totales</label>
                            <input type="number" name="total_seats" id="edit_total_seats" class="form-control" min="1" required>
                        </div>
                    </div>
                    <input type="hidden" id="edit_departureLat" name="departure_lat" />
                    <input type="hidden" id="edit_departureLng" name="departure_lng" />
                    <input type="hidden" id="edit_arrivalLat" name="arrival_lat" />
                    <input type="hidden" id="edit_arrivalLng" name="arrival_lng" />
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Show Ride Modal -->
<div class="modal fade" id="showRideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Detalle del Viaje</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="showRideBody">Cargando...</div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
