@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('styles')
    @vite(['resources/css/dashboard-home.css'])
    <style>
        /* ============================================
           ESTILOS ADICIONALES PARA EL DASHBOARD MODERNO
           ============================================ */

        /* ---- Tarjeta de bienvenida mejorada ---- */
        .welcome-card {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: 10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }

        .welcome-card .card-body {
            position: relative;
            z-index: 1;
        }

        .welcome-card .welcome-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .welcome-card .welcome-icon svg {
            stroke: white;
            width: 28px;
            height: 28px;
        }

        /* ---- Tarjetas de estadísticas modernas ---- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card-modern {
            background: white;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            border: 1px solid rgba(30, 60, 114, 0.06);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: default;
        }

        .stat-card-modern:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(30, 60, 114, 0.12);
            border-color: rgba(30, 60, 114, 0.15);
        }

        .stat-card-modern .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .stat-card-modern .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .stat-card-modern:hover .stat-icon {
            transform: scale(1.05) rotate(-3deg);
        }

        .stat-card-modern .stat-icon.blue {
            background: rgba(30, 60, 114, 0.1);
            color: #1e3c72;
        }

        .stat-card-modern .stat-icon.green {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .stat-card-modern .stat-icon.orange {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .stat-card-modern .stat-icon.red {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .stat-card-modern .stat-icon.purple {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .stat-card-modern .stat-icon.cyan {
            background: rgba(6, 182, 212, 0.1);
            color: #06b6d4;
        }

        .stat-card-modern .stat-icon svg {
            width: 24px;
            height: 24px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
        }

        .stat-card-modern .stat-badge {
            font-size: 0.6rem;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 20px;
            background: rgba(30, 60, 114, 0.08);
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card-modern .stat-badge.up {
            background: rgba(34, 197, 94, 0.12);
            color: #16a34a;
        }

        .stat-card-modern .stat-badge.down {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
        }

        .stat-card-modern .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .stat-card-modern .stat-label {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card-modern .stat-change {
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 4px;
        }

        .stat-card-modern .stat-change.positive {
            color: #16a34a;
        }

        .stat-card-modern .stat-change.negative {
            color: #dc2626;
        }

        /* Línea decorativa en la parte inferior de la tarjeta */
        .stat-card-modern .stat-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: 0 0 16px 16px;
        }

        .stat-card-modern .stat-bar.blue { background: linear-gradient(90deg, #1e3c72, #2a5298); }
        .stat-card-modern .stat-bar.green { background: linear-gradient(90deg, #22c55e, #4ade80); }
        .stat-card-modern .stat-bar.orange { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .stat-card-modern .stat-bar.red { background: linear-gradient(90deg, #ef4444, #f87171); }
        .stat-card-modern .stat-bar.purple { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
        .stat-card-modern .stat-bar.cyan { background: linear-gradient(90deg, #06b6d4, #22d3ee); }

        /* ---- Módulos Rápidos ---- */
        .quick-modules {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .quick-module {
            background: white;
            border-radius: 14px;
            padding: 1.25rem 1rem;
            text-align: center;
            border: 1px solid rgba(30, 60, 114, 0.06);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .quick-module:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(30, 60, 114, 0.1);
            border-color: rgba(30, 60, 114, 0.15);
        }

        .quick-module .module-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(30, 60, 114, 0.08);
            color: #1e3c72;
            transition: all 0.3s ease;
        }

        .quick-module:hover .module-icon {
            background: #1e3c72;
            color: white;
            transform: scale(1.05);
        }

        .quick-module .module-icon svg {
            width: 22px;
            height: 22px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
        }

        .quick-module .module-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #0f172a;
        }

        .quick-module .module-desc {
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .quick-module.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ---- Actividad Reciente ---- */
        .activity-section {
            background: white;
            border-radius: 16px;
            border: 1px solid rgba(30, 60, 114, 0.06);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .activity-section .section-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-section .section-header h5 {
            font-weight: 600;
            color: #0f172a;
            margin: 0;
            font-size: 1rem;
        }

        .activity-section .section-header .badge-count {
            background: rgba(30, 60, 114, 0.08);
            color: #1e3c72;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid #f8fafc;
            transition: all 0.2s ease;
        }

        .activity-item:hover {
            background: #f8fafc;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-item .activity-icon.blue { background: rgba(30, 60, 114, 0.1); color: #1e3c72; }
        .activity-item .activity-icon.green { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .activity-item .activity-icon.orange { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .activity-item .activity-icon.red { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .activity-item .activity-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

        .activity-item .activity-icon svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .activity-item .activity-content {
            flex: 1;
        }

        .activity-item .activity-text {
            font-size: 0.85rem;
            color: #0f172a;
        }

        .activity-item .activity-text strong {
            color: #1e3c72;
        }

        .activity-item .activity-time {
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .activity-item .activity-status {
            font-size: 0.6rem;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .activity-item .activity-status.pending {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
        }

        .activity-item .activity-status.completed {
            background: rgba(34, 197, 94, 0.12);
            color: #16a34a;
        }

        .activity-item .activity-status.cancelled {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
        }

        /* ---- Responsive ---- */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-modules {
                grid-template-columns: repeat(2, 1fr);
            }

            .welcome-card .welcome-icon {
                width: 44px;
                height: 44px;
            }

            .activity-item {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .activity-item .activity-status {
                margin-left: auto;
            }
        }

        @media (max-width: 480px) {
            .quick-modules {
                grid-template-columns: 1fr;
            }
        }

        /* ---- Animaciones ---- */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card-modern {
            animation: fadeInUp 0.5s ease forwards;
        }

        .stat-card-modern:nth-child(1) { animation-delay: 0.05s; }
        .stat-card-modern:nth-child(2) { animation-delay: 0.1s; }
        .stat-card-modern:nth-child(3) { animation-delay: 0.15s; }
        .stat-card-modern:nth-child(4) { animation-delay: 0.2s; }
        .stat-card-modern:nth-child(5) { animation-delay: 0.25s; }
        .stat-card-modern:nth-child(6) { animation-delay: 0.3s; }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4">

    <!-- ========================================== -->
    <!-- TARJETA DE BIENVENIDA MEJORADA            -->
    <!-- ========================================== -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="welcome-card">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="welcome-icon">
                                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                            <h2 class="text-white mb-1">
                                ¡Bienvenido, {{ Auth::user()->trabajador->nombre }} {{ Auth::user()->trabajador->apellido }}!
                            </h2>
                            <p class="text-white-50 mb-0">
                                {{ now()->format('l, d \\d\\e F \\d\\e Y') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill" style="font-size: 0.8rem; background: rgba(255,255,255,0.15) !important; color: white !important;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1" style="display: inline;">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                {{ now()->format('H:i') }} hrs
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ESTADÍSTICAS (solo con permisos)           -->
    <!-- ========================================== -->
    @php
        use App\Models\Usuario;
        use App\Models\Trabajador;
        use App\Models\Rol;
        use App\Models\Solicitud;
        use App\Models\Prestamo;
        use App\Models\FichaSoporte;

        $usuariosActivos = Usuario::where('status', 'activo')->count();
        $usuariosInactivos = Usuario::where('status', 'inactivo')->count();
        $totalTrabajadores = Trabajador::count();
        $totalRoles = Rol::count();
        $pendientesCambio = Usuario::where('must_change_password', true)->count();

        // Nuevas estadísticas
        $solicitudesPendientes = Solicitud::where('estado_solicitud', 'pendiente')->count();
        $prestamosActivos = Prestamo::whereIn('estado', ['entregado', 'extendido'])->count();
        $fichasEnProceso = FichaSoporte::where('estado', 'en_proceso')->count();
    @endphp

    <div class="stats-grid mt-4">

        @if(auth()->user()->hasPermission('ver-usuarios'))
        <!-- Usuarios Activos -->
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon blue">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <span class="stat-badge up">+{{ $usuariosActivos - ($usuariosInactivos ?? 0) }}</span>
            </div>
            <div class="stat-number">{{ $usuariosActivos }}</div>
            <div class="stat-label">Usuarios Activos</div>
            <div class="stat-bar blue"></div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('ver-trabajadores'))
        <!-- Trabajadores -->
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon green">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                </div>
                <span class="stat-badge">Total</span>
            </div>
            <div class="stat-number">{{ $totalTrabajadores }}</div>
            <div class="stat-label">Trabajadores Registrados</div>
            <div class="stat-bar green"></div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('ver-solicitudes'))
        <!-- Solicitudes Pendientes -->
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon orange">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M22 7l-10 7L2 7"/>
                    </svg>
                </div>
                <span class="stat-badge {{ $solicitudesPendientes > 0 ? 'up' : '' }}">Pendientes</span>
            </div>
            <div class="stat-number" style="color: {{ $solicitudesPendientes > 0 ? '#f59e0b' : '#0f172a' }}">
                {{ $solicitudesPendientes }}
            </div>
            <div class="stat-label">Solicitudes Pendientes</div>
            <div class="stat-bar orange"></div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('ver-prestamos'))
        <!-- Préstamos Activos -->
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon purple">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <span class="stat-badge up">Activos</span>
            </div>
            <div class="stat-number">{{ $prestamosActivos }}</div>
            <div class="stat-label">Préstamos en Curso</div>
            <div class="stat-bar purple"></div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('ver-fichas-soporte'))
        <!-- Fichas de Soporte -->
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon cyan">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <span class="stat-badge {{ $fichasEnProceso > 0 ? 'up' : '' }}">En Proceso</span>
            </div>
            <div class="stat-number" style="color: {{ $fichasEnProceso > 0 ? '#06b6d4' : '#0f172a' }}">
                {{ $fichasEnProceso }}
            </div>
            <div class="stat-label">Fichas de Soporte</div>
            <div class="stat-bar cyan"></div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('ver-usuarios'))
        <!-- Pendientes Cambio Contraseña -->
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon red">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                </div>
                <span class="stat-badge {{ $pendientesCambio > 0 ? 'down' : '' }}">
                    {{ $pendientesCambio > 0 ? '' : '✓' }}
                </span>
            </div>
            <div class="stat-number" style="color: {{ $pendientesCambio > 0 ? '#ef4444' : '#0f172a' }}">
                {{ $pendientesCambio }}
            </div>
            <div class="stat-label">Pendientes Cambio Clave</div>
            <div class="stat-bar red"></div>
        </div>
        @endif

    </div>

    <!-- ========================================== -->
    <!-- MÓDULOS RÁPIDOS                            -->
    <!-- ========================================== -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0" style="color: #1e3c72;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="9" y1="3" x2="9" y2="21"/>
                    </svg>
                    Acceso Rápido
                </h6>
                <small class="text-muted">Módulos principales</small>
            </div>
            <div class="quick-modules">

                @if(auth()->user()->hasPermission('ver-usuarios'))
                <a href="{{ route('admin.usuarios.index') }}" class="quick-module">
                    <div class="module-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <span class="module-name">Usuarios</span>
                    <span class="module-desc">Gestionar accesos</span>
                </a>
                @endif

                @if(auth()->user()->hasPermission('ver-trabajadores'))
                <a href="{{ route('admin.trabajadores.index') }}" class="quick-module">
                    <div class="module-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                    </div>
                    <span class="module-name">Trabajadores</span>
                    <span class="module-desc">Personal del depto.</span>
                </a>
                @endif

                @if(auth()->user()->hasPermission('ver-activos') || auth()->user()->hasPermission('ver-componentes'))
                <a href="{{ route('admin.inventario.index') }}" class="quick-module">
                    <div class="module-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                    </div>
                    <span class="module-name">Inventario</span>
                    <span class="module-desc">Activos y componentes</span>
                </a>
                @endif

                @if(auth()->user()->hasPermission('ver-prestamos'))
                <a href="{{ route('admin.prestamos.index') }}" class="quick-module">
                    <div class="module-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <rect x="2" y="3" width="20" height="14" rx="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <span class="module-name">Préstamos</span>
                    <span class="module-desc">Gestión de préstamos</span>
                </a>
                @endif

                @if(auth()->user()->hasPermission('ver-solicitudes'))
                <a href="{{ route('admin.solicitudes.index') }}" class="quick-module">
                    <div class="module-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M22 7l-10 7L2 7"/>
                        </svg>
                    </div>
                    <span class="module-name">Solicitudes</span>
                    <span class="module-desc">Solicitudes de préstamo</span>
                </a>
                @endif

                @if(auth()->user()->hasPermission('ver-fichas-soporte'))
                <a href="{{ route('admin.soporte.index') }}" class="quick-module">
                    <div class="module-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <span class="module-name">Soporte</span>
                    <span class="module-desc">Fichas de soporte</span>
                </a>
                @endif

                @if(auth()->user()->hasPermission('ver-marcas') || auth()->user()->hasPermission('ver-categorias-equipos'))
                <a href="{{ route('admin.equipos.index') }}" class="quick-module">
                    <div class="module-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                            <line x1="9" y1="4" x2="9" y2="20"/>
                            <line x1="15" y1="4" x2="15" y2="20"/>
                        </svg>
                    </div>
                    <span class="module-name">Equipos</span>
                    <span class="module-desc">Catálogo de equipos</span>
                </a>
                @endif

                @if(auth()->user()->hasPermission('ver-instituciones'))
                <a href="{{ route('admin.entidades.index') }}" class="quick-module">
                    <div class="module-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <rect x="4" y="8" width="16" height="12" rx="1"/>
                            <path d="M8 20V8M16 20V8M4 12h16"/>
                        </svg>
                    </div>
                    <span class="module-name">Entidades</span>
                    <span class="module-desc">Instituciones y más</span>
                </a>
                @endif

            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ACTIVIDAD RECIENTE (solo con permisos)     -->
    <!-- ========================================== -->
    @if(auth()->user()->hasPermission('ver-solicitudes') || auth()->user()->hasPermission('ver-prestamos'))
    <div class="row mt-4">
        <div class="col-12">
            <div class="activity-section">
                <div class="section-header">
                    <h5>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                        Actividad Reciente
                    </h5>
                    <span class="badge-count">{{ now()->format('d/m/Y') }}</span>
                </div>

                @php
                    // Obtener actividad reciente combinada
                    $actividad = collect();

                    if (auth()->user()->hasPermission('ver-solicitudes')) {
                        $solicitudesRecientes = Solicitud::with(['usuario.trabajador'])
                            ->latest()
                            ->limit(3)
                            ->get()
                            ->map(function($item) {
                                return [
                                    'tipo' => 'solicitud',
                                    'id' => $item->id,
                                    'texto' => 'Nueva solicitud creada por <strong>' . ($item->usuario?->trabajador?->nombre ?? 'Usuario') . '</strong>',
                                    'estado' => $item->estado_solicitud,
                                    'fecha' => $item->created_at,
                                    'icono' => 'orange',
                                    'url' => route('admin.solicitudes.index')
                                ];
                            });
                        $actividad = $actividad->merge($solicitudesRecientes);
                    }

                    if (auth()->user()->hasPermission('ver-prestamos')) {
                        $prestamosRecientes = Prestamo::with(['usuarioRegistra.trabajador'])
                            ->latest()
                            ->limit(3)
                            ->get()
                            ->map(function($item) {
                                $estadoMap = [
                                    'pendiente' => ['label' => 'Pendiente', 'class' => 'pending'],
                                    'aprobado' => ['label' => 'Aprobado', 'class' => 'completed'],
                                    'entregado' => ['label' => 'Entregado', 'class' => 'completed'],
                                    'devuelto' => ['label' => 'Devuelto', 'class' => 'completed'],
                                    'cancelado' => ['label' => 'Cancelado', 'class' => 'cancelled'],
                                ];
                                $info = $estadoMap[$item->estado] ?? ['label' => $item->estado, 'class' => 'pending'];

                                return [
                                    'tipo' => 'prestamo',
                                    'id' => $item->id,
                                    'texto' => 'Préstamo <strong>' . $item->codigo . '</strong> - ' . $info['label'],
                                    'estado' => $info['label'],
                                    'estado_class' => $info['class'],
                                    'fecha' => $item->created_at,
                                    'icono' => $item->estado === 'devuelto' ? 'green' : ($item->estado === 'cancelado' ? 'red' : 'blue'),
                                    'url' => route('admin.prestamos.index')
                                ];
                            });
                        $actividad = $actividad->merge($prestamosRecientes);
                    }

                    $actividad = $actividad->sortByDesc('fecha')->take(5);
                @endphp

                @if($actividad->isNotEmpty())
                    @foreach($actividad as $item)
                    <div class="activity-item">
                        <div class="activity-icon {{ $item['icono'] }}">
                            @if($item['tipo'] === 'solicitud')
                            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="M22 7l-10 7L2 7"/>
                            </svg>
                            @else
                            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                            @endif
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">{!! $item['texto'] !!}</div>
                            <div class="activity-time">{{ $item['fecha']->diffForHumans() }}</div>
                        </div>
                        @if(isset($item['estado_class']))
                        <span class="activity-status {{ $item['estado_class'] }}">{{ $item['estado'] }}</span>
                        @endif
                        <a href="{{ $item['url'] }}" class="btn btn-sm btn-outline-primary-dark" style="border-radius: 30px; padding: 0.15rem 0.6rem; font-size: 0.7rem;">
                            Ver
                        </a>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-muted">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" class="mb-2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4M12 16h.01"/>
                        </svg>
                        <p class="mb-0">No hay actividad reciente</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
    @endif

</div>
@endsection
