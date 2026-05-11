@extends('layouts.dashboard')

@section('title', 'Gestión de Inventario')

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
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="M8 8h8M8 12h6M8 16h4"/>
                                <circle cx="12" cy="12" r="2"/>
                            </svg>
                            Gestión de Inventario
                        </h2>
                        <p class="text-white-50 mb-0">Administra todos los activos tecnológicos de la Gobernación</p>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <button id="btnAgregarActivo" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 10px; padding: 10px 20px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                                <path d="M12 5v14M5 12h14"/>
                            </svg>
                            Nuevo Equipo
                        </button>
                        <div class="text-center">
                            <div style="font-size: 2rem;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <path d="M8 8h8M8 12h6M8 16h4"/>
                                    <circle cx="12" cy="12" r="2"/>
                                </svg>
                            </div>
                            <small class="text-white-50" id="totalActivosCount">Total: 0</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Panel de filtros -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">🔍 Buscar</label>
                            <div class="position-relative">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                <input type="text" id="filtroInventario" class="form-control" placeholder="Serial, marca/modelo o ubicación..." style="padding-left: 36px; border-radius: 10px; background: white;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">📂 Categoría</label>
                            <select id="filtroCategoria" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todas las categorías</option>
                                @foreach($tiposActivo as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">⚡ Estatus</label>
                            <select id="filtroEstatus" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todos los estatus</option>
                                @foreach($estatusList as $estatus)
                                    <option value="{{ $estatus->id }}">{{ $estatus->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="btnFiltrar" style="border-radius: 10px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                                    <polygon points="22 3 2 3 10 13 10 21 14 18 14 13 22 3"/>
                                </svg>
                                Filtrar
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted small">Mostrando <span id="resultadosCount">0</span> activos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de activos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-0 overflow-auto" style="max-height: 65vh;">
                    <table class="table table-hover mb-0" style="min-width: 1000px;">
                        <thead style="background: #f8f9fc; position: sticky; top: 0; z-index: 10;">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold">Serial</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Marca/Modelo</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Categoría</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Estatus</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Ubicación</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Vida Útil</th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaInventarioBody" style="background: white;">
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Cargando activos...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 py-3">
                    <div id="paginationLinks" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear/editar activo -->
<div id="activoModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 900px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2" id="modalTitle">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Nuevo Activo
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalActivo()"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto; background: white;">
                <input type="hidden" id="activo_id" name="activo_id">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Serial *</label>
                        <input type="text" id="serial" name="serial" class="form-control" style="border-radius: 10px; background: white;" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">📂 Categoría *</label>
                        <select id="id_categoria" name="id_categoria" class="form-select" style="border-radius: 10px; background: white;" required>
                            <option value="">Seleccione una categoría...</option>
                            @foreach($tiposActivo as $tipo)
                                <option value="{{ $tipo->id }}" data-vida-util="{{ $tipo->vida_util_por_defecto ?? 5 }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Marca *</label>
                        <select id="id_marca" name="id_marca" class="form-select" style="border-radius: 10px; background: white;" required disabled>
                            <option value="">Primero seleccione una categoría...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Modelo *</label>
                        <select id="id_modelo" name="id_modelo" class="form-select" style="border-radius: 10px; background: white;" required disabled>
                            <option value="">Primero seleccione una marca...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">⚡ Estatus *</label>
                        <select id="id_estatus" name="id_estatus" class="form-select" style="border-radius: 10px; background: white;" required>
                            <option value="">Seleccione un estatus...</option>
                            @foreach($estatusList as $estatus)
                                <option value="{{ $estatus->id }}">{{ $estatus->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" class="form-control" placeholder="Ej: Oficina 301, Laboratorio 2B" style="border-radius: 10px; background: white;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">📅 Fecha de Adquisición *</label>
                        <input type="date" id="fecha_adquisicion" name="fecha_adquisicion" class="form-control" style="border-radius: 10px; background: white;" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">⏱️ Vida Útil (años) *</label>
                        <input type="number" id="vida_util_anos" name="vida_util_anos" class="form-control" placeholder="Ej: 5" min="1" max="20" step="1" style="border-radius: 10px; background: white;" required>
                        <small class="text-muted">Según categoría del equipo</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">🔧 Fecha Fin de Garantía</label>
                        <input type="date" id="fecha_fin_garantia" name="fecha_fin_garantia" class="form-control" style="border-radius: 10px; background: white;">
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info" id="vidaUtilPreview" style="display: none; border-radius: 12px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; margin-right: 8px;">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <span id="vidaUtilMensaje"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" class="form-control" rows="2" style="border-radius: 10px; background: white;"></textarea>
                    </div>
                </div>

                <!-- Contenedor para campos específicos por categoría -->
                <div id="camposEspecificosContainer" class="row mt-3"></div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 d-flex justify-content-end gap-2">
                <button type="button" onclick="cerrarModalActivo()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                <button type="button" id="btnGuardarActivo" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalle -->
<div id="verActivoModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Detalle del Activo
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalVerActivo()"></button>
            </div>
            <div class="modal-body p-4" id="detalleActivoBody" style="background: white;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando...</p>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" onclick="cerrarModalVerActivo()" class="btn btn-light px-4" style="border-radius: 10px;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver especificaciones técnicas -->
<div id="especificacionesModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10002; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 2v2M12 20v2M22 12h-2M4 12H2"/>
                    </svg>
                    Especificaciones Técnicas
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalEspecificaciones()"></button>
            </div>
            <div class="modal-body p-4" id="especificacionesBody" style="background: white;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando...</p>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 d-flex justify-content-between">
                <button type="button" onclick="cerrarModalEspecificaciones()" class="btn btn-light px-4" style="border-radius: 10px;">Cerrar</button>
                <button type="button" id="btnEditarEspecificaciones" class="btn btn-primary px-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px; display: none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                    </svg>
                    Editar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminar -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10003; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <div class="d-flex align-items-center gap-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    <h5 class="modal-title text-white">Confirmar Eliminación</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalEliminar()"></button>
            </div>
            <div class="modal-body p-4 text-center" style="background: white;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5" style="margin-bottom: 16px;">
                    <path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/>
                </svg>
                <h5 class="mb-3 fw-bold" style="color: #1e3c72;">¿Estás seguro de eliminar este activo?</h5>
                <p class="text-muted mb-0">Esta acción no se puede deshacer.</p>
                <p class="text-muted small mt-2" id="deleteActivoInfo"></p>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 d-flex gap-2 justify-content-center">
                <button type="button" onclick="cerrarModalEliminar()" class="btn btn-light px-4" style="border-radius: 10px; padding: 10px 24px;">Cancelar</button>
                <button type="button" id="btnConfirmarEliminar" class="btn px-4" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border-radius: 10px; padding: 10px 24px;">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- Sistema de Notificaciones Toast -->
<div id="notification-container" style="position: fixed; top: 80px; right: 20px; z-index: 99999; width: 350px;"></div>

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
    .btn-accion-eliminar { background: rgba(220,53,69,0.1); color: #8b1a24; border: 1px solid rgba(220,53,69,0.3); }
    .btn-accion-eliminar:hover { background: #dc3545; color: white; border-color: #dc3545; }

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
// ========== VARIABLES GLOBALES ==========
let currentPage = 1;
let deleteId = null;
let currentActivoIdForEspecs = null;
let activoModal = null;
let verActivoModal = null;
let deleteModal = null;
let especificacionesModal = null;

let filters = {
    search: '',
    id_categoria: '',
    id_estatus: ''
};

// ========== NOTIFICACIONES ==========
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

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar modales
    const modalCrear = document.getElementById('activoModal');
    const modalVer = document.getElementById('verActivoModal');
    const modalDelete = document.getElementById('deleteModal');
    const modalEspec = document.getElementById('especificacionesModal');

    if (modalCrear) modalCrear.addEventListener('click', function(e) { if(e.target === e.currentTarget) cerrarModalActivo(); });
    if (modalVer) modalVer.addEventListener('click', function(e) { if(e.target === e.currentTarget) cerrarModalVerActivo(); });
    if (modalDelete) modalDelete.addEventListener('click', function(e) { if(e.target === e.currentTarget) cerrarModalEliminar(); });
    if (modalEspec) modalEspec.addEventListener('click', function(e) { if(e.target === e.currentTarget) cerrarModalEspecificaciones(); });

    configurarEventos();
    configurarJerarquia();
    configurarCamposPorCategoria();
    cargarActivos();

    // Configurar botón editar especificaciones
    const btnEditarEspecs = document.getElementById('btnEditarEspecificaciones');
    if (btnEditarEspecs) {
        btnEditarEspecs.addEventListener('click', function() {
            if (currentActivoIdForEspecs) {
                cerrarModalEspecificaciones();
                setTimeout(() => {
                    editarActivo(currentActivoIdForEspecs);
                }, 300);
            }
        });
    }
});

// ========== CONFIGURAR JERARQUÍA CATEGORÍA → MARCA → MODELO ==========
function configurarJerarquia() {
    const selectCategoria = document.getElementById('id_categoria');
    const selectMarca = document.getElementById('id_marca');
    const selectModelo = document.getElementById('id_modelo');

    if (!selectCategoria || !selectMarca || !selectModelo) return;

    selectCategoria.addEventListener('change', function() {
        const categoriaId = this.value;

        if (!categoriaId) {
            selectMarca.innerHTML = '<option value="">Primero seleccione una categoría...</option>';
            selectMarca.disabled = true;
            selectModelo.innerHTML = '<option value="">Primero seleccione una marca...</option>';
            selectModelo.disabled = true;
            return;
        }

        selectMarca.innerHTML = '<option value="">Cargando marcas...</option>';
        selectMarca.disabled = true;
        selectModelo.innerHTML = '<option value="">Primero seleccione una marca...</option>';
        selectModelo.disabled = true;

        fetch(`/api/marcas/por-categoria/${categoriaId}`)
            .then(response => response.json())
            .then(data => {
                selectMarca.innerHTML = '<option value="">Seleccione una marca...</option>';
                if (data.length === 0) {
                    selectMarca.innerHTML = '<option value="">No hay marcas para esta categoría</option>';
                } else {
                    data.forEach(marca => {
                        selectMarca.innerHTML += `<option value="${marca.id}">${escapeHtml(marca.nombre)}</option>`;
                    });
                    selectMarca.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error cargando marcas:', error);
                selectMarca.innerHTML = '<option value="">Error al cargar marcas</option>';
                mostrarNotificacion('error', 'Error', 'Error al cargar las marcas');
            });
    });

    selectMarca.addEventListener('change', function() {
        const marcaId = this.value;

        if (!marcaId) {
            selectModelo.innerHTML = '<option value="">Primero seleccione una marca...</option>';
            selectModelo.disabled = true;
            return;
        }

        selectModelo.innerHTML = '<option value="">Cargando modelos...</option>';
        selectModelo.disabled = true;

        fetch(`/api/modelos/por-marca/${marcaId}`)
            .then(response => response.json())
            .then(data => {
                selectModelo.innerHTML = '<option value="">Seleccione un modelo...</option>';
                if (data.length === 0) {
                    selectModelo.innerHTML = '<option value="">No hay modelos para esta marca</option>';
                } else {
                    data.forEach(modelo => {
                        selectModelo.innerHTML += `<option value="${modelo.id}">${escapeHtml(modelo.nombre)}</option>`;
                    });
                    selectModelo.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error cargando modelos:', error);
                selectModelo.innerHTML = '<option value="">Error al cargar modelos</option>';
                mostrarNotificacion('error', 'Error', 'Error al cargar los modelos');
            });
    });
}

function configurarEventos() {
    const btnAgregar = document.getElementById('btnAgregarActivo');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function() {
            limpiarFormulario();
            document.getElementById('modalTitle').innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Nuevo Activo
            `;
            abrirModalActivo();
        });
    }

    const btnGuardar = document.getElementById('btnGuardarActivo');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', guardarActivo);
    }

    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmarEliminar) {
        btnConfirmarEliminar.addEventListener('click', confirmarEliminar);
    }

    const btnFiltrar = document.getElementById('btnFiltrar');
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function() {
            filters.search = document.getElementById('filtroInventario').value;
            filters.id_categoria = document.getElementById('filtroCategoria').value;
            filters.id_estatus = document.getElementById('filtroEstatus').value;
            currentPage = 1;
            cargarActivos();
        });
    }

    const filtroInventario = document.getElementById('filtroInventario');
    if (filtroInventario) {
        let debounceTimer;
        filtroInventario.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filters.search = this.value;
                currentPage = 1;
                cargarActivos();
            }, 500);
        });
    }
}

// ========== MODALES ==========
function abrirModalActivo() {
    const modal = document.getElementById('activoModal');
    if (modal) modal.style.display = 'flex';
}

function cerrarModalActivo() {
    const modal = document.getElementById('activoModal');
    if (modal) modal.style.display = 'none';
    limpiarFormulario();
}

function abrirModalVerActivo() {
    const modal = document.getElementById('verActivoModal');
    if (modal) modal.style.display = 'flex';
}

function cerrarModalVerActivo() {
    const modal = document.getElementById('verActivoModal');
    if (modal) modal.style.display = 'none';
}

function abrirModalEspecificaciones() {
    const modal = document.getElementById('especificacionesModal');
    if (modal) modal.style.display = 'flex';
}

function cerrarModalEspecificaciones() {
    const modal = document.getElementById('especificacionesModal');
    if (modal) modal.style.display = 'none';
}

function abrirModalEliminar() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.style.display = 'flex';
}

function cerrarModalEliminar() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.style.display = 'none';
    deleteId = null;
}

// ========== CRUD FUNCTIONS ==========
function cargarActivos() {
    const params = new URLSearchParams({
        page: currentPage,
        search: filters.search,
        id_categoria: filters.id_categoria,
        id_estatus: filters.id_estatus
    });

    fetch(`{{ route('inventario.data') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            renderTabla(data.data);
            renderPagination(data);
            actualizarTotal(data.total);
            document.getElementById('resultadosCount').innerHTML = data.total;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tablaInventarioBody').innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-danger">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 12px; display: block;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        Error al cargar los datos
                                            Error al cargar los datos
                    </td>
                </tr>
            `;
            mostrarNotificacion('error', 'Error', 'Error al cargar los datos');
        });
}

function actualizarTotal(total) {
    const totalSpan = document.getElementById('totalActivosCount');
    if (totalSpan) totalSpan.innerHTML = `Total: ${total}`;
}

function renderVidaUtil(fechaAdquisicion, vidaUtilAnos, fechaFinGarantia) {
    if (!fechaAdquisicion || !vidaUtilAnos) return '<span class="badge bg-secondary">No definida</span>';

    const adquisicion = new Date(fechaAdquisicion);
    const hoy = new Date();
    const fechaFin = new Date(adquisicion);
    fechaFin.setFullYear(adquisicion.getFullYear() + parseInt(vidaUtilAnos));

    const diffTime = fechaFin - hoy;
    const diffDias = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    const diffMeses = Math.floor(diffDias / 30);

    let badgeClass = 'success';
    let mensaje = '';

    if (diffDias < 0) {
        badgeClass = 'danger';
        mensaje = 'Vida útil vencida';
    } else if (diffMeses <= 6) {
        badgeClass = 'warning';
        mensaje = `Próximo a vencer (${diffMeses} meses)`;
    } else if (diffMeses <= 12) {
        badgeClass = 'info';
        mensaje = `${diffMeses} meses restantes`;
    } else {
        const añosRest = (diffMeses / 12).toFixed(1);
        mensaje = `${añosRest} años restantes`;
    }

    if (fechaFinGarantia) {
        const garantia = new Date(fechaFinGarantia);
        if (garantia < hoy) {
            mensaje += ' | Garantía vencida';
        } else if (garantia < fechaFin) {
            mensaje += ' | En garantía';
        }
    }

    return `<span class="badge bg-${badgeClass}">${mensaje}</span>`;
}

function renderTabla(activos) {
    const tbody = document.getElementById('tablaInventarioBody');
    if (!activos || !activos.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M8 8h8M8 12h6M8 16h4"/>
                    </svg>
                    <p class="text-muted">No hay activos registrados</p>
                    <small class="text-muted">Haz clic en "Nuevo Equipo" para agregar</small>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = activos.map(activo => `
        <tr>
            <td class="px-4 py-3"><code class="badge bg-secondary">${escapeHtml(activo.serial)}</code></td>
            <td class="px-4 py-3"><span class="badge-fecha" style="background: #e3f2fd; color: #0d47a1;">${escapeHtml(activo.marca_modelo || activo.marca?.nombre + ' ' + activo.modelo?.nombre || '-')}</span></td>
            <td class="px-4 py-3"><span class="badge-fecha" style="background: #f3e5f5; color: #4a148c;">${activo.categoria ? escapeHtml(activo.categoria.nombre) : '-'}</span></td>
            <td class="px-4 py-3">
                <span class="badge bg-${activo.estatus ? activo.estatus.color_badge : 'secondary'}">
                    ${activo.estatus ? activo.estatus.descripcion : '-'}
                </span>
            </td>
            <td class="px-4 py-3">${escapeHtml(activo.ubicacion || '-')}</td>
            <td class="px-4 py-3">${renderVidaUtil(activo.fecha_adquisicion, activo.vida_util_anos, activo.fecha_fin_garantia)}</td>
            <td class="px-4 py-3 text-center">
                <div class="d-flex gap-2 justify-content-center">
                    <button onclick="verActivo(${activo.id})" class="btn btn-sm btn-accion-ver" data-tooltip="Ver detalles">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        Ver
                    </button>
                    <button onclick="verEspecificaciones(${activo.id})" class="btn btn-sm btn-accion-ver" style="background: rgba(40,167,69,0.1); color: #1b5e20; border-color: rgba(40,167,69,0.3);" data-tooltip="Ver especificaciones técnicas">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 2v2M12 20v2M22 12h-2M4 12H2"/>
                        </svg>
                        Especs
                    </button>
                    <button onclick="editarActivo(${activo.id})" class="btn btn-sm btn-accion-editar" data-tooltip="Editar activo">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                            <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                        </svg>
                        Editar
                    </button>
                    <button onclick="abrirModalEliminar(${activo.id}, '${escapeHtml(activo.serial)}', '${escapeHtml(activo.marca_modelo || activo.marca?.nombre + ' ' + activo.modelo?.nombre)}')" class="btn btn-sm btn-accion-eliminar" data-tooltip="Eliminar activo">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/>
                        </svg>
                        Eliminar
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderPagination(data) {
    const container = document.getElementById('paginationLinks');
    if (!container) return;

    if (data.last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '<nav><ul class="pagination justify-content-center">';

    if (data.prev_page_url) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${data.current_page - 1}); return false;">« Anterior</a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link">« Anterior</span></li>`;
    }

    let startPage = Math.max(1, data.current_page - 2);
    let endPage = Math.min(data.last_page, data.current_page + 2);

    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(1); return false;">1</a></li>`;
        if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }

    for (let i = startPage; i <= endPage; i++) {
        if (i === data.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a></li>`;
        }
    }

    if (endPage < data.last_page) {
        if (endPage < data.last_page - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${data.last_page}); return false;">${data.last_page}</a></li>`;
    }

    if (data.next_page_url) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${data.current_page + 1}); return false;">Siguiente »</a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link">Siguiente »</span></li>`;
    }

    html += '</ul></nav>';
    container.innerHTML = html;
}

function cambiarPagina(page) {
    currentPage = page;
    cargarActivos();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ========== VER ACTIVO ==========
function verActivo(id) {
    fetch(`{{ url('inventario') }}/${id}`)
        .then(response => response.json())
        .then(activo => {
            const modalBody = document.getElementById('detalleActivoBody');
            modalBody.innerHTML = `
                <div class="mb-3">
                    <button onclick="cerrarModalVerActivo(); verEspecificaciones(${activo.id});" class="btn btn-sm btn-success">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 2v2M12 20v2M22 12h-2M4 12H2"/>
                        </svg>
                        Ver Especificaciones Técnicas
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Serial</label><div class="fw-semibold"><code>${escapeHtml(activo.serial)}</code></div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Marca/Modelo</label><div class="fw-semibold">${escapeHtml(activo.marca_modelo || activo.marca?.nombre + ' ' + activo.modelo?.nombre || '-')}</div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Categoría</label><div class="fw-semibold">${activo.categoria ? activo.categoria.nombre : '-'}</div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Estatus</label><div><span class="badge bg-${activo.estatus ? activo.estatus.color_badge : 'secondary'}">${activo.estatus ? activo.estatus.descripcion : '-'}</span></div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Ubicación</label><div class="fw-semibold">${escapeHtml(activo.ubicacion || '-')}</div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Fecha Adquisición</label><div class="fw-semibold">${activo.fecha_adquisicion || '-'}</div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Vida Útil</label><div class="fw-semibold">${activo.vida_util_anos || '-'} años</div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Fecha Fin Garantía</label><div class="fw-semibold">${activo.fecha_fin_garantia || '-'}</div></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Estado Vida Útil</label><div>${renderVidaUtil(activo.fecha_adquisicion, activo.vida_util_anos, activo.fecha_fin_garantia)}</div></div></div>
                    <div class="col-12"><div class="mb-3"><label class="text-muted small">Observaciones</label><div class="p-2 bg-light rounded">${escapeHtml(activo.observaciones || '-')}</div></div></div>
                    <div class="col-12"><div class="mb-3"><label class="text-muted small">Registrado</label><div class="fw-semibold">${activo.created_at || '-'}</div></div></div>
                </div>
            `;
            abrirModalVerActivo();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error', 'Error al cargar el detalle');
        });
}

// ========== VER ESPECIFICACIONES ==========
function verEspecificaciones(id) {
    currentActivoIdForEspecs = id;

    fetch(`{{ url('inventario') }}/${id}`)
        .then(response => response.json())
        .then(activo => {
            const especBody = document.getElementById('especificacionesBody');
            const btnEditar = document.getElementById('btnEditarEspecificaciones');

            let especificaciones = null;
            if (activo.especificaciones_tecnicas) {
                try {
                    especificaciones = typeof activo.especificaciones_tecnicas === 'string' ?
                        JSON.parse(activo.especificaciones_tecnicas) : activo.especificaciones_tecnicas;
                } catch(e) {
                    console.error('Error parsing especificaciones:', e);
                }
            }

            const nombreCompleto = activo.marca_modelo || (activo.marca?.nombre + ' ' + activo.modelo?.nombre) || 'Equipo';

            if (especificaciones && Object.keys(especificaciones).length > 0) {
                let html = `
                    <div class="alert alert-info mb-3">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; margin-right: 8px;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <strong>${escapeHtml(nombreCompleto)}</strong> - ${activo.categoria ? activo.categoria.nombre : 'Equipo'}
                        <br>
                        <small class="text-muted">Serial: ${escapeHtml(activo.serial)}</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr><th width="40%">Característica</th><th width="60%">Especificación</th></tr>
                            </thead>
                            <tbody>
                `;

                const nombresLegibles = {
                    'procesador': 'Procesador',
                    'ram': 'RAM',
                    'disco_duro': 'Disco Duro',
                    'sistema_operativo': 'Sistema Operativo',
                    'bateria': 'Duración de Batería',
                    'almacenamiento': 'Almacenamiento',
                    'pantalla': 'Tamaño de Pantalla',
                    'cpu_cores': 'CPU Cores',
                    'ram_total': 'RAM Total',
                    'tipo_impresora': 'Tipo de Impresora',
                    'velocidad': 'Velocidad',
                    'modelo': 'Modelo',
                    'imei': 'IMEI',
                    'procesador_grafico': 'Procesador Gráfico',
                    'puertos': 'Puertos',
                    'conectividad': 'Conectividad',
                    'incluye': 'Incluye'
                };

                let tieneEspecificaciones = false;
                for (const [key, value] of Object.entries(especificaciones)) {
                    if (value && value.toString().trim() !== '') {
                        tieneEspecificaciones = true;
                        const nombreLegible = nombresLegibles[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        html += `<tr><td class="fw-bold bg-light">${escapeHtml(nombreLegible)}</td><td>${escapeHtml(value.toString())}</td></tr>`;
                    }
                }

                if (!tieneEspecificaciones) {
                    html = `
                        <div class="text-center py-5">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" style="margin: 0 auto 16px;">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M12 2v2M12 20v2M22 12h-2M4 12H2"/>
                            </svg>
                            <h5 class="text-muted">No hay especificaciones técnicas registradas</h5>
                            <p class="text-muted">Este equipo no tiene especificaciones técnicas asociadas.</p>
                        </div>
                    `;
                    btnEditar.style.display = 'block';
                } else {
                    html += `</tbody></table></div><div class="mt-3 text-muted small"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; margin-right: 6px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Última actualización: ${activo.updated_at || activo.created_at || 'No disponible'}</div>`;
                    btnEditar.style.display = 'block';
                }

                especBody.innerHTML = html;
            } else {
                especBody.innerHTML = `
                    <div class="text-center py-5">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" style="margin: 0 auto 16px;">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 2v2M12 20v2M22 12h-2M4 12H2"/>
                        </svg>
                        <h5 class="text-muted">No hay especificaciones técnicas registradas</h5>
                        <p class="text-muted">Este equipo no tiene especificaciones técnicas asociadas.</p>
                        <div class="alert alert-info mt-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; margin-right: 8px;">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            Puedes agregar especificaciones técnicas editando el activo y completando los campos específicos según la categoría.
                        </div>
                    </div>
                `;
                btnEditar.style.display = 'block';
            }

            abrirModalEspecificaciones();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error', 'Error al cargar las especificaciones');
        });
}

// ========== EDITAR ACTIVO ==========
function editarActivo(id) {
    fetch(`{{ url('inventario') }}/${id}`)
        .then(response => response.json())
        .then(activo => {
            console.log('Datos recibidos:', activo); // Para depurar

            document.getElementById('modalTitle').innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                    <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                </svg>
                Editar Activo
            `;
            document.getElementById('activo_id').value = activo.id;
            document.getElementById('serial').value = activo.serial;
            document.getElementById('id_categoria').value = activo.id_categoria;
            document.getElementById('id_estatus').value = activo.id_estatus;
            document.getElementById('ubicacion').value = activo.ubicacion || '';

            // ==== CORRECCIÓN DE FECHAS ====
            // Formatear fecha_adquisicion si existe
            if (activo.fecha_adquisicion) {
                const fechaAdq = new Date(activo.fecha_adquisicion);
                if (!isNaN(fechaAdq.getTime())) {
                    document.getElementById('fecha_adquisicion').value = fechaAdq.toISOString().split('T')[0];
                } else {
                    document.getElementById('fecha_adquisicion').value = activo.fecha_adquisicion;
                }
            } else {
                document.getElementById('fecha_adquisicion').value = '';
            }

            // Formatear fecha_fin_garantia si existe
            if (activo.fecha_fin_garantia) {
                const fechaGar = new Date(activo.fecha_fin_garantia);
                if (!isNaN(fechaGar.getTime())) {
                    document.getElementById('fecha_fin_garantia').value = fechaGar.toISOString().split('T')[0];
                } else {
                    document.getElementById('fecha_fin_garantia').value = activo.fecha_fin_garantia;
                }
            } else {
                document.getElementById('fecha_fin_garantia').value = '';
            }

            document.getElementById('vida_util_anos').value = activo.vida_util_anos || '';
            document.getElementById('observaciones').value = activo.observaciones || '';

            // Disparar evento change para cargar marcas y modelos
            const selectCategoria = document.getElementById('id_categoria');
            if (selectCategoria) {
                selectCategoria.dispatchEvent(new Event('change'));
            }

            // Cargar marca y modelo después de que se carguen las opciones
            setTimeout(() => {
                if (activo.id_marca) {
                    const selectMarca = document.getElementById('id_marca');
                    selectMarca.value = activo.id_marca;
                    selectMarca.dispatchEvent(new Event('change'));

                    setTimeout(() => {
                        if (activo.id_modelo) {
                            document.getElementById('id_modelo').value = activo.id_modelo;
                        }
                    }, 500);
                }
            }, 500);

            // Disparar evento change para cargar campos específicos de categoría
            if (selectCategoria) {
                selectCategoria.dispatchEvent(new Event('change'));
            }

            // Cargar campos específicos si existen
            setTimeout(() => {
                if (activo.especificaciones_tecnicas) {
                    const especs = typeof activo.especificaciones_tecnicas === 'string' ?
                        JSON.parse(activo.especificaciones_tecnicas) : activo.especificaciones_tecnicas;
                    for (const [key, value] of Object.entries(especs)) {
                        const input = document.getElementById(key);
                        if (input) input.value = value;
                    }
                }
            }, 200);

            // Mostrar preview de vida útil si hay fechas
            const vidaPreview = document.getElementById('vidaUtilPreview');
            const vidaMensaje = document.getElementById('vidaUtilMensaje');
            if (activo.fecha_adquisicion && activo.vida_util_anos && vidaPreview) {
                const fechaFin = new Date(activo.fecha_adquisicion);
                fechaFin.setFullYear(fechaFin.getFullYear() + parseInt(activo.vida_util_anos));
                vidaMensaje.innerHTML = `Fecha estimada de fin de vida útil: ${fechaFin.toLocaleDateString()}`;
                vidaPreview.style.display = 'block';
            }

            abrirModalActivo();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error', 'Error al cargar el activo para editar');
        });
}
// ========== ELIMINAR ACTIVO ==========
function abrirModalEliminar(id, serial, marcaModelo) {
    deleteId = id;
    const deleteMessage = document.getElementById('deleteActivoInfo');
    if (deleteMessage) {
        deleteMessage.innerHTML = `<strong>${escapeHtml(serial)}</strong> - ${escapeHtml(marcaModelo)}`;
    }
    abrirModalEliminar();
}

function confirmarEliminar() {
    if (!deleteId) return;

    const btn = document.getElementById('btnConfirmarEliminar');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Eliminando...';

    fetch(`{{ url('inventario') }}/${deleteId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cerrarModalEliminar();
            cargarActivos();
            mostrarNotificacion('success', 'Éxito', data.message || 'Activo eliminado correctamente');
            deleteId = null;
        } else {
            mostrarNotificacion('error', 'Error', data.message || 'Error al eliminar');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error', 'Error al eliminar el activo');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// ========== GUARDAR ACTIVO ==========
function guardarActivo() {
    const id = document.getElementById('activo_id').value;
    const url = id ? `{{ url('inventario') }}/${id}` : `{{ route('inventario.store') }}`;
    const method = id ? 'PUT' : 'POST';

    // Recolectar campos específicos dinámicos
    const especificacionesTecnicas = {};
    const camposDinamicos = document.querySelectorAll('#camposEspecificosContainer input, #camposEspecificosContainer select, #camposEspecificosContainer textarea');
    camposDinamicos.forEach(campo => {
        if (campo.value && campo.value.trim() !== '') {
            especificacionesTecnicas[campo.id] = campo.value;
        }
    });

    const formData = {
        serial: document.getElementById('serial').value,
        id_categoria: document.getElementById('id_categoria').value,
        id_marca: document.getElementById('id_marca').value,
        id_modelo: document.getElementById('id_modelo').value,
        id_estatus: document.getElementById('id_estatus').value,
        ubicacion: document.getElementById('ubicacion').value,
        fecha_adquisicion: document.getElementById('fecha_adquisicion').value,
        vida_util_anos: document.getElementById('vida_util_anos').value,
        fecha_fin_garantia: document.getElementById('fecha_fin_garantia').value,
        observaciones: document.getElementById('observaciones').value,
        especificaciones_tecnicas: especificacionesTecnicas
    };

    // Validaciones
    if (!formData.serial) {
        mostrarNotificacion('error', 'Error', 'El campo Serial es requerido');
        document.getElementById('serial').focus();
        return;
    }
    if (!formData.id_categoria) {
        mostrarNotificacion('error', 'Error', 'Debe seleccionar una categoría');
        document.getElementById('id_categoria').focus();
        return;
    }
    if (!formData.id_marca) {
        mostrarNotificacion('error', 'Error', 'Debe seleccionar una marca');
        document.getElementById('id_marca').focus();
        return;
    }
    if (!formData.id_modelo) {
        mostrarNotificacion('error', 'Error', 'Debe seleccionar un modelo');
        document.getElementById('id_modelo').focus();
        return;
    }
    if (!formData.id_estatus) {
        mostrarNotificacion('error', 'Error', 'El campo Estatus es requerido');
        document.getElementById('id_estatus').focus();
        return;
    }
    if (!formData.fecha_adquisicion) {
        mostrarNotificacion('error', 'Error', 'El campo Fecha de Adquisición es requerido');
        document.getElementById('fecha_adquisicion').focus();
        return;
    }
    if (!formData.vida_util_anos) {
        mostrarNotificacion('error', 'Error', 'El campo Vida Útil es requerido');
        document.getElementById('vida_util_anos').focus();
        return;
    }

    const btn = document.getElementById('btnGuardarActivo');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cerrarModalActivo();
            cargarActivos();
            mostrarNotificacion('success', 'Éxito', data.message || 'Activo guardado correctamente');
            limpiarFormulario();
        } else {
            mostrarNotificacion('error', 'Error', data.message || 'Error al guardar el activo');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error', 'Error al guardar el activo');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function limpiarFormulario() {
    document.getElementById('activo_id').value = '';
    document.getElementById('serial').value = '';
    document.getElementById('id_categoria').value = '';
    document.getElementById('id_marca').innerHTML = '<option value="">Primero seleccione una categoría...</option>';
    document.getElementById('id_marca').disabled = true;
    document.getElementById('id_modelo').innerHTML = '<option value="">Primero seleccione una marca...</option>';
    document.getElementById('id_modelo').disabled = true;
    document.getElementById('id_estatus').value = '';
    document.getElementById('ubicacion').value = '';
    document.getElementById('fecha_adquisicion').value = '';
    document.getElementById('vida_util_anos').value = '';
    document.getElementById('fecha_fin_garantia').value = '';
    document.getElementById('observaciones').value = '';

    // Limpiar campos específicos
    const container = document.getElementById('camposEspecificosContainer');
    if (container) container.innerHTML = '';

    // Ocultar preview
    const previewDiv = document.getElementById('vidaUtilPreview');
    if (previewDiv) previewDiv.style.display = 'none';
}

// ========== CAMPOS DINÁMICOS POR CATEGORÍA ==========
function configurarCamposPorCategoria() {
    const selectCategoria = document.getElementById('id_categoria');
    if (!selectCategoria) return;

    const vidaUtilPorDefecto = {
        1: 5,  // Computadoras
        2: 4,  // Laptops
        3: 3,  // Tablets
        4: 6,  // Servidores
        5: 10, // Mobiliario
        6: 3,  // Teléfonos
        7: 5   // Impresoras
    };

    selectCategoria.addEventListener('change', function() {
        const categoriaId = parseInt(this.value);
        const campoVidaUtil = document.getElementById('vida_util_anos');
        const previewDiv = document.getElementById('vidaUtilPreview');
        const mensajeSpan = document.getElementById('vidaUtilMensaje');

        if (categoriaId && vidaUtilPorDefecto[categoriaId]) {
            const años = vidaUtilPorDefecto[categoriaId];
            campoVidaUtil.value = años;

            const fechaAdquisicion = document.getElementById('fecha_adquisicion').value;
            if (fechaAdquisicion) {
                const fechaFin = new Date(fechaAdquisicion);
                fechaFin.setFullYear(fechaFin.getFullYear() + años);
                mensajeSpan.innerHTML = `Este equipo tiene una vida útil estimada de ${años} años. Fecha estimada de fin de vida: ${fechaFin.toLocaleDateString()}`;
                previewDiv.style.display = 'block';
            } else {
                mensajeSpan.innerHTML = `Este equipo tiene una vida útil estimada de ${años} años. Complete la fecha de adquisición para ver el cálculo.`;
                previewDiv.style.display = 'block';
            }
        }

        mostrarCamposEspecificosPorCategoria(categoriaId);
    });

    const fechaAdquisicionInput = document.getElementById('fecha_adquisicion');
    if (fechaAdquisicionInput) {
        fechaAdquisicionInput.addEventListener('change', function() {
            const categoriaId = parseInt(selectCategoria.value);
            const años = document.getElementById('vida_util_anos').value;
            if (categoriaId && años && this.value) {
                const fechaFin = new Date(this.value);
                fechaFin.setFullYear(fechaFin.getFullYear() + parseInt(años));
                const mensajeSpan = document.getElementById('vidaUtilMensaje');
                if (mensajeSpan) {
                    mensajeSpan.innerHTML = `Fecha estimada de fin de vida útil: ${fechaFin.toLocaleDateString()}`;
                    document.getElementById('vidaUtilPreview').style.display = 'block';
                }
            }
        });
    }
}

function mostrarCamposEspecificosPorCategoria(categoriaId) {
    const container = document.getElementById('camposEspecificosContainer');
    if (!container) return;

    container.innerHTML = '';

    const camposPorCategoria = {
        1: [ // Computadoras
            { tipo: 'text', id: 'procesador', label: 'Procesador', col: 4, placeholder: 'Ej: Intel Core i7-12700' },
            { tipo: 'text', id: 'ram', label: 'RAM (GB)', col: 4, placeholder: 'Ej: 16' },
            { tipo: 'text', id: 'disco_duro', label: 'Disco Duro', col: 4, placeholder: 'Ej: SSD 512GB' },
            { tipo: 'text', id: 'sistema_operativo', label: 'Sistema Operativo', col: 6, placeholder: 'Ej: Windows 11 Pro' },
            { tipo: 'text', id: 'procesador_grafico', label: 'Procesador Gráfico', col: 6, placeholder: 'Ej: NVIDIA GTX 1660' }
        ],
        2: [ // Laptops
            { tipo: 'text', id: 'procesador', label: 'Procesador', col: 4, placeholder: 'Ej: Intel Core i5' },
            { tipo: 'text', id: 'ram', label: 'RAM (GB)', col: 4, placeholder: 'Ej: 8' },
            { tipo: 'text', id: 'bateria', label: 'Duración batería (horas)', col: 4, placeholder: 'Ej: 6' },
            { tipo: 'text', id: 'pantalla', label: 'Tamaño Pantalla (pulgadas)', col: 6, placeholder: 'Ej: 15.6' }
        ],
        3: [ // Tablets
            { tipo: 'text', id: 'almacenamiento', label: 'Almacenamiento (GB)', col: 6, placeholder: 'Ej: 64' },
            { tipo: 'text', id: 'pantalla', label: 'Tamaño Pantalla (pulgadas)', col: 6, placeholder: 'Ej: 10.1' },
            { tipo: 'text', id: 'conectividad', label: 'Conectividad', col: 12, placeholder: 'Ej: WiFi, 4G' }
        ],
        4: [ // Servidores
            { tipo: 'text', id: 'cpu_cores', label: 'CPU Cores', col: 3, placeholder: 'Ej: 8' },
            { tipo: 'text', id: 'ram_total', label: 'RAM Total (GB)', col: 3, placeholder: 'Ej: 32' },
            { tipo: 'text', id: 'almacenamiento', label: 'Almacenamiento (TB)', col: 3, placeholder: 'Ej: 2' },
            { tipo: 'text', id: 'sistema_operativo', label: 'Sistema Operativo', col: 3, placeholder: 'Ej: Ubuntu Server' }
        ],
        6: [ // Teléfonos
            { tipo: 'text', id: 'modelo', label: 'Modelo', col: 4, placeholder: 'Ej: iPhone 13' },
            { tipo: 'text', id: 'imei', label: 'IMEI', col: 4, placeholder: 'Ej: 123456789012345' },
            { tipo: 'text', id: 'almacenamiento', label: 'Almacenamiento (GB)', col: 4, placeholder: 'Ej: 128' }
        ],
        7: [ // Impresoras
            { tipo: 'text', id: 'tipo_impresora', label: 'Tipo', col: 6, placeholder: 'Laser/Tinta' },
            { tipo: 'text', id: 'velocidad', label: 'Velocidad (ppm)', col: 6, placeholder: 'Ej: 20' },
            { tipo: 'text', id: 'conectividad', label: 'Conectividad', col: 12, placeholder: 'Ej: USB, WiFi, Ethernet' }
        ]
    };

    const campos = camposPorCategoria[categoriaId] || [];

    if (campos.length > 0) {
        const tituloDiv = document.createElement('div');
        tituloDiv.className = 'col-12 mb-3';
        tituloDiv.innerHTML = '<hr><h6 class="fw-bold text-primary">Especificaciones Técnicas</h6><p class="text-muted small">Complete los detalles técnicos del equipo</p>';
        container.appendChild(tituloDiv);
    }

    campos.forEach(campo => {
        const colDiv = document.createElement('div');
        colDiv.className = `col-md-${campo.col} mb-3`;
        colDiv.innerHTML = `
            <label class="form-label fw-semibold">${campo.label}</label>
            <input type="${campo.tipo}" id="${campo.id}" name="${campo.id}"
                   class="form-control" style="border-radius: 10px; background: white;" placeholder="${campo.placeholder || ''}">
        `;
        container.appendChild(colDiv);
    });
}

// Inicializar tooltips
document.querySelectorAll('[data-tooltip]').forEach(el => {
    el.setAttribute('title', el.getAttribute('data-tooltip'));
});
</script>
@endsection
