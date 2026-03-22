<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Sistema</title>

    @vite(['resources/css/bootstrap.css', 'resources/js/app.js'])

    <style>
        :root {
            --sidebar-width: 280px;
            --header-height: 70px;
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --dark-color: #5a5c69;
        }

        body {
            font-family: 'Nunito', 'Segoe UI', sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
            color: white;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar.collapsed {
            margin-left: calc(var(--sidebar-width) * -1);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .sidebar-header p {
            margin: 5px 0 0;
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: white;
        }

        .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.15);
            border-left-color: white;
        }

        .nav-link i {
            width: 25px;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Topbar */
        .topbar {
            background: white;
            height: var(--header-height);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
        }

        .toggle-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark-color);
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .toggle-btn:hover {
            background: #f0f2f5;
        }

        .datetime-panel {
            display: flex;
            gap: 20px;
            background: #f8f9fc;
            padding: 10px 20px;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .datetime-item {
            text-align: center;
        }

        .datetime-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--dark-color);
            font-weight: 600;
        }

        .datetime-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        .user-role {
            font-size: 0.75rem;
            color: #858796;
            margin: 0;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--danger-color);
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #fee2e2;
        }

        /* Page Content */
        .page-content {
            padding: 20px;
        }

        /* Cards */
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

        /* Responsive */
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
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>📊 Sistema</h3>
            <p>Panel de Control</p>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i>📈</i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('profile.index') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i>👤</i>
                    <span>Mi Perfil</span>
                </a>
            </li>

            <!-- Módulo Trabajador - Solo para worker y super_admin -->
            @if(Auth::check() && Auth::user()->hasAccess('worker'))
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i>🔧</i>
                    <span>Módulo Trabajador</span>
                </a>
            </li>
            @endif

            <!-- Gestión de Usuarios - Solo para super_admin -->
            @if(Auth::check() && Auth::user()->isSuperAdmin())
            <li class="nav-item">
                <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <i>👑</i>
                    <span>Gestión de Usuarios</span>
                </a>
            </li>
            @endif

            <!-- Registro de Actividad - Solo para super_admin -->
            @if(Auth::check() && Auth::user()->isSuperAdmin())
            <li class="nav-item">
                <a href="{{ route('audit.logs') }}" class="nav-link {{ request()->routeIs('audit.logs') ? 'active' : '' }}">
                    <i>📋</i>
                    <span>Registro de Actividad</span>
                </a>
            </li>
            @endif

            <!-- Sesiones Activas - Solo para super_admin -->
            @if(Auth::check() && Auth::user()->isSuperAdmin())
            <li class="nav-item">
                <a href="{{ route('audit.sessions') }}" class="nav-link {{ request()->routeIs('audit.sessions') ? 'active' : '' }}">
                    <i>🟢</i>
                    <span>Sesiones Activas</span>
                </a>
            </li>
            @endif
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="topbar">
            <button class="toggle-btn" id="toggleSidebar">
                ☰
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
                    <p class="user-name">{{ Auth::user()->name }}</p>
                    <p class="user-role">{!! Auth::user()->role_badge !!}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="logout-btn" title="Cerrar sesión">
                        🚪 Salir
                    </button>
                </form>
            </div>
        </div>

        <div class="page-content">
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

            @if(session('status'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        // Toggle sidebar
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');

            // Guardar estado en localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });

        // Cargar estado guardado del sidebar
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        // Actualizar fecha y hora en tiempo real
        function updateDateTime() {
            const now = new Date();

            // Fecha
            const date = now.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('currentDate').textContent = date;

            // Hora
            const time = now.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('currentTime').textContent = time;

            // Día de la semana
            const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            const dayName = days[now.getDay()];
            document.getElementById('currentDay').textContent = dayName;
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Cerrar sidebar en móvil al hacer clic en un enlace
        if (window.innerWidth <= 768) {
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    sidebar.classList.remove('mobile-open');
                });
            });
        }
    </script>
</body>
</html>
