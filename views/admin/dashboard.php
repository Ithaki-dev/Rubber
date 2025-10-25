<?php
$pageTitle = "Panel de Administrador - Carpooling UCR";
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4 bg-dark text-light vh-100 position-sticky top-0 pt-5">
            <div class="p-3">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-check display-4 text-warning"></i>
                    <h5 class="mt-2">¡Hola <?= htmlspecialchars(Session::getCurrentUser()['first_name']) ?>!</h5>
                    <small class="text-muted">Administrador</small>
                </div>

                <nav class="nav nav-pills flex-column">
                    <a class="nav-link active text-light" href="#dashboard" data-section="dashboard">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                    <a class="nav-link text-light" href="#users" data-section="users">
                        <i class="bi bi-people me-2"></i>Usuarios
                    </a>
                    <a class="nav-link text-light" href="#rides" data-section="rides">
                        <i class="bi bi-car-front me-2"></i>Viajes
                    </a>
                    <a class="nav-link text-light" href="#vehicles" data-section="vehicles">
                        <i class="bi bi-car-front-fill me-2"></i>Vehículos
                    </a>
                    <a class="nav-link text-light" href="#reports" data-section="reports">
                        <i class="bi bi-bar-chart me-2"></i>Reportes
                    </a>
                    <a class="nav-link text-light" href="#settings" data-section="settings">
                        <i class="bi bi-gear me-2"></i>Configuración
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">
            <div class="p-4">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard General</h2>
                        <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                            <i class="bi bi-arrow-clockwise"></i> Actualizar
                        </button>
                    </div>

                    <!-- KPI Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-people display-4 me-3"></i>
                                        <div>
                                            <h3 id="totalUsers">0</h3>
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
                                            <h3 id="totalRides">0</h3>
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
                                            <h3 id="activeReservations">0</h3>
                                            <small>Reservas Activas</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-cash-coin display-4 me-3"></i>
                                        <div>
                                            <h3 id="totalRevenue">₡0</h3>
                                            <small>Ingresos Totales</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Viajes por Mes</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="ridesChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Usuarios por Tipo</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="usersChart" width="200" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Actividad Reciente</h5>
                                </div>
                                <div class="card-body">
                                    <div id="recentActivity">
                                        <div class="text-center text-muted">
                                            <i class="bi bi-clock-history display-4"></i>
                                            <p class="mt-2">Cargando actividad...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Estadísticas Rápidas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <h4 id="todayRides" class="text-primary">0</h4>
                                            <small>Viajes Hoy</small>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <h4 id="newUsers" class="text-success">0</h4>
                                            <small>Nuevos Usuarios</small>
                                        </div>
                                        <div class="col-6">
                                            <h4 id="avgRating" class="text-warning">0.0</h4>
                                            <small>Calificación Promedio</small>
                                        </div>
                                        <div class="col-6">
                                            <h4 id="activeDrivers" class="text-info">0</h4>
                                            <small>Conductores Activos</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Section -->
                <div id="users-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-people me-2"></i>Gestión de Usuarios</h2>
                        <div class="btn-group">
                            <button class="btn btn-outline-primary" onclick="exportUsers()">
                                <i class="bi bi-download"></i> Exportar
                            </button>
                            <button class="btn btn-primary" onclick="showAddUser()">
                                <i class="bi bi-plus-circle"></i> Nuevo Usuario
                            </button>
                        </div>
                    </div>

                    <!-- Users Filters -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <select class="form-select" id="userRoleFilter">
                                        <option value="">Todos los roles</option>
                                        <option value="passenger">Pasajeros</option>
                                        <option value="driver">Conductores</option>
                                        <option value="admin">Administradores</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="userStatusFilter">
                                        <option value="">Todos los estados</option>
                                        <option value="active">Activos</option>
                                        <option value="inactive">Inactivos</option>
                                        <option value="pending">Pendientes</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="userSearch" placeholder="Buscar por nombre, email...">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100" onclick="filterUsers()">
                                        <i class="bi bi-search"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Rol</th>
                                            <th>Estado</th>
                                            <th>Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usersTableBody">
                                        <tr>
                                            <td colspan="7" class="text-center">Cargando usuarios...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rides Section -->
                <div id="rides-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-car-front me-2"></i>Gestión de Viajes</h2>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3 id="todayRidesCount">0</h3>
                                    <small>Viajes Hoy</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3 id="upcomingRidesCount">0</h3>
                                    <small>Próximos Viajes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3 id="completedRidesCount">0</h3>
                                    <small>Completados</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <h3 id="cancelledRidesCount">0</h3>
                                    <small>Cancelados</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div id="ridesTableContainer">
                                <div class="text-center">Cargando viajes...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicles Section -->
                <div id="vehicles-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-car-front-fill me-2"></i>Gestión de Vehículos</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div id="vehiclesTableContainer">
                                <div class="text-center">Cargando vehículos...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reports Section -->
                <div id="reports-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-bar-chart me-2"></i>Reportes y Análisis</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Reportes Disponibles</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary" onclick="generateReport('users')">
                                            <i class="bi bi-people me-2"></i>Reporte de Usuarios
                                        </button>
                                        <button class="btn btn-outline-success" onclick="generateReport('rides')">
                                            <i class="bi bi-car-front me-2"></i>Reporte de Viajes
                                        </button>
                                        <button class="btn btn-outline-info" onclick="generateReport('revenue')">
                                            <i class="bi bi-cash-coin me-2"></i>Reporte Financiero
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="generateReport('activity')">
                                            <i class="bi bi-activity me-2"></i>Reporte de Actividad
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Vista Previa del Reporte</h5>
                                </div>
                                <div class="card-body">
                                    <div id="reportPreview">
                                        <div class="text-center text-muted">
                                            <i class="bi bi-file-text display-4"></i>
                                            <p class="mt-2">Selecciona un reporte para ver la vista previa</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Section -->
                <div id="settings-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-gear me-2"></i>Configuración del Sistema</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Configuración General</h5>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="mb-3">
                                            <label class="form-label">Nombre del Sistema</label>
                                            <input type="text" class="form-control" value="Carpooling UCR">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email de Contacto</label>
                                            <input type="email" class="form-control" value="soporte@ucr.ac.cr">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Precio Mínimo por Asiento</label>
                                            <input type="number" class="form-control" value="500">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Precio Máximo por Asiento</label>
                                            <input type="number" class="form-control" value="5000">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Configuración de Email</h5>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="mb-3">
                                            <label class="form-label">API Key Testmail.app</label>
                                            <input type="text" class="form-control" value="<?= TESTMAIL_API_KEY ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Namespace</label>
                                            <input type="text" class="form-control" value="<?= TESTMAIL_NAMESPACE ?>">
                                        </div>
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="emailEnabled" checked>
                                            <label class="form-check-label" for="emailEnabled">
                                                Activar envío de emails
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                                    </form>
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
    loadDashboardData();
});

function loadSectionData(section) {
    switch(section) {
        case 'dashboard':
            loadDashboardData();
            break;
        case 'users':
            loadUsersData();
            break;
        case 'rides':
            loadRidesData();
            break;
        case 'vehicles':
            loadVehiclesData();
            break;
    }
}

function loadDashboardData() {
    fetch(`${BASE_URL}/api/admin/dashboard`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update KPI cards
                document.getElementById('totalUsers').textContent = data.kpis.totalUsers;
                document.getElementById('totalRides').textContent = data.kpis.totalRides;
                document.getElementById('activeReservations').textContent = data.kpis.activeReservations;
                document.getElementById('totalRevenue').textContent = '₡' + data.kpis.totalRevenue;
                
                // Update quick stats
                document.getElementById('todayRides').textContent = data.stats.todayRides;
                document.getElementById('newUsers').textContent = data.stats.newUsers;
                document.getElementById('avgRating').textContent = data.stats.avgRating;
                document.getElementById('activeDrivers').textContent = data.stats.activeDrivers;
                
                // Load recent activity
                loadRecentActivity(data.activity);
            }
        })
        .catch(error => console.error('Error loading dashboard:', error));
}

function loadRecentActivity(activities) {
    const container = document.getElementById('recentActivity');
    if (activities && activities.length > 0) {
        container.innerHTML = activities.map(activity => `
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-${activity.icon} me-2 text-${activity.type}"></i>
                <div class="flex-grow-1">
                    <small>${activity.message}</small>
                    <div class="text-muted" style="font-size: 0.8em">${activity.time}</div>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = `
            <div class="text-center text-muted">
                <i class="bi bi-clock-history display-4"></i>
                <p class="mt-2">No hay actividad reciente</p>
            </div>
        `;
    }
}

function loadUsersData() {
    fetch(`${BASE_URL}/api/admin/users`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('usersTableBody');
            if (data.success && data.users.length > 0) {
                tbody.innerHTML = data.users.map(user => `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.first_name} ${user.last_name}</td>
                        <td>${user.email}</td>
                        <td>
                            <span class="badge bg-${user.role === 'admin' ? 'danger' : user.role === 'driver' ? 'primary' : 'secondary'}">
                                ${user.role}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-${user.is_active ? 'success' : 'warning'}">
                                ${user.is_active ? 'Activo' : 'Inactivo'}
                            </span>
                        </td>
                        <td>${user.created_at}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editUser(${user.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-${user.is_active ? 'warning' : 'success'}" onclick="toggleUserStatus(${user.id})">
                                <i class="bi bi-${user.is_active ? 'pause' : 'play'}"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay usuarios registrados</td></tr>';
            }
        });
}

function loadRidesData() {
    // TODO: Implement rides data loading
}

function loadVehiclesData() {
    // TODO: Implement vehicles data loading
}

function refreshDashboard() {
    loadDashboardData();
}

function filterUsers() {
    const role = document.getElementById('userRoleFilter').value;
    const status = document.getElementById('userStatusFilter').value;
    const search = document.getElementById('userSearch').value;
    
    const params = new URLSearchParams();
    if (role) params.append('role', role);
    if (status) params.append('status', status);
    if (search) params.append('search', search);
    
    fetch(`${BASE_URL}/api/admin/users?${params}`)
        .then(response => response.json())
        .then(data => {
            loadUsersData(); // Reload with filters
        });
}

function exportUsers() {
    window.open(`${BASE_URL}/api/admin/users/export`, '_blank');
}

function showAddUser() {
    // TODO: Show add user modal
    alert('Función en desarrollo');
}

function editUser(userId) {
    // TODO: Show edit user modal
    alert('Editar usuario: ' + userId);
}

function toggleUserStatus(userId) {
    if (confirm('¿Cambiar el estado de este usuario?')) {
        fetch(`${BASE_URL}/api/admin/users/${userId}/toggle`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadUsersData();
            }
        });
    }
}

function generateReport(type) {
    const container = document.getElementById('reportPreview');
    container.innerHTML = '<div class="text-center"><div class="spinner-border"></div><p>Generando reporte...</p></div>';
    
    fetch(`${BASE_URL}/api/admin/reports/${type}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = `
                    <div class="alert alert-success">
                        <h5>Reporte: ${data.report.title}</h5>
                        <p>${data.report.description}</p>
                        <a href="${data.report.downloadUrl}" class="btn btn-primary">
                            <i class="bi bi-download me-2"></i>Descargar Reporte
                        </a>
                    </div>
                `;
            } else {
                container.innerHTML = '<div class="alert alert-danger">Error al generar el reporte</div>';
            }
        });
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>