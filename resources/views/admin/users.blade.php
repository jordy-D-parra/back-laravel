@extends('layouts.dashboard')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-3">👥 Gestión de Usuarios</h2>
                        <p class="text-muted mb-0">Administra los usuarios, sus roles y estados en el sistema</p>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 2rem;">👥</div>
                        <small class="text-muted">Total: {{ $users->count() }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes de éxito/error (respaldo) -->
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

    <!-- Tabla de usuarios -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>Rol Actual</th>
                                <th>Estado</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    {{ $user->nombre }} {{ $user->apellido }}
                                    @if($user->id === Auth::id())
                                        <span class="badge bg-info ms-2">Tú</span>
                                    @endif
                                </td>
                                <td>{{ $user->cedula }}</td>
                                <td>
                                    @if($user->rol)
                                        @php
                                            $badgeClass = match($user->rol->nombre) {
                                                'super_admin' => 'bg-danger',
                                                'admin' => 'bg-warning text-dark',
                                                'worker' => 'bg-primary',
                                                'user', 'usuario' => 'bg-success',
                                                default => 'bg-secondary'
                                            };
                                            $roleIcon = match($user->rol->nombre) {
                                                'super_admin' => '👑',
                                                'admin' => '⚙️',
                                                'worker' => '🔧',
                                                'user', 'usuario' => '👤',
                                                default => '❓'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} fs-6 p-2" id="rol-badge-{{ $user->id }}">
                                            {{ $roleIcon }} {{ ucfirst($user->rol->nombre) }}
                                        </span>
                                    @else
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-danger fs-6 p-2" id="rol-badge-{{ $user->id }}">
                                                ⚠️ SIN ROL
                                            </span>
                                            <button type="button"
                                                    class="btn btn-sm btn-warning"
                                                    onclick="asignarRolUrgente({{ $user->id }}, '{{ addslashes($user->nombre . ' ' . $user->apellido) }}')"
                                                    title="Asignar rol">
                                                🚨 Asignar
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $estadoColors = [
                                            'activo' => 'success',
                                            'pendiente' => 'warning',
                                            'inactivo' => 'danger',
                                            'suspendido' => 'secondary'
                                        ];
                                        $color = $estadoColors[$user->estado_usuario] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }} fs-6 p-2" id="estado-badge-{{ $user->id }}">
                                        @if($user->estado_usuario == 'activo') ✅
                                        @elseif($user->estado_usuario == 'pendiente') ⏳
                                        @elseif($user->estado_usuario == 'inactivo') ❌
                                        @elseif($user->estado_usuario == 'suspendido') ⚠️
                                        @endif
                                        {{ ucfirst($user->estado_usuario) }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($user->fecha_solicitud)->format('d/m/Y') }}</td>
                                <td>
                                    <!-- Botón cambiar rol -->
                                    <button type="button"
                                            class="btn btn-sm btn-primary mb-1 w-100"
                                            onclick="openRoleModalWithSound({{ $user->id }}, '{{ addslashes($user->nombre . ' ' . $user->apellido) }}', '{{ $user->rol->nombre ?? 'user' }}')">
                                        🔄 Cambiar Rol
                                    </button>

                                    <!-- Botón cambiar estado -->
                                    <button type="button"
                                            class="btn btn-sm btn-info mb-1 w-100"
                                            onclick="openEstadoModalWithSound({{ $user->id }}, '{{ addslashes($user->nombre . ' ' . $user->apellido) }}', '{{ $user->estado_usuario }}')">
                                        📌 Cambiar Estado
                                    </button>

                                    <!-- Botón cambiar contraseña -->
                                    <button type="button"
                                            class="btn btn-sm btn-warning w-100"
                                            onclick="openPasswordModalWithSound({{ $user->id }}, '{{ addslashes($user->nombre . ' ' . $user->apellido) }}')">
                                        🔒 Cambiar Pass
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Rol -->
<div class="modal fade" id="roleModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="roleModalTitle">Cambiar Rol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="roleUserId">
                <div class="mb-3">
                    <label class="form-label fw-bold">Seleccionar nuevo rol</label>
                    <select class="form-select" id="roleSelect" required>
                        <option value="">-- Seleccione un rol --</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}">{{ ucfirst($rol->nombre) }} - {{ $rol->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="alert alert-info">
                    <small>
                        <strong>📌 Descripción de roles:</strong><br>
                        • <strong>super_admin:</strong> Acceso total al sistema.<br>
                        • <strong>admin:</strong> Puede administrar usuarios y solicitudes.<br>
                        • <strong>worker:</strong> Puede gestionar préstamos y equipos.<br>
                        • <strong>user:</strong> Solo puede ver y solicitar préstamos.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarCambioRol()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado -->
<div class="modal fade" id="estadoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="estadoModalTitle">Cambiar Estado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="estadoUserId">
                <div class="mb-3">
                    <label class="form-label fw-bold">Seleccionar nuevo estado</label>
                    <select class="form-select" id="estadoSelect" required>
                        <option value="activo">✅ Activo - Puede iniciar sesión normalmente</option>
                        <option value="pendiente">⏳ Pendiente - Esperando aprobación</option>
                        <option value="inactivo">❌ Inactivo - No puede iniciar sesión</option>
                        <option value="suspendido">⚠️ Suspendido - Suspendido temporalmente</option>
                    </select>
                </div>
                <div class="alert alert-info">
                    <small>
                        <strong>📌 Estados disponibles:</strong><br>
                        • <strong>Activo:</strong> Usuario puede iniciar sesión y usar el sistema.<br>
                        • <strong>Pendiente:</strong> Usuario registrado, espera aprobación.<br>
                        • <strong>Inactivo:</strong> Usuario deshabilitado permanentemente.<br>
                        • <strong>Suspendido:</strong> Usuario suspendido temporalmente.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" onclick="confirmarCambioEstado()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal fade" id="passwordModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="passwordModalTitle">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="passwordForm" action="{{ route('admin.reset-password') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="passwordUserId">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control" name="new_password" id="new_password" required minlength="6">
                        <div class="form-text">Mínimo 6 caracteres</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
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
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s;
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
    
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.2em;
    }
    
    /* Animación para el botón de sonido */
    .sound-control-btn {
        transition: all 0.3s ease;
    }
    
    .sound-control-btn:hover {
        transform: scale(1.1);
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
            // Definir los sonidos
            const soundFiles = {
                success: '/sounds/success.mp3',
                error: '/sounds/error.mp3',
                notification: '/sounds/notification.mp3',
                warning: '/sounds/warning.mp3'
            };
            
            // Precargar sonidos
            for (const [key, url] of Object.entries(soundFiles)) {
                const audio = new Audio(url);
                audio.preload = 'auto';
                audio.volume = this.volume;
                audio.load();
                this.sounds[key] = audio;
                
                // Verificar si el sonido se carga correctamente
                audio.addEventListener('canplaythrough', () => {
                    console.log(`Sonido ${key} cargado correctamente`);
                });
                
                audio.addEventListener('error', (e) => {
                    console.warn(`No se pudo cargar el sonido ${key}: ${url}`);
                    // Crear un sonido de fallback usando Web Audio API
                    this.createFallbackSound(key);
                });
            }
            
            // Cargar preferencias
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
    
    // Crear sonido de fallback usando Web Audio API
    createFallbackSound(type) {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            // Configurar según el tipo
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
            console.log(`Sonido ${type} no reproducido (enabled: ${this.enabled}, initialized: ${this.initialized})`);
            return;
        }
        
        const sound = this.sounds[type];
        if (sound) {
            try {
                // Para Audio elements
                if (sound.cloneNode) {
                    const soundClone = sound.cloneNode();
                    soundClone.volume = this.volume;
                    soundClone.play().catch(error => {
                        console.log(`Error reproduciendo sonido ${type}:`, error);
                    });
                    soundClone.onended = () => soundClone.remove();
                } 
                // Para fallback de Web Audio
                else if (typeof sound.play === 'function') {
                    sound.play();
                }
                console.log(`Reproduciendo sonido: ${type}`);
            } catch (error) {
                console.error(`Error al reproducir ${type}:`, error);
            }
        } else {
            console.warn(`Sonido ${type} no encontrado`);
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
        // Verificar si ya existe el botón
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

// ========== SISTEMA DE NOTIFICACIONES MEJORADO ==========
let toastInstance = null;

function showNotification(message, type = 'success') {
    console.log(`Mostrando notificación: ${message} (${type})`);
    
    // Reproducir sonido
    soundManager.play(type);
    
    const toastEl = document.getElementById('notificationToast');
    const toastMessage = document.getElementById('toastMessage');
    
    if (!toastEl || !toastMessage) {
        console.error('Elementos de notificación no encontrados');
        // Fallback: alerta si no hay toast
        if (type === 'error') {
            alert('❌ Error: ' + message);
        }
        return;
    }
    
    // Cambiar color del toast
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
    
    // Mostrar toast
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

// ========== FUNCIONES ACTUALIZADAS CON SONIDOS ==========

// Función para abrir modales con sonido
function openRoleModalWithSound(userId, userName, currentRole) {
    soundManager.play('notification');
    openRoleModal(userId, userName, currentRole);
}

function openEstadoModalWithSound(userId, userName, currentEstado) {
    soundManager.play('notification');
    openEstadoModal(userId, userName, currentEstado);
}

function openPasswordModalWithSound(userId, userName) {
    soundManager.play('notification');
    openPasswordModal(userId, userName);
}

// Funciones originales (sin cambios)
function openRoleModal(userId, userName, currentRole) {
    document.getElementById('roleModalTitle').innerHTML = 'Cambiar Rol - ' + userName;
    document.getElementById('roleUserId').value = userId;
    
    const select = document.getElementById('roleSelect');
    let selected = false;
    
    for(let i = 0; i < select.options.length; i++) {
        const optionText = select.options[i].text.toLowerCase();
        if(currentRole && optionText.includes(currentRole.toLowerCase())) {
            select.options[i].selected = true;
            selected = true;
            break;
        }
    }
    
    if(!selected && currentRole === 'user') {
        for(let i = 0; i < select.options.length; i++) {
            const optionText = select.options[i].text.toLowerCase();
            if(optionText.includes('user') || optionText.includes('usuario')) {
                select.options[i].selected = true;
                break;
            }
        }
    }
    
    const modal = new bootstrap.Modal(document.getElementById('roleModal'));
    modal.show();
}

function openEstadoModal(userId, userName, currentEstado) {
    document.getElementById('estadoModalTitle').innerHTML = 'Cambiar Estado - ' + userName;
    document.getElementById('estadoUserId').value = userId;
    
    const select = document.getElementById('estadoSelect');
    for(let i = 0; i < select.options.length; i++) {
        if(select.options[i].value === currentEstado) {
            select.options[i].selected = true;
            break;
        }
    }
    
    const modal = new bootstrap.Modal(document.getElementById('estadoModal'));
    modal.show();
}

function openPasswordModal(userId, userName) {
    document.getElementById('passwordModalTitle').innerHTML = 'Cambiar Contraseña - ' + userName;
    document.getElementById('passwordUserId').value = userId;
    document.getElementById('new_password').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('passwordModal'));
    modal.show();
}

// ========== CONFIRMAR CAMBIOS CON SONIDOS ==========

async function confirmarCambioRol() {
    const userId = document.getElementById('roleUserId').value;
    const roleId = document.getElementById('roleSelect').value;
    
    if(!roleId) {
        soundManager.play('warning');
        showNotification('Por favor seleccione un rol', 'warning');
        return;
    }
    
    const modalElement = document.getElementById('roleModal');
    const modal = bootstrap.Modal.getInstance(modalElement);
    const btn = modalElement.querySelector('.btn-primary');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    try {
        console.log('Enviando petición para cambiar rol...');
        
        const response = await fetch(`/admin/change-role/${userId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_rol: roleId })
        });
        
        const data = await response.json();
        console.log('Respuesta:', data);
        
        if(data.success) {
            modal.hide();
            showNotification(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Error al cambiar el rol', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch(error) {
        console.error('Error:', error);
        showNotification('Error al cambiar el rol: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function confirmarCambioEstado() {
    const userId = document.getElementById('estadoUserId').value;
    const estado = document.getElementById('estadoSelect').value;
    
    if(!estado) {
        soundManager.play('warning');
        showNotification('Por favor seleccione un estado', 'warning');
        return;
    }
    
    const modalElement = document.getElementById('estadoModal');
    const modal = bootstrap.Modal.getInstance(modalElement);
    const btn = modalElement.querySelector('.btn-info');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    try {
        console.log('Enviando petición para cambiar estado...');
        
        const response = await fetch(`/admin/change-status`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                user_id: userId,
                estado_usuario: estado 
            })
        });
        
        const data = await response.json();
        console.log('Respuesta:', data);
        
        if(data.success) {
            modal.hide();
            showNotification(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Error al cambiar el estado', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch(error) {
        console.error('Error:', error);
        showNotification('Error al cambiar el estado: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function confirmarAsignacionRolUrgente(userId) {
    const select = document.getElementById('rolUrgenteSelect');
    const roleId = select?.value;
    
    if(!roleId) {
        soundManager.play('warning');
        showNotification('Por favor seleccione un rol', 'warning');
        return;
    }
    
    const modalElement = document.getElementById('rolUrgenteModal');
    if (!modalElement) return;
    
    const btn = modalElement.querySelector('.btn-danger');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Asignando...';
    
    try {
        const response = await fetch(`/admin/change-role/${userId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_rol: roleId })
        });
        
        const data = await response.json();
        
        if(data.success) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            modal.hide();
            showNotification(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Error al asignar el rol', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch(error) {
        console.error('Error:', error);
        showNotification('Error al asignar el rol: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function asignarRolUrgente(userId, userName) {
    soundManager.play('warning');
    
    const modalHtml = `
        <div class="modal fade" id="rolUrgenteModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">🚨 ASIGNAR ROL URGENTE - ${userName}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>⚠️ Este usuario NO tiene un rol asignado!</strong><br>
                            Sin un rol, no podrá acceder correctamente al sistema.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Seleccionar rol:</label>
                            <select class="form-select form-select-lg" id="rolUrgenteSelect" required>
                                <option value="">-- Seleccione un rol --</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}">
                                        {{ ucfirst($rol->nombre) }} - {{ $rol->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" onclick="confirmarAsignacionRolUrgente(${userId})">✅ Asignar Rol Ahora</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const existingModal = document.getElementById('rolUrgenteModal');
    if(existingModal) {
        existingModal.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById('rolUrgenteModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// ========== MANEJAR FORMULARIO DE CONTRASEÑA ==========
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - Inicializando handlers');
    
    // Agregar meta tag CSRF si no existe
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = '{{ csrf_token() }}';
        document.head.appendChild(meta);
    }
    
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        console.log('Formulario de contraseña encontrado');
        
        // Remover event listener anterior si existe
        const newForm = passwordForm.cloneNode(true);
        passwordForm.parentNode.replaceChild(newForm, passwordForm);
        
        newForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Formulario de contraseña enviado');
            
            const userId = document.getElementById('passwordUserId')?.value;
            const newPassword = document.getElementById('new_password')?.value;
            
            if(!newPassword || newPassword.length < 6) {
                soundManager.play('warning');
                showNotification('La contraseña debe tener al menos 6 caracteres', 'warning');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Actualizando...';
            
            try {
                const formData = new FormData(this);
                
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                console.log('Respuesta contraseña:', data);
                
                if(data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
                    if (modal) modal.hide();
                    showNotification(data.message, 'success');
                    this.reset();
                    
                    // Opcional: recargar después de 2 segundos
                    // setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification(data.message || 'Error al cambiar la contraseña', 'error');
                }
            } catch(error) {
                console.error('Error:', error);
                showNotification('Error al cambiar la contraseña: ' + error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
    
    // Mostrar mensajes de sesión con sonido
    @if(session('success'))
        console.log('Mensaje de éxito detectado');
        setTimeout(() => {
            soundManager.play('success');
            showNotification('{{ session('success') }}', 'success');
        }, 500);
    @endif
    
    @if(session('error'))
        console.log('Mensaje de error detectado');
        setTimeout(() => {
            soundManager.play('error');
            showNotification('{{ session('error') }}', 'error');
        }, 500);
    @endif
    
    @if(session('warning'))
        console.log('Mensaje de warning detectado');
        setTimeout(() => {
            soundManager.play('warning');
            showNotification('{{ session('warning') }}', 'warning');
        }, 500);
    @endif
});

// Prueba de sonido (opcional, presiona Ctrl+Shift+S para probar)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'S') {
        e.preventDefault();
        console.log('Probando sonidos...');
        soundManager.play('success');
        setTimeout(() => soundManager.play('notification'), 500);
        setTimeout(() => soundManager.play('warning'), 1000);
        setTimeout(() => soundManager.play('error'), 1500);
        showNotification('Prueba de sonidos', 'success');
    }
});
</script>
@endsection