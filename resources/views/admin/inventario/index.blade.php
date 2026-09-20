@extends('layouts.dashboard')

@section('title', 'Gestión de Inventario')

@section('styles')
    @vite(['resources/css/admin-inventario.css'])
@endsection

@section('content')
<div class="container-fluid px-4">

    {{-- ========== HEADER ========== --}}
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M20 7h-4.18A3 3 0 0 0 14 5.18V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v1.18A3 3 0 0 0 4.18 7H0v13a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7z"/>
                    <circle cx="12" cy="14" r="2"/>
                </svg>
                Gestión de Inventario
            </h4>
            <p>Administra todos los activos y componentes tecnológicos</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-light" id="btnAgregarActivo" onclick="window.abrirModalActivo && window.abrirModalActivo(); return false;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nuevo Activo
            </button>
            <button type="button" class="btn btn-light" id="btnAgregarComponente" onclick="window.abrirModalComponente && window.abrirModalComponente(); return false;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nuevo Componente
            </button>
        </div>
    </div>

    {{-- ========== STATS ========== --}}
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalActivos ?? 0 }}</div>
                <div class="stat-label">Total Activos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalComponentes ?? 0 }}</div>
                <div class="stat-label">Total Componentes</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" style="color: {{ ($activosPrestados ?? 0) > 0 ? '#f6c23e' : '#1e3c72' }};">{{ $activosPrestados ?? 0 }}</div>
                <div class="stat-label">Activos Prestados</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(246, 194, 62, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f6c23e" stroke-width="1.8">
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $componentesBodega ?? 0 }}</div>
                <div class="stat-label">Componentes en Bodega</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(30, 126, 52, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1e7e34" stroke-width="1.8">
                    <path d="M20 7h-4.18A3 3 0 0 0 14 5.18V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v1.18A3 3 0 0 0 4.18 7H0v13a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ========== TABS ========== --}}
    <ul class="nav nav-tabs-custom" id="inventarioTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
                Activos
                <span class="tab-badge" id="badgeActivos">{{ $totalActivos ?? 0 }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="componentes-tab" data-bs-toggle="tab" data-bs-target="#componentes" type="button" role="tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                Componentes
                <span class="tab-badge" id="badgeComponentes">{{ $totalComponentes ?? 0 }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- ========== TAB ACTIVOS ========== --}}
        <div class="tab-pane fade show active" id="activos" role="tabpanel">
            <div class="filters-bar">
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </span>
                    <input type="text" class="form-control" id="buscarActivos" placeholder="Buscar por serial, modelo, marca...">
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="filtroEstadoActivos" style="width: 160px;">
                        <option value="">Todos los estados</option>
                        @foreach($estatusList as $estatus)
                            <option value="{{ $estatus->descripcion }}">{{ $estatus->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="table-container">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Serial</th>
                            <th>Modelo</th>
                            <th>Marca</th>
                            <th>Estado</th>
                            <th>Ubicación</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaActivos">
                        <tr><td colspan="6" class="text-center py-4 text-muted">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="paginacionActivos"></div>
        </div>

        {{-- ========== TAB COMPONENTES ========== --}}
        <div class="tab-pane fade" id="componentes" role="tabpanel">
            <div class="filters-bar">
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </span>
                    <input type="text" class="form-control" id="buscarComponentes" placeholder="Buscar por tipo, marca, serial...">
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="filtroTipoComponentes" style="width: 160px;">
                        <option value="">Todos los tipos</option>
                    </select>
                    <select class="form-select form-select-sm" id="filtroEstadoComponentes" style="width: 160px;">
                        <option value="">Todos los estados</option>
                        @foreach($estadosComponentes as $estado)
                            <option value="{{ $estado['valor'] }}">{{ $estado['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="table-container">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Marca</th>
                            <th>Serial</th>
                            <th>Capacidad</th>
                            <th>Estado</th>
                            <th>Activo</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaComponentes">
                        <tr><td colspan="7" class="text-center py-4 text-muted">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="paginacionComponentes"></div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODALES --}}
{{-- ============================================================ --}}

{{-- Modal Activo --}}
<div class="modal fade" id="modalActivo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalActivoLabel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                    Nuevo Activo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formActivo">
                @csrf
                <input type="hidden" id="activoId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Serial <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="activo_serial" name="serial" required>
                            <div id="serialFeedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Modelo <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="activo_modelo_buscar" placeholder="Buscar modelo..." autocomplete="off" oninput="filtrarModelos()">
                                <input type="hidden" id="activo_modelo_id" name="modelo_id">
                                <div id="modeloDropdown" class="list-group" style="position: absolute; z-index: 1000; width: 100%; display: none; max-height: 200px; overflow-y: auto;"></div>
                            </div>
                            <div id="modeloInfoBadges" class="mt-1"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Institución <span class="text-danger">*</span></label>
                            <select class="form-select" id="activo_institucion_id" name="institucion_id" required>
                                <option value="">Seleccionar institución...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Departamento</label>
                            <select class="form-select" id="activo_departamento_id" name="departamento_id">
                                <option value="">Sin departamento</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estatus <span class="text-danger">*</span></label>
                            <select class="form-select" id="activo_id_estatus" name="id_estatus" required>
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Responsable (auto)</label>
                            <div id="activo_responsable_display" class="form-control bg-light" style="min-height: 42px; padding: 0.55rem 0.85rem;">
                                <span class="text-muted">Selecciona una institución</span>
                            </div>
                            <input type="hidden" id="activo_responsable_id" name="responsable_id">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="activo_ubicacion" name="ubicacion">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Fecha Adquisición</label>
                            <input type="date" class="form-control" id="activo_fecha_adquisicion" name="fecha_adquisicion">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Vida Útil (años)</label>
                            <input type="number" class="form-control" id="activo_vida_util_anos" name="vida_util_anos" min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fin de Garantía</label>
                            <input type="date" class="form-control" id="activo_fecha_fin_garantia" name="fecha_fin_garantia">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="activo_observaciones" name="observaciones" rows="2"></textarea>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--primary-dark);">Componentes del Activo</h6>
                        <button type="button" class="btn btn-outline-primary-dark btn-sm" onclick="window.agregarComponenteFormulario()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Agregar Componente
                        </button>
                    </div>
                    <div id="componentesActivoContainer">
                        <p class="text-muted text-center py-3" id="sinComponentes">No hay componentes agregados</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">Guardar Activo</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Componente --}}
<div class="modal fade" id="modalComponente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalComponenteLabel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Nuevo Componente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formComponente">
                @csrf
                <input type="hidden" id="componenteId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="comp_tipo" name="tipo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Serial</label>
                            <input type="text" class="form-control" id="comp_serial" name="serial">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marca</label>
                            <input type="text" class="form-control" id="comp_marca" name="marca">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="comp_modelo" name="modelo">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Capacidad</label>
                            <input type="text" class="form-control" id="comp_capacidad" name="capacidad">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado <span class="text-danger">*</span></label>
                            <select class="form-select" id="comp_estado" name="estado" required>
                                @foreach($estadosComponentes as $estado)
                                    <option value="{{ $estado['valor'] }}">{{ $estado['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Activo (Opcional)</label>
                            <select class="form-select" id="comp_activo_id" name="activo_id">
                                <option value="">Sin activo (en bodega)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Institución <span class="text-danger">*</span></label>
                            <select class="form-select" id="comp_institucion_id" name="institucion_id" required>
                                <option value="">Seleccionar institución...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Departamento</label>
                            <select class="form-select" id="comp_departamento_id" name="departamento_id">
                                <option value="">Sin departamento</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Responsable (auto)</label>
                            <div id="comp_responsable_display" class="form-control bg-light" style="min-height: 42px; padding: 0.55rem 0.85rem;">
                                <span class="text-muted">Selecciona una institución</span>
                            </div>
                            <input type="hidden" id="comp_responsable_id" name="responsable_id">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="comp_ubicacion" name="ubicacion">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="comp_observaciones" name="observaciones" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">Guardar Componente</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Detalle --}}
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleLabel">Detalle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">
                <div class="text-center py-4 text-muted">Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Cambiar Estado --}}
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Activo: <strong id="estadoSerial"></strong></p>
                <p class="mb-3">Estado actual: <span class="badge bg-secondary" id="estadoActual"></span></p>
                <label class="form-label">Nuevo estado</label>
                <select class="form-select" id="nuevoEstadoSelect"></select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary-dark" id="btnConfirmarCambioEstado">Cambiar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Eliminar --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de eliminar <strong id="deleteNombre"></strong>?</p>
                <p class="small text-danger">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

{{-- Contenedor de Notificaciones --}}
<div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 99999; width: 340px;"></div>

@endsection

@section('scripts')
    @vite(['resources/js/admin-inventario.js'])

    <script>
        window.userPermissions = @json(auth()->user()->rol->permisos->pluck('nombre'));

        function authUserHasPermission(permission) {
            return window.userPermissions.includes(permission);
        }
    </script>
@endsection