<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling System - Inicio</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/components.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1>🚗 Carpooling</h1>
            <nav>
                <ul>
                    <li><a href="<?= BASE_URL ?>">Inicio</a></li>
                    <li><a href="<?= BASE_URL ?>/search">Buscar Viajes</a></li>
                    <?php if (Session::isLoggedIn()): ?>
                        <li><a href="<?= BASE_URL ?>/dashboard">Mi Panel</a></li>
                        <li><a href="<?= BASE_URL ?>/logout">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>/login">Iniciar Sesión</a></li>
                        <li><a href="<?= BASE_URL ?>/register">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Comparte tu viaje, ahorra dinero</h1>
            <p>La forma más fácil y económica de viajar por todo el país</p>
            <?php if (!Session::isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/register" class="btn btn-primary btn-lg">Regístrate Ahora</a>
                <a href="<?= BASE_URL ?>/search" class="btn btn-outline btn-lg">Buscar Viajes</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Search Box -->
    <div class="container">
        <div class="search-box">
            <h3>Encuentra tu viaje</h3>
            <form action="<?= BASE_URL ?>/search" method="GET">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="departure">Origen</label>
                            <input type="text" 
                                   id="departure" 
                                   name="departure" 
                                   class="form-control" 
                                   placeholder="¿Desde dónde viajas?">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="arrival">Destino</label>
                            <input type="text" 
                                   id="arrival" 
                                   name="arrival" 
                                   class="form-control" 
                                   placeholder="¿A dónde vas?">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    🔍 Buscar Viajes
                </button>
            </form>
        </div>
    </div>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="text-center mb-3">¿Por qué elegir Carpooling?</h2>
            <div class="row">
                <div class="col-4">
                    <div class="feature-item">
                        <div class="feature-icon">💰</div>
                        <h3 class="feature-title">Ahorra Dinero</h3>
                        <p class="feature-description">
                            Comparte los gastos del viaje y ahorra en combustible y peajes.
                        </p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="feature-item">
                        <div class="feature-icon">🌍</div>
                        <h3 class="feature-title">Cuida el Ambiente</h3>
                        <p class="feature-description">
                            Reduce tu huella de carbono compartiendo tu viaje con otros.
                        </p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="feature-item">
                        <div class="feature-icon">👥</div>
                        <h3 class="feature-title">Conoce Gente</h3>
                        <p class="feature-description">
                            Haz nuevos amigos y conexiones durante tus viajes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section style="background: #f4f4f4; padding: 60px 0;">
        <div class="container">
            <h2 class="text-center mb-3">¿Cómo funciona?</h2>
            <div class="row">
                <div class="col-3">
                    <div class="card text-center">
                        <div style="font-size: 3rem; color: var(--primary-color);">1️⃣</div>
                        <h4>Regístrate</h4>
                        <p>Crea tu cuenta como chofer o pasajero en minutos.</p>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card text-center">
                        <div style="font-size: 3rem; color: var(--primary-color);">2️⃣</div>
                        <h4>Publica o Busca</h4>
                        <p>Los choferes publican viajes, los pasajeros buscan.</p>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card text-center">
                        <div style="font-size: 3rem; color: var(--primary-color);">3️⃣</div>
                        <h4>Reserva</h4>
                        <p>Solicita tu espacio en el viaje que necesitas.</p>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card text-center">
                        <div style="font-size: 3rem; color: var(--primary-color);">4️⃣</div>
                        <h4>¡Viaja!</h4>
                        <p>Disfruta tu viaje compartido de forma segura.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <?php if (!Session::isLoggedIn()): ?>
    <section style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 60px 0; text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; margin-bottom: 20px;">¿Listo para comenzar?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 30px;">
                Únete a nuestra comunidad de viajeros hoy mismo
            </p>
            <a href="<?= BASE_URL ?>/register" class="btn btn-primary btn-lg" style="margin-right: 10px;">
                Registrarse como Pasajero
            </a>
            <a href="<?= BASE_URL ?>/register-driver" class="btn btn-outline btn-lg" style="border-color: white; color: white;">
                Registrarse como Chofer
            </a>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Carpooling System - Proyecto Universitario ISW-613</p>
            <p>Universidad Técnica Nacional</p>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>/js/main.js"></script>
</body>
</html>
