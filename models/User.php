<?php
/**
 * User Model
 * Gestiona usuarios: Admin, Choferes y Pasajeros
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Validator.php';

class User {
    private $db;
    private $validator;
    
    // Propiedades del usuario
    public $id;
    public $user_type;
    public $first_name;
    public $last_name;
    public $cedula;
    public $birth_date;
    public $email;
    public $phone;
    public $photo_path;
    public $password_hash;
    public $status;
    public $activation_token;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->validator = new Validator();
    }
    
    /**
     * Crear nuevo usuario
     * @param array $data Datos del usuario
     * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
     */
    public function create($data) {
        // Validar datos
        $validation = $this->validateUserData($data, 'create');
        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode(', ', $validation['errors'])];
        }
        
        // Verificar si el email ya existe
        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'El email ya está registrado'];
        }
        
        // Verificar si la cédula ya existe
        if ($this->cedulaExists($data['cedula'])) {
            return ['success' => false, 'message' => 'La cédula ya está registrada'];
        }
        
        // Generar token de activación
        $activation_token = bin2hex(random_bytes(32));
        
        // Hash de contraseña
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        
        // Preparar consulta
        $sql = "INSERT INTO users 
                (user_type, first_name, last_name, cedula, birth_date, email, phone, 
                 password_hash, photo_path, status, activation_token) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
        
        try {
            $stmt = $this->db->query($sql, [
                $data['user_type'],
                $data['first_name'],
                $data['last_name'],
                $data['cedula'],
                $data['birth_date'],
                $data['email'],
                $data['phone'],
                $password_hash,
                $data['photo_path'] ?? null,
                $activation_token
            ]);
            
            $user_id = $this->db->getConnection()->lastInsertId();
            
            return [
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'user_id' => $user_id,
                'activation_token' => $activation_token
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al crear usuario: ' . $e->getMessage()];
        }
    }
    
    /**
     * Buscar usuario por ID
     * @param int $id ID del usuario
     * @return array|null Datos del usuario o null
     */
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar usuario por email
     * @param string $email Email del usuario
     * @return array|null Datos del usuario o null
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar usuario por cédula
     * @param string $cedula Cédula del usuario
     * @return array|null Datos del usuario o null
     */
    public function findByCedula($cedula) {
        $sql = "SELECT * FROM users WHERE cedula = ?";
        $stmt = $this->db->query($sql, [$cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualizar usuario
     * @param int $id ID del usuario
     * @param array $data Datos a actualizar
     * @return array ['success' => bool, 'message' => string]
     */
    public function update($id, $data) {
        // Verificar que el usuario existe
        if (!$this->findById($id)) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        // Validar datos
        $validation = $this->validateUserData($data, 'update');
        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode(', ', $validation['errors'])];
        }
        
        // Verificar email duplicado (excluyendo el usuario actual)
        if (isset($data['email'])) {
            $existing = $this->findByEmail($data['email']);
            if ($existing && $existing['id'] != $id) {
                return ['success' => false, 'message' => 'El email ya está registrado'];
            }
        }
        
        // Verificar cédula duplicada (excluyendo el usuario actual)
        if (isset($data['cedula'])) {
            $existing = $this->findByCedula($data['cedula']);
            if ($existing && $existing['id'] != $id) {
                return ['success' => false, 'message' => 'La cédula ya está registrada'];
            }
        }
        
        // Construir consulta dinámica
        $fields = [];
        $values = [];
        
        $allowed_fields = ['first_name', 'last_name', 'cedula', 'birth_date', 'email', 'phone', 'photo_path', 'status'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        // Si hay nueva contraseña
        if (!empty($data['password'])) {
            $fields[] = "password_hash = ?";
            $values[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => 'No hay datos para actualizar'];
        }
        
        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        try {
            $this->db->query($sql, $values);
            return ['success' => true, 'message' => 'Usuario actualizado exitosamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar usuario: ' . $e->getMessage()];
        }
    }
    
    /**
     * Eliminar usuario
     * @param int $id ID del usuario
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete($id) {
        if (!$this->findById($id)) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        $sql = "DELETE FROM users WHERE id = ?";
        
        try {
            $this->db->query($sql, [$id]);
            return ['success' => true, 'message' => 'Usuario eliminado exitosamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar usuario: ' . $e->getMessage()];
        }
    }
    
    /**
     * Activar cuenta de usuario
     * @param string $token Token de activación
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function activate($token) {
        // Buscar usuario con el token
        $sql = "SELECT id, email, first_name, last_name FROM users WHERE activation_token = ? AND status = 'pending'";
        $stmt = $this->db->query($sql, [$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Token de activación inválido o la cuenta ya está activada'];
        }
        
        // Activar la cuenta
        $sql = "UPDATE users SET status = 'active', activation_token = NULL, activated_at = NOW() WHERE id = ?";
        
        try {
            $this->db->query($sql, [$user['id']]);
            return [
                'success' => true, 
                'message' => 'Cuenta activada exitosamente',
                'user' => $user
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al activar cuenta: ' . $e->getMessage()];
        }
    }
    
    /**
     * Reenviar token de activación
     * @param string $email Email del usuario
     * @return array ['success' => bool, 'message' => string, 'user' => array|null, 'activation_token' => string|null]
     */
    public function resendActivation($email) {
        // Buscar usuario pendiente de activación
        $sql = "SELECT id, email, first_name, last_name, status FROM users WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => 'No se encontró una cuenta con ese email'];
        }
        
        if ($user['status'] === 'active') {
            return ['success' => false, 'message' => 'Esta cuenta ya está activada. Puedes iniciar sesión normalmente.'];
        }
        
        if ($user['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Esta cuenta tiene un estado que no permite la activación'];
        }
        
        // Generar nuevo token
        $newToken = bin2hex(random_bytes(32));
        
        // Actualizar token en la base de datos
        $sql = "UPDATE users SET activation_token = ?, created_at = NOW() WHERE id = ?";
        
        try {
            $this->db->query($sql, [$newToken, $user['id']]);
            return [
                'success' => true,
                'message' => 'Nuevo enlace de activación generado',
                'user' => $user,
                'activation_token' => $newToken
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al generar nuevo token: ' . $e->getMessage()];
        }
    }
    
    /**
     * Verificar credenciales de login
     * @param string $email Email del usuario
     * @param string $password Contraseña
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function login($email, $password) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Credenciales inválidas'];
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Credenciales inválidas'];
        }
        
        if ($user['status'] === 'pending') {
            return ['success' => false, 'message' => 'Cuenta pendiente de activación'];
        }
        
        if ($user['status'] === 'inactive') {
            return ['success' => false, 'message' => 'Cuenta inactiva'];
        }
        
        // No devolver password_hash ni activation_token
        unset($user['password_hash']);
        unset($user['activation_token']);
        
        return [
            'success' => true,
            'message' => 'Login exitoso',
            'user' => $user
        ];
    }
    
    /**
     * Obtener todos los usuarios con filtros
     * @param array $filters Filtros opcionales (user_type, status)
     * @return array Lista de usuarios
     */
    public function getAll($filters = []) {
        $sql = "SELECT id, user_type, first_name, last_name, cedula, birth_date, email, phone, 
                       photo_path, status, created_at, updated_at 
                FROM users WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_type'])) {
            $sql .= " AND user_type = ?";
            $params[] = $filters['user_type'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR cedula LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener choferes activos
     * @return array Lista de choferes
     */
    public function getActiveDrivers() {
        $sql = "SELECT id, first_name, last_name, email, phone, photo_path 
                FROM users 
                WHERE user_type = 'driver' AND status = 'active' 
                ORDER BY first_name, last_name";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcular edad del usuario
     * @param int $user_id ID del usuario
     * @return int|null Edad en años o null
     */
    public function getAge($user_id) {
        $user = $this->findById($user_id);
        if (!$user || !$user['birth_date']) {
            return null;
        }
        
        $birthDate = new DateTime($user['birth_date']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        
        return $age;
    }
    
    /**
     * Cambiar estado del usuario
     * @param int $id ID del usuario
     * @param string $status Nuevo estado (active, inactive, pending)
     * @return array ['success' => bool, 'message' => string]
     */
    public function changeStatus($id, $status) {
        $valid_statuses = ['active', 'inactive', 'pending'];
        
        if (!in_array($status, $valid_statuses)) {
            return ['success' => false, 'message' => 'Estado inválido'];
        }
        
        if (!$this->findById($id)) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        
        try {
            $this->db->query($sql, [$status, $id]);
            return ['success' => true, 'message' => 'Estado actualizado exitosamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar estado: ' . $e->getMessage()];
        }
    }
    
    /**
     * Verificar si un email ya existe
     * @param string $email Email a verificar
     * @return bool True si existe
     */
    private function emailExists($email) {
        return $this->findByEmail($email) !== false;
    }
    
    /**
     * Verificar si una cédula ya existe
     * @param string $cedula Cédula a verificar
     * @return bool True si existe
     */
    private function cedulaExists($cedula) {
        return $this->findByCedula($cedula) !== false;
    }
    
    /**
     * Validar datos del usuario
     * @param array $data Datos a validar
     * @param string $mode Modo: 'create' o 'update'
     * @return array ['valid' => bool, 'errors' => array]
     */
    private function validateUserData($data, $mode = 'create') {
        $errors = [];
        
        // Validaciones para creación
        if ($mode === 'create') {
            if (empty($data['user_type']) || !in_array($data['user_type'], ['admin', 'driver', 'passenger'])) {
                $errors[] = 'Tipo de usuario inválido';
            }
            
            if (empty($data['password']) || !$this->validator->validatePassword($data['password'])) {
                $errors[] = 'La contraseña debe tener al menos 8 caracteres';
            }
        }
        
        // Validaciones comunes
        if (isset($data['first_name']) && !$this->validator->validateName($data['first_name'])) {
            $errors[] = 'Nombre inválido';
        }
        
        if (isset($data['last_name']) && !$this->validator->validateName($data['last_name'])) {
            $errors[] = 'Apellido inválido';
        }
        
        if (isset($data['cedula']) && !$this->validator->validateCedula($data['cedula'])) {
            $errors[] = 'Cédula inválida';
        }
        
        if (isset($data['email']) && !$this->validator->validateEmail($data['email'])) {
            $errors[] = 'Email inválido';
        }
        
        if (isset($data['phone']) && !$this->validator->validatePhone($data['phone'])) {
            $errors[] = 'Teléfono inválido';
        }
        
        if (isset($data['birth_date']) && !$this->validator->validateDate($data['birth_date'])) {
            $errors[] = 'Fecha de nacimiento inválida';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Obtener total de usuarios registrados
     * @return int
     */
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM users";
        $result = $this->db->query($query);
        $data = $result->fetch();
        return (int)$data['total'];
    }
    
    /**
     * Obtener usuarios nuevos en los últimos N días
     * @param int $days Número de días
     * @return int
     */
    public function getNewUsersCount($days = 30) {
        $query = "SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $result = $this->db->query($query, [$days]);
        $data = $result->fetch();
        return (int)$data['total'];
    }
    
    /**
     * Obtener conductores activos
     * @return int
     */
    public function getActiveDriversCount() {
        $query = "SELECT COUNT(*) as total FROM users WHERE user_type = 'driver' AND status = 'active'";
        $result = $this->db->query($query);
        $data = $result->fetch();
        return (int)$data['total'];
    }
}
