@extends('layouts.app')

@section('title', 'Configuración Inicial')

@section('styles')
    @vite(['resources/css/auth/login.css'])
    <style>
        .primer-registro-form {
            max-width: 500px;
            width: 100%;
        }

        .emoji-svg {
            width: 20px;
            height: 20px;
            vertical-align: middle;
            margin-right: 5px;
        }

        .btn-svg {
            width: 20px;
            height: 20px;
            vertical-align: middle;
            margin-right: 8px;
        }

        h3 .emoji-svg {
            width: 28px;
            height: 28px;
            margin-right: 10px;
        }
    </style>
@endsection

@section('content')
<div class="split-image" style="background-image: url('{{ asset('images/fondo-universitario.jpeg') }}');  background-size: cover;
    background-position: center;">
    <!-- Lado izquierdo -->
    <div class="split-left-image">
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
                    <span>Configuración Inicial</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <span>Primer Acceso al Sistema</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <span>Registro de Administrador</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lado derecho - Formulario -->
    <div class="split-right-image">
        <div class="primer-registro-form">
            <div class="text-center mb-4">
                <h3 class="fw-bold" style="color: #1e3c72;">
                    <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" fill="none"/>
                        <circle cx="12" cy="12" r="2" fill="currentColor"/>
                    </svg>
                    Configuración Inicial
                </h3>
                <p class="text-muted">Registre al administrador del sistema</p>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('primer.registro') }}">
                @csrf

                <h6 class="text-muted mb-3 border-bottom pb-2">
                    <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" fill="none"/>
                        <circle cx="12" cy="7" r="4" stroke="currentColor" fill="none"/>
                    </svg>
                    Datos del Trabajador
                </h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cedula" class="form-label small">
                            <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" fill="none"/>
                                <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor"/>
                                <circle cx="7.5" cy="14.5" r="1.5" fill="currentColor"/>
                                <line x1="11" y1="14" x2="17" y2="14" stroke="currentColor"/>
                            </svg>
                            Cédula
                        </label>
                        <input type="text" class="form-control" id="cedula" name="cedula"
                               value="{{ old('cedula') }}" placeholder="V-12345678" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label small">
                            <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="2" width="14" height="20" rx="2" stroke="currentColor" fill="none"/>
                                <line x1="12" y1="18" x2="12" y2="18" stroke="currentColor" stroke-width="3"/>
                            </svg>
                            Teléfono
                        </label>
                        <input type="text" class="form-control" id="telefono" name="telefono"
                               value="{{ old('telefono') }}" placeholder="0412-1234567">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label small">
                            <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" fill="none"/>
                                <circle cx="12" cy="7" r="4" stroke="currentColor" fill="none"/>
                            </svg>
                            Nombre
                        </label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               value="{{ old('nombre') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="apellido" class="form-label small">
                            <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" fill="none"/>
                                <circle cx="12" cy="7" r="4" stroke="currentColor" fill="none"/>
                                <line x1="17" y1="3" x2="21" y2="7" stroke="currentColor"/>
                                <line x1="21" y1="3" x2="17" y2="7" stroke="currentColor"/>
                            </svg>
                            Apellido
                        </label>
                        <input type="text" class="form-control" id="apellido" name="apellido"
                               value="{{ old('apellido') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="departamento" class="form-label small">
                        <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" stroke="currentColor" fill="none"/>
                            <line x1="16" y1="21" x2="16" y2="15" stroke="currentColor"/>
                            <line x1="8" y1="21" x2="8" y2="15" stroke="currentColor"/>
                            <line x1="2" y1="11" x2="22" y2="11" stroke="currentColor"/>
                            <rect x="5" y="2" width="3" height="5" stroke="currentColor" fill="none"/>
                            <rect x="16" y="2" width="3" height="5" stroke="currentColor" fill="none"/>
                        </svg>
                        Departamento
                    </label>
                    <input type="text" class="form-control" id="departamento" name="departamento"
                           value="{{ old('departamento', 'Informática') }}" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cargo" class="form-label small">
                            <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="2" width="16" height="20" rx="2" stroke="currentColor" fill="none"/>
                                <line x1="8" y1="6" x2="16" y2="6" stroke="currentColor"/>
                                <line x1="8" y1="10" x2="16" y2="10" stroke="currentColor"/>
                                <line x1="8" y1="14" x2="12" y2="14" stroke="currentColor"/>
                                <circle cx="17" cy="16" r="3" stroke="currentColor" fill="none"/>
                                <path d="M21 21l-3-3" stroke="currentColor"/>
                            </svg>
                            Cargo
                        </label>
                        <input type="text" class="form-control" id="cargo" name="cargo"
                               value="{{ old('cargo', 'Jefe de Departamento') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="especialidad" class="form-label small">
                            <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4" stroke="currentColor" fill="none"/>
                                <path d="M5 20v-2a7 7 0 0 1 14 0v2" stroke="currentColor" fill="none"/>
                                <line x1="12" y1="12" x2="12" y2="16" stroke="currentColor"/>
                                <circle cx="12" cy="16" r="1" fill="currentColor"/>
                            </svg>
                            Especialidad
                        </label>
                        <input type="text" class="form-control" id="especialidad" name="especialidad"
                               value="{{ old('especialidad') }}" placeholder="Ej: Redes, Sistemas...">
                    </div>
                </div>

                <h6 class="text-muted mb-3 border-bottom pb-2 mt-4">
                    <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" fill="none"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" fill="none"/>
                        <circle cx="12" cy="16" r="1.5" fill="currentColor"/>
                        <line x1="12" y1="18" x2="12" y2="20" stroke="currentColor"/>
                    </svg>
                    Datos de Acceso
                </h6>

                <div class="mb-3">
                    <label for="usuario" class="form-label small">
                        <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" fill="none"/>
                            <circle cx="12" cy="7" r="4" stroke="currentColor" fill="none"/>
                            <circle cx="12" cy="7" r="2" fill="currentColor"/>
                        </svg>
                        Usuario
                    </label>
                    <input type="text" class="form-control" id="usuario" name="usuario"
                           value="{{ old('usuario') }}" placeholder="admin" required>
                </div>

                <div class="mb-3">
                    <label for="rol_id" class="form-label small">
                        <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" stroke="currentColor"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" fill="none"/>
                            <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                        </svg>
                        Rol
                    </label>
                    <select class="form-select" id="rol_id" name="rol_id" required>
                        <option value="">Seleccione un rol</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" {{ old('rol_id') == $rol->id ? 'selected' : '' }}>
                                {{ ucfirst($rol->nombre) }} - {{ $rol->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small">
                        <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" fill="none"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" fill="none"/>
                            <circle cx="12" cy="16" r="1.5" fill="currentColor"/>
                            <line x1="12" y1="18" x2="12" y2="20" stroke="currentColor"/>
                        </svg>
                        Contraseña
                    </label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Mínimo 8 caracteres" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label small">
                        <svg class="emoji-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" fill="none"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" fill="none"/>
                            <path d="M9 16l2 2 4-4" stroke="currentColor" fill="none"/>
                        </svg>
                        Confirmar Contraseña
                    </label>
                    <input type="password" class="form-control" id="password_confirmation"
                           name="password_confirmation" placeholder="Repita la contraseña" required>
                </div>

                <button type="submit" class="btn btn-success btn-lg w-100">
                    <svg class="btn-svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <circle cx="12" cy="12" r="10" stroke="white" fill="none"/>
                        <path d="M12 8v8M8 12h8" stroke="white"/>
                        <path d="M12 16l-4-4M12 16l4-4" stroke="white"/>
                    </svg>
                    Inicializar Sistema
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
