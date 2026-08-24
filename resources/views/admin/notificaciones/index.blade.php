@extends('layouts.dashboard')

@section('title', 'Mis Notificaciones')

@section('styles')
<style>
    .notificacion-item {
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    .notificacion-item:hover {
        background: #f8f9fc;
    }
    .notificacion-item.no-leida {
        border-left-color: #1e3c72;
        background: #f0f4ff;
    }
    .notificacion-item.no-leida:hover {
        background: #e8edf8;
    }
    .notificacion-item.leida {
        border-left-color: #28a745;
        opacity: 0.7;
    }
    .badge-tipo {
        font-size: 0.65rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge-tipo.sistema { background: #e9ecef; color: #495057; }
    .badge-tipo.solicitud { background: #d4edda; color: #155724; }
    .badge-tipo.prestamo { background: #cce5ff; color: #004085; }
    .badge-tipo.recordatorio { background: #fff3cd; color: #856404; }
    .badge-tipo.alerta { background: #f8d7da; color: #721c24; }
    .fecha-notificacion {
        font-size: 0.7rem;
        color: #6c757d;
    }
    .btn-marcar-leida {
        padding: 0.15rem 0.6rem;
        font-size: 0.7rem;
        border-radius: 20px;
        border: 1px solid #ced4da;
        background: white;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-marcar-leida:hover {
        background: #1e3c72;
        color: white;
        border-color: #1e3c72;
    }
    .btn-marcar-leida.marcada {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }
    .page-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 16px;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .page-header h4 {
        color: white;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .page-header h4 svg {
        stroke: white;
    }
    .page-header p {
        color: rgba(255,255,255,0.8);
        font-size: 0.85rem;
        margin: 0;
    }
    .page-header .badge-count-header {
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 0.15rem 0.6rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                Mis Notificaciones
                <span class="badge-count-header">{{ $noLeidas }} sin leer</span>
            </h4>
            <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem; margin: 0;">
                Todas las notificaciones del sistema
            </p>
        </div>
        @if($noLeidas > 0)
        <div>
            <button onclick="marcarTodasComoLeidas()" class="btn btn-light" style="border-radius: 30px; font-weight: 500;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1" style="display:inline;">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Marcar todas como leídas
            </button>
        </div>
        @endif
    </div>

    <!-- Lista de notificaciones -->
    <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-0">
            @if($notificaciones->isEmpty())
                <div class="text-center py-5">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" style="margin-bottom: 16px;">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <h5 class="text-muted">No tienes notificaciones</h5>
                    <p class="text-muted small">Todas tus notificaciones aparecerán aquí</p>
                </div>
            @else
                @foreach($notificaciones as $notificacion)
                    <div class="notificacion-item p-3 border-bottom {{ $notificacion->leida ? 'leida' : 'no-leida' }}" data-id="{{ $notificacion->id }}">
                        <div class="d-flex align-items-start gap-3">
                            <!-- Icono -->
                            <div style="width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: {{ $notificacion->tipo === 'solicitud' ? 'rgba(30, 126, 52, 0.1)' : ($notificacion->tipo === 'prestamo' ? 'rgba(30, 60, 114, 0.1)' : 'rgba(246, 194, 62, 0.1)') }}; color: {{ $notificacion->tipo === 'solicitud' ? '#1e7e34' : ($notificacion->tipo === 'prestamo' ? '#1e3c72' : '#d97706') }};">
                                @if($notificacion->tipo === 'solicitud')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                                        <path d="M22 7l-10 7L2 7"/>
                                    </svg>
                                @elseif($notificacion->tipo === 'prestamo')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                                        <line x1="8" y1="21" x2="16" y2="21"/>
                                        <line x1="12" y1="17" x2="12" y2="21"/>
                                    </svg>
                                @else
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                @endif
                            </div>

                            <!-- Contenido -->
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div>
                                        <span class="badge-tipo {{ $notificacion->tipo }}">{{ ucfirst($notificacion->tipo) }}</span>
                                        <span class="fw-semibold ms-2" style="color: #1e3c72;">{{ $notificacion->titulo }}</span>
                                    </div>
                                    <span class="fecha-notificacion">{{ $notificacion->fecha_envio->diffForHumans() }}</span>
                                </div>
                                <p class="mb-1 text-muted" style="font-size: 0.9rem; white-space: pre-line;">{{ $notificacion->mensaje }}</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="fecha-notificacion">{{ $notificacion->fecha_envio->format('d/m/Y H:i') }}</span>
                                    <div>
                                        @if($notificacion->url)
                                            <a href="{{ url($notificacion->url) }}" class="btn btn-sm btn-primary-dark me-2" style="color: #fff; border-radius: 20px; padding: 0.2rem 1rem; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                                                Ver
                                            </a>
                                        @endif
                                        @if(!$notificacion->leida)
                                            <button onclick="marcarComoLeida({{ $notificacion->id }}, this)" class="btn-marcar-leida">
                                                Marcar como leída
                                            </button>
                                        @else
                                            <span class="text-success" style="font-size: 0.75rem;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2.5" class="me-1" style="display:inline;">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                                Leída
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        @if($notificaciones->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $notificaciones->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function marcarComoLeida(id, btn) {
    fetch('/admin/notificaciones/' + id + '/leer', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = btn.closest('.notificacion-item');
            item.classList.remove('no-leida');
            item.classList.add('leida');
            
            // Reemplazar botón con badge de leída
            const container = btn.closest('.d-flex');
            if (container) {
                container.innerHTML = `
                    <span class="text-success" style="font-size: 0.75rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2.5" class="me-1" style="display:inline;">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Leída
                    </span>
                `;
            }
            
            // Actualizar contador de la campana
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                const current = parseInt(badge.textContent);
                if (current > 1) {
                    badge.textContent = current - 1;
                } else {
                    badge.remove();
                }
            }
            
            // Actualizar contador del header
            const headerBadge = document.querySelector('.badge-count-header');
            if (headerBadge) {
                const current = parseInt(headerBadge.textContent);
                if (current > 1) {
                    headerBadge.textContent = (current - 1) + ' sin leer';
                } else {
                    headerBadge.textContent = '0 sin leer';
                }
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

function marcarTodasComoLeidas() {
    if (!confirm('¿Marcar todas las notificaciones como leídas?')) return;
    
    const btn = document.querySelector('.page-header .btn-light');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';
    btn.disabled = true;
    
    fetch('/admin/notificaciones/marcar-todas-leidas', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error))
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>
@endsection