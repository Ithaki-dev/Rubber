<?php
/**
 * Clase Session - Manejo de sesiones
 */

class Session {
    
    /**
     * Iniciar sesión
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Establecer un valor en la sesión
     * 
     * @param string $key Clave
     * @param mixed $value Valor
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Obtener un valor de la sesión
     * 
     * @param string $key Clave
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verificar si existe una clave en la sesión
     * 
     * @param string $key Clave
     * @return bool
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Eliminar un valor de la sesión
     * 
     * @param string $key Clave
     */
    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destruir la sesión
     */
    public static function destroy() {
        self::start();
        session_unset();
        session_destroy();
    }

    /**
     * Verificar si el usuario está autenticado
     * 
     * @return bool
     */
    public static function isLoggedIn() {
        return self::has('user_id') && self::has('user_type');
    }

    /**
     * Obtener el ID del usuario actual
     * 
     * @return int|null
     */
    public static function getUserId() {
        return self::get('user_id');
    }

    /**
     * Obtener el tipo de usuario actual
     * 
     * @return string|null
     */
    public static function getUserType() {
        return self::get('user_type');
    }

    /**
     * Obtener datos del usuario actual
     * 
     * @return array
     */
    public static function getCurrentUser() {
        return [
            'id' => self::get('user_id'),
            'first_name' => self::get('first_name', 'Usuario'),
            'last_name' => self::get('last_name', ''),
            'email' => self::get('user_email', ''),
            'phone' => self::get('phone', ''),
            'student_id' => self::get('student_id', ''),
            // photo_path may be stored under different session keys depending on controller
            'photo_path' => self::get('user_photo', self::get('photo_path', '')),
            // legacy short key 'pr' kept for compatibility with older views
            'pr' => self::get('user_photo', self::get('photo_path', '')),
            'user_type' => self::get('user_type', 'guest')
        ];
    }

    /**
     * Verificar si el usuario es administrador
     * 
     * @return bool
     */
    public static function isAdmin() {
        return self::getUserType() === 'admin';
    }

    /**
     * Verificar si el usuario es chofer
     * 
     * @return bool
     */
    public static function isDriver() {
        return self::getUserType() === 'driver';
    }

    /**
     * Verificar si el usuario es pasajero
     * 
     * @return bool
     */
    public static function isPassenger() {
        return self::getUserType() === 'passenger';
    }

    /**
     * Establecer mensaje flash
     * 
     * @param string $type Tipo de mensaje (success, error, warning, info)
     * @param string $message Mensaje
     */
    public static function setFlashMessage($type, $message) {
        self::set('flash_message', ['type' => $type, 'message' => $message]);
    }

    /**
     * Alias para setFlashMessage() - Compatibilidad
     * 
     * @param string $type Tipo de mensaje (success, error, warning, info)
     * @param string $message Mensaje
     */
    public static function setFlash($type, $message) {
        self::setFlashMessage($type, $message);
    }

    /**
     * Obtener y eliminar mensaje flash
     * 
     * @return array|null
     */
    public static function getFlashMessage() {
        $message = self::get('flash_message');
        self::remove('flash_message');
        return $message;
    }

    /**
     * Regenerar ID de sesión (importante después del login)
     */
    public static function regenerate() {
        self::start();
        session_regenerate_id(true);
    }
}
