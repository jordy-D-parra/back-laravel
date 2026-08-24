@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('styles')
    @vite(['resources/css/auth/login.css'])
@endsection

@section('scripts')
    @vite(['resources/js/auth/login.js'])
@endsection

@section('content')
<div class="split-image" style="background-image: url('{{ asset('images/fondo-universitario.jpeg') }}');  background-size: cover;
    background-position: center;">
    <!-- Lado izquierdo - Imagen con overlay -->
    <div class="split-left-image">
        <div class="left-content">
            <!-- Escudo -->
            <div class="mb-4">
                <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex p-3">
                    <img src="{{ asset('images/escudo-yaracuy.jpeg') }}"
                         alt="Escudo del Estado Yaracuy"
                         style="width: 50px; height: 50px; object-fit: contain;">
                </div>
            </div>

            <!-- Título principal -->
            <div class="mb-3">
                <h2 class="fw-bold mb-2 display-6" style="font-size: 1.8rem;">Sistema Informático</h2>
                <p class="mb-0 fs-6 opacity-75">Gestión de Inventario de Equipos Tecnológicos</p>
            </div>
            <div class="mb-4">
                <small class="opacity-75">Gobernación del Estado Yaracuy - San Felipe, Edo. Yaracuy</small>
            </div>

            <!-- Línea decorativa -->
            <div class="d-flex justify-content-center gap-2 mb-4">
                <div class="bg-white rounded-pill" style="width: 40px; height: 2px;"></div>
                <div class="bg-white rounded-pill opacity-50" style="width: 20px; height: 2px;"></div>
            </div>

            <!-- Valores -->
            <div class="mt-4 text-start">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <span>Excelencia Académica</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <span>Innovación Tecnológica</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <span>Compromiso Social</span>
                </div>
            </div>

            <!-- Cita -->
            <div class="mt-5 pt-3">
                <div class="quote-icon">"</div>
                <p class="fst-italic mb-2 small">Tecnología al servicio del pueblo yaracuyano</p>
                <small class="opacity-75">— Proyecto de Grado</small>
            </div>

            <!-- Tecnologías -->
            <div class="mt-4 pt-2">
                <div class="d-flex justify-content-center gap-4">
                    <div class="tech-item text-center">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5">
                            <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7l-2-2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/>
                        </svg>
                        <div><small class="opacity-75">Laravel</small></div>
                    </div>
                    <div class="tech-item text-center">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <line x1="9" y1="3" x2="9" y2="21"/>
                        </svg>
                        <div><small class="opacity-75">Bootstrap</small></div>
                    </div>
                    <div class="tech-item text-center">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5">
                            <ellipse cx="12" cy="5" rx="9" ry="3"/>
                            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                        </svg>
                        <div><small class="opacity-75">PostgreSQL</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lado derecho - Formulario de login -->
    <div class="split-right-image">
        <div class="login-card-image">
            <div class="text-center mb-4">
                <div class="d-inline-block mb-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#1e3c72" class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="fw-bold" style="color: #1e3c72;">Acceso al Sistema</h3>
                <p class="text-muted">Ingresa tu usuario y contraseña</p>
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
                {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="usuario" class="form-label fw-semibold text-secondary">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#6c757d" class="bi bi-person-badge" viewBox="0 0 16 16">
                                <path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                <path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492V2.5z"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control form-control-lg border-start-0 ps-0"
                               id="usuario" name="usuario" value="{{ old('usuario') }}"
                               placeholder="admin" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold text-secondary">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#6c757d" class="bi bi-lock" viewBox="0 0 16 16">
                                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                            </svg>
                        </span>
                        <input type="password" class="form-control form-control-lg border-start-0 ps-0"
                               id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label text-secondary" for="remember">Recordarme</label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 btn-primary-custom">
                    Ingresar al Sistema
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
