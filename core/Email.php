<?php
/**
 * Clase Email - Servicio de envío usando Testmail.app API
 */

class Email {
    private $namespace;
    private $apiKey;
    private $apiUrl;

    public function __construct() {
        $this->namespace = defined('TESTMAIL_NAMESPACE') ? TESTMAIL_NAMESPACE : '';
        $this->apiKey = defined('TESTMAIL_API_KEY') ? TESTMAIL_API_KEY : '';
        $this->apiUrl = defined('TESTMAIL_API_URL') ? TESTMAIL_API_URL : '';
    }

    private function sendEmail($to, $subject, $htmlBody, $textBody = '') {
        // Si API no está configurada, solo simular
        if (empty($this->apiKey)) {
            error_log("Email simulado - To: $to | Subject: $subject");
            return true; // Retornar true para no romper el flujo
        }

        try {
            if (strpos($to, '@') === false) {
                $to = $to . '@' . $this->namespace . '.testmail.app';
            }
            
            $data = [
                'to' => $to,
                'from' => SMTP_FROM_EMAIL,
                'from_name' => SMTP_FROM_NAME,
                'subject' => $subject,
                'html' => $htmlBody,
                'text' => $textBody ?: strip_tags($htmlBody)
            ];

            $ch = curl_init($this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return ($httpCode >= 200 && $httpCode < 300);
            
        } catch (Exception $e) {
            error_log("Error email: " . $e->getMessage());
            return false;
        }
    }

    public function sendActivationEmail($to, $name, $token) {
        $link = BASE_URL . "/auth/activate?token=" . urlencode($token);
        $html = "<h2>Bienvenido, $name!</h2><p>Activa tu cuenta: <a href='$link'>$link</a></p>";
        return $this->sendEmail($to, 'Activa tu cuenta', $html);
    }

    public function sendPendingReservationNotification($to, $name, $count) {
        $html = "<h2>Hola, $name</h2><p>Tienes $count reserva(s) pendiente(s).</p>";
        return $this->sendEmail($to, 'Reservas pendientes', $html);
    }

    public function sendContactEmail($adminEmail, $senderName, $senderEmail, $subject, $message) {
        $html = "<h2>Contacto de: $senderName</h2><p>Email: $senderEmail</p><p>$message</p>";
        return $this->sendEmail($adminEmail, "Contacto: $subject", $html);
    }
}
