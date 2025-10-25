<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reenviar Activación - Carpooling UTN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .auth-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .auth-header {
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .auth-body {
            padding: 2rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5f3dc4, #9775fa);
            transform: translateY(-1px);
        }
        .form-control:focus {
            border-color: #6c5ce7;
            box-shadow: 0 0 0 0.2rem rgba(108, 92, 231, 0.25);
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            border-radius: 0 8px 8px 0;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="auth-container">
                    <div class="auth-header">
                        <h1 class="h3 mb-3">
                            <i class="fas fa-envelope me-2"></i>
                            Reenviar Activación
                        </h1>
                        <p class="mb-0 opacity-90">
                            Te enviaremos un nuevo enlace de activación
                        </p>
                    </div>
                    
                    <div class="auth-body">
                        <?php if (Session::hasFlash('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= Session::getFlash('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (Session::hasFlash('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= Session::getFlash('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-box">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                                <div>
                                    <h6 class="mb-1">¿No recibiste el email de activación?</h6>
                                    <small class="text-muted">
                                        Ingresa tu email y te enviaremos un nuevo enlace de activación.
                                        Revisa también tu carpeta de spam.
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST" action="<?= BASE_URL ?>/auth/resend-activation">
                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>
                                    Email
                                </label>
                                <input type="email" 
                                       class="form-control form-control-lg" 
                                       id="email" 
                                       name="email" 
                                       placeholder="tu-email@estudiante.utn.ac.cr"
                                       required>
                                <div class="form-text">
                                    Usa el mismo email con el que te registraste
                                </div>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Reenviar Email de Activación
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center">
                            <div class="border-top pt-3">
                                <small class="text-muted">
                                    ¿Ya tienes tu cuenta activa? 
                                    <a href="<?= BASE_URL ?>/auth/login" class="text-decoration-none">
                                        <i class="fas fa-sign-in-alt me-1"></i>
                                        Iniciar sesión
                                    </a>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-4">
                    <small class="text-white-50">
                        <i class="fas fa-university me-1"></i>
                        Universidad Técnica Nacional - Carpooling Estudiantil
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-focus en el campo email
        document.getElementById('email').focus();
        
        // Mostrar loading en el botón al enviar
        document.querySelector('form').addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
            btn.disabled = true;
        });
    </script>
</body>
</html>