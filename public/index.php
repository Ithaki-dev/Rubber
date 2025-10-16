<?php
/**
 * Punto de entrada de la aplicación
 * public/index.php
 */

// Iniciar output buffering
ob_start();

// Cargar configuración
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/email.php';

// Cargar autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar clases core
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Email.php';
require_once __DIR__ . '/../core/Helpers.php';

// Iniciar sesión
Session::start();

// Obtener la URL solicitada
$url = $_GET['url'] ?? 'home';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Enrutamiento simple
$page = $url[0] ?? 'home';
$action = $url[1] ?? 'index';

// Definir rutas
$routes = [
    // Páginas públicas
    'home' => __DIR__ . '/../views/public/home.php',
    'search' => __DIR__ . '/../views/public/search_rides.php',
    'about' => __DIR__ . '/../views/public/about.php',
    
    // Autenticación
    'login' => __DIR__ . '/../views/auth/login.php',
    'register' => __DIR__ . '/../views/auth/register_passenger.php',
    'register-driver' => __DIR__ . '/../views/auth/register_driver.php',
    'activate' => __DIR__ . '/../views/auth/activate.php',
    'logout' => __DIR__ . '/../controllers/AuthController.php',
    
    // Dashboard general (redirige según tipo de usuario)
    'dashboard' => __DIR__ . '/../views/dashboard.php',
    
    // Admin
    'admin' => __DIR__ . '/../views/admin/dashboard.php',
    'admin-users' => __DIR__ . '/../views/admin/users.php',
    
    // Driver
    'driver-dashboard' => __DIR__ . '/../views/driver/dashboard.php',
    'driver-vehicles' => __DIR__ . '/../views/driver/vehicles.php',
    'driver-rides' => __DIR__ . '/../views/driver/rides.php',
    'driver-reservations' => __DIR__ . '/../views/driver/reservations.php',
    'driver-profile' => __DIR__ . '/../views/driver/profile.php',
    
    // Passenger
    'passenger-dashboard' => __DIR__ . '/../views/passenger/dashboard.php',
    'passenger-search' => __DIR__ . '/../views/passenger/search.php',
    'passenger-reservations' => __DIR__ . '/../views/passenger/my_reservations.php',
    'passenger-profile' => __DIR__ . '/../views/passenger/profile.php',
];

// Buscar la ruta
$file = null;

// Verificar ruta exacta
if (isset($routes[$page])) {
    $file = $routes[$page];
} 
// Verificar ruta con acción
elseif (isset($routes[$page . '-' . $action])) {
    $file = $routes[$page . '-' . $action];
}
// Página por defecto
else {
    $file = $routes['home'];
}

// Cargar la página
if ($file && file_exists($file)) {
    // Manejo especial para logout
    if ($page === 'logout') {
        Session::destroy();
        redirect(BASE_URL . '/login');
    }
    
    require_once $file;
} else {
    // Página 404
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Página no encontrada</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .container {
                text-align: center;
                padding: 40px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 20px;
                backdrop-filter: blur(10px);
            }
            h1 {
                font-size: 120px;
                margin: 0;
            }
            p {
                font-size: 24px;
                margin: 20px 0;
            }
            a {
                display: inline-block;
                padding: 12px 30px;
                background: white;
                color: #667eea;
                text-decoration: none;
                border-radius: 25px;
                font-weight: bold;
                margin-top: 20px;
                transition: transform 0.3s;
            }
            a:hover {
                transform: scale(1.05);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>404</h1>
            <p>Página no encontrada</p>
            <p>Lo sentimos, la página que buscas no existe.</p>
            <a href="' . BASE_URL . '">Volver al inicio</a>
        </div>
    </body>
    </html>';
}

// Enviar output
ob_end_flush();
