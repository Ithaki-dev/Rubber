<?php
$pageTitle = "Registrarse - Carpooling UTN";
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus-fill text-primary fs-1"></i>
                        <h2 class="mt-2">Crear Cuenta</h2>
                        <p class="text-muted">Únete a la comunidad de Carpooling UTN</p>
                    </div>

                    <form action="<?= BASE_URL ?>/auth/register" method="POST" class="needs-validation" novalidate>
                        <?php
                        // Generar token CSRF
                        if (!isset($_SESSION['csrf_token'])) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
                        ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="bi bi-person me-1"></i>Nombre
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="first_name" 
                                    name="first_name" 
                                    value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                                    required
                                    maxlength="50"
                                >
                                <div class="invalid-feedback">
                                    Por favor ingresa tu nombre.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="bi bi-person me-1"></i>Apellido
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="last_name" 
                                    name="last_name" 
                                    value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                                    required
                                    maxlength="50"
                                >
                                <div class="invalid-feedback">
                                    Por favor ingresa tu apellido.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>Correo Electrónico
                            </label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                required
                            >
                            <div class="form-text">Preferiblemente usa tu correo institucional (@utn.ac.cr)</div>
                            <div class="invalid-feedback">
                                Por favor ingresa un correo válido.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone me-1"></i>Teléfono
                            </label>
                            <input 
                                type="tel" 
                                class="form-control" 
                                id="phone" 
                                name="phone" 
                                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                placeholder="8888-8888"
                                pattern="[0-9]{4}-[0-9]{4}"
                                required
                            >
                            <div class="form-text">Formato: 8888-8888</div>
                            <div class="invalid-feedback">
                                Por favor ingresa un teléfono válido (8888-8888).
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="student_id" class="form-label">
                                <i class="bi bi-card-text me-1"></i>Carné Estudiantil (Opcional)
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="student_id" 
                                name="student_id" 
                                value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>"
                                placeholder="B12345"
                                maxlength="10"
                            >
                            <div class="form-text">Si eres estudiante de la UTN</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-1"></i>Contraseña
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password" 
                                    required
                                    minlength="8"
                                >
                                <button 
                                    class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="togglePassword"
                                >
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Mínimo 8 caracteres</div>
                            <div class="invalid-feedback">
                                La contraseña debe tener al menos 8 caracteres.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">
                                <i class="bi bi-lock-fill me-1"></i>Confirmar Contraseña
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password_confirm" 
                                    name="password_confirm" 
                                    required
                                >
                                <button 
                                    class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="togglePasswordConfirm"
                                >
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Las contraseñas no coinciden.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo de Usuario</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="radio" 
                                            name="role" 
                                            id="passenger" 
                                            value="passenger" 
                                            <?= ($_POST['role'] ?? '') === 'passenger' ? 'checked' : '' ?>
                                            required
                                        >
                                        <label class="form-check-label" for="passenger">
                                            <i class="bi bi-person me-1"></i>Pasajero
                                            <small class="d-block text-muted">Solo busco viajes</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="radio" 
                                            name="role" 
                                            id="driver" 
                                            value="driver"
                                            <?= ($_POST['role'] ?? '') === 'driver' ? 'checked' : '' ?>
                                        >
                                        <label class="form-check-label" for="driver">
                                            <i class="bi bi-car-front me-1"></i>Conductor
                                            <small class="d-block text-muted">Ofrezco viajes</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="invalid-feedback">
                                Por favor selecciona un tipo de usuario.
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                id="terms" 
                                name="terms" 
                                required
                            >
                            <label class="form-check-label" for="terms">
                                Acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">términos y condiciones</a>
                            </label>
                            <div class="invalid-feedback">
                                Debes aceptar los términos y condiciones.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-person-plus me-2"></i>Crear Cuenta
                        </button>
                    </form>

                    <hr>
                    
                    <div class="text-center">
                        <p class="mb-0">¿Ya tienes cuenta?</p>
                        <a href="<?= BASE_URL ?>/auth/login" class="btn btn-outline-primary w-100 mt-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Términos y Condiciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Uso del Servicio</h6>
                <p>Este servicio está destinado exclusivamente para la comunidad universitaria de la UTN para compartir viajes de manera segura.</p>
                
                <h6>2. Responsabilidades</h6>
                <p>Cada usuario es responsable de verificar la identidad y confiabilidad de otros usuarios antes de compartir un viaje.</p>
                
                <h6>3. Seguridad</h6>
                <p>Se recomienda siempre informar a familiares o amigos sobre los detalles del viaje y utilizar el sentido común.</p>
                
                <h6>4. Privacidad</h6>
                <p>Tus datos personales serán protegidos y solo serán compartidos con otros usuarios para facilitar la comunicación de viajes.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    function setupPasswordToggle(toggleId, passwordId) {
        const toggle = document.getElementById(toggleId);
        const password = document.getElementById(passwordId);
        
        toggle.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    }
    
    setupPasswordToggle('togglePassword', 'password');
    setupPasswordToggle('togglePasswordConfirm', 'password_confirm');

    // Password confirmation validation
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');
    
    function validatePasswordMatch() {
        if (password.value !== passwordConfirm.value) {
            passwordConfirm.setCustomValidity('Las contraseñas no coinciden');
        } else {
            passwordConfirm.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePasswordMatch);
    passwordConfirm.addEventListener('keyup', validatePasswordMatch);

    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length >= 4) {
            value = value.slice(0, 4) + '-' + value.slice(4, 8);
        }
        this.value = value;
    });

    // Bootstrap form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>