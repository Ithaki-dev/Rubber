<?php

/**
 * Funciones de Estadísticas y Utilidades
 * Equivalentes a los procedimientos almacenados que no se pudieron crear
 * debido al bug de mysql.proc en XAMPP
 */

require_once __DIR__ . '/Database.php';

/**
 * Obtener estadísticas completas de un chofer
 * Equivalente a: CALL sp_driver_statistics($driver_id)
 * 
 * @param int $driver_id ID del chofer
 * @return array Estadísticas del chofer
 */
function getDriverStatistics($driver_id) {
    $db = Database::getInstance();
    
    $query = "
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
        WHERE u.id = ?
        GROUP BY u.id
    ";
    
    $result = $db->query($query, [$driver_id]);
    $stats = $result->fetch(PDO::FETCH_ASSOC);
    
    // Si no hay datos, retornar valores por defecto
    if (!$stats) {
        return [
            'total_vehicles' => 0,
            'total_rides' => 0,
            'total_reservations' => 0,
            'pending_reservations' => 0,
            'accepted_reservations' => 0,
            'total_earnings' => 0
        ];
    }
    
    return $stats;
}

/**
 * Obtener estadísticas completas de un pasajero
 * Equivalente a: CALL sp_passenger_statistics($passenger_id)
 * 
 * @param int $passenger_id ID del pasajero
 * @return array Estadísticas del pasajero
 */
function getPassengerStatistics($passenger_id) {
    $db = Database::getInstance();
    
    $query = "
        SELECT 
            COUNT(*) AS total_reservations,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_reservations,
            SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS accepted_reservations,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_reservations,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_reservations,
            SUM(total_cost) AS total_spent
        FROM reservations
        WHERE passenger_id = ?
    ";
    
    $result = $db->query($query, [$passenger_id]);
    $stats = $result->fetch(PDO::FETCH_ASSOC);
    
    // Si no hay datos, retornar valores por defecto
    if (!$stats || $stats['total_reservations'] == 0) {
        return [
            'total_reservations' => 0,
            'pending_reservations' => 0,
            'accepted_reservations' => 0,
            'rejected_reservations' => 0,
            'cancelled_reservations' => 0,
            'total_spent' => 0
        ];
    }
    
    return $stats;
}

/**
 * Obtener reservas pendientes antiguas para notificaciones
 * Equivalente a: CALL sp_get_old_pending_reservations($minutes)
 * 
 * @param int $minutes Minutos desde la creación de la reserva
 * @return array Lista de choferes con reservas pendientes antiguas
 */
function getOldPendingReservations($minutes = 30) {
    $db = Database::getInstance();
    
    $query = "
        SELECT 
            r.driver_id,
            u.email AS driver_email,
            u.first_name AS driver_first_name,
            COUNT(*) AS pending_count
        FROM reservations res
        INNER JOIN rides r ON res.ride_id = r.id
        INNER JOIN users u ON r.driver_id = u.id
        WHERE res.status = 'pending'
        AND TIMESTAMPDIFF(MINUTE, res.created_at, NOW()) >= ?
        GROUP BY r.driver_id, u.email, u.first_name
    ";
    
    $result = $db->query($query, [$minutes]);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Calcular edad basada en fecha de nacimiento
 * Equivalente a: SELECT fn_calculate_age($birth_date)
 * 
 * @param string $birth_date Fecha de nacimiento (formato: Y-m-d)
 * @return int Edad en años
 */
function calculateAge($birth_date) {
    if (empty($birth_date)) {
        return 0;
    }
    
    $birth = new DateTime($birth_date);
    $today = new DateTime('today');
    $age = $birth->diff($today)->y;
    
    return $age;
}

/**
 * Verificar si un viaje está lleno
 * Equivalente a: SELECT fn_is_ride_full($ride_id)
 * 
 * @param int $ride_id ID del viaje
 * @return bool True si está lleno, False si tiene asientos disponibles
 */
function isRideFull($ride_id) {
    $db = Database::getInstance();
    
    $query = "SELECT available_seats FROM rides WHERE id = ?";
    $result = $db->query($query, [$ride_id]);
    $ride = $result->fetch(PDO::FETCH_ASSOC);
    
    if (!$ride) {
        return true; // Si no existe el viaje, considerarlo "lleno"
    }
    
    return $ride['available_seats'] <= 0;
}

/**
 * Obtener asientos disponibles de un viaje
 * 
 * @param int $ride_id ID del viaje
 * @return int Número de asientos disponibles
 */
function getAvailableSeats($ride_id) {
    $db = Database::getInstance();
    
    $query = "SELECT available_seats FROM rides WHERE id = ?";
    $result = $db->query($query, [$ride_id]);
    $ride = $result->fetch(PDO::FETCH_ASSOC);
    
    return $ride ? (int)$ride['available_seats'] : 0;
}

/**
 * Verificar si un usuario puede reservar cierta cantidad de asientos
 * 
 * @param int $ride_id ID del viaje
 * @param int $seats_requested Asientos solicitados
 * @return array ['can_reserve' => bool, 'available' => int, 'message' => string]
 */
function canReserveSeats($ride_id, $seats_requested) {
    $available = getAvailableSeats($ride_id);
    
    if ($seats_requested > $available) {
        return [
            'can_reserve' => false,
            'available' => $available,
            'message' => "Solo hay {$available} asiento(s) disponible(s)"
        ];
    }
    
    return [
        'can_reserve' => true,
        'available' => $available,
        'message' => 'Asientos disponibles'
    ];
}

/**
 * Obtener el total de ganancias de un chofer
 * 
 * @param int $driver_id ID del chofer
 * @return float Total de ganancias
 */
function getDriverEarnings($driver_id) {
    $db = Database::getInstance();
    
    $query = "
        SELECT COALESCE(SUM(res.total_cost), 0) AS total_earnings
        FROM rides r
        INNER JOIN reservations res ON r.id = res.ride_id
        WHERE r.driver_id = ?
        AND res.status = 'accepted'
    ";
    
    $result = $db->query($query, [$driver_id]);
    $data = $result->fetch(PDO::FETCH_ASSOC);
    
    return (float)$data['total_earnings'];
}

/**
 * Obtener el número de reservas pendientes de un chofer
 * 
 * @param int $driver_id ID del chofer
 * @return int Número de reservas pendientes
 */
function getDriverPendingReservationsCount($driver_id) {
    $db = Database::getInstance();
    
    $query = "
        SELECT COUNT(*) AS pending_count
        FROM rides r
        INNER JOIN reservations res ON r.id = res.ride_id
        WHERE r.driver_id = ?
        AND res.status = 'pending'
    ";
    
    $result = $db->query($query, [$driver_id]);
    $data = $result->fetch(PDO::FETCH_ASSOC);
    
    return (int)$data['pending_count'];
}

/**
 * Obtener viajes próximos de un chofer
 * 
 * @param int $driver_id ID del chofer
 * @param int $limit Límite de resultados
 * @return array Lista de viajes
 */
function getDriverUpcomingRides($driver_id, $limit = 5) {
    $db = Database::getInstance();
    
    $query = "
        SELECT * FROM v_rides_complete
        WHERE driver_id = ?
        AND ride_date >= CURDATE()
        AND is_active = 1
        ORDER BY ride_date ASC, ride_time ASC
        LIMIT ?
    ";
    
    $result = $db->query($query, [$driver_id, $limit]);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener reservas próximas de un pasajero
 * 
 * @param int $passenger_id ID del pasajero
 * @param int $limit Límite de resultados
 * @return array Lista de reservas
 */
function getPassengerUpcomingReservations($passenger_id, $limit = 5) {
    $db = Database::getInstance();
    
    $query = "
        SELECT * FROM v_reservations_complete
        WHERE passenger_id = ?
        AND ride_date >= CURDATE()
        AND status IN ('pending', 'accepted')
        ORDER BY ride_date ASC, ride_time ASC
        LIMIT ?
    ";
    
    $result = $db->query($query, [$passenger_id, $limit]);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}
