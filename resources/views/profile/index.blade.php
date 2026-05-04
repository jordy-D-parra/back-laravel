@extends('layouts.dashboard')

@section('title', 'Mi Perfil')

@section('content')
<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-3">👤 Mi Perfil</h2>
                        <p class="text-muted mb-0">Gestiona tu información personal y configuración de cuenta</p>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 4rem;">👤</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advertencia de rol faltante -->
    @if(session('role_warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>¡Atención!</strong> {{ session('role_warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Información Personal -->
        <div class="col-md-6 mb-4">
            <div class="stat-card">
                <h4 class="mb-3">📋 Información Personal</h4>

                @if(session('profile_success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('profile_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('profile_error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('profile_error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-bold">Nombre</label>
                        <input type="text"
                               class="form-control @error('nombre') is-invalid @enderror"
                               id="nombre"
                               name="nombre"
                               value="{{ old('nombre', Auth::user()->nombre) }}"
                               required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="apellido" class="form-label fw-bold">Apellido</label>
                        <input type="text"
                               class="form-control @error('apellido') is-invalid @enderror"
                               id="apellido"
                               name="apellido"
                               value="{{ old('apellido', Auth::user()->apellido) }}"
                               required>
                        @error('apellido')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="cedula" class="form-label fw-bold">Cédula</label>
                        <input type="text"
                               class="form-control bg-light"
                               id="cedula"
                               value="{{ Auth::user()->cedula }}"
                               readonly
                               disabled>
                        <div class="form-text">La cédula no se puede modificar.</div>
                    </div>

                    <div class="mb-3">
                        <label for="departamento" class="form-label fw-bold">Departamento</label>
                        <input type="text"
                               class="form-control @error('departamento') is-invalid @enderror"
                               id="departamento"
                               name="departamento"
                               value="{{ old('departamento', Auth::user()->departamento) }}">
                        @error('departamento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="cargo" class="form-label fw-bold">Cargo</label>
                        <input type="text"
                               class="form-control @error('cargo') is-invalid @enderror"
                               id="cargo"
                               name="cargo"
                               value="{{ old('cargo', Auth::user()->cargo) }}">
                        @error('cargo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Rol en el sistema</label>
                        <div class="form-control bg-light" style="min-height: 50px;">
                            @php
                                $user = Auth::user();
                            @endphp
                            
                            @if($user->rol)
                                @php
                                    $roleName = $user->rol->nombre ?? 'sin_rol';
                                    $roleIcon = match($roleName) {
                                        'super_admin' => '👑',
                                        'admin' => '⚙️',
                                        'worker' => '🔧',
                                        'user', 'usuario' => '👤',
                                        default => '❓'
                                    };
                                    $roleDisplay = match($roleName) {
                                        'super_admin' => 'Super Administrador',
                                        'admin' => 'Administrador',
                                        'worker' => 'Trabajador',
                                        'user', 'usuario' => 'Usuario Base',
                                        default => ucfirst($roleName)
                                    };
                                    $badgeClass = match($roleName) {
                                        'super_admin' => 'bg-danger',
                                        'admin' => 'bg-warning text-dark',
                                        'worker' => 'bg-primary',
                                        'user', 'usuario' => 'bg-success',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} fs-6 p-2">
                                    {{ $roleIcon }} {{ $roleDisplay }}
                                </span>
                                @if($roleName == 'user' || $roleName == 'usuario')
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-info-circle"></i> 
                                        Como usuario base puedes realizar solicitudes de préstamo y ver su estado.
                                    </div>
                                @elseif($roleName == 'worker')
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-info-circle"></i> 
                                        Como trabajador puedes gestionar préstamos y equipos.
                                    </div>
                                @elseif($roleName == 'admin')
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-info-circle"></i> 
                                        Como administrador puedes gestionar usuarios y aprobar solicitudes.
                                    </div>
                                @elseif($roleName == 'super_admin')
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-info-circle"></i> 
                                        Tienes acceso total al sistema.
                                    </div>
                                @endif
                            @else
                                <div class="text-center">
                                    <span class="badge bg-danger fs-6 p-2 mb-2">
                                        ⚠️ Sin rol asignado
                                    </span>
                                    <div class="text-danger small mt-2">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        No tienes un rol asignado. Contacta al administrador para solicitar uno.
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado de la cuenta</label>
                        <div class="form-control bg-light">
                            @php
                                $estado = Auth::user()->estado_usuario ?? 'inactivo';
                                $estadoIcon = match($estado) {
                                    'activo' => '✅',
                                    'pendiente' => '⏳',
                                    'inactivo' => '❌',
                                    'suspendido' => '⚠️',
                                    default => '❓'
                                };
                                $estadoDisplay = match($estado) {
                                    'activo' => 'Activo',
                                    'pendiente' => 'Pendiente de aprobación',
                                    'inactivo' => 'Inactivo',
                                    'suspendido' => 'Suspendido',
                                    default => ucfirst($estado)
                                };
                                $badgeClass = match($estado) {
                                    'activo' => 'bg-success',
                                    'pendiente' => 'bg-warning text-dark',
                                    'inactivo' => 'bg-danger',
                                    'suspendido' => 'bg-secondary',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} fs-6 p-2">
                                {{ $estadoIcon }} {{ $estadoDisplay }}
                            </span>
                            
                            @if($estado == 'pendiente')
                                <div class="text-warning small mt-2">
                                    <i class="fas fa-clock"></i> 
                                    Tu cuenta está pendiente de aprobación por un administrador.
                                </div>
                            @elseif($estado == 'inactivo')
                                <div class="text-danger small mt-2">
                                    <i class="fas fa-ban"></i> 
                                    Tu cuenta está inactiva. Contacta al administrador.
                                </div>
                            @elseif($estado == 'suspendido')
                                <div class="text-secondary small mt-2">
                                    <i class="fas fa-pause"></i> 
                                    Tu cuenta ha sido suspendida temporalmente.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de registro</label>
                        <div class="form-control bg-light">
                            {{ Auth::user()->fecha_solicitud ? \Carbon\Carbon::parse(Auth::user()->fecha_solicitud)->format('d/m/Y H:i:s') : 'No registrada' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Último acceso</label>
                        <div class="form-control bg-light">
                            {{ Auth::user()->ultimo_login ? \Carbon\Carbon::parse(Auth::user()->ultimo_login)->format('d/m/Y H:i:s') : 'Nunca' }}
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        💾 Actualizar información
                    </button>
                </form>
            </div>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="col-md-6 mb-4">
            <div class="stat-card">
                <h4 class="mb-3">🔒 Cambiar Contraseña</h4>

                @if(session('password_success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('password_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('password_error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('password_error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold">Contraseña actual</label>
                        <input type="password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               id="current_password"
                               name="current_password"
                               required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-bold">Nueva contraseña</label>
                        <input type="password"
                               class="form-control @error('new_password') is-invalid @enderror"
                               id="new_password"
                               name="new_password"
                               required>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Mínimo 6 caracteres.</div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label fw-bold">Confirmar nueva contraseña</label>
                        <input type="password"
                               class="form-control"
                               id="new_password_confirmation"
                               name="new_password_confirmation"
                               required>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">
                        🔄 Actualizar contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Preguntas de Seguridad -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <h4 class="mb-3">❓ Preguntas de Seguridad</h4>
                <p class="text-muted mb-3">Estas preguntas te ayudarán a recuperar tu contraseña en caso de olvido.</p>

                @if(session('security_success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('security_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('security_error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('security_error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.security') }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pregunta_seguridad_1" class="form-label fw-bold">Pregunta 1</label>
                            <select class="form-select @error('pregunta_seguridad_1') is-invalid @enderror"
                                    id="pregunta_seguridad_1"
                                    name="pregunta_seguridad_1"
                                    required>
                                <option value="">Selecciona una pregunta...</option>
                                <option value="¿Nombre de tu primera mascota?" {{ Auth::user()->pregunta_seguridad_1 == '¿Nombre de tu primera mascota?' ? 'selected' : '' }}>¿Nombre de tu primera mascota?</option>
                                <option value="¿Ciudad donde naciste?" {{ Auth::user()->pregunta_seguridad_1 == '¿Ciudad donde naciste?' ? 'selected' : '' }}>¿Ciudad donde naciste?</option>
                                <option value="¿Nombre de tu madre soltera?" {{ Auth::user()->pregunta_seguridad_1 == '¿Nombre de tu madre soltera?' ? 'selected' : '' }}>¿Nombre de tu madre soltera?</option>
                                <option value="¿Comida favorita?" {{ Auth::user()->pregunta_seguridad_1 == '¿Comida favorita?' ? 'selected' : '' }}>¿Comida favorita?</option>
                                <option value="¿Color favorito?" {{ Auth::user()->pregunta_seguridad_1 == '¿Color favorito?' ? 'selected' : '' }}>¿Color favorito?</option>
                            </select>
                            @error('pregunta_seguridad_1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="respuesta_1" class="form-label fw-bold">Respuesta 1</label>
                            <input type="text"
                                   class="form-control @error('respuesta_1') is-invalid @enderror"
                                   id="respuesta_1"
                                   name="respuesta_1"
                                   value="{{ old('respuesta_1') }}"
                                   placeholder="Ingresa tu respuesta"
                                   required>
                            @error('respuesta_1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">No importan las mayúsculas/minúsculas.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pregunta_seguridad_2" class="form-label fw-bold">Pregunta 2</label>
                            <select class="form-select @error('pregunta_seguridad_2') is-invalid @enderror"
                                    id="pregunta_seguridad_2"
                                    name="pregunta_seguridad_2"
                                    required>
                                <option value="">Selecciona una pregunta...</option>
                                <option value="¿Nombre de tu primera mascota?" {{ Auth::user()->pregunta_seguridad_2 == '¿Nombre de tu primera mascota?' ? 'selected' : '' }}>¿Nombre de tu primera mascota?</option>
                                <option value="¿Ciudad donde naciste?" {{ Auth::user()->pregunta_seguridad_2 == '¿Ciudad donde naciste?' ? 'selected' : '' }}>¿Ciudad donde naciste?</option>
                                <option value="¿Nombre de tu madre soltera?" {{ Auth::user()->pregunta_seguridad_2 == '¿Nombre de tu madre soltera?' ? 'selected' : '' }}>¿Nombre de tu madre soltera?</option>
                                <option value="¿Comida favorita?" {{ Auth::user()->pregunta_seguridad_2 == '¿Comida favorita?' ? 'selected' : '' }}>¿Comida favorita?</option>
                                <option value="¿Color favorito?" {{ Auth::user()->pregunta_seguridad_2 == '¿Color favorito?' ? 'selected' : '' }}>¿Color favorito?</option>
                            </select>
                            @error('pregunta_seguridad_2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="respuesta_2" class="form-label fw-bold">Respuesta 2</label>
                            <input type="text"
                                   class="form-control @error('respuesta_2') is-invalid @enderror"
                                   id="respuesta_2"
                                   name="respuesta_2"
                                   value="{{ old('respuesta_2') }}"
                                   placeholder="Ingresa tu respuesta"
                                   required>
                            @error('respuesta_2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">No importan las mayúsculas/minúsculas.</div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> Si cambias tus preguntas de seguridad, también deberás actualizar tus respuestas.
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        ✅ Actualizar preguntas de seguridad
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s;
        border: none;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .badge {
        font-size: 0.9rem;
        padding: 6px 12px;
        border-radius: 20px;
    }
    
    .form-control.bg-light {
        background-color: #f8f9fa;
    }
    
    .alert {
        border-radius: 10px;
    }
    
    .btn {
        border-radius: 8px;
        padding: 10px;
        font-weight: 500;
    }
</style>
@endsection