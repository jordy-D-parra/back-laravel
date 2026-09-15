// resources/js/admin-inventario.js
// ✅ Inventario con componentes dinámicos por activo
// ✅ Los componentes se envían en UNA sola petición con el activo
// ✅ Sistema de reportes integrado

// ============================================================
// VARIABLES GLOBALES
// ============================================================
var activosData = [];
var activosPage = 1;
var activosPerPage = 10;

var componentesData = [];
var componentesPage = 1;
var componentesPerPage = 10;

var todosModelos = [];
var listaEstados = [];
var todosActivosList = [];

var activoCambioEstado = null;
var elementoAEliminar = null;

// Iconos SVG
var SVG_ICONS = {
    ver: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>',
    editar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
    eliminar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>',
    cambiarEstado: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>',
    plus: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>'
};

// ============================================================
// INICIALIZACIÓN
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Módulo de inventario cargado');

    // Cargar estados primero
    cargarEstados().then(function() {
        cargarActivos();
        cargarComponentes();
    });

    cargarSelectsBase();

    // Formulario de activo
    document.getElementById('formActivo')?.addEventListener('submit', function(e) {
        e.preventDefault();
        guardarActivo();
    });

    // Formulario de componente
    document.getElementById('formComponente')?.addEventListener('submit', function(e) {
        e.preventDefault();
        guardarComponente();
    });

    // Botón eliminar
    document.getElementById('btnConfirmarEliminar')?.addEventListener('click', function() {
        confirmarEliminacion();
    });

    // Botón cambiar estado
    document.getElementById('btnConfirmarCambioEstado')?.addEventListener('click', function() {
        confirmarCambioEstado();
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

    // Cerrar dropdown de modelos al hacer clic fuera
    document.addEventListener('click', function(e) {
        var dropdown = document.getElementById('modeloDropdown');
        var input = document.getElementById('activo_modelo_buscar');
        if (dropdown && input && e.target !== input && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
});

// ============================================================
// UTILIDADES
// ============================================================
function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function mostrarToast(mensaje, tipo) {
    tipo = tipo || 'success';
    var colores = { success: '#1e7e34', error: '#c5221f', warning: '#f6c23e', info: '#1e3c72' };
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:10000;background:' + colores[tipo] + ';color:white;padding:12px 20px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);animation:slideIn 0.3s ease-out;cursor:pointer;max-width:400px;white-space:pre-line;font-size:0.9rem;';
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 4000);
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

function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

// ============================================================
// COMPONENTES DINÁMICOS EN EL FORMULARIO DE INVENTARIO
// ============================================================

/**
 * Agrega una nueva tarjeta de componente al formulario.
 */
window.agregarComponenteFormulario = function(compData) {
    compData = compData || {};

    var container = document.getElementById('componentesActivoContainer');
    var sinComponentes = document.getElementById('sinComponentes');
    if (!container) return;
    if (sinComponentes) sinComponentes.style.display = 'none';

    var index = Date.now() + Math.floor(Math.random() * 1000);
    var compId = compData.id || '';

    var tipos = [
        'RAM', 'Disco Duro', 'Disco SSD', 'Batería', 'Cargador',
        'Fuente de Poder', 'Pantalla', 'Teclado', 'Mouse', 'Touchpad',
        'Ventilador', 'Tarjeta Madre', 'Procesador', 'Tarjeta Gráfica',
        'Tarjeta de Red', 'Cable', 'Adaptador', 'Webcam', 'Otro'
    ];
    var tiposOptions = tipos.map(function(t) {
        var sel = (compData.tipo === t) ? 'selected' : '';
        return '<option value="' + t + '" ' + sel + '>' + t + '</option>';
    }).join('');

    var item = document.createElement('div');
    item.className = 'componente-activo-item border rounded p-3 mb-3';
    item.style.cssText = 'background: #f8f9fc; border: 1px solid #e9ecef; border-radius: 12px;';
    item.setAttribute('data-comp-index', index);
    item.setAttribute('data-comp-id', compId);

    item.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                    <line x1="9" y1="6" x2="9" y2="18"/>
                </svg>
                <strong style="color:#1e3c72;">Componente #<span class="comp-numero"></span></strong>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarComponenteFormulario(this)" title="Eliminar">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
            </button>
        </div>

        <input type="hidden" class="comp-id" value="${compId}">

        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tipo <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm comp-tipo" required>
                    <option value="">Seleccionar...</option>
                    ${tiposOptions}
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Marca</label>
                <input type="text" class="form-control form-control-sm comp-marca"
                       value="${escapeHtml(compData.marca || '')}" placeholder="Ej: Kingston">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Modelo</label>
                <input type="text" class="form-control form-control-sm comp-modelo"
                       value="${escapeHtml(compData.modelo || '')}" placeholder="Ej: DDR4">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Serial</label>
                <input type="text" class="form-control form-control-sm comp-serial"
                       value="${escapeHtml(compData.serial || '')}" placeholder="Ej: SN-12345">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Capacidad</label>
                <input type="text" class="form-control form-control-sm comp-capacidad"
                       value="${escapeHtml(compData.capacidad || '')}" placeholder="Ej: 8GB, 512GB">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Estado</label>
                <select class="form-select form-select-sm comp-estado">
                    <option value="instalado" ${compData.estado === 'instalado' || !compData.estado ? 'selected' : ''}>Instalado</option>
                    <option value="en_bodega" ${compData.estado === 'en_bodega' ? 'selected' : ''}>En Bodega</option>
                    <option value="en_reparacion" ${compData.estado === 'en_reparacion' ? 'selected' : ''}>En Reparación</option>
                    <option value="desechado" ${compData.estado === 'desechado' ? 'selected' : ''}>Desechado</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label small fw-semibold">Observaciones</label>
                <input type="text" class="form-control form-control-sm comp-observaciones"
                       value="${escapeHtml(compData.observaciones || '')}" placeholder="Observaciones adicionales...">
            </div>
        </div>
    `;

    container.appendChild(item);
    renumerarComponentes();

    if (!compId) {
        setTimeout(function() {
            item.querySelector('.comp-tipo')?.focus();
        }, 100);
    }
};

/**
 * Elimina una tarjeta de componente del formulario.
 */
window.eliminarComponenteFormulario = function(btn) {
    var item = btn.closest('.componente-activo-item');
    if (!item) return;

    var compId = item.querySelector('.comp-id')?.value;
    if (compId) {
        if (!confirm('¿Eliminar este componente del formulario? Si ya estaba guardado, se desvinculará del equipo y volverá a bodega.')) {
            return;
        }
    }

    item.remove();
    renumerarComponentes();
    verificarComponentesVacios();
};

/**
 * Verifica si quedan componentes y muestra el mensaje vacío si no hay.
 */
function verificarComponentesVacios() {
    var container = document.getElementById('componentesActivoContainer');
    if (!container) return;
    var items = container.querySelectorAll('.componente-activo-item');
    var sinComponentes = document.getElementById('sinComponentes');
    if (items.length === 0) {
        if (sinComponentes) sinComponentes.style.display = 'block';
    } else {
        if (sinComponentes) sinComponentes.style.display = 'none';
    }
}

/**
 * Renumera visualmente los componentes.
 */
function renumerarComponentes() {
    var items = document.querySelectorAll('#componentesActivoContainer .componente-activo-item');
    items.forEach(function(item, idx) {
        var numeroSpan = item.querySelector('.comp-numero');
        if (numeroSpan) numeroSpan.textContent = (idx + 1);
    });
}

/**
 * Recolecta los componentes del formulario.
 */
function recolectarComponentesFormulario() {
    var componentes = [];
    var items = document.querySelectorAll('#componentesActivoContainer .componente-activo-item');

    items.forEach(function(item) {
        var tipo = item.querySelector('.comp-tipo')?.value?.trim();
        if (!tipo) return;

        var comp = {
            id: item.querySelector('.comp-id')?.value || null,
            tipo: tipo,
            marca: item.querySelector('.comp-marca')?.value?.trim() || null,
            modelo: item.querySelector('.comp-modelo')?.value?.trim() || null,
            serial: item.querySelector('.comp-serial')?.value?.trim() || null,
            capacidad: item.querySelector('.comp-capacidad')?.value?.trim() || null,
            estado: item.querySelector('.comp-estado')?.value || 'instalado',
            observaciones: item.querySelector('.comp-observaciones')?.value?.trim() || null
        };

        if (!comp.id || comp.id === '') comp.id = null;

        componentes.push(comp);
    });

    return componentes;
}

/**
 * Limpia todos los componentes del formulario.
 */
function limpiarComponentesFormulario() {
    var container = document.getElementById('componentesActivoContainer');
    if (!container) return;

    container.querySelectorAll('.componente-activo-item').forEach(function(el) {
        el.remove();
    });

    var sinComponentes = document.getElementById('sinComponentes');
    if (sinComponentes) sinComponentes.style.display = 'block';
}

/**
 * Carga los componentes existentes de un activo en el formulario.
 */
function cargarComponentesEnFormulario(componentes) {
    limpiarComponentesFormulario();
    if (!componentes || componentes.length === 0) return;

    componentes.forEach(function(comp) {
        window.agregarComponenteFormulario(comp);
    });
}

// ============================================================
// RENDERIZADO DE COMPONENTES EN EL DETALLE
// ============================================================
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

// ============================================================
// ESTILOS ADICIONALES PARA EL DETALLE
// ============================================================
function agregarEstilosDetalle() {
    if (document.getElementById('detalle-activo-styles')) return;

    var styles = `
        <style id="detalle-activo-styles">
            .detalle-activo-moderno { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
            .detalle-seccion { background: #ffffff; border-radius: 16px; padding: 1rem; border: 1px solid #e9ecef; }
            .detalle-seccion-titulo { font-size: 0.85rem; font-weight: 600; color: #1e3c72; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #eef2f6; display: flex; align-items: center; }
            .detalle-seccion-titulo i { color: #1e3c72; }
            .detalle-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
            .detalle-item { padding: 0.5rem; background: #f8f9fc; border-radius: 12px; transition: all 0.2s ease; }
            .detalle-item:hover { background: #eef3fc; transform: translateY(-1px); }
            .detalle-label { font-size: 0.65rem; text-transform: uppercase; color: #6c757d; letter-spacing: 0.5px; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.25rem; }
            .detalle-label i { font-size: 0.7rem; color: #1e3c72; }
            .detalle-valor { font-weight: 600; color: #1a1a1a; font-size: 0.9rem; word-break: break-word; }
            .detalle-observaciones { background: #f8f9fc; padding: 1rem; border-radius: 12px; font-size: 0.85rem; color: #495057; line-height: 1.5; }
            .nav-tabs-componentes { border-bottom: 2px solid #e9ecef; margin-bottom: 0; }
            .nav-tabs-componentes .nav-link { border: none; background: transparent; padding: 0.6rem 1.2rem; font-weight: 500; color: #6c757d; position: relative; transition: all 0.2s ease; }
            .nav-tabs-componentes .nav-link:hover { color: #1e3c72; background: #f8f9fc; }
            .nav-tabs-componentes .nav-link.active { color: #1e3c72; background: transparent; }
            .nav-tabs-componentes .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: #1e3c72; border-radius: 2px; }
            .badge-componentes { background: #e9ecef; color: #495057; padding: 0.15rem 0.5rem; border-radius: 20px; font-size: 0.65rem; margin-left: 0.5rem; }
            .componentes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
            .componente-card { background: #ffffff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; transition: all 0.2s ease; }
            .componente-card:hover { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); transform: translateY(-2px); }
            .componente-card-header { padding: 0.75rem 1rem; background: #f8f9fc; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
            .componente-tipo { font-weight: 600; color: #1e3c72; display: flex; align-items: center; gap: 0.5rem; }
            .componente-tipo i { font-size: 0.9rem; }
            .componente-estado { padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.65rem; font-weight: 600; }
            .componente-estado-instalado { background: #d4edda; color: #155724; }
            .componente-estado-bodega { background: #e2e3e5; color: #383d41; }
            .componente-estado-prestado { background: #fff3cd; color: #856404; }
            .componente-estado-reparacion { background: #f8d7da; color: #721c24; }
            .componente-estado-desechado { background: #f8d7da; color: #721c24; }
            .componente-card-body { padding: 0.75rem 1rem; }
            .componente-info { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8rem; }
            .componente-label { color: #6c757d; }
            .componente-value { font-weight: 500; color: #1a1a1a; }
            .componente-serial { font-family: 'Courier New', monospace; font-size: 0.75rem; }
            .componentes-modelo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
            .componente-modelo-card { background: #f8f9fc; border-radius: 12px; padding: 1rem; text-align: center; transition: all 0.2s ease; border: 1px solid #e9ecef; }
            .componente-modelo-card:hover { background: #eef3fc; transform: translateY(-2px); }
            .componente-modelo-tipo { font-weight: 700; color: #1e3c72; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
            .componente-modelo-descripcion { font-size: 0.75rem; color: #6c757d; margin-bottom: 0.5rem; }
            .componente-modelo-capacidad { font-size: 0.7rem; color: #28a745; background: #d4edda; display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; }
            .detalle-acciones .btn-editar-detalle { background: #1e3c72; border: none; color: white; padding: 0.5rem 1.2rem; border-radius: 30px; font-size: 0.8rem; font-weight: 500; transition: all 0.2s ease; }
            .detalle-acciones .btn-editar-detalle:hover { background: #2a5298; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3); }
            .detalle-acciones .btn-cerrar-detalle { background: #f8f9fa; border: 1px solid #dee2e6; color: #495057; padding: 0.5rem 1.2rem; border-radius: 30px; font-size: 0.8rem; font-weight: 500; transition: all 0.2s ease; }
            .detalle-acciones .btn-cerrar-detalle:hover { background: #e9ecef; border-color: #ced4da; }
            .badge-garantia-vencida { background: #f8d7da; color: #721c24; padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.7rem; }
            .badge-garantia-vigente { background: #d4edda; color: #155724; padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.7rem; }
            @media (max-width: 768px) {
                .detalle-grid { grid-template-columns: 1fr; }
                .componentes-grid, .componentes-modelo-grid { grid-template-columns: 1fr; }
                .nav-tabs-componentes .nav-link { padding: 0.4rem 0.8rem; font-size: 0.75rem; }
            }
        </style>
    `;

    document.head.insertAdjacentHTML('beforeend', styles);
}

window.cerrarModalDetalleManual = function() {
    var modalElement = document.getElementById('modalDetalle');
    var modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) modal.hide();
};

// ============================================================
// CARGAR ESTADOS
// ============================================================
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

// ============================================================
// PAGINACIÓN
// ============================================================
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

// ============================================================
// VALIDACIÓN DE SERIAL
// ============================================================
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

// ============================================================
// SELECTS BASE
// ============================================================
function cargarSelectsBase() {
    // Cargar modelos
    fetch('/admin/equipos/modelos', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            todosModelos = response.data;
        }
    });

    // Cargar estatus
    fetch('/admin/estatus-list', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var select = document.getElementById('activo_id_estatus');
            if (select) {
                select.innerHTML = '<option value="">Seleccionar...</option>';
                response.data.forEach(function(e) {
                    select.innerHTML += '<option value="' + e.id + '">' + escapeHtml(e.descripcion) + '</option>';
                });
                var disponible = response.data.find(function(e) { return e.descripcion === 'Disponible'; });
                if (disponible) select.value = disponible.id;
            }
        }
    }).catch(function() {
        var select = document.getElementById('activo_id_estatus');
        if (select) select.innerHTML = '<option value="">Seleccionar...</option><option value="1" selected>Disponible</option>';
    });

    // Cargar instituciones
    fetch('/admin/instituciones', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var select1 = document.getElementById('activo_institucion_id');
            var select2 = document.getElementById('comp_institucion_id');

            if (select1) {
                select1.innerHTML = '<option value="">Seleccionar...</option>';
                response.data.forEach(function(i) {
                    select1.innerHTML += '<option value="' + i.id + '" data-representante="' + escapeHtml(i.representante || '') + '">' + escapeHtml(i.nombre) + '</option>';
                });
                var gob = response.data.find(function(i) {
                    var n = i.nombre.toLowerCase();
                    return n.indexOf('gobernacion') >= 0 || n.indexOf('informatica') >= 0;
                });
                if (gob) {
                    select1.value = gob.id;
                    cargarResponsablesPorInstitucion(gob.id, 'activo_responsable_id', gob.representante);
                }
                select1.addEventListener('change', function() {
                    var opt = this.options[this.selectedIndex];
                    var rep = opt.getAttribute('data-representante') || '';
                    cargarResponsablesPorInstitucion(this.value, 'activo_responsable_id', rep);
                });
            }

            if (select2) {
                select2.innerHTML = '<option value="">Seleccionar...</option>';
                response.data.forEach(function(i) {
                    select2.innerHTML += '<option value="' + i.id + '" data-representante="' + escapeHtml(i.representante || '') + '">' + escapeHtml(i.nombre) + '</option>';
                });
                var gob = response.data.find(function(i) {
                    var n = i.nombre.toLowerCase();
                    return n.indexOf('gobernacion') >= 0 || n.indexOf('informatica') >= 0;
                });
                if (gob) {
                    select2.value = gob.id;
                    cargarResponsablesPorInstitucion(gob.id, 'comp_responsable_id', gob.representante);
                }
                select2.addEventListener('change', function() {
                    var opt = this.options[this.selectedIndex];
                    var rep = opt.getAttribute('data-representante') || '';
                    cargarResponsablesPorInstitucion(this.value, 'comp_responsable_id', rep);
                });
            }
        }
    });

    // Cargar activos para el select de componentes
    fetch('/admin/activos', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            todosActivosList = response.data;
            var selectComp = document.getElementById('comp_activo_id');
            if (selectComp) {
                selectComp.innerHTML = '<option value="">Sin activo</option>';
                response.data.forEach(function(a) {
                    var modeloNombre = a.modelo ? a.modelo.nombre : 'N/A';
                    selectComp.innerHTML += '<option value="' + a.id + '">' + escapeHtml(a.serial) + ' - ' + escapeHtml(modeloNombre) + '</option>';
                });
            }
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
            response.data.forEach(function(r) {
                select.innerHTML += '<option value="' + r.id + '">' + escapeHtml(r.nombre) + ' - ' + escapeHtml(r.cargo || 'Sin cargo') + '</option>';
            });
            if (representanteSugerido && response.data.length > 0) {
                var encontrado = response.data.find(function(r) {
                    return r.nombre.toLowerCase().indexOf(representanteSugerido.toLowerCase()) >= 0;
                });
                select.value = encontrado ? encontrado.id : response.data[0].id;
            }
        }
    });
}

// ============================================================
// BUSCADOR DE MODELOS
// ============================================================
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
            var marcaNombre = m.marca ? m.marca.nombre : '';
            var categoriaNombre = m.categoria ? m.categoria.nombre : '';
            return '<a href="#" class="list-group-item list-group-item-action py-2 px-3" onclick="seleccionarModelo(' + m.id + ', \'' + escapeHtml(marcaNombre + ' ' + m.nombre) + '\', \'' + escapeHtml(marcaNombre) + '\', \'' + escapeHtml(categoriaNombre) + '\'); return false;">' +
                '<strong>' + escapeHtml(marcaNombre + ' ' + m.nombre) + '</strong>' +
                '<small class="d-block text-muted">' + escapeHtml(categoriaNombre) + '</small>' +
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
};

// ============================================================
// FILTROS
// ============================================================
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
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron activos</td></tr>';
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
        '</tr>';
    }
    html += '<tr><td colspan="6">' + renderPaginacion(totalPages, activosPage, 'activos') + '</td></tr>';
    tbody.innerHTML = html;
}

function renderizarComponentesFiltrados(filtrados) {
    var tbody = document.getElementById('tablaComponentes');
    if (!tbody) return;

    var totalPages = Math.ceil(filtrados.length / componentesPerPage);
    var start = (componentesPage - 1) * componentesPerPage;
    var pageData = filtrados.slice(start, start + componentesPerPage);

    if (pageData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron componentes</td></tr>';
        return;
    }

    var html = '';
    for (var i = 0; i < pageData.length; i++) {
        var c = pageData[i];
        html += '<tr>' +
            '<td><strong>' + escapeHtml(c.tipo) + '</strong></td>' +
            '<td>' + escapeHtml(c.marca || 'N/A') + '</td>' +
            '<td>' + escapeHtml(c.serial || 'N/A') + '</td>' +
            '<td>' + escapeHtml(c.capacidad || 'N/A') + '</td>' +
            '<td><span class="badge ' + getEstadoBadge(c.estado) + '">' + getEstadoLabel(c.estado) + '</span></td>' +
            '<td>' + (c.activo ? '<a href="#" onclick="verActivo(' + c.activo.id + '); return false;" class="text-decoration-none">' + escapeHtml(c.activo.serial) + '</a>' : '—') + '</td>' +
            '<td class="text-end">' +
                (window.authUserHasPermission && authUserHasPermission('editar-componente') ? '<button class="btn btn-sm btn-outline-primary-dark" onclick="editarComponente(' + c.id + ')" title="Editar">' + SVG_ICONS.editar + '</button> ' : '') +
                (window.authUserHasPermission && authUserHasPermission('eliminar-componente') ? '<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminarComponente(' + c.id + ')" title="Eliminar">' + SVG_ICONS.eliminar + '</button>' : '') +
            '</td>' +
        '</tr>';
    }
    html += '<tr><td colspan="7">' + renderPaginacion(totalPages, componentesPage, 'componentes') + '</td></tr>';
    tbody.innerHTML = html;
}

// ============================================================
// ACTIVOS — CRUD
// ============================================================
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
    limpiarComponentesFormulario();
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
                var modeloTexto = a.modelo ? ((a.modelo.marca ? a.modelo.marca.nombre + ' ' : '') + a.modelo.nombre) : '';
                document.getElementById('activo_modelo_buscar').value = modeloTexto;
                document.getElementById('activo_id_estatus').value = a.id_estatus || '';
                document.getElementById('activo_institucion_id').value = a.institucion_id || '';
                document.getElementById('activo_responsable_id').value = a.responsable_id || '';
                document.getElementById('activo_ubicacion').value = a.ubicacion || '';
                document.getElementById('activo_fecha_adquisicion').value = a.fecha_adquisicion || '';
                document.getElementById('activo_fecha_fin_garantia').value = a.fecha_fin_garantia || '';
                document.getElementById('activo_vida_util_anos').value = a.vida_util_anos || '';
                document.getElementById('activo_observaciones').value = a.observaciones || '';

                if (a.modelo) {
                    var marca = a.modelo.marca ? a.modelo.marca.nombre : '';
                    var categoria = a.modelo.categoria ? a.modelo.categoria.nombre : '';
                    document.getElementById('modeloInfoBadges').innerHTML = '<span class="badge bg-primary-dark">' + escapeHtml(marca) + '</span> <span class="badge bg-secondary">' + escapeHtml(categoria) + '</span>';
                }

                // Cargar componentes existentes en el formulario
                if (a.componentes && a.componentes.length > 0) {
                    cargarComponentesEnFormulario(a.componentes);
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

            var fechaAdquisicion = a.fecha_adquisicion ? new Date(a.fecha_adquisicion).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' }) : 'No registrada';
            var fechaGarantia = a.fecha_fin_garantia ? new Date(a.fecha_fin_garantia).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' }) : 'No registrada';

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

            agregarEstilosDetalle();

            var modeloMarca = a.modelo && a.modelo.marca ? a.modelo.marca.nombre : 'N/A';
            var modeloNombre = a.modelo ? a.modelo.nombre : 'N/A';
            var categoriaNombre = a.modelo && a.modelo.categoria ? a.modelo.categoria.nombre : 'N/A';
            var institucionNombre = a.institucion ? a.institucion.nombre : 'N/A';
            var responsableNombre = a.responsable ? a.responsable.nombre : 'No asignado';

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
                                    <i class="fas fa-tag me-1"></i> ${escapeHtml(modeloMarca)} ${escapeHtml(modeloNombre)}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-folder me-1"></i> ${escapeHtml(categoriaNombre)}
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
                                        <div class="detalle-valor">${escapeHtml(institucionNombre)}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-map-marker-alt"></i> Ubicación</div>
                                        <div class="detalle-valor">${escapeHtml(a.ubicacion || 'No especificada')}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-user"></i> Responsable</div>
                                        <div class="detalle-valor">${escapeHtml(responsableNombre)}</div>
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

            // Cargar componentes del modelo (plantilla)
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
            } else {
                document.getElementById('detalleCompModeloContent').innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-info-circle"></i> No hay modelo asignado</div>';
            }
        }
    });
};

// ============================================================
// GUARDAR ACTIVO (CON COMPONENTES EN UNA SOLA PETICIÓN)
// ============================================================
function guardarActivo() {
    var id = document.getElementById('activoId').value;
    var url = id ? '/admin/activos/' + id : '/admin/activos';
    var formData = new FormData(document.getElementById('formActivo'));

    // Validaciones mínimas del cliente
    if (!document.getElementById('activo_serial').value.trim()) {
        mostrarToast('El serial del activo es requerido', 'warning');
        return;
    }
    if (!document.getElementById('activo_modelo_id').value) {
        mostrarToast('Debe seleccionar un modelo', 'warning');
        return;
    }

    // Recolectar componentes del formulario
    var componentes = recolectarComponentesFormulario();

    // Enviar componentes como JSON dentro del FormData
    formData.append('componentes', JSON.stringify(componentes));

    if (id) formData.append('_method', 'PUT');

    var btn = document.querySelector('#formActivo button[type="submit"]');
    var originalText = btn ? btn.innerHTML : 'Guardar';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalActivo')).hide();
            mostrarToast(response.message || 'Activo guardado correctamente', 'success');
            cargarActivos();
            cargarComponentes();
        } else {
            var msg = response.message || 'Error al guardar';
            if (response.errors) {
                msg += '\n' + Object.values(response.errors).flat().join('\n');
            }
            mostrarToast(msg, 'error');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión', 'error');
    })
    .finally(function() {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
}

// ============================================================
// COMPONENTES (CRUD INDIVIDUAL)
// ============================================================
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
                document.getElementById('comp_modelo').value = c.modelo || '';
                document.getElementById('comp_serial').value = c.serial || '';
                document.getElementById('comp_capacidad').value = c.capacidad || '';
                document.getElementById('comp_estado').value = c.estado || '';
                document.getElementById('comp_activo_id').value = c.activo_id || '';
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
        if (response.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalComponente')).hide();
            mostrarToast(response.message, 'success');
            cargarComponentes();
            cargarActivos();
        } else {
            mostrarToast(response.message || 'Error', 'error');
        }
    });
}

// ============================================================
// CAMBIO DE ESTADO
// ============================================================
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

// ============================================================
// ELIMINACIÓN
// ============================================================
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
    var tipo = elementoAEliminar.tipo;
    var pluralTipo = tipo === 'activo' ? 'activos' : 'componentes';
    var url = '/admin/' + pluralTipo + '/' + elementoAEliminar.id;

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
        if (response.success) {
            mostrarToast(response.message, 'success');
            if (tipo === 'activo') {
                cargarActivos();
            } else {
                cargarComponentes();
            }
            cargarActivos();
        } else {
            mostrarToast(response.message || 'Error', 'error');
        }
        elementoAEliminar = null;
    });
}