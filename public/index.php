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
    // RUTAS API
    // ==========================================
    
    if ($controller === 'api') {
        header('Content-Type: application/json');
        
        if ($method === 'admin') {
            require_once __DIR__ . '/../controllers/AdminController.php';
            $adminController = new AdminController();
            
            $apiAction = $params[0] ?? '';
            $apiId = $params[1] ?? '';
            
            switch($apiAction) {
                case 'dashboard':
                    $adminController->apiDashboard();
                    break;
                    
                case 'users':
                    error_log("API Users route - Method: $httpMethod, ID: $apiId");
                    if ($httpMethod === 'GET') {
                        if (empty($apiId)) {
                            // GET /api/admin/users - Listar usuarios
                            $adminController->apiUsers();
                        } else {
                            // GET /api/admin/users/{id} - Usuario específico
                            $adminController->apiUser($apiId);
                        }
                    } elseif ($httpMethod === 'POST') {
                        if (empty($apiId)) {
                            // POST /api/admin/users - Crear usuario
                            error_log("Calling createUser for new user creation");
                            $adminController->createUser();
                        } else {
                            $subAction = $params[2] ?? '';
                            if ($subAction === 'toggle') {
                                // POST /api/admin/users/{id}/toggle - Cambiar estado
                                $adminController->apiToggleUserStatus($apiId);
                            } else {
                                // POST /api/admin/users/{id} - Actualizar usuario
                                $adminController->updateUser($apiId);
                            }
                        }
                    }
                    break;
                case 'rides':
                    error_log("API Rides route - Method: $httpMethod, ID: $apiId");
                    if ($httpMethod === 'GET') {
                        if (empty($apiId)) {
                            // GET /api/admin/rides - Listar viajes
                            $adminController->apiRides();
                        } else {
                            // GET /api/admin/rides/{id} - Usuario específico (no implementado)
                            echo json_encode(['success' => false, 'message' => 'Endpoint no implementado']);
                        }
                    } elseif ($httpMethod === 'POST') {
                        if (empty($apiId)) {
                            // POST /api/admin/rides - Crear viaje
                            $adminController->createRide();
                        } else {
                            $subAction = $params[2] ?? '';
                            if ($subAction === 'delete') {
                                // POST /api/admin/rides/{id}/delete
                                $adminController->deleteRide($apiId);
                            } else {
                                // POST /api/admin/rides/{id} - Actualizar viaje
                                $adminController->updateRide($apiId);
                            }
                        }
                    }
                    break;
                case 'vehicles':
                    error_log("API Vehicles route - Method: $httpMethod, ID: $apiId");
                    if ($httpMethod === 'GET') {
                        if (empty($apiId)) {
                            // GET /api/admin/vehicles - Listar vehículos
                            $adminController->apiVehicles();
                        } else {
                            // GET /api/admin/vehicles/{id} - Obtener vehículo
                            $adminController->apiVehicle($apiId);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Método no permitido para vehicles']);
                    }
                    break;
                    
                case 'settings':
                    if ($httpMethod === 'GET') {
                        // GET /api/admin/settings - Obtener configuraciones
                        $adminController->apiGetSettings();
                    } elseif ($httpMethod === 'POST') {
                        // POST /api/admin/settings - Actualizar configuraciones
                        $adminController->apiUpdateSettings();
                    }
                    break;
                    
                case 'reports':
                    if (!empty($apiId)) {
                        // GET /api/admin/reports/{type}
                        $adminController->apiReport($apiId);
                    }
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
                    break;
            }
            exit;
        }
        // Public API for rides (passenger-facing)
        elseif ($method === 'rides') {
            require_once __DIR__ . '/../controllers/PassengerController.php';
            $passengerController = new PassengerController();

            $apiAction = $params[0] ?? '';
            $apiId = $params[1] ?? '';

            // GET /api/rides[/available|/search]?bounds=...&date=...
            if ($httpMethod === 'GET') {
                // All GET actions map to apiRides which reads query params
                $passengerController->apiRides();
            } else {
                echo json_encode(['success' => false, 'message' => 'Método no permitido para esta API']);
            }

            exit;
        }
        // API for driver dashboard (driver-facing)
        elseif ($method === 'driver') {
            require_once __DIR__ . '/../controllers/DriverController.php';
            $driverController = new DriverController();

            $apiAction = $params[0] ?? '';
            $apiId = $params[1] ?? '';

            if ($httpMethod === 'GET') {
                switch ($apiAction) {
                    case 'stats':
                        $driverController->apiStats();
                        break;
                    case 'rides':
                        $driverController->apiRides();
                        break;
                    case 'vehicles':
                        $driverController->apiVehicles();
                        break;
                    default:
                        echo json_encode(['success' => false, 'message' => 'API driver endpoint not found']);
                        break;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Método no permitido para esta API']);
            }

            exit;
        }
    }
    
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
            
            case 'resend-activation':
                $authController->resendActivation();
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
                // POST /driver/vehicles - Crear
                if ($httpMethod === 'POST' && !isset($params[0])) {
                    $driverController->storeVehicle();
                }
                // GET /driver/vehicles/create
                elseif (isset($params[0]) && $params[0] === 'create') {
                    $driverController->createVehicle();
                }
                // Actions for a specific vehicle id: /driver/vehicles/{id}[/{action}]
                elseif (isset($params[0])) {
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
                        } else {
                            // Fallback: show vehicles list if GET without action
                            $driverController->vehicles();
                        }
                    }
                } else {
                    // GET /driver/vehicles - Listar
                    $driverController->vehicles();
                }
                break;
            
            // ==========================================
            // VIAJES
            // ==========================================
            case 'rides':
                // POST /driver/rides - Crear
                if ($httpMethod === 'POST' && !isset($params[0])) {
                    $driverController->storeRide();
                }
                // GET /driver/rides/create
                elseif (isset($params[0]) && $params[0] === 'create') {
                    $driverController->createRide();
                }
                // Actions for a specific ride id: /driver/rides/{id}[/{action}]
                elseif (isset($params[0])) {
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
                    $adminController->createUser();
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
                        // Manejar sub-rutas no numéricas (export) o ids
                        if ($params[0] === 'export') {
                            // GET /admin/users/export - Descargar CSV
                            $adminController->exportUsers();
                        } else {
                            // POST /admin/users/{id} - Actualizar
                            // GET /admin/users/{id} - Ver
                            if ($httpMethod === 'POST') {
                                $adminController->updateUser($params[0]);
                            } else {
                                $adminController->searchUserById($params[0]);
                            }
                        }
                    }
                } elseif (isset($url[2]) && $url[2] === 'create') {
                    // GET /admin/users/create
                    $adminController->showCreateUserForm();
                } else {
                    // GET /admin/users - Listar
                    $adminController->listUsers();
                }
                break;

            // ==========================================
            // DRIVERS (lista de choferes y vehículos por chofer)
            // Rutas antiguas: /admin/drivers and /admin/drivers/{id}/vehicles
            // ==========================================
            case 'drivers':
                if (isset($params[0])) {
                    if (isset($params[1]) && $params[1] === 'vehicles') {
                        // GET /admin/drivers/{id}/vehicles
                        $adminController->driverVehicles($params[0]);
                    } else {
                        // Not found
                        require_once __DIR__ . '/../controllers/HomeController.php';
                        $homeController = new HomeController();
                        $homeController->notFound();
                    }
                } else {
                    // GET /admin/drivers - Listar conductores activos
                    $adminController->drivers();
                }
                break;
            
            // ==========================================
            // VEHÍCULOS
            // ==========================================
            case 'vehicles':
                // POST /admin/vehicles - crear
                if ($httpMethod === 'POST' && !isset($params[0])) {
                    $adminController->createVehicle();
                    break;
                }

                if (isset($params[0])) {
                    if ($httpMethod === 'POST' && !isset($params[1])) {
                        // POST /admin/vehicles/{id} - actualizar
                        $adminController->updateVehicle($params[0]);
                        break;
                    }

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
