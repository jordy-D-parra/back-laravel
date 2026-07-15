// resources/js/admin-inventario.js

// Datos para paginación
var activosData = [];
var activosPage = 1;
var activosPerPage = 10;

var componentesData = [];
var componentesPage = 1;
var componentesPerPage = 10;

// Modelos para el buscador
var todosModelos = [];

// Lista de estados
var listaEstados = [];

// Componentes existentes al editar
var componentesExistentesActivo = [];

// Variable para cambio de estado
var activoCambioEstado = null;

// Iconos SVG
var SVG_ICONS = {
    ver: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>',
    editar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
    eliminar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>',
    cambiarEstado: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>',
    plus: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
    check: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>'
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de inventario cargado');

    // Cargar estados primero
    cargarEstados().then(() => {
        cargarActivos();
        cargarComponentes();
    });

    cargarSelectsBase();

    document.getElementById('formActivo').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarActivo();
    });

    document.getElementById('formComponente').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarComponente();
    });

    document.getElementById('btnConfirmarEliminar').addEventListener('click', function() {
        confirmarEliminacion();
    });

    // Filtros para activos
    var buscarActivos = document.getElementById('buscarActivos');
    if (buscarActivos) {
        buscarActivos.addEventListener('input', function() {
            activosPage = 1;
            aplicarFiltrosActivos();
        });
    }

    var filtroEstadoActivos = document.getElementById('filtroEstadoActivos');
    if (filtroEstadoActivos) {
        filtroEstadoActivos.addEventListener('change', function() {
            activosPage = 1;
            aplicarFiltrosActivos();
        });
    }

    // Filtros para componentes
    var buscarComponentes = document.getElementById('buscarComponentes');
    if (buscarComponentes) {
        buscarComponentes.addEventListener('input', function() {
            componentesPage = 1;
            aplicarFiltrosComponentes();
        });
    }

    var filtroTipoComponentes = document.getElementById('filtroTipoComponentes');
    if (filtroTipoComponentes) {
        filtroTipoComponentes.addEventListener('change', function() {
            componentesPage = 1;
            aplicarFiltrosComponentes();
        });
    }

    var filtroEstadoComponentes = document.getElementById('filtroEstadoComponentes');
    if (filtroEstadoComponentes) {
        filtroEstadoComponentes.addEventListener('change', function() {
            componentesPage = 1;
            aplicarFiltrosComponentes();
        });
    }

    // Validar serial activo en tiempo real
    var serialInput = document.getElementById('activo_serial');
    if (serialInput) {
        serialInput.addEventListener('blur', function() { validarSerialActivo(); });
    }

    document.addEventListener('click', function(e) {
        var dropdown = document.getElementById('modeloDropdown');
        var input = document.getElementById('activo_modelo_buscar');
        if (dropdown && input && e.target !== input && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Botón de cambio de estado
    document.getElementById('btnConfirmarCambioEstado')?.addEventListener('click', function() {
        confirmarCambioEstado();
    });
});

// ==================== UTILIDADES ====================
function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function mostrarToast(mensaje, tipo) {
    tipo = tipo || 'success';
    var colores = { success: '#1e7e34', error: '#c5221f', warning: '#f6c23e', info: '#1e3c72' };
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:10000;background:' + colores[tipo] + ';color:white;padding:12px 20px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);animation:slideIn 0.3s ease-out;cursor:pointer;';
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}

function getEstadoBadge(estado) {
    var map = { 'en_bodega': 'badge-en-bodega', 'instalado': 'badge-instalado', 'prestado': 'badge-prestado', 'en_reparacion': 'badge-en-reparacion', 'desechado': 'badge-desechado' };
    return map[estado] || 'bg-secondary text-white';
}

function getEstadoLabel(estado) {
    var map = { 'en_bodega': 'En Bodega', 'instalado': 'Instalado', 'prestado': 'Prestado', 'en_reparacion': 'En Reparación', 'desechado': 'Desechado' };
    return map[estado] || estado;
}

function garantiaVencida(fecha) {
    if (!fecha) return false;
    return new Date(fecha) < new Date();
}

function getColorByEstado(estado) {
    switch(estado) {
        case 'Disponible': return '#28a745';
        case 'Prestado': return '#ffc107';
        case 'En reparación': return '#fd7e14';
        case 'Desechado': return '#dc3545';
        case 'En bodega': return '#6c757d';
        default: return '#1e3c72';
    }
}

function getEstadoComponenteClass(estado) {
    switch(estado) {
        case 'instalado': return 'componente-estado-instalado';
        case 'en_bodega': return 'componente-estado-bodega';
        case 'prestado': return 'componente-estado-prestado';
        case 'en_reparacion': return 'componente-estado-reparacion';
        case 'desechado': return 'componente-estado-desechado';
        default: return 'componente-estado-default';
    }
}

function getEstadoComponenteTexto(estado) {
    switch(estado) {
        case 'instalado': return 'Instalado';
        case 'en_bodega': return 'En Bodega';
        case 'prestado': return 'Prestado';
        case 'en_reparacion': return 'En Reparación';
        case 'desechado': return 'Desechado';
        default: return estado || 'N/A';
    }
}

function renderComponentesInstalados(componentes) {
    if (!componentes || componentes.length === 0) {
        return '<div class="text-center py-4 text-muted"><i class="fas fa-info-circle"></i> No hay componentes instalados</div>';
    }

    var html = '<div class="componentes-grid">';
    for (var i = 0; i < componentes.length; i++) {
        var c = componentes[i];
        var estadoClass = getEstadoComponenteClass(c.estado);
        var estadoTexto = getEstadoComponenteTexto(c.estado);

        html += `
            <div class="componente-card">
                <div class="componente-card-header">
                    <div class="componente-tipo">
                        <i class="fas fa-microchip"></i> ${escapeHtml(c.tipo)}
                    </div>
                    <span class="componente-estado ${estadoClass}">${estadoTexto}</span>
                </div>
                <div class="componente-card-body">
                    <div class="componente-info">
                        <span class="componente-label">Marca:</span>
                        <span class="componente-value">${escapeHtml(c.marca || 'N/A')}</span>
                    </div>
                    ${c.serial ? `
                    <div class="componente-info">
                        <span class="componente-label">Serial:</span>
                        <span class="componente-value componente-serial">${escapeHtml(c.serial)}</span>
                    </div>
                    ` : ''}
                    ${c.capacidad ? `
                    <div class="componente-info">
                        <span class="componente-label">Capacidad:</span>
                        <span class="componente-value">${escapeHtml(c.capacidad)}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
    html += '</div>';
    return html;
}

function renderComponentesModelo(componentes) {
    if (!componentes || componentes.length === 0) {
        return '<div class="text-center py-4 text-muted"><i class="fas fa-info-circle"></i> No hay componentes definidos para este modelo</div>';
    }

    var html = '<div class="componentes-modelo-grid">';
    for (var i = 0; i < componentes.length; i++) {
        var c = componentes[i];
        html += `
            <div class="componente-modelo-card">
                <div class="componente-modelo-tipo">
                    <i class="fas fa-cog"></i> ${escapeHtml(c.tipo)}
                </div>
                <div class="componente-modelo-descripcion">
                    ${escapeHtml(c.descripcion)}
                </div>
                ${c.capacidad ? `<div class="componente-modelo-capacidad"><i class="fas fa-tachometer-alt"></i> ${escapeHtml(c.capacidad)}</div>` : ''}
            </div>
        `;
    }
    html += '</div>';
    return html;
}

function agregarEstilosDetalle() {
    if (document.getElementById('detalle-activo-styles')) return;

    var styles = `
        <style id="detalle-activo-styles">
            .detalle-activo-moderno {
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
            }

            .detalle-seccion {
                background: #ffffff;
                border-radius: 16px;
                padding: 1rem;
                border: 1px solid #e9ecef;
            }

            .detalle-seccion-titulo {
                font-size: 0.85rem;
                font-weight: 600;
                color: #1e3c72;
                margin-bottom: 1rem;
                padding-bottom: 0.5rem;
                border-bottom: 2px solid #eef2f6;
                display: flex;
                align-items: center;
            }

            .detalle-seccion-titulo i {
                color: #1e3c72;
            }

            .detalle-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }

            .detalle-item {
                padding: 0.5rem;
                background: #f8f9fc;
                border-radius: 12px;
                transition: all 0.2s ease;
            }

            .detalle-item:hover {
                background: #eef3fc;
                transform: translateY(-1px);
            }

            .detalle-label {
                font-size: 0.65rem;
                text-transform: uppercase;
                color: #6c757d;
                letter-spacing: 0.5px;
                margin-bottom: 0.25rem;
                display: flex;
                align-items: center;
                gap: 0.25rem;
            }

            .detalle-label i {
                font-size: 0.7rem;
                color: #1e3c72;
            }

            .detalle-valor {
                font-weight: 600;
                color: #1a1a1a;
                font-size: 0.9rem;
                word-break: break-word;
            }

            .detalle-observaciones {
                background: #f8f9fc;
                padding: 1rem;
                border-radius: 12px;
                font-size: 0.85rem;
                color: #495057;
                line-height: 1.5;
            }

            .nav-tabs-componentes {
                border-bottom: 2px solid #e9ecef;
                margin-bottom: 0;
            }

            .nav-tabs-componentes .nav-link {
                border: none;
                background: transparent;
                padding: 0.6rem 1.2rem;
                font-weight: 500;
                color: #6c757d;
                position: relative;
                transition: all 0.2s ease;
            }

            .nav-tabs-componentes .nav-link:hover {
                color: #1e3c72;
                background: #f8f9fc;
            }

            .nav-tabs-componentes .nav-link.active {
                color: #1e3c72;
                background: transparent;
            }

            .nav-tabs-componentes .nav-link.active::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                right: 0;
                height: 2px;
                background: #1e3c72;
                border-radius: 2px;
            }

            .badge-componentes {
                background: #e9ecef;
                color: #495057;
                padding: 0.15rem 0.5rem;
                border-radius: 20px;
                font-size: 0.65rem;
                margin-left: 0.5rem;
            }

            .componentes-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 1rem;
            }

            .componente-card {
                background: #ffffff;
                border: 1px solid #e9ecef;
                border-radius: 12px;
                overflow: hidden;
                transition: all 0.2s ease;
            }

            .componente-card:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                transform: translateY(-2px);
            }

            .componente-card-header {
                padding: 0.75rem 1rem;
                background: #f8f9fc;
                border-bottom: 1px solid #e9ecef;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .componente-tipo {
                font-weight: 600;
                color: #1e3c72;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .componente-tipo i {
                font-size: 0.9rem;
            }

            .componente-estado {
                padding: 0.2rem 0.6rem;
                border-radius: 20px;
                font-size: 0.65rem;
                font-weight: 600;
            }

            .componente-estado-instalado {
                background: #d4edda;
                color: #155724;
            }

            .componente-estado-bodega {
                background: #e2e3e5;
                color: #383d41;
            }

            .componente-estado-prestado {
                background: #fff3cd;
                color: #856404;
            }

            .componente-estado-reparacion {
                background: #f8d7da;
                color: #721c24;
            }

            .componente-card-body {
                padding: 0.75rem 1rem;
            }

            .componente-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 0.5rem;
                font-size: 0.8rem;
            }

            .componente-label {
                color: #6c757d;
            }

            .componente-value {
                font-weight: 500;
                color: #1a1a1a;
            }

            .componente-serial {
                font-family: 'Courier New', monospace;
                font-size: 0.75rem;
            }

            .componentes-modelo-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 1rem;
            }

            .componente-modelo-card {
                background: #f8f9fc;
                border-radius: 12px;
                padding: 1rem;
                text-align: center;
                transition: all 0.2s ease;
                border: 1px solid #e9ecef;
            }

            .componente-modelo-card:hover {
                background: #eef3fc;
                transform: translateY(-2px);
            }

            .componente-modelo-tipo {
                font-weight: 700;
                color: #1e3c72;
                margin-bottom: 0.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }

            .componente-modelo-descripcion {
                font-size: 0.75rem;
                color: #6c757d;
                margin-bottom: 0.5rem;
            }

            .componente-modelo-capacidad {
                font-size: 0.7rem;
                color: #28a745;
                background: #d4edda;
                display: inline-block;
                padding: 0.2rem 0.6rem;
                border-radius: 20px;
            }

            .detalle-acciones .btn-editar-detalle {
                background: #1e3c72;
                border: none;
                color: white;
                padding: 0.5rem 1.2rem;
                border-radius: 30px;
                font-size: 0.8rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .detalle-acciones .btn-editar-detalle:hover {
                background: #2a5298;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
            }

            .detalle-acciones .btn-cerrar-detalle {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                color: #495057;
                padding: 0.5rem 1.2rem;
                border-radius: 30px;
                font-size: 0.8rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .detalle-acciones .btn-cerrar-detalle:hover {
                background: #e9ecef;
                border-color: #ced4da;
            }

            .badge-garantia-vencida {
                background: #f8d7da;
                color: #721c24;
                padding: 0.2rem 0.5rem;
                border-radius: 20px;
                font-size: 0.7rem;
            }

            .badge-garantia-vigente {
                background: #d4edda;
                color: #155724;
                padding: 0.2rem 0.5rem;
                border-radius: 20px;
                font-size: 0.7rem;
            }

            @media (max-width: 768px) {
                .detalle-grid {
                    grid-template-columns: 1fr;
                }

                .componentes-grid,
                .componentes-modelo-grid {
                    grid-template-columns: 1fr;
                }

                .nav-tabs-componentes .nav-link {
                    padding: 0.4rem 0.8rem;
                    font-size: 0.75rem;
                }
            }
        </style>
    `;

    document.head.insertAdjacentHTML('beforeend', styles);
}

window.cerrarModalDetalleManual = function() {
    var modalElement = document.getElementById('modalDetalle');
    var modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
        modal.hide();
    }
};

// ==================== CARGAR ESTADOS ====================
function cargarEstados() {
    return fetch('/admin/estatus-list', { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(response) {
            if (response.success) {
                listaEstados = response.data;
                var filtroEstadoActivos = document.getElementById('filtroEstadoActivos');
                if (filtroEstadoActivos) {
                    filtroEstadoActivos.innerHTML = '<option value="">Todos los estados</option>';
                    listaEstados.forEach(function(estado) {
                        filtroEstadoActivos.innerHTML += '<option value="' + estado.descripcion + '">' + estado.descripcion + '</option>';
                    });
                }
                return listaEstados;
            }
            return [];
        });
}

// ==================== PAGINACIÓN ====================
function renderPaginacion(totalPages, currentPage, tipo) {
    if (totalPages <= 1) return '';
    var html = '<div class="pagination-bar"><div class="pagination-info">Página ' + currentPage + ' de ' + totalPages + '</div><div class="pagination-btns">';
    html += '<button class="pagination-btn' + (currentPage === 1 ? ' disabled' : '') + '" onclick="window.cambiarPaginaInv(\'' + tipo + '\',' + (currentPage - 1) + ')">«</button>';
    for (var i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += '<button class="pagination-btn' + (i === currentPage ? ' active' : '') + '" onclick="window.cambiarPaginaInv(\'' + tipo + '\',' + i + ')">' + i + '</button>';
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += '<span class="pagination-ellipsis">...</span>';
        }
    }
    html += '<button class="pagination-btn' + (currentPage === totalPages ? ' disabled' : '') + '" onclick="window.cambiarPaginaInv(\'' + tipo + '\',' + (currentPage + 1) + ')">»</button>';
    html += '</div></div>';
    return html;
}

window.cambiarPaginaInv = function(tipo, page) {
    if (tipo === 'activos') { activosPage = page; aplicarFiltrosActivos(); }
    else if (tipo === 'componentes') { componentesPage = page; aplicarFiltrosComponentes(); }
};

// ==================== VALIDACIÓN DE SERIAL ====================
function validarSerialActivo() {
    var serial = document.getElementById('activo_serial').value.trim();
    var id = document.getElementById('activoId').value;
    var feedback = document.getElementById('serialFeedback');

    if (!serial) {
        if (feedback) feedback.innerHTML = '';
        return;
    }

    if (!feedback) {
        feedback = document.createElement('div');
        feedback.id = 'serialFeedback';
        feedback.style.cssText = 'font-size:0.75rem;margin-top:4px;';
        document.getElementById('activo_serial').parentNode.appendChild(feedback);
    }

    feedback.innerHTML = '<span class="text-muted">Verificando...</span>';

    fetch('/admin/activos?buscar=' + encodeURIComponent(serial), { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var existe = response.data.some(function(a) { return a.serial === serial && a.id != id; });
            if (existe) {
                feedback.innerHTML = '<span class="text-danger">Este serial ya existe</span>';
                document.getElementById('activo_serial').style.borderColor = '#dc3545';
            } else {
                feedback.innerHTML = '<span class="text-success">Serial disponible</span>';
                document.getElementById('activo_serial').style.borderColor = '#28a745';
            }
        }
    });
}

// ==================== SELECTS BASE ====================
function cargarSelectsBase() {
    fetch('/admin/equipos/modelos', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) { if (response.success) { todosModelos = response.data; } });

    fetch('/admin/estatus-list', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var select = document.getElementById('activo_id_estatus');
            if (select) {
                select.innerHTML = '<option value="">Seleccionar...</option>';
                response.data.forEach(function(e) { select.innerHTML += '<option value="' + e.id + '">' + escapeHtml(e.descripcion) + '</option>'; });
                var disponible = response.data.find(function(e) { return e.descripcion === 'Disponible'; });
                if (disponible) select.value = disponible.id;
            }
        }
    }).catch(function() {
        var select = document.getElementById('activo_id_estatus');
        if (select) select.innerHTML = '<option value="">Seleccionar...</option><option value="1" selected>Disponible</option>';
    });

    fetch('/admin/instituciones', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var select1 = document.getElementById('activo_institucion_id');
            var select2 = document.getElementById('comp_institucion_id');
            if (select1) {
                select1.innerHTML = '<option value="">Seleccionar...</option>';
                response.data.forEach(function(i) { select1.innerHTML += '<option value="' + i.id + '" data-representante="' + escapeHtml(i.representante || '') + '">' + escapeHtml(i.nombre) + '</option>'; });
                var gob = response.data.find(function(i) { return i.nombre.toLowerCase().indexOf('gobernacion') >= 0 || i.nombre.toLowerCase().indexOf('informatica') >= 0; });
                if (gob) { select1.value = gob.id; cargarDepartamentosPorInstitucion(gob.id, 'activo_departamento_id', gob.representante); cargarResponsablesPorInstitucion(gob.id, 'activo_responsable_id', gob.representante); }
                select1.addEventListener('change', function() {
                    var opt = this.options[this.selectedIndex];
                    var rep = opt.getAttribute('data-representante') || '';
                    cargarDepartamentosPorInstitucion(this.value, 'activo_departamento_id', rep);
                    cargarResponsablesPorInstitucion(this.value, 'activo_responsable_id', rep);
                });
            }
            if (select2) {
                select2.innerHTML = '<option value="">Seleccionar...</option>';
                response.data.forEach(function(i) { select2.innerHTML += '<option value="' + i.id + '" data-representante="' + escapeHtml(i.representante || '') + '">' + escapeHtml(i.nombre) + '</option>'; });
                var gob = response.data.find(function(i) { return i.nombre.toLowerCase().indexOf('gobernacion') >= 0 || i.nombre.toLowerCase().indexOf('informatica') >= 0; });
                if (gob) { select2.value = gob.id; cargarResponsablesPorInstitucion(gob.id, 'comp_responsable_id', gob.representante); }
                select2.addEventListener('change', function() {
                    var opt = this.options[this.selectedIndex];
                    var rep = opt.getAttribute('data-representante') || '';
                    cargarResponsablesPorInstitucion(this.value, 'comp_responsable_id', rep);
                });
            }
        }
    });
}

function cargarDepartamentosPorInstitucion(institucionId, selectId, representanteInstitucion) {
    var select = document.getElementById(selectId);
    if (!select || !institucionId) return;
    fetch('/admin/departamentos/por-institucion/' + institucionId, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            select.innerHTML = '<option value="">Sin departamento</option>';
            response.data.forEach(function(d) { select.innerHTML += '<option value="' + d.id + '" data-representante="' + escapeHtml(d.representante || '') + '">' + escapeHtml(d.nombre) + '</option>'; });
            var info = response.data.find(function(d) { var n = d.nombre.toLowerCase(); return n.indexOf('informatica') >= 0 || n.indexOf('sistemas') >= 0 || n.indexOf('ti') >= 0; });
            if (!info && response.data.length > 0) info = response.data[0];
            if (info) { select.value = info.id; var resp = info.representante || representanteInstitucion || ''; cargarResponsablesPorInstitucion(institucionId, 'activo_responsable_id', resp); }
            select.addEventListener('change', function() {
                var opt = this.options[this.selectedIndex];
                var rep = opt.getAttribute('data-representante') || representanteInstitucion || '';
                cargarResponsablesPorInstitucion(institucionId, 'activo_responsable_id', rep);
            });
        }
    });
}

function cargarResponsablesPorInstitucion(institucionId, selectId, representanteSugerido) {
    var select = document.getElementById(selectId);
    if (!select || !institucionId) return;
    fetch('/admin/responsables?institucion_id=' + institucionId, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            select.innerHTML = '<option value="">Seleccionar...</option>';
            response.data.forEach(function(r) { select.innerHTML += '<option value="' + r.id + '">' + escapeHtml(r.nombre) + ' - ' + escapeHtml(r.cargo || 'Sin cargo') + '</option>'; });
            if (representanteSugerido && response.data.length > 0) {
                var encontrado = response.data.find(function(r) { return r.nombre.toLowerCase().indexOf(representanteSugerido.toLowerCase()) >= 0; });
                select.value = encontrado ? encontrado.id : response.data[0].id;
            }
        }
    });
}

// ==================== BUSCADOR DE MODELOS ====================
window.filtrarModelos = function() {
    var input = document.getElementById('activo_modelo_buscar');
    var dropdown = document.getElementById('modeloDropdown');
    var buscar = input.value.toLowerCase();
    if (!todosModelos.length) { dropdown.style.display = 'none'; return; }
    var filtrados = todosModelos.filter(function(m) {
        var texto = (m.marca ? m.marca.nombre + ' ' : '') + m.nombre + ' ' + (m.categoria ? m.categoria.nombre : '');
        return texto.toLowerCase().indexOf(buscar) >= 0;
    }).slice(0, 10);
    if (filtrados.length === 0) {
        dropdown.innerHTML = '<div class="list-group-item text-muted small">No se encontraron modelos</div>';
    } else {
        dropdown.innerHTML = filtrados.map(function(m) {
            return '<a href="#" class="list-group-item list-group-item-action py-2 px-3" onclick="seleccionarModelo(' + m.id + ', \'' + escapeHtml(m.marca ? m.marca.nombre + ' ' : '') + escapeHtml(m.nombre) + '\', \'' + escapeHtml(m.marca ? m.marca.nombre : '') + '\', \'' + escapeHtml(m.categoria ? m.categoria.nombre : '') + '\'); return false;">' +
                '<strong>' + escapeHtml(m.marca ? m.marca.nombre + ' ' : '') + escapeHtml(m.nombre) + '</strong>' +
                '<small class="d-block text-muted">' + escapeHtml(m.categoria ? m.categoria.nombre : '') + '</small>' +
            '</a>';
        }).join('');
    }
    dropdown.style.display = 'block';
};

window.seleccionarModelo = function(id, texto, marca, categoria) {
    document.getElementById('activo_modelo_id').value = id;
    document.getElementById('activo_modelo_buscar').value = texto;
    document.getElementById('modeloDropdown').style.display = 'none';
    var badges = document.getElementById('modeloInfoBadges');
    badges.innerHTML = '<span class="badge bg-primary-dark">' + escapeHtml(marca) + '</span> <span class="badge bg-secondary">' + escapeHtml(categoria) + '</span>';
    window.cargarComponentesDelModelo();
};

// ==================== COMPONENTES DEL MODELO ====================
window.cargarComponentesDelModelo = function() {
    var modeloId = document.getElementById('activo_modelo_id').value;
    var container = document.getElementById('componentesActivoContainer');
    if (!modeloId) { container.innerHTML = '<p class="text-muted text-center py-3">Seleccione un modelo para cargar sus componentes.</p>'; return; }
    container.innerHTML = '<p class="text-center py-3 text-muted">Cargando componentes...</p>';

    fetch('/admin/equipos/modelos/' + modeloId + '/componentes', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success && response.data && response.data.length > 0) {
            var html = '';
            response.data.forEach(function(comp, index) {
                html += renderComponenteItem(comp, index, false);
            });
            html += '<hr><div class="form-check mb-2">' +
                '<input class="form-check-input" type="checkbox" id="equipoCompleto" checked>' +
                '<label class="form-check-label" for="equipoCompleto">El equipo llegó con todos los componentes</label>' +
            '</div>' +
            '<div class="alert alert-warning py-2 px-3" id="alertaComponentes" style="display:none;font-size:0.85rem;">' +
                'Hay componentes requeridos sin completar. Verifique los datos antes de guardar.' +
            '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-muted text-center py-3">Este modelo no tiene componentes definidos. Puede guardar el activo sin componentes.</p>';
        }
    }).catch(function() { container.innerHTML = '<p class="text-danger text-center py-3">Error al cargar componentes.</p>'; });
};

function renderComponenteItem(comp, index, esExistente) {
    var compId = esExistente ? comp.id : '';
    var prefix = esExistente ? 'comp_existente_' + index : 'comp_nuevo_' + index;
    var extraClass = esExistente ? 'border-primary' : '';

    return '<div class="componente-activo-item border rounded p-3 mb-2 bg-light ' + extraClass + '" data-comp-id="' + compId + '" data-tipo="' + escapeHtml(comp.tipo) + '" data-requerido="true">' +
        '<div class="d-flex justify-content-between align-items-center mb-2">' +
            '<div>' +
                '<strong>' + escapeHtml(comp.tipo) + ' - ' + escapeHtml(comp.descripcion) + '</strong>' +
                (comp.capacidad ? '<span class="badge bg-secondary ms-2">' + escapeHtml(comp.capacidad) + '</span>' : '') +
                '<span class="badge bg-success ms-1" style="font-size:0.65rem;">Requerido</span>' +
            '</div>' +
            '<div class="d-flex gap-1">' +
                '<button type="button" class="btn btn-sm btn-outline-primary-dark" onclick="duplicarComponente(this)" title="Agregar otro igual">' + SVG_ICONS.plus + '</button>' +
                (!esExistente ? '<button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.componente-activo-item\').remove(); verificarAlertasComponentes()" title="Eliminar">' + SVG_ICONS.eliminar + '</button>' : '') +
            '</div>' +
        '</div>' +
        '<input type="hidden" name="' + prefix + '[modelo_componente_id]" value="' + (comp.modelo_componente_id || comp.id || '') + '">' +
        '<input type="hidden" name="' + prefix + '[tipo]" value="' + escapeHtml(comp.tipo) + '">' +
        '<input type="hidden" name="' + prefix + '[descripcion]" value="' + escapeHtml(comp.descripcion) + '">' +
        '<input type="hidden" name="' + prefix + '[capacidad]" value="' + escapeHtml(comp.capacidad || '') + '">' +
        (esExistente ? '<input type="hidden" name="' + prefix + '[id]" value="' + comp.id + '">' : '') +
        '<div class="row">' +
            '<div class="col-md-3 mb-2"><label class="form-label small">Marca</label><input type="text" class="form-control form-control-sm comp-marca" name="' + prefix + '[marca]" value="' + escapeHtml(comp.marca || '') + '" placeholder="Kingston, Samsung..."></div>' +
            '<div class="col-md-3 mb-2"><label class="form-label small">Serial</label><input type="text" class="form-control form-control-sm comp-serial" name="' + prefix + '[serial]" value="' + escapeHtml(comp.serial || '') + '" placeholder="ABC123"></div>' +
            '<div class="col-md-2 mb-2"><label class="form-label small">Capacidad Real</label><input type="text" class="form-control form-control-sm" name="' + prefix + '[capacidad_real]" value="' + escapeHtml(comp.capacidad_real || comp.capacidad || '') + '" placeholder="8GB"></div>' +
            '<div class="col-md-2 mb-2"><label class="form-label small">Estado</label><select class="form-select form-select-sm comp-estado" name="' + prefix + '[estado]"><option value="instalado" selected>Instalado</option><option value="en_bodega">En Bodega</option></select></div>' +
            '<div class="col-md-2 mb-2 d-flex align-items-end"><div class="form-check"><input class="form-check-input comp-check" type="checkbox" checked onchange="verificarAlertasComponentes()"><label class="form-check-label small">Registrado</label></div></div>' +
        '</div>' +
    '</div>';
}

window.duplicarComponente = function(btn) {
    var item = btn.closest('.componente-activo-item');
    var clone = item.cloneNode(true);
    clone.querySelectorAll('input[type="text"]').forEach(function(i) { i.value = ''; });
    clone.querySelectorAll('.comp-marca').forEach(function(i) { i.value = ''; });
    clone.querySelectorAll('.comp-serial').forEach(function(i) { i.value = ''; });
    clone.querySelectorAll('.comp-check').forEach(function(i) { i.checked = true; });
    clone.querySelector('select.comp-estado').value = 'instalado';
    clone.classList.remove('border-primary');
    clone.querySelectorAll('input, select').forEach(function(input) {
        var name = input.name;
        if (name) {
            name = name.replace(/comp_existente_\d+/, 'comp_nuevo_' + Date.now());
            input.name = name;
        }
    });
    var dupBtn = clone.querySelector('button[onclick*="duplicarComponente"]');
    if (dupBtn) dupBtn.remove();
    item.parentNode.insertBefore(clone, item.nextSibling);
    verificarAlertasComponentes();
};

window.verificarAlertasComponentes = function() {
    var items = document.querySelectorAll('#componentesActivoContainer .componente-activo-item');
    var faltantes = false;
    items.forEach(function(item) {
        var check = item.querySelector('.comp-check');
        if (check && !check.checked) faltantes = true;
    });
    var alerta = document.getElementById('alertaComponentes');
    if (alerta) alerta.style.display = faltantes ? 'block' : 'none';
};

// ==================== FILTROS ====================
function aplicarFiltrosActivos() {
    var buscar = document.getElementById('buscarActivos') ? document.getElementById('buscarActivos').value.toLowerCase() : '';
    var filtroEstado = document.getElementById('filtroEstadoActivos') ? document.getElementById('filtroEstadoActivos').value : '';

    var filtrados = activosData.filter(function(a) {
        var coincideBuscar = !buscar ||
            (a.serial && a.serial.toLowerCase().indexOf(buscar) >= 0) ||
            (a.modelo && a.modelo.nombre && a.modelo.nombre.toLowerCase().indexOf(buscar) >= 0) ||
            (a.modelo && a.modelo.marca && a.modelo.marca.nombre && a.modelo.marca.nombre.toLowerCase().indexOf(buscar) >= 0);

        var coincideEstado = !filtroEstado || (a.estatus && a.estatus.descripcion === filtroEstado);

        return coincideBuscar && coincideEstado;
    });

    renderizarActivosFiltrados(filtrados);
}

function aplicarFiltrosComponentes() {
    var buscar = document.getElementById('buscarComponentes') ? document.getElementById('buscarComponentes').value.toLowerCase() : '';
    var filtroTipo = document.getElementById('filtroTipoComponentes') ? document.getElementById('filtroTipoComponentes').value : '';
    var filtroEstado = document.getElementById('filtroEstadoComponentes') ? document.getElementById('filtroEstadoComponentes').value : '';

    var tiposUnicos = [];
    componentesData.forEach(function(c) { if (c.tipo && tiposUnicos.indexOf(c.tipo) < 0) tiposUnicos.push(c.tipo); });
    var selectTipo = document.getElementById('filtroTipoComponentes');
    if (selectTipo && selectTipo.options.length <= 1) {
        selectTipo.innerHTML = '<option value="">Todos los tipos</option>';
        tiposUnicos.sort().forEach(function(t) { selectTipo.innerHTML += '<option value="' + t + '">' + t + '</option>'; });
    }

    var filtrados = componentesData.filter(function(c) {
        var coincideBuscar = !buscar ||
            (c.tipo && c.tipo.toLowerCase().indexOf(buscar) >= 0) ||
            (c.marca && c.marca.toLowerCase().indexOf(buscar) >= 0) ||
            (c.serial && c.serial.toLowerCase().indexOf(buscar) >= 0);

        var coincideTipo = !filtroTipo || (c.tipo === filtroTipo);
        var coincideEstado = !filtroEstado || (c.estado === filtroEstado);

        return coincideBuscar && coincideTipo && coincideEstado;
    });

    renderizarComponentesFiltrados(filtrados);
}

function renderizarActivosFiltrados(filtrados) {
    var tbody = document.getElementById('tablaActivos');
    if (!tbody) return;

    var totalPages = Math.ceil(filtrados.length / activosPerPage);
    var start = (activosPage - 1) * activosPerPage;
    var pageData = filtrados.slice(start, start + activosPerPage);

    if (pageData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron activos</td>' + '</tr>';
        return;
    }

    var puedeCambiarEstadoGlobal = typeof authUserHasPermission !== 'undefined' ? authUserHasPermission('cambiar-estatus-activo') : true;

    var html = '';
    for (var i = 0; i < pageData.length; i++) {
        var a = pageData[i];
        var garantiaBadge = '';
        if (a.fecha_fin_garantia) {
            garantiaBadge = garantiaVencida(a.fecha_fin_garantia) ?
                '<span class="badge badge-garantia-vencida ms-1">Vencida</span>' :
                '<span class="badge badge-garantia-vigente ms-1">Vigente</span>';
        }
        var compCount = a.componentes ? a.componentes.length : 0;
        var estadoDescripcion = a.estatus ? a.estatus.descripcion : 'N/A';
        var colorBadge = a.estatus ? a.estatus.color_badge : 'secondary';
        var esTerminal = a.estatus ? a.estatus.es_terminal : false;
        var mostrarBotonEstado = puedeCambiarEstadoGlobal && !esTerminal;

        html += '<tr>' +
            '<td><strong>' + escapeHtml(a.serial) + '</strong>' + garantiaBadge + '</td>' +
            '<td>' + escapeHtml(a.modelo ? a.modelo.nombre : 'N/A') + (compCount > 0 ? ' <span class="badge bg-info text-dark">' + compCount + '</span>' : '') + '</td>' +
            '<td>' + escapeHtml(a.modelo && a.modelo.marca ? a.modelo.marca.nombre : 'N/A') + '</td>' +
            '<td><span class="badge bg-' + colorBadge + '">' + escapeHtml(estadoDescripcion) + '</span></td>' +
            '<td>' + escapeHtml(a.ubicacion || (a.institucion ? a.institucion.nombre : 'N/A')) + '</td>' +
            '<td class="text-end">' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="verActivo(' + a.id + ')" title="Ver detalle">' + SVG_ICONS.ver + '</button> ' +
                (window.authUserHasPermission && authUserHasPermission('editar-activo') ? '<button class="btn btn-sm btn-outline-primary-dark" onclick="editarActivo(' + a.id + ')" title="Editar">' + SVG_ICONS.editar + '</button> ' : '') +
                (mostrarBotonEstado ? '<button class="btn btn-sm btn-cambiar-estado" onclick="abrirModalCambiarEstado(' + a.id + ', \'' + escapeHtml(a.serial) + '\', \'' + estadoDescripcion + '\', ' + (a.estatus ? a.estatus.id : 'null') + ')" title="Cambiar estado">' + SVG_ICONS.cambiarEstado + '</button> ' : '') +
                (window.authUserHasPermission && authUserHasPermission('eliminar-activo') ? '<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminarActivo(' + a.id + ')" title="Eliminar">' + SVG_ICONS.eliminar + '</button>' : '') +
            '</td>' +
        '<\/tr>';
    }
    html += '<tr><td colspan="6">' + renderPaginacion(totalPages, activosPage, 'activos') + '</td><\/tr>';
    tbody.innerHTML = html;
}

function renderizarComponentesFiltrados(filtrados) {
    var tbody = document.getElementById('tablaComponentes');
    if (!tbody) return;

    var totalPages = Math.ceil(filtrados.length / componentesPerPage);
    var start = (componentesPage - 1) * componentesPerPage;
    var pageData = filtrados.slice(start, start + componentesPerPage);

    if (pageData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron componentes</td><\/tr>';
        return;
    }

    var html = '';
    for (var i = 0; i < pageData.length; i++) {
        var c = pageData[i];
        html += '<tr>' +
            '<td><strong>' + escapeHtml(c.tipo) + '<\/strong><\/td>' +
            '<td>' + escapeHtml(c.marca || 'N/A') + '<\/td>' +
            '<td>' + escapeHtml(c.serial || 'N/A') + '<\/td>' +
            '<td>' + escapeHtml(c.capacidad || 'N/A') + '<\/td>' +
            '<td><span class="badge ' + getEstadoBadge(c.estado) + '">' + getEstadoLabel(c.estado) + '<\/span><\/td>' +
            '<td>' + (c.activo ? '<a href="#" onclick="verActivo(' + c.activo.id + '); return false;" class="text-decoration-none">' + escapeHtml(c.activo.serial) + '<\/a>' : '—') + '<\/td>' +
            '<td class="text-end">' +
                (window.authUserHasPermission && authUserHasPermission('editar-componente') ? '<button class="btn btn-sm btn-outline-primary-dark" onclick="editarComponente(' + c.id + ')" title="Editar">' + SVG_ICONS.editar + '<\/button> ' : '') +
                (window.authUserHasPermission && authUserHasPermission('eliminar-componente') ? '<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminarComponente(' + c.id + ')" title="Eliminar">' + SVG_ICONS.eliminar + '<\/button>' : '') +
            '<\/td>' +
        '<\/tr>';
    }
    html += '<tr><td colspan="7">' + renderPaginacion(totalPages, componentesPage, 'componentes') + '<\/td><\/tr>';
    tbody.innerHTML = html;
}

// ==================== ACTIVOS ====================
function cargarActivos() {
    fetch('/admin/activos', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            activosData = response.data;
            activosPage = 1;
            aplicarFiltrosActivos();
        }
    });
}

window.abrirModalActivo = function(id) {
    var modal = new bootstrap.Modal(document.getElementById('modalActivo'));
    document.getElementById('formActivo').reset();
    document.getElementById('activoId').value = '';
    document.getElementById('activo_modelo_id').value = '';
    document.getElementById('activo_modelo_buscar').value = '';
    document.getElementById('modeloDropdown').style.display = 'none';
    document.getElementById('modeloInfoBadges').innerHTML = '';
    document.getElementById('modalActivoLabel').textContent = 'Nuevo Activo';
    document.getElementById('componentesActivoContainer').innerHTML = '<p class="text-muted text-center py-3">Seleccione un modelo para cargar sus componentes.</p>';
    document.getElementById('activo_serial').style.borderColor = '';
    var feedback = document.getElementById('serialFeedback');
    if (feedback) feedback.innerHTML = '';
    cargarSelectsBase();

    if (id) {
        document.getElementById('modalActivoLabel').textContent = 'Editar Activo';
        document.getElementById('activoId').value = id;
        fetch('/admin/activos/' + id, { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(response) {
            if (response.success) {
                var a = response.data;
                document.getElementById('activo_serial').value = a.serial || '';
                document.getElementById('activo_modelo_id').value = a.modelo_id || '';
                document.getElementById('activo_modelo_buscar').value = a.modelo ? (a.modelo.marca ? a.modelo.marca.nombre + ' ' : '') + a.modelo.nombre : '';
                document.getElementById('activo_id_estatus').value = a.id_estatus || '';
                document.getElementById('activo_institucion_id').value = a.institucion_id || '';
                document.getElementById('activo_responsable_id').value = a.responsable_id || '';
                document.getElementById('activo_ubicacion').value = a.ubicacion || '';
                document.getElementById('activo_fecha_adquisicion').value = a.fecha_adquisicion || '';
                document.getElementById('activo_fecha_fin_garantia').value = a.fecha_fin_garantia || '';
                document.getElementById('activo_vida_util_anos').value = a.vida_util_anos || '';
                document.getElementById('activo_observaciones').value = a.observaciones || '';
                if (a.modelo) {
                    document.getElementById('modeloInfoBadges').innerHTML = '<span class="badge bg-primary-dark">' + escapeHtml(a.modelo.marca ? a.modelo.marca.nombre : '') + '</span> <span class="badge bg-secondary">' + escapeHtml(a.modelo.categoria ? a.modelo.categoria.nombre : '') + '</span>';
                }
                if (a.componentes && a.componentes.length > 0) {
                    var html = '';
                    a.componentes.forEach(function(comp, index) {
                        comp.modelo_componente_id = comp.modelo_componente_id;
                        html += renderComponenteItem(comp, index, true);
                    });
                    html += '<hr><div class="form-check mb-2">' +
                        '<input class="form-check-input" type="checkbox" id="equipoCompleto" checked>' +
                        '<label class="form-check-label" for="equipoCompleto">El equipo tiene todos los componentes</label>' +
                    '</div>' +
                    '<div class="alert alert-warning py-2 px-3" id="alertaComponentes" style="display:none;font-size:0.85rem;">Hay componentes sin verificar.</div>';
                    document.getElementById('componentesActivoContainer').innerHTML = html;
                } else {
                    window.cargarComponentesDelModelo();
                }
            }
        });
    }
    modal.show();
    setTimeout(function() { document.getElementById('activo_serial').focus(); }, 500);
};

window.editarActivo = function(id) { window.abrirModalActivo(id); };

window.verActivo = function(id) {
    fetch('/admin/activos/' + id, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var a = response.data;

            // Formatear fechas
            var fechaAdquisicion = a.fecha_adquisicion ? new Date(a.fecha_adquisicion).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' }) : 'No registrada';
            var fechaGarantia = a.fecha_fin_garantia ? new Date(a.fecha_fin_garantia).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' }) : 'No registrada';

            // Calcular si garantía está vencida
            var garantiaVencidaFlag = a.fecha_fin_garantia && new Date(a.fecha_fin_garantia) < new Date();
            var garantiaBadge = garantiaVencidaFlag ?
                '<span class="badge-garantia-vencida ms-2"><i class="fas fa-exclamation-triangle"></i> Vencida</span>' :
                (a.fecha_fin_garantia ? '<span class="badge-garantia-vigente ms-2"><i class="fas fa-check-circle"></i> Vigente</span>' : '');

            var estadoIcono = '';
            switch(a.estatus?.descripcion) {
                case 'Disponible': estadoIcono = '<i class="fas fa-check-circle"></i> '; break;
                case 'Prestado': estadoIcono = '<i class="fas fa-hand-holding"></i> '; break;
                case 'En reparación': estadoIcono = '<i class="fas fa-tools"></i> '; break;
                case 'Desechado': estadoIcono = '<i class="fas fa-trash-alt"></i> '; break;
                default: estadoIcono = '<i class="fas fa-circle"></i> ';
            }

            // Agregar estilos CSS
            agregarEstilosDetalle();

            var html = `
                <div class="detalle-activo-moderno">
                    <div class="detalle-header-moderno" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); margin: -1.5rem -1.5rem 1.5rem -1.5rem; padding: 1.5rem; border-radius: 12px 12px 0 0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-microchip" style="font-size: 1.8rem; color: #ffcd3c;"></i>
                                    <h4 class="mb-0 text-white">${escapeHtml(a.serial)}</h4>
                                </div>
                                <p class="mb-0 text-white-50">
                                    <i class="fas fa-tag me-1"></i> ${escapeHtml(a.modelo?.marca?.nombre || 'N/A')} ${escapeHtml(a.modelo?.nombre || 'N/A')}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-folder me-1"></i> ${escapeHtml(a.modelo?.categoria?.nombre || 'N/A')}
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="badge-estado-detalle" style="background: ${getColorByEstado(a.estatus?.descripcion)}; color: white; padding: 0.5rem 1rem; border-radius: 30px; font-size: 0.8rem;">
                                    ${estadoIcono} ${escapeHtml(a.estatus?.descripcion || 'N/A')}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="detalle-seccion">
                                <h6 class="detalle-seccion-titulo">
                                    <i class="fas fa-info-circle me-2"></i>Información General
                                </h6>
                                <div class="detalle-grid">
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-barcode"></i> Número de Serie</div>
                                        <div class="detalle-valor">${escapeHtml(a.serial)}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-building"></i> Institución</div>
                                        <div class="detalle-valor">${escapeHtml(a.institucion?.nombre || 'N/A')}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-map-marker-alt"></i> Ubicación</div>
                                        <div class="detalle-valor">${escapeHtml(a.ubicacion || 'No especificada')}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-user"></i> Responsable</div>
                                        <div class="detalle-valor">${escapeHtml(a.responsable?.nombre || 'No asignado')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-seccion">
                                <h6 class="detalle-seccion-titulo">
                                    <i class="fas fa-calendar-alt me-2"></i>Información de Adquisición
                                </h6>
                                <div class="detalle-grid">
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-shopping-cart"></i> Fecha Adquisición</div>
                                        <div class="detalle-valor">${fechaAdquisicion}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-shield-alt"></i> Fin de Garantía</div>
                                        <div class="detalle-valor">${fechaGarantia} ${garantiaBadge}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-hourglass-half"></i> Vida Útil Estimada</div>
                                        <div class="detalle-valor">${a.vida_util_anos ? a.vida_util_anos + ' años' : 'No especificada'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    ${a.observaciones ? `
                    <div class="detalle-seccion mb-4">
                        <h6 class="detalle-seccion-titulo">
                            <i class="fas fa-sticky-note me-2"></i>Observaciones
                        </h6>
                        <div class="detalle-observaciones">
                            ${escapeHtml(a.observaciones)}
                        </div>
                    </div>
                    ` : ''}

                    <div class="detalle-seccion">
                        <ul class="nav nav-tabs nav-tabs-componentes" id="componentesTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="instalados-tab" data-bs-toggle="tab" data-bs-target="#instalados" type="button" role="tab">
                                    <i class="fas fa-microchip me-1"></i> Componentes Instalados
                                    <span class="badge-componentes">${a.componentes ? a.componentes.length : 0}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="modelo-tab" data-bs-toggle="tab" data-bs-target="#modelo" type="button" role="tab">
                                    <i class="fas fa-cube me-1"></i> Componentes del Modelo
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content p-3">
                            <div class="tab-pane fade show active" id="instalados" role="tabpanel">
                                ${renderComponentesInstalados(a.componentes)}
                            </div>
                            <div class="tab-pane fade" id="modelo" role="tabpanel">
                                <div id="detalleCompModeloContent" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Cargando componentes del modelo...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="detalle-acciones mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-end gap-2">
                            ${window.authUserHasPermission && authUserHasPermission('editar-activo') ?
                                `<button class="btn btn-editar-detalle" onclick="editarActivo(${a.id}); bootstrap.Modal.getInstance(document.getElementById('modalDetalle')).hide();">
                                    <i class="fas fa-edit"></i> Editar Activo
                                </button>` : ''}
                            <button class="btn btn-cerrar-detalle" onclick="cerrarModalDetalleManual()">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('modalDetalleLabel').textContent = 'Detalle del Activo';
            document.getElementById('detalleContenido').innerHTML = html;

            var modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
            modal.show();

            if (a.modelo_id) {
                fetch('/admin/equipos/modelos/' + a.modelo_id + '/componentes', { headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(response) {
                    if (response.success && response.data && response.data.length > 0) {
                        var modelHtml = renderComponentesModelo(response.data);
                        document.getElementById('detalleCompModeloContent').innerHTML = modelHtml;
                    } else {
                        document.getElementById('detalleCompModeloContent').innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-info-circle"></i> Este modelo no tiene componentes definidos</div>';
                    }
                })
                .catch(function() {
                    document.getElementById('detalleCompModeloContent').innerHTML = '<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle"></i> Error al cargar componentes</div>';
                });
            }
        }
    });
};

function recolectarComponentesFormulario() {
    var componentes = [];
    var items = document.querySelectorAll('#componentesActivoContainer .componente-activo-item');
    items.forEach(function(item) {
        var inputs = item.querySelectorAll('input, select');
        var comp = {};
        inputs.forEach(function(input) {
            var name = input.name;
            if (name) {
                var match = name.match(/comp_(?:existente|nuevo)_\d+\[(\w+)\]/);
                if (match) {
                    comp[match[1]] = input.type === 'checkbox' ? input.checked : input.value;
                }
            }
        });
        if (comp.tipo) {
            comp.activo_id = document.getElementById('activoId').value;
            comp.institucion_id = document.getElementById('activo_institucion_id').value;
            comp.responsable_id = document.getElementById('activo_responsable_id').value;
            comp.ubicacion = comp.estado === 'en_bodega' ? 'Bodega Central' : document.getElementById('activo_ubicacion').value;
            componentes.push(comp);
        }
    });
    return componentes;
}

function guardarActivo() {
    var id = document.getElementById('activoId').value;
    var url = id ? '/admin/activos/' + id : '/admin/activos';
    var formData = new FormData(document.getElementById('formActivo'));

    // ✅ CORRECCIÓN IMPORTANTE: Agregar _method=PUT para actualizar
    if (id) {
        formData.append('_method', 'PUT');
    }

    var componentes = recolectarComponentesFormulario();
    var equipoCompleto = document.getElementById('equipoCompleto') ? document.getElementById('equipoCompleto').checked : true;

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': getCsrfToken() },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var activoId = id || (response.data ? response.data.id : null);

            // Si hay componentes, guardarlos
            if (activoId && componentes.length > 0) {
                var promesas = componentes.map(function(comp) {
                    comp.activo_id = activoId;
                    var compUrl = comp.id ? '/admin/componentes/' + comp.id : '/admin/componentes';
                    var method = comp.id ? 'PUT' : 'POST';
                    if (method === 'PUT') comp._method = 'PUT';
                    return fetch(compUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(comp)
                    }).then(function(r) { return r.json(); });
                });

                Promise.all(promesas).then(function() {
                    var msg = id ? 'Activo actualizado con ' + componentes.length + ' componentes. ' : 'Activo guardado con ' + componentes.length + ' componentes. ';
                    msg += equipoCompleto ? 'Equipo marcado como completo.' : 'Equipo marcado como incompleto.';
                    mostrarToast(msg, 'success');
                }).catch(function() {
                    mostrarToast(id ? 'Activo actualizado. Revisar componentes' : 'Activo guardado. Revisar componentes', 'warning');
                });
            } else {
                mostrarToast(response.message || (id ? 'Activo actualizado' : 'Activo guardado'), 'success');
            }

            bootstrap.Modal.getInstance(document.getElementById('modalActivo')).hide();
            cargarActivos();
            cargarComponentes();
        } else {
            mostrarToast(response.message || (id ? 'Error al actualizar' : 'Error al guardar'), 'error');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión', 'error');
    });
}

// ==================== COMPONENTES ====================
function cargarComponentes() {
    fetch('/admin/componentes', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            componentesData = response.data;
            componentesPage = 1;
            aplicarFiltrosComponentes();
        }
    });
}

window.abrirModalComponente = function(id) {
    var modal = new bootstrap.Modal(document.getElementById('modalComponente'));
    document.getElementById('formComponente').reset();
    document.getElementById('componenteId').value = '';
    document.getElementById('modalComponenteLabel').textContent = 'Nuevo Componente';
    cargarSelectsBase();
    if (id) {
        document.getElementById('modalComponenteLabel').textContent = 'Editar Componente';
        document.getElementById('componenteId').value = id;
        fetch('/admin/componentes/' + id, { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(response) {
            if (response.success) {
                var c = response.data;
                document.getElementById('comp_tipo').value = c.tipo || '';
                document.getElementById('comp_marca').value = c.marca || '';
                document.getElementById('comp_serial').value = c.serial || '';
                document.getElementById('comp_capacidad').value = c.capacidad || '';
                document.getElementById('comp_estado').value = c.estado || '';
                document.getElementById('comp_institucion_id').value = c.institucion_id || '';
                document.getElementById('comp_responsable_id').value = c.responsable_id || '';
                document.getElementById('comp_ubicacion').value = c.ubicacion || '';
                document.getElementById('comp_observaciones').value = c.observaciones || '';
            }
        });
    }
    modal.show();
    setTimeout(function() { document.getElementById('comp_tipo').focus(); }, 500);
};

window.editarComponente = function(id) { window.abrirModalComponente(id); };

function guardarComponente() {
    var id = document.getElementById('componenteId').value;
    var url = id ? '/admin/componentes/' + id : '/admin/componentes';
    var formData = new FormData(document.getElementById('formComponente'));
    if (id) formData.append('_method', 'PUT');
    fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: formData })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) { bootstrap.Modal.getInstance(document.getElementById('modalComponente')).hide(); mostrarToast(response.message, 'success'); cargarComponentes(); }
        else { mostrarToast(response.message || 'Error', 'error'); }
    });
}

// ==================== CAMBIO DE ESTADO ====================
window.abrirModalCambiarEstado = function(id, serial, estadoActual, estadoIdActual) {
    activoCambioEstado = { id: id, estadoActualId: estadoIdActual };
    document.getElementById('estadoSerial').textContent = serial;
    document.getElementById('estadoActual').textContent = estadoActual;

    var select = document.getElementById('nuevoEstadoSelect');
    select.innerHTML = '<option value="">Seleccionar estado...</option>';

    var estadosDisponibles = listaEstados.filter(function(e) {
        return !e.es_terminal && e.descripcion !== estadoActual;
    });

    if (estadosDisponibles.length === 0) {
        select.innerHTML += '<option value="" disabled>No hay estados disponibles</option>';
        document.getElementById('btnConfirmarCambioEstado').disabled = true;
    } else {
        document.getElementById('btnConfirmarCambioEstado').disabled = false;
        for (var i = 0; i < estadosDisponibles.length; i++) {
            var estado = estadosDisponibles[i];
            select.innerHTML += '<option value="' + estado.id + '">' + escapeHtml(estado.descripcion) + '</option>';
        }
    }

    new bootstrap.Modal(document.getElementById('modalCambiarEstado')).show();
};

function confirmarCambioEstado() {
    if (!activoCambioEstado) return;

    var nuevoEstadoId = document.getElementById('nuevoEstadoSelect').value;
    if (!nuevoEstadoId) {
        mostrarToast('Seleccione un estado', 'warning');
        return;
    }

    var nuevoEstado = listaEstados.find(function(e) { return e.id == nuevoEstadoId; });

    fetch('/admin/activos/' + activoCambioEstado.id, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id_estatus: nuevoEstadoId })
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        bootstrap.Modal.getInstance(document.getElementById('modalCambiarEstado')).hide();
        if (response.success) {
            mostrarToast('Estado cambiado a ' + (nuevoEstado ? nuevoEstado.descripcion : ''), 'success');
            cargarActivos();
        } else {
            mostrarToast(response.message || 'Error al cambiar estado', 'error');
        }
        activoCambioEstado = null;
    })
    .catch(function(error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión', 'error');
        activoCambioEstado = null;
    });
}

// ==================== ELIMINACIÓN ====================
var elementoAEliminar = null;

window.confirmarEliminarActivo = function(id) {
    elementoAEliminar = { tipo: 'activo', id: id };
    document.getElementById('deleteNombre').textContent = 'Activo #' + id;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
};

window.confirmarEliminarComponente = function(id) {
    elementoAEliminar = { tipo: 'componente', id: id };
    document.getElementById('deleteNombre').textContent = 'Componente #' + id;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
};

function confirmarEliminacion() {
    if (!elementoAEliminar) return;
    var url = '/admin/' + elementoAEliminar.tipo + 's/' + elementoAEliminar.id;
    fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
        if (response.success) { mostrarToast(response.message, 'success'); if (elementoAEliminar.tipo === 'activo') cargarActivos(); else cargarComponentes(); }
        else { mostrarToast(response.message || 'Error', 'error'); }
        elementoAEliminar = null;
    });
}
