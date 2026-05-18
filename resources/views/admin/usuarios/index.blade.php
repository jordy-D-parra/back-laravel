@extends('layouts.dashboard')

@section('title', 'Usuarios')

@section('styles')
    @vite(['resources/css/admin-usuarios.css'])
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold" style="color: #1e3c72;">Gestion de Usuarios</h3>
        <button class="btn btn-primary-dark" data-bs-toggle="modal" data-bs-target="#modalUsuario">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo Usuario
        </button>
    </div>

    <!-- Tarjetas de estadisticas -->
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalActivos }}</div>
                <div class="stat-label">Usuarios Activos</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(30, 60, 114, 0.08);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                </svg>
            </div>
        </div>

        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalInactivos }}</div>
                <div class="stat-label">Usuarios Inactivos</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(220, 53, 69, 0.08);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c5221f" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="23" y1="3" x2="17" y2="9"/>
                    <line x1="17" y1="3" x2="23" y2="9"/>
                </svg>
            </div>
        </div>

        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $pendientesCambio }}</div>
                <div class="stat-label">Pendientes Cambio de Clave</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(255, 193, 7, 0.08);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f0a020" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>
        </div>

        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $nuncaLogeados }}</div>
                <div class="stat-label">Nunca Han Ingresado</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(23, 162, 184, 0.08);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#17a2b8" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Barra de busqueda y filtros -->
    <form method="GET" action="{{ route('admin.usuarios.index') }}" class="filters-bar">
        <div class="flex-grow-1" style="min-width: 200px;">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </span>
                <input type="text" class="form-control" name="search" placeholder="Buscar por nombre, cedula o usuario..."
                       value="{{ request('search') }}">
            </div>
        </div>

        <select class="form-select" name="rol" style="min-width: 150px;">
            <option value="">Todos los roles</option>
            @foreach($roles as $rol)
                <option value="{{ $rol->id }}" {{ request('rol') == $rol->id ? 'selected' : '' }}>
                    {{ ucfirst($rol->nombre) }}
                </option>
            @endforeach
        </select>

        <select class="form-select" name="status" style="min-width: 140px;">
            <option value="">Todos los estados</option>
            <option value="activo" {{ request('status') === 'activo' ? 'selected' : '' }}>Activo</option>
            <option value="inactivo" {{ request('status') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
        </select>

        <select class="form-select" name="must_change" style="min-width: 170px;">
            <option value="">Cambio de clave</option>
            <option value="1" {{ request('must_change') === '1' ? 'selected' : '' }}>Pendiente</option>
            <option value="0" {{ request('must_change') === '0' ? 'selected' : '' }}>Completado</option>
        </select>

        <button type="submit" class="btn btn-primary-dark">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
            </svg>
            Filtrar
        </button>

        @if(request()->anyFilled(['search', 'rol', 'status', 'must_change']))
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-primary-dark">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Limpiar
            </a>
        @endif
    </form>

    <!-- Tabla de usuarios -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Trabajador</th>
                        <th>Cedula</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Ultimo Ingreso</th>
                        <th>Clave</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                    <tr>
                        <td>
                            <span class="fw-medium">{{ $usuario->usuario }}</span>
                        </td>
                        <td>{{ $usuario->trabajador->nombre }} {{ $usuario->trabajador->apellido }}</td>
                        <td><small>{{ $usuario->trabajador->cedula }}</small></td>
                        <td>
                            @php
                                $rolClass = 'badge-role-' . $usuario->rol->nombre;
                            @endphp
                            <span class="badge-role {{ $rolClass }}">
                                {{ ucfirst($usuario->rol->nombre) }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-toggle-status {{ $usuario->status === 'activo' ? 'badge-status-activo' : 'badge-status-inactivo' }} border-0"
                                    data-id="{{ $usuario->id }}" style="font-size: 0.75rem;">
                                {{ $usuario->status === 'activo' ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td>
                            <small>{{ $usuario->ultimo_login ? $usuario->ultimo_login->format('d/m/Y H:i') : 'Nunca' }}</small>
                        </td>
                        <td>
                            @if($usuario->must_change_password)
                                <span class="badge-status-inactivo" style="font-size: 0.7rem; padding: 3px 8px; border-radius: 12px;">Pendiente</span>
                            @else
                                <span class="badge-status-activo" style="font-size: 0.7rem; padding: 3px 8px; border-radius: 12px;">OK</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <!-- Ver detalle -->
                                <button class="btn btn-sm btn-action btn-outline-primary-dark btn-ver-usuario"
                                        data-id="{{ $usuario->id }}" title="Ver detalle">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>

                                <!-- Editar -->
                                <button class="btn btn-sm btn-action btn-outline-primary-dark btn-editar-usuario"
                                        data-id="{{ $usuario->id }}"
                                        data-usuario="{{ $usuario->usuario }}"
                                        data-rol-id="{{ $usuario->rol_id }}"
                                        data-status="{{ $usuario->status }}" title="Editar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>

                                <!-- Reset password -->
                                <button class="btn btn-sm btn-action btn-outline-primary-dark btn-reset-password"
                                        data-id="{{ $usuario->id }}" title="Resetear contrasena">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </button>

                                <!-- Eliminar -->
                                @if($usuario->id !== Auth::id())
                                <button class="btn btn-sm btn-action btn-outline-danger btn-eliminar-usuario"
                                        data-id="{{ $usuario->id }}"
                                        data-usuario="{{ $usuario->usuario }}" title="Eliminar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#c5221f" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" class="mb-2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            </svg>
                            <p>No se encontraron usuarios</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $usuarios->links() }}
    </div>
</div>

<!-- =========================== -->
<!-- MODAL: Crear / Editar Usuario -->
<!-- =========================== -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioTitulo">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUsuario" method="POST" action="/admin/usuarios">
                @csrf
                <input type="hidden" name="_method" id="usuarioMethod" value="POST">

                <div class="modal-body px-4">

                    <!-- BUSQUEDA POR CEDULA (solo en creacion) -->
                    <div class="mb-4" id="divTrabajadorSelect">
                        <label class="form-label small fw-bold" style="color: #1e3c72;">
                            Buscar Trabajador por Cedula
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                            </span>
                            <input type="text" class="form-control" id="usuarioCedulaSearch"
                                   placeholder="V-12345678" autocomplete="off">
                        </div>
                        <!-- Resultados de busqueda -->
                        <div id="cedulaSearchResults" class="mt-2" style="display:none;"></div>

                        <!-- Trabajador encontrado -->
                        <div id="infoTrabajadorEncontrado" class="mt-3 p-3 rounded"
                             style="display:none; background: #f0f4ff; border: 1px solid #c5d5f0;">
                            <div class="d-flex align-items-start gap-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" class="mt-1 flex-shrink-0">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <div>
                                    <p class="mb-1 fw-medium" style="color: #1e3c72;" id="trabajadorEncontradoNombre"></p>
                                    <p class="mb-0 small text-muted" id="trabajadorEncontradoCargo"></p>
                                    <p class="mb-0 small text-muted" id="trabajadorEncontradoDepartamento"></p>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="usuarioTrabajadorId" name="trabajador_id">
                    </div>

                    <!-- Info trabajador (solo en edicion) -->
                    <div class="mb-3" id="divTrabajadorInfo" style="display:none;">
                        <div class="p-2 rounded" style="background: #f0f4ff; border: 1px solid #c5d5f0;">
                            <small class="fw-bold" style="color: #1e3c72;">Trabajador vinculado</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="usuarioNombre" class="form-label small fw-bold">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="usuarioNombre" name="usuario"
                               placeholder="ejemplo: juan.perez" required>
                        <small class="text-muted">
                            Sugerido: <span id="usuarioSugerido" style="color: #1e3c72; font-weight: 500;"></span>
                        </small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="usuarioRolId" class="form-label small fw-bold">Rol</label>
                        <select class="form-select" id="usuarioRolId" name="rol_id" required>
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}">
                                    {{ ucfirst($rol->nombre) }} - {{ $rol->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="usuarioStatus" class="form-label small fw-bold">Estado</label>
                        <select class="form-select" id="usuarioStatus" name="status">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <div class="p-3 rounded mt-3" id="alertaPassword" style="background: #f0f4ff; border: 1px solid #c5d5f0;">
                        <div class="d-flex align-items-start gap-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" class="mt-1 flex-shrink-0">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            <small style="color: #1e3c72;">Se generara una contrasena temporal automaticamente. El usuario debera cambiarla en su primer acceso.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer px-4 pb-4 border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarUsuario">
                        Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =========================== -->
<!-- MODAL: Confirmar Eliminacion -->
<!-- =========================== -->
<div class="modal fade" id="modalConfirmDelete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminacion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div class="delete-warning">
                    <div class="d-flex align-items-start gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c5221f" stroke-width="2" class="flex-shrink-0 mt-1">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <div>
                            <p class="mb-1 fw-medium" style="color: #c5221f;">Esta accion no se puede deshacer.</p>
                            <p class="mb-0 small text-muted">Se eliminara permanentemente al usuario <strong id="deleteUserName"></strong>.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formDelete" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Permanentemente</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- =========================== -->
<!-- MODAL: Ver Detalle Usuario -->
<!-- =========================== -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <h6 style="color: #1e3c72; font-weight: 600; margin-bottom: 1rem;">Datos del Trabajador</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Cedula</div>
                        <div class="detail-value" id="detailCedula">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Nombre Completo</div>
                        <div class="detail-value" id="detailNombre">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Departamento</div>
                        <div class="detail-value" id="detailDepartamento">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Cargo</div>
                        <div class="detail-value" id="detailCargo">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Especialidad</div>
                        <div class="detail-value" id="detailEspecialidad">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Telefono</div>
                        <div class="detail-value" id="detailTelefono">-</div>
                    </div>
                </div>

                <hr class="my-3">

                <h6 style="color: #1e3c72; font-weight: 600; margin-bottom: 1rem;">Datos de Acceso</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Usuario</div>
                        <div class="detail-value" id="detailUsuario">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Rol</div>
                        <div class="detail-value" id="detailRol">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Estado</div>
                        <div class="detail-value" id="detailStatus">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Ultimo Ingreso</div>
                        <div class="detail-value" id="detailUltimoLogin">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Creado</div>
                        <div class="detail-value" id="detailCreado">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- =========================== -->
<!-- MODAL: Contrasena Temporal (creacion) -->
<!-- =========================== -->
@if(session('new_password'))
<div class="modal fade" id="modalPassword" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">Contrasena Generada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body px-4 text-center">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="1.5" class="mb-2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <p class="small text-muted mb-2">Usuario: <strong>{{ session('new_usuario') }}</strong></p>
                <div class="password-display" id="passwordDisplay">{{ session('new_password') }}</div>
                <p class="small text-danger mt-2 mb-0">Copie esta contrasena ahora. No se volvera a mostrar.</p>
                <p class="small text-muted">El usuario debera cambiarla en su primer acceso.</p>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 justify-content-center">
                <button type="button" class="btn btn-primary-dark w-100" id="btnClosePasswordModal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- =========================== -->
<!-- MODAL: Contrasena Temporal (reset) -->
<!-- =========================== -->
@if(session('reset_password'))
<div class="modal fade" id="modalPassword" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">Contrasena Reseteada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body px-4 text-center">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="1.5" class="mb-2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <p class="small text-muted mb-2">Usuario: <strong>{{ session('reset_usuario') }}</strong></p>
                <div class="password-display" id="passwordDisplay">{{ session('reset_password') }}</div>
                <p class="small text-danger mt-2 mb-0">Copie esta contrasena ahora. No se volvera a mostrar.</p>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 justify-content-center">
                <button type="button" class="btn btn-primary-dark w-100" id="btnClosePasswordModal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Forms ocultos -->
<form id="formToggleStatus" method="POST" style="display:none;">
    @csrf
    @method('PATCH')
</form>

<form id="formResetPassword" method="POST" style="display:none;">
    @csrf
    @method('PATCH')
</form>
@endsection

@section('scripts')
    @vite(['resources/js/admin-usuarios.js'])
@endsection
