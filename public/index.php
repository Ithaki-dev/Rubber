<?php
/**
 * Router Principal - Front Controller
 * Punto de entrada único de la aplicación
 */

// Iniciar sesión
session_start();

// Configuración de errores (desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar configuración
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/email.php';

// Cargar core
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Helpers.php';

// Inicializar sesión (ya iniciada arriba, pero aseguramos)
Session::start();

// Obtener la URL solicitada
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Extraer componentes de la URL
$controller = !empty($url[0]) ? $url[0] : 'home';
$method = $url[1] ?? 'index';
$params = array_slice($url, 2);

// Manejo especial para método HTTP
$httpMethod = $_SERVER['REQUEST_METHOD'];

/**
 * ==========================================
 * ROUTER - MAPA DE RUTAS
 * ==========================================
 */

try {
    // ==========================================
    // RUTAS PÚBLICAS
    // ==========================================
    
    // Rutas especiales que van directo al HomeController
    if ($controller === 'home' || $controller === '' || 
        $controller === 'about' || $controller === 'contact' || 
        $controller === 'how-it-works') {
        
        require_once __DIR__ . '/../controllers/HomeController.php';
        $homeController = new HomeController();
        
        // Si es una ruta directa, ajustar controller y method
        if ($controller === 'about') {
            $controller = 'home';
            $method = 'about';
        } elseif ($controller === 'contact') {
            $controller = 'home';
            $method = 'contact';
        } elseif ($controller === 'how-it-works') {
            $controller = 'home';
            $method = 'how-it-works';
        }
        
    }
    
    if ($controller === 'home' || $controller === '') {
        require_once __DIR__ . '/../controllers/HomeController.php';
        $homeController = new HomeController();
        
        switch ($method) {
            case 'index':
            case '':
                $homeController->index();
                break;
            
            case 'search':
                $homeController->searchPublic();
                break;
            
            case 'ride':
                if (isset($params[0])) {
                    $homeController->showRide($params[0]);
                } else {
                    $homeController->notFound();
                }
                break;
            
            case 'how-it-works':
                $homeController->howItWorks();
                break;
            
            case 'about':
                $homeController->about();
                break;
            
            case 'contact':
                if ($httpMethod === 'POST') {
                    $homeController->sendContact();
                } else {
                    $homeController->contact();
                }
                break;
            
            default:
                $homeController->notFound();
                break;
        }
    }
    
    // ==========================================
    // RUTAS DE AUTENTICACIÓN
    // ==========================================
    
    elseif ($controller === 'auth') {
        require_once __DIR__ . '/../controllers/AuthController.php';
        $authController = new AuthController();
        
        switch ($method) {
            case 'register':
                if ($httpMethod === 'POST') {
                    $authController->register();
                } else {
                    $authController->showRegister();
                }
                break;
            
            case 'login':
                if ($httpMethod === 'POST') {
                    $authController->login();
                } else {
                    $authController->showLogin();
                }
                break;
            
            case 'logout':
                $authController->logout();
                break;
            
            case 'activate':
                $authController->activate();
                break;
            
            case 'forgot-password':
                if ($httpMethod === 'POST') {
                    $authController->forgotPassword();
                } else {
                    $authController->showForgotPassword();
                }
                break;
            
            case 'profile':
                if ($httpMethod === 'POST') {
                    $authController->updateProfile();
                } else {
                    $authController->showProfile();
                }
                break;
            
            default:
                require_once __DIR__ . '/../controllers/HomeController.php';
                $homeController = new HomeController();
                $homeController->notFound();
                break;
        }
    }
    
    // ==========================================
    // DASHBOARD GENÉRICO (redirige según rol)
    // ==========================================
    
    elseif ($controller === 'dashboard' && $method === 'index') {
        require_once __DIR__ . '/../controllers/HomeController.php';
        $homeController = new HomeController();
        $homeController->dashboard();
    }
    
    // ==========================================
    // RUTAS DEL PASAJERO
    // ==========================================
    
    elseif ($controller === 'passenger') {
        require_once __DIR__ . '/../controllers/PassengerController.php';
        $passengerController = new PassengerController();
        
        switch ($method) {
            case 'dashboard':
            case 'index':
                $passengerController->dashboard();
                break;
            
            case 'search':
                $passengerController->searchRides();
                break;
            
            case 'rides':
                if (isset($params[0])) {
                    $passengerController->showRide($params[0]);
                } else {
                    $passengerController->searchRides();
                }
                break;
            
            case 'reservations':
                if ($httpMethod === 'POST' && !isset($params[0])) {
                    // POST /passenger/reservations - Crear reserva
                    $passengerController->makeReservation();
                } elseif (isset($params[0])) {
                    if (isset($params[1]) && $params[1] === 'cancel') {
                        // POST /passenger/reservations/{id}/cancel
                        $passengerController->cancelReservation($params[0]);
                    } else {
                        // GET /passenger/reservations/{id}
                        $passengerController->showReservation($params[0]);
                    }
                } else {
                    // GET /passenger/reservations - Listar
                    $passengerController->reservations();
                }
                break;
            
            case 'history':
                $passengerController->history();
                break;
            
            default:
                require_once __DIR__ . '/../controllers/HomeController.php';
                $homeController = new HomeController();
                $homeController->notFound();
                break;
        }
    }
    
    // ==========================================
    // RUTAS DEL CHOFER
    // ==========================================
    
    elseif ($controller === 'driver') {
        require_once __DIR__ . '/../controllers/DriverController.php';
        $driverController = new DriverController();
        
        switch ($method) {
            case 'dashboard':
            case 'index':
                $driverController->dashboard();
                break;
            
            // ==========================================
            // VEHÍCULOS
            // ==========================================
            case 'vehicles':
                if ($httpMethod === 'POST' && !isset($params[0])) {
                    // POST /driver/vehicles - Crear
                    $driverController->storeVehicle();
                } elseif (isset($params[0])) {
                    if (isset($params[1])) {
                        // Acciones específicas
                        switch ($params[1]) {
                            case 'edit':
                                $driverController->editVehicle($params[0]);
                                break;
                            case 'delete':
                                $driverController->deleteVehicle($params[0]);
                                break;
                            case 'toggle':
                                $driverController->toggleVehicle($params[0]);
                                break;
                            default:
                                require_once __DIR__ . '/../controllers/HomeController.php';
                                $homeController = new HomeController();
                                $homeController->notFound();
                                break;
                        }
                    } else {
                        // POST /driver/vehicles/{id} - Actualizar
                        if ($httpMethod === 'POST') {
                            $driverController->updateVehicle($params[0]);
                        }
                    }
                } elseif (isset($url[2]) && $url[2] === 'create') {
                    // GET /driver/vehicles/create
                    $driverController->createVehicle();
                } else {
                    // GET /driver/vehicles - Listar
                    $driverController->vehicles();
                }
                break;
            
            // ==========================================
            // VIAJES
            // ==========================================
            case 'rides':
                if ($httpMethod === 'POST' && !isset($params[0])) {
                    // POST /driver/rides - Crear
                    $driverController->storeRide();
                } elseif (isset($params[0])) {
                    if (isset($params[1])) {
                        // Acciones específicas
                        switch ($params[1]) {
                            case 'edit':
                                $driverController->editRide($params[0]);
                                break;
                            case 'delete':
                                $driverController->deleteRide($params[0]);
                                break;
                            case 'toggle':
                                $driverController->toggleRide($params[0]);
                                break;
                            default:
                                require_once __DIR__ . '/../controllers/HomeController.php';
                                $homeController = new HomeController();
                                $homeController->notFound();
                                break;
                        }
                    } else {
                        // POST /driver/rides/{id} - Actualizar
                        // GET /driver/rides/{id} - Ver
                        if ($httpMethod === 'POST') {
                            $driverController->updateRide($params[0]);
                        } else {
                            $driverController->showRide($params[0]);
                        }
                    }
                } elseif (isset($url[2]) && $url[2] === 'create') {
                    // GET /driver/rides/create
                    $driverController->createRide();
                } else {
                    // GET /driver/rides - Listar
                    $driverController->rides();
                }
                break;
            
            // ==========================================
            // RESERVAS
            // ==========================================
            case 'reservations':
                if (isset($params[0]) && isset($params[1])) {
                    // Acciones específicas
                    switch ($params[1]) {
                        case 'accept':
                            $driverController->acceptReservation($params[0]);
                            break;
                        case 'reject':
                            $driverController->rejectReservation($params[0]);
                            break;
                        default:
                            require_once __DIR__ . '/../controllers/HomeController.php';
                            $homeController = new HomeController();
                            $homeController->notFound();
                            break;
                    }
                } else {
                    // GET /driver/reservations - Listar
                    $driverController->reservations();
                }
                break;
            
            default:
                require_once __DIR__ . '/../controllers/HomeController.php';
                $homeController = new HomeController();
                $homeController->notFound();
                break;
        }
    }
    
    // ==========================================
    // RUTAS DEL ADMIN
    // ==========================================
    
    elseif ($controller === 'admin') {
        require_once __DIR__ . '/../controllers/AdminController.php';
        $adminController = new AdminController();
        
        switch ($method) {
            case 'dashboard':
            case 'index':
                $adminController->dashboard();
                break;
            
            // ==========================================
            // USUARIOS
            // ==========================================
            case 'users':
                if ($httpMethod === 'POST' && !isset($params[0])) {
                    // POST /admin/users - Crear
                    $adminController->storeUser();
                } elseif (isset($params[0])) {
                    if (isset($params[1])) {
                        // Acciones específicas
                        switch ($params[1]) {
                            case 'edit':
                                $adminController->editUser($params[0]);
                                break;
                            case 'delete':
                                $adminController->deleteUser($params[0]);
                                break;
                            case 'status':
                                $adminController->changeUserStatus($params[0]);
                                break;
                            default:
                                require_once __DIR__ . '/../controllers/HomeController.php';
                                $homeController = new HomeController();
                                $homeController->notFound();
                                break;
                        }
                    } else {
                        // POST /admin/users/{id} - Actualizar
                        // GET /admin/users/{id} - Ver
                        if ($httpMethod === 'POST') {
                            $adminController->updateUser($params[0]);
                        } else {
                            $adminController->showUser($params[0]);
                        }
                    }
                } elseif (isset($url[2]) && $url[2] === 'create') {
                    // GET /admin/users/create
                    $adminController->createUser();
                } else {
                    // GET /admin/users - Listar
                    $adminController->users();
                }
                break;
            
            // ==========================================
            // VEHÍCULOS
            // ==========================================
            case 'vehicles':
                if (isset($params[0])) {
                    if (isset($params[1]) && $params[1] === 'delete') {
                        $adminController->deleteVehicle($params[0]);
                    } else {
                        $adminController->showVehicle($params[0]);
                    }
                } else {
                    $adminController->vehicles();
                }
                break;
            
            // ==========================================
            // VIAJES
            // ==========================================
            case 'rides':
                if (isset($params[0])) {
                    if (isset($params[1]) && $params[1] === 'delete') {
                        $adminController->deleteRide($params[0]);
                    } else {
                        $adminController->showRide($params[0]);
                    }
                } else {
                    $adminController->rides();
                }
                break;
            
            // ==========================================
            // RESERVAS
            // ==========================================
            case 'reservations':
                if (isset($params[0])) {
                    if (isset($params[1]) && $params[1] === 'delete') {
                        $adminController->deleteReservation($params[0]);
                    } else {
                        $adminController->showReservation($params[0]);
                    }
                } else {
                    $adminController->reservations();
                }
                break;
            
            // ==========================================
            // REPORTES
            // ==========================================
            case 'reports':
                $adminController->reports();
                break;
            
            default:
                require_once __DIR__ . '/../controllers/HomeController.php';
                $homeController = new HomeController();
                $homeController->notFound();
                break;
        }
    }
    
    // ==========================================
    // RUTA NO ENCONTRADA
    // ==========================================
    
    else {
        require_once __DIR__ . '/../controllers/HomeController.php';
        $homeController = new HomeController();
        $homeController->notFound();
    }
    
} catch (Exception $e) {
    // Manejo de errores global
    error_log('Error en router: ' . $e->getMessage());
    
    // En producción, mostrar página de error genérica
    require_once __DIR__ . '/../controllers/HomeController.php';
    $homeController = new HomeController();
    $homeController->serverError();
}
