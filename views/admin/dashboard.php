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
                <h2><i class="bi bi-people me-2"></i>Gestión de Usuarios</h2>
                <p class="text-muted">Funcionalidad de usuarios en desarrollo...</p>
            </div>
            
            <!-- Sección Viajes -->
            <div id="rides-section" class="content-section d-none">
                <h2><i class="bi bi-car-front me-2"></i>Gestión de Viajes</h2>
                <p class="text-muted">Funcionalidad de viajes en desarrollo...</p>
            </div>
            
            <!-- Sección Vehículos -->
            <div id="vehicles-section" class="content-section d-none">
                <h2><i class="bi bi-truck me-2"></i>Gestión de Vehículos</h2>
                <p class="text-muted">Funcionalidad de vehículos en desarrollo...</p>
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
        });
    });
});

function refreshDashboard() {
    location.reload();
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
