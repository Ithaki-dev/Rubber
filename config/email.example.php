<?php
/**
 * Configuración de email (SMTP)
 * 
 * Copia este archivo como email.php y configura tus credenciales
 * Puedes usar servicios como:
 * - Gmail (requiere app password)
 * - Mailtrap (para desarrollo)
 * - SendGrid, Mailgun, etc.
 */

// Configuración SMTP
define('SMTP_HOST', 'smtp.mailtrap.io'); // o smtp.gmail.com para Gmail
define('SMTP_PORT', 2525); // 587 para Gmail
define('SMTP_USER', 'tu_usuario');
define('SMTP_PASS', 'tu_password');
define('SMTP_SECURE', 'tls'); // tls o ssl

// Información del remitente
define('SMTP_FROM_EMAIL', 'noreply@carpooling.com');
define('SMTP_FROM_NAME', 'Carpooling System');

// Para desarrollo con Mailtrap:
// 1. Regístrate en https://mailtrap.io
// 2. Crea un inbox
// 3. Copia las credenciales aquí
