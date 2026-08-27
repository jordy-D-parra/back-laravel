@extends('layouts.dashboard')

@section('title', 'Inventario')

@section('styles')
    @vite(['resources/css/admin-inventario.css'])
    <style>
        /* ========== ESTILOS PARA REPORTES (SOLO LO NUEVO) ========== */
        .reporte-filters {
            background: white;
            border-radius: 12px;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }
        .reporte-filters .form-control,
        .reporte-filters .form-select {
            border-radius: 8px;
            border: 1px solid #e9ecef;
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
        }
        .reporte-filters .form-control:focus,
        .reporte-filters .form-select:focus {
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30,60,114,0.1);
        }
        .reporte-filters .btn-export {
            margin-left: auto;
            display: flex;
            gap: 0.5rem;
        }

        .reporte-resultados {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #e9ecef;
            overflow: hidden;
        }
        .reporte-resultados .table {
            margin-bottom: 0;
            font-size: 0.85rem;
        }
        .reporte-resultados .table thead th {
            background: #f8f9fc;
            color: #1e3c72;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #1e3c72;
            padding: 0.75rem;
            white-space: nowrap;
        }
        .reporte-resultados .table tbody td {
            vertical-align: middle;
            padding: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .reporte-resultados .table tbody tr:hover {
            background: #f8fafc;
        }

        .btn-export-pdf {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-export-pdf:hover {
            background: #c82333;
            color: white;
            transform: translateY(-1px);
        }

        .btn-export-excel {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-export-excel:hover {
            background: #1e7e34;
            color: white;
            transform: translateY(-1px);
        }

        .stat-reporte {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-reporte-item {
            background: white;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1px solid #e9ecef;
            text-align: center;
        }
        .stat-reporte-item .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e3c72;
        }
        .stat-reporte-item .stat-label {
            font-size: 0.65rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        .stat-reporte-item .stat-number.vencido { color: #dc3545; }
        .stat-reporte-item .stat-number.prestado { color: #f59e0b; }
        .stat-reporte-item .stat-number.disponible { color: #1e7e34; }

        .tab-badge {
            background: #eef3fc;
            color: #1e3c72;
            padding: 0.1rem 0.5rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            margin-left: 4px;
        }
        .nav-tabs-custom .nav-link.active .tab-badge {
            background: #1e3c72;
            color: white;
        }

        @media (max-width: 768px) {
            .stat-reporte {
                grid-template-columns: repeat(2, 1fr);
            }
            .reporte-filters {
                flex-direction: column;
                align-items: stretch;
            }
            .reporte-filters .btn-export {
                margin-left: 0;
                justify-content: center;
            }
        }
        @media (max-width: 576px) {
            .stat-reporte {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4">

    <!-- ========== HEADER CON GRADIENTE ========== -->
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:10px;">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
                Inventario
            </h4>
            <p>Gestión de activos y componentes tecnológicos</p>
        </div>
    </div>

    <!-- ========== TARJETAS DE ESTADÍSTICAS ========== -->
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalActivos }}</div>
                <div class="stat-label">Total Activos</div>
            </div>
            <div class="stat-icon-circle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                    <line x1="9" y1="4" x2="9" y2="20"/>
                    <line x1="15" y1="4" x2="15" y2="20"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalComponentes }}</div>
                <div class="stat-label">Total Componentes</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(23, 162, 184, 0.1);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#17a2b8" stroke-width="1.8">
                    <rect x="2" y="6" width="20" height="12" rx="2" ry="2"/>
                    <line x1="9" y1="6" x2="9" y2="18"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $componentesBodega }}</div>
                <div class="stat-label">En Bodega</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(108, 117, 125, 0.1);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="1.8">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/>
                    <polyline points="3 9 12 13 21 9"/>
                    <path d="M12 13v9"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $activosPrestados }}</div>
                <div class="stat-label">Prestados</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(255, 193, 7, 0.1);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f6c23e" stroke-width="1.8">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <path d="M17 11l2.5-2.5M22 9l-2.5 2.5M19 11.5V6"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- ========== BOTONES PARA CAMBIAR ENTRE ACTIVOS Y COMPONENTES ========== -->
    <div class="mb-3">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary-dark active" id="btnActivos" onclick="mostrarActivos()" >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; margin-right:4px;">
                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                    <line x1="9" y1="4" x2="9" y2="20"/>
                    <line x1="15" y1="4" x2="15" y2="20"/>
                </svg>
                Activos
                <span class="badge bg-light text-dark ms-1">{{ $totalActivos }}</span>
            </button>
            <button type="button" class="btn btn-outline-primary-dark" id="btnComponentes" onclick="mostrarComponentes()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; margin-right:4px;">
                    <rect x="2" y="6" width="20" height="12" rx="2" ry="2"/>
                    <line x1="9" y1="6" x2="9" y2="18"/>
                </svg>
                Componentes
                <span class="badge bg-light text-dark ms-1">{{ $totalComponentes }}</span>
            </button>
            <!-- ========== 🆕 BOTÓN REPORTES ========== -->
            <button type="button" class="btn btn-outline-primary-dark" id="btnReportes" onclick="mostrarReportes()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; margin-right:4px;">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="3" y1="9" x2="21" y2="9"/>
                    <line x1="9" y1="21" x2="9" y2="9"/>
                </svg>
                Reportes
            </button>
        </div>
    </div>

    <!-- ========== SECCIÓN DE ACTIVOS ========== -->
    <div id="seccionActivos">
        <div class="filters-bar">
            <div class="filtro-busqueda">
                <div class="input-group">
                    <span class="input-group-text">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </span>
                    <input type="text" class="form-control" id="buscarActivos"
                           placeholder="Buscar por serial, modelo...">
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if(auth()->user()->hasPermission('crear-activo'))
                <button class="btn btn-primary-dark btn-accion" onclick="abrirModalActivo()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block; margin-right:4px;">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo Activo
                </button>
                @endif
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
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
        </div>
    </div>

    <!-- ========== SECCIÓN DE COMPONENTES ========== -->
    <div id="seccionComponentes" style="display:none;">
        <div class="filters-bar">
            <div class="filtro-busqueda">
                <div class="input-group">
                    <span class="input-group-text">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </span>
                    <input type="text" class="form-control" id="buscarComponentes"
                           placeholder="Buscar por tipo, marca, serial...">
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if(auth()->user()->hasPermission('crear-componente'))
                <button class="btn btn-primary-dark btn-accion" onclick="abrirModalComponente()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block; margin-right:4px;">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo Componente
                </button>
                @endif
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
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
        </div>
    </div>

    <!-- ========== 🆕 SECCIÓN DE REPORTES (CORREGIDA) ========== -->
    <div id="seccionReportes" style="display:none;">
        <div class="reporte-filters">
            <!-- ========== SELECTOR DE TIPO DE REPORTE ========== -->
            <div class="d-flex gap-2 align-items-center">
                <label class="form-label mb-0 fw-bold" style="font-size:0.8rem; color:#1e3c72;">Reporte de:</label>
                <select class="form-select" id="tipoReporte" style="width: 150px;">
                    <option value="activos">Activos</option>
                    <option value="componentes">Componentes</option>
                </select>
            </div>

            <div class="input-group" style="flex: 1; min-width: 200px;">
                <span class="input-group-text bg-white border-end-0">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </span>
                <input type="text" class="form-control border-start-0" id="buscarReporte" placeholder="Buscar por serial, modelo, tipo...">
            </div>

            <select class="form-select" id="filtroEstadoReporte" style="width: 150px;">
                <option value="">Todos los estados</option>
                <option value="Disponible">Disponible</option>
                <option value="Prestado">Prestado</option>
                <option value="En reparación">En reparación</option>
                <option value="En bodega">En bodega</option>
                <option value="Desechado">Desechado</option>
                <option value="instalado">Instalado (Componentes)</option>
                <option value="en_bodega">En Bodega (Componentes)</option>
            </select>

            <select class="form-select" id="filtroCategoriaReporte" style="width: 150px;">
                <option value="">Todas las categorías</option>
            </select>

            <div class="btn-export">
                <button class="btn btn-outline-secondary" id="limpiarFiltrosReporte" style="border-radius: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 4 23 10 17 10"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                    Limpiar
                </button>
                <button class="btn btn-export-pdf" onclick="exportarReportePDF()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M18 9H6"/>
                        <rect x="4" y="12" width="16" height="10" rx="1"/>
                        <line x1="8" y1="17" x2="16" y2="17"/>
                        <line x1="8" y1="21" x2="12" y2="21"/>
                        <line x1="16" y1="21" x2="16" y2="21"/>
                    </svg>
                    Exportar PDF
                </button>
                <button class="btn btn-export-excel" onclick="exportarReporteExcel()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M21 4H3v16h18V4z"/>
                        <polyline points="8 10 12 14 16 10"/>
                        <line x1="12" y1="14" x2="12" y2="20"/>
                    </svg>
                    Exportar Excel
                </button>
            </div>
        </div>

        <!-- Estadísticas del reporte -->
        <div class="stat-reporte" id="reporteStats">
            <div class="stat-reporte-item">
                <div class="stat-number" id="reporteTotal">0</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-reporte-item">
                <div class="stat-number disponible" id="reporteDisponibles">0</div>
                <div class="stat-label">Disponibles</div>
            </div>
            <div class="stat-reporte-item">
                <div class="stat-number prestado" id="reportePrestados">0</div>
                <div class="stat-label">Prestados</div>
            </div>
            <div class="stat-reporte-item">
                <div class="stat-number reparacion" id="reporteReparacion">0</div>
                <div class="stat-label">En Reparación / Bodega</div>
            </div>
        </div>

        <!-- Resultados del reporte -->
        <div class="reporte-resultados">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr id="reporteHeaders">
                            <!-- Los headers se actualizan dinámicamente según el tipo -->
                            <th>Serial</th>
                            <th>Modelo / Tipo</th>
                            <th>Marca</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Ubicación</th>
                            <th>Responsable</th>
                        </tr>
                    </thead>
                    <tbody id="reporteTablaBody">
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" class="mb-2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                </svg>
                                <p>Aplique filtros para generar el reporte</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========== MODALES ========== -->
    <!-- (Mantén todos tus modales existentes sin cambios) -->

    <!-- ========== MODAL ACTIVO ========== -->
    <div class="modal fade" id="modalActivo" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalActivoLabel">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:8px;">
                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                            <line x1="9" y1="4" x2="9" y2="20"/>
                            <line x1="15" y1="4" x2="15" y2="20"/>
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
                                <div id="serialFeedback" class="small mt-1"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Modelo <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="activo_modelo_buscar" placeholder="Escriba para buscar modelo..." autocomplete="off" oninput="filtrarModelos()" onfocus="filtrarModelos()">
                                    <input type="hidden" id="activo_modelo_id" name="modelo_id">
                                    <div id="modeloDropdown" class="list-group position-absolute w-100" style="display:none; z-index:1000; max-height:200px; overflow-y:auto; background:white; border:1px solid #dee2e6; border-radius:0 0 8px 8px;"></div>
                                </div>
                                <div id="modeloInfoBadges" class="mt-2"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Estatus</label>
                                <select class="form-select" id="activo_id_estatus" name="id_estatus">
                                    <option value="">Seleccionar...</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Institución</label>
                                <select class="form-select" id="activo_institucion_id" name="institucion_id">
                                    <option value="">Seleccionar...</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Responsable</label>
                                <select class="form-select" id="activo_responsable_id" name="responsable_id">
                                    <option value="">Seleccionar...</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ubicación</label>
                                <input type="text" class="form-control" id="activo_ubicacion" name="ubicacion" placeholder="Oficina 3B">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fecha Adquisición</label>
                                <input type="date" class="form-control" id="activo_fecha_adquisicion" name="fecha_adquisicion">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fin Garantía</label>
                                <input type="date" class="form-control" id="activo_fecha_fin_garantia" name="fecha_fin_garantia">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vida Útil (años)</label>
                                <input type="number" class="form-control" id="activo_vida_util_anos" name="vida_util_anos" min="1">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="activo_observaciones" name="observaciones" rows="2"></textarea>
                        </div>
                        <hr>
                        <h6 class="fw-bold mb-3" style="color: var(--primary-dark);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; margin-right:6px;">
                                <rect x="2" y="6" width="20" height="12" rx="2" ry="2"/>
                                <line x1="9" y1="6" x2="9" y2="18"/>
                            </svg>
                            Componentes del Equipo
                        </h6>
                        <div id="componentesActivoContainer">
                            <p class="text-muted text-center py-3">Seleccione un modelo para cargar sus componentes.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-dark">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:4px;">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                            </svg>
                            Guardar Activo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========== MODAL COMPONENTE ========== -->
    <div class="modal fade" id="modalComponente" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalComponenteLabel">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:8px;">
                            <rect x="2" y="6" width="20" height="12" rx="2" ry="2"/>
                            <line x1="9" y1="6" x2="9" y2="18"/>
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
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select class="form-select" id="comp_tipo" name="tipo" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="RAM">RAM</option>
                                    <option value="Disco">Disco</option>
                                    <option value="Batería">Batería</option>
                                    <option value="Cargador">Cargador</option>
                                    <option value="Pantalla">Pantalla</option>
                                    <option value="Teclado">Teclado</option>
                                    <option value="Mouse">Mouse</option>
                                    <option value="Procesador">Procesador</option>
                                    <option value="Tarjeta">Tarjeta</option>
                                    <option value="Cable">Cable</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Marca</label>
                                <input type="text" class="form-control" id="comp_marca" name="marca">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Serial</label>
                                <input type="text" class="form-control" id="comp_serial" name="serial">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Capacidad</label>
                                <input type="text" class="form-control" id="comp_capacidad" name="capacidad" placeholder="8GB, 512GB">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="comp_estado" name="estado">
                                    <option value="en_bodega">En Bodega</option>
                                    <option value="instalado">Instalado</option>
                                    <option value="prestado">Prestado</option>
                                    <option value="en_reparacion">En Reparación</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Institución</label>
                                <select class="form-select" id="comp_institucion_id" name="institucion_id">
                                    <option value="">Seleccionar...</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Responsable</label>
                                <select class="form-select" id="comp_responsable_id" name="responsable_id">
                                    <option value="">Seleccionar...</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="comp_ubicacion" name="ubicacion" placeholder="Bodega Central">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="comp_observaciones" name="observaciones" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-dark">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:4px;">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                            </svg>
                            Guardar Componente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========== MODAL DETALLE ========== -->
    <div class="modal fade" id="modalDetalle" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalleLabel">Detalle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalleContenido">Cargando...</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== MODAL ELIMINAR ========== -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Eliminar este registro?</p>
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

    <!-- ========== MODAL CAMBIAR ESTADO ========== -->
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:8px;">
                            <polyline points="1 4 1 10 7 10"/>
                            <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
                        </svg>
                        Cambiar Estado
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Activo: <strong id="estadoSerial"></strong></p>
                    <p class="mb-2">Estado actual: <strong id="estadoActual"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Nuevo estado:</label>
                        <select id="nuevoEstadoSelect" class="form-select">
                            <option value="">Seleccionar estado...</option>
                        </select>
                    </div>
                    <p class="small text-muted mt-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" style="display:inline-block; margin-right:4px;">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4M12 8h.01"/>
                        </svg>
                        Los estados terminales no se pueden cambiar.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary-dark" id="btnConfirmarCambioEstado">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:4px;">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Cambiar Estado
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    @vite(['resources/js/admin-inventario.js'])
    <script>
        window.userPermissions = @json(auth()->user()->rol->permisos->pluck('nombre'));
        function authUserHasPermission(permiso) {
            return window.userPermissions.includes(permiso);
        }

        // ============================================================
        // FUNCIONES PARA CAMBIAR ENTRE ACTIVOS Y COMPONENTES
        // ============================================================
        function mostrarActivos() {
            document.getElementById('seccionActivos').style.display = 'block';
            document.getElementById('seccionComponentes').style.display = 'none';
            document.getElementById('seccionReportes').style.display = 'none';

            document.getElementById('btnActivos').className = 'btn btn-primary-dark active';
            document.getElementById('btnComponentes').className = 'btn btn-outline-primary-dark';
            document.getElementById('btnReportes').className = 'btn btn-outline-primary-dark';

            if (typeof cargarActivos === 'function') {
                cargarActivos();
            }
        }

        function mostrarComponentes() {
            document.getElementById('seccionActivos').style.display = 'none';
            document.getElementById('seccionComponentes').style.display = 'block';
            document.getElementById('seccionReportes').style.display = 'none';

            document.getElementById('btnComponentes').className = 'btn btn-primary-dark active';
            document.getElementById('btnActivos').className = 'btn btn-outline-primary-dark';
            document.getElementById('btnReportes').className = 'btn btn-outline-primary-dark';

            if (typeof cargarComponentes === 'function') {
                cargarComponentes();
            }
        }

        function mostrarReportes() {
            document.getElementById('seccionActivos').style.display = 'none';
            document.getElementById('seccionComponentes').style.display = 'none';
            document.getElementById('seccionReportes').style.display = 'block';

            document.getElementById('btnReportes').className = 'btn btn-primary-dark active';
            document.getElementById('btnActivos').className = 'btn btn-outline-primary-dark';
            document.getElementById('btnComponentes').className = 'btn btn-outline-primary-dark';

            // Cargar categorías para el filtro
            cargarCategoriasReporte();
            // Generar reporte automáticamente
            generarReporte();
        }

        // ============================================================
        // FUNCIONES PARA REPORTES (CORREGIDAS Y FUNCIONALES)
        // ============================================================

        function cargarCategoriasReporte() {
            fetch('/admin/equipos/categorias-list', {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('filtroCategoriaReporte');
                    if (select) {
                        select.innerHTML = '<option value="">Todas las categorías</option>';
                        data.data.forEach(cat => {
                            select.innerHTML += `<option value="${cat.id}">${escapeHtml(cat.nombre)}</option>`;
                        });
                    }
                }
            })
            .catch(error => console.error('Error cargando categorías:', error));
        }

        function generarReporte() {
            const buscar = document.getElementById('buscarReporte')?.value || '';
            const estado = document.getElementById('filtroEstadoReporte')?.value || '';
            const categoria = document.getElementById('filtroCategoriaReporte')?.value || '';
            const tipo = document.getElementById('tipoReporte')?.value || 'activos';

            console.log('Generando reporte de:', tipo); // Depuración

            // Actualizar headers según el tipo
            actualizarHeaders(tipo);

            // Mostrar loading
            const tbody = document.getElementById('reporteTablaBody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="${tipo === 'componentes' ? 9 : 7}" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2 text-muted">Generando reporte de ${tipo}...</p>
                        </td>
                    </tr>
                `;
            }

            let url = `/admin/reportes/inventario?tipo=${tipo}`;
            if (buscar) url += `&buscar=${encodeURIComponent(buscar)}`;
            if (estado) url += `&estado=${encodeURIComponent(estado)}`;
            if (categoria) url += `&categoria=${encodeURIComponent(categoria)}`;

            fetch(url, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta del servidor:', data); // Depuración
                if (data.success) {
                    renderizarReporte(data);
                } else {
                    if (tbody) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="${tipo === 'componentes' ? 9 : 7}" class="text-center py-4 text-danger">
                                    ${data.message || 'Error al generar el reporte'}
                                </td>
                            </tr>
                        `;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="${tipo === 'componentes' ? 9 : 7}" class="text-center py-4 text-danger">
                                Error de conexión al servidor
                            </td>
                        </tr>
                    `;
                }
            });
        }

        function actualizarHeaders(tipo) {
            const thead = document.getElementById('reporteHeaders');
            if (!thead) return;

            if (tipo === 'componentes') {
                thead.innerHTML = `
                    <th>Tipo</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Serial</th>
                    <th>Capacidad</th>
                    <th>Estado</th>
                    <th>Ubicación</th>
                    <th>Activo</th>
                    <th>Responsable</th>
                `;
            } else {
                thead.innerHTML = `
                    <th>Serial</th>
                    <th>Modelo</th>
                    <th>Marca</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Ubicación</th>
                    <th>Responsable</th>
                `;
            }
        }

        function renderizarReporte(data) {
            console.log('Renderizando reporte tipo:', data.tipo); // Depuración

            // Actualizar estadísticas
            document.getElementById('reporteTotal').textContent = data.total || 0;
            document.getElementById('reporteDisponibles').textContent = data.disponibles || 0;
            document.getElementById('reportePrestados').textContent = data.prestados || 0;
            document.getElementById('reporteReparacion').textContent = data.reparacion || 0;

            // Actualizar tabla
            const tbody = document.getElementById('reporteTablaBody');
            if (!tbody) return;

            const items = data.data || [];
            console.log('Items a renderizar:', items.length); // Depuración

            if (items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="${data.tipo === 'componentes' ? 9 : 7}" class="text-center py-4 text-muted">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" class="mb-2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                            <p>No se encontraron ${data.tipo === 'componentes' ? 'componentes' : 'activos'} con los filtros seleccionados</p>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            const esComponente = data.tipo === 'componentes';

            items.forEach(item => {
                if (esComponente) {
                    // Tabla de componentes - USANDO LA ESTRUCTURA CORRECTA
                    const estado = item.estado || 'N/A';
                    const estadoColor = getEstadoColorComponente(estado);
                    html += `
                        <tr>
                            <td><span class="fw-medium" style="color: #1e3c72;">${escapeHtml(item.tipo || 'N/A')}</span></td>
                            <td>${escapeHtml(item.marca || 'N/A')}</td>
                            <td>${escapeHtml(item.modelo || 'N/A')}</td>
                            <td>${escapeHtml(item.serial || 'N/A')}</td>
                            <td>${escapeHtml(item.capacidad || 'N/A')}</td>
                            <td><span class="badge bg-${estadoColor}">${escapeHtml(estado)}</span></td>
                            <td>${escapeHtml(item.ubicacion || 'No especificada')}</td>
                            <td>${escapeHtml(item.activo_serial || 'No instalado')}</td>
                            <td>${escapeHtml(item.responsable || 'No asignado')}</td>
                        </tr>
                    `;
                } else {
                    // Tabla de activos
                    const estado = item.estado || 'N/A';
                    const estadoColor = getEstadoColor(estado);
                    html += `
                        <tr>
                            <td><span class="fw-medium" style="color: #1e3c72;">${escapeHtml(item.serial || 'N/A')}</span></td>
                            <td>${escapeHtml(item.modelo || 'N/A')}</td>
                            <td>${escapeHtml(item.marca || 'N/A')}</td>
                            <td>${escapeHtml(item.categoria || 'N/A')}</td>
                            <td><span class="badge bg-${estadoColor}">${escapeHtml(estado)}</span></td>
                            <td>${escapeHtml(item.ubicacion || 'No especificada')}</td>
                            <td>${escapeHtml(item.responsable || 'No asignado')}</td>
                        </tr>
                    `;
                }
            });

            tbody.innerHTML = html;
            console.log('Tabla renderizada con', items.length, 'items'); // Depuración
        }

        function getEstadoColor(estado) {
            const colors = {
                'Disponible': 'success',
                'Prestado': 'warning',
                'En reparación': 'danger',
                'En bodega': 'secondary',
                'Desechado': 'dark'
            };
            return colors[estado] || 'secondary';
        }

        function getEstadoColorComponente(estado) {
            const colors = {
                'En Bodega': 'secondary',
                'Instalado': 'primary',
                'Prestado': 'warning',
                'En Reparación': 'danger',
                'Desechado': 'dark'
            };
            return colors[estado] || 'secondary';
        }

        // ============================================================
        // EXPORTAR REPORTES
        // ============================================================
        function exportarReportePDF() {
            const buscar = document.getElementById('buscarReporte')?.value || '';
            const estado = document.getElementById('filtroEstadoReporte')?.value || '';
            const categoria = document.getElementById('filtroCategoriaReporte')?.value || '';
            const tipo = document.getElementById('tipoReporte')?.value || 'activos';

            let url = `/admin/reportes/inventario/exportar-pdf?tipo=${tipo}`;
            if (buscar) url += `&buscar=${encodeURIComponent(buscar)}`;
            if (estado) url += `&estado=${encodeURIComponent(estado)}`;
            if (categoria) url += `&categoria=${encodeURIComponent(categoria)}`;

            window.open(url, '_blank');
        }

        function exportarReporteExcel() {
            const buscar = document.getElementById('buscarReporte')?.value || '';
            const estado = document.getElementById('filtroEstadoReporte')?.value || '';
            const categoria = document.getElementById('filtroCategoriaReporte')?.value || '';
            const tipo = document.getElementById('tipoReporte')?.value || 'activos';

            let url = `/admin/reportes/inventario/exportar-excel?tipo=${tipo}`;
            if (buscar) url += `&buscar=${encodeURIComponent(buscar)}`;
            if (estado) url += `&estado=${encodeURIComponent(estado)}`;
            if (categoria) url += `&categoria=${encodeURIComponent(categoria)}`;

            window.open(url, '_blank');
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ============================================================
        // DEBOUNCE
        // ============================================================
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // ============================================================
        // EVENTOS DE FILTROS EN REPORTES
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar activos al inicio
            if (typeof cargarActivos === 'function') {
                cargarActivos();
            }

            // Búsqueda en tiempo real - Activos
            const buscarActivos = document.getElementById('buscarActivos');
            if (buscarActivos) {
                buscarActivos.addEventListener('input', function() {
                    if (typeof cargarActivos === 'function') {
                        cargarActivos();
                    }
                });
            }

            // Búsqueda en tiempo real - Componentes
            const buscarComponentes = document.getElementById('buscarComponentes');
            if (buscarComponentes) {
                buscarComponentes.addEventListener('input', function() {
                    if (typeof cargarComponentes === 'function') {
                        cargarComponentes();
                    }
                });
            }

            // Filtros de activos
            const filtroEstadoActivos = document.getElementById('filtroEstadoActivos');
            if (filtroEstadoActivos) {
                filtroEstadoActivos.addEventListener('change', function() {
                    if (typeof cargarActivos === 'function') {
                        cargarActivos();
                    }
                });
            }

            // Filtros de componentes
            const filtroTipoComponentes = document.getElementById('filtroTipoComponentes');
            if (filtroTipoComponentes) {
                filtroTipoComponentes.addEventListener('change', function() {
                    if (typeof cargarComponentes === 'function') {
                        cargarComponentes();
                    }
                });
            }

            const filtroEstadoComponentes = document.getElementById('filtroEstadoComponentes');
            if (filtroEstadoComponentes) {
                filtroEstadoComponentes.addEventListener('change', function() {
                    if (typeof cargarComponentes === 'function') {
                        cargarComponentes();
                    }
                });
            }

            // ============================================================
            // 🆕 EVENTOS DE FILTROS EN REPORTES (CORREGIDO)
            // ============================================================
            const buscarReporte = document.getElementById('buscarReporte');
            const filtroEstadoReporte = document.getElementById('filtroEstadoReporte');
            const filtroCategoriaReporte = document.getElementById('filtroCategoriaReporte');
            const limpiarFiltrosReporte = document.getElementById('limpiarFiltrosReporte');
            const tipoReporte = document.getElementById('tipoReporte');

            function aplicarFiltrosReporte() {
                generarReporte();
            }

            if (buscarReporte) {
                buscarReporte.addEventListener('input', debounce(aplicarFiltrosReporte, 400));
            }
            if (filtroEstadoReporte) {
                filtroEstadoReporte.addEventListener('change', aplicarFiltrosReporte);
            }
            if (filtroCategoriaReporte) {
                filtroCategoriaReporte.addEventListener('change', aplicarFiltrosReporte);
            }
            if (tipoReporte) {
                tipoReporte.addEventListener('change', function() {
                    actualizarHeaders(this.value);
                    aplicarFiltrosReporte();
                });
            }
            if (limpiarFiltrosReporte) {
                limpiarFiltrosReporte.addEventListener('click', function() {
                    if (buscarReporte) buscarReporte.value = '';
                    if (filtroEstadoReporte) filtroEstadoReporte.value = '';
                    if (filtroCategoriaReporte) filtroCategoriaReporte.value = '';
                    aplicarFiltrosReporte();
                });
            }

            // ========== CORRECCIÓN: HEADERS INICIALES ==========
            // Asegurar que los headers sean correctos al cargar la página
            const tipoInicial = tipoReporte ? tipoReporte.value : 'activos';
            actualizarHeaders(tipoInicial);
        });
    </script>
@endsection