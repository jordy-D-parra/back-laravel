(function() {
    'use strict';

    var MAX_AUTO_SESSIONS = 5;
    var AUTO_CLOSE_DELAY = 6000;

    var HELP_MESSAGES = {
        dashboard: {
            icon: '📊',
            tips: [
                { icon: 'info', text: 'Bienvenido al <strong>Panel de Control</strong>. Aquí verás el resumen general.' },
                { icon: 'info', text: 'Usa el menú lateral para navegar entre los diferentes módulos.' }
            ]
        },
        entidades: {
            icon: '🏛️',
            tips: [
                { icon: 'info', text: 'Administra <strong>instituciones, departamentos y responsables</strong>.' },
                { icon: 'success', text: 'Al crear una institución con representante, se crea un <strong>responsable automáticamente</strong>.' },
                { icon: 'warning', text: 'Eliminar una institución elimina sus departamentos y responsables.' }
            ]
        },
        equipos: {
            icon: '📱',
            tips: [
                { icon: 'info', text: 'Define el <strong>catálogo de equipos</strong>: marcas, categorías y modelos.' },
                { icon: 'success', text: 'Al crear un modelo, puedes agregar sus <strong>componentes internos</strong>.' },
                { icon: 'info', text: 'Los componentes del modelo sirven como <strong>plantilla</strong> para activos reales.' }
            ]
        },
        inventario: {
            icon: '💻',
            tips: [
                { icon: 'info', text: 'Gestiona <strong>activos físicos</strong> y <strong>componentes reales</strong>.' },
                { icon: 'success', text: 'Los componentes pueden estar: <strong>en bodega, instalados o prestados</strong>.' }
            ]
        }
    };

    function getCurrentPage() {
        var path = window.location.pathname;
        if (path.indexOf('/entidades') >= 0) return 'entidades';
        if (path.indexOf('/equipos') >= 0) return 'equipos';
        if (path.indexOf('/inventario') >= 0) return 'inventario';
        if (path.indexOf('/usuarios') >= 0) return 'usuarios';
        if (path.indexOf('/trabajadores') >= 0) return 'trabajadores';
        return 'dashboard';
    }

    function getSessionCount() {
        return parseInt(localStorage.getItem('help_sessions_count') || '0');
    }

    function incrementSession() {
        var today = new Date().toDateString();
        var lastSession = localStorage.getItem('help_last_session');
        if (lastSession !== today) {
            var sessions = getSessionCount() + 1;
            localStorage.setItem('help_sessions_count', sessions);
            localStorage.setItem('help_last_session', today);
            return sessions;
        }
        return getSessionCount();
    }

    function isPageDismissed(page) {
        var dismissed = JSON.parse(localStorage.getItem('help_dismissed_pages') || '{}');
        return dismissed[page] === true;
    }

    function dismissPage(page) {
        var dismissed = JSON.parse(localStorage.getItem('help_dismissed_pages') || '{}');
        dismissed[page] = true;
        localStorage.setItem('help_dismissed_pages', JSON.stringify(dismissed));
    }

    function shouldAutoOpen(page) {
        var sessions = incrementSession();
        if (sessions > MAX_AUTO_SESSIONS) return false;
        if (isPageDismissed(page)) return false;
        return true;
    }

    function renderHelpPanel(page) {
        var data = HELP_MESSAGES[page] || HELP_MESSAGES['dashboard'];
        var sessions = getSessionCount();

        var tipsHtml = data.tips.map(function(tip) {
            var iconSymbol = tip.icon === 'info' ? 'i' : (tip.icon === 'warning' ? '!' : '✓');
            return '<div class="help-tip">' +
                '<div class="help-tip-icon ' + tip.icon + '">' + iconSymbol + '</div>' +
                '<div class="help-tip-text">' + tip.text + '</div>' +
                '</div>';
        }).join('');

        var sessionDots = '';
        for (var i = 1; i <= MAX_AUTO_SESSIONS; i++) {
            var dotClass = 'help-session-dot';
            if (i <= sessions) dotClass += ' used';
            if (i === sessions && sessions <= MAX_AUTO_SESSIONS) dotClass += ' active';
            sessionDots += '<div class="' + dotClass + '"></div>';
        }

        return '<div class="help-panel-header">' +
            '<h5>' + data.icon + ' ' + (HELP_MESSAGES[page] ? page.charAt(0).toUpperCase() + page.slice(1) : 'Ayuda') + '</h5>' +
            '<button class="help-panel-close" onclick="window.closeHelpPanel()">✕</button>' +
        '</div>' +
        '<div class="help-panel-body">' +
            '<div class="help-section-title">Consejos útiles</div>' + tipsHtml +
        '</div>' +
        '<div class="help-panel-footer">' +
            '<div><div class="session-info">Sesión ' + sessions + ' de ' + MAX_AUTO_SESSIONS + '</div>' +
            '<div class="help-session-progress">' + sessionDots + '</div></div>' +
            '<button class="btn-dismiss-page" onclick="window.dismissHelpPage()">No mostrar aquí</button>' +
        '</div>';
    }

    function createHelpPanel() {
        // Overlay
        var overlay = document.createElement('div');
        overlay.className = 'help-overlay';
        overlay.id = 'helpOverlay';
        overlay.addEventListener('click', function() { window.closeHelpPanel(); });
        document.body.appendChild(overlay);

        // Panel
        var panel = document.createElement('div');
        panel.className = 'help-panel';
        panel.id = 'helpPanel';
        document.body.appendChild(panel);

        // Botón
        createHelpButton();
    }

    function createHelpButton() {
        var topbar = document.querySelector('.topbar .user-menu');
        if (!topbar) return;
        var btn = document.createElement('button');
        btn.className = 'btn-help-toggle';
        btn.id = 'btnHelpToggle';
        btn.title = 'Ayuda contextual';
        btn.innerHTML = '?';
        btn.addEventListener('click', function() { window.toggleHelpPanel(); });
        topbar.parentNode.insertBefore(btn, topbar);
    }

    window.toggleHelpPanel = function() {
        var panel = document.getElementById('helpPanel');
        if (panel && panel.classList.contains('open')) {
            window.closeHelpPanel();
        } else {
            window.openHelpPanel();
        }
    };

    window.openHelpPanel = function() {
        var page = getCurrentPage();
        var panel = document.getElementById('helpPanel');
        var overlay = document.getElementById('helpOverlay');
        if (!panel || !overlay) return;
        panel.innerHTML = renderHelpPanel(page);
        panel.classList.add('open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    window.closeHelpPanel = function() {
        var panel = document.getElementById('helpPanel');
        var overlay = document.getElementById('helpOverlay');
        if (panel) panel.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    };

    window.dismissHelpPage = function() {
        var page = getCurrentPage();
        dismissPage(page);
        window.closeHelpPanel();
    };

    // Init
    function init() {
        createHelpPanel();
        var page = getCurrentPage();
        if (shouldAutoOpen(page)) {
            setTimeout(function() {
                window.openHelpPanel();
                setTimeout(function() {
                    var panel = document.getElementById('helpPanel');
                    if (panel && panel.classList.contains('open')) {
                        window.closeHelpPanel();
                    }
                }, AUTO_CLOSE_DELAY);
            }, 1000);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
