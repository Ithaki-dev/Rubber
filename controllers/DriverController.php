<?php
/**
 * DriverController
 * Gestiona funcionalidades del chofer: vehículos, viajes, reservas
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Statistics.php';
require_once __DIR__ . '/../core/Helpers.php';

class DriverController {
    private $vehicleModel;
    private $rideModel;
    private $reservationModel;
    private $statistics;
    
    public function __construct() {
        $this->vehicleModel = new Vehicle();
        $this->rideModel = new Ride();
        $this->reservationModel = new Reservation();
        $this->statistics = new Statistics();
        
        // Verificar que el usuario es chofer
        $this->requireDriverRole();
    }

    /**
     * API: Obtener estadísticas del driver (JSON)
     * Ruta: GET /api/driver/stats
     */
    public function apiStats() {
        header('Content-Type: application/json');
        try {
            $driver_id = Session::get('user_id');
            $stats = $this->statistics->getDriverStatistics($driver_id);
            echo json_encode(['success' => true, 'stats' => $stats]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error obteniendo estadísticas', 'detail' => $e->getMessage()]);
        }
    }

    /**
     * API: Listar viajes del driver (JSON)
     * Ruta: GET /api/driver/rides?status=upcoming|completed
     */
    public function apiRides() {
        header('Content-Type: application/json');
        try {
            $driver_id = Session::get('user_id');
            $status = sanitize($_GET['status'] ?? 'all');
            $filters = [];
            if ($status === 'upcoming') {
                $filters['future_only'] = true;
            } elseif ($status === 'completed') {
                $filters['past_only'] = true;
            }

            $rides = $this->rideModel->getByDriver($driver_id, $filters);
            echo json_encode(['success' => true, 'rides' => $rides]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error en la consulta de viajes', 'detail' => $e->getMessage()]);
        }
    }

    /**
     * API: Listar vehículos del driver (JSON)
     * Ruta: GET /api/driver/vehicles
     */
    public function apiVehicles() {
        header('Content-Type: application/json');
        try {
            $driver_id = Session::get('user_id');
            $vehicles = $this->vehicleModel->getByDriver($driver_id, true);
            echo json_encode(['success' => true, 'vehicles' => $vehicles]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error obteniendo vehículos', 'detail' => $e->getMessage()]);
        }
    }
    
    /**
     * Dashboard del chofer
     */
    public function dashboard() {
        $driver_id = Session::get('user_id');
        
        // Obtener estadísticas del chofer
        $stats = $this->statistics->getDriverStatistics($driver_id);
        
        // Obtener viajes próximos
        $upcoming_rides = $this->rideModel->getByDriver($driver_id, ['future_only' => true]);
        $upcoming_rides = array_slice($upcoming_rides, 0, 5); // Solo 5
        
        // Obtener reservas pendientes
        $pending_reservations = $this->reservationModel->getPendingByDriver($driver_id);
        
        // Obtener vehículos activos
        $vehicles = $this->vehicleModel->getByDriver($driver_id, true);
        
        require_once __DIR__ . '/../views/driver/dashboard.php';
    }
    
    // ==========================================
    // VEHÍCULOS
    // ==========================================
    
    /**
     * Listar vehículos del chofer
     */
    public function vehicles() {
        $driver_id = Session::get('user_id');
        $vehicles = $this->vehicleModel->getByDriver($driver_id);
        // Vehicles are displayed in the driver dashboard. Redirect there
        redirect('/driver/dashboard?open_vehicles=1');
    }
    
    /**
     * Mostrar formulario para crear vehículo
     */
    public function createVehicle() {
        // Use dashboard modal for adding vehicles
        redirect('/driver/dashboard?open_add_vehicle=1');
    }
    
    /**
     * Guardar nuevo vehículo
     */
    public function storeVehicle() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/driver/vehicles');
            return;
        }
        
        $driver_id = Session::get('user_id');
        
        $data = [
            'driver_id' => $driver_id,
            'plate_number' => sanitize($_POST['plate_number'] ?? ''),
            'color' => sanitize($_POST['color'] ?? ''),
            'brand' => sanitize($_POST['brand'] ?? ''),
            'model' => sanitize($_POST['model'] ?? ''),
            'year' => sanitize($_POST['year'] ?? ''),
            'seats_capacity' => sanitize($_POST['seats_capacity'] ?? ''),
        ];
        
        // Manejar foto del vehículo
        if (!empty($_FILES['photo']['name'])) {
            $upload_result = uploadFile($_FILES['photo'], 'vehicles');
            if ($upload_result['success']) {
                $data['photo_path'] = $upload_result['path'];
            } else {
                Session::setFlash('error', $upload_result['message']);
                Session::setFlash('old_input', $data);
                redirect('/driver/vehicles/create');
                return;
            }
        }
        
        $result = $this->vehicleModel->create($data);

        // Detect AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => $result['message'], 'vehicle' => $result['vehicle'] ?? null]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
            return;
        }

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            redirect('/driver/dashboard');
        } else {
            Session::setFlash('error', $result['message']);
            Session::setFlash('old_input', $data);
            redirect('/driver/dashboard?open_add_vehicle=1');
        }
    }
    
    /**
     * Mostrar formulario para editar vehículo
     */
    public function editVehicle($id) {
        $driver_id = Session::get('user_id');
        $vehicle = $this->vehicleModel->findById($id);
        
        // Verificar que el vehículo pertenece al chofer
        if (!$vehicle || $vehicle['driver_id'] != $driver_id) {
            Session::setFlash('error', 'Vehículo no encontrado');
            redirect('/driver/vehicles');
            return;
        }
        
        // Use dashboard modal/edit flow
        redirect('/driver/dashboard?edit_vehicle=' . (int)$id);
    }
    
    /**
     * Actualizar vehículo
     */
    public function updateVehicle($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/driver/vehicles');
            return;
        }
        
        $driver_id = Session::get('user_id');
        $vehicle = $this->vehicleModel->findById($id);
        
        // Verificar que el vehículo pertenece al chofer
        if (!$vehicle || $vehicle['driver_id'] != $driver_id) {
            Session::setFlash('error', 'Vehículo no encontrado');
            redirect('/driver/vehicles');
            return;
        }
        
        $data = [
            'plate_number' => sanitize($_POST['plate_number'] ?? ''),
            'color' => sanitize($_POST['color'] ?? ''),
            'brand' => sanitize($_POST['brand'] ?? ''),
            'model' => sanitize($_POST['model'] ?? ''),
            'year' => sanitize($_POST['year'] ?? ''),
            'seats_capacity' => sanitize($_POST['seats_capacity'] ?? ''),
        ];
        
        // Manejar nueva foto
        if (!empty($_FILES['photo']['name'])) {
            $upload_result = uploadFile($_FILES['photo'], 'vehicles');
            if ($upload_result['success']) {
                $data['photo_path'] = $upload_result['path'];
                
                // Eliminar foto anterior
                if (!empty($vehicle['photo_path'])) {
                    deleteFile($vehicle['photo_path']);
                }
            }
        }
        
        $result = $this->vehicleModel->update($id, $data);
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            redirect('/driver/dashboard');
        } else {
            Session::setFlash('error', $result['message']);
            redirect('/driver/dashboard?edit_vehicle=' . (int)$id);
        }
    }
    
    /**
     * Eliminar vehículo
     */
    public function deleteVehicle($id) {
        $driver_id = Session::get('user_id');
        $vehicle = $this->vehicleModel->findById($id);
        
        // Verificar que el vehículo pertenece al chofer
        if (!$vehicle || $vehicle['driver_id'] != $driver_id) {
            Session::setFlash('error', 'Vehículo no encontrado');
            redirect('/driver/vehicles');
            return;
        }
        
        $result = $this->vehicleModel->delete($id);

        // If AJAX request, return JSON
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($result['success']) {
                // Eliminar foto si existe
                if (!empty($vehicle['photo_path'])) {
                    deleteFile($vehicle['photo_path']);
                }
                echo json_encode(['success' => true, 'message' => $result['message']]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
            return;
        }

        if ($result['success']) {
            // Eliminar foto si existe
            if (!empty($vehicle['photo_path'])) {
                deleteFile($vehicle['photo_path']);
            }
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        redirect('/driver/dashboard');
    }
    
    /**
     * Activar/Desactivar vehículo
     */
    public function toggleVehicle($id) {
        $driver_id = Session::get('user_id');
        $vehicle = $this->vehicleModel->findById($id);
        
        // Verificar que el vehículo pertenece al chofer
        if (!$vehicle || $vehicle['driver_id'] != $driver_id) {
            Session::setFlash('error', 'Vehículo no encontrado');
            redirect('/driver/vehicles');
            return;
        }
        
        $new_status = !$vehicle['is_active'];
        $result = $this->vehicleModel->toggleActive($id, $new_status);
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        redirect('/driver/dashboard');
    }
    
    // ==========================================
    // VIAJES
    // ==========================================
    
    /**
     * Listar viajes del chofer
     */
    public function rides() {
        $driver_id = Session::get('user_id');
        $filter = sanitize($_GET['filter'] ?? 'all');
        
        $filters = [];
        if ($filter === 'future') {
            $filters['future_only'] = true;
        } elseif ($filter === 'past') {
            $filters['past_only'] = true;
        }
        
        $rides = $this->rideModel->getByDriver($driver_id, $filters);

        // Render rides via dashboard (SPA-like). Redirect to dashboard where JS
        // will fetch rides through the API and render them.
        redirect('/driver/dashboard?open_rides=1');
    }
    
    /**
     * Mostrar formulario para crear viaje
     */
    public function createRide() {
        $driver_id = Session::get('user_id');
        $vehicles = $this->vehicleModel->getByDriver($driver_id, true);
        
        // Verificar que tiene vehículos activos
        if (empty($vehicles)) {
            Session::setFlash('error', 'Debes tener al menos un vehículo activo para crear viajes');
            redirect('/driver/vehicles');
            return;
        }
        
        // Instead of rendering a separate create page, redirect to the driver dashboard
        // which contains the Create Ride modal. We add a query flag so the dashboard
        // frontend opens the modal automatically.
        redirect('/driver/dashboard?open_create=1');
    }
    
    /**
     * Guardar nuevo viaje
     */
    public function storeRide() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/driver/rides');
            return;
        }
        
        $driver_id = Session::get('user_id');
        
        $data = [
            'driver_id' => $driver_id,
            'vehicle_id' => sanitize($_POST['vehicle_id'] ?? ''),
            'ride_name' => sanitize($_POST['ride_name'] ?? ''),
            'departure_location' => sanitize($_POST['departure_location'] ?? ''),
            'arrival_location' => sanitize($_POST['arrival_location'] ?? ''),
            'ride_date' => sanitize($_POST['ride_date'] ?? ''),
            'ride_time' => sanitize($_POST['ride_time'] ?? ''),
            'cost_per_seat' => sanitize($_POST['cost_per_seat'] ?? ''),
            'total_seats' => sanitize($_POST['total_seats'] ?? ''),
            // Optional coordinates: include only if both lat and lng are provided
        ];

        $depLatRaw = isset($_POST['departure_lat']) ? trim($_POST['departure_lat']) : '';
        $depLngRaw = isset($_POST['departure_lng']) ? trim($_POST['departure_lng']) : '';
        if ($depLatRaw !== '' && $depLngRaw !== '') {
            $data['departure_lat'] = $depLatRaw;
            $data['departure_lng'] = $depLngRaw;
        }

        $arrLatRaw = isset($_POST['arrival_lat']) ? trim($_POST['arrival_lat']) : '';
        $arrLngRaw = isset($_POST['arrival_lng']) ? trim($_POST['arrival_lng']) : '';
        if ($arrLatRaw !== '' && $arrLngRaw !== '') {
            $data['arrival_lat'] = $arrLatRaw;
            $data['arrival_lng'] = $arrLngRaw;
        }
        
        $result = $this->rideModel->create($data);

        // Support AJAX requests (SPA-like) — return JSON if requested via X-Requested-With
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => $result['message'], 'ride_id' => $result['ride_id'] ?? null]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
            return;
        }

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            redirect('/driver/rides');
        } else {
            // Store error message and old input correctly in session so the dashboard
            // can show the error and repopulate the form. Previously old_input was
            // incorrectly stored using setFlash which overwrote the flash payload.
            Session::setFlash('error', $result['message']);
            Session::set('old_input', $data);
            // Redirect directly to the dashboard create modal flag (avoid extra redirect)
            redirect('/driver/dashboard?open_create=1');
        }
    }
    
    /**
     * Ver detalles del viaje con reservas
     */
    public function showRide($id) {
        $driver_id = Session::get('user_id');
        $ride = $this->rideModel->findById($id);
        
        // Verificar que el viaje pertenece al chofer
        if (!$ride || $ride['driver_id'] != $driver_id) {
            Session::setFlash('error', 'Viaje no encontrado');
            redirect('/driver/rides');
            return;
        }
        
        // Obtener reservas del viaje (used by JS if needed)
        $reservations = $this->rideModel->getReservations($id);

        // Show ride details in dashboard modal instead of separate view
        redirect('/driver/dashboard?show_ride=' . (int)$id);
    }
    
    /**
     * Mostrar formulario para editar viaje
     */
    public function editRide($id) {
        $driver_id = Session::get('user_id');
        $ride = $this->rideModel->findById($id);
        
        // Verificar que el viaje pertenece al chofer
        if (!$ride || $ride['driver_id'] != $driver_id) {
            Session::setFlash('error', 'Viaje no encontrado');
            redirect('/driver/rides');
            return;
        }
        
        $vehicles = $this->vehicleModel->getByDriver($driver_id, true);

        // Edit via dashboard modal
        redirect('/driver/dashboard?edit_ride=' . (int)$id);
    }
    
    /**
     * Actualizar viaje
     */
    public function updateRide($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/driver/rides');
            return;
        }
        
        $driver_id = Session::get('user_id');
        $ride = $this->rideModel->findById($id);
        
        // Verificar que el viaje pertenece al chofer
        if (!$ride || $ride['driver_id'] != $driver_id) {
            Session::setFlash('error', 'Viaje no encontrado');
            redirect('/driver/rides');
            return;
        }
        
        $data = [
            'ride_name' => sanitize($_POST['ride_name'] ?? ''),
            'departure_location' => sanitize($_POST['departure_location'] ?? ''),
            'arrival_location' => sanitize($_POST['arrival_location'] ?? ''),
            'ride_date' => sanitize($_POST['ride_date'] ?? ''),
            'ride_time' => sanitize($_POST['ride_time'] ?? ''),
            'cost_per_seat' => sanitize($_POST['cost_per_seat'] ?? '')
        ];

        // Accept optional coordinates on update only when both lat and lng provided
        $depLatRaw = isset($_POST['departure_lat']) ? trim($_POST['departure_lat']) : '';
        $depLngRaw = isset($_POST['departure_lng']) ? trim($_POST['departure_lng']) : '';
        if ($depLatRaw !== '' && $depLngRaw !== '') {
            $data['departure_lat'] = $depLatRaw;
            $data['departure_lng'] = $depLngRaw;
        }
        $arrLatRaw = isset($_POST['arrival_lat']) ? trim($_POST['arrival_lat']) : '';
        $arrLngRaw = isset($_POST['arrival_lng']) ? trim($_POST['arrival_lng']) : '';
        if ($arrLatRaw !== '' && $arrLngRaw !== '') {
            $data['arrival_lat'] = $arrLatRaw;
            $data['arrival_lng'] = $arrLngRaw;
        }
        
        $result = $this->rideModel->update($id, $data);
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            redirect('/driver/dashboard');
        } else {
            Session::setFlash('error', $result['message']);
            redirect('/driver/dashboard?edit_ride=' . (int)$id);
        }
    }
    
    /**
     * Eliminar viaje
     */
    public function deleteRide($id) {
        $driver_id = Session::get('user_id');
        $ride = $this->rideModel->findById($id);
        
        // Verificar que el viaje pertenece al chofer
        if (!$ride || $ride['driver_id'] != $driver_id) {
            Session::setFlash('error', 'Viaje no encontrado');
            redirect('/driver/rides');
            return;
        }
        
        $result = $this->rideModel->delete($id);

        // If AJAX request, return JSON
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => $result['message']]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
            return;
        }

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        redirect('/driver/dashboard');
    }
    
    /**
     * Activar/Desactivar viaje
     */
    public function toggleRide($id) {
        $driver_id = Session::get('user_id');
        $ride = $this->rideModel->findById($id);
        
        // Verificar que el viaje pertenece al chofer
        if (!$ride || $ride['driver_id'] != $driver_id) {
            Session::setFlash('error', 'Viaje no encontrado');
            redirect('/driver/rides');
            return;
        }
        
        $new_status = !$ride['is_active'];
        $result = $this->rideModel->toggleActive($id, $new_status);
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        redirect('/driver/dashboard');
    }
    
    // ==========================================
    // RESERVAS
    // ==========================================
    
    /**
     * Listar reservas de los viajes del chofer
     */
    public function reservations() {
        $driver_id = Session::get('user_id');
        $filter = sanitize($_GET['filter'] ?? 'all');
        
        $filters = [];
        if ($filter !== 'all') {
            $filters['status'] = $filter;
        }
        
        $reservations = $this->reservationModel->getByDriver($driver_id, $filters);
        // If AJAX request, return JSON (used by dashboard JS to populate reservations)
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'reservations' => $reservations]);
            return;
        }

        // Non-AJAX: use dashboard reservations section/modal
        redirect('/driver/dashboard?open_reservations=1');
    }
    
    /**
     * Aceptar reserva
     */
    public function acceptReservation($id) {
        $driver_id = Session::get('user_id');
        $result = $this->reservationModel->accept($id, $driver_id);
        // If AJAX call, return JSON instead of redirecting
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => $result['message']]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
            return;
        }

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        // Redirigir según el origen
        $redirect = sanitize($_GET['redirect'] ?? 'list');
        if ($redirect === 'ride') {
            $reservation = $this->reservationModel->findById($id);
            redirect('/driver/rides/show/' . $reservation['ride_id']);
        } else {
            redirect('/driver/reservations');
        }
    }
    
    /**
     * Rechazar reserva
     */
    public function rejectReservation($id) {
        $driver_id = Session::get('user_id');
        $result = $this->reservationModel->reject($id, $driver_id);
        // If AJAX call, return JSON instead of redirecting
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => $result['message']]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
            return;
        }

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        // Redirigir según el origen
        $redirect = sanitize($_GET['redirect'] ?? 'list');
        if ($redirect === 'ride') {
            $reservation = $this->reservationModel->findById($id);
            redirect('/driver/rides/show/' . $reservation['ride_id']);
        } else {
            redirect('/driver/reservations');
        }
    }
    
    /**
     * Verificar que el usuario es chofer
     */
    private function requireDriverRole() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Debes iniciar sesión');
            redirect('/auth/login');
            exit;
        }
        
        if (Session::get('user_type') !== 'driver') {
            Session::setFlash('error', 'No tienes permiso para acceder a esta página');
            redirect('/dashboard');
            exit;
        }
    }
}
