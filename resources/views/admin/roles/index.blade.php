@extends('layouts.dashboard')

@section('title', 'Roles y Permisos')

@section('styles')
    @vite(['resources/css/admin-roles.css'])
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
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                </svg>
                Roles y Permisos
            </h4>
            <p>Gestión de roles y asignación de permisos del sistema</p>
        </div>
    </div>

    <!-- ========== TARJETAS DE ESTADÍSTICAS ========== -->
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsTotal">{{ $roles->count() ?? 0 }}</div>
                <div class="stat-label">Total Roles</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsPermisos">{{ $totalPermisos ?? 0 }}</div>
                <div class="stat-label">Total Permisos</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(23, 162, 184, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#17a2b8" stroke-width="1.8">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsUsuarios">0</div>
                <div class="stat-label">Usuarios con Roles</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(30, 126, 52, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1e7e34" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- ========== BARRA DE FILTROS ========== -->
    <div class="filters-bar">
        <div class="filtro-busqueda">
            <div class="input-group">
                <span class="input-group-text">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </span>
                <input type="text" class="form-control" id="buscarRol"
                       placeholder="Buscar rol por nombre...">
            </div>
        </div>

        @if(auth()->user()->hasPermission('crear-rol'))
        <button class="btn btn-primary-dark btn-accion" style="color: #fff" onclick="abrirModalRol()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo Rol
        </button>
        @endif
    </div>

    <!-- ========== TABLA ========== -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Descripción</th>
                        <th>Usuarios</th>
                        <th>Permisos</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaRoles">
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Cargando roles...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="paginacionRoles"></div>
    </div>
</div>

<!-- ========== MODAL CREAR/EDITAR ROL ========== -->
<div class="modal fade" id="modalRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRolLabel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                    </svg>
                    Nuevo Rol
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRol">
                @csrf
                <input type="hidden" id="formMethodRol" name="_method" value="POST">
                <input type="hidden" id="rolId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="rol_nombre" name="nombre" required placeholder="Ej: Auditor, Supervisor, Visitante...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="rol_descripcion" name="descripcion" placeholder="Descripción del rol">
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold mb-3" style="color: var(--primary-dark);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        </svg>
                        Permisos del Rol
                    </h6>
                    <p class="text-muted small mb-3">Seleccione los permisos que tendrá este rol:</p>

                    <div id="permisosContainer">
                        <div class="text-center py-4 text-muted">Cargando permisos...</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" style="color: #fff;">Guardar Rol y Permisos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== MODAL DETALLE ========== -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleLabel">Detalle del Rol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">
                <div class="text-center py-4 text-muted">Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal" style="color: #fff;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL ELIMINAR ========== -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de eliminar el rol <strong id="deleteRolNombre"></strong>?</p>
                <p class="small text-danger" id="deleteWarning"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

{{-- DATOS INICIALES PARA EL JS --}}
<script>
    window.rolesIniciales = @json($roles ?? []);
</script>

@endsection

@section('scripts')
    @vite(['resources/js/admin-roles.js'])
@endsection