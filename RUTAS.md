# 🗺️ DOCUMENTACIÓN DE RUTAS

Sistema de enrutamiento basado en controladores para el proyecto Carpooling.

## 📋 Tabla de Contenidos

- [Estructura General](#estructura-general)
- [Rutas Públicas](#rutas-públicas)
- [Rutas de Autenticación](#rutas-de-autenticación)
- [Rutas del Pasajero](#rutas-del-pasajero)
- [Rutas del Chofer](#rutas-del-chofer)
- [Rutas del Admin](#rutas-del-admin)
- [Convenciones](#convenciones)
- [Ejemplos de Uso](#ejemplos-de-uso)

---

## 🏗️ Estructura General

### Patrón de URL
```
http://localhost/Rubber/public/{controller}/{method}/{param1}/{param2}
```

### Flujo de Petición
```
URL → .htaccess → index.php → Router → Controller → Method → View
```

---

## 🌐 Rutas Públicas

**Controller:** `HomeController`  
**Acceso:** Sin autenticación

| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/` | `HomeController::index()` | Página de inicio |
| GET | `/home/search` | `HomeController::searchPublic()` | Búsqueda pública de viajes |
| GET | `/home/ride/{id}` | `HomeController::showRide(id)` | Ver detalles de viaje |
| GET | `/home/how-it-works` | `HomeController::howItWorks()` | Página "Cómo funciona" |
| GET | `/home/about` | `HomeController::about()` | Página "Acerca de" |
| GET | `/home/contact` | `HomeController::contact()` | Formulario de contacto |
| POST | `/home/contact` | `HomeController::sendContact()` | Enviar mensaje de contacto |

**Ejemplo:**
```
http://localhost/Rubber/public/
http://localhost/Rubber/public/home/search?departure=San+José&arrival=Cartago
http://localhost/Rubber/public/home/ride/5
```

---

## 🔐 Rutas de Autenticación

**Controller:** `AuthController`  
**Acceso:** Público (registro/login) o Autenticado (perfil)

| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/auth/register` | `AuthController::showRegister()` | Formulario de registro |
| POST | `/auth/register` | `AuthController::register()` | Procesar registro |
| GET | `/auth/login` | `AuthController::showLogin()` | Formulario de login |
| POST | `/auth/login` | `AuthController::login()` | Procesar login |
| GET | `/auth/logout` | `AuthController::logout()` | Cerrar sesión |
| GET | `/auth/activate?token=xxx` | `AuthController::activate()` | Activar cuenta |
| GET | `/auth/forgot-password` | `AuthController::showForgotPassword()` | Formulario recuperar contraseña |
| POST | `/auth/forgot-password` | `AuthController::forgotPassword()` | Procesar recuperación |
| GET | `/auth/profile` | `AuthController::showProfile()` | Ver perfil de usuario |
| POST | `/auth/profile` | `AuthController::updateProfile()` | Actualizar perfil |

**Ejemplo:**
```
http://localhost/Rubber/public/auth/register
http://localhost/Rubber/public/auth/login
http://localhost/Rubber/public/auth/profile
```

---

## 🚶 Rutas del Pasajero

**Controller:** `PassengerController`  
**Acceso:** Requiere autenticación + rol `passenger`

### Dashboard
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/passenger/dashboard` | `PassengerController::dashboard()` | Dashboard del pasajero |

### Búsqueda de Viajes
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/passenger/search` | `PassengerController::searchRides()` | Buscar viajes disponibles |
| GET | `/passenger/rides/{id}` | `PassengerController::showRide(id)` | Ver detalles de viaje |

### Reservas
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/passenger/reservations` | `PassengerController::reservations()` | Listar mis reservas |
| POST | `/passenger/reservations` | `PassengerController::makeReservation()` | Crear nueva reserva |
| GET | `/passenger/reservations/{id}` | `PassengerController::showReservation(id)` | Ver detalles de reserva |
| POST | `/passenger/reservations/{id}/cancel` | `PassengerController::cancelReservation(id)` | Cancelar reserva |
| GET | `/passenger/history` | `PassengerController::history()` | Historial de viajes |

**Ejemplo:**
```
http://localhost/Rubber/public/passenger/dashboard
http://localhost/Rubber/public/passenger/search?departure=San+José
http://localhost/Rubber/public/passenger/reservations
```

---

## 🚗 Rutas del Chofer

**Controller:** `DriverController`  
**Acceso:** Requiere autenticación + rol `driver`

### Dashboard
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/driver/dashboard` | `DriverController::dashboard()` | Dashboard del chofer |

### Vehículos (CRUD)
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/driver/vehicles` | `DriverController::vehicles()` | Listar vehículos |
| GET | `/driver/vehicles/create` | `DriverController::createVehicle()` | Formulario crear vehículo |
| POST | `/driver/vehicles` | `DriverController::storeVehicle()` | Guardar vehículo |
| GET | `/driver/vehicles/{id}/edit` | `DriverController::editVehicle(id)` | Formulario editar vehículo |
| POST | `/driver/vehicles/{id}` | `DriverController::updateVehicle(id)` | Actualizar vehículo |
| POST | `/driver/vehicles/{id}/delete` | `DriverController::deleteVehicle(id)` | Eliminar vehículo |
| POST | `/driver/vehicles/{id}/toggle` | `DriverController::toggleVehicle(id)` | Activar/desactivar vehículo |

### Viajes (CRUD)
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/driver/rides` | `DriverController::rides()` | Listar viajes |
| GET | `/driver/rides/create` | `DriverController::createRide()` | Formulario crear viaje |
| POST | `/driver/rides` | `DriverController::storeRide()` | Guardar viaje |
| GET | `/driver/rides/{id}` | `DriverController::showRide(id)` | Ver detalles con reservas |
| GET | `/driver/rides/{id}/edit` | `DriverController::editRide(id)` | Formulario editar viaje |
| POST | `/driver/rides/{id}` | `DriverController::updateRide(id)` | Actualizar viaje |
| POST | `/driver/rides/{id}/delete` | `DriverController::deleteRide(id)` | Eliminar viaje |
| POST | `/driver/rides/{id}/toggle` | `DriverController::toggleRide(id)` | Activar/desactivar viaje |

### Reservas
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/driver/reservations` | `DriverController::reservations()` | Listar reservas de mis viajes |
| POST | `/driver/reservations/{id}/accept` | `DriverController::acceptReservation(id)` | Aceptar reserva |
| POST | `/driver/reservations/{id}/reject` | `DriverController::rejectReservation(id)` | Rechazar reserva |

**Ejemplo:**
```
http://localhost/Rubber/public/driver/dashboard
http://localhost/Rubber/public/driver/vehicles
http://localhost/Rubber/public/driver/vehicles/create
http://localhost/Rubber/public/driver/rides
http://localhost/Rubber/public/driver/rides/5
```

---

## 👑 Rutas del Admin

**Controller:** `AdminController`  
**Acceso:** Requiere autenticación + rol `admin`

### Dashboard
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/admin/dashboard` | `AdminController::dashboard()` | Dashboard del administrador |
| GET | `/admin/reports` | `AdminController::reports()` | Reportes y estadísticas |

### Usuarios (CRUD)
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/admin/users` | `AdminController::users()` | Listar usuarios |
| GET | `/admin/users/create` | `AdminController::createUser()` | Formulario crear usuario |
| POST | `/admin/users` | `AdminController::storeUser()` | Guardar usuario |
| GET | `/admin/users/{id}` | `AdminController::showUser(id)` | Ver perfil completo |
| GET | `/admin/users/{id}/edit` | `AdminController::editUser(id)` | Formulario editar usuario |
| POST | `/admin/users/{id}` | `AdminController::updateUser(id)` | Actualizar usuario |
| POST | `/admin/users/{id}/delete` | `AdminController::deleteUser(id)` | Eliminar usuario |
| POST | `/admin/users/{id}/status` | `AdminController::changeUserStatus(id)` | Cambiar estado |

### Vehículos
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/admin/vehicles` | `AdminController::vehicles()` | Listar vehículos |
| GET | `/admin/vehicles/{id}` | `AdminController::showVehicle(id)` | Ver detalles |
| POST | `/admin/vehicles/{id}/delete` | `AdminController::deleteVehicle(id)` | Eliminar vehículo |

### Viajes
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/admin/rides` | `AdminController::rides()` | Listar viajes |
| GET | `/admin/rides/{id}` | `AdminController::showRide(id)` | Ver detalles |
| POST | `/admin/rides/{id}/delete` | `AdminController::deleteRide(id)` | Eliminar viaje |

### Reservas
| Método HTTP | Ruta | Controlador::Método | Descripción |
|------------|------|---------------------|-------------|
| GET | `/admin/reservations` | `AdminController::reservations()` | Listar reservas |
| GET | `/admin/reservations/{id}` | `AdminController::showReservation(id)` | Ver detalles |
| POST | `/admin/reservations/{id}/delete` | `AdminController::deleteReservation(id)` | Eliminar reserva |

**Ejemplo:**
```
http://localhost/Rubber/public/admin/dashboard
http://localhost/Rubber/public/admin/users
http://localhost/Rubber/public/admin/users/5
http://localhost/Rubber/public/admin/reports
```

---

## 📐 Convenciones

### Nomenclatura de Rutas

1. **Controlador en singular:** `/passenger`, `/driver`, `/admin`, `/auth`
2. **Recursos en plural:** `/vehicles`, `/rides`, `/reservations`, `/users`
3. **Acciones con verbos:** `/create`, `/edit`, `/delete`, `/toggle`, `/accept`, `/reject`
4. **IDs numéricos:** `/{id}` donde id es el número del registro

### Métodos HTTP

- **GET:** Obtener datos, mostrar formularios, listar recursos
- **POST:** Crear, actualizar, eliminar (por simplicidad, sin PUT/DELETE)

### Patrones de URL

```
# Listar
GET /{controller}/{resource}

# Crear (formulario)
GET /{controller}/{resource}/create

# Guardar (procesar formulario)
POST /{controller}/{resource}

# Ver detalles
GET /{controller}/{resource}/{id}

# Editar (formulario)
GET /{controller}/{resource}/{id}/edit

# Actualizar (procesar formulario)
POST /{controller}/{resource}/{id}

# Eliminar
POST /{controller}/{resource}/{id}/delete

# Acciones especiales
POST /{controller}/{resource}/{id}/{action}
```

---

## 💡 Ejemplos de Uso

### Flujo de Registro y Login

```
1. Usuario visita: /auth/register
2. Completa formulario y envía (POST)
3. Sistema crea usuario y envía email
4. Usuario accede a link de activación: /auth/activate?token=xxx
5. Usuario hace login: /auth/login (POST)
6. Sistema redirige según rol:
   - Admin → /admin/dashboard
   - Driver → /driver/dashboard
   - Passenger → /passenger/dashboard
```

### Flujo de Crear Viaje (Chofer)

```
1. Chofer en dashboard: /driver/dashboard
2. Clic en "Crear Viaje": /driver/rides/create (GET)
3. Completa formulario y envía (POST a /driver/rides)
4. Sistema crea viaje y redirige: /driver/rides
5. Chofer ve el viaje en la lista
```

### Flujo de Hacer Reserva (Pasajero)

```
1. Pasajero busca viajes: /passenger/search?departure=San+José
2. Ve resultados y selecciona uno: /passenger/rides/5 (GET)
3. Completa formulario de reserva y envía (POST a /passenger/reservations)
4. Sistema crea reserva (triggers actualizan asientos)
5. Redirige a: /passenger/reservations
6. Pasajero ve reserva pendiente
```

### Flujo de Gestión de Reserva (Chofer)

```
1. Chofer recibe notificación de reserva pendiente
2. Accede a: /driver/reservations
3. Ve la lista de reservas pendientes
4. Clic en "Aceptar" (POST a /driver/reservations/{id}/accept)
5. Sistema actualiza estado de reserva
6. Trigger devuelve asientos si es necesario
7. Redirige a: /driver/reservations con mensaje de éxito
```

---

## 🔒 Seguridad

- **Autenticación:** Verificada en `__construct()` de cada controlador
- **Autorización:** Cada controlador verifica el rol apropiado
- **CSRF:** Tokens implementados en `Session.php`
- **Sanitización:** Todos los inputs pasan por `sanitize()`
- **Validación:** Doble validación (cliente + servidor)

---

## 🚀 Agregar Nuevas Rutas

### Paso 1: Agregar en el Router (`public/index.php`)

```php
elseif ($controller === 'mi_controlador') {
    require_once __DIR__ . '/../controllers/MiController.php';
    $miController = new MiController();
    
    switch ($method) {
        case 'mi_metodo':
            $miController->miMetodo();
            break;
        // ...
    }
}
```

### Paso 2: Crear método en el Controlador

```php
public function miMetodo($param1 = null) {
    // Lógica aquí
    require_once __DIR__ . '/../views/mi_vista.php';
}
```

### Paso 3: Crear la Vista

```php
// views/mi_vista.php
<!DOCTYPE html>
<html>
<head>
    <title>Mi Vista</title>
</head>
<body>
    <!-- Contenido -->
</body>
</html>
```

---

## 📝 Notas Importantes

1. **BASE_URL:** Ajustar en `config/constants.php` según tu instalación
2. **.htaccess:** Verificar `RewriteBase` en `public/.htaccess`
3. **Permisos:** Carpeta `uploads/` debe ser escribible
4. **Producción:** Cambiar `display_errors` a `Off` en `.htaccess`
5. **Logs:** Errores se registran en `error_log` de Apache

---

✅ **Sistema de rutas completamente funcional y documentado.**
