sudo cat > resources/views/auth/forgot-password.blade.php << 'EOF'
@extends('layouts.app')

@section('title', 'Recuperar Contraseña')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-warning text-dark text-center rounded-top-4 py-3">
                    <h4 class="mb-0">🔐 Recuperar Contraseña</h4>
                    <p class="mb-0 mt-1 small">Ingresa tu cédula para comenzar</p>
                </div>
                <div class="card-body p-4">

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form id="forgotForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Cédula</label>
                            <input type="text" class="form-control form-control-sm"
                                   id="cedula" name="cedula" placeholder="V-12345678" required autofocus>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning btn-sm py-2 rounded-3 fw-bold" id="submitBtn">
                                Verificar cédula →
                            </button>
                        </div>
                    </form>

                    <hr class="my-3">
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="small">← Volver al inicio de sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('forgotForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const cedula = document.getElementById('cedula').value;
        const submitBtn = document.getElementById('submitBtn');

        if (!cedula) {
            alert('Ingresa tu cédula');
            return;
        }

        submitBtn.innerHTML = 'Verificando...';
        submitBtn.disabled = true;

        // Obtener token CSRF
        const token = document.querySelector('input[name="_token"]')?.value;

        fetch('{{ route("password.verify-email") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ cedula: cedula })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                sessionStorage.setItem('resetData', JSON.stringify({
                    user_id: data.user_id,
                    question1: data.question1,
                    question2: data.question2
                }));
                window.location.href = '{{ route("password.reset-form") }}';
            } else {
                alert(data.message || 'Error al verificar la cédula.');
                submitBtn.innerHTML = 'Verificar cédula →';
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al conectar con el servidor. Revisa la consola.');
            submitBtn.innerHTML = 'Verificar cédula →';
            submitBtn.disabled = false;
        });
    });
</script>
@endsection
EOF
