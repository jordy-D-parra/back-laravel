@extends('layouts.dashboard')

@section('title', 'Usuarios')

@section('styles')
    @vite(['resources/css/admin-usuarios.css'])
    <style>
        /* ============================================================
           PAGINACIÓN ELEGANTE
           ============================================================ */
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            flex-wrap: wrap;
            gap: 0.75rem;
            background: white;
            border-top: 1px solid var(--border-light);
        }

        .pagination-info {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .pagination-btns {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .pagination-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 0.6rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--primary-dark);
            background: white;
            border: 1.5px solid var(--border-light);
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            cursor: pointer;
            user-select: none;
            line-height: 1;
        }

        .pagination-btn:hover:not(.disabled):not(.active) {
            background: var(--primary-lighter);
            border-color: var(--primary-dark);
            color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.15);
        }

        .pagination-btn.active {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-light));
            color: white;
            border-color: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
            font-weight: 700;
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .pagination-ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
        }

        @media (max-width: 576px) {
            .pagination-bar {
                flex-direction: column;
                align-items: center;
            }
            .pagination-btn {
                min-width: 32px;
                height: 32px;
                font-size: 0.75rem;
            }
        }
    </style>
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
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Gestión de Usuarios
            </h4>
            <p>Administración de usuarios, roles y accesos al sistema</p>
        </div>
    </div>

    <!-- ========== TARJETAS DE ESTADÍSTICAS ========== -->
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalActivos ?? 0 }}</div>
                <div class="stat-label">Usuarios Activos</div>
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
                <div class="stat-number">{{ $totalInactivos ?? 0 }}</div>
                <div class="stat-label">Usuarios Inactivos</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(197, 34, 31, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#c5221f" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="23" y1="3" x2="17" y2="9"/>
                    <line x1="17" y1="3" x2="23" y2="9"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $pendientesCambio ?? 0 }}</div>
                <div class="stat-label">Pendientes Cambio Clave</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(246, 194, 62, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f6c23e" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $nuncaLogeados ?? 0 }}</div>
                <div class="stat-label">Nunca Han Ingresado</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(23, 162, 184, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#17a2b8" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- ========== BARRA DE FILTROS ========== -->
    <div class="filters-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div class="input-group" style="flex: 1; min-width: 200px; max-width: 450px;">
            <span class="input-group-text bg-white">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
            </span>
            <input type="text" class="form-control border-start-0" id="buscarUsuario"
                   placeholder="Buscar por nombre, cédula o usuario..."
                   value="{{ request('search') }}">
        </div>

        @if(auth()->user()->hasPermission('crear-usuario'))
        <button class="btn btn-primary-dark" data-bs-toggle="modal" data-bs-target="#modalUsuario" style="flex-shrink: 0; color: #fff">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo Usuario
        </button>
        @endif
    </div>

    <!-- ========== TABLA ========== -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Trabajador</th>
                        <th>Cédula</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Ingreso</th>
                        <th>Clave</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaUsuarios">
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Cargando usuarios...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="paginacionUsuarios"></div>
    </div>
</div>

<!-- ========== MODAL CREAR/EDITAR USUARIO ========== -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioTitulo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    Nuevo Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUsuario" method="POST" action="/admin/usuarios">
                @csrf
                <input type="hidden" name="_method" id="usuarioMethod" value="POST">
                <input type="hidden" name="id" id="usuarioId" value="">
                <div class="modal-body px-4">

                    <!-- BUSCADOR POR CÉDULA -->
                    <div class="mb-4" id="divTrabajadorSelect">
                        <label class="form-label small fw-bold" style="color: var(--primary-dark);">
                            Buscar Trabajador por Cédula
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary-dark)" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                            </span>
                            <input type="text" class="form-control" id="usuarioCedulaSearch"
                                   placeholder="V-12345678" autocomplete="off">
                        </div>
                        <div id="cedulaSearchResults" class="mt-2" style="display:none;"></div>
                        <div id="infoTrabajadorEncontrado" class="mt-3" style="display:none;">
                            <div class="d-flex align-items-start gap-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-dark)" stroke-width="2" class="mt-1 flex-shrink-0">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <div>
                                    <p class="mb-1 fw-medium" style="color: var(--primary-dark);" id="trabajadorEncontradoNombre"></p>
                                    <p class="mb-0 small text-muted" id="trabajadorEncontradoCargo"></p>
                                    <p class="mb-0 small text-muted" id="trabajadorEncontradoDepartamento"></p>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="usuarioTrabajadorId" name="trabajador_id">
                    </div>

                    <!-- INFO TRABAJADOR (EDICIÓN) -->
                    <div class="mb-3" id="divTrabajadorInfo" style="display:none;">
                        <div class="p-2 rounded" style="background: var(--primary-lighter); border: 1px solid #c5d5f0;">
                            <small class="fw-bold" style="color: var(--primary-dark);">Trabajador vinculado</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="usuarioNombre" class="form-label small fw-bold">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="usuarioNombre" name="usuario"
                               placeholder="ejemplo: juan.perez" required>
                        <small class="text-muted">
                            Sugerido: <span id="usuarioSugerido" style="color: var(--primary-dark); font-weight: 500;"></span>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="usuarioRolId" class="form-label small fw-bold">Rol</label>
                        <select class="form-select" id="usuarioRolId" name="rol_id" required>
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}">{{ ucfirst($rol->nombre) }} - {{ $rol->descripcion }}</option>
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

                    <div class="p-3 rounded mt-3" style="background: var(--primary-lighter); border: 1px solid #c5d5f0;">
                        <div class="d-flex align-items-start gap-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-dark)" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            <small style="color: var(--primary-dark);">
                                Se generará una contraseña temporal automáticamente. El usuario deberá cambiarla en su primer acceso.
                            </small>
                        </div>
                    </div>

                    <div id="passwordHashContainer" class="password-hash-container">
                        <span class="hash-label">🔐 Contraseña Encriptada (Hash)</span>
                        <span class="hash-value" id="passwordHashValue">---</span>
                    </div>

                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarUsuario">Guardar Usuario</button>
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
                <div class="delete-warning" style="background: #fff3f3; border: 1px solid #f5c6cb; border-radius: 10px; padding: 1rem;">
                    <p class="mb-1 fw-medium" style="color: var(--danger);">Esta acción no se puede deshacer.</p>
                    <p class="mb-0 small text-muted">Se eliminará permanentemente al usuario <strong id="deleteUserName"></strong>.</p>
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
                <h5 class="modal-title">Detalle del Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <h6 style="color: var(--primary-dark); font-weight: 600; margin-bottom: 1rem;">Datos del Trabajador</h6>
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">Cédula</div><div class="detail-value" id="detailCedula">-</div></div>
                    <div class="detail-item"><div class="detail-label">Nombre Completo</div><div class="detail-value" id="detailNombre">-</div></div>
                    <div class="detail-item"><div class="detail-label">Departamento</div><div class="detail-value" id="detailDepartamento">-</div></div>
                    <div class="detail-item"><div class="detail-label">Cargo</div><div class="detail-value" id="detailCargo">-</div></div>
                    <div class="detail-item"><div class="detail-label">Especialidad</div><div class="detail-value" id="detailEspecialidad">-</div></div>
                    <div class="detail-item"><div class="detail-label">Teléfono</div><div class="detail-value" id="detailTelefono">-</div></div>
                    <div class="detail-item"><div class="detail-label">Email</div><div class="detail-value" id="detailEmail">-</div></div>
                </div>
                <hr>
                <h6 style="color: var(--primary-dark); font-weight: 600; margin-bottom: 1rem;">Datos de Acceso</h6>
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">Usuario</div><div class="detail-value" id="detailUsuario">-</div></div>
                    <div class="detail-item"><div class="detail-label">Rol</div><div class="detail-value" id="detailRol">-</div></div>
                    <div class="detail-item"><div class="detail-label">Estado</div><div class="detail-value" id="detailStatus">-</div></div>
                    <div class="detail-item"><div class="detail-label">Último Ingreso</div><div class="detail-value" id="detailUltimoLogin">-</div></div>
                    <div class="detail-item"><div class="detail-label">Creado</div><div class="detail-value" id="detailCreado">-</div></div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal" style="color: #fff;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- DATOS INICIALES PARA EL JS --}}
<script>
    window.usuariosIniciales = @json($usuarios);
</script>

@endsection

@section('scripts')
    @vite(['resources/js/admin-usuarios.js'])
@endsection