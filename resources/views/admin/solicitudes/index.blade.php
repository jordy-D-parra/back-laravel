@extends('layouts.dashboard')

@section('title', 'Solicitudes de Préstamo')

@section('styles')
    @vite(['resources/css/admin-solicitudes.css'])
@endsection

@section('content')
<div class="container-fluid px-4">

    <!-- ========== HEADER CON GRADIENTE ========== -->
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M22 7l-10 7L2 7"/>
                </svg>
                Solicitudes de Préstamo
            </h4>
            <p>Gestión de solicitudes de préstamo de equipos y componentes</p>
        </div>
    </div>

    <!-- ========== TARJETAS DE ESTADÍSTICAS ========== -->
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
            <div class="stat-icon-circle" style="background: rgba(246, 194, 62, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f6c23e" stroke-width="1.8">
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
            <div class="stat-icon-circle" style="background: rgba(30, 126, 52, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1e7e34" stroke-width="1.8">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsRechazadas">0</div>
                <div class="stat-label">Rechazadas</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(197, 34, 31, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#c5221f" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="18" y1="6" x2="6" y2="18"/>
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
                <input type="text" class="form-control" id="searchInput"
                       placeholder="Buscar por entidad, justificación..."
                       value="{{ request('search') }}">
            </div>
        </div>

        <!-- Botones a la DERECHA -->
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

    <!-- ========== TABLA ========== -->
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
            <div id="skeletonLoader" style="display: none;">
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
            </div>
        </div>
    </div>

    <!-- ========== PAGINACIÓN ========== -->
    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <div class="text-muted small">
            Mostrando <span id="resultadosCount">0</span> de <span id="totalRegistrosCount">0</span>
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0" id="paginationContainer"></ul>
        </nav>
    </div>
</div>

<!-- ========== MODAL VER DETALLES - DISEÑO PROFESIONAL ========== -->
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            
            <!-- HEADER CON GRADIENTE -->
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 1.5rem 2rem; border-bottom: none;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M22 7l-10 7L2 7"/>
                        </svg>
                    </div>
                    <div>
                        <h5 class="modal-title" style="color: white; font-weight: 700; font-size: 1.2rem; margin: 0;">
                            Detalle de la Solicitud
                        </h5>
                        <span style="color: rgba(255,255,255,0.7); font-size: 0.8rem; display: block; margin-top: 2px;">
                            Información completa de la solicitud
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="opacity: 0.8; transition: opacity 0.2s;"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body" id="modalDetallesBody" style="padding: 2rem; background: #f8fafc; max-height: 80vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Cargando detalles de la solicitud...</p>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer" style="padding: 1.25rem 2rem; background: white; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal" style="padding: 0.5rem 1.5rem; border-radius: 10px; font-weight: 500;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right: 6px;">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* ============================================================
       ESTILOS EXCLUSIVOS PARA EL MODAL DE DETALLE
       ============================================================ */
    
    /* Contenedor principal del detalle */
    .detalle-solicitud-moderno {
        animation: fadeInUp 0.4s ease forwards;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Tarjeta de encabezado con estado */
    .detalle-header-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .detalle-header-card .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .detalle-header-card .header-left .badge-id {
        background: #1e3c72;
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .detalle-header-card .header-left .badge-prioridad {
        padding: 0.25rem 1rem;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .detalle-header-card .header-left .badge-prioridad.baja { background: #e9ecef; color: #495057; }
    .detalle-header-card .header-left .badge-prioridad.normal { background: #d4edda; color: #155724; }
    .detalle-header-card .header-left .badge-prioridad.alta { background: #fff3cd; color: #856404; }
    .detalle-header-card .header-left .badge-prioridad.urgente { background: #f8d7da; color: #721c24; }

    .detalle-header-card .header-right .badge-estado-detalle {
        padding: 0.35rem 1.2rem;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .detalle-header-card .header-right .badge-estado-detalle.pendiente { background: #fff3cd; color: #856404; }
    .detalle-header-card .header-right .badge-estado-detalle.aprobada { background: #d4edda; color: #155724; }
    .detalle-header-card .header-right .badge-estado-detalle.rechazada { background: #f8d7da; color: #721c24; }
    .detalle-header-card .header-right .badge-estado-detalle.cancelada { background: #e2e3e5; color: #383d41; }

    .detalle-header-card .header-right .badge-estado-detalle svg {
        width: 14px;
        height: 14px;
    }

    /* Grid de información */
    .detalle-grid-moderno {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .detalle-grid-moderno .detalle-item-moderno {
        background: white;
        border-radius: 12px;
        padding: 0.85rem 1.2rem;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }

    .detalle-grid-moderno .detalle-item-moderno:hover {
        border-color: #1e3c72;
        box-shadow: 0 2px 12px rgba(30, 60, 114, 0.06);
    }

    .detalle-grid-moderno .detalle-item-moderno .detalle-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.25rem;
    }

    .detalle-grid-moderno .detalle-item-moderno .detalle-label svg {
        width: 14px;
        height: 14px;
        stroke: #94a3b8;
    }

    .detalle-grid-moderno .detalle-item-moderno .detalle-value {
        font-weight: 500;
        color: #0f172a;
        font-size: 0.95rem;
        word-break: break-word;
    }

    .detalle-grid-moderno .detalle-item-moderno .detalle-value .badge-tipo-solicitante {
        font-size: 0.7rem;
        padding: 0.15rem 0.7rem;
        border-radius: 20px;
        font-weight: 500;
    }

    .detalle-grid-moderno .detalle-item-moderno .detalle-value .badge-tipo-solicitante.interno { background: #dbeafe; color: #2563eb; }
    .detalle-grid-moderno .detalle-item-moderno .detalle-value .badge-tipo-solicitante.externo { background: #fef3c7; color: #d97706; }

    /* Sección de justificación */
    .detalle-justificacion {
        background: white;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        border: 1px solid #e9ecef;
        margin-bottom: 1.5rem;
    }

    .detalle-justificacion .justificacion-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.5rem;
    }

    .detalle-justificacion .justificacion-label svg {
        width: 14px;
        height: 14px;
        stroke: #94a3b8;
    }

    .detalle-justificacion .justificacion-texto {
        color: #1a1a1a;
        font-size: 0.95rem;
        line-height: 1.7;
        white-space: pre-line;
    }

    /* Sección de observaciones */
    .detalle-observaciones-moderno {
        background: #fffbf0;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        border: 1px solid #fef3c7;
        margin-bottom: 1.5rem;
    }

    .detalle-observaciones-moderno .obs-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.5rem;
    }

    .detalle-observaciones-moderno .obs-label svg {
        width: 14px;
        height: 14px;
        stroke: #94a3b8;
    }

    .detalle-observaciones-moderno .obs-texto {
        color: #1a1a1a;
        font-size: 0.95rem;
        line-height: 1.7;
        white-space: pre-line;
    }

    /* Tabla de items */
    .detalle-items-container {
        background: white;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        overflow: hidden;
    }

    .detalle-items-container .items-header {
        background: #f8fafc;
        padding: 0.85rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .detalle-items-container .items-header .items-title {
        font-weight: 600;
        color: #1e3c72;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detalle-items-container .items-header .items-title .badge-count {
        background: #1e3c72;
        color: white;
        padding: 0.1rem 0.6rem;
        border-radius: 20px;
        font-size: 0.7rem;
    }

    .detalle-items-container .table-items {
        margin-bottom: 0;
    }

    .detalle-items-container .table-items thead th {
        background: #f8fafc;
        color: #1e3c72;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e9ecef;
        padding: 0.75rem 1.5rem;
    }

    .detalle-items-container .table-items tbody td {
        padding: 0.75rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        color: #1a1a1a;
    }

    .detalle-items-container .table-items tbody tr:hover {
        background: #f8fafc;
    }

    .detalle-items-container .table-items tbody tr:last-child td {
        border-bottom: none;
    }

    .detalle-items-container .table-items .badge-tipo-item {
        font-size: 0.65rem;
        padding: 0.15rem 0.6rem;
        border-radius: 20px;
        font-weight: 500;
    }

    .detalle-items-container .table-items .badge-tipo-item.activo { background: #dbeafe; color: #2563eb; }
    .detalle-items-container .table-items .badge-tipo-item.componente { background: #fef3c7; color: #d97706; }

    /* Ubicación del evento */
    .detalle-ubicacion {
        background: white;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        border: 1px solid #e9ecef;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .detalle-ubicacion .ubicacion-icon {
        width: 44px;
        height: 44px;
        background: rgba(30, 60, 114, 0.08);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .detalle-ubicacion .ubicacion-icon svg {
        stroke: #1e3c72;
        width: 22px;
        height: 22px;
        fill: none;
    }

    .detalle-ubicacion .ubicacion-info {
        flex: 1;
    }

    .detalle-ubicacion .ubicacion-info .ubicacion-label {
        font-size: 0.6rem;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.8px;
        font-weight: 600;
    }

    .detalle-ubicacion .ubicacion-info .ubicacion-texto {
        font-weight: 500;
        color: #0f172a;
        font-size: 0.95rem;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .detalle-grid-moderno {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .detalle-header-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .detalle-header-card .header-left {
            flex-wrap: wrap;
        }

        .detalle-header-card .header-right {
            width: 100%;
        }

        .detalle-items-container .table-items {
            font-size: 0.8rem;
        }

        .detalle-items-container .table-items thead th,
        .detalle-items-container .table-items tbody td {
            padding: 0.5rem 0.75rem;
        }

        .modal-body {
            padding: 1rem !important;
        }

        .detalle-ubicacion {
            flex-direction: column;
            align-items: flex-start;
            text-align: center;
        }

        .detalle-ubicacion .ubicacion-icon {
            align-self: center;
        }
    }

    @media (max-width: 576px) {
        .detalle-header-card .header-left .badge-id {
            font-size: 0.7rem;
            padding: 0.15rem 0.7rem;
        }

        .detalle-header-card .header-left .badge-prioridad {
            font-size: 0.65rem;
            padding: 0.15rem 0.7rem;
        }

        .detalle-grid-moderno .detalle-item-moderno {
            padding: 0.6rem 0.9rem;
        }

        .detalle-grid-moderno .detalle-item-moderno .detalle-value {
            font-size: 0.85rem;
        }

        .detalle-justificacion {
            padding: 0.9rem 1rem;
        }

        .detalle-justificacion .justificacion-texto {
            font-size: 0.85rem;
        }

        .detalle-observaciones-moderno {
            padding: 0.9rem 1rem;
        }
    }
</style>

<script>
// ============================================================
// FUNCIÓN MEJORADA PARA VER DETALLES
// ============================================================

window.verDetalles = async function(id) {
    const modalElement = document.getElementById('modalDetalles');
    if (!modalElement) return;
    
    const modalBody = document.getElementById('modalDetallesBody');
    const modal = new bootstrap.Modal(modalElement);
    
    // Mostrar loading
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted">Cargando detalles de la solicitud...</p>
        </div>
    `;
    
    modal.show();

    try {
        const response = await fetch(`/admin/solicitudes/${id}/detalles`);
        const data = await response.json();
        
        if (!modalBody) return;
        
        if (data.error) {
            modalBody.innerHTML = `
                <div class="text-center py-5 text-danger">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5" style="margin-bottom: 1rem;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p style="font-size: 1.1rem; font-weight: 500;">${data.error}</p>
                </div>
            `;
            return;
        }

        // Formatear fechas
        const fechaSolicitud = data.fecha_solicitud ? new Date(data.fecha_solicitud).toLocaleDateString('es-ES', {
            year: 'numeric', month: 'long', day: 'numeric'
        }) : 'N/A';
        
        const fechaRequerida = data.fecha_requerida ? new Date(data.fecha_requerida).toLocaleDateString('es-ES', {
            year: 'numeric', month: 'long', day: 'numeric'
        }) : 'N/A';
        
        const fechaFin = data.fecha_fin_estimada ? new Date(data.fecha_fin_estimada).toLocaleDateString('es-ES', {
            year: 'numeric', month: 'long', day: 'numeric'
        }) : 'N/A';

        // Obtener nombre de entidad
        let nombreEntidad = 'No especificado';
        let tipoEntidad = '';
        if (data.tipo_solicitante === 'interno' && data.departamento) {
            nombreEntidad = data.departamento.nombre;
            tipoEntidad = 'Departamento';
        } else if (data.tipo_solicitante === 'externo' && data.institucion) {
            nombreEntidad = data.institucion.nombre;
            tipoEntidad = 'Institución';
        }

        const nombreResponsable = data.responsable?.nombre || 'No especificado';
        const ubicacionEvento = data.ubicacion_evento || 'No especificada';

        // Construir HTML del detalle
        let html = `
            <div class="detalle-solicitud-moderno">

                <!-- HEADER CARD -->
                <div class="detalle-header-card">
                    <div class="header-left">
                        <span class="badge-id">#${data.id}</span>
                        <span class="badge-prioridad ${data.prioridad}">
                            ${data.prioridad.charAt(0).toUpperCase() + data.prioridad.slice(1)}
                        </span>
                        <span class="badge-tipo-solicitante ${data.tipo_solicitante}" style="font-size:0.7rem; padding:0.15rem 0.7rem; border-radius:20px; font-weight:500; background: ${data.tipo_solicitante === 'interno' ? '#dbeafe' : '#fef3c7'}; color: ${data.tipo_solicitante === 'interno' ? '#2563eb' : '#d97706'};">
                            ${data.tipo_solicitante === 'interno' ? 'Interno' : 'Externo'}
                        </span>
                    </div>
                    <div class="header-right">
                        <span class="badge-estado-detalle ${data.estado_solicitud}">
                            ${data.estado_solicitud === 'aprobada' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>' : ''}
                            ${data.estado_solicitud === 'pendiente' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' : ''}
                            ${data.estado_solicitud === 'rechazada' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' : ''}
                            ${data.estado_solicitud === 'cancelada' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' : ''}
                            ${data.estado_solicitud.charAt(0).toUpperCase() + data.estado_solicitud.slice(1)}
                        </span>
                    </div>
                </div>

                <!-- UBICACIÓN DEL EVENTO -->
                ${data.lugar_evento || data.estado_id || data.municipio_id || data.parroquia_id ? `
                <div class="detalle-ubicacion">
                    <div class="ubicacion-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div class="ubicacion-info">
                        <div class="ubicacion-label">Ubicación del Evento</div>
                        <div class="ubicacion-texto">${escapeHtml(ubicacionEvento)}</div>
                    </div>
                </div>
                ` : ''}

                <!-- GRID DE INFORMACIÓN -->
                <div class="detalle-grid-moderno">
                    <div class="detalle-item-moderno">
                        <div class="detalle-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Fecha Solicitud
                        </div>
                        <div class="detalle-value">${fechaSolicitud}</div>
                    </div>
                    <div class="detalle-item-moderno">
                        <div class="detalle-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="8" width="16" height="12" rx="1"/><path d="M8 20V8M16 20V8M4 12h16"/></svg>
                            Entidad
                        </div>
                        <div class="detalle-value">
                            ${escapeHtml(nombreEntidad)}
                            <small class="text-muted" style="font-size:0.7rem; display:block; margin-top:2px;">${tipoEntidad}</small>
                        </div>
                    </div>
                    <div class="detalle-item-moderno">
                        <div class="detalle-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Responsable
                        </div>
                        <div class="detalle-value">
                            ${escapeHtml(nombreResponsable)}
                            ${data.responsable?.cargo ? `<small class="text-muted" style="font-size:0.7rem; display:block; margin-top:2px;">${escapeHtml(data.responsable.cargo)}</small>` : ''}
                            ${data.responsable?.telefono ? `<small class="text-muted" style="font-size:0.7rem; display:block; margin-top:2px;">📞 ${escapeHtml(data.responsable.telefono)}</small>` : ''}
                        </div>
                    </div>
                    <div class="detalle-item-moderno">
                        <div class="detalle-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Fechas Requeridas
                        </div>
                        <div class="detalle-value">
                            <div><strong>Requerida:</strong> ${fechaRequerida}</div>
                            <div style="font-size:0.85rem; margin-top:2px;"><strong>Fin estimado:</strong> ${fechaFin}</div>
                        </div>
                    </div>
                </div>

                <!-- JUSTIFICACIÓN -->
                ${data.justificacion ? `
                <div class="detalle-justificacion">
                    <div class="justificacion-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M22 7l-10 7L2 7"/></svg>
                        Justificación
                    </div>
                    <div class="justificacion-texto">${escapeHtml(data.justificacion)}</div>
                </div>
                ` : ''}

                <!-- OBSERVACIONES -->
                ${data.observaciones ? `
                <div class="detalle-observaciones-moderno">
                    <div class="obs-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M22 7l-10 7L2 7"/></svg>
                        Observaciones
                    </div>
                    <div class="obs-texto">${escapeHtml(data.observaciones)}</div>
                </div>
                ` : ''}

                <!-- ITEMS SOLICITADOS -->
                <div class="detalle-items-container">
                    <div class="items-header">
                        <div class="items-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            Items Solicitados
                            <span class="badge-count">${data.detalles?.length || 0}</span>
                        </div>
                    </div>
                    ${data.detalles && data.detalles.length > 0 ? `
                    <div class="table-responsive">
                        <table class="table table-items">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.detalles.map(item => `
                                    <tr>
                                        <td>
                                            <span class="badge-tipo-item ${item.tipo_item}">
                                                ${item.tipo_item === 'activo' ? 'Activo' : 'Componente'}
                                            </span>
                                        </td>
                                        <td>${escapeHtml(item.item_descripcion)}</td>
                                        <td class="text-center"><strong>${item.cantidad_solicitada}</strong></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    ` : `
                    <div class="text-center py-4 text-muted">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" style="margin-bottom: 0.5rem;">
                            <rect x="2" y="6" width="20" height="12" rx="2"/>
                        </svg>
                        <p style="margin: 0;">No hay items registrados</p>
                    </div>
                    `}
                </div>

            </div>
        `;

        modalBody.innerHTML = html;

    } catch (error) {
        console.error('Error:', error);
        modalBody.innerHTML = `
            <div class="text-center py-5 text-danger">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5" style="margin-bottom: 1rem;">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <p style="font-size: 1.1rem; font-weight: 500;">Error al cargar los detalles</p>
                <p class="text-muted">${error.message || 'Intente nuevamente más tarde'}</p>
            </div>
        `;
    }
};

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<!-- ========== MODAL CREAR ========== -->
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
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
                                <option value="normal">Normal</option>
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
                        <div id="departamento-nuevo-field" class="mb-3" style="display: none;">
                            <label class="form-label">Nuevo Departamento</label>
                            <input type="text" name="nuevo_departamento" class="form-control" placeholder="Nombre del departamento">
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
                        <div id="institucion-nuevo-field" class="mb-3" style="display: none;">
                            <label class="form-label">Nueva Institución</label>
                            <input type="text" name="nueva_institucion" class="form-control" placeholder="Nombre de la institución">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Responsable</label>
                        <div class="responsable-display" id="responsableDisplay">
                            <span class="text-muted">Seleccione una opción</span>
                        </div>
                        <input type="hidden" name="responsable_id" id="responsable_id_hidden" value="">
                    </div>

                    <!-- ========== UBICACIÓN DEL EVENTO ========== -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6 style="color:#1e3c72; font-weight:600; margin-bottom:1rem; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                Ubicación del Evento
                            </h6>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado_id" id="solicitud_estado_id" class="form-select">
                                <option value="">Seleccionar estado...</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Municipio</label>
                            <select name="municipio_id" id="solicitud_municipio_id" class="form-select" disabled>
                                <option value="">Seleccionar estado primero</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Parroquia</label>
                            <select name="parroquia_id" id="solicitud_parroquia_id" class="form-select" disabled>
                                <option value="">Seleccionar municipio primero</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Lugar del Evento (Dirección específica)</label>
                            <input type="text" name="lugar_evento" id="solicitud_lugar_evento" class="form-control" placeholder="Ej: Auditorio Principal, Calle 5 con Avenida 3...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Requerida <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_requerida" id="fechaRequeridaInput" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Fin Estimada <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin_estimada" id="fechaFinEstimadaInput" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Justificación <span class="text-danger">*</span></label>
                        <textarea name="justificacion" id="justificacionInput" rows="3" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" rows="2" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adjunto (PDF)</label>
                        <input type="file" name="oficio_adjunto" accept=".pdf,.doc,.docx" class="form-control">
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

<!-- ========== MODAL EDITAR ========== -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Editar Solicitud
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarSolicitud">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo_solicitante" id="editTipoSolicitante" class="form-select" required>
                                <option value="interno">Interno</option>
                                <option value="externo">Externo</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                            <select name="prioridad" id="editPrioridad" class="form-select" required>
                                <option value="baja">Baja</option>
                                <option value="normal">Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                    </div>

                    <div id="editInternoFields">
                        <div class="mb-3">
                            <label class="form-label">Departamento</label>
                            <select name="departamento_id" id="editDepartamentoId" class="form-select">
                                <option value="">Seleccionar</option>
                                @foreach($departamentos ?? [] as $departamento)
                                    <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="editExternoFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Institución</label>
                            <select name="institucion_id" id="editInstitucionId" class="form-select">
                                <option value="">Seleccionar</option>
                                @foreach($instituciones ?? [] as $institucion)
                                    <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Responsable</label>
                        <div class="responsable-display" id="editResponsableDisplay"></div>
                        <input type="hidden" name="responsable_id" id="edit_responsable_id_hidden" value="">
                    </div>

                    <!-- ========== UBICACIÓN DEL EVENTO (EDITAR) ========== -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6 style="color:#1e3c72; font-weight:600; margin-bottom:1rem; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                Ubicación del Evento
                            </h6>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado_id" id="edit_solicitud_estado_id" class="form-select">
                                <option value="">Seleccionar estado...</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Municipio</label>
                            <select name="municipio_id" id="edit_solicitud_municipio_id" class="form-select" disabled>
                                <option value="">Seleccionar estado primero</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Parroquia</label>
                            <select name="parroquia_id" id="edit_solicitud_parroquia_id" class="form-select" disabled>
                                <option value="">Seleccionar municipio primero</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Lugar del Evento (Dirección específica)</label>
                            <input type="text" name="lugar_evento" id="edit_solicitud_lugar_evento" class="form-control" placeholder="Ej: Auditorio Principal, Calle 5 con Avenida 3...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Requerida <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_requerida" id="editFechaRequerida" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Fin <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin_estimada" id="editFechaFin" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Justificación <span class="text-danger">*</span></label>
                        <textarea name="justificacion" id="editJustificacion" rows="3" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="editObservaciones" rows="2" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Items <span class="text-danger">*</span></label>
                            <button type="button" id="add-item-editar" class="btn btn-sm btn-outline-primary-dark">+ Agregar</button>
                        </div>
                        <div id="items-container-editar"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">Actualizar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== MODAL CANCELAR ========== -->
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

<!-- ========== MODAL ELIMINAR ========== -->
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

<!-- ========== NOTIFICACIONES ========== -->
<div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: 320px;"></div>

@endsection

@section('scripts')
    @vite(['resources/js/admin-solicitudes.js'])
    <script>
        window.userPermissions = @json(auth()->user()->rol->permisos->pluck('nombre'));
        function authUserHasPermission(p) { return window.userPermissions.includes(p); }
        window.departamentos = @json($departamentos ?? []);
        window.instituciones = @json($instituciones ?? []);
    </script>
@endsection