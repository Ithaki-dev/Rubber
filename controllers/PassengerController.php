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
    private $session;
    private $statistics;
    
    public function __construct() {
        $this->rideModel = new Ride();
        $this->reservationModel = new Reservation();
        $this->session = Session::getInstance();
        $this->statistics = new Statistics();
        
        // Verificar que el usuario es pasajero
        $this->requirePassengerRole();
    }
    
    /**
     * Dashboard del pasajero
     */
    public function dashboard() {
        $passenger_id = $this->session->get('user_id');
        
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
            $this->session->setFlash('error', 'Viaje no encontrado');
            redirect('/passenger/search');
            return;
        }
        
        if (!$ride['is_active'] || $ride['available_seats'] <= 0) {
            $this->session->setFlash('error', 'Este viaje no está disponible');
            redirect('/passenger/search');
            return;
        }
        
        // Verificar si ya tiene una reserva en este viaje
        $passenger_id = $this->session->get('user_id');
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
        
        $passenger_id = $this->session->get('user_id');
        
        $data = [
            'passenger_id' => $passenger_id,
            'ride_id' => sanitize($_POST['ride_id'] ?? ''),
            'seats_requested' => sanitize($_POST['seats_requested'] ?? '1')
        ];
        
        $result = $this->reservationModel->create($data);
        
        if ($result['success']) {
            $this->session->setFlash('success', $result['message']);
            redirect('/passenger/reservations');
        } else {
            $this->session->setFlash('error', $result['message']);
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
        $passenger_id = $this->session->get('user_id');
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
        
        require_once __DIR__ . '/../views/passenger/reservations/index.php';
    }
    
    /**
     * Ver detalles de una reserva
     */
    public function showReservation($id) {
        $passenger_id = $this->session->get('user_id');
        $reservation = $this->reservationModel->findById($id);
        
        // Verificar que la reserva pertenece al pasajero
        if (!$reservation || $reservation['passenger_id'] != $passenger_id) {
            $this->session->setFlash('error', 'Reserva no encontrada');
            redirect('/passenger/reservations');
            return;
        }
        
        require_once __DIR__ . '/../views/passenger/reservations/show.php';
    }
    
    /**
     * Cancelar reserva
     */
    public function cancelReservation($id) {
        $passenger_id = $this->session->get('user_id');
        $result = $this->reservationModel->cancel($id, $passenger_id);
        
        if ($result['success']) {
            $this->session->setFlash('success', $result['message']);
        } else {
            $this->session->setFlash('error', $result['message']);
        }
        
        redirect('/passenger/reservations');
    }
    
    /**
     * Historial de viajes realizados
     */
    public function history() {
        $passenger_id = $this->session->get('user_id');
        
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
        if (!$this->session->isAuthenticated()) {
            $this->session->setFlash('error', 'Debes iniciar sesión');
            redirect('/auth/login');
            exit;
        }
        
        if ($this->session->get('user_type') !== 'passenger') {
            $this->session->setFlash('error', 'No tienes permiso para acceder a esta página');
            redirect('/dashboard');
            exit;
        }
    }
}
