@extends('layouts.app')

@section('title', 'Registrarse')

@section('content')
<style>
    .register-container {
        min-height: 100vh;
        background-image: url('{{ asset("images/frieren.jpeg") }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .register-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(25, 42, 86, 0.85) 0%, rgba(35, 58, 118, 0.75) 100%);
    }

    .register-card {
        background: white;
        border-radius: 32px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        max-width: 550px;
        width: 100%;
        padding: 40px;
        position: relative;
        z-index: 1;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .register-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.3);
    }

    .register-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, transparent, #1e3c72, #2a5298, transparent);
        animation: shimmer 3s infinite;
        border-radius: 32px 32px 0 0;
    }

    @keyframes shimmer {
        0% { left: -100%; }
        100% { left: 100%; }
    }

    /* Contenedor de pasos */
    .steps-container {
        position: relative;
        margin-bottom: 40px;
    }

    .steps-line {
        position: absolute;
        top: 22px;
        left: 50%;
        width: calc(100% - 80px);
        transform: translateX(-50%);
        height: 3px;
        background: #e9ecef;
        z-index: 0;
        border-radius: 3px;
    }

    .steps-line-fill {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 0%;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        transition: width 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        border-radius: 3px;
    }

    .step-item {
        position: relative;
        z-index: 1;
        text-align: center;
        flex: 1;
    }

    .step-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        margin: 0 auto;
        position: relative;
        background: white;
        border: 3px solid #e9ecef;
    }

    .step-active .step-circle {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        border-color: #1e3c72;
        transform: scale(1.1);
        box-shadow: 0 0 0 5px rgba(30,60,114,0.2);
    }

    .step-completed .step-circle {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        border-color: #1e3c72;
        animation: bounce 0.5s ease;
    }

    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }

    .step-inactive .step-circle {
        background: white;
        color: #6c757d;
        border-color: #e9ecef;
    }

    .step-label {
        margin-top: 10px;
        font-size: 12px;
        transition: all 0.3s ease;
    }

    .step-active .step-label {
        color: #1e3c72;
        font-weight: bold;
        transform: translateY(2px);
    }

    /* Animaciones de contenido */
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(30px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes slideInLeft {
        from { opacity: 0; transform: translateX(-30px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .slide-right { animation: slideInRight 0.4s ease-out forwards; }
    .slide-left { animation: slideInLeft 0.4s ease-out forwards; }

    /* Partículas */
    @keyframes particleFly {
        0% { transform: translate(0, 0) scale(1); opacity: 1; }
        100% { transform: translate(var(--tx), var(--ty)) scale(0); opacity: 0; }
    }

    .particle-fly {
        position: fixed;
        width: 8px;
        height: 8px;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 1000;
        animation: particleFly 0.5s ease-out forwards;
    }

    /* Formulario */
    .form-control {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 12px 16px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #1e3c72;
        box-shadow: 0 0 0 0.2rem rgba(30,60,114,0.25);
        transform: translateX(5px);
    }

    .form-select {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 12px 16px;
        transition: all 0.3s ease;
    }

    .form-select:focus {
        border-color: #1e3c72;
        box-shadow: 0 0 0 0.2rem rgba(30,60,114,0.25);
    }

    /* Select personalizado */
    .custom-select {
        position: relative;
        width: 100%;
        cursor: pointer;
        margin-bottom: 20px;
    }

    .custom-select-trigger {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 12px 16px;
        background: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }

    .custom-select-trigger:hover {
        border-color: #2a5298;
        background-color: #f8f9fa;
    }

    .custom-select-trigger.open {
        border-color: #1e3c72;
        box-shadow: 0 0 0 3px rgba(30,60,114,0.2);
    }

    .custom-select-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        margin-top: 8px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 100;
        display: none;
    }

    .custom-select-options.show {
        display: block;
        animation: fadeIn 0.2s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .custom-select-option {
        padding: 12px 16px;
        transition: all 0.2s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #4a5568;
    }

    .custom-select-option:hover {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
    }

    .custom-select-option.selected {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
    }

    .custom-select-option.disabled {
        color: #adb5bd;
        font-style: italic;
        cursor: default;
    }

    .custom-select-option.disabled:hover {
        background: none;
        color: #adb5bd;
    }

    .arrow {
        transition: transform 0.3s ease;
    }

    .arrow.rotated {
        transform: rotate(180deg);
    }

    .btn-register {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border: none;
        border-radius: 12px;
        padding: 14px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-register::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-register:hover::before {
        left: 100%;
    }

    .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(30,60,114,0.4);
    }

    .btn-outline-secondary {
        border-radius: 12px;
        padding: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        border-color: #1e3c72;
        color: #1e3c72;
        background: transparent;
    }

    .btn-outline-secondary:hover {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        border-color: transparent;
        transform: translateY(-2px);
    }

    .alert-security {
        border-radius: 16px;
        border: none;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        margin-bottom: 24px;
        padding: 16px 20px;
    }

    .icon-circle {
        transition: all 0.3s ease;
    }

    .icon-circle:hover {
        transform: scale(1.05);
        box-shadow: 0 0 15px rgba(30,60,114,0.2);
    }
</style>

<div class="register-container">
    <div class="register-card">
        <!-- Logo/Icono -->
        <div class="text-center mb-4">
            <div class="d-inline-block mb-3">
                <div class="icon-circle rounded-circle p-3" style="background: rgba(30,60,114,0.1); display: inline-block;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="#1e3c72" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
                        <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                        <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/>
                    </svg>
                </div>
            </div>
            <h3 class="fw-bold" style="color: #1e3c72;">Crear Cuenta</h3>
            <p class="text-muted">Regístrate para solicitar préstamos de equipos</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Indicador de pasos -->
        <div class="steps-container">
            <div class="steps-line">
                <div class="steps-line-fill" id="progressLine"></div>
            </div>
            <div class="d-flex justify-content-between">
                <div class="step-item step-active" id="step1-item">
                    <div class="step-circle" id="step1-indicator">1</div>
                    <div class="step-label" id="step1-label">Datos básicos</div>
                </div>
                <div class="step-item step-inactive" id="step2-item">
                    <div class="step-circle" id="step2-indicator">2</div>
                    <div class="step-label" id="step2-label">Seguridad</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf

            <!-- Paso 1 -->
            <div id="step1">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-secondary">Nombre</label>
                        <input type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-secondary">Apellido</label>
                        <input type="text" class="form-control" name="apellido" value="{{ old('apellido') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Cédula</label>
                    <input type="text" class="form-control" name="cedula" value="{{ old('cedula') }}" placeholder="V-12345678" required>
                    <small class="text-muted">Tu cédula será tu usuario para iniciar sesión</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Departamento</label>
                    <input type="text" class="form-control" name="departamento" value="{{ old('departamento') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Cargo</label>
                    <input type="text" class="form-control" name="cargo" value="{{ old('cargo') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Contraseña</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Confirmar Contraseña</label>
                    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
                </div>

                <div class="d-grid mt-4">
                    <button type="button" class="btn btn-register" id="nextBtn">
                        Siguiente: Preguntas de seguridad →
                    </button>
                </div>
            </div>

            <!-- Paso 2 -->
            <div id="step2" style="display: none;">
                <div class="alert-security">
                    <div class="d-flex">
                        <div class="flex-grow-1 ms-2">
                            <strong>Preguntas de seguridad</strong><br>
                            Estas preguntas te ayudarán a recuperar tu contraseña si la olvidas.
                        </div>
                    </div>
                </div>

                <!-- Select personalizado Pregunta 1 -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Pregunta 1</label>
                    <div class="custom-select" id="custom-select-1">
                        <div class="custom-select-trigger">
                            <span> Seleccione una pregunta...</span>
                            <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                        <div class="custom-select-options">
                            <div class="custom-select-option disabled" data-value=""> Seleccione una pregunta...</div>
                            <div class="custom-select-option" data-value="¿Nombre de tu primera mascota?"> ¿Nombre de tu primera mascota?</div>
                            <div class="custom-select-option" data-value="¿Nombre de tu madre soltera?"> ¿Nombre de tu madre soltera?</div>
                            <div class="custom-select-option" data-value="¿Modelo de tu primer auto?"> ¿Modelo de tu primer auto?</div>
                            <div class="custom-select-option" data-value="¿Ciudad donde naciste?"> ¿Ciudad donde naciste?</div>
                            <div class="custom-select-option" data-value="¿Nombre de tu mejor amigo de la infancia?"> ¿Nombre de tu mejor amigo de la infancia?</div>
                            <div class="custom-select-option" data-value="¿Tu comida favorita?"> ¿Tu comida favorita?</div>
                            <div class="custom-select-option" data-value="¿Nombre de tu primer profesor?"> ¿Nombre de tu primer profesor?</div>
                            <div class="custom-select-option" data-value="¿Color favorito?"> ¿Color favorito?</div>
                            <div class="custom-select-option" data-value="¿Marca de tu primer celular?"> ¿Marca de tu primer celular?</div>
                        </div>
                    </div>
                    <input type="hidden" name="pregunta_seguridad_1" id="hidden-pregunta-1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Respuesta 1</label>
                    <input type="text" class="form-control" name="respuesta_1" placeholder="Escribe tu respuesta" required>
                    <small class="text-muted">No distingue mayúsculas/minúsculas</small>
                </div>

                <!-- Select personalizado Pregunta 2 -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Pregunta 2</label>
                    <div class="custom-select" id="custom-select-2">
                        <div class="custom-select-trigger">
                            <span>Seleccione una pregunta...</span>
                            <svg class="arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                        <div class="custom-select-options">
                            <div class="custom-select-option disabled" data-value=""> Seleccione una pregunta...</div>
                            <div class="custom-select-option" data-value="¿Tu comida favorita?"> ¿Tu comida favorita?</div>
                            <div class="custom-select-option" data-value="¿Nombre de tu primer profesor?"> ¿Nombre de tu primer profesor?</div>
                            <div class="custom-select-option" data-value="¿Color favorito?"> ¿Color favorito?</div>
                            <div class="custom-select-option" data-value="¿Marca de tu primer celular?"> ¿Marca de tu primer celular?</div>
                            <div class="custom-select-option" data-value="¿Nombre de tu héroe favorito?"> ¿Nombre de tu héroe favorito?</div>
                            <div class="custom-select-option" data-value="¿Nombre de tu primera mascota?"> ¿Nombre de tu primera mascota?</div>
                            <div class="custom-select-option" data-value="¿Nombre de tu madre soltera?"> ¿Nombre de tu madre soltera?</div>
                            <div class="custom-select-option" data-value="¿Modelo de tu primer auto?"> ¿Modelo de tu primer auto?</div>
                            <div class="custom-select-option" data-value="¿Ciudad donde naciste?"> ¿Ciudad donde naciste?</div>
                        </div>
                    </div>
                    <input type="hidden" name="pregunta_seguridad_2" id="hidden-pregunta-2" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Respuesta 2</label>
                    <input type="text" class="form-control" name="respuesta_2" placeholder="Escribe tu respuesta" required>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary flex-grow-1" id="prevBtn">← Volver</button>
                    <button type="submit" class="btn btn-register flex-grow-1">Registrarse</button>
                </div>
            </div>
        </form>

        <div class="text-center mt-4">
            <p class="mb-0">¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="text-decoration-none fw-bold" style="color: #1e3c72;">Inicia sesión</a>
            </p>
        </div>
    </div>
</div>

<script>
    // Elementos de pasos
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step1Item = document.getElementById('step1-item');
    const step2Item = document.getElementById('step2-item');
    const step1Indicator = document.getElementById('step1-indicator');
    const step2Indicator = document.getElementById('step2-indicator');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const progressLine = document.getElementById('progressLine');

    // Crear partículas
    function createParticles(fromElement, toElement) {
        const fromRect = fromElement.getBoundingClientRect();
        const toRect = toElement.getBoundingClientRect();
        const startX = fromRect.left + fromRect.width / 2;
        const startY = fromRect.top + fromRect.height / 2;
        const endX = toRect.left + toRect.width / 2;
        const endY = toRect.top + toRect.height / 2;
        const deltaX = endX - startX;
        const deltaY = endY - startY;

        for (let i = 0; i < 15; i++) {
            setTimeout(() => {
                const particle = document.createElement('div');
                particle.className = 'particle-fly';
                const randomOffsetX = (Math.random() - 0.5) * 30;
                const randomOffsetY = (Math.random() - 0.5) * 30;
                particle.style.setProperty('--tx', (deltaX + randomOffsetX) + 'px');
                particle.style.setProperty('--ty', (deltaY + randomOffsetY) + 'px');
                particle.style.left = startX + 'px';
                particle.style.top = startY + 'px';
                document.body.appendChild(particle);
                setTimeout(() => particle.remove(), 500);
            }, i * 30);
        }
    }

    // Función para inicializar selects personalizados
    function initCustomSelect(containerId, hiddenInputId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const trigger = container.querySelector('.custom-select-trigger');
        const optionsContainer = container.querySelector('.custom-select-options');
        const arrow = trigger.querySelector('.arrow');
        const hiddenInput = document.getElementById(hiddenInputId);
        const triggerText = trigger.querySelector('span');

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = optionsContainer.classList.contains('show');

            document.querySelectorAll('.custom-select-options').forEach(el => {
                el.classList.remove('show');
            });
            document.querySelectorAll('.custom-select-trigger').forEach(el => {
                el.classList.remove('open');
            });
            document.querySelectorAll('.arrow').forEach(el => {
                el.classList.remove('rotated');
            });

            if (!isOpen) {
                optionsContainer.classList.add('show');
                trigger.classList.add('open');
                arrow.classList.add('rotated');
            }
        });

        const options = container.querySelectorAll('.custom-select-option');
        options.forEach(option => {
            option.addEventListener('click', function() {
                if (this.classList.contains('disabled')) return;

                const value = this.getAttribute('data-value');
                const text = this.textContent;

                triggerText.textContent = text;
                hiddenInput.value = value;

                options.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');

                optionsContainer.classList.remove('show');
                trigger.classList.remove('open');
                arrow.classList.remove('rotated');
            });
        });
    }

    // Cerrar selects al hacer clic fuera
    document.addEventListener('click', function() {
        document.querySelectorAll('.custom-select-options').forEach(el => {
            el.classList.remove('show');
        });
        document.querySelectorAll('.custom-select-trigger').forEach(el => {
            el.classList.remove('open');
        });
        document.querySelectorAll('.arrow').forEach(el => {
            el.classList.remove('rotated');
        });
    });

    // Validar paso 1
    function validateStep1() {
        const nombre = document.querySelector('input[name="nombre"]').value.trim();
        const apellido = document.querySelector('input[name="apellido"]').value.trim();
        const cedula = document.querySelector('input[name="cedula"]').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirmation').value;

        if (!nombre) { alert('Ingresa tu nombre'); return false; }
        if (!apellido) { alert('Ingresa tu apellido'); return false; }
        if (!cedula) { alert('Ingresa tu cédula'); return false; }
        if (!password) { alert('Ingresa una contraseña'); return false; }
        if (password.length < 6) { alert('La contraseña debe tener al menos 6 caracteres'); return false; }
        if (password !== passwordConfirm) { alert('Las contraseñas no coinciden'); return false; }
        return true;
    }

    // Ir al paso 2
    nextBtn.addEventListener('click', function() {
        if (validateStep1()) {
            createParticles(step1Indicator, step2Indicator);
            progressLine.style.width = '100%';
            step1Item.className = 'step-item step-completed';
            step2Item.className = 'step-item step-active';
            step1Indicator.innerHTML = '✓';
            step1.style.display = 'none';
            step2.style.display = 'block';
            step2.classList.add('slide-right');
            setTimeout(() => step2.classList.remove('slide-right'), 400);
        }
    });

    // Volver al paso 1
    prevBtn.addEventListener('click', function() {
        createParticles(step2Indicator, step1Indicator);
        progressLine.style.width = '0%';
        step2Item.className = 'step-item step-inactive';
        step1Item.className = 'step-item step-active';
        step1Indicator.innerHTML = '1';
        step2.style.display = 'none';
        step1.style.display = 'block';
        step1.classList.add('slide-left');
        setTimeout(() => step1.classList.remove('slide-left'), 400);
    });

    // Inicializar selects cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        initCustomSelect('custom-select-1', 'hidden-pregunta-1');
        initCustomSelect('custom-select-2', 'hidden-pregunta-2');
    });
</script>
@endsection
