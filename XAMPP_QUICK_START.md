# 🚀 INICIO RÁPIDO CON XAMPP

## ⚡ 5 Minutos para Empezar

### 1️⃣ Instalar XAMPP
- **Descargar:** https://www.apachefriends.org/
- **Instalar** con configuración por defecto
- **Iniciar** Apache y MySQL

### 2️⃣ Ejecutar Script de Instalación

**Linux:**
```bash
cd /home/bob/Github/Rubber
./install-xampp.sh
```

**Windows:**
```cmd
cd C:\ruta\a\Rubber
install-xampp.bat
```

### 3️⃣ Importar Base de Datos

1. Abrir: **http://localhost/phpmyadmin**
2. Click en **"Nueva"** → Nombre: `carpooling_db`
3. Click en **"Importar"**
4. Seleccionar: `sql/schema.sql` → **Continuar**
5. (Opcional) Importar: `sql/seed.sql`

### 4️⃣ Configurar Credenciales

**Editar `config/database.php`:**
```php
define('DB_USER', 'root');
define('DB_PASS', '');  // Sin password en XAMPP
```

**Editar `config/email.php`:**
```php
// Usar Mailtrap.io para desarrollo
define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
define('SMTP_USER', 'tu_usuario_mailtrap');
define('SMTP_PASS', 'tu_password_mailtrap');
```

> 💡 Registrarse gratis en: https://mailtrap.io

### 5️⃣ Acceder a la Aplicación

🌐 **http://localhost/rubber**

---

## 👤 Usuarios de Prueba

**Admin:**
- Email: `admin@carpooling.com`
- Password: `admin123`

**Chofer:**
- Email: `juan.perez@email.com`
- Password: `password123`

**Pasajero:**
- Email: `ana.martinez@email.com`
- Password: `password123`

---

## 🔗 Enlaces Útiles

| Servicio | URL |
|----------|-----|
| 🏠 Aplicación | http://localhost/rubber |
| 🗄️ phpMyAdmin | http://localhost/phpmyadmin |
| 📊 XAMPP Dashboard | http://localhost/dashboard |

---

## ⚙️ Comandos XAMPP

### Linux:
```bash
# Iniciar todo
sudo /opt/lampp/lampp start

# Detener todo
sudo /opt/lampp/lampp stop

# Ver estado
sudo /opt/lampp/lampp status

# Reiniciar
sudo /opt/lampp/lampp restart
```

### Windows:
- Usar **XAMPP Control Panel**
- Botones **Start/Stop** para cada servicio

---

## 🐛 Problemas Comunes

### ❌ Apache no inicia
**Causa:** Puerto 80 ocupado

**Solución Linux:**
```bash
sudo netstat -tulpn | grep :80
sudo systemctl stop apache2
```

**Solución Windows:**
- Cambiar puerto en `httpd.conf`: `Listen 80` → `Listen 8080`
- Acceder: `http://localhost:8080/rubber`

### ❌ MySQL no inicia
**Causa:** Puerto 3306 ocupado

**Solución:**
- Detener otros servicios MySQL
- O cambiar puerto en XAMPP config

### ❌ "Access Denied" en Base de Datos
**Solución:**
En phpMyAdmin ejecutar:
```sql
CREATE USER 'root'@'localhost' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON carpooling_db.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### ❌ Error 404 - Página no encontrada
**Verificar:**
1. Apache está corriendo
2. Proyecto está en `htdocs/rubber`
3. Acceder a: `http://localhost/rubber` (con /rubber)

### ❌ Error "Call to undefined function"
**Solución:**
1. Abrir XAMPP Control Panel
2. Config → PHP (php.ini)
3. Buscar la extensión y descomentar (quitar `;`)
4. Reiniciar Apache

---

## 📋 Checklist Pre-Desarrollo

Antes de empezar a codear:

- [ ] ✅ XAMPP instalado
- [ ] ✅ Apache corriendo
- [ ] ✅ MySQL corriendo
- [ ] ✅ Base de datos `carpooling_db` creada
- [ ] ✅ Tablas importadas
- [ ] ✅ Datos de prueba cargados
- [ ] ✅ `composer install` ejecutado
- [ ] ✅ Archivos config editados
- [ ] ✅ Página carga sin errores
- [ ] ✅ phpMyAdmin accesible

---

## 🎯 Siguiente Paso

Una vez todo funcione, continúa con:

### Implementar Autenticación
Ver: `GUIA_PROYECTO.md` - Sección 5.5

1. Crear modelos (User, Vehicle, Ride, Reservation)
2. Crear AuthController
3. Crear vistas de login/registro
4. Probar flujo completo

---

## 📚 Documentación Completa

Para más detalles, ver:
- 📘 **[CONFIGURACION_XAMPP.md](CONFIGURACION_XAMPP.md)** - Guía completa
- 📗 **[README.md](README.md)** - Documentación general
- 📕 **[GUIA_PROYECTO.md](GUIA_PROYECTO.md)** - Desarrollo paso a paso

---

## 💡 Tips

1. **Usa phpMyAdmin** para ver y modificar datos en tiempo real
2. **Habilita errores** en `php.ini`: `display_errors = On`
3. **Revisa logs** en `/opt/lampp/logs/error_log` o `C:\xampp\apache\logs\error.log`
4. **Backup frecuente** desde phpMyAdmin (Exportar)
5. **Git commits frecuentes** cada funcionalidad pequeña

---

## 🎉 ¡Listo!

Tu entorno está configurado. **¡A codear!** 🚀

Si tienes problemas, consulta **CONFIGURACION_XAMPP.md** para troubleshooting detallado.

---

**Última actualización:** Octubre 15, 2025
