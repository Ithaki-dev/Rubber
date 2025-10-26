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
                <p class="text-muted">Funcionalidad de reportes en desarrollo...</p>
            </div>
            
            <!-- Sección Configuración -->
            <div id="settings-section" class="content-section d-none">
                <h2><i class="bi bi-gear me-2"></i>Configuración</h2>
                <p class="text-muted">Funcionalidad de configuración en desarrollo...</p>
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

<script>
const BASE_URL = '<?= BASE_URL ?>';

document.addEventListener('DOMContentLoaded', function() {
    // Navegación entre secciones
    const navLinks = document.querySelectorAll('.nav-link[data-section]');
    const sections = document.querySelectorAll('.content-section');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Clicked section:', this.getAttribute('data-section'));
            
            // Actualizar navegación activa
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Mostrar/ocultar secciones
            const targetSection = this.getAttribute('data-section');
            sections.forEach(section => {
                if (section.id === targetSection + '-section') {
                    section.classList.remove('d-none');
                } else {
                    section.classList.add('d-none');
                }
            });
            
            // Cargar datos específicos de la sección
            if (targetSection === 'users') {
                loadUsersData();
            } else if (targetSection === 'rides') {
                // Cargar viajes al mostrar la sección
                loadRidesData();
            }
        });
    });
});

function refreshDashboard() {
    location.reload();
}

// ==========================================
// GESTIÓN DE USUARIOS
// ==========================================

let currentUserId = null;
let usersData = [];

function loadUsersData() {
    const container = document.getElementById('usersTableContainer');
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando usuarios...</p>
        </div>
    `;
    
    // Obtener usuarios reales del servidor
    fetch(`${BASE_URL}/api/admin/users`)
        .then(response => response.json())
        .then(data => {
            console.log('Users loaded:', data);
            if (data.success) {
                usersData = data.users;
                console.log('usersData set to:', usersData);
                renderUsersTable(usersData);
            } else {
                console.error('Error loading users:', data.message);
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle display-4 text-warning"></i>
                        <h5 class="mt-3 text-muted">Error al cargar usuarios</h5>
                        <p class="text-muted">${data.message || 'Error desconocido'}</p>
                        <button class="btn btn-primary" onclick="loadUsersData()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reintentar
                        </button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            renderUsersTable(usersData);
        });
}

function renderUsersTable(users) {
    const container = document.getElementById('usersTableContainer');
    const totalCount = document.getElementById('totalUsersCount');
    // Ordenar por ID de usuario (menor a mayor) antes de renderizar
    const sortedUsers = Array.isArray(users) ? users.slice().sort((a, b) => {
        const ai = Number(a.user_id ?? a.id ?? 0);
        const bi = Number(b.user_id ?? b.id ?? 0);
        return ai - bi;
    }) : [];

    totalCount.textContent = sortedUsers.length;
    
    if (sortedUsers.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-people display-4 text-muted"></i>
                <h5 class="mt-3 text-muted">No hay usuarios</h5>
                <p class="text-muted">No se encontraron usuarios con los criterios seleccionados.</p>
                <button class="btn btn-primary" onclick="showCreateUserModal()">
                    <i class="bi bi-plus me-1"></i>Crear Primer Usuario
                </button>
            </div>
        `;
        return;
    }
    
    const tableHTML = `
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Contacto</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Registro</th>
                        <th>Último Acceso</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ${sortedUsers.map(user => `
                        <tr>
                            <td>
                                <div>
                                    <div class="fw-semibold">${user.first_name} ${user.last_name}</div>
                                    <small class="text-muted">${user.cedula || 'N/A'}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div><i class="bi bi-envelope me-1"></i>${user.email}</div>
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i>${user.phone || 'N/A'}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge ${getUserTypeBadgeClass(user.user_type)}">
                                    <i class="bi ${getUserTypeIcon(user.user_type)} me-1"></i>
                                    ${getUserTypeLabel(user.user_type)}
                                </span>
                            </td>
                            <td>
                                <span class="badge ${getStatusBadgeClass(user.status)}">
                                    <i class="bi ${getStatusIcon(user.status)} me-1"></i>
                                    ${getStatusLabel(user.status)}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    ${formatDate(user.created_at)}
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    ${user.last_login ? formatDate(user.last_login) : 'Nunca'}
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewUser(${user.user_id})" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="editUser(${user.user_id})" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-${user.status === 'active' ? 'secondary' : 'success'}" 
                                            onclick="toggleUserStatus(${user.user_id})" 
                                            title="${user.status === 'active' ? 'Desactivar' : 'Activar'}">
                                        <i class="bi bi-${user.status === 'active' ? 'pause' : 'play'}"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="showDeleteUserModal(${user.user_id})" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = tableHTML;
}

// ==========================================
// GESTIÓN DE VIAJES
// ==========================================

let currentRideId = null;
let ridesData = [];
let vehiclesMap = {}; // vehicleId -> vehicle object (capacity etc)

function loadRidesData() {
    const container = document.getElementById('ridesTableContainer');
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando viajes...</p>
        </div>
    `;

    const params = new URLSearchParams();
    const search = document.getElementById('rideSearchInput') ? document.getElementById('rideSearchInput').value : '';
    const active = document.getElementById('rideActiveFilter') ? document.getElementById('rideActiveFilter').value : '';
    if (search) params.append('search', search);
    if (active !== '') params.append('active', active);

    fetch(`${BASE_URL}/api/admin/rides` + (params.toString() ? ('?' + params.toString()) : ''))
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                ridesData = data.rides;
                renderRidesTable(ridesData);
            } else {
                container.innerHTML = `<div class="text-center py-4 text-muted">Error: ${data.message || 'No se pudieron cargar los viajes'}</div>`;
            }
        })
        .catch(err => {
            console.error('Error loading rides:', err);
            container.innerHTML = `<div class="text-center py-4 text-muted">Error de conexión</div>`;
        });
}

// Cargar lista de drivers (conductores activos) y poblar el select
function loadDriversIntoModal(selectedDriverId = null) {
    const driverSelect = document.getElementById('rideDriver');
    const vehicleSelect = document.getElementById('rideVehicle');

    if (!driverSelect) return Promise.resolve();

    driverSelect.innerHTML = '<option value="">Cargando conductores...</option>';
    vehicleSelect.innerHTML = '<option value="">Seleccione un conductor primero</option>';
    vehicleSelect.disabled = true;

    return fetch(`${BASE_URL}/admin/drivers`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                driverSelect.innerHTML = '<option value="">Error al cargar conductores</option>';
                return;
            }
            const drivers = data.drivers || [];
            driverSelect.innerHTML = '<option value="">Seleccionar conductor</option>' + drivers.map(d => `
                <option value="${d.id}" ${selectedDriverId && d.id == selectedDriverId ? 'selected' : ''}>
                    ${d.first_name} ${d.last_name} ${d.cedula ? ' - ' + d.cedula : ''}
                </option>
            `).join('');

            // If a driver is selected, load their vehicles
            if (selectedDriverId) {
                return loadVehiclesForDriver(selectedDriverId, null);
            }
        })
        .catch(err => {
            console.error('Error loading drivers', err);
            driverSelect.innerHTML = '<option value="">Error al cargar conductores</option>';
        });
}

// Cargar vehículos para un driver y poblar el select
function loadVehiclesForDriver(driverId, selectedVehicleId = null) {
    const vehicleSelect = document.getElementById('rideVehicle');
    if (!vehicleSelect) return;

    vehicleSelect.innerHTML = '<option value="">Cargando vehículos...</option>';
    vehicleSelect.disabled = true;

    return fetch(`${BASE_URL}/admin/drivers/${driverId}/vehicles`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                vehicleSelect.innerHTML = '<option value="">Error al cargar vehículos</option>';
                return;
            }
            const vehicles = data.vehicles || [];
            vehiclesMap = {};
            if (vehicles.length === 0) {
                vehicleSelect.innerHTML = '<option value="">El conductor no tiene vehículos</option>';
                vehicleSelect.disabled = true;
                return;
            }

            vehicleSelect.innerHTML = '<option value="">Seleccionar vehículo</option>' + vehicles.map(v => {
                vehiclesMap[v.id] = v;
                return `<option value="${v.id}" ${selectedVehicleId && v.id == selectedVehicleId ? 'selected' : ''}>${v.display}</option>`;
            }).join('');
            vehicleSelect.disabled = false;
        })
        .catch(err => {
            console.error('Error loading vehicles', err);
            vehicleSelect.innerHTML = '<option value="">Error al cargar vehículos</option>';
            vehicleSelect.disabled = true;
        });
}

// ======= Vehículos (Admin) =======
let currentVehicleId = null;

function showCreateVehicleModal() {
    currentVehicleId = null;
    document.getElementById('vehicleModalLabel').textContent = 'Nuevo Vehículo';
    document.getElementById('vehicleSaveText').textContent = 'Crear Vehículo';
    document.getElementById('vehicleForm').reset();
    loadDriversIntoVehicleModal();
    const modal = new bootstrap.Modal(document.getElementById('vehicleModal'));
    modal.show();
}

function loadDriversIntoVehicleModal(selectedDriverId = null) {
    const select = document.getElementById('vehicleDriver');
    if (!select) return;
    select.innerHTML = '<option value="">Cargando conductores...</option>';
    return fetch(`${BASE_URL}/admin/drivers`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                select.innerHTML = '<option value="">Error al cargar conductores</option>';
                return;
            }
            const drivers = data.drivers || [];
            select.innerHTML = '<option value="">Seleccionar conductor</option>' + drivers.map(d => `
                <option value="${d.id}" ${selectedDriverId && d.id == selectedDriverId ? 'selected' : ''}>
                    ${d.first_name} ${d.last_name} ${d.cedula ? ' - ' + d.cedula : ''}
                </option>
            `).join('');
        })
        .catch(err => {
            console.error('Error loading drivers for vehicle modal', err);
            select.innerHTML = '<option value="">Error al cargar conductores</option>';
        });
}

function editVehicle(vehicleId) {
    const modal = new bootstrap.Modal(document.getElementById('vehicleModal'));
    // Preferimos consumir el endpoint API que devuelve JSON
    fetch(`${BASE_URL}/api/admin/vehicles/${vehicleId}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showAlert('error', data.message || 'No se pudo cargar el vehículo');
                return;
            }
            populateVehicleForm(data.vehicle);
            modal.show();
        })
        .catch(err => {
            console.error('Error loading vehicle for edit', err);
            showAlert('error', 'No se pudo cargar el vehículo');
        });
}

function populateVehicleForm(vehicle) {
    currentVehicleId = vehicle.id;
    document.getElementById('vehicleModalLabel').textContent = 'Editar Vehículo';
    document.getElementById('vehicleSaveText').textContent = 'Actualizar Vehículo';
    document.getElementById('vehicleId').value = vehicle.id;
    document.getElementById('plateNumber').value = vehicle.plate_number || vehicle.plate || '';
    document.getElementById('brand').value = vehicle.brand || '';
    document.getElementById('model').value = vehicle.model || '';
    document.getElementById('year').value = vehicle.year || '';
    document.getElementById('color').value = vehicle.color || '';
    document.getElementById('seatsCapacity').value = vehicle.seats_capacity || vehicle.capacity || '';
    loadDriversIntoVehicleModal(vehicle.driver_id);
}

// Submit handler for vehicle form
document.addEventListener('DOMContentLoaded', function() {
    const vehicleForm = document.getElementById('vehicleForm');
    if (!vehicleForm) return;

    vehicleForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(vehicleForm);
        const id = currentVehicleId;
        const url = id ? `${BASE_URL}/admin/vehicles/${id}` : `${BASE_URL}/admin/vehicles`;
        document.getElementById('vehicleSaveSpinner').classList.remove('d-none');

        fetch(url, { method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}, body: fd })
            .then(async r => {
                const text = await r.text();
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showAlert('success', data.message || 'Vehículo guardado');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('vehicleModal'));
                        if (modal) modal.hide();
                        // recargar listado de vehículos si existe
                        if (typeof loadVehiclesTable === 'function') loadVehiclesTable();
                    } else {
                        showAlert('error', data.message || 'Error al guardar vehículo');
                    }
                } catch (e) {
                    console.error('Vehicle save raw response:', text);
                    showAlert('error', 'Respuesta inválida del servidor. Revisa la consola.');
                }
            })
            .catch(err => {
                console.error('Error saving vehicle', err);
                showAlert('error', 'Error de conexión');
            })
            .finally(() => document.getElementById('vehicleSaveSpinner').classList.add('d-none'));
    });
});

// Simple vehicles table loader for admin (uses server-side admin list)
function loadVehiclesTable() {
    const container = document.getElementById('vehiclesTableContainer');
    container.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-2 text-muted">Cargando vehículos...</p></div>`;

    const params = new URLSearchParams();
    const active = '';
    if (active !== '') params.append('active', active);

    fetch(`${BASE_URL}/api/admin/vehicles` + (params.toString() ? ('?' + params.toString()) : ''))
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                container.innerHTML = `<div class="text-center py-4 text-muted">Error: ${data.message || 'No se pudieron cargar los vehículos'}</div>`;
                return;
            }

            const vehicles = data.vehicles || [];
            if (vehicles.length === 0) {
                container.innerHTML = `<div class="text-center py-4 text-muted">No hay vehículos registrados</div>`;
                return;
            }

            const table = `
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Placa</th>
                                <th>Chofer</th>
                                <th>Marca / Modelo</th>
                                <th>Año</th>
                                <th>Seats</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${vehicles.map(v => `
                                <tr>
                                    <td>#${v.id}</td>
                                    <td>${v.plate_number}</td>
                                    <td>${v.driver_first_name} ${v.driver_last_name} ${v.driver_id ? `<small class="text-muted">(#${v.driver_id})</small>` : ''}</td>
                                    <td>${v.brand} ${v.model}</td>
                                    <td>${v.year || ''}</td>
                                    <td>${v.seats_capacity}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editVehicle(${v.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-outline-danger" onclick="deleteVehicle(${v.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = table;
        })
        .catch(err => {
            console.error('Error loading admin vehicles', err);
            container.innerHTML = `<div class="text-center py-4 text-muted">Error al cargar vehículos</div>`;
        });
}

function deleteVehicle(vehicleId) {
    if (!confirm('¿Seguro que deseas eliminar este vehículo?')) return;
    fetch(`${BASE_URL}/admin/vehicles/${vehicleId}/delete`, { method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'} })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message || 'Vehículo eliminado');
                loadVehiclesTable();
            } else {
                showAlert('error', data.message || 'Error al eliminar vehículo');
            }
        })
        .catch(err => {
            console.error('Error deleting vehicle', err);
            showAlert('error', 'Error de conexión');
        });
}

// Cargar tabla de vehículos cuando se muestra la sección
document.addEventListener('DOMContentLoaded', function() {
    const vehiclesNav = document.querySelector('.nav-link[data-section="vehicles"]');
    if (vehiclesNav) {
        vehiclesNav.addEventListener('click', function() {
            loadVehiclesTable();
        });
    }
});

function renderRidesTable(rides) {
    const container = document.getElementById('ridesTableContainer');
    const totalCount = document.getElementById('totalRidesCount');

    const sorted = Array.isArray(rides) ? rides.slice().sort((a,b) => Number(a.ride_id) - Number(b.ride_id)) : [];
    totalCount.textContent = sorted.length;

    if (sorted.length === 0) {
        container.innerHTML = `<div class="text-center py-5 text-muted">No hay viajes</div>`;
        return;
    }

    const html = `
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Fecha / Hora</th>
                        <th>Costo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ${sorted.map(r => `
                        <tr>
                            <td>#${r.ride_id}</td>
                            <td>${r.ride_name}</td>
                            <td>${r.departure_location}</td>
                            <td>${r.arrival_location}</td>
                            <td>${r.ride_date} ${r.ride_time}</td>
                            <td>${r.cost_per_seat ? r.cost_per_seat : 'N/A'}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editRide(${r.ride_id})" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="showDeleteRideModal(${r.ride_id})" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;

    container.innerHTML = html;
}

function filterRides() {
    loadRidesData();
}

function refreshRidesTable() {
    loadRidesData();
}

function showCreateRideModal() {
    currentRideId = null;
    document.getElementById('rideForm').reset();
    document.getElementById('rideModalLabel').textContent = 'Nuevo Viaje';
    document.getElementById('rideSaveText').textContent = 'Crear';
    // Cargar drivers y limpiar vehicle select
    loadDriversIntoModal().then(() => {
        const modal = new bootstrap.Modal(document.getElementById('rideModal'));
        modal.show();
    });
}

function editRide(rideId) {
    const ride = ridesData.find(r => r.ride_id == rideId);
    if (!ride) { showAlert('error','Viaje no encontrado'); return; }
    currentRideId = rideId;
    document.getElementById('rideModalLabel').textContent = 'Editar Viaje';
    document.getElementById('rideSaveText').textContent = 'Actualizar';
    document.getElementById('rideId').value = ride.ride_id;
    document.getElementById('rideName').value = ride.ride_name;
    document.getElementById('departureLocation').value = ride.departure_location;
    document.getElementById('arrivalLocation').value = ride.arrival_location;
    document.getElementById('rideDate').value = ride.ride_date;
    document.getElementById('rideTime').value = ride.ride_time;
    document.getElementById('costPerSeat').value = ride.cost_per_seat || '';
    document.getElementById('totalSeats').value = ride.total_seats || '';
    // Cargar drivers y seleccionar el actual, luego cargar vehículos del driver y seleccionar el vehículo
    loadDriversIntoModal(ride.driver_id).then(() => {
        return loadVehiclesForDriver(ride.driver_id, ride.vehicle_id);
    }).then(() => {
        const modal = new bootstrap.Modal(document.getElementById('rideModal'));
        modal.show();
    });
}

function showDeleteRideModal(rideId) {
    const ride = ridesData.find(r => r.ride_id == rideId);
    if (!ride) { showAlert('error','Viaje no encontrado'); return; }
    currentRideId = rideId;
    document.getElementById('deleteRideInfo').textContent = `${ride.ride_name} (${ride.departure_location} → ${ride.arrival_location})`;
    const modal = new bootstrap.Modal(document.getElementById('deleteRideModal'));
    modal.show();
}

function confirmDeleteRide() {
    if (!currentRideId) return;
    fetch(`${BASE_URL}/api/admin/rides/${currentRideId}/delete`, {
        method: 'POST',
        headers: {'X-Requested-With':'XMLHttpRequest'}
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || 'Viaje eliminado');
            loadRidesData();
        } else {
            showAlert('error', data.message || 'Error al eliminar');
        }
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteRideModal'));
        if (modal) modal.hide();
    })
    .catch(err => {
        console.error('Delete ride error', err);
        showAlert('error','Error de conexión');
    });
}

// Manejar envío del formulario de viajes
document.addEventListener('DOMContentLoaded', function() {
    const rideForm = document.getElementById('rideForm');
    if (rideForm) {
        // Cuando cambia el driver, cargar vehículos correspondientes
        const rideDriverSelect = document.getElementById('rideDriver');
        if (rideDriverSelect) {
            rideDriverSelect.addEventListener('change', function() {
                const driverId = this.value;
                if (driverId) {
                    loadVehiclesForDriver(driverId);
                } else {
                    const vehicleSelect = document.getElementById('rideVehicle');
                    vehicleSelect.innerHTML = '<option value="">Seleccione un conductor primero</option>';
                    vehicleSelect.disabled = true;
                }
            });
        }

        rideForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(rideForm);
            // Validaciones cliente: vehicle obligatorio
            const driverId = formData.get('driver_id');
            const vehicleId = formData.get('vehicle_id');
            if (!driverId) {
                showAlert('error', 'Seleccione un conductor');
                return;
            }
            if (!vehicleId) {
                showAlert('error', 'Seleccione un vehículo');
                return;
            }

            // Validar total seats vs vehicle capacity - 1 (driver)
            const totalSeats = Number(formData.get('total_seats') || 0);
            const vehicle = vehiclesMap[vehicleId];
            if (!vehicle) {
                showAlert('error', 'Vehículo inválido');
                return;
            }
            const vehicleCapacity = Number(vehicle.capacity || 0);
            const maxAllowed = Math.max(0, vehicleCapacity - 1);
            if (totalSeats > maxAllowed) {
                showAlert('error', `El vehículo tiene capacidad ${vehicleCapacity}. Máximo asientos disponibles para viaje: ${maxAllowed}`);
                return;
            }
            const url = currentRideId ? `${BASE_URL}/api/admin/rides/${currentRideId}` : `${BASE_URL}/api/admin/rides`;
            document.getElementById('rideSaveSpinner').classList.remove('d-none');
            fetch(url, { method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}, body: formData })
            .then(async r => {
                console.log('Save ride response status:', r.status, 'content-type:', r.headers.get('Content-Type'));
                const text = await r.text();
                // Intentar parsear JSON; si falla, loguear el texto crudo para depuración
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showAlert('success', data.message || 'Guardado');
                        loadRidesData();
                        const modal = bootstrap.Modal.getInstance(document.getElementById('rideModal'));
                        if (modal) modal.hide();
                    } else {
                        showAlert('error', data.message || 'Error al guardar');
                    }
                } catch (e) {
                    console.error('Save ride raw response (not JSON):', text);
                    // Mostrar al usuario un mensaje más informativo
                    showAlert('error', 'Respuesta inválida del servidor. Revisa la consola para más detalles.');
                    // Re-lanzar para que el .catch también reciba el error
                    throw e;
                }
            })
            .catch(err => {
                console.error('Save ride error', err);
                showAlert('error','Error de conexión o respuesta inválida');
            })
            .finally(() => document.getElementById('rideSaveSpinner').classList.add('d-none'));
        });
    }
});

function getUserTypeBadgeClass(type) {
    const classes = {
        'admin': 'bg-danger',
        'driver': 'bg-primary',
        'passenger': 'bg-info'
    };
    return classes[type] || 'bg-secondary';
}

function getUserTypeIcon(type) {
    const icons = {
        'admin': 'bi-shield-check',
        'driver': 'bi-car-front',
        'passenger': 'bi-person'
    };
    return icons[type] || 'bi-person';
}

function getUserTypeLabel(type) {
    const labels = {
        'admin': 'Admin',
        'driver': 'Conductor',
        'passenger': 'Pasajero'
    };
    return labels[type] || 'Usuario';
}

function getStatusBadgeClass(status) {
    const classes = {
        'active': 'bg-success',
        'pending': 'bg-warning',
        'inactive': 'bg-secondary'
    };
    return classes[status] || 'bg-secondary';
}

function getStatusIcon(status) {
    const icons = {
        'active': 'bi-check-circle',
        'pending': 'bi-clock',
        'inactive': 'bi-x-circle'
    };
    return icons[status] || 'bi-question-circle';
}

function getStatusLabel(status) {
    const labels = {
        'active': 'Activo',
        'pending': 'Pendiente',
        'inactive': 'Inactivo'
    };
    return labels[status] || 'Desconocido';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-CR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function filterUsers() {
    const typeFilter = document.getElementById('userTypeFilter').value;
    const statusFilter = document.getElementById('userStatusFilter').value;
    const searchTerm = document.getElementById('userSearchInput').value.toLowerCase();
    
    let filteredUsers = usersData.filter(user => {
        const matchesType = !typeFilter || user.user_type === typeFilter;
        const matchesStatus = !statusFilter || user.status === statusFilter;
        const matchesSearch = !searchTerm || 
            user.first_name.toLowerCase().includes(searchTerm) ||
            user.last_name.toLowerCase().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm);
        
        return matchesType && matchesStatus && matchesSearch;
    });
    
    renderUsersTable(filteredUsers);
}

function refreshUsersTable() {
    loadUsersData();
}

function showCreateUserModal() {
    currentUserId = null;
    document.getElementById('userModalLabel').textContent = 'Nuevo Usuario';
    document.getElementById('saveButtonText').textContent = 'Crear Usuario';
    document.getElementById('userForm').reset();
    document.getElementById('passwordSection').style.display = 'block';
    document.getElementById('userPassword').required = true;
    document.getElementById('confirmPassword').required = true;
    
    const modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
}

function editUser(userId) {
    const user = usersData.find(u => u.user_id === userId);
    if (!user) return;
    
    currentUserId = userId;
    document.getElementById('userModalLabel').textContent = 'Editar Usuario';
    document.getElementById('saveButtonText').textContent = 'Actualizar Usuario';
    
    // Llenar formulario con datos del usuario
    document.getElementById('userId').value = user.user_id;
    document.getElementById('userType').value = user.user_type;
    document.getElementById('userStatus').value = user.status;
    document.getElementById('firstName').value = user.first_name;
    document.getElementById('lastName').value = user.last_name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userPhone').value = user.phone || '';
    document.getElementById('userCedula').value = user.cedula || '';
    document.getElementById('birthDate').value = user.birth_date || '';
    
    // Hacer contraseña opcional en edición
    document.getElementById('passwordSection').style.display = 'block';
    document.getElementById('userPassword').required = false;
    document.getElementById('confirmPassword').required = false;
    document.getElementById('userPassword').placeholder = 'Dejar vacío para mantener actual';
    document.getElementById('confirmPassword').placeholder = 'Dejar vacío para mantener actual';
    
    const modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
}

function viewUser(userId) {
    const user = usersData.find(u => u.user_id === userId);
    if (!user) return;
    
    // Crear modal de vista detallada (implementar según necesidades)
    alert(`Ver detalles de: ${user.first_name} ${user.last_name}\nEmail: ${user.email}\nTipo: ${getUserTypeLabel(user.user_type)}\nEstado: ${getStatusLabel(user.status)}`);
}

function toggleUserStatus(userId) {
    const user = usersData.find(u => u.user_id === userId);
    if (!user) return;
    
    const newStatus = user.status === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? 'activar' : 'desactivar';
    
    if (confirm(`¿Estás seguro de que deseas ${action} a ${user.first_name} ${user.last_name}?`)) {
        fetch(`${BASE_URL}/api/admin/users/${userId}/toggle`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                // Recargar la tabla de usuarios desde el servidor
                loadUsersData();
            } else {
                showAlert('error', data.message || 'Error al cambiar el estado del usuario');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error de conexión al servidor');
        });
    }
}

// Función de test
function testDeleteFunction(userId) {
    console.log('=== TEST DELETE FUNCTION ===');
    console.log('userId parameter:', userId);
    console.log('usersData exists:', typeof usersData !== 'undefined');
    console.log('usersData length:', usersData ? usersData.length : 'undefined');
    console.log('Modal element exists:', document.getElementById('deleteUserModal') !== null);
    showDeleteUserModal(userId);
}

function showDeleteUserModal(userId) {
    console.log('showDeleteUserModal called with userId:', userId);
    console.log('usersData:', usersData);
    
    if (!usersData || usersData.length === 0) {
        console.error('usersData is empty or undefined');
        showAlert('error', 'No hay datos de usuarios cargados');
        return;
    }
    
    const user = usersData.find(u => u.user_id == userId); // Usar == en lugar de === por si hay diferencia de tipos
    console.log('Found user:', user);
    
    if (!user) {
        console.error('User not found in usersData');
        console.log('Available user IDs:', usersData.map(u => u.user_id));
        showAlert('error', 'Usuario no encontrado');
        return;
    }
    
    currentUserId = userId;
    
    // Crear o encontrar el elemento de información del usuario
    let deleteUserInfoElement = document.getElementById('deleteUserInfo');
    const modalElement = document.getElementById('deleteUserModal');
    
    if (!modalElement) {
        console.error('Modal deleteUserModal not found');
        showAlert('error', 'Error: Modal no encontrado');
        return;
    }
    
    if (!deleteUserInfoElement) {
        console.log('Creating deleteUserInfo element dynamically');
        
        // Buscar el contenedor del modal body
        const modalBody = modalElement.querySelector('.modal-body .text-center');
        if (modalBody) {
            // Crear el div de información del usuario
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-info mt-3';
            alertDiv.innerHTML = `
                <strong>Usuario a eliminar:</strong><br>
                <span id="deleteUserInfo" class="text-dark"></span>
            `;
            modalBody.appendChild(alertDiv);
            deleteUserInfoElement = alertDiv.querySelector('#deleteUserInfo');
        }
        
        if (!deleteUserInfoElement) {
            // Fallback: usar el título del modal
            const modalTitle = modalElement.querySelector('#deleteUserModalLabel');
            if (modalTitle) {
                modalTitle.textContent = `Eliminar: ${user.first_name} ${user.last_name}`;
            }
            console.log('Using modal title as fallback');
        }
    }
    
    // Establecer la información del usuario
    if (deleteUserInfoElement) {
        deleteUserInfoElement.innerHTML = `
            <strong>${user.first_name} ${user.last_name}</strong><br>
            <small class="text-muted">${user.email}</small>
        `;
    }
    
    // Mostrar el modal
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function confirmDeleteUser() {
    if (!currentUserId) return;
    
    const user = usersData.find(u => u.user_id === currentUserId);
    if (!user) return;
    
    // Realizar petición al servidor para eliminar
    fetch(`${BASE_URL}/admin/users/${currentUserId}/delete`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(text => {
        console.log('Delete response:', text);
        
        // Cerrar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteUserModal'));
        modal.hide();
        
        // Intentar parsear como JSON si es posible
        try {
            const data = JSON.parse(text);
            if (data.success) {
                showAlert('success', data.message || `Usuario ${user.first_name} ${user.last_name} eliminado exitosamente`);
            } else {
                showAlert('error', data.message || 'Error al eliminar el usuario');
            }
        } catch (e) {
            // Si no es JSON, asumir que fue exitoso si no hay error HTML
            if (text.includes('Fatal error') || text.includes('Error')) {
                showAlert('error', 'Error del servidor al eliminar usuario');
            } else {
                showAlert('success', `Usuario ${user.first_name} ${user.last_name} eliminado exitosamente`);
            }
        }
        
        // Recargar datos del servidor
        loadUsersData();
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error de conexión al eliminar el usuario');
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteUserModal'));
        modal.hide();
    });
}

function exportUsers() {
    // Exportar usuarios filtrados a CSV (descarga del servidor)
    const role = document.getElementById('userTypeFilter') ? document.getElementById('userTypeFilter').value : '';
    const status = document.getElementById('userStatusFilter') ? document.getElementById('userStatusFilter').value : '';
    const search = document.getElementById('userSearchInput') ? document.getElementById('userSearchInput').value : '';

    showAlert('info', 'Preparando archivo de exportación...');

    const params = [];
    if (role) params.push('role=' + encodeURIComponent(role));
    if (status) params.push('status=' + encodeURIComponent(status));
    if (search) params.push('search=' + encodeURIComponent(search));

    const url = `${BASE_URL}/admin/users/export` + (params.length ? ('?' + params.join('&')) : '');

    // Redirigir a la URL para que el servidor entregue el CSV (Content-Disposition: attachment)
    window.location.href = url;
}

// Manejar envío del formulario de usuario
document.addEventListener('DOMContentLoaded', function() {
    const userForm = document.getElementById('userForm');
    if (userForm) {
        userForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(userForm);
            const password = formData.get('userPassword');
            const confirmPassword = formData.get('confirmPassword');
            
            // Validar contraseñas si se proporcionaron
            if (password || confirmPassword) {
                if (password !== confirmPassword) {
                    showAlert('error', 'Las contraseñas no coinciden');
                    return;
                }
                if (password.length < 6) {
                    showAlert('error', 'La contraseña debe tener al menos 6 caracteres');
                    return;
                }
            }
            
            // Mostrar spinner
            const spinner = document.getElementById('saveButtonSpinner');
            const buttonText = document.getElementById('saveButtonText');
            spinner.classList.remove('d-none');
            buttonText.textContent = currentUserId ? 'Actualizando...' : 'Creando...';
            
            // Realizar petición al servidor
            const url = currentUserId ? 
                `${BASE_URL}/api/admin/users/${currentUserId}` : 
                `${BASE_URL}/api/admin/users`;
            
            console.log('Enviando datos a:', url);
            console.log('FormData:', Object.fromEntries(formData.entries()));
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', [...response.headers.entries()]);
                
                return response.text().then(text => {
                    console.log('Raw response length:', text.length);
                    console.log('Raw response:', text);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}, body: ${text.substring(0, 200)}`);
                    }
                    
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response that failed to parse:', text.substring(0, 500));
                        throw new Error(`Invalid JSON response from server. Response: ${text.substring(0, 100)}`);
                    }
                });
            })
            .then(data => {
                console.log('Parsed data:', data);
                if (data.success) {
                    showAlert('success', data.message);
                    
                    // Recargar la tabla de usuarios desde el servidor
                    loadUsersData();
                    
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('userModal'));
                    modal.hide();
                } else {
                    showAlert('error', data.message || 'Error al procesar la solicitud');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showAlert('error', `Error de conexión: ${error.message}`);
            })
            .finally(() => {
                // Resetear spinner
                spinner.classList.add('d-none');
                buttonText.textContent = currentUserId ? 'Actualizar Usuario' : 'Crear Usuario';
            });
        });
    }
});

function showAlert(type, message) {
    // Crear alerta temporal
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
