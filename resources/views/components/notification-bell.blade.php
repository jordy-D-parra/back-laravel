{{-- resources/views/components/notification-bell.blade.php --}}
@php
    use App\Services\NotificacionService;
    $notificacionService = app(NotificacionService::class);
    $noLeidas = $notificacionService->countNoLeidas(auth()->user());
    $notificaciones = $notificacionService->getNoLeidas(auth()->user())->take(10);
@endphp

<div class="notification-bell-container" style="position: relative; display: inline-block;">
    <button class="notification-bell-btn" id="notificationBellBtn" style="
        background: transparent;
        border: none;
        cursor: pointer;
        position: relative;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.3s ease;
        color: #1e3c72;
    ">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        @if($noLeidas > 0)
            <span class="notification-badge" style="
                position: absolute;
                top: -2px;
                right: -2px;
                background: #ef4444;
                color: white;
                font-size: 10px;
                font-weight: 700;
                min-width: 18px;
                height: 18px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0 4px;
                border: 2px solid white;
                animation: bellPulse 2s infinite;
            ">{{ $noLeidas > 99 ? '99+' : $noLeidas }}</span>
        @endif
    </button>

    <div class="notification-dropdown" id="notificationDropdown" style="
        position: absolute;
        right: 0;
        top: calc(100% + 8px);
        width: 380px;
        max-height: 420px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        border: 1px solid #e9ecef;
        overflow: hidden;
        display: none;
        z-index: 1000;
    ">
        <div class="notification-dropdown-header" style="
            padding: 16px 20px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        ">
            <span style="font-weight: 600; font-size: 15px;">
                🔔 Notificaciones
                <span style="
                    background: rgba(255,255,255,0.2);
                    padding: 1px 10px;
                    border-radius: 20px;
                    font-size: 11px;
                    margin-left: 6px;
                ">{{ $noLeidas }}</span>
            </span>
            @if($noLeidas > 0)
                <button onclick="marcarTodasComoLeidas()" style="
                    background: rgba(255,255,255,0.15);
                    border: none;
                    color: white;
                    font-size: 12px;
                    padding: 4px 12px;
                    border-radius: 20px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                ">
                    Marcar todas
                </button>
            @endif
        </div>

        <div class="notification-list" style="
            overflow-y: auto;
            max-height: 340px;
        ">
            @if($notificaciones->isEmpty())
                <div style="
                    text-align: center;
                    padding: 40px 20px;
                    color: #6c757d;
                ">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" style="margin-bottom: 12px;">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <p style="margin: 0; font-weight: 500;">No hay notificaciones pendientes</p>
                    <p style="font-size: 13px; margin: 4px 0 0;">Todas las notificaciones están leídas</p>
                </div>
            @else
                @foreach($notificaciones as $notificacion)
                    <div class="notification-item" data-id="{{ $notificacion->id }}" style="
                        padding: 14px 20px;
                        border-bottom: 1px solid #f1f5f9;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        display: flex;
                        gap: 12px;
                        align-items: flex-start;
                        background: #f8fafc;
                    ">
                        <div class="notification-icon" style="
                            width: 36px;
                            height: 36px;
                            border-radius: 10px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                            background: {{ $notificacion->tipo === 'solicitud' ? 'rgba(30, 126, 52, 0.1)' : ($notificacion->tipo === 'prestamo' ? 'rgba(30, 60, 114, 0.1)' : 'rgba(246, 194, 62, 0.1)') }};
                            color: {{ $notificacion->tipo === 'solicitud' ? '#1e7e34' : ($notificacion->tipo === 'prestamo' ? '#1e3c72' : '#d97706') }};
                        ">
                            @if($notificacion->tipo === 'solicitud')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <path d="M22 7l-10 7L2 7"/>
                                </svg>
                            @elseif($notificacion->tipo === 'prestamo')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                                    <line x1="8" y1="21" x2="16" y2="21"/>
                                    <line x1="12" y1="17" x2="12" y2="21"/>
                                </svg>
                            @else
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            @endif
                        </div>

                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 14px; color: #0f172a;">
                                {{ $notificacion->titulo }}
                            </div>
                            <div style="font-size: 13px; color: #64748b; line-height: 1.4; margin-top: 2px;">
                                {{ Str::limit($notificacion->mensaje, 80) }}
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
                                {{ $notificacion->fecha_envio->diffForHumans() }}
                            </div>
                        </div>

                        <div style="display: flex; gap: 4px; align-items: center; flex-shrink: 0;">
                            @if($notificacion->url)
                                <a href="{{ url($notificacion->url) }}" style="
                                    color: #1e3c72;
                                    font-size: 12px;
                                    font-weight: 500;
                                    text-decoration: none;
                                    padding: 2px 8px;
                                    border-radius: 6px;
                                    background: rgba(30, 60, 114, 0.08);
                                ">
                                    Ver
                                </a>
                            @endif
                            <button onclick="marcarNotificacionComoLeida({{ $notificacion->id }}, this)" style="
                                color: #6c757d;
                                font-size: 12px;
                                font-weight: 500;
                                text-decoration: none;
                                padding: 2px 8px;
                                border-radius: 6px;
                                border: 1px solid #e9ecef;
                                background: white;
                                cursor: pointer;
                                transition: all 0.2s ease;
                            ">
                                ✓ Leída
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        @if($noLeidas > 0)
            <div style="
                padding: 10px 20px;
                border-top: 1px solid #e9ecef;
                text-align: center;
                background: #f8f9fc;
            ">
                <a href="{{ route('admin.notificaciones.index') }}" style="
                    color: #1e3c72;
                    font-size: 13px;
                    font-weight: 500;
                    text-decoration: none;
                ">
                    Ver todas las notificaciones
                </a>
            </div>
        @endif
    </div>
</div>

<style>
    @keyframes bellPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.15); }
    }

    .notification-item:hover {
        background: #f1f5f9 !important;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-dropdown::-webkit-scrollbar {
        width: 4px;
    }

    .notification-dropdown::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .notification-dropdown::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .notification-dropdown::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .notification-item button:hover {
        background: #1e3c72 !important;
        color: white !important;
        border-color: #1e3c72 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bellBtn = document.getElementById('notificationBellBtn');
        const dropdown = document.getElementById('notificationDropdown');

        if (bellBtn && dropdown) {
            bellBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isVisible = dropdown.style.display === 'block';
                dropdown.style.display = isVisible ? 'none' : 'block';
            });

            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target) && e.target !== bellBtn) {
                    dropdown.style.display = 'none';
                }
            });
        }
    });

    function marcarNotificacionComoLeida(id, btn) {
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
                const item = btn.closest('.notification-item');
                if (item) {
                    item.style.background = '#ffffff';
                    item.style.opacity = '0.7';
                    
                    const btns = item.querySelectorAll('button');
                    btns.forEach(b => {
                        if (b.textContent.includes('Leída')) {
                            b.remove();
                        }
                    });
                }
                
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    const current = parseInt(badge.textContent);
                    if (current > 1) {
                        badge.textContent = current - 1;
                    } else {
                        badge.remove();
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function marcarTodasComoLeidas() {
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
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>