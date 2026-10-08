@extends('layouts.dashboard')

@section('title', 'Reportes del Sistema')

@section('styles')
@vite(['resources/css/admin-reportes.css'])
<style>
    /* ============================================================
       SECCIÓN: REPORTES IMPRIMIBLES
       ============================================================ */
    .reportes-imprimibles-section {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 3px double #e2e8f0;
    }

    .reportes-imprimibles-section .section-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .reportes-imprimibles-section .section-title svg {
        stroke: #1e3c72;
    }

    .reportes-imprimibles-section .section-title h4 {
        color: #1e3c72;
        font-weight: 700;
        margin: 0;
        font-size: 1.4rem;
    }

    .reportes-imprimibles-section .section-subtitle {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }

    .card-reporte {
        background: white;
        border-radius: 16px;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        padding: 1.5rem;
        height: 100%;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .card-reporte::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #1e3c72, #2a5298);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }

    .card-reporte:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(30, 60, 114, 0.12);
        border-color: rgba(30, 60, 114, 0.15);
    }

    .card-reporte:hover::before {
        transform: scaleX(1);
    }

    .card-reporte .card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .card-reporte:hover .card-icon {
        transform: scale(1.08) rotate(-5deg);
    }

    .card-reporte h6 {
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 0.25rem;
        color: #0f172a;
    }

    .card-reporte .card-desc {
        font-size: 0.82rem;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 1.25rem;
        flex: 1;
    }

    .card-reporte .btn-reporte {
        border-radius: 10px;
        padding: 0.6rem 1rem;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.3s ease;
        border: none;
        color: white;
        text-decoration: none;
        width: 100%;
    }

    .card-reporte .btn-reporte:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        color: white;
    }

    /* Colores de tarjetas */
    .card-reporte.color-blue .card-icon { background: rgba(30, 60, 114, 0.1); }
    .card-reporte.color-blue .card-icon svg { stroke: #1e3c72; }
    .card-reporte.color-blue .btn-reporte { background: linear-gradient(135deg, #1e3c72, #2a5298); }

    .card-reporte.color-red .card-icon { background: rgba(239, 68, 68, 0.1); }
    .card-reporte.color-red .card-icon svg { stroke: #ef4444; }
    .card-reporte.color-red .btn-reporte { background: linear-gradient(135deg, #ef4444, #dc2626); }

    .card-reporte.color-green .card-icon { background: rgba(16, 185, 129, 0.1); }
    .card-reporte.color-green .card-icon svg { stroke: #10b981; }
    .card-reporte.color-green .btn-reporte { background: linear-gradient(135deg, #10b981, #059669); }

    .card-reporte.color-orange .card-icon { background: rgba(245, 158, 11, 0.1); }
    .card-reporte.color-orange .card-icon svg { stroke: #f59e0b; }
    .card-reporte.color-orange .btn-reporte { background: linear-gradient(135deg, #f59e0b, #d97706); }

    .card-reporte.color-purple .card-icon { background: rgba(139, 92, 246, 0.1); }
    .card-reporte.color-purple .card-icon svg { stroke: #8b5cf6; }
    .card-reporte.color-purple .btn-reporte { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

    .card-reporte.color-cyan .card-icon { background: rgba(6, 182, 212, 0.1); }
    .card-reporte.color-cyan .card-icon svg { stroke: #06b6d4; }
    .card-reporte.color-cyan .btn-reporte { background: linear-gradient(135deg, #06b6d4, #0891b2); }

    .card-reporte.color-gray .card-icon { background: rgba(100, 116, 139, 0.1); }
    .card-reporte.color-gray .card-icon svg { stroke: #64748b; }
    .card-reporte.color-gray .btn-reporte { background: linear-gradient(135deg, #64748b, #475569); }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">

    {{-- ============================================================ --}}
    {{-- HEADER --}}
    {{-- ============================================================ --}}
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M3 3v18h18"/>
                    <path d="M18 17V9"/>
                    <path d="M13 17V5"/>
                    <path d="M8 17v-3"/>
                </svg>
                Reportes del Sistema
            </h4>
            <p>Estadísticas en vivo y reportes imprimibles en PDF</p>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- STATS GENERALES --}}
    {{-- ============================================================ --}}
    <div class="stats-row mb-4">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalActivos }}</div>
                <div class="stat-label">Activos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalComponentes }}</div>
                <div class="stat-label">Componentes</div>
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
                <div class="stat-number">{{ $totalPrestamos ?? 0 }}</div>
                <div class="stat-label">Préstamos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalSolicitudes }}</div>
                <div class="stat-label">Solicitudes</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M22 7l-10 7L2 7"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalSoportes }}</div>
                <div class="stat-label">Soportes</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalUsuarios ?? 0 }}</div>
                <div class="stat-label">Usuarios</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECCIÓN 1: ESTADÍSTICAS EN VIVO (TABS) --}}
    {{-- ============================================================ --}}
    <ul class="nav nav-tabs-custom" id="reportesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-inventario" data-bs-toggle="tab" data-bs-target="#panel-inventario" type="button" role="tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
                Inventario
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-solicitudes" data-bs-toggle="tab" data-bs-target="#panel-solicitudes" type="button" role="tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M22 7l-10 7L2 7"/>
                </svg>
                Solicitudes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-soporte" data-bs-toggle="tab" data-bs-target="#panel-soporte" type="button" role="tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Soporte Técnico
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ==================== PANEL INVENTARIO ==================== --}}
        <div class="tab-pane fade show active" id="panel-inventario" role="tabpanel">
            <div class="tab-content-wrap">
                <div class="filtros-reporte">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Tipo de Filtro</label>
                            <select class="form-select form-select-sm" id="inv_tipo_filtro">
                                <option value="todos">Todos los registros</option>
                                <option value="dia">Por día</option>
                                <option value="mes">Mensual (Mes/Año)</option>
                                <option value="rango">Rango de fechas</option>
                            </select>
                        </div>

                        <div class="col-md-3 filtro-inv-dia" style="display:none;">
                            <label class="form-label small fw-bold">Fecha</label>
                            <input type="date" class="form-control form-control-sm" id="inv_fecha" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-2 filtro-inv-mes" style="display:none;">
                            <label class="form-label small fw-bold">Mes</label>
                            <select class="form-select form-select-sm" id="inv_mes">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-2 filtro-inv-mes" style="display:none;">
                            <label class="form-label small fw-bold">Año</label>
                            <select class="form-select form-select-sm" id="inv_anio">
                                @for($a = date('Y'); $a >= 2020; $a--)
                                    <option value="{{ $a }}" {{ $a == date('Y') ? 'selected' : '' }}>{{ $a }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-2 filtro-inv-rango" style="display:none;">
                            <label class="form-label small fw-bold">Desde</label>
                            <input type="date" class="form-control form-control-sm" id="inv_fecha_desde" value="{{ date('Y-m-01') }}">
                        </div>

                        <div class="col-md-2 filtro-inv-rango" style="display:none;">
                            <label class="form-label small fw-bold">Hasta</label>
                            <input type="date" class="form-control form-control-sm" id="inv_fecha_hasta" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-primary-dark btn-sm w-100" onclick="Reportes.cargar('inventario')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; vertical-align:middle;">
                                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                                </svg>
                                Generar
                            </button>
                        </div>

                        <div class="col-md-2">
                            <a class="btn btn-danger btn-sm w-100" href="{{ route('admin.reportes.inventario-completo') }}" target="_blank">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; vertical-align:middle;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                PDF Completo
                            </a>
                        </div>
                    </div>
                </div>

                <div id="inv_contenido">
                    <div class="loading-reporte">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Cargando reporte de inventario...</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== PANEL SOLICITUDES ==================== --}}
        <div class="tab-pane fade" id="panel-solicitudes" role="tabpanel">
            <div class="tab-content-wrap">
                <div class="filtros-reporte">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Tipo de Filtro</label>
                            <select class="form-select form-select-sm" id="sol_tipo_filtro">
                                <option value="todos">Todos los registros</option>
                                <option value="dia">Por día</option>
                                <option value="mes">Mensual (Mes/Año)</option>
                                <option value="rango">Rango de fechas</option>
                            </select>
                        </div>

                        <div class="col-md-3 filtro-sol-dia" style="display:none;">
                            <label class="form-label small fw-bold">Fecha</label>
                            <input type="date" class="form-control form-control-sm" id="sol_fecha" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-2 filtro-sol-mes" style="display:none;">
                            <label class="form-label small fw-bold">Mes</label>
                            <select class="form-select form-select-sm" id="sol_mes">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-2 filtro-sol-mes" style="display:none;">
                            <label class="form-label small fw-bold">Año</label>
                            <select class="form-select form-select-sm" id="sol_anio">
                                @for($a = date('Y'); $a >= 2020; $a--)
                                    <option value="{{ $a }}" {{ $a == date('Y') ? 'selected' : '' }}>{{ $a }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-2 filtro-sol-rango" style="display:none;">
                            <label class="form-label small fw-bold">Desde</label>
                            <input type="date" class="form-control form-control-sm" id="sol_fecha_desde" value="{{ date('Y-m-01') }}">
                        </div>

                        <div class="col-md-2 filtro-sol-rango" style="display:none;">
                            <label class="form-label small fw-bold">Hasta</label>
                            <input type="date" class="form-control form-control-sm" id="sol_fecha_hasta" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-primary-dark btn-sm w-100" onclick="Reportes.cargar('solicitudes')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; vertical-align:middle;">
                                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                                </svg>
                                Generar
                            </button>
                        </div>

                        <div class="col-md-2">
                            <a class="btn btn-danger btn-sm w-100" href="{{ route('admin.reportes.solicitudes-detalle') }}" target="_blank">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; vertical-align:middle;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                PDF Detallado
                            </a>
                        </div>
                    </div>
                </div>

                <div id="sol_contenido">
                    <div class="loading-reporte">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Cargando reporte de solicitudes...</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== PANEL SOPORTE ==================== --}}
        <div class="tab-pane fade" id="panel-soporte" role="tabpanel">
            <div class="tab-content-wrap">
                <div class="filtros-reporte">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Tipo de Filtro</label>
                            <select class="form-select form-select-sm" id="sop_tipo_filtro">
                                <option value="todos">Todos los registros</option>
                                <option value="dia">Por día</option>
                                <option value="mes">Mensual (Mes/Año)</option>
                                <option value="rango">Rango de fechas</option>
                            </select>
                        </div>

                        <div class="col-md-3 filtro-sop-dia" style="display:none;">
                            <label class="form-label small fw-bold">Fecha</label>
                            <input type="date" class="form-control form-control-sm" id="sop_fecha" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-2 filtro-sop-mes" style="display:none;">
                            <label class="form-label small fw-bold">Mes</label>
                            <select class="form-select form-select-sm" id="sop_mes">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-2 filtro-sop-mes" style="display:none;">
                            <label class="form-label small fw-bold">Año</label>
                            <select class="form-select form-select-sm" id="sop_anio">
                                @for($a = date('Y'); $a >= 2020; $a--)
                                    <option value="{{ $a }}" {{ $a == date('Y') ? 'selected' : '' }}>{{ $a }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-2 filtro-sop-rango" style="display:none;">
                            <label class="form-label small fw-bold">Desde</label>
                            <input type="date" class="form-control form-control-sm" id="sop_fecha_desde" value="{{ date('Y-m-01') }}">
                        </div>

                        <div class="col-md-2 filtro-sop-rango" style="display:none;">
                            <label class="form-label small fw-bold">Hasta</label>
                            <input type="date" class="form-control form-control-sm" id="sop_fecha_hasta" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-primary-dark btn-sm w-100" onclick="Reportes.cargar('soporte')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; vertical-align:middle;">
                                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                                </svg>
                                Generar
                            </button>
                        </div>

                        <div class="col-md-2">
                            <a class="btn btn-danger btn-sm w-100" href="{{ route('admin.reportes.soporte-detalle') }}" target="_blank">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; vertical-align:middle;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                PDF Detallado
                            </a>
                        </div>
                    </div>
                </div>

                <div id="sop_contenido">
                    <div class="loading-reporte">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Cargando reporte de soporte técnico...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECCIÓN 2: REPORTES IMPRIMIBLES (PDF) --}}
    {{-- ============================================================ --}}
    <div class="reportes-imprimibles-section">
        <div class="section-title">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            <h4>Reportes Imprimibles en PDF</h4>
        </div>
        <p class="section-subtitle">Documentos formales con encabezado institucional, listos para imprimir o archivar</p>

        <div class="row g-4">

            {{-- Préstamos por Período --}}
            <div class="col-md-6 col-lg-4">
                <div class="card-reporte color-blue">
                    <div class="card-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <h6>Préstamos por Período</h6>
                    <p class="card-desc">Reporte completo de préstamos en un rango de fechas con estadísticas, top destinos y análisis por mes.</p>
                    <a href="{{ route('admin.reportes.prestamos') }}" target="_blank" class="btn-reporte">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Generar PDF
                    </a>
                </div>
            </div>

            {{-- Préstamos Vencidos --}}
            <div class="col-md-6 col-lg-4">
                <div class="card-reporte color-red">
                    <div class="card-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <h6>Préstamos Vencidos y Por Vencer</h6>
                    <p class="card-desc">Listado priorizado con días de atraso, datos de contacto y préstamos que vencen en los próximos 7 días.</p>
                    <a href="{{ route('admin.reportes.prestamos-vencidos') }}" target="_blank" class="btn-reporte">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Generar PDF
                    </a>
                </div>
            </div>

            {{-- Inventario Completo --}}
            <div class="col-md-6 col-lg-4">
                <div class="card-reporte color-green">
                    <div class="card-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                    </div>
                    <h6>Inventario Completo</h6>
                    <p class="card-desc">Todos los activos con sus componentes anidados, agrupados por categoría, con estado y responsable.</p>
                    <a href="{{ route('admin.reportes.inventario-completo') }}" target="_blank" class="btn-reporte">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Generar PDF
                    </a>
                </div>
            </div>

            {{-- Solicitudes Detalladas --}}
            <div class="col-md-6 col-lg-4">
                <div class="card-reporte color-orange">
                    <div class="card-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M22 7l-10 7L2 7"/>
                        </svg>
                    </div>
                    <h6>Solicitudes Detalladas</h6>
                    <p class="card-desc">Todas las solicitudes con estadísticas de estados, prioridades y tiempo promedio de respuesta.</p>
                    <a href="{{ route('admin.reportes.solicitudes-detalle') }}" target="_blank" class="btn-reporte">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Generar PDF
                    </a>
                </div>
            </div>

            {{-- Soporte Técnico --}}
            <div class="col-md-6 col-lg-4">
                <div class="card-reporte color-purple">
                    <div class="card-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <h6>Soporte Técnico</h6>
                    <p class="card-desc">Fichas de soporte con productividad por técnico, promedio de reparación y detalles de diagnóstico.</p>
                    <a href="{{ route('admin.reportes.soporte-detalle') }}" target="_blank" class="btn-reporte">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Generar PDF
                    </a>
                </div>
            </div>

            {{-- Activos por Entidad --}}
            <div class="col-md-6 col-lg-4">
                <div class="card-reporte color-cyan">
                    <div class="card-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="8" width="16" height="12" rx="1"/>
                            <path d="M8 20V8M16 20V8M4 12h16"/>
                        </svg>
                    </div>
                    <h6>Activos por Entidad</h6>
                    <p class="card-desc">Activos agrupados por institución y departamento. Ideal para rendición de cuentas y firmas.</p>
                    <a href="{{ route('admin.reportes.activos-por-entidad') }}" target="_blank" class="btn-reporte">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Generar PDF
                    </a>
                </div>
            </div>

            {{-- Usuarios --}}
            <div class="col-md-6 col-lg-4">
                <div class="card-reporte color-blue">
                    <div class="card-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                    </div>
                    <h6>Usuarios y Roles</h6>
                    <p class="card-desc">Distribución de usuarios por rol, activos, inactivos y pendientes de cambio de clave.</p>
                    <a href="{{ route('admin.reportes.usuarios') }}" target="_blank" class="btn-reporte">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Generar PDF
                    </a>
                </div>
            </div>

            {{-- Auditoría --}}
            <div class="col-md-6 col-lg-4">
                <div class="card-reporte color-gray">
                    <div class="card-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    </div>
                    <h6>Auditoría / Bitácora</h6>
                    <p class="card-desc">Registro completo de acciones del sistema: creaciones, ediciones, eliminaciones y logins.</p>
                    <a href="{{ route('admin.reportes.auditoria') }}" target="_blank" class="btn-reporte">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Generar PDF
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@vite(['resources/js/admin-reportes.js'])
@endsection