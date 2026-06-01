// resources/js/admin-solicitudes.js

let solicitudesData = [];
let currentPage = 1;
let lastPage = 1;
let perPage = 10;
let totalRegistros = 0;
let timeoutBusqueda = null;
let solicitudACancelar = null;
let itemCount = 1;

// Elementos DOM
const searchInput = document.getElementById('searchInput');
const estadoFilter = document.getElementById('estadoFilter');
const prioridadFilter = document.getElementById('prioridadFilter');
const limpiarBtn = document.getElementById('limpiarFiltros');
const perPageSelect = document.getElementById('perPageSelect');

// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

let filtros = {
    search: '',
    estado: '',
    prioridad: ''
};

// ==================== UTILIDADES ====================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function mostrarNotificacion(tipo, mensaje) {
    const container = document.getElementById('notification-container');
    if (!container) return;

    const colores = { success: '#28a745', error: '#dc3545', warning: '#ffc107', info: '#17a2b8' };
    const toast = document.createElement('div');
    toast.style.cssText = `background: ${colores[tipo]}; color: white; border-radius: 10px; padding: 12px 16px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 12px; cursor: pointer; animation: slideIn 0.3s ease-out;`;
    toast.innerHTML = `<span style="flex:1">${mensaje}</span><span style="opacity:0.7; cursor:pointer;" onclick="this.parentElement.remove()">✕</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        if (toast.parentNode) toast.remove();
    }, 4000);
}

function mostrarSkeleton(show) {
    const skeleton = document.getElementById('skeletonLoader');
    const tablaBody = document.getElementById('tablaBody');
    if (show) {
        if (skeleton) skeleton.style.display = 'block';
        if (tablaBody) tablaBody.style.display = 'none';
    } else {
        if (skeleton) skeleton.style.display = 'none';
        if (tablaBody) tablaBody.style.display = '';
    }
}

function actualizarEstadisticas() {
    const total = solicitudesData.length;
    const pendientes = solicitudesData.filter(s => s.estado_solicitud === 'pendiente').length;
    const aprobadas = solicitudesData.filter(s => s.estado_solicitud === 'aprobada').length;
    const rechazadas = solicitudesData.filter(s => s.estado_solicitud === 'rechazada').length;

    document.getElementById('statsTotal') && (document.getElementById('statsTotal').textContent = total);
    document.getElementById('statsPendientes') && (document.getElementById('statsPendientes').textContent = pendientes);
    document.getElementById('statsAprobadas') && (document.getElementById('statsAprobadas').textContent = aprobadas);
    document.getElementById('statsRechazadas') && (document.getElementById('statsRechazadas').textContent = rechazadas);
}

// ==================== RENDERIZAR TABLA ====================
function renderizarTabla() {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;

    if (solicitudesData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-muted">No hay solicitudes registradas</td></tr>`;
        return;
    }

    let html = '';
    let contador = ((currentPage - 1) * perPage) + 1;

    for (const s of solicitudesData) {
        const fechaSolicitud = s.fecha_solicitud ? new Date(s.fecha_solicitud).toLocaleDateString() : 'N/A';
        const fechaRequerida = s.fecha_requerida ? new Date(s.fecha_requerida).toLocaleDateString() : 'N/A';

        let nombreEntidad = 'No especificado';
        if (s.tipo_solicitante === 'interno' && s.departamento) nombreEntidad = s.departamento.nombre;
        else if (s.tipo_solicitante === 'externo' && s.institucion) nombreEntidad = s.institucion.nombre;

        const nombreResponsable = s.responsable ? s.responsable.nombre : 'No especificado';

        const prioridadClass = `badge-prioridad-${s.prioridad}`;
        const estadoClass = `badge-estado-${s.estado_solicitud}`;

        html += `<tr>
            <td class="px-3 py-2">${contador++}</td>
            <td class="px-3 py-2">${fechaSolicitud}</td>
            <td class="px-3 py-2">${escapeHtml(nombreEntidad)}</td>
            <td class="px-3 py-2">${escapeHtml(nombreResponsable)}</td>
            <td class="px-3 py-2">${fechaRequerida}</td>
            <td class="px-3 py-2"><span class="badge-prioridad ${prioridadClass}">${s.prioridad}</span></td>
            <td class="px-3 py-2"><span class="badge-estado ${estadoClass}">${s.estado_solicitud}</span></td>
            <td class="px-3 py-2 text-center">${s.detalles?.length || 0}</td>
            <td class="px-3 py-2 text-end">
                <button class="btn-accion btn-ver" onclick="verDetalles(${s.id})" style="background: none; border: none; color: #17a2b8; cursor: pointer;" title="Ver">👁️ Ver</button>
                ${s.estado_solicitud === 'pendiente' ? `<button class="btn-accion btn-editar" onclick="editarSolicitud(${s.id})" style="background: none; border: none; color: #ffc107; cursor: pointer;" title="Editar">✏️ Editar</button>` : ''}
                ${s.estado_solicitud === 'pendiente' ? `<button class="btn-accion btn-cancelar" onclick="abrirModalConfirmacionCancelar(${s.id})" style="background: none; border: none; color: #dc3545; cursor: pointer;" title="Cancelar">❌ Cancelar</button>` : ''}
                ${authUserHasPermission('aprobar-solicitudes') && s.estado_solicitud === 'pendiente' ? `
                    <button class="btn-accion btn-aprobar" onclick="aprobarSolicitud(${s.id})" style="background: none; border: none; color: #28a745; cursor: pointer;" title="Aprobar">✓ Aprobar</button>
                    <button class="btn-accion btn-rechazar" onclick="rechazarSolicitud(${s.id})" style="background: none; border: none; color: #dc3545; cursor: pointer;" title="Rechazar">✗ Rechazar</button>
                ` : ''}
            </td>
        </tr>`;
    }
    tbody.innerHTML = html;
}

// ==================== PAGINACIÓN ====================
function renderizarPaginacion() {
    const container = document.getElementById('paginationContainer');
    if (!container) return;

    if (lastPage <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="cambiarPagina(${currentPage - 1}); return false;">«</a></li>`;

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(lastPage, currentPage + 2);

    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(1); return false;">1</a></li>`;
        if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a></li>`;
    }

    if (endPage < lastPage) {
        if (endPage < lastPage - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${lastPage}); return false;">${lastPage}</a></li>`;
    }

    html += `<li class="page-item ${currentPage === lastPage ? 'disabled' : ''}"><a class="page-link" href="#" onclick="cambiarPagina(${currentPage + 1}); return false;">»</a></li>`;

    container.innerHTML = html;
}

function cambiarPagina(page) {
    if (page < 1 || page > lastPage) return;
    currentPage = page;
    cargarPagina(currentPage);
}

// ==================== CARGAR DATOS ====================
async function cargarPagina(page) {
    mostrarSkeleton(true);

    try {
        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: filtros.search,
            estado: filtros.estado,
            prioridad: filtros.prioridad
        });

        const response = await fetch(`/admin/solicitudes?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error('Error al cargar datos');

        const data = await response.json();

        solicitudesData = data.data || [];
        currentPage = data.current_page || 1;
        lastPage = data.last_page || 1;
        perPage = data.per_page || 10;
        totalRegistros = data.total || 0;

        renderizarTabla();
        actualizarEstadisticas();
        renderizarPaginacion();

        document.getElementById('resultadosCount') && (document.getElementById('resultadosCount').textContent = solicitudesData.length);
        document.getElementById('totalRegistrosCount') && (document.getElementById('totalRegistrosCount').textContent = totalRegistros);
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'No se pudieron cargar las solicitudes');
    } finally {
        mostrarSkeleton(false);
    }
}

// ==================== FILTROS ====================
function aplicarFiltros() {
    filtros = {
        search: searchInput?.value || '',
        estado: estadoFilter?.value || '',
        prioridad: prioridadFilter?.value || ''
    };
    currentPage = 1;
    cargarPagina(1);
}

function aplicarFiltrosConDebounce() {
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(() => {
        aplicarFiltros();
    }, 300);
}

// ==================== CREAR SOLICITUD ====================
window.abrirModalCrear = function() {
    // Resetear formulario
    document.getElementById('formCrearSolicitud').reset();
    document.getElementById('tipoSolicitante').value = 'interno';
    document.getElementById('interno-fields').style.display = 'block';
    document.getElementById('externo-fields').style.display = 'none';
    document.getElementById('responsableDisplay').innerHTML = '<span class="text-muted">Seleccione una opción</span>';
    document.getElementById('departamento-nuevo-field').style.display = 'none';
    document.getElementById('institucion-nuevo-field').style.display = 'none';
    itemCount = 1;

    // Limpiar items
    document.getElementById('items-container-modal').innerHTML = `
        <div class="item-card">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="items[0][tipo_item]" class="form-select form-select-sm" required>
                        <option value="activo">Activo</option>
                        <option value="componente">Componente</option>
                    </select>
                </div>
                <div class="col-md-7">
                    <input type="text" name="items[0][item_descripcion]" class="form-control form-control-sm" placeholder="Descripción del item" required>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <input type="number" name="items[0][cantidad]" class="form-control form-control-sm" value="1" min="1" required>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-modal">×</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    new bootstrap.Modal(document.getElementById('modalCrear')).show();
};

// ==================== VER DETALLES ====================
window.verDetalles = async function(id) {
    const modalElement = document.getElementById('modalDetalles');
    const modalBody = document.getElementById('modalDetallesBody');
    const modal = new bootstrap.Modal(modalElement);

    modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando...</p></div>';
    modal.show();

    try {
        const response = await fetch(`/admin/solicitudes/${id}/detalles`);
        const data = await response.json();

        if (data.error) {
            modalBody.innerHTML = `<div class="text-center text-danger py-4">${data.error}</div>`;
            return;
        }

        let itemsHtml = '';
        if (data.detalles && data.detalles.length > 0) {
            itemsHtml = '<div class="table-responsive mt-3"><table class="table table-sm"><thead><tr><th>Tipo</th><th>Descripción</th><th class="text-center">Cantidad</th></tr></thead><tbody>';
            for (const item of data.detalles) {
                itemsHtml += `<tr>
                    <td>${item.tipo_item === 'activo' ? 'Activo' : 'Componente'}</td>
                    <td>${escapeHtml(item.item_descripcion)}</td>
                    <td class="text-center"><strong>${item.cantidad_solicitada}</strong></td>
                </tr>`;
            }
            itemsHtml += '</tbody></table></div>';
        } else {
            itemsHtml = '<div class="alert alert-secondary mt-3">No hay items registrados</div>';
        }

        const fechaSolicitud = data.fecha_solicitud ? new Date(data.fecha_solicitud).toLocaleDateString() : 'N/A';
        const fechaRequerida = data.fecha_requerida ? new Date(data.fecha_requerida).toLocaleDateString() : 'N/A';
        const fechaFin = data.fecha_fin_estimada ? new Date(data.fecha_fin_estimada).toLocaleDateString() : 'N/A';

        let nombreEntidad = 'No especificado';
        if (data.tipo_solicitante === 'interno' && data.departamento) nombreEntidad = data.departamento.nombre;
        else if (data.tipo_solicitante === 'externo' && data.institucion) nombreEntidad = data.institucion.nombre;

        const html = `
            <div class="row">
                <div class="col-md-6 mb-3"><label class="text-muted small">Fecha Solicitud</label><div class="fw-semibold">${fechaSolicitud}</div></div>
                <div class="col-md-6 mb-3"><label class="text-muted small">Tipo Solicitante</label><div class="fw-semibold">${data.tipo_solicitante === 'interno' ? 'Interno' : 'Externo'}</div></div>
                <div class="col-md-6 mb-3"><label class="text-muted small">Prioridad</label><div><span class="badge-prioridad badge-prioridad-${data.prioridad}">${data.prioridad}</span></div></div>
                <div class="col-md-6 mb-3"><label class="text-muted small">Estado</label><div><span class="badge-estado badge-estado-${data.estado_solicitud}">${data.estado_solicitud}</span></div></div>
                <div class="col-md-6 mb-3"><label class="text-muted small">Entidad</label><div class="fw-semibold">${escapeHtml(nombreEntidad)}</div></div>
                <div class="col-md-6 mb-3"><label class="text-muted small">Responsable</label><div class="fw-semibold">${escapeHtml(data.responsable?.nombre || 'No especificado')}</div></div>
                <div class="col-md-6 mb-3"><label class="text-muted small">Fecha Requerida</label><div>${fechaRequerida}</div></div>
                <div class="col-md-6 mb-3"><label class="text-muted small">Fecha Fin Estimada</label><div>${fechaFin}</div></div>
                <div class="col-12 mb-3"><label class="text-muted small">Justificación</label><div class="p-2 bg-light rounded">${escapeHtml(data.justificacion || 'No especificada')}</div></div>
                <div class="col-12"><label class="text-muted small fw-semibold mb-2">Items Solicitados</label>${itemsHtml}</div>
            </div>
        `;
        modalBody.innerHTML = html;
    } catch (error) {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="text-center text-danger py-4">Error al cargar los detalles</div>';
    }
};

// ==================== EDITAR SOLICITUD ====================
window.editarSolicitud = function(id) {
    const solicitud = solicitudesData.find(s => s.id === id);
    if (!solicitud) return;

    document.getElementById('editId').value = solicitud.id;
    document.getElementById('editTipoSolicitante').value = solicitud.tipo_solicitante;
    document.getElementById('editPrioridad').value = solicitud.prioridad;
    document.getElementById('editFechaRequerida').value = solicitud.fecha_requerida || '';
    document.getElementById('editFechaFin').value = solicitud.fecha_fin_estimada || '';
    document.getElementById('editJustificacion').value = solicitud.justificacion || '';
    document.getElementById('editObservaciones').value = solicitud.observaciones || '';

    // Mostrar/ocultar campos según tipo
    const editInternoFields = document.getElementById('editInternoFields');
    const editExternoFields = document.getElementById('editExternoFields');

    if (solicitud.tipo_solicitante === 'interno') {
        editInternoFields.style.display = 'block';
        editExternoFields.style.display = 'none';
        if (solicitud.departamento_id) {
            document.getElementById('editDepartamentoId').value = solicitud.departamento_id;
        }
    } else {
        editInternoFields.style.display = 'none';
        editExternoFields.style.display = 'block';
        if (solicitud.institucion_id) {
            document.getElementById('editInstitucionId').value = solicitud.institucion_id;
        }
    }

    const display = document.getElementById('editResponsableDisplay');
    if (solicitud.responsable) {
        display.innerHTML = `<strong>${escapeHtml(solicitud.responsable.nombre)}</strong><br><small class="text-muted">${escapeHtml(solicitud.responsable.cargo || '')}</small>`;
    } else {
        display.innerHTML = '<span class="text-muted">No hay responsable asignado</span>';
    }

    new bootstrap.Modal(document.getElementById('modalEditar')).show();
};

// ==================== APROBAR/RECHAZAR ====================
window.aprobarSolicitud = async function(id) {
    if (!confirm('¿Aprobar esta solicitud?')) return;

    try {
        const response = await fetch(`/admin/solicitudes/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (response.ok) {
            mostrarNotificacion('success', result.message || 'Solicitud aprobada');
            cargarPagina(currentPage);
        } else {
            mostrarNotificacion('error', result.message || 'Error al aprobar');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
    }
};

window.rechazarSolicitud = async function(id) {
    const motivo = prompt('Motivo del rechazo:');
    if (!motivo) return;

    try {
        const response = await fetch(`/admin/solicitudes/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ motivo: motivo })
        });

        const result = await response.json();

        if (response.ok) {
            mostrarNotificacion('success', result.message || 'Solicitud rechazada');
            cargarPagina(currentPage);
        } else {
            mostrarNotificacion('error', result.message || 'Error al rechazar');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
    }
};

// ==================== CANCELAR ====================
window.abrirModalConfirmacionCancelar = function(id) {
    solicitudACancelar = id;
    new bootstrap.Modal(document.getElementById('modalConfirmacionCancelar')).show();
};

window.confirmarCancelar = async function() {
    if (!solicitudACancelar) return;

    try {
        const response = await fetch(`/admin/solicitudes/${solicitudACancelar}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (response.ok && result.success) {
            mostrarNotificacion('success', result.message || 'Solicitud cancelada');
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmacionCancelar')).hide();
            cargarPagina(currentPage);
        } else {
            mostrarNotificacion('error', result.message || 'No se pudo cancelar');
        }
        solicitudACancelar = null;
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
    }
};

// ==================== EVENTOS DEL MODAL CREAR ====================
function initCrearEventListeners() {
    // Toggle entre interno/externo
    document.getElementById('tipoSolicitante')?.addEventListener('change', function() {
        const internoFields = document.getElementById('interno-fields');
        const externoFields = document.getElementById('externo-fields');
        if (this.value === 'interno') {
            internoFields.style.display = 'block';
            externoFields.style.display = 'none';
        } else {
            internoFields.style.display = 'none';
            externoFields.style.display = 'block';
        }
    });

    // Mostrar/ocultar campo nuevo departamento
    document.getElementById('departamentoSelect')?.addEventListener('change', function() {
        const nuevoField = document.getElementById('departamento-nuevo-field');
        if (this.value === 'otro') {
            nuevoField.style.display = 'block';
        } else {
            nuevoField.style.display = 'none';
            if (this.value) {
                cargarResponsablePorDepartamento(this.value);
            }
        }
    });

    // Mostrar/ocultar campo nueva institución
    document.getElementById('institucionSelect')?.addEventListener('change', function() {
        const nuevoField = document.getElementById('institucion-nuevo-field');
        if (this.value === 'otro') {
            nuevoField.style.display = 'block';
        } else {
            nuevoField.style.display = 'none';
            if (this.value) {
                cargarResponsablePorInstitucion(this.value);
            }
        }
    });

    // Agregar item
    document.getElementById('add-item-modal')?.addEventListener('click', function() {
        const container = document.getElementById('items-container-modal');
        const newCard = document.createElement('div');
        newCard.className = 'item-card';
        newCard.innerHTML = `
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="items[${itemCount}][tipo_item]" class="form-select form-select-sm" required>
                        <option value="activo">Activo</option>
                        <option value="componente">Componente</option>
                    </select>
                </div>
                <div class="col-md-7">
                    <input type="text" name="items[${itemCount}][item_descripcion]" class="form-control form-control-sm" placeholder="Descripción del item" required>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <input type="number" name="items[${itemCount}][cantidad]" class="form-control form-control-sm" value="1" min="1" required>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-modal">×</button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newCard);
        itemCount++;
    });

    // Eliminar item
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item-modal') || e.target.parentElement?.classList?.contains('remove-item-modal')) {
            const btn = e.target.classList.contains('remove-item-modal') ? e.target : e.target.parentElement;
            const card = btn.closest('.item-card');
            const cards = document.querySelectorAll('#items-container-modal .item-card');
            if (cards.length > 1) {
                card.remove();
            } else {
                mostrarNotificacion('error', 'Debe haber al menos un item');
            }
        }
    });
}

async function cargarResponsablePorDepartamento(departamentoId) {
    try {
        const response = await fetch(`/api/departamento/${departamentoId}/responsable`);
        const data = await response.json();
        const display = document.getElementById('responsableDisplay');

        if (data.responsable) {
            display.innerHTML = `<strong>${escapeHtml(data.responsable.nombre)}</strong><br>
                                <small>${escapeHtml(data.responsable.cargo || '')} - ${escapeHtml(data.responsable.telefono || '')}</small>`;
            // Crear un campo oculto para el responsable_id si existe en el formulario
            let hiddenInput = document.getElementById('responsable_id_hidden');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'responsable_id';
                hiddenInput.id = 'responsable_id_hidden';
                display.appendChild(hiddenInput);
            }
            hiddenInput.value = data.responsable.id;
        } else {
            display.innerHTML = '<span class="text-muted">No hay responsable asignado a este departamento</span>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function cargarResponsablePorInstitucion(institucionId) {
    try {
        const response = await fetch(`/api/institucion/${institucionId}/responsable`);
        const data = await response.json();
        const display = document.getElementById('responsableDisplay');

        if (data.responsable) {
            display.innerHTML = `<strong>${escapeHtml(data.responsable.nombre)}</strong><br>
                                <small>${escapeHtml(data.responsable.cargo || '')} - ${escapeHtml(data.responsable.telefono || '')}</small>`;
            let hiddenInput = document.getElementById('responsable_id_hidden');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'responsable_id';
                hiddenInput.id = 'responsable_id_hidden';
                display.appendChild(hiddenInput);
            }
            hiddenInput.value = data.responsable.id;
        } else {
            display.innerHTML = '<span class="text-muted">No hay responsable asignado a esta institución</span>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// ==================== ENVÍO DE FORMULARIOS ====================
document.getElementById('formCrearSolicitud')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.querySelector('#formCrearSolicitud button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';
    submitBtn.disabled = true;

    const formData = new FormData(this);

    try {
        const response = await fetch('/admin/solicitudes/store', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
            mostrarNotificacion('success', 'Solicitud creada exitosamente');
            bootstrap.Modal.getInstance(document.getElementById('modalCrear')).hide();
            cargarPagina(1);
        } else {
            mostrarNotificacion('error', result.message || 'No se pudo crear la solicitud');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

document.getElementById('formEditarSolicitud')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('editId').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Actualizando...';
    submitBtn.disabled = true;

    const formData = new FormData(this);
    formData.append('_method', 'PUT');

    try {
        const response = await fetch(`/admin/solicitudes/${id}/update`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
            mostrarNotificacion('success', result.message || 'Solicitud actualizada');
            bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
            cargarPagina(currentPage);
        } else {
            mostrarNotificacion('error', result.message || 'No se pudo actualizar');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// ==================== EVENT LISTENERS ====================
function initEventListeners() {
    searchInput?.addEventListener('input', aplicarFiltrosConDebounce);
    estadoFilter?.addEventListener('change', aplicarFiltros);
    prioridadFilter?.addEventListener('change', aplicarFiltros);
    limpiarBtn?.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        if (estadoFilter) estadoFilter.value = '';
        if (prioridadFilter) prioridadFilter.value = '';
        aplicarFiltros();
    });

    document.getElementById('btnConfirmarCancelar')?.addEventListener('click', window.confirmarCancelar);
}

// ==================== INICIALIZACIÓN ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de solicitudes inicializado');
    initEventListeners();
    initCrearEventListeners();
    cargarPagina(1);
});
