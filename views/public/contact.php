<?php
$pageTitle = "Contacto - Carpooling UCR";
ob_start();
?>

<div class="container py-5">
    <div class="row">
        <!-- Contact Form -->
        <div class="col-lg-8 mb-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0">
                        <i class="bi bi-envelope me-2"></i>Envíanos un Mensaje
                    </h2>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">¿Tienes preguntas, sugerencias o necesitas ayuda? Completa el formulario y nos pondremos en contacto contigo lo antes posible.</p>
                    
                    <form id="contactForm" class="needs-validation" novalidate>
                        <?= Helpers::generateCSRFToken() ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="bi bi-person me-1"></i>Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="name" 
                                    name="name" 
                                    required
                                    maxlength="100"
                                >
                                <div class="invalid-feedback">
                                    Por favor ingresa tu nombre completo.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-1"></i>Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="email" 
                                    name="email" 
                                    required
                                >
                                <div class="invalid-feedback">
                                    Por favor ingresa un correo electrónico válido.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">
                                    <i class="bi bi-telephone me-1"></i>Teléfono
                                </label>
                                <input 
                                    type="tel" 
                                    class="form-control" 
                                    id="phone" 
                                    name="phone" 
                                    placeholder="8888-8888"
                                    pattern="[0-9]{4}-[0-9]{4}"
                                >
                                <div class="form-text">Formato: 8888-8888 (opcional)</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">
                                    <i class="bi bi-chat-dots me-1"></i>Asunto <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Selecciona un asunto</option>
                                    <option value="general">Consulta General</option>
                                    <option value="technical">Problema Técnico</option>
                                    <option value="account">Problemas con mi Cuenta</option>
                                    <option value="safety">Reporte de Seguridad</option>
                                    <option value="suggestion">Sugerencia</option>
                                    <option value="other">Otro</option>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor selecciona un asunto.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">
                                <i class="bi bi-chat-square-text me-1"></i>Mensaje <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control" 
                                id="message" 
                                name="message" 
                                rows="5" 
                                required
                                maxlength="1000"
                                placeholder="Describe tu consulta o problema con el mayor detalle posible..."
                            ></textarea>
                            <div class="form-text">Máximo 1000 caracteres</div>
                            <div class="invalid-feedback">
                                Por favor escribe tu mensaje.
                            </div>
                        </div>

                        <?php if (Session::isLoggedIn()): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Como usuario registrado, también puedes contactarnos directamente a través de tu panel de usuario.
                            </div>
                        <?php endif; ?>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="privacy" name="privacy" required>
                            <label class="form-check-label" for="privacy">
                                Acepto que mis datos sean procesados para responder a mi consulta según la 
                                <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">política de privacidad</a>
                            </label>
                            <div class="invalid-feedback">
                                Debes aceptar el procesamiento de datos.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send me-2"></i>Enviar Mensaje
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="col-lg-4">
            <!-- Contact Info Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Información de Contacto
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-envelope-fill text-primary me-3 fs-5"></i>
                            <div>
                                <strong>Email</strong><br>
                                <a href="mailto:carpooling@ucr.ac.cr">carpooling@ucr.ac.cr</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-telephone-fill text-primary me-3 fs-5"></i>
                            <div>
                                <strong>Teléfono</strong><br>
                                <a href="tel:+50625114000">(506) 2511-4000</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-start mb-2">
                            <i class="bi bi-geo-alt-fill text-primary me-3 fs-5"></i>
                            <div>
                                <strong>Ubicación</strong><br>
                                Universidad de Costa Rica<br>
                                Ciudad Universitaria Rodrigo Facio<br>
                                San José, Costa Rica
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-clock-fill text-primary me-3 fs-5"></i>
                            <div>
                                <strong>Horario de Atención</strong><br>
                                Lunes a Viernes<br>
                                8:00 AM - 5:00 PM
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-question-circle me-2"></i>Preguntas Frecuentes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion accordion-flush" id="faqAccordion">
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    ¿Cómo me registro?
                                </button>
                            </h6>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <small>Puedes registrarte usando tu correo UCR o cualquier correo válido. Solo necesitas completar el formulario de registro.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    ¿Es seguro compartir viajes?
                                </button>
                            </h6>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <small>Sí, todos los usuarios están verificados y recomendamos siempre informar a familiares sobre tus viajes.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    ¿Cuánto cuesta usar la plataforma?
                                </button>
                            </h6>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <small>La plataforma es completamente gratuita. Solo pagas tu parte del viaje acordada con el conductor.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Links -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-share me-2"></i>Síguenos
                    </h5>
                </div>
                <div class="card-body text-center">
                    <p class="small text-muted mb-3">Mantente al día con noticias y actualizaciones</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Policy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Política de Privacidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Recolección de Datos</h6>
                <p>Recolectamos únicamente los datos necesarios para responder a tu consulta y mejorar nuestro servicio.</p>
                
                <h6>Uso de la Información</h6>
                <p>Tus datos se utilizan exclusivamente para:</p>
                <ul>
                    <li>Responder a tu consulta</li>
                    <li>Mejorar nuestros servicios</li>
                    <li>Comunicaciones relacionadas con tu consulta</li>
                </ul>
                
                <h6>Protección de Datos</h6>
                <p>Implementamos medidas de seguridad para proteger tu información personal y nunca la compartimos con terceros sin tu consentimiento.</p>
                
                <h6>Derechos</h6>
                <p>Tienes derecho a acceder, rectificar o eliminar tus datos personales contactándonos directamente.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    // Phone formatting
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length >= 4) {
            value = value.slice(0, 4) + '-' + value.slice(4, 8);
        }
        this.value = value;
    });

    // Character counter for message
    const messageTextarea = document.getElementById('message');
    const messageContainer = messageTextarea.parentElement;
    
    const charCounter = document.createElement('div');
    charCounter.className = 'text-end text-muted small mt-1';
    charCounter.innerHTML = '<span id="charCount">0</span>/1000 caracteres';
    messageContainer.appendChild(charCounter);
    
    messageTextarea.addEventListener('input', function() {
        const currentLength = this.value.length;
        document.getElementById('charCount').textContent = currentLength;
        
        if (currentLength > 900) {
            charCounter.classList.remove('text-muted');
            charCounter.classList.add('text-warning');
        } else {
            charCounter.classList.remove('text-warning');
            charCounter.classList.add('text-muted');
        }
    });

    // Form submission
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Enviando...';

        // Prepare form data
        const formData = new FormData(this);

        // Send request
        fetch(`${BASE_URL}/contact`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show';
                successAlert.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>¡Mensaje enviado exitosamente!</strong> Te responderemos a la brevedad posible.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                this.insertBefore(successAlert, this.firstChild);
                this.reset();
                this.classList.remove('was-validated');
                
                // Scroll to success message
                successAlert.scrollIntoView({ behavior: 'smooth' });
                
            } else {
                throw new Error(data.message || 'Error al enviar el mensaje');
            }
        })
        .catch(error => {
            // Show error message
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show';
            errorAlert.innerHTML = `
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Error:</strong> ${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            this.insertBefore(errorAlert, this.firstChild);
        })
        .finally(() => {
            // Restore button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    // Bootstrap form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>