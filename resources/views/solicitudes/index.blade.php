@extends('layouts.dashboard')

@section('title', 'Mis Solicitudes de Préstamo')

@section('content')
<div class="container-fluid px-4">
    <!-- Tarjeta de bienvenida / cabecera -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h2 class="text-white mb-2 d-flex align-items-center gap-2">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
                                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                                    <path d="M8 8h8M8 12h6M8 16h4"/>
                                </svg>
                                Mis Solicitudes de Préstamo
                            </h2>
                            <p class="text-white-50 mb-0">Gestiona tus solicitudes de préstamo de equipos</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="abrirBandejaCorreos()" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 10px; position: relative;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <path d="M22 7l-10 7L2 7"/>
                                </svg>
                                Correos
                                <span id="notificacionCorreos" style="background: #dc3545; color: white; font-size: 11px; padding: 2px 6px; border-radius: 20px; position: absolute; top: -8px; right: -8px; display: none;">0</span>
                            </button>
                            <button onclick="abrirModalCrear()" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 10px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Nueva Solicitud
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 📊 Tarjeta de Estadísticas - Versión Compacta (más pequeñas) -->
    <div class="row mt-3 g-2">
        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-body p-2" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50" style="font-size: 10px;">Total</small>
                            <h4 class="text-white mb-0 fw-bold" id="statsTotal" style="font-size: 1.5rem;">0</h4>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 p-2">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <rect x="4" y="4" width="16" height="16" rx="2"/>
                                <path d="M8 8h8M8 12h6M8 16h4"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-body p-2" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50" style="font-size: 10px;">Pendientes</small>
                            <h4 class="text-white mb-0 fw-bold" id="statsPendientes" style="font-size: 1.5rem;">0</h4>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 p-2">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-body p-2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50" style="font-size: 10px;">Aprobadas</small>
                            <h4 class="text-white mb-0 fw-bold" id="statsAprobadas" style="font-size: 1.5rem;">0</h4>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 p-2">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-body p-2" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50" style="font-size: 10px;">Rechazadas</small>
                            <h4 class="text-white mb-0 fw-bold" id="statsRechazadas" style="font-size: 1.5rem;">0</h4>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 p-2">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="18" y1="6" x2="6" y2="18"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-success border-0 rounded-4" style="background: #d4edda; color: #155724; border-left: 4px solid #28a745;">
                    <div class="d-flex align-items-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-danger border-0 rounded-4" style="background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545;">
                    <div class="d-flex align-items-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4M12 16h.01"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Panel de filtros mejorado -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Buscar</label>
                            <div class="position-relative">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                <input type="text" id="searchInput" class="form-control" placeholder="Buscar por institución o justificación..." style="padding-left: 36px; border-radius: 10px; background: white;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Estado</label>
                            <select id="estadoFilter" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobada">Aprobada</option>
                                <option value="rechazada">Rechazada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Prioridad</label>
                            <select id="prioridadFilter" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todas</option>
                                <option value="baja">Baja</option>
                                <option value="normal">Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Filtro Rápido</label>
                            <select id="filtroRapido" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Seleccionar...</option>
                                <option value="today">Hoy</option>
                                <option value="week">Esta semana</option>
                                <option value="month">Este mes</option>
                                <option value="last7">Últimos 7 días</option>
                                <option value="last30">Últimos 30 días</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Desde</label>
                            <input type="date" id="fechaDesde" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-1">
                            <button id="limpiarFiltros" class="btn w-100" style="background: #6c757d; color: white; border-radius: 10px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 4px;">
                                    <path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/>
                                </svg>
                                Limpiar
                            </button>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Fecha Desde</label>
                            <input type="date" id="fechaDesdeCustom" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Fecha Hasta</label>
                            <input type="date" id="fechaHastaCustom" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button id="aplicarFechas" class="btn w-100" style="background: #1e3c72; color: white; border-radius: 10px;">Aplicar Fechas</button>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="text-muted small">Mostrando <span id="resultadosCount">0</span> solicitudes de <span id="totalRegistrosCount">0</span></span>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <label class="text-muted small mb-0">Registros por página:</label>
                                <select id="perPageSelect" class="form-select form-select-sm" style="width: auto; border-radius: 8px;">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <div class="text-muted small">
                                Página <span id="currentPageDisplay">1</span> de <span id="lastPageDisplay">1</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de listado con columnas ordenables -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-0 overflow-auto" style="max-height: 65vh;">
                    <table class="table table-hover mb-0" style="min-width: 1000px;">
                        <thead style="background: #f8f9fc; position: sticky; top: 0; z-index: 10;">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold sortable" data-sort="index" style="cursor: pointer;">#
                                    <span class="sort-icon ms-1">↕️</span>
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold sortable" data-sort="fecha_solicitud" style="cursor: pointer;">Fecha Solicitud
                                    <span class="sort-icon ms-1">↕️</span>
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold sortable" data-sort="entidad" style="cursor: pointer;">Entidad
                                    <span class="sort-icon ms-1">↕️</span>
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold sortable" data-sort="responsable" style="cursor: pointer;">Responsable
                                    <span class="sort-icon ms-1">↕️</span>
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold sortable" data-sort="fecha_requerida" style="cursor: pointer;">Fecha Requerida
                                    <span class="sort-icon ms-1">↕️</span>
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold sortable" data-sort="fecha_fin" style="cursor: pointer;">Fecha Fin
                                    <span class="sort-icon ms-1">↕️</span>
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold sortable" data-sort="prioridad" style="cursor: pointer;">Prioridad
                                    <span class="sort-icon ms-1">↕️</span>
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold sortable" data-sort="estado" style="cursor: pointer;">Estado
                                    <span class="sort-icon ms-1">↕️</span>
                                </th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">Items</th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody" style="background: white;"></tbody>
                    </table>
                    <div id="skeletonLoader" style="display: none; padding: 20px;">
                        <div class="skeleton-row" style="height: 60px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: loading 1.5s infinite; border-radius: 8px; margin-bottom: 10px;"></div>
                        <div class="skeleton-row" style="height: 60px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: loading 1.5s infinite; border-radius: 8px; margin-bottom: 10px;"></div>
                        <div class="skeleton-row" style="height: 60px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: loading 1.5s infinite; border-radius: 8px; margin-bottom: 10px;"></div>
                        <div class="skeleton-row" style="height: 60px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: loading 1.5s infinite; border-radius: 8px; margin-bottom: 10px;"></div>
                        <div class="skeleton-row" style="height: 60px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: loading 1.5s infinite; border-radius: 8px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Paginación de solicitudes">
                <ul class="pagination justify-content-center" id="paginationContainer">
                    <!-- Los botones de paginación se generarán dinámicamente -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Resto de los modales (se mantienen igual) -->
<!-- Sistema de Notificaciones -->
<div id="notification-container" style="position: fixed; top: 80px; right: 20px; z-index: 99999; width: 350px;"></div>

<!-- MODAL VER DETALLES DE LA SOLICITUD -->
<div id="modalDetalles" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10002; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content rounded-4 border-0" style="background: white; max-height: 85vh; overflow-y: auto;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Detalles de la Solicitud
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalDetalles()"></button>
            </div>
            <div class="modal-body p-4" id="modalDetallesBody" style="background: white;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando...</p>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" onclick="cerrarModalDetalles()" class="btn btn-light px-4" style="border-radius: 10px;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CREAR (se mantiene igual) -->
<div id="modalCrear" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 1000px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Nueva Solicitud de Préstamo
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalCrear()"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto; background: white;">
                <form id="formCrearSolicitud" action="{{ route('solicitudes.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo Solicitante</label>
                            <select name="tipo_solicitante" id="tipoSolicitante" required class="form-select" style="border-radius: 10px; background: white;">
                                <option value="interno">Interno (Departamento/Área)</option>
                                <option value="externo">Externo (Institución)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prioridad</label>
                            <select name="prioridad" required class="form-select" style="border-radius: 10px; background: white;">
                                <option value="baja">Baja</option>
                                <option value="normal">Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>

                        <div id="interno-fields">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Departamento / Área</label>
                                <select name="departamento_id" id="departamentoSelect" class="form-select" style="border-radius: 10px; background: white;">
                                    <option value="">Seleccione un departamento</option>
                                    @foreach($departamentos ?? [] as $departamento)
                                        <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                                    @endforeach
                                    <option value="otro">+ Otro (No está en la lista)</option>
                                </select>
                            </div>
                            <div id="departamento-nuevo-field" style="display: none;" class="mt-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Nuevo Departamento</label>
                                    <input type="text" name="nuevo_departamento" id="nuevoDepartamento" class="form-control" placeholder="Ej: Recursos Humanos" style="border-radius: 10px; background: white;">
                                    <small class="text-muted">Se registrará automáticamente</small>
                                </div>
                            </div>
                        </div>

                        <div id="externo-fields" style="display: none;">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Institución</label>
                                <select name="institucion_id" id="institucionSelect" class="form-select" style="border-radius: 10px; background: white;">
                                    <option value="">Seleccione una institución</option>
                                    @foreach($instituciones ?? [] as $institucion)
                                        <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                                    @endforeach
                                    <option value="otro">+ Otra (No está en la lista)</option>
                                </select>
                            </div>
                            <div id="institucion-nuevo-field" style="display: none;" class="mt-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Nueva Institución</label>
                                    <input type="text" name="nueva_institucion" id="nuevaInstitucion" class="form-control" placeholder="Ej: Universidad Nacional" style="border-radius: 10px; background: white;">
                                    <small class="text-muted">Se registrará automáticamente</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Persona Responsable</label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="flex-grow-1 p-3 bg-light rounded" id="responsableDisplay" style="background: #f8f9fc; border-radius: 10px; min-height: 70px;">
                                    <span class="text-muted">Selecciona una institución o departamento</span>
                                </div>
                                <button type="button" onclick="editarResponsableActual()" class="btn" style="background: #1e3c72; color: white; border-radius: 10px; display: none;" id="btnEditarResponsable">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                                    </svg>
                                    Editar
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Requerida</label>
                            <input type="date" name="fecha_requerida" id="fechaRequeridaInput" required class="form-control" style="border-radius: 10px; background: white;">
                            <small class="text-danger" id="fechaRequeridaError" style="display: none; font-size: 11px;">La fecha no puede ser anterior a hoy</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Fin Estimada</label>
                            <input type="date" name="fecha_fin_estimada" id="fechaFinEstimadaInput" required class="form-control" style="border-radius: 10px; background: white;">
                            <small class="text-danger" id="fechaFinError" style="display: none; font-size: 11px;">La fecha fin debe ser posterior a la fecha requerida</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Justificación</label>
                            <textarea name="justificacion" id="justificacionInput" rows="3" required class="form-control" style="border-radius: 10px; background: white;"></textarea>
                            <small class="text-danger" id="justificacionError" style="display: none; font-size: 11px;">La justificación debe tener al menos 20 caracteres</small>
                            <small class="text-muted" id="justificacionContador" style="font-size: 11px;">0/1000 caracteres</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <textarea name="observaciones" rows="2" class="form-control" style="border-radius: 10px; background: white;"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Oficio Adjunto (PDF)</label>
                            <input type="file" name="oficio_adjunto" accept=".pdf,.doc,.docx" class="form-control" style="border-radius: 10px; background: white;">
                            <small class="text-danger" id="archivoError" style="display: none; font-size: 11px;">El archivo debe ser PDF, DOC o DOCX (máx. 2MB)</small>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold" style="color: #1e3c72;">Items Solicitados</h6>
                                <button type="button" id="add-item-modal" class="btn btn-sm btn-light" style="border-radius: 8px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                                    Agregar Item
                                </button>
                            </div>
                            <div id="items-container-modal">
                                <div class="item-card-modal p-3 mb-3" style="background: #f8f9fc; border: 1px solid #e9ecef; border-radius: 12px;">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="small text-muted">Tipo</label>
                                            <select name="items[0][tipo_item]" required class="form-select form-select-sm" style="border-radius: 8px; background: white;">
                                                <option value="activo">Activo</option>
                                                <option value="periferico">Periférico</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Descripción del Item</label>
                                            <input type="text" name="items[0][item_descripcion]" required placeholder="Ej: Laptop HP, Mouse, Cargador, etc." class="form-control form-control-sm" style="border-radius: 8px; background: white;">
                                            <small class="text-danger item-desc-error" style="display: none; font-size: 10px;">Descripción requerida</small>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small text-muted">Cantidad</label>
                                            <input type="number" name="items[0][cantidad]" min="1" value="1" required class="form-control form-control-sm" style="border-radius: 8px; background: white;">
                                            <small class="text-danger item-cant-error" style="display: none; font-size: 10px;">Mínimo 1</small>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12 text-end">
                                            <button type="button" class="remove-item-modal btn btn-sm text-danger" style="font-size: 14px;">× Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalCrear()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="submit" id="submitSolicitudBtn" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Enviar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR (se mantiene igual) -->
<div id="modalEditar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10003; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 1000px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                    </svg>
                    Editar Solicitud
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalEditar()"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto; background: white;">
                <form id="formEditarSolicitud">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" id="editId">
                    <input type="hidden" name="responsable_id" id="editResponsableId">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo Solicitante</label>
                            <select name="tipo_solicitante" id="editTipoSolicitante" required class="form-select" style="border-radius: 10px; background: white;">
                                <option value="interno">Interno (Departamento/Área)</option>
                                <option value="externo">Externo (Institución)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prioridad</label>
                            <select name="prioridad" id="editPrioridad" required class="form-select" style="border-radius: 10px; background: white;">
                                <option value="baja">Baja</option>
                                <option value="normal">Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>

                        <div class="col-md-12" id="editInternoFields">
                            <label class="form-label fw-semibold">Departamento / Área</label>
                            <select name="departamento_id" id="editDepartamentoId" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Seleccione un departamento</option>
                                @foreach($departamentos ?? [] as $departamento)
                                    <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12" id="editExternoFields" style="display: none;">
                            <label class="form-label fw-semibold">Institución</label>
                            <select name="institucion_id" id="editInstitucionId" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Seleccione una institución</option>
                                @foreach($instituciones ?? [] as $institucion)
                                    <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Persona Responsable</label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="flex-grow-1 p-3 bg-light rounded" id="editResponsableDisplay" style="background: #f8f9fc; border-radius: 10px; min-height: 70px;">
                                    <span class="text-muted">Selecciona una institución o departamento</span>
                                </div>
                                <button type="button" onclick="editarResponsableActualEdit()" class="btn" style="background: #1e3c72; color: white; border-radius: 10px; display: none;" id="btnEditarResponsableEdit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                                    </svg>
                                    Editar
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Requerida</label>
                            <input type="date" name="fecha_requerida" id="editFechaRequerida" required class="form-control" style="border-radius: 10px; background: white;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Fin Estimada</label>
                            <input type="date" name="fecha_fin_estimada" id="editFechaFin" required class="form-control" style="border-radius: 10px; background: white;">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Justificación</label>
                            <textarea name="justificacion" id="editJustificacion" rows="3" required class="form-control" style="border-radius: 10px; background: white;"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <textarea name="observaciones" id="editObservaciones" rows="2" class="form-control" style="border-radius: 10px; background: white;"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalEditar()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="submit" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Actualizar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL BANDEJA DE CORREOS (se mantiene igual) -->
<div id="modalBandeja" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1400px;">
        <div class="modal-content rounded-4 border-0" style="height: 90vh; background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <div>
                    <h5 class="modal-title text-white d-flex align-items-center gap-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M22 7l-10 7L2 7"/>
                        </svg>
                        Bandeja de Correos
                    </h5>
                    <p class="text-white-50 small mb-0">Recibe solicitudes por correo y conviértelas en solicitudes de préstamo</p>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="revisarCorreosManual()" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white; border-radius: 8px;">Revisar ahora</button>
                    <button type="button" class="btn-close btn-close-white" onclick="cerrarBandejaCorreos()"></button>
                </div>
            </div>
            <div class="modal-body p-0 d-flex" style="overflow: hidden; background: white;">
                <div class="col-md-4 border-end" style="overflow-y: auto; background: #f8f9fc;">
                    <div class="p-3 border-bottom" style="background: white;">
                        <input type="text" id="buscarCorreos" class="form-control" placeholder="Buscar correos..." style="border-radius: 10px;">
                    </div>
                    <div id="listaCorreosContainer" class="p-2"></div>
                </div>
                <div class="col-md-8 d-flex flex-column" style="overflow-y: auto; background: white;">
                    <div id="previewCorreo" class="p-4 border-bottom" style="background: #f8f9fc;">
                        <div class="text-center text-muted py-5">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="M22 7l-10 7L2 7"/>
                            </svg>
                            <h6 class="mt-3">Selecciona un correo de la lista</h6>
                            <p class="small">Al seleccionar un correo, se precargarán los datos en el formulario</p>
                        </div>
                    </div>
                    <div class="p-4 overflow-auto" style="background: white;">
                        <h6 class="fw-bold mb-3" style="color: #1e3c72;">Crear solicitud desde este correo</h6>
                        <form id="formSolicitudCorreo" action="{{ route('solicitudes.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="correo_origen" id="correoOrigen">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tipo Solicitante</label>
                                    <select name="tipo_solicitante" id="formTipoSolicitante" required class="form-select" style="border-radius: 10px; background: white;">
                                        <option value="interno">Interno (Departamento/Área)</option>
                                        <option value="externo">Externo (Institución)</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Prioridad</label>
                                    <select name="prioridad" id="formPrioridad" required class="form-select" style="border-radius: 10px; background: white;">
                                        <option value="baja">Baja</option>
                                        <option value="normal">Normal</option>
                                        <option value="alta">Alta</option>
                                        <option value="urgente">Urgente</option>
                                    </select>
                                </div>

                                <div id="formInternoFields">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Departamento / Área</label>
                                        <select name="departamento_id" id="formDepartamentoSelect" class="form-select" style="border-radius: 10px; background: white;">
                                            <option value="">Seleccione un departamento</option>
                                            @foreach($departamentos ?? [] as $departamento)
                                                <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                                            @endforeach
                                            <option value="otro">+ Otro (No está en la lista)</option>
                                        </select>
                                    </div>
                                    <div id="formDepartamentoNuevoField" style="display: none;" class="mt-2">
                                        <label class="form-label fw-semibold">Nuevo Departamento</label>
                                        <input type="text" name="nuevo_departamento" id="formNuevoDepartamento" class="form-control" placeholder="Ej: Recursos Humanos" style="border-radius: 10px; background: white;">
                                    </div>
                                </div>

                                <div id="formExternoFields" style="display: none;">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Institución</label>
                                        <select name="institucion_id" id="formInstitucionSelect" class="form-select" style="border-radius: 10px; background: white;">
                                            <option value="">Seleccione una institución</option>
                                            @foreach($instituciones ?? [] as $institucion)
                                                <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                                            @endforeach
                                            <option value="otro">+ Otra (No está en la lista)</option>
                                        </select>
                                    </div>
                                    <div id="formInstitucionNuevoField" style="display: none;" class="mt-2">
                                        <label class="form-label fw-semibold">Nueva Institución</label>
                                        <input type="text" name="nueva_institucion" id="formNuevaInstitucion" class="form-control" placeholder="Ej: Universidad Nacional" style="border-radius: 10px; background: white;">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Persona Responsable</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="flex-grow-1 p-3 bg-light rounded" id="formResponsableDisplay" style="background: #f8f9fc; border-radius: 10px; min-height: 70px;">
                                            <span class="text-muted">Selecciona una institución o departamento</span>
                                        </div>
                                        <button type="button" onclick="editarResponsableActualForm()" class="btn" style="background: #1e3c72; color: white; border-radius: 10px; display: none;" id="formBtnEditarResponsable">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                                <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                                                <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                                            </svg>
                                            Editar
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Fecha Requerida</label>
                                    <input type="date" name="fecha_requerida" id="formFechaRequerida" required class="form-control" style="border-radius: 10px; background: white;">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Fecha Fin Estimada</label>
                                    <input type="date" name="fecha_fin_estimada" id="formFechaFin" required class="form-control" style="border-radius: 10px; background: white;">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Justificación</label>
                                    <textarea name="justificacion" id="formJustificacion" rows="3" required class="form-control" style="border-radius: 10px; background: white;"></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Observaciones</label>
                                    <textarea name="observaciones" id="formObservaciones" rows="2" class="form-control" style="border-radius: 10px; background: white;"></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Oficio Adjunto</label>
                                    <input type="file" name="oficio_adjunto" id="formAdjunto" accept=".pdf,.doc,.docx" class="form-control" style="border-radius: 10px; background: white;">
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 fw-bold" style="color: #1e3c72;">Items Solicitados</h6>
                                        <button type="button" id="add-item-correo" class="btn btn-sm btn-light" style="border-radius: 8px;">+ Agregar Item</button>
                                    </div>
                                    <div id="items-container-correo">
                                        <div class="item-card-correo p-3 mb-3" style="background: #f8f9fc; border: 1px solid #e9ecef; border-radius: 12px;">
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-4">
                                                    <label class="small text-muted">Tipo</label>
                                                    <select name="items[0][tipo_item]" required class="form-select form-select-sm" style="border-radius: 8px; background: white;">
                                                        <option value="activo">Activo</option>
                                                        <option value="periferico">Periférico</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small text-muted">Descripción del Item</label>
                                                    <input type="text" name="items[0][item_descripcion]" required placeholder="Ej: Laptop HP, Mouse, Cargador" class="form-control form-control-sm" style="border-radius: 8px; background: white;">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="small text-muted">Cantidad</label>
                                                    <input type="number" name="items[0][cantidad]" min="1" value="1" required class="form-control form-control-sm" style="border-radius: 8px; background: white;">
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-12 text-end">
                                                    <button type="button" class="remove-item-correo btn btn-sm text-danger">× Eliminar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                                <button type="button" onclick="cerrarBandejaCorreos()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                                <button type="submit" id="submitCorreoBtn" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Crear Solicitud</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN PARA CANCELAR SOLICITUD -->
<div id="modalConfirmacionCancelar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10006; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <div class="d-flex align-items-center gap-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    <h5 class="modal-title text-white">Cancelar Solicitud</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalConfirmacion()"></button>
            </div>
            <div class="modal-body p-4 text-center" style="background: white;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5" style="margin-bottom: 16px;">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <h5 class="mb-3 fw-bold" style="color: #1e3c72;">¿Estás seguro de cancelar esta solicitud?</h5>
                <p class="text-muted mb-0">Esta acción no se puede deshacer. La solicitud quedará cancelada y no podrá ser recuperada.</p>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 d-flex gap-2 justify-content-center">
                <button type="button" onclick="cerrarModalConfirmacion()" class="btn btn-light px-4" style="border-radius: 10px; padding: 10px 24px;">No, mantener</button>
                <button type="button" onclick="confirmarCancelar()" class="btn px-4" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border-radius: 10px; padding: 10px 24px;">Sí, cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE EDICIÓN DE RESPONSABLE -->
<div id="modalEditarResponsable" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 10005; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background: white; animation: modalFadeIn 0.3s ease;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                            <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                        </svg>
                    </div>
                    <div>
                        <h5 class="modal-title text-white" id="modalEditarResponsableTitulo" style="font-weight: 600;">Editar Responsable</h5>
                        <p class="text-white-50 small mb-0" style="opacity: 0.8;">Actualiza la información del responsable</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalEditarResponsable()"></button>
            </div>
            <div class="modal-body p-4" style="background: white;">
                <input type="hidden" id="editResponsableTipo">
                <input type="hidden" id="editResponsableEntidadId">
                <input type="hidden" id="editResponsableId">

                <div class="mb-4">
                    <label class="form-label fw-semibold" style="color: #1e3c72;">Nombre del Responsable</label>
                    <input type="text" id="editResponsableNombre" class="form-control" placeholder="Ej: Juan Pérez" style="border-radius: 12px; background: white; border: 1px solid #e9ecef; padding: 12px 16px;">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" style="color: #1e3c72;">Cargo / Departamento</label>
                    <input type="text" id="editResponsableCargo" class="form-control" placeholder="Ej: Jefe de TI" style="border-radius: 12px; background: white; border: 1px solid #e9ecef; padding: 12px 16px;">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" style="color: #1e3c72;">Teléfono</label>
                    <input type="text" id="editResponsableTelefono" class="form-control" placeholder="Ej: (809) 555-1234" style="border-radius: 12px; background: white; border: 1px solid #e9ecef; padding: 12px 16px;">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="color: #1e3c72;">Email</label>
                    <input type="email" id="editResponsableEmail" class="form-control" placeholder="Ej: juan.perez@empresa.com" style="border-radius: 12px; background: white; border: 1px solid #e9ecef; padding: 12px 16px;">
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 d-flex gap-3 justify-content-end" style="background: white; border-radius: 0 0 20px 20px;">
                <button type="button" onclick="cerrarModalEditarResponsable()" class="btn px-4" style="border-radius: 12px; padding: 10px 24px; background: #f8f9fc; color: #6c757d; border: 1px solid #e9ecef;">Cancelar</button>
                <button type="button" onclick="guardarResponsable()" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 12px; padding: 10px 24px;">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Animaciones */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes slideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100px); }
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Estilos de tabla */
    .table tbody tr {
        transition: all 0.2s ease;
        animation: fadeInUp 0.25s ease;
        background: white;
    }

    .table tbody tr:hover {
        background-color: #f8f9fc !important;
        transform: scale(1.01);
    }

    /* Badges */
    .badge-prioridad, .badge-estado, .badge-fecha {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .fecha-solicitud-badge { background: #e9ecef; color: #1a1a2e; font-weight: 600; }
    .fecha-requerida-badge { background: #fff3cd; color: #856404; font-weight: 600; }
    .fecha-requerida-vencida { background: #f8d7da; color: #721c24; font-weight: 600; }
    .fecha-fin-badge { background: #d1e7dd; color: #0f5132; font-weight: 600; }

    /* Correos */
    .correo-item {
        transition: all 0.2s ease;
        cursor: pointer;
        padding: 12px;
        border-radius: 12px;
        background: white;
        margin-bottom: 8px;
        border: 1px solid #e9ecef;
    }

    .correo-item:hover {
        background: #f8f9fc;
        transform: translateX(4px);
        border-color: #1e3c72;
    }

    /* Botones */
    .btn:active { transform: scale(0.98); }
    .card { background: white !important; }

    /* Notificaciones */
    .notification-toast {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        margin-bottom: 12px;
        overflow: hidden;
        animation: slideInRight 0.3s ease forwards;
    }

    .notification-toast.success { border-left: 4px solid #28a745; }
    .notification-toast.error { border-left: 4px solid #dc3545; }
    .notification-toast.warning { border-left: 4px solid #ffc107; }
    .notification-toast.info { border-left: 4px solid #17a2b8; }

    /* Botones de acción */
    .btn-accion-ver { background: rgba(23,162,184,0.1); color: #0c5c6e; border: 1px solid rgba(23,162,184,0.3); }
    .btn-accion-ver:hover { background: #17a2b8; color: white; border-color: #17a2b8; }
    .btn-accion-editar { background: rgba(255,193,7,0.1); color: #8a6300; border: 1px solid rgba(255,193,7,0.3); }
    .btn-accion-editar:hover { background: #ffc107; color: #1e3c72; border-color: #ffc107; }
    .btn-accion-cancelar { background: rgba(220,53,69,0.1); color: #8b1a24; border: 1px solid rgba(220,53,69,0.3); }
    .btn-accion-cancelar:hover { background: #dc3545; color: white; border-color: #dc3545; }

    /* Paginación */
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-color: #1e3c72;
        color: white;
    }

    .pagination .page-link {
        color: #1e3c72;
        border-radius: 8px;
        margin: 0 4px;
    }

    .pagination .page-link:hover {
        background: #1e3c72;
        color: white;
    }

    /* Sortable columns */
    .sortable {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s ease;
    }

    .sortable:hover {
        background-color: #e9ecef;
    }

    .sort-icon {
        font-size: 10px;
        opacity: 0.5;
        transition: opacity 0.2s ease;
    }

    .sortable:hover .sort-icon {
        opacity: 1;
    }

    /* Tooltips */
    [data-tooltip] {
        position: relative;
        cursor: help;
    }

    [data-tooltip]:before {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-8px);
        background: #1e3c72;
        color: white;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
        z-index: 1000;
        pointer-events: none;
    }

    [data-tooltip]:hover:before {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(-4px);
    }

    /* Skeleton loading */
    .skeleton-row {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    /* Validación en tiempo real */
    .input-error {
        border-color: #dc3545 !important;
        background-color: #fff8f8 !important;
    }

    .input-success {
        border-color: #28a745 !important;
        background-color: #f8fff8 !important;
    }

    .input-warning {
        border-color: #ffc107 !important;
        background-color: #fffcf0 !important;
    }
</style>

<script>
// ==================== DATOS INICIALES ====================
let solicitudesData = @json($solicitudes);
let todasLasSolicitudes = solicitudesData.data || [];
let currentPage = solicitudesData.current_page || 1;
let lastPage = solicitudesData.last_page || 1;
let perPage = solicitudesData.per_page || 10;
let totalRegistros = solicitudesData.total || 0;
let timeoutBusqueda = null;

// Variables para ordenamiento
let sortColumn = 'fecha_solicitud';
let sortDirection = 'desc'; // 'asc' o 'desc'

// Filtros
let filtros = {
    search: '',
    estado: '',
    prioridad: '',
    fecha_desde: '',
    fecha_hasta: ''
};

let activos = @json($activos ?? []);
let perifericos = @json($perifericos ?? []);
let instituciones = @json($instituciones ?? []);
let departamentos = @json($departamentos ?? []);
let responsables = @json($responsables ?? []);

let correosPendientes = [
    {
        id: 1,
        from: "juan.perez@empresa.com",
        subject: "Solicitud de préstamo - Proyecto A",
        date: new Date().toISOString().split('T')[0],
        body: "Necesito equipos para el proyecto A. Fecha requerida: " + new Date(Date.now() + 3*24*60*60*1000).toISOString().split('T')[0] + ". Prioridad: alta. Justificación: Proyecto A requiere equipos adicionales.",
        extracted: {
            prioridad: "alta",
            fecha_requerida: new Date(Date.now() + 3*24*60*60*1000).toISOString().split('T')[0],
            justificacion: "Proyecto A requiere equipos adicionales"
        }
    },
    {
        id: 2,
        from: "maria.garcia@empresa.com",
        subject: "URGENTE: Equipos para reunión",
        date: new Date().toISOString().split('T')[0],
        body: "Se requiere con urgencia computadoras para la reunión con clientes. Fecha requerida: " + new Date(Date.now() + 1*24*60*60*1000).toISOString().split('T')[0],
        extracted: {
            prioridad: "urgente",
            fecha_requerida: new Date(Date.now() + 1*24*60*60*1000).toISOString().split('T')[0],
            justificacion: "Reunión con clientes importantes"
        }
    }
];

// ==================== ACTUALIZAR ESTADÍSTICAS ====================
function actualizarEstadisticas() {
    const total = todasLasSolicitudes.length;
    const pendientes = todasLasSolicitudes.filter(s => s.estado_solicitud === 'pendiente').length;
    const aprobadas = todasLasSolicitudes.filter(s => s.estado_solicitud === 'aprobada').length;
    const rechazadas = todasLasSolicitudes.filter(s => s.estado_solicitud === 'rechazada').length;

    document.getElementById('statsTotal').textContent = total;
    document.getElementById('statsPendientes').textContent = pendientes;
    document.getElementById('statsAprobadas').textContent = aprobadas;
    document.getElementById('statsRechazadas').textContent = rechazadas;
}

// ==================== ORDENAMIENTO ====================
function ordenarSolicitudes() {
    todasLasSolicitudes.sort((a, b) => {
        let valorA, valorB;

        switch(sortColumn) {
            case 'index':
                valorA = a.id;
                valorB = b.id;
                break;
            case 'fecha_solicitud':
                valorA = new Date(a.fecha_solicitud);
                valorB = new Date(b.fecha_solicitud);
                break;
            case 'entidad':
                valorA = (a.departamento?.nombre || a.institucion?.nombre || '').toLowerCase();
                valorB = (b.departamento?.nombre || b.institucion?.nombre || '').toLowerCase();
                break;
            case 'responsable':
                let respA = a.departamento?.responsable?.nombre || a.institucion?.responsable?.nombre || '';
                let respB = b.departamento?.responsable?.nombre || b.institucion?.responsable?.nombre || '';
                valorA = respA.toLowerCase();
                valorB = respB.toLowerCase();
                break;
            case 'fecha_requerida':
                valorA = new Date(a.fecha_requerida);
                valorB = new Date(b.fecha_requerida);
                break;
            case 'fecha_fin':
                valorA = new Date(a.fecha_fin_estimada);
                valorB = new Date(b.fecha_fin_estimada);
                break;
            case 'prioridad':
                const prioridades = { 'baja': 1, 'normal': 2, 'alta': 3, 'urgente': 4 };
                valorA = prioridades[a.prioridad] || 0;
                valorB = prioridades[b.prioridad] || 0;
                break;
            case 'estado':
                const estados = { 'pendiente': 1, 'aprobada': 2, 'rechazada': 3, 'cancelada': 4 };
                valorA = estados[a.estado_solicitud] || 0;
                valorB = estados[b.estado_solicitud] || 0;
                break;
            default:
                return 0;
        }

        if (valorA < valorB) return sortDirection === 'asc' ? -1 : 1;
        if (valorA > valorB) return sortDirection === 'asc' ? 1 : -1;
        return 0;
    });

    renderizarTabla();
}

function cambiarOrden(columna) {
    if (sortColumn === columna) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = columna;
        sortDirection = 'asc';
    }

    // Actualizar iconos de ordenamiento
    document.querySelectorAll('.sortable .sort-icon').forEach(icon => {
        icon.textContent = '↕️';
    });
    const activeHeader = document.querySelector(`.sortable[data-sort="${columna}"] .sort-icon`);
    if (activeHeader) {
        activeHeader.textContent = sortDirection === 'asc' ? '↑' : '↓';
    }

    ordenarSolicitudes();
}

// ==================== SKELETON LOADING ====================
function mostrarSkeleton(show) {
    const skeleton = document.getElementById('skeletonLoader');
    const tablaBody = document.getElementById('tablaBody');
    if (show) {
        if (skeleton) skeleton.style.display = 'block';
        if (tablaBody) tablaBody.style.display = 'none';
    } else {
        if (skeleton) skeleton.style.display = 'none';
        if (tablaBody) tablaBody.style.display = '';
    }
}

function mostrarLoading(show) {
    if (show) {
        mostrarSkeleton(true);
    } else {
        mostrarSkeleton(false);
    }
}

// ==================== FILTROS RÁPIDOS ====================
function aplicarFiltroRapido() {
    const filtro = document.getElementById('filtroRapido').value;
    const hoy = new Date();
    let fechaDesde = '';
    let fechaHasta = '';

    switch(filtro) {
        case 'today':
            fechaDesde = hoy.toISOString().split('T')[0];
            fechaHasta = hoy.toISOString().split('T')[0];
            break;
        case 'week':
            const inicioSemana = new Date(hoy);
            inicioSemana.setDate(hoy.getDate() - hoy.getDay());
            fechaDesde = inicioSemana.toISOString().split('T')[0];
            fechaHasta = hoy.toISOString().split('T')[0];
            break;
        case 'month':
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            fechaDesde = inicioMes.toISOString().split('T')[0];
            fechaHasta = hoy.toISOString().split('T')[0];
            break;
        case 'last7':
            const hace7Dias = new Date(hoy);
            hace7Dias.setDate(hoy.getDate() - 7);
            fechaDesde = hace7Dias.toISOString().split('T')[0];
            fechaHasta = hoy.toISOString().split('T')[0];
            break;
        case 'last30':
            const hace30Dias = new Date(hoy);
            hace30Dias.setDate(hoy.getDate() - 30);
            fechaDesde = hace30Dias.toISOString().split('T')[0];
            fechaHasta = hoy.toISOString().split('T')[0];
            break;
        default:
            return;
    }

    if (fechaDesde) document.getElementById('fechaDesde').value = fechaDesde;
    if (fechaHasta) document.getElementById('fechaHasta').value = fechaHasta;

    aplicarFiltros();
}

// ==================== VALIDACIÓN EN TIEMPO REAL ====================
function initValidacionTiempoReal() {
    // Validar fecha requerida
    const fechaRequerida = document.getElementById('fechaRequeridaInput');
    if (fechaRequerida) {
        fechaRequerida.addEventListener('change', function() {
            const hoy = new Date().toISOString().split('T')[0];
            const errorElement = document.getElementById('fechaRequeridaError');
            if (this.value < hoy) {
                this.classList.add('input-error');
                errorElement.style.display = 'block';
            } else {
                this.classList.remove('input-error');
                this.classList.add('input-success');
                errorElement.style.display = 'none';
            }
            validarFechas();
        });
    }

    // Validar fecha fin
    const fechaFin = document.getElementById('fechaFinEstimadaInput');
    if (fechaFin) {
        fechaFin.addEventListener('change', validarFechas);
    }

    // Validar justificación
    const justificacion = document.getElementById('justificacionInput');
    if (justificacion) {
        justificacion.addEventListener('input', function() {
            const contador = document.getElementById('justificacionContador');
            const errorElement = document.getElementById('justificacionError');
            const longitud = this.value.length;
            contador.textContent = `${longitud}/1000 caracteres`;

            if (longitud < 20 && longitud > 0) {
                this.classList.add('input-warning');
                errorElement.style.display = 'block';
            } else if (longitud >= 20) {
                this.classList.remove('input-warning');
                this.classList.add('input-success');
                errorElement.style.display = 'none';
            } else {
                this.classList.remove('input-warning', 'input-success');
                errorElement.style.display = 'none';
            }
        });
    }

    // Validar archivo
    const archivo = document.querySelector('input[name="oficio_adjunto"]');
    if (archivo) {
        archivo.addEventListener('change', function() {
            const errorElement = document.getElementById('archivoError');
            if (this.files.length > 0) {
                const file = this.files[0];
                const extensiones = ['.pdf', '.doc', '.docx'];
                const ext = '.' + file.name.split('.').pop().toLowerCase();
                if (!extensiones.includes(ext)) {
                    this.classList.add('input-error');
                    errorElement.style.display = 'block';
                } else if (file.size > 2 * 1024 * 1024) {
                    this.classList.add('input-error');
                    errorElement.textContent = 'El archivo debe pesar máximo 2MB';
                    errorElement.style.display = 'block';
                } else {
                    this.classList.remove('input-error');
                    this.classList.add('input-success');
                    errorElement.style.display = 'none';
                }
            }
        });
    }
}

function validarFechas() {
    const fechaRequerida = document.getElementById('fechaRequeridaInput');
    const fechaFin = document.getElementById('fechaFinEstimadaInput');
    const errorElement = document.getElementById('fechaFinError');

    if (fechaRequerida && fechaFin && fechaRequerida.value && fechaFin.value) {
        if (new Date(fechaFin.value) < new Date(fechaRequerida.value)) {
            fechaFin.classList.add('input-error');
            errorElement.style.display = 'block';
            return false;
        } else {
            fechaFin.classList.remove('input-error');
            fechaFin.classList.add('input-success');
            errorElement.style.display = 'none';
            return true;
        }
    }
    return true;
}

// ==================== PAGINACIÓN ====================
async function cargarPagina(page) {
    mostrarLoading(true);

    try {
        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: filtros.search,
            estado: filtros.estado,
            prioridad: filtros.prioridad,
            fecha_desde: filtros.fecha_desde,
            fecha_hasta: filtros.fecha_hasta
        });

        const response = await fetch(`/solicitudes?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        });

        if (!response.ok) throw new Error('Error al cargar datos');

        const data = await response.json();

        todasLasSolicitudes = data.data || [];
        currentPage = data.current_page || 1;
        lastPage = data.last_page || 1;
        perPage = data.per_page || 10;
        totalRegistros = data.total || 0;

        ordenarSolicitudes();
        actualizarEstadisticas();

        document.getElementById('resultadosCount').textContent = todasLasSolicitudes.length;
        document.getElementById('totalRegistrosCount').textContent = totalRegistros;
        document.getElementById('currentPageDisplay').textContent = currentPage;
        document.getElementById('lastPageDisplay').textContent = lastPage;

    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error', 'No se pudieron cargar las solicitudes');
    } finally {
        mostrarLoading(false);
    }
}

function renderizarPaginacion() {
    const container = document.getElementById('paginationContainer');
    if (!container) return;

    if (lastPage <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="cambiarPagina(${currentPage - 1}); return false;">&laquo; Anterior</a>
    </li>`;

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(lastPage, currentPage + 2);

    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(1); return false;">1</a></li>`;
        if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a>
        </li>`;
    }

    if (endPage < lastPage) {
        if (endPage < lastPage - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${lastPage}); return false;">${lastPage}</a></li>`;
    }

    html += `<li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="cambiarPagina(${currentPage + 1}); return false;">Siguiente &raquo;</a>
    </li>`;

    container.innerHTML = html;
}

function cambiarPagina(page) {
    if (page < 1 || page > lastPage) return;
    currentPage = page;
    cargarPagina(currentPage);
}

function aplicarFiltros() {
    filtros = {
        search: document.getElementById('searchInput')?.value || '',
        estado: document.getElementById('estadoFilter')?.value || '',
        prioridad: document.getElementById('prioridadFilter')?.value || '',
        fecha_desde: document.getElementById('fechaDesde')?.value || '',
        fecha_hasta: document.getElementById('fechaHasta')?.value || ''
    };
    currentPage = 1;
    cargarPagina(1);
}

function aplicarFiltrosConDebounce() {
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(() => {
        aplicarFiltros();
    }, 300);
}

// ==================== NOTIFICACIONES ====================
function mostrarNotificacion(tipo, titulo, mensaje, datos = null) {
    const container = document.getElementById('notification-container');
    if (!container) return;

    const notificacion = document.createElement('div');
    notificacion.className = `notification-toast ${tipo}`;

    let icono = '';
    if (tipo === 'success') icono = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>';
    else if (tipo === 'error') icono = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>';
    else if (tipo === 'warning') icono = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2"><path d="M12 9v4M12 17h.01"/><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z"/></svg>';
    else icono = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#17a2b8" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';

    let datosHtml = '';
    if (datos) {
        datosHtml = '<div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #e9ecef; font-size: 11px;">' + Object.entries(datos).map(([k,v]) => `<div><strong>${k}:</strong> ${v}</div>`).join('') + '</div>';
    }

    notificacion.innerHTML = `<div style="padding: 16px; display: flex; gap: 12px;">
        <div style="flex-shrink: 0;">${icono}</div>
        <div style="flex: 1;">
            <div style="font-weight: 600; margin-bottom: 4px;">${titulo}</div>
            <div style="font-size: 13px; color: #495057;">${mensaje}</div>
            ${datosHtml}
        </div>
        <button onclick="this.closest('.notification-toast').remove()" style="background: none; border: none; cursor: pointer; font-size: 18px;">&times;</button>
    </div>`;

    container.appendChild(notificacion);

    setTimeout(() => {
        if(notificacion && notificacion.parentNode) {
            notificacion.style.animation = 'slideOutRight 0.3s forwards';
            setTimeout(() => notificacion.remove(), 300);
        }
    }, 8000);
}

function isFechaVencida(fechaRequerida, estado) {
    if (estado !== 'pendiente') return false;
    const hoy = new Date();
    hoy.setHours(0,0,0,0);
    return new Date(fechaRequerida) < hoy;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

// ==================== VER DETALLES ====================
function verDetalles(id) {
    const modal = document.getElementById('modalDetalles');
    const modalBody = document.getElementById('modalDetallesBody');
    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando...</p></div>';

    fetch(`/solicitudes/${id}/detalles`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                modalBody.innerHTML = `<div class="text-center text-danger py-4">${data.error}</div>`;
                return;
            }

            let itemsHtml = '';
            if (data.detalles && data.detalles.length > 0) {
                itemsHtml = `<div class="table-responsive mt-3"><table class="table table-sm"><thead><tr><th>Tipo</th><th>Descripción</th><th class="text-center">Cantidad</th></tr></thead><tbody>`;
                for (let i = 0; i < data.detalles.length; i++) {
                    itemsHtml += `<tr><td>${data.detalles[i].tipo_item === 'activo' ? 'Activo' : 'Periférico'}</td><td>${escapeHtml(data.detalles[i].item_descripcion)}</td><td class="text-center"><strong>${data.detalles[i].cantidad_solicitada}</strong></td></tr>`;
                }
                itemsHtml += '</tbody></table></div>';
            } else {
                itemsHtml = '<div class="alert alert-secondary mt-3">No hay items registrados en esta solicitud</div>';
            }

            let estadoColor = data.estado_solicitud === 'pendiente' ? '#b26a00' : data.estado_solicitud === 'aprobada' ? '#1b5e20' : data.estado_solicitud === 'rechazada' ? '#c62828' : '#4e342e';
            let prioridadColor = data.prioridad === 'urgente' ? '#c62828' : data.prioridad === 'alta' ? '#e65100' : data.prioridad === 'normal' ? '#1b5e20' : '#4a2e00';

            let nombreEntidad = 'No especificado';
            if (data.tipo_solicitante === 'interno' && data.departamento) nombreEntidad = data.departamento.nombre;
            else if (data.tipo_solicitante === 'externo' && data.institucion) nombreEntidad = data.institucion.nombre;
            const nombreResponsable = data.responsable ? data.responsable.nombre : 'No especificado';

            const fechaSolicitud = data.fecha_solicitud ? new Date(data.fecha_solicitud).toLocaleDateString() : 'No definida';
            const fechaRequerida = data.fecha_requerida ? new Date(data.fecha_requerida).toLocaleDateString() : 'No definida';
            const fechaFin = data.fecha_fin_estimada ? new Date(data.fecha_fin_estimada).toLocaleDateString() : 'No definida';

            const html = `
                <div class="row g-3">
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Fecha Solicitud</label><div class="fw-semibold">${fechaSolicitud}</div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Tipo Solicitante</label><div class="fw-semibold">${data.tipo_solicitante === 'interno' ? 'Interno' : 'Externo'}</div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Prioridad</label><div><span class="badge-prioridad" style="background: ${prioridadColor}15; color: ${prioridadColor};">${data.prioridad}</span></div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Estado</label><div><span class="badge-estado" style="background: ${estadoColor}15; color: ${estadoColor};">${data.estado_solicitud}</span></div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Entidad</label><div class="fw-semibold">${escapeHtml(nombreEntidad)}</div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Responsable</label><div class="fw-semibold">${escapeHtml(nombreResponsable)}</div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Fecha Requerida</label><div>${fechaRequerida}</div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Fecha Fin Estimada</label><div>${fechaFin}</div></div></div>
                    <div class="col-12"><div class="mb-3"><label class="text-muted small">Justificación</label><div class="p-2 bg-light rounded">${escapeHtml(data.justificacion || 'No especificada')}</div></div></div>
                    <div class="col-12"><div class="mb-3"><label class="text-muted small">Observaciones</label><div class="p-2 bg-light rounded">${escapeHtml(data.observaciones || 'No hay observaciones')}</div></div></div>
                    <div class="col-12"><label class="text-muted small fw-semibold mb-2">Items Solicitados</label>${itemsHtml}</div>
                </div>
            `;
            modalBody.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = '<div class="text-center text-danger py-4">Error al cargar los detalles</div>';
        });
}

function cerrarModalDetalles() {
    document.getElementById('modalDetalles').style.display = 'none';
}

// ==================== RENDERIZAR TABLA ====================
function renderizarTabla() {
    const tbody = document.getElementById('tablaBody');

    if (!todasLasSolicitudes || todasLasSolicitudes.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center py-5 text-muted">No hay solicitudes<br><small>Las solicitudes que crees aparecerán aquí</small></td></tr>`;
        return;
    }

    let html = '';
    let contador = ((currentPage - 1) * perPage) + 1;

    for (const s of todasLasSolicitudes) {
        let prioridadColor = s.prioridad === 'urgente' ? '#c62828' : s.prioridad === 'alta' ? '#e65100' : s.prioridad === 'normal' ? '#1b5e20' : '#4a2e00';
        let prioridadBg = s.prioridad === 'urgente' ? '#ffebee' : s.prioridad === 'alta' ? '#fff3e0' : s.prioridad === 'normal' ? '#e8f5e9' : '#fff8e1';
        let estadoColor = s.estado_solicitud === 'pendiente' ? '#b26a00' : s.estado_solicitud === 'aprobada' ? '#1b5e20' : s.estado_solicitud === 'rechazada' ? '#c62828' : '#4e342e';
        let estadoBg = s.estado_solicitud === 'pendiente' ? '#fff8e1' : s.estado_solicitud === 'aprobada' ? '#e8f5e9' : s.estado_solicitud === 'rechazada' ? '#ffebee' : '#eceff1';
        const fechaVencida = isFechaVencida(s.fecha_requerida, s.estado_solicitud);
        const fechaReqClass = fechaVencida ? 'fecha-requerida-vencida' : 'fecha-requerida-badge';
        let prioridadTexto = s.prioridad === 'urgente' ? 'Urgente' : s.prioridad === 'alta' ? 'Alta' : s.prioridad === 'normal' ? 'Normal' : 'Baja';
        let estadoTexto = s.estado_solicitud === 'pendiente' ? 'Pendiente' : s.estado_solicitud === 'aprobada' ? 'Aprobada' : s.estado_solicitud === 'rechazada' ? 'Rechazada' : 'Cancelada';
        const fechaSolicitud = s.fecha_solicitud ? new Date(s.fecha_solicitud).toLocaleDateString() : 'No registrada';
        const fechaRequerida = s.fecha_requerida ? new Date(s.fecha_requerida).toLocaleDateString() : 'No definida';
        const fechaFin = s.fecha_fin_estimada ? new Date(s.fecha_fin_estimada).toLocaleDateString() : 'No definida';
        let nombreEntidad = 'No especificado';
        if (s.tipo_solicitante === 'interno' && s.departamento) nombreEntidad = s.departamento.nombre;
        else if (s.tipo_solicitante === 'externo' && s.institucion) nombreEntidad = s.institucion.nombre;

        let nombreResponsable = 'No especificado';
        if (s.tipo_solicitante === 'interno' && s.departamento && s.departamento.responsable) {
            nombreResponsable = s.departamento.responsable.nombre;
        } else if (s.tipo_solicitante === 'externo' && s.institucion && s.institucion.responsable) {
            nombreResponsable = s.institucion.responsable.nombre;
        }

        html += `<tr data-id="${s.id}">
            <td class="px-4 py-3">${contador++}</td>
            <td class="px-4 py-3"><span class="badge-fecha fecha-solicitud-badge"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> ${fechaSolicitud}</span></td>
            <td class="px-4 py-3"><span class="badge-fecha" style="background: ${s.tipo_solicitante === 'interno' ? '#e3f2fd' : '#f3e5f5'}; color: ${s.tipo_solicitante === 'interno' ? '#0d47a1' : '#4a148c'}; font-weight: 600;"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${s.tipo_solicitante === 'interno' ? '<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>' : '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'}</svg> ${escapeHtml(nombreEntidad)}</span></td>
            <td class="px-4 py-3"><span class="badge-fecha" style="background: #e8f5e9; color: #1b5e20; font-weight: 600;"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> ${escapeHtml(nombreResponsable)}</span></td>
            <td class="px-4 py-3"><span class="badge-fecha ${fechaReqClass}"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> ${fechaRequerida} ${fechaVencida ? '(Vencida)' : ''}</span></td>
            <td class="px-4 py-3"><span class="badge-fecha fecha-fin-badge"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> ${fechaFin}</span></td>
            <td class="px-4 py-3"><span class="badge-prioridad" style="background: ${prioridadBg}; color: ${prioridadColor}; font-weight: 600;"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg> ${prioridadTexto}</span></td>
            <td class="px-4 py-3"><span class="badge-estado" style="background: ${estadoBg}; color: ${estadoColor}; font-weight: 600;">${estadoTexto}</span></td>
            <td class="px-4 py-3 text-center"><span class="badge-fecha" style="background: #e9ecef; color: #1a1a2e; font-weight: 600;">${s.detalles?.length || 0}</span></td>
            <td class="px-4 py-3 text-center"><div class="d-flex gap-2 justify-content-center">
                <button onclick="verDetalles(${s.id})" class="btn btn-sm btn-accion-ver" style="border-radius: 8px; padding: 6px 12px;" data-tooltip="Ver detalles de la solicitud"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><circle cx="12" cy="12" r="3"/></svg> Ver</button>
                <button onclick="editarSolicitud(${s.id})" class="btn btn-sm btn-accion-editar" style="border-radius: 8px; padding: 6px 12px;" data-tooltip="Editar solicitud (solo pendientes)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/><polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/></svg> Editar</button>
                ${s.estado_solicitud === 'pendiente' ? `<button onclick="abrirModalConfirmacionCancelar(${s.id})" class="btn btn-sm btn-accion-cancelar" style="border-radius: 8px; padding: 6px 12px;" data-tooltip="Cancelar solicitud"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/></svg> Cancelar</button>` : ''}
            </div></td>
        </tr>`;
    }
    tbody.innerHTML = html;
}

// ==================== RESPONSABLE DE ENTIDAD ====================
function actualizarResponsableDisplay() {
    const tipo = document.getElementById('tipoSolicitante').value;
    let entidadId = null;
    let entidadNombre = '';

    if (tipo === 'interno') {
        entidadId = document.getElementById('departamentoSelect').value;
        entidadNombre = document.getElementById('departamentoSelect').selectedOptions[0]?.text || '';
    } else {
        entidadId = document.getElementById('institucionSelect').value;
        entidadNombre = document.getElementById('institucionSelect').selectedOptions[0]?.text || '';
    }

    const display = document.getElementById('responsableDisplay');
    const btnEditar = document.getElementById('btnEditarResponsable');

    if (entidadId && entidadId !== '' && entidadId !== 'otro') {
        const endpoint = tipo === 'interno' ? 'departamento' : 'institucion';
        fetch(`/api/${endpoint}/${entidadId}/responsable`)
            .then(r => r.json())
            .then(data => {
                if (data.responsable && data.responsable.nombre) {
                    display.innerHTML = `
                        <div class="d-flex flex-column">
                            <strong class="mb-1">${escapeHtml(data.responsable.nombre)}</strong>
                            <div class="small text-muted">
                                ${data.responsable.departamento ? `<span>${escapeHtml(data.responsable.departamento)}</span>` : ''}
                                ${data.responsable.telefono ? `<span class="ms-2">📞 ${escapeHtml(data.responsable.telefono)}</span>` : ''}
                                ${data.responsable.email ? `<span class="ms-2">✉️ ${escapeHtml(data.responsable.email)}</span>` : ''}
                            </div>
                        </div>
                    `;
                    btnEditar.style.display = 'inline-block';
                } else {
                    display.innerHTML = `<span class="text-muted">⚠️ No hay responsable asignado a ${entidadNombre}. Haz clic en "Editar" para agregar uno.</span>`;
                    btnEditar.style.display = 'inline-block';
                }
            })
            .catch(() => {
                display.innerHTML = `<span class="text-danger">Error al cargar responsable de ${entidadNombre}</span>`;
                btnEditar.style.display = 'inline-block';
            });
    } else {
        display.innerHTML = '<span class="text-muted">Selecciona una institución o departamento</span>';
        btnEditar.style.display = 'none';
    }
}

function editarResponsableActual() {
    const tipo = document.getElementById('tipoSolicitante').value;
    let entidadId = null;

    if (tipo === 'interno') {
        entidadId = document.getElementById('departamentoSelect').value;
    } else {
        entidadId = document.getElementById('institucionSelect').value;
    }

    if (entidadId && entidadId !== '' && entidadId !== 'otro') {
        const entidadNombre = document.getElementById(tipo === 'interno' ? 'departamentoSelect' : 'institucionSelect').selectedOptions[0]?.text;
        abrirModalEditarResponsable(tipo === 'interno' ? 'departamento' : 'institucion', entidadId, entidadNombre);
    }
}

function abrirModalEditarResponsable(tipo, entidadId, entidadNombre) {
    const modal = document.getElementById('modalEditarResponsable');
    if (!modal) return;

    modal.style.display = 'flex';
    document.getElementById('editResponsableTipo').value = tipo;
    document.getElementById('editResponsableEntidadId').value = entidadId;
    document.getElementById('modalEditarResponsableTitulo').innerHTML = `Editar Responsable de ${entidadNombre}`;

    fetch(`/api/${tipo}/${entidadId}/responsable`)
        .then(r => r.json())
        .then(data => {
            if (data.responsable) {
                document.getElementById('editResponsableNombre').value = data.responsable.nombre || '';
                document.getElementById('editResponsableCargo').value = data.responsable.departamento || '';
                document.getElementById('editResponsableTelefono').value = data.responsable.telefono || '';
                document.getElementById('editResponsableEmail').value = data.responsable.email || '';
                document.getElementById('editResponsableId').value = data.responsable.id || '';
            } else {
                document.getElementById('editResponsableNombre').value = '';
                document.getElementById('editResponsableCargo').value = '';
                document.getElementById('editResponsableTelefono').value = '';
                document.getElementById('editResponsableEmail').value = '';
                document.getElementById('editResponsableId').value = '';
            }
        });
}

function cerrarModalEditarResponsable() {
    const modal = document.getElementById('modalEditarResponsable');
    if (modal) modal.style.display = 'none';
}

async function guardarResponsable() {
    const tipo = document.getElementById('editResponsableTipo').value;
    const entidadId = document.getElementById('editResponsableEntidadId').value;
    const responsableId = document.getElementById('editResponsableId').value;
    const token = document.querySelector('meta[name="csrf-token"]').content;

    const data = {
        nombre: document.getElementById('editResponsableNombre').value,
        cargo: document.getElementById('editResponsableCargo').value,
        telefono: document.getElementById('editResponsableTelefono').value,
        email: document.getElementById('editResponsableEmail').value,
        responsable_id: responsableId || null,
        _token: token
    };

    if (!data.nombre) {
        mostrarNotificacion('error', 'Error', 'El nombre del responsable es obligatorio');
        return;
    }

    try {
        const response = await fetch(`/api/${tipo}/${entidadId}/responsable`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.success) {
            mostrarNotificacion('success', 'Éxito', 'Responsable actualizado correctamente');
            cerrarModalEditarResponsable();
            actualizarResponsableDisplay();
            actualizarResponsableDisplayForm();
        } else {
            mostrarNotificacion('error', 'Error', result.message || 'Error al guardar');
        }
    } catch (error) {
        mostrarNotificacion('error', 'Error', 'Error de conexión');
    }
}

// ==================== MODALES PRINCIPALES ====================
function abrirModalCrear() {
    document.getElementById('formCrearSolicitud').reset();
    document.getElementById('tipoSolicitante').value = 'interno';
    actualizarCamposSolicitante();
    document.getElementById('modalCrear').style.display = 'flex';
    actualizarResponsableDisplay();
    initValidacionTiempoReal();
}

function cerrarModalCrear() {
    document.getElementById('modalCrear').style.display = 'none';
}

function abrirBandejaCorreos() {
    document.getElementById('modalBandeja').style.display = 'flex';
    cargarListaCorreos();
    actualizarCamposFormularioCorreo();
    document.getElementById('previewCorreo').innerHTML = `
        <div class="text-center text-muted py-5">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="M22 7l-10 7L2 7"/>
            </svg>
            <h6 class="mt-3">Selecciona un correo de la lista</h6>
            <p class="small">Al seleccionar un correo, se precargarán los datos en el formulario</p>
        </div>
    `;
    document.getElementById('formSolicitudCorreo').reset();
    document.getElementById('formTipoSolicitante').value = 'interno';
    actualizarCamposFormularioCorreo();
}

function cerrarBandejaCorreos() {
    document.getElementById('modalBandeja').style.display = 'none';
}

function editarSolicitud(id) {
    const solicitud = todasLasSolicitudes.find(s => s.id === id);
    if (!solicitud) return;

    document.getElementById('editId').value = solicitud.id;
    document.getElementById('editTipoSolicitante').value = solicitud.tipo_solicitante;
    document.getElementById('editPrioridad').value = solicitud.prioridad;

    if (solicitud.fecha_requerida) {
        const fechaReq = new Date(solicitud.fecha_requerida);
        document.getElementById('editFechaRequerida').value = fechaReq.toISOString().split('T')[0];
    }
    if (solicitud.fecha_fin_estimada) {
        const fechaFin = new Date(solicitud.fecha_fin_estimada);
        document.getElementById('editFechaFin').value = fechaFin.toISOString().split('T')[0];
    }

    document.getElementById('editJustificacion').value = solicitud.justificacion;
    document.getElementById('editObservaciones').value = solicitud.observaciones || '';

    if (solicitud.departamento_id) document.getElementById('editDepartamentoId').value = solicitud.departamento_id;
    if (solicitud.institucion_id) document.getElementById('editInstitucionId').value = solicitud.institucion_id;

    const editInternoFields = document.getElementById('editInternoFields');
    const editExternoFields = document.getElementById('editExternoFields');
    if (solicitud.tipo_solicitante === 'interno') {
        editInternoFields.style.display = 'block';
        editExternoFields.style.display = 'none';
    } else {
        editInternoFields.style.display = 'none';
        editExternoFields.style.display = 'block';
    }

    document.getElementById('modalEditar').style.display = 'flex';
    cargarResponsableEdit(solicitud);
}

function cargarResponsableEdit(solicitud) {
    const display = document.getElementById('editResponsableDisplay');
    const btnEditar = document.getElementById('btnEditarResponsableEdit');
    const hiddenResponsableId = document.getElementById('editResponsableId');

    if (solicitud.tipo_solicitante === 'interno' && solicitud.departamento && solicitud.departamento.responsable) {
        display.innerHTML = `
            <div class="d-flex flex-column">
                <strong class="mb-1">${escapeHtml(solicitud.departamento.responsable.nombre)}</strong>
                <div class="small text-muted">
                    ${solicitud.departamento.responsable.departamento ? `<span>${escapeHtml(solicitud.departamento.responsable.departamento)}</span>` : ''}
                    ${solicitud.departamento.responsable.telefono ? `<span class="ms-2">📞 ${escapeHtml(solicitud.departamento.responsable.telefono)}</span>` : ''}
                    ${solicitud.departamento.responsable.email ? `<span class="ms-2">✉️ ${escapeHtml(solicitud.departamento.responsable.email)}</span>` : ''}
                </div>
            </div>
        `;
        if (hiddenResponsableId) hiddenResponsableId.value = solicitud.departamento.responsable.id;
        btnEditar.style.display = 'inline-block';
    } else if (solicitud.tipo_solicitante === 'externo' && solicitud.institucion && solicitud.institucion.responsable) {
        display.innerHTML = `
            <div class="d-flex flex-column">
                <strong class="mb-1">${escapeHtml(solicitud.institucion.responsable.nombre)}</strong>
                <div class="small text-muted">
                    ${solicitud.institucion.responsable.departamento ? `<span>${escapeHtml(solicitud.institucion.responsable.departamento)}</span>` : ''}
                    ${solicitud.institucion.responsable.telefono ? `<span class="ms-2">📞 ${escapeHtml(solicitud.institucion.responsable.telefono)}</span>` : ''}
                    ${solicitud.institucion.responsable.email ? `<span class="ms-2">✉️ ${escapeHtml(solicitud.institucion.responsable.email)}</span>` : ''}
                </div>
            </div>
        `;
        if (hiddenResponsableId) hiddenResponsableId.value = solicitud.institucion.responsable.id;
        btnEditar.style.display = 'inline-block';
    } else {
        display.innerHTML = '<span class="text-muted">No hay responsable asignado a esta entidad</span>';
        if (hiddenResponsableId) hiddenResponsableId.value = '';
        btnEditar.style.display = 'inline-block';
    }
}

function editarResponsableActualEdit() {
    const tipo = document.getElementById('editTipoSolicitante').value;
    let entidadId = null;

    if (tipo === 'interno') {
        entidadId = document.getElementById('editDepartamentoId').value;
    } else {
        entidadId = document.getElementById('editInstitucionId').value;
    }

    if (entidadId && entidadId !== '') {
        const entidadNombre = document.getElementById(tipo === 'interno' ? 'editDepartamentoId' : 'editInstitucionId').selectedOptions[0]?.text;
        abrirModalEditarResponsable(tipo === 'interno' ? 'departamento' : 'institucion', entidadId, entidadNombre);
    }
}

function cerrarModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
}

// ==================== MODAL DE CONFIRMACIÓN PARA CANCELAR ====================
let solicitudACancelar = null;

function abrirModalConfirmacionCancelar(solicitudId) {
    solicitudACancelar = solicitudId;
    const modal = document.getElementById('modalConfirmacionCancelar');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function cerrarModalConfirmacion() {
    const modal = document.getElementById('modalConfirmacionCancelar');
    if (modal) {
        modal.style.display = 'none';
    }
    solicitudACancelar = null;
}

async function confirmarCancelar() {
    if (!solicitudACancelar) {
        mostrarNotificacion('error', 'Error', 'No se identificó la solicitud a cancelar');
        return;
    }

    const modal = document.getElementById('modalConfirmacionCancelar');
    const confirmBtn = modal.querySelector('button:last-child');
    const originalText = confirmBtn.innerHTML;

    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Cancelando...';
    confirmBtn.disabled = true;

    try {
        const response = await fetch(`/solicitudes/${solicitudACancelar}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (response.ok && result.success) {
            mostrarNotificacion('success', 'Solicitud Cancelada', result.message || 'La solicitud ha sido cancelada exitosamente');
            cerrarModalConfirmacion();
            setTimeout(() => {
                cargarPagina(currentPage);
            }, 1500);
        } else {
            mostrarNotificacion('error', 'Error', result.message || 'No se pudo cancelar la solicitud');
            cerrarModalConfirmacion();
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión', 'No se pudo conectar con el servidor');
        cerrarModalConfirmacion();
    } finally {
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
    }
}

// ==================== FUNCIONES PARA MODAL NORMAL ====================
function actualizarCamposSolicitante() {
    const tipo = document.getElementById('tipoSolicitante').value;
    const internoFields = document.getElementById('interno-fields');
    const externoFields = document.getElementById('externo-fields');
    if (tipo === 'interno') {
        internoFields.style.display = 'block';
        externoFields.style.display = 'none';
    } else {
        internoFields.style.display = 'none';
        externoFields.style.display = 'block';
    }
    actualizarResponsableDisplay();
}

function manejarDepartamentoOtro() {
    const select = document.getElementById('departamentoSelect');
    const nuevoField = document.getElementById('departamento-nuevo-field');
    const nuevoInput = document.getElementById('nuevoDepartamento');
    if (select.value === 'otro') {
        nuevoField.style.display = 'block';
        nuevoInput.required = true;
    } else {
        nuevoField.style.display = 'none';
        nuevoInput.required = false;
        nuevoInput.value = '';
        actualizarResponsableDisplay();
    }
}

function manejarInstitucionOtro() {
    const select = document.getElementById('institucionSelect');
    const nuevoField = document.getElementById('institucion-nuevo-field');
    const nuevoInput = document.getElementById('nuevaInstitucion');
    if (select.value === 'otro') {
        nuevoField.style.display = 'block';
        nuevoInput.required = true;
    } else {
        nuevoField.style.display = 'none';
        nuevoInput.required = false;
        nuevoInput.value = '';
        actualizarResponsableDisplay();
    }
}

// ==================== FUNCIONES PARA FORMULARIO DE CORREOS ====================

function actualizarCamposFormularioCorreo() {
    const tipo = document.getElementById('formTipoSolicitante').value;
    const internoFields = document.getElementById('formInternoFields');
    const externoFields = document.getElementById('formExternoFields');

    if (tipo === 'interno') {
        if (internoFields) internoFields.style.display = 'block';
        if (externoFields) externoFields.style.display = 'none';
    } else {
        if (internoFields) internoFields.style.display = 'none';
        if (externoFields) externoFields.style.display = 'block';
    }
    actualizarResponsableDisplayForm();
}

function actualizarResponsableDisplayForm() {
    const tipo = document.getElementById('formTipoSolicitante').value;
    let entidadId = null;
    let entidadNombre = '';

    if (tipo === 'interno') {
        entidadId = document.getElementById('formDepartamentoSelect').value;
        entidadNombre = document.getElementById('formDepartamentoSelect').selectedOptions[0]?.text || '';
    } else {
        entidadId = document.getElementById('formInstitucionSelect').value;
        entidadNombre = document.getElementById('formInstitucionSelect').selectedOptions[0]?.text || '';
    }

    const display = document.getElementById('formResponsableDisplay');
    const btnEditar = document.getElementById('formBtnEditarResponsable');

    if (entidadId && entidadId !== '' && entidadId !== 'otro') {
        const endpoint = tipo === 'interno' ? 'departamento' : 'institucion';
        fetch(`/api/${endpoint}/${entidadId}/responsable`)
            .then(r => r.json())
            .then(data => {
                if (data.responsable && data.responsable.nombre) {
                    display.innerHTML = `
                        <div class="d-flex flex-column">
                            <strong class="mb-1">${escapeHtml(data.responsable.nombre)}</strong>
                            <div class="small text-muted">
                                ${data.responsable.departamento ? `<span>${escapeHtml(data.responsable.departamento)}</span>` : ''}
                                ${data.responsable.telefono ? `<span class="ms-2">📞 ${escapeHtml(data.responsable.telefono)}</span>` : ''}
                                ${data.responsable.email ? `<span class="ms-2">✉️ ${escapeHtml(data.responsable.email)}</span>` : ''}
                            </div>
                        </div>
                    `;
                    btnEditar.style.display = 'inline-block';
                } else {
                    display.innerHTML = `<span class="text-muted">⚠️ No hay responsable asignado a ${entidadNombre}. Haz clic en "Editar" para agregar uno.</span>`;
                    btnEditar.style.display = 'inline-block';
                }
            })
            .catch(() => {
                display.innerHTML = `<span class="text-danger">Error al cargar responsable de ${entidadNombre}</span>`;
                btnEditar.style.display = 'inline-block';
            });
    } else {
        display.innerHTML = '<span class="text-muted">Selecciona una institución o departamento</span>';
        btnEditar.style.display = 'none';
    }
}

function editarResponsableActualForm() {
    const tipo = document.getElementById('formTipoSolicitante').value;
    let entidadId = null;

    if (tipo === 'interno') {
        entidadId = document.getElementById('formDepartamentoSelect').value;
    } else {
        entidadId = document.getElementById('formInstitucionSelect').value;
    }

    if (entidadId && entidadId !== '' && entidadId !== 'otro') {
        const entidadNombre = document.getElementById(tipo === 'interno' ? 'formDepartamentoSelect' : 'formInstitucionSelect').selectedOptions[0]?.text;
        abrirModalEditarResponsable(tipo === 'interno' ? 'departamento' : 'institucion', entidadId, entidadNombre);
    }
}

function manejarFormDepartamentoOtro() {
    const select = document.getElementById('formDepartamentoSelect');
    const nuevoField = document.getElementById('formDepartamentoNuevoField');
    const nuevoInput = document.getElementById('formNuevoDepartamento');
    if (select && select.value === 'otro') {
        nuevoField.style.display = 'block';
        nuevoInput.required = true;
    } else {
        if (nuevoField) nuevoField.style.display = 'none';
        if (nuevoInput) {
            nuevoInput.required = false;
            nuevoInput.value = '';
        }
        actualizarResponsableDisplayForm();
    }
}

function manejarFormInstitucionOtro() {
    const select = document.getElementById('formInstitucionSelect');
    const nuevoField = document.getElementById('formInstitucionNuevoField');
    const nuevoInput = document.getElementById('formNuevaInstitucion');
    if (select && select.value === 'otro') {
        nuevoField.style.display = 'block';
        nuevoInput.required = true;
    } else {
        if (nuevoField) nuevoField.style.display = 'none';
        if (nuevoInput) {
            nuevoInput.required = false;
            nuevoInput.value = '';
        }
        actualizarResponsableDisplayForm();
    }
}

// ==================== FUNCIONES DE CORREOS ====================
function cargarListaCorreos() {
    const container = document.getElementById('listaCorreosContainer');
    if (correosPendientes.length === 0) {
        container.innerHTML = `<div class="text-center py-5 text-muted">No hay correos pendientes</div>`;
        return;
    }
    let html = '';
    for (const correo of correosPendientes) {
        html += `<div class="correo-item" onclick="seleccionarCorreo(${correo.id})">
            <div class="d-flex align-items-center gap-2 mb-2">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d"><rect x="2" y="4" width="16" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>
                <strong class="flex-grow-1 small">${escapeHtml(correo.from)}</strong>
                <small class="text-muted">${correo.date}</small>
            </div>
            <div class="small fw-semibold">${escapeHtml(correo.subject)}</div>
            <div class="small text-muted mt-1">${escapeHtml(correo.body.substring(0, 80))}...</div>
        </div>`;
    }
    container.innerHTML = html;
}

function seleccionarCorreo(id) {
    const correo = correosPendientes.find(c => c.id === id);
    if (!correo) return;

    document.getElementById('previewCorreo').innerHTML = `
        <div class="border rounded-3 p-3" style="background: white;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M22 7l-10 7L2 7"/>
                        </svg>
                    </div>
                    <div>
                        <strong>${escapeHtml(correo.from)}</strong>
                        <div class="small text-muted">${correo.date}</div>
                    </div>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary">Correo seleccionado</span>
            </div>
            <div class="mb-2"><strong>Asunto:</strong> ${escapeHtml(correo.subject)}</div>
            <div class="p-3 rounded-2" style="background: #f8f9fc; border-left: 3px solid #1e3c72;">${escapeHtml(correo.body)}</div>
        </div>
    `;

    document.getElementById('correoOrigen').value = correo.from;
    document.getElementById('formPrioridad').value = correo.extracted.prioridad;
    document.getElementById('formFechaRequerida').value = correo.extracted.fecha_requerida;
    document.getElementById('formJustificacion').value = correo.extracted.justificacion;

    const fechaReq = new Date(correo.extracted.fecha_requerida);
    fechaReq.setDate(fechaReq.getDate() + 7);
    document.getElementById('formFechaFin').value = fechaReq.toISOString().split('T')[0];

    const container = document.getElementById('items-container-correo');
    container.innerHTML = `
        <div class="item-card-correo p-3 mb-3" style="background: #f8f9fc; border: 1px solid #e9ecef; border-radius: 12px;">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="small text-muted">Tipo</label>
                    <select name="items[0][tipo_item]" required class="form-select form-select-sm" style="border-radius: 8px; background: white;">
                        <option value="activo">Activo</option>
                        <option value="periferico">Periférico</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="small text-muted">Descripción del Item</label>
                    <input type="text" name="items[0][item_descripcion]" required placeholder="Ej: Laptop HP, Mouse, Cargador, etc." class="form-control form-control-sm" style="border-radius: 8px; background: white;">
                </div>
                <div class="col-md-2">
                    <label class="small text-muted">Cantidad</label>
                    <input type="number" name="items[0][cantidad]" min="1" value="1" required class="form-control form-control-sm" style="border-radius: 8px; background: white;">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 text-end">
                    <button type="button" class="remove-item-correo btn btn-sm text-danger" style="font-size: 14px;">× Eliminar</button>
                </div>
            </div>
        </div>
    `;
    itemCountCorreo = 1;
}

function revisarCorreosManual() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Revisando...';
    btn.disabled = true;

    setTimeout(() => {
        const nuevoCorreo = {
            id: correosPendientes.length + 1,
            from: "solicitudes@example.com",
            subject: "Nueva solicitud de préstamo - " + new Date().toLocaleDateString(),
            date: new Date().toISOString().split('T')[0],
            body: "Se solicita el préstamo de equipos para el departamento de TI. Fecha requerida: " + new Date(Date.now() + 3 * 24 * 60 * 60 * 1000).toISOString().split('T')[0] + ". Prioridad: normal. Justificación: Necesitamos equipos para el nuevo proyecto.",
            extracted: {
                prioridad: "normal",
                fecha_requerida: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                justificacion: "Necesitamos equipos para el nuevo proyecto"
            }
        };
        correosPendientes.push(nuevoCorreo);
        cargarListaCorreos();
        btn.innerHTML = originalText;
        btn.disabled = false;

        document.getElementById('notificacionCorreos').style.display = 'inline-block';
        document.getElementById('notificacionCorreos').textContent = correosPendientes.length;

        mostrarNotificacion('success', 'Correos actualizados', `Se encontraron ${correosPendientes.length} correos pendientes`);
    }, 1500);
}

// ==================== ITEMS DINÁMICOS ====================
let itemCountModal = 1;
let itemCountCorreo = 1;

document.getElementById('add-item-modal')?.addEventListener('click', function() {
    const container = document.getElementById('items-container-modal');
    const newCard = document.createElement('div');
    newCard.className = 'item-card-modal p-3 mb-3';
    newCard.style.cssText = 'background: #f8f9fc; border: 1px solid #e9ecef; border-radius: 12px;';
    newCard.innerHTML = `<div class="row g-2 align-items-end">
        <div class="col-md-4">
            <select name="items[${itemCountModal}][tipo_item]" required class="form-select form-select-sm" style="border-radius: 8px; background: white;">
                <option value="activo">Activo</option>
                <option value="periferico">Periférico</option>
            </select>
        </div>
        <div class="col-md-6">
            <input type="text" name="items[${itemCountModal}][item_descripcion]" required placeholder="Ej: Laptop HP, Mouse" class="form-control form-control-sm" style="border-radius: 8px; background: white;">
        </div>
        <div class="col-md-2">
            <input type="number" name="items[${itemCountModal}][cantidad]" min="1" value="1" required class="form-control form-control-sm" style="border-radius: 8px; background: white;">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12 text-end">
            <button type="button" class="remove-item-modal btn btn-sm text-danger" style="font-size: 14px;">× Eliminar</button>
        </div>
    </div>`;
    container.appendChild(newCard);
    itemCountModal++;
});

document.getElementById('add-item-correo')?.addEventListener('click', function() {
    const container = document.getElementById('items-container-correo');
    const newCard = document.createElement('div');
    newCard.className = 'item-card-correo p-3 mb-3';
    newCard.style.cssText = 'background: #f8f9fc; border: 1px solid #e9ecef; border-radius: 12px;';
    newCard.innerHTML = `<div class="row g-2 align-items-end">
        <div class="col-md-4">
            <select name="items[${itemCountCorreo}][tipo_item]" required class="form-select form-select-sm" style="border-radius: 8px; background: white;">
                <option value="activo">Activo</option>
                <option value="periferico">Periférico</option>
            </select>
        </div>
        <div class="col-md-6">
            <input type="text" name="items[${itemCountCorreo}][item_descripcion]" required placeholder="Ej: Laptop HP, Mouse" class="form-control form-control-sm" style="border-radius: 8px; background: white;">
        </div>
        <div class="col-md-2">
            <input type="number" name="items[${itemCountCorreo}][cantidad]" min="1" value="1" required class="form-control form-control-sm" style="border-radius: 8px; background: white;">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12 text-end">
            <button type="button" class="remove-item-correo btn btn-sm text-danger" style="font-size: 14px;">× Eliminar</button>
        </div>
    </div>`;
    container.appendChild(newCard);
    itemCountCorreo++;
});

document.addEventListener('click', e => {
    if (e.target.classList.contains('remove-item-modal') || e.target.parentElement?.classList?.contains('remove-item-modal')) {
        const btn = e.target.classList.contains('remove-item-modal') ? e.target : e.target.parentElement;
        const card = btn.closest('.item-card-modal');
        if (document.querySelectorAll('#items-container-modal .item-card-modal').length > 1) {
            card.remove();
        } else {
            mostrarNotificacion('warning', 'Atención', 'Debe haber al menos un item');
        }
    }
    if (e.target.classList.contains('remove-item-correo') || e.target.parentElement?.classList?.contains('remove-item-correo')) {
        const btn = e.target.classList.contains('remove-item-correo') ? e.target : e.target.parentElement;
        const card = btn.closest('.item-card-correo');
        if (document.querySelectorAll('#items-container-correo .item-card-correo').length > 1) {
            card.remove();
        } else {
            mostrarNotificacion('warning', 'Atención', 'Debe haber al menos un item');
        }
    }
});

document.getElementById('buscarCorreos')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('#listaCorreosContainer .correo-item');
    items.forEach(item => {
        const text = item.innerText.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// ==================== ENVÍO DE FORMULARIOS ====================

document.getElementById('formCrearSolicitud')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!validarFechas()) {
        mostrarNotificacion('error', 'Error', 'Por favor corrija los errores en el formulario');
        return;
    }

    const items = document.querySelectorAll('#items-container-modal .item-card-modal');
    if (items.length === 0) {
        mostrarNotificacion('error', 'Error', 'Debe agregar al menos un item');
        return;
    }
    let itemsValidos = true;
    items.forEach(item => {
        if(!item.querySelector('select[name$="[tipo_item]"]')?.value ||
           !item.querySelector('input[name$="[item_descripcion]"]')?.value ||
           item.querySelector('input[name$="[cantidad]"]')?.value < 1) itemsValidos = false;
    });
    if (!itemsValidos) {
        mostrarNotificacion('error', 'Error', 'Complete todos los datos de los items');
        return;
    }
    const submitBtn = document.getElementById('submitSolicitudBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';
    submitBtn.disabled = true;
    const formData = new FormData(this);
    try {
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        const result = await response.json();
        if (response.ok && result.success) {
            mostrarNotificacion('success', 'Solicitud Creada', 'Solicitud registrada exitosamente', { ID: result.solicitud_id || 'N/A', Items: items.length });
            cerrarModalCrear();
            setTimeout(() => cargarPagina(1), 1500);
        } else {
            mostrarNotificacion('error', 'Error', result.message || 'No se pudo crear');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error', 'Error de conexión');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

document.getElementById('formSolicitudCorreo')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const items = document.querySelectorAll('#items-container-correo .item-card-correo');
    if (items.length === 0) {
        mostrarNotificacion('error', 'Error', 'Debe agregar al menos un item');
        return;
    }

    let itemsValidos = true;
    items.forEach(item => {
        const tipo = item.querySelector('select[name$="[tipo_item]"]')?.value;
        const descripcion = item.querySelector('input[name$="[item_descripcion]"]')?.value;
        const cantidad = item.querySelector('input[name$="[cantidad]"]')?.value;
        if (!tipo || !descripcion || cantidad < 1) itemsValidos = false;
    });

    if (!itemsValidos) {
        mostrarNotificacion('error', 'Error', 'Complete todos los datos de los items');
        return;
    }

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';
    submitBtn.disabled = true;

    const formData = new FormData(this);

    try {
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
            mostrarNotificacion('success', 'Solicitud Creada', 'Solicitud registrada exitosamente', {
                ID: result.solicitud_id || 'N/A',
                Items: items.length
            });
            cerrarBandejaCorreos();
            setTimeout(() => cargarPagina(1), 1500);
        } else {
            mostrarNotificacion('error', 'Error', result.message || 'No se pudo crear la solicitud');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión', 'No se pudo conectar con el servidor');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// ==================== ENVÍO DE EDICIÓN ====================
document.addEventListener('DOMContentLoaded', function() {
    const formEditar = document.getElementById('formEditarSolicitud');

    if (formEditar) {
        const newForm = formEditar.cloneNode(true);
        formEditar.parentNode.replaceChild(newForm, formEditar);

        newForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            const id = document.getElementById('editId').value;

            if (!id) {
                mostrarNotificacion('error', 'Error', 'ID de solicitud no encontrado');
                return;
            }

            const fechaRequerida = document.getElementById('editFechaRequerida').value;
            const fechaFin = document.getElementById('editFechaFin').value;

            if (!fechaRequerida || !fechaFin) {
                mostrarNotificacion('error', 'Error', 'Las fechas son requeridas');
                return;
            }

            if (new Date(fechaFin) < new Date(fechaRequerida)) {
                mostrarNotificacion('error', 'Error de validación', 'La fecha fin no puede ser menor a la fecha requerida');
                return;
            }

            const formData = new FormData(this);
            if (!formData.has('_method')) {
                formData.append('_method', 'PUT');
            }

            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Actualizando...';
            submitBtn.disabled = true;

            try {
                const response = await fetch(`/solicitudes/${id}/update`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    mostrarNotificacion('success', '¡Éxito!', result.message || 'Solicitud actualizada correctamente');
                    cerrarModalEditar();
                    setTimeout(() => cargarPagina(currentPage), 1500);
                } else {
                    let errorMsg = result.message || 'No se pudo actualizar';
                    if (result.errors) {
                        errorMsg = Object.values(result.errors).flat().join('\n');
                    }
                    mostrarNotificacion('error', 'Error', errorMsg);
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarNotificacion('error', 'Error de conexión', 'No se pudo conectar con el servidor');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }
});

// ==================== EVENT LISTENERS ====================
document.getElementById('searchInput')?.addEventListener('input', aplicarFiltrosConDebounce);
document.getElementById('estadoFilter')?.addEventListener('change', aplicarFiltros);
document.getElementById('prioridadFilter')?.addEventListener('change', aplicarFiltros);
document.getElementById('fechaDesde')?.addEventListener('change', aplicarFiltros);
document.getElementById('fechaHasta')?.addEventListener('change', aplicarFiltros);
document.getElementById('filtroRapido')?.addEventListener('change', aplicarFiltroRapido);
document.getElementById('limpiarFiltros')?.addEventListener('click', () => {
    document.getElementById('searchInput').value = '';
    document.getElementById('estadoFilter').value = '';
    document.getElementById('prioridadFilter').value = '';
    document.getElementById('fechaDesde').value = '';
    document.getElementById('fechaHasta').value = '';
    document.getElementById('filtroRapido').value = '';
    aplicarFiltros();
});

document.getElementById('perPageSelect')?.addEventListener('change', (e) => {
    perPage = parseInt(e.target.value);
    cargarPagina(1);
});

document.getElementById('tipoSolicitante')?.addEventListener('change', actualizarCamposSolicitante);
document.getElementById('departamentoSelect')?.addEventListener('change', actualizarResponsableDisplay);
document.getElementById('institucionSelect')?.addEventListener('change', actualizarResponsableDisplay);
document.getElementById('departamentoSelect')?.addEventListener('change', manejarDepartamentoOtro);
document.getElementById('institucionSelect')?.addEventListener('change', manejarInstitucionOtro);

document.getElementById('editTipoSolicitante')?.addEventListener('change', function() {
    const tipo = this.value;
    const editInternoFields = document.getElementById('editInternoFields');
    const editExternoFields = document.getElementById('editExternoFields');
    if (tipo === 'interno') {
        editInternoFields.style.display = 'block';
        editExternoFields.style.display = 'none';
    } else {
        editInternoFields.style.display = 'none';
        editExternoFields.style.display = 'block';
    }
});

document.getElementById('editDepartamentoId')?.addEventListener('change', function() {
    const departamentoId = this.value;
    if (departamentoId) {
        fetch(`/api/departamento/${departamentoId}/responsable`)
            .then(r => r.json())
            .then(data => {
                if (data.responsable) {
                    const display = document.getElementById('editResponsableDisplay');
                    display.innerHTML = `
                        <div class="d-flex flex-column">
                            <strong class="mb-1">${escapeHtml(data.responsable.nombre)}</strong>
                            <div class="small text-muted">
                                ${data.responsable.departamento ? `<span>${escapeHtml(data.responsable.departamento)}</span>` : ''}
                                ${data.responsable.telefono ? `<span class="ms-2">📞 ${escapeHtml(data.responsable.telefono)}</span>` : ''}
                                ${data.responsable.email ? `<span class="ms-2">✉️ ${escapeHtml(data.responsable.email)}</span>` : ''}
                            </div>
                        </div>
                    `;
                    document.getElementById('editResponsableId').value = data.responsable.id;
                }
            });
    }
});

document.getElementById('editInstitucionId')?.addEventListener('change', function() {
    const institucionId = this.value;
    if (institucionId) {
        fetch(`/api/institucion/${institucionId}/responsable`)
            .then(r => r.json())
            .then(data => {
                if (data.responsable) {
                    const display = document.getElementById('editResponsableDisplay');
                    display.innerHTML = `
                        <div class="d-flex flex-column">
                            <strong class="mb-1">${escapeHtml(data.responsable.nombre)}</strong>
                            <div class="small text-muted">
                                ${data.responsable.departamento ? `<span>${escapeHtml(data.responsable.departamento)}</span>` : ''}
                                ${data.responsable.telefono ? `<span class="ms-2">📞 ${escapeHtml(data.responsable.telefono)}</span>` : ''}
                                ${data.responsable.email ? `<span class="ms-2">✉️ ${escapeHtml(data.responsable.email)}</span>` : ''}
                            </div>
                        </div>
                    `;
                    document.getElementById('editResponsableId').value = data.responsable.id;
                }
            });
    }
});

// Event listeners para el formulario de correos
document.getElementById('formTipoSolicitante')?.addEventListener('change', actualizarCamposFormularioCorreo);
document.getElementById('formDepartamentoSelect')?.addEventListener('change', manejarFormDepartamentoOtro);
document.getElementById('formInstitucionSelect')?.addEventListener('change', manejarFormInstitucionOtro);
document.getElementById('formDepartamentoSelect')?.addEventListener('change', actualizarResponsableDisplayForm);
document.getElementById('formInstitucionSelect')?.addEventListener('change', actualizarResponsableDisplayForm);

// Cerrar modales al hacer clic fuera
document.getElementById('modalCrear')?.addEventListener('click', e => { if(e.target === e.currentTarget) cerrarModalCrear(); });
document.getElementById('modalBandeja')?.addEventListener('click', e => { if(e.target === e.currentTarget) cerrarBandejaCorreos(); });
document.getElementById('modalEditar')?.addEventListener('click', e => { if(e.target === e.currentTarget) cerrarModalEditar(); });
document.getElementById('modalDetalles')?.addEventListener('click', e => { if(e.target === e.currentTarget) cerrarModalDetalles(); });
document.getElementById('modalConfirmacionCancelar')?.addEventListener('click', e => { if(e.target === e.currentTarget) cerrarModalConfirmacion(); });
document.getElementById('modalEditarResponsable')?.addEventListener('click', e => { if(e.target === e.currentTarget) cerrarModalEditarResponsable(); });

// Inicializar tooltips
document.querySelectorAll('[data-tooltip]').forEach(el => {
    el.setAttribute('title', el.getAttribute('data-tooltip'));
});

// Agregar event listener para ordenamiento de columnas
document.querySelectorAll('.sortable').forEach(header => {
    header.addEventListener('click', () => {
        const sortField = header.getAttribute('data-sort');
        if (sortField) {
            cambiarOrden(sortField);
        }
    });
});

// Aplicar fechas custom
document.getElementById('aplicarFechas')?.addEventListener('click', () => {
    const fechaDesde = document.getElementById('fechaDesdeCustom').value;
    const fechaHasta = document.getElementById('fechaHastaCustom').value;
    if (fechaDesde) document.getElementById('fechaDesde').value = fechaDesde;
    if (fechaHasta) document.getElementById('fechaHasta').value = fechaHasta;
    aplicarFiltros();
});

// ==================== INICIALIZAR ====================
renderizarTabla();
renderizarPaginacion();
actualizarEstadisticas();
actualizarCamposSolicitante();
initValidacionTiempoReal();

document.getElementById('resultadosCount').textContent = todasLasSolicitudes.length;
document.getElementById('totalRegistrosCount').textContent = totalRegistros;
document.getElementById('currentPageDisplay').textContent = currentPage;
document.getElementById('lastPageDisplay').textContent = lastPage;
document.getElementById('perPageSelect').value = perPage;

setTimeout(() => {
    if(correosPendientes.length > 0) {
        document.getElementById('notificacionCorreos').style.display = 'inline-block';
        document.getElementById('notificacionCorreos').textContent = correosPendientes.length;
    }
}, 500);

// Inicializar iconos de ordenamiento en la cabecera
document.querySelectorAll('.sortable').forEach(header => {
    const sortField = header.getAttribute('data-sort');
    if (sortField === sortColumn) {
        const icon = header.querySelector('.sort-icon');
        if (icon) icon.textContent = sortDirection === 'asc' ? '↑' : '↓';
    }
});
</script>
@endsection
