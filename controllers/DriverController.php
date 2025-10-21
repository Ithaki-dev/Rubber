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
        
        require_once __DIR__ . '/../views/driver/vehicles/index.php';
    }
    
    /**
     * Mostrar formulario para crear vehículo
     */
    public function createVehicle() {
        require_once __DIR__ . '/../views/driver/vehicles/create.php';
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
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            redirect('/driver/vehicles');
        } else {
            Session::setFlash('error', $result['message']);
            Session::setFlash('old_input', $data);
            redirect('/driver/vehicles/create');
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
        
        require_once __DIR__ . '/../views/driver/vehicles/edit.php';
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
            redirect('/driver/vehicles');
        } else {
            Session::setFlash('error', $result['message']);
            redirect('/driver/vehicles/edit/' . $id);
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
        
        if ($result['success']) {
            // Eliminar foto si existe
            if (!empty($vehicle['photo_path'])) {
                deleteFile($vehicle['photo_path']);
            }
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        redirect('/driver/vehicles');
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
        
        redirect('/driver/vehicles');
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
        
        require_once __DIR__ . '/../views/driver/rides/index.php';
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
        
        require_once __DIR__ . '/../views/driver/rides/create.php';
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
            'total_seats' => sanitize($_POST['total_seats'] ?? '')
        ];
        
        $result = $this->rideModel->create($data);
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            redirect('/driver/rides');
        } else {
            Session::setFlash('error', $result['message']);
            Session::setFlash('old_input', $data);
            redirect('/driver/rides/create');
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
        
        // Obtener reservas del viaje
        $reservations = $this->rideModel->getReservations($id);
        
        require_once __DIR__ . '/../views/driver/rides/show.php';
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
        
        require_once __DIR__ . '/../views/driver/rides/edit.php';
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
        
        $result = $this->rideModel->update($id, $data);
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            redirect('/driver/rides');
        } else {
            Session::setFlash('error', $result['message']);
            redirect('/driver/rides/edit/' . $id);
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
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        redirect('/driver/rides');
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
        
        redirect('/driver/rides');
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
        
        require_once __DIR__ . '/../views/driver/reservations/index.php';
    }
    
    /**
     * Aceptar reserva
     */
    public function acceptReservation($id) {
        $driver_id = Session::get('user_id');
        $result = $this->reservationModel->accept($id, $driver_id);
        
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
        if (!Session::isAuthenticated()) {
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
