<?php
/**
 * AdminController
 * Gestiona funcionalidades del administrador: usuarios, vehículos, viajes, reservas
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Statistics.php';
require_once __DIR__ . '/../core/Helpers.php';

class AdminController {
    private $userModel;
    private $vehicleModel;
    private $rideModel;
    private $reservationModel;
    private $statistics;
    
    public function __construct() {
        $this->userModel = new User();
        $this->vehicleModel = new Vehicle();
        $this->rideModel = new Ride();
        $this->reservationModel = new Reservation();
        $this->statistics = new Statistics();
        
        // Verificar que el usuario es admin
        $this->requireAdminRole();
    }
    
    /**
     * Dashboard del administrador
     */
    public function dashboard() {
        // Obtener estadísticas generales
        $stats = [
            'total_users' => count($this->userModel->getAll()),
            'total_drivers' => count($this->userModel->getAll(['user_type' => 'driver', 'status' => 'active'])),
            'total_passengers' => count($this->userModel->getAll(['user_type' => 'passenger', 'status' => 'active'])),
            'pending_users' => count($this->userModel->getAll(['status' => 'pending'])),
            'total_vehicles' => count($this->vehicleModel->getAll()),
            'active_vehicles' => count($this->vehicleModel->getAll(['is_active' => 1])),
            'total_rides' => count($this->rideModel->getAll()),
            'active_rides' => count($this->rideModel->getAll(['is_active' => 1])),
            'total_reservations' => count($this->reservationModel->getAll()),
            'pending_reservations' => count($this->reservationModel->getAll(['status' => 'pending']))
        ];
        
        // Obtener actividad reciente
        $recent_users = array_slice($this->userModel->getAll(), 0, 5);
        $recent_reservations = array_slice($this->reservationModel->getAll(), 0, 10);
        
        // Cargar la vista (que internamente usa el layout base)
        include __DIR__ . '/../views/admin/dashboard.php';
    }
    
    // ==========================================
    // USUARIOS
    // ==========================================
    
    /**
     * Listar usuarios
     */
    public function users() {
        $filter_type = sanitize($_GET['type'] ?? '');
        $filter_status = sanitize($_GET['status'] ?? '');
        $search = sanitize($_GET['search'] ?? '');
        
        $filters = [];
        if ($filter_type) $filters['user_type'] = $filter_type;
        if ($filter_status) $filters['status'] = $filter_status;
        if ($search) $filters['search'] = $search;
        
        $users = $this->userModel->getAll($filters);
        
        require_once __DIR__ . '/../views/admin/users/index.php';
    }
    
    /**
     * Ver detalles de usuario
     */
    public function showUser($id) {
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            Session::setFlash('error', 'Usuario no encontrado');
            redirect('/admin/users');
            return;
        }
        
        // Obtener datos adicionales según tipo de usuario
        $additional_data = [];
        
        if ($user['user_type'] === 'driver') {
            $additional_data['vehicles'] = $this->vehicleModel->getByDriver($id);
            $additional_data['rides'] = $this->rideModel->getByDriver($id);
            $additional_data['stats'] = $this->statistics->getDriverStatistics($id);
        } elseif ($user['user_type'] === 'passenger') {
            $additional_data['reservations'] = $this->reservationModel->getByPassenger($id);
            $additional_data['stats'] = $this->statistics->getPassengerStatistics($id);
        }
        
        require_once __DIR__ . '/../views/admin/users/show.php';
    }
    
    /**
     * Mostrar formulario para crear usuario
     */
    public function createUser() {
        require_once __DIR__ . '/../views/admin/users/create.php';
    }
    
    /**
     * Guardar nuevo usuario
     */
    public function storeUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/users');
            return;
        }
        
        $data = [
            'user_type' => sanitize($_POST['user_type'] ?? ''),
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'cedula' => sanitize($_POST['cedula'] ?? ''),
            'birth_date' => sanitize($_POST['birth_date'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];
        
        // Manejar foto
        if (!empty($_FILES['photo']['name'])) {
            $upload_result = uploadFile($_FILES['photo'], 'users');
            if ($upload_result['success']) {
                $data['photo_path'] = $upload_result['path'];
            }
        }
        
        $result = $this->userModel->create($data);
        
        if ($result['success']) {
            // Admin puede activar directamente
            $this->userModel->changeStatus($result['user_id'], 'active');
            
            Session::setFlash('success', 'Usuario creado exitosamente');
            redirect('/admin/users');
        } else {
            Session::setFlash('error', $result['message']);
            Session::setFlash('old_input', $data);
            redirect('/admin/users/create');
        }
    }
    
    /**
     * Mostrar formulario para editar usuario
     */
    public function editUser($id) {
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            Session::setFlash('error', 'Usuario no encontrado');
            redirect('/admin/users');
            return;
        }
        
        require_once __DIR__ . '/../views/admin/users/edit.php';
    }
    
    /**
     * Actualizar usuario
     */
    public function updateUser($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/users');
            return;
        }
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            Session::setFlash('error', 'Usuario no encontrado');
            redirect('/admin/users');
            return;
        }
        
        $data = [
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'cedula' => sanitize($_POST['cedula'] ?? ''),
            'birth_date' => sanitize($_POST['birth_date'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'status' => sanitize($_POST['status'] ?? '')
        ];
        
        // Nueva contraseña (opcional)
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        // Nueva foto (opcional)
        if (!empty($_FILES['photo']['name'])) {
            $upload_result = uploadFile($_FILES['photo'], 'users');
            if ($upload_result['success']) {
                $data['photo_path'] = $upload_result['path'];
                if (!empty($user['photo_path'])) {
                    deleteFile($user['photo_path']);
                }
            }
        }
        
        $result = $this->userModel->update($id, $data);
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            redirect('/admin/users');
        } else {
            Session::setFlash('error', $result['message']);
            redirect('/admin/users/edit/' . $id);
        }
    }
    
    /**
     * Eliminar usuario
     */
    public function deleteUser($id) {
        // No permitir eliminar el admin actual
        if ($id == Session::get('user_id')) {
            Session::setFlash('error', 'No puedes eliminar tu propia cuenta');
            redirect('/admin/users');
            return;
        }
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            Session::setFlash('error', 'Usuario no encontrado');
            redirect('/admin/users');
            return;
        }
        
        $result = $this->userModel->delete($id);
        
        if ($result['success']) {
            // Eliminar foto si existe
            if (!empty($user['photo_path'])) {
                deleteFile($user['photo_path']);
            }
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        redirect('/admin/users');
    }
    
    /**
     * Cambiar estado de usuario
     */
    public function changeUserStatus($id) {
        $new_status = sanitize($_POST['status'] ?? '');
        $result = $this->userModel->changeStatus($id, $new_status);
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        redirect('/admin/users');
    }
    
    // ==========================================
    // VEHÍCULOS
    // ==========================================
    
    /**
     * Listar vehículos
     */
    public function vehicles() {
        $search = sanitize($_GET['search'] ?? '');
        $filter_active = isset($_GET['active']) ? (int)$_GET['active'] : null;
        
        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($filter_active !== null) $filters['is_active'] = $filter_active;
        
        $vehicles = $this->vehicleModel->getAll($filters);
        
        require_once __DIR__ . '/../views/admin/vehicles/index.php';
    }
    
    /**
     * Ver detalles de vehículo
     */
    public function showVehicle($id) {
        $vehicle = $this->vehicleModel->findById($id);
        
        if (!$vehicle) {
            Session::setFlash('error', 'Vehículo no encontrado');
            redirect('/admin/vehicles');
            return;
        }
        
        $rides = $this->rideModel->getAll(['vehicle_id' => $id]);
        $stats = $this->vehicleModel->getStatistics($id);
        
        require_once __DIR__ . '/../views/admin/vehicles/show.php';
    }
    
    /**
     * Eliminar vehículo
     */
    public function deleteVehicle($id) {
        $vehicle = $this->vehicleModel->findById($id);
        if (!$vehicle) {
            Session::setFlash('error', 'Vehículo no encontrado');
            redirect('/admin/vehicles');
            return;
        }
        
        $result = $this->vehicleModel->delete($id);
        
        if ($result['success']) {
            if (!empty($vehicle['photo_path'])) {
                deleteFile($vehicle['photo_path']);
            }
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        redirect('/admin/vehicles');
    }
    
    // ==========================================
    // VIAJES
    // ==========================================
    
    /**
     * Listar viajes
     */
    public function rides() {
        $search = sanitize($_GET['search'] ?? '');
        $filter_active = isset($_GET['active']) ? (int)$_GET['active'] : null;
        
        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($filter_active !== null) $filters['is_active'] = $filter_active;
        
        $rides = $this->rideModel->getAll($filters);
        
        require_once __DIR__ . '/../views/admin/rides/index.php';
    }
    
    /**
     * Ver detalles de viaje
     */
    public function showRide($id) {
        $ride = $this->rideModel->findById($id);
        
        if (!$ride) {
            Session::setFlash('error', 'Viaje no encontrado');
            redirect('/admin/rides');
            return;
        }
        
        $reservations = $this->rideModel->getReservations($id);
        
        require_once __DIR__ . '/../views/admin/rides/show.php';
    }
    
    /**
     * Eliminar viaje
     */
    public function deleteRide($id) {
        $result = $this->rideModel->delete($id);
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        redirect('/admin/rides');
    }
    
    // ==========================================
    // RESERVAS
    // ==========================================
    
    /**
     * Listar reservas
     */
    public function reservations() {
        $search = sanitize($_GET['search'] ?? '');
        $filter_status = sanitize($_GET['status'] ?? '');
        
        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($filter_status) $filters['status'] = $filter_status;
        
        $reservations = $this->reservationModel->getAll($filters);
        
        require_once __DIR__ . '/../views/admin/reservations/index.php';
    }
    
    /**
     * Ver detalles de reserva
     */
    public function showReservation($id) {
        $reservation = $this->reservationModel->findById($id);
        
        if (!$reservation) {
            Session::setFlash('error', 'Reserva no encontrada');
            redirect('/admin/reservations');
            return;
        }
        
        require_once __DIR__ . '/../views/admin/reservations/show.php';
    }
    
    /**
     * Eliminar reserva
     */
    public function deleteReservation($id) {
        $result = $this->reservationModel->delete($id);
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        redirect('/admin/reservations');
    }
    
    // ==========================================
    // REPORTES Y ESTADÍSTICAS
    // ==========================================
    
    /**
     * Página de reportes y estadísticas
     */
    public function reports() {
        // Estadísticas globales
        $global_stats = [
            'total_users' => count($this->userModel->getAll()),
            'total_rides' => count($this->rideModel->getAll()),
            'total_reservations' => count($this->reservationModel->getAll()),
            'reservation_stats' => $this->reservationModel->getStatistics()
        ];
        
        // Top choferes
        $drivers = $this->userModel->getAll(['user_type' => 'driver', 'status' => 'active']);
        $driver_stats = [];
        foreach ($drivers as $driver) {
            $stats = $this->statistics->getDriverStatistics($driver['id']);
            $driver_stats[] = [
                'driver' => $driver,
                'stats' => $stats
            ];
        }
        
        // Ordenar por total de viajes
        usort($driver_stats, function($a, $b) {
            return $b['stats']['total_rides'] - $a['stats']['total_rides'];
        });
        
        $top_drivers = array_slice($driver_stats, 0, 10);
        
        require_once __DIR__ . '/../views/admin/reports.php';
    }
    
    /**
     * Verificar que el usuario es admin
     */
    private function requireAdminRole() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Debes iniciar sesión');
            redirect('/auth/login');
            exit;
        }
        
        if (Session::get('user_type') !== 'admin') {
            Session::setFlash('error', 'No tienes permiso para acceder a esta página');
            redirect('/dashboard');
            exit;
        }
    }
    
    // ==========================================
    // MÉTODOS API
    // ==========================================
    
    /**
     * API: Obtener estadísticas del dashboard
     */
    public function apiDashboard() {
        $this->requireAdminRole();
        
        try {
            // Estadísticas principales (KPIs)
            $kpis = [
                'totalUsers' => $this->userModel->getTotalCount(),
                'totalRides' => $this->rideModel->getTotalCount(),
                'activeReservations' => $this->reservationModel->getActiveCount(),
                'totalRevenue' => $this->reservationModel->getTotalRevenue()
            ];
            
            // Estadísticas rápidas
            $stats = [
                'todayRides' => $this->rideModel->getTodayCount(),
                'newUsers' => $this->userModel->getNewUsersCount(30), // últimos 30 días
                'avgRating' => 4.5, // TODO: implementar sistema de calificaciones
                'activeDrivers' => $this->userModel->getActiveDriversCount()
            ];
            
            // Actividad reciente (últimos 10 eventos)
            $activity = [
                ['icon' => 'person-plus', 'type' => 'success', 'message' => 'Nuevo usuario registrado', 'time' => '2 min'],
                ['icon' => 'car-front', 'type' => 'info', 'message' => 'Viaje creado', 'time' => '5 min'],
                ['icon' => 'calendar-check', 'type' => 'primary', 'message' => 'Reserva confirmada', 'time' => '10 min']
            ];
            
            echo json_encode([
                'success' => true,
                'kpis' => $kpis,
                'stats' => $stats,
                'activity' => $activity
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cargar estadísticas: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * API: Listar usuarios con filtros opcionales
     */
    public function apiUsers() {
        $this->requireAdminRole();
        
        try {
            // Obtener filtros de query string
            $filters = [
                'user_type' => $_GET['role'] ?? '',
                'status' => $_GET['status'] ?? '',
                'search' => $_GET['search'] ?? ''
            ];
            
            // Remover filtros vacíos
            $filters = array_filter($filters);
            
            // Obtener usuarios
            $users = $this->userModel->getAll($filters);
            
            // Formatear datos para la tabla
            $formattedUsers = array_map(function($user) {
                return [
                    'id' => $user['id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['user_type'],
                    'is_active' => $user['status'] === 'active',
                    'created_at' => date('d/m/Y', strtotime($user['created_at']))
                ];
            }, $users);
            
            echo json_encode([
                'success' => true,
                'users' => $formattedUsers
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cargar usuarios: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * API: Obtener usuario específico
     */
    public function apiUser($id) {
        $this->requireAdminRole();
        
        try {
            $user = $this->userModel->getById($id);
            
            if (!$user) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);
                return;
            }
            
            // No devolver información sensible
            unset($user['password_hash']);
            unset($user['activation_token']);
            
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cargar usuario: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * API: Generar reporte específico
     */
    public function apiReport($type) {
        $this->requireAdminRole();
        
        try {
            $report = null;
            
            switch($type) {
                case 'users':
                    $report = [
                        'title' => 'Reporte de Usuarios',
                        'description' => 'Estadísticas completas de usuarios registrados',
                        'downloadUrl' => BASE_URL . '/admin/exports/users.pdf'
                    ];
                    break;
                    
                case 'rides':
                    $report = [
                        'title' => 'Reporte de Viajes',
                        'description' => 'Análisis de viajes realizados y estadísticas',
                        'downloadUrl' => BASE_URL . '/admin/exports/rides.pdf'
                    ];
                    break;
                    
                case 'revenue':
                    $report = [
                        'title' => 'Reporte Financiero',
                        'description' => 'Ingresos y transacciones del sistema',
                        'downloadUrl' => BASE_URL . '/admin/exports/revenue.pdf'
                    ];
                    break;
                    
                case 'activity':
                    $report = [
                        'title' => 'Reporte de Actividad',
                        'description' => 'Log de actividades y uso del sistema',
                        'downloadUrl' => BASE_URL . '/admin/exports/activity.pdf'
                    ];
                    break;
                    
                default:
                    echo json_encode([
                        'success' => false,
                        'message' => 'Tipo de reporte no válido'
                    ]);
                    return;
            }
            
            echo json_encode([
                'success' => true,
                'report' => $report
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ]);
        }
    }
}
