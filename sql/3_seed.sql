-- =========================================
-- DATOS DE PRUEBA - CARPOOLING SYSTEM
-- Incluye usuarios, vehículos, viajes y reservas
-- =========================================

USE carpooling_db;

-- =========================================
-- USUARIOS
-- Password para todos: password123
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- =========================================

-- Admin
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('admin', 'Admin', 'System', '000000000', '1990-01-01', 'admin@carpooling.com', '8888-8888', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Choferes
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('driver', 'Juan', 'Pérez', '101110111', '1985-03-15', 'juan.perez@email.com', '8888-1111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active'),
('driver', 'María', 'García', '202220222', '1990-07-22', 'maria.garcia@email.com', '8888-2222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active'),
('driver', 'Carlos', 'Rodríguez', '303330333', '1988-11-30', 'carlos.rodriguez@email.com', '8888-3333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Pasajeros
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('passenger', 'Ana', 'Martínez', '404440444', '1995-02-14', 'ana.martinez@email.com', '8888-4444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active'),
('passenger', 'Pedro', 'López', '505550555', '1992-08-25', 'pedro.lopez@email.com', '8888-5555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active'),
('passenger', 'Lucía', 'Fernández', '606660666', '1998-12-05', 'lucia.fernandez@email.com', '8888-6666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Usuario pendiente
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status, activation_token) VALUES
('passenger', 'Usuario', 'Pendiente', '707770777', '2000-05-10', 'pendiente@email.com', '8888-7777', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pending', 'test_activation_token_123456');

-- Usuario inactivo
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('passenger', 'Usuario', 'Inactivo', '808880888', '1987-09-18', 'inactivo@email.com', '8888-8888', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inactive');

-- =========================================
-- VEHÍCULOS
-- =========================================

-- Vehículos de Juan Pérez (driver_id = 2)
INSERT INTO vehicles (driver_id, plate_number, color, brand, model, year, seats_capacity, is_active) VALUES
(2, 'ABC-123', 'Rojo', 'Toyota', 'Corolla', 2020, 4, 1),
(2, 'DEF-456', 'Azul', 'Honda', 'Civic', 2019, 4, 1);

-- Vehículos de María García (driver_id = 3)
INSERT INTO vehicles (driver_id, plate_number, color, brand, model, year, seats_capacity, is_active) VALUES
(3, 'GHI-789', 'Blanco', 'Mazda', 'CX-5', 2021, 5, 1),
(3, 'JKL-012', 'Negro', 'Nissan', 'Sentra', 2018, 4, 0);

-- Vehículos de Carlos Rodríguez (driver_id = 4)
INSERT INTO vehicles (driver_id, plate_number, color, brand, model, year, seats_capacity, is_active) VALUES
(4, 'MNO-345', 'Gris', 'Hyundai', 'Elantra', 2022, 4, 1),
(4, 'PQR-678', 'Verde', 'Kia', 'Sportage', 2020, 5, 1);

-- =========================================
-- VIAJES
-- =========================================

-- Viajes futuros de Juan Pérez
INSERT INTO rides (driver_id, vehicle_id, ride_name, departure_location, arrival_location, ride_date, ride_time, day_of_week, cost_per_seat, available_seats, total_seats, is_active) VALUES
(2, 1, 'San José - Cartago Mañana', 'San José Centro', 'Cartago Centro', '2025-10-20', '07:00:00', 'Monday', 1500.00, 4, 4, 1),
(2, 1, 'San José - Cartago Tarde', 'San José Centro', 'Cartago Centro', '2025-10-20', '17:00:00', 'Monday', 1500.00, 4, 4, 1),
(2, 1, 'Cartago - San José Mañana', 'Cartago Centro', 'San José Centro', '2025-10-21', '06:30:00', 'Tuesday', 1500.00, 4, 4, 1),
(2, 1, 'Cartago - San José Tarde', 'Cartago Centro', 'San José Centro', '2025-10-21', '18:00:00', 'Tuesday', 1500.00, 4, 4, 1);

-- Viajes de María García
INSERT INTO rides (driver_id, vehicle_id, ride_name, departure_location, arrival_location, ride_date, ride_time, day_of_week, cost_per_seat, available_seats, total_seats, is_active) VALUES
(3, 3, 'San José - Heredia Expreso', 'San José Centro', 'Heredia Centro', '2025-10-22', '07:30:00', 'Wednesday', 1200.00, 5, 5, 1),
(3, 3, 'Heredia - San José Retorno', 'Heredia Centro', 'San José Centro', '2025-10-22', '17:30:00', 'Wednesday', 1200.00, 5, 5, 1);

-- Viajes de Carlos Rodríguez
INSERT INTO rides (driver_id, vehicle_id, ride_name, departure_location, arrival_location, ride_date, ride_time, day_of_week, cost_per_seat, available_seats, total_seats, is_active) VALUES
(4, 5, 'Alajuela - San José Diario', 'Alajuela Centro', 'San José Centro', '2025-10-23', '06:00:00', 'Thursday', 1800.00, 4, 4, 1),
(4, 5, 'San José - Alajuela Retorno', 'San José Centro', 'Alajuela Centro', '2025-10-23', '18:30:00', 'Thursday', 1800.00, 4, 4, 1);

-- Viajes pasados (para historial)
INSERT INTO rides (driver_id, vehicle_id, ride_name, departure_location, arrival_location, ride_date, ride_time, day_of_week, cost_per_seat, available_seats, total_seats, is_active) VALUES
(2, 1, 'San José - Cartago Pasado', 'San José Centro', 'Cartago Centro', '2025-10-10', '07:00:00', 'Friday', 1500.00, 2, 4, 0),
(3, 3, 'Heredia - San José Pasado', 'Heredia Centro', 'San José Centro', '2025-10-11', '17:00:00', 'Saturday', 1200.00, 3, 5, 0);

-- Viaje para pruebas
INSERT INTO rides (driver_id, vehicle_id, ride_name, departure_location, arrival_location, ride_date, ride_time, day_of_week, cost_per_seat, available_seats, total_seats, is_active) VALUES
(2, 2, 'Prueba Reservas Múltiples', 'San José', 'Puntarenas', '2025-10-25', '08:00:00', 'Saturday', 3500.00, 4, 4, 1);

-- =========================================
-- RESERVAS
-- Los triggers actualizarán automáticamente available_seats
-- =========================================

-- Reservas futuras
INSERT INTO reservations (passenger_id, ride_id, seats_requested, status, total_cost) VALUES
(5, 1, 2, 'pending', 3000.00),
(6, 5, 1, 'accepted', 1200.00),
(7, 7, 1, 'pending', 1800.00),
(5, 3, 1, 'accepted', 1500.00),
(5, 5, 2, 'pending', 2400.00),
(6, 7, 1, 'accepted', 1800.00),
(6, 8, 1, 'rejected', 1800.00),
(7, 2, 2, 'cancelled', 3000.00),
(7, 6, 1, 'accepted', 1200.00);

-- Reservas pasadas
INSERT INTO reservations (passenger_id, ride_id, seats_requested, status, total_cost, created_at) VALUES
(5, 10, 2, 'accepted', 4000.00, '2025-10-09 10:00:00'),
(6, 10, 1, 'cancelled', 2000.00, '2025-10-09 11:00:00'),
(7, 11, 2, 'rejected', 3600.00, '2025-10-10 09:00:00');
