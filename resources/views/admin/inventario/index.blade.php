@extends('layouts.dashboard')

@section('title', 'Inventario')

@section('styles')
    @vite(['resources/css/admin-inventario.css'])
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
        </div>
    </div>

    <!-- ========== SECCIÓN DE ACTIVOS ========== -->
    <div id="seccionActivos">
        <!-- Barra de filtros para Activos -->
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

        <!-- Tabla de Activos -->
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
        <!-- Barra de filtros para Componentes -->
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

        <!-- Tabla de Componentes -->
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
</div>

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

            document.getElementById('btnActivos').className = 'btn btn-primary-dark active';
            document.getElementById('btnComponentes').className = 'btn btn-outline-primary-dark';

            if (typeof cargarActivos === 'function') {
                cargarActivos();
            }
        }

        function mostrarComponentes() {
            document.getElementById('seccionActivos').style.display = 'none';
            document.getElementById('seccionComponentes').style.display = 'block';

            document.getElementById('btnComponentes').className = 'btn btn-primary-dark active';
            document.getElementById('btnActivos').className = 'btn btn-outline-primary-dark';

            if (typeof cargarComponentes === 'function') {
                cargarComponentes();
            }
        }

        // ============================================================
        // INICIALIZACIÓN - Cargar Activos por defecto
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar activos al inicio
            if (typeof cargarActivos === 'function') {
                cargarActivos();
            }

            // Búsqueda en tiempo real
            const buscarActivos = document.getElementById('buscarActivos');
            if (buscarActivos) {
                buscarActivos.addEventListener('input', function() {
                    if (typeof cargarActivos === 'function') {
                        cargarActivos();
                    }
                });
            }

            const buscarComponentes = document.getElementById('buscarComponentes');
            if (buscarComponentes) {
                buscarComponentes.addEventListener('input', function() {
                    if (typeof cargarComponentes === 'function') {
                        cargarComponentes();
                    }
                });
            }

            // Filtros
            const filtroEstadoActivos = document.getElementById('filtroEstadoActivos');
            if (filtroEstadoActivos) {
                filtroEstadoActivos.addEventListener('change', function() {
                    if (typeof cargarActivos === 'function') {
                        cargarActivos();
                    }
                });
            }

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
        });
    </script>
@endsection