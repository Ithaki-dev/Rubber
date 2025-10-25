<?php
/**
 * Helpers.php - Funciones auxiliares globales
 */

/**
 * Escapar output HTML para prevenir XSS
 * 
 * @param string $string Cadena a escapar
 * @return string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirigir a una URL
 * 
 * @param string $url URL de destino
 */
function redirect($url) {
    // Si la URL es relativa (empieza con /), agregar BASE_URL
    if (strpos($url, '/') === 0 && !preg_match('/^https?:\/\//', $url)) {
        $url = BASE_URL . $url;
    }
    header("Location: $url");
    exit();
}

/**
 * Redirigir atrás
 */
function redirectBack() {
    $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
    redirect($referer);
}

/**
 * Verificar si el usuario está autenticado, si no redirigir al login
 */
function requireAuth() {
    if (!Session::isLoggedIn()) {
        Session::setFlashMessage('error', 'Debes iniciar sesión para acceder a esta página');
        redirect(BASE_URL . '/login.php');
    }
}

/**
 * Verificar si el usuario es administrador
 */
function requireAdmin() {
    requireAuth();
    if (!Session::isAdmin()) {
        Session::setFlashMessage('error', 'No tienes permisos para acceder a esta página');
        redirect(BASE_URL . '/dashboard.php');
    }
}

/**
 * Verificar si el usuario es chofer
 */
function requireDriver() {
    requireAuth();
    if (!Session::isDriver()) {
        Session::setFlashMessage('error', 'Solo los choferes pueden acceder a esta página');
        redirect(BASE_URL . '/dashboard.php');
    }
}

/**
 * Verificar si el usuario es pasajero
 */
function requirePassenger() {
    requireAuth();
    if (!Session::isPassenger()) {
        Session::setFlashMessage('error', 'Solo los pasajeros pueden acceder a esta página');
        redirect(BASE_URL . '/dashboard.php');
    }
}

/**
 * Verificar si el usuario NO está autenticado (para páginas de login/registro)
 */
function requireGuest() {
    if (Session::isLoggedIn()) {
        redirect(BASE_URL . '/dashboard.php');
    }
}

/**
 * Formatear fecha
 * 
 * @param string $date Fecha
 * @param string $format Formato de salida
 * @return string
 */
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

/**
 * Formatear fecha y hora
 * 
 * @param string $datetime Fecha y hora
 * @param string $format Formato de salida
 * @return string
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    return date($format, strtotime($datetime));
}

/**
 * Formatear moneda
 * 
 * @param float $amount Cantidad
 * @param string $currency Moneda
 * @return string
 */
function formatCurrency($amount, $currency = '₡') {
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Subir archivo de imagen
 * 
 * @param array $file Archivo $_FILES
 * @param string $folder Carpeta de destino (profiles, vehicles)
 * @param int $maxSize Tamaño máximo en bytes
 * @return array ['success' => bool, 'path' => string, 'error' => string]
 */
function uploadImage($file, $folder, $maxSize = MAX_FILE_SIZE) {
    // Verificar si hay archivo
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'error' => 'No se seleccionó ningún archivo'];
    }

    // Verificar errores de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Error al subir el archivo'];
    }

    // Verificar tamaño
    if ($file['size'] > $maxSize) {
        $maxSizeMB = $maxSize / 1024 / 1024;
        return ['success' => false, 'error' => "El archivo no debe superar {$maxSizeMB}MB"];
    }

    // Verificar tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Solo se permiten imágenes JPG y PNG'];
    }

    // Crear carpeta si no existe
    $uploadDir = UPLOADS_PATH . '/' . $folder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $uploadDir . $filename;

    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'path' => '/uploads/' . $folder . '/' . $filename
        ];
    }

    return ['success' => false, 'error' => 'Error al guardar el archivo'];
}

/**
 * Eliminar archivo
 * 
 * @param string $path Ruta del archivo
 * @return bool
 */
function deleteFile($path) {
    if (empty($path)) {
        return false;
    }

    $fullPath = PUBLIC_PATH . $path;
    
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    
    return false;
}

/**
 * Obtener URL del avatar por defecto
 * 
 * @param string $name Nombre del usuario
 * @return string
 */
function getDefaultAvatar($name) {
    $initial = strtoupper(substr($name, 0, 1));
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=random&size=200";
}

/**
 * Generar token aleatorio
 * 
 * @param int $length Longitud
 * @return string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Sanitizar string
 * 
 * @param string $string Cadena
 * @return string
 */
function sanitize($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

/**
 * Verificar si es una petición POST
 * 
 * @return bool
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Verificar si es una petición GET
 * 
 * @return bool
 */
function isGet() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Obtener valor POST
 * 
 * @param string $key Clave
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function post($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * Obtener valor GET
 * 
 * @param string $key Clave
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function get($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Debug - dump de variables
 * 
 * @param mixed $var Variable a mostrar
 * @param bool $die Detener ejecución
 */
function dd($var, $die = true) {
    if (DEBUG_MODE) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        
        if ($die) {
            die();
        }
    }
}

/**
 * Obtener días de la semana en español
 * 
 * @return array
 */
function getDaysOfWeek() {
    return [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];
}

/**
 * Traducir día de la semana
 * 
 * @param string $day Día en inglés
 * @return string
 */
function translateDay($day) {
    $days = getDaysOfWeek();
    return $days[$day] ?? $day;
}

/**
 * Generar CSRF token
 * 
 * @return string
 */
function csrfToken() {
    if (!Session::has('csrf_token')) {
        Session::set('csrf_token', generateToken());
    }
    return Session::get('csrf_token');
}

/**
 * Verificar CSRF token
 * 
 * @param string $token Token a verificar
 * @return bool
 */
function verifyCsrfToken($token) {
    return hash_equals(csrfToken(), $token);
}

/**
 * Generar campo hidden de CSRF
 * 
 * @return string
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/**
 * Paginar resultados
 * 
 * @param int $total Total de items
 * @param int $perPage Items por página
 * @param int $currentPage Página actual
 * @return array
 */
function paginate($total, $perPage = ITEMS_PER_PAGE, $currentPage = 1) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Clase Helpers para métodos utilitarios estáticos
 */
class Helpers {
    
    /**
     * Generar token CSRF y campo oculto para formularios
     * 
     * @return string HTML con campo oculto del token CSRF
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
    }
    
    /**
     * Verificar token CSRF
     * 
     * @param string $token Token a verificar
     * @return bool True si es válido
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Escapar salida HTML
     * 
     * @param string $string Cadena a escapar
     * @return string Cadena escapada
     */
    public static function escape($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Formatear fecha para mostrar
     * 
     * @param string $date Fecha en formato Y-m-d H:i:s
     * @param string $format Formato de salida
     * @return string Fecha formateada
     */
    public static function formatDate($date, $format = 'd/m/Y H:i') {
        if (empty($date) || $date === '0000-00-00 00:00:00') {
            return '-';
        }
        
        try {
            $datetime = new DateTime($date);
            return $datetime->format($format);
        } catch (Exception $e) {
            return $date;
        }
    }
    
    /**
     * Formatear moneda costarricense
     * 
     * @param float $amount Cantidad
     * @return string Cantidad formateada
     */
    public static function formatMoney($amount) {
        return '₡' . number_format($amount, 0, ',', '.');
    }
    
    /**
     * Generar opciones para select
     * 
     * @param array $options Array asociativo con value => label
     * @param mixed $selected Valor seleccionado
     * @return string HTML con las opciones
     */
    public static function generateSelectOptions($options, $selected = null) {
        $html = '';
        foreach ($options as $value => $label) {
            $selectedAttr = ($value == $selected) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '"' . $selectedAttr . '>';
            $html .= htmlspecialchars($label) . '</option>';
        }
        return $html;
    }
    
    /**
     * Truncar texto
     * 
     * @param string $text Texto a truncar
     * @param int $length Longitud máxima
     * @param string $suffix Sufijo a agregar
     * @return string Texto truncado
     */
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Generar URL con parámetros
     * 
     * @param string $path Ruta base
     * @param array $params Parámetros GET
     * @return string URL completa
     */
    public static function url($path, $params = []) {
        $url = BASE_URL . '/' . ltrim($path, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
}
