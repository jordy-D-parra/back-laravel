@extends('layouts.dashboard')

@section('title', 'Soporte Técnico')

@section('content')
<div class="container-fluid px-4">
    <!-- Encabezado con stat-card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-3">🔧 Soporte Técnico</h2>
                        <p class="text-muted mb-0">Gestión de mantenimiento y reparación de equipos</p>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 2rem;">🔧</div>
                        <small class="text-muted">Total: {{ $fichas->total() }}</small>
                    </div>
                </div>
            </div>
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

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card text-center">
                <div style="font-size: 2.5rem;">⏳</div>
                <div class="display-6 fw-bold mb-2">{{ $estadisticas['pendientes'] ?? 0 }}</div>
                <div class="text-muted">Pendientes</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card text-center">
                <div style="font-size: 2.5rem;">🔍</div>
                <div class="display-6 fw-bold mb-2">{{ $estadisticas['en_proceso'] ?? 0 }}</div>
                <div class="text-muted">En Proceso</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card text-center">
                <div style="font-size: 2.5rem;">✅</div>
                <div class="display-6 fw-bold mb-2">{{ $estadisticas['completados'] ?? 0 }}</div>
                <div class="text-muted">Completados</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card text-center">
                <div style="font-size: 2.5rem;">📅</div>
                <div class="display-6 fw-bold mb-2">{{ $estadisticas['total_mes'] ?? 0 }}</div>
                <div class="text-muted">Este Mes</div>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" onclick="openExternoSoportewithSound()">
                        📱 Equipo Externo
                    </button>
                    <button type="button" class="btn btn-primary" onclick="openCreateFichawithSound()">
                        + Nueva Ficha
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de fichas de soporte -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Equipo</th>
                                <th>Serial</th>
                                <th>Técnico</th>
                                <th>Fecha Ingreso</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fichas as $ficha)
                            <tr>
                                <td>#{{ $ficha->id }}</td>
                                <td>
                                    @if($ficha->activo)
                                        {{ $ficha->activo->marca_modelo }}
                                    @else
                                        <span class="text-muted">📱 {{ $ficha->equipo_externo_nombre ?? 'Equipo Externo' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ficha->serial_asignado)
                                        <code class="bg-light p-1 rounded">{{ $ficha->serial_asignado }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ficha->tecnico)
                                        {{ $ficha->tecnico->nombre }} {{ $ficha->tecnico->apellido }}
                                    @else
                                        <span class="text-muted">No asignado</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($ficha->fecha_ingreso)->format('d/m/Y') }}</td>
                                <td>
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
                                        $color = $estadoColors[$ficha->estado] ?? 'secondary';
                                        $icono = $estadoIconos[$ficha->estado] ?? '📌';
                                    @endphp
                                    <span class="badge bg-{{ $color }} fs-6 p-2">
                                        {{ $icono }} {{ ucfirst(str_replace('_', ' ', $ficha->estado)) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('soporte.show', $ficha->id) }}" 
                                       class="btn btn-sm btn-info w-100"
                                       onclick="playSoundAndRedirect()">
                                        👁️ Ver Detalle
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div style="font-size: 3rem;">📭</div>
                                    <p class="text-muted mt-3">No hay fichas de soporte registradas</p>
                                    <button type="button" class="btn btn-primary mt-2" onclick="openCreateFichawithSound()">
                                        Crear primera ficha
                                    </button>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $fichas->links() }}
                </div>
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
        transition: transform 0.3s, box-shadow 0.3s;
        border: none;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .btn-sm {
        font-size: 0.8rem;
    }

    .table th, .table td {
        vertical-align: middle;
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
                warning: '/sounds/warning.mp3'
            };
            
            for (const [key, url] of Object.entries(soundFiles)) {
                const audio = new Audio(url);
                audio.preload = 'auto';
                audio.volume = this.volume;
                audio.load();
                this.sounds[key] = audio;
                
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
                case 'success': frequency = 880; duration = 0.2; break;
                case 'error': frequency = 440; duration = 0.5; break;
                case 'warning': frequency = 660; duration = 0.4; break;
                default: frequency = 528; duration = 0.3;
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
                    soundClone.play().catch(error => console.log(error));
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
            if (sound.volume !== undefined) sound.volume = this.volume;
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
                <button id="soundControlBtn" class="btn btn-secondary rounded-circle shadow"
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

const soundManager = new SoundManager();

// ========== SISTEMA DE NOTIFICACIONES ==========
let toastInstance = null;

function showNotification(message, type = 'success') {
    soundManager.play(type);
    const toastEl = document.getElementById('notificationToast');
    const toastMessage = document.getElementById('toastMessage');
    if (!toastEl || !toastMessage) return;
    
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
    if (type === 'success') toastEl.classList.add('bg-success');
    else if (type === 'error') toastEl.classList.add('bg-danger');
    else if (type === 'warning') toastEl.classList.add('bg-warning');
    else toastEl.classList.add('bg-info');
    
    toastMessage.textContent = message;
    
    if (toastInstance) {
        toastInstance.hide();
        setTimeout(() => toastInstance.show(), 200);
    } else {
        toastInstance = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
        toastInstance.show();
    }
}

// ========== FUNCIONES PRINCIPALES ==========
function openExternoSoportewithSound() {
    soundManager.play('notification');
    window.location.href = '{{ route("soporte.externo") }}';
}

function openCreateFichawithSound() {
    soundManager.play('notification');
    window.location.href = '{{ route("soporte.create") }}';
}

function playSoundAndRedirect() {
    soundManager.play('notification');
    // El sonido se reproduce y luego redirige (el href del enlace hace la redirección)
    return true;
}

// Mostrar mensajes de sesión
document.addEventListener('DOMContentLoaded', function() {
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

// Prueba de sonido (Ctrl+Shift+S)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'S') {
        e.preventDefault();
        soundManager.play('success');
        setTimeout(() => soundManager.play('notification'), 500);
        setTimeout(() => soundManager.play('warning'), 1000);
        setTimeout(() => soundManager.play('error'), 1500);
        showNotification('Prueba de sonidos - Soporte Técnico', 'success');
    }
});
</script>
@endsection