# 📚 GUÍA PASO A PASO - IMPORTACIÓN DE BASE DE DATOS

## ✅ Archivos SQL Simplificados

Esta versión **NO incluye** procedimientos almacenados debido al bug de XAMPP.
La lógica de estadísticas está en `core/Statistics.php`.

### 📁 Archivos creados:

1. **1_schema.sql** - Tablas y vistas
2. **2_triggers.sql** - Triggers para gestionar asientos
3. **3_seed.sql** - Datos de prueba

---

## 🚀 OPCIÓN 1: Importar desde phpMyAdmin (RECOMENDADO)

### Paso 1: Acceder a phpMyAdmin
```
URL: http://localhost/phpmyadmin
Usuario: root
Contraseña: (dejar vacío)
```

### Paso 2: Seleccionar la base de datos
- En el menú lateral izquierdo, haz clic en **carpooling_db**

### Paso 3: Importar schema (Tablas y Vistas)
1. Haz clic en la pestaña **"Importar"**
2. Haz clic en **"Seleccionar archivo"**
3. Navega a: `Github/Rubber/sql/1_schema.sql`
4. Haz clic en **"Continuar"** al final de la página
5. ✅ Deberías ver: **"Importación finalizada correctamente"**

### Paso 4: Importar triggers
1. Permanece en la pestaña **"Importar"**
2. Haz clic en **"Seleccionar archivo"**
3. Navega a: `Github/Rubber/sql/2_triggers.sql`
4. Haz clic en **"Continuar"**
5. ✅ Deberías ver: **"Importación finalizada correctamente"**

### Paso 5: Verificar triggers creados
1. En el menú lateral izquierdo, haz clic en cualquier tabla (ej: **reservations**)
2. Haz clic en la pestaña **"Disparadores"** o **"Triggers"**
3. ✅ Deberías ver 3 triggers:
   - `before_reservation_insert`
   - `after_reservation_insert`
   - `after_reservation_update`

### Paso 6: Importar datos de prueba
1. Permanece en la pestaña **"Importar"**
2. Haz clic en **"Seleccionar archivo"**
3. Navega a: `Github/Rubber/sql/3_seed.sql`
4. Haz clic en **"Continuar"**
5. ✅ Deberías ver: **"Importación finalizada correctamente"**

### Paso 7: Verificar datos importados
```sql
-- Copiar y pegar en la pestaña SQL de phpMyAdmin:

SELECT 'users' AS tabla, COUNT(*) AS total FROM users
UNION ALL
SELECT 'vehicles', COUNT(*) FROM vehicles
UNION ALL
SELECT 'rides', COUNT(*) FROM rides
UNION ALL
SELECT 'reservations', COUNT(*) FROM reservations;
```

**Resultado esperado:**
```
tabla           total
-----------------------
users           9
vehicles        6
rides           12
reservations    12
```

---

## 🖥️ OPCIÓN 2: Importar desde Terminal

### Paso 1: Abrir terminal

### Paso 2: Navegar al directorio del proyecto
```bash
cd /home/bob/Github/Rubber
```

### Paso 3: Ejecutar los tres archivos SQL
```bash
# 1. Schema (tablas y vistas)
sudo /opt/lampp/bin/mysql -u root carpooling_db < sql/1_schema.sql

# 2. Triggers
sudo /opt/lampp/bin/mysql -u root carpooling_db < sql/2_triggers.sql

# 3. Datos de prueba
sudo /opt/lampp/bin/mysql -u root carpooling_db < sql/3_seed.sql
```

### Paso 4: Verificar importación
```bash
sudo /opt/lampp/bin/mysql -u root -e "
USE carpooling_db;
SELECT 'users' AS tabla, COUNT(*) AS total FROM users
UNION ALL
SELECT 'vehicles', COUNT(*) FROM vehicles
UNION ALL
SELECT 'rides', COUNT(*) FROM rides
UNION ALL
SELECT 'reservations', COUNT(*) FROM reservations;
"
```

---

## 🧪 PRUEBAS POST-IMPORTACIÓN

### 1. Verificar que los triggers funcionan

```sql
-- En phpMyAdmin, pestaña SQL:

-- Seleccionar un viaje con asientos disponibles
SELECT id, ride_name, available_seats, total_seats 
FROM rides 
WHERE id = 1;

-- Resultado esperado: available_seats = 2 (porque hay 2 reservas pendientes/aceptadas)
```

### 2. Probar trigger de validación

```sql
-- Esto DEBE fallar con error "No hay suficientes asientos disponibles"
INSERT INTO reservations (passenger_id, ride_id, seats_requested, status, total_cost)
VALUES (5, 1, 10, 'pending', 15000.00);

-- Si falla correctamente, ¡los triggers funcionan! ✅
```

### 3. Probar vistas

```sql
-- Ver rides completas
SELECT * FROM v_rides_complete LIMIT 3;

-- Ver reservations completas
SELECT * FROM v_reservations_complete LIMIT 3;
```

---

## 👥 USUARIOS DE PRUEBA

Todos los usuarios tienen la misma contraseña: **password123**

### Admin
- Email: `admin@carpooling.com`
- Password: `password123`

### Choferes (Drivers)
- Email: `juan.perez@email.com` - Password: `password123`
- Email: `maria.garcia@email.com` - Password: `password123`
- Email: `carlos.rodriguez@email.com` - Password: `password123`

### Pasajeros (Passengers)
- Email: `ana.martinez@email.com` - Password: `password123`
- Email: `pedro.lopez@email.com` - Password: `password123`
- Email: `lucia.fernandez@email.com` - Password: `password123`

---

## 📊 ESTRUCTURA DE DATOS

### Tablas principales:
1. **users** - 9 usuarios (1 admin, 3 choferes, 5 pasajeros)
2. **vehicles** - 6 vehículos
3. **rides** - 12 viajes (8 futuros, 2 pasados, 2 prueba)
4. **reservations** - 12 reservas (varios estados)

### Vistas:
1. **v_rides_complete** - Rides con datos del chofer y vehículo
2. **v_reservations_complete** - Reservas con todos los detalles

### Triggers:
1. **before_reservation_insert** - Valida asientos disponibles
2. **after_reservation_insert** - Descuenta asientos al reservar
3. **after_reservation_update** - Devuelve asientos al cancelar/rechazar

---

## ⚠️ SOLUCIÓN DE PROBLEMAS

### Error: "Column count of mysql.proc is wrong"
**Este error NO debería aparecer** porque eliminamos todos los procedimientos almacenados.
Si aparece, verifica que estás importando los archivos correctos.

### Error: "Can't find file: 'carpooling_db'"
Verifica que la base de datos existe:
```sql
SHOW DATABASES LIKE 'carpooling_db';
```

Si no existe, créala:
```sql
CREATE DATABASE carpooling_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Error: "Table 'users' already exists"
La base de datos ya tiene tablas. Opciones:
1. Eliminar y recrear (BORRA TODOS LOS DATOS):
```bash
sudo /opt/lampp/bin/mysql -u root -e "DROP DATABASE IF EXISTS carpooling_db; CREATE DATABASE carpooling_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```
2. Importar en otra base de datos de prueba

### Los triggers no aparecen
Verifica que importaste el archivo `2_triggers.sql` correctamente.
En phpMyAdmin: carpooling_db → tabla reservations → pestaña "Disparadores"

---

## 🎯 PRÓXIMOS PASOS

Una vez importada la base de datos:

1. ✅ Crear modelos PHP: `User.php`, `Vehicle.php`, `Ride.php`, `Reservation.php`
2. ✅ Implementar autenticación: `AuthController.php`
3. ✅ Crear vistas de login/registro
4. ✅ Usar `core/Statistics.php` para estadísticas (reemplazo de stored procedures)
5. ✅ Crear dashboards por tipo de usuario

---

## 📝 NOTAS IMPORTANTES

- **No hay procedimientos almacenados** debido al bug de XAMPP
- Las estadísticas se calculan con `core/Statistics.php`
- Los triggers **SÍ funcionan** (no usan mysql.proc)
- Password hash usado: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`
- Todos los datos son de prueba, puedes modificarlos

---

✅ **¡Listo! Base de datos configurada y funcionando.**
