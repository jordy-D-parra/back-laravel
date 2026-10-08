@extends('layouts.dashboard')

@section('title', 'Fichas de Soporte Técnico')

@section('styles')
@vite(['resources/css/admin-soporte.css'])
<style>
    /* ============================================================
       OVERRIDES ALINEADOS CON PRÉSTAMOS / INVENTARIO / ENTIDADES
       ============================================================ */

    /* Header con gradiente */
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
    .page-header h4 svg { stroke: white; }
    .page-header p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.85rem;
        margin: 0;
    }

    .page-header .btn-header {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: white;
        border-radius: 10px;
        padding: 0.55rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .page-header .btn-header:hover {
        background: rgba(255, 255, 255, 0.25);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    .page-header .btn-header svg { stroke: white; }

    .page-header .dropdown-menu {
        border-radius: 12px;
        border: 1px solid #e9ecef;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        padding: 0.5rem;
        min-width: 240px;
    }
    .page-header .dropdown-item {
        border-radius: 8px;
        padding: 0.6rem 0.9rem;
        font-size: 0.85rem;
        font-weight: 500;
        color: #1e3c72;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        transition: all 0.2s ease;
    }
    .page-header .dropdown-item:hover {
        background: #eef3fc;
        color: #1e3c72;
        transform: translateX(2px);
    }
    .page-header .dropdown-item svg { stroke: #1e3c72; flex-shrink: 0; }

    /* ============================================================
       TABLA — Tamaños idénticos a Préstamos / Inventario
       ============================================================ */
    .table-container {
        background: white;
        border-radius: 0.75rem 0.75rem 0 0;
        border: 1px solid #e9ecef;
        border-bottom: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        overflow-x: auto;
    }
    .table {
        margin-bottom: 0;
        font-size: 0.85rem;              /* ✅ igual que Préstamos */
    }
    .table thead th {
        background: #f8f9fc;
        color: #1e3c72;
        font-weight: 600;
        font-size: 0.7rem;               /* ✅ igual que Préstamos */
        text-transform: uppercase;
        letter-spacing: 0.6px;
        border-bottom: 2px solid #1e3c72;
        padding: 0.9rem 0.75rem;         /* ✅ igual que Préstamos */
        white-space: nowrap;
    }
    .table tbody td {
        vertical-align: middle;
        padding: 0.85rem 0.75rem;        /* ✅ igual que Préstamos */
        border-bottom: 1px solid #e9ecef;
        font-size: 0.85rem;
        color: #0f172a;
    }
    .table tbody td small {
        font-size: 0.75rem;
    }
    .table-hover tbody tr {
        transition: background 0.15s ease;
    }
    .table-hover tbody tr:hover {
        background-color: #eef3fc;
    }
    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* ============================================================
       BADGES DE ESTADO
       ============================================================ */
    .badge-estado {
        display: inline-block;
        padding: 4px 10px;               /* ✅ igual que Préstamos */
        border-radius: 20px;
        font-size: 0.7rem;               /* ✅ igual que Préstamos */
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        white-space: nowrap;
    }
    .badge-estado-en-proceso {
        background: #fef9e7;
        color: #92400e;
        border: 1px solid #f59e0b;
    }
    .badge-estado-finalizado {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #10b981;
    }
    .badge-estado-pendiente {
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #3b82f6;
    }
    .badge-estado-rechazado {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }
    .badge-estado-cancelado {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #94a3b8;
    }

    /* Badge fecha de entrega */
    .badge-fecha-entrega {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .badge-fecha-vigente  { background: #ecfdf5; color: #065f46; border: 1px solid #10b981; }
    .badge-fecha-proxima  { background: #fef9e7; color: #92400e; border: 1px solid #f59e0b; }
    .badge-fecha-vencida  { background: #fef2f2; color: #991b1b; border: 1px solid #ef4444; }
    .badge-fecha-sin      { background: #f8fafc; color: #64748b; border: 1px solid #cbd5e1; }

    /* ============================================================
       BOTÓN "CERRAR" — suave pero con buen contraste
       ============================================================ */
    .btn-cerrar-ficha {
        background: #fef9e7;
        border: 1px solid #fcd34d;
        color: #92400e;
        padding: 0.3rem 0.7rem;
        border-radius: 6px;
        font-size: 0.75rem;              /* ✅ legible */
        font-weight: 600;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }
    .btn-cerrar-ficha:hover {
        background: #fde68a;
        border-color: #f59e0b;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(245, 158, 11, 0.25);
        color: #78350f;
    }

    /* Botones de acción — tamaño legible */
    .btn-action {
        background: transparent;
        border: none;
        cursor: pointer;
        color: #6c757d;
        padding: 0.3rem 0.5rem;
        border-radius: 6px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-action:hover {
        background: #eef3fc;
        color: #1e3c72;
        transform: scale(1.08);
    }
    .btn-action.text-danger:hover {
        background: #fee2e2;
        color: #c5221f;
    }

    /* ============================================================
       FILTROS
       ============================================================ */
    .filters-bar {
        background: white;
        border: 1px solid #e9ecef;
        border-top: none;
        padding: 0.75rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
        border-radius: 0 0 0.75rem 0.75rem;
        margin-bottom: 1rem;
    }
    .filters-bar .input-group-text {
        background: white;
        border-right: none;
        color: #6c757d;
    }
    .filters-bar .form-control,
    .filters-bar .form-select {
        border-radius: 8px;
        border: 1px solid #e9ecef;
        padding: 0.45rem 0.75rem;
        font-size: 0.82rem;
        background: white;
        transition: all 0.2s ease;
    }
    .filters-bar .input-group .form-control {
        border-left: none;
        border-radius: 0 8px 8px 0;
    }
    .filters-bar .input-group .input-group-text {
        border-radius: 8px 0 0 8px;
    }
    .filters-bar .form-control:focus,
    .filters-bar .form-select:focus {
        border-color: #1e3c72;
        box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
    }

    /* ============================================================
       TABS
       ============================================================ */
    .nav-tabs-custom {
        display: flex;
        gap: 0.25rem;
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 0;
        background: white;
        padding: 0 0.5rem;
        border-radius: 0.75rem 0.75rem 0 0;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        padding: 0.85rem 1.5rem;         /* ✅ igual que Préstamos */
        color: #6c757d;
        font-weight: 500;
        font-size: 0.85rem;              /* ✅ igual que Préstamos */
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 0;
        background: transparent;
        transition: all 0.3s ease;
        position: relative;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #1e3c72;
        background: #eef3fc;
    }
    .nav-tabs-custom .nav-link.active {
        color: #1e3c72;
        font-weight: 600;
        background: transparent;
    }
    .nav-tabs-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #1e3c72, #2a5298);
        border-radius: 3px 3px 0 0;
    }
    .nav-tabs-custom .nav-link svg { stroke: currentColor; }
    .tab-correos-badge {
        background: #dc3545 !important;
        color: white !important;
        padding: 0.15rem 0.55rem;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 800;
        margin-left: 0.5rem;
    }

    /* ============================================================
       PAGINACIÓN (idéntica al módulo de Instituciones)
       ============================================================ */
    .pagination-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.85rem 1rem;
        flex-wrap: wrap;
        gap: 0.75rem;
        background: white;
        border: 1px solid #e9ecef;
        border-top: none;
        border-radius: 0 0 0.75rem 0.75rem;
    }
    .pagination-info {
        font-size: 0.8rem;
        color: #6c757d;
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
        min-width: 34px;
        height: 34px;
        padding: 0 0.55rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #1e3c72;
        background: white;
        border: 1.5px solid #e9ecef;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.25s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        cursor: pointer;
        user-select: none;
        line-height: 1;
    }
    .pagination-btn:hover:not(.disabled):not(.active) {
        background: #eef3fc;
        border-color: #1e3c72;
        color: #1e3c72;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 60, 114, 0.15);
    }
    .pagination-btn.active {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        border-color: #1e3c72;
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
        min-width: 34px;
        height: 34px;
        color: #6c757d;
        font-weight: 600;
        font-size: 0.82rem;
    }

    /* Responsive tabla en móvil */
    @media (max-width: 576px) {
        .table thead { display: none; }
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
            display: block;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
            background: white;
        }
        .table tbody td:last-child { border-bottom: none; }
        .pagination-bar { flex-direction: column; align-items: center; }
        .pagination-btn { min-width: 32px; height: 32px; font-size: 0.75rem; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">

    {{-- ========== HEADER ========== --}}
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Fichas de Soporte Técnico
            </h4>
            <p>Gestión de mantenimiento y reparaciones</p>
        </div>
        <div class="dropdown">
            <button class="btn-header dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nueva Ficha
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @if(auth()->user()->hasPermission('crear-ficha-soporte'))
                <li>
                    <a class="dropdown-item" href="#" onclick="window.abrirModalCrearFicha(); return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="6" width="20" height="12" rx="2"/>
                        </svg>
                        Crear Ficha Soporte
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="window.abrirModalEquipoExterno(); return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                            <line x1="9" y1="4" x2="9" y2="20"/>
                            <line x1="15" y1="4" x2="15" y2="20"/>
                        </svg>
                        Registrar Equipo Externo
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>

    {{-- ========== TARJETAS DE ESTADÍSTICAS ========== --}}
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsTotal">{{ $totalFichas ?? 0 }}</div>
                <div class="stat-label">Total Fichas</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsEnProceso">{{ $enProceso ?? 0 }}</div>
                <div class="stat-label">En Proceso</div>
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
                <div class="stat-number" id="statsFinalizados">{{ $finalizados ?? 0 }}</div>
                <div class="stat-label">Finalizados</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(30, 126, 52, 0.1); color: #1e7e34;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsEquiposReparacion">{{ $equiposReparacion ?? 0 }}</div>
                <div class="stat-label">En Reparación</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ========== TABS ========== --}}
    <ul class="nav nav-tabs-custom" id="soporteTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-fichas" data-bs-toggle="tab" data-bs-target="#panel-fichas" type="button" role="tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                </svg>
                Fichas de Soporte
            </button>
        </li>
        @if(auth()->user()->hasPermission('ver-fichas-soporte'))
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-correos" data-bs-toggle="tab" data-bs-target="#panel-correos" type="button" role="tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M22 7l-10 7L2 7"/>
                </svg>
                Correo de Soporte
                <span class="tab-correos-badge" id="tabCorreosBadge" style="{{ ($correosNoProcesados ?? 0) > 0 ? '' : 'display:none;' }}">
                    {{ $correosNoProcesados ?? 0 }}
                </span>
            </button>
        </li>
        @endif
    </ul>

    <div class="tab-content">

        {{-- ============================================ --}}
        {{-- PANEL 1: FICHAS DE SOPORTE --}}
        {{-- ============================================ --}}
        <div class="tab-pane fade show active" id="panel-fichas" role="tabpanel">

            {{-- Filtros --}}
            <div class="filters-bar">
                <div class="input-group" style="max-width: 350px;">
                    <span class="input-group-text">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </span>
                    <input type="text" class="form-control" id="buscarFichas"
                           placeholder="Buscar por activo, técnico, reportante...">
                </div>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <select class="form-select" id="filtroEstadoFichas" style="width: 180px;">
                        <option value="">Todos los estados</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="aceptada">Aceptada</option>
                        <option value="finalizado">Finalizados</option>
                        <option value="rechazada">Rechazada</option>
                    </select>
                    <button class="btn btn-outline-primary-dark btn-sm" id="limpiarFiltros">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/>
                        </svg>
                        Limpiar
                    </button>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="table-container">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Activo</th>
                            <th>Técnico</th>
                            <th>Reporta</th>
                            <th>Ingreso</th>
                            <th>F. Requerida</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaFichas">
                        @forelse($fichas as $ficha)
                        <tr>
                            {{-- Activo --}}
                            <td data-label="Activo">
                                @if($ficha->activo)
                                    <span class="fw-medium" style="color:#1e3c72;">
                                        {{ $ficha->activo->serial }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $ficha->activo->modelo?->nombre ?? 'N/A' }}
                                    </small>
                                @else
                                    <span class="text-muted">Equipo externo</span>
                                @endif
                            </td>

                            {{-- Técnico --}}
                            <td data-label="Técnico">{{ $ficha->tecnico_nombre ?? '---' }}</td>

                            {{-- Reporta --}}
                            <td data-label="Reporta">{{ $ficha->usuario_reporta_nombre ?? '---' }}</td>

                            {{-- Fecha Ingreso --}}
                            <td data-label="Ingreso">
                                @if($ficha->fecha_ingreso)
                                    <small class="fw-medium">
                                        {{ $ficha->fecha_ingreso->format('d/m/Y') }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        {{ $ficha->fecha_ingreso->format('H:i') }}
                                    </small>
                                @else
                                    <small class="text-muted">---</small>
                                @endif
                            </td>

                            {{-- Fecha Requerida --}}
                            <td data-label="F. Requerida">
                                @if($ficha->fecha_requerida_entrega)
                                    @php
                                        $dias = $ficha->dias_restantes;
                                        $clase = 'badge-fecha-vigente';
                                        $texto = $ficha->fecha_requerida_entrega->format('d/m/Y');
                                        if ($ficha->esta_vencida) {
                                            $clase = 'badge-fecha-vencida';
                                            $texto .= ' (Vencida)';
                                        } elseif ($dias !== null && $dias <= 3) {
                                            $clase = 'badge-fecha-proxima';
                                            $texto .= " ({$dias} d)";
                                        }
                                    @endphp
                                    <span class="badge-fecha-entrega {{ $clase }}">
                                        {{ $texto }}
                                    </span>
                                @else
                                    <span class="badge-fecha-entrega badge-fecha-sin">Sin fecha</span>
                                @endif
                            </td>

                            {{-- Fecha Salida --}}
                            <td data-label="Salida">
                                @if($ficha->fecha_salida)
                                    <small class="fw-medium" style="color: #1e7e34;">
                                        {{ $ficha->fecha_salida->format('d/m/Y') }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        {{ $ficha->fecha_salida->format('H:i') }}
                                    </small>
                                @else
                                    <small class="text-muted">---</small>
                                @endif
                            </td>

                            {{-- Estado --}}
                            <td data-label="Estado">
                                @php
                                    $estadoLabel = match($ficha->estado) {
                                        'en_proceso' => 'En Proceso',
                                        'aceptada'   => 'Aceptada',
                                        'rechazada'  => 'Rechazada',
                                        'finalizado' => 'Finalizado',
                                        'cancelada'  => 'Cancelada',
                                        default      => ucfirst($ficha->estado),
                                    };
                                    $estadoBadge = match($ficha->estado) {
                                        'en_proceso' => 'badge-estado-en-proceso',
                                        'aceptada'   => 'badge-estado-pendiente',
                                        'rechazada'  => 'badge-estado-rechazado',
                                        'finalizado' => 'badge-estado-finalizado',
                                        'cancelada'  => 'badge-estado-cancelado',
                                        default      => 'badge-estado',
                                    };
                                @endphp
                                <span class="badge-estado {{ $estadoBadge }}">
                                    {{ $estadoLabel }}
                                </span>
                            </td>

                            {{-- Acciones --}}
                            <td data-label="Acciones" class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button type="button" class="btn-action"
                                            onclick="verDetalle({{ $ficha->id }})" title="Ver detalle">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M12 8v4"/>
                                            <path d="M12 16h.01"/>
                                        </svg>
                                    </button>

                                    @if(in_array($ficha->estado, ['en_proceso', 'aceptada']))
                                        <button type="button" class="btn-cerrar-ficha"
                                                onclick="abrirModalCerrarFicha({{ $ficha->id }})"
                                                title="Cerrar ficha">
                                            ✓ Cerrar
                                        </button>
                                    @endif

                                    @if(auth()->user()->hasPermission('eliminar-ficha-soporte'))
                                        <button type="button" class="btn-action text-danger"
                                                onclick="confirmarEliminar({{ $ficha->id }})"
                                                title="Eliminar">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                            <td colspan="8" class="text-center py-5 text-muted">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" class="mb-2">
                                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                                </svg>
                                <p>No hay fichas de soporte registradas</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación (mismo estilo que Instituciones) --}}
            <div class="pagination-bar" id="paginacionFichas">
                <div class="pagination-info">
                    @if($fichas->total() > 0)
                        Mostrando {{ $fichas->firstItem() }} a {{ $fichas->lastItem() }}
                        de {{ $fichas->total() }} registros
                    @else
                        Sin registros
                    @endif
                </div>
                <div class="pagination-btns">
                    {{-- Anterior --}}
                    <button class="pagination-btn {{ $fichas->onFirstPage() ? 'disabled' : '' }}"
                            onclick="cambiarPagina({{ $fichas->currentPage() - 1 }})"
                            {{ $fichas->onFirstPage() ? 'disabled' : '' }}>
                        «
                    </button>

                    {{-- Números de página --}}
                    @php
                        $currentPage = $fichas->currentPage();
                        $lastPage = $fichas->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @if($start > 1)
                        <button class="pagination-btn" onclick="cambiarPagina(1)">1</button>
                        @if($start > 2)
                            <span class="pagination-ellipsis">...</span>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <button class="pagination-btn {{ $i === $currentPage ? 'active' : '' }}"
                                onclick="cambiarPagina({{ $i }})">
                            {{ $i }}
                        </button>
                    @endfor                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="pagination-ellipsis">...</span>
                        @endif
                        <button class="pagination-btn" onclick="cambiarPagina({{ $lastPage }})">{{ $lastPage }}</button>
                    @endif

                    {{-- Siguiente --}}
                    <button class="pagination-btn {{ !$fichas->hasMorePages() ? 'disabled' : '' }}"
                            onclick="cambiarPagina({{ $fichas->currentPage() + 1 }})"
                            {{ !$fichas->hasMorePages() ? 'disabled' : '' }}>
                        »
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- PANEL 2: CORREO DE SOPORTE --}}
        {{-- ============================================ --}}
        @if(auth()->user()->hasPermission('ver-fichas-soporte'))
        <div class="tab-pane fade" id="panel-correos" role="tabpanel">
            <div class="filters-bar" style="border-top: 1px solid #e9ecef; border-radius: 0.75rem;">
                <div class="input-group" style="max-width: 400px;">
                    <span class="input-group-text">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </span>
                    <input type="text" class="form-control" id="buscarCorreo"
                           placeholder="Buscar por remitente, asunto...">
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <select class="form-select" id="filtroCorreo" style="max-width: 200px;">
                        <option value="">Todos</option>
                        <option value="no_leidos">No leídos</option>
                        <option value="no_procesados">Sin procesar</option>
                        <option value="procesados">Procesados</option>
                    </select>
                    <button class="btn btn-primary-dark" onclick="revisarCorreos()" id="btnRevisarCorreos" style="color: #fff;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:4px;">
                            <polyline points="23 4 23 10 17 10"/>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                        </svg>
                        Revisar ahora
                    </button>
                </div>
            </div>
            <div id="listaCorreos" class="p-3" style="background: white; border-radius: 12px;">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando correos...</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODALES --}}
{{-- ============================================================ --}}

{{-- Modal Crear Ficha --}}
<div class="modal fade" id="modalCrearFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearFichaLabel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:8px;">
                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                    </svg>
                    Nueva Ficha de Soporte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCrearFicha">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Buscar Activo <span class="text-danger">*</span></label>
                        <div class="activo-buscar-container">
                            <input type="text" class="form-control" id="activoBuscarInput" placeholder="Escriba el serial, modelo o marca del activo..." autocomplete="off">
                            <input type="hidden" id="fichaActivoId" name="activo_id" value="">
                            <div class="activo-dropdown" id="activoDropdown"></div>
                        </div>
                        <div class="activo-seleccionado-info" id="activoSeleccionadoInfo" style="display: none;">
                            <span class="fw-medium" id="activoSeleccionadoTexto"></span>
                            <span class="badge bg-success ms-2" id="activoSeleccionadoEstado"></span>
                            <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="window.limpiarActivoSeleccionado()">✕</button>
                        </div>
                        <small class="text-muted">Solo se muestran activos disponibles</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Técnico Responsable</label>
                        <div class="tecnico-search-container">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"/>
                                        <path d="M21 21l-4.35-4.35"/>
                                    </svg>
                                </span>
                                <input type="text" class="form-control" id="tecnicoBuscarInput" placeholder="Buscar por cédula, nombre o usuario..." autocomplete="off">
                            </div>
                            <div id="tecnicoSearchResults" class="tecnico-search-results" style="display:none;"></div>
                            <div id="tecnicoEncontrado" class="tecnico-encontrado" style="display:none;">
                                <div class="d-flex align-items-start gap-2">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" class="mt-1 flex-shrink-0">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <div>
                                        <p class="mb-1 nombre" id="tecnicoEncontradoNombre"></p>
                                        <p class="mb-0 info" id="tecnicoEncontradoCedula"></p>
                                        <p class="mb-0 info" id="tecnicoEncontradoUsuario"></p>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="fichaTecnicoId" name="tecnico_id" value="">
                            <input type="hidden" id="fichaTecnicoNombre" name="tecnico_nombre" value="">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Usuario que Reporta <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fichaUsuarioReporta" name="usuario_reporta_nombre" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Diagnóstico</label>
                        <textarea name="diagnostico" id="fichaDiagnostico" rows="3" class="form-control" placeholder="Describa el problema del equipo..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="fichaObservaciones" rows="2" class="form-control" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarFicha" style="color:#fff;">Guardar Ficha</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Equipo Externo --}}
<div class="modal fade" id="modalEquipoExterno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEquipoExternoLabel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:8px;">
                        <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                        <line x1="9" y1="4" x2="9" y2="20"/>
                        <line x1="15" y1="4" x2="15" y2="20"/>
                    </svg>
                    Registrar Equipo Externo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEquipoExterno">
                @csrf
                <div class="modal-body">
                    <div class="equipo-externo-card">
                        <div class="card-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; margin-right:8px;">
                                <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                                <line x1="9" y1="4" x2="9" y2="20"/>
                                <line x1="15" y1="4" x2="15" y2="20"/>
                            </svg>
                            Datos del Equipo Externo
                        </div>
                        <p class="text-muted small">Complete los datos del equipo que ingresa a reparación. Se creará automáticamente en el inventario.</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Serial <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ext_serial" name="serial" required>
                                <small id="ext_serial_feedback" class="text-muted">Ingrese el número de serie para verificar si ya existe</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Modelo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ext_modelo_nombre" name="modelo_nombre" required placeholder="Ej: Dell Latitude 5540">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Marca <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ext_marca" name="marca" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select" id="ext_categoria_id" name="categoria_id" required>
                                    <option value="">Seleccionar categoría...</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Institución <span class="text-danger">*</span></label>
                                <select class="form-select" id="ext_institucion_id" name="institucion_id" required>
                                    <option value="">Seleccionar institución...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Responsable <span class="text-danger">*</span></label>
                                <select class="form-select" id="ext_responsable_id" name="responsable_id" required>
                                    <option value="">Seleccionar responsable...</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ubicación</label>
                                <input type="text" class="form-control" id="ext_ubicacion" name="ubicacion" placeholder="Laboratorio 2">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Adquisición</label>
                                <input type="date" class="form-control" id="ext_fecha_adquisicion" name="fecha_adquisicion">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="ext_observaciones" name="observaciones" rows="2" placeholder="Observaciones del equipo..."></textarea>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6 style="color:#1e3c72; font-weight:600; margin-bottom:1rem; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; margin-right:6px;">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            Datos de la Ficha de Soporte
                        </h6>
                        <div class="mb-3">
                            <label class="form-label">Técnico Responsable</label>
                            <div class="tecnico-search-container">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                                            <circle cx="11" cy="11" r="8"/>
                                            <path d="M21 21l-4.35-4.35"/>
                                        </svg>
                                    </span>
                                    <input type="text" class="form-control" id="ext_tecnicoBuscarInput" placeholder="Buscar por cédula, nombre o usuario..." autocomplete="off">
                                </div>
                                <div id="extTecnicoSearchResults" class="tecnico-search-results" style="display:none;"></div>
                                <div id="extTecnicoEncontrado" class="tecnico-encontrado" style="display:none;">
                                    <div class="d-flex align-items-start gap-2">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" class="mt-1 flex-shrink-0">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                            <circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        <div>
                                            <p class="mb-1 nombre" id="extTecnicoEncontradoNombre"></p>
                                            <p class="mb-0 info" id="extTecnicoEncontradoCedula"></p>
                                            <p class="mb-0 info" id="extTecnicoEncontradoUsuario"></p>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="ext_fichaTecnicoId" name="tecnico_id" value="">
                                <input type="hidden" id="ext_fichaTecnicoNombre" name="tecnico_nombre" value="">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuario que Reporta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ext_usuario_reporta" name="usuario_reporta_nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnóstico</label>
                            <textarea name="diagnostico" id="ext_diagnostico" rows="3" class="form-control" placeholder="Describa el problema del equipo..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones_ficha" id="ext_observaciones_ficha" rows="2" class="form-control" placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarEquipoExterno" style="color:#fff;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:4px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Registrar Equipo y Crear Ficha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Cerrar Ficha --}}
<div class="modal fade" id="modalCerrarFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCerrarFichaLabel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:8px;">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Cerrar Ficha de Soporte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCerrarFicha">
                @csrf
                <input type="hidden" id="cerrarFichaId" name="ficha_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Trabajo Realizado</label>
                        <textarea name="trabajo_realizado" id="cerrarTrabajoRealizado" rows="3" class="form-control" placeholder="Describa el trabajo realizado..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones Finales</label>
                        <textarea name="observaciones_finales" id="cerrarObservacionesFinales" rows="2" class="form-control" placeholder="Observaciones finales..."></textarea>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-3" style="color: #1e3c72;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; margin-right:6px;">
                            <rect x="2" y="6" width="20" height="12" rx="2"/>
                        </svg>
                        Estado de Componentes
                    </h6>
                    <div id="componentesContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cerrar-ficha">Finalizar Ficha</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Detalle --}}
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleLabel">Detalle de Ficha</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">
                <div class="text-center py-4 text-muted">Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal" style="color:#fff;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Eliminar --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar esta ficha?</p>
                <p class="fw-bold text-danger" id="deleteNombre"></p>
                <p class="small text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Correo + Wizard --}}
@include('admin.soporte.partials.modal-correo-wizard')

{{-- Notificaciones --}}
<div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: 320px;"></div>

@endsection

@section('scripts')
@vite(['resources/js/admin-soporte.js'])
<script>
document.addEventListener('DOMContentLoaded', function () {
    const serialInput = document.getElementById('ext_serial');
    const feedback = document.getElementById('ext_serial_feedback');

    if (serialInput && feedback) {
        serialInput.addEventListener('blur', function () {
            const serial = this.value.trim();
            if (serial.length < 3) {
                feedback.textContent = 'Ingrese al menos 3 caracteres para verificar';
                feedback.className = 'text-muted';
                this.classList.remove('is-valid', 'is-invalid');
                return;
            }
            feedback.textContent = 'Verificando serial...';
            feedback.className = 'text-muted';

            fetch(`/admin/activos?buscar=${encodeURIComponent(serial)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const existe = data.data.some(a => a.serial === serial);
                    if (existe) {
                        feedback.textContent = '⚠️ Este serial ya está registrado en el sistema.';
                        feedback.className = 'text-danger';
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    } else {
                        feedback.textContent = '✓ Serial disponible';
                        feedback.className = 'text-success';
                        this.classList.add('is-valid');
                        this.classList.remove('is-invalid');
                    }
                }
            })
            .catch(() => {
                feedback.textContent = 'Error al verificar serial';
                feedback.className = 'text-danger';
            });
        });

        serialInput.addEventListener('input', function () {
            this.classList.remove('is-valid', 'is-invalid');
            const fb = document.getElementById('ext_serial_feedback');
            if (fb) {
                fb.textContent = 'Ingrese el número de serie para verificar si ya existe';
                fb.className = 'text-muted';
            }
        });
    }
});
</script>
@endsection