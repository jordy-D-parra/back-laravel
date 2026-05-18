// ===========================
// Dashboard Layout JavaScript
// ===========================

document.addEventListener('DOMContentLoaded', function() {

    // ===========================
    // Toggle Sidebar
    // ===========================
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    if (toggleBtn && sidebar && mainContent) {
        // Restaurar estado desde localStorage
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }

    // ===========================
    // Submenús desplegables
    // ===========================
    document.querySelectorAll('.submenu-toggle').forEach(toggle => {
        const parent = toggle.closest('.has-submenu');
        const submenu = parent ? parent.querySelector('.submenu') : null;
        const submenuId = parent ? parent.getAttribute('data-submenu') : null;

        if (!parent || !submenu) return;

        // Expandir automáticamente si hay un enlace activo dentro
        const hasActiveLink = submenu.querySelector('.nav-link.active');
        if (hasActiveLink) {
            toggle.classList.add('expanded');
            submenu.classList.add('expanded');
        }

        // Restaurar estado desde localStorage
        if (submenuId) {
            const savedState = localStorage.getItem('submenu_' + submenuId);
            if (savedState === 'expanded') {
                toggle.classList.add('expanded');
                submenu.classList.add('expanded');
            } else if (savedState === 'collapsed') {
                toggle.classList.remove('expanded');
                submenu.classList.remove('expanded');
            }
        }

        // Evento click para toggle
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const isExpanded = submenu.classList.contains('expanded');

            if (isExpanded) {
                toggle.classList.remove('expanded');
                submenu.classList.remove('expanded');
                if (submenuId) localStorage.setItem('submenu_' + submenuId, 'collapsed');
            } else {
                toggle.classList.add('expanded');
                submenu.classList.add('expanded');
                if (submenuId) localStorage.setItem('submenu_' + submenuId, 'expanded');
            }
        });
    });

    // ===========================
    // Fecha y hora en tiempo real
    // ===========================
    function updateDateTime() {
        const now = new Date();

        const dateElement = document.getElementById('currentDate');
        const timeElement = document.getElementById('currentTime');
        const dayElement = document.getElementById('currentDay');

        if (dateElement) {
            dateElement.textContent = now.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }

        if (dayElement) {
            const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            dayElement.textContent = days[now.getDay()];
        }
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);

    // ===========================
    // Mobile sidebar
    // ===========================
    if (window.innerWidth <= 768) {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (sidebar) sidebar.classList.remove('mobile-open');
            });
        });
    }

});
