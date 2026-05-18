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

// Componentes existentes al editar
var componentesExistentesActivo = [];

document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de inventario cargado');
    cargarActivos();
    cargarComponentes();
    cargarSelectsBase();
    cargarFiltros();

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

    var buscarActivos = document.getElementById('buscarActivos');
    if (buscarActivos) buscarActivos.addEventListener('input', function() { activosPage = 1; renderizarActivos(); });

    var filtroEstadoActivos = document.getElementById('filtroEstadoActivos');
    if (filtroEstadoActivos) filtroEstadoActivos.addEventListener('change', function() { activosPage = 1; renderizarActivos(); });

    var buscarComponentes = document.getElementById('buscarComponentes');
    if (buscarComponentes) buscarComponentes.addEventListener('input', function() { componentesPage = 1; renderizarComponentes(); });

    var filtroTipoComponentes = document.getElementById('filtroTipoComponentes');
    if (filtroTipoComponentes) filtroTipoComponentes.addEventListener('change', function() { componentesPage = 1; renderizarComponentes(); });

    var filtroEstadoComponentes = document.getElementById('filtroEstadoComponentes');
    if (filtroEstadoComponentes) filtroEstadoComponentes.addEventListener('change', function() { componentesPage = 1; renderizarComponentes(); });

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

var SVG_ICONS = {
    ver: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
    editar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
    eliminar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>',
    plus: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>'
};

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
    if (tipo === 'activos') { activosPage = page; renderizarActivos(); }
    else if (tipo === 'componentes') { componentesPage = page; renderizarComponentes(); }
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

function cargarFiltros() {
    fetch('/admin/estatus-list', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var select = document.getElementById('filtroEstadoActivos');
            if (select) { select.innerHTML = '<option value="">Todos los estados</option>'; response.data.forEach(function(e) { select.innerHTML += '<option value="' + e.descripcion + '">' + e.descripcion + '</option>'; }); }
        }
    }).catch(function() {
        var select = document.getElementById('filtroEstadoActivos');
        if (select) select.innerHTML = '<option value="">Todos</option><option value="Disponible">Disponible</option><option value="Prestado">Prestado</option>';
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

// ==================== COMPONENTES DEL MODELO (DINÁMICOS) ====================
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
            // Checkbox equipo completo + alerta
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
    // Limpiar valores
    clone.querySelectorAll('input[type="text"]').forEach(function(i) { i.value = ''; });
    clone.querySelectorAll('.comp-marca').forEach(function(i) { i.value = ''; });
    clone.querySelectorAll('.comp-serial').forEach(function(i) { i.value = ''; });
    clone.querySelectorAll('.comp-check').forEach(function(i) { i.checked = true; });
    clone.querySelector('select.comp-estado').value = 'instalado';
    // Quitar clase de existente
    clone.classList.remove('border-primary');
    // Cambiar nombres para que sean nuevo_
    clone.querySelectorAll('input, select').forEach(function(input) {
        var name = input.name;
        if (name) {
            name = name.replace(/comp_existente_\d+/, 'comp_nuevo_' + Date.now());
            input.name = name;
        }
    });
    // Quitar botón duplicar del clone (para evitar recursión infinita)
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

// ==================== ACTIVOS ====================
function cargarActivos() {
    fetch('/admin/activos', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) { activosData = response.data; activosPage = 1; renderizarActivos(); }
    });
}

function renderizarActivos() {
    var tbody = document.getElementById('tablaActivos');
    if (!tbody) return;
    var buscar = document.getElementById('buscarActivos') ? document.getElementById('buscarActivos').value.toLowerCase() : '';
    var filtroEstado = document.getElementById('filtroEstadoActivos') ? document.getElementById('filtroEstadoActivos').value : '';
    var filtrados = activosData.filter(function(a) {
        var coincideBuscar = !buscar || (a.serial && a.serial.toLowerCase().indexOf(buscar) >= 0) || (a.modelo && a.modelo.nombre && a.modelo.nombre.toLowerCase().indexOf(buscar) >= 0);
        var coincideEstado = !filtroEstado || (a.estatus && a.estatus.descripcion === filtroEstado);
        return coincideBuscar && coincideEstado;
    });
    var totalPages = Math.ceil(filtrados.length / activosPerPage);
    var start = (activosPage - 1) * activosPerPage;
    var pageData = filtrados.slice(start, start + activosPerPage);
    if (pageData.length === 0) { tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron activos</td></tr>'; return; }
    tbody.innerHTML = pageData.map(function(a) {
        var garantiaBadge = '';
        if (a.fecha_fin_garantia) { garantiaBadge = garantiaVencida(a.fecha_fin_garantia) ? '<span class="badge badge-garantia-vencida ms-1">Vencida</span>' : '<span class="badge badge-garantia-vigente ms-1">Vigente</span>'; }
        var compCount = a.componentes ? a.componentes.length : 0;
        return '<tr>' +
            '<td><strong>' + escapeHtml(a.serial) + '</strong>' + garantiaBadge + '</td>' +
            '<td>' + escapeHtml(a.modelo ? a.modelo.nombre : 'N/A') + (compCount > 0 ? ' <span class="badge bg-info text-dark">' + compCount + '</span>' : '') + '</td>' +
            '<td>' + escapeHtml(a.modelo && a.modelo.marca ? a.modelo.marca.nombre : 'N/A') + '</td>' +
            '<td><span class="badge bg-' + (a.estatus ? a.estatus.color_badge : 'secondary') + '">' + escapeHtml(a.estatus ? a.estatus.descripcion : 'N/A') + '</span></td>' +
            '<td>' + escapeHtml(a.ubicacion || (a.institucion ? a.institucion.nombre : 'N/A')) + '</td>' +
            '<td class="text-end">' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="verActivo(' + a.id + ')" title="Ver detalle">' + SVG_ICONS.ver + '</button> ' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="editarActivo(' + a.id + ')" title="Editar">' + SVG_ICONS.editar + '</button> ' +
                '<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminarActivo(' + a.id + ')" title="Eliminar">' + SVG_ICONS.eliminar + '</button>' +
            '</td></tr>';
    }).join('') + '<tr><td colspan="6">' + renderPaginacion(totalPages, activosPage, 'activos') + '</td></tr>';
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
                // Cargar componentes existentes
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
            var html = '<div class="detail-card">' +
                '<div class="detail-card-header"><div class="detail-card-icon bg-primary-dark"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/></svg></div><div><h5 class="mb-0">' + escapeHtml(a.serial) + '</h5><span class="badge bg-' + (a.estatus ? a.estatus.color_badge : 'secondary') + '">' + escapeHtml(a.estatus ? a.estatus.descripcion : 'N/A') + '</span></div></div>' +
                '<div class="detail-card-body">' +
                    '<div class="detail-row"><span class="detail-label">Modelo</span><span class="detail-value">' + escapeHtml(a.modelo ? a.modelo.nombre : 'N/A') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Marca</span><span class="detail-value">' + escapeHtml(a.modelo && a.modelo.marca ? a.modelo.marca.nombre : 'N/A') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Institución</span><span class="detail-value">' + escapeHtml(a.institucion ? a.institucion.nombre : 'N/A') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Responsable</span><span class="detail-value">' + escapeHtml(a.responsable ? a.responsable.nombre : 'N/A') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Ubicación</span><span class="detail-value">' + escapeHtml(a.ubicacion || 'N/A') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Adquisición</span><span class="detail-value">' + (a.fecha_adquisicion || 'N/A') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Fin Garantía</span><span class="detail-value">' + (a.fecha_fin_garantia || 'N/A') + (a.fecha_fin_garantia && garantiaVencida(a.fecha_fin_garantia) ? ' <span class="badge badge-garantia-vencida">Vencida</span>' : '') + '</span></div>' +
                '</div>';

            // Pestañas en detalle
            html += '<hr><ul class="nav nav-tabs nav-tabs-custom small mb-2">' +
                '<li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#detalleCompInstalados">Instalados (' + (a.componentes ? a.componentes.length : 0) + ')</button></li>' +
                '<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#detalleCompModelo">Del Modelo</button></li>' +
            '</ul>' +
            '<div class="tab-content">' +
                '<div class="tab-pane fade show active" id="detalleCompInstalados">';

            if (a.componentes && a.componentes.length > 0) {
                html += '<div class="detail-chips">' + a.componentes.map(function(c) {
                    return '<span class="detail-chip">' + escapeHtml(c.tipo) + ' - ' + escapeHtml(c.marca || 'N/A') + '<small>S/N: ' + escapeHtml(c.serial || 'S/N') + ' | ' + getEstadoLabel(c.estado) + '</small></span>';
                }).join('') + '</div>';
            } else { html += '<p class="text-muted text-center py-2">Sin componentes instalados</p>'; }

            html += '</div><div class="tab-pane fade" id="detalleCompModelo"><div id="detalleCompModeloContent"><p class="text-center py-2 text-muted">Cargando...</p></div></div></div>';
            html += '</div>';

            document.getElementById('modalDetalleLabel').textContent = 'Detalle de Activo';
            document.getElementById('detalleContenido').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();

            // Cargar componentes del modelo
            if (a.modelo_id) {
                fetch('/admin/equipos/modelos/' + a.modelo_id + '/componentes', { headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(response) {
                    if (response.success && response.data && response.data.length > 0) {
                        var modelHtml = '<div class="detail-chips">' + response.data.map(function(c) {
                            return '<span class="detail-chip">' + escapeHtml(c.tipo) + ' - ' + escapeHtml(c.descripcion) + '<small>' + escapeHtml(c.capacidad || 'N/A') + '</small></span>';
                        }).join('') + '</div>';
                        document.getElementById('detalleCompModeloContent').innerHTML = modelHtml;
                    } else {
                        document.getElementById('detalleCompModeloContent').innerHTML = '<p class="text-muted text-center py-2">Sin componentes definidos</p>';
                    }
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
    if (id) formData.append('_method', 'PUT');
    var componentes = recolectarComponentesFormulario();
    var equipoCompleto = document.getElementById('equipoCompleto') ? document.getElementById('equipoCompleto').checked : true;

    fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: formData })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var activoId = id || (response.data ? response.data.id : null);
            if (activoId && componentes.length > 0) {
                var promesas = componentes.map(function(comp) {
                    comp.activo_id = activoId;
                    var compUrl = comp.id ? '/admin/componentes/' + comp.id : '/admin/componentes';
                    var method = comp.id ? 'PUT' : 'POST';
                    if (method === 'PUT') comp._method = 'PUT';
                    return fetch(compUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' }, body: JSON.stringify(comp) }).then(function(r) { return r.json(); });
                });
                Promise.all(promesas).then(function(results) {
                    var msg = 'Activo guardado con ' + resultados.length + ' componentes. ';
                    msg += equipoCompleto ? 'Equipo marcado como completo.' : 'Equipo marcado como incompleto.';
                    mostrarToast(msg, 'success');
                }).catch(function() { mostrarToast('Activo guardado. Revisar componentes', 'warning'); });
            } else { mostrarToast(response.message || 'Activo guardado', 'success'); }
            bootstrap.Modal.getInstance(document.getElementById('modalActivo')).hide();
            cargarActivos();
            cargarComponentes();
        } else { mostrarToast(response.message || 'Error al guardar', 'error'); }
    });
}

// ==================== COMPONENTES ====================
function cargarComponentes() {
    fetch('/admin/componentes', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) { componentesData = response.data; componentesPage = 1; renderizarComponentes(); }
    });
}

function renderizarComponentes() {
    var tbody = document.getElementById('tablaComponentes');
    if (!tbody) return;
    var buscar = document.getElementById('buscarComponentes') ? document.getElementById('buscarComponentes').value.toLowerCase() : '';
    var filtroTipo = document.getElementById('filtroTipoComponentes') ? document.getElementById('filtroTipoComponentes').value : '';
    var filtroEstado = document.getElementById('filtroEstadoComponentes') ? document.getElementById('filtroEstadoComponentes').value : '';
    var filtrados = componentesData.filter(function(c) {
        var cb = !buscar || (c.tipo && c.tipo.toLowerCase().indexOf(buscar) >= 0) || (c.marca && c.marca.toLowerCase().indexOf(buscar) >= 0) || (c.serial && c.serial.toLowerCase().indexOf(buscar) >= 0);
        return cb && (!filtroTipo || c.tipo === filtroTipo) && (!filtroEstado || c.estado === filtroEstado);
    });
    var tiposUnicos = [];
    componentesData.forEach(function(c) { if (c.tipo && tiposUnicos.indexOf(c.tipo) < 0) tiposUnicos.push(c.tipo); });
    var selectTipo = document.getElementById('filtroTipoComponentes');
    if (selectTipo && selectTipo.options.length <= 1) {
        selectTipo.innerHTML = '<option value="">Todos los tipos</option>';
        tiposUnicos.sort().forEach(function(t) { selectTipo.innerHTML += '<option value="' + t + '">' + t + '</option>'; });
    }
    var totalPages = Math.ceil(filtrados.length / componentesPerPage);
    var start = (componentesPage - 1) * componentesPerPage;
    var pageData = filtrados.slice(start, start + componentesPerPage);
    if (pageData.length === 0) { tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron componentes</td></tr>'; return; }
    tbody.innerHTML = pageData.map(function(c) {
        return '<tr>' +
            '<td><strong>' + escapeHtml(c.tipo) + '</strong></td>' +
            '<td>' + escapeHtml(c.marca || 'N/A') + '</td>' +
            '<td>' + escapeHtml(c.serial || 'N/A') + '</td>' +
            '<td>' + escapeHtml(c.capacidad || 'N/A') + '</td>' +
            '<td><span class="badge ' + getEstadoBadge(c.estado) + '">' + getEstadoLabel(c.estado) + '</span></td>' +
            '<td>' + (c.activo ? '<a href="#" onclick="verActivo(' + c.activo.id + '); return false;" class="text-decoration-none">' + escapeHtml(c.activo.serial) + '</a>' : '—') + '</td>' +
            '<td class="text-end">' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="editarComponente(' + c.id + ')" title="Editar">' + SVG_ICONS.editar + '</button> ' +
                '<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminarComponente(' + c.id + ')" title="Eliminar">' + SVG_ICONS.eliminar + '</button>' +
            '</td></tr>';
    }).join('') + '<tr><td colspan="7">' + renderPaginacion(totalPages, componentesPage, 'componentes') + '</td></tr>';
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
