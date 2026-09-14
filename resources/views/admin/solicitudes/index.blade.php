@extends('layouts.dashboard')

@section('title', 'Solicitudes de Préstamo')

@section('styles')
@vite(['resources/css/admin-solicitudes.css'])
<style>
    /* ============ SOLICITUDES NO LEÍDAS ============ */
    .solicitud-no-leida {
        background-color: #fff8e1 !important;
        border-left: 4px solid #ffc107 !important;
    }
    .badge-nueva {
        background: #dc3545;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.65rem;
        font-weight: 700;
        margin-left: 6px;
        animation: pulseBadge 1.5s infinite;
    }
    @keyframes pulseBadge {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    /* ============ CORREOS ============ */
    .correo-item {
        background: white;
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 10px;
        border: 1px solid #e9ecef;
        cursor: pointer;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    .correo-item:hover {
        background: #f8f9fc;
        transform: translateX(4px);
        border-left-color: #1e3c72;
    }
    .correo-item.no-leido {
        background: #f0f4ff;
        border-left-color: #1e3c72;
    }
    .correo-item.procesado {
        opacity: 0.7;
        background: #f8f9fa;
    }
    .tab-correos-badge {
        background: #dc3545 !important;
        color: white !important;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        margin-left: 6px;
    }

    /* ============ WIZARD ============ */
    .wizard-step { display: none; }
    .wizard-step.active {
        display: block;
        animation: fadeInUp 0.3s ease;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .step-circle {
        display: inline-block;
        width: 32px; height: 32px;
        line-height: 32px;
        text-align: center;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        font-weight: 700;
        margin-right: 8px;
        transition: all 0.3s ease;
    }
    .step-circle.active {
        background: #1e3c72;
        color: white;
        box-shadow: 0 0 0 4px rgba(30, 60, 114, 0.15);
    }
    .step-circle.completed {
        background: #1e7e34;
        color: white;
    }

    /* ============ STOCK ERROR ============ */
    .lista-faltantes {
        background: #fff5f5;
        border-left: 4px solid #dc3545;
        padding: 12px 16px;
        border-radius: 8px;
        margin: 12px 0;
    }
    .lista-faltantes .item-faltante {
        padding: 8px 0;
        border-bottom: 1px dashed #f5c6cb;
        font-size: 0.9rem;
    }
    .lista-faltantes .item-faltante:last-child {
        border-bottom: none;
    }
    .item-faltante .nombre {
        font-weight: 600;
        color: #721c24;
    }
    .item-faltante .detalles {
        font-size: 0.8rem;
        color: #856404;
        margin-top: 4px;
    }

    /* ============ PAGINACIÓN ============ */
    .pagination .page-link {
        cursor: pointer;
        color: #1e3c72;
        border: 1px solid #dee2e6;
        padding: 0.375rem 0.75rem;
        transition: all 0.15s ease;
    }
    .pagination .page-link:hover {
        background-color: #eef2ff;
        color: #1e3c72;
        border-color: #1e3c72;
    }
    .pagination .page-item.active .page-link {
        background-color: #1e3c72;
        border-color: #1e3c72;
        color: white;
        font-weight: 600;
    }
    .pagination .page-item.disabled .page-link {
        color: #adb5bd;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* ============================================================
       MODAL DETALLES - DISEÑO PROFESIONAL
       ============================================================ */
    .detalle-modal {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
    }

    /* HEADER */
    .detalle-modal-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .detalle-modal-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .detalle-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
        z-index: 1;
    }

    .detalle-icon-circle {
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .detalle-header-title {
        color: white;
        font-weight: 700;
        font-size: 1.15rem;
        margin: 0;
        letter-spacing: 0.3px;
    }

    .detalle-header-subtitle {
        color: rgba(255, 255, 255, 0.75);
        font-size: 0.82rem;
        margin: 0;
        margin-top: 2px;
    }

    /* BODY */
    .detalle-modal-body {
        padding: 2rem;
        background: #f7f9fc;
        max-height: 70vh;
        overflow-y: auto;
    }

    .detalle-loading {
        text-align: center;
        padding: 4rem 1rem;
    }

    /* SECCIONES */
    .detalle-section {
        margin-bottom: 1.5rem;
    }

    .detalle-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        margin-bottom: 0.85rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .detalle-section-title svg {
        color: #1e3c72;
    }

    /* HEADER CARD */
    .detalle-hero-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .detalle-hero-left {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .detalle-hero-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e3c72;
        font-size: 1.5rem;
        font-weight: 800;
    }

    .detalle-hero-info h3 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.3px;
    }

    .detalle-hero-info p {
        margin: 0;
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 2px;
    }

    .detalle-hero-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .detalle-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .detalle-badge.prioridad-baja    { background: #f1f5f9; color: #475569; }
    .detalle-badge.prioridad-normal  { background: #dbeafe; color: #1e40af; }
    .detalle-badge.prioridad-alta    { background: #fef3c7; color: #92400e; }
    .detalle-badge.prioridad-urgente { background: #fee2e2; color: #991b1b; }

    .detalle-badge.estado-pendiente  { background: #fef3c7; color: #92400e; }
    .detalle-badge.estado-aprobada   { background: #d1fae5; color: #065f46; }
    .detalle-badge.estado-rechazada  { background: #fee2e2; color: #991b1b; }
    .detalle-badge.estado-cancelada  { background: #f1f5f9; color: #475569; }

    /* GRID DE INFO */
    .detalle-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
    }

    .detalle-info-card {
        background: white;
        border-radius: 12px;
        padding: 1rem 1.15rem;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .detalle-info-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .detalle-info-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .detalle-info-label svg {
        width: 13px;
        height: 13px;
    }

    .detalle-info-value {
        font-size: 0.92rem;
        font-weight: 600;
        color: #1e293b;
        word-break: break-word;
        line-height: 1.4;
    }

    .detalle-info-value.muted {
        color: #94a3b8;
        font-weight: 500;
        font-style: italic;
    }

    /* JUSTIFICACIÓN */
    .detalle-justificacion {
        background: white;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #1e3c72;
        color: #334155;
        line-height: 1.65;
        font-size: 0.92rem;
        white-space: pre-wrap;
    }

    /* TIMELINE FECHAS */
    .detalle-timeline {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .detalle-timeline-item {
        background: white;
        border-radius: 12px;
        padding: 1.15rem;
        border: 1px solid #e2e8f0;
        position: relative;
        overflow: hidden;
    }

    .detalle-timeline-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #1e3c72;
    }

    .detalle-timeline-item.fecha-solicitud::before  { background: #64748b; }
    .detalle-timeline-item.fecha-requerida::before  { background: #f59e0b; }
    .detalle-timeline-item.fecha-fin::before        { background: #10b981; }

    .detalle-timeline-date {
        font-size: 1.05rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 2px;
    }

    .detalle-timeline-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* TABLA DE ITEMS */
    .detalle-items-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        width: 100%;
        border-collapse: collapse;
    }

    .detalle-items-table thead {
        background: #f8fafc;
    }

    .detalle-items-table th {
        padding: 12px 16px;
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    .detalle-items-table th.text-center { text-align: center; }

    .detalle-items-table td {
        padding: 14px 16px;
        font-size: 0.9rem;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .detalle-items-table tr:last-child td { border-bottom: none; }

    .detalle-items-table tr:hover td { background: #f8fafc; }

    .detalle-items-table td.text-center { text-align: center; }

    .detalle-item-tipo {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .detalle-item-tipo.activo     { background: #dbeafe; color: #1e40af; }
    .detalle-item-tipo.componente { background: #ede9fe; color: #5b21b6; }

    .detalle-item-cantidad {
        display: inline-block;
        min-width: 36px;
        padding: 5px 12px;
        border-radius: 8px;
        background: #1e3c72;
        color: white;
        font-weight: 700;
        font-size: 0.85rem;
    }

    /* EMPTY STATE */
    .detalle-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: #94a3b8;
        background: white;
        border-radius: 12px;
        border: 1px dashed #e2e8f0;
    }

    .detalle-empty svg {
        margin-bottom: 10px;
        opacity: 0.4;
    }

    /* FOOTER */
    .detalle-modal-footer {
        padding: 1rem 2rem;
        background: white;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* SCROLLBAR PERSONALIZADA */
    .detalle-modal-body::-webkit-scrollbar {
        width: 8px;
    }
    .detalle-modal-body::-webkit-scrollbar-track {
        background: transparent;
    }
    .detalle-modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .detalle-modal-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* RESPONSIVE */
    @media (max-width: 576px) {
        .detalle-modal-header {
            padding: 1.15rem 1.25rem;
        }
        .detalle-modal-body {
            padding: 1.25rem;
        }
        .detalle-hero-card {
            padding: 1.15rem;
        }
        .detalle-hero-info h3 {
            font-size: 1.1rem;
        }
        .detalle-modal-footer {
            padding: 1rem 1.25rem;
        }
    }
</style>
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsPendientes">0</div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(246, 194, 62, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f6c23e" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsAprobadas">0</div>
                <div class="stat-label">Aprobadas</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(30, 126, 52, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1e7e34" stroke-width="1.8"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsRechazadas">0</div>
                <div class="stat-label">Rechazadas</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(197, 34, 31, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#c5221f" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
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
{{-- MODAL: VER DETALLES (REDISEÑADO) --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content detalle-modal">

            {{-- HEADER --}}
            <div class="detalle-modal-header">
                <div class="detalle-header-left">
                    <div class="detalle-icon-circle">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <div>
                        <h5 class="detalle-header-title">Detalle de la Solicitud</h5>
                        <p class="detalle-header-subtitle" id="detalleSubtitulo">Cargando información...</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body detalle-modal-body" id="modalDetallesBody">
                <div class="detalle-loading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Cargando detalles de la solicitud...</p>
                </div>
            </div>

            {{-- FOOTER --}}
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
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
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

                {{-- WIZARD --}}
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

                        {{-- PASO 1 --}}
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

                        {{-- PASO 2 --}}
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

                        {{-- PASO 3 --}}
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
    // ============================================================
    // PERMISOS Y DATOS INICIALES (deben definirse ANTES del JS externo)
    // ============================================================
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