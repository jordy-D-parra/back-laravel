// resources/js/admin-equipos.js

// Esperar a que DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - Inicializando módulo de equipos');
    
    // Inicializar funciones
    cargarMarcas();
    cargarCategorias();
    cargarModelos();
    cargarArbol();
    
    // Event listeners
    const buscarMarcas = document.getElementById('buscarMarcas');
    if (buscarMarcas) buscarMarcas.addEventListener('input', cargarMarcas);
    
    const filtroEstadoMarcas = document.getElementById('filtroEstadoMarcas');
    if (filtroEstadoMarcas) filtroEstadoMarcas.addEventListener('change', cargarMarcas);
    
    const buscarCategorias = document.getElementById('buscarCategorias');
    if (buscarCategorias) buscarCategorias.addEventListener('input', cargarCategorias);
    
    const filtroEstadoCategorias = document.getElementById('filtroEstadoCategorias');
    if (filtroEstadoCategorias) filtroEstadoCategorias.addEventListener('change', cargarCategorias);
    
    const buscarModelos = document.getElementById('buscarModelos');
    if (buscarModelos) buscarModelos.addEventListener('input', cargarModelos);
    
    const filtroMarcaModelos = document.getElementById('filtroMarcaModelos');
    if (filtroMarcaModelos) filtroMarcaModelos.addEventListener('change', cargarModelos);
    
    const filtroCategoriaModelos = document.getElementById('filtroCategoriaModelos');
    if (filtroCategoriaModelos) filtroCategoriaModelos.addEventListener('change', cargarModelos);
    
    const filtroEstadoModelos = document.getElementById('filtroEstadoModelos');
    if (filtroEstadoModelos) filtroEstadoModelos.addEventListener('change', cargarModelos);
    
    const buscarArbol = document.getElementById('buscarArbol');
    if (buscarArbol) buscarArbol.addEventListener('input', cargarArbol);
    
    // Eventos de formularios
    const formMarca = document.getElementById('formMarca');
    if (formMarca) formMarca.addEventListener('submit', function(e) { e.preventDefault(); guardarMarca(); });
    
    const formCategoria = document.getElementById('formCategoria');
    if (formCategoria) formCategoria.addEventListener('submit', function(e) { e.preventDefault(); guardarCategoria(); });
    
    const formModelo = document.getElementById('formModelo');
    if (formModelo) formModelo.addEventListener('submit', function(e) { e.preventDefault(); guardarModelo(); });
    
    // Botón eliminar
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmar) btnConfirmar.addEventListener('click', function() { confirmarEliminacion(); });
});

// ==================== UTILIDADES ====================
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function resaltarTexto(texto, buscar) {
    if (!buscar || !texto) return escapeHtml(texto);
    const regex = new RegExp(`(${buscar.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return escapeHtml(texto).replace(regex, '<span class="highlight">$1</span>');
}

function mostrarToast(mensaje, tipo = 'success') {
    const colores = { success: '#1e7e34', error: '#c5221f', warning: '#f6c23e', info: '#1e3c72' };
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        background: ${colores[tipo]}; color: white; padding: 12px 20px;
        border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out; cursor: pointer; z-index: 10000;
    `;
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ==================== MARCAS ====================
function cargarMarcas() {
    const buscar = document.getElementById('buscarMarcas')?.value || '';
    const estado = document.getElementById('filtroEstadoMarcas')?.value || '';
    
    fetch(`/admin/equipos/marcas?buscar=${encodeURIComponent(buscar)}&estado=${estado}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(response => {
        if (response.success) {
            const tbody = document.getElementById('tablaMarcas');
            if (tbody) tbody.innerHTML = renderTablaMarcas(response.data, buscar);
        }
    })
    .catch(error => console.error('Error cargando marcas:', error));
}

function renderTablaMarcas(marcas, buscar) {
    if (!marcas.length) {
        return `<tr><td colspan="5" class="text-center py-4 text-muted">No se encontraron marcas</td></tr>`;
    }
    return marcas.map(marca => `
        <tr>
            <td><span class="fw-medium" style="color:#1e3c72">${resaltarTexto(marca.nombre, buscar)}</span></td>
            <td>${escapeHtml(marca.descripcion?.substring(0, 50) || '—')}</td>
            <td><span class="badge-activo">${marca.modelos_count || 0}</span></td>
            <td><span class="badge ${marca.activo ? 'badge-activo' : 'badge-inactivo'}">${marca.activo ? 'Activa' : 'Inactiva'}</span></td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary-dark" onclick="verMarca(${marca.id})" title="Ver">👁️</button>
                <button class="btn btn-sm btn-outline-primary-dark" onclick="editarMarca(${marca.id})" title="Editar">✏️</button>
                <button class="btn btn-sm btn-outline-primary-dark" onclick="toggleMarca(${marca.id})" title="Cambiar estado">🔄</button>
                <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('marca', ${marca.id}, '${escapeHtml(marca.nombre).replace(/'/g, "\\'")}', ${marca.modelos_count || 0})" title="Eliminar">🗑️</button>
            </td>
        </tr>
    `).join('');
}

// FUNCIONES GLOBALES PARA MARCAS
window.abrirModalMarca = function(id) {
    console.log('abrirModalMarca llamado');
    const modalElement = document.getElementById('modalMarca');
    if (!modalElement) {
        console.error('Modal marca no encontrado');
        return;
    }
    const modal = new bootstrap.Modal(modalElement);
    const form = document.getElementById('formMarca');
    form.reset();
    
    if (id) {
        document.getElementById('modalMarcaLabel').textContent = 'Editar Marca';
        document.getElementById('formMethodMarca').value = 'PUT';
        document.getElementById('marcaId').value = id;
        
        fetch(`/admin/equipos/marcas/${id}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(response => {
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

window.editarMarca = function(id) {
    window.abrirModalMarca(id);
};

window.verMarca = function(id) {
    fetch(`/admin/equipos/marcas/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                const d = response.data;
                const html = `
                    <div class="detail-header"><h5>${escapeHtml(d.nombre)}</h5><span class="badge ${d.activo ? 'badge-activo' : 'badge-inactivo'}">${d.activo ? 'Activa' : 'Inactiva'}</span></div>
                    <div class="detail-grid">
                        <div class="detail-item"><div class="detail-label">Descripción</div><div class="detail-value">${escapeHtml(d.descripcion) || 'Sin descripción'}</div></div>
                        <div class="detail-item"><div class="detail-label">Total Modelos</div><div class="detail-value">${d.modelos_count || 0}</div></div>
                    </div>
                    <div class="detail-section"><div class="detail-section-title">📱 Modelos asociados</div>
                    ${d.modelos?.length ? `<ul class="detail-list">${d.modelos.map(m => `<li class="detail-list-item"><div class="item-info"><span class="item-name">${escapeHtml(m.nombre)}</span><span class="item-sub">Categoría: ${escapeHtml(m.categoria?.nombre || 'N/A')}</span></div></li>`).join('')}</ul>` : '<div class="detail-empty">Sin modelos asociados</div>'}</div>
                `;
                document.getElementById('modalDetalleLabel').textContent = 'Detalle de Marca';
                document.getElementById('detalleContenido').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalDetalle')).show();
            }
        });
};

window.toggleMarca = function(id) {
    fetch(`/admin/equipos/marcas/${id}/toggle`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(response => { if (response.success) { mostrarToast(response.message, 'success'); cargarMarcas(); } });
};

function guardarMarca() {
    const id = document.getElementById('marcaId').value;
    const method = document.getElementById('formMethodMarca').value;
    let url = '/admin/equipos/marcas';
    if (method === 'PUT') url = `/admin/equipos/marcas/${id}`;
    
    const formData = new FormData(document.getElementById('formMarca'));
    if (method === 'PUT') formData.append('_method', 'PUT');
    
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': getCsrfToken() },
        body: formData
    })
    .then(r => r.json())
    .then(response => {
        if (response.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalMarca')).hide();
            mostrarToast(response.message, 'success');
            cargarMarcas();
            setTimeout(() => location.reload(), 500);
        } else {
            mostrarToast(response.message, 'error');
        }
    });
}

// ==================== CATEGORÍAS ====================
function cargarCategorias() {
    const buscar = document.getElementById('buscarCategorias')?.value || '';
    const estado = document.getElementById('filtroEstadoCategorias')?.value || '';
    
    fetch(`/admin/equipos/categorias?buscar=${encodeURIComponent(buscar)}&estado=${estado}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(response => {
        if (response.success) {
            const tbody = document.getElementById('tablaCategorias');
            if (tbody) tbody.innerHTML = renderTablaCategorias(response.data, buscar);
        }
    });
}

function renderTablaCategorias(categorias, buscar) {
    if (!categorias.length) return `<tr><td colspan="5" class="text-center py-4 text-muted">No se encontraron categorías</td></tr>`;
    return categorias.map(cat => `
        <tr>
            <td><span class="fw-medium" style="color:#1e3c72">${resaltarTexto(cat.nombre, buscar)}</span></td>
            <td>${escapeHtml(cat.descripcion?.substring(0, 50) || '—')}</td>
            <td><span class="badge-activo">${cat.modelos_count || 0}</span></td>
            <td><span class="badge ${cat.activo ? 'badge-activo' : 'badge-inactivo'}">${cat.activo ? 'Activa' : 'Inactiva'}</span></td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary-dark" onclick="verCategoria(${cat.id})" title="Ver">👁️</button>
                <button class="btn btn-sm btn-outline-primary-dark" onclick="editarCategoria(${cat.id})" title="Editar">✏️</button>
                <button class="btn btn-sm btn-outline-primary-dark" onclick="toggleCategoria(${cat.id})" title="Cambiar estado">🔄</button>
                <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('categoria', ${cat.id}, '${escapeHtml(cat.nombre).replace(/'/g, "\\'")}', ${cat.modelos_count || 0})" title="Eliminar">🗑️</button>
            </td>
        </tr>
    `).join('');
}

// FUNCIONES GLOBALES PARA CATEGORÍAS
window.abrirModalCategoria = function(id) {
    console.log('abrirModalCategoria llamado');
    const modalElement = document.getElementById('modalCategoria');
    if (!modalElement) {
        console.error('Modal categoria no encontrado');
        return;
    }
    const modal = new bootstrap.Modal(modalElement);
    const form = document.getElementById('formCategoria');
    form.reset();
    
    if (id) {
        document.getElementById('modalCategoriaLabel').textContent = 'Editar Categoría';
        document.getElementById('formMethodCategoria').value = 'PUT';
        document.getElementById('categoriaId').value = id;
        
        fetch(`/admin/equipos/categorias/${id}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(response => {
                if (response.success) {
                    document.getElementById('categoria_nombre').value = response.data.nombre;
                    document.getElementById('categoria_descripcion').value = response.data.descripcion || '';
                }
            });
    } else {
        document.getElementById('modalCategoriaLabel').textContent = 'Nueva Categoría';
        document.getElementById('formMethodCategoria').value = 'POST';
        document.getElementById('categoriaId').value = '';
    }
    modal.show();
};

window.editarCategoria = function(id) {
    window.abrirModalCategoria(id);
};

window.verCategoria = function(id) {
    fetch(`/admin/equipos/categorias/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                const d = response.data;
                const html = `
                    <div class="detail-header"><h5>${escapeHtml(d.nombre)}</h5><span class="badge ${d.activo ? 'badge-activo' : 'badge-inactivo'}">${d.activo ? 'Activa' : 'Inactiva'}</span></div>
                    <div class="detail-grid"><div class="detail-item"><div class="detail-label">Descripción</div><div class="detail-value">${escapeHtml(d.descripcion) || 'Sin descripción'}</div></div><div class="detail-item"><div class="detail-label">Total Modelos</div><div class="detail-value">${d.modelos_count || 0}</div></div></div>
                    <div class="detail-section"><div class="detail-section-title">📱 Modelos asociados</div>
                    ${d.modelos?.length ? `<ul class="detail-list">${d.modelos.map(m => `<li class="detail-list-item"><div class="item-info"><span class="item-name">${escapeHtml(m.nombre)}</span><span class="item-sub">Marca: ${escapeHtml(m.marca?.nombre || 'N/A')}</span></div></li>`).join('')}</ul>` : '<div class="detail-empty">Sin modelos asociados</div>'}</div>
                `;
                document.getElementById('modalDetalleLabel').textContent = 'Detalle de Categoría';
                document.getElementById('detalleContenido').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalDetalle')).show();
            }
        });
};

window.toggleCategoria = function(id) {
    fetch(`/admin/equipos/categorias/${id}/toggle`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(response => { if (response.success) { mostrarToast(response.message, 'success'); cargarCategorias(); } });
};

function guardarCategoria() {
    const id = document.getElementById('categoriaId').value;
    const method = document.getElementById('formMethodCategoria').value;
    let url = '/admin/equipos/categorias';
    if (method === 'PUT') url = `/admin/equipos/categorias/${id}`;
    
    const formData = new FormData(document.getElementById('formCategoria'));
    if (method === 'PUT') formData.append('_method', 'PUT');
    
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': getCsrfToken() },
        body: formData
    })
    .then(r => r.json())
    .then(response => {
        if (response.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalCategoria')).hide();
            mostrarToast(response.message, 'success');
            cargarCategorias();
            setTimeout(() => location.reload(), 500);
        } else {
            mostrarToast(response.message, 'error');
        }
    });
}

// ==================== MODELOS ====================
function cargarModelos() {
    const buscar = document.getElementById('buscarModelos')?.value || '';
    const marcaId = document.getElementById('filtroMarcaModelos')?.value || '';
    const categoriaId = document.getElementById('filtroCategoriaModelos')?.value || '';
    const estado = document.getElementById('filtroEstadoModelos')?.value || '';
    
    let url = `/admin/equipos/modelos?buscar=${encodeURIComponent(buscar)}&estado=${estado}`;
    if (marcaId) url += `&marca_id=${marcaId}`;
    if (categoriaId) url += `&categoria_id=${categoriaId}`;
    
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                const tbody = document.getElementById('tablaModelos');
                if (tbody) tbody.innerHTML = renderTablaModelos(response.data, buscar);
            }
        });
}

function renderTablaModelos(modelos, buscar) {
    if (!modelos?.length) return `<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron modelos</td></tr>`;
    return modelos.map(m => `
        <tr>
            <td><span class="fw-medium" style="color:#1e3c72">${resaltarTexto(m.nombre, buscar)}</span></td>
            <td>${escapeHtml(m.marca?.nombre || 'N/A')}</td>
            <td>${escapeHtml(m.categoria?.nombre || 'N/A')}</td>
            <td>${escapeHtml(m.descripcion?.substring(0, 40) || '—')}</td>
            <td><span class="badge ${m.activo ? 'badge-activo' : 'badge-inactivo'}">${m.activo ? 'Activo' : 'Inactivo'}</span></td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary-dark" onclick="verModelo(${m.id})" title="Ver">👁️</button>
                <button class="btn btn-sm btn-outline-primary-dark" onclick="editarModelo(${m.id})" title="Editar">✏️</button>
                <button class="btn btn-sm btn-outline-primary-dark" onclick="toggleModelo(${m.id})" title="Cambiar estado">🔄</button>
                <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('modelo', ${m.id}, '${escapeHtml(m.nombre).replace(/'/g, "\\'")}', 0)" title="Eliminar">🗑️</button>
            </td>
        </tr>
    `).join('');
}

function cargarSelectsModelo() {
    fetch('/admin/equipos/marcas-list', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                const select = document.getElementById('modelo_marca_id');
                if (select) {
                    select.innerHTML = '<option value="">Seleccionar marca...</option>';
                    response.data.forEach(marca => select.innerHTML += `<option value="${marca.id}">${escapeHtml(marca.nombre)}</option>`);
                }
            }
        });
    
    fetch('/admin/equipos/categorias-list', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                const select = document.getElementById('modelo_categoria_id');
                if (select) {
                    select.innerHTML = '<option value="">Seleccionar categoría...</option>';
                    response.data.forEach(cat => select.innerHTML += `<option value="${cat.id}">${escapeHtml(cat.nombre)}</option>`);
                }
            }
        });
}

// FUNCIONES GLOBALES PARA MODELOS
window.abrirModalModelo = function(id) {
    console.log('abrirModalModelo llamado');
    const modalElement = document.getElementById('modalModelo');
    if (!modalElement) return;
    const modal = new bootstrap.Modal(modalElement);
    document.getElementById('formModelo').reset();
    cargarSelectsModelo();
    
    if (id) {
        document.getElementById('modalModeloLabel').textContent = 'Editar Modelo';
        document.getElementById('formMethodModelo').value = 'PUT';
        document.getElementById('modeloId').value = id;
        fetch(`/admin/equipos/modelos/${id}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(response => {
                if (response.success) {
                    setTimeout(() => {
                        document.getElementById('modelo_marca_id').value = response.data.marca_id;
                        document.getElementById('modelo_categoria_id').value = response.data.categoria_id;
                        document.getElementById('modelo_nombre').value = response.data.nombre;
                        document.getElementById('modelo_descripcion').value = response.data.descripcion || '';
                        document.getElementById('modelo_especificaciones').value = response.data.especificaciones || '';
                    }, 100);
                }
            });
    } else {
        document.getElementById('modalModeloLabel').textContent = 'Nuevo Modelo';
        document.getElementById('formMethodModelo').value = 'POST';
        document.getElementById('modeloId').value = '';
    }
    modal.show();
};

window.editarModelo = function(id) {
    window.abrirModalModelo(id);
};

window.verModelo = function(id) {
    fetch(`/admin/equipos/modelos/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                const d = response.data;
                const html = `
                    <div class="detail-header"><h5>${escapeHtml(d.nombre)}</h5><span class="badge ${d.activo ? 'badge-activo' : 'badge-inactivo'}">${d.activo ? 'Activo' : 'Inactivo'}</span></div>
                    <div class="detail-grid">
                        <div class="detail-item"><div class="detail-label">Marca</div><div class="detail-value">${escapeHtml(d.marca?.nombre || 'N/A')}</div></div>
                        <div class="detail-item"><div class="detail-label">Categoría</div><div class="detail-value">${escapeHtml(d.categoria?.nombre || 'N/A')}</div></div>
                        <div class="detail-item"><div class="detail-label">Descripción</div><div class="detail-value">${escapeHtml(d.descripcion) || 'Sin descripción'}</div></div>
                        <div class="detail-item"><div class="detail-label">Especificaciones</div><div class="detail-value">${escapeHtml(d.especificaciones) || 'Sin especificaciones'}</div></div>
                    </div>
                `;
                document.getElementById('modalDetalleLabel').textContent = 'Detalle de Modelo';
                document.getElementById('detalleContenido').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalDetalle')).show();
            }
        });
};

window.toggleModelo = function(id) {
    fetch(`/admin/equipos/modelos/${id}/toggle`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(response => { if (response.success) { mostrarToast(response.message, 'success'); cargarModelos(); } });
};

function guardarModelo() {
    const id = document.getElementById('modeloId').value;
    const method = document.getElementById('formMethodModelo').value;
    let url = '/admin/equipos/modelos';
    if (method === 'PUT') url = `/admin/equipos/modelos/${id}`;
    
    const formData = new FormData(document.getElementById('formModelo'));
    if (method === 'PUT') formData.append('_method', 'PUT');
    
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': getCsrfToken() },
        body: formData
    })
    .then(r => r.json())
    .then(response => {
        if (response.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalModelo')).hide();
            mostrarToast(response.message, 'success');
            cargarModelos();
            setTimeout(() => location.reload(), 500);
        } else {
            mostrarToast(response.message, 'error');
        }
    });
}

// ==================== ELIMINACIÓN ====================
let elementoAEliminar = null;

window.confirmarEliminar = function(tipo, id, nombre, tieneDependencias) {
    if (tieneDependencias > 0) {
        mostrarToast(`No se puede eliminar ${tipo === 'marca' ? 'la marca' : 'la categoría'} porque tiene modelos asociados`, 'error');
        return;
    }
    elementoAEliminar = { tipo, id };
    document.getElementById('deleteNombre').textContent = nombre;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
};

function confirmarEliminacion() {
    if (!elementoAEliminar) return;
    const { tipo, id } = elementoAEliminar;
    let url = '';
    switch(tipo) {
        case 'marca': url = `/admin/equipos/marcas/${id}`; break;
        case 'categoria': url = `/admin/equipos/categorias/${id}`; break;
        case 'modelo': url = `/admin/equipos/modelos/${id}`; break;
    }
    fetch(url, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(response => {
        bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
        if (response.success) {
            mostrarToast(response.message, 'success');
            if (tipo === 'marca') cargarMarcas();
            else if (tipo === 'categoria') cargarCategorias();
            else if (tipo === 'modelo') cargarModelos();
            setTimeout(() => location.reload(), 500);
        } else {
            mostrarToast(response.message, 'error');
        }
        elementoAEliminar = null;
    });
}

// ==================== ÁRBOL ====================
function cargarArbol() {
    const buscar = document.getElementById('buscarArbol')?.value.toLowerCase() || '';
    const contenedor = document.getElementById('arbolContenedor');
    contenedor.innerHTML = '<div class="loading-spinner">Cargando árbol...</div>';
    
    fetch('/admin/equipos/marcas', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            const marcas = response.data || [];
            contenedor.innerHTML = '<div class="arbol-container"></div>';
            const arbolContainer = contenedor.querySelector('.arbol-container');
            if (marcas.length > 0) {
                let procesadas = 0;
                marcas.forEach(marca => {
                    fetch(`/admin/equipos/marcas/${marca.id}`, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(result => {
                            if (result.success) {
                                const d = result.data;
                                const coincide = !buscar || d.nombre.toLowerCase().includes(buscar) || (d.modelos && d.modelos.some(m => m.nombre.toLowerCase().includes(buscar)));
                                if (coincide || !buscar) arbolContainer.innerHTML += renderNodoMarca(d);
                            }
                            procesadas++;
                            if (procesadas === marcas.length && arbolContainer.innerHTML === '') {
                                arbolContainer.innerHTML = '<p class="text-center py-4 text-muted">No se encontraron resultados</p>';
                            }
                        });
                });
            } else {
                contenedor.innerHTML = '<p class="text-center py-4 text-muted">No hay marcas registradas</p>';
            }
        });
}

function renderNodoMarca(marca) {
    let html = `<div class="arbol-nodo arbol-raiz"><div class="arbol-nodo-header" onclick="toggleNodo(this)"><span class="arbol-toggle">▼</span><span class="arbol-icon">🏢</span><span class="arbol-nombre">${escapeHtml(marca.nombre)}</span><span class="arbol-badge badge-activo">${marca.modelos_count || 0} modelos</span><div class="arbol-acciones"><button class="btn-item-action" onclick="event.stopPropagation(); verMarca(${marca.id})" title="Ver">👁️</button><button class="btn-item-action" onclick="event.stopPropagation(); editarMarca(${marca.id})" title="Editar">✏️</button></div></div><div class="arbol-hijos">`;
    if (marca.modelos && marca.modelos.length > 0) {
        marca.modelos.forEach(modelo => {
            html += `<div class="arbol-nodo arbol-hoja"><div class="arbol-nodo-header"><span class="arbol-toggle" style="visibility:hidden">▼</span><span class="arbol-icon">📱</span><span class="arbol-nombre">${escapeHtml(modelo.nombre)}</span><span class="arbol-sub">${escapeHtml(modelo.categoria?.nombre || 'Sin categoría')}</span><div class="arbol-acciones"><button class="btn-item-action" onclick="event.stopPropagation(); verModelo(${modelo.id})" title="Ver">👁️</button><button class="btn-item-action" onclick="event.stopPropagation(); editarModelo(${modelo.id})" title="Editar">✏️</button></div></div></div>`;
        });
    } else {
        html += '<div class="detail-empty">Sin modelos registrados</div>';
    }
    html += `</div></div>`;
    return html;
}

window.toggleNodo = function(header) {
    const hijos = header.nextElementSibling;
    const toggle = header.querySelector('.arbol-toggle');
    if (hijos && hijos.classList.contains('arbol-hijos')) {
        hijos.classList.toggle('collapsed');
        toggle.classList.toggle('collapsed');
    }
};

window.expandirTodo = function() {
    document.querySelectorAll('.arbol-hijos').forEach(h => h.classList.remove('collapsed'));
    document.querySelectorAll('.arbol-toggle').forEach(t => t.classList.remove('collapsed'));
};

window.colapsarTodo = function() {
    document.querySelectorAll('.arbol-hijos').forEach(h => h.classList.add('collapsed'));
    document.querySelectorAll('.arbol-toggle').forEach(t => t.classList.add('collapsed'));
};