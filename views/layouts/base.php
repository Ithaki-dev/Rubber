<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Carpooling UTN' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/style.css" rel="stylesheet">
    <script>
        // Exponer BASE_URL al cliente lo antes posible para que scripts en el cuerpo puedan usarlo
        const BASE_URL = '<?= rtrim(BASE_URL, "\/") ?>';
    </script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>">
                <i class="bi bi-car-front-fill me-2"></i>
                Carpooling UTN
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/about">Sobre Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/contact">Contacto</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (Session::isLoggedIn()): ?>
                        <?php $user = Session::getCurrentUser(); ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars($user['first_name']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if ($user['user_type'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard">
                                        <i class="bi bi-speedometer2 me-2"></i>Panel Admin
                                    </a></li>
                                <?php elseif ($user['user_type'] === 'driver'): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/driver/dashboard">
                                        <i class="bi bi-speedometer2 me-2"></i>Panel Conductor
                                    </a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/passenger/dashboard">
                                        <i class="bi bi-speedometer2 me-2"></i>Panel Pasajero
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/profile">
                                    <i class="bi bi-person me-2"></i>Mi Perfil
                                </a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/auth/login">Ingresar</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="<?= BASE_URL ?>/auth/register">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-5 mt-3">
        <?php
            // Mostrar mensajes flash (compatibilidad con Session::setFlash)
            if (class_exists('Session')) {
                $flash = Session::getFlashMessage();
                if (!empty($flash) && isset($flash['type'], $flash['message'])) {
                    $type = $flash['type'];
                    // Sólo renderizamos tipos de alert conocidos; otros (old_input, etc.) se usan internamente
                    $allowedTypes = ['success', 'error', 'warning', 'info'];
                    if (in_array($type, $allowedTypes)) {
                        $msg = $flash['message'];
                        $alertClass = 'info';
                        $icon = 'bi-info-circle';

                        switch ($type) {
                            case 'success':
                                $alertClass = 'success';
                                $icon = 'bi-check-circle';
                                break;
                            case 'error':
                                $alertClass = 'danger';
                                $icon = 'bi-exclamation-triangle';
                                break;
                            case 'warning':
                                $alertClass = 'warning';
                                $icon = 'bi-exclamation-circle';
                                break;
                            case 'info':
                            default:
                                $alertClass = 'info';
                                $icon = 'bi-info-circle';
                                break;
                        }

                        echo '<div class="container">';
                        echo '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show" role="alert">';
                        echo '<i class="bi ' . $icon . ' me-2"></i>' . $msg;
                        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                        echo '</div></div>';
                    }
                }
            }
        ?>

        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-car-front-fill me-2"></i>Carpooling UTN</h5>
                    <p class="mb-0">Conectando estudiantes para viajes compartidos seguros y económicos.</p>
                </div>
                <div class="col-md-3">
                    <h6>Enlaces</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>/about" class="text-light text-decoration-none">Sobre Nosotros</a></li>
                        <li><a href="<?= BASE_URL ?>/contact" class="text-light text-decoration-none">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Soporte</h6>
                    <ul class="list-unstyled">
                        <li><span class="text-light">Email: soporte@utn.ac.cr</span></li>
                        <li><span class="text-light">Tel: (506) 2511-4000</span></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <small>&copy; <?= date('Y') ?> Ithakidev. Todos los derechos reservados.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/js/main.js"></script>
</body>
</html>