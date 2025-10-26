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
    public function listUsers() {
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
     * Exportar usuarios a CSV (descarga)
     * URL: /admin/users/export
     */
    public function exportUsers() {
        // Asegurar permisos
        $this->requireAdminRole();

        // Obtener filtros de query string
        $filters = [];
        $role = sanitize($_GET['role'] ?? $_GET['user_type'] ?? '');
        $status = sanitize($_GET['status'] ?? '');
        $search = sanitize($_GET['search'] ?? '');

        if ($role) $filters['user_type'] = $role;
        if ($status) $filters['status'] = $status;
        if ($search) $filters['search'] = $search;

        // Obtener usuarios
        $users = $this->userModel->getAll($filters);

        // Forzar descarga CSV
        $filename = 'users_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // BOM para Excel (opcional)
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        // Cabecera CSV
        fputcsv($out, ['ID', 'Nombre', 'Apellido', 'Email', 'Tipo', 'Estado', 'Teléfono', 'Cédula', 'Creado', 'Último Acceso']);

        foreach ($users as $u) {
            $row = [
                $u['id'],
                $u['first_name'] ?? '',
                $u['last_name'] ?? '',
                $u['email'] ?? '',
                $u['user_type'] ?? '',
                $u['status'] ?? '',
                $u['phone'] ?? '',
                $u['cedula'] ?? '',
                isset($u['created_at']) ? $u['created_at'] : '',
                isset($u['last_login']) ? $u['last_login'] : ''
            ];
            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }
    
    /**
     * Buscar y mostrar detalles de usuario específico
     */
    public function searchUserById($id) {
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
     * Crear nuevo usuario (híbrido: HTML + AJAX)
     */
    public function createUser() {
        // Log para debug
        error_log("=== createUser START ===");
        error_log("Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'true' : 'false'));
        error_log("POST data: " . json_encode($_POST));
        error_log("FILES data: " . json_encode(array_keys($_FILES)));
        
        // Limpiar cualquier output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Si es una petición AJAX, devolver JSON
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Método no permitido'
                ]);
                return;
            }
            redirect('/admin/users');
            return;
        }
        
        // Detectar si los datos vienen del dashboard (nombres diferentes)
        $isFromDashboard = isset($_POST['userType']) || isset($_POST['firstName']);
        
        // Preparar datos básicos
        $data = [
            'user_type' => sanitize($_POST[$isFromDashboard ? 'userType' : 'user_type'] ?? ''),
            'first_name' => sanitize($_POST[$isFromDashboard ? 'firstName' : 'first_name'] ?? ''),
            'last_name' => sanitize($_POST[$isFromDashboard ? 'lastName' : 'last_name'] ?? ''),
            'cedula' => sanitize($_POST[$isFromDashboard ? 'userCedula' : 'cedula'] ?? ''),
            'email' => sanitize($_POST[$isFromDashboard ? 'userEmail' : 'email'] ?? ''),
            'phone' => sanitize($_POST[$isFromDashboard ? 'userPhone' : 'phone'] ?? ''),
            'password' => $_POST[$isFromDashboard ? 'userPassword' : 'password'] ?? ''
        ];
        
        // Manejar fecha de nacimiento (puede ser NULL)
        $birthDate = sanitize($_POST[$isFromDashboard ? 'birthDate' : 'birth_date'] ?? '');
        if (!empty($birthDate)) {
            $data['birth_date'] = $birthDate;
        }
        
        // Si viene del dashboard, usar el status seleccionado
        if ($isFromDashboard && isset($_POST['userStatus'])) {
            $selectedStatus = sanitize($_POST['userStatus']);
        } else {
            $selectedStatus = 'active'; // Default para admin
        }
        
        // Manejar foto
        $photoField = $isFromDashboard ? 'userPhoto' : 'photo';
        if (!empty($_FILES[$photoField]['name'])) {
            error_log("Photo upload attempted but uploadFile function not implemented yet");
            // TODO: Implement uploadFile function
            // $upload_result = uploadFile($_FILES[$photoField], 'users');
            // if ($upload_result['success']) {
            //     $data['photo_path'] = $upload_result['path'];
            // }
        }
        
        // Validaciones
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
            $message = 'Los campos nombre, apellido y email son obligatorios';
            if ($this->isAjaxRequest()) {
                error_log("Validation error: " . $message);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
                exit;
            }
            Session::setFlash('error', $message);
            Session::setFlash('old_input', $data);
            redirect('/admin/users/create');
            return;
        }
        
        if (empty($data['password']) || strlen($data['password']) < 6) {
            $message = 'La contraseña debe tener al menos 6 caracteres';
            if ($this->isAjaxRequest()) {
                error_log("Password validation error: " . $message);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
                exit;
            }
            Session::setFlash('error', $message);
            Session::setFlash('old_input', $data);
            redirect('/admin/users/create');
            return;
        }
        
        $result = $this->userModel->create($data);
        
        if ($result['success']) {
            // Admin puede cambiar el estado directamente
            $this->userModel->changeStatus($result['user_id'], $selectedStatus);
            
            if ($this->isAjaxRequest()) {
                error_log("Sending success JSON response");
                header('Content-Type: application/json; charset=utf-8');
                $response = [
                    'success' => true,
                    'message' => 'Usuario creado exitosamente',
                    'user_id' => $result['user_id']
                ];
                error_log("Response: " . json_encode($response));
                echo json_encode($response);
                exit;
            }
            
            Session::setFlash('success', 'Usuario creado exitosamente');
            redirect('/admin/users');
        } else {
            if ($this->isAjaxRequest()) {
                error_log("Sending error JSON response: " . $result['message']);
                header('Content-Type: application/json; charset=utf-8');
                $response = [
                    'success' => false,
                    'message' => $result['message']
                ];
                error_log("Error response: " . json_encode($response));
                echo json_encode($response);
                exit;
            }
            
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
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Método no permitido'
                ]);
                return;
            }
            redirect('/admin/users');
            return;
        }
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            $message = 'Usuario no encontrado';
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
                return;
            }
            Session::setFlash('error', $message);
            redirect('/admin/users');
            return;
        }
        
        // Detectar si los datos vienen del dashboard (nombres diferentes)
        $isFromDashboard = isset($_POST['userType']) || isset($_POST['firstName']);
        
        // Preparar datos básicos
        $data = [
            'user_type' => sanitize($_POST[$isFromDashboard ? 'userType' : 'user_type'] ?? ''),
            'first_name' => sanitize($_POST[$isFromDashboard ? 'firstName' : 'first_name'] ?? ''),
            'last_name' => sanitize($_POST[$isFromDashboard ? 'lastName' : 'last_name'] ?? ''),
            'cedula' => sanitize($_POST[$isFromDashboard ? 'userCedula' : 'cedula'] ?? ''),
            'email' => sanitize($_POST[$isFromDashboard ? 'userEmail' : 'email'] ?? ''),
            'phone' => sanitize($_POST[$isFromDashboard ? 'userPhone' : 'phone'] ?? ''),
            'status' => sanitize($_POST[$isFromDashboard ? 'userStatus' : 'status'] ?? '')
        ];
        
        // Manejar fecha de nacimiento (puede ser NULL)
        $birthDate = sanitize($_POST[$isFromDashboard ? 'birthDate' : 'birth_date'] ?? '');
        if (!empty($birthDate)) {
            $data['birth_date'] = $birthDate;
        } else {
            $data['birth_date'] = null;
        }
        
        // Nueva contraseña (opcional)
        $passwordField = $isFromDashboard ? 'userPassword' : 'password';
        if (!empty($_POST[$passwordField])) {
            if (strlen($_POST[$passwordField]) < 6) {
                $message = 'La contraseña debe tener al menos 6 caracteres';
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => $message
                    ]);
                    return;
                }
                Session::setFlash('error', $message);
                redirect('/admin/users/edit/' . $id);
                return;
            }
            $data['password'] = $_POST[$passwordField];
        }
        
        // Nueva foto (opcional)
        $photoField = $isFromDashboard ? 'userPhoto' : 'photo';
        if (!empty($_FILES[$photoField]['name'])) {
            $upload_result = uploadFile($_FILES[$photoField], 'users');
            if ($upload_result['success']) {
                $data['photo_path'] = $upload_result['path'];
                if (!empty($user['photo_path'])) {
                    deleteFile($user['photo_path']);
                }
            }
        }
        
        // Validaciones básicas
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
            $message = 'Los campos nombre, apellido y email son obligatorios';
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
                return;
            }
            Session::setFlash('error', $message);
            redirect('/admin/users/edit/' . $id);
            return;
        }
        
        $result = $this->userModel->update($id, $data);
        
        if ($result['success']) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente'
                ]);
                return;
            }
            Session::setFlash('success', $result['message']);
            redirect('/admin/users');
        } else {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
                return;
            }
            Session::setFlash('error', $result['message']);
            redirect('/admin/users/edit/' . $id);
        }
    }
    
    /**
     * Eliminar usuario
     */
    public function deleteUser($id) {
        error_log("=== deleteUser START ===");
        error_log("User ID to delete: " . $id);
        error_log("Current user ID: " . Session::get('user_id'));
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'true' : 'false'));
        
        // Limpiar cualquier output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        // No permitir eliminar el admin actual
        if ($id == Session::get('user_id')) {
            $message = 'No puedes eliminar tu propia cuenta';
            error_log("Trying to delete own account: " . $message);
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
                exit;
            }
            Session::setFlash('error', $message);
            redirect('/admin/users');
            return;
        }
        
        $user = $this->userModel->findById($id);
        error_log("User found: " . ($user ? json_encode($user) : 'null'));
        
        if (!$user) {
            $message = 'Usuario no encontrado';
            error_log("User not found: " . $message);
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
                exit;
            }
            Session::setFlash('error', $message);
            redirect('/admin/users');
            return;
        }
        
        error_log("Attempting to delete user: " . json_encode($user));
        $result = $this->userModel->delete($id);
        error_log("Delete result: " . json_encode($result));
        
        if ($result['success']) {
            // Eliminar foto si existe
            if (!empty($user['photo_path'])) {
                error_log("Deleting photo: " . $user['photo_path']);
                deleteFile($user['photo_path']);
            }
            
            if ($this->isAjaxRequest()) {
                error_log("Sending success JSON response");
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'message' => $result['message']
                ]);
                exit;
            }
            Session::setFlash('success', $result['message']);
        } else {
            error_log("Delete failed: " . $result['message']);
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
                exit;
            }
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
        error_log("=== admin deleteRide START === id: $id");
        // Limpiar output buffer
        if (ob_get_level()) ob_clean();

        $result = $this->rideModel->delete($id);

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
            exit;
        }

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        redirect('/admin/rides');
    }

    /**
     * API: Listar viajes (JSON)
     */
    public function apiRides() {
        $this->requireAdminRole();

        try {
            $filters = [
                'search' => $_GET['search'] ?? '',
                'is_active' => isset($_GET['active']) ? (int)$_GET['active'] : null
            ];
            $filters = array_filter($filters, function($v){ return $v !== null && $v !== ''; });

            $rides = $this->rideModel->getAll($filters);

            $formatted = array_map(function($r) {
                return [
                    'ride_id' => $r['id'],
                    'ride_name' => $r['ride_name'],
                    'driver_id' => $r['driver_id'] ?? null,
                    'vehicle_id' => $r['vehicle_id'] ?? null,
                    'departure_location' => $r['departure_location'] ?? '',
                    'arrival_location' => $r['arrival_location'] ?? '',
                    'ride_date' => $r['ride_date'] ?? '',
                    'ride_time' => $r['ride_time'] ?? '',
                    'cost_per_seat' => $r['cost_per_seat'] ?? 0,
                    'total_seats' => $r['total_seats'] ?? 0,
                    'is_active' => isset($r['is_active']) ? (bool)$r['is_active'] : true
                ];
            }, $rides);

            echo json_encode(['success' => true, 'rides' => $formatted]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al cargar viajes: ' . $e->getMessage()]);
        }
    }

    /**
     * Crear viaje (admin) - acepta POST
     */
    public function createRide() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('/admin/rides');
                return;
            }

            // Log input for debugging
            error_log('createRide POST data: ' . json_encode($_POST));

            $data = [
                'driver_id' => sanitize($_POST['driver_id'] ?? ''),
                'vehicle_id' => sanitize($_POST['vehicle_id'] ?? ''),
                'ride_name' => sanitize($_POST['ride_name'] ?? ''),
                'departure_location' => sanitize($_POST['departure_location'] ?? ''),
                'arrival_location' => sanitize($_POST['arrival_location'] ?? ''),
                'ride_date' => sanitize($_POST['ride_date'] ?? ''),
                'ride_time' => sanitize($_POST['ride_time'] ?? ''),
                'cost_per_seat' => sanitize($_POST['cost_per_seat'] ?? ''),
                'total_seats' => sanitize($_POST['total_seats'] ?? '')
            ];

            $result = $this->rideModel->create($data);

            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($result);
                exit;
            }

            if ($result['success']) {
                Session::setFlash('success', $result['message']);
                redirect('/admin/rides');
            } else {
                Session::setFlash('error', $result['message']);
                Session::setFlash('old_input', $data);
                redirect('/admin/rides');
            }
        } catch (Exception $e) {
            error_log('Exception in createRide: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
                exit;
            }
            Session::setFlash('error', 'Error interno al crear viaje');
            redirect('/admin/rides');
        }
    }

    /**
     * Actualizar viaje (admin)
     */
    public function updateRide($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('/admin/rides');
                return;
            }

            error_log('updateRide POST data: id=' . $id . ' data=' . json_encode($_POST));

            $data = [
                'ride_name' => sanitize($_POST['ride_name'] ?? ''),
                'departure_location' => sanitize($_POST['departure_location'] ?? ''),
                'arrival_location' => sanitize($_POST['arrival_location'] ?? ''),
                'ride_date' => sanitize($_POST['ride_date'] ?? ''),
                'ride_time' => sanitize($_POST['ride_time'] ?? ''),
                'cost_per_seat' => sanitize($_POST['cost_per_seat'] ?? ''),
                'total_seats' => sanitize($_POST['total_seats'] ?? '')
            ];

            $result = $this->rideModel->update($id, $data);

            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($result);
                exit;
            }

            if ($result['success']) {
                Session::setFlash('success', $result['message']);
                redirect('/admin/rides');
            } else {
                Session::setFlash('error', $result['message']);
                redirect('/admin/rides');
            }
        } catch (Exception $e) {
            error_log('Exception in updateRide: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
                exit;
            }
            Session::setFlash('error', 'Error interno al actualizar viaje');
            redirect('/admin/rides');
        }
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
    
    /**
     * Detectar si es una petición AJAX
     */
    private function isAjaxRequest() {
        // Verificar si la petición viene de una API
        $isApiRequest = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
        
        // Verificar headers AJAX
        $isXmlHttpRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        // Verificar Content-Type JSON
        $isJsonRequest = isset($_SERVER['CONTENT_TYPE']) && 
                        strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
        
        // Parámetro de formato
        $hasJsonFormat = isset($_GET['format']) && $_GET['format'] === 'json';
        
        $result = $isApiRequest || $isXmlHttpRequest || $isJsonRequest || $hasJsonFormat;
        
        error_log("isAjaxRequest check - API: $isApiRequest, XMLHttp: $isXmlHttpRequest, JSON: $isJsonRequest, Format: $hasJsonFormat, Result: $result");
        
        return $result;
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
                    'user_id' => $user['id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'user_type' => $user['user_type'],
                    'status' => $user['status'],
                    'phone' => $user['phone'] ?? '',
                    'cedula' => $user['cedula'] ?? '',
                    'created_at' => $user['created_at'],
                    'last_login' => $user['last_login'] ?? null
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
     * Listar choferes activos (JSON)
     * Ruta: GET /admin/drivers
     */
    public function drivers() {
        $this->requireAdminRole();

        try {
            $drivers = $this->userModel->getAll(['user_type' => 'driver', 'status' => 'active']);
            $formatted = array_map(function($d) {
                return [
                    'id' => $d['id'],
                    'first_name' => $d['first_name'],
                    'last_name' => $d['last_name'],
                    'email' => $d['email'] ?? '',
                    'cedula' => $d['cedula'] ?? ''
                ];
            }, $drivers);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'drivers' => $formatted]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Listar vehículos de un chofer (JSON)
     * Ruta: GET /admin/drivers/{id}/vehicles
     */
    public function driverVehicles($driverId) {
        $this->requireAdminRole();

        try {
            $vehicles = $this->vehicleModel->getByDriver($driverId);
            $formatted = array_map(function($v) {
                // The DB uses `seats_capacity` and `plate_number` column names.
                // Fallback to other common names for compatibility.
                $plate = $v['plate_number'] ?? $v['plate'] ?? $v['license_plate'] ?? '';
                $make = $v['brand'] ?? $v['make'] ?? '';
                $model = $v['model'] ?? '';

                // Prefer seats_capacity column; fall back to capacity or seats if present.
                $capacity = isset($v['seats_capacity']) ? (int)$v['seats_capacity'] : (int)($v['capacity'] ?? $v['seats'] ?? 0);

                return [
                    'id' => $v['id'],
                    'plate' => $plate,
                    'make' => $make,
                    'model' => $model,
                    'capacity' => $capacity,
                    'display' => trim($make . ' ' . $model . ' - ' . $plate)
                ];
            }, $vehicles);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'vehicles' => $formatted]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * API: Obtener usuario específico
     */
    public function apiUser($id) {
        $this->requireAdminRole();
        
        try {
            $user = $this->userModel->findById($id);
            
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
      
    /**
     * API: Cambiar estado de usuario
     */
    public function apiToggleUserStatus($id) {
        $this->requireAdminRole();
        
        try {
            // Establecer cabeceras para JSON
            header('Content-Type: application/json');
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Método no permitido'
                ]);
                return;
            }
            
            // Verificar que el usuario existe
            $user = $this->userModel->findById($id);
            if (!$user) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);
                return;
            }
            
            // No permitir cambiar el estado del admin actual
            if ($id == Session::get('user_id')) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No puedes cambiar tu propio estado'
                ]);
                return;
            }
            
            // Cambiar estado
            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            $result = $this->userModel->changeStatus($id, $newStatus);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => $result['message'],
                    'new_status' => $newStatus
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ]);
        }
    }
    
}
