-- =========================================
-- SCHEMA DE BASE DE DATOS - CARPOOLING SYSTEM
-- =========================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS carpooling_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE carpooling_db;

-- =========================================
-- TABLA: users
-- Almacena información de todos los usuarios
-- =========================================
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
    INDEX idx_cedula (cedula),
    INDEX idx_status (status),
    INDEX idx_user_type (user_type),
    INDEX idx_activation_token (activation_token)
) ENGINE=InnoDB;

-- =========================================
-- TABLA: vehicles
-- Almacena vehículos registrados por choferes
-- =========================================
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
    INDEX idx_driver (driver_id),
    INDEX idx_plate (plate_number),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- =========================================
-- TABLA: rides
-- Almacena viajes creados por choferes
-- =========================================
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
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_date (ride_date),
    INDEX idx_locations (departure_location, arrival_location),
    INDEX idx_active (is_active),
    INDEX idx_available_seats (available_seats)
) ENGINE=InnoDB;

-- =========================================
-- TABLA: reservations
-- Almacena reservas de pasajeros en viajes
-- =========================================
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
    INDEX idx_created (created_at),
    
    -- Evitar reservas duplicadas pendientes o aceptadas
    UNIQUE KEY unique_active_reservation (passenger_id, ride_id, status)
) ENGINE=InnoDB;

-- =========================================
-- VISTAS ÚTILES
-- =========================================

-- Vista de rides con información completa
CREATE OR REPLACE VIEW v_rides_complete AS
SELECT 
    r.*,
    u.first_name AS driver_first_name,
    u.last_name AS driver_last_name,
    u.email AS driver_email,
    u.phone AS driver_phone,
    u.photo_path AS driver_photo,
    v.plate_number,
    v.color AS vehicle_color,
    v.brand AS vehicle_brand,
    v.model AS vehicle_model,
    v.year AS vehicle_year,
    v.photo_path AS vehicle_photo,
    (r.total_seats - r.available_seats) AS reserved_seats
FROM rides r
INNER JOIN users u ON r.driver_id = u.id
INNER JOIN vehicles v ON r.vehicle_id = v.id;

-- Vista de reservas con información completa
CREATE OR REPLACE VIEW v_reservations_complete AS
SELECT 
    res.*,
    u.first_name AS passenger_first_name,
    u.last_name AS passenger_last_name,
    u.email AS passenger_email,
    u.phone AS passenger_phone,
    u.photo_path AS passenger_photo,
    r.ride_name,
    r.departure_location,
    r.arrival_location,
    r.ride_date,
    r.ride_time,
    r.driver_id,
    d.first_name AS driver_first_name,
    d.last_name AS driver_last_name,
    d.email AS driver_email,
    d.phone AS driver_phone,
    v.brand AS vehicle_brand,
    v.model AS vehicle_model,
    v.plate_number AS vehicle_plate
FROM reservations res
INNER JOIN users u ON res.passenger_id = u.id
INNER JOIN rides r ON res.ride_id = r.id
INNER JOIN users d ON r.driver_id = d.id
INNER JOIN vehicles v ON r.vehicle_id = v.id;

-- =========================================
-- TRIGGERS
-- =========================================

-- Trigger para validar asientos disponibles antes de insertar reserva
DELIMITER //
CREATE TRIGGER before_reservation_insert
BEFORE INSERT ON reservations
FOR EACH ROW
BEGIN
    DECLARE available INT;
    
    SELECT available_seats INTO available
    FROM rides
    WHERE id = NEW.ride_id;
    
    IF NEW.seats_requested > available THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No hay suficientes asientos disponibles';
    END IF;
END//
DELIMITER ;

-- Trigger para actualizar asientos disponibles después de insertar reserva
DELIMITER //
CREATE TRIGGER after_reservation_insert
AFTER INSERT ON reservations
FOR EACH ROW
BEGIN
    IF NEW.status = 'pending' OR NEW.status = 'accepted' THEN
        UPDATE rides
        SET available_seats = available_seats - NEW.seats_requested
        WHERE id = NEW.ride_id;
    END IF;
END//
DELIMITER ;

-- Trigger para actualizar asientos disponibles cuando cambia el estado de la reserva
DELIMITER //
CREATE TRIGGER after_reservation_update
AFTER UPDATE ON reservations
FOR EACH ROW
BEGIN
    -- Si se cancela o rechaza, devolver los asientos
    IF (OLD.status = 'pending' OR OLD.status = 'accepted') 
       AND (NEW.status = 'cancelled' OR NEW.status = 'rejected') THEN
        UPDATE rides
        SET available_seats = available_seats + OLD.seats_requested
        WHERE id = OLD.ride_id;
    END IF;
    
    -- Si se acepta una reserva pendiente (no hacer nada, ya se restaron)
    IF OLD.status = 'pending' AND NEW.status = 'accepted' THEN
        -- Los asientos ya fueron restados al crear la reserva
        -- No se necesita ninguna acción adicional
        SET @dummy = 0;
    END IF;
END//
DELIMITER ;

-- =========================================
-- PROCEDIMIENTOS ALMACENADOS
-- =========================================

-- Procedimiento para obtener estadísticas de un chofer
DELIMITER //
CREATE PROCEDURE sp_driver_statistics(IN p_driver_id INT)
BEGIN
    SELECT 
        COUNT(DISTINCT v.id) AS total_vehicles,
        COUNT(DISTINCT r.id) AS total_rides,
        COUNT(DISTINCT res.id) AS total_reservations,
        SUM(CASE WHEN res.status = 'pending' THEN 1 ELSE 0 END) AS pending_reservations,
        SUM(CASE WHEN res.status = 'accepted' THEN 1 ELSE 0 END) AS accepted_reservations,
        SUM(CASE WHEN res.status = 'accepted' THEN res.total_cost ELSE 0 END) AS total_earnings
    FROM users u
    LEFT JOIN vehicles v ON u.id = v.driver_id AND v.is_active = 1
    LEFT JOIN rides r ON u.id = r.driver_id AND r.is_active = 1
    LEFT JOIN reservations res ON r.id = res.ride_id
    WHERE u.id = p_driver_id
    GROUP BY u.id;
END//
DELIMITER ;

-- Procedimiento para obtener estadísticas de un pasajero
DELIMITER //
CREATE PROCEDURE sp_passenger_statistics(IN p_passenger_id INT)
BEGIN
    SELECT 
        COUNT(*) AS total_reservations,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_reservations,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS accepted_reservations,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_reservations,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_reservations,
        SUM(total_cost) AS total_spent
    FROM reservations
    WHERE passenger_id = p_passenger_id;
END//
DELIMITER ;

-- Procedimiento para obtener reservas pendientes antiguas
DELIMITER //
CREATE PROCEDURE sp_get_old_pending_reservations(IN p_minutes INT)
BEGIN
    SELECT 
        r.driver_id,
        u.email AS driver_email,
        u.first_name AS driver_first_name,
        COUNT(*) AS pending_count
    FROM reservations res
    INNER JOIN rides r ON res.ride_id = r.id
    INNER JOIN users u ON r.driver_id = u.id
    WHERE res.status = 'pending'
    AND TIMESTAMPDIFF(MINUTE, res.created_at, NOW()) >= p_minutes
    GROUP BY r.driver_id, u.email, u.first_name;
END//
DELIMITER ;

-- =========================================
-- FUNCIONES
-- =========================================

-- Función para calcular la edad de un usuario
DELIMITER //
CREATE FUNCTION fn_calculate_age(birth_date DATE)
RETURNS INT
DETERMINISTIC
BEGIN
    RETURN TIMESTAMPDIFF(YEAR, birth_date, CURDATE());
END//
DELIMITER ;

-- Función para verificar si un ride está lleno
DELIMITER //
CREATE FUNCTION fn_is_ride_full(p_ride_id INT)
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE available INT;
    
    SELECT available_seats INTO available
    FROM rides
    WHERE id = p_ride_id;
    
    RETURN available <= 0;
END//
DELIMITER ;

-- =========================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =========================================

-- Índice compuesto para búsquedas de rides
CREATE INDEX idx_rides_search ON rides(departure_location, arrival_location, ride_date, is_active);

-- Índice para reservas de un pasajero con estado
CREATE INDEX idx_passenger_status ON reservations(passenger_id, status);

-- Índice para consultas de rides futuros
CREATE INDEX idx_future_rides ON rides(ride_date, is_active) WHERE ride_date >= CURDATE();
