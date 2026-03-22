@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-4">
    <!-- Bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <h2 class="h4 mb-3">👋 ¡Bienvenido, {{ Auth::user()->name }}!</h2>
                <p class="text-muted mb-0">Este es tu panel de control. Aquí podrás gestionar todas las funcionalidades del sistema.</p>
            </div>
        </div>
    </div>

    <!-- Cards de estadísticas (demo) -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Usuarios activos</p>
                        <h3 class="mb-0">1,234</h3>
                    </div>
                    <div style="font-size: 2rem;">👥</div>
                </div>
                <div class="mt-2">
                    <span class="text-success">↑ 12%</span>
                    <span class="text-muted small"> vs mes anterior</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Ventas del mes</p>
                        <h3 class="mb-0">$45,678</h3>
                    </div>
                    <div style="font-size: 2rem;">💰</div>
                </div>
                <div class="mt-2">
                    <span class="text-success">↑ 8%</span>
                    <span class="text-muted small"> vs mes anterior</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Proyectos</p>
                        <h3 class="mb-0">24</h3>
                    </div>
                    <div style="font-size: 2rem;">📁</div>
                </div>
                <div class="mt-2">
                    <span class="text-warning">3 en progreso</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Tasa de éxito</p>
                        <h3 class="mb-0">94%</h3>
                    </div>
                    <div style="font-size: 2rem;">📊</div>
                </div>
                <div class="mt-2">
                    <span class="text-success">↑ 5%</span>
                    <span class="text-muted small"> objetivo: 90%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de módulos (demo) -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <h4 class="mb-3">📦 Módulos disponibles</h4>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="p-3 border rounded text-center">
                            <div style="font-size: 2rem;">👥</div>
                            <h6 class="mt-2 mb-1">Gestión de Usuarios</h6>
                            <small class="text-muted">Administrar usuarios del sistema</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 border rounded text-center">
                            <div style="font-size: 2rem;">📄</div>
                            <h6 class="mt-2 mb-1">Reportes</h6>
                            <small class="text-muted">Generar reportes personalizados</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 border rounded text-center">
                            <div style="font-size: 2rem;">⚙️</div>
                            <h6 class="mt-2 mb-1">Configuración</h6>
                            <small class="text-muted">Ajustes del sistema</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 border rounded text-center">
                            <div style="font-size: 2rem;">🔔</div>
                            <h6 class="mt-2 mb-1">Notificaciones</h6>
                            <small class="text-muted">Centro de notificaciones</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <div class="stat-card">
                <h5>📈 Actividad reciente</h5>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Usuario registrado</span>
                        <span class="text-muted">Hace 5 minutos</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Inicio de sesión</span>
                        <span class="text-muted">Hace 10 minutos</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Actualización de perfil</span>
                        <span class="text-muted">Hace 1 hora</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="stat-card">
                <h5>💡 Tips rápidos</h5>
                <ul class="mt-3 mb-0">
                    <li class="mb-2">✓ Usa el menú lateral para navegar entre módulos</li>
                    <li class="mb-2">✓ Puedes colapsar el menú para más espacio</li>
                    <li class="mb-2">✓ Los administradores tienen acceso al panel de administración</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
