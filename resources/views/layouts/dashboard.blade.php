<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Sistema de Gestión</title>

    @vite(['resources/css/bootstrap.css', 'resources/js/app.js'])

    <style>
        :root {
            --sidebar-width: 280px;
            --header-height: 70px;
            --primary-color: #1e3c72;
            --primary-light: #2a5298;
            --secondary-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --dark-color: #5a5c69;
            --bg-gradient-start: #1e3c72;
            --bg-gradient-end: #2a5298;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Nunito', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 5px;
        }

        .sidebar.collapsed {
            margin-left: calc(var(--sidebar-width) * -1);
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .logo-container {
            margin-bottom: 15px;
        }

        .logo-img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px;
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .sidebar-header p {
            margin: 8px 0 0;
            font-size: 0.75rem;
            opacity: 0.7;
            letter-spacing: 0.5px;
        }

        .nav-menu {
            list-style: none;
            padding: 0 15px;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 12px;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .nav-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-icon svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
        }

        .badge-count {
            background: #e74a3b;
            color: white;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            margin-left: auto;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Topbar */
        .topbar {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
            height: var(--header-height);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid rgba(30, 60, 114, 0.08);
        }

        .toggle-btn {
            background: #f0f2f5;
            border: none;
            cursor: pointer;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-btn svg {
            width: 22px;
            height: 22px;
            stroke: var(--primary-color);
            stroke-width: 1.8;
            fill: none;
        }

        .toggle-btn:hover {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        }

        .toggle-btn:hover svg {
            stroke: white;
        }

        .datetime-panel {
            display: flex;
            gap: 25px;
            background: white;
            padding: 8px 25px;
            border-radius: 40px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .datetime-item {
            text-align: center;
            position: relative;
        }

        .datetime-item:not(:last-child)::after {
            content: '';
            position: absolute;
            right: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 25px;
            background: #dee2e6;
        }

        .datetime-label {
            font-size: 0.6rem;
            text-transform: uppercase;
            color: var(--dark-color);
            font-weight: 700;
            letter-spacing: 0.8px;
            margin-bottom: 2px;
        }

        .datetime-value {
            font-size: 0.95rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
            font-size: 0.95rem;
        }

        .user-role {
            font-size: 0.7rem;
            margin: 0;
        }

        /* Badges para roles */
        .role-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .role-badge-super_admin {
            background: #dc3545;
            color: white;
        }
        
        .role-badge-admin {
            background: #fd7e14;
            color: white;
        }
        
        .role-badge-worker {
            background: #0d6efd;
            color: white;
        }
        
        .role-badge-user, .role-badge-usuario {
            background: #198754;
            color: white;
        }
        
        .role-badge-default {
            background: #6c757d;
            color: white;
        }

        .logout-btn {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            cursor: pointer;
            padding: 8px 18px;
            border-radius: 30px;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn svg {
            width: 16px;
            height: 16px;
            stroke: var(--primary-color);
            stroke-width: 1.8;
            fill: none;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
        }

        .logout-btn:hover svg {
            stroke: white;
        }

        .page-content {
            padding: 25px;
            animation: fadeInUp 0.4s ease-out;
        }

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

        .alert-success-custom {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: none;
            border-radius: 16px;
            color: #155724;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .alert-danger-custom {
            background: linear-gradient(135deg, #f8d7da 0%, #f1c3c7 100%);
            border: none;
            border-radius: 16px;
            color: #721c24;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .alert-info-custom {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border: none;
            border-radius: 16px;
            color: #0c5460;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }
            .sidebar.mobile-open {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .datetime-panel {
                display: none;
            }
            .user-name, .user-role {
                display: none;
            }
            .topbar {
                padding: 0 15px;
            }
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(30, 60, 114, 0.15);
        }
    </style>

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

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2h-5v-8H9v8H4a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </span>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('profile.index') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <span>Mi Perfil</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('solicitudes.index') }}" class="nav-link {{ request()->routeIs('solicitudes.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </span>
                    <span>Mis Solicitudes</span>
                </a>
            </li>

            @if(Auth::check() && (Auth::user()->isSuperAdmin() || Auth::user()->isAdmin()))
            <li class="nav-item">
                <a href="{{ route('inventario.index') }}" class="nav-link {{ request()->routeIs('inventario.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                    </span>
                    <span>Inventario</span>
                </a>
            </li>
            @endif

            @if(Auth::check() && (Auth::user()->isSuperAdmin() || Auth::user()->isAdmin()))
            <li class="nav-item">
                <a href="{{ route('aprobaciones.index') }}" class="nav-link {{ request()->routeIs('aprobaciones.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </span>
                    <span>Préstamos</span>
                    @php
                        $pendientesCount = App\Models\Solicitud::where('estado_solicitud', 'pendiente')->count();
                    @endphp
                    @if($pendientesCount > 0)
                        <span class="badge-count">{{ $pendientesCount }}</span>
                    @endif
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a href="{{ route('soporte.index') }}" class="nav-link {{ request()->routeIs('soporte.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4"/>
                            <path d="M12 16h.01"/>
                        </svg>
                    </span>
                    <span>Soporte Técnico</span>
                    @php
                        $soportePendientes = App\Models\FichaSoporte::where('estado', 'pendiente')->count();
                    @endphp
                    @if($soportePendientes > 0)
                        <span class="badge-count">{{ $soportePendientes }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('reportes.index') }}" class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <path d="M21 12a9 9 0 1 1-9-9"/>
                            <path d="M21 3v6h-6"/>
                            <path d="M12 7v5l3 3"/>
                        </svg>
                    </span>
                    <span>Reportes</span>
                </a>
            </li>

            @if(Auth::check() && Auth::user()->isSuperAdmin())
            <li class="nav-item">
                <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </span>
                    <span>Gestión de Usuarios</span>
                </a>
            </li>
            @endif

            @if(Auth::check() && Auth::user()->isSuperAdmin())
            <li class="nav-item">
                <a href="{{ route('audit.logs') }}" class="nav-link {{ request()->routeIs('audit.logs') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </span>
                    <span>Registro de Actividad</span>
                </a>
            </li>
            @endif

            @if(Auth::check() && Auth::user()->isSuperAdmin())
            <li class="nav-item">
                <a href="{{ route('audit.sessions') }}" class="nav-link {{ request()->routeIs('audit.sessions') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </span>
                    <span>Sesiones Activas</span>
                </a>
            </li>
            @endif
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Topbar Rediseñado -->
        <div class="topbar">
            <button class="toggle-btn" id="toggleSidebar">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
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
                    <p class="user-name">{{ Auth::user()->nombre ?? Auth::user()->name ?? 'Usuario' }} {{ Auth::user()->apellido ?? '' }}</p>
                    <p class="user-role">
                        @php
                            $user = Auth::user();
                            $roleName = $user->rol->nombre ?? ($user->role ?? 'sin_rol');
                            $roleIcon = match($roleName) {
                                'super_admin' => '👑',
                                'admin' => '⚙️',
                                'worker' => '🔧',
                                'user', 'usuario' => '👤',
                                default => '❓'
                            };
                            $roleDisplay = match($roleName) {
                                'super_admin' => 'Super Administrador',
                                'admin' => 'Administrador',
                                'worker' => 'Trabajador',
                                'user', 'usuario' => 'Usuario',
                                default => ucfirst($roleName)
                            };
                            $badgeClass = match($roleName) {
                                'super_admin' => 'role-badge-super_admin',
                                'admin' => 'role-badge-admin',
                                'worker' => 'role-badge-worker',
                                'user', 'usuario' => 'role-badge-user',
                                default => 'role-badge-default'
                            };
                        @endphp
                        <span class="role-badge {{ $badgeClass }}">
                            {{ $roleIcon }} {{ $roleDisplay }}
                        </span>
                    </p>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
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

    <script>
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }

        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        function updateDateTime() {
            const now = new Date();
            const dateElement = document.getElementById('currentDate');
            const timeElement = document.getElementById('currentTime');
            const dayElement = document.getElementById('currentDay');

            if (dateElement) {
                dateElement.textContent = now.toLocaleDateString('es-ES', {
                    year: 'numeric', month: 'long', day: 'numeric'
                });
            }
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString('es-ES', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit'
                });
            }
            if (dayElement) {
                dayElement.textContent = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'][now.getDay()];
            }
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        if (window.innerWidth <= 768) {
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => sidebar.classList.remove('mobile-open'));
            });
        }
    </script>
</body>
</html>