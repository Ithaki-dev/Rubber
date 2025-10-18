-- =========================================
-- SCHEMA SIMPLIFICADO - CARPOOLING SYSTEM
-- Solo tablas, vistas y triggers
-- Sin procedimientos almacenados (bug de XAMPP)
-- =========================================

USE carpooling_db;

-- =========================================
-- TABLAS
-- =========================================

-- Tabla: users
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

-- Tabla: vehicles
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

-- Tabla: rides
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

-- Tabla: reservations
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
    
    UNIQUE KEY unique_active_reservation (passenger_id, ride_id, status)
) ENGINE=InnoDB;

-- =========================================
-- VISTAS
-- =========================================

-- Vista: rides con información completa
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

-- Vista: reservations con información completa
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
-- ÍNDICES ADICIONALES
-- =========================================

CREATE INDEX idx_rides_search ON rides(departure_location, arrival_location, ride_date, is_active);
CREATE INDEX idx_passenger_status ON reservations(passenger_id, status);
