// resources/js/admin-equipos.js
// ✅ Catálogo de equipos: Marcas, Categorías (globales), Modelos y Wizard
// ✅ Sin referencias a modelo_componente
// ✅ Wizard adaptado: la categoría es global, no lleva marca_id

(function() {
    'use strict';

    // ============================================================
    // CONSTANTES Y UTILIDADES
    // ============================================================
    const SVG_ICONS = {
        ver: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
        editar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
        toggle: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>',
        eliminar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>',
        marca: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="8" width="16" height="12" rx="1"/></svg>',
        modelo: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"/><line x1="9" y1="4" x2="9" y2="20"/></svg>',
        categoria: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/></svg>'
    };

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function resaltarTexto(texto, buscar) {
        if (!buscar || !texto) return escapeHtml(texto);
        const regex = new RegExp('(' + buscar.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return escapeHtml(texto).replace(regex, '<span class="highlight">$1</span>');
    }

    function mostrarToast(mensaje, tipo) {
        tipo = tipo || 'success';
        const colores = {
            success: '#1e7e34',
            error: '#c5221f',
            warning: '#f6c23e',
            info: '#1e3c72'
        };
        const iconos = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 10000;
            background: ${colores[tipo] || colores.success}; color: white;
            padding: 14px 20px; border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            font-weight: 500; font-size: 0.9rem;
            animation: slideInRight 0.3s ease-out; max-width: 400px;
            cursor: pointer; display: flex; align-items: center; gap: 10px;
        `;
        toast.innerHTML = `<span style="font-size:1.1rem;">${iconos[tipo]}</span><span>${escapeHtml(mensaje)}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // ============================================================
    // ESTADO GLOBAL
    // ============================================================
    let elementoAEliminar = null;
    let wizardData = {
        paso: 1,
        marcaId: null,
        marcaNombre: null,
        categoriaId: null,
        categoriaNombre: null,
        marcaEsNueva: false,
        categoriaEsNueva: false
    };

    // ============================================================
    // MARCAS
    // ============================================================
    function cargarMarcas() {
    const buscar = document.getElementById('buscarMarcas')?.value || '';
    const tbody = document.getElementById('tablaMarcas');

    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';
    }

    fetch('/admin/equipos/marcas?buscar=' + encodeURIComponent(buscar), {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'  // <-- IMPORTANTE: envía cookies de sesión
    })
    .then(r => r.json())
    .then(response => {
        if (response.success) {
            renderizarMarcas(response.data, buscar);
        } else {
            if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error al cargar marcas</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error de conexión</td></tr>';
    });
}

    function renderizarMarcas(data, buscar) {
        const tbody = document.getElementById('tablaMarcas');
        if (!tbody) return;

        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No se encontraron marcas</td></tr>';
            return;
        }

        let html = '';
        for (const marca of data) {
            html += `
                <tr>
                    <td><span class="fw-medium" style="color:#1e3c72">${resaltarTexto(marca.nombre, buscar)}</span></td>
                    <td>${escapeHtml(marca.descripcion ? marca.descripcion.substring(0, 50) : '—')}</td>
                    <td><span class="badge bg-info text-dark">${marca.modelos_count || 0}</span></td>
                    <td><span class="badge ${marca.activo ? 'bg-success' : 'bg-danger'}">${marca.activo ? 'Activa' : 'Inactiva'}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary-dark" onclick="window.verMarca(${marca.id})" title="Ver">${SVG_ICONS.ver}</button>
                        <button class="btn btn-sm btn-outline-primary-dark" onclick="window.editarMarca(${marca.id})" title="Editar">${SVG_ICONS.editar}</button>
                        <button class="btn btn-sm btn-outline-primary-dark" onclick="window.toggleMarca(${marca.id})" title="Cambiar estado">${SVG_ICONS.toggle}</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="window.confirmarEliminar('marca', ${marca.id}, '${escapeHtml(marca.nombre).replace(/'/g, "\\'")}', ${marca.modelos_count || 0})" title="Eliminar">${SVG_ICONS.eliminar}</button>
                    </td>
                </tr>
            `;
        }
        tbody.innerHTML = html;
    }

    function verMarca(id) {
        fetch('/admin/equipos/marcas/' + id, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                const d = response.data;
                const html = `
                    <div class="detail-card">
                        <div class="detail-card-header">
                            <div class="detail-card-icon bg-primary-dark">${SVG_ICONS.marca}</div>
                            <div>
                                <h5 class="mb-0">${escapeHtml(d.nombre)}</h5>
                                <span class="badge ${d.activo ? 'bg-success' : 'bg-danger'}">${d.activo ? 'Activa' : 'Inactiva'}</span>
                            </div>
                        </div>
                        <div class="detail-card-body">
                            <div class="detail-row">
                                <span class="detail-label">Descripción</span>
                                <span class="detail-value">${escapeHtml(d.descripcion) || 'Sin descripción'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total Modelos</span>
                                <span class="detail-value badge bg-info text-dark">${d.modelos_count || 0}</span>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('modalDetalleLabel').textContent = 'Detalle de Marca';
                document.getElementById('detalleContenido').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalDetalle')).show();
            }
        });
    }

    function editarMarca(id) {
        const modal = new bootstrap.Modal(document.getElementById('modalMarca'));
        const form = document.getElementById('formMarca');
        form.reset();
        document.getElementById('modalMarcaLabel').textContent = 'Editar Marca';
        document.getElementById('formMethodMarca').value = 'PUT';
        document.getElementById('marcaId').value = id;

        fetch('/admin/equipos/marcas/' + id, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                document.getElementById('marca_nombre').value = response.data.nombre;
                document.getElementById('marca_descripcion').value = response.data.descripcion || '';
            }
        });
        modal.show();
    }

    function toggleMarca(id) {
        fetch('/admin/equipos/marcas/' + id + '/toggle', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                mostrarToast(response.message || 'Estado actualizado', 'success');
                cargarMarcas();
            }
        });
    }

    // ============================================================
    // CATEGORÍAS (GLOBALES)
    // ============================================================
    function cargarCategorias() {
        const buscar = document.getElementById('buscarCategorias')?.value || '';
        const tbody = document.getElementById('tablaCategorias');

        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';
        }

        fetch('/admin/equipos/categorias?buscar=' + encodeURIComponent(buscar), {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                renderizarCategorias(response.data, buscar);
            } else {
                if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error al cargar categorías</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error de conexión</td></tr>';
        });
    }

    function renderizarCategorias(data, buscar) {
        const tbody = document.getElementById('tablaCategorias');
        if (!tbody) return;

        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No se encontraron categorías</td></tr>';
            return;
        }

        let html = '';
        for (const cat of data) {
            html += `
                <tr>
                    <td><span class="fw-medium" style="color:#1e3c72">${resaltarTexto(cat.nombre, buscar)}</span></td>
                    <td>${escapeHtml(cat.descripcion ? cat.descripcion.substring(0, 40) : '—')}</td>
                    <td><span class="badge bg-info text-dark">${cat.modelos_count || 0}</span></td>
                    <td><span class="badge ${cat.activo ? 'bg-success' : 'bg-danger'}">${cat.activo ? 'Activa' : 'Inactiva'}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary-dark" onclick="window.verCategoria(${cat.id})" title="Ver">${SVG_ICONS.ver}</button>
                        <button class="btn btn-sm btn-outline-primary-dark" onclick="window.editarCategoria(${cat.id})" title="Editar">${SVG_ICONS.editar}</button>
                        <button class="btn btn-sm btn-outline-primary-dark" onclick="window.toggleCategoria(${cat.id})" title="Cambiar estado">${SVG_ICONS.toggle}</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="window.confirmarEliminar('categoria', ${cat.id}, '${escapeHtml(cat.nombre).replace(/'/g, "\\'")}', ${cat.modelos_count || 0})" title="Eliminar">${SVG_ICONS.eliminar}</button>
                    </td>
                </tr>
            `;
        }
        tbody.innerHTML = html;
    }

    function verCategoria(id) {
        fetch('/admin/equipos/categorias/' + id, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                const d = response.data;
                const html = `
                    <div class="detail-card">
                        <div class="detail-card-header">
                            <div class="detail-card-icon bg-primary-dark">${SVG_ICONS.categoria}</div>
                            <div>
                                <h5 class="mb-0">${escapeHtml(d.nombre)}</h5>
                                <span class="badge ${d.activo ? 'bg-success' : 'bg-danger'}">${d.activo ? 'Activa' : 'Inactiva'}</span>
                            </div>
                        </div>
                        <div class="detail-card-body">
                            <div class="detail-row">
                                <span class="detail-label">Descripción</span>
                                <span class="detail-value">${escapeHtml(d.descripcion) || 'Sin descripción'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total Modelos</span>
                                <span class="detail-value badge bg-info text-dark">${d.modelos_count || 0}</span>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('modalDetalleLabel').textContent = 'Detalle de Categoría';
                document.getElementById('detalleContenido').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalDetalle')).show();
            }
        });
    }

    function editarCategoria(id) {
        const modal = new bootstrap.Modal(document.getElementById('modalCategoria'));
        const form = document.getElementById('formCategoria');
        form.reset();
        document.getElementById('modalCategoriaLabel').textContent = 'Editar Categoría';
        document.getElementById('formMethodCategoria').value = 'PUT';
        document.getElementById('categoriaId').value = id;

        fetch('/admin/equipos/categorias/' + id, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                document.getElementById('categoria_nombre').value = response.data.nombre;
                document.getElementById('categoria_descripcion').value = response.data.descripcion || '';
            }
        });
        modal.show();
    }

    function toggleCategoria(id) {
        fetch('/admin/equipos/categorias/' + id + '/toggle', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                mostrarToast(response.message || 'Estado actualizado', 'success');
                cargarCategorias();
            }
        });
    }

    // ============================================================
    // MODELOS
    // ============================================================
    function cargarModelos() {
        const buscar = document.getElementById('buscarModelos')?.value || '';
        const tbody = document.getElementById('tablaModelos');

        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';
        }

        fetch('/admin/equipos/modelos?buscar=' + encodeURIComponent(buscar), {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                renderizarModelos(response.data, buscar);
            } else {
                if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Error al cargar modelos</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Error de conexión</td></tr>';
        });
    }

    function renderizarModelos(data, buscar) {
        const tbody = document.getElementById('tablaModelos');
        if (!tbody) return;

        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron modelos</td></tr>';
            return;
        }

        let html = '';
        for (const m of data) {
            html += `
                <tr>
                    <td><span class="fw-medium" style="color:#1e3c72">${resaltarTexto(m.nombre, buscar)}</span></td>
                    <td>${escapeHtml(m.marca ? m.marca.nombre : 'N/A')}</td>
                    <td>${escapeHtml(m.categoria ? m.categoria.nombre : 'N/A')}</td>
                    <td>${escapeHtml(m.descripcion ? m.descripcion.substring(0, 40) : '—')}</td>
                    <td><span class="badge ${m.activo ? 'bg-success' : 'bg-danger'}">${m.activo ? 'Activo' : 'Inactivo'}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary-dark" onclick="window.verModelo(${m.id})" title="Ver">${SVG_ICONS.ver}</button>
                        <button class="btn btn-sm btn-outline-primary-dark" onclick="window.editarModelo(${m.id})" title="Editar">${SVG_ICONS.editar}</button>
                        <button class="btn btn-sm btn-outline-primary-dark" onclick="window.toggleModelo(${m.id})" title="Cambiar estado">${SVG_ICONS.toggle}</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="window.confirmarEliminar('modelo', ${m.id}, '${escapeHtml(m.nombre).replace(/'/g, "\\'")}', 0)" title="Eliminar">${SVG_ICONS.eliminar}</button>
                    </td>
                </tr>
            `;
        }
        tbody.innerHTML = html;
    }

    function verModelo(id) {
        fetch('/admin/equipos/modelos/' + id, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                const d = response.data;
                const html = `
                    <div class="detail-card">
                        <div class="detail-card-header">
                            <div class="detail-card-icon bg-primary-dark">${SVG_ICONS.modelo}</div>
                            <div>
                                <h5 class="mb-0">${escapeHtml(d.nombre)}</h5>
                                <span class="badge ${d.activo ? 'bg-success' : 'bg-danger'}">${d.activo ? 'Activo' : 'Inactivo'}</span>
                                <br><small class="text-muted">${escapeHtml(d.marca ? d.marca.nombre : 'N/A')} · ${escapeHtml(d.categoria ? d.categoria.nombre : 'N/A')}</small>
                            </div>
                        </div>
                        <div class="detail-card-body">
                            <div class="detail-row">
                                <span class="detail-label">Marca</span>
                                <span class="detail-value">${escapeHtml(d.marca ? d.marca.nombre : 'N/A')}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Categoría</span>
                                <span class="detail-value">${escapeHtml(d.categoria ? d.categoria.nombre : 'N/A')}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Descripción</span>
                                <span class="detail-value">${escapeHtml(d.descripcion) || 'Sin descripción'}</span>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('modalDetalleLabel').textContent = 'Detalle de Modelo';
                document.getElementById('detalleContenido').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalDetalle')).show();
            }
        });
    }

    function editarModelo(id) {
        const modal = new bootstrap.Modal(document.getElementById('modalModelo'));
        const form = document.getElementById('formModelo');
        form.reset();
        document.getElementById('modalModeloLabel').textContent = 'Editar Modelo';
        document.getElementById('formMethodModelo').value = 'PUT';
        document.getElementById('modeloId').value = id;

        Promise.all([
            cargarMarcasEnSelect('modelo_marca_id'),
            cargarCategoriasEnSelect('modelo_categoria_id')
        ]).then(() => {
            fetch('/admin/equipos/modelos/' + id, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(response => {
                if (response.success) {
                    const d = response.data;
                    document.getElementById('modelo_marca_id').value = d.marca_id;
                    document.getElementById('modelo_categoria_id').value = d.categoria_id;
                    document.getElementById('modelo_nombre').value = d.nombre;
                    document.getElementById('modelo_descripcion').value = d.descripcion || '';
                }
            });
        });

        modal.show();
    }

    function toggleModelo(id) {
        fetch('/admin/equipos/modelos/' + id + '/toggle', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                mostrarToast(response.message || 'Estado actualizado', 'success');
                cargarModelos();
            }
        });
    }

    // ============================================================
    // SELECTS AUXILIARES (Promise para poder encadenar)
    // ============================================================
    function cargarMarcasEnSelect(selectId, valorSeleccionado) {
        return new Promise((resolve) => {
            const select = document.getElementById(selectId);
            if (!select) return resolve();

            fetch('/admin/equipos/marcas-list', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(response => {
                if (response.success) {
                    select.innerHTML = '<option value="">Seleccionar marca...</option>';
                    response.data.forEach(marca => {
                        select.innerHTML += `<option value="${marca.id}">${escapeHtml(marca.nombre)}</option>`;
                    });
                    if (valorSeleccionado) select.value = valorSeleccionado;
                }
                resolve();
            })
            .catch(() => resolve());
        });
    }

    function cargarCategoriasEnSelect(selectId, valorSeleccionado) {
        return new Promise((resolve) => {
            const select = document.getElementById(selectId);
            if (!select) return resolve();

            fetch('/admin/equipos/categorias-list', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(response => {
                if (response.success) {
                    select.innerHTML = '<option value="">Seleccionar categoría...</option>';
                    response.data.forEach(cat => {
                        select.innerHTML += `<option value="${cat.id}">${escapeHtml(cat.nombre)}</option>`;
                    });
                    if (valorSeleccionado) select.value = valorSeleccionado;
                }
                resolve();
            })
            .catch(() => resolve());
        });
    }

    // ============================================================
    // MODALES - ABRIR
    // ============================================================
    function abrirModalMarca() {
        const modal = new bootstrap.Modal(document.getElementById('modalMarca'));
        const form = document.getElementById('formMarca');
        form.reset();
        document.getElementById('modalMarcaLabel').textContent = 'Nueva Marca';
        document.getElementById('formMethodMarca').value = 'POST';
        document.getElementById('marcaId').value = '';
        modal.show();
    }

    function abrirModalCategoria() {
        const modal = new bootstrap.Modal(document.getElementById('modalCategoria'));
        const form = document.getElementById('formCategoria');
        form.reset();
        document.getElementById('modalCategoriaLabel').textContent = 'Nueva Categoría';
        document.getElementById('formMethodCategoria').value = 'POST';
        document.getElementById('categoriaId').value = '';
        modal.show();
    }

    function abrirModalModelo() {
        const modal = new bootstrap.Modal(document.getElementById('modalModelo'));
        const form = document.getElementById('formModelo');
        form.reset();
        document.getElementById('modalModeloLabel').textContent = 'Nuevo Modelo';
        document.getElementById('formMethodModelo').value = 'POST';
        document.getElementById('modeloId').value = '';

        Promise.all([
            cargarMarcasEnSelect('modelo_marca_id'),
            cargarCategoriasEnSelect('modelo_categoria_id')
        ]).then(() => modal.show());
    }

    // ============================================================
    // ELIMINAR
    // ============================================================
    function confirmarEliminar(tipo, id, nombre, tieneDependencias) {
        if (tipo !== 'modelo' && tieneDependencias > 0) {
            mostrarToast('No se puede eliminar porque tiene elementos asociados', 'error');
            return;
        }
        elementoAEliminar = { tipo, id };
        document.getElementById('deleteNombre').textContent = nombre;
        new bootstrap.Modal(document.getElementById('modalEliminar')).show();
    }

    function confirmarEliminacion() {
        if (!elementoAEliminar) return;
        const { tipo, id } = elementoAEliminar;
        let url = '';
        if (tipo === 'marca') url = '/admin/equipos/marcas/' + id;
        else if (tipo === 'categoria') url = '/admin/equipos/categorias/' + id;
        else if (tipo === 'modelo') url = '/admin/equipos/modelos/' + id;

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(response => {
            bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
            if (response.success) {
                mostrarToast(response.message, 'success');
                if (tipo === 'marca') cargarMarcas();
                else if (tipo === 'categoria') cargarCategorias();
                else if (tipo === 'modelo') cargarModelos();
            } else {
                mostrarToast(response.message, 'error');
            }
            elementoAEliminar = null;
        });
    }

    // ============================================================
    // FORMULARIOS - GUARDAR
    // ============================================================
    function guardarMarca(e) {
        e.preventDefault();
        const id = document.getElementById('marcaId').value;
        const method = document.getElementById('formMethodMarca').value;
        const url = method === 'PUT' ? '/admin/equipos/marcas/' + id : '/admin/equipos/marcas';
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
                mostrarToast(response.message || 'Marca guardada', 'success');
                cargarMarcas();
            } else {
                mostrarToast(response.message || 'Error al guardar', 'error');
            }
        });
    }

    function guardarCategoria(e) {
        e.preventDefault();
        const id = document.getElementById('categoriaId').value;
        const method = document.getElementById('formMethodCategoria').value;
        const url = method === 'PUT' ? '/admin/equipos/categorias/' + id : '/admin/equipos/categorias';
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
                mostrarToast(response.message || 'Categoría guardada', 'success');
                cargarCategorias();
            } else {
                mostrarToast(response.message || 'Error al guardar', 'error');
            }
        });
    }

    function guardarModelo(e) {
        e.preventDefault();
        const id = document.getElementById('modeloId').value;
        const method = document.getElementById('formMethodModelo').value;
        const url = method === 'PUT' ? '/admin/equipos/modelos/' + id : '/admin/equipos/modelos';
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
                mostrarToast(response.message || 'Modelo guardado', 'success');
                cargarModelos();
            } else {
                mostrarToast(response.message || 'Error al guardar', 'error');
            }
        });
    }

    // ============================================================
    // WIZARD "EQUIPO COMPLETO"
    // ============================================================
    function abrirWizardEquipo() {
        wizardData = {
            paso: 1,
            marcaId: null,
            marcaNombre: null,
            categoriaId: null,
            categoriaNombre: null,
            marcaEsNueva: false,
            categoriaEsNueva: false
        };

        document.getElementById('wizardStep1').style.display = 'block';
        document.getElementById('wizardStep2').style.display = 'none';
        document.getElementById('wizardStep3').style.display = 'none';

        document.getElementById('wizardMarcaNombre').value = '';
        document.getElementById('wizardMarcaDescripcion').value = '';
        document.getElementById('wizardCategoriaNombre').value = '';
        document.getElementById('wizardCategoriaDescripcion').value = '';
        document.getElementById('wizardModeloNombre').value = '';
        document.getElementById('wizardModeloDescripcion').value = '';

        document.querySelector('input[name="wizard_marca_tipo"][value="existente"]').checked = true;
        document.querySelector('input[name="wizard_categoria_tipo"][value="existente"]').checked = true;

        cambiarOpcionWizard('marca', 'existente');
        cambiarOpcionWizard('categoria', 'existente');

        actualizarIndicadoresWizard();
        cargarMarcasWizard();

        new bootstrap.Modal(document.getElementById('modalWizardEquipo')).show();
    }

    function irPasoWizard(paso) {
        if (paso < 1 || paso > 3) return;

        if (paso > wizardData.paso) {
            if (wizardData.paso === 1 && !validarPasoMarca()) return;
            if (wizardData.paso === 2 && !validarPasoCategoria()) return;
        }

        document.getElementById('wizardStep' + wizardData.paso).style.display = 'none';
        document.getElementById('wizardStep' + paso).style.display = 'block';

        wizardData.paso = paso;
        actualizarIndicadoresWizard();

        if (paso === 2) {
            cargarCategoriasWizard();
            if (wizardData.marcaNombre) {
                document.getElementById('wizardMarcaSeleccionadaLabel').textContent = wizardData.marcaNombre;
            }
        }
        if (paso === 3) actualizarDatosFinalesWizard();
    }

    function actualizarIndicadoresWizard() {
        for (let i = 1; i <= 3; i++) {
            const circle = document.querySelector(`#wizardLabel${i} .step-circle`);
            const label = document.getElementById(`wizardLabel${i}`);
            if (!circle || !label) continue;

            circle.classList.remove('active', 'completed');
            if (i < wizardData.paso) {
                circle.classList.add('completed');
                circle.textContent = '✓';
                label.style.color = '#1e7e34';
            } else if (i === wizardData.paso) {
                circle.classList.add('active');
                circle.textContent = i;
                label.style.color = '#1e3c72';
            } else {
                circle.textContent = i;
                label.style.color = '#adb5bd';
            }
        }

        const progress = ((wizardData.paso - 1) / 2) * 100;
        document.getElementById('wizardProgressBar').style.width = progress + '%';
        document.getElementById('wizardStepIndicator').textContent = 'Paso ' + wizardData.paso + ' de 3';
    }

    function validarPasoMarca() {
        const tipo = document.querySelector('input[name="wizard_marca_tipo"]:checked').value;
        if (tipo === 'existente') {
            const select = document.getElementById('wizardMarcaSelect');
            if (!select.value) {
                mostrarToast('Debe seleccionar una marca', 'warning');
                return false;
            }
            wizardData.marcaId = parseInt(select.value);
            wizardData.marcaNombre = select.options[select.selectedIndex].text;
            wizardData.marcaEsNueva = false;
            return true;
        } else {
            const nombre = document.getElementById('wizardMarcaNombre').value.trim();
            if (!nombre) {
                mostrarToast('Ingrese el nombre de la nueva marca', 'warning');
                return false;
            }
            wizardData.marcaNombre = nombre;
            wizardData.marcaEsNueva = true;
            wizardData.marcaId = null;
            return true;
        }
    }

    function validarPasoCategoria() {
        const tipo = document.querySelector('input[name="wizard_categoria_tipo"]:checked').value;
        if (tipo === 'existente') {
            const select = document.getElementById('wizardCategoriaSelect');
            if (!select.value) {
                mostrarToast('Debe seleccionar una categoría', 'warning');
                return false;
            }
            wizardData.categoriaId = parseInt(select.value);
            wizardData.categoriaNombre = select.options[select.selectedIndex].text;
            wizardData.categoriaEsNueva = false;
            return true;
        } else {
            const nombre = document.getElementById('wizardCategoriaNombre').value.trim();
            if (!nombre) {
                mostrarToast('Ingrese el nombre de la nueva categoría', 'warning');
                return false;
            }
            wizardData.categoriaNombre = nombre;
            wizardData.categoriaEsNueva = true;
            wizardData.categoriaId = null;
            return true;
        }
    }

    function cambiarOpcionWizard(tipo, opcion) {
        if (tipo === 'marca') {
            const cardExistente = document.getElementById('cardMarcaExistente');
            const cardNueva = document.getElementById('cardMarcaNueva');
            const containerSelect = document.getElementById('wizardMarcaSelectContainer');
            const containerNueva = document.getElementById('wizardMarcaNuevaContainer');

            if (opcion === 'existente') {
                cardExistente.classList.add('active');
                cardNueva.classList.remove('active');
                cardExistente.style.borderColor = '#1e3c72';
                cardNueva.style.borderColor = '#e9ecef';
                containerSelect.style.display = 'block';
                containerNueva.style.display = 'none';
                document.querySelector('input[name="wizard_marca_tipo"][value="existente"]').checked = true;
            } else {
                cardNueva.classList.add('active');
                cardExistente.classList.remove('active');
                cardNueva.style.borderColor = '#1e3c72';
                cardExistente.style.borderColor = '#e9ecef';
                containerSelect.style.display = 'none';
                containerNueva.style.display = 'block';
                document.querySelector('input[name="wizard_marca_tipo"][value="nueva"]').checked = true;
                document.getElementById('wizardMarcaNombre').focus();
            }
        } else if (tipo === 'categoria') {
            const cardExistente = document.getElementById('cardCategoriaExistente');
            const cardNueva = document.getElementById('cardCategoriaNueva');
            const containerSelect = document.getElementById('wizardCategoriaSelectContainer');
            const containerNueva = document.getElementById('wizardCategoriaNuevaContainer');

            if (opcion === 'existente') {
                cardExistente.classList.add('active');
                cardNueva.classList.remove('active');
                cardExistente.style.borderColor = '#1e3c72';
                cardNueva.style.borderColor = '#e9ecef';
                containerSelect.style.display = 'block';
                containerNueva.style.display = 'none';
                document.querySelector('input[name="wizard_categoria_tipo"][value="existente"]').checked = true;
            } else {
                cardNueva.classList.add('active');
                cardExistente.classList.remove('active');
                cardNueva.style.borderColor = '#1e3c72';
                cardExistente.style.borderColor = '#e9ecef';
                containerSelect.style.display = 'none';
                containerNueva.style.display = 'block';
                document.querySelector('input[name="wizard_categoria_tipo"][value="nueva"]').checked = true;
                document.getElementById('wizardCategoriaNombre').focus();
            }
        }
    }

    function cargarMarcasWizard() {
        fetch('/admin/equipos/marcas-list', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            const select = document.getElementById('wizardMarcaSelect');
            if (!select) return;
            if (response.success) {
                select.innerHTML = '<option value="">Seleccionar marca...</option>';
                response.data.forEach(marca => {
                    select.innerHTML += `<option value="${marca.id}">${escapeHtml(marca.nombre)}</option>`;
                });
            }
        });
    }

    function cargarCategoriasWizard() {
        fetch('/admin/equipos/categorias-list', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(response => {
            const select = document.getElementById('wizardCategoriaSelect');
            if (!select) return;
            if (response.success) {
                select.innerHTML = '<option value="">Seleccionar categoría...</option>';
                response.data.forEach(cat => {
                    select.innerHTML += `<option value="${cat.id}">${escapeHtml(cat.nombre)}</option>`;
                });
            }
        });
    }

    function actualizarDatosFinalesWizard() {
        document.getElementById('wizardModeloMarca').value = wizardData.marcaNombre || '(Ninguna)';
        document.getElementById('wizardModeloCategoria').value = wizardData.categoriaNombre || '(Ninguna)';
        document.getElementById('wizardMarcaFinalLabel').textContent = wizardData.marcaNombre || '(Ninguna)';
        document.getElementById('wizardCategoriaFinalLabel').textContent = wizardData.categoriaNombre || '(Ninguna)';
    }

    // ---- Acciones del wizard ----

    async function wizardCrearMarcaSiNecesario() {
        if (!wizardData.marcaEsNueva) return wizardData.marcaId;

        const nombre = document.getElementById('wizardMarcaNombre').value.trim();
        const descripcion = document.getElementById('wizardMarcaDescripcion').value.trim();

        const response = await fetch('/admin/equipos/marcas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ nombre, descripcion })
        });
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Error al crear la marca');
        }

        wizardData.marcaId = data.data.id;
        wizardData.marcaNombre = nombre;
        return wizardData.marcaId;
    }

    async function wizardCrearCategoriaSiNecesario() {
        // ✅ Ya NO recibe marcaId: la categoría es global
        if (!wizardData.categoriaEsNueva) return wizardData.categoriaId;

        const nombre = document.getElementById('wizardCategoriaNombre').value.trim();
        const descripcion = document.getElementById('wizardCategoriaDescripcion').value.trim();

        const response = await fetch('/admin/equipos/categorias', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ nombre, descripcion })
        });
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Error al crear la categoría');
        }

        wizardData.categoriaId = data.data.id;
        wizardData.categoriaNombre = nombre;
        return wizardData.categoriaId;
    }

    async function soloCrearMarcaWizard() {
        if (!validarPasoMarca()) return;

        try {
            await wizardCrearMarcaSiNecesario();
            mostrarToast('Marca creada exitosamente', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalWizardEquipo')).hide();
            cargarMarcas();
        } catch (error) {
            mostrarToast(error.message, 'error');
        }
    }

    async function soloCrearCategoriaWizard() {
        if (!validarPasoMarca()) return;
        if (!validarPasoCategoria()) return;

        try {
            await wizardCrearMarcaSiNecesario();
            await wizardCrearCategoriaSiNecesario();
            mostrarToast('Categoría creada exitosamente', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalWizardEquipo')).hide();
            cargarMarcas();
            cargarCategorias();
        } catch (error) {
            mostrarToast(error.message, 'error');
        }
    }

    async function guardarEquipoCompleto() {
        const btn = document.getElementById('wizardBtnGuardarModelo');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';
        btn.disabled = true;

        try {
            // Validar
            if (!validarPasoMarca()) throw new Error('Datos de marca incompletos');
            if (!validarPasoCategoria()) throw new Error('Datos de categoría incompletos');

            const modeloNombre = document.getElementById('wizardModeloNombre').value.trim();
            if (!modeloNombre) throw new Error('Ingrese el nombre del modelo');

            const modeloDescripcion = document.getElementById('wizardModeloDescripcion').value.trim();

            // Crear marca si es nueva
            const marcaId = await wizardCrearMarcaSiNecesario();

            // Crear categoría si es nueva (ya NO recibe marcaId)
            const categoriaId = await wizardCrearCategoriaSiNecesario();

            // ✅ Crear modelo con AMBOS: marca_id + categoria_id
            const response = await fetch('/admin/equipos/modelos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    marca_id: marcaId,
                    categoria_id: categoriaId,
                    nombre: modeloNombre,
                    descripcion: modeloDescripcion
                })
            });
            const data = await response.json();

            if (!data.success) throw new Error(data.message || 'Error al crear el modelo');

            mostrarToast('✅ Equipo registrado exitosamente', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalWizardEquipo')).hide();

            cargarMarcas();
            cargarCategorias();
            cargarModelos();

        } catch (error) {
            console.error('Error en wizard:', error);
            mostrarToast(error.message || 'Error al guardar', 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    // ============================================================
    // INICIALIZACIÓN
    // ============================================================
    function inicializar() {
        console.log('✅ Módulo de equipos inicializado');

        // Cargar datos iniciales
        cargarMarcas();
        cargarCategorias();
        cargarModelos();

        // ============ BÚSQUEDAS EN TIEMPO REAL ============
        const buscarMarcasInput = document.getElementById('buscarMarcas');
        if (buscarMarcasInput) {
            buscarMarcasInput.addEventListener('input', debounce(cargarMarcas, 300));
        }

        const buscarCategoriasInput = document.getElementById('buscarCategorias');
        if (buscarCategoriasInput) {
            buscarCategoriasInput.addEventListener('input', debounce(cargarCategorias, 300));
        }

        const buscarModelosInput = document.getElementById('buscarModelos');
        if (buscarModelosInput) {
            buscarModelosInput.addEventListener('input', debounce(cargarModelos, 300));
        }

        // ============ FORMULARIOS ============
        document.getElementById('formMarca')?.addEventListener('submit', guardarMarca);
        document.getElementById('formCategoria')?.addEventListener('submit', guardarCategoria);
        document.getElementById('formModelo')?.addEventListener('submit', guardarModelo);

        // ============ CONFIRMAR ELIMINACIÓN ============
        document.getElementById('btnConfirmarEliminar')?.addEventListener('click', confirmarEliminacion);

        // ============ DROPDOWN "NUEVO" ============
        document.querySelectorAll('.dropdown-nuevo .dropdown-item[data-action]').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.getAttribute('data-action');
                const dropdown = bootstrap.Dropdown.getInstance(this.closest('.dropdown').querySelector('[data-bs-toggle="dropdown"]'));
                if (dropdown) dropdown.hide();

                switch (action) {
                    case 'wizard': abrirWizardEquipo(); break;
                    case 'marca': abrirModalMarca(); break;
                    case 'categoria': abrirModalCategoria(); break;
                    case 'modelo': abrirModalModelo(); break;
                }
            });
        });

        // ============ WIZARD - EVENTOS ============
        document.getElementById('cardMarcaExistente')?.addEventListener('click', () => cambiarOpcionWizard('marca', 'existente'));
        document.getElementById('cardMarcaNueva')?.addEventListener('click', () => cambiarOpcionWizard('marca', 'nueva'));
        document.getElementById('cardCategoriaExistente')?.addEventListener('click', () => cambiarOpcionWizard('categoria', 'existente'));
        document.getElementById('cardCategoriaNueva')?.addEventListener('click', () => cambiarOpcionWizard('categoria', 'nueva'));

        document.getElementById('wizardBtnIrPaso2')?.addEventListener('click', () => irPasoWizard(2));
        document.getElementById('wizardBtnIrPaso3')?.addEventListener('click', () => irPasoWizard(3));
        document.getElementById('wizardBtnAtras1')?.addEventListener('click', () => irPasoWizard(1));
        document.getElementById('wizardBtnAtras2')?.addEventListener('click', () => irPasoWizard(2));
        document.getElementById('wizardBtnSoloMarca')?.addEventListener('click', soloCrearMarcaWizard);
        document.getElementById('wizardBtnSoloCategoria')?.addEventListener('click', soloCrearCategoriaWizard);
        document.getElementById('wizardBtnGuardarModelo')?.addEventListener('click', guardarEquipoCompleto);

        // ============ RECARGAR AL CAMBIAR DE TAB ============
        document.querySelectorAll('#equipoTab .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                if (target === '#marcas') cargarMarcas();
                else if (target === '#categorias') cargarCategorias();
                else if (target === '#modelos') cargarModelos();
            });
        });
    }

    // ============================================================
    // EXPONER FUNCIONES GLOBALES (para onclick en HTML generado)
    // ============================================================
    window.verMarca = verMarca;
    window.editarMarca = editarMarca;
    window.toggleMarca = toggleMarca;
    window.verCategoria = verCategoria;
    window.editarCategoria = editarCategoria;
    window.toggleCategoria = toggleCategoria;
    window.verModelo = verModelo;
    window.editarModelo = editarModelo;
    window.toggleModelo = toggleModelo;
    window.confirmarEliminar = confirmarEliminar;

    window.abrirModalMarca = abrirModalMarca;
    window.abrirModalCategoria = abrirModalCategoria;
    window.abrirModalModelo = abrirModalModelo;
    window.abrirWizardEquipo = abrirWizardEquipo;
    window.cambiarOpcionWizard = cambiarOpcionWizard;

    // ============================================================
    // DOM READY
    // ============================================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar);
    } else {
        inicializar();
    }

})();