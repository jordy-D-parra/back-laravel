@extends('layouts.dashboard')

@section('title', 'Fichas de Soporte Técnico')

@section('styles')
    @vite(['resources/css/admin-soporte.css'])
    <style>
        /* Estilos para el feedback del serial */
        #ext_serial_feedback {
            font-size: 0.75rem;
            margin-top: 4px;
            display: block;
            transition: all 0.2s ease;
        }
        #ext_serial_feedback.text-success {
            color: #1e7e34 !important;
        }
        #ext_serial_feedback.text-danger {
            color: #c5221f !important;
        }
        #ext_serial_feedback.text-muted {
            color: #6c757d !important;
        }

        .is-valid {
            border-color: #1e7e34 !important;
        }
        .is-invalid {
            border-color: #c5221f !important;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4">

    <!-- ========== HEADER CON GRADIENTE ========== -->
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
            <button class="btn btn-primary-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1" style="display:inline-block;">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nueva Ficha
            </button>
            <ul class="dropdown-menu">
                @if(auth()->user()->hasPermission('crear-ficha-soporte'))
                <li>
                    <a class="dropdown-item" href="#" onclick="window.abrirModalCrearFicha(); return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2" style="display:inline-block;">
                            <rect x="2" y="6" width="20" height="12" rx="2"/>
                        </svg>
                        Crear Ficha Soporte
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="window.abrirModalEquipoExterno(); return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2" style="display:inline-block;">
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

    <!-- ========== TARJETAS DE ESTADÍSTICAS ========== -->
    <div class="stats-row">
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
            <div class="stat-icon-circle" style="background: rgba(246, 194, 62, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f6c23e" stroke-width="1.8">
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
            <div class="stat-icon-circle" style="background: rgba(30, 126, 52, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1e7e34" stroke-width="1.8">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsEquiposReparacion">0</div>
                <div class="stat-label">En Reparación</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(23, 162, 184, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#17a2b8" stroke-width="1.8">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- ========== BARRA DE FILTROS ========== -->
    <div class="filters-bar">
        <div class="filtro-busqueda">
            <div class="input-group">
                <span class="input-group-text">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </span>
                <input type="text" class="form-control" id="buscarFichas" placeholder="Buscar por activo, técnico, reportante...">
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
         
        </div>
    </div>

    <!-- ========== TABLA ========== -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
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
                    <tr><td colspan="7" class="text-center py-4 text-muted">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========== PAGINACIÓN ========== -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small" id="paginationInfo"></div>
        <nav>
            <ul class="pagination pagination-sm mb-0" id="paginationContainer"></ul>
        </nav>
    </div>
</div>

<!-- ========== MODAL CREAR FICHA ========== -->
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
                    <!-- Buscador de Activo -->
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

                    <!-- Buscador de Técnico -->
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
                                <input type="text" class="form-control" id="tecnicoBuscarInput"
                                       placeholder="Buscar por cédula, nombre o usuario..." autocomplete="off">
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

                    <!-- Usuario que Reporta -->
                    <div class="mb-3">
                        <label class="form-label">Usuario que Reporta <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fichaUsuarioReporta" name="usuario_reporta_nombre" required>
                    </div>

                    <!-- Diagnóstico -->
                    <div class="mb-3">
                        <label class="form-label">Diagnóstico</label>
                        <textarea name="diagnostico" id="fichaDiagnostico" rows="3" class="form-control" placeholder="Describa el problema del equipo..."></textarea>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="fichaObservaciones" rows="2" class="form-control" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarFicha">Guardar Ficha</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== 🆕 MODAL EQUIPO EXTERNO CON FEEDBACK DE SERIAL ========== -->
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
                    <!-- Datos del Equipo -->
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
                                <!-- ========== 🆕 FEEDBACK DE SERIAL ========== -->
                                <small id="ext_serial_feedback" class="text-muted">
                                    Ingrese el número de serie para verificar si ya existe
                                </small>
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

                    <!-- Datos de la Ficha de Soporte -->
                    <div class="mt-3">
                        <h6 style="color:#1e3c72; font-weight:600; margin-bottom:1rem; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; margin-right:6px;">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            Datos de la Ficha de Soporte
                        </h6>

                        <!-- Buscador de Técnico -->
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
                                    <input type="text" class="form-control" id="ext_tecnicoBuscarInput"
                                           placeholder="Buscar por cédula, nombre o usuario..." autocomplete="off">
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
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarEquipoExterno">
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

<!-- ========== MODAL CERRAR FICHA ========== -->
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

<!-- ========== MODAL DETALLE ========== -->
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
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL ELIMINAR ========== -->
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

<!-- ========== NOTIFICACIONES ========== -->
<div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: 320px;"></div>

@endsection

@section('scripts')
    @vite(['resources/js/admin-soporte.js'])
    <script>
        window.userPermissions = @json(auth()->user()->rol->permisos->pluck('nombre'));
        function authUserHasPermission(p) { return window.userPermissions.includes(p); }

        // ============================================================
        // 🆕 VALIDACIÓN DE SERIAL EN TIEMPO REAL (EQUIPO EXTERNO)
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            const serialInput = document.getElementById('ext_serial');
            const feedback = document.getElementById('ext_serial_feedback');
            
            if (serialInput && feedback) {
                serialInput.addEventListener('blur', function() {
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
                
                // Limpiar validación al escribir
                serialInput.addEventListener('input', function() {
                    this.classList.remove('is-valid', 'is-invalid');
                    const feedback = document.getElementById('ext_serial_feedback');
                    if (feedback) {
                        feedback.textContent = 'Ingrese el número de serie para verificar si ya existe';
                        feedback.className = 'text-muted';
                    }
                });
            }
        });
    </script>
@endsection