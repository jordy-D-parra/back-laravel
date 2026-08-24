// ============================================================
// ADMIN ROLES - Gestión de Roles y Permisos
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Módulo de roles inicializado');

    // ===========================
    // BÚSQUEDA EN TIEMPO REAL
    // ===========================
    const buscarInput = document.getElementById('buscarRol');
    let timeoutBusqueda = null;

    if (buscarInput) {
        buscarInput.addEventListener('input', function() {
            const termino = this.value.trim().toLowerCase();

            clearTimeout(timeoutBusqueda);
            timeoutBusqueda = setTimeout(function() {
                filtrarRoles(termino);
            }, 300);
        });
    }

    function filtrarRoles(termino) {
        const rows = document.querySelectorAll('#tablaRoles tr');
        
        rows.forEach(row => {
            // Saltar fila de "No hay roles" o mensajes
            if (row.querySelector('.text-muted') || row.querySelector('.text-center')) {
                return;
            }

            const nombreRol = row.querySelector('td:first-child')?.textContent?.toLowerCase() || '';
            const descripcion = row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '';

            if (termino === '' || nombreRol.includes(termino) || descripcion.includes(termino)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // ===========================
    // FUNCIONES GLOBALES
    // ===========================
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let rolesData = [];
    let elementoAEliminar = null;

    // Iconos SVG
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
    // CARGAR ROLES
    // ===========================
    function cargarRoles() {
        const tbody = document.getElementById('tablaRoles');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Cargando roles...</td></tr>';
        }

        fetch('/admin/roles/list', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                rolesData = data.data;
                renderizarTablaRoles();
                actualizarEstadisticas();
            } else {
                mostrarToast(data.message || 'Error al cargar roles', 'error');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error al cargar roles</td></tr>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarToast('Error de conexión: ' + error.message, 'error');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error de conexión al servidor</td></tr>';
            }
        });
    }

    function renderizarTablaRoles() {
        const tbody = document.getElementById('tablaRoles');
        if (!tbody) return;

        if (rolesData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No hay roles registrados</td></tr>';
            return;
        }

        let html = '';
        for (let i = 0; i < rolesData.length; i++) {
            const rol = rolesData[i];
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

        // Aplicar filtro si hay búsqueda activa
        const buscarInput = document.getElementById('buscarRol');
        if (buscarInput && buscarInput.value.trim()) {
            filtrarRoles(buscarInput.value.trim().toLowerCase());
        }
    }

    function actualizarEstadisticas() {
        const total = rolesData.length;
        const totalPermisos = rolesData.reduce((sum, rol) => sum + (rol.permisos_count || 0), 0);
        const totalUsuarios = rolesData.reduce((sum, rol) => sum + (rol.usuarios_count || 0), 0);

        document.getElementById('statsTotal').textContent = total;
        document.getElementById('statsPermisos').textContent = totalPermisos;
        document.getElementById('statsUsuarios').textContent = totalUsuarios;
    }

    // ===========================
    // CARGAR PERMISOS EN MODAL
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
                cargarRoles();
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
                cargarRoles();
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
    cargarRoles();

    // Exponer funciones globales
    window.abrirModalRol = window.abrirModalRol;
    window.editarRol = window.editarRol;
    window.verRol = window.verRol;
    window.confirmarEliminarRol = window.confirmarEliminarRol;
});