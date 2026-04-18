@extends('layouts.app')

@section('title', 'Registrarse')

@section('content')
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
                    <p class="mb-0 mt-2 small">Regístrate para solicitar préstamos de equipos</p>
                </div>
                <div class="card-body p-5">

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

                    <!-- INDICADOR DE PASOS -->
                    <div class="d-flex justify-content-between mb-4">
                        <div class="text-center flex-grow-1">
                            <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px; font-weight: bold;" id="step1-indicator">1</div>
                            <div class="small mt-2 fw-bold" id="step1-label">Datos básicos</div>
                        </div>
                        <div class="text-center flex-grow-1">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px; font-weight: bold;" id="step2-indicator">2</div>
                            <div class="small mt-2 text-muted" id="step2-label">Seguridad</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('register') }}" id="registerForm">
                        @csrf

                        <!-- PASO 1: DATOS BÁSICOS -->
                        <div id="step1">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nombre</label>
                                    <input type="text" class="form-control form-control-lg"
                                           name="nombre" value="{{ old('nombre') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Apellido</label>
                                    <input type="text" class="form-control form-control-lg"
                                           name="apellido" value="{{ old('apellido') }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Cédula</label>
                                <input type="text" class="form-control form-control-lg"
                                       name="cedula" value="{{ old('cedula') }}"
                                       placeholder="V-12345678" required>
                                <small class="text-muted">Tu cédula será tu usuario para iniciar sesión</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Departamento</label>
                                <input type="text" class="form-control form-control-lg"
                                       name="departamento" value="{{ old('departamento') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Cargo</label>
                                <input type="text" class="form-control form-control-lg"
                                       name="cargo" value="{{ old('cargo') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Contraseña</label>
                                <input type="password" class="form-control form-control-lg"
                                       name="password" id="password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Confirmar Contraseña</label>
                                <input type="password" class="form-control form-control-lg"
                                       name="password_confirmation" id="password_confirmation" required>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="button" class="btn btn-primary btn-lg py-3 rounded-3 fw-bold" id="nextBtn">
                                    Siguiente: Preguntas de seguridad →
                                </button>
                            </div>
                        </div>

                        <!-- PASO 2: PREGUNTAS DE SEGURIDAD (oculto al inicio) -->
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

                            <div class="mb-3">
                                <label class="form-label fw-bold">Pregunta 1</label>
                                <select class="form-select form-select-lg" name="pregunta_seguridad_1" required>
                                    <option value="">Seleccione una pregunta...</option>
                                    <option value="¿Nombre de tu primera mascota?">🐕 ¿Nombre de tu primera mascota?</option>
                                    <option value="¿Nombre de tu madre soltera?">👩 ¿Nombre de tu madre soltera?</option>
                                    <option value="¿Modelo de tu primer auto?">🚗 ¿Modelo de tu primer auto?</option>
                                    <option value="¿Ciudad donde naciste?">🏙️ ¿Ciudad donde naciste?</option>
                                    <option value="¿Nombre de tu mejor amigo de la infancia?">👫 ¿Nombre de tu mejor amigo de la infancia?</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Respuesta 1</label>
                                <input type="text" class="form-control form-control-lg"
                                       name="respuesta_1" required>
                                <small class="text-muted">No distingue mayúsculas/minúsculas</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Pregunta 2</label>
                                <select class="form-select form-select-lg" name="pregunta_seguridad_2" required>
                                    <option value="">Seleccione una pregunta...</option>
                                    <option value="¿Tu comida favorita?">🍕 ¿Tu comida favorita?</option>
                                    <option value="¿Nombre de tu primer profesor?">👨‍🏫 ¿Nombre de tu primer profesor?</option>
                                    <option value="¿Color favorito?">🎨 ¿Color favorito?</option>
                                    <option value="¿Marca de tu primer celular?">📱 ¿Marca de tu primer celular?</option>
                                    <option value="¿Nombre de tu héroe favorito?">🦸 ¿Nombre de tu héroe favorito?</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Respuesta 2</label>
                                <input type="text" class="form-control form-control-lg"
                                       name="respuesta_2" required>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg py-3 rounded-3 flex-grow-1" id="prevBtn">
                                    ← Volver
                                </button>
                                <button type="submit" class="btn btn-success btn-lg py-3 rounded-3 flex-grow-1 fw-bold">
                                    🚀 Registrarse
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">¿Ya tienes cuenta?
                            <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Inicia sesión</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Elementos
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step1Indicator = document.getElementById('step1-indicator');
    const step2Indicator = document.getElementById('step2-indicator');
    const step1Label = document.getElementById('step1-label');
    const step2Label = document.getElementById('step2-label');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');

    // Función para validar el paso 1
    function validateStep1() {
        const nombre = document.querySelector('input[name="nombre"]').value.trim();
        const apellido = document.querySelector('input[name="apellido"]').value.trim();
        const cedula = document.querySelector('input[name="cedula"]').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirmation').value;

        if (!nombre) {
            alert('Por favor, ingresa tu nombre.');
            return false;
        }
        if (!apellido) {
            alert('Por favor, ingresa tu apellido.');
            return false;
        }
        if (!cedula) {
            alert('Por favor, ingresa tu cédula.');
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

            // Actualizar indicadores
            step1Indicator.classList.remove('bg-success');
            step1Indicator.classList.add('bg-secondary');
            step1Label.classList.remove('fw-bold');
            step1Label.classList.add('text-muted');

            step2Indicator.classList.remove('bg-secondary');
            step2Indicator.classList.add('bg-success');
            step2Label.classList.remove('text-muted');
            step2Label.classList.add('fw-bold');
        }
    });

    // Volver al paso 1
    prevBtn.addEventListener('click', function() {
        step2.style.display = 'none';
        step1.style.display = 'block';

        // Actualizar indicadores
        step2Indicator.classList.remove('bg-success');
        step2Indicator.classList.add('bg-secondary');
        step2Label.classList.remove('fw-bold');
        step2Label.classList.add('text-muted');

        step1Indicator.classList.remove('bg-secondary');
        step1Indicator.classList.add('bg-success');
        step1Label.classList.remove('text-muted');
        step1Label.classList.add('fw-bold');
    });
</script>

<style>
    .bg-gradient {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
</style>
@endsection
