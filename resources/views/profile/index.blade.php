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
                        <div class="form-control bg-light">
                            @if(Auth::user()->rol)
                                {!! Auth::user()->role_badge !!}
                            @else
                                <span class="badge bg-danger">⚠️ Sin rol asignado</span>
                                <div class="text-danger small mt-1">
                                    Contacta al administrador para solicitar un rol.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado</label>
                        <div class="form-control bg-light">
                            @if(Auth::user()->activo)
                                <span class="badge bg-success">✓ Activo</span>
                            @else
                                <span class="badge bg-danger">✗ Inactivo</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de registro</label>
                        <div class="form-control bg-light">
                            {{ Auth::user()->fecha_solicitud ? Auth::user()->fecha_solicitud->format('d/m/Y H:i:s') : 'No registrada' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Último acceso</label>
                        <div class="form-control bg-light">
                            {{ Auth::user()->ultimo_login ? Auth::user()->ultimo_login->format('d/m/Y H:i:s') : 'Nunca' }}
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
@endsection