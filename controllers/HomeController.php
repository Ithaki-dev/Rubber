<?php
/**
 * HomeController
 * Gestiona páginas públicas: inicio, búsqueda pública, contacto
 */

require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Email.php';
require_once __DIR__ . '/../core/Helpers.php';

class HomeController {
    private $rideModel;
    private $email;
    
    public function __construct() {
        $this->rideModel = new Ride();
        $this->email = new Email();
    }
    
    /**
     * Página de inicio
     */
    public function index() {
        // Obtener viajes próximos destacados
        $featured_rides = $this->rideModel->getUpcoming(6);
        
        // Estadísticas generales
        $stats = [
            'total_rides' => count($this->rideModel->getAll(['is_active' => 1])),
            'available_today' => count($this->rideModel->search(['ride_date' => date('Y-m-d')])),
        ];
        
        require_once __DIR__ . '/../views/home/index.php';
    }
    
    /**
     * Búsqueda pública de viajes (sin autenticación)
     */
    public function searchPublic() {
        $filters = [
            'departure_location' => sanitize($_GET['departure'] ?? ''),
            'arrival_location' => sanitize($_GET['arrival'] ?? ''),
            'ride_date' => sanitize($_GET['date'] ?? ''),
            'min_seats' => sanitize($_GET['seats'] ?? '')
        ];
        
        // Remover filtros vacíos
        $filters = array_filter($filters);
        
        $rides = empty($filters) ? $this->rideModel->getUpcoming(20) : $this->rideModel->search($filters);
        
        require_once __DIR__ . '/../views/home/search.php';
    }
    
    /**
     * Ver detalles de un viaje (público)
     */
    public function showRide($id) {
        $ride = $this->rideModel->findById($id);
        
        if (!$ride) {
            Session::setFlash('error', 'Viaje no encontrado');
            redirect('/');
            return;
        }
        
        require_once __DIR__ . '/../views/home/ride-details.php';
    }
    
    /**
     * Página "Cómo funciona"
     */
    public function howItWorks() {
        require_once __DIR__ . '/../views/home/how-it-works.php';
    }
    
    /**
     * Página "Acerca de"
     */
    public function about() {
        require_once __DIR__ . '/../views/public/about.php';
    }
    
    /**
     * Página de contacto
     */
    public function contact() {
        require_once __DIR__ . '/../views/public/contact.php';
    }
    
    /**
     * Procesar formulario de contacto
     */
    public function sendContact() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/contact');
            return;
        }
        
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'subject' => sanitize($_POST['subject'] ?? ''),
            'message' => sanitize($_POST['message'] ?? '')
        ];
        
        // Validar campos
        if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
            Session::setFlash('error', 'Todos los campos son requeridos');
            Session::setFlash('old_input', $data);
            redirect('/contact');
            return;
        }
        
        // Enviar email al administrador
        $email_sent = $this->email->sendContactEmail(
            ADMIN_EMAIL,
            $data['name'],
            $data['email'],
            $data['subject'],
            $data['message']
        );
        
        if ($email_sent) {
            Session::setFlash('success', 'Mensaje enviado exitosamente. Te contactaremos pronto.');
        } else {
            Session::setFlash('warning', 'Mensaje recibido, pero no se pudo enviar el email de confirmación.');
        }
        
        redirect('/contact');
    }
    
    /**
     * Redirigir al dashboard correspondiente
     */
    public function dashboard() {
        if (!Session::isLoggedIn()) {
            redirect('/auth/login');
            return;
        }
        
        $user_type = Session::get('user_type');
        
        switch ($user_type) {
            case 'admin':
                redirect('/admin/dashboard');
                break;
            case 'driver':
                redirect('/driver/dashboard');
                break;
            case 'passenger':
                redirect('/passenger/dashboard');
                break;
            default:
                redirect('/');
                break;
        }
    }
    
    /**
     * Página de error 404
     */
    public function notFound() {
        http_response_code(404);
        require_once __DIR__ . '/../views/errors/404.php';
    }
    
    /**
     * Página de error 403
     */
    public function forbidden() {
        http_response_code(403);
        require_once __DIR__ . '/../views/errors/403.php';
    }
    
    /**
     * Página de error 500
     */
    public function serverError() {
        http_response_code(500);
        require_once __DIR__ . '/../views/errors/500.php';
    }
}
