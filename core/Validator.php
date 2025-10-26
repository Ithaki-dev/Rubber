<?php
/**
 * Clase Validator - Validación de datos
 */

class Validator {
    private $errors = [];
    private $data = [];

    /**
     * Validar datos según reglas
     * 
     * @param array $data Datos a validar
     * @param array $rules Reglas de validación
     * @return bool
     */
    public function validate($data, $rules) {
        $this->errors = [];
        $this->data = $data;

        foreach ($rules as $field => $ruleSet) {
            $ruleList = explode('|', $ruleSet);
            
            foreach ($ruleList as $rule) {
                $this->applyRule($field, $data[$field] ?? '', $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Aplicar una regla específica
     * 
     * @param string $field Campo
     * @param mixed $value Valor
     * @param string $rule Regla
     */
    private function applyRule($field, $value, $rule) {
        $parameter = null;
        
        // Separar regla y parámetro (ej: min:6)
        if (strpos($rule, ':') !== false) {
            list($rule, $parameter) = explode(':', $rule, 2);
        }

        $fieldLabel = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = "$fieldLabel es requerido";
                }
                break;
            
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "$fieldLabel no es un email válido";
                }
                break;
            
            case 'min':
                if (strlen($value) < $parameter) {
                    $this->errors[$field][] = "$fieldLabel debe tener al menos $parameter caracteres";
                }
                break;
            
            case 'max':
                if (strlen($value) > $parameter) {
                    $this->errors[$field][] = "$fieldLabel no puede tener más de $parameter caracteres";
                }
                break;
            
            case 'matches':
                if ($value !== ($this->data[$parameter] ?? '')) {
                    $this->errors[$field][] = "$fieldLabel no coincide con " . ucfirst(str_replace('_', ' ', $parameter));
                }
                break;
            
            case 'unique':
                list($table, $column) = explode(',', $parameter);
                if ($this->checkUnique($table, $column, $value)) {
                    $this->errors[$field][] = "$fieldLabel ya está registrado";
                }
                break;
            
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = "$fieldLabel debe ser numérico";
                }
                break;
            
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->errors[$field][] = "$fieldLabel debe ser un número entero";
                }
                break;
            
            case 'date':
                if (!empty($value) && !$this->validateDate($value)) {
                    $this->errors[$field][] = "$fieldLabel no es una fecha válida";
                }
                break;
            
            case 'future_date':
                if (!empty($value) && strtotime($value) < strtotime('today')) {
                    $this->errors[$field][] = "$fieldLabel debe ser una fecha futura";
                }
                break;
            
            case 'past_date':
                if (!empty($value) && strtotime($value) > strtotime('today')) {
                    $this->errors[$field][] = "$fieldLabel debe ser una fecha pasada";
                }
                break;
            
            case 'alpha':
                if (!empty($value) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $value)) {
                    $this->errors[$field][] = "$fieldLabel solo debe contener letras";
                }
                break;
            
            case 'alphanumeric':
                if (!empty($value) && !preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+$/', $value)) {
                    $this->errors[$field][] = "$fieldLabel solo debe contener letras y números";
                }
                break;
        }
    }

    /**
     * Verificar si un valor es único en la base de datos
     * 
     * @param string $table Tabla
     * @param string $column Columna
     * @param mixed $value Valor
     * @return bool
     */
    private function checkUnique($table, $column, $value) {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT COUNT(*) as count FROM $table WHERE $column = ?", [$value]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Validar formato de fecha
     *
     * @param string $date Fecha
     * @param string $format Formato
     * @return bool
     */
    public function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Validar formato de hora (HH:MM)
     *
     * @param string $time Hora
     * @param string $format Formato
     * @return bool
     */
    public function validateTime($time, $format = 'H:i') {
        $t = DateTime::createFromFormat($format, $time);
        return $t && $t->format($format) === $time;
    }

    /**
     * Validar precio (número >= 0)
     *
     * @param mixed $price
     * @return bool
     */
    public function validatePrice($price) {
        if ($price === '' || $price === null) return false;
        if (!is_numeric($price)) return false;
        return floatval($price) >= 0;
    }

    /**
     * Validar año razonable
     *
     * @param mixed $year
     * @return bool
     */
    public function validateYear($year) {
        if (!ctype_digit((string)$year)) return false;
        $y = (int)$year;
        $current = (int)date('Y');
        return $y >= 1900 && $y <= ($current + 1);
    }

    /**
     * Validar entero positivo
     *
     * @param mixed $v
     * @return bool
     */
    public function validatePositiveInt($v) {
        if ($v === '' || $v === null) return false;
        if (!is_numeric($v)) return false;
        return filter_var($v, FILTER_VALIDATE_INT) !== false && (int)$v >= 0;
    }

    /**
     * Obtener todos los errores
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Obtener errores de un campo específico
     * 
     * @param string $field Campo
     * @return array
     */
    public function getFieldErrors($field) {
        return $this->errors[$field] ?? [];
    }

    /**
     * Verificar si hay errores
     * 
     * @return bool
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
}
