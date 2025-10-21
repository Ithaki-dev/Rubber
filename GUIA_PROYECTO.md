# Guía Detallada - Sistema de Gestión de Viajes Compartidos (Carpooling)

## Índice
1. [Planificación del Proyecto](#1-planificación-del-proyecto)
2. [Estructura del Proyecto](#2-estructura-del-proyecto)
3. [Configuración Inicial](#3-configuración-inicial)
4. [Base de Datos](#4-base-de-datos)
5. [Implementación por Módulos](#5-implementación-por-módulos)
6. [Script de Consola](#6-script-de-consola)
7. [Seguridad](#7-seguridad)
8. [Testing y Validación](#8-testing-y-validación)
9. [Deployment](#9-deployment)

---

## 1. Planificación del Proyecto

### 1.1 Cronograma Sugerido (6-8 semanas)

**Semana 1-2: Configuración y Base de Datos**
- Configurar entorno de desarrollo
- Diseñar modelo de base de datos
- Crear estructura de archivos
- Implementar sistema de sesiones

**Semana 3-4: Autenticación y Usuarios**
- Sistema de login/registro
- Activación de cuentas por correo
- Gestión de perfiles
- Panel administrativo

**Semana 5-6: Funcionalidades Core**
- Gestión de vehículos (choferes)
- Gestión de rides (choferes)
- Búsqueda pública de rides
- Sistema de reservas

**Semana 7: Script y Refinamiento**
- Script de consola para notificaciones
- Corrección de bugs
- Mejoras de UI/UX

**Semana 8: Testing y Documentación**
- Pruebas exhaustivas
- Documentación final
- Preparación para entrega

### 1.2 Tecnologías a Utilizar

- **Backend**: PHP 8.0+
- **Base de Datos**: MySQL/MariaDB o PostgreSQL
- **Frontend**: HTML5, CSS3, JavaScript vanilla
- **Estilos**: CSS puro o preprocesador (SASS/LESS)
- **Email**: PHPMailer
- **Control de Versiones**: Git + GitHub

---

## 2. Estructura del Proyecto

```
Rubber/
│
├── config/
│   ├── database.php          # Configuración de BD
│   ├── email.php             # Configuración de email
│   └── constants.php         # Constantes globales
│
├── core/
│   ├── Database.php          # Clase de conexión a BD
│   ├── Session.php           # Manejo de sesiones
│   ├── Validator.php         # Validaciones
│   ├── Email.php             # Servicio de email
│   └── Helpers.php           # Funciones helper
│
├── models/
│   ├── User.php              # Modelo de usuarios
│   ├── Driver.php            # Modelo de choferes
│   ├── Passenger.php         # Modelo de pasajeros
│   ├── Vehicle.php           # Modelo de vehículos
│   ├── Ride.php              # Modelo de viajes
│   └── Reservation.php       # Modelo de reservas
│
├── controllers/
│   ├── AuthController.php    # Login, registro, activación
│   ├── UserController.php    # Gestión de usuarios
│   ├── VehicleController.php # Gestión de vehículos
│   ├── RideController.php    # Gestión de rides
│   ├── ReservationController.php # Gestión de reservas
│   └── AdminController.php   # Panel administrativo
│
├── views/
│   ├── layouts/
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── navigation.php
│   │
│   ├── auth/
│   │   ├── login.php
│   │   ├── register_passenger.php
│   │   ├── register_driver.php
│   │   └── activate.php
│   │
│   ├── admin/
│   │   ├── dashboard.php
│   │   └── users.php
│   │
│   ├── driver/
│   │   ├── dashboard.php
│   │   ├── vehicles.php
│   │   ├── rides.php
│   │   └── reservations.php
│   │
│   ├── passenger/
│   │   ├── dashboard.php
│   │   ├── search.php
│   │   └── my_reservations.php
│   │
│   └── public/
│       ├── home.php
│       └── search_rides.php
│
├── public/
│   ├── index.php             # Punto de entrada
│   ├── .htaccess             # Configuración Apache
│   │
│   ├── css/
│   │   ├── style.css
│   │   └── components.css
│   │
│   ├── js/
│   │   ├── main.js
│   │   └── validation.js
│   │
│   └── uploads/
│       ├── profiles/         # Fotos de perfil
│       └── vehicles/         # Fotos de vehículos
│
├── scripts/
│   └── check_pending_reservations.php  # Script de consola
│
├── sql/
│   ├── schema.sql            # Esquema de BD
│   └── seed.sql              # Datos iniciales
│
├── vendor/                   # Dependencias (PHPMailer, etc.)
│
├── .gitignore
├── composer.json
└── README.md
```

---

## 3. Configuración Inicial

### 3.1 Configurar Git

```bash
cd /home/bob/Github/Rubber
git init
git config user.name "Tu Nombre"
git config user.email "tu@email.com"
```

### 3.2 Crear .gitignore

```
/vendor/
/config/database.php
/config/email.php
/public/uploads/*
!public/uploads/.gitkeep
.env
*.log
.DS_Store
Thumbs.db
```

### 3.3 Instalar Composer y Dependencias

```bash
# Instalar Composer si no lo tienes
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Crear composer.json
composer init

# Instalar PHPMailer
composer require phpmailer/phpmailer
```

### 3.4 Configurar Servidor Local

**Opción 1: PHP Built-in Server**
```bash
cd public
php -S localhost:8000
```

**Opción 2: XAMPP/LAMP**
- Configurar virtual host apuntando a `/home/bob/Github/Rubber/public`

---

## 4. Base de Datos

### 4.1 Diseño del Esquema

**Tablas principales:**

1. **users** - Tabla principal de usuarios
2. **drivers** - Información adicional de choferes
3. **passengers** - Información adicional de pasajeros
4. **vehicles** - Vehículos registrados
5. **rides** - Viajes creados
6. **reservations** - Reservas de pasajeros

### 4.2 Script SQL Completo

```sql
-- Crear base de datos
CREATE DATABASE IF NOT EXISTS carpooling_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE carpooling_db;

-- Tabla de usuarios (base)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('admin', 'driver', 'passenger') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    birth_date DATE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    photo_path VARCHAR(255),
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    activation_token VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB;

-- Tabla de vehículos
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    plate_number VARCHAR(20) UNIQUE NOT NULL,
    color VARCHAR(50) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    seats_capacity INT NOT NULL,
    photo_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_driver (driver_id)
) ENGINE=InnoDB;

-- Tabla de viajes (rides)
CREATE TABLE rides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    ride_name VARCHAR(255) NOT NULL,
    departure_location VARCHAR(255) NOT NULL,
    arrival_location VARCHAR(255) NOT NULL,
    ride_date DATE NOT NULL,
    ride_time TIME NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    cost_per_seat DECIMAL(10, 2) NOT NULL,
    available_seats INT NOT NULL,
    total_seats INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_driver (driver_id),
    INDEX idx_date (ride_date),
    INDEX idx_locations (departure_location, arrival_location),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Tabla de reservas
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    passenger_id INT NOT NULL,
    ride_id INT NOT NULL,
    seats_requested INT NOT NULL DEFAULT 1,
    status ENUM('pending', 'accepted', 'rejected', 'cancelled') DEFAULT 'pending',
    total_cost DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE,
    INDEX idx_passenger (passenger_id),
    INDEX idx_ride (ride_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Insertar usuario administrador por defecto
INSERT INTO users (
    user_type, first_name, last_name, cedula, birth_date, 
    email, phone, password_hash, status
) VALUES (
    'admin', 'Admin', 'System', '000000000', '1990-01-01',
    'admin@carpooling.com', '0000-0000', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'active'
);
```

### 4.3 Crear Configuración de Base de Datos

**config/database.php**
```php
<?php
// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'carpooling_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

---

## 5. Implementación por Módulos

### 5.1 MÓDULO: Core - Database Connection

**core/Database.php**
```php
<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
```

### 5.2 MÓDULO: Session Management

**core/Session.php**
```php
<?php
class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        unset($_SESSION[$key]);
    }

    public static function destroy() {
        session_destroy();
    }

    public static function isLoggedIn() {
        return self::has('user_id');
    }

    public static function getUserType() {
        return self::get('user_type');
    }

    public static function isAdmin() {
        return self::getUserType() === 'admin';
    }

    public static function isDriver() {
        return self::getUserType() === 'driver';
    }

    public static function isPassenger() {
        return self::getUserType() === 'passenger';
    }

    public static function setFlashMessage($type, $message) {
        self::set('flash_message', ['type' => $type, 'message' => $message]);
    }

    public static function getFlashMessage() {
        $message = self::get('flash_message');
        self::remove('flash_message');
        return $message;
    }
}
```

### 5.3 MÓDULO: Validators

**core/Validator.php**
```php
<?php
class Validator {
    private $errors = [];

    public function validate($data, $rules) {
        foreach ($rules as $field => $ruleSet) {
            $rules = explode('|', $ruleSet);
            foreach ($rules as $rule) {
                $this->applyRule($field, $data[$field] ?? '', $rule, $data);
            }
        }
        return empty($this->errors);
    }

    private function applyRule($field, $value, $rule, $allData) {
        if (strpos($rule, ':') !== false) {
            list($rule, $parameter) = explode(':', $rule);
        }

        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = ucfirst($field) . " es requerido";
                }
                break;
            
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "Email no válido";
                }
                break;
            
            case 'min':
                if (strlen($value) < $parameter) {
                    $this->errors[$field][] = ucfirst($field) . " debe tener al menos $parameter caracteres";
                }
                break;
            
            case 'max':
                if (strlen($value) > $parameter) {
                    $this->errors[$field][] = ucfirst($field) . " no puede tener más de $parameter caracteres";
                }
                break;
            
            case 'matches':
                if ($value !== $allData[$parameter]) {
                    $this->errors[$field][] = ucfirst($field) . " no coincide";
                }
                break;
            
            case 'unique':
                list($table, $column) = explode(',', $parameter);
                if ($this->checkUnique($table, $column, $value)) {
                    $this->errors[$field][] = ucfirst($field) . " ya existe";
                }
                break;
        }
    }

    private function checkUnique($table, $column, $value) {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT COUNT(*) as count FROM $table WHERE $column = ?", [$value]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function getErrors() {
        return $this->errors;
    }
}
```

### 5.4 MÓDULO: Email Service

**core/Email.php**
```php
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure() {
        // Configuración SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = SMTP_USER;
        $this->mailer->Password = SMTP_PASS;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = SMTP_PORT;
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    }

    public function sendActivationEmail($to, $name, $token) {
        try {
            $this->mailer->addAddress($to, $name);
            $this->mailer->Subject = 'Activa tu cuenta';
            
            $activationLink = BASE_URL . "/activate.php?token=" . $token;
            
            $this->mailer->isHTML(true);
            $this->mailer->Body = "
                <h2>Bienvenido $name</h2>
                <p>Gracias por registrarte. Por favor activa tu cuenta haciendo clic en el siguiente enlace:</p>
                <p><a href='$activationLink'>Activar Cuenta</a></p>
                <p>Si no solicitaste esta cuenta, ignora este correo.</p>
            ";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error enviando email: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    public function sendPendingReservationNotification($to, $driverName, $count) {
        try {
            $this->mailer->addAddress($to, $driverName);
            $this->mailer->Subject = 'Tienes reservas pendientes';
            
            $this->mailer->isHTML(true);
            $this->mailer->Body = "
                <h2>Hola $driverName</h2>
                <p>Tienes $count reserva(s) pendiente(s) de respuesta.</p>
                <p>Por favor ingresa a tu panel para aceptar o rechazar las solicitudes.</p>
                <p><a href='" . BASE_URL . "'>Ir al sitio</a></p>
            ";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error enviando email: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}
```

### 5.5 MÓDULO: Authentication Controller

**controllers/AuthController.php**
```php
<?php
class AuthController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register($data, $userType) {
        // Validar datos
        $validator = new Validator();
        $rules = [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'cedula' => 'required|unique:users,cedula',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required',
            'password' => 'required|min:6',
            'confirm_password' => 'required|matches:password'
        ];

        if (!$validator->validate($data, $rules)) {
            return ['success' => false, 'errors' => $validator->getErrors()];
        }

        // Procesar foto
        $photoPath = $this->uploadPhoto($_FILES['photo'], 'profiles');
        if (!$photoPath) {
            return ['success' => false, 'errors' => ['photo' => ['Error al subir la foto']]];
        }

        // Generar token de activación
        $activationToken = bin2hex(random_bytes(32));

        // Hash password
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insertar usuario
        $sql = "INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, 
                email, phone, photo_path, password_hash, activation_token, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        try {
            $this->db->query($sql, [
                $userType,
                $data['first_name'],
                $data['last_name'],
                $data['cedula'],
                $data['birth_date'],
                $data['email'],
                $data['phone'],
                $photoPath,
                $passwordHash,
                $activationToken
            ]);

            // Enviar email de activación
            $email = new Email();
            $email->sendActivationEmail(
                $data['email'], 
                $data['first_name'] . ' ' . $data['last_name'],
                $activationToken
            );

            return ['success' => true, 'message' => 'Registro exitoso. Revisa tu correo para activar tu cuenta.'];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['general' => [$e->getMessage()]]];
        }
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Credenciales inválidas'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Credenciales inválidas'];
        }

        if ($user['status'] === 'pending') {
            return ['success' => false, 'message' => 'Tu cuenta está pendiente de activación'];
        }

        if ($user['status'] === 'inactive') {
            return ['success' => false, 'message' => 'Tu cuenta está inactiva'];
        }

        // Iniciar sesión
        Session::set('user_id', $user['id']);
        Session::set('user_type', $user['user_type']);
        Session::set('user_name', $user['first_name'] . ' ' . $user['last_name']);

        return ['success' => true, 'user_type' => $user['user_type']];
    }

    public function activateAccount($token) {
        $sql = "UPDATE users SET status = 'active', activation_token = NULL 
                WHERE activation_token = ? AND status = 'pending'";
        
        $stmt = $this->db->query($sql, [$token]);
        
        return $stmt->rowCount() > 0;
    }

    private function uploadPhoto($file, $folder) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        $uploadDir = __DIR__ . "/../public/uploads/$folder/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return "/uploads/$folder/$filename";
        }

        return null;
    }

    public function logout() {
        Session::destroy();
    }
}
```

### 5.6 MÓDULO: Models

**models/User.php**
```php
<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        return $stmt->fetch();
    }

    public function getAll($userType = null) {
        $sql = "SELECT * FROM users";
        $params = [];
        
        if ($userType) {
            $sql .= " WHERE user_type = ?";
            $params[] = $userType;
        }
        
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function update($id, $data) {
        $sql = "UPDATE users SET 
                first_name = ?, last_name = ?, phone = ?, 
                birth_date = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->db->query($sql, [
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $data['birth_date'],
            $id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$status, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
}
```

**models/Vehicle.php**
```php
<?php
class Vehicle {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($driverId, $data, $photoPath) {
        $sql = "INSERT INTO vehicles (driver_id, plate_number, color, brand, model, 
                year, seats_capacity, photo_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->query($sql, [
            $driverId,
            $data['plate_number'],
            $data['color'],
            $data['brand'],
            $data['model'],
            $data['year'],
            $data['seats_capacity'],
            $photoPath
        ]);

        return $this->db->getConnection()->lastInsertId();
    }

    public function getByDriver($driverId) {
        $sql = "SELECT * FROM vehicles WHERE driver_id = ? AND is_active = 1 
                ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, [$driverId]);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $sql = "SELECT * FROM vehicles WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $sql = "UPDATE vehicles SET 
                plate_number = ?, color = ?, brand = ?, model = ?, 
                year = ?, seats_capacity = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->db->query($sql, [
            $data['plate_number'],
            $data['color'],
            $data['brand'],
            $data['model'],
            $data['year'],
            $data['seats_capacity'],
            $id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete($id) {
        $sql = "UPDATE vehicles SET is_active = 0 WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
}
```

**models/Ride.php**
```php
<?php
class Ride {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($driverId, $data) {
        $sql = "INSERT INTO rides (driver_id, vehicle_id, ride_name, departure_location, 
                arrival_location, ride_date, ride_time, day_of_week, cost_per_seat, 
                available_seats, total_seats) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->query($sql, [
            $driverId,
            $data['vehicle_id'],
            $data['ride_name'],
            $data['departure_location'],
            $data['arrival_location'],
            $data['ride_date'],
            $data['ride_time'],
            $data['day_of_week'],
            $data['cost_per_seat'],
            $data['available_seats'],
            $data['available_seats'] // total_seats igual a available_seats inicialmente
        ]);

        return $this->db->getConnection()->lastInsertId();
    }

    public function getByDriver($driverId) {
        $sql = "SELECT r.*, v.brand, v.model, v.plate_number 
                FROM rides r 
                JOIN vehicles v ON r.vehicle_id = v.id 
                WHERE r.driver_id = ? AND r.is_active = 1 
                ORDER BY r.ride_date DESC, r.ride_time DESC";
        
        $stmt = $this->db->query($sql, [$driverId]);
        return $stmt->fetchAll();
    }

    public function searchPublic($departure = null, $arrival = null, $orderBy = 'date', $orderDir = 'ASC') {
        $sql = "SELECT r.*, v.brand, v.model, v.year, 
                u.first_name, u.last_name 
                FROM rides r 
                JOIN vehicles v ON r.vehicle_id = v.id 
                JOIN users u ON r.driver_id = u.id 
                WHERE r.is_active = 1 AND r.available_seats > 0 
                AND r.ride_date >= CURDATE()";
        
        $params = [];
        
        if ($departure) {
            $sql .= " AND r.departure_location LIKE ?";
            $params[] = "%$departure%";
        }
        
        if ($arrival) {
            $sql .= " AND r.arrival_location LIKE ?";
            $params[] = "%$arrival%";
        }

        // Ordenamiento
        switch ($orderBy) {
            case 'departure':
                $sql .= " ORDER BY r.departure_location $orderDir";
                break;
            case 'arrival':
                $sql .= " ORDER BY r.arrival_location $orderDir";
                break;
            default:
                $sql .= " ORDER BY r.ride_date $orderDir, r.ride_time $orderDir";
        }

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $sql = "SELECT r.*, v.brand, v.model, v.year, v.plate_number, v.color,
                u.first_name, u.last_name, u.email, u.phone 
                FROM rides r 
                JOIN vehicles v ON r.vehicle_id = v.id 
                JOIN users u ON r.driver_id = u.id 
                WHERE r.id = ?";
        
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $sql = "UPDATE rides SET 
                vehicle_id = ?, ride_name = ?, departure_location = ?, 
                arrival_location = ?, ride_date = ?, ride_time = ?, 
                day_of_week = ?, cost_per_seat = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->db->query($sql, [
            $data['vehicle_id'],
            $data['ride_name'],
            $data['departure_location'],
            $data['arrival_location'],
            $data['ride_date'],
            $data['ride_time'],
            $data['day_of_week'],
            $data['cost_per_seat'],
            $id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function updateAvailableSeats($rideId, $change) {
        $sql = "UPDATE rides SET available_seats = available_seats + ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$change, $rideId]);
        return $stmt->rowCount() > 0;
    }

    public function delete($id) {
        $sql = "UPDATE rides SET is_active = 0 WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
}
```

**models/Reservation.php**
```php
<?php
class Reservation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($passengerId, $rideId, $seatsRequested, $totalCost) {
        $sql = "INSERT INTO reservations (passenger_id, ride_id, seats_requested, total_cost) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->query($sql, [$passengerId, $rideId, $seatsRequested, $totalCost]);
        
        // Actualizar asientos disponibles
        $rideModel = new Ride();
        $rideModel->updateAvailableSeats($rideId, -$seatsRequested);

        return $this->db->getConnection()->lastInsertId();
    }

    public function getByPassenger($passengerId) {
        $sql = "SELECT res.*, r.ride_name, r.departure_location, r.arrival_location,
                r.ride_date, r.ride_time, v.brand, v.model, 
                u.first_name as driver_first_name, u.last_name as driver_last_name 
                FROM reservations res 
                JOIN rides r ON res.ride_id = r.id 
                JOIN vehicles v ON r.vehicle_id = v.id 
                JOIN users u ON r.driver_id = u.id 
                WHERE res.passenger_id = ? 
                ORDER BY r.ride_date DESC, r.ride_time DESC";
        
        $stmt = $this->db->query($sql, [$passengerId]);
        return $stmt->fetchAll();
    }

    public function getByDriver($driverId) {
        $sql = "SELECT res.*, r.ride_name, r.departure_location, r.arrival_location,
                r.ride_date, r.ride_time, 
                u.first_name as passenger_first_name, u.last_name as passenger_last_name,
                u.email as passenger_email, u.phone as passenger_phone 
                FROM reservations res 
                JOIN rides r ON res.ride_id = r.id 
                JOIN users u ON res.passenger_id = u.id 
                WHERE r.driver_id = ? 
                ORDER BY res.created_at DESC";
        
        $stmt = $this->db->query($sql, [$driverId]);
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE reservations SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->query($sql, [$status, $id]);
        return $stmt->rowCount() > 0;
    }

    public function cancel($id) {
        // Obtener información de la reserva
        $sql = "SELECT ride_id, seats_requested FROM reservations WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        $reservation = $stmt->fetch();

        if ($reservation) {
            // Devolver asientos disponibles
            $rideModel = new Ride();
            $rideModel->updateAvailableSeats($reservation['ride_id'], $reservation['seats_requested']);
            
            // Cancelar reserva
            return $this->updateStatus($id, 'cancelled');
        }

        return false;
    }

    public function getPendingByMinutes($minutes) {
        $sql = "SELECT res.*, r.driver_id, u.email as driver_email, 
                u.first_name as driver_first_name 
                FROM reservations res 
                JOIN rides r ON res.ride_id = r.id 
                JOIN users u ON r.driver_id = u.id 
                WHERE res.status = 'pending' 
                AND TIMESTAMPDIFF(MINUTE, res.created_at, NOW()) >= ? 
                GROUP BY r.driver_id";
        
        $stmt = $this->db->query($sql, [$minutes]);
        return $stmt->fetchAll();
    }
}
```

---

## 6. Script de Consola

### 6.1 Script para Notificaciones de Reservas Pendientes

**scripts/check_pending_reservations.php**
```php
<?php
// Incluir archivos necesarios
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/email.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Email.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Configurar tiempo de espera (en minutos)
$MINUTES_THRESHOLD = 30; // Puedes cambiar este valor

echo "=== Verificando reservas pendientes ===\n";
echo "Buscando reservas con más de {$MINUTES_THRESHOLD} minutos sin respuesta...\n\n";

// Obtener reservas pendientes
$reservationModel = new Reservation();
$pendingReservations = $reservationModel->getPendingByMinutes($MINUTES_THRESHOLD);

if (empty($pendingReservations)) {
    echo "No se encontraron reservas pendientes.\n";
    exit(0);
}

// Agrupar por conductor
$driverReservations = [];
foreach ($pendingReservations as $reservation) {
    $driverId = $reservation['driver_id'];
    if (!isset($driverReservations[$driverId])) {
        $driverReservations[$driverId] = [
            'email' => $reservation['driver_email'],
            'name' => $reservation['driver_first_name'],
            'count' => 0
        ];
    }
    $driverReservations[$driverId]['count']++;
}

// Enviar emails
$emailService = new Email();
$sentCount = 0;

foreach ($driverReservations as $driverId => $driverInfo) {
    echo "Enviando notificación a {$driverInfo['name']} ({$driverInfo['email']})...\n";
    echo "Reservas pendientes: {$driverInfo['count']}\n";
    
    $result = $emailService->sendPendingReservationNotification(
        $driverInfo['email'],
        $driverInfo['name'],
        $driverInfo['count']
    );
    
    if ($result) {
        echo "✓ Email enviado exitosamente\n\n";
        $sentCount++;
    } else {
        echo "✗ Error al enviar email\n\n";
    }
}

echo "=== Proceso completado ===\n";
echo "Emails enviados: $sentCount\n";
```

### 6.2 Configurar Cron Job (Linux)

```bash
# Editar crontab
crontab -e

# Ejecutar cada 30 minutos
*/30 * * * * /usr/bin/php /home/bob/Github/Rubber/scripts/check_pending_reservations.php >> /home/bob/Github/Rubber/logs/cron.log 2>&1
```

---

## 7. Seguridad

### 7.1 Medidas de Seguridad a Implementar

1. **Protección contra SQL Injection**
   - Usar prepared statements (PDO)
   - Nunca concatenar queries directamente

2. **Protección XSS**
   - Escapar todo output: `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`
   - Validar y sanitizar inputs

3. **CSRF Protection**
   - Implementar tokens CSRF en formularios
   - Validar tokens en cada POST request

4. **Password Security**
   - Usar `password_hash()` y `password_verify()`
   - Mínimo 6 caracteres (mejor 8+)

5. **File Upload Security**
   - Validar tipo MIME
   - Renombrar archivos
   - Limitar tamaño
   - Almacenar fuera del webroot si es posible

6. **Session Security**
   - Regenerar session ID al login
   - Usar HTTPS en producción
   - Configurar session timeout

### 7.2 Ejemplo de CSRF Protection

**core/CSRF.php**
```php
<?php
class CSRF {
    public static function generateToken() {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    public static function validateToken($token) {
        return hash_equals(Session::get('csrf_token', ''), $token);
    }

    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}
```

---

## 8. Testing y Validación

### 8.1 Checklist de Pruebas

**Autenticación:**
- [ ] Registro de pasajero
- [ ] Registro de chofer
- [ ] Activación de cuenta por email
- [ ] Login exitoso
- [ ] Login con cuenta pendiente (debe fallar)
- [ ] Login con cuenta inactiva (debe fallar)
- [ ] Logout

**Panel Administrativo:**
- [ ] Crear usuario administrador
- [ ] Listar todos los usuarios
- [ ] Desactivar usuarios
- [ ] Activar usuarios

**Gestión de Vehículos (Chofer):**
- [ ] Crear vehículo
- [ ] Listar vehículos propios
- [ ] Editar vehículo
- [ ] Eliminar vehículo
- [ ] Upload de foto de vehículo

**Gestión de Rides (Chofer):**
- [ ] Crear ride
- [ ] Listar rides propios
- [ ] Editar ride
- [ ] Eliminar ride
- [ ] Asignar vehículo a ride

**Búsqueda y Reservas (Pasajero):**
- [ ] Búsqueda pública sin login
- [ ] Búsqueda con filtros
- [ ] Ordenamiento por fecha, origen, destino
- [ ] Crear reserva (requiere login)
- [ ] Listar mis reservas
- [ ] Cancelar reserva

**Gestión de Reservas (Chofer):**
- [ ] Ver reservas de mis rides
- [ ] Aceptar reserva
- [ ] Rechazar reserva
- [ ] Verificar actualización de asientos disponibles

**Script:**
- [ ] Ejecutar script manualmente
- [ ] Verificar detección de reservas pendientes
- [ ] Verificar envío de emails

**Seguridad:**
- [ ] Intentar acceso sin autenticación
- [ ] Intentar acceso a recursos de otros usuarios
- [ ] Validación de formularios
- [ ] Upload de archivos maliciosos

---

## 9. Deployment

### 9.1 Preparación para Producción

1. **Configuración de Entorno**
```php
// config/constants.php
define('ENVIRONMENT', 'production'); // o 'development'
define('DEBUG_MODE', ENVIRONMENT === 'development');

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
```

2. **.htaccess para Apache**
```apache
RewriteEngine On
RewriteBase /

# Redirigir todo a public/
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L]

# Proteger archivos sensibles
<FilesMatch "\.(php|sql|md|json)$">
    Order allow,deny
    Deny from all
</FilesMatch>

<Directory "/home/bob/Github/Rubber/public">
    Allow from all
</Directory>
```

3. **Optimizaciones**
   - Habilitar OPCache
   - Minificar CSS/JS
   - Optimizar imágenes
   - Configurar caché de navegador

### 9.2 Checklist Final

- [ ] Todas las funcionalidades implementadas
- [ ] Tests pasando
- [ ] Sin errores ni warnings
- [ ] Código comentado y documentado
- [ ] README actualizado
- [ ] .gitignore configurado
- [ ] Commits descriptivos en Git
- [ ] Base de datos documentada
- [ ] Credenciales de admin documentadas
- [ ] Script de consola probado
- [ ] Validaciones funcionando
- [ ] Emails enviándose correctamente
- [ ] Responsive design
- [ ] Cross-browser testing

---

## 10. Estructura de Commits (Buenas Prácticas)

### 10.1 Formato de Commits

```
tipo(alcance): descripción breve

Descripción más detallada si es necesario

Refs: #issue_number
```

### 10.2 Tipos de Commits

- **feat**: Nueva funcionalidad
- **fix**: Corrección de bugs
- **docs**: Documentación
- **style**: Formato, punto y coma faltantes, etc
- **refactor**: Refactorización de código
- **test**: Agregar tests
- **chore**: Tareas de mantenimiento

### 10.3 Ejemplos

```bash
git commit -m "feat(auth): implementar registro de usuarios"
git commit -m "fix(reservations): corregir actualización de asientos disponibles"
git commit -m "docs(readme): agregar instrucciones de instalación"
git commit -m "refactor(database): mejorar queries de búsqueda"
```

---

## 11. Recursos Adicionales

### 11.1 Documentación Recomendada

- PHP Official Documentation: https://www.php.net/docs.php
- PDO Tutorial: https://phpdelusions.net/pdo
- PHPMailer: https://github.com/PHPMailer/PHPMailer
- OWASP Security: https://owasp.org/www-project-top-ten/

### 11.2 Herramientas Útiles

- **XAMPP/LAMP**: Servidor local
- **MySQL Workbench**: Gestión de BD
- **Postman**: Testing de endpoints
- **Git**: Control de versiones
- **VS Code Extensions**:
  - PHP Intelephense
  - PHP Debug
  - Git Lens
  - ESLint

---

## 12. Cronograma Detallado Día a Día

### Semana 1
**Día 1-2**: Setup inicial
- Configurar Git
- Crear estructura de carpetas
- Configurar base de datos
- Instalar dependencias

**Día 3-4**: Core y Database
- Implementar Database class
- Implementar Session class
- Implementar Validator class
- Crear tablas en BD

**Día 5-7**: Autenticación básica
- Formularios de registro
- Controller de autenticación
- Sistema de activación por email
- Formulario de login

### Semana 2
**Día 8-10**: Panel de usuarios
- Dashboard de admin
- Dashboard de chofer
- Dashboard de pasajero
- Gestión de perfil

**Día 11-14**: Gestión de vehículos
- Modelo de vehículos
- CRUD completo de vehículos
- Upload de fotos
- Validaciones

### Semana 3
**Día 15-17**: Gestión de Rides
- Modelo de rides
- CRUD completo de rides
- Asociación con vehículos
- Validaciones de fechas/horarios

**Día 18-21**: Búsqueda pública
- Página de búsqueda
- Filtros de búsqueda
- Ordenamiento
- Vista de detalles

### Semana 4
**Día 22-24**: Sistema de reservas
- Modelo de reservas
- Crear reserva
- Listar reservas
- Actualización de asientos

**Día 25-28**: Gestión de reservas
- Aceptar/rechazar (chofer)
- Cancelar (pasajero)
- Notificaciones
- Estados de reservas

### Semana 5
**Día 29-31**: Panel administrativo
- Listar usuarios
- Crear admin
- Activar/desactivar usuarios
- Estadísticas

**Día 32-35**: Script de consola
- Implementar lógica de detección
- Email de notificaciones
- Testing del script
- Configurar cron job

### Semana 6
**Día 36-38**: Seguridad
- CSRF protection
- Validaciones adicionales
- Sanitización de inputs
- File upload security

**Día 39-42**: UI/UX
- Mejorar estilos
- Responsive design
- Mensajes de usuario
- Loading states

### Semana 7
**Día 43-45**: Testing
- Probar todas las funcionalidades
- Corrección de bugs
- Testing de seguridad
- Cross-browser testing

**Día 46-49**: Refinamiento
- Optimizaciones
- Code cleanup
- Performance improvements
- Final touches

### Semana 8
**Día 50-52**: Documentación
- README completo
- Comentarios en código
- Guía de instalación
- Documentación de API

**Día 53-56**: Preparación para entrega
- Deployment testing
- Final review
- Preparar presentación
- Buffer para imprevistos

---

## 13. Consejos Finales

### 13.1 Durante el Desarrollo

1. **Commits frecuentes**: Haz commits pequeños y frecuentes
2. **Testing continuo**: Prueba cada funcionalidad inmediatamente
3. **Código limpio**: Mantén el código ordenado y comentado
4. **Backup**: Haz backup regular de la base de datos
5. **Documentación**: Documenta mientras desarrollas, no al final

### 13.2 Resolución de Problemas Comunes

**Problema**: No se envían emails
- Verificar configuración SMTP
- Revisar firewall
- Usar servicios como Mailtrap para desarrollo

**Problema**: Error de permisos en uploads
```bash
chmod -R 777 public/uploads/
```

**Problema**: Sesiones no persisten
- Verificar `session_start()` al inicio
- Revisar configuración de PHP
- Verificar cookies del navegador

**Problema**: SQL errors
- Habilitar error reporting
- Usar try-catch
- Revisar PDO error mode

### 13.3 Checklist Pre-Entrega

- [ ] Código en GitHub con historial de commits
- [ ] README con instrucciones claras
- [ ] Base de datos con datos de prueba
- [ ] Usuario admin funcional (admin@carpooling.com / password)
- [ ] Todas las funcionalidades operativas
- [ ] Sin errores en consola del navegador
- [ ] Sin warnings de PHP
- [ ] Formularios validados
- [ ] Responsive en móvil/tablet/desktop
- [ ] Scripts documentados
- [ ] Archivos de configuración de ejemplo incluidos

---

## ¡Éxito en tu proyecto!

Esta guía cubre todos los aspectos necesarios para completar exitosamente el proyecto. Recuerda adaptar los tiempos según tu disponibilidad y experiencia. Lo más importante es mantener un progreso constante y documentar bien tu trabajo.

Para cualquier duda específica durante el desarrollo, consulta la documentación oficial de PHP y las buenas prácticas de desarrollo web.
