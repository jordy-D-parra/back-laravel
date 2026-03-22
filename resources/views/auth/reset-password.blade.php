@extends('layouts.app')

@section('title', 'Restablecer Contraseña')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-success text-white text-center rounded-top-4 py-4">
                    <h3 class="mb-0">Nueva Contraseña</h3>
                    <p class="mb-0 mt-2 small">Ingresa tu nueva contraseña</p>
                </div>
                <div class="card-body p-5">

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="mb-4">
                            <label for="password" class="form-label">Nueva contraseña</label>
                            <input type="password"
                                   class="form-control form-control-lg"
                                   id="password"
                                   name="password"
                                   placeholder="Mínimo 6 caracteres"
                                   required
                                   autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <input type="password"
                                   class="form-control form-control-lg"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   placeholder="Repite tu nueva contraseña"
                                   required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg py-2">
                                Actualizar contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
