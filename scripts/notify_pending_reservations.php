#!/usr/bin/env php
<?php
// Script: notify_pending_reservations.php
// Uso: php notify_pending_reservations.php
// Busca reservas pendientes (sin filtrar por antigüedad) y notifica por email a los choferes que tengan reservas pendientes

require_once __DIR__ . '/../config/constants.php';
// Asegurarse de cargar la configuración de email (SMTP) antes de instanciar Email
// Este archivo normalmente está fuera de git y contiene las constantes SMTP_*
require_once __DIR__ . '/../config/email.php';
// Cargar configuración de base de datos (constantes DB_*)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Email.php';

// Opcionales: modelos para info adicional
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/User.php';


echo "[notify] Buscar reservas pendientes (sin filtrar por antigüedad)...\n";

$db = Database::getInstance();

$sql = "SELECT r.driver_id, d.first_name AS driver_first_name, d.last_name AS driver_last_name, d.email AS driver_email,
               vrc.id as reservation_id, vrc.passenger_first_name, vrc.passenger_last_name, vrc.ride_id, vrc.ride_name,
               vrc.ride_date, vrc.ride_time, vrc.seats_requested, vrc.total_cost, vrc.created_at
        FROM v_reservations_complete vrc
        INNER JOIN rides r ON vrc.ride_id = r.id
        INNER JOIN users d ON r.driver_id = d.id
        WHERE vrc.status = 'pending'
        ORDER BY d.id, vrc.created_at ASC";

try {
    $stmt = $db->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "[error] Consulta fallida: " . $e->getMessage() . "\n";
    exit(1);
}

if (empty($rows)) {
    echo "[notify] No se encontraron reservas pendientes para notificar.\n";
    exit(0);
}

// Agrupar por driver_id
$byDriver = [];
foreach ($rows as $r) {
    $did = $r['driver_id'];
    if (!isset($byDriver[$did])) {
        $byDriver[$did] = [
            'driver' => [
                'id' => $did,
                'first_name' => $r['driver_first_name'] ?? '',
                'last_name' => $r['driver_last_name'] ?? '',
                'email' => $r['driver_email'] ?? ''
            ],
            'reservations' => []
        ];
    }
    $byDriver[$did]['reservations'][] = $r;
}

$emailer = new Email();

// Cargar template (simple replace)
$tplPath = __DIR__ . '/../templates/emails/driver_pending.html';
$tpl = file_exists($tplPath) ? file_get_contents($tplPath) : null;

$sentCount = 0;
foreach ($byDriver as $driverId => $block) {
    $driver = $block['driver'];
    $reservations = $block['reservations'];

    $driverName = trim(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')) ?: 'Chofer';
    $to = $driver['email'] ?? null;
    if (empty($to)) {
        echo "[warn] Chofer {$driverId} sin email, saltando...\n";
        continue;
    }

    $lines = [];
    foreach ($reservations as $res) {
        $pname = trim(($res['passenger_first_name'] ?? '') . ' ' . ($res['passenger_last_name'] ?? ''));
        $ride = $res['ride_name'] ?? ('Viaje #' . $res['ride_id']);
        $when = ($res['ride_date'] ?? '') . ' ' . ($res['ride_time'] ?? '');
        $created = $res['created_at'] ?? '';
        $seats = $res['seats_requested'] ?? 1;
        $cost = $res['total_cost'] ?? 0;

        $lines[] = sprintf("- Reserva #%s: %s solicitó %s asiento(s) para '%s' (%s). Creada: %s. Costo: ₡%s",
            $res['reservation_id'], $pname, $seats, $ride, $when, $created, number_format($cost));
    }

    $reservationsPlain = implode("\n", $lines);

    // Prepare HTML by substituting into template (escape values)
    if ($tpl) {
        $html = str_replace('{{DRIVER_NAME}}', htmlspecialchars($driverName), $tpl);
        $html = str_replace('{{RESERVATIONS_LIST}}', htmlspecialchars($reservationsPlain), $html);
        $html = str_replace('{{DASHBOARD_URL}}', htmlspecialchars(BASE_URL . '/driver/dashboard'), $html);
    } else {
        // Fallback simple HTML
        $html = '<p>Hola ' . htmlspecialchars($driverName) . ',</p>';
        $html .= '<p>Tienes solicitudes de reserva pendientes:</p>';
        $html .= '<pre>' . htmlspecialchars($reservationsPlain) . '</pre>';
        $html .= '<p><a href="' . htmlspecialchars(BASE_URL . '/driver/dashboard') . '">Ir al panel</a></p>';
    }

    $text = "Hola {$driverName}\n\nTienes las siguientes reservas pendientes:\n" . $reservationsPlain . "\n\nAccede al panel: " . BASE_URL . '/driver/dashboard';

    echo "[notify] Enviando email a {$to} ({$driverName})... ";
    $result = $emailer->sendEmail($to, 'Reservas pendientes - Carpooling UTN', $html, $text);
    if (!empty($result['success'])) {
        echo "OK\n";
        $sentCount++;
    } else {
        echo "FAIL: " . ($result['error'] ?? ($result['message'] ?? 'Unknown')) . "\n";
    }
}

echo "[notify] Emails enviados: {$sentCount}\n";

exit(0);
