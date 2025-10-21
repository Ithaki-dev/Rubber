<?php
/**
 * Clase Database - Conexión a base de datos usando PDO (Singleton)
 */

class Database {
    private static $instance = null;
    private $connection;

    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET . ";port=" . DB_PORT;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            } else {
                die("Error de conexión a la base de datos. Por favor contacte al administrador.");
            }
        }
    }

    /**
     * Obtener instancia única de la clase
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener la conexión PDO
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Ejecutar una query preparada
     * 
     * @param string $sql SQL query
     * @param array $params Parámetros para la query
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Error en la query: " . $e->getMessage() . "<br>SQL: " . $sql);
            } else {
                error_log("Database error: " . $e->getMessage() . " | SQL: " . $sql);
                die("Error en la base de datos. Por favor contacte al administrador.");
            }
        }
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback() {
        return $this->connection->rollback();
    }

    /**
     * Obtener el último ID insertado
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Prevenir clonación
     */
    private function __clone() {}

    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
