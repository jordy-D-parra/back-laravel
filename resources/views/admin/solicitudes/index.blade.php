@extends('layouts.dashboard')

@section('title', 'Solicitudes de Préstamo')

@section('styles')
@vite(['resources/css/admin-solicitudes.css'])
@endsection

@section('content')
<div class="container-fluid px-4">

    {{-- ============ HEADER ============ --}}
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M22 7l-10 7L2 7"/>
                </svg>
                Solicitudes de Préstamo
            </h4>
            <p>Gestión de solicitudes y bandeja de correos</p>
        </div>
    </div>

    {{-- ============ STATS ============ --}}
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsTotal">0</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsPendientes">0</div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(246, 194, 62, 0.1); color: #f6c23e;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsAprobadas">0</div>
                <div class="stat-label">Aprobadas</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(30, 126, 52, 0.1); color: #1e7e34;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsRechazadas">0</div>
                <div class="stat-label">Rechazadas</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(197, 34, 31, 0.1); color: #c5221f;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="18" y1="6" x2="6" y2="18"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ============ TABS ============ --}}
    <ul class="nav nav-tabs-custom" id="solicitudesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-solicitudes" data-bs-toggle="tab" data-bs-target="#panel-solicitudes" type="button" role="tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px;">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                </svg>
                Solicitudes
            </button>
        </li>
        @if(auth()->user()->hasPermission('aprobar-solicitudes'))
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-correos" data-bs-toggle="tab" data-bs-target="#panel-correos" type="button" role="tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px;">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M22 7l-10 7L2 7"/>
                </svg>
                Correo de Solicitudes
                <span class="tab-correos-badge" id="tabCorreosBadge" style="display:none;">0</span>
            </button>
        </li>
        @endif
    </ul>

    <div class="tab-content">

        {{-- ============================================ --}}
        {{-- PANEL 1: SOLICITUDES --}}
        {{-- ============================================ --}}
        <div class="tab-pane fade show active" id="panel-solicitudes" role="tabpanel">
            <div class="filters-bar">
                <div class="filtro-busqueda">
                    <div class="input-group">
                        <span class="input-group-text">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar solicitud...">
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if(auth()->user()->hasPermission('crear-solicitud'))
                    <button class="btn btn-primary-dark btn-accion" onclick="abrirModalCrear()" style="color: #fff">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Nueva Solicitud
                    </button>
                    @endif
                </div>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Entidad</th>
                                <th>Responsable</th>
                                <th>Fecha Requerida</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th class="text-center">Items</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody">
                            <tr><td colspan="9" class="text-center py-4 text-muted">Cargando solicitudes...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <div class="text-muted small">
                    Mostrando <span id="resultadosCount">0</span> de <span id="totalRegistrosCount">0</span>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="paginationContainer"></ul>
                </nav>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- PANEL 2: CORREO DE SOLICITUDES --}}
        {{-- ============================================ --}}
        @if(auth()->user()->hasPermission('aprobar-solicitudes'))
        <div class="tab-pane fade" id="panel-correos" role="tabpanel">
            <div class="filters-bar">
                <div class="input-group" style="max-width: 400px;">
                    <span class="input-group-text">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </span>
                    <input type="text" class="form-control" id="buscarCorreo" placeholder="Buscar por remitente, asunto...">
                </div>
                <select class="form-select" id="filtroCorreo" style="max-width: 200px;">
                    <option value="">Todos</option>
                    <option value="no_leidos">No leídos</option>
                    <option value="no_procesados">Sin procesar</option>
                    <option value="procesados">Procesados</option>
                </select>
                <button class="btn btn-primary-dark" onclick="revisarCorreos()" id="btnRevisarCorreos">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:4px;">
                        <polyline points="23 4 23 10 17 10"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                    Revisar ahora
                </button>
            </div>

            <div id="listaCorreos" class="p-3" style="background: white; border-radius: 12px;">
                <div class="text-center py-5 text-muted">
                    <p>Cargando correos...</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: CREAR SOLICITUD --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:8px;">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M22 7l-10 7L2 7"/>
                    </svg>
                    Nueva Solicitud
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCrearSolicitud" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo Solicitante <span class="text-danger">*</span></label>
                            <select name="tipo_solicitante" id="tipoSolicitante" class="form-select" required>
                                <option value="interno">Interno (Departamento)</option>
                                <option value="externo">Externo (Institución)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                            <select name="prioridad" class="form-select" required>
                                <option value="baja">Baja</option>
                                <option value="normal" selected>Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                    </div>

                    <div id="interno-fields">
                        <div class="mb-3">
                            <label class="form-label">Departamento</label>
                            <select name="departamento_id" id="departamentoSelect" class="form-select">
                                <option value="">Seleccionar</option>
                                @foreach($departamentos ?? [] as $departamento)
                                <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                                @endforeach
                                <option value="otro">+ Otro</option>
                            </select>
                        </div>
                    </div>

                    <div id="externo-fields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Institución</label>
                            <select name="institucion_id" id="institucionSelect" class="form-select">
                                <option value="">Seleccionar</option>
                                @foreach($instituciones ?? [] as $institucion)
                                <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                                @endforeach
                                <option value="otro">+ Otra</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Responsable</label>
                        <div class="responsable-display" id="responsableDisplay">
                            <span class="text-muted">Seleccione una opción</span>
                        </div>
                        <input type="hidden" name="responsable_id" id="responsable_id_hidden" value="">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Requerida <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_requerida" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Fin Estimada <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin_estimada" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Justificación <span class="text-danger">*</span></label>
                        <textarea name="justificacion" rows="3" class="form-control" required minlength="20"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" rows="2" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Items <span class="text-danger">*</span></label>
                            <button type="button" id="add-item-modal" class="btn btn-sm btn-outline-primary-dark">+ Agregar</button>
                        </div>
                        <div id="items-container-modal">
                            <div class="item-card">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <select name="items[0][tipo_item]" class="form-select form-select-sm" required>
                                            <option value="activo">Activo</option>
                                            <option value="componente">Componente</option>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="text" name="items[0][item_descripcion]" class="form-control form-control-sm" placeholder="Descripción" required>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="input-group">
                                            <input type="number" name="items[0][cantidad]" class="form-control form-control-sm" value="1" min="1" required>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-modal">×</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: VER DETALLES --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content detalle-modal">
            <div class="detalle-modal-header">
                <div class="detalle-header-left">
                    <div class="detalle-icon-circle">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                    </div>
                    <div>
                        <h5 class="detalle-header-title">Detalle de la Solicitud</h5>
                        <p class="detalle-header-subtitle" id="detalleSubtitulo">Cargando información...</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body detalle-modal-body" id="modalDetallesBody">
                <div class="detalle-loading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Cargando detalles de la solicitud...</p>
                </div>
            </div>
            <div class="detalle-modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px;">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: APROBAR SOLICITUD --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalAprobarSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:8px;">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Aprobar Solicitud
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAprobarSolicitud">
                @csrf
                <input type="hidden" name="id" id="aprobarSolicitudId">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <small class="text-muted">Solicitud</small>
                                <div class="fw-bold" id="aprobarCodigo">---</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Prioridad</small>
                                <div class="fw-bold" id="aprobarPrioridad">---</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Solicitante</small>
                                <div class="fw-bold" id="aprobarSolicitante">---</div>
                            </div>
                        </div>
                    </div>
                    <div id="aprobarAlertaStock" class="alert alert-danger" style="display:none;"></div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Requerida <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="aprobarFechaRequerida" name="fecha_requerida" required>
                            <div id="aprobarFechaAdvertencia" class="text-danger small mt-1" style="display:none;">
                                ⚠️ Esta fecha ya pasó.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Fin Estimada <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="aprobarFechaFin" name="fecha_fin_estimada" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones de aprobación</label>
                        <textarea class="form-control" id="aprobarObservaciones" name="observaciones" rows="3" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnAprobarSolicitud">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:4px;">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Aprobar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: EDITAR SOLICITUD --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalEditarSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:8px;">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Editar Solicitud
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarSolicitud">
                @csrf
                <input type="hidden" name="id" id="editarSolicitudId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                            <select name="prioridad" id="editarPrioridad" class="form-select" required>
                                <option value="baja">Baja</option>
                                <option value="normal">Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado_solicitud" id="editarEstado" class="form-select">
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobada">Aprobada</option>
                                <option value="rechazada">Rechazada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Requerida <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_requerida" id="editarFechaRequerida" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Fin Estimada <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin_estimada" id="editarFechaFin" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Justificación <span class="text-danger">*</span></label>
                        <textarea name="justificacion" id="editarJustificacion" rows="3" class="form-control" required minlength="20"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="editarObservaciones" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarEdicion">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:4px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: ERROR DE STOCK --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalStockError" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:8px;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Stock Insuficiente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold text-danger">No se puede aprobar la solicitud porque no hay stock suficiente.</p>
                <p class="text-muted small">La solicitud será rechazada automáticamente y se notificará al usuario.</p>
                <div class="lista-faltantes" id="listaFaltantes"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: CANCELAR SOLICITUD --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalConfirmacionCancelar" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Cancelar Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>¿Estás seguro de cancelar esta solicitud?</p>
                <small class="text-muted">Esta acción no se puede deshacer</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" id="btnConfirmarCancelar" class="btn btn-danger">Sí, cancelar</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: ELIMINAR SOLICITUD --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalEliminarSolicitud" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Eliminar Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>¿Estás seguro de eliminar la solicitud <strong id="deleteSolicitudNombre"></strong>?</p>
                <small class="text-muted">Esta acción no se puede deshacer</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" id="btnConfirmarEliminarSolicitud" class="btn btn-danger">Sí, eliminar</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: CORREO + WIZARD DE CONVERSIÓN --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalCorreo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 20px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title text-white">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:8px;">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M22 7l-10 7L2 7"/>
                    </svg>
                    Correo Recibido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 2rem; max-height: 80vh; overflow-y: auto;">
                <div id="infoCorreo" class="mb-4">
                    <div class="alert alert-info">
                        <div class="row g-2">
                            <div class="col-md-6"><strong>De:</strong> <span id="correoFrom"></span></div>
                            <div class="col-md-6"><strong>Fecha:</strong> <span id="correoFecha"></span></div>
                            <div class="col-12 mt-2"><strong>Asunto:</strong> <span id="correoAsunto"></span></div>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded" style="white-space: pre-wrap; max-height: 200px; overflow-y: auto;" id="correoCuerpo"></div>
                </div>
                <div id="botonIniciarWizard" class="text-center py-3"></div>
                <div id="wizardContainer" style="display: none;">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <span><span class="step-circle active" id="step1Circle">1</span> Entidad</span>
                            <span><span class="step-circle" id="step2Circle">2</span> Fechas y Datos</span>
                            <span><span class="step-circle" id="step3Circle">3</span> Items</span>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar" id="wizardProgress" style="width: 33%; background: linear-gradient(90deg, #1e3c72, #2a5298);"></div>
                        </div>
                    </div>
                    <form id="formWizard">
                        @csrf
                        <input type="hidden" id="wizardCorreoId">
                        <div class="wizard-step active" id="step1">
                            <h6 class="fw-bold mb-3" style="color: #1e3c72;">Paso 1: Entidad Solicitante</h6>
                            <div class="mb-3">
                                <label class="form-label">Tipo Solicitante <span class="text-danger">*</span></label>
                                <select name="tipo_solicitante" id="wzTipoSolicitante" class="form-select" required>
                                    <option value="interno">Interno (Departamento)</option>
                                    <option value="externo">Externo (Institución)</option>
                                </select>
                            </div>
                            <div id="wzInternoFields">
                                <div class="mb-3">
                                    <label class="form-label">Departamento <span class="text-danger">*</span></label>
                                    <select name="departamento_id" id="wzDepartamento" class="form-select">
                                        <option value="">Seleccionar departamento...</option>
                                        @foreach($departamentos ?? [] as $depto)
                                        <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div id="wzExternoFields" style="display:none;">
                                <div class="mb-3">
                                    <label class="form-label">Institución <span class="text-danger">*</span></label>
                                    <select name="institucion_id" id="wzInstitucion" class="form-select">
                                        <option value="">Seleccionar institución...</option>
                                        @foreach($instituciones ?? [] as $inst)
                                        <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Responsable <span class="text-danger">*</span></label>
                                <select name="responsable_id" id="wzResponsable" class="form-select" required>
                                    <option value="">Seleccionar responsable...</option>
                                    @foreach($responsables ?? [] as $resp)
                                    <option value="{{ $resp->id }}">{{ $resp->nombre }} - {{ $resp->cargo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-primary-dark" onclick="irPaso(2)">Siguiente →</button>
                            </div>
                        </div>
                        <div class="wizard-step" id="step2">
                            <h6 class="fw-bold mb-3" style="color: #1e3c72;">Paso 2: Fechas y Justificación</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Requerida <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_requerida" id="wzFechaRequerida" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Fin Estimada <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_fin_estimada" id="wzFechaFin" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                                <select name="prioridad" id="wzPrioridad" class="form-select" required>
                                    <option value="baja">Baja</option>
                                    <option value="normal">Normal</option>
                                    <option value="alta">Alta</option>
                                    <option value="urgente">Urgente</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Justificación <span class="text-danger">*</span></label>
                                <textarea name="justificacion" id="wzJustificacion" rows="4" class="form-control" required minlength="20"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" id="wzObservaciones" rows="2" class="form-control"></textarea>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-primary-dark" onclick="irPaso(1)">← Anterior</button>
                                <button type="button" class="btn btn-primary-dark" onclick="irPaso(3)">Siguiente →</button>
                            </div>
                        </div>
                        <div class="wizard-step" id="step3">
                            <h6 class="fw-bold mb-3" style="color: #1e3c72;">Paso 3: Items Solicitados</h6>
                            <div class="d-flex justify-content-between mb-3">
                                <p class="text-muted small mb-0">Items detectados automáticamente. Puede editarlos.</p>
                                <button type="button" class="btn btn-sm btn-outline-primary-dark" onclick="agregarItemWizard()">+ Agregar Item</button>
                            </div>
                            <div id="wzItemsContainer"></div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-primary-dark" onclick="irPaso(2)">← Anterior</button>
                                <button type="submit" class="btn btn-success" id="btnGuardarWizard">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:6px;">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                        <polyline points="17 21 17 13 7 13 7 21"/>
                                    </svg>
                                    Crear Solicitud
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Notificaciones --}}
<div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: 320px;"></div>

@endsection

@section('scripts')
<script>
window.userPermissions = @json(auth()->user()->rol->permisos->pluck('nombre'));
window.authUserHasPermission = function(p) {
    return window.userPermissions.includes(p);
};
window.departamentos = @json($departamentos ?? []);
window.instituciones = @json($instituciones ?? []);
window.responsables = @json($responsables ?? []);
</script>
@vite(['resources/js/admin-solicitudes.js'])
@endsection