# 🚗 Carpooling System - Rubber

Sistema de gestión de viajes compartidos (Carpooling) desarrollado en PHP puro sin frameworks MVC.

## 📋 Descripción

Proyecto universitario para el curso ISW-613 - Programación en Ambiente Web I de la Universidad Técnica Nacional.

Sistema web que permite a choferes ofrecer viajes y a pasajeros reservar espacios en dichos viajes, facilitando el transporte compartido.

## ✨ Características

- ✅ Sistema de autenticación con 3 roles (Admin, Chofer, Pasajero)
- ✅ Registro de usuarios con activación por email
- ✅ Gestión de vehículos para choferes
- ✅ Creación y gestión de viajes (rides)
- ✅ Búsqueda pública de viajes con filtros
- ✅ Sistema de reservas con estados
- ✅ Panel administrativo
- ✅ Script de consola para notificaciones
- ✅ Upload de imágenes (perfil y vehículos)
- ✅ Protección contra CSRF, XSS y SQL Injection

## 🛠️ Tecnologías

- **Backend**: PHP 8.0+
- **Base de Datos**: MySQL 8.0+ / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (vanilla)
- **Email**: PHPMailer
- **Control de Versiones**: Git

## 📦 Requisitos

- PHP >= 8.0
- MySQL >= 8.0 o MariaDB >= 10.5
- Composer
- Apache/Nginx o PHP Built-in Server
- Extensiones PHP requeridas:
  - pdo_mysql
  - mbstring
  - openssl
  - fileinfo

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/Ithaki-dev/Rubber.git
cd Rubber
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar base de datos

```bash
# Crear la base de datos
mysql -u root -p < sql/schema.sql

# (Opcional) Cargar datos de prueba
mysql -u root -p < sql/seed.sql
```

### 4. Configurar archivos de configuración

```bash
# Copiar archivos de ejemplo
cp config/database.example.php config/database.php
cp config/email.example.php config/email.php

# Editar config/database.php con tus credenciales
# Editar config/email.php con tu configuración SMTP
```

**config/database.php:**
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'carpooling_db');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_password');
```

**config/email.php:**
```php
// Para desarrollo, usa Mailtrap.io
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USER', 'tu_usuario_mailtrap');
define('SMTP_PASS', 'tu_password_mailtrap');
```

### 5. Configurar permisos

```bash
chmod -R 777 public/uploads/
chmod -R 777 logs/
```

### 6. Iniciar servidor

**Opción 1: PHP Built-in Server**
```bash
cd public
php -S localhost:8000
```

**Opción 2: Apache/Nginx**
Configurar virtual host apuntando a `/ruta/a/Rubber/public`

### 7. Acceder a la aplicación

Abrir navegador en: `http://localhost:8000`

## 👥 Usuarios de Prueba

Si cargaste los datos de prueba (`seed.sql`), puedes usar:

### Administrador
- **Email**: admin@carpooling.com
- **Password**: admin123

### Choferes
- **Email**: juan.perez@email.com | **Password**: password123
- **Email**: maria.garcia@email.com | **Password**: password123
- **Email**: carlos.rodriguez@email.com | **Password**: password123

### Pasajeros
- **Email**: ana.martinez@email.com | **Password**: password123
- **Email**: pedro.lopez@email.com | **Password**: password123
- **Email**: lucia.fernandez@email.com | **Password**: password123

## 📁 Estructura del Proyecto

```
Rubber/
├── config/           # Archivos de configuración
├── core/             # Clases core (Database, Session, Validator, Email, Helpers)
├── models/           # Modelos de datos
├── controllers/      # Controladores
├── views/            # Vistas (HTML)
│   ├── layouts/      # Plantillas base
│   ├── auth/         # Login, registro
│   ├── admin/        # Panel admin
│   ├── driver/       # Panel chofer
│   ├── passenger/    # Panel pasajero
│   └── public/       # Páginas públicas
├── public/           # Archivos públicos
│   ├── css/          # Estilos
│   ├── js/           # Scripts
│   ├── uploads/      # Imágenes subidas
│   └── index.php     # Punto de entrada
├── scripts/          # Scripts de consola
├── sql/              # Archivos SQL
└── logs/             # Logs del sistema
```

## 🔧 Configuración del Script de Consola

El script revisa reservas pendientes y envía notificaciones a choferes.

### Ejecutar manualmente

```bash
php scripts/check_pending_reservations.php
```

### Configurar Cron Job (Linux)

```bash
# Editar crontab
crontab -e

# Ejecutar cada 30 minutos
*/30 * * * * /usr/bin/php /ruta/completa/Rubber/scripts/check_pending_reservations.php >> /ruta/completa/Rubber/logs/cron.log 2>&1
```

### Task Scheduler (Windows)

1. Abrir "Programador de tareas"
2. Crear tarea básica
3. Configurar trigger (cada 30 minutos)
4. Acción: `php.exe` con argumento: `C:\ruta\Rubber\scripts\check_pending_reservations.php`

## 🔒 Seguridad

- Todas las queries usan PDO prepared statements
- Protección CSRF en formularios
- Escapado de output (XSS prevention)
- Passwords hasheados con `password_hash()`
- Validación de archivos subidos
- Control de acceso por roles
- Sesiones regeneradas después del login

## 🧪 Testing

Para probar todas las funcionalidades, revisar la checklist en `GUIA_PROYECTO.md` sección 8.1

## 📚 Documentación Adicional

- **GUIA_PROYECTO.md**: Guía detallada de desarrollo paso a paso
- **sql/schema.sql**: Estructura completa de la base de datos
- **sql/seed.sql**: Datos de prueba

## 🤝 Contribución

Este es un proyecto académico. No se aceptan contribuciones externas.

## 📝 Licencia

Este proyecto es para fines educativos únicamente.

## 👨‍💻 Autor

Proyecto desarrollado como parte del curso ISW-613 - Universidad Técnica Nacional

---

## 📞 Soporte

Para dudas sobre el proyecto, consultar la documentación en `GUIA_PROYECTO.md` o contactar al facilitador del curso.

## 🎯 Roadmap

- [x] Estructura base del proyecto
- [x] Sistema de autenticación
- [x] Gestión de usuarios
- [ ] Gestión de vehículos
- [ ] Gestión de rides
- [ ] Sistema de reservas
- [ ] Búsqueda pública
- [ ] Panel administrativo
- [ ] Script de notificaciones
- [ ] UI/UX final
- [ ] Testing completo

---

**Última actualización**: Octubre 2025
I Project
