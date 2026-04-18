@extends('layouts.dashboard')

@section('title', 'Gestión de Inventario')

@section('content')
<div class="container-fluid px-4">
    <!-- Pestañas de navegación (SOLO 2 TABS) -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="inventarioTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab">
                        📦 Activos en Stock
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="componentes-tab" data-bs-toggle="tab" data-bs-target="#componentes" type="button" role="tab">
                        💾 Componentes por Categoría
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content">
        <!-- TAB 1: ACTIVOS EN STOCK -->
        <div class="tab-pane fade show active" id="activos" role="tabpanel">
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
                                        <th>Capacidad</th>
                                        <th>Tipo Equipo</th>
                                        <th>Categoría</th>
                                        <th>Cantidad</th>
                                        <th>Estatus</th>
                                        <th>Ubicación</th>
                                        <th>Valor</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaInventarioBody">
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Cargando activos...</p>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="paginationLinks" class="mt-3 d-flex justify-content-center"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: COMPONENTES POR CATEGORÍA (CON SERIALES Y CAPACIDADES) -->
        <div class="tab-pane fade" id="componentes" role="tabpanel">
            <div class="row">
                <div class="col-12">
                    <div class="stat-card">
                        <h5 class="mb-3">💾 Componentes por Categoría</h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Seleccionar Categoría</label>
                                <select id="categoriaComponentes" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($tiposActivo as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div id="componentesDetalle" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear/editar activo con generación de seriales -->
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
                        <label class="form-label fw-bold">Serial Principal *</label>
                        <input type="text" id="serial" name="serial" class="form-control" required>
                        <small class="text-muted">Para equipos con múltiples unidades, se generarán seriales automáticos</small>
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
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Campo CAPACIDAD (importante para discos duros) -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">💾 Capacidad</label>
                        <input type="text" id="capacidad" name="capacidad" class="form-control" placeholder="Ej: 1TB, 512GB SSD, 2TB HDD">
                        <small class="text-muted">Especificar capacidad para discos duros, RAM, etc.</small>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Cantidad *</label>
                        <input type="number" id="cantidad" name="cantidad" class="form-control" value="1" min="1" required>
                        <small class="text-muted">Si es >1, se generarán seriales automáticos</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Estatus *</label>
                        <select id="id_estatus" name="id_estatus" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($estatusList as $estatus)
                                <option value="{{ $estatus->id }}">{{ $estatus->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" class="form-control" placeholder="Ej: Oficina 301">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Fecha Adquisición</label>
                        <input type="date" id="fecha_adquisicion" name="fecha_adquisicion" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Valor Compra</label>
                        <input type="number" step="0.01" id="valor_compra" name="valor_compra" class="form-control" placeholder="0.00">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Disponible Desde</label>
                        <input type="date" id="disponible_desde" name="disponible_desde" class="form-control">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Detalles Técnicos</label>
                        <textarea id="detalles_tecnicos" name="detalles_tecnicos" class="form-control" rows="2" placeholder="Velocidad, interfaz, tecnología, etc."></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <!-- Sección para seriales múltiples -->
                <div id="serialesMultiplesSection" style="display: none;">
                    <hr>
                    <label class="form-label fw-bold">🔢 Seriales Generados:</label>
                    <div id="listaSeriales" class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                        <small class="text-muted">Los seriales se generarán automáticamente al guardar</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarActivo">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalle de activo con sus componentes -->
<div class="modal fade" id="verActivoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalle del Activo y sus Componentes</h5>
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
    
    .nav-tabs .nav-link {
        font-size: 1rem;
        padding: 10px 20px;
        color: #495057;
    }
    
    .nav-tabs .nav-link.active {
        font-weight: bold;
        color: #1e4a76;
        border-bottom: 3px solid #1e4a76;
    }
    
    .componente-item {
        border-left: 4px solid #1e4a76;
        margin-bottom: 10px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.2s;
    }
    
    .componente-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .serial-badge {
        font-family: monospace;
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
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
let filters = {
    search: '',
    id_tipo_activo: '',
    id_estatus: ''
};

let activoModal = null;
let verActivoModal = null;
let deleteModal = null;

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('activoModal');
    const verModalElement = document.getElementById('verActivoModal');
    const deleteModalElement = document.getElementById('deleteModal');
    
    if (modalElement) activoModal = new bootstrap.Modal(modalElement);
    if (verModalElement) verActivoModal = new bootstrap.Modal(verModalElement);
    if (deleteModalElement) deleteModal = new bootstrap.Modal(deleteModalElement);
    
    configurarEventos();
    cargarActivos();
    
    // Evento cambio de categoría para componentes
    document.getElementById('categoriaComponentes')?.addEventListener('change', function() {
        if (this.value) {
            cargarComponentesPorCategoria(this.value);
        } else {
            document.getElementById('componentesDetalle').innerHTML = '';
        }
    });
    
    // Generar vista previa de seriales al cambiar cantidad
    document.getElementById('cantidad')?.addEventListener('change', function() {
        const cantidad = parseInt(this.value) || 1;
        const serialBase = document.getElementById('serial').value;
        if (cantidad > 1 && serialBase) {
            mostrarVistaPreviaSeriales(serialBase, cantidad);
        } else {
            document.getElementById('serialesMultiplesSection').style.display = 'none';
        }
    });
    
    document.getElementById('serial')?.addEventListener('input', function() {
        const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
        if (cantidad > 1 && this.value) {
            mostrarVistaPreviaSeriales(this.value, cantidad);
        }
    });
});

function mostrarVistaPreviaSeriales(serialBase, cantidad) {
    const section = document.getElementById('serialesMultiplesSection');
    const container = document.getElementById('listaSeriales');
    
    let html = '<div class="small">';
    for (let i = 1; i <= Math.min(cantidad, 20); i++) {
        const serialGenerado = `${serialBase}-${String(i).padStart(3, '0')}`;
        html += `<div class="componente-item mb-1">
                    <i class="fas fa-hdd me-2"></i>
                    <code>${serialGenerado}</code>
                    <span class="badge bg-secondary ms-2">Unidad ${i}</span>
                 </div>`;
    }
    if (cantidad > 20) {
        html += `<div class="text-muted mt-2">... y ${cantidad - 20} unidades más</div>`;
    }
    html += '</div>';
    
    container.innerHTML = html;
    section.style.display = 'block';
}

function configurarEventos() {
    // Botón nuevo activo
    const btnAgregar = document.getElementById('btnAgregarActivo');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function() {
            soundManager.play('click');
            limpiarFormulario();
            document.getElementById('modalTitle').innerHTML = 'Nuevo Activo';
            document.getElementById('serialesMultiplesSection').style.display = 'none';
            if (activoModal) activoModal.show();
        });
    }
    
    // Botón guardar activo
    const btnGuardar = document.getElementById('btnGuardarActivo');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', guardarActivo);
    }
    
    // Botón confirmar eliminar
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmarEliminar) {
        btnConfirmarEliminar.addEventListener('click', confirmarEliminar);
    }
    
    // Botón filtrar
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
    
    // Filtro en tiempo real
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

// ========== FUNCIONES PARA ACTIVOS ==========
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
            renderTablaActivos(data.data);
            renderPagination(data, 'paginationLinks', 'cambiarPagina');
            actualizarTotal(data.total);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tablaInventarioBody').innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                        Error al cargar los datos
                </tr>
            `;
            showNotification('Error al cargar los datos', 'error');
        });
}

function cargarComponentesPorCategoria(categoriaId) {
    const container = document.getElementById('componentesDetalle');
    container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div><p>Cargando componentes...</p></div>';
    
    fetch(`{{ url('inventario/componentes-por-categoria') }}/${categoriaId}`)
        .then(response => response.json())
        .then(data => {
            if (!data || data.length === 0) {
                container.innerHTML = '<div class="alert alert-info">📭 No hay componentes registrados en esta categoría</div>';
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Serial</th>
                                <th>Marca/Modelo</th>
                                <th>💾 Capacidad</th>
                                <th>Cantidad</th>
                                <th>Ubicación</th>
                                <th>Detalles Técnicos</th>
                                <th>Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.forEach(componente => {
                // Si tiene múltiples seriales, mostrarlos todos
                if (componente.seriales && componente.seriales.length > 0) {
                    componente.seriales.forEach(serialItem => {
                        html += `
                            <tr>
                                <td><code class="serial-badge">${escapeHtml(serialItem.serial)}</code></td>
                                <td>${escapeHtml(componente.marca_modelo)}</td>
                                <td><span class="badge bg-info">${escapeHtml(componente.capacidad || 'N/E')}</span></td>
                                <td>1</td>
                                <td>${escapeHtml(componente.ubicacion || '-')}</td>
                                <td><small>${escapeHtml(componente.detalles_tecnicos || '-')}</small></td>
                                <td><span class="badge bg-success">Disponible</span></td>
                            </tr>
                        `;
                    });
                } else {
                    html += `
                        <tr>
                            <td><code class="serial-badge">${escapeHtml(componente.serial)}</code></td>
                            <td>${escapeHtml(componente.marca_modelo)}</td>
                            <td><span class="badge bg-info">${escapeHtml(componente.capacidad || 'N/E')}</span></td>
                            <td>${componente.cantidad}</td>
                            <td>${escapeHtml(componente.ubicacion || '-')}</td>
                            <td><small>${escapeHtml(componente.detalles_tecnicos || '-')}</small></td>
                            <td><span class="badge bg-${componente.cantidad > 0 ? 'success' : 'danger'}">${componente.cantidad > 0 ? 'Disponible' : 'Agotado'}</span></td>
                        </tr>
                    `;
                }
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-muted small">
                    <i class="fas fa-info-circle"></i> Total de componentes: ${data.reduce((sum, c) => sum + (c.seriales ? c.seriales.length : c.cantidad), 0)} unidades
                </div>
            `;
            
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="alert alert-danger">❌ Error al cargar los componentes</div>';
            showNotification('Error al cargar componentes', 'error');
        });
}

function actualizarTotal(total) {
    const totalSpan = document.getElementById('totalActivosCount');
    if (totalSpan) totalSpan.innerHTML = `Total: ${total}`;
}

function renderTablaActivos(activos) {
    const tbody = document.getElementById('tablaInventarioBody');
    if (!activos || !activos.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-4">
                    <i class="fas fa-box-open fa-2x text-muted mb-2 d-block"></i>
                    No hay activos registrados
            </tr>
        `;
        return;
    }

    tbody.innerHTML = activos.map(activo => `
        <tr>
            <td><code class="badge bg-secondary">${escapeHtml(activo.serial)}</code></td>
            <td>${escapeHtml(activo.marca_modelo)}</td>
            <td>${activo.capacidad ? `<span class="badge bg-info">${escapeHtml(activo.capacidad)}</span>` : '-'}</td>
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
            <td>$${parseFloat(activo.valor_compra || 0).toFixed(2)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-info" onclick="verActivo(${activo.id})" title="Ver">
                        <i class="fas fa-eye"></i> Ver
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

function renderPagination(data, containerId, callbackName) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    if (data.last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '<nav><ul class="pagination justify-content-center">';
    
    if (data.prev_page_url) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="${callbackName}(${data.current_page - 1})">« Anterior</a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link">« Anterior</span></li>`;
    }
    
    for (let i = 1; i <= data.last_page; i++) {
        if (i === data.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="${callbackName}(${i})">${i}</a></li>`;
        }
    }
    
    if (data.next_page_url) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="${callbackName}(${data.current_page + 1})">Siguiente »</a></li>`;
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

function guardarActivo() {
    const id = document.getElementById('activo_id').value;
    const url = id ? `{{ url('inventario') }}/${id}` : `{{ route('inventario.store') }}`;
    const method = id ? 'PUT' : 'POST';
    
    const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
    const serialBase = document.getElementById('serial').value;
    
    const formData = {
        serial: serialBase,
        tipo_equipo: document.getElementById('tipo_equipo').value,
        marca_modelo: document.getElementById('marca_modelo').value,
        id_tipo_activo: document.getElementById('id_tipo_activo').value,
        id_estatus: document.getElementById('id_estatus').value,
        cantidad: cantidad,
        capacidad: document.getElementById('capacidad').value,
        ubicacion: document.getElementById('ubicacion').value,
        fecha_adquisicion: document.getElementById('fecha_adquisicion').value,
        valor_compra: document.getElementById('valor_compra').value,
        disponible_desde: document.getElementById('disponible_desde').value,
        detalles_tecnicos: document.getElementById('detalles_tecnicos').value,
        observaciones: document.getElementById('observaciones').value
    };
    
    // Si cantidad > 1, generar seriales automáticos
    if (cantidad > 1 && !id) {
        const seriales = [];
        for (let i = 1; i <= cantidad; i++) {
            seriales.push(`${serialBase}-${String(i).padStart(3, '0')}`);
        }
        formData.seriales_generados = seriales;
    }
    
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
            if (cantidad > 1) {
                showNotification(`✅ Se crearon ${cantidad} seriales automáticamente`, 'success');
            } else {
                showNotification(data.message || 'Activo guardado correctamente', 'success');
            }
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

// ========== FUNCIONES DE VISTA ==========
window.verActivo = function(id) {
    soundManager.play('click');
    fetch(`{{ url('inventario') }}/${id}`)
        .then(response => response.json())
        .then(activo => {
            const modalBody = document.getElementById('detalleActivoBody');
            modalBody.innerHTML = `
                <table class="table table-bordered">
                    <tr><th width="35%">Serial:</th><td><code>${escapeHtml(activo.serial)}</code></td></tr>
                    <tr><th>Marca/Modelo:</th><td>${escapeHtml(activo.marca_modelo)}</td></tr>
                    <tr><th>💾 Capacidad:</th><td>${activo.capacidad ? `<span class="badge bg-info">${escapeHtml(activo.capacidad)}</span>` : '-'}</td></tr>
                    <tr><th>Tipo Equipo:</th><td><span class="badge bg-${activo.tipo_equipo === 'principal' ? 'primary' : 'secondary'}">${activo.tipo_equipo}</span></td></tr>
                    <tr><th>Categoría:</th><td>${activo.tipo_activo ? activo.tipo_activo.nombre : '-'}</td></tr>
                    <tr><th>Cantidad:</th><td>${activo.cantidad}</td></tr>
                    <tr><th>Estatus:</th><td><span class="badge bg-${activo.estatus ? activo.estatus.color_badge : 'secondary'}">${activo.estatus ? activo.estatus.descripcion : '-'}</span></td></tr>
                    <tr><th>Ubicación:</th><td>${escapeHtml(activo.ubicacion || '-')}</td></tr>
                    <tr><th>Fecha Adquisición:</th><td>${activo.fecha_adquisicion || '-'}</td></tr>
                    <tr><th>Valor Compra:</th><td>$${parseFloat(activo.valor_compra || 0).toFixed(2)}</td></tr>
                    <tr><th>Disponible Desde:</th><td>${activo.disponible_desde || '-'}</td></tr>
                    <tr><th>Detalles Técnicos:</th><td>${escapeHtml(activo.detalles_tecnicos || '-')}</td></tr>
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
            document.getElementById('capacidad').value = activo.capacidad || '';
            document.getElementById('ubicacion').value = activo.ubicacion || '';
            document.getElementById('fecha_adquisicion').value = activo.fecha_adquisicion || '';
            document.getElementById('valor_compra').value = activo.valor_compra || '';
            document.getElementById('disponible_desde').value = activo.disponible_desde || '';
            document.getElementById('detalles_tecnicos').value = activo.detalles_tecnicos || '';
            document.getElementById('observaciones').value = activo.observaciones || '';
            document.getElementById('serialesMultiplesSection').style.display = 'none';
            if (activoModal) activoModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al cargar el activo para editar', 'error');
        });
};

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

function limpiarFormulario() {
    document.getElementById('activo_id').value = '';
    document.getElementById('serial').value = '';
    document.getElementById('tipo_equipo').value = '';
    document.getElementById('marca_modelo').value = '';
    document.getElementById('id_tipo_activo').value = '';
    document.getElementById('id_estatus').value = '';
    document.getElementById('cantidad').value = '1';
    document.getElementById('capacidad').value = '';
    document.getElementById('ubicacion').value = '';
    document.getElementById('fecha_adquisicion').value = '';
    document.getElementById('valor_compra').value = '';
    document.getElementById('disponible_desde').value = '';
    document.getElementById('detalles_tecnicos').value = '';
    document.getElementById('observaciones').value = '';
    document.getElementById('serialesMultiplesSection').style.display = 'none';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection