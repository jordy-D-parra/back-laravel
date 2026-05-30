@extends('layouts.dashboard')

@section('title', 'Prestamo')

@section('styles')
    @vite(['resources/css/admin-inventario.css'])
    @vite(['resources/css/contrast-system.css'])
    @vite(['resources/css/smooth-modals.css'])
    <style>
        /* Copiado de inventario, adaptado a estructura de préstamos */
        .bg-primary-dark { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); }
        .modal-header.bg-primary-dark .btn-close { filter: brightness(0) invert(1); }
        .badge-pendiente    { background: #ffc107; color: #000; }
        .badge-entregado    { background: #198754; color: #fff; }
        .badge-vencido      { background: #dc3545; color: #fff; }
        .badge-devuelto     { background: #0dcaf0; color: #000; }
        .modelo-info-badges { display: flex; gap: 0.5rem; margin-top: 0.25rem; flex-wrap: wrap; }
        .stat-icon-circle svg { width: 24px; height: 24px; stroke: #1e3c72; stroke-width: 1.8; fill: none; }
        .stat-card-mini:hover .stat-icon-circle svg { stroke: white; }
        .btn-cambiar-estado {
            background: transparent;
            border: 1px solid #ffc107;
            color: #856404;
            transition: all 0.2s ease;
        }
        .btn-cambiar-estado:hover {
            background: #ffc107;
            color: #1a1a1a;
            transform: scale(1.02);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #1e3c72;">Préstamos</h3>
            <p class="text-muted mb-0">Gestión de préstamos de activos y componentes</p>
        </div>
        @if(auth()->user()->hasPermission('crear-prestamo'))
        <button class="btn btn-primary-dark" onclick="abrirModalPrestamo()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo Préstamo
        </button>
        @endif
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-row mb-4">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">0</div>
                <div class="stat-label">Préstamos Activos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                    <line x1="9" y1="4" x2="9" y2="20"/>
                    <line x1="15" y1="4" x2="15" y2="20"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">0</div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="6" width="20" height="12" rx="2" ry="2"/>
                    <line x1="9" y1="6" x2="9" y2="18"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">0</div>
                <div class="stat-label">Vencidos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/>
                    <polyline points="3 9 12 13 21 9"/>
                    <path d="M12 13v9"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">0</div>
                <div class="stat-label">Devueltos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="8.5" cy="7" r="4"/>
                    <line x1="17" y1="11" x2="22" y2="9"/>
                    <line x1="22" y1="9" x2="17" y2="11"/>
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters-bar mb-3">
        <div class="flex-grow-1">
            <input type="text" id="buscarPrestamos" class="form-control" placeholder="Buscar por responsable, activo...">
        </div>
        <select id="filtroEstadoPrestamo" class="form-select" style="width:160px">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="entregado">Entregado</option>
            <option value="vencido">Vencido</option>
            <option value="devuelto">Devuelto</option>
        </select>
        @if(auth()->user()->hasPermission('crear-prestamo'))
        <button class="btn btn-primary-dark" onclick="abrirModalPrestamo()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo
        </button>
        @endif
    </div>

    <div class="table-container">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Responsable</th>
                    <th>Activo</th>
                    <th>Fecha Salida</th>
                    <th>Fecha Devolución</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaPrestamos">
                <tr><td colspan="7" class="text-center py-4 text-muted">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL PRÉSTAMO -->
<div class="modal fade" id="modalPrestamo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalPrestamoLabel">Nuevo Préstamo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPrestamo">
                @csrf
                <input type="hidden" id="prestamoId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Responsable <span class="text-danger">*</span></label>
                            <select class="form-select" id="prestamo_responsable_id" name="responsable_id" required>
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Activo <span class="text-danger">*</span></label>
                            <select class="form-select" id="prestamo_activo_id" name="activo_id" required>
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Salida</label>
                            <input type="date" class="form-control" id="prestamo_fecha_salida" name="fecha_salida">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Devolución</label>
                            <input type="date" class="form-control" id="prestamo_fecha_devolucion" name="fecha_devolucion">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" id="prestamo_estado" name="estado">
                            <option value="pendiente">Pendiente</option>
                            <option value="entregado">Entregado</option>
                            <option value="vencido">Vencido</option>
                            <option value="devuelto">Devuelto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" id="prestamo_observaciones" name="observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                        </svg>
                        Guardar Préstamo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DETALLE -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalDetalleLabel">Detalle Préstamo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">Cargando...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Eliminar este préstamo?</p>
                <p class="fw-bold text-danger" id="deleteNombre"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        // Permisos del usuario desde el backend
        window.userPermissions = @json(auth()->user()->rol->permisos->pluck('nombre'));

        function authUserHasPermission(permiso) {
            return window.userPermissions.includes(permiso);
        }
    </script>
@endsection
