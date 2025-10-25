<?php
/**
 * Ride Model
 * Gestiona viajes (rides) creados por choferes
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Validator.php';

class Ride {
    private $db;
    private $validator;
    
    // Propiedades del viaje
    public $id;
    public $driver_id;
    public $vehicle_id;
    public $ride_name;
    public $departure_location;
    public $arrival_location;
    public $ride_date;
    public $ride_time;
    public $day_of_week;
    public $cost_per_seat;
    public $available_seats;
    public $total_seats;
    public $is_active;
    public $created_at;
    public $updated_at;
    
    private $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->validator = new Validator();
    }
    
    /**
     * Crear nuevo viaje
     * @param array $data Datos del viaje
     * @return array ['success' => bool, 'message' => string, 'ride_id' => int|null]
     */
    public function create($data) {
        // Validar datos
        $validation = $this->validateRideData($data);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode(', ', $validation['errors'])];
        }
        
        // Verificar que el vehículo existe y pertenece al chofer
        if (!$this->vehicleBelongsToDriver($data['vehicle_id'], $data['driver_id'])) {
            return ['success' => false, 'message' => 'Vehículo no válido para este chofer'];
        }
        
        // Obtener capacidad del vehículo
        $vehicle_capacity = $this->getVehicleCapacity($data['vehicle_id']);
        if (!$vehicle_capacity) {
            return ['success' => false, 'message' => 'Vehículo no encontrado'];
        }
        
        // Si no se especifica total_seats, usar la capacidad del vehículo
        $total_seats = $data['total_seats'] ?? $vehicle_capacity;
        
        // Validar que total_seats no exceda la capacidad
        if ($total_seats > $vehicle_capacity) {
            return ['success' => false, 'message' => "El vehículo solo tiene capacidad para $vehicle_capacity asientos"];
        }
        
        // Calcular día de la semana
        $day_of_week = $this->getDayOfWeek($data['ride_date']);
        
        $sql = "INSERT INTO rides 
                (driver_id, vehicle_id, ride_name, departure_location, arrival_location, 
                 ride_date, ride_time, day_of_week, cost_per_seat, available_seats, total_seats, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->query($sql, [
                $data['driver_id'],
                $data['vehicle_id'],
                $data['ride_name'],
                $data['departure_location'],
                $data['arrival_location'],
                $data['ride_date'],
                $data['ride_time'],
                $day_of_week,
                $data['cost_per_seat'],
                $total_seats, // available_seats = total_seats al crear
                $total_seats,
                $data['is_active'] ?? 1
            ]);
            
            $ride_id = $this->db->getConnection()->lastInsertId();
            
            return [
                'success' => true,
                'message' => 'Viaje creado exitosamente',
                'ride_id' => $ride_id
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al crear viaje: ' . $e->getMessage()];
        }
    }
    
    /**
     * Buscar viaje por ID (con información completa)
     * @param int $id ID del viaje
     * @return array|null Datos del viaje o null
     */
    public function findById($id) {
        $sql = "SELECT * FROM v_rides_complete WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener viajes de un chofer
     * @param int $driver_id ID del chofer
     * @param array $filters Filtros adicionales
     * @return array Lista de viajes
     */
    public function getByDriver($driver_id, $filters = []) {
        $sql = "SELECT * FROM v_rides_complete WHERE driver_id = ?";
        $params = [$driver_id];
        
        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        if (!empty($filters['future_only'])) {
            $sql .= " AND ride_date >= CURDATE()";
        }
        
        if (!empty($filters['past_only'])) {
            $sql .= " AND ride_date < CURDATE()";
        }
        
        $sql .= " ORDER BY ride_date DESC, ride_time DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar viajes públicos (para pasajeros)
     * @param array $filters Filtros de búsqueda
     * @return array Lista de viajes disponibles
     */
    public function search($filters = []) {
        $sql = "SELECT * FROM v_rides_complete 
                WHERE is_active = 1 
                AND ride_date >= CURDATE() 
                AND available_seats > 0";
        $params = [];
        
        if (!empty($filters['departure_location'])) {
            $sql .= " AND departure_location LIKE ?";
            $params[] = '%' . $filters['departure_location'] . '%';
        }
        
        if (!empty($filters['arrival_location'])) {
            $sql .= " AND arrival_location LIKE ?";
            $params[] = '%' . $filters['arrival_location'] . '%';
        }
        
        if (!empty($filters['ride_date'])) {
            $sql .= " AND ride_date = ?";
            $params[] = $filters['ride_date'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND ride_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND ride_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['day_of_week'])) {
            $sql .= " AND day_of_week = ?";
            $params[] = $filters['day_of_week'];
        }
        
        if (!empty($filters['min_seats'])) {
            $sql .= " AND available_seats >= ?";
            $params[] = $filters['min_seats'];
        }
        
        if (!empty($filters['max_cost'])) {
            $sql .= " AND cost_per_seat <= ?";
            $params[] = $filters['max_cost'];
        }
        
        // Ordenar por fecha y hora
        $sql .= " ORDER BY ride_date ASC, ride_time ASC";
        
        // Limitar resultados si se especifica
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualizar viaje
     * @param int $id ID del viaje
     * @param array $data Datos a actualizar
     * @return array ['success' => bool, 'message' => string]
     */
    public function update($id, $data) {
        // Verificar que el viaje existe
        $ride = $this->findById($id);
        if (!$ride) {
            return ['success' => false, 'message' => 'Viaje no encontrado'];
        }
        
        // No permitir actualizar si tiene reservas aceptadas
        if (isset($data['total_seats']) || isset($data['ride_date']) || isset($data['ride_time'])) {
            if ($this->hasAcceptedReservations($id)) {
                return ['success' => false, 'message' => 'No se puede modificar: el viaje tiene reservas aceptadas'];
            }
        }
        
        // Validar datos
        $validation = $this->validateRideData($data, 'update');
        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode(', ', $validation['errors'])];
        }
        
        // Construir consulta dinámica
        $fields = [];
        $values = [];
        
        $allowed_fields = ['ride_name', 'departure_location', 'arrival_location', 
                          'ride_date', 'ride_time', 'cost_per_seat', 'is_active'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
                
                // Actualizar day_of_week si se cambia la fecha
                if ($field === 'ride_date') {
                    $fields[] = "day_of_week = ?";
                    $values[] = $this->getDayOfWeek($data[$field]);
                }
            }
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => 'No hay datos para actualizar'];
        }
        
        $values[] = $id;
        $sql = "UPDATE rides SET " . implode(', ', $fields) . " WHERE id = ?";
        
        try {
            $this->db->query($sql, $values);
            return ['success' => true, 'message' => 'Viaje actualizado exitosamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar viaje: ' . $e->getMessage()];
        }
    }
    
    /**
     * Eliminar viaje
     * @param int $id ID del viaje
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete($id) {
        if (!$this->findById($id)) {
            return ['success' => false, 'message' => 'Viaje no encontrado'];
        }
        
        // Verificar si tiene reservas
        if ($this->hasReservations($id)) {
            return ['success' => false, 'message' => 'No se puede eliminar: el viaje tiene reservas asociadas'];
        }
        
        $sql = "DELETE FROM rides WHERE id = ?";
        
        try {
            $this->db->query($sql, [$id]);
            return ['success' => true, 'message' => 'Viaje eliminado exitosamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar viaje: ' . $e->getMessage()];
        }
    }
    
    /**
     * Activar/Desactivar viaje
     * @param int $id ID del viaje
     * @param bool $is_active Estado activo/inactivo
     * @return array ['success' => bool, 'message' => string]
     */
    public function toggleActive($id, $is_active) {
        if (!$this->findById($id)) {
            return ['success' => false, 'message' => 'Viaje no encontrado'];
        }
        
        $sql = "UPDATE rides SET is_active = ? WHERE id = ?";
        
        try {
            $this->db->query($sql, [$is_active ? 1 : 0, $id]);
            $status = $is_active ? 'activado' : 'desactivado';
            return ['success' => true, 'message' => "Viaje $status exitosamente"];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener viajes próximos (próximos 7 días)
     * @param int $limit Límite de resultados
     * @return array Lista de viajes
     */
    public function getUpcoming($limit = 10) {
        $sql = "SELECT * FROM v_rides_complete 
                WHERE is_active = 1 
                AND ride_date >= CURDATE() 
                AND ride_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                AND available_seats > 0
                ORDER BY ride_date ASC, ride_time ASC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener todos los viajes con filtros (Admin)
     * @param array $filters Filtros opcionales
     * @return array Lista de viajes
     */
    public function getAll($filters = []) {
        $sql = "SELECT * FROM v_rides_complete WHERE 1=1";
        $params = [];
        
        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        if (!empty($filters['driver_id'])) {
            $sql .= " AND driver_id = ?";
            $params[] = $filters['driver_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (ride_name LIKE ? OR departure_location LIKE ? OR arrival_location LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY ride_date DESC, ride_time DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si un viaje tiene asientos disponibles
     * @param int $ride_id ID del viaje
     * @param int $seats_requested Asientos solicitados
     * @return bool True si hay disponibilidad
     */
    public function hasAvailableSeats($ride_id, $seats_requested = 1) {
        $sql = "SELECT available_seats FROM rides WHERE id = ?";
        $stmt = $this->db->query($sql, [$ride_id]);
        $ride = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ride) {
            return false;
        }
        
        return $ride['available_seats'] >= $seats_requested;
    }
    
    /**
     * Verificar si un viaje tiene reservas
     * @param int $ride_id ID del viaje
     * @return bool True si tiene reservas
     */
    public function hasReservations($ride_id) {
        $sql = "SELECT COUNT(*) as count FROM reservations WHERE ride_id = ?";
        $stmt = $this->db->query($sql, [$ride_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    /**
     * Verificar si un viaje tiene reservas aceptadas
     * @param int $ride_id ID del viaje
     * @return bool True si tiene reservas aceptadas
     */
    public function hasAcceptedReservations($ride_id) {
        $sql = "SELECT COUNT(*) as count FROM reservations 
                WHERE ride_id = ? AND status = 'accepted'";
        $stmt = $this->db->query($sql, [$ride_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    /**
     * Obtener reservas de un viaje
     * @param int $ride_id ID del viaje
     * @return array Lista de reservas
     */
    public function getReservations($ride_id) {
        $sql = "SELECT * FROM v_reservations_complete WHERE ride_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, [$ride_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si un vehículo pertenece a un chofer
     * @param int $vehicle_id ID del vehículo
     * @param int $driver_id ID del chofer
     * @return bool True si pertenece
     */
    private function vehicleBelongsToDriver($vehicle_id, $driver_id) {
        $sql = "SELECT id FROM vehicles WHERE id = ? AND driver_id = ? AND is_active = 1";
        $stmt = $this->db->query($sql, [$vehicle_id, $driver_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    /**
     * Obtener capacidad de asientos de un vehículo
     * @param int $vehicle_id ID del vehículo
     * @return int|null Capacidad o null
     */
    private function getVehicleCapacity($vehicle_id) {
        $sql = "SELECT seats_capacity FROM vehicles WHERE id = ?";
        $stmt = $this->db->query($sql, [$vehicle_id]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
        return $vehicle ? $vehicle['seats_capacity'] : null;
    }
    
    /**
     * Calcular día de la semana desde una fecha
     * @param string $date Fecha en formato Y-m-d
     * @return string Día de la semana en inglés
     */
    private function getDayOfWeek($date) {
        $timestamp = strtotime($date);
        return date('l', $timestamp); // Monday, Tuesday, etc.
    }
    
    /**
     * Validar datos del viaje
     * @param array $data Datos a validar
     * @param string $mode Modo: 'create' o 'update'
     * @return array ['valid' => bool, 'errors' => array]
     */
    private function validateRideData($data, $mode = 'create') {
        $errors = [];
        
        // Validaciones para creación
        if ($mode === 'create') {
            if (empty($data['driver_id']) || !is_numeric($data['driver_id'])) {
                $errors[] = 'ID de chofer inválido';
            }
            
            if (empty($data['vehicle_id']) || !is_numeric($data['vehicle_id'])) {
                $errors[] = 'ID de vehículo inválido';
            }
            
            if (empty($data['ride_name'])) {
                $errors[] = 'Nombre del viaje es requerido';
            }
            
            if (empty($data['departure_location'])) {
                $errors[] = 'Ubicación de salida es requerida';
            }
            
            if (empty($data['arrival_location'])) {
                $errors[] = 'Ubicación de llegada es requerida';
            }
            
            if (empty($data['ride_date']) || !$this->validator->validateDate($data['ride_date'])) {
                $errors[] = 'Fecha de viaje inválida';
            } elseif (strtotime($data['ride_date']) < strtotime(date('Y-m-d'))) {
                $errors[] = 'La fecha del viaje no puede ser en el pasado';
            }
            
            if (empty($data['ride_time']) || !$this->validator->validateTime($data['ride_time'])) {
                $errors[] = 'Hora de viaje inválida';
            }
            
            if (empty($data['cost_per_seat']) || !$this->validator->validatePrice($data['cost_per_seat'])) {
                $errors[] = 'Costo por asiento inválido';
            }
        }
        
        // Validaciones para actualización
        if ($mode === 'update') {
            if (isset($data['ride_date'])) {
                if (!$this->validator->validateDate($data['ride_date'])) {
                    $errors[] = 'Fecha de viaje inválida';
                } elseif (strtotime($data['ride_date']) < strtotime(date('Y-m-d'))) {
                    $errors[] = 'La fecha del viaje no puede ser en el pasado';
                }
            }
            
            if (isset($data['ride_time']) && !$this->validator->validateTime($data['ride_time'])) {
                $errors[] = 'Hora de viaje inválida';
            }
            
            if (isset($data['cost_per_seat']) && !$this->validator->validatePrice($data['cost_per_seat'])) {
                $errors[] = 'Costo por asiento inválido';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Obtener total de viajes
     * @return int
     */
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM rides";
        $result = $this->db->query($query);
        $data = $result->fetch();
        return (int)$data['total'];
    }
    
    /**
     * Obtener viajes de hoy
     * @return int
     */
    public function getTodayCount() {
        $query = "SELECT COUNT(*) as total FROM rides WHERE DATE(ride_date) = CURDATE()";
        $result = $this->db->query($query);
        $data = $result->fetch();
        return (int)$data['total'];
    }
}
