@extends('layouts.app')

@section('title', 'Recuperar Contraseña')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-warning text-dark text-center rounded-top-4 py-3">
                    <h4 class="mb-0">🔐 Recuperar Contraseña</h4>
                    <p class="mb-0 mt-1 small">Ingresa tu cédula y responde las preguntas</p>
                </div>
                <div class="card-body p-4">

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.reset-by-questions') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Cédula</label>
                            <input type="text" class="form-control form-control-sm"
                                   name="cedula" value="{{ old('cedula') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pregunta 1</label>
                            <select class="form-select form-select-sm" name="pregunta_seguridad_1" required>
                                <option value="">Seleccione una pregunta...</option>
                                <option value="¿Nombre de tu primera mascota?">¿Nombre de tu primera mascota?</option>
                                <option value="¿Nombre de tu madre soltera?">¿Nombre de tu madre soltera?</option>
                                <option value="¿Modelo de tu primer auto?">¿Modelo de tu primer auto?</option>
                                <option value="¿Ciudad donde naciste?">¿Ciudad donde naciste?</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Respuesta 1</label>
                            <input type="text" class="form-control form-control-sm" name="respuesta_1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pregunta 2</label>
                            <select class="form-select form-select-sm" name="pregunta_seguridad_2" required>
                                <option value="">Seleccione una pregunta...</option>
                                <option value="¿Tu comida favorita?">¿Tu comida favorita?</option>
                                <option value="¿Nombre de tu primer profesor?">¿Nombre de tu primer profesor?</option>
                                <option value="¿Color favorito?">¿Color favorito?</option>
                                <option value="¿Marca de tu primer celular?">¿Marca de tu primer celular?</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Respuesta 2</label>
                            <input type="text" class="form-control form-control-sm" name="respuesta_2" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nueva Contraseña</label>
                            <input type="password" class="form-control form-control-sm" name="password" placeholder="Mínimo 6 caracteres" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Confirmar Contraseña</label>
                            <input type="password" class="form-control form-control-sm" name="password_confirmation" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning btn-sm py-2 rounded-3 fw-bold">
                                ✅ Cambiar contraseña
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
@endsection
