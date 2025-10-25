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
    private $email;
    private $validator;
    
    public function __construct() {
        $this->userModel = new User();
        $this->email = new Email();
        $this->validator = new Validator();
    }
    
    /**
     * Mostrar formulario de registro
     */
    public function showRegister() {
        // Si ya está autenticado, redirigir al dashboard
        if (Session::isLoggedIn()) {
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
            Session::setFlash('error', 'Las contraseñas no coinciden');
            Session::setFlash('old_input', $data);
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
                Session::setFlash('error', $upload_result['message']);
                Session::setFlash('old_input', $data);
                redirect('/auth/register');
                return;
            }
        }
        
        $data['photo_path'] = $photo_path;
        
        // Crear usuario
        $result = $this->userModel->create($data);
        
        if ($result['success']) {
            // Enviar email de activación
            $emailResult = $this->email->sendActivationEmail(
                $data['email'],
                $data['first_name'],
                $result['activation_token']
            );
            
            if ($emailResult['success']) {
                Session::setFlash('success', '
                    <strong>¡Registro exitoso! 🎉</strong><br>
                    Te hemos enviado un email de activación a <strong>' . $data['email'] . '</strong><br>
                    <small class="text-muted">Revisa también tu carpeta de spam</small>
                ');
            } else {
                // Log el error del email para debugging
                error_log('Error enviando email de activación: ' . print_r($emailResult, true));
                
                $activation_url = BASE_URL . '/auth/activate?token=' . $result['activation_token'];
                Session::setFlash('warning', '
                    <strong>Registro exitoso</strong>, pero no se pudo enviar el email de activación.<br>
                    <strong>Enlace manual:</strong><br>
                    <a href="' . $activation_url . '" class="btn btn-sm btn-outline-primary mt-2">Activar cuenta ahora</a>
                ');
            }
            
            redirect('/auth/login?registered=1');
        } else {
            Session::setFlash('error', $result['message']);
            Session::setFlash('old_input', $data);
            redirect('/auth/register');
        }
    }
    
    /**
     * Mostrar formulario de login
     */
    public function showLogin() {
        // Si ya está autenticado, redirigir al dashboard
        if (Session::isLoggedIn()) {
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
            Session::setFlash('error', 'Email y contraseña son requeridos');
            redirect('/auth/login');
            return;
        }
        
        // Intentar login
        $result = $this->userModel->login($email, $password);
        
        if ($result['success']) {
            // Guardar datos en sesión
            Session::set('user_id', $result['user']['id']);
            Session::set('user_type', $result['user']['user_type']);
            Session::set('first_name', $result['user']['first_name']);
            Session::set('last_name', $result['user']['last_name']);
            Session::set('user_name', $result['user']['first_name'] . ' ' . $result['user']['last_name']);
            Session::set('user_email', $result['user']['email']);
            Session::set('phone', $result['user']['phone'] ?? '');
            Session::set('student_id', $result['user']['cedula'] ?? '');
            Session::set('user_photo', $result['user']['photo_path']);
            
            // Regenerar session ID por seguridad
            session_regenerate_id(true);
            
            Session::setFlash('success', 'Bienvenido, ' . $result['user']['first_name']);
            
            // Redirigir según tipo de usuario
            $this->redirectToDashboard();
        } else {
            Session::setFlash('error', $result['message']);
            redirect('/auth/login');
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        Session::destroy();
        Session::setFlash('success', 'Sesión cerrada exitosamente');
        redirect('/');
    }
    
    /**
     * Activar cuenta de usuario
     */
    public function activate() {
        $token = sanitize($_GET['token'] ?? '');
        
        if (empty($token)) {
            Session::setFlash('error', 'Token de activación inválido o no proporcionado');
            redirect('/auth/login');
            return;
        }
        
        // Intentar activar la cuenta
        $result = $this->userModel->activate($token);
        
        if ($result['success']) {
            // Enviar email de bienvenida
            if (isset($result['user'])) {
                $welcomeResult = $this->email->sendWelcome(
                    $result['user']['email'],
                    $result['user']['first_name']
                );
                
                if (!$welcomeResult['success']) {
                    // Log el error del email pero no interrumpir el flujo
                    error_log('Error enviando email de bienvenida: ' . print_r($welcomeResult, true));
                }
            }
            
            Session::setFlash('success', '🎉 ¡Cuenta activada exitosamente! Ya puedes iniciar sesión y comenzar a usar Carpooling UTN.');
            redirect('/auth/login?activated=1');
        } else {
            $errorMessage = $result['message'] ?? 'Error desconocido al activar la cuenta';
            Session::setFlash('error', $errorMessage);
            
            // Si el token es inválido o expirado, ofrecer reenviar
            if (strpos($errorMessage, 'inválido') !== false || strpos($errorMessage, 'expirado') !== false) {
                Session::setFlash('info', 'Si necesitas un nuevo enlace de activación, <a href="/auth/resend-activation">haz clic aquí</a>.');
            }
            
            redirect('/auth/login');
        }
    }
    
    /**
     * Reenviar email de activación
     */
    public function resendActivation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            
            if (empty($email)) {
                Session::setFlash('error', 'Por favor ingresa tu email');
                redirect('/auth/resend-activation');
                return;
            }
            
            $result = $this->userModel->resendActivation($email);
            
            if ($result['success']) {
                $emailResult = $this->email->sendActivationEmail(
                    $email,
                    $result['user']['first_name'],
                    $result['activation_token']
                );
                
                if ($emailResult['success']) {
                    Session::setFlash('success', 'Email de activación reenviado exitosamente a ' . $email);
                } else {
                    $activation_url = BASE_URL . '/auth/activate?token=' . $result['activation_token'];
                    Session::setFlash('warning', 'No se pudo enviar el email. Usa este enlace: <a href="' . $activation_url . '">Activar cuenta</a>');
                }
            } else {
                Session::setFlash('error', $result['message']);
            }
            
            redirect('/auth/login');
        } else {
            // Mostrar formulario de reenvío
            include '../app/views/auth/resend-activation.php';
        }
    }
    
    /**
     * Mostrar formulario de recuperación de contraseña
     */
    public function showForgotPassword() {
        if (Session::isLoggedIn()) {
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
            Session::setFlash('error', 'El email es requerido');
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
        Session::setFlash('success', 'Si el email existe, recibirás instrucciones para recuperar tu contraseña.');
        redirect('/auth/login');
    }
    
    /**
     * Mostrar perfil del usuario autenticado
     */
    public function showProfile() {
        // Verificar autenticación
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Debes iniciar sesión');
            redirect('/auth/login');
            return;
        }
        
        $user_id = Session::get('user_id');
        $user = $this->userModel->findById($user_id);
        
        if (!$user) {
            Session::setFlash('error', 'Usuario no encontrado');
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
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Debes iniciar sesión');
            redirect('/auth/login');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/profile');
            return;
        }
        
        $user_id = Session::get('user_id');
        
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
                Session::setFlash('error', 'Las contraseñas no coinciden');
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
            Session::set('user_name', $data['first_name'] . ' ' . $data['last_name']);
            Session::set('user_email', $data['email']);
            if (isset($data['photo_path'])) {
                Session::set('user_photo', $data['photo_path']);
            }
            
            Session::setFlash('success', 'Perfil actualizado exitosamente');
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        redirect('/auth/profile');
    }
    
    /**
     * Verificar que el usuario esté autenticado
     * @return bool
     */
    public function requireAuth() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Debes iniciar sesión para acceder a esta página');
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
        
        $user_type = Session::get('user_type');
        
        if (is_array($allowed_roles)) {
            if (!in_array($user_type, $allowed_roles)) {
                Session::setFlash('error', 'No tienes permiso para acceder a esta página');
                redirect('/dashboard');
                return false;
            }
        } else {
            if ($user_type !== $allowed_roles) {
                Session::setFlash('error', 'No tienes permiso para acceder a esta página');
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
}
