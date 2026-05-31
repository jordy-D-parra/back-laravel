@extends('layouts.dashboard')

@section('title', 'Préstamos')

@section('styles')
    @vite(['resources/css/admin-solicitudes.css'])
    @vite(['resources/css/contrast-system.css'])
    @vite(['resources/css/smooth-modals.css'])
    <style>
        /* ----------- NUEVOS ESTILOS SIMILARES A ENTIDADES ----------- */
        .main-container-entidades {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.2rem 30px 1.2rem;
        }
        .custom-title-h3 {
            color: #245694;
            font-weight: 700;
            font-size: 1.58rem;
            margin-bottom: 2px;
            letter-spacing: -.02em;
            text-align: center; /* Centramos el título */
        }
        .custom-subtitle-p {
            color: #76849D;
            margin-bottom: 6px;
            font-size: 1.04rem;
            text-align: center; /* Centramos el subtítulo */
        }
        .main-container-entidades .card-w-shadow {
            margin-left: auto;
            margin-right: auto;
        }
        .card-w-shadow {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 13px 2px rgba(35,80,132,0.08);
            padding: 18px 18px 0 18px;
            margin-bottom: 18px;
        }

        /* --- NUEVOS ESTILOS PARA TARJETAS DE ESTADÍSTICAS EN HORIZONTAL Y CUADROS REDONDEADOS --- */
        .stat-cards-row {
            display: flex;
            gap: 24px;
            margin-bottom: 25px;
            justify-content: center; /* Centramos la fila de tarjetas */
            flex-wrap: wrap;
        }
        .stat-card-rounded {
            display: flex;
            align-items: center;
            min-width: 230px;
            background: #f8fafd;
            border-radius: 24px;
            box-shadow: 0 2px 12px 0 rgba(35,80,132,0.10);
            padding: 24px 30px;
            transition: box-shadow 0.15s;
            position: relative;
        }
        .stat-card-rounded:hover {
            box-shadow: 0 4px 22px 0 rgba(35,80,132,0.15);
        }
        .stat-info {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .stat-number {
            font-size: 1.45rem;
            font-weight: 800;
            color: #245694;
            margin-bottom: 4px;
        }
        .stat-label {
            font-size: 1.01rem;
            color: #7582a0;
            font-weight: 500;
        }
        .stat-icon-circle {
            margin-left: 22px;
            background: #deecfa;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stat-icon-circle svg {
            width: 28px;
            height: 28px;
            color: #407ccf;
        }

        /* Responsive para tarjetas */
        @media (max-width: 900px) {
            .stat-cards-row {
                gap: 16px;
            }
            .stat-card-rounded {
                min-width: 180px;
                padding: 18px 14px;
            }
            .stat-icon-circle {
                width: 38px;
                height: 38px;
            }
            .stat-icon-circle svg {
                width: 22px;
                height: 22px;
            }
        }

        .filters-bar {
            display: flex;
            gap: 14px;
            align-items: center;
            margin: 13px 0 20px 0;
            flex-wrap: wrap;
        }
        .filters-bar > * {
            min-width: 0;
        }

        .btn-outline-primary-dark {
            border: 1.2px solid #245694 !important;
            color: #245694 !important;
            background: transparent !important;
        }
        .btn-outline-primary-dark:hover {
            border: 1.2px solid #18395e !important;
            background: #f0f5fb !important;
            color: #18395e !important;
        }
        .table-custom-bordered {
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 14px;
            background: #fff;
            box-shadow: 0 2px 16px 0 rgba(35,80,132,0.08);
        }
        .table-custom-bordered table {
            margin-bottom: 0 !important;
        }
        .table thead th {
            background: #f4f7fa;
            color: #245694;
            border-bottom: 1.5px solid #e8edf3;
            font-size: 1.04rem;
            font-weight: 600;
            vertical-align: middle;
            padding: .64rem 1.12rem;
        }
        .table tbody tr {
            border-bottom: 1px solid #f2f2f5;
        }
        .table td {
            vertical-align: middle;
            font-size: 1.01rem;
            padding: .66rem 1.12rem;
        }
        .d-flex.justify-content-between.align-items-center.mt-3.flex-wrap.gap-2,
        .pagination-bar {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-top: 22px !important;
            flex-wrap: wrap !important;
            gap: 12px !important;
        }

        .modal-dialog.modal-lg {
            max-width: 900px !important;
        }
        .modal-header.bg-primary-dark {
            background: linear-gradient(135deg, #245694 0%, #3b7bd4 100%);
            color: #fff;
        }
        .modal-header.bg-primary-dark .btn-close {
            filter: brightness(0) invert(1);
        }
        .btn-primary-dark {
            background: linear-gradient(135deg, #245694 0%, #3b7bd4 100%);
            border: none;
            color: #fff;
            font-weight: 500;
        }
        .btn-primary-dark:hover {
            background: #18395e !important;
        }
        .btn-secondary {
            background: #e6ebf3;
            color: #27374a;
            border: none;
        }
        .btn-secondary:hover {
            background: #d3dbe7;
        }
        .btn-accion {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.97rem;
            padding: 0 3px;
            margin-left: 0.16rem;
        }
        .btn-ver { color: #3866ac; }
        .btn-registrar { color: #198754; }
        .btn-editar { color: #efb50c; }
    </style>
@endsection

@section('content')
<div class="container-fluid">

    <!-- Estadísticas de Préstamos -->
    <div class="stat-cards-row">
        <div class="stat-card-rounded">
            <div class="stat-info">
                <div class="stat-number">{{ $totalPrestamos ?? '0' }}</div>
                <div class="stat-label">Total Préstamos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                    <rect x="3" y="8" width="18" height="10" rx="2"/>
                    <path d="M7 8V6a5 5 0 0 1 10 0v2"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-rounded">
            <div class="stat-info">
                <div class="stat-number">{{ $prestamosActivos ?? '0' }}</div>
                <div class="stat-label">Activos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                    <path d="M12 8v4l3 3"/>
                    <circle cx="12" cy="12" r="10"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-rounded">
            <div class="stat-info">
                <div class="stat-number">{{ $prestamosVencidos ?? '0' }}</div>
                <div class="stat-label">Vencidos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M15 9l-6 6M9 9l6 6"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-rounded">
            <div class="stat-info">
                <div class="stat-number">{{ $prestamosCompletados ?? '0' }}</div>
                <div class="stat-label">Préstamos Completados</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M8 12.5l3 3 5-5"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tab-rounded-container">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between" style="gap: 16px;">
            <ul class="nav nav-tabs-custom tabs-rounded mb-1 mb-md-0" id="prestamoTabs" role="tablist" style="margin-bottom: 0;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="prestamos-tab" data-bs-toggle="tab" data-bs-target="#prestamos" type="button" role="tab">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                            <rect x="4" y="8" width="16" height="12" rx="1"/>
                            <path d="M8 20V8M16 20V8M4 12h16"/>
                        </svg>
                        Préstamos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                            <path d="M3 12a9 9 0 1 0 9-9"/>
                            <polyline points="3 7 3 12 8 12"/>
                        </svg>
                        Historial
                    </button>
                </li>
            </ul>
            <div class="tab-header-bar d-flex gap-2 align-items-center" style="margin-bottom: 0;">
                <input type="text" class="form-control form-control-sm" id="buscarPrestamo" placeholder="Buscar préstamo..." style="max-width: 250px; border-radius: 10px;">
                <select class="form-select form-select-sm" id="filtroEstadoPrestamo" style="max-width: 200px; border-radius: 10px;">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activo</option>
                    <option value="vencido">Vencido</option>
                    <option value="finalizado">Finalizado</option>
                </select>
            </div>
        </div>
    </div>

    <style>
        
    </style>

    <div class="tab-content mt-3" id="prestamoTabContent">

        <!-- TAB PRÉSTAMOS ACTIVOS -->
        <div class="tab-pane fade show active" id="prestamos" role="tabpanel">
       
       
            <!-- Sección tipo "página de libro" con navegación suave y cuadros redondeados -->
            <div class="prestamo-book-sections" style="background: #f8fafd; border-radius: 22px; box-shadow: 0 2px 18px 0 rgba(35,80,132,0.09); padding: 26px 22px 20px 22px; margin-top: 20px;">

                <!-- Navegación/paginado tipo "libro" mejorado visualmente -->
                <ul class="nav nav-pills justify-content-center mb-4" id="prestamoBookTabs" role="tablist" style="gap: 18px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active"
                            id="all-prestamos-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#all-prestamos-section"
                            type="button"
                            role="tab"
                            style="border-radius: 18px; padding: 8px 22px; font-weight: 500;">
                            <i class="bi bi-book" style="font-size:16px;"></i> Todos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                            id="active-prestamos-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#active-prestamos-section"
                            type="button"
                            role="tab"
                            style="border-radius: 18px; padding: 8px 22px; font-weight: 500;">
                            <i class="bi bi-bookmark-star" style="font-size:16px;"></i> Activos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                            id="ended-prestamos-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#ended-prestamos-section"
                            type="button"
                            role="tab"
                            style="border-radius: 18px; padding: 8px 22px; font-weight: 500;">
                            <i class="bi bi-check2-square" style="font-size:16px;"></i> Terminados
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="prestamoBookTabContent" style="min-height: 260px;">
                    <!-- Sección TODOS LOS PRÉSTAMOS -->
                    <div class="tab-pane fade show active" id="all-prestamos-section" role="tabpanel">
                        <div class="table-custom-bordered" style="border-radius: 14px; background: #fff; box-shadow: 0 2px 14px 0 rgba(35,80,132,0.07); padding: 0;">
                            <div class="table-container" id="tablaPrestamosAll" style="padding: 18px 0;">
                                <p class="text-center py-4 text-muted m-0">Cargando todos los préstamos...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sección PRÉSTAMOS ACTIVOS -->
                    <div class="tab-pane fade" id="active-prestamos-section" role="tabpanel">
                        <div class="table-custom-bordered" style="border-radius: 14px; background: #fff; box-shadow: 0 2px 14px 0 rgba(35,80,132,0.07); padding: 0;">
                            <div class="table-container" id="tablaPrestamosActivos" style="padding: 18px 0;">
                                <p class="text-center py-4 text-muted m-0">Cargando préstamos activos...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sección PRÉSTAMOS TERMINADOS -->
                    <div class="tab-pane fade" id="ended-prestamos-section" role="tabpanel">
                        <div class="table-custom-bordered" style="border-radius: 14px; background: #fff; box-shadow: 0 2px 14px 0 rgba(35,80,132,0.07); padding: 0;">
                            <div class="table-container" id="tablaPrestamosTerminados" style="padding: 18px 0;">
                                <p class="text-center py-4 text-muted m-0">Cargando préstamos terminados...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
       

        <!-- TAB HISTORIAL -->
        <div class="tab-pane fade" id="historial" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="buscarHistorial" placeholder="Buscar historial..." style="max-width: 300px;">
                </div>
            </div>
            <div class="table-container" id="tablaHistorial">
                <p class="text-center py-4 text-muted">Cargando historial...</p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODALES -->
<!-- ============================================ -->

<!-- Modal Préstamo -->
<div class="modal fade" id="modalPrestamo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formPrestamo" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethodPrestamo">
                <input type="hidden" name="id" id="prestamoId">
                <div class="modal-header bg-primary-dark">
                    <h5 class="modal-title" id="modalPrestamoLabel">Nuevo Préstamo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="prestamo_solicitante" class="form-label">Solicitante <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prestamo_solicitante" name="solicitante" required maxlength="150">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="prestamo_fecha" class="form-label">Fecha de Préstamo <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="prestamo_fecha" name="fecha" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="prestamo_estado" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select class="form-select" id="prestamo_estado" name="estado" required>
                                <option value="">Seleccionar...</option>
                                <option value="activo">Activo</option>
                                <option value="vencido">Vencido</option>
                                <option value="finalizado">Finalizado</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="prestamo_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="prestamo_descripcion" name="descripcion" rows="2" maxlength="300"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="prestamo_producto" class="form-label">Producto <span class="text-danger">*</span></label>
                            <select class="form-select" id="prestamo_producto" name="producto_id" required>
                                <option value="">Seleccionar producto...</option>
                              
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="prestamo_cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="prestamo_cantidad" name="cantidad" min="1" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="prestamo_devolucion" class="form-label">Fecha Devolución</label>
                            <input type="date" class="form-control" id="prestamo_devolucion" name="fecha_devolucion">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary-dark" onclick="limpiarFormPrestamo()">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px;margin-right:4px;">
                            <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                        </svg>
                        Limpiar
                    </button>
                    <button type="submit" class="btn btn-primary-dark">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalle Préstamo -->
<div class="modal fade" id="modalDetallePrestamo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetallePrestamoLabel">Detalle del Préstamo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detallePrestamoContenido"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="modalEliminarPrestamo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="delete-warning" id="deleteWarningPrestamo" style="display: none;"></div>
                <p>¿Está seguro de eliminar <strong id="deletePrestamoNombre"></strong>?</p>
                <p id="deleteAdvertenciaPrestamo" style="display: none; font-size: 0.85rem; color: #c5221f;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminarPrestamo">Eliminar</button>
            </div>
        </div>
    </div>
</div>

@endsection
