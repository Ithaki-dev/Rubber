<?php
/**
 * Constantes globales de la aplicación
 */

// Configuración de entorno
define('ENVIRONMENT', 'development'); // cambiar a 'production' en producción
define('DEBUG_MODE', ENVIRONMENT === 'development');

// Configuración de errores
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// URLs base
define('BASE_URL', 'http://localhost:8080/Rubber/public');
define('ASSETS_URL', BASE_URL . '/assets');

// Email del administrador
define('ADMIN_EMAIL', 'ithakidev@gmail.com');
// Teléfono de soporte (editable desde el dashboard)
define('SUPPORT_PHONE', '6043-7458');
// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CORE_PATH', ROOT_PATH . '/core');
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Configuración de sesiones
define('SESSION_LIFETIME', 3600); // 1 hora en segundos

// Configuración de uploads
define('MAX_FILE_SIZE', 5242880); // 5MB en bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Configuración de paginación
define('ITEMS_PER_PAGE', 10);

// Configuración de reservas
define('RESERVATION_PENDING_MINUTES', 30); // Para el script de notificaciones

// Zona horaria
date_default_timezone_set('America/Costa_Rica');
