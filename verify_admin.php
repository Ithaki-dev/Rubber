<?php
/**
 * Script para verificar y crear usuario administrador
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

echo "=== Verificación de Usuario Administrador ===\n\n";

try {
    $db = Database::getInstance();
    
    // Verificar si el usuario admin existe
    $query = "SELECT * FROM users WHERE email = ? AND user_type = 'admin'";
    $result = $db->query($query, ['admin@carpooling.com']);
    $admin = $result->fetch();
    
    if ($admin) {
        echo "✅ Usuario administrador encontrado:\n";
        echo "  ID: " . $admin['id'] . "\n";
        echo "  Email: " . $admin['email'] . "\n";
        echo "  Nombre: " . $admin['first_name'] . " " . $admin['last_name'] . "\n";
        echo "  Tipo: " . $admin['user_type'] . "\n";
        echo "  Estado: " . $admin['status'] . "\n";
        echo "  Creado: " . $admin['created_at'] . "\n\n";
        
        // Verificar la contraseña
        echo "🔐 Verificando contraseña 'admin123'...\n";
        if (password_verify('admin123', $admin['password_hash'])) {
            echo "✅ Contraseña correcta\n\n";
        } else {
            echo "❌ Contraseña incorrecta\n";
            echo "🔧 Actualizando contraseña...\n";
            
            $newPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET password_hash = ? WHERE id = ?";
            $db->query($updateQuery, [$newPassword, $admin['id']]);
            
            echo "✅ Contraseña actualizada correctamente\n\n";
        }
        
    } else {
        echo "❌ Usuario administrador NO encontrado\n";
        echo "🔧 Creando usuario administrador...\n";
        
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $insertQuery = "
            INSERT INTO users (email, password_hash, first_name, last_name, user_type, status, cedula, birth_date, phone, created_at) 
            VALUES (?, ?, ?, ?, 'admin', 'active', '000000000', '1990-01-01', '0000-0000', NOW())
        ";
        
        $db->query($insertQuery, [
            'admin@carpooling.com',
            $password,
            'Administrador',
            'Sistema'
        ]);
        
        echo "✅ Usuario administrador creado correctamente\n\n";
    }
    
    // Verificar la tabla users
    echo "📊 Estadísticas de usuarios:\n";
    $statsQuery = "
        SELECT 
            user_type, 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as activos
        FROM users 
        GROUP BY user_type
    ";
    $stats = $db->query($statsQuery)->fetchAll();
    
    foreach ($stats as $stat) {
        echo "  {$stat['user_type']}: {$stat['total']} total ({$stat['activos']} activos)\n";
    }
    
    echo "\n=== Credenciales de administrador ===\n";
    echo "Email: admin@carpooling.com\n";
    echo "Password: admin123\n";
    echo "URL Login: http://localhost:8080/Rubber/public/auth/login\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}