<?php
/**
 * Vehicle Model
 * Gestiona vehículos de los choferes
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Validator.php';

class Vehicle {
    private $db;
    private $validator;
    
    // Propiedades del vehículo
    public $id;
    public $driver_id;
    public $plate_number;
    public $color;
    public $brand;
    public $model;
    public $year;
    public $seats_capacity;
    public $photo_path;
    public $is_active;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->validator = new Validator();
    }
    
    /**
     * Crear nuevo vehículo
     * @param array $data Datos del vehículo
     * @return array ['success' => bool, 'message' => string, 'vehicle_id' => int|null]
     */
    public function create($data) {
        // Validar datos
        $validation = $this->validateVehicleData($data);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode(', ', $validation['errors'])];
        }
        
        // Verificar que la placa no exista
        if ($this->plateExists($data['plate_number'])) {
            return ['success' => false, 'message' => 'La placa ya está registrada'];
        }
        
        // Verificar que el chofer existe
        if (!$this->driverExists($data['driver_id'])) {
            return ['success' => false, 'message' => 'Chofer no encontrado'];
        }
        
        $sql = "INSERT INTO vehicles 
                (driver_id, plate_number, color, brand, model, year, seats_capacity, photo_path, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->query($sql, [
                $data['driver_id'],
                $data['plate_number'],
                $data['color'],
                $data['brand'],
                $data['model'],
                $data['year'],
                $data['seats_capacity'],
                $data['photo_path'] ?? null,
                $data['is_active'] ?? 1
            ]);
            
            $vehicle_id = $this->db->getConnection()->lastInsertId();
            
            return [
                'success' => true,
                'message' => 'Vehículo creado exitosamente',
                'vehicle_id' => $vehicle_id
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al crear vehículo: ' . $e->getMessage()];
        }
    }
    
    /**
     * Buscar vehículo por ID
     * @param int $id ID del vehículo
     * @return array|null Datos del vehículo o null
     */
    public function findById($id) {
        $sql = "SELECT v.*, 
                       u.first_name AS driver_first_name, 
                       u.last_name AS driver_last_name,
                       u.email AS driver_email,
                       u.phone AS driver_phone
                FROM vehicles v
                INNER JOIN users u ON v.driver_id = u.id
                WHERE v.id = ?";
        
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar vehículo por placa
     * @param string $plate_number Placa del vehículo
     * @return array|null Datos del vehículo o null
     */
    public function findByPlate($plate_number) {
        $sql = "SELECT * FROM vehicles WHERE plate_number = ?";
        $stmt = $this->db->query($sql, [$plate_number]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener vehículos de un chofer
     * @param int $driver_id ID del chofer
     * @param bool $active_only Solo vehículos activos
     * @return array Lista de vehículos
     */
    public function getByDriver($driver_id, $active_only = false) {
        $sql = "SELECT * FROM vehicles WHERE driver_id = ?";
        $params = [$driver_id];
        
        if ($active_only) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualizar vehículo
     * @param int $id ID del vehículo
     * @param array $data Datos a actualizar
     * @return array ['success' => bool, 'message' => string]
     */
    public function update($id, $data) {
        // Verificar que el vehículo existe
        if (!$this->findById($id)) {
            return ['success' => false, 'message' => 'Vehículo no encontrado'];
        }
        
        // Validar datos
        $validation = $this->validateVehicleData($data, 'update');
        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode(', ', $validation['errors'])];
        }
        
        // Verificar placa duplicada (excluyendo el vehículo actual)
        if (isset($data['plate_number'])) {
            $existing = $this->findByPlate($data['plate_number']);
            if ($existing && $existing['id'] != $id) {
                return ['success' => false, 'message' => 'La placa ya está registrada'];
            }
        }
        
        // Construir consulta dinámica
        $fields = [];
        $values = [];
        
        $allowed_fields = ['plate_number', 'color', 'brand', 'model', 'year', 'seats_capacity', 'photo_path', 'is_active'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => 'No hay datos para actualizar'];
        }
        
        $values[] = $id;
        $sql = "UPDATE vehicles SET " . implode(', ', $fields) . " WHERE id = ?";
        
        try {
            $this->db->query($sql, $values);
            return ['success' => true, 'message' => 'Vehículo actualizado exitosamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar vehículo: ' . $e->getMessage()];
        }
    }
    
    /**
     * Eliminar vehículo
     * @param int $id ID del vehículo
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete($id) {
        if (!$this->findById($id)) {
            return ['success' => false, 'message' => 'Vehículo no encontrado'];
        }
        
        // Verificar si tiene viajes asociados
        if ($this->hasRides($id)) {
            return ['success' => false, 'message' => 'No se puede eliminar: el vehículo tiene viajes asociados'];
        }
        
        $sql = "DELETE FROM vehicles WHERE id = ?";
        
        try {
            $this->db->query($sql, [$id]);
            return ['success' => true, 'message' => 'Vehículo eliminado exitosamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar vehículo: ' . $e->getMessage()];
        }
    }
    
    /**
     * Activar/Desactivar vehículo
     * @param int $id ID del vehículo
     * @param bool $is_active Estado activo/inactivo
     * @return array ['success' => bool, 'message' => string]
     */
    public function toggleActive($id, $is_active) {
        if (!$this->findById($id)) {
            return ['success' => false, 'message' => 'Vehículo no encontrado'];
        }
        
        $sql = "UPDATE vehicles SET is_active = ? WHERE id = ?";
        
        try {
            $this->db->query($sql, [$is_active ? 1 : 0, $id]);
            $status = $is_active ? 'activado' : 'desactivado';
            return ['success' => true, 'message' => "Vehículo $status exitosamente"];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener todos los vehículos con filtros
     * @param array $filters Filtros opcionales
     * @return array Lista de vehículos
     */
    public function getAll($filters = []) {
        $sql = "SELECT v.*, 
                       u.first_name AS driver_first_name, 
                       u.last_name AS driver_last_name,
                       u.email AS driver_email
                FROM vehicles v
                INNER JOIN users u ON v.driver_id = u.id
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['is_active'])) {
            $sql .= " AND v.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        if (!empty($filters['brand'])) {
            $sql .= " AND v.brand LIKE ?";
            $params[] = '%' . $filters['brand'] . '%';
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (v.plate_number LIKE ? OR v.brand LIKE ? OR v.model LIKE ? OR v.color LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY v.created_at DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si un vehículo tiene viajes asociados
     * @param int $vehicle_id ID del vehículo
     * @return bool True si tiene viajes
     */
    public function hasRides($vehicle_id) {
        $sql = "SELECT COUNT(*) as count FROM rides WHERE vehicle_id = ?";
        $stmt = $this->db->query($sql, [$vehicle_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    /**
     * Obtener estadísticas del vehículo
     * @param int $vehicle_id ID del vehículo
     * @return array Estadísticas
     */
    public function getStatistics($vehicle_id) {
        $sql = "SELECT 
                    COUNT(DISTINCT r.id) as total_rides,
                    COUNT(DISTINCT res.id) as total_reservations,
                    SUM(res.total_cost) as total_earnings
                FROM vehicles v
                LEFT JOIN rides r ON v.id = r.vehicle_id
                LEFT JOIN reservations res ON r.id = res.ride_id AND res.status = 'accepted'
                WHERE v.id = ?
                GROUP BY v.id";
        
        $stmt = $this->db->query($sql, [$vehicle_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$stats) {
            return [
                'total_rides' => 0,
                'total_reservations' => 0,
                'total_earnings' => 0
            ];
        }
        
        return $stats;
    }
    
    /**
     * Verificar si una placa ya existe
     * @param string $plate_number Placa a verificar
     * @return bool True si existe
     */
    private function plateExists($plate_number) {
        return $this->findByPlate($plate_number) !== false;
    }
    
    /**
     * Verificar si un chofer existe
     * @param int $driver_id ID del chofer
     * @return bool True si existe
     */
    private function driverExists($driver_id) {
        $sql = "SELECT id FROM users WHERE id = ? AND user_type = 'driver'";
        $stmt = $this->db->query($sql, [$driver_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    /**
     * Validar datos del vehículo
     * @param array $data Datos a validar
     * @param string $mode Modo: 'create' o 'update'
     * @return array ['valid' => bool, 'errors' => array]
     */
    private function validateVehicleData($data, $mode = 'create') {
        $errors = [];
        
        // Validaciones para creación
        if ($mode === 'create') {
            if (empty($data['driver_id']) || !is_numeric($data['driver_id'])) {
                $errors[] = 'ID de chofer inválido';
            }
            
            if (empty($data['plate_number'])) {
                $errors[] = 'Placa es requerida';
            }
            
            if (empty($data['color'])) {
                $errors[] = 'Color es requerido';
            }
            
            if (empty($data['brand'])) {
                $errors[] = 'Marca es requerida';
            }
            
            if (empty($data['model'])) {
                $errors[] = 'Modelo es requerido';
            }
            
            if (empty($data['year']) || !$this->validator->validateYear($data['year'])) {
                $errors[] = 'Año inválido';
            }
            
            if (empty($data['seats_capacity']) || !$this->validator->validatePositiveInt($data['seats_capacity'])) {
                $errors[] = 'Capacidad de asientos inválida';
            }
        }
        
        // Validaciones para actualización
        if ($mode === 'update') {
            if (isset($data['plate_number']) && empty($data['plate_number'])) {
                $errors[] = 'Placa no puede estar vacía';
            }
            
            if (isset($data['year']) && !$this->validator->validateYear($data['year'])) {
                $errors[] = 'Año inválido';
            }
            
            if (isset($data['seats_capacity']) && !$this->validator->validatePositiveInt($data['seats_capacity'])) {
                $errors[] = 'Capacidad de asientos inválida';
            }
        }
        
        // Validaciones comunes
        if (isset($data['color']) && strlen($data['color']) > 50) {
            $errors[] = 'Color demasiado largo';
        }
        
        if (isset($data['brand']) && strlen($data['brand']) > 100) {
            $errors[] = 'Marca demasiado larga';
        }
        
        if (isset($data['model']) && strlen($data['model']) > 100) {
            $errors[] = 'Modelo demasiado largo';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
