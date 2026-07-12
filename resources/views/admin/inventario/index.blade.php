@extends('layouts.dashboard')

@section('title', 'Inventario')

@section('styles')
    @vite(['resources/css/admin-inventario.css'])
    @vite(['resources/css/contrast-system.css'])
    @vite(['resources/css/smooth-modals.css'])
    <style>
        .bg-primary-dark { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); }
        .modal-header.bg-primary-dark .btn-close { filter: brightness(0) invert(1); }
        .badge-en-bodega { background: #6c757d; color: #fff; }
        .badge-instalado { background: #198754; color: #fff; }
        .badge-prestado { background: #ffc107; color: #000; }
        .badge-en-reparacion { background: #0dcaf0; color: #000; }
        .badge-desechado { background: #dc3545; color: #fff; }
        .badge-garantia-vencida { background: #dc3545; color: #fff; font-size: 0.7rem; }
        .badge-garantia-vigente { background: #198754; color: #fff; font-size: 0.7rem; }
        .modelo-info-badges { display: flex; gap: 0.5rem; margin-top: 0.25rem; flex-wrap: wrap; }
        .modelo-info-badges .badge { font-size: 0.7rem; }
        .stat-icon-circle svg { width: 24px; height: 24px; stroke: #1e3c72; stroke-width: 1.8; fill: none; }
        .stat-card-mini:hover .stat-icon-circle svg { stroke: white; }
        .btn-cambiar-estado {
            background: transparent;
            border: 1px solid #ffc107;
            color: #856404;
            transition: all 0.2s ease;
        }
        .btn-cambiar-estado:hover {
            background: #ffc107;
            color: #1a1a1a;
            transform: scale(1.02);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #1e3c72;">Inventario</h3>
            <p class="text-muted mb-0">Gestión de activos y componentes</p>
        </div>
        @if(auth()->user()->hasPermission('crear-activo'))
        <button class="btn btn-primary-dark" onclick="abrirModalActivo()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo Activo
        </button>
        @endif
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-row mb-4">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalActivos }}</div>
                <div class="stat-label">Total Activos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
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
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
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
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
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
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <path d="M17 11l2.5-2.5M22 9l-2.5 2.5M19 11.5V6"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs nav-tabs-custom mb-3">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#activos">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                    <line x1="9" y1="4" x2="9" y2="20"/>
                    <line x1="15" y1="4" x2="15" y2="20"/>
                </svg>
                Activos <span class="tab-badge">{{ $totalActivos }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#componentes">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <rect x="2" y="6" width="20" height="12" rx="2" ry="2"/>
                    <line x1="9" y1="6" x2="9" y2="18"/>
                </svg>
                Componentes <span class="tab-badge">{{ $totalComponentes }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <!-- ACTIVOS -->
        <div class="tab-pane fade show active" id="activos">
            <div class="filters-bar mb-3">
                <div class="flex-grow-1">
                    <input type="text" id="buscarActivos" class="form-control" placeholder="Buscar por serial, modelo...">
                </div>
                <select id="filtroEstadoActivos" class="form-select" style="width:160px">
                    <option value="">Todos los estados</option>
                </select>
                @if(auth()->user()->hasPermission('crear-activo'))
                <button class="btn btn-primary-dark" onclick="abrirModalActivo()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo
                </button>
                @endif
            </div>
            <div class="table-container">
                <table class="table table-hover">
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
                        <tr><td colspan="6" class="text-center py-4 text-muted">Cargando...<\/td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- COMPONENTES -->
        <div class="tab-pane fade" id="componentes">
            <div class="filters-bar mb-3">
                <div class="flex-grow-1">
                    <input type="text" id="buscarComponentes" class="form-control" placeholder="Buscar por tipo, marca, serial...">
                </div>
                <select id="filtroTipoComponentes" class="form-select" style="width:150px">
                    <option value="">Todos los tipos</option>
                </select>
                <select id="filtroEstadoComponentes" class="form-select" style="width:160px">
                    <option value="">Todos los estados</option>
                </select>
                @if(auth()->user()->hasPermission('crear-componente'))
                <button class="btn btn-primary-dark" onclick="abrirModalComponente()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo
                </button>
                @endif
            </div>
            <div class="table-container">
                <table class="table table-hover">
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
                        <tr><td colspan="7" class="text-center py-4 text-muted">Cargando...<\/td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ACTIVO -->
<div class="modal fade" id="modalActivo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalActivoLabel">Nuevo Activo</h5>
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
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Modelo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="activo_modelo_buscar" placeholder="Escriba para buscar modelo..." autocomplete="off" oninput="filtrarModelos()" onfocus="filtrarModelos()">
                            <input type="hidden" id="activo_modelo_id" name="modelo_id">
                            <div id="modeloDropdown" class="list-group" style="display:none; position:absolute; z-index:1000; max-height:200px; overflow-y:auto; width:calc(100% - 1.5rem);"></div>
                            <div id="modeloInfoBadges" class="modelo-info-badges"></div>
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
                    <h6 class="fw-bold mb-3" style="color: #1e3c72;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                        </svg>
                        Guardar Activo y Componentes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL COMPONENTE -->
<div class="modal fade" id="modalComponente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalComponenteLabel">Nuevo Componente</h5>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                        </svg>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DETALLE -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalDetalleLabel">Detalle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">Cargando...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Eliminar este registro?</p>
                <p class="fw-bold text-danger" id="deleteNombre"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CAMBIAR ESTADO - CON SELECTOR -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="me-2">
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
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" class="me-1">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4M12 8h.01"/>
                    </svg>
                    Los estados terminales no se pueden cambiar.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary-dark" id="btnConfirmarCambioEstado">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="me-1">
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
    @vite(['resources/js/help-panel.js'])
    @vite(['resources/js/validations.js'])
    <script>
        // Permisos del usuario desde el backend
        window.userPermissions = @json(auth()->user()->rol->permisos->pluck('nombre'));

        function authUserHasPermission(permiso) {
            return window.userPermissions.includes(permiso);
        }
    </script>
@endsection
