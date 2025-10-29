<?php
/**
 * PassengerController
 * Gestiona funcionalidades del pasajero: buscar viajes, reservas
 */

require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Statistics.php';
require_once __DIR__ . '/../core/Helpers.php';

class PassengerController {
    private $rideModel;
    private $reservationModel;
    private $statistics;
    
    public function __construct() {
        $this->rideModel = new Ride();
        $this->reservationModel = new Reservation();
        $this->statistics = new Statistics();
        
        // Verificar que el usuario es pasajero para rutas que no sean API.
        // Si la petición viene desde /api/..., permitimos acceso público (búsqueda pública).
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/api/') === false) {
            $this->requirePassengerRole();
        }
    }

    /**
     * API: Obtener viajes (JSON) — usado por el mapa del pasajero
     * Parámetros GET soportados: bounds (swLat,swLng,neLat,neLng), date, date_from, date_to, seats, max_cost, search
     */
    public function apiRides() {
        // Construir filtros a partir de query params
        $filters = [];
        if (!empty($_GET['bounds'])) {
            $filters['bounds'] = sanitize($_GET['bounds']);
        }
        if (!empty($_GET['date'])) {
            $filters['ride_date'] = sanitize($_GET['date']);
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = sanitize($_GET['date_from']);
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = sanitize($_GET['date_to']);
        }
        if (!empty($_GET['seats'])) {
            $filters['min_seats'] = (int)$_GET['seats'];
        }
        if (!empty($_GET['max_cost'])) {
            $filters['max_cost'] = (float)$_GET['max_cost'];
        }
        if (!empty($_GET['departure'])) {
            $filters['departure_location'] = sanitize($_GET['departure']);
        }
        if (!empty($_GET['arrival'])) {
            $filters['arrival_location'] = sanitize($_GET['arrival']);
        }

        // Llamar al modelo
        header('Content-Type: application/json');
        try {
            $rides = $this->rideModel->search($filters);
            echo json_encode(['success' => true, 'rides' => $rides]);
        } catch (Exception $e) {
            // Devolver error en JSON para no romper el cliente (evitar HTML)
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error en la consulta de viajes', 'detail' => $e->getMessage()]);
        }
    }
    
    /**
     * Dashboard del pasajero
     */
    public function dashboard() {
        $passenger_id = Session::get('user_id');
        
        // Obtener estadísticas del pasajero
        $stats = $this->statistics->getPassengerStatistics($passenger_id);
        
        // Obtener próximas reservas
        $upcoming_reservations = $this->reservationModel->getByPassenger($passenger_id, [
            'future_only' => true,
            'status' => 'accepted'
        ]);
        $upcoming_reservations = array_slice($upcoming_reservations, 0, 5);
        
        // Obtener reservas pendientes
        $pending_reservations = $this->reservationModel->getByPassenger($passenger_id, [
            'status' => 'pending'
        ]);
        
        // Obtener viajes disponibles próximos
        $available_rides = $this->rideModel->getUpcoming(5);
        
        require_once __DIR__ . '/../views/passenger/dashboard.php';
    }
    
    // ==========================================
    // BÚSQUEDA DE VIAJES
    // ==========================================
    
    /**
     * Buscar viajes disponibles
     */
    public function searchRides() {
        $filters = [
            'departure_location' => sanitize($_GET['departure'] ?? ''),
            'arrival_location' => sanitize($_GET['arrival'] ?? ''),
            'ride_date' => sanitize($_GET['date'] ?? ''),
            'min_seats' => sanitize($_GET['seats'] ?? '1')
        ];
        
        // Remover filtros vacíos
        $filters = array_filter($filters);
        
        $rides = empty($filters) ? [] : $this->rideModel->search($filters);
        
        require_once __DIR__ . '/../views/passenger/rides/search.php';
    }
    
    /**
     * Ver detalles de un viaje
     */
    public function showRide($id) {
        $ride = $this->rideModel->findById($id);
        
        if (!$ride) {
            Session::setFlash('error', 'Viaje no encontrado');
            redirect('/passenger/search');
            return;
        }
        
        if (!$ride['is_active'] || $ride['available_seats'] <= 0) {
            Session::setFlash('error', 'Este viaje no está disponible');
            redirect('/passenger/search');
            return;
        }
        
        // Verificar si ya tiene una reserva en este viaje
        $passenger_id = Session::get('user_id');
        $existing_reservations = $this->reservationModel->getByPassenger($passenger_id, [
            'ride_id' => $id
        ]);
        
        $has_reservation = false;
        foreach ($existing_reservations as $res) {
            if (in_array($res['status'], ['pending', 'accepted'])) {
                $has_reservation = true;
                break;
            }
        }
        
        require_once __DIR__ . '/../views/passenger/rides/show.php';
    }
    
    /**
     * Hacer una reserva
     */
    public function makeReservation() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/passenger/search');
            return;
        }
        
        $passenger_id = Session::get('user_id');
        
        $data = [
            'passenger_id' => $passenger_id,
            'ride_id' => sanitize($_POST['ride_id'] ?? ''),
            'seats_requested' => sanitize($_POST['seats_requested'] ?? '1')
        ];
        
        $result = $this->reservationModel->create($data);

        // Detectar petición AJAX para devolver JSON en lugar de redirigir
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {
            header('Content-Type: application/json');
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => $result['message'], 'reservation_id' => $result['reservation_id'] ?? null]);
            } else {
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
            exit;
        }

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            redirect('/passenger/reservations');
        } else {
            Session::setFlash('error', $result['message']);
            redirect('/passenger/rides/show/' . $data['ride_id']);
        }
    }
    
    // ==========================================
    // RESERVAS
    // ==========================================
    
    /**
     * Listar reservas del pasajero
     */
    public function reservations() {
        $passenger_id = Session::get('user_id');
        $filter = sanitize($_GET['filter'] ?? 'all');
        
        $filters = [];
        if ($filter === 'future') {
            $filters['future_only'] = true;
        } elseif ($filter === 'past') {
            $filters['past_only'] = true;
        } elseif ($filter !== 'all') {
            $filters['status'] = $filter;
        }
        
        $reservations = $this->reservationModel->getByPassenger($passenger_id, $filters);

        // If AJAX request, return JSON (used by dashboard JS to populate "Mis Reservas")
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            // DEBUG: include passenger_id and count to help client-side diagnostics
            echo json_encode([
                'success' => true,
                'passenger_id' => $passenger_id,
                'count' => count($reservations),
                'reservations' => $reservations
            ]);
            return;
        }

        require_once __DIR__ . '/../views/passenger/reservations/index.php';
    }
    
    /**
     * Ver detalles de una reserva
     */
    public function showReservation($id) {
        $passenger_id = Session::get('user_id');
        $reservation = $this->reservationModel->findById($id);
        
        // Verificar que la reserva pertenece al pasajero
        if (!$reservation || $reservation['passenger_id'] != $passenger_id) {
            Session::setFlash('error', 'Reserva no encontrada');
            redirect('/passenger/reservations');
            return;
        }
        
        require_once __DIR__ . '/../views/passenger/reservations/show.php';
    }
    
    /**
     * Cancelar reserva
     */
    public function cancelReservation($id) {
        $passenger_id = Session::get('user_id');
        $result = $this->reservationModel->cancel($id, $passenger_id);

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

        redirect('/passenger/reservations');
    }
    
    /**
     * Historial de viajes realizados
     */
    public function history() {
        $passenger_id = Session::get('user_id');
        
        // Obtener reservas pasadas aceptadas
        $reservations = $this->reservationModel->getByPassenger($passenger_id, [
            'past_only' => true,
            'status' => 'accepted'
        ]);
        
        require_once __DIR__ . '/../views/passenger/history.php';
    }
    
    /**
     * Verificar que el usuario es pasajero
     */
    private function requirePassengerRole() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Debes iniciar sesión');
            redirect('/auth/login');
            exit;
        }
        
        if (Session::get('user_type') !== 'passenger') {
            Session::setFlash('error', 'No tienes permiso para acceder a esta página');
            redirect('/dashboard');
            exit;
        }
    }
}
