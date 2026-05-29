<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Sistema de Gestión</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/js/bootstrap.js'])
    @vite(['resources/css/dashboard-layout.css'])
    @vite(['resources/css/dashboard-home.css'])
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="{{ asset('images/escudo-yaracuy.jpeg') }}" alt="Logo" class="logo-img">
            </div>
            <h3>Sistema de Gestión</h3>
            <p>Inventario y Préstamos</p>
        </div>

        @php
            $user = Auth::user();
        @endphp

        <ul class="nav-menu">
            <!-- ========== SECCIÓN PRINCIPAL ========== -->
            <li class="nav-section">PRINCIPAL</li>
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2h-5v-8H9v8H4a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </span>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- ========== MAESTROS ========== -->
            @if($user->hasPermission('ver-instituciones') || $user->hasPermission('ver-departamentos') || $user->hasPermission('ver-responsables') ||
                $user->hasPermission('ver-marcas') || $user->hasPermission('ver-categorias-equipos') || $user->hasPermission('ver-modelos'))
            <li class="nav-divider"></li>
            <li class="nav-section">MAESTROS</li>
            @endif

            <!-- Entidades -->
            @if($user->hasPermission('ver-instituciones') || $user->hasPermission('ver-departamentos') || $user->hasPermission('ver-responsables'))
            <li class="nav-item">
                <a href="{{ route('admin.entidades.index') }}" class="nav-link {{ request()->routeIs('admin.entidades.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <rect x="4" y="8" width="16" height="12" rx="1"/>
                            <path d="M8 20V8M16 20V8M4 12h16"/>
                        </svg>
                    </span>
                    <span>Entidades</span>
                </a>
            </li>
            @endif

            <!-- Catálogo de Equipos -->
            @if($user->hasPermission('ver-marcas') || $user->hasPermission('ver-categorias-equipos') || $user->hasPermission('ver-modelos'))
            <li class="nav-item">
                <a href="{{ route('admin.equipos.index') }}" class="nav-link {{ request()->routeIs('admin.equipos.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                            <line x1="9" y1="4" x2="9" y2="20"/>
                            <line x1="15" y1="4" x2="15" y2="20"/>
                        </svg>
                    </span>
                    <span>Equipos</span>
                </a>
            </li>
            @endif

            <!-- ========== GESTIÓN DE USUARIOS ========== -->
            @if($user->hasPermission('ver-roles') || $user->hasPermission('ver-trabajadores') || $user->hasPermission('ver-usuarios'))
            <li class="nav-divider"></li>
            <li class="nav-section">GESTIÓN DE USUARIOS</li>
            @endif

            <!-- Roles y Permisos -->
            @if($user->hasPermission('ver-roles'))
            <li class="nav-item">
                <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                        </svg>
                    </span>
                    <span>Roles y Permisos</span>
                </a>
            </li>
            @endif

            <!-- Trabajadores -->
            @if($user->hasPermission('ver-trabajadores'))
            <li class="nav-item">
                <a href="{{ route('admin.trabajadores.index') }}" class="nav-link {{ request()->routeIs('admin.trabajadores.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </span>
                    <span>Trabajadores</span>
                </a>
            </li>
            @endif

            <!-- Usuarios -->
            @if($user->hasPermission('ver-usuarios'))
            <li class="nav-item">
                <a href="{{ route('admin.usuarios.index') }}" class="nav-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                    <span>Usuarios</span>
                </a>
            </li>
            @endif

            <!-- ========== PROCESOS ========== -->
            @if($user->hasPermission('ver-activos') || $user->hasPermission('ver-componentes'))
            <li class="nav-divider"></li>
            <li class="nav-section">PROCESOS</li>

            <!-- Inventario -->
            <li class="nav-item">
                <a href="{{ route('admin.inventario.index') }}" class="nav-link {{ request()->routeIs('admin.inventario.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                    </span>
                    <span>Inventario</span>
                </a>
            </li>
            @endif

            <!-- Préstamos (Próximamente) -->
            <li class="nav-item">
                <a href="#" class="nav-link pending">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </span>
                    <span>Préstamos</span>
                    <span class="badge-count">Pronto</span>
                </a>
            </li>

            <!-- Solicitudes de Préstamo -->
@if($user->hasPermission('ver-solicitudes'))
<li class="nav-item">
    <a href="{{ route('admin.solicitudes.index') }}" class="nav-link {{ request()->routeIs('admin.solicitudes.*') ? 'active' : '' }}">
        <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="M22 7l-10 7L2 7"/>
            </svg>
        </span>
        <span>Solicitudes</span>
    </a>
</li>
@endif

            <!-- Soporte Técnico -->
@if(auth()->user()->hasPermission('ver-fichas-soporte'))
<li class="nav-item">
    <a href="{{ route('admin.soporte.index') }}" class="nav-link {{ request()->routeIs('admin.soporte.*') ? 'active' : '' }}">
        <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </span>
        <span>Soporte Técnico</span>
    </a>
</li>
@endif

            <!-- ========== REPORTES ========== -->
            <li class="nav-divider"></li>
            <li class="nav-section">REPORTES</li>

            <li class="nav-item">
                <a href="#" class="nav-link pending">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-9-9"/><path d="M21 3v6h-6"/></svg>
                    </span>
                    <span>Generar Reportes</span>
                    <span class="badge-count">Pronto</span>
                </a>
            </li>

            <!-- ========== CONFIGURACIÓN ========== -->
            @if($user->hasPermission('ver-roles') || $user->hasPermission('ver-usuarios'))
            <li class="nav-divider"></li>
            <li class="nav-section">CONFIGURACIÓN</li>

            <!-- Auditoría (Próximamente) -->
            <li class="nav-item">
                <a href="#" class="nav-link pending">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </span>
                    <span>Auditoría</span>
                    <span class="badge-count">Pronto</span>
                </a>
            </li>
            @endif
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Topbar -->
        <div class="topbar">
            <button class="toggle-btn" id="toggleSidebar">
                <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="datetime-panel">
                <div class="datetime-item">
                    <div class="datetime-label">Fecha</div>
                    <div class="datetime-value" id="currentDate"></div>
                </div>
                <div class="datetime-item">
                    <div class="datetime-label">Hora</div>
                    <div class="datetime-value" id="currentTime"></div>
                </div>
                <div class="datetime-item">
                    <div class="datetime-label">Día</div>
                    <div class="datetime-value" id="currentDay"></div>
                </div>
            </div>
            <div class="user-menu">
                <div class="user-info">
                    @php
                        $usuario = Auth::user();
                        $trabajador = $usuario->trabajador;
                        $rolNombre = $usuario->rol->nombre ?? 'sin_rol';
                        $rolDisplay = match($rolNombre) {
                            'admin' => 'Administrador',
                            'ingeniero' => 'Ingeniero',
                            'tecnico' => 'Técnico',
                            'secretaria' => 'Secretaria',
                            default => ucfirst($rolNombre)
                        };
                        $badgeClass = 'role-badge-' . $rolNombre;
                        if (!in_array($rolNombre, ['admin', 'ingeniero', 'tecnico', 'secretaria'])) {
                            $badgeClass = 'role-badge-default';
                        }
                    @endphp
                    <p class="user-name">{{ $trabajador->nombre }} {{ $trabajador->apellido }}</p>
                    <p class="user-role">
                        <span class="role-badge {{ $badgeClass }}">{{ $rolDisplay }}</span>
                    </p>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Salir
                    </button>
                </form>
            </div>
        </div>

        <div class="page-content">
            @if(session('success'))
                <div class="alert-success-custom alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-danger-custom alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('status'))
                <div class="alert-info-custom alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    @vite(['resources/js/dashboard-layout.js'])
    @vite(['resources/css/dashboard-home.css'])
    @vite(['resources/js/app.js'])
    @yield('scripts')
    @vite(['resources/js/help-panel.js'])
</body>
</html>
