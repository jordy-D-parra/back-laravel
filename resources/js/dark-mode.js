// resources/js/dark-mode.js
// 🌙 Modo Oscuro - Toggle elegante con persistencia

(function () {
    'use strict';

    const STORAGE_KEY = 'darkModeEnabled';
    const BODY_CLASS = 'dark-mode';

    /**
     * Aplica el modo oscuro al body
     */
    function aplicarModoOscuro(activo) {
        if (activo) {
            document.body.classList.add(BODY_CLASS);
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.body.classList.remove(BODY_CLASS);
            document.documentElement.setAttribute('data-theme', 'light');
        }
    }

    /**
     * Detecta la preferencia del sistema
     */
    function prefiereSistemaOscuro() {
        return window.matchMedia &&
               window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    /**
     * Obtiene el estado guardado (o detecta preferencia del sistema)
     */
    function obtenerEstadoInicial() {
        const guardado = localStorage.getItem(STORAGE_KEY);

        if (guardado !== null) {
            return guardado === 'true';
        }

        // Primera vez: usar preferencia del sistema
        return prefiereSistemaOscuro();
    }

    /**
     * Crea el botón de toggle
     */
    function crearBoton() {
        // Evitar duplicados
        if (document.getElementById('darkModeToggle')) return;

        const btn = document.createElement('button');
        btn.id = 'darkModeToggle';
        btn.className = 'dark-mode-toggle';
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Cambiar modo de color');
        btn.setAttribute('title', 'Cambiar modo de color');

        btn.innerHTML = `
            <!-- Sol -->
            <svg class="dm-icon dm-icon-sun" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>

            <!-- Luna -->
            <svg class="dm-icon dm-icon-moon" viewBox="0 0 24 24">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        `;

        document.body.appendChild(btn);

        // Evento click
        btn.addEventListener('click', function () {
            const activo = !document.body.classList.contains(BODY_CLASS);
            aplicarModoOscuro(activo);
            localStorage.setItem(STORAGE_KEY, activo.toString());

            // Animación de feedback
            btn.style.transform = 'scale(0.9)';
            setTimeout(() => {
                btn.style.transform = '';
            }, 150);
        });

        return btn;
    }

    /**
     * Inicializa el modo oscuro
     */
    function init() {
        // Aplicar estado inicial ANTES de crear el botón (evita flash)
        aplicarModoOscuro(obtenerEstadoInicial());

        // Crear botón cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', crearBoton);
        } else {
            crearBoton();
        }

        // Escuchar cambios en la preferencia del sistema
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
                // Solo cambiar si el usuario no ha elegido manualmente
                if (localStorage.getItem(STORAGE_KEY) === null) {
                    aplicarModoOscuro(e.matches);
                }
            });
        }
    }

    // Ejecutar INMEDIATAMENTE para evitar flash blanco
    init();

    // Exponer API pública (opcional)
    window.DarkMode = {
        toggle: function () {
            const activo = !document.body.classList.contains(BODY_CLASS);
            aplicarModoOscuro(activo);
            localStorage.setItem(STORAGE_KEY, activo.toString());
        },
        enable: function () {
            aplicarModoOscuro(true);
            localStorage.setItem(STORAGE_KEY, 'true');
        },
        disable: function () {
            aplicarModoOscuro(false);
            localStorage.setItem(STORAGE_KEY, 'false');
        },
        isEnabled: function () {
            return document.body.classList.contains(BODY_CLASS);
        }
    };
})();