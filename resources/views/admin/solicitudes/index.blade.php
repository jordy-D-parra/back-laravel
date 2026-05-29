@extends('layouts.dashboard')

@section('title', 'Solicitudes de Préstamo')

@section('styles')
    @vite(['resources/css/admin-solicitudes.css'])
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #1e3c72;">Solicitudes de Préstamo</h3>
            <p class="text-muted mb-0">Gestión de solicitudes de préstamo de equipos</p>
        </div>
        @if(auth()->user()->hasPermission('crear-solicitud'))
        <button class="btn btn-primary-dark" onclick="abrirModalCrear()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nueva Solicitud
        </button>
        @endif
    </div>

    <!-- Tarjetas de estadísticas -->
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
            <div class="stat-icon-circle">
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
            <div class="stat-icon-circle">
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
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="18" y1="6" x2="6" y2="18"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filtros simplificados (solo 3) -->
    <div class="filters-bar">
        <div class="flex-grow-1">
            <input type="text" class="form-control" id="searchInput" placeholder="Buscar...">
        </div>
        <select class="form-select" id="estadoFilter" style="width: 130px;">
            <option value="">Todos</option>
            <option value="pendiente">Pendiente</option>
            <option value="aprobada">Aprobada</option>
            <option value="rechazada">Rechazada</option>
            <option value="cancelada">Cancelada</option>
        </select>
        <select class="form-select" id="prioridadFilter" style="width: 120px;">
            <option value="">Prioridad</option>
            <option value="baja">Baja</option>
            <option value="normal">Normal</option>
            <option value="alta">Alta</option>
            <option value="urgente">Urgente</option>
        </select>
        <button id="limpiarFiltros" class="btn btn-outline-primary-dark">Limpiar</button>
    </div>

    <!-- Tabla -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                    <tr><td colspan="9" class="text-center py-4 text-muted">Cargando...</td></tr>
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

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <div class="text-muted small">
            Mostrando <span id="resultadosCount">0</span> de <span id="totalRegistrosCount">0</span>
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0" id="paginationContainer"></ul>
        </nav>
    </div>
</div>

<!-- MODAL VER DETALLES -->
<div class="modal fade" id="modalDetalles" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetallesBody">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div><p>Cargando...</p></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CREAR -->
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCrearSolicitud" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo Solicitante</label>
                            <select name="tipo_solicitante" id="tipoSolicitante" class="form-select" required>
                                <option value="interno">Interno (Departamento)</option>
                                <option value="externo">Externo (Institución)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prioridad</label>
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
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Requerida</label>
                            <input type="date" name="fecha_requerida" id="fechaRequeridaInput" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Fin Estimada</label>
                            <input type="date" name="fecha_fin_estimada" id="fechaFinEstimadaInput" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Justificación</label>
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
                            <label class="form-label mb-0">Items</label>
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
                    <button type="submit" class="btn btn-primary-dark">Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarSolicitud">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="tipo_solicitante" id="editTipoSolicitante" class="form-select" required>
                                <option value="interno">Interno</option>
                                <option value="externo">Externo</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prioridad</label>
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
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Requerida</label>
                            <input type="date" name="fecha_requerida" id="editFechaRequerida" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin_estimada" id="editFechaFin" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Justificación</label>
                        <textarea name="justificacion" id="editJustificacion" rows="3" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="editObservaciones" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CANCELAR -->
<div class="modal fade" id="modalConfirmacionCancelar" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Cancelar Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>¿Estás seguro?</p>
                <small class="text-muted">Esta acción no se puede deshacer</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" id="btnConfirmarCancelar" class="btn btn-danger">Sí, cancelar</button>
            </div>
        </div>
    </div>
</div>

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
