<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling UCR - Home</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 90%;
        }
        h1 {
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }
        h2 {
            color: #764ba2;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        .success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin: 2rem 0;
        }
        .routes-list {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .route-link {
            display: block;
            padding: 0.8rem;
            margin: 0.5rem 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s;
        }
        .route-link:hover {
            background: #667eea;
            color: white;
            transform: translateX(5px);
        }
        .info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚗 Carpooling UCR</h1>
        
        <div class="success">
            <strong>✅ ¡Sistema de Rutas Funcionando!</strong><br>
            El router está correctamente configurado y procesando solicitudes.
        </div>
        
        <p>
            Bienvenido al sistema de carpooling de la Universidad de Costa Rica. 
            Comparte viajes, ahorra dinero y contribuye al medio ambiente.
        </p>
        
        <div class="info">
            <strong>🔧 Estado del Sistema:</strong><br>
            • Router: Activo<br>
            • Base de Datos: Conectada<br>
            • Controladores: Cargados<br>
            • Modelos: Disponibles
        </div>
        
        <h2>🧪 Pruebas de Rutas</h2>
        <div class="routes-list">
            <a href="<?= BASE_URL ?>/auth/login" class="route-link">
                📝 Login (/auth/login)
            </a>
            <a href="<?= BASE_URL ?>/auth/register" class="route-link">
                ✍️ Registro (/auth/register)
            </a>
            <a href="<?= BASE_URL ?>/passenger/dashboard" class="route-link">
                👤 Dashboard Pasajero (/passenger/dashboard)
            </a>
            <a href="<?= BASE_URL ?>/driver/dashboard" class="route-link">
                🚙 Dashboard Chofer (/driver/dashboard)
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard" class="route-link">
                👨‍💼 Dashboard Admin (/admin/dashboard)
            </a>
            <a href="<?= BASE_URL ?>/about" class="route-link">
                ℹ️ Acerca de (/about)
            </a>
            <a href="<?= BASE_URL ?>/contact" class="route-link">
                📧 Contacto (/contact)
            </a>
            <a href="<?= BASE_URL ?>/ruta-invalida" class="route-link">
                ❌ Prueba 404 (/ruta-invalida)
            </a>
        </div>
        
        <h2>📋 Rutas Implementadas</h2>
        <p>
            El sistema cuenta con <strong>61 rutas</strong> distribuidas en 5 controladores:
        </p>
        <ul style="margin-left: 2rem; color: #555; line-height: 2;">
            <li><strong>HomeController:</strong> 7 rutas públicas</li>
            <li><strong>AuthController:</strong> 8 rutas de autenticación</li>
            <li><strong>PassengerController:</strong> 8 rutas para pasajeros</li>
            <li><strong>DriverController:</strong> 20 rutas para choferes</li>
            <li><strong>AdminController:</strong> 18 rutas de administración</li>
        </ul>
        
        <p style="margin-top: 2rem; text-align: center; color: #888;">
            <small>ISW-613 - Universidad de Costa Rica</small>
        </p>
    </div>
</body>
</html>
