@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('styles')
    @vite(['resources/css/dashboard-home.css'])
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Tarjeta de bienvenida -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4 welcome-card">
                <div class="card-body p-4">
                    <h2 class="text-white mb-2">
                        ¡Bienvenido, {{ Auth::user()->trabajador->nombre }} {{ Auth::user()->trabajador->apellido }}!
                    </h2>
                    <p class="text-white-50 mb-0">Este es tu panel de control. Aquí podrás gestionar todas las funcionalidades del sistema.</p>
                </div>
            </div>
        </div>
    </div>

    @php
        use App\Models\Usuario;
        use App\Models\Trabajador;
        use App\Models\Rol;

        $usuariosActivos = Usuario::where('status', 'activo')->count();
        $usuariosInactivos = Usuario::where('status', 'inactivo')->count();
        $totalTrabajadores = Trabajador::count();
        $totalRoles = Rol::count();
        $pendientesCambio = Usuario::where('must_change_password', true)->count();
    @endphp

    <!-- Tarjetas de estadísticas -->
    <div class="row mt-4 g-4">
        <!-- Usuarios activos -->
        <div class="col-md-3">
            <div class="stat-card">
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

        <!-- Trabajadores -->
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Trabajadores</p>
                        <h3 class="mb-0">{{ number_format($totalTrabajadores) }}</h3>
                    </div>
                    <div class="rounded-circle p-2" style="background: rgba(40,167,69,0.1);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="1.8">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="text-muted small">Personal del departamento</span>
                </div>
            </div>
        </div>

        <!-- Roles -->
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Roles del sistema</p>
                        <h3 class="mb-0">{{ number_format($totalRoles) }}</h3>
                    </div>
                    <div class="rounded-circle p-2" style="background: rgba(255,193,7,0.1);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="1.8">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="text-muted small">Admin, Ingeniero, Técnico, Secretaria</span>
                </div>
            </div>
        </div>

        <!-- Pendientes cambio contraseña -->
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Pendientes cambio</p>
                        <h3 class="mb-0">{{ number_format($pendientesCambio) }}</h3>
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
                    <span class="text-muted small">Deben cambiar contraseña</span>
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
                        @if(Auth::user()->isRole('admin'))
                        <div class="col-md-3">
                            <a href="{{ route('admin.usuarios.index') }}" class="module-card text-decoration-none text-dark">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="1.8">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                <h6 class="fw-bold mb-1">Usuarios</h6>
                                <small class="text-muted">Gestionar accesos</small>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.trabajadores.index') }}" class="module-card text-decoration-none text-dark">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="1.8">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                </svg>
                                <h6 class="fw-bold mb-1">Trabajadores</h6>
                                <small class="text-muted">Personal del depto.</small>
                            </a>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <div class="module-card pending">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="1.8">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                </svg>
                                <h6 class="fw-bold mb-1">Inventario</h6>
                                <small class="text-muted">Próximamente</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="module-card pending">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="1.8">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <h6 class="fw-bold mb-1">Reportes</h6>
                                <small class="text-muted">Próximamente</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips rápidos -->
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold" style="color: #1e3c72;">Tips rápidos</h5>
                </div>
                <div class="card-body p-4">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">💡 Usa el menú lateral para navegar entre módulos</li>
                        <li class="mb-2">💡 Para crear un usuario, primero registra al trabajador</li>
                        <li class="mb-2">💡 Los usuarios nuevos deben cambiar su contraseña en el primer acceso</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
