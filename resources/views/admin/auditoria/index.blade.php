@extends('layouts.dashboard')

@section('title', 'Bitácora de Auditoría')

@section('styles')
<style>
    .badge-accion {
        font-size: 0.7rem;
        padding: 0.25rem 0.7rem;
        border-radius: 20px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .badge-accion.crear { background: #d4edda; color: #155724; }
    .badge-accion.editar { background: #cce5ff; color: #004085; }
    .badge-accion.eliminar { background: #f8d7da; color: #721c24; }
    .badge-accion.cambio_estado { background: #fff3cd; color: #856404; }
    .badge-accion.login { background: #d1ecf1; color: #0c5460; }
    .badge-accion.logout { background: #e2e3e5; color: #383d41; }
    .badge-accion.login_fallido { background: #f8d7da; color: #721c24; }

    .badge-modulo {
        font-size: 0.65rem;
        padding: 0.2rem 0.6rem;
        border-radius: 12px;
        background: #e9ecef;
        color: #495057;
    }

    .filters-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        background: white;
        border-radius: 12px;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #e9ecef;
    }

    .filtro-busqueda {
        flex: 1;
        min-width: 200px;
        max-width: 350px;
    }

    .filtro-busqueda .input-group-text {
        background: white;
        border-right: none;
        color: #6c757d;
    }

    .filtro-busqueda .form-control {
        border-left: none;
        font-size: 0.85rem;
        padding: 0.45rem 0.75rem;
    }

    .filtro-busqueda .form-control:focus {
        border-color: #1e3c72;
        box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
    }

    .btn-primary-dark {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border: none;
        color: white;
        font-weight: 500;
        padding: 0.45rem 1.2rem;
        border-radius: 10px;
        font-size: 0.8rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-primary-dark:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(30, 60, 114, 0.3);
        color: white;
    }

    .btn-outline-primary-dark {
        background: transparent;
        border: 1.5px solid #1e3c72;
        color: #1e3c72;
        font-weight: 500;
        padding: 0.4rem 1rem;
        border-radius: 10px;
        font-size: 0.8rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-outline-primary-dark:hover {
        background: #1e3c72;
        color: white;
        transform: translateY(-2px);
    }

    .btn-action {
        padding: 0.3rem 0.6rem;
        font-size: 0.7rem;
        border-radius: 8px;
        transition: all 0.2s ease;
        background: transparent;
        border: none;
        color: #6c757d;
        cursor: pointer;
    }

    .btn-action:hover {
        background: #eef3fc;
        color: #1e3c72;
        transform: scale(1.05);
    }

    .table-container {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border: 1px solid #e9ecef;
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
        font-size: 0.85rem;
    }

    .table thead th {
        background: #f8f9fc;
        color: #1e3c72;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        border-bottom: 2px solid #1e3c72;
        padding: 0.9rem 0.75rem;
        white-space: nowrap;
    }

    .table tbody td {
        vertical-align: middle;
        padding: 0.85rem 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }

    .table-hover tbody tr:hover {
        background-color: #eef3fc;
        transition: background 0.15s ease;
    }

    .page-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 16px;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-header h4 {
        color: white;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-header h4 svg {
        stroke: white;
    }

    .page-header p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.85rem;
        margin: 0;
    }

    .pagination-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .pagination-info { font-size: 0.8rem; color: #6c757d; }

    .modal-content {
        border-radius: 1rem;
        border: none;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 1.25rem 1.75rem;
        border-bottom: none;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .modal-header .btn-close:hover {
        opacity: 1;
    }

    .modal-title {
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modal-title svg {
        stroke: white;
    }

    .modal-body {
        padding: 1.5rem;
        max-height: 80vh;
        overflow-y: auto;
    }

    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f8f9fc;
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #1e3c72;
        border-radius: 3px;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 1rem 1.75rem;
        background: #f8f9fc;
    }

    .modal-footer .btn {
        border-radius: 10px;
        padding: 0.5rem 1.25rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .modal-footer .btn:hover {
        transform: translateY(-1px);
    }

    .form-label {
        font-weight: 600;
        font-size: 0.8rem;
        color: #1e3c72;
        margin-bottom: 0.3rem;
    }

    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid #e9ecef;
        padding: 0.55rem 0.85rem;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #1e3c72;
        box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.12);
    }

    @media (max-width: 768px) {
        .filters-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .filtro-busqueda {
            max-width: 100%;
            min-width: unset;
        }
        .filters-bar .d-flex {
            flex-direction: column;
            width: 100%;
        }
        .filters-bar .form-control,
        .filters-bar .form-select {
            width: 100%;
        }
        .page-header {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        .table thead {
            display: none;
        }
        .table tbody td {
            display: block;
            text-align: right;
            padding-left: 50%;
            position: relative;
        }
        .table tbody td::before {
            content: attr(data-label);
            position: absolute;
            left: 0.75rem;
            width: 45%;
            text-align: left;
            font-weight: 600;
            color: #1e3c72;
            font-size: 0.7rem;
        }
        .table tbody tr {
            margin-bottom: 1rem;
            display: block;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 0.5rem 0;
            background: white;
        }
        .table tbody td:last-child {
            border-bottom: none;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">

    <!-- ========== HEADER ========== -->
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                </svg>
                Bitácora de Auditoría
            </h4>
            <p>Registro detallado de todas las acciones realizadas en el sistema</p>
        </div>
        <div>
            <span class="badge bg-light text-dark me-2">Total: {{ $totalRegistros }}</span>
            <span class="badge bg-info text-white me-2">Hoy: {{ $hoy }}</span>
            <span class="badge bg-primary text-white me-2">Semana: {{ $semana }}</span>
            <span class="badge bg-success text-white">Mes: {{ $mes }}</span>
        </div>
    </div>

    <!-- ========== FILTROS ========== -->
    <div class="filters-bar">
        <div class="filtro-busqueda">
            <div class="input-group">
                <span class="input-group-text">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </span>
                <input type="text" class="form-control" id="buscarAuditoria"
                       placeholder="Buscar por usuario, módulo, acción, IP..."
                       value="{{ request('buscar') }}">
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <select class="form-select form-select-sm" id="filtroModulo" style="width:150px;">
                <option value="">Todos los módulos</option>
                @foreach($modulos as $modulo)
                    <option value="{{ $modulo }}" {{ request('modulo') == $modulo ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $modulo)) }}</option>
                @endforeach
            </select>
            <select class="form-select form-select-sm" id="filtroAccion" style="width:150px;">
                <option value="">Todas las acciones</option>
                @foreach($acciones as $accion)
                    <option value="{{ $accion }}" {{ request('accion') == $accion ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $accion)) }}</option>
                @endforeach
            </select>
            <input type="date" class="form-control form-control-sm" id="filtroFechaDesde" style="width:150px;" value="{{ request('fecha_desde') }}">
            <input type="date" class="form-control form-control-sm" id="filtroFechaHasta" style="width:150px;" value="{{ request('fecha_hasta') }}">
            <button class="btn btn-sm btn-outline-primary-dark" id="limpiarFiltros">Limpiar</button>
        </div>
    </div>

    <!-- ========== TABLA ========== -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th style="width:170px;">Fecha</th>
                        <th style="width:130px;">Usuario</th>
                        <th style="width:130px;">Módulo</th>
                        <th style="width:110px;">Acción</th>
                        <th>Descripción</th>
                        <th style="width:130px;">IP</th>
                        <th style="width:60px;">Detalle</th>
                    </tr>
                </thead>
                <tbody id="tablaAuditoria">
                    @forelse($auditoria as $item)
                    <tr>
                        <td>{{ $loop->iteration + ($auditoria->currentPage() - 1) * $auditoria->perPage() }}</td>
                        <td data-label="Fecha">
                            <small>{{ $item->created_at->format('d/m/Y H:i:s') }}</small>
                        </td>
                        <td data-label="Usuario">
                            <span class="fw-medium" style="color: var(--primary-dark);">
                                {{ $item->usuario_nombre ?? 'Sistema' }}
                            </span>
                        </td>
                        <td data-label="Módulo">
                            <span class="badge-modulo">{{ ucfirst(str_replace('_', ' ', $item->modulo)) }}</span>
                        </td>
                        <td data-label="Acción">
                            <span class="badge-accion {{ $item->accion }}">
                                {{ ucfirst(str_replace('_', ' ', $item->accion)) }}
                            </span>
                        </td>
                        <td data-label="Descripción">
                            <span class="small">{{ Str::limit($item->descripcion, 100) }}</span>
                        </td>
                        <td data-label="IP"><small>{{ $item->ip_address ?? '—' }}</small></td>
                        <td data-label="Detalle">
                            <button class="btn btn-sm btn-action btn-outline-primary-dark ver-detalle"
                                    data-id="{{ $item->id }}" title="Ver detalle">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" class="mb-2">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                            </svg>
                            <p>No hay registros de auditoría</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========== PAGINACIÓN ========== -->
    <div class="pagination-bar">
        <div class="pagination-info">
            Mostrando {{ $auditoria->firstItem() ?? 0 }} a {{ $auditoria->lastItem() ?? 0 }} de {{ $auditoria->total() }} registros
        </div>
        {{ $auditoria->links() }}
    </div>
</div>

<!-- ========== MODAL DETALLE PROFESIONAL ========== -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            
            <!-- HEADER CON GRADIENTE -->
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 1rem 1.75rem; border-bottom: none;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 38px; height: 38px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    </div>
                    <div>
                        <h5 class="modal-title" style="color: white; font-weight: 700; font-size: 1.1rem; margin: 0;">
                            Detalle de Auditoría
                        </h5>
                        <span style="color: rgba(255,255,255,0.7); font-size: 0.75rem; display: block; margin-top: 1px;">
                            Información completa del registro
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="opacity: 0.8; transition: opacity 0.2s;"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body" id="detalleContenido" style="padding: 1.5rem; background: #f8fafc;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 2.5rem; height: 2.5rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Cargando detalles del registro...</p>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer" style="padding: 0.75rem 1.75rem; background: white; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal" style="padding: 0.4rem 1.2rem; border-radius: 8px; font-weight: 500; font-size: 0.85rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right: 6px;">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ESTILOS ADICIONALES PARA EL DETALLE - VERSIÓN COMPACTA -->
<style>
    .detalle-auditoria-moderno {
        animation: fadeInUp 0.3s ease forwards;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .detalle-header-card {
        background: white;
        border-radius: 12px;
        padding: 0.9rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .detalle-header-card .header-left {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .detalle-header-card .header-left .badge-id {
        background: #1e3c72;
        color: white;
        padding: 0.15rem 0.8rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .detalle-header-card .header-left .badge-accion-detalle {
        padding: 0.2rem 0.9rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .detalle-header-card .header-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .detalle-header-card .header-right .badge-modulo-detalle {
        padding: 0.15rem 0.8rem;
        border-radius: 16px;
        font-weight: 500;
        font-size: 0.7rem;
        background: #e9ecef;
        color: #495057;
    }

    .detalle-header-card .header-right .badge-ip {
        font-size: 0.65rem;
        color: #6c757d;
        background: #f8f9fc;
        padding: 0.15rem 0.6rem;
        border-radius: 16px;
        border: 1px solid #e9ecef;
    }

    .detalle-header-card .header-right .fecha-detalle {
        font-size: 0.65rem;
        color: #94a3b8;
    }

    .detalle-grid-moderno {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.6rem;
        margin-bottom: 1rem;
    }

    .detalle-grid-moderno .detalle-item-moderno {
        background: white;
        border-radius: 10px;
        padding: 0.6rem 0.9rem;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }

    .detalle-grid-moderno .detalle-item-moderno:hover {
        border-color: #1e3c72;
        box-shadow: 0 2px 8px rgba(30, 60, 114, 0.06);
    }

    .detalle-grid-moderno .detalle-item-moderno .detalle-label {
        font-size: 0.6rem;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        margin-bottom: 0.15rem;
    }

    .detalle-grid-moderno .detalle-item-moderno .detalle-label svg {
        width: 12px;
        height: 12px;
        stroke: #94a3b8;
    }

    .detalle-grid-moderno .detalle-item-moderno .detalle-value {
        font-weight: 500;
        color: #0f172a;
        font-size: 0.85rem;
        word-break: break-word;
    }

    .detalle-grid-moderno .detalle-item-moderno .detalle-value .text-muted-small {
        color: #94a3b8;
        font-size: 0.7rem;
        font-weight: 400;
    }

    /* Sección de cambios - Versión compacta */
    .detalle-cambios-container {
        background: white;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        overflow: hidden;
        margin-top: 1rem;
    }

    .detalle-cambios-container .cambios-header {
        background: #f8fafc;
        padding: 0.5rem 1.25rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .detalle-cambios-container .cambios-header .cambios-title {
        font-weight: 600;
        color: #1e3c72;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .detalle-cambios-container .cambios-header .cambios-title .badge-count {
        background: #1e3c72;
        color: white;
        padding: 0.05rem 0.5rem;
        border-radius: 16px;
        font-size: 0.6rem;
    }

    .detalle-cambios-container .table-cambios {
        margin-bottom: 0;
        font-size: 0.85rem;
    }

    .detalle-cambios-container .table-cambios thead th {
        background: #f8fafc;
        color: #1e3c72;
        font-weight: 600;
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        border-bottom: 2px solid #e9ecef;
        padding: 0.5rem 1.25rem;
    }

    .detalle-cambios-container .table-cambios tbody td {
        padding: 0.5rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        color: #1a1a1a;
    }

    .detalle-cambios-container .table-cambios tbody tr:hover {
        background: #f8fafc;
    }

    .detalle-cambios-container .table-cambios tbody tr:last-child td {
        border-bottom: none;
    }

    .detalle-cambios-container .table-cambios .badge-cambio {
        font-size: 0.55rem;
        padding: 0.1rem 0.5rem;
        border-radius: 16px;
        font-weight: 600;
    }

    .detalle-cambios-container .table-cambios .badge-cambio.modificado {
        background: #fff3cd;
        color: #856404;
    }

    .detalle-cambios-container .table-cambios .badge-cambio.nuevo {
        background: #d4edda;
        color: #155724;
    }

    .detalle-cambios-container .table-cambios .badge-cambio.eliminado {
        background: #f8d7da;
        color: #721c24;
    }

    .detalle-cambios-container .table-cambios .valor-antiguo {
        color: #dc3545;
        text-decoration: line-through;
        background: #fff5f5;
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    .detalle-cambios-container .table-cambios .valor-nuevo {
        color: #28a745;
        background: #f0fff4;
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    .detalle-cambios-container .table-cambios .valor-sin-cambio {
        color: #495057;
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
        background: #f8f9fc;
        font-size: 0.8rem;
    }

    /* Descripción del usuario - Compacta */
    .detalle-descripcion-usuario {
        background: #fffbf0;
        border-radius: 10px;
        padding: 0.6rem 1.25rem;
        border: 1px solid #fef3c7;
        margin: 0.75rem 0;
    }

    .detalle-descripcion-usuario .desc-label {
        font-size: 0.6rem;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        margin-bottom: 0.1rem;
    }

    .detalle-descripcion-usuario .desc-label svg {
        width: 12px;
        height: 12px;
        stroke: #94a3b8;
    }

    .detalle-descripcion-usuario .desc-texto {
        color: #1a1a1a;
        font-size: 0.85rem;
        line-height: 1.5;
        white-space: pre-line;
    }

    /* User Agent - Compacto */
    .detalle-user-agent {
        background: #f8f9fc;
        border-radius: 6px;
        padding: 0.4rem 0.8rem;
        font-size: 0.65rem;
        color: #6c757d;
        word-break: break-all;
        border: 1px solid #e9ecef;
        margin-top: 0.5rem;
    }

    .detalle-user-agent .ua-label {
        font-weight: 600;
        color: #495057;
        margin-right: 0.3rem;
    }

    /* Texto vacío - Compacto */
    .detalle-vacio {
        text-align: center;
        padding: 1.5rem 0.5rem;
        color: #94a3b8;
    }

    .detalle-vacio svg {
        width: 36px;
        height: 36px;
        stroke: #cbd5e1;
        margin-bottom: 0.3rem;
    }

    .detalle-vacio p {
        font-size: 0.85rem;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .detalle-grid-moderno {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .detalle-grid-moderno {
            grid-template-columns: 1fr;
        }

        .detalle-header-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .detalle-header-card .header-left {
            flex-wrap: wrap;
        }

        .detalle-cambios-container .table-cambios {
            font-size: 0.75rem;
        }

        .detalle-cambios-container .table-cambios thead th,
        .detalle-cambios-container .table-cambios tbody td {
            padding: 0.4rem 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .detalle-grid-moderno .detalle-item-moderno {
            padding: 0.5rem 0.75rem;
        }

        .detalle-grid-moderno .detalle-item-moderno .detalle-value {
            font-size: 0.8rem;
        }

        .detalle-header-card .header-left .badge-id {
            font-size: 0.65rem;
            padding: 0.1rem 0.6rem;
        }

        .detalle-header-card .header-left .badge-accion-detalle {
            font-size: 0.6rem;
            padding: 0.1rem 0.6rem;
        }

        .detalle-cambios-container .table-cambios thead {
            display: none;
        }

        .detalle-cambios-container .table-cambios tbody td {
            display: block;
            text-align: right;
            padding-left: 50%;
            position: relative;
            font-size: 0.75rem;
        }

        .detalle-cambios-container .table-cambios tbody td::before {
            content: attr(data-label);
            position: absolute;
            left: 0.5rem;
            width: 45%;
            text-align: left;
            font-weight: 600;
            color: #1e3c72;
            font-size: 0.65rem;
        }

        .detalle-cambios-container .table-cambios tbody tr {
            display: block;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            padding: 0.3rem 0;
            background: white;
        }

        .detalle-cambios-container .table-cambios tbody td:last-child {
            border-bottom: none;
        }

        .detalle-descripcion-usuario {
            padding: 0.5rem 0.75rem;
        }

        .detalle-descripcion-usuario .desc-texto {
            font-size: 0.8rem;
        }

        .detalle-user-agent {
            font-size: 0.6rem;
            padding: 0.3rem 0.6rem;
        }
    }
</style>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== BÚSQUEDA EN TIEMPO REAL ==========
    const buscarInput = document.getElementById('buscarAuditoria');
    const filtroModulo = document.getElementById('filtroModulo');
    const filtroAccion = document.getElementById('filtroAccion');
    const filtroFechaDesde = document.getElementById('filtroFechaDesde');
    const filtroFechaHasta = document.getElementById('filtroFechaHasta');
    const limpiarBtn = document.getElementById('limpiarFiltros');
    let timeoutBusqueda = null;

    function aplicarFiltros() {
        const url = new URL(window.location.href);
        const params = new URLSearchParams();

        if (buscarInput.value.trim()) params.set('buscar', buscarInput.value.trim());
        if (filtroModulo.value) params.set('modulo', filtroModulo.value);
        if (filtroAccion.value) params.set('accion', filtroAccion.value);
        if (filtroFechaDesde.value) params.set('fecha_desde', filtroFechaDesde.value);
        if (filtroFechaHasta.value) params.set('fecha_hasta', filtroFechaHasta.value);

        const queryString = params.toString();
        window.location.href = window.location.pathname + (queryString ? '?' + queryString : '');
    }

    function aplicarFiltrosConDebounce() {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(aplicarFiltros, 400);
    }

    if (buscarInput) {
        buscarInput.addEventListener('input', aplicarFiltrosConDebounce);
    }

    if (filtroModulo) {
        filtroModulo.addEventListener('change', aplicarFiltros);
    }

    if (filtroAccion) {
        filtroAccion.addEventListener('change', aplicarFiltros);
    }

    if (filtroFechaDesde) {
        filtroFechaDesde.addEventListener('change', aplicarFiltros);
    }

    if (filtroFechaHasta) {
        filtroFechaHasta.addEventListener('change', aplicarFiltros);
    }

    if (limpiarBtn) {
        limpiarBtn.addEventListener('click', function() {
            window.location.href = window.location.pathname;
        });
    }

    // ========== VER DETALLE PROFESIONAL - VERSIÓN COMPACTA ==========
    document.querySelectorAll('.ver-detalle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
            const body = document.getElementById('detalleContenido');

            body.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" style="width: 2.5rem; height: 2.5rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted" style="font-size: 0.9rem;">Cargando detalles...</p>
                </div>
            `;
            modal.show();

            fetch(`/admin/auditoria/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const d = data.data;
                        
                        // Generar HTML de los campos
                        let camposHtml = '';
                        let tieneCambios = false;
                        
                        if (d.campos && d.campos.length > 0) {
                            const camposConCambios = d.campos.filter(c => c.cambio === true);
                            const camposSinCambios = d.campos.filter(c => c.cambio === false);
                            
                            if (camposConCambios.length > 0) {
                                tieneCambios = true;
                                camposHtml += `
                                    <div class="detalle-cambios-container">
                                        <div class="cambios-header">
                                            <div class="cambios-title">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="23 4 23 10 17 10"/>
                                                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                                                </svg>
                                                Cambios Realizados
                                                <span class="badge-count">${camposConCambios.length}</span>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-cambios">
                                                <thead>
                                                    <tr>
                                                        <th style="width:25%;">Campo</th>
                                                        <th style="width:30%;">Valor Anterior</th>
                                                        <th style="width:30%;">Nuevo Valor</th>
                                                        <th style="width:15%;">Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${camposConCambios.map(campo => `
                                                        <tr>
                                                            <td data-label="Campo"><strong>${campo.etiqueta}</strong></td>
                                                            <td data-label="Valor Anterior"><span class="valor-antiguo">${campo.valor_original}</span></td>
                                                            <td data-label="Nuevo Valor"><span class="valor-nuevo">${campo.valor_nuevo}</span></td>
                                                            <td data-label="Estado"><span class="badge-cambio modificado">Modificado</span></td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                `;
                            }
                            
                            // Mostrar campos nuevos (para creaciones)
                            if (camposSinCambios.length > 0 && !camposConCambios.length) {
                                tieneCambios = true;
                                camposHtml += `
                                    <div class="detalle-cambios-container">
                                        <div class="cambios-header">
                                            <div class="cambios-title">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                                Datos Registrados
                                                <span class="badge-count">${camposSinCambios.length}</span>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-cambios">
                                                <thead>
                                                    <tr>
                                                        <th style="width:40%;">Campo</th>
                                                        <th style="width:60%;">Valor</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${camposSinCambios.map(campo => `
                                                        <tr>
                                                            <td data-label="Campo"><strong>${campo.etiqueta}</strong></td>
                                                            <td data-label="Valor"><span class="valor-nuevo">${campo.valor_nuevo}</span></td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                `;
                            }
                        }

                        // Si no hay cambios mostrados, mostrar mensaje
                        if (!tieneCambios) {
                            camposHtml = `
                                <div class="detalle-vacio">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    <p>No hay datos adicionales para mostrar</p>
                                </div>
                            `;
                        }

                        // Construir el HTML completo - Versión compacta
                        const html = `
                            <div class="detalle-auditoria-moderno">
                                <!-- HEADER CARD -->
                                <div class="detalle-header-card">
                                    <div class="header-left">
                                        <span class="badge-id">#${d.id}</span>
                                        <span class="badge-accion-detalle" style="background: ${d.accion_color}20; color: ${d.accion_color}; border: 1px solid ${d.accion_color}40;">
                                            ${d.accion_icono} ${d.accion_texto}
                                        </span>
                                        <span class="badge-modulo-detalle">${d.modulo}</span>
                                    </div>
                                    <div class="header-right">
                                        <span class="badge-ip">🌐 ${d.ip_address || '—'}</span>
                                        <span class="fecha-detalle">🕐 ${d.fecha}</span>
                                    </div>
                                </div>

                                <!-- GRID DE INFORMACIÓN -->
                                <div class="detalle-grid-moderno">
                                    <div class="detalle-item-moderno">
                                        <div class="detalle-label">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                            Usuario
                                        </div>
                                        <div class="detalle-value">
                                            ${d.usuario_nombre}
                                            ${d.usuario ? `<span class="text-muted-small">(${d.usuario.usuario})</span>` : ''}
                                        </div>
                                    </div>
                                    <div class="detalle-item-moderno">
                                        <div class="detalle-label">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                            Fecha y Hora
                                        </div>
                                        <div class="detalle-value">
                                            ${d.fecha}
                                            <span class="text-muted-small">(${d.fecha_humana})</span>
                                        </div>
                                    </div>
                                    <div class="detalle-item-moderno">
                                        <div class="detalle-label">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="8" width="16" height="12" rx="1"/><path d="M8 20V8M16 20V8M4 12h16"/></svg>
                                            Tabla Afectada
                                        </div>
                                        <div class="detalle-value">${d.tabla_afectada || '—'}</div>
                                    </div>
                                    <div class="detalle-item-moderno">
                                        <div class="detalle-label">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"/><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg>
                                            Registro ID
                                        </div>
                                        <div class="detalle-value">${d.registro_id || '—'}</div>
                                    </div>
                                </div>

                                <!-- DESCRIPCIÓN -->
                                ${d.descripcion ? `
                                <div class="detalle-descripcion-usuario">
                                    <div class="desc-label">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M22 7l-10 7L2 7"/></svg>
                                        Descripción de la Acción
                                    </div>
                                    <div class="desc-texto">${d.descripcion}</div>
                                </div>
                                ` : ''}

                                <!-- CAMPOS / CAMBIOS -->
                                ${camposHtml}

                                <!-- USER AGENT -->
                                ${d.user_agent ? `
                                <div class="detalle-user-agent">
                                    <span class="ua-label">🖥️ User Agent:</span>
                                    ${d.user_agent}
                                </div>
                                ` : ''}
                            </div>
                        `;

                        body.innerHTML = html;
                    } else {
                        body.innerHTML = `
                            <div class="text-center text-danger py-4">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5" class="mb-2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                </svg>
                                <p style="font-size: 1rem; font-weight: 500;">${data.message || 'Error al cargar los detalles'}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    body.innerHTML = `
                        <div class="text-center text-danger py-4">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5" class="mb-2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="15" y1="9" x2="9" y2="15"/>
                                <line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                            <p style="font-size: 1rem; font-weight: 500;">Error al cargar los detalles</p>
                            <p class="text-muted" style="font-size: 0.85rem;">${error.message || 'Intente nuevamente'}</p>
                        </div>
                    `;
                });
        });
    });
});
</script>
@endsection