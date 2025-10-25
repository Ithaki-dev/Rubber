<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Carpooling UTN' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/style.css" rel="stylesheet">
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
                                <?php if ($user['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard">
                                        <i class="bi bi-speedometer2 me-2"></i>Panel Admin
                                    </a></li>
                                <?php elseif ($user['role'] === 'driver'): ?>
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="container">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="container">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

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
                <small>&copy; <?= date('Y') ?> Universidad Técnica Nacional. Todos los derechos reservados.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/js/main.js"></script>
</body>
</html>