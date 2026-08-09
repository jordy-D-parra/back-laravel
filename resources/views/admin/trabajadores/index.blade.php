@extends('layouts.dashboard')

@section('title', 'Trabajadores')

@section('styles')
    @vite(['resources/css/admin-trabajadores.css'])
@endsection

@section('content')
<div class="container-fluid px-4">

    <!-- ========== HEADER CON GRADIENTE ========== -->
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                </svg>
                Gestión de Trabajadores
            </h4>
            <p>Registro y administración del personal del departamento</p>
        </div>
    </div>

    <!-- ========== TARJETAS DE ESTADÍSTICAS ========== -->
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalTrabajadores ?? 0 }}</div>
                <div class="stat-label">Total Trabajadores</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $conUsuario ?? 0 }}</div>
                <div class="stat-label">Con Usuario</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(30, 126, 52, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1e7e34" stroke-width="1.8">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $sinUsuario ?? 0 }}</div>
                <div class="stat-label">Sin Usuario</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(197, 34, 31, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#c5221f" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $departamentos ?? 0 }}</div>
                <div class="stat-label">Departamentos</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(23, 162, 184, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#17a2b8" stroke-width="1.8">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M8 8h8M8 12h6M8 16h4"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- ========== BARRA DE FILTROS CON SEPARACIÓN ========== -->
    <div class="filters-bar">
        <!-- Filtro de búsqueda a la IZQUIERDA -->
        <div class="filtro-busqueda">
            <div class="input-group">
                <span class="input-group-text">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </span>
                <input type="text" class="form-control" id="buscarTrabajador"
                       placeholder="Buscar por nombre, apellido o cédula..."
                       value="{{ request('search') }}">
            </div>
        </div>

        <!-- Botón Nuevo Trabajador a la DERECHA -->
        @if(auth()->user()->hasPermission('crear-trabajador'))
        <button style="color: #fff" class="btn btn-primary-dark btn-accion" data-bs-toggle="modal" data-bs-target="#modalTrabajador">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo Trabajador
        </button>
        @endif
    </div>

    <!-- ========== TABLA ========== -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>Especialidad</th>
                        <th>Usuario</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaTrabajadores">
                    @forelse($trabajadores as $trabajador)
                    <tr>
                        <td><small>{{ $trabajador->cedula }}</small></td>
                        <td><span class="fw-medium" style="color: var(--primary-dark);">{{ $trabajador->nombre }} {{ $trabajador->apellido }}</span></td>
                        <td>{{ $trabajador->cargo }}</td>
                        <td>{{ $trabajador->departamento }}</td>
                        <td>{{ $trabajador->especialidad ?? '-' }}</td>
                        <td>
                            @if($trabajador->usuario)
                                <span class="badge-usuario-si">{{ $trabajador->usuario->usuario }}</span>
                            @else
                                <span class="badge-usuario-no">Sin usuario</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                @if(auth()->user()->hasPermission('ver-trabajadores'))
                                <button class="btn btn-sm btn-action btn-outline-primary-dark btn-ver-trabajador"
                                        data-id="{{ $trabajador->id }}" title="Ver detalle">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                                @endif

                                @if(auth()->user()->hasPermission('editar-trabajador'))
                                <button class="btn btn-sm btn-action btn-outline-primary-dark btn-editar-trabajador"
                                        data-id="{{ $trabajador->id }}"
                                        data-cedula="{{ $trabajador->cedula }}"
                                        data-nombre="{{ $trabajador->nombre }}"
                                        data-apellido="{{ $trabajador->apellido }}"
                                        data-departamento="{{ $trabajador->departamento }}"
                                        data-cargo="{{ $trabajador->cargo }}"
                                        data-especialidad="{{ $trabajador->especialidad }}"
                                        data-telefono="{{ $trabajador->telefono }}" title="Editar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                @endif

                                @if(!$trabajador->usuario && auth()->user()->hasPermission('crear-usuario'))
                                <a href="{{ route('admin.usuarios.index', ['search' => $trabajador->cedula, 'crear' => 1]) }}"
                                   class="btn btn-sm btn-action btn-outline-primary-dark" title="Crear usuario">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </a>
                                @endif

                                @if(auth()->user()->hasPermission('eliminar-trabajador') && (!$trabajador->usuario || $trabajador->usuario->id !== Auth::id()))
                                <button class="btn btn-sm btn-action btn-outline-danger btn-eliminar-trabajador"
                                        data-id="{{ $trabajador->id }}"
                                        data-nombre="{{ $trabajador->nombre }} {{ $trabajador->apellido }}"
                                        data-tiene-usuario="{{ $trabajador->usuario ? '1' : '0' }}" title="Eliminar">
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
                        <td colspan="7" class="text-center py-5 text-muted">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" class="mb-2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                            </svg>
                            <p>No se encontraron trabajadores</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========== PAGINACIÓN ========== -->
    <div class="mt-3">
        {{ $trabajadores->links() }}
    </div>
</div>

<!-- ========== MODAL CREAR/EDITAR TRABAJADOR ========== -->
<div class="modal fade" id="modalTrabajador" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTrabajadorTitulo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    Nuevo Trabajador
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTrabajador" method="POST" action="/admin/trabajadores">
                @csrf
                <input type="hidden" name="_method" id="trabajadorMethod" value="POST">
                <div class="modal-body px-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Cédula <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="trabajadorCedula" name="cedula" placeholder="V-12345678" required>
                            <div id="cedulaFeedback" class="cedula-feedback" style="font-size:0.75rem;margin-top:4px;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Teléfono</label>
                            <input type="text" class="form-control" id="trabajadorTelefono" name="telefono" placeholder="0412-1234567">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="trabajadorNombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="trabajadorApellido" name="apellido" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Departamento <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="trabajadorDepartamento" name="departamento" value="Informática" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Cargo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="trabajadorCargo" name="cargo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Especialidad</label>
                            <input type="text" class="form-control" id="trabajadorEspecialidad" name="especialidad" placeholder="Ej: Redes, Circuitos, Soporte...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarTrabajador">Guardar Trabajador</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== MODAL CONFIRMAR ELIMINACIÓN ========== -->
<div class="modal fade" id="modalConfirmDelete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div id="deleteWarningUsuario" class="delete-warning" style="display:none;">
                    <p class="mb-1 fw-medium" style="color: var(--danger);">No se puede eliminar.</p>
                    <p class="mb-0 small text-muted">Este trabajador tiene un usuario vinculado. Debe eliminar el usuario primero.</p>
                </div>
                <div id="deleteConfirmText">
                    <p class="mb-1">Se eliminará permanentemente al trabajador:</p>
                    <p class="fw-bold" id="deleteTrabajadorNombre" style="color: var(--primary-dark);"></p>
                    <p class="small text-danger mb-0">Esta acción no se puede deshacer.</p>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                <form id="formDelete" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Permanentemente</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL DETALLE ========== -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Trabajador</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <h6 style="color: var(--primary-dark); font-weight: 600; margin-bottom: 1rem;">Datos Personales</h6>
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">Cédula</div><div class="detail-value" id="dtCedula">-</div></div>
                    <div class="detail-item"><div class="detail-label">Nombre Completo</div><div class="detail-value" id="dtNombre">-</div></div>
                    <div class="detail-item"><div class="detail-label">Departamento</div><div class="detail-value" id="dtDepartamento">-</div></div>
                    <div class="detail-item"><div class="detail-label">Cargo</div><div class="detail-value" id="dtCargo">-</div></div>
                    <div class="detail-item"><div class="detail-label">Especialidad</div><div class="detail-value" id="dtEspecialidad">-</div></div>
                    <div class="detail-item"><div class="detail-label">Teléfono</div><div class="detail-value" id="dtTelefono">-</div></div>
                    <div class="detail-item"><div class="detail-label">Registrado</div><div class="detail-value" id="dtCreado">-</div></div>
                </div>
                <hr>
                <h6 style="color: var(--primary-dark); font-weight: 600; margin-bottom: 1rem;">Usuario Vinculado</h6>
                <div id="dtInfoUsuario" class="detail-grid" style="display:none;"></div>
                <div id="dtSinUsuario" class="text-muted small">Sin usuario vinculado.</div>
                <button type="button" class="btn btn-primary-dark btn-sm mt-2" id="btnCrearUsuarioDesdeDetalle" style="display:none;">Crear Usuario para este Trabajador</button>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    @vite(['resources/js/admin-trabajadores.js'])
@endsection