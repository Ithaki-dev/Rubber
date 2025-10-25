<?php
$pageTitle = "Acceso Prohibido - Carpooling UTN";
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <!-- Error Icon -->
            <div class="mb-4">
                <i class="bi bi-shield-exclamation display-1 text-danger"></i>
            </div>
            
            <!-- Error Message -->
            <h1 class="display-4 fw-bold mb-3">403</h1>
            <h2 class="h4 mb-3">Acceso Prohibido</h2>
            <p class="lead text-muted mb-4">
                No tienes permisos para acceder a esta página o recurso.
            </p>
            
            <!-- Explanation -->
            <div class="alert alert-warning" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <strong>¿Por qué veo este mensaje?</strong><br>
                Puede que necesites iniciar sesión o que tu cuenta no tenga los permisos necesarios para esta página.
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-4">
                <button onclick="history.back()" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Regresar
                </button>
                <a href="<?= BASE_URL ?>" class="btn btn-primary">
                    <i class="bi bi-house-fill me-2"></i>Ir al Inicio
                </a>
                <?php if (!Session::isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/auth/login" class="btn btn-success">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Help Section -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">¿Necesitas ayuda?</h5>
                    <p class="card-text">
                        Si crees que deberías tener acceso a esta página, contacta a nuestro equipo de soporte.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-envelope me-2"></i>Contactar Soporte
                        </a>
                        <a href="mailto:soporte@utn.ac.cr" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-envelope-at me-2"></i>Enviar Email
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- User Status -->
            <?php if (Session::isLoggedIn()): ?>
                <?php $user = Session::getCurrentUser(); ?>
                <div class="mt-4">
                    <small class="text-muted">
                        Sesión iniciada como: <strong><?= htmlspecialchars($user['first_name']) ?></strong> 
                        (<?= ucfirst($user['role']) ?>)
                    </small>
                </div>
            <?php else: ?>
                <div class="mt-4">
                    <small class="text-muted">
                        No has iniciado sesión. <a href="<?= BASE_URL ?>/auth/login">Haz clic aquí para ingresar</a>
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>