// resources/js/admin-equipos.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - Inicializando módulo de equipos');
    cargarMarcas();
    cargarCategorias();
    cargarModelos();
    cargarArbol();
    cargarSelectsModelo();

    var buscarMarcas = document.getElementById('buscarMarcas');
    if (buscarMarcas) buscarMarcas.addEventListener('input', function() { cargarMarcas(); });
    var filtroEstadoMarcas = document.getElementById('filtroEstadoMarcas');
    if (filtroEstadoMarcas) filtroEstadoMarcas.addEventListener('change', function() { cargarMarcas(); });

    var buscarCategorias = document.getElementById('buscarCategorias');
    if (buscarCategorias) buscarCategorias.addEventListener('input', function() { cargarCategorias(); });
    var filtroEstadoCategorias = document.getElementById('filtroEstadoCategorias');
    if (filtroEstadoCategorias) filtroEstadoCategorias.addEventListener('change', function() { cargarCategorias(); });

    var buscarModelos = document.getElementById('buscarModelos');
    if (buscarModelos) buscarModelos.addEventListener('input', function() { cargarModelos(); });
    var filtroMarcaModelos = document.getElementById('filtroMarcaModelos');
    if (filtroMarcaModelos) filtroMarcaModelos.addEventListener('change', function() { cargarModelos(); });
    var filtroCategoriaModelos = document.getElementById('filtroCategoriaModelos');
    if (filtroCategoriaModelos) filtroCategoriaModelos.addEventListener('change', function() { cargarModelos(); });
    var filtroEstadoModelos = document.getElementById('filtroEstadoModelos');
    if (filtroEstadoModelos) filtroEstadoModelos.addEventListener('change', function() { cargarModelos(); });

    var buscarArbol = document.getElementById('buscarArbol');
    if (buscarArbol) buscarArbol.addEventListener('input', function() { cargarArbol(); });

    var formMarca = document.getElementById('formMarca');
    if (formMarca) formMarca.addEventListener('submit', function(e) { e.preventDefault(); guardarMarca(); });
    var formCategoria = document.getElementById('formCategoria');
    if (formCategoria) formCategoria.addEventListener('submit', function(e) { e.preventDefault(); guardarCategoria(); });
    var formModelo = document.getElementById('formModelo');
    if (formModelo) formModelo.addEventListener('submit', function(e) { e.preventDefault(); guardarModelo(); });

    var btnConfirmar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmar) btnConfirmar.addEventListener('click', function() { confirmarEliminacion(); });
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

function resaltarTexto(texto, buscar) {
    if (!buscar || !texto) return escapeHtml(texto);
    var regex = new RegExp('(' + buscar.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    return escapeHtml(texto).replace(regex, '<span class="highlight">$1</span>');
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

var SVG_ICONS = {
    ver: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
    editar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
    toggle: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>',
    eliminar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>',
    marca: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="8" width="16" height="12" rx="1"/></svg>',
    modelo: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"/><line x1="9" y1="4" x2="9" y2="20"/></svg>'
};

// ==================== PAGINACIÓN FRONTEND ====================
function renderPaginacionFrontend(totalPages, currentPage, tipo) {
    if (totalPages <= 1) return '';
    var html = '<div class="pagination-bar"><div class="pagination-info">Página ' + currentPage + ' de ' + totalPages + '</div><div class="pagination-btns">';

    html += '<button class="pagination-btn' + (currentPage === 1 ? ' disabled' : '') + '" onclick="window.cambiarPagina(\'' + tipo + '\',' + (currentPage - 1) + ')">«</button>';

    for (var i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += '<button class="pagination-btn' + (i === currentPage ? ' active' : '') + '" onclick="window.cambiarPagina(\'' + tipo + '\',' + i + ')">' + i + '</button>';
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += '<span class="pagination-ellipsis">...</span>';
        }
    }

    html += '<button class="pagination-btn' + (currentPage === totalPages ? ' disabled' : '') + '" onclick="window.cambiarPagina(\'' + tipo + '\',' + (currentPage + 1) + ')">»</button>';
    html += '</div></div>';
    return html;
}

window.cambiarPagina = function(tipo, page) {
    if (tipo === 'marcas') { marcasPage = page; renderizarMarcas(); }
    else if (tipo === 'categorias') { categoriasPage = page; renderizarCategorias(); }
    else if (tipo === 'modelos') { modelosPage = page; renderizarModelos(); }
};

// ==================== MARCAS ====================
var marcasData = [];
var marcasPage = 1;
var marcasPerPage = 10;

function cargarMarcas() {
    var buscar = document.getElementById('buscarMarcas') ? document.getElementById('buscarMarcas').value : '';
    var estado = document.getElementById('filtroEstadoMarcas') ? document.getElementById('filtroEstadoMarcas').value : '';
    fetch('/admin/equipos/marcas?buscar=' + encodeURIComponent(buscar) + '&estado=' + estado, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) { marcasData = response.data; marcasPage = 1; renderizarMarcas(); }
    });
}

function renderizarMarcas() {
    var tbody = document.getElementById('tablaMarcas');
    if (!tbody) return;
    var totalPages = Math.ceil(marcasData.length / marcasPerPage);
    var start = (marcasPage - 1) * marcasPerPage;
    var pageData = marcasData.slice(start, start + marcasPerPage);
    var buscar = document.getElementById('buscarMarcas') ? document.getElementById('buscarMarcas').value : '';

    if (pageData.length === 0) { tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No se encontraron marcas</td></tr>'; return; }

    tbody.innerHTML = pageData.map(function(marca) {
        return '<tr>' +
            '<td><span class="fw-medium" style="color:#1e3c72">' + resaltarTexto(marca.nombre, buscar) + '</span></td>' +
            '<td>' + escapeHtml(marca.descripcion ? marca.descripcion.substring(0, 50) : '—') + '</td>' +
            '<td><span class="badge bg-info text-dark">' + (marca.modelos_count || 0) + '</span></td>' +
            '<td><span class="badge ' + (marca.activo ? 'bg-success' : 'bg-danger') + '">' + (marca.activo ? 'Activa' : 'Inactiva') + '</span></td>' +
            '<td class="text-end">' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="verMarca(' + marca.id + ')">' + SVG_ICONS.ver + '</button> ' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="editarMarca(' + marca.id + ')">' + SVG_ICONS.editar + '</button> ' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="toggleMarca(' + marca.id + ')">' + SVG_ICONS.toggle + '</button> ' +
                '<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar(\'marca\',' + marca.id + ',\'' + escapeHtml(marca.nombre).replace(/'/g, "\\'") + '\',' + (marca.modelos_count || 0) + ')">' + SVG_ICONS.eliminar + '</button>' +
            '</td></tr>';
    }).join('') + '<tr><td colspan="5">' + renderPaginacionFrontend(totalPages, marcasPage, 'marcas') + '</td></tr>';
}

window.abrirModalMarca = function(id) {
    var modalElement = document.getElementById('modalMarca');
    if (!modalElement) return;
    var modal = new bootstrap.Modal(modalElement);
    var form = document.getElementById('formMarca');
    form.reset();
    if (id) {
        document.getElementById('modalMarcaLabel').textContent = 'Editar Marca';
        document.getElementById('formMethodMarca').value = 'PUT';
        document.getElementById('marcaId').value = id;
        fetch('/admin/equipos/marcas/' + id, { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(response) {
            if (response.success) {
                document.getElementById('marca_nombre').value = response.data.nombre;
                document.getElementById('marca_descripcion').value = response.data.descripcion || '';
            }
        });
    } else {
        document.getElementById('modalMarcaLabel').textContent = 'Nueva Marca';
        document.getElementById('formMethodMarca').value = 'POST';
        document.getElementById('marcaId').value = '';
    }
    modal.show();
};

window.editarMarca = function(id) { window.abrirModalMarca(id); };

window.verMarca = function(id) {
    fetch('/admin/equipos/marcas/' + id, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var d = response.data;
            var html = '<div class="detail-card">' +
                '<div class="detail-card-header"><div class="detail-card-icon bg-primary-dark">' + SVG_ICONS.marca + '</div><div><h5 class="mb-0">' + escapeHtml(d.nombre) + '</h5><span class="badge ' + (d.activo ? 'bg-success' : 'bg-danger') + '">' + (d.activo ? 'Activa' : 'Inactiva') + '</span></div></div>' +
                '<div class="detail-card-body"><div class="detail-row"><span class="detail-label">Descripción</span><span class="detail-value">' + (escapeHtml(d.descripcion) || 'Sin descripción') + '</span></div><div class="detail-row"><span class="detail-label">Total Modelos</span><span class="detail-value badge bg-info text-dark">' + (d.modelos_count || 0) + '</span></div></div>';
            if (d.modelos && d.modelos.length) {
                html += '<div class="detail-card-footer"><h6 class="detail-section-title">Modelos asociados</h6><div class="detail-chips">' +
                    d.modelos.map(function(m) { return '<span class="detail-chip">' + escapeHtml(m.nombre) + '<small>' + escapeHtml(m.categoria ? m.categoria.nombre : 'N/A') + '</small></span>'; }).join('') + '</div></div>';
            }
            html += '</div>';
            document.getElementById('modalDetalleLabel').textContent = 'Detalle de Marca';
            document.getElementById('detalleContenido').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        }
    });
};

window.toggleMarca = function(id) {
    fetch('/admin/equipos/marcas/' + id + '/toggle', { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) { if (response.success) { mostrarToast(response.message, 'success'); cargarMarcas(); } });
};

function guardarMarca() {
    var id = document.getElementById('marcaId').value;
    var method = document.getElementById('formMethodMarca').value;
    var url = method === 'PUT' ? '/admin/equipos/marcas/' + id : '/admin/equipos/marcas';
    var formData = new FormData(document.getElementById('formMarca'));
    if (method === 'PUT') formData.append('_method', 'PUT');
    fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: formData })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) { bootstrap.Modal.getInstance(document.getElementById('modalMarca')).hide(); mostrarToast(response.message, 'success'); cargarMarcas(); }
        else { mostrarToast(response.message, 'error'); }
    });
}

// ==================== CATEGORÍAS ====================
var categoriasData = [];
var categoriasPage = 1;
var categoriasPerPage = 10;

function cargarCategorias() {
    var buscar = document.getElementById('buscarCategorias') ? document.getElementById('buscarCategorias').value : '';
    var estado = document.getElementById('filtroEstadoCategorias') ? document.getElementById('filtroEstadoCategorias').value : '';
    fetch('/admin/equipos/categorias?buscar=' + encodeURIComponent(buscar) + '&estado=' + estado, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) { categoriasData = response.data; categoriasPage = 1; renderizarCategorias(); }
    });
}

function renderizarCategorias() {
    var tbody = document.getElementById('tablaCategorias');
    if (!tbody) return;
    var totalPages = Math.ceil(categoriasData.length / categoriasPerPage);
    var start = (categoriasPage - 1) * categoriasPerPage;
    var pageData = categoriasData.slice(start, start + categoriasPerPage);
    var buscar = document.getElementById('buscarCategorias') ? document.getElementById('buscarCategorias').value : '';

    if (pageData.length === 0) { tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No se encontraron categorías</td></tr>'; return; }

    tbody.innerHTML = pageData.map(function(cat) {
        return '<tr>' +
            '<td><span class="fw-medium" style="color:#1e3c72">' + resaltarTexto(cat.nombre, buscar) + '</span></td>' +
            '<td>' + escapeHtml(cat.descripcion ? cat.descripcion.substring(0, 50) : '—') + '</td>' +
            '<td><span class="badge bg-info text-dark">' + (cat.modelos_count || 0) + '</span></td>' +
            '<td><span class="badge ' + (cat.activo ? 'bg-success' : 'bg-danger') + '">' + (cat.activo ? 'Activa' : 'Inactiva') + '</span></td>' +
            '<td class="text-end">' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="verCategoria(' + cat.id + ')">' + SVG_ICONS.ver + '</button> ' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="editarCategoria(' + cat.id + ')">' + SVG_ICONS.editar + '</button> ' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="toggleCategoria(' + cat.id + ')">' + SVG_ICONS.toggle + '</button> ' +
                '<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar(\'categoria\',' + cat.id + ',\'' + escapeHtml(cat.nombre).replace(/'/g, "\\'") + '\',' + (cat.modelos_count || 0) + ')">' + SVG_ICONS.eliminar + '</button>' +
            '</td></tr>';
    }).join('') + '<tr><td colspan="5">' + renderPaginacionFrontend(totalPages, categoriasPage, 'categorias') + '</td></tr>';
}

window.abrirModalCategoria = function(id) {
    var modalElement = document.getElementById('modalCategoria');
    if (!modalElement) return;
    var modal = new bootstrap.Modal(modalElement);
    var form = document.getElementById('formCategoria');
    form.reset();
    if (id) {
        document.getElementById('modalCategoriaLabel').textContent = 'Editar Categoría';
        document.getElementById('formMethodCategoria').value = 'PUT';
        document.getElementById('categoriaId').value = id;
        fetch('/admin/equipos/categorias/' + id, { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(response) {
            if (response.success) { document.getElementById('categoria_nombre').value = response.data.nombre; document.getElementById('categoria_descripcion').value = response.data.descripcion || ''; }
        });
    } else {
        document.getElementById('modalCategoriaLabel').textContent = 'Nueva Categoría';
        document.getElementById('formMethodCategoria').value = 'POST';
        document.getElementById('categoriaId').value = '';
    }
    modal.show();
};

window.editarCategoria = function(id) { window.abrirModalCategoria(id); };

window.verCategoria = function(id) {
    fetch('/admin/equipos/categorias/' + id, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var d = response.data;
            var html = '<div class="detail-card">' +
                '<div class="detail-card-header"><div class="detail-card-icon bg-primary-dark"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/></svg></div><div><h5 class="mb-0">' + escapeHtml(d.nombre) + '</h5><span class="badge ' + (d.activo ? 'bg-success' : 'bg-danger') + '">' + (d.activo ? 'Activa' : 'Inactiva') + '</span></div></div>' +
                '<div class="detail-card-body"><div class="detail-row"><span class="detail-label">Descripción</span><span class="detail-value">' + (escapeHtml(d.descripcion) || 'Sin descripción') + '</span></div><div class="detail-row"><span class="detail-label">Total Modelos</span><span class="detail-value badge bg-info text-dark">' + (d.modelos_count || 0) + '</span></div></div>';
            if (d.modelos && d.modelos.length) {
                html += '<div class="detail-card-footer"><h6 class="detail-section-title">Modelos asociados</h6><div class="detail-chips">' +
                    d.modelos.map(function(m) { return '<span class="detail-chip">' + escapeHtml(m.nombre) + '<small>' + escapeHtml(m.marca ? m.marca.nombre : 'N/A') + '</small></span>'; }).join('') + '</div></div>';
            }
            html += '</div>';
            document.getElementById('modalDetalleLabel').textContent = 'Detalle de Categoría';
            document.getElementById('detalleContenido').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        }
    });
};

window.toggleCategoria = function(id) {
    fetch('/admin/equipos/categorias/' + id + '/toggle', { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) { if (response.success) { mostrarToast(response.message, 'success'); cargarCategorias(); } });
};

function guardarCategoria() {
    var id = document.getElementById('categoriaId').value;
    var method = document.getElementById('formMethodCategoria').value;
    var url = method === 'PUT' ? '/admin/equipos/categorias/' + id : '/admin/equipos/categorias';
    var formData = new FormData(document.getElementById('formCategoria'));
    if (method === 'PUT') formData.append('_method', 'PUT');
    fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: formData })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) { bootstrap.Modal.getInstance(document.getElementById('modalCategoria')).hide(); mostrarToast(response.message, 'success'); cargarCategorias(); }
        else { mostrarToast(response.message, 'error'); }
    });
}

// ==================== MODELOS ====================
var modelosData = [];
var modelosPage = 1;
var modelosPerPage = 10;

function cargarModelos() {
    var buscar = document.getElementById('buscarModelos') ? document.getElementById('buscarModelos').value : '';
    var marcaId = document.getElementById('filtroMarcaModelos') ? document.getElementById('filtroMarcaModelos').value : '';
    var categoriaId = document.getElementById('filtroCategoriaModelos') ? document.getElementById('filtroCategoriaModelos').value : '';
    var estado = document.getElementById('filtroEstadoModelos') ? document.getElementById('filtroEstadoModelos').value : '';
    var url = '/admin/equipos/modelos?buscar=' + encodeURIComponent(buscar) + '&estado=' + estado;
    if (marcaId) url += '&marca_id=' + marcaId;
    if (categoriaId) url += '&categoria_id=' + categoriaId;
    fetch(url, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) { modelosData = response.data; modelosPage = 1; renderizarModelos(); }
    });
}

function renderizarModelos() {
    var tbody = document.getElementById('tablaModelos');
    if (!tbody) return;
    var totalPages = Math.ceil(modelosData.length / modelosPerPage);
    var start = (modelosPage - 1) * modelosPerPage;
    var pageData = modelosData.slice(start, start + modelosPerPage);
    var buscar = document.getElementById('buscarModelos') ? document.getElementById('buscarModelos').value : '';

    if (pageData.length === 0) { tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron modelos</td></tr>'; return; }

    tbody.innerHTML = pageData.map(function(m) {
        return '<tr>' +
            '<td><span class="fw-medium" style="color:#1e3c72">' + resaltarTexto(m.nombre, buscar) + '</span></td>' +
            '<td>' + escapeHtml(m.marca ? m.marca.nombre : 'N/A') + '</td>' +
            '<td>' + escapeHtml(m.categoria ? m.categoria.nombre : 'N/A') + '</td>' +
            '<td>' + escapeHtml(m.descripcion ? m.descripcion.substring(0, 40) : '—') + '</td>' +
            '<td><span class="badge ' + (m.activo ? 'bg-success' : 'bg-danger') + '">' + (m.activo ? 'Activo' : 'Inactivo') + '</span></td>' +
            '<td class="text-end">' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="verModelo(' + m.id + ')">' + SVG_ICONS.ver + '</button> ' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="editarModelo(' + m.id + ')">' + SVG_ICONS.editar + '</button> ' +
                '<button class="btn btn-sm btn-outline-primary-dark" onclick="toggleModelo(' + m.id + ')">' + SVG_ICONS.toggle + '</button> ' +
                '<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar(\'modelo\',' + m.id + ',\'' + escapeHtml(m.nombre).replace(/'/g, "\\'") + '\',0)">' + SVG_ICONS.eliminar + '</button>' +
            '</td></tr>';
    }).join('') + '<tr><td colspan="6">' + renderPaginacionFrontend(totalPages, modelosPage, 'modelos') + '</td></tr>';
}

function cargarSelectsModelo() {
    fetch('/admin/equipos/marcas-list', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var select = document.getElementById('modelo_marca_id');
            var filtro = document.getElementById('filtroMarcaModelos');
            if (select) { select.innerHTML = '<option value="">Seleccionar marca...</option>'; response.data.forEach(function(marca) { select.innerHTML += '<option value="' + marca.id + '">' + escapeHtml(marca.nombre) + '</option>'; }); }
            if (filtro) { filtro.innerHTML = '<option value="">Todas las marcas</option>'; response.data.forEach(function(marca) { filtro.innerHTML += '<option value="' + marca.id + '">' + escapeHtml(marca.nombre) + '</option>'; }); }
        }
    });
    fetch('/admin/equipos/categorias-list', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var select = document.getElementById('modelo_categoria_id');
            var filtro = document.getElementById('filtroCategoriaModelos');
            if (select) { select.innerHTML = '<option value="">Seleccionar categoría...</option>'; response.data.forEach(function(cat) { select.innerHTML += '<option value="' + cat.id + '">' + escapeHtml(cat.nombre) + '</option>'; }); }
            if (filtro) { filtro.innerHTML = '<option value="">Todas las categorías</option>'; response.data.forEach(function(cat) { filtro.innerHTML += '<option value="' + cat.id + '">' + escapeHtml(cat.nombre) + '</option>'; }); }
        }
    });
}

// ==================== COMPONENTES DEL MODELO ====================
window.agregarComponenteModelo = function() {
    var template = document.getElementById('templateComponenteModelo');
    if (!template) return;
    var clone = template.content.cloneNode(true);
    var container = document.getElementById('componentesModeloContainer');
    if (!container) return;
    container.appendChild(clone);
    var sinMsg = document.getElementById('sinComponentesMsg');
    if (sinMsg) sinMsg.style.display = 'none';
};

window.verificarSinComponentes = function() {
    var container = document.getElementById('componentesModeloContainer');
    if (!container) return;
    var items = container.querySelectorAll('.componente-modelo-item');
    if (items.length === 0) { var sinMsg = document.getElementById('sinComponentesMsg'); if (sinMsg) sinMsg.style.display = 'block'; }
};

window.recolectarComponentes = function() {
    var componentes = [];
    var items = document.querySelectorAll('#componentesModeloContainer .componente-modelo-item');
    items.forEach(function(item) {
        var tipo = item.querySelector('.comp-tipo') ? item.querySelector('.comp-tipo').value : '';
        var descripcion = item.querySelector('.comp-descripcion') ? item.querySelector('.comp-descripcion').value : '';
        if (tipo && descripcion) {
            componentes.push({ tipo: tipo, descripcion: descripcion, capacidad: item.querySelector('.comp-capacidad') ? item.querySelector('.comp-capacidad').value : null, requerido: true });
        }
    });
    return componentes;
};

function cargarComponentesExistentes(modeloId) {
    fetch('/admin/equipos/modelos/' + modeloId + '/componentes', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success && response.data && response.data.length > 0) {
            var existentes = document.getElementById('componentesExistentesContainer');
            if (existentes) existentes.style.display = 'block';
            var lista = document.getElementById('listaComponentesExistentes');
            if (lista) {
                lista.innerHTML = response.data.map(function(c) {
                    return '<div class="componente-existente-item border rounded p-2 mb-2 d-flex justify-content-between align-items-center bg-white">' +
                        '<div><strong>' + escapeHtml(c.tipo) + '</strong> - ' + escapeHtml(c.descripcion) + (c.capacidad ? '<span class="badge bg-secondary ms-2">' + escapeHtml(c.capacidad) + '</span>' : '') + '</div>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger" onclick="window.eliminarComponenteExistente(' + c.id + ', this)">' + SVG_ICONS.eliminar + '</button></div>';
                }).join('');
            }
        }
    });
}

window.eliminarComponenteExistente = function(componenteId, btn) {
    var modeloId = document.getElementById('modeloId') ? document.getElementById('modeloId').value : '';
    if (!modeloId) return;
    if (!confirm('¿Eliminar este componente?')) return;
    fetch('/admin/equipos/modelos/' + modeloId + '/componentes/' + componenteId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) { btn.closest('.componente-existente-item').remove(); mostrarToast('Componente eliminado', 'success'); var lista = document.getElementById('listaComponentesExistentes'); if (lista && lista.children.length === 0) { var existentes = document.getElementById('componentesExistentesContainer'); if (existentes) existentes.style.display = 'none'; } }
    });
};

function guardarComponentesModelo(modeloId, componentes) {
    if (!componentes || componentes.length === 0) {
        console.log('No hay componentes para guardar');
        return Promise.resolve();
    }

    console.log('Guardando ' + componentes.length + ' componentes para modelo ' + modeloId);

    var promesas = componentes.map(function(comp, index) {
        console.log('Enviando componente ' + index + ':', JSON.stringify(comp));

        return fetch('/admin/equipos/modelos/' + modeloId + '/componentes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(comp)
        })
        .then(function(r) {
            console.log('Respuesta componente ' + index + ' status:', r.status);
            return r.json();
        })
        .then(function(data) {
            console.log('Respuesta componente ' + index + ' data:', data);
            return data;
        })
        .catch(function(err) {
            console.error('Error en componente ' + index + ':', err);
            throw err;
        });
    });

    return Promise.all(promesas);
}

// ==================== ABRIR MODAL MODELO ====================
window.abrirModalModelo = function(id) {
    var modalElement = document.getElementById('modalModelo');
    if (!modalElement) return;
    var container = document.getElementById('componentesModeloContainer');
    if (container) container.innerHTML = '';
    var sinMsg = document.getElementById('sinComponentesMsg');
    if (sinMsg) sinMsg.style.display = 'block';
    var existentes = document.getElementById('componentesExistentesContainer');
    if (existentes) existentes.style.display = 'none';
    var lista = document.getElementById('listaComponentesExistentes');
    if (lista) lista.innerHTML = '';
    var modal = new bootstrap.Modal(modalElement);
    var form = document.getElementById('formModelo');
    form.reset();
    document.getElementById('formMethodModelo').value = 'POST';
    document.getElementById('modeloId').value = '';
    document.getElementById('modalModeloLabel').textContent = 'Nuevo Modelo';
    cargarSelectsModelo();
    if (id) {
        document.getElementById('modalModeloLabel').textContent = 'Editar Modelo';
        document.getElementById('formMethodModelo').value = 'PUT';
        document.getElementById('modeloId').value = id;
        fetch('/admin/equipos/modelos/' + id, { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(response) {
            if (response.success) {
                var d = response.data;
                setTimeout(function() { document.getElementById('modelo_marca_id').value = d.marca_id; document.getElementById('modelo_categoria_id').value = d.categoria_id; }, 300);
                document.getElementById('modelo_nombre').value = d.nombre;
                document.getElementById('modelo_descripcion').value = d.descripcion || '';
                cargarComponentesExistentes(id);
            }
        });
    }
    modal.show();
};

window.editarModelo = function(id) { window.abrirModalModelo(id); };

window.verModelo = function(id) {
    fetch('/admin/equipos/modelos/' + id, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (!response.success) return;
        var d = response.data;
        fetch('/admin/equipos/modelos/' + id + '/componentes', { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(compResponse) {
            var componentes = compResponse.success ? compResponse.data : [];
            var html = '<div class="detail-card">' +
                '<div class="detail-card-header"><div class="detail-card-icon bg-primary-dark">' + SVG_ICONS.modelo + '</div><div><h5 class="mb-0">' + escapeHtml(d.nombre) + '</h5><span class="badge ' + (d.activo ? 'bg-success' : 'bg-danger') + '">' + (d.activo ? 'Activo' : 'Inactivo') + '</span></div></div>' +
                '<div class="detail-card-body"><div class="detail-row"><span class="detail-label">Marca</span><span class="detail-value">' + escapeHtml(d.marca ? d.marca.nombre : 'N/A') + '</span></div><div class="detail-row"><span class="detail-label">Categoría</span><span class="detail-value">' + escapeHtml(d.categoria ? d.categoria.nombre : 'N/A') + '</span></div><div class="detail-row"><span class="detail-label">Descripción</span><span class="detail-value">' + (escapeHtml(d.descripcion) || 'Sin descripción') + '</span></div></div>';
            if (componentes.length > 0) {
                html += '<div class="detail-card-footer"><h6 class="detail-section-title">Componentes (' + componentes.length + ')</h6><div class="detail-chips">' +
                    componentes.map(function(c) { return '<span class="detail-chip">' + escapeHtml(c.tipo) + ' - ' + escapeHtml(c.descripcion) + '<small>' + escapeHtml(c.capacidad || 'N/A') + '</small></span>'; }).join('') + '</div></div>';
            } else { html += '<div class="detail-card-footer text-muted text-center py-2">Sin componentes registrados</div>'; }
            html += '</div>';
            document.getElementById('modalDetalleLabel').textContent = 'Detalle de Modelo';
            document.getElementById('detalleContenido').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        });
    });
};

window.toggleModelo = function(id) {
    fetch('/admin/equipos/modelos/' + id + '/toggle', { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) { if (response.success) { mostrarToast(response.message, 'success'); cargarModelos(); } });
};

function guardarModelo() {
    var id = document.getElementById('modeloId').value;
    var method = document.getElementById('formMethodModelo').value;
    var url = method === 'PUT' ? '/admin/equipos/modelos/' + id : '/admin/equipos/modelos';
    var formData = new FormData(document.getElementById('formModelo'));
    if (method === 'PUT') formData.append('_method', 'PUT');

    fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: formData })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            // Obtener el ID correcto: si es edición usa 'id', si es nuevo usa 'response.data.id'
            var modeloId = id || (response.data && response.data.id ? response.data.id : null);
            console.log('Modelo guardado. ID:', modeloId, 'Respuesta:', response);

            var componentes = window.recolectarComponentes();
            console.log('Componentes a guardar:', componentes);

            if (modeloId && componentes.length > 0) {
                guardarComponentesModelo(modeloId, componentes).then(function() {
                    mostrarToast('Modelo y componentes guardados exitosamente', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('modalModelo')).hide();
                    cargarModelos();
                }).catch(function(err) {
                    console.error('Error guardando componentes:', err);
                    mostrarToast('Modelo guardado pero fallaron los componentes', 'warning');
                    bootstrap.Modal.getInstance(document.getElementById('modalModelo')).hide();
                    cargarModelos();
                });
            } else if (modeloId) {
                mostrarToast(response.message || 'Modelo guardado exitosamente', 'success');
                bootstrap.Modal.getInstance(document.getElementById('modalModelo')).hide();
                cargarModelos();
            } else {
                mostrarToast('Error: No se pudo obtener el ID del modelo', 'error');
            }
        } else {
            mostrarToast(response.message || 'Error al guardar', 'error');
        }
    }).catch(function(err) {
        console.error('Error en guardarModelo:', err);
        mostrarToast('Error de conexión al guardar', 'error');
    });
}

// ==================== ELIMINACIÓN ====================
var elementoAEliminar = null;

window.confirmarEliminar = function(tipo, id, nombre, tieneDependencias) {
    if (tipo !== 'modelo' && tieneDependencias > 0) { mostrarToast('No se puede eliminar porque tiene elementos asociados', 'error'); return; }
    elementoAEliminar = { tipo: tipo, id: id };
    document.getElementById('deleteNombre').textContent = nombre;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
};

function confirmarEliminacion() {
    if (!elementoAEliminar) return;
    var tipo = elementoAEliminar.tipo;
    var id = elementoAEliminar.id;
    var url = '';
    if (tipo === 'marca') url = '/admin/equipos/marcas/' + id;
    else if (tipo === 'categoria') url = '/admin/equipos/categorias/' + id;
    else if (tipo === 'modelo') url = '/admin/equipos/modelos/' + id;
    fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
        if (response.success) { mostrarToast(response.message, 'success'); if (tipo === 'marca') cargarMarcas(); else if (tipo === 'categoria') cargarCategorias(); else if (tipo === 'modelo') cargarModelos(); }
        else { mostrarToast(response.message, 'error'); }
        elementoAEliminar = null;
    });
}

// ==================== ÁRBOL ====================
function cargarArbol() {
    var buscar = document.getElementById('buscarArbol') ? document.getElementById('buscarArbol').value.toLowerCase() : '';
    var contenedor = document.getElementById('arbolContenedor');
    contenedor.innerHTML = '<div class="text-center py-4 text-muted">Cargando árbol...</div>';
    fetch('/admin/equipos/marcas', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        var marcas = response.data || [];
        contenedor.innerHTML = '<div class="arbol-container"></div>';
        var arbolContainer = contenedor.querySelector('.arbol-container');
        if (marcas.length > 0) {
            marcas.forEach(function(marca) {
                fetch('/admin/equipos/marcas/' + marca.id, { headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (result.success) {
                        var d = result.data;
                        var coincide = !buscar || d.nombre.toLowerCase().indexOf(buscar) >= 0;
                        if (coincide || !buscar) arbolContainer.innerHTML += renderNodoMarca(d);
                    }
                });
            });
        } else { contenedor.innerHTML = '<p class="text-center py-4 text-muted">No hay marcas registradas</p>'; }
    });
}

function renderNodoMarca(marca) {
    var html = '<div class="arbol-nodo arbol-raiz"><div class="arbol-nodo-header" onclick="toggleNodo(this)"><span class="arbol-toggle">▼</span><span class="arbol-icon">' + SVG_ICONS.marca + '</span><span class="arbol-nombre">' + escapeHtml(marca.nombre) + '</span><span class="arbol-badge">' + (marca.modelos_count || 0) + ' mod.</span></div><div class="arbol-hijos">';
    if (marca.modelos && marca.modelos.length) {
        marca.modelos.forEach(function(modelo) {
            html += '<div class="arbol-nodo arbol-hoja"><div class="arbol-nodo-header"><span class="arbol-icon">' + SVG_ICONS.modelo + '</span><span class="arbol-nombre">' + escapeHtml(modelo.nombre) + '</span><span class="arbol-sub">' + escapeHtml(modelo.categoria ? modelo.categoria.nombre : '') + '</span></div></div>';
        });
    } else { html += '<div class="ps-4 text-muted small">Sin modelos</div>'; }
    html += '</div></div>';
    return html;
}

window.toggleNodo = function(header) {
    var hijos = header.nextElementSibling;
    var toggle = header.querySelector('.arbol-toggle');
    if (hijos) { hijos.classList.toggle('collapsed'); toggle.classList.toggle('collapsed'); }
};

window.expandirTodo = function() { document.querySelectorAll('.arbol-hijos').forEach(function(h) { h.classList.remove('collapsed'); }); };
window.colapsarTodo = function() { document.querySelectorAll('.arbol-hijos').forEach(function(h) { h.classList.add('collapsed'); }); };
