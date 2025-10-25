<?php
/**
 * Clase Email - Servicio de envío usando PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class Email {
    private $mailer;
    private $templatesPath;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->templatesPath = __DIR__ . '/../templates/emails/';
        $this->configureSMTP();
    }
    
    private function configureSMTP() {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = SMTP_PORT;
            
            $this->mailer->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
            $this->mailer->addReplyTo(SMTP_USERNAME, SMTP_FROM_NAME);
            
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            throw new Exception("Error configuración email: " . $e->getMessage());
        }
    }
    
    public function sendEmail($to, $subject, $htmlContent, $textContent = null) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlContent;
            
            if ($textContent) {
                $this->mailer->AltBody = $textContent;
            }
            
            $sent = $this->mailer->send();
            
            return [
                'success' => $sent,
                'message' => $sent ? 'Email enviado exitosamente' : 'Error enviando email',
                'to' => $to,
                'method' => 'phpmailer'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    private function loadTemplate($templateName, $variables = []) {
        $templatePath = $this->templatesPath . $templateName . '.html';
        
        if (!file_exists($templatePath)) {
            throw new Exception("Template no encontrado: $templateName");
        }
        
        $content = file_get_contents($templatePath);
        
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . strtoupper($key) . '}}', htmlspecialchars($value), $content);
        }
        
        return $content;
    }
    
    public function sendActivationEmail($to, $userName, $token) {
        try {
            $activationUrl = BASE_URL . "/auth/activate?token=" . urlencode($token);
            
            $variables = [
                'user_name' => $userName,
                'activation_url' => $activationUrl
            ];
            
            $htmlContent = $this->loadTemplate('activation', $variables);
            
            return $this->sendEmail($to, 'Activa tu cuenta - Carpooling UTN', $htmlContent);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    public function sendPasswordReset($to, $userName, $token) {
        try {
            $resetUrl = BASE_URL . "/auth/reset?token=" . urlencode($token);
            
            $variables = [
                'user_name' => $userName,
                'reset_url' => $resetUrl
            ];
            
            $htmlContent = $this->loadTemplate('password-reset', $variables);
            
            return $this->sendEmail($to, 'Recuperar contraseña - Carpooling UTN', $htmlContent);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    public function sendWelcome($to, $userName) {
        try {
            $variables = [
                'user_name' => $userName,
                'dashboard_url' => BASE_URL . '/dashboard'
            ];
            
            $htmlContent = $this->loadTemplate('welcome', $variables);
            
            return $this->sendEmail($to, '¡Bienvenido a Carpooling UTN!', $htmlContent);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    public function sendReservationConfirmation($to, $reservationData) {
        try {
            $variables = [
                'date' => $reservationData['fecha'] ?? 'No especificado',
                'time' => $reservationData['hora'] ?? 'No especificado',
                'origin' => $reservationData['origen'] ?? 'No especificado',
                'destination' => $reservationData['destino'] ?? 'No especificado',
                'cost' => number_format($reservationData['costo'] ?? 0),
                'seats' => $reservationData['asientos'] ?? 1
            ];
            
            $htmlContent = $this->loadTemplate('reservation', $variables);
            
            return $this->sendEmail($to, 'Reserva confirmada - Carpooling UTN', $htmlContent);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    public function sendContactEmail($adminEmail, $senderName, $senderEmail, $subject, $message) {
        try {
            $variables = [
                'sender_name' => $senderName,
                'sender_email' => $senderEmail,
                'subject' => $subject,
                'message' => $message,
                'timestamp' => date('d/m/Y H:i:s')
            ];
            
            $htmlContent = $this->loadTemplate('contact', $variables);
            
            return $this->sendEmail($adminEmail, "Contacto: $subject", $htmlContent);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    public function testConfiguration() {
        try {
            $requiredConstants = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USERNAME', 'SMTP_PASSWORD'];
            $missing = [];
            
            foreach ($requiredConstants as $constant) {
                if (!defined($constant) || empty(constant($constant))) {
                    $missing[] = $constant;
                }
            }
            
            if (!empty($missing)) {
                return [
                    'success' => false,
                    'error' => 'Constantes faltantes: ' . implode(', ', $missing)
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Configuración correcta',
                'smtp_host' => SMTP_HOST,
                'smtp_port' => SMTP_PORT,
                'smtp_username' => SMTP_USERNAME,
                'phpmailer_version' => PHPMailer::VERSION
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
?>
