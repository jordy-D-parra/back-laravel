@extends('layouts.dashboard')

@section('title', 'Detalle de Ficha de Soporte #' . $ficha->id)

@section('content')
<div class="container-fluid px-4">
    <!-- Botón volver -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('soporte.index') }}" class="text-decoration-none" style="color: #4361ee;">
                ← Volver al listado
            </a>
        </div>
    </div>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tarjeta principal -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card" style="padding: 0; overflow: hidden;">
                <!-- Cabecera con gradiente -->
                <div style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); padding: 24px 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h2 style="margin: 0; color: white;">🔧 Ficha de Soporte #{{ $ficha->id }}</h2>
                        <p style="margin: 8px 0 0 0; color: rgba(255,255,255,0.8);">
                            Creada el {{ \Carbon\Carbon::parse($ficha->created_at)->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div>
                        @php
                            $estadoColors = [
                                'pendiente' => 'warning',
                                'en_proceso' => 'primary',
                                'completado' => 'success'
                            ];
                            $estadoIconos = [
                                'pendiente' => '⏳',
                                'en_proceso' => '🔧',
                                'completado' => '✅'
                            ];
                            $badgeColor = $estadoColors[$ficha->estado] ?? 'secondary';
                            $estadoIcono = $estadoIconos[$ficha->estado] ?? '📌';
                        @endphp
                        <span class="badge bg-{{ $badgeColor }} fs-6 p-3">
                            {{ $estadoIcono }} {{ ucfirst(str_replace('_', ' ', $ficha->estado)) }}
                        </span>
                    </div>
                </div>

                <!-- Contenido principal -->
                <div style="padding: 30px;">
                    
                    <!-- Grid de información principal -->
                    <div class="row mb-4">
                        <!-- Información del equipo -->
                        <div class="col-md-4 mb-3">
                            <div class="info-section">
                                <h4 class="info-title">📟 Equipo</h4>
                                @if($ficha->activo)
                                    <p class="info-text"><strong>Marca/Modelo:</strong> {{ $ficha->activo->marca_modelo }}</p>
                                    <p class="info-text"><strong>Capacidad:</strong> {{ $ficha->activo->capacidad ?? 'N/A' }}</p>
                                    <p class="info-text"><strong>Serial asignado:</strong> 
                                        <code class="bg-light p-1 rounded">{{ $ficha->serial_asignado ?? 'No asignado' }}</code>
                                    </p>
                                    <p class="info-text"><strong>Tipo:</strong> {{ $ficha->activo->tipo_equipo ?? 'N/A' }}</p>
                                @else
                                    <p class="info-text"><strong>Equipo externo:</strong> {{ $ficha->equipo_externo_nombre }}</p>
                                    <p class="text-muted">Este equipo no está registrado en el inventario</p>
                                @endif
                            </div>
                        </div>

                        <!-- Información del técnico -->
                        <div class="col-md-4 mb-3">
                            <div class="info-section">
                                <h4 class="info-title">👨‍🔧 Técnico</h4>
                                @if($ficha->tecnico)
                                    <p class="info-text"><strong>Nombre:</strong> {{ $ficha->tecnico->nombre }} {{ $ficha->tecnico->apellido }}</p>
                                    <p class="info-text"><strong>Rol:</strong> {{ $ficha->tecnico->rol->nombre ?? 'Sin rol' }}</p>
                                    <p class="info-text"><strong>Cargo:</strong> {{ $ficha->tecnico->cargo ?? 'N/A' }}</p>
                                    <p class="info-text"><strong>Departamento:</strong> {{ $ficha->tecnico->departamento ?? 'N/A' }}</p>
                                @else
                                    <p class="text-muted">No hay técnico asignado</p>
                                    @if($ficha->estado != 'completado')
                                        <button type="button" class="btn btn-primary btn-sm mt-2" onclick="openTecnicoModalWithSound()">
                                            + Asignar Técnico
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Fechas -->
                        <div class="col-md-4 mb-3">
                            <div class="info-section">
                                <h4 class="info-title">📅 Fechas</h4>
                                <p class="info-text"><strong>Fecha ingreso:</strong> {{ \Carbon\Carbon::parse($ficha->fecha_ingreso)->format('d/m/Y H:i') }}</p>
                                @if($ficha->fecha_entrega)
                                    <p class="info-text"><strong>Fecha entrega:</strong> {{ \Carbon\Carbon::parse($ficha->fecha_entrega)->format('d/m/Y') }}</p>
                                @endif
                                @if($ficha->costo_reparacion)
                                    <p class="info-text"><strong>Costo reparación:</strong> ${{ number_format($ficha->costo_reparacion, 2) }}</p>
                                @endif
                                <p class="info-text"><strong>Reportado por:</strong> {{ $ficha->usuario_reporta_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Diagnóstico -->
                    <div class="info-section mb-4">
                        <h4 class="info-title">🔍 Diagnóstico</h4>
                        <p class="info-text">{{ $ficha->diagnostico ?? 'Sin diagnóstico registrado' }}</p>
                        @if($ficha->observaciones)
                            <hr class="my-3">
                            <p class="info-text"><strong>Observaciones:</strong> {{ $ficha->observaciones }}</p>
                        @endif
                    </div>

                    <!-- Trabajo realizado -->
                    <div class="info-section mb-4">
                        <h4 class="info-title">🛠️ Trabajo Realizado</h4>
                        @if($ficha->trabajo_realizado)
                            <p class="info-text">{{ $ficha->trabajo_realizado }}</p>
                        @else
                            <p class="text-muted">Aún no se ha registrado trabajo realizado.</p>
                            @if($ficha->estado != 'completado')
                                <button type="button" class="btn btn-success btn-sm mt-2" onclick="openTrabajoModalWithSound()">
                                    + Agregar Trabajo Realizado
                                </button>
                            @endif
                        @endif
                    </div>

                    <!-- Componentes utilizados -->
                    <div class="info-section mb-4">
                        <h4 class="info-title">💾 Componentes Utilizados</h4>
                        @if($ficha->componentes && $ficha->componentes->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Componente</th>
                                            <th>Serial</th>
                                            <th>Cantidad</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ficha->componentes as $detalle)
                                            <tr>
                                                <td>{{ $detalle->activo->marca_modelo ?? 'N/A' }}</td>
                                                <td><code>{{ $detalle->serial_usado ?? 'N/A' }}</code></td>
                                                <td>{{ $detalle->cantidad }}</td>
                                                <td>{{ \Carbon\Carbon::parse($detalle->fecha_salida)->format('d/m/Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No se han utilizado componentes en esta reparación.</p>
                        @endif
                        
                        @if($ficha->estado != 'completado')
                            <button type="button" class="btn btn-info btn-sm mt-2" onclick="openComponenteModalWithSound()">
                                + Agregar Componente
                            </button>
                        @endif
                    </div>

                    <!-- Botón completar -->
                    @if($ficha->estado != 'completado')
                        <div class="text-end mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                            <button type="button" class="btn btn-success btn-lg" onclick="openCompletarModalWithSound()">
                                ✅ Completar Ficha
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Asignar Técnico -->
<div class="modal fade" id="modalTecnico" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">👨‍🔧 Asignar Técnico</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('soporte.asignarTecnico', $ficha) }}" method="POST" id="tecnicoForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Seleccionar técnico</label>
                        <select name="tecnico_id" class="form-select" required>
                            <option value="">Seleccione un técnico...</option>
                            @foreach($tecnicos as $tecnico)
                                <option value="{{ $tecnico->id }}">{{ $tecnico->nombre }} {{ $tecnico->apellido }} - {{ $tecnico->rol->nombre ?? 'Sin rol' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Asignar Técnico</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Agregar Trabajo Realizado -->
<div class="modal fade" id="modalTrabajo" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">🛠️ Agregar Trabajo Realizado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('soporte.actualizarTrabajo', $ficha) }}" method="POST" id="trabajoForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción del trabajo</label>
                        <textarea name="trabajo_realizado" rows="5" class="form-control" required placeholder="Describa el trabajo realizado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Agregar Componente -->
<div class="modal fade" id="modalComponente" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">💾 Agregar Componente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('soporte.agregarComponente', $ficha) }}" method="POST" id="componenteForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Componente</label>
                        <select name="componente_id" id="componente_id" class="form-select" required>
                            <option value="">Seleccione un componente...</option>
                            @foreach($componentesDisponibles as $componente)
                                <option value="{{ $componente->id }}">{{ $componente->marca_modelo }} @if($componente->capacidad)({{ $componente->capacidad }})@endif</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3" id="serialesContainer" style="display: none;">
                        <label class="form-label fw-bold">Serial</label>
                        <select name="serial_id" id="serial_id" class="form-select">
                            <option value="">Seleccione un serial...</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cantidad</label>
                        <input type="number" name="cantidad" value="1" min="1" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción (opcional)</label>
                        <textarea name="descripcion" rows="3" class="form-control" placeholder="Descripción del componente..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">Agregar Componente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Completar Ficha -->
<div class="modal fade" id="modalCompletar" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">✅ Completar Ficha de Soporte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('soporte.completar', $ficha) }}" method="POST" id="completarForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de entrega</label>
                        <input type="date" name="fecha_entrega" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Costo de reparación</label>
                        <input type="number" name="costo_reparacion" step="0.01" min="0" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Completar Ficha</button>
                </div>
            </form>
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
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s, box-shadow 0.3s;
        border: none;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .info-section {
        background: #f8f9fa;
        border-radius: 16px;
        padding: 20px;
        height: 100%;
        transition: all 0.3s ease;
    }
    
    .info-section:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .info-title {
        margin: 0 0 15px 0;
        font-size: 18px;
        color: #4361ee;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 8px;
    }
    
    .info-text {
        margin: 8px 0;
        line-height: 1.6;
    }
    
    .badge {
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }
    
    .toast {
        opacity: 0.95;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 250px;
    }
    
    .btn-sm {
        font-size: 0.8rem;
    }
    
    .table th, .table td {
        vertical-align: middle;
    }
</style>

<script>
// ========== SISTEMA DE SONIDOS MEJORADO ==========
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
                warning: '/sounds/warning.mp3'
            };
            
            for (const [key, url] of Object.entries(soundFiles)) {
                const audio = new Audio(url);
                audio.preload = 'auto';
                audio.volume = this.volume;
                audio.load();
                this.sounds[key] = audio;
                
                audio.addEventListener('canplaythrough', () => {
                    console.log(`Sonido ${key} cargado correctamente`);
                });
                
                audio.addEventListener('error', (e) => {
                    console.warn(`No se pudo cargar el sonido ${key}: ${url}`);
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
            console.log('Sistema de sonidos inicializado');
        } catch (error) {
            console.error('Error inicializando sonidos:', error);
        }
    }
    
    createFallbackSound(type) {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
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
        if (!this.enabled || !this.initialized) {
            return;
        }
        
        const sound = this.sounds[type];
        if (sound) {
            try {
                if (sound.cloneNode) {
                    const soundClone = sound.cloneNode();
                    soundClone.volume = this.volume;
                    soundClone.play().catch(error => {
                        console.log(`Error reproduciendo sonido ${type}:`, error);
                    });
                    soundClone.onended = () => soundClone.remove();
                } 
                else if (typeof sound.play === 'function') {
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
        
        if (this.enabled) {
            this.play('notification');
        }
        
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
        if (type === 'error') {
            alert('❌ Error: ' + message);
        }
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
        setTimeout(() => {
            toastInstance.show();
        }, 200);
    } else {
        toastInstance = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 3000
        });
        toastInstance.show();
    }
}

// ========== FUNCIONES CON SONIDOS PARA MODALES ==========
function openTecnicoModalWithSound() {
    soundManager.play('notification');
    const modal = new bootstrap.Modal(document.getElementById('modalTecnico'));
    modal.show();
}

function openTrabajoModalWithSound() {
    soundManager.play('notification');
    const modal = new bootstrap.Modal(document.getElementById('modalTrabajo'));
    modal.show();
}

function openComponenteModalWithSound() {
    soundManager.play('notification');
    const modal = new bootstrap.Modal(document.getElementById('modalComponente'));
    modal.show();
}

function openCompletarModalWithSound() {
    soundManager.play('warning');
    const modal = new bootstrap.Modal(document.getElementById('modalCompletar'));
    modal.show();
}

// ========== MANEJAR ENVÍOS DE FORMULARIOS CON SONIDOS ==========
document.addEventListener('DOMContentLoaded', function() {
    // Formulario de técnico
    const tecnicoForm = document.getElementById('tecnicoForm');
    if (tecnicoForm) {
        tecnicoForm.addEventListener('submit', function(e) {
            soundManager.play('success');
            showNotification('Asignando técnico...', 'info');
        });
    }
    
    // Formulario de trabajo
    const trabajoForm = document.getElementById('trabajoForm');
    if (trabajoForm) {
        trabajoForm.addEventListener('submit', function(e) {
            soundManager.play('success');
            showNotification('Guardando trabajo realizado...', 'info');
        });
    }
    
    // Formulario de componente
    const componenteForm = document.getElementById('componenteForm');
    if (componenteForm) {
        componenteForm.addEventListener('submit', function(e) {
            soundManager.play('success');
            showNotification('Agregando componente...', 'info');
        });
    }
    
    // Formulario de completar
    const completarForm = document.getElementById('completarForm');
    if (completarForm) {
        completarForm.addEventListener('submit', function(e) {
            soundManager.play('success');
            showNotification('Completando ficha...', 'info');
        });
    }
    
    // Mostrar mensajes de sesión con sonido
    @if(session('success'))
        setTimeout(() => {
            soundManager.play('success');
            showNotification('{{ session('success') }}', 'success');
        }, 500);
    @endif
    
    @if(session('error'))
        setTimeout(() => {
            soundManager.play('error');
            showNotification('{{ session('error') }}', 'error');
        }, 500);
    @endif
});

// ========== CARGAR SERIALES DINÁMICAMENTE ==========
const componenteSelect = document.getElementById('componente_id');
const serialesContainer = document.getElementById('serialesContainer');
const serialSelect = document.getElementById('serial_id');

if (componenteSelect) {
    componenteSelect.addEventListener('change', function() {
        const componenteId = this.value;
        if (componenteId) {
            fetch(`/soporte/api/activo/${componenteId}/componentes`)
                .then(response => response.json())
                .then(data => {
                    if (data.tiene_seriales && data.seriales && data.seriales.length > 0) {
                        serialesContainer.style.display = 'block';
                        serialSelect.innerHTML = '<option value="">Seleccione un serial...</option>';
                        data.seriales.forEach(serial => {
                            serialSelect.innerHTML += `<option value="${serial.id}">${serial.serial}</option>`;
                        });
                    } else {
                        serialesContainer.style.display = 'none';
                        serialSelect.innerHTML = '<option value="">No hay seriales disponibles</option>';
                    }
                })
                .catch(error => {
                    console.error('Error cargando seriales:', error);
                    serialesContainer.style.display = 'none';
                });
        } else {
            serialesContainer.style.display = 'none';
        }
    });
}

// Prueba de sonido (Ctrl+Shift+S)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'S') {
        e.preventDefault();
        soundManager.play('success');
        setTimeout(() => soundManager.play('notification'), 500);
        setTimeout(() => soundManager.play('warning'), 1000);
        setTimeout(() => soundManager.play('error'), 1500);
        showNotification('Prueba de sonidos - Detalle de Ficha', 'success');
    }
});
</script>
@endsection