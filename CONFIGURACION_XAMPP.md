# 🚀 Configuración con XAMPP - Guía Paso a Paso

## 📋 Requisitos Previos

- ✅ XAMPP instalado (incluye Apache, MySQL/MariaDB, PHP)
- ✅ Composer instalado
- ✅ Git configurado

---

## 📍 Paso 1: Ubicar el Proyecto en XAMPP

### Opción A: Mover el Proyecto a htdocs (Recomendado para XAMPP)

```bash
# Copiar el proyecto a la carpeta htdocs de XAMPP
# En Linux:
sudo cp -r /home/bob/Github/Rubber /opt/lampp/htdocs/

# O crear un enlace simbólico (mejor opción):
sudo ln -s /home/bob/Github/Rubber /opt/lampp/htdocs/rubber

# En Windows:
# Copiar la carpeta Rubber a C:\xampp\htdocs\
```

### Opción B: Configurar Virtual Host (Recomendado para desarrollo profesional)

**En Linux (/opt/lampp/etc/extra/httpd-vhosts.conf):**
```apache
<VirtualHost *:80>
    ServerName carpooling.local
    DocumentRoot "/home/bob/Github/Rubber/public"
    
    <Directory "/home/bob/Github/Rubber/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "/home/bob/Github/Rubber/logs/error.log"
    CustomLog "/home/bob/Github/Rubber/logs/access.log" common
</VirtualHost>
```

**Agregar al archivo /etc/hosts:**
```
127.0.0.1   carpooling.local
```

**En Windows (C:\xampp\apache\conf\extra\httpd-vhosts.conf):**
```apache
<VirtualHost *:80>
    ServerName carpooling.local
    DocumentRoot "C:/xampp/htdocs/Rubber/public"
    
    <Directory "C:/xampp/htdocs/Rubber/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Agregar al archivo C:\Windows\System32\drivers\etc\hosts:**
```
127.0.0.1   carpooling.local
```

---

## 🗄️ Paso 2: Configurar Base de Datos con phpMyAdmin

### 1. Iniciar XAMPP

**En Linux:**
```bash
sudo /opt/lampp/lampp start
# o
sudo /opt/lampp/manager-linux-x64.run
```

**En Windows:**
- Abrir XAMPP Control Panel
- Iniciar Apache
- Iniciar MySQL

### 2. Acceder a phpMyAdmin

Abrir navegador en: **http://localhost/phpmyadmin**

### 3. Crear Base de Datos

#### Opción A: Desde phpMyAdmin (Interfaz Gráfica)

1. Click en "Nueva" en el panel izquierdo
2. Nombre: `carpooling_db`
3. Cotejamiento: `utf8mb4_unicode_ci`
4. Click en "Crear"

#### Opción B: Desde SQL en phpMyAdmin

1. Click en la pestaña "SQL"
2. Pegar y ejecutar:
```sql
CREATE DATABASE IF NOT EXISTS carpooling_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### 4. Importar Schema

1. Seleccionar la base de datos `carpooling_db`
2. Click en "Importar"
3. Click en "Seleccionar archivo"
4. Navegar a `/home/bob/Github/Rubber/sql/schema.sql`
5. Click en "Continuar"
6. ✅ Esperar confirmación de éxito

### 5. Importar Datos de Prueba (Opcional)

1. Con `carpooling_db` seleccionada
2. Click en "Importar"
3. Seleccionar `/home/bob/Github/Rubber/sql/seed.sql`
4. Click en "Continuar"
5. ✅ Confirmar que se insertaron los datos

---

## ⚙️ Paso 3: Configurar Archivos de Conexión

### Actualizar config/database.php

```php
<?php
/**
 * Configuración de base de datos - XAMPP
 */

define('DB_HOST', 'localhost');  // o '127.0.0.1'
define('DB_NAME', 'carpooling_db');
define('DB_USER', 'root');       // Usuario por defecto de XAMPP
define('DB_PASS', '');           // Sin password por defecto en XAMPP
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);         // Puerto por defecto de MySQL
```

### Actualizar config/constants.php

Si usas Virtual Host:
```php
define('BASE_URL', 'http://carpooling.local');
```

Si usas htdocs directamente:
```php
define('BASE_URL', 'http://localhost/rubber');
```

---

## 📧 Paso 4: Configurar Email (Desarrollo)

### Opción 1: Usar Mailtrap.io (Recomendado)

1. Registrarse en https://mailtrap.io (gratis)
2. Crear un inbox
3. Copiar credenciales SMTP

**Actualizar config/email.php:**
```php
<?php
// Configuración Mailtrap para desarrollo
define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USER', 'tu_usuario_mailtrap');
define('SMTP_PASS', 'tu_password_mailtrap');
define('SMTP_SECURE', 'tls');

define('SMTP_FROM_EMAIL', 'noreply@carpooling.com');
define('SMTP_FROM_NAME', 'Carpooling System');
```

### Opción 2: Usar Gmail (Para pruebas reales)

**IMPORTANTE:** Necesitas crear una "Contraseña de Aplicación" en tu cuenta Google

**Actualizar config/email.php:**
```php
<?php
// Configuración Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'tu_email@gmail.com');
define('SMTP_PASS', 'tu_password_de_aplicacion');
define('SMTP_SECURE', 'tls');

define('SMTP_FROM_EMAIL', 'tu_email@gmail.com');
define('SMTP_FROM_NAME', 'Carpooling System');
```

### Opción 3: Desactivar Email (Temporal)

Para desarrollo rápido sin configurar email, puedes comentar el envío:

En `core/Email.php`, modificar temporalmente los métodos para que retornen `true`:
```php
public function sendActivationEmail($to, $name, $token) {
    // Temporalmente desactivado para desarrollo
    error_log("Email a $to con token: $token");
    return true;
}
```

---

## 📦 Paso 5: Instalar Dependencias

```bash
cd /home/bob/Github/Rubber
composer install
```

Si no tienes Composer instalado:
```bash
# Linux
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Windows
# Descargar desde: https://getcomposer.org/download/
```

---

## 🔐 Paso 6: Configurar Permisos (Linux)

```bash
cd /home/bob/Github/Rubber

# Permisos para uploads
chmod -R 777 public/uploads/

# Permisos para logs
chmod -R 777 logs/

# Opcional: Cambiar propietario a usuario de Apache
# sudo chown -R www-data:www-data public/uploads/
# sudo chown -R www-data:www-data logs/
```

**En Windows:** No es necesario, XAMPP ya tiene los permisos correctos.

---

## 🚀 Paso 7: Iniciar la Aplicación

### Si usas htdocs:
1. Asegúrate que Apache esté corriendo en XAMPP
2. Abrir navegador en: **http://localhost/rubber**

### Si usas Virtual Host:
1. Reiniciar Apache en XAMPP
2. Abrir navegador en: **http://carpooling.local**

---

## ✅ Paso 8: Verificar Instalación

### Checklist de Verificación

- [ ] Apache está corriendo en XAMPP
- [ ] MySQL está corriendo en XAMPP
- [ ] Base de datos `carpooling_db` existe en phpMyAdmin
- [ ] Tablas creadas (users, vehicles, rides, reservations)
- [ ] Datos de prueba cargados (opcional)
- [ ] Página de inicio carga correctamente
- [ ] No hay errores en el navegador

### Probar Conexión a Base de Datos

Crear archivo temporal `test-db.php` en la carpeta `public/`:

```php
<?php
require_once '../config/database.php';
require_once '../core/Database.php';

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Conexión exitosa! Usuarios en BD: " . $result['count'];
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
```

Acceder: `http://localhost/rubber/test-db.php`

Si ves "✅ Conexión exitosa!", todo está bien. **Eliminar el archivo después.**

---

## 🔧 Troubleshooting

### Problema 1: "Access Denied" al conectar a MySQL

**Solución:**
```sql
-- En phpMyAdmin, ejecutar:
CREATE USER 'root'@'localhost' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON carpooling_db.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### Problema 2: Apache no inicia - Puerto 80 ocupado

**Solución Linux:**
```bash
# Ver qué usa el puerto 80
sudo netstat -tulpn | grep :80

# Detener Apache del sistema (si existe)
sudo systemctl stop apache2
```

**Solución Windows:**
1. Cambiar puerto de Apache en XAMPP
2. Editar `C:\xampp\apache\conf\httpd.conf`
3. Cambiar `Listen 80` por `Listen 8080`
4. Acceder con: `http://localhost:8080/rubber`

### Problema 3: Error "Call to undefined function mysqli_connect()"

**Solución:**
1. Abrir XAMPP Control Panel
2. Click en "Config" de Apache
3. Seleccionar `php.ini`
4. Buscar `;extension=mysqli`
5. Quitar el `;` para descomentar: `extension=mysqli`
6. Reiniciar Apache

### Problema 4: PHPMailer no funciona

**Solución:**
1. Verificar que Composer instaló PHPMailer: `ls vendor/phpmailer/`
2. Si no existe: `composer require phpmailer/phpmailer`
3. Verificar credenciales SMTP en `config/email.php`
4. Para desarrollo, usar Mailtrap.io

### Problema 5: .htaccess no funciona

**Solución:**
1. Verificar que `mod_rewrite` está activo
2. En Linux: editar `/opt/lampp/etc/httpd.conf`
3. Buscar `LoadModule rewrite_module` y asegurar que NO esté comentado
4. Buscar `AllowOverride None` y cambiar a `AllowOverride All`
5. Reiniciar Apache

### Problema 6: "File not found" en uploads

**Solución Linux:**
```bash
cd /home/bob/Github/Rubber
mkdir -p public/uploads/profiles
mkdir -p public/uploads/vehicles
chmod -R 777 public/uploads/
```

---

## 📊 Verificar Estado de Servicios XAMPP

### Linux:
```bash
# Ver estado
sudo /opt/lampp/lampp status

# Iniciar
sudo /opt/lampp/lampp start

# Detener
sudo /opt/lampp/lampp stop

# Reiniciar
sudo /opt/lampp/lampp restart
```

### Windows:
- Abrir XAMPP Control Panel
- Botones Start/Stop para cada servicio

---

## 🎯 Accesos Rápidos

### URLs Importantes

| Servicio | URL |
|----------|-----|
| Aplicación (htdocs) | http://localhost/rubber |
| Aplicación (vhost) | http://carpooling.local |
| phpMyAdmin | http://localhost/phpmyadmin |
| XAMPP Dashboard | http://localhost/dashboard |

### Credenciales por Defecto

| Servicio | Usuario | Password |
|----------|---------|----------|
| MySQL (XAMPP) | root | (vacío) |
| phpMyAdmin | root | (vacío) |
| App Admin | admin@carpooling.com | admin123 |

---

## 📱 Probar la Aplicación

### 1. Acceder a la Página de Inicio
```
http://localhost/rubber
o
http://carpooling.local
```

### 2. Registrar un Usuario
```
http://localhost/rubber/register
```

### 3. Iniciar Sesión (con datos de prueba)
```
Email: admin@carpooling.com
Password: admin123
```

### 4. Explorar phpMyAdmin
```
http://localhost/phpmyadmin
```
- Ver estructura de tablas
- Consultar datos
- Ejecutar queries

---

## 🎓 Siguiente Paso: Desarrollo

Ahora que XAMPP está configurado, puedes continuar con:

1. **Crear modelos** - Ver `GUIA_PROYECTO.md` sección 5.6
2. **Implementar autenticación** - Ver `GUIA_PROYECTO.md` sección 5.5
3. **Crear vistas** - Ver `GUIA_PROYECTO.md` sección 5

---

## 💡 Tips para Desarrollo con XAMPP

### 1. Ver Logs de Error
- **Linux:** `/opt/lampp/logs/error_log`
- **Windows:** `C:\xampp\apache\logs\error.log`

### 2. Habilitar Display Errors
En `php.ini` (Config -> PHP en XAMPP Control Panel):
```ini
display_errors = On
error_reporting = E_ALL
```

### 3. Backup de Base de Datos
En phpMyAdmin:
1. Seleccionar `carpooling_db`
2. Click en "Exportar"
3. Formato: SQL
4. Click en "Continuar"

### 4. Acceso Rápido a Archivos
Crear alias en Linux:
```bash
echo "alias rubber='cd /home/bob/Github/Rubber'" >> ~/.bashrc
source ~/.bashrc
rubber  # ahora te lleva directo al proyecto
```

### 5. Auto-reload en Desarrollo
Usar extensión del navegador "Live Server" o similar para auto-reload.

---

## ✅ Checklist Final

Antes de comenzar a desarrollar, verifica:

- [ ] ✅ XAMPP instalado y funcionando
- [ ] ✅ Apache iniciado
- [ ] ✅ MySQL iniciado
- [ ] ✅ Base de datos `carpooling_db` creada
- [ ] ✅ Tablas importadas desde `schema.sql`
- [ ] ✅ Datos de prueba cargados (opcional)
- [ ] ✅ Composer dependencies instaladas
- [ ] ✅ Archivos de configuración actualizados
- [ ] ✅ Permisos configurados (Linux)
- [ ] ✅ Página de inicio carga sin errores
- [ ] ✅ phpMyAdmin accesible
- [ ] ✅ Conexión a BD funcionando

---

## 🎉 ¡Listo para Desarrollar!

Tu entorno XAMPP está completamente configurado. Puedes comenzar a desarrollar las funcionalidades del sistema.

**Siguiente:** Implementar sistema de autenticación (login/registro)

---

**Última actualización:** Octubre 15, 2025
