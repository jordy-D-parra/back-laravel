@extends('layouts.app')

@section('title', 'Verificar Seguridad')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-warning text-dark text-center rounded-top-4 py-3">
                    <h4 class="mb-0">🔐 Preguntas de Seguridad</h4>
                    <p class="mb-0 mt-1 small">Responde para recuperar tu contraseña</p>
                </div>
                <div class="card-body p-4">

                    <form id="securityForm">
                        @csrf
                        <input type="hidden" id="user_id" name="user_id">

                        <div class="mb-3">
                            <label class="form-label small fw-bold" id="question1_label">Cargando pregunta 1...</label>
                            <input type="text" class="form-control form-control-sm" id="answer_1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold" id="question2_label">Cargando pregunta 2...</label>
                            <input type="text" class="form-control form-control-sm" id="answer_2" required>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('password.request') }}" class="btn btn-outline-secondary btn-sm py-2 rounded-3 flex-grow-1">
                                ← Volver
                            </a>
                            <button type="submit" class="btn btn-warning btn-sm py-2 rounded-3 flex-grow-1 fw-bold" id="submitBtn">
                                Verificar respuestas →
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Recuperar datos del sessionStorage
    const resetData = JSON.parse(sessionStorage.getItem('resetData'));
    
    if (resetData) {
        document.getElementById('user_id').value = resetData.user_id;
        document.getElementById('question1_label').innerHTML = resetData.question1;
        document.getElementById('question2_label').innerHTML = resetData.question2;
    } else {
        alert('No hay datos de recuperación. Inicia el proceso nuevamente.');
        window.location.href = '{{ route("password.request") }}';
    }

    // Enviar formulario con AJAX
    document.getElementById('securityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerHTML = 'Verificando...';
        submitBtn.disabled = true;
        
        const token = document.querySelector('input[name="_token"]').value;
        
        fetch('{{ route("password.verify-answers") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                user_id: document.getElementById('user_id').value,
                answer_1: document.getElementById('answer_1').value,
                answer_2: document.getElementById('answer_2').value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("password.reset-form") }}';
            } else {
                alert(data.message || 'Respuestas incorrectas');
                submitBtn.innerHTML = 'Verificar respuestas →';
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            alert('Error al conectar con el servidor');
            submitBtn.innerHTML = 'Verificar respuestas →';
            submitBtn.disabled = false;
        });
    });
</script>
@endsection
