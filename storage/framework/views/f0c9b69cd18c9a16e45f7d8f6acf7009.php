<?php $__env->startSection('title', 'Registrarse'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-gradient bg-success text-white text-center rounded-top-4 py-4">
                    <div class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
                            <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                            <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/>
                        </svg>
                    </div>
                    <h3 class="mb-0">Crear Cuenta</h3>
                    <p class="mb-0 mt-2 small opacity-75">Únete a nuestra comunidad</p>
                </div>
                <div class="card-body p-5">

                    
                    <div class="d-flex justify-content-between mb-5">
                        <div class="text-center flex-grow-1">
                            <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px;" id="step1-indicator">1</div>
                            <div class="small mt-2">Datos básicos</div>
                        </div>
                        <div class="text-center flex-grow-1">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px;" id="step2-indicator">2</div>
                            <div class="small mt-2">Seguridad</div>
                        </div>
                    </div>

                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                            <div class="d-flex">
                                <div class="flex-shrink-0">⚠️</div>
                                <div class="flex-grow-1 ms-2"><?php echo e($errors->first()); ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('register')); ?>" id="registerForm">
                        <?php echo csrf_field(); ?>

                        
                        <div id="step1">
                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Z"/>
                                        </svg>
                                    </span>
                                    <input type="text" class="form-control form-control-lg bg-transparent border-start-0 ps-0"
                                           id="name" name="name" value="<?php echo e(old('name')); ?>"
                                           placeholder="Nombre completo" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                                        </svg>
                                    </span>
                                    <input type="email" class="form-control form-control-lg bg-transparent border-start-0 ps-0"
                                           id="email" name="email" value="<?php echo e(old('email')); ?>"
                                           placeholder="Correo electrónico" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
                                            <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                                        </svg>
                                    </span>
                                    <input type="password" class="form-control form-control-lg bg-transparent border-start-0 ps-0"
                                           id="password" name="password" placeholder="Contraseña" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                                            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.06.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                        </svg>
                                    </span>
                                    <input type="password" class="form-control form-control-lg bg-transparent border-start-0 ps-0"
                                           id="password_confirmation" name="password_confirmation"
                                           placeholder="Confirmar contraseña" required>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="button" class="btn btn-success btn-lg py-3 rounded-3 fw-bold" id="nextBtn">
                                    Siguiente: Preguntas de seguridad →
                                </button>
                            </div>
                        </div>

                        
                        <div id="step2" style="display: none;">
                            <div class="alert alert-info mb-4">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">🔐</div>
                                    <div class="flex-grow-1 ms-2">
                                        <strong>Preguntas de seguridad</strong><br>
                                        Estas preguntas te ayudarán a recuperar tu contraseña si la olvidas.
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">📌 Pregunta 1</label>
                                <select class="form-select form-select-lg" id="security_question_1" name="security_question_1" required>
                                    <option value="">Selecciona una pregunta...</option>
                                    <option value="¿Nombre de tu primera mascota?">🐕 ¿Nombre de tu primera mascota?</option>
                                    <option value="¿Nombre de tu madre soltera?">👩 ¿Nombre de tu madre soltera?</option>
                                    <option value="¿Modelo de tu primer auto?">🚗 ¿Modelo de tu primer auto?</option>
                                    <option value="¿Ciudad donde naciste?">🏙️ ¿Ciudad donde naciste?</option>
                                    <option value="¿Nombre de tu mejor amigo de la infancia?">👫 ¿Nombre de tu mejor amigo de la infancia?</option>
                                    <option value="¿Comida favorita?">🍕 ¿Comida favorita?</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">✏️ Respuesta 1</label>
                                <input type="text" class="form-control form-control-lg"
                                       id="security_answer_1" name="security_answer_1"
                                       placeholder="Tu respuesta (no distingue mayúsculas/minúsculas)"
                                       required>
                                <div class="form-text">La respuesta no distingue entre mayúsculas y minúsculas.</div>
                            </div>

                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">📌 Pregunta 2</label>
                                <select class="form-select form-select-lg" id="security_question_2" name="security_question_2" required>
                                    <option value="">Selecciona una pregunta...</option>
                                    <option value="¿Nombre de tu primera mascota?">🐕 ¿Nombre de tu primera mascota?</option>
                                    <option value="¿Nombre de tu madre soltera?">👩 ¿Nombre de tu madre soltera?</option>
                                    <option value="¿Modelo de tu primer auto?">🚗 ¿Modelo de tu primer auto?</option>
                                    <option value="¿Ciudad donde naciste?">🏙️ ¿Ciudad donde naciste?</option>
                                    <option value="¿Nombre de tu mejor amigo de la infancia?">👫 ¿Nombre de tu mejor amigo de la infancia?</option>
                                    <option value="¿Comida favorita?">🍕 ¿Comida favorita?</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">✏️ Respuesta 2</label>
                                <input type="text" class="form-control form-control-lg"
                                       id="security_answer_2" name="security_answer_2"
                                       placeholder="Tu respuesta (no distingue mayúsculas/minúsculas)"
                                       required>
                                <div class="form-text">La respuesta no distingue entre mayúsculas y minúsculas.</div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg py-3" id="prevBtn">
                                    ← Volver atrás
                                </button>
                                <button type="submit" class="btn btn-success btn-lg py-3 rounded-3 fw-bold">
                                    🚀 Completar registro
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted">¿Ya tienes cuenta?
                            <a href="<?php echo e(route('login')); ?>" class="text-decoration-none fw-bold">Inicia sesión</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step1Indicator = document.getElementById('step1-indicator');
    const step2Indicator = document.getElementById('step2-indicator');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');

    // Validar paso 1 antes de continuar
    function validateStep1() {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirmation').value;

        if (!name) {
            alert('Por favor, ingresa tu nombre completo.');
            return false;
        }
        if (!email) {
            alert('Por favor, ingresa tu correo electrónico.');
            return false;
        }
        if (!password) {
            alert('Por favor, ingresa una contraseña.');
            return false;
        }
        if (password.length < 6) {
            alert('La contraseña debe tener al menos 6 caracteres.');
            return false;
        }
        if (password !== passwordConfirm) {
            alert('Las contraseñas no coinciden.');
            return false;
        }
        return true;
    }

    // Ir al paso 2
    nextBtn.addEventListener('click', function() {
        if (validateStep1()) {
            step1.style.display = 'none';
            step2.style.display = 'block';
            step1Indicator.classList.remove('bg-success');
            step1Indicator.classList.add('bg-secondary');
            step2Indicator.classList.remove('bg-secondary');
            step2Indicator.classList.add('bg-success');
        }
    });

    // Volver al paso 1
    prevBtn.addEventListener('click', function() {
        step2.style.display = 'none';
        step1.style.display = 'block';
        step2Indicator.classList.remove('bg-success');
        step2Indicator.classList.add('bg-secondary');
        step1Indicator.classList.remove('bg-secondary');
        step1Indicator.classList.add('bg-success');
    });
</script>

<style>
    .bg-gradient {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    .input-group-text {
        border: 1px solid #dee2e6;
        border-right: none;
    }
    .form-control.bg-transparent {
        background-color: transparent !important;
    }
    .form-control:focus {
        box-shadow: none;
        border-color: #28a745;
    }
    .input-group:focus-within .input-group-text {
        border-color: #28a745;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/jordanys/Escritorio/proyecto x/back-laravel/resources/views/auth/register.blade.php ENDPATH**/ ?>