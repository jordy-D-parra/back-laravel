<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Sistema de Gestión</title>

    @vite(['resources/js/bootstrap.js'])
    @vite(['resources/css/dashboard-layout.css'])
    @yield('styles')
</head>
<body>
    <!-- Sidebar mejorado con estructura jerárquica clara -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="{{ asset('images/escudo-yaracuy.jpeg') }}" alt="Logo" class="logo-img">
            </div>
            <h3>Sistema de Gestión</h3>
            <p>Inventario y Préstamos</p>
        </div>

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

            @if(Auth::user()->isRole('admin'))
            <!-- ========== SECCIÓN ADMINISTRACIÓN ========== -->
            <li class="nav-divider"></li>
            <li class="nav-section">ADMINISTRACIÓN</li>

            <!-- Gestión de Entidades -->
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

            <!-- Gestión de Usuarios -->
            <li class="nav-item">
                <a href="{{ route('admin.usuarios.index') }}" class="nav-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                    <span>Usuarios</span>
                </a>
            </li>

            <!-- Gestión de Trabajadores -->
            <li class="nav-item">
                <a href="{{ route('admin.trabajadores.index') }}" class="nav-link {{ request()->routeIs('admin.trabajadores.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </span>
                    <span>Trabajadores</span>
                </a>
            </li>

            <!-- Catálogo de Equipos -->
            <li class="nav-item">
                <a href="{{ route('admin.equipos.index') }}" class="nav-link {{ request()->routeIs('admin.equipos.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                            <line x1="9" y1="4" x2="9" y2="20"/>
                            <line x1="15" y1="4" x2="15" y2="20"/>
                            <line x1="4" y1="9" x2="20" y2="9"/>
                            <line x1="4" y1="15" x2="20" y2="15"/>
                        </svg>
                    </span>
                    <span>Equipos</span>
                </a>
            </li>
            @endif

            <!-- ========== SECCIÓN OPERACIONES ========== -->
            <li class="nav-divider"></li>
            <li class="nav-section">OPERACIONES</li>

            <!-- Solicitudes de préstamo -->
            <li class="nav-item">
                <a href="#" class="nav-link pending">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </span>
                    <span>Mis Solicitudes</span>
                </a>
            </li>

            <!-- Control de inventario -->
            <li class="nav-item">
                <a href="#" class="nav-link pending">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </span>
                    <span>Inventario</span>
                </a>
            </li>

            <!-- Soporte técnico y mantenimiento -->
            <li class="nav-item">
                <a href="#" class="nav-link pending">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    </span>
                    <span>Soporte Técnico</span>
                </a>
            </li>

            <!-- ========== SECCIÓN REPORTES ========== -->
            <li class="nav-divider"></li>
            <li class="nav-section">REPORTES</li>

            <!-- Generación de reportes -->
            <li class="nav-item">
                <a href="#" class="nav-link pending">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-9-9"/><path d="M21 3v6h-6"/></svg>
                    </span>
                    <span>Reportes</span>
                </a>
            </li>
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
    @vite(['resources/js/app.js'])
    @yield('scripts')
</body>
</html>