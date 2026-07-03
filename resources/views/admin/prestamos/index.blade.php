@extends('layouts.dashboard')

@section('title', 'Gestión de Préstamos')

@section('styles')
    @vite(['resources/css/admin-prestamos.css'])
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="color: #1e3c72; font-weight: 700; margin: 0;">Gestión de Préstamos</h4>
            <p style="color: #6c757d; font-size: 0.85rem; margin: 0;">Registro, control y seguimiento de préstamos de equipos y componentes</p>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalPrestamos }}</div>
                <div class="stat-label">Total Préstamos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 22px; height: 22px;">
                    <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $prestamosActivos }}</div>
                <div class="stat-label">Activos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 22px; height: 22px;">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" style="color: {{ $prestamosVencidos > 0 ? '#c5221f' : '#1e3c72' }};">{{ $prestamosVencidos }}</div>
                <div class="stat-label">Vencidos</div>
            </div>
            <div class="stat-icon-circle" style="background: {{ $prestamosVencidos > 0 ? 'rgba(197, 34, 31, 0.1)' : 'rgba(30, 60, 114, 0.1)' }};">
                <svg viewBox="0 0 24 24" stroke="{{ $prestamosVencidos > 0 ? '#c5221f' : '#1e3c72' }}" stroke-width="1.8" fill="none" style="width: 22px; height: 22px;">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $prestamosDevueltos }}</div>
                <div class="stat-label">Finalizados</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 22px; height: 22px;">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs-custom" id="prestamosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="solicitudes-tab" data-bs-toggle="tab" data-bs-target="#solicitudes" type="button" role="tab">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                    <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/>
                </svg>
                Solicitudes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                    <path d="M3 7h18M3 12h18M3 17h18"/>
                </svg>
                Pendientes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                Préstamos Activos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="finalizados-tab" data-bs-toggle="tab" data-bs-target="#finalizados" type="button" role="tab">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>
                </svg>
                Finalizados
            </button>
        </li>
    </ul>

    <div class="tab-content" id="prestamosTabContent">

        <!-- ========== TAB 1: SOLICITUDES ========== -->
        <div class="tab-pane fade show active" id="solicitudes" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="buscarSolicitudes" placeholder="Buscar solicitud..." style="max-width: 300px;">
                </div>
                <button class="btn btn-primary-dark btn-sm" onclick="abrirModalNuevoPrestamo()">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width: 16px; height: 16px; margin-right: 4px;">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo Préstamo
                </button>
            </div>
            <div class="table-container" id="tablaSolicitudes">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>

        <!-- ========== TAB 2: PRÉSTAMOS PENDIENTES ========== -->
        <div class="tab-pane fade" id="pendientes" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="buscarPendientes" placeholder="Buscar préstamo pendiente..." style="max-width: 300px;">
                </div>
            </div>
            <div class="table-container" id="tablaPendientes">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>

        <!-- ========== TAB 3: PRÉSTAMOS ACTIVOS ========== -->
        <div class="tab-pane fade" id="activos" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="buscarActivos" placeholder="Buscar préstamo..." style="max-width: 300px;">
                    <select class="form-select form-select-sm" id="filtroTipoActivos" style="max-width: 160px;">
                        <option value="">Todos los tipos</option>
                        <option value="equipo">Equipo</option>
                        <option value="componente">Componente</option>
                        <option value="mixto">Mixto</option>
                    </select>
                    <select class="form-select form-select-sm" id="filtroEstadoActivos" style="max-width: 160px;">
                        <option value="">Todos los estados</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="entregado">Entregado</option>
                        <option value="extendido">Extendido</option>
                        <option value="vencido">Vencido</option>
                    </select>
                </div>
            </div>
            <div class="table-container" id="tablaActivos">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>

        <!-- ========== TAB 3: FINALIZADOS ========== -->
        <div class="tab-pane fade" id="finalizados" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="buscarFinalizados" placeholder="Buscar préstamo..." style="max-width: 300px;">
                    <select class="form-select form-select-sm" id="filtroEstadoFinalizados" style="max-width: 160px;">
                        <option value="">Todos</option>
                        <option value="devuelto">Devuelto</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                    <input type="date" class="form-control form-control-sm" id="filtroFechaDesde" style="max-width: 150px;" title="Desde">
                    <input type="date" class="form-control form-control-sm" id="filtroFechaHasta" style="max-width: 150px;" title="Hasta">
                </div>
            </div>
            <div class="table-container" id="tablaFinalizados">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODALES -->
<!-- ============================================ -->

<!-- Modal Nuevo Préstamo -->
<div class="modal fade" id="modalPrestamo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="formPrestamo" novalidate>
                @csrf
                <input type="hidden" name="id" id="prestamoId">
                <input type="hidden" name="solicitud_id" id="solicitudIdInput">
                <div class="modal-header bg-primary-dark text-white">
                    <h5 class="modal-title" id="modalPrestamoLabel">Nuevo Préstamo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Columna izquierda -->
                        <div class="col-md-7">
                            <h6 style="color:#1e3c72; font-weight:600; margin-bottom:1rem; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem;">
                                Datos del Préstamo
                            </h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tipoPrestamo" name="tipo_prestamo" required>
                                        <option value="equipo">Equipo</option>
                                        <option value="componente">Componente</option>
                                        <option value="mixto">Mixto</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Solicitud</label>
                                    <input type="text" class="form-control" id="solicitudCodigo" readonly placeholder="Sin solicitud">
                                </div>
                            </div>

                            <div id="solicitudInfoSection" style="display:none;" class="mb-4 border rounded p-3 bg-light">
                                <h6 style="margin-bottom: 1rem; font-weight: 600; color: #1e3c72;">Detalle de la Solicitud</h6>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="fw-semibold">Tipo</div>
                                        <div id="solicitudTipoSolicitante">—</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="fw-semibold">Entidad</div>
                                        <div id="solicitudEntidad">—</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="fw-semibold">Responsable</div>
                                        <div id="solicitudResponsable">—</div>
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <div class="fw-semibold">Prioridad</div>
                                        <div id="solicitudPrioridad">—</div>
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <div class="fw-semibold">Fecha requerida</div>
                                        <div id="solicitudFechaRequerida">—</div>
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <div class="fw-semibold">Fecha fin</div>
                                        <div id="solicitudFechaFin">—</div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="fw-semibold">Justificación</div>
                                        <div id="solicitudJustificacion" class="text-muted">—</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="fw-semibold">Observaciones</div>
                                        <div id="solicitudObservaciones" class="text-muted">—</div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="fw-semibold mb-2">Items solicitados</div>
                                    <ul class="list-group" id="solicitudItemsContainer"></ul>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha de Préstamo <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fechaPrestamo" name="fecha_prestamo" required value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Devolución Esperada <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fechaDevolucionEsperada" name="fecha_devolucion_esperada" required>
                                </div>
                            </div>

                            <hr>

                            <h6 style="color:#1e3c72; font-weight:600; margin-bottom:1rem; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem;">
                                Destino del Préstamo
                            </h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de Destino</label>
                                    <select class="form-select" id="tipoDestino" onchange="cambiarTipoDestino()">
                                        <option value="departamento">Departamento</option>
                                        <option value="institucion">Institución</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3" id="contenedorDepartamento">
                                    <label class="form-label">Departamento <span class="text-danger">*</span></label>
                                    <select class="form-select" id="departamentoId" name="departamento_id" onchange="cargarResponsableDestino()">
                                        <option value="">Seleccionar departamento...</option>
                                        @foreach($departamentos as $depto)
                                            <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3" id="contenedorInstitucion" style="display:none;">
                                    <label class="form-label">Institución <span class="text-danger">*</span></label>
                                    <select class="form-select" id="institucionId" name="institucion_id" onchange="cargarResponsableDestino()">
                                        <option value="">Seleccionar institución...</option>
                                        @foreach($instituciones as $inst)
                                            <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Responsable que Recibe <span class="text-danger">*</span></label>
                                <div class="responsable-display" id="responsableReceptorDisplay">
                                    <span class="text-muted">Seleccione un departamento o institución primero</span>
                                </div>
                                <input type="hidden" name="responsable_receptor_id" id="responsableReceptorId">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Responsable que Entrega (Informática) <span class="text-danger">*</span></label>
                                <select class="form-select" id="responsableEmisorId" name="responsable_emisor_id" required>
                                    <option value="">Seleccionar responsable...</option>
                                    @if($responsableInformatica)
                                        <option value="{{ $responsableInformatica->id }}">{{ $responsableInformatica->nombre }} - {{ $responsableInformatica->cargo }}</option>
                                    @endif
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Condiciones del Préstamo</label>
                                <textarea class="form-control" id="condiciones" name="condiciones" rows="2" placeholder="Condiciones especiales del préstamo..."></textarea>
                            </div>
                        </div>

                        <!-- Columna derecha: Items -->
                        <div class="col-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 style="color:#1e3c72; font-weight:600; margin:0; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem; flex:1;">
                                    Equipos / Componentes
                                </h6>
                                <button type="button" class="btn btn-primary-dark btn-sm" onclick="agregarItem()">
                                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px;margin-right:4px;">
                                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    Agregar
                                </button>
                            </div>

                            <!-- Buscador de items -->
                            <div class="mb-3">
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control form-control-sm" id="buscarItem" placeholder="Buscar activo o componente..." oninput="buscarItemsPrestamo()">
                                    <select class="form-select form-select-sm" id="filtroTipoInventario" onchange="buscarItemsPrestamo()" style="max-width: 160px;">
                                        <option value="ambos">Todos</option>
                                        <option value="activo">Activo</option>
                                        <option value="componente">Componente</option>
                                    </select>
                                </div>
                                <div class="list-group" id="resultadosBusqueda" style="display:none; max-height: 240px; overflow-y: auto; position: absolute; z-index: 2050; left: 0; right: 0; width: auto; background: #fff; box-shadow: 0 6px 18px rgba(0,0,0,0.08);"></div>
                            </div>

                            <!-- Lista de items -->
                            <div id="itemsContainer" style="max-height: 350px; overflow-y: auto;">
                                <p class="text-muted text-center py-3" id="itemsVacio">No hay items agregados</p>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones generales -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label">Observaciones Generales</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2" placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarPrestamo">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:16px;height:16px;margin-right:4px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Guardar Préstamo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalle Préstamo -->
<div class="modal fade" id="modalDetallePrestamo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark text-white">
                <h5 class="modal-title" id="modalDetalleLabel">Detalle de Préstamo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detallePrestamoContenido"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Devolución -->
<div class="modal fade" id="modalDevolucion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formDevolucion" novalidate>
                @csrf
                <input type="hidden" name="prestamo_id" id="devolucionPrestamoId">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Registrar Devolución</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Información del préstamo -->
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between flex-wrap">
                            <span><strong>Código:</strong> <span id="devolucionCodigo">---</span></span>
                            <span><strong>Destino:</strong> <span id="devolucionDestino">---</span></span>
                            <span><strong>Responsable:</strong> <span id="devolucionResponsable">---</span></span>
                        </div>
                    </div>

                    <!-- Fecha de devolución -->
                    <div class="mb-3">
                        <label class="form-label">Fecha de Devolución <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fechaDevolucionReal" name="fecha_devolucion_real" required>
                    </div>

                    <!-- Items a devolver -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Items a devolver</label>
                        <p class="text-muted small">Selecciona los items que se devuelven y su estado actual</p>
                        <div id="itemsDevolucionContainer" class="items-list-container">
                            <div class="text-center py-3 text-muted">Cargando items...</div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label class="form-label">Observaciones de la devolución</label>
                        <textarea class="form-control" id="observacionesDevolucion" name="observaciones" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Registrar Devolución</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Extensión -->
<div class="modal fade" id="modalExtension" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formExtension" novalidate>
                @csrf
                <input type="hidden" name="prestamo_id" id="extensionPrestamoId">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Extender Préstamo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Información del préstamo -->
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between flex-wrap">
                            <span><strong>Código:</strong> <span id="extensionCodigo">---</span></span>
                            <span><strong>Destino:</strong> <span id="extensionDestino">---</span></span>
                            <span><strong>Fecha actual:</strong> <span id="extensionFechaActual">---</span></span>
                            <span><strong>Estado:</strong> <span id="extensionEstado" class="badge bg-warning">---</span></span>
                        </div>
                    </div>

                    <!-- Tipo de extensión -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de Extensión <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="tipoExtensionCompleta" name="tipo" value="completa" checked onchange="toggleTipoExtension()">
                                <label class="form-check-label" for="tipoExtensionCompleta">
                                    <strong>Completa</strong>
                                    <small class="d-block text-muted">Extender todos los items del préstamo</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="tipoExtensionParcial" name="tipo" value="parcial" onchange="toggleTipoExtension()">
                                <label class="form-check-label" for="tipoExtensionParcial">
                                    <strong>Parcial</strong>
                                    <small class="d-block text-muted">Extender solo items específicos</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Items a extender (parcial) -->
                    <div class="mb-3" id="itemsExtensionContainer" style="display:none;">
                        <label class="form-label fw-bold">Selecciona los items a extender</label>
                        <p class="text-muted small">Selecciona uno o más items para extender su fecha de devolución</p>
                        <div id="itemsExtensionList" class="items-list-container">
                            <div class="text-center py-3 text-muted">Cargando items...</div>
                        </div>
                    </div>

                    <!-- Nueva fecha -->
                    <div class="mb-3">
                        <label class="form-label">Nueva Fecha de Devolución <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fechaNuevaExtension" name="fecha_nueva" required>
                        <small class="text-muted">La fecha debe ser posterior a la fecha actual de devolución</small>
                    </div>

                    <!-- Motivo -->
                    <div class="mb-3">
                        <label class="form-label">Motivo de la Extensión <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivoExtension" name="motivo" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Extender Préstamo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cancelar Préstamo -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="formCancelar" novalidate>
                @csrf
                <input type="hidden" name="prestamo_id" id="cancelarPrestamoId">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Cancelar Préstamo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de cancelar este préstamo?</p>
                    <div class="mb-3">
                        <label class="form-label">Motivo de Cancelación <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivoCancelacion" name="motivo" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger">Cancelar Préstamo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Aprobar Préstamo -->
<div class="modal fade" id="modalAprobacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="formAprobacion" novalidate>
                @csrf
                <input type="hidden" name="prestamo_id" id="aprobacionPrestamoId">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Aprobar Préstamo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Deseas aprobar este préstamo?</p>
                    <div class="mb-3">
                        <label class="form-label">Observaciones de aprobación</label>
                        <textarea class="form-control" id="observacionesAprobacion" name="observaciones" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success">Aprobar Préstamo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Rechazar Préstamo -->
<div class="modal fade" id="modalRechazo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="formRechazo" novalidate>
                @csrf
                <input type="hidden" name="prestamo_id" id="rechazoPrestamoId">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Rechazar Préstamo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Deseas rechazar este préstamo?</p>
                    <div class="mb-3">
                        <label class="form-label">Motivo de rechazo <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivoRechazo" name="motivo" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">Rechazar Préstamo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Entregar Préstamo -->
<div class="modal fade" id="modalEntrega" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEntrega" novalidate>
                @csrf
                <input type="hidden" name="prestamo_id" id="entregaPrestamoId">
                <div class="modal-header bg-primary-dark text-white">
                    <h5 class="modal-title">Registrar Entrega</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de préstamo</label>
                            <input type="date" class="form-control" id="fechaEntregaPrestamo" name="fecha_prestamo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de devolución esperada</label>
                            <input type="date" class="form-control" id="fechaEntregaDevolucion" name="fecha_devolucion_esperada" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observacionesEntrega" name="observaciones" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">Registrar Entrega</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    @vite(['resources/js/admin-prestamos.js'])
@endsection
