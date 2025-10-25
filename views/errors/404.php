<?php
$pageTitle = "Página No Encontrada - Carpooling UTN";
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <!-- Error Icon -->
            <div class="mb-4">
                <i class="bi bi-exclamation-triangle-fill display-1 text-warning"></i>
            </div>
            
            <!-- Error Message -->
            <h1 class="display-4 fw-bold mb-3">404</h1>
            <h2 class="h4 mb-3">Página No Encontrada</h2>
            <p class="lead text-muted mb-4">
                Lo sentimos, la página que estás buscando no existe o ha sido movida.
            </p>
            
            <!-- Suggestions -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">¿Qué puedes hacer?</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-primary me-2"></i>
                            Verifica que la URL esté escrita correctamente
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-primary me-2"></i>
                            Regresa a la página anterior usando el botón de tu navegador
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-primary me-2"></i>
                            Ve a la página principal y navega desde ahí
                        </li>
                        <li>
                            <i class="bi bi-arrow-right text-primary me-2"></i>
                            Contacta a nuestro equipo si el problema persiste
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <button onclick="history.back()" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Regresar
                </button>
                <a href="<?= BASE_URL ?>" class="btn btn-primary">
                    <i class="bi bi-house-fill me-2"></i>Ir al Inicio
                </a>
                <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-secondary">
                    <i class="bi bi-envelope me-2"></i>Contactar Soporte
                </a>
            </div>
            
            <!-- Quick Links -->
            <div class="mt-5">
                <h6 class="text-muted mb-3">Enlaces Útiles</h6>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?= BASE_URL ?>/about" class="text-decoration-none">
                        <i class="bi bi-info-circle me-1"></i>Sobre Nosotros
                    </a>
                    <?php if (!Session::isLoggedIn()): ?>
                        <a href="<?= BASE_URL ?>/auth/login" class="text-decoration-none">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión
                        </a>
                        <a href="<?= BASE_URL ?>/auth/register" class="text-decoration-none">
                            <i class="bi bi-person-plus me-1"></i>Registrarse
                        </a>
                    <?php else: ?>
                        <?php $user = Session::getCurrentUser(); ?>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>/admin/dashboard" class="text-decoration-none">
                                <i class="bi bi-speedometer2 me-1"></i>Panel Admin
                            </a>
                        <?php elseif ($user['role'] === 'driver'): ?>
                            <a href="<?= BASE_URL ?>/driver/dashboard" class="text-decoration-none">
                                <i class="bi bi-car-front me-1"></i>Panel Conductor
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/passenger/dashboard" class="text-decoration-none">
                                <i class="bi bi-person me-1"></i>Panel Pasajero
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide after 10 seconds and redirect to home
    setTimeout(function() {
        const redirectConfirm = confirm('¿Deseas ser redirigido a la página principal?');
        if (redirectConfirm) {
            window.location.href = '<?= BASE_URL ?>';
        }
    }, 10000);
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>