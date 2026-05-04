@extends('layouts.app')

@section('title', 'Recuperar Contraseña')

@section('content')
<style>
    .recover-container {
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

    .recover-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(25, 42, 86, 0.85) 0%, rgba(35, 58, 118, 0.75) 100%);
    }

    .recover-card {
        background: white;
        border-radius: 32px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        max-width: 500px;
        width: 100%;
        padding: 40px;
        position: relative;
        z-index: 1;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .recover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.3);
    }

    .recover-card::after {
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

    .btn-recover {
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

    .btn-recover::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-recover:hover::before {
        left: 100%;
    }

    .btn-recover:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(30,60,114,0.4);
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

    /* Alerta de seguridad */
    .alert-security {
        border-radius: 16px;
        border: none;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        margin-bottom: 24px;
        padding: 16px 20px;
    }

    .alert-danger-custom {
        border-radius: 16px;
        border: none;
        background: linear-gradient(135deg, #8b0000 0%, #b22222 100%);
        color: white;
        margin-bottom: 20px;
        padding: 12px 16px;
    }

    .alert-danger-custom .btn-close-white {
        filter: brightness(0) invert(1);
    }

    .alert-security .btn-close-white {
        filter: brightness(0) invert(1);
    }

    .icon-circle {
        transition: all 0.3s ease;
    }

    .icon-circle:hover {
        transform: scale(1.05);
        box-shadow: 0 0 15px rgba(30,60,114,0.2);
    }

    .text-link {
        color: #1e3c72;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .text-link:hover {
        color: #2a5298;
        text-decoration: underline;
    }
</style>

<div class="recover-container">
    <div class="recover-card">
        <!-- Icono -->
        <div class="text-center mb-4">
            <div class="d-inline-block mb-3">
                <div class="icon-circle rounded-circle p-3" style="background: rgba(30,60,114,0.1); display: inline-block;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="#1e3c72" class="bi bi-lock-fill" viewBox="0 0 16 16">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                    </svg>
                </div>
            </div>
            <h3 class="fw-bold" style="color: #1e3c72;">Recuperar Contraseña</h3>
            <p class="text-muted">Responde las preguntas de seguridad para restablecer tu contraseña</p>
        </div>

        @if(session('error'))
            <div class="alert-danger-custom alert-dismissible fade show rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert-security alert-dismissible fade show rounded-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-danger-custom alert-dismissible fade show rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ $errors->first() }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('password.reset-by-questions') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary">Cédula</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#6c757d" class="bi bi-person-badge" viewBox="0 0 16 16">
                            <path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                            <path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492V2.5z"/>
                        </svg>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0"
                           name="cedula" value="{{ old('cedula') }}"
                           placeholder="V-12345678" required>
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
                        <span> Seleccione una pregunta...</span>
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

            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary">Nueva Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#6c757d" class="bi bi-lock" viewBox="0 0 16 16">
                            <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                        </svg>
                    </span>
                    <input type="password" class="form-control border-start-0 ps-0"
                           name="password" placeholder="Mínimo 6 caracteres" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary">Confirmar Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#6c757d" class="bi bi-check-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                        </svg>
                    </span>
                    <input type="password" class="form-control border-start-0 ps-0"
                           name="password_confirmation" placeholder="Confirmar contraseña" required>
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-recover">
                    Cambiar Contraseña
                </button>
            </div>
        </form>

        <hr class="my-4">

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-link">
                ← Volver al inicio de sesión
            </a>
        </div>
    </div>
</div>

<script>
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

    // Inicializar selects cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        initCustomSelect('custom-select-1', 'hidden-pregunta-1');
        initCustomSelect('custom-select-2', 'hidden-pregunta-2');
    });
</script>
@endsection
