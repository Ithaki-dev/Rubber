-- =========================================
-- DATOS INICIALES (SEED) - CARPOOLING SYSTEM
-- =========================================

USE carpooling_db;

-- =========================================
-- USUARIOS
-- =========================================

-- Usuario Administrador por defecto
-- Email: admin@carpooling.com
-- Password: admin123
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('admin', 'Admin', 'System', '000000000', '1990-01-01', 'admin@carpooling.com', '8888-8888', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Choferes de prueba
-- Email: juan.perez@email.com / Password: password123
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('driver', 'Juan', 'Pérez', '101110111', '1985-03-15', 'juan.perez@email.com', '8888-1111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Email: maria.garcia@email.com / Password: password123
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('driver', 'María', 'García', '202220222', '1990-07-22', 'maria.garcia@email.com', '8888-2222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Email: carlos.rodriguez@email.com / Password: password123
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('driver', 'Carlos', 'Rodríguez', '303330333', '1988-11-30', 'carlos.rodriguez@email.com', '8888-3333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Pasajeros de prueba
-- Email: ana.martinez@email.com / Password: password123
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('passenger', 'Ana', 'Martínez', '404440444', '1995-02-14', 'ana.martinez@email.com', '8888-4444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Email: pedro.lopez@email.com / Password: password123
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('passenger', 'Pedro', 'López', '505550555', '1992-08-25', 'pedro.lopez@email.com', '8888-5555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Email: lucia.fernandez@email.com / Password: password123
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('passenger', 'Lucía', 'Fernández', '606660666', '1998-12-05', 'lucia.fernandez@email.com', '8888-6666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Usuario pendiente de activación
-- Email: pendiente@email.com / Password: password123
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status, activation_token) VALUES
('passenger', 'Usuario', 'Pendiente', '707770777', '2000-05-10', 'pendiente@email.com', '8888-7777', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pending', 'test_activation_token_123456');

-- Usuario inactivo
-- Email: inactivo@email.com / Password: password123
INSERT INTO users (user_type, first_name, last_name, cedula, birth_date, email, phone, password_hash, status) VALUES
('passenger', 'Usuario', 'Inactivo', '808880888', '1997-09-18', 'inactivo@email.com', '8888-8888', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inactive');

-- =========================================
-- VEHÍCULOS
-- =========================================

-- Vehículos de Juan Pérez (driver_id = 2)
INSERT INTO vehicles (driver_id, plate_number, color, brand, model, year, seats_capacity) VALUES
(2, 'ABC-123', 'Rojo', 'Toyota', 'Corolla', 2020, 4),
(2, 'DEF-456', 'Azul', 'Honda', 'Civic', 2019, 4);

-- Vehículos de María García (driver_id = 3)
INSERT INTO vehicles (driver_id, plate_number, color, brand, model, year, seats_capacity) VALUES
(3, 'GHI-789', 'Blanco', 'Nissan', 'Sentra', 2021, 4),
(3, 'JKL-012', 'Negro', 'Mazda', 'CX-5', 2022, 6);

-- Vehículos de Carlos Rodríguez (driver_id = 4)
INSERT INTO vehicles (driver_id, plate_number, color, brand, model, year, seats_capacity) VALUES
(4, 'MNO-345', 'Gris', 'Hyundai', 'Elantra', 2020, 4),
(4, 'PQR-678', 'Verde', 'Kia', 'Sportage', 2021, 5);

-- =========================================
-- VIAJES (RIDES)
-- =========================================

-- Rides de Juan Pérez
INSERT INTO rides (driver_id, vehicle_id, ride_name, departure_location, arrival_location, ride_date, ride_time, day_of_week, cost_per_seat, available_seats, total_seats) VALUES
(2, 1, 'San José - Heredia Mañana', 'San José Centro', 'Heredia Centro', '2025-10-20', '07:00:00', 'Monday', 2000.00, 3, 3),
(2, 1, 'Heredia - San José Tarde', 'Heredia Centro', 'San José Centro', '2025-10-20', '17:00:00', 'Monday', 2000.00, 3, 3),
(2, 2, 'San José - Cartago', 'San José Este', 'Cartago Centro', '2025-10-21', '08:00:00', 'Tuesday', 2500.00, 4, 4);

-- Rides de María García
INSERT INTO rides (driver_id, vehicle_id, ride_name, departure_location, arrival_location, ride_date, ride_time, day_of_week, cost_per_seat, available_seats, total_seats) VALUES
(3, 3, 'Alajuela - San José Express', 'Alajuela Centro', 'San José Centro', '2025-10-20', '06:30:00', 'Monday', 1800.00, 4, 4),
(3, 4, 'San José - Escazú Premium', 'San José Oeste', 'Escazú', '2025-10-22', '07:30:00', 'Wednesday', 3000.00, 5, 5),
(3, 3, 'San José - Alajuela Noche', 'San José Centro', 'Alajuela Centro', '2025-10-23', '18:00:00', 'Thursday', 1800.00, 4, 4);

-- Rides de Carlos Rodríguez
INSERT INTO rides (driver_id, vehicle_id, ride_name, departure_location, arrival_location, ride_date, ride_time, day_of_week, cost_per_seat, available_seats, total_seats) VALUES
(4, 5, 'Cartago - San José Madrugada', 'Cartago Centro', 'San José Este', '2025-10-20', '05:30:00', 'Monday', 2200.00, 4, 4),
(4, 6, 'San José - Guanacaste Weekend', 'San José Centro', 'Liberia', '2025-10-25', '06:00:00', 'Saturday', 15000.00, 4, 4),
(4, 5, 'Heredia - Alajuela Diario', 'Heredia Centro', 'Alajuela Centro', '2025-10-21', '16:00:00', 'Tuesday', 1500.00, 4, 4);

-- Rides pasados (para historial)
INSERT INTO rides (driver_id, vehicle_id, ride_name, departure_location, arrival_location, ride_date, ride_time, day_of_week, cost_per_seat, available_seats, total_seats) VALUES
(2, 1, 'San José - Heredia (Pasado)', 'San José Centro', 'Heredia Centro', '2025-10-10', '07:00:00', 'Friday', 2000.00, 0, 3),
(3, 3, 'Alajuela - San José (Pasado)', 'Alajuela Centro', 'San José Centro', '2025-10-11', '06:30:00', 'Saturday', 1800.00, 1, 4);

-- =========================================
-- RESERVAS
-- =========================================

-- Reservas activas (pendientes y aceptadas)
INSERT INTO reservations (passenger_id, ride_id, seats_requested, status, total_cost) VALUES
-- Ana Martínez
(5, 1, 1, 'accepted', 2000.00),  -- San José - Heredia Mañana
(5, 4, 2, 'pending', 3600.00),   -- Alajuela - San José Express

-- Pedro López  
(6, 2, 1, 'accepted', 2000.00),  -- Heredia - San José Tarde
(6, 5, 1, 'pending', 3000.00),   -- San José - Escazú Premium

-- Lucía Fernández
(7, 3, 1, 'pending', 2500.00),   -- San José - Cartago
(7, 7, 2, 'accepted', 4400.00);  -- Cartago - San José Madrugada

-- Reservas pasadas
INSERT INTO reservations (passenger_id, ride_id, seats_requested, status, total_cost, created_at) VALUES
(5, 10, 2, 'accepted', 4000.00, '2025-10-09 10:00:00'),
(6, 10, 1, 'cancelled', 2000.00, '2025-10-09 11:00:00'),
(7, 11, 2, 'rejected', 3600.00, '2025-10-10 09:00:00');

-- Reservas antiguas pendientes (para testing del script)
INSERT INTO reservations (passenger_id, ride_id, seats_requested, status, total_cost, created_at) VALUES
(5, 6, 1, 'pending', 1800.00, DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
(6, 9, 1, 'pending', 1500.00, DATE_SUB(NOW(), INTERVAL 60 MINUTE));

-- =========================================
-- RESUMEN DE DATOS DE PRUEBA
-- =========================================

/*
USUARIOS CREADOS:
-----------------
Admin:
- Email: admin@carpooling.com | Password: admin123

Choferes:
- Email: juan.perez@email.com | Password: password123
- Email: maria.garcia@email.com | Password: password123
- Email: carlos.rodriguez@email.com | Password: password123

Pasajeros:
- Email: ana.martinez@email.com | Password: password123
- Email: pedro.lopez@email.com | Password: password123
- Email: lucia.fernandez@email.com | Password: password123

Usuarios especiales:
- Email: pendiente@email.com | Password: password123 | Status: pending
- Email: inactivo@email.com | Password: password123 | Status: inactive

VEHÍCULOS: 6 vehículos registrados
RIDES: 12 rides (9 futuros, 2 pasados, 1 lleno)
RESERVAS: 11 reservas (6 activas, 3 pasadas, 2 antiguas pendientes)
*/
