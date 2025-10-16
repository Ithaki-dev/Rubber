@echo off
REM Script de instalación automática para Windows con XAMPP

echo ================================================
echo 🚗 Carpooling System - Instalacion Automatica
echo ================================================
echo.

REM Verificar si XAMPP está instalado
echo 1. Verificando XAMPP...
if exist "C:\xampp" (
    echo [OK] XAMPP encontrado
) else (
    echo [ERROR] XAMPP no encontrado en C:\xampp
    echo Por favor instala XAMPP desde: https://www.apachefriends.org/
    pause
    exit /b 1
)

REM Verificar si Composer está instalado
echo.
echo 2. Verificando Composer...
where composer >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo [OK] Composer encontrado
) else (
    echo [ERROR] Composer no encontrado
    echo Por favor instala Composer desde: https://getcomposer.org/download/
    pause
    exit /b 1
)

REM Instalar dependencias
echo.
echo 3. Instalando dependencias de PHP...
call composer install
if %ERRORLEVEL% EQU 0 (
    echo [OK] Dependencias instaladas
) else (
    echo [ERROR] Error al instalar dependencias
    pause
    exit /b 1
)

REM Configurar archivos de configuración
echo.
echo 4. Configurando archivos...

if not exist "config\database.php" (
    copy "config\database.example.php" "config\database.php"
    echo [OK] Archivo database.php creado
) else (
    echo [INFO] database.php ya existe
)

if not exist "config\email.php" (
    copy "config\email.example.php" "config\email.php"
    echo [OK] Archivo email.php creado
) else (
    echo [INFO] email.php ya existe
)

REM Crear directorios necesarios
echo.
echo 5. Creando directorios...
if not exist "public\uploads\profiles" mkdir "public\uploads\profiles"
if not exist "public\uploads\vehicles" mkdir "public\uploads\vehicles"
if not exist "logs" mkdir "logs"
echo [OK] Directorios creados

REM Copiar proyecto a htdocs (opcional)
echo.
echo 6. Configurando acceso web...
if exist "C:\xampp\htdocs\rubber" (
    echo [INFO] Directorio rubber ya existe en htdocs
) else (
    set /p copy_htdocs="¿Deseas copiar el proyecto a C:\xampp\htdocs\rubber? (s/n): "
    if /i "%copy_htdocs%"=="s" (
        xcopy /E /I /Y . C:\xampp\htdocs\rubber
        echo [OK] Proyecto copiado a htdocs
        echo Acceso: http://localhost/rubber
    )
)

REM Resumen final
echo.
echo ================================================
echo ✅ Instalacion Completada
echo ================================================
echo.
echo 📋 Proximos pasos:
echo.
echo 1. Inicia XAMPP Control Panel y arranca Apache y MySQL
echo.
echo 2. Configura las credenciales en:
echo    - config\database.php (usar root sin password)
echo    - config\email.php (usar Mailtrap.io)
echo.
echo 3. Importa la base de datos:
echo    - Abre http://localhost/phpmyadmin
echo    - Crea la base de datos 'carpooling_db'
echo    - Importa sql\schema.sql
echo    - (Opcional) Importa sql\seed.sql
echo.
echo 4. Accede a la aplicacion:
echo    http://localhost/rubber
echo.
echo 5. Usuario admin de prueba:
echo    Email: admin@carpooling.com
echo    Password: admin123
echo.
echo 📚 Documentacion:
echo    - README.md - Documentacion general
echo    - CONFIGURACION_XAMPP.md - Guia detallada de XAMPP
echo    - GUIA_PROYECTO.md - Guia de desarrollo
echo.
echo ================================================
pause
