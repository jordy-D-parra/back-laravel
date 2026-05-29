{{-- resources/views/admin/soporte/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Fichas de Soporte Técnico')

@section('styles')
    @vite(['resources/css/admin-soporte.css'])
    @vite(['resources/css/contrast-system.css'])
    @vite(['resources/css/skeleton-loading.css'])
    @vite(['resources/css/smooth-modals.css'])
    <style>
        .bg-primary-dark {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        .modal-header.bg-primary-dark .btn-close {
            filter: brightness(0) invert(1);
        }
        .highlight {
            background: #fff3cd;
            padding: 1px 3px;
            border-radius: 3px;
        }
        .stat-icon-circle svg {
            width: 24px;
            height: 24px;
            stroke: #1e3c72;
            stroke-width: 1.8;
            fill: none;
        }
        .stat-card-mini:hover .stat-icon-circle svg {
            stroke: white;
        }
        .badge-estado-en-proceso {
            background: #fd7e14;
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .badge-estado-finalizado {
            background: #28a745;
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .btn-cerrar-ficha {
            background: #fd7e14;
            border: none;
            color: white;
            transition: all 0.2s ease;
        }
        .btn-cerrar-ficha:hover {
            background: #e66a0a;
            color: white;
            transform: translateY(-1px);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #1e3c72;">Fichas de Soporte Técnico</h3>
            <p class="text-muted mb-0">Gestión de mantenimiento y reparaciones</p>
        </div>
        <div class="dropdown">
            <button class="btn btn-primary-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nueva Ficha
            </button>
            <ul class="dropdown-menu">
                @if(auth()->user()->hasPermission('crear-ficha-soporte'))
                <li>
                    <a class="dropdown-item" href="#" onclick="abrirModalCrearFicha(); return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                            <rect x="2" y="6" width="20" height="12" rx="2"/>
                        </svg>
                        Nueva Ficha de Soporte
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-row mb-4">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsTotal">0</div>
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
                <div class="stat-number" id="statsEnProceso">0</div>
                <div class="stat-label">En Proceso</div>
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
                <div class="stat-number" id="statsFinalizados">0</div>
                <div class="stat-label">Finalizados</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsEquiposReparacion">0</div>
                <div class="stat-label">En Reparación</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters-bar mb-3">
        <div class="flex-grow-1">
            <input type="text" id="buscarFichas" class="form-control" placeholder="Buscar por activo, técnico, reportante...">
        </div>
        <select id="filtroEstadoFichas" class="form-select" style="width:130px">
            <option value="">Todos</option>
            <option value="en_proceso">En Proceso</option>
            <option value="finalizado">Finalizado</option>
        </select>
        <button class="btn btn-outline-primary-dark" id="limpiarFiltros">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            Limpiar
        </button>
    </div>

    <!-- Tabla SIN columna ID -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Activo</th>
                        <th>Técnico</th>
                        <th>Reporta</th>
                        <th>Ingreso</th>
                        <th>Salida</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaFichas">
                    <tr><td colspan="7" class="text-center py-4 text-muted">Cargando...<\/td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small" id="paginationInfo"></div>
        <nav>
            <ul class="pagination pagination-sm mb-0" id="paginationContainer"></ul>
        </nav>
    </div>
</div>

<!-- MODAL CREAR FICHA -->
<div class="modal fade" id="modalCrearFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalCrearFichaLabel">Nueva Ficha de Soporte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCrearFicha">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Activo <span class="text-danger">*</span></label>
                            <select name="activo_id" id="fichaActivoId" class="form-select" required>
                                <option value="">Seleccionar activo...</option>
                                @foreach($activosDisponibles as $activo)
                                    <option value="{{ $activo->id }}">{{ $activo->serial }} - {{ $activo->modelo->nombre ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                            <div id="activoErrorMensaje" style="display: none;"></div>
                            <small class="text-muted">Solo se muestran activos disponibles</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Técnico Responsable</label>
                            <select name="tecnico_id" id="fichaTecnicoId" class="form-select">
                                <option value="">Seleccionar técnico...</option>
                                @foreach($tecnicos as $tecnico)
                                    <option value="{{ $tecnico->id }}">{{ $tecnico->trabajador->nombre ?? '' }} {{ $tecnico->trabajador->apellido ?? '' }} ({{ $tecnico->usuario }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Técnico <span class="text-danger">*</span></label>
                            <input type="text" name="tecnico_nombre" id="fichaTecnicoNombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usuario que Reporta <span class="text-danger">*</span></label>
                            <input type="text" name="usuario_reporta_nombre" id="fichaUsuarioReporta" class="form-control" required>
                        </div>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">Guardar Ficha</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CERRAR FICHA -->
<div class="modal fade" id="modalCerrarFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white">Cerrar Ficha de Soporte</h5>
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
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                            <rect x="2" y="6" width="20" height="12" rx="2"/>
                        </svg>
                        Estado de Componentes
                    </h6>
                    <div id="componentesContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cerrar-ficha">Finalizar Ficha</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DETALLE -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalDetalleLabel">Detalle de Ficha</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">
                <div class="text-center py-4 text-muted">Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR -->
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: 320px;"></div>
@endsection

@section('scripts')
    @vite(['resources/js/admin-soporte.js'])
    <script>
        window.userPermissions = @json(auth()->user()->rol->permisos->pluck('nombre'));
        function authUserHasPermission(p) { return window.userPermissions.includes(p); }
    </script>
@endsection