@extends('layouts.app')

@section('title', 'Recuperar Contraseña')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-gradient bg-warning text-dark text-center rounded-top-4 py-4">
                    <div class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-shield-lock-fill" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.777 11.777 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7.159 7.159 0 0 0 1.048-.625 11.775 11.775 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.541 1.541 0 0 0-1.044-1.263 62.467 62.467 0 0 0-2.887-.87C9.843.266 8.69 0 8 0z"/>
                        </svg>
                    </div>
                    <h3 class="mb-0">🔐 Recuperar Contraseña</h3>
                    <p class="mb-0 mt-2 small opacity-75">Responde tus preguntas de seguridad</p>
                </div>
                <div class="card-body p-5">

                    {{-- Indicador de pasos --}}
                    <div class="d-flex justify-content-between mb-5">
                        <div class="text-center flex-grow-1">
                            <div class="rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px;" id="step1-indicator">1</div>
                            <div class="small mt-2">Verificar email</div>
                        </div>
                        <div class="text-center flex-grow-1">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px;" id="step2-indicator">2</div>
                            <div class="small mt-2">Preguntas seguridad</div>
                        </div>
                        <div class="text-center flex-grow-1">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px;" id="step3-indicator">3</div>
                            <div class="small mt-2">Nueva contraseña</div>
                        </div>
                    </div>

                    {{-- Mensajes de error --}}
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                            <div class="d-flex">
                                <div class="flex-shrink-0">⚠️</div>
                                <div class="flex-grow-1 ms-2">{{ session('error') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- PASO 1: Ingresar email --}}
                    <div id="step1">
                        <form method="POST" action="{{ route('password.verify-email') }}" id="emailForm">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold">📧 Correo electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                                        </svg>
                                    </span>
                                    <input type="email" class="form-control form-control-lg bg-transparent border-start-0 ps-0"
                                           id="email" name="email" placeholder="usuario@ejemplo.com" required autofocus>
                                </div>
                                <div class="form-text mt-2">Ingresa el correo con el que te registraste.</div>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-warning btn-lg py-3 rounded-3 fw-bold" id="verifyEmailBtn">
                                    Verificar email →
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- PASO 2: Preguntas de seguridad (se muestra después de verificar email) --}}
                    <div id="step2" style="display: none;">
                        <form method="POST" action="{{ route('password.verify-answers') }}" id="securityForm">
                            @csrf
                            <input type="hidden" name="user_id" id="user_id" value="">

                            <div class="alert alert-info mb-4">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">🔐</div>
                                    <div class="flex-grow-1 ms-2">
                                        <strong>Verificación de seguridad</strong><br>
                                        Responde correctamente las siguientes preguntas para continuar.
                                    </div>
                                </div>
                            </div>

                            {{-- Pregunta 1 --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">📌 Pregunta 1</label>
                                <div class="bg-light p-3 rounded-3 border" id="question1-display">
                                    Cargando pregunta...
                                </div>
                                <input type="hidden" id="question1-text" name="question1">
                                <div class="mt-3">
                                    <input type="text" class="form-control form-control-lg"
                                           id="answer_1" name="answer_1"
                                           placeholder="Tu respuesta (no distingue mayúsculas/minúsculas)"
                                           required>
                                </div>
                            </div>

                            {{-- Pregunta 2 --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">📌 Pregunta 2</label>
                                <div class="bg-light p-3 rounded-3 border" id="question2-display">
                                    Cargando pregunta...
                                </div>
                                <input type="hidden" id="question2-text" name="question2">
                                <div class="mt-3">
                                    <input type="text" class="form-control form-control-lg"
                                           id="answer_2" name="answer_2"
                                           placeholder="Tu respuesta (no distingue mayúsculas/minúsculas)"
                                           required>
                                </div>
                            </div>

                            <div class="text-muted small mb-3">
                                ℹ️ Las respuestas no distinguen entre mayúsculas y minúsculas.
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg py-3" id="backToEmailBtn">
                                    ← Volver atrás
                                </button>
                                <button type="submit" class="btn btn-warning btn-lg py-3 rounded-3 fw-bold">
                                    Verificar respuestas →
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- PASO 3: Nueva contraseña (se muestra después de verificar respuestas) --}}
                    <div id="step3" style="display: none;">
                        <form method="POST" action="{{ route('password.reset-by-questions') }}" id="passwordForm">
                            @csrf
                            <input type="hidden" name="user_id" id="reset_user_id" value="">

                            <div class="alert alert-success mb-4">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">✅</div>
                                    <div class="flex-grow-1 ms-2">
                                        <strong>¡Verificación exitosa!</strong><br>
                                        Ahora puedes establecer tu nueva contraseña.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">🔒 Nueva contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">🔒</span>
                                    <input type="password" class="form-control form-control-lg bg-transparent border-start-0 ps-0"
                                           id="new_password" name="password" placeholder="Mínimo 6 caracteres" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">✓ Confirmar nueva contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">✓</span>
                                    <input type="password" class="form-control form-control-lg bg-transparent border-start-0 ps-0"
                                           id="password_confirmation" name="password_confirmation" placeholder="Repite tu nueva contraseña" required>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg py-3" id="backToQuestionsBtn">
                                    ← Volver a preguntas
                                </button>
                                <button type="submit" class="btn btn-success btn-lg py-3 rounded-3 fw-bold">
                                    ✅ Actualizar contraseña
                                </button>
                            </div>
                        </form>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-decoration-none">← Volver al inicio de sesión</a>
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
    const step3 = document.getElementById('step3');
    const step1Indicator = document.getElementById('step1-indicator');
    const step2Indicator = document.getElementById('step2-indicator');
    const step3Indicator = document.getElementById('step3-indicator');
    const backToEmailBtn = document.getElementById('backToEmailBtn');
    const backToQuestionsBtn = document.getElementById('backToQuestionsBtn');

    // Actualizar indicadores
    function updateIndicators(step) {
        // Resetear todos
        step1Indicator.classList.remove('bg-warning', 'bg-success');
        step2Indicator.classList.remove('bg-warning', 'bg-success');
        step3Indicator.classList.remove('bg-warning', 'bg-success');
        step1Indicator.classList.add('bg-secondary');
        step2Indicator.classList.add('bg-secondary');
        step3Indicator.classList.add('bg-secondary');
        step1Indicator.classList.remove('text-white');
        step2Indicator.classList.remove('text-white');
        step3Indicator.classList.remove('text-white');
        step1Indicator.classList.add('text-white');
        step2Indicator.classList.add('text-white');
        step3Indicator.classList.add('text-white');

        if (step === 1) {
            step1Indicator.classList.remove('bg-secondary');
            step1Indicator.classList.add('bg-warning');
            step1Indicator.classList.remove('text-white');
            step1Indicator.classList.add('text-dark');
        } else if (step === 2) {
            step2Indicator.classList.remove('bg-secondary');
            step2Indicator.classList.add('bg-warning');
            step2Indicator.classList.remove('text-white');
            step2Indicator.classList.add('text-dark');
        } else if (step === 3) {
            step3Indicator.classList.remove('bg-secondary');
            step3Indicator.classList.add('bg-success');
        }
    }

    // Mostrar paso
    function showStep(step) {
        step1.style.display = 'none';
        step2.style.display = 'none';
        step3.style.display = 'none';

        if (step === 1) {
            step1.style.display = 'block';
        } else if (step === 2) {
            step2.style.display = 'block';
        } else if (step === 3) {
            step3.style.display = 'block';
        }
        updateIndicators(step);
    }

    // Manejar envío del email (verificar email vía AJAX)
    document.getElementById('emailForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const email = document.getElementById('email').value;
        const submitBtn = document.getElementById('verifyEmailBtn');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verificando...';
        submitBtn.disabled = true;

        fetch('{{ route("password.verify-email") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Guardar user_id y preguntas
                document.getElementById('user_id').value = data.user_id;
                document.getElementById('question1-display').innerHTML = data.question1;
                document.getElementById('question1-text').value = data.question1;
                document.getElementById('question2-display').innerHTML = data.question2;
                document.getElementById('question2-text').value = data.question2;

                showStep(2);
            } else {
                alert(data.message || 'Error al verificar el email.');
            }
        })
        .catch(error => {
            alert('Error al conectar con el servidor.');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Manejar envío de respuestas
    document.getElementById('securityForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const user_id = document.getElementById('user_id').value;
        const answer_1 = document.getElementById('answer_1').value;
        const answer_2 = document.getElementById('answer_2').value;
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verificando...';
        submitBtn.disabled = true;

        fetch('{{ route("password.verify-answers") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                user_id: user_id,
                answer_1: answer_1,
                answer_2: answer_2
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('reset_user_id').value = user_id;
                showStep(3);
            } else {
                alert(data.message || 'Respuestas incorrectas. Intenta nuevamente.');
            }
        })
        .catch(error => {
            alert('Error al conectar con el servidor.');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Volver del paso 2 al paso 1
    backToEmailBtn.addEventListener('click', function() {
        showStep(1);
        document.getElementById('email').focus();
    });

    // Volver del paso 3 al paso 2
    backToQuestionsBtn.addEventListener('click', function() {
        showStep(2);
    });
</script>

<style>
    .bg-gradient {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
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
        border-color: #ffc107;
    }
    .input-group:focus-within .input-group-text {
        border-color: #ffc107;
    }
</style>
@endsection
