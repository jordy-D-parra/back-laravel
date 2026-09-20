@extends('layouts.dashboard')

@section('title', 'Reportes del Sistema')

@section('styles')
@vite(['resources/css/admin-reportes.css'])
@endsection

@section('content')
<div class="container-fluid px-4">

    {{-- HEADER --}}
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
            <p>Genera reportes de inventario, solicitudes y soporte técnico</p>
        </div>
    </div>

    {{-- STATS GENERALES --}}
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
    </div>

    {{-- TABS --}}
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
                            <button class="btn btn-danger btn-sm w-100" onclick="Reportes.exportarPdf('inventario')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; vertical-align:middle;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                                PDF
                            </button>
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
                            <button class="btn btn-danger btn-sm w-100" onclick="Reportes.exportarPdf('solicitudes')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; vertical-align:middle;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                                PDF
                            </button>
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
                            <button class="btn btn-danger btn-sm w-100" onclick="Reportes.exportarPdf('soporte')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; vertical-align:middle;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                                PDF
                            </button>
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
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@vite(['resources/js/admin-reportes.js'])
@endsection