// resources/js/admin-roles.js

// Variables globales
let rolesData = [];
let elementoAEliminar = null;

// Obtener CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

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
    toast.style.cssText = `position:fixed;top:20px;right:20px;z-index:10000;background:${colores[tipo]};color:white;padding:12px 20px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);animation:slideIn 0.3s ease-out;cursor:pointer;z-index:9999;`;
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ==================== CARGAR ROLES ====================
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
        // La respuesta ya es un objeto, no necesitamos parsear
        if (data.success) {
            rolesData = data.data;
            renderizarTablaRoles();
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
                <td><span class="fw-medium" style="color:#1e3c72">${escapeHtml(rol.nombre)}</span></td>
                <td>${escapeHtml(rol.descripcion || '—')}</td>
                <td><span class="badge bg-info text-dark">${usuariosCount}</span></td>
                <td><span class="badge bg-primary-dark">${permisosCount} permisos</span></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary-dark" onclick="verRol(${rol.id})" title="Ver permisos">${iconos.ver}</button>
                    <button class="btn btn-sm btn-outline-primary-dark" onclick="editarRol(${rol.id})" title="Editar">${iconos.editar}</button>
                    ${rol.nombre !== 'admin' ? `<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminarRol(${rol.id}, '${escapeHtml(rol.nombre).replace(/'/g, "\\'")}')" title="Eliminar">${iconos.eliminar}</button>` : ''}
                </td>
            </tr>
        `;
    }
    tbody.innerHTML = html;
}

// ==================== CARGAR PERMISOS ====================
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

            // Recorrer las categorías
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

// ==================== CRUD ROLES ====================
function abrirModalRol() {
    document.getElementById('modalRolLabel').textContent = 'Nuevo Rol';
    document.getElementById('formMethodRol').value = 'POST';
    document.getElementById('rolId').value = '';
    document.getElementById('rol_nombre').value = '';
    document.getElementById('rol_descripcion').value = '';

    cargarPermisosEnModal([]);

    const modal = new bootstrap.Modal(document.getElementById('modalRol'));
    modal.show();
}

function editarRol(id) {
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
}

function verRol(id) {
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
                                <h6 class="fw-bold" style="color:#1e3c72">${categoria.toUpperCase()}</h6>
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
                    <span class="badge bg-info">${data.usuarios_count || 0} usuarios</span>
                </div>
                <div class="mb-3">
                    <strong>Descripción:</strong>
                    <p>${escapeHtml(data.descripcion || 'Sin descripción')}</p>
                </div>
                <hr>
                <h6 class="fw-bold mb-3">Permisos Asignados (${data.permisos.length})</h6>
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
}

function guardarRol(event) {
    event.preventDefault();

    const id = document.getElementById('rolId').value;
    const method = document.getElementById('formMethodRol').value;
    const url = method === 'PUT' ? `/admin/roles/${id}` : '/admin/roles';

    const formData = new FormData(document.getElementById('formRol'));
    if (method === 'PUT') formData.append('_method', 'PUT');

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
    });
}

function confirmarEliminarRol(id, nombre) {
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
}

function eliminarRol() {
    if (!elementoAEliminar) return;

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
    });
}

// ==================== INICIALIZACIÓN ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - Inicializando módulo de roles');
    cargarRoles();

    // Event listeners
    const formRol = document.getElementById('formRol');
    if (formRol) {
        formRol.addEventListener('submit', guardarRol);
    }

    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', eliminarRol);
    }

    // Exponer funciones globales
    window.abrirModalRol = abrirModalRol;
    window.editarRol = editarRol;
    window.verRol = verRol;
    window.confirmarEliminarRol = confirmarEliminarRol;
});
