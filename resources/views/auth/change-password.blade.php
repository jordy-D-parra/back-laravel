@extends('layouts.app')

@section('title', 'Cambiar Contraseña')

@section('styles')
    @vite(['resources/css/auth/login.css'])
@endsection

@section('content')
<div class="split-image">
    <!-- Lado izquierdo - Mismo que el login -->
    <div class="split-left-image" style="background-image: url('{{ asset('images/fondo-universitario.jpeg') }}');  background-size: cover;
    background-position: center;">
        <div class="left-content">
            <div class="mb-4">
                <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex p-3">
                    <img src="{{ asset('images/escudo-yaracuy.jpeg') }}"
                         alt="Escudo del Estado Yaracuy"
                         style="width: 50px; height: 50px; object-fit: contain;">
                </div>
            </div>

            <div class="mb-3">
                <h2 class="fw-bold mb-2 display-6" style="font-size: 1.8rem;">Sistema Informático</h2>
                <p class="mb-0 fs-6 opacity-75">Gestión de Inventario de Equipos Tecnológicos</p>
            </div>
            <div class="mb-4">
                <small class="opacity-75">Gobernación del Estado Yaracuy - San Felipe, Edo. Yaracuy</small>
            </div>

            <div class="d-flex justify-content-center gap-2 mb-4">
                <div class="bg-white rounded-pill" style="width: 40px; height: 2px;"></div>
                <div class="bg-white rounded-pill opacity-50" style="width: 20px; height: 2px;"></div>
            </div>

            <div class="mt-4 text-start">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <span>Seguridad de Acceso</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <span>Protección de Datos</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <span>Confidencialidad</span>
                </div>
            </div>

            <div class="mt-5 pt-3">
                <div class="quote-icon">"</div>
                <p class="fst-italic mb-2 small">Tecnología al servicio del pueblo yaracuyano</p>
                <small class="opacity-75">— Proyecto de Grado</small>
            </div>
        </div>
    </div>

    <!-- Lado derecho - Formulario de cambio de contraseña -->
    <div class="split-right-image">
        <div class="login-card-image">
            <div class="text-center mb-4">
                <div class="d-inline-block mb-3">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#e6a817" class="bi bi-shield-lock" viewBox="0 0 16 16">
                            <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.6.6 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56z"/>
                            <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="fw-bold" style="color: #1e3c72;">Cambio de Contraseña</h3>
                <p class="text-muted">Por seguridad, debe cambiar su contraseña temporal</p>
            </div>

            @if (session('status'))
            <div class="alert alert-security alert-dismissible fade show rounded-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('status') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('password.change') }}">
                @csrf

                <div class="mb-3">
                    <label for="current_password" class="form-label fw-semibold text-secondary">Contraseña Actual</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#6c757d" class="bi bi-lock" viewBox="0 0 16 16">
                                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                            </svg>
                        </span>
                        <input type="password" class="form-control form-control-lg border-start-0 ps-0"
                               id="current_password" name="current_password"
                               placeholder="Contraseña temporal" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label fw-semibold text-secondary">Nueva Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#6c757d" class="bi bi-key" viewBox="0 0 16 16">
                                <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z"/>
                                <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                            </svg>
                        </span>
                        <input type="password" class="form-control form-control-lg border-start-0 ps-0"
                               id="new_password" name="new_password"
                               placeholder="Mínimo 8 caracteres" required>
                    </div>
                    <small class="text-muted">Debe contener mayúsculas, minúsculas, números y símbolos</small>
                </div>

                <div class="mb-4">
                    <label for="new_password_confirmation" class="form-label fw-semibold text-secondary">Confirmar Nueva Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#6c757d" class="bi bi-check-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                            </svg>
                        </span>
                        <input type="password" class="form-control form-control-lg border-start-0 ps-0"
                               id="new_password_confirmation" name="new_password_confirmation"
                               placeholder="Repita la contraseña" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold" style="background: linear-gradient(135deg, #1e3c72 0%, #1e3c72 100%); border: none; padding: 12px; transition: all 0.3s ease;">
                    Actualizar Contraseña
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
