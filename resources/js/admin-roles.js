// ============================================================
// ADMIN ROLES - Gestión de Roles con Paginación
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Módulo de roles inicializado');

    // ===========================
    // ESTADO DE PAGINACIÓN
    // ===========================
    const ITEMS_POR_PAGINA = 8;
    let rolesData = [];
    let rolesFiltrados = [];
    let paginaActual = 1;
    let elementoAEliminar = null;

    // ===========================
    // HELPERS
    // ===========================
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const iconos = {
        ver: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
        editar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
        eliminar: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>'
    };

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function mostrarToast(mensaje, tipo = 'success') {
        const colores = { success: '#1e7e34', error: '#c5221f', warning: '#f6c23e', info: '#1e3c72' };
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            background: ${colores[tipo] || colores.success}; color: white;
            padding: 14px 20px; border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            font-weight: 500; font-size: 0.9rem;
            animation: slideInRight 0.3s ease-out; max-width: 400px;
            cursor: pointer;
        `;
        toast.textContent = mensaje;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // ===========================
    // HELPERS DE PAGINACIÓN
    // ===========================
    function renderPaginacion(totalPaginas, pageActual, totalRegistros) {
        if (totalPaginas <= 1) return '';

        let html = `<div class="pagination-bar">
            <div class="pagination-info">Mostrando ${((pageActual - 1) * ITEMS_POR_PAGINA) + 1} a ${Math.min(pageActual * ITEMS_POR_PAGINA, totalRegistros)} de ${totalRegistros} registros</div>
            <div class="pagination-btns">`;

        html += `<button class="pagination-btn ${pageActual === 1 ? 'disabled' : ''}"
                    onclick="window.cambiarPaginaRoles(${pageActual - 1})" ${pageActual === 1 ? 'disabled' : ''}>«</button>`;

        let inicio = Math.max(1, pageActual - 2);
        let fin = Math.min(totalPaginas, pageActual + 2);

        if (inicio > 1) {
            html += `<button class="pagination-btn" onclick="window.cambiarPaginaRoles(1)">1</button>`;
            if (inicio > 2) html += `<span class="pagination-ellipsis">...</span>`;
        }

        for (let i = inicio; i <= fin; i++) {
            html += `<button class="pagination-btn ${i === pageActual ? 'active' : ''}"
                        onclick="window.cambiarPaginaRoles(${i})">${i}</button>`;
        }

        if (fin < totalPaginas) {
            if (fin < totalPaginas - 1) html += `<span class="pagination-ellipsis">...</span>`;
            html += `<button class="pagination-btn" onclick="window.cambiarPaginaRoles(${totalPaginas})">${totalPaginas}</button>`;
        }

        html += `<button class="pagination-btn ${pageActual === totalPaginas ? 'disabled' : ''}"
                    onclick="window.cambiarPaginaRoles(${pageActual + 1})" ${pageActual === totalPaginas ? 'disabled' : ''}>»</button>`;

        html += `</div></div>`;
        return html;
    }

    window.cambiarPaginaRoles = function(nuevaPagina) {
        const totalPaginas = Math.ceil(rolesFiltrados.length / ITEMS_POR_PAGINA);
        if (nuevaPagina < 1 || nuevaPagina > totalPaginas) return;

        paginaActual = nuevaPagina;
        renderizarTablaRoles();
        document.querySelector('.table-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // ===========================
    // CARGAR ROLES (desde Blade)
    // ===========================
    function cargarDatosDesdeBlade() {
        if (window.rolesIniciales && Array.isArray(window.rolesIniciales)) {
            rolesData = window.rolesIniciales;
            rolesFiltrados = [...rolesData];
            paginaActual = 1;
            renderizarTablaRoles();
            actualizarEstadisticas();
        }
    }

    // ===========================
    // RENDERIZAR TABLA
    // ===========================
    function renderizarTablaRoles() {
        const tbody = document.getElementById('tablaRoles');
        if (!tbody) return;

        const total = rolesFiltrados.length;
        const totalPaginas = Math.ceil(total / ITEMS_POR_PAGINA);

        if (paginaActual > totalPaginas && totalPaginas > 0) {
            paginaActual = totalPaginas;
        }

        const inicio = (paginaActual - 1) * ITEMS_POR_PAGINA;
        const paginaData = rolesFiltrados.slice(inicio, inicio + ITEMS_POR_PAGINA);

        if (total === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No hay roles registrados</td></tr>';
            const pag = document.getElementById('paginacionRoles');
            if (pag) pag.innerHTML = '';
            return;
        }

        let html = '';
        for (let i = 0; i < paginaData.length; i++) {
            const rol = paginaData[i];
            const permisosCount = rol.permisos_count || 0;
            const usuariosCount = rol.usuarios_count || 0;

            html += `
                <tr>
                    <td><span class="fw-medium" style="color: var(--primary-dark);">${escapeHtml(rol.nombre)}</span></td>
                    <td>${escapeHtml(rol.descripcion || '—')}</td>
                    <td><span class="badge-role-count">${usuariosCount}</span></td>
                    <td><span class="badge-permisos">${permisosCount} permisos</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-action btn-outline-primary-dark" onclick="verRol(${rol.id})" title="Ver permisos">${iconos.ver}</button>
                        <button class="btn btn-sm btn-action btn-outline-primary-dark" onclick="editarRol(${rol.id})" title="Editar">${iconos.editar}</button>
                        ${rol.nombre !== 'admin' ? `<button class="btn btn-sm btn-action btn-outline-danger" onclick="confirmarEliminarRol(${rol.id}, '${escapeHtml(rol.nombre).replace(/'/g, "\\'")}')" title="Eliminar">${iconos.eliminar}</button>` : ''}
                    </td>
                </tr>
            `;
        }
        tbody.innerHTML = html;

        // Renderizar paginación
        const paginacionContainer = document.getElementById('paginacionRoles');
        if (paginacionContainer) {
            paginacionContainer.innerHTML = renderPaginacion(totalPaginas, paginaActual, total);
        }
    }

    // ===========================
    // ACTUALIZAR ESTADÍSTICAS
    // ===========================
    function actualizarEstadisticas() {
        const total = rolesData.length;
        const totalPermisos = rolesData.reduce((sum, rol) => sum + (rol.permisos_count || 0), 0);
        const totalUsuarios = rolesData.reduce((sum, rol) => sum + (rol.usuarios_count || 0), 0);

        const elTotal = document.getElementById('statsTotal');
        const elPermisos = document.getElementById('statsPermisos');
        const elUsuarios = document.getElementById('statsUsuarios');

        if (elTotal) elTotal.textContent = total;
        if (elPermisos) elPermisos.textContent = totalPermisos;
        if (elUsuarios) elUsuarios.textContent = totalUsuarios;
    }

    // ===========================
    // BÚSQUEDA EN TIEMPO REAL (CLIENTE)
    // ===========================
    const buscarInput = document.getElementById('buscarRol');
    let timeoutBusqueda = null;

    if (buscarInput) {
        buscarInput.addEventListener('input', function() {
            const termino = this.value.trim().toLowerCase();
            clearTimeout(timeoutBusqueda);
            timeoutBusqueda = setTimeout(function() {
                if (termino === '') {
                    rolesFiltrados = [...rolesData];
                } else {
                    rolesFiltrados = rolesData.filter(rol => {
                        return (rol.nombre || '').toLowerCase().includes(termino) ||
                               (rol.descripcion || '').toLowerCase().includes(termino);
                    });
                }
                paginaActual = 1;
                renderizarTablaRoles();
            }, 300);
        });
    }

    // ===========================
    // CARGAR PERMISOS EN MODAL (crear/editar)
    // ===========================
    function cargarPermisosEnModal(permisosSeleccionados = []) {
        const container = document.getElementById('permisosContainer');
        if (!container) return;

        container.innerHTML = '<div class="text-center py-4 text-muted">Cargando permisos...</div>';

        fetch('/admin/permisos/todos', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                const agrupados = response.data;
                let html = '';

                for (const categoria in agrupados) {
                    if (agrupados.hasOwnProperty(categoria)) {
                        const permisos = agrupados[categoria];
                        html += `
                            <div class="permiso-categoria">
                                <h6>${categoria.toUpperCase()} <span class="badge-count">${permisos.length}</span></h6>
                                <div class="permisos-grid">
                        `;

                        for (let i = 0; i < permisos.length; i++) {
                            const permiso = permisos[i];
                            const checked = permisosSeleccionados.includes(permiso.id) ? 'checked' : '';
                            html += `
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permisos[]" value="${permiso.id}" id="perm_${permiso.id}" ${checked}>
                                    <label class="form-check-label small" for="perm_${permiso.id}">
                                        ${escapeHtml(permiso.nombre)}
                                        <br><small class="text-muted">${escapeHtml(permiso.descripcion || '')}</small>
                                    </label>
                                </div>
                            `;
                        }
                        html += `</div></div>`;
                    }
                }
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center py-4 text-danger">Error cargando permisos</div>';
            }
        })
        .catch(error => {
            console.error('Error cargando permisos:', error);
            container.innerHTML = '<div class="text-center py-4 text-danger">Error de conexión</div>';
        });
    }

    // ===========================
    // CRUD ROLES
    // ===========================
    window.abrirModalRol = function() {
        document.getElementById('modalRolLabel').textContent = 'Nuevo Rol';
        document.getElementById('formMethodRol').value = 'POST';
        document.getElementById('rolId').value = '';
        document.getElementById('rol_nombre').value = '';
        document.getElementById('rol_descripcion').value = '';

        cargarPermisosEnModal([]);

        const modal = new bootstrap.Modal(document.getElementById('modalRol'));
        modal.show();
    };

    window.editarRol = function(id) {
        fetch(`/admin/roles/${id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                const data = response.data;
                document.getElementById('modalRolLabel').textContent = 'Editar Rol';
                document.getElementById('formMethodRol').value = 'PUT';
                document.getElementById('rolId').value = data.id;
                document.getElementById('rol_nombre').value = data.nombre;
                document.getElementById('rol_descripcion').value = data.descripcion || '';

                cargarPermisosEnModal(data.permisos || []);

                const modal = new bootstrap.Modal(document.getElementById('modalRol'));
                modal.show();
            } else {
                mostrarToast(response.message || 'Error al cargar rol', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarToast('Error de conexión', 'error');
        });
    };

    window.verRol = function(id) {
        Promise.all([
            fetch(`/admin/roles/${id}`, { headers: { 'Accept': 'application/json' } }).then(r => r.json()),
            fetch('/admin/permisos/todos', { headers: { 'Accept': 'application/json' } }).then(r => r.json())
        ])
        .then(([rolResponse, permisosResponse]) => {
            if (rolResponse.success && permisosResponse.success) {
                const data = rolResponse.data;
                const todosPermisos = permisosResponse.data;
                let permisosHtml = '<div class="row">';

                for (const categoria in todosPermisos) {
                    if (todosPermisos.hasOwnProperty(categoria)) {
                        const permisosLista = todosPermisos[categoria];
                        const permisosDelRol = permisosLista.filter(p => data.permisos.includes(p.id));
                        if (permisosDelRol.length > 0) {
                            permisosHtml += `
                                <div class="col-md-6 mb-3">
                                    <h6 class="fw-bold" style="color: var(--primary-dark);">${categoria.toUpperCase()}</h6>
                                    <ul class="list-unstyled">
                            `;
                            for (let i = 0; i < permisosDelRol.length; i++) {
                                permisosHtml += `<li><small>✓ ${escapeHtml(permisosDelRol[i].nombre)}</small></li>`;
                            }
                            permisosHtml += `</ul></div>`;
                        }
                    }
                }
                permisosHtml += '</div>';

                const modalHtml = `
                    <div class="detail-header">
                        <h5>${escapeHtml(data.nombre)}</h5>
                        <span class="badge-role-count">${data.usuarios_count || 0} usuarios</span>
                    </div>
                    <div class="mb-3">
                        <strong>Descripción:</strong>
                        <p>${escapeHtml(data.descripcion || 'Sin descripción')}</p>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-3" style="color: var(--primary-dark);">Permisos Asignados (${data.permisos.length})</h6>
                    ${permisosHtml}
                `;

                document.getElementById('modalDetalleLabel').textContent = 'Detalle del Rol';
                document.getElementById('detalleContenido').innerHTML = modalHtml;
                const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
                modal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarToast('Error al cargar detalles', 'error');
        });
    };

    function guardarRol(event) {
        event.preventDefault();

        const id = document.getElementById('rolId').value;
        const method = document.getElementById('formMethodRol').value;
        const url = method === 'PUT' ? `/admin/roles/${id}` : '/admin/roles';

        const formData = new FormData(document.getElementById('formRol'));
        if (method === 'PUT') formData.append('_method', 'PUT');

        const submitBtn = document.getElementById('formRol').querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';
            submitBtn.disabled = true;
        }

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalRol'));
                modal.hide();
                mostrarToast(response.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                mostrarToast(response.message || 'Error al guardar', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarToast('Error de conexión', 'error');
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.innerHTML = 'Guardar Rol y Permisos';
                submitBtn.disabled = false;
            }
        });
    }

    window.confirmarEliminarRol = function(id, nombre) {
        const rol = rolesData.find(r => r.id === id);
        elementoAEliminar = id;
        document.getElementById('deleteRolNombre').textContent = nombre;
        const btnConfirmar = document.getElementById('btnConfirmarEliminar');

        if (rol && rol.usuarios_count > 0) {
            document.getElementById('deleteWarning').textContent = `⚠️ Este rol tiene ${rol.usuarios_count} usuarios asignados. Debe reasignarlos antes de eliminar.`;
            if (btnConfirmar) btnConfirmar.disabled = true;
        } else {
            document.getElementById('deleteWarning').textContent = '';
            if (btnConfirmar) btnConfirmar.disabled = false;
        }

        const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
        modal.show();
    };

    function eliminarRol() {
        if (!elementoAEliminar) return;

        const btnConfirmar = document.getElementById('btnConfirmarEliminar');
        if (btnConfirmar) {
            btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Eliminando...';
            btnConfirmar.disabled = true;
        }

        fetch(`/admin/roles/${elementoAEliminar}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(response => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEliminar'));
            modal.hide();
            if (response.success) {
                mostrarToast(response.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                mostrarToast(response.message, 'error');
            }
            elementoAEliminar = null;
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarToast('Error de conexión', 'error');
        })
        .finally(() => {
            if (btnConfirmar) {
                btnConfirmar.innerHTML = 'Eliminar';
                btnConfirmar.disabled = false;
            }
        });
    }

    // ===========================
    // EVENT LISTENERS
    // ===========================
    document.getElementById('formRol')?.addEventListener('submit', guardarRol);
    document.getElementById('btnConfirmarEliminar')?.addEventListener('click', eliminarRol);

    // ===========================
    // INICIALIZAR
    // ===========================
    cargarDatosDesdeBlade();
});