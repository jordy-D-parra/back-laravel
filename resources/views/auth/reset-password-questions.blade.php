@extends('layouts.app')

@section('title', 'Nueva Contraseña')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-gradient bg-success text-white text-center rounded-top-4 py-4">
                    <div class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </svg>
                    </div>
                    <h3 class="mb-0">✅ Verificación exitosa</h3>
                    <p class="mb-0 mt-2 small opacity-75">Ahora puedes establecer tu nueva contraseña</p>
                </div>
                <div class="card-body p-5">

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.reset-by-questions') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">🔒 Nueva contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">🔒</span>
                                <input type="password" class="form-control form-control-lg bg-transparent border-start-0 ps-0"
                                       id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-bold">✓ Confirmar contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">✓</span>
                                <input type="password" class="form-control form-control-lg bg-transparent border-start-0 ps-0"
                                       id="password_confirmation" name="password_confirmation" placeholder="Repite tu nueva contraseña" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg py-3 rounded-3 fw-bold">
                                ✅ Actualizar contraseña
                            </button>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg py-3">
                                ← Volver al inicio de sesión
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
@endsection
