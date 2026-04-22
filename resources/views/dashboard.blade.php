@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-4">
    <!-- Tarjeta de bienvenida -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px;">
                    <h2 class="text-white mb-2">¡Bienvenido, {{ Auth::user()->nombre }} {{ Auth::user()->apellido }}!</h2>
                    <p class="text-white-50 mb-0">Este es tu panel de control. Aquí podrás gestionar todas las funcionalidades del sistema.</p>
                </div>
            </div>
        </div>
    </div>

@php
    use App\Models\User;
    use App\Models\Prestamo;
    use App\Models\Activo;
    use App\Models\Periferico;
    use App\Models\Solicitud;
    use App\Models\FichaSoporte;

    // Usuarios activos
    $usuariosActivos = User::where('activo', true)->count();

    // Préstamos activos
    $prestamosActivos = Prestamo::where('estado_prestamo', 'activo')->count();

    // Equipos totales (activos + periféricos)
    $totalActivos = Activo::sum('cantidad');
    $totalPerifericos = Periferico::sum('cantidad_total');
    $totalEquipos = $totalActivos + $totalPerifericos;

    // Solicitudes pendientes
    $solicitudesPendientes = Solicitud::where('estado_solicitud', 'pendiente')->count();

    // Soporte técnico en proceso
    $soporteActivo = FichaSoporte::where('estado', 'en_proceso')->count();

    // Devoluciones pendientes (préstamos sin fecha de retorno real)
    $devolucionesPendientes = Prestamo::where('estado_prestamo', 'activo')
        ->whereNull('fecha_retorno_real')
        ->count();
@endphp

    <!-- Tarjetas de estadísticas -->
    <div class="row mt-4 g-4">
        <!-- Usuarios activos -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Usuarios activos</p>
                            <h3 class="mb-0">{{ number_format($usuariosActivos) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(30,60,114,0.1);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="1.8">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-muted small">Usuarios registrados en el sistema</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Préstamos activos -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Préstamos activos</p>
                            <h3 class="mb-0">{{ number_format($prestamosActivos) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(40,167,69,0.1);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="1.8">
                                <path d="M20 12V8H4v12h16v-4"/>
                                <path d="M12 2v4"/>
                                <path d="M8 6h8"/>
                                <circle cx="12" cy="14" r="2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-muted small">Equipos actualmente prestados</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipos totales -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Equipos en inventario</p>
                            <h3 class="mb-0">{{ number_format($totalEquipos) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(255,193,7,0.1);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="1.8">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-muted small">Entre activos y periféricos</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Solicitudes pendientes -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Solicitudes pendientes</p>
                            <h3 class="mb-0">{{ number_format($solicitudesPendientes) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(23,162,184,0.1);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#17a2b8" stroke-width="1.8">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-muted small">Esperando aprobación</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda fila de tarjetas -->
    <div class="row mt-4 g-4">
        <!-- Soporte técnico activo -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Soporte técnico activo</p>
                            <h3 class="mb-0">{{ number_format($soporteActivo) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(220,53,69,0.1);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.8">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 8v4"/>
                                <path d="M12 16h.01"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-muted small">En proceso de reparación/mantenimiento</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Devoluciones pendientes -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Devoluciones pendientes</p>
                            <h3 class="mb-0">{{ number_format($devolucionesPendientes) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(102,16,242,0.1);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6610f2" stroke-width="1.8">
                                <path d="M21 12a9 9 0 1 1-9-9"/>
                                <path d="M9 12h6"/>
                                <path d="M12 9v6"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-muted small">Préstamos sin fecha de retorno</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasa de ocupación -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Tasa de ocupación</p>
                            @php
                                $tasaOcupacion = $totalEquipos > 0 ? round(($prestamosActivos / $totalEquipos) * 100) : 0;
                            @endphp
                            <h3 class="mb-0">{{ $tasaOcupacion }}%</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(32,201,151,0.1);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#20c997" stroke-width="1.8">
                                <path d="M21 12a9 9 0 1 1-9-9"/>
                                <path d="M21 3v6h-6"/>
                                <path d="M12 7v5l3 3"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-muted small">Equipos prestados vs total inventario</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Módulos disponibles -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold" style="color: #1e3c72;">Módulos disponibles</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded-3" style="background: #f8f9fc;">
                                <div class="mb-2">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="1.8">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                </div>
                                <h6 class="fw-bold mb-1">Gestión de Usuarios</h6>
                                <small class="text-muted">Administrar usuarios del sistema</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded-3" style="background: #f8f9fc;">
                                <div class="mb-2">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="1.8">
                                        <path d="M21 12a9 9 0 1 1-9-9"/>
                                        <path d="M21 3v6h-6"/>
                                        <path d="M12 7v5l3 3"/>
                                    </svg>
                                </div>
                                <h6 class="fw-bold mb-1">Reportes</h6>
                                <small class="text-muted">Generar reportes personalizados</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded-3" style="background: #f8f9fc;">
                                <div class="mb-2">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="3"/>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                                    </svg>
                                </div>
                                <h6 class="fw-bold mb-1">Configuración</h6>
                                <small class="text-muted">Ajustes del sistema</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded-3" style="background: #f8f9fc;">
                                <div class="mb-2">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="1.8">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                    </svg>
                                </div>
                                <h6 class="fw-bold mb-1">Notificaciones</h6>
                                <small class="text-muted">Centro de notificaciones</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actividad reciente y Tips rápidos -->
    <div class="row mt-4 g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold" style="color: #1e3c72;">Actividad reciente</h5>
                </div>
                <div class="card-body p-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>📋 Usuario registrado</span>
                            <span class="badge" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">Hoy</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>🔄 Inicio de sesión</span>
                            <span class="badge" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">Hace 10 minutos</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>📦 Préstamo registrado</span>
                            <span class="badge" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">Hace 1 hora</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold" style="color: #1e3c72;">Tips rápidos</h5>
                </div>
                <div class="card-body p-4">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">💡 Usa el menú lateral para navegar entre módulos</li>
                        <li class="mb-2">💡 Puedes colapsar el menú para más espacio</li>
                        <li class="mb-2">💡 Los administradores tienen acceso al panel de administración</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
