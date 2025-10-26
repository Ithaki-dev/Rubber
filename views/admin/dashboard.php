<?php
$pageTitle = "Panel de Administrador - Carpooling UTN";
ob_start();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar de Administración -->
        <div class="col-lg-3 col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="text-center">
                        <i class="bi bi-shield-check display-6"></i>
                        <h5 class="mt-2 mb-0">Panel de Admin</h5>
                        <small><?= htmlspecialchars(Session::getCurrentUser()['first_name'] ?? 'Administrador') ?></small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <nav class="nav nav-pills flex-column">
                        <a class="nav-link active" href="#" data-section="dashboard">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="#" data-section="users">
                            <i class="bi bi-people me-2"></i>Usuarios
                        </a>
                        <a class="nav-link" href="#" data-section="rides">
                            <i class="bi bi-car-front me-2"></i>Viajes
                        </a>
                        <a class="nav-link" href="#" data-section="vehicles">
                            <i class="bi bi-truck me-2"></i>Vehículos
                        </a>
                        <a class="nav-link" href="#" data-section="reports">
                            <i class="bi bi-graph-up me-2"></i>Reportes
                        </a>
                        <a class="nav-link" href="#" data-section="settings">
                            <i class="bi bi-gear me-2"></i>Configuración
                        </a>
                    </nav>
                </div>
                <div class="card-footer text-center">
                    <a href="<?= BASE_URL ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-house me-1"></i>Ver Sitio
                    </a>
                    <a href="<?= BASE_URL ?>/auth/logout" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>Salir
                    </a>

                    <!-- Modal: Crear/Editar Viaje -->
                    <div class="modal fade" id="rideModal" tabindex="-1" aria-labelledby="rideModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="rideModalLabel">Nuevo Viaje</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="rideForm">
                                    <div class="modal-body">
                                        <input type="hidden" id="rideId" name="rideId">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="rideName" class="form-label">Nombre del Viaje *</label>
                                                <input type="text" class="form-control" id="rideName" name="ride_name" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="rideDate" class="form-label">Fecha *</label>
                                                <input type="date" class="form-control" id="rideDate" name="ride_date" required>
                                            </div>
                                        </div>
                                        
                                        <!-- Driver & Vehicle selects -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="rideDriver" class="form-label">Conductor *</label>
                                                <select id="rideDriver" name="driver_id" class="form-select" required>
                                                    <option value="">Cargando conductores...</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="rideVehicle" class="form-label">Vehículo *</label>
                                                <select id="rideVehicle" name="vehicle_id" class="form-select" required disabled>
                                                    <option value="">Seleccione un conductor primero</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="departureLocation" class="form-label">Origen *</label>
                                                <input type="text" class="form-control" id="departureLocation" name="departure_location" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="arrivalLocation" class="form-label">Destino *</label>
                                                <input type="text" class="form-control" id="arrivalLocation" name="arrival_location" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="rideTime" class="form-label">Hora *</label>
                                                <input type="time" class="form-control" id="rideTime" name="ride_time" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="costPerSeat" class="form-label">Costo por Asiento</label>
                                                <input type="number" step="0.01" class="form-control" id="costPerSeat" name="cost_per_seat">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="totalSeats" class="form-label">Asientos Totales</label>
                                                <input type="number" class="form-control" id="totalSeats" name="total_seats">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">
                                            <span id="rideSaveText">Guardar</span>
                                            <span id="rideSaveSpinner" class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal: Confirmar Eliminación Viaje -->
                    <div class="modal fade" id="deleteRideModal" tabindex="-1" aria-labelledby="deleteRideModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteRideModalLabel">Confirmar Eliminación</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <i class="bi bi-exclamation-triangle display-4 text-warning"></i>
                                    <h5 class="mt-3">¿Estás seguro que deseas eliminar este viaje?</h5>
                                    <p class="text-muted">Esta acción es irreversible.</p>
                                    <div class="mt-2"><strong id="deleteRideInfo"></strong></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-danger" onclick="confirmDeleteRide()">Eliminar Viaje</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Contenido Principal -->
        <div class="col-lg-9 col-md-8">
            
            <!-- Sección Dashboard -->
            <div id="dashboard-section" class="content-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
                    <button class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                    </button>
                </div>
                
                <!-- Estadísticas Principales -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-people display-4 me-3"></i>
                                    <div>
                                        <h3 class="mb-0"><?= $stats['total_users'] ?? 0 ?></h3>
                                        <small>Usuarios Totales</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-car-front display-4 me-3"></i>
                                    <div>
                                        <h3 class="mb-0"><?= $stats['total_rides'] ?? 0 ?></h3>
                                        <small>Viajes Totales</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-check display-4 me-3"></i>
                                    <div>
                                        <h3 class="mb-0"><?= $stats['pending_reservations'] ?? 0 ?></h3>
                                        <small>Reservas Pendientes</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-truck display-4 me-3"></i>
                                    <div>
                                        <h3 class="mb-0"><?= $stats['active_vehicles'] ?? 0 ?></h3>
                                        <small>Vehículos Activos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actividad Reciente -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Usuarios Recientes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Tipo</th>
                                                <th>Estado</th>
                                                <th>Registro</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($recent_users)): ?>
                                                <?php foreach ($recent_users as $user): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($user['user_id'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></td>
                                                    <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                                    <td>
                                                        <span class="badge <?= ($user['user_type'] ?? '') === 'driver' ? 'bg-primary' : 'bg-info' ?>">
                                                            <?= ucfirst($user['user_type'] ?? 'usuario') ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= ($user['status'] ?? '') === 'active' ? 'bg-success' : 'bg-warning' ?>">
                                                            <?= ucfirst($user['status'] ?? 'pendiente') ?>
                                                        </span>
                                                    </td>
                                                    <td><?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'N/A' ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="viewUser(<?= $user['user_id'] ?? 0 ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-warning" onclick="editUser(<?= $user['user_id'] ?? 0 ?>)">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">No hay usuarios registrados</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sección Usuarios -->
            <div id="users-section" class="content-section d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-people me-2"></i>Gestión de Usuarios</h2>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" onclick="exportUsers()">
                            <i class="bi bi-download me-1"></i>Exportar
                        </button>
                        <button class="btn btn-success" onclick="showCreateUserModal()">
                            <i class="bi bi-plus me-1"></i>Nuevo Usuario
                        </button>
                    </div>
                </div>
                
                <!-- Filtros de Usuarios -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Tipo de Usuario</label>
                                <select class="form-select" id="userTypeFilter">
                                    <option value="">Todos los tipos</option>
                                    <option value="passenger">Pasajeros</option>
                                    <option value="driver">Conductores</option>
                                    <option value="admin">Administradores</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="userStatusFilter">
                                    <option value="">Todos los estados</option>
                                    <option value="active">Activos</option>
                                    <option value="pending">Pendientes</option>
                                    <option value="inactive">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="userSearchInput" placeholder="Buscar por nombre, email...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-primary w-100" onclick="filterUsers()">
                                    <i class="bi bi-search me-1"></i>Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabla de Usuarios -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Lista de Usuarios</h5>
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-3">Total: <span id="totalUsersCount">0</span></span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="refreshUsersTable()">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="usersTableContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted">Cargando usuarios...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sección Viajes -->
            <div id="rides-section" class="content-section d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-car-front me-2"></i>Gestión de Viajes</h2>
                    <div class="btn-group">
                        <button class="btn btn-success" onclick="showCreateRideModal()">
                            <i class="bi bi-plus me-1"></i>Nuevo Viaje
                        </button>
                        <button class="btn btn-outline-secondary" onclick="refreshRidesTable()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>

                <!-- Filtros de Viajes -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="rideSearchInput" placeholder="Buscar por nombre, origen, destino...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="rideActiveFilter">
                                    <option value="">Todos</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-2 align-self-end">
                                <button class="btn btn-primary w-100" onclick="filterRides()">
                                    <i class="bi bi-search me-1"></i>Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Viajes -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Lista de Viajes</h5>
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-3">Total: <span id="totalRidesCount">0</span></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="ridesTableContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted">Cargando viajes...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sección Vehículos -->
            <div id="vehicles-section" class="content-section d-none">
                <h2><i class="bi bi-truck me-2"></i>Gestión de Vehículos</h2>
                <p class="text-muted">Administrar vehículos de los choferes. Puedes crear, editar y eliminar vehículos.</p>

                <div class="mb-3">
                    <button class="btn btn-primary" onclick="showCreateVehicleModal()">
                        <i class="bi bi-plus me-1"></i>Nuevo Vehículo
                    </button>
                </div>

                <div id="vehiclesTableContainer">
                    <div class="text-center py-4 text-muted">Cargando vehículos...</div>
                </div>
            </div>
            
            <!-- Sección Reportes -->
            <div id="reports-section" class="content-section d-none">
                <h2><i class="bi bi-graph-up me-2"></i>Reportes</h2>
                <p class="text-muted">Visualiza métricas de viajes, ingresos y actividad de usuarios. Selecciona el rango y agrupa por día/semana/mes. Puedes exportar los datos en CSV.</p>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Desde</label>
                                <input type="date" id="reportFrom" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Hasta</label>
                                <input type="date" id="reportTo" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Agrupar por</label>
                                <select id="reportGroupBy" class="form-select">
                                    <option value="day">Día</option>
                                    <option value="week">Semana</option>
                                    <option value="month">Mes</option>
                                </select>
                            </div>
                            <div class="col-md-3 text-end">
                                <button class="btn btn-primary" id="reportApplyBtn">Aplicar</button>
                                <button class="btn btn-outline-secondary" id="reportExportBtn">Exportar CSV</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI cards -->
                <div class="row mb-3" id="reports-kpis">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Total Viajes</h6>
                                <h3 id="kpiTotalRides">-</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Ingresos Totales</h6>
                                <h3 id="kpiTotalRevenue">-</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Ocupación Promedio (%)</h6>
                                <h3 id="kpiOccupancy">-</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">Viajes & Ingresos (serie de tiempo)</div>
                            <div class="card-body">
                                <canvas id="ridesChart" height="140"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card mb-3">
                            <div class="card-header">Usuarios (nuevos / cancelaciones)</div>
                            <div class="card-body">
                                <canvas id="usersBarChart" height="160"></canvas>
                                <hr>
                                <canvas id="usersDonutChart" height="140"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">Detalle de períodos</div>
                    <div class="card-body">
                        <div id="reportsTableContainer">Cargando datos...</div>
                    </div>
                </div>
            </div>
            
            <!-- Sección Configuración -->
            <div id="settings-section" class="content-section d-none">
                <h2><i class="bi bi-gear me-2"></i>Configuración</h2>
                <p class="text-muted">Ajustes de contacto de soporte y configuración de correo SMTP.</p>

                <div class="card">
                    <div class="card-body">
                        <div id="settingsAlertPlaceholder"></div>
                        <form id="settingsForm">
                            <h6>Soporte</h6>
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Teléfono de Soporte</label>
                                    <input type="text" id="supportPhone" name="support_phone" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email de Soporte</label>
                                    <input type="email" id="supportEmail" name="support_email" class="form-control">
                                </div>
                            </div>

                            <h6>SMTP (Correo saliente)</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Host</label>
                                    <input type="text" id="smtpHost" name="smtp[host]" class="form-control">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Puerto</label>
                                    <input type="number" id="smtpPort" name="smtp[port]" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Encriptación</label>
                                    <select id="smtpEncryption" name="smtp[encryption]" class="form-select">
                                        <option value="">Ninguna</option>
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Usuario</label>
                                    <input type="text" id="smtpUser" name="smtp[username]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" id="smtpPass" name="smtp[password]" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">From email</label>
                                    <input type="email" id="smtpFromEmail" name="smtp[from_email]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">From name</label>
                                    <input type="text" id="smtpFromName" name="smtp[from_name]" class="form-control">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" id="settingsSaveBtn" class="btn btn-primary">Guardar Configuración</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Modal: Crear/Editar Usuario -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    <input type="hidden" id="userId" name="userId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="userType" class="form-label">Tipo de Usuario *</label>
                            <select class="form-select" id="userType" name="userType" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="passenger">Pasajero</option>
                                <option value="driver">Conductor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="userStatus" class="form-label">Estado</label>
                            <select class="form-select" id="userStatus" name="userStatus">
                                <option value="pending">Pendiente</option>
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="userEmail" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="userEmail" name="userEmail" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="userPhone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="userPhone" name="userPhone">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="userCedula" class="form-label">Cédula</label>
                            <input type="text" class="form-control" id="userCedula" name="userCedula">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="birthDate" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="birthDate" name="birthDate">
                        </div>
                    </div>
                    
                    <div class="row" id="passwordSection">
                        <div class="col-md-6 mb-3">
                            <label for="userPassword" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="userPassword" name="userPassword">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirmPassword" class="form-label">Confirmar Contraseña *</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="userPhoto" class="form-label">Foto de Perfil</label>
                        <input type="file" class="form-control" id="userPhoto" name="userPhoto" accept="image/*">
                        <div class="form-text">Formatos permitidos: JPG, PNG. Tamaño máximo: 5MB</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="saveButtonText">Crear Usuario</span>
                        <span id="saveButtonSpinner" class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Eliminación -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="bi bi-exclamation-triangle display-4 text-warning"></i>
                    <h5 class="mt-3">¿Estás seguro?</h5>
                    <p class="text-muted">Esta acción no se puede deshacer. El usuario y todos sus datos asociados serán eliminados permanentemente.</p>
                    <div class="alert alert-info">
                        <strong>Usuario a eliminar:</strong><br>
                        <span id="deleteUserInfo"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteUser()">
                    <i class="bi bi-trash me-1"></i>Eliminar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

            <!-- Modal: Crear/Editar Vehículo (Admin) -->
            <div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="vehicleModalLabel">Nuevo Vehículo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="vehicleForm">
                            <div class="modal-body">
                                <input type="hidden" id="vehicleId" name="vehicleId">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="vehicleDriver" class="form-label">Conductor *</label>
                                        <select id="vehicleDriver" name="driver_id" class="form-select" required>
                                            <option value="">Cargando conductores...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="plateNumber" class="form-label">Placa *</label>
                                        <input type="text" class="form-control" id="plateNumber" name="plate_number" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="brand" class="form-label">Marca</label>
                                        <input type="text" class="form-control" id="brand" name="brand">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="model" class="form-label">Modelo</label>
                                        <input type="text" class="form-control" id="model" name="model">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="year" class="form-label">Año</label>
                                        <input type="number" class="form-control" id="year" name="year" min="1900" max="2100">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="color" class="form-label">Color</label>
                                        <input type="text" class="form-control" id="color" name="color">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="seatsCapacity" class="form-label">Capacidad de Asientos *</label>
                                        <input type="number" class="form-control" id="seatsCapacity" name="seats_capacity" min="1" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="vehiclePhoto" class="form-label">Foto del Vehículo</label>
                                    <input type="file" class="form-control" id="vehiclePhoto" name="photo" accept="image/*">
                                    <div class="form-text">Formatos permitidos: JPG, PNG. Tamaño máximo: 5MB</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">
                                    <span id="vehicleSaveText">Guardar Vehículo</span>
                                    <span id="vehicleSaveSpinner" class="spinner-border spinner-border-sm d-none ms-2"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Expose BASE_URL to the external admin dashboard script
const BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>/js/admin-dashboard.js"></script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
