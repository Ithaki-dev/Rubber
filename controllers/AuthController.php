<?php
/**
 * AuthController
 * Gestiona autenticación: registro, login, logout, activación
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Email.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Helpers.php';

class AuthController {
    private $userModel;
    private $session;
    private $email;
    private $validator;
    
    public function __construct() {
        $this->userModel = new User();
        $this->session = Session::getInstance();
        $this->email = new Email();
        $this->validator = new Validator();
    }
    
    /**
     * Mostrar formulario de registro
     */
    public function showRegister() {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->session->isAuthenticated()) {
            $this->redirectToDashboard();
            return;
        }
        
        require_once __DIR__ . '/../views/auth/register.php';
    }
    
    /**
     * Procesar registro de nuevo usuario
     */
    public function register() {
        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/register');
            return;
        }
        
        // Obtener datos del formulario
        $data = [
            'user_type' => sanitize($_POST['user_type'] ?? ''),
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'cedula' => sanitize($_POST['cedula'] ?? ''),
            'birth_date' => sanitize($_POST['birth_date'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? ''
        ];
        
        // Validar que las contraseñas coincidan
        if ($data['password'] !== $data['password_confirm']) {
            $this->session->setFlash('error', 'Las contraseñas no coinciden');
            $this->session->setFlash('old_input', $data);
            redirect('/auth/register');
            return;
        }
        
        // Manejar foto (opcional)
        $photo_path = null;
        if (!empty($_FILES['photo']['name'])) {
            $upload_result = uploadFile($_FILES['photo'], 'users');
            if ($upload_result['success']) {
                $photo_path = $upload_result['path'];
            } else {
                $this->session->setFlash('error', $upload_result['message']);
                $this->session->setFlash('old_input', $data);
                redirect('/auth/register');
                return;
            }
        }
        
        $data['photo_path'] = $photo_path;
        
        // Crear usuario
        $result = $this->userModel->create($data);
        
        if ($result['success']) {
            // Enviar email de activación
            $activation_url = BASE_URL . '/auth/activate?token=' . $result['activation_token'];
            
            $email_sent = $this->email->sendActivationEmail(
                $data['email'],
                $data['first_name'],
                $activation_url
            );
            
            if ($email_sent) {
                $this->session->setFlash('success', 'Registro exitoso. Revisa tu email para activar tu cuenta.');
            } else {
                $this->session->setFlash('success', 'Registro exitoso. No se pudo enviar el email de activación, pero puedes usar este enlace: ' . $activation_url);
            }
            
            redirect('/auth/login');
        } else {
            $this->session->setFlash('error', $result['message']);
            $this->session->setFlash('old_input', $data);
            redirect('/auth/register');
        }
    }
    
    /**
     * Mostrar formulario de login
     */
    public function showLogin() {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->session->isAuthenticated()) {
            $this->redirectToDashboard();
            return;
        }
        
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    /**
     * Procesar login
     */
    public function login() {
        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/login');
            return;
        }
        
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validar campos
        if (empty($email) || empty($password)) {
            $this->session->setFlash('error', 'Email y contraseña son requeridos');
            redirect('/auth/login');
            return;
        }
        
        // Intentar login
        $result = $this->userModel->login($email, $password);
        
        if ($result['success']) {
            // Guardar datos en sesión
            $this->session->set('user_id', $result['user']['id']);
            $this->session->set('user_type', $result['user']['user_type']);
            $this->session->set('user_name', $result['user']['first_name'] . ' ' . $result['user']['last_name']);
            $this->session->set('user_email', $result['user']['email']);
            $this->session->set('user_photo', $result['user']['photo_path']);
            
            // Regenerar session ID por seguridad
            session_regenerate_id(true);
            
            $this->session->setFlash('success', 'Bienvenido, ' . $result['user']['first_name']);
            
            // Redirigir según tipo de usuario
            $this->redirectToDashboard();
        } else {
            $this->session->setFlash('error', $result['message']);
            redirect('/auth/login');
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        $this->session->destroy();
        $this->session->setFlash('success', 'Sesión cerrada exitosamente');
        redirect('/');
    }
    
    /**
     * Activar cuenta de usuario
     */
    public function activate() {
        $token = sanitize($_GET['token'] ?? '');
        
        if (empty($token)) {
            $this->session->setFlash('error', 'Token de activación inválido');
            redirect('/auth/login');
            return;
        }
        
        $result = $this->userModel->activate($token);
        
        if ($result['success']) {
            $this->session->setFlash('success', 'Cuenta activada exitosamente. Ya puedes iniciar sesión.');
        } else {
            $this->session->setFlash('error', $result['message']);
        }
        
        redirect('/auth/login');
    }
    
    /**
     * Mostrar formulario de recuperación de contraseña
     */
    public function showForgotPassword() {
        if ($this->session->isAuthenticated()) {
            $this->redirectToDashboard();
            return;
        }
        
        require_once __DIR__ . '/../views/auth/forgot-password.php';
    }
    
    /**
     * Procesar recuperación de contraseña
     */
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/forgot-password');
            return;
        }
        
        $email = sanitize($_POST['email'] ?? '');
        
        if (empty($email)) {
            $this->session->setFlash('error', 'El email es requerido');
            redirect('/auth/forgot-password');
            return;
        }
        
        // Buscar usuario
        $user = $this->userModel->findByEmail($email);
        
        if ($user) {
            // Generar token de recuperación
            $reset_token = bin2hex(random_bytes(32));
            
            // Aquí deberías guardar el token en la BD (agregar campo en tabla users)
            // Por ahora solo enviamos un email informativo
            
            $reset_url = BASE_URL . '/auth/reset-password?token=' . $reset_token;
            
            // Enviar email
            $this->email->sendPasswordResetEmail(
                $email,
                $user['first_name'],
                $reset_url
            );
        }
        
        // Siempre mostrar el mismo mensaje (seguridad)
        $this->session->setFlash('success', 'Si el email existe, recibirás instrucciones para recuperar tu contraseña.');
        redirect('/auth/login');
    }
    
    /**
     * Mostrar perfil del usuario autenticado
     */
    public function showProfile() {
        // Verificar autenticación
        if (!$this->session->isAuthenticated()) {
            $this->session->setFlash('error', 'Debes iniciar sesión');
            redirect('/auth/login');
            return;
        }
        
        $user_id = $this->session->get('user_id');
        $user = $this->userModel->findById($user_id);
        
        if (!$user) {
            $this->session->setFlash('error', 'Usuario no encontrado');
            redirect('/');
            return;
        }
        
        require_once __DIR__ . '/../views/auth/profile.php';
    }
    
    /**
     * Actualizar perfil del usuario
     */
    public function updateProfile() {
        // Verificar autenticación
        if (!$this->session->isAuthenticated()) {
            $this->session->setFlash('error', 'Debes iniciar sesión');
            redirect('/auth/login');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/profile');
            return;
        }
        
        $user_id = $this->session->get('user_id');
        
        // Obtener datos del formulario
        $data = [
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'cedula' => sanitize($_POST['cedula'] ?? ''),
            'birth_date' => sanitize($_POST['birth_date'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? '')
        ];
        
        // Manejar nueva contraseña (opcional)
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] !== $_POST['new_password_confirm']) {
                $this->session->setFlash('error', 'Las contraseñas no coinciden');
                redirect('/auth/profile');
                return;
            }
            $data['password'] = $_POST['new_password'];
        }
        
        // Manejar nueva foto (opcional)
        if (!empty($_FILES['photo']['name'])) {
            $upload_result = uploadFile($_FILES['photo'], 'users');
            if ($upload_result['success']) {
                $data['photo_path'] = $upload_result['path'];
                
                // Eliminar foto anterior si existe
                $user = $this->userModel->findById($user_id);
                if (!empty($user['photo_path'])) {
                    deleteFile($user['photo_path']);
                }
            }
        }
        
        // Actualizar usuario
        $result = $this->userModel->update($user_id, $data);
        
        if ($result['success']) {
            // Actualizar datos de sesión
            $this->session->set('user_name', $data['first_name'] . ' ' . $data['last_name']);
            $this->session->set('user_email', $data['email']);
            if (isset($data['photo_path'])) {
                $this->session->set('user_photo', $data['photo_path']);
            }
            
            $this->session->setFlash('success', 'Perfil actualizado exitosamente');
        } else {
            $this->session->setFlash('error', $result['message']);
        }
        
        redirect('/auth/profile');
    }
    
    /**
     * Verificar que el usuario esté autenticado
     * @return bool
     */
    public function requireAuth() {
        if (!$this->session->isAuthenticated()) {
            $this->session->setFlash('error', 'Debes iniciar sesión para acceder a esta página');
            redirect('/auth/login');
            return false;
        }
        return true;
    }
    
    /**
     * Verificar que el usuario tenga el rol adecuado
     * @param string|array $allowed_roles Roles permitidos
     * @return bool
     */
    public function requireRole($allowed_roles) {
        if (!$this->requireAuth()) {
            return false;
        }
        
        $user_type = $this->session->get('user_type');
        
        if (is_array($allowed_roles)) {
            if (!in_array($user_type, $allowed_roles)) {
                $this->session->setFlash('error', 'No tienes permiso para acceder a esta página');
                redirect('/dashboard');
                return false;
            }
        } else {
            if ($user_type !== $allowed_roles) {
                $this->session->setFlash('error', 'No tienes permiso para acceder a esta página');
                redirect('/dashboard');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Redirigir al dashboard según tipo de usuario
     */
    private function redirectToDashboard() {
        $user_type = $this->session->get('user_type');
        
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
}
