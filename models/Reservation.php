<?php
/**
 * Reservation Model
 * Gestiona reservas de pasajeros en viajes
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Validator.php';

class Reservation {
    private $db;
    private $validator;
    
    // Propiedades de la reserva
    public $id;
    public $passenger_id;
    public $ride_id;
    public $seats_requested;
    public $status;
    public $total_cost;
    public $created_at;
    public $updated_at;
    
    private $valid_statuses = ['pending', 'accepted', 'rejected', 'cancelled'];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->validator = new Validator();
    }
    
    /**
     * Crear nueva reserva
     * @param array $data Datos de la reserva
     * @return array ['success' => bool, 'message' => string, 'reservation_id' => int|null]
     */
    public function create($data) {
        // Validar datos
        $validation = $this->validateReservationData($data);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode(', ', $validation['errors'])];
        }
        
        // Verificar que el viaje existe y está activo
        $ride = $this->getRideInfo($data['ride_id']);
        if (!$ride) {
            return ['success' => false, 'message' => 'Viaje no encontrado'];
        }
        
        if (!$ride['is_active']) {
            return ['success' => false, 'message' => 'El viaje no está disponible'];
        }
        
        // Verificar que el viaje no sea en el pasado
        if (strtotime($ride['ride_date']) < strtotime(date('Y-m-d'))) {
            return ['success' => false, 'message' => 'No se pueden hacer reservas en viajes pasados'];
        }
        
        // Verificar que el pasajero no sea el chofer
        if ($data['passenger_id'] == $ride['driver_id']) {
            return ['success' => false, 'message' => 'No puedes reservar tu propio viaje'];
        }
        
        // Verificar que no tenga una reserva activa en este viaje
        if ($this->hasActiveReservation($data['passenger_id'], $data['ride_id'])) {
            return ['success' => false, 'message' => 'Ya tienes una reserva activa en este viaje'];
        }
        
        // Calcular costo total
        $seats_requested = $data['seats_requested'] ?? 1;
        $total_cost = $ride['cost_per_seat'] * $seats_requested;
        
        // Iniciar transacción (los triggers validarán y actualizarán available_seats)
        $this->db->beginTransaction();
        
        try {
            $sql = "INSERT INTO reservations 
                    (passenger_id, ride_id, seats_requested, status, total_cost) 
                    VALUES (?, ?, ?, 'pending', ?)";
            
            $stmt = $this->db->query($sql, [
                $data['passenger_id'],
                $data['ride_id'],
                $seats_requested,
                $total_cost
            ]);
            
            $reservation_id = $this->db->getConnection()->lastInsertId();
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Reserva creada exitosamente',
                'reservation_id' => $reservation_id
            ];
            
        } catch (PDOException $e) {
            $this->db->rollback();
            
            // Verificar si el error es por falta de asientos (trigger)
            if (strpos($e->getMessage(), 'No hay suficientes asientos disponibles') !== false) {
                return ['success' => false, 'message' => 'No hay suficientes asientos disponibles'];
            }
            
            return ['success' => false, 'message' => 'Error al crear reserva: ' . $e->getMessage()];
        }
    }
    
    /**
     * Buscar reserva por ID (con información completa)
     * @param int $id ID de la reserva
     * @return array|null Datos de la reserva o null
     */
    public function findById($id) {
        $sql = "SELECT * FROM v_reservations_complete WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener reservas de un pasajero
     * @param int $passenger_id ID del pasajero
     * @param array $filters Filtros adicionales
     * @return array Lista de reservas
     */
    public function getByPassenger($passenger_id, $filters = []) {
        $sql = "SELECT * FROM v_reservations_complete WHERE passenger_id = ?";
        $params = [$passenger_id];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['future_only'])) {
            $sql .= " AND ride_date >= CURDATE()";
        }
        
        if (!empty($filters['past_only'])) {
            $sql .= " AND ride_date < CURDATE()";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener reservas de un viaje
     * @param int $ride_id ID del viaje
     * @param array $filters Filtros adicionales
     * @return array Lista de reservas
     */
    public function getByRide($ride_id, $filters = []) {
        $sql = "SELECT * FROM v_reservations_complete WHERE ride_id = ?";
        $params = [$ride_id];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY created_at ASC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener reservas de viajes de un chofer
     * @param int $driver_id ID del chofer
     * @param array $filters Filtros adicionales
     * @return array Lista de reservas
     */
    public function getByDriver($driver_id, $filters = []) {
        $sql = "SELECT * FROM v_reservations_complete WHERE driver_id = ?";
        $params = [$driver_id];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['ride_id'])) {
            $sql .= " AND ride_id = ?";
            $params[] = $filters['ride_id'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cambiar estado de una reserva
     * @param int $id ID de la reserva
     * @param string $new_status Nuevo estado
     * @param int $user_id ID del usuario que hace el cambio
     * @return array ['success' => bool, 'message' => string]
     */
    public function changeStatus($id, $new_status, $user_id) {
        // Verificar que el estado es válido
        if (!in_array($new_status, $this->valid_statuses)) {
            return ['success' => false, 'message' => 'Estado inválido'];
        }
        
        // Obtener reserva actual
        $reservation = $this->findById($id);
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reserva no encontrada'];
        }
        
        // Validar permisos
        $can_change = $this->canChangeStatus($reservation, $new_status, $user_id);
        if (!$can_change['success']) {
            return $can_change;
        }
        
        // El trigger se encargará de actualizar available_seats
        $sql = "UPDATE reservations SET status = ? WHERE id = ?";
        
        try {
            $this->db->query($sql, [$new_status, $id]);
            return ['success' => true, 'message' => 'Estado actualizado exitosamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar estado: ' . $e->getMessage()];
        }
    }
    
    /**
     * Aceptar reserva (solo chofer)
     * @param int $id ID de la reserva
     * @param int $driver_id ID del chofer
     * @return array ['success' => bool, 'message' => string]
     */
    public function accept($id, $driver_id) {
        $reservation = $this->findById($id);
        
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reserva no encontrada'];
        }
        
        if ($reservation['driver_id'] != $driver_id) {
            return ['success' => false, 'message' => 'No tienes permiso para aceptar esta reserva'];
        }
        
        if ($reservation['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Solo se pueden aceptar reservas pendientes'];
        }
        
        return $this->changeStatus($id, 'accepted', $driver_id);
    }
    
    /**
     * Rechazar reserva (solo chofer)
     * @param int $id ID de la reserva
     * @param int $driver_id ID del chofer
     * @return array ['success' => bool, 'message' => string]
     */
    public function reject($id, $driver_id) {
        $reservation = $this->findById($id);
        
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reserva no encontrada'];
        }
        
        if ($reservation['driver_id'] != $driver_id) {
            return ['success' => false, 'message' => 'No tienes permiso para rechazar esta reserva'];
        }
        
        if ($reservation['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Solo se pueden rechazar reservas pendientes'];
        }
        
        return $this->changeStatus($id, 'rejected', $driver_id);
    }
    
    /**
     * Cancelar reserva (solo pasajero)
     * @param int $id ID de la reserva
     * @param int $passenger_id ID del pasajero
     * @return array ['success' => bool, 'message' => string]
     */
    public function cancel($id, $passenger_id) {
        $reservation = $this->findById($id);
        
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reserva no encontrada'];
        }
        
        if ($reservation['passenger_id'] != $passenger_id) {
            return ['success' => false, 'message' => 'No tienes permiso para cancelar esta reserva'];
        }
        
        if (!in_array($reservation['status'], ['pending', 'accepted'])) {
            return ['success' => false, 'message' => 'No se puede cancelar esta reserva'];
        }
        
        // Verificar que no sea muy tarde para cancelar (ej: 24 horas antes)
        $ride_datetime = strtotime($reservation['ride_date'] . ' ' . $reservation['ride_time']);
        $hours_until_ride = ($ride_datetime - time()) / 3600;
        
        if ($hours_until_ride < 24) {
            return ['success' => false, 'message' => 'No se puede cancelar con menos de 24 horas de anticipación'];
        }
        
        return $this->changeStatus($id, 'cancelled', $passenger_id);
    }
    
    /**
     * Eliminar reserva (solo admin o si no tiene impacto)
     * @param int $id ID de la reserva
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete($id) {
        $reservation = $this->findById($id);
        
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reserva no encontrada'];
        }
        
        // Solo permitir eliminar si está cancelada o rechazada
        if (!in_array($reservation['status'], ['cancelled', 'rejected'])) {
            return ['success' => false, 'message' => 'Solo se pueden eliminar reservas canceladas o rechazadas'];
        }
        
        $sql = "DELETE FROM reservations WHERE id = ?";
        
        try {
            $this->db->query($sql, [$id]);
            return ['success' => true, 'message' => 'Reserva eliminada exitosamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar reserva: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener todas las reservas con filtros (Admin)
     * @param array $filters Filtros opcionales
     * @return array Lista de reservas
     */
    public function getAll($filters = []) {
        $sql = "SELECT * FROM v_reservations_complete WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['passenger_id'])) {
            $sql .= " AND passenger_id = ?";
            $params[] = $filters['passenger_id'];
        }
        
        if (!empty($filters['driver_id'])) {
            $sql .= " AND driver_id = ?";
            $params[] = $filters['driver_id'];
        }
        
        if (!empty($filters['ride_id'])) {
            $sql .= " AND ride_id = ?";
            $params[] = $filters['ride_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (passenger_first_name LIKE ? OR passenger_last_name LIKE ? OR ride_name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener reservas pendientes de un chofer
     * @param int $driver_id ID del chofer
     * @return array Lista de reservas pendientes
     */
    public function getPendingByDriver($driver_id) {
        return $this->getByDriver($driver_id, ['status' => 'pending']);
    }
    
    /**
     * Obtener estadísticas de reservas
     * @param array $filters Filtros opcionales
     * @return array Estadísticas
     */
    public function getStatistics($filters = []) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'accepted' THEN 1 END) as accepted,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                    SUM(CASE WHEN status = 'accepted' THEN total_cost ELSE 0 END) as total_revenue
                FROM reservations WHERE 1=1";
        $params = [];
        
        if (!empty($filters['passenger_id'])) {
            $sql .= " AND passenger_id = ?";
            $params[] = $filters['passenger_id'];
        }
        
        if (!empty($filters['ride_id'])) {
            $sql .= " AND ride_id = ?";
            $params[] = $filters['ride_id'];
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si un pasajero tiene una reserva activa en un viaje
     * @param int $passenger_id ID del pasajero
     * @param int $ride_id ID del viaje
     * @return bool True si tiene reserva activa
     */
    private function hasActiveReservation($passenger_id, $ride_id) {
        $sql = "SELECT id FROM reservations 
                WHERE passenger_id = ? AND ride_id = ? 
                AND status IN ('pending', 'accepted')";
        
        $stmt = $this->db->query($sql, [$passenger_id, $ride_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    /**
     * Obtener información del viaje
     * @param int $ride_id ID del viaje
     * @return array|null Información del viaje
     */
    private function getRideInfo($ride_id) {
        $sql = "SELECT id, driver_id, cost_per_seat, available_seats, ride_date, is_active 
                FROM rides WHERE id = ?";
        $stmt = $this->db->query($sql, [$ride_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si un usuario puede cambiar el estado de una reserva
     * @param array $reservation Datos de la reserva
     * @param string $new_status Nuevo estado
     * @param int $user_id ID del usuario
     * @return array ['success' => bool, 'message' => string]
     */
    private function canChangeStatus($reservation, $new_status, $user_id) {
        $current_status = $reservation['status'];
        
        // No se puede cambiar de un estado final
        if (in_array($current_status, ['rejected', 'cancelled'])) {
            return ['success' => false, 'message' => 'No se puede modificar una reserva finalizada'];
        }
        
        // Validar transiciones de estado
        $valid_transitions = [
            'pending' => ['accepted', 'rejected', 'cancelled'],
            'accepted' => ['cancelled']
        ];
        
        if (!isset($valid_transitions[$current_status]) || 
            !in_array($new_status, $valid_transitions[$current_status])) {
            return ['success' => false, 'message' => 'Transición de estado inválida'];
        }
        
        return ['success' => true];
    }
    
    /**
     * Validar datos de la reserva
     * @param array $data Datos a validar
     * @return array ['valid' => bool, 'errors' => array]
     */
    private function validateReservationData($data) {
        $errors = [];
        
        if (empty($data['passenger_id']) || !is_numeric($data['passenger_id'])) {
            $errors[] = 'ID de pasajero inválido';
        }
        
        if (empty($data['ride_id']) || !is_numeric($data['ride_id'])) {
            $errors[] = 'ID de viaje inválido';
        }
        
        $seats = $data['seats_requested'] ?? 1;
        if (!$this->validator->validatePositiveInt($seats) || $seats < 1 || $seats > 4) {
            $errors[] = 'Cantidad de asientos inválida (1-4)';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Obtener reservas activas
     * @return int
     */
    public function getActiveCount() {
        $query = "SELECT COUNT(*) as total FROM reservations WHERE status IN ('pending', 'accepted')";
        $result = $this->db->query($query);
        $data = $result->fetch();
        return (int)$data['total'];
    }
    
    /**
     * Obtener total de ingresos
     * @return float
     */
    public function getTotalRevenue() {
        $query = "SELECT COALESCE(SUM(total_cost), 0) as total FROM reservations WHERE status = 'accepted'";
        $result = $this->db->query($query);
        $data = $result->fetch();
        return (float)$data['total'];
    }
}
