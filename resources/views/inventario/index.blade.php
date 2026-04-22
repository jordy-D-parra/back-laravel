@extends('layouts.dashboard')

@section('title', 'Gestión de Inventario')

@section('content')
<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-3">📦 Gestión de Inventario</h2>
                        <p class="text-muted mb-0">Administra todos los activos tecnológicos de la Gobernación</p>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 2rem;">📦</div>
                        <small class="text-muted" id="totalActivosCount">Total: 0</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">🔍 Buscar</label>
                        <input type="text" id="filtroInventario" class="form-control" 
                               placeholder="Serial, marca/modelo o ubicación...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">📂 Tipo de Activo</label>
                        <select id="filtroTipoActivo" class="form-select">
                            <option value="">Todos</option>
                            @foreach($tiposActivo as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">⚡ Estatus</label>
                        <select id="filtroEstatus" class="form-select">
                            <option value="">Todos</option>
                            @foreach($estatusList as $estatus)
                                <option value="{{ $estatus->id }}">{{ $estatus->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" id="btnFiltrar">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de activos -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">📋 Lista de Activos</h5>
                    <button class="btn btn-primary" id="btnAgregarActivo">
                        <i class="fas fa-plus"></i> Nuevo activo
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Serial</th>
                                <th>Marca/Modelo</th>
                                <th>Tipo Equipo</th>
                                <th>Categoría</th>
                                <th>Cantidad</th>
                                <th>Estatus</th>
                                <th>Ubicación</th>
                                <th>Vida Útil</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaInventarioBody">
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Cargando activos...</p>
                                </td>
                        </tbody>
                    </table>
                </div>
                <div id="paginationLinks" class="mt-3 d-flex justify-content-center"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear/editar activo -->
<div class="modal fade" id="activoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Nuevo Activo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="activo_id" name="activo_id">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Serial *</label>
                        <input type="text" id="serial" name="serial" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tipo de Equipo *</label>
                        <select id="tipo_equipo" name="tipo_equipo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="principal">Principal</option>
                            <option value="secundario">Secundario</option>
                            <option value="componente">Componente</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Marca / Modelo *</label>
                        <input type="text" id="marca_modelo" name="marca_modelo" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Categoría *</label>
                        <select id="id_tipo_activo" name="id_tipo_activo" class="form-select" required>
                            <option value="">Seleccione una categoría...</option>
                            @foreach($tiposActivo as $tipo)
                                <option value="{{ $tipo->id }}" data-vida-util="{{ $tipo->vida_util_por_defecto ?? 5 }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Estatus *</label>
                        <select id="id_estatus" name="id_estatus" class="form-select" required>
                            <option value="">Seleccione un estatus...</option>
                            @foreach($estatusList as $estatus)
                                <option value="{{ $estatus->id }}">{{ $estatus->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Cantidad *</label>
                        <input type="number" id="cantidad" name="cantidad" class="form-control" value="1" min="0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" class="form-control" placeholder="Ej: Oficina 301">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">📅 Fecha de Adquisición *</label>
                        <input type="date" id="fecha_adquisicion" name="fecha_adquisicion" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">⏱️ Vida Útil (años) *</label>
                        <input type="number" id="vida_util_anos" name="vida_util_anos" class="form-control" 
                               placeholder="Ej: 5" min="1" max="20" step="1" required>
                        <small class="text-muted">Según categoría del equipo</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">🔧 Fecha Fin de Garantía</label>
                        <input type="date" id="fecha_fin_garantia" name="fecha_fin_garantia" class="form-control">
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-info" id="vidaUtilPreview" style="display: none;">
                            <i class="fas fa-info-circle"></i> 
                            <span id="vidaUtilMensaje"></span>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                
                <!-- Contenedor para campos específicos por categoría -->
                <div id="camposEspecificosContainer" class="row mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarActivo">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalle -->
<div class="modal fade" id="verActivoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalle del Activo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleActivoBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver especificaciones técnicas -->
<div class="modal fade" id="especificacionesModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-microchip"></i> Especificaciones Técnicas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="especificacionesBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnEditarEspecificaciones" style="display: none;">
                    <i class="fas fa-edit"></i> Editar Especificaciones
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-3">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3 d-block"></i>
                    <p class="fw-bold mb-2">¿Estás seguro de eliminar este activo?</p>
                    <p class="text-muted" id="deleteMessage">Esta acción no se puede deshacer.</p>
                    <p class="text-muted small" id="deleteActivoInfo"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Sistema de Notificaciones Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="notificationToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">
                Mensaje de notificación
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s;
        border: none;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .btn-group .btn {
        transition: all 0.3s ease;
        margin: 0 3px;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.85rem;
    }
    
    .btn-group .btn i {
        font-size: 0.9rem;
        margin-right: 4px;
    }
    
    .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .badge {
        font-size: 0.85rem;
        padding: 0.5rem 0.85rem;
        border-radius: 20px;
    }
    
    .table th, .table td {
        vertical-align: middle;
        padding: 12px 10px;
    }
    
    .pagination .page-link {
        color: #1e4a76;
        transition: all 0.3s ease;
        border-radius: 8px;
        margin: 0 3px;
        padding: 8px 14px;
    }
    
    .pagination .page-link:hover {
        background-color: #1e4a76;
        color: white;
        transform: translateY(-2px);
    }
    
    .pagination .active .page-link {
        background-color: #1e4a76;
        border-color: #1e4a76;
    }
    
    .toast {
        opacity: 0.95;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 280px;
    }
    
    #btnAgregarActivo {
        padding: 8px 20px;
        font-size: 0.9rem;
        border-radius: 10px;
    }
    
    #btnFiltrar {
        padding: 8px 16px;
    }
    
    .modal-footer .btn {
        padding: 8px 20px;
        border-radius: 8px;
    }
    
    #deleteModal .modal-content {
        border-radius: 16px;
    }
    
    .especificacion-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
    }
    
    .especificacion-label {
        font-weight: bold;
        color: #1e4a76;
    }
</style>

<script>
// ========== SISTEMA DE SONIDOS ==========
class SoundManager {
    constructor() {
        this.enabled = true;
        this.volume = 0.5;
        this.sounds = {};
        this.initialized = false;
        this.init();
    }
    
    init() {
        try {
            const soundFiles = {
                success: '/sounds/success.mp3',
                error: '/sounds/error.mp3',
                notification: '/sounds/notification.mp3',
                warning: '/sounds/warning.mp3',
                click: '/sounds/click.mp3'
            };
            
            for (const [key, url] of Object.entries(soundFiles)) {
                const audio = new Audio(url);
                audio.preload = 'auto';
                audio.volume = this.volume;
                audio.load();
                this.sounds[key] = audio;
                
                audio.addEventListener('error', (e) => {
                    console.warn(`No se pudo cargar el sonido ${key}`);
                    this.createFallbackSound(key);
                });
            }
            
            const savedEnabled = localStorage.getItem('soundEnabled');
            if (savedEnabled !== null) {
                this.enabled = savedEnabled === 'true';
            }
            
            const savedVolume = localStorage.getItem('soundVolume');
            if (savedVolume !== null) {
                this.volume = parseFloat(savedVolume);
                this.setVolume(this.volume);
            }
            
            this.initialized = true;
            this.addSoundControl();
        } catch (error) {
            console.error('Error inicializando sonidos:', error);
        }
    }
    
    createFallbackSound(type) {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            let frequency = 800;
            let duration = 0.3;
            
            switch(type) {
                case 'success':
                    frequency = 880;
                    duration = 0.2;
                    break;
                case 'error':
                    frequency = 440;
                    duration = 0.5;
                    break;
                case 'warning':
                    frequency = 660;
                    duration = 0.4;
                    break;
                case 'click':
                    frequency = 528;
                    duration = 0.15;
                    break;
                default:
                    frequency = 528;
                    duration = 0.3;
            }
            
            this.sounds[type] = {
                play: () => {
                    if (!this.enabled) return;
                    const osc = audioContext.createOscillator();
                    const gain = audioContext.createGain();
                    osc.connect(gain);
                    gain.connect(audioContext.destination);
                    osc.frequency.value = frequency;
                    gain.gain.value = this.volume;
                    osc.start();
                    gain.gain.exponentialRampToValueAtTime(0.00001, audioContext.currentTime + duration);
                    osc.stop(audioContext.currentTime + duration);
                }
            };
        } catch (e) {
            console.error('No se pudo crear sonido de fallback:', e);
        }
    }
    
    play(type) {
        if (!this.enabled || !this.initialized) return;
        
        const sound = this.sounds[type];
        if (sound) {
            try {
                if (sound.cloneNode) {
                    const soundClone = sound.cloneNode();
                    soundClone.volume = this.volume;
                    soundClone.play().catch(error => console.log('Error:', error));
                    soundClone.onended = () => soundClone.remove();
                } else if (typeof sound.play === 'function') {
                    sound.play();
                }
            } catch (error) {
                console.error(`Error al reproducir ${type}:`, error);
            }
        }
    }
    
    setVolume(volume) {
        this.volume = Math.max(0, Math.min(1, volume));
        for (const sound of Object.values(this.sounds)) {
            if (sound.volume !== undefined) {
                sound.volume = this.volume;
            }
        }
        localStorage.setItem('soundVolume', this.volume);
    }
    
    toggle() {
        this.enabled = !this.enabled;
        localStorage.setItem('soundEnabled', this.enabled);
        if (this.enabled) this.play('notification');
        return this.enabled;
    }
    
    addSoundControl() {
        if (document.getElementById('soundControlBtn')) return;
        
        const controlHtml = `
            <div style="position: fixed; bottom: 20px; left: 20px; z-index: 9999;">
                <button id="soundControlBtn" 
                        class="btn btn-secondary rounded-circle shadow"
                        style="width: 50px; height: 50px; font-size: 24px;"
                        title="${this.enabled ? 'Desactivar sonidos' : 'Activar sonidos'}">
                    ${this.enabled ? '🔊' : '🔇'}
                </button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', controlHtml);
        
        const btn = document.getElementById('soundControlBtn');
        if (btn) {
            btn.addEventListener('click', () => {
                const isEnabled = this.toggle();
                btn.textContent = isEnabled ? '🔊' : '🔇';
                btn.title = isEnabled ? 'Desactivar sonidos' : 'Activar sonidos';
            });
        }
    }
}

// Inicializar sistema de sonidos
const soundManager = new SoundManager();

// ========== SISTEMA DE NOTIFICACIONES ==========
let toastInstance = null;

function showNotification(message, type = 'success') {
    soundManager.play(type);
    
    const toastEl = document.getElementById('notificationToast');
    const toastMessage = document.getElementById('toastMessage');
    
    if (!toastEl || !toastMessage) {
        if (type === 'error') alert('❌ Error: ' + message);
        return;
    }
    
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
    if (type === 'success') {
        toastEl.classList.add('bg-success');
    } else if (type === 'error') {
        toastEl.classList.add('bg-danger');
    } else if (type === 'warning') {
        toastEl.classList.add('bg-warning');
    } else {
        toastEl.classList.add('bg-info');
    }
    
    toastMessage.textContent = message;
    
    if (toastInstance) {
        toastInstance.hide();
        setTimeout(() => toastInstance.show(), 200);
    } else {
        toastInstance = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 3000
        });
        toastInstance.show();
    }
}

// ========== VARIABLES GLOBALES ==========
let currentPage = 1;
let deleteId = null;
let currentActivoIdForEspecs = null;
let filters = {
    search: '',
    id_tipo_activo: '',
    id_estatus: ''
};

let activoModal = null;
let verActivoModal = null;
let deleteModal = null;
let especificacionesModal = null;

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('activoModal');
    const verModalElement = document.getElementById('verActivoModal');
    const deleteModalElement = document.getElementById('deleteModal');
    const especModalElement = document.getElementById('especificacionesModal');
    
    if (modalElement) activoModal = new bootstrap.Modal(modalElement);
    if (verModalElement) verActivoModal = new bootstrap.Modal(verModalElement);
    if (deleteModalElement) deleteModal = new bootstrap.Modal(deleteModalElement);
    if (especModalElement) especificacionesModal = new bootstrap.Modal(especModalElement);
    
    configurarEventos();
    configurarCamposPorCategoria();
    cargarActivos();
    
    // Configurar botón editar especificaciones
    const btnEditarEspecs = document.getElementById('btnEditarEspecificaciones');
    if (btnEditarEspecs) {
        btnEditarEspecs.addEventListener('click', function() {
            if (currentActivoIdForEspecs) {
                if (especificacionesModal) especificacionesModal.hide();
                setTimeout(() => {
                    editarActivo(currentActivoIdForEspecs);
                }, 300);
            }
        });
    }
});

function configurarEventos() {
    const btnAgregar = document.getElementById('btnAgregarActivo');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function() {
            soundManager.play('click');
            limpiarFormulario();
            document.getElementById('modalTitle').innerHTML = 'Nuevo Activo';
            if (activoModal) activoModal.show();
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
            soundManager.play('click');
            filters.search = document.getElementById('filtroInventario').value;
            filters.id_tipo_activo = document.getElementById('filtroTipoActivo').value;
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

// ========== CRUD FUNCTIONS ==========
function cargarActivos() {
    const params = new URLSearchParams({
        page: currentPage,
        search: filters.search,
        id_tipo_activo: filters.id_tipo_activo,
        id_estatus: filters.id_estatus
    });

    fetch(`{{ route('inventario.data') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            renderTabla(data.data);
            renderPagination(data);
            actualizarTotal(data.total);
            verificarActivosPorVencer(data.data);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tablaInventarioBody').innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                        Error al cargar los datos
                    </td>
                </tr>
            `;
            showNotification('Error al cargar los datos', 'error');
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
        mensaje = '❌ Vida útil vencida';
    } else if (diffMeses <= 6) {
        badgeClass = 'warning';
        mensaje = `⚠️ Próximo a vencer (${diffMeses} meses)`;
    } else if (diffMeses <= 12) {
        badgeClass = 'info';
        mensaje = `⏰ ${diffMeses} meses restantes`;
    } else {
        const añosRest = (diffMeses / 12).toFixed(1);
        mensaje = `✅ ${añosRest} años restantes`;
    }
    
    if (fechaFinGarantia) {
        const garantia = new Date(fechaFinGarantia);
        if (garantia < hoy) {
            mensaje += ' | Garantía vencida';
        } else if (garantia < fechaFin) {
            mensaje += ' | En garantía';
        }
    }
    
    return `<span class="badge bg-${badgeClass}" title="${mensaje}">${mensaje}</span>`;
}

function renderTabla(activos) {
    const tbody = document.getElementById('tablaInventarioBody');
    if (!activos || !activos.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <i class="fas fa-box-open fa-2x text-muted mb-2 d-block"></i>
                    No hay activos registrados
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = activos.map(activo => `
        <tr>
            <td><code class="badge bg-secondary">${escapeHtml(activo.serial)}</code></td>
            <td>${escapeHtml(activo.marca_modelo)}</td>
            <td>
                <span class="badge bg-${activo.tipo_equipo === 'principal' ? 'primary' : (activo.tipo_equipo === 'secundario' ? 'info' : 'secondary')}">
                    ${activo.tipo_equipo}
                </span>
             </td>
            <td>${activo.tipo_activo ? escapeHtml(activo.tipo_activo.nombre) : '-'}</td>
            <td>
                <span class="badge bg-${activo.cantidad > 0 ? 'success' : 'danger'}">
                    ${activo.cantidad}
                </span>
             </td>
            <td>
                <span class="badge bg-${activo.estatus ? activo.estatus.color_badge : 'secondary'}">
                    ${activo.estatus ? activo.estatus.descripcion : '-'}
                </span>
             </td>
            <td>${escapeHtml(activo.ubicacion || '-')}</td>
            <td>${renderVidaUtil(activo.fecha_adquisicion, activo.vida_util_anos, activo.fecha_fin_garantia)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-info" onclick="verActivo(${activo.id})" title="Ver">
                        <i class="fas fa-eye"></i> Ver
                    </button>
                    <button class="btn btn-success" onclick="verEspecificaciones(${activo.id})" title="Ver Especificaciones">
                        <i class="fas fa-microchip"></i> Especs
                    </button>
                    <button class="btn btn-warning" onclick="editarActivo(${activo.id})" title="Editar">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn btn-danger" onclick="abrirModalEliminar(${activo.id}, '${escapeHtml(activo.serial)}', '${escapeHtml(activo.marca_modelo)}')" title="Eliminar">
                        <i class="fas fa-trash"></i> Eliminar
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
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${data.current_page - 1})">« Anterior</a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link">« Anterior</span></li>`;
    }
    
    for (let i = 1; i <= data.last_page; i++) {
        if (i === data.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a></li>`;
        }
    }
    
    if (data.next_page_url) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${data.current_page + 1})">Siguiente »</a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link">Siguiente »</span></li>`;
    }
    
    html += '</ul></nav>';
    container.innerHTML = html;
}

function cambiarPagina(page) {
    soundManager.play('click');
    currentPage = page;
    cargarActivos();
}

function verificarActivosPorVencer(activos) {
    const hoy = new Date();
    const proximosAVencer = [];
    
    activos.forEach(activo => {
        if (activo.fecha_adquisicion && activo.vida_util_anos) {
            const fechaFin = new Date(activo.fecha_adquisicion);
            fechaFin.setFullYear(fechaFin.getFullYear() + parseInt(activo.vida_util_anos));
            const diffMeses = Math.ceil((fechaFin - hoy) / (1000 * 60 * 60 * 24 * 30));
            
            if (diffMeses <= 6 && diffMeses > 0) {
                proximosAVencer.push({
                    nombre: `${activo.serial} - ${activo.marca_modelo}`,
                    meses: diffMeses
                });
            }
        }
    });
    
    if (proximosAVencer.length > 0) {
        const mensaje = `⚠️ ${proximosAVencer.length} equipo(s) próximo(s) a cumplir su vida útil`;
        showNotification(mensaje, 'warning');
    }
}

// ========== FUNCIÓN VER ACTIVO (con botón de especificaciones) ==========
window.verActivo = function(id) {
    soundManager.play('click');
    fetch(`{{ url('inventario') }}/${id}`)
        .then(response => response.json())
        .then(activo => {
            const modalBody = document.getElementById('detalleActivoBody');
            modalBody.innerHTML = `
                <div class="mb-3">
                    <button class="btn btn-success btn-sm" onclick="verEspecificaciones(${activo.id})">
                        <i class="fas fa-microchip"></i> Ver Especificaciones Técnicas
                    </button>
                </div>
                <table class="table table-bordered">
                    <tr><th width="35%">Serial:</th><td><code>${escapeHtml(activo.serial)}</code></td></tr>
                    <tr><th>Tipo Equipo:</th><td><span class="badge bg-${activo.tipo_equipo === 'principal' ? 'primary' : 'secondary'}">${activo.tipo_equipo}</span></td></tr>
                    <tr><th>Marca/Modelo:</th><td>${escapeHtml(activo.marca_modelo)}</td></tr>
                    <tr><th>Categoría:</th><td>${activo.tipo_activo ? activo.tipo_activo.nombre : '-'}</td></tr>
                    <tr><th>Cantidad:</th><td>${activo.cantidad}</td></tr>
                    <tr><th>Estatus:</th><td><span class="badge bg-${activo.estatus ? activo.estatus.color_badge : 'secondary'}">${activo.estatus ? activo.estatus.descripcion : '-'}</span></td></tr>
                    <tr><th>Ubicación:</th><td>${escapeHtml(activo.ubicacion || '-')}</td></tr>
                    <tr><th>Fecha Adquisición:</th><td>${activo.fecha_adquisicion || '-'}</td></tr>
                    <tr><th>Vida Útil:</th><td>${activo.vida_util_anos || '-'} años</td></tr>
                    <tr><th>Fecha Fin Garantía:</th><td>${activo.fecha_fin_garantia || '-'}</td></tr>
                    <tr><th>Estado Vida Útil:</th><td>${renderVidaUtil(activo.fecha_adquisicion, activo.vida_util_anos, activo.fecha_fin_garantia)}</td></tr>
                    <tr><th>Observaciones:</th><td>${escapeHtml(activo.observaciones || '-')}</td></tr>
                    <tr><th>Registrado:</th><td>${activo.created_at || '-'}</td></tr>
                </table>
            `;
            if (verActivoModal) verActivoModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al cargar el detalle', 'error');
        });
};

// ========== FUNCIÓN PARA VER ESPECIFICACIONES TÉCNICAS ==========
window.verEspecificaciones = function(id) {
    soundManager.play('click');
    currentActivoIdForEspecs = id;
    
    fetch(`{{ url('inventario') }}/${id}`)
        .then(response => response.json())
        .then(activo => {
            const especBody = document.getElementById('especificacionesBody');
            const btnEditar = document.getElementById('btnEditarEspecificaciones');
            
            // Parsear especificaciones si existen
            let especificaciones = null;
            if (activo.especificaciones_tecnicas) {
                try {
                    especificaciones = typeof activo.especificaciones_tecnicas === 'string' ? 
                        JSON.parse(activo.especificaciones_tecnicas) : activo.especificaciones_tecnicas;
                } catch(e) {
                    console.error('Error parsing especificaciones:', e);
                }
            }
            
            if (especificaciones && Object.keys(especificaciones).length > 0) {
                // Mostrar especificaciones en una tabla bonita
                let html = `
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        <strong>${escapeHtml(activo.marca_modelo)}</strong> - ${activo.tipo_activo ? activo.tipo_activo.nombre : 'Equipo'}
                        <br>
                        <small class="text-muted">Serial: ${escapeHtml(activo.serial)}</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-success">
                                <tr>
                                    <th width="40%">Característica</th>
                                    <th width="60%">Especificación</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                // Mapear nombres de campos a nombres legibles
                const nombresLegibles = {
                    'procesador': '🖥️ Procesador',
                    'ram': '💾 RAM',
                    'disco_duro': '💿 Disco Duro',
                    'sistema_operativo': '⚙️ Sistema Operativo',
                    'bateria': '🔋 Duración de Batería',
                    'almacenamiento': '💽 Almacenamiento',
                    'pantalla': '📱 Tamaño de Pantalla',
                    'cpu_cores': '🎛️ CPU Cores',
                    'ram_total': '💾 RAM Total',
                    'tipo_impresora': '🖨️ Tipo de Impresora',
                    'velocidad': '⚡ Velocidad',
                    'modelo': '📱 Modelo',
                    'imei': '🔢 IMEI',
                    'procesador_grafico': '🎮 Procesador Gráfico',
                    'puertos': '🔌 Puertos',
                    'conectividad': '📡 Conectividad',
                    'incluye': '📦 Incluye'
                };
                
                let tieneEspecificaciones = false;
                for (const [key, value] of Object.entries(especificaciones)) {
                    if (value && value.toString().trim() !== '') {
                        tieneEspecificaciones = true;
                        const nombreLegible = nombresLegibles[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        html += `
                            <tr>
                                <td class="fw-bold bg-light">${escapeHtml(nombreLegible)}</td>
                                <td>${escapeHtml(value.toString())}</td>
                            </tr>
                        `;
                    }
                }
                
                if (!tieneEspecificaciones) {
                    html = `
                        <div class="text-center py-5">
                            <i class="fas fa-microchip fa-4x text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">No hay especificaciones técnicas registradas</h5>
                            <p class="text-muted">Este equipo no tiene especificaciones técnicas asociadas.</p>
                        </div>
                    `;
                    btnEditar.style.display = 'block';
                } else {
                    html += `
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-muted small">
                        <i class="fas fa-clock"></i> Última actualización: ${activo.updated_at || activo.created_at || 'No disponible'}
                    </div>
                    `;
                    btnEditar.style.display = 'block';
                }
                
                especBody.innerHTML = html;
            } else {
                // No hay especificaciones registradas
                especBody.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-microchip fa-4x text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">No hay especificaciones técnicas registradas</h5>
                        <p class="text-muted">Este equipo no tiene especificaciones técnicas asociadas.</p>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i> 
                            Puedes agregar especificaciones técnicas editando el activo y completando los campos específicos según la categoría.
                        </div>
                    </div>
                `;
                btnEditar.style.display = 'block';
            }
            
            if (especificacionesModal) especificacionesModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al cargar las especificaciones', 'error');
        });
};

// Función para cerrar modal de especificaciones y editar
window.cerrarModalEspecificacionesYEditar = function(id) {
    if (especificacionesModal) especificacionesModal.hide();
    setTimeout(() => {
        editarActivo(id);
    }, 300);
};

// ========== FUNCIÓN EDITAR ACTIVO ==========
window.editarActivo = function(id) {
    soundManager.play('click');
    fetch(`{{ url('inventario') }}/${id}`)
        .then(response => response.json())
        .then(activo => {
            document.getElementById('modalTitle').innerHTML = 'Editar Activo';
            document.getElementById('activo_id').value = activo.id;
            document.getElementById('serial').value = activo.serial;
            document.getElementById('tipo_equipo').value = activo.tipo_equipo;
            document.getElementById('marca_modelo').value = activo.marca_modelo;
            document.getElementById('id_tipo_activo').value = activo.id_tipo_activo;
            document.getElementById('id_estatus').value = activo.id_estatus;
            document.getElementById('cantidad').value = activo.cantidad;
            document.getElementById('ubicacion').value = activo.ubicacion || '';
            document.getElementById('fecha_adquisicion').value = activo.fecha_adquisicion || '';
            document.getElementById('vida_util_anos').value = activo.vida_util_anos || '';
            document.getElementById('fecha_fin_garantia').value = activo.fecha_fin_garantia || '';
            document.getElementById('observaciones').value = activo.observaciones || '';
            
            // Disparar evento change para cargar campos específicos
            const selectCategoria = document.getElementById('id_tipo_activo');
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
            }, 100);
            
            if (activoModal) activoModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al cargar el activo para editar', 'error');
        });
};

// ========== MODAL ELIMINAR ==========
window.abrirModalEliminar = function(id, serial, marcaModelo) {
    soundManager.play('warning');
    deleteId = id;
    const deleteMessage = document.getElementById('deleteActivoInfo');
    if (deleteMessage) {
        deleteMessage.innerHTML = `<strong>${escapeHtml(serial)}</strong> - ${escapeHtml(marcaModelo)}`;
    }
    if (deleteModal) deleteModal.show();
};

function confirmarEliminar() {
    if (!deleteId) return;
    
    const btn = document.getElementById('btnConfirmarEliminar');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Eliminando...';
    
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
            if (deleteModal) deleteModal.hide();
            cargarActivos();
            showNotification(data.message || 'Activo eliminado correctamente', 'success');
            deleteId = null;
        } else {
            showNotification(data.message || 'Error al eliminar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al eliminar el activo', 'error');
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
        tipo_equipo: document.getElementById('tipo_equipo').value,
        marca_modelo: document.getElementById('marca_modelo').value,
        id_tipo_activo: document.getElementById('id_tipo_activo').value,
        id_estatus: document.getElementById('id_estatus').value,
        cantidad: document.getElementById('cantidad').value,
        ubicacion: document.getElementById('ubicacion').value,
        fecha_adquisicion: document.getElementById('fecha_adquisicion').value,
        vida_util_anos: document.getElementById('vida_util_anos').value,
        fecha_fin_garantia: document.getElementById('fecha_fin_garantia').value,
        observaciones: document.getElementById('observaciones').value,
        especificaciones_tecnicas: especificacionesTecnicas
    };
    
    // Validaciones
    if (!formData.serial) {
        showNotification('El campo Serial es requerido', 'warning');
        document.getElementById('serial').focus();
        return;
    }
    if (!formData.tipo_equipo) {
        showNotification('El campo Tipo de Equipo es requerido', 'warning');
        document.getElementById('tipo_equipo').focus();
        return;
    }
    if (!formData.marca_modelo) {
        showNotification('El campo Marca/Modelo es requerido', 'warning');
        document.getElementById('marca_modelo').focus();
        return;
    }
    if (!formData.id_tipo_activo) {
        showNotification('El campo Categoría es requerido', 'warning');
        document.getElementById('id_tipo_activo').focus();
        return;
    }
    if (!formData.id_estatus) {
        showNotification('El campo Estatus es requerido', 'warning');
        document.getElementById('id_estatus').focus();
        return;
    }
    if (!formData.fecha_adquisicion) {
        showNotification('El campo Fecha de Adquisición es requerido', 'warning');
        document.getElementById('fecha_adquisicion').focus();
        return;
    }
    if (!formData.vida_util_anos) {
        showNotification('El campo Vida Útil es requerido', 'warning');
        document.getElementById('vida_util_anos').focus();
        return;
    }
    
    const btn = document.getElementById('btnGuardarActivo');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
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
            if (activoModal) activoModal.hide();
            cargarActivos();
            showNotification(data.message || 'Activo guardado correctamente', 'success');
            limpiarFormulario();
        } else {
            showNotification(data.message || 'Error al guardar el activo', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al guardar el activo', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function limpiarFormulario() {
    document.getElementById('activo_id').value = '';
    document.getElementById('serial').value = '';
    document.getElementById('tipo_equipo').value = '';
    document.getElementById('marca_modelo').value = '';
    document.getElementById('id_tipo_activo').value = '';
    document.getElementById('id_estatus').value = '';
    document.getElementById('cantidad').value = '1';
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
    const selectCategoria = document.getElementById('id_tipo_activo');
    if (!selectCategoria) return;
    
    // Vida útil por defecto según categoría (valores de ejemplo)
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
        const selectedOption = this.options[this.selectedIndex];
        const vidaUtilData = selectedOption.getAttribute('data-vida-util');
        const campoVidaUtil = document.getElementById('vida_util_anos');
        const previewDiv = document.getElementById('vidaUtilPreview');
        const mensajeSpan = document.getElementById('vidaUtilMensaje');
        
        // Auto-completar vida útil según categoría
        if (categoriaId && vidaUtilPorDefecto[categoriaId]) {
            const años = vidaUtilPorDefecto[categoriaId];
            campoVidaUtil.value = años;
            
            // Mostrar preview
            const fechaAdquisicion = document.getElementById('fecha_adquisicion').value;
            if (fechaAdquisicion) {
                const fechaFin = new Date(fechaAdquisicion);
                fechaFin.setFullYear(fechaFin.getFullYear() + años);
                mensajeSpan.innerHTML = `Este equipo tiene una vida útil estimada de ${años} años. 
                                         Fecha estimada de fin de vida: ${fechaFin.toLocaleDateString()}`;
                previewDiv.style.display = 'block';
            } else {
                mensajeSpan.innerHTML = `Este equipo tiene una vida útil estimada de ${años} años. 
                                         Complete la fecha de adquisición para ver el cálculo.`;
                previewDiv.style.display = 'block';
            }
        }
        
        // Mostrar campos específicos según categoría
        mostrarCamposEspecificosPorCategoria(categoriaId);
    });
    
    // Recalcular cuando cambie la fecha de adquisición
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
    
    // Definir campos según categoría (ajusta según tu BD)
    const camposPorCategoria = {
        1: [ // Computadoras
            { tipo: 'text', id: 'procesador', label: '🖥️ Procesador', col: 4, placeholder: 'Ej: Intel Core i7-12700' },
            { tipo: 'text', id: 'ram', label: '💾 RAM (GB)', col: 4, placeholder: 'Ej: 16' },
            { tipo: 'text', id: 'disco_duro', label: '💿 Disco Duro', col: 4, placeholder: 'Ej: SSD 512GB' },
            { tipo: 'text', id: 'sistema_operativo', label: '⚙️ Sistema Operativo', col: 6, placeholder: 'Ej: Windows 11 Pro' },
            { tipo: 'text', id: 'procesador_grafico', label: '🎮 Procesador Gráfico', col: 6, placeholder: 'Ej: NVIDIA GTX 1660' }
        ],
        2: [ // Laptops
            { tipo: 'text', id: 'procesador', label: '🖥️ Procesador', col: 4, placeholder: 'Ej: Intel Core i5' },
            { tipo: 'text', id: 'ram', label: '💾 RAM (GB)', col: 4, placeholder: 'Ej: 8' },
            { tipo: 'text', id: 'bateria', label: '🔋 Duración batería (horas)', col: 4, placeholder: 'Ej: 6' },
            { tipo: 'text', id: 'pantalla', label: '📱 Tamaño Pantalla (pulgadas)', col: 6, placeholder: 'Ej: 15.6' }
        ],
        3: [ // Tablets
            { tipo: 'text', id: 'almacenamiento', label: '💽 Almacenamiento (GB)', col: 6, placeholder: 'Ej: 64' },
            { tipo: 'text', id: 'pantalla', label: '📱 Tamaño Pantalla (pulgadas)', col: 6, placeholder: 'Ej: 10.1' },
            { tipo: 'text', id: 'conectividad', label: '📡 Conectividad', col: 12, placeholder: 'Ej: WiFi, 4G' }
        ],
        4: [ // Servidores
            { tipo: 'text', id: 'cpu_cores', label: '🎛️ CPU Cores', col: 3, placeholder: 'Ej: 8' },
            { tipo: 'text', id: 'ram_total', label: '💾 RAM Total (GB)', col: 3, placeholder: 'Ej: 32' },
            { tipo: 'text', id: 'almacenamiento', label: '💽 Almacenamiento (TB)', col: 3, placeholder: 'Ej: 2' },
            { tipo: 'text', id: 'sistema_operativo', label: '⚙️ Sistema Operativo', col: 3, placeholder: 'Ej: Ubuntu Server' }
        ],
        6: [ // Teléfonos
            { tipo: 'text', id: 'modelo', label: '📱 Modelo', col: 4, placeholder: 'Ej: iPhone 13' },
            { tipo: 'text', id: 'imei', label: '🔢 IMEI', col: 4, placeholder: 'Ej: 123456789012345' },
            { tipo: 'text', id: 'almacenamiento', label: '💽 Almacenamiento (GB)', col: 4, placeholder: 'Ej: 128' }
        ],
        7: [ // Impresoras
            { tipo: 'text', id: 'tipo_impresora', label: '🖨️ Tipo', col: 6, placeholder: 'Laser/Tinta' },
            { tipo: 'text', id: 'velocidad', label: '⚡ Velocidad (ppm)', col: 6, placeholder: 'Ej: 20' },
            { tipo: 'text', id: 'conectividad', label: '📡 Conectividad', col: 12, placeholder: 'Ej: USB, WiFi, Ethernet' }
        ]
    };
    
    const campos = camposPorCategoria[categoriaId] || [];
    
    if (campos.length > 0) {
        const tituloDiv = document.createElement('div');
        tituloDiv.className = 'col-12 mb-3';
        tituloDiv.innerHTML = '<hr><h6 class="fw-bold text-primary">📋 Especificaciones Técnicas</h6><p class="text-muted small">Complete los detalles técnicos del equipo</p>';
        container.appendChild(tituloDiv);
    }
    
    campos.forEach(campo => {
        const colDiv = document.createElement('div');
        colDiv.className = `col-md-${campo.col} mb-3`;
        colDiv.innerHTML = `
            <label class="form-label fw-bold">${campo.label}</label>
            <input type="${campo.tipo}" id="${campo.id}" name="${campo.id}" 
                   class="form-control" placeholder="${campo.placeholder || ''}">
        `;
        container.appendChild(colDiv);
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection