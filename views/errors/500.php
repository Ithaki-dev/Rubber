<?php
$pageTitle = "Error del Servidor - Carpooling UTN";
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <!-- Error Icon -->
            <div class="mb-4">
                <i class="bi bi-exclamation-octagon-fill display-1 text-danger"></i>
            </div>
            
            <!-- Error Message -->
            <h1 class="display-4 fw-bold mb-3">500</h1>
            <h2 class="h4 mb-3">Error del Servidor</h2>
            <p class="lead text-muted mb-4">
                Ocurrió un error interno en el servidor. Estamos trabajando para solucionarlo.
            </p>
            
            <!-- Explanation -->
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>¡Ups!</strong> Algo salió mal en nuestros servidores. El equipo técnico ha sido notificado.
            </div>
            
            <!-- Suggestions -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">¿Qué puedes hacer?</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-arrow-clockwise text-primary me-2"></i>
                            Recarga la página en unos minutos
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-left text-primary me-2"></i>
                            Regresa a la página anterior
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-house text-primary me-2"></i>
                            Ve a la página principal
                        </li>
                        <li>
                            <i class="bi bi-envelope text-primary me-2"></i>
                            Reporta el problema si persiste
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-4">
                <button onclick="location.reload()" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise me-2"></i>Recargar Página
                </button>
                <button onclick="history.back()" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Regresar
                </button>
                <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-house-fill me-2"></i>Ir al Inicio
                </a>
            </div>
            
            <!-- Report Section -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Reportar Problema</h5>
                    <p class="card-text">
                        Si este error persiste, ayúdanos reportándolo con los siguientes detalles:
                    </p>
                    <div class="bg-light p-3 rounded">
                        <small class="text-muted">
                            <strong>Hora:</strong> <?= date('Y-m-d H:i:s') ?><br>
                            <strong>URL:</strong> <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?><br>
                            <strong>Método:</strong> <?= htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? '') ?><br>
                            <strong>User Agent:</strong> <?= htmlspecialchars(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100)) ?>...
                        </small>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-bug me-2"></i>Reportar Error
                        </a>
                        <a href="mailto:soporte@utn.ac.cr?subject=Error 500 - Carpooling UTN" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-envelope me-2"></i>Enviar Email
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Status -->
            <div class="mt-4">
                <small class="text-muted">
                    Error ID: <?= uniqid() ?> | 
                    Tiempo: <?= date('Y-m-d H:i:s') ?>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh suggestion after 30 seconds
    setTimeout(function() {
        if (confirm('¿Deseas recargar la página para ver si el problema se ha solucionado?')) {
            location.reload();
        }
    }, 30000);
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>