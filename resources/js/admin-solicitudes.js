// resources/js/admin-solicitudes.js
// ✅ VERSIÓN COMPLETA CORREGIDA - Solo se agregaron validaciones

let solicitudesData = [];
let currentPage = 1;
let lastPage = 1;
let perPage = 10;
let totalRegistros = 0;
let timeoutBusqueda = null;
let solicitudAEliminar = null;
let solicitudACancelar = null;
let itemCount = 1;

const searchInput = document.getElementById('searchInput');
const estadoFilter = document.getElementById('estadoFilter');
const prioridadFilter = document.getElementById('prioridadFilter');
const limpiarBtn = document.getElementById('limpiarFiltros');

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

let filtros = {
    search: '',
    estado: '',
    prioridad: ''
};

// SVG ICONS
const SVG_ICONS = {
    ver: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`,
    editar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
    eliminar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>`,
    cancelar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
    aprobar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>`,
    rechazar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`
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
    setTimeout(() => { if (toast.parentNode) toast.remove(); }, 4000);
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

    const elTotal = document.getElementById('statsTotal');
    const elPendientes = document.getElementById('statsPendientes');
    const elAprobadas = document.getElementById('statsAprobadas');
    const elRechazadas = document.getElementById('statsRechazadas');

    if (elTotal) elTotal.textContent = total;
    if (elPendientes) elPendientes.textContent = pendientes;
    if (elAprobadas) elAprobadas.textContent = aprobadas;
    if (elRechazadas) elRechazadas.textContent = rechazadas;
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
                <button class="btn-accion btn-ver" onclick="verDetalles(${s.id})" style="background: none; border: none; color: #17a2b8; cursor: pointer;" title="Ver">${SVG_ICONS.ver}</button>
                ${s.estado_solicitud === 'pendiente' ? `<button class="btn-accion btn-editar" onclick="editarSolicitud(${s.id})" style="background: none; border: none; color: #ffc107; cursor: pointer;" title="Editar">${SVG_ICONS.editar}</button>` : ''}
                ${s.estado_solicitud === 'pendiente' ? `<button class="btn-accion btn-cancelar" onclick="abrirModalConfirmacionCancelar(${s.id})" style="background: none; border: none; color: #dc3545; cursor: pointer;" title="Cancelar">${SVG_ICONS.cancelar}</button>` : ''}
                ${authUserHasPermission('aprobar-solicitudes') && s.estado_solicitud === 'pendiente' ? `
                    <button class="btn-accion btn-aprobar" onclick="aprobarSolicitud(${s.id})" style="background: none; border: none; color: #28a745; cursor: pointer;" title="Aprobar">${SVG_ICONS.aprobar}</button>
                    <button class="btn-accion btn-rechazar" onclick="rechazarSolicitud(${s.id})" style="background: none; border: none; color: #dc3545; cursor: pointer;" title="Rechazar">${SVG_ICONS.rechazar}</button>
                ` : ''}
                ${authUserHasPermission('aprobar-solicitudes') ? `<button class="btn-accion btn-eliminar" onclick="confirmarEliminarSolicitud(${s.id})" style="background: none; border: none; color: #dc3545; cursor: pointer;" title="Eliminar">${SVG_ICONS.eliminar}</button>` : ''}
            </td>
        </tr>`;
    }
    tbody.innerHTML = html;
}

function renderizarPaginacion() {
    const container = document.getElementById('paginationContainer');
    if (!container) return;
    if (lastPage <= 1) { container.innerHTML = ''; return; }
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

async function cargarPagina(page) {
    mostrarSkeleton(true);
    try {
        const params = new URLSearchParams({ page, per_page: perPage, search: filtros.search, estado: filtros.estado, prioridad: filtros.prioridad });
        const response = await fetch(`/admin/solicitudes?${params.toString()}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
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
        const elResultados = document.getElementById('resultadosCount');
        const elTotal = document.getElementById('totalRegistrosCount');
        if (elResultados) elResultados.textContent = solicitudesData.length;
        if (elTotal) elTotal.textContent = totalRegistros;
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'No se pudieron cargar las solicitudes');
    } finally { mostrarSkeleton(false); }
}

function aplicarFiltros() {
    filtros = { search: searchInput?.value || '', estado: estadoFilter?.value || '', prioridad: prioridadFilter?.value || '' };
    currentPage = 1;
    cargarPagina(1);
}

function aplicarFiltrosConDebounce() {
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(() => aplicarFiltros(), 300);
}

// ✅ CORREGIDO: abrirModalCrear con validaciones
window.abrirModalCrear = function() {
    const form = document.getElementById('formCrearSolicitud');
    if (form) form.reset();

    const tipoSolicitante = document.getElementById('tipoSolicitante');
    if (tipoSolicitante) tipoSolicitante.value = 'interno';

    const internoFields = document.getElementById('interno-fields');
    const externoFields = document.getElementById('externo-fields');
    if (internoFields) internoFields.style.display = 'block';
    if (externoFields) externoFields.style.display = 'none';

    const responsableDisplay = document.getElementById('responsableDisplay');
    if (responsableDisplay) responsableDisplay.innerHTML = '<span class="text-muted">Seleccione una opción</span>';

    const deptoNuevo = document.getElementById('departamento-nuevo-field');
    const instNuevo = document.getElementById('institucion-nuevo-field');
    if (deptoNuevo) deptoNuevo.style.display = 'none';
    if (instNuevo) instNuevo.style.display = 'none';

    const responsableHidden = document.getElementById('responsable_id_hidden');
    if (responsableHidden) responsableHidden.value = '';

    itemCount = 1;

    const itemsContainer = document.getElementById('items-container-modal');
    if (itemsContainer) {
        itemsContainer.innerHTML = `<div class="item-card"><div class="row g-2"><div class="col-md-3"><select name="items[0][tipo_item]" class="form-select form-select-sm" required><option value="activo">Activo</option><option value="componente">Componente</option></select></div><div class="col-md-7"><input type="text" name="items[0][item_descripcion]" class="form-control form-control-sm" placeholder="Descripción del item" required></div><div class="col-md-2"><div class="input-group"><input type="number" name="items[0][cantidad]" class="form-control form-control-sm" value="1" min="1" required><button type="button" class="btn btn-sm btn-outline-danger remove-item-modal">×</button></div></div></div></div>`;
    }

    const modalCrear = document.getElementById('modalCrear');
    if (modalCrear) new bootstrap.Modal(modalCrear).show();
};

window.verDetalles = async function(id) {
    const modalElement = document.getElementById('modalDetalles');
    if (!modalElement) return;
    const modalBody = document.getElementById('modalDetallesBody');
    const modal = new bootstrap.Modal(modalElement);
    if (modalBody) modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando...</p></div>';
    modal.show();
    try {
        const response = await fetch(`/admin/solicitudes/${id}/detalles`);
        const data = await response.json();
        if (!modalBody) return;
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
        const html = `<div class="row"><div class="col-md-6 mb-3"><label class="text-muted small">Fecha Solicitud</label><div class="fw-semibold">${fechaSolicitud}</div></div><div class="col-md-6 mb-3"><label class="text-muted small">Tipo Solicitante</label><div class="fw-semibold">${data.tipo_solicitante === 'interno' ? 'Interno' : 'Externo'}</div></div><div class="col-md-6 mb-3"><label class="text-muted small">Prioridad</label><div><span class="badge-prioridad badge-prioridad-${data.prioridad}">${data.prioridad}</span></div></div><div class="col-md-6 mb-3"><label class="text-muted small">Estado</label><div><span class="badge-estado badge-estado-${data.estado_solicitud}">${data.estado_solicitud}</span></div></div><div class="col-md-6 mb-3"><label class="text-muted small">Entidad</label><div class="fw-semibold">${escapeHtml(nombreEntidad)}</div></div><div class="col-md-6 mb-3"><label class="text-muted small">Responsable</label><div class="fw-semibold">${escapeHtml(data.responsable?.nombre || 'No especificado')}</div></div><div class="col-md-6 mb-3"><label class="text-muted small">Fecha Requerida</label><div>${fechaRequerida}</div></div><div class="col-md-6 mb-3"><label class="text-muted small">Fecha Fin Estimada</label><div>${fechaFin}</div></div><div class="col-12 mb-3"><label class="text-muted small">Justificación</label><div class="p-2 bg-light rounded">${escapeHtml(data.justificacion || 'No especificada')}</div></div><div class="col-12"><label class="text-muted small fw-semibold mb-2">Items Solicitados</label>${itemsHtml}</div></div>`;
        modalBody.innerHTML = html;
    } catch (error) {
        console.error('Error:', error);
        if (modalBody) modalBody.innerHTML = '<div class="text-center text-danger py-4">Error al cargar los detalles</div>';
    }
};

window.editarSolicitud = async function(id) {
    try {
        const response = await fetch(`/admin/solicitudes/${id}/detalles`);
        const data = await response.json();
        if (!data.success) {
            mostrarNotificacion('error', 'No se pudieron cargar los datos de la solicitud');
            return;
        }
        const form = document.getElementById('formEditarSolicitud');
        if (form) form.reset();
        const editId = document.getElementById('editId');
        if (editId) editId.value = data.id;
        const editTipoSolicitante = document.getElementById('editTipoSolicitante');
        if (editTipoSolicitante) editTipoSolicitante.value = data.tipo_solicitante;
        const editPrioridad = document.getElementById('editPrioridad');
        if (editPrioridad) editPrioridad.value = data.prioridad;
        const editFechaRequerida = document.getElementById('editFechaRequerida');
        if (editFechaRequerida && data.fecha_requerida) editFechaRequerida.value = data.fecha_requerida.split('T')[0];
        const editFechaFin = document.getElementById('editFechaFin');
        if (editFechaFin && data.fecha_fin_estimada) editFechaFin.value = data.fecha_fin_estimada.split('T')[0];
        const editJustificacion = document.getElementById('editJustificacion');
        if (editJustificacion) editJustificacion.value = data.justificacion || '';
        const editObservaciones = document.getElementById('editObservaciones');
        if (editObservaciones) editObservaciones.value = data.observaciones || '';

        const editInternoFields = document.getElementById('editInternoFields');
        const editExternoFields = document.getElementById('editExternoFields');

        if (data.tipo_solicitante === 'interno') {
            if (editInternoFields) editInternoFields.style.display = 'block';
            if (editExternoFields) editExternoFields.style.display = 'none';
            if (data.departamento_id) {
                const editDepartamentoId = document.getElementById('editDepartamentoId');
                if (editDepartamentoId) editDepartamentoId.value = data.departamento_id;
                await cargarResponsablePorDepartamentoEditar(data.departamento_id, data.responsable_id);
            }
        } else {
            if (editInternoFields) editInternoFields.style.display = 'none';
            if (editExternoFields) editExternoFields.style.display = 'block';
            if (data.institucion_id) {
                const editInstitucionId = document.getElementById('editInstitucionId');
                if (editInstitucionId) editInstitucionId.value = data.institucion_id;
                await cargarResponsablePorInstitucionEditar(data.institucion_id, data.responsable_id);
            }
        }
        cargarItemsEdicion(data.detalles || []);
        const modalEditar = document.getElementById('modalEditar');
        if (modalEditar) new bootstrap.Modal(modalEditar).show();
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error al cargar los datos de la solicitud');
    }
};

function cargarItemsEdicion(detalles) {
    const container = document.getElementById('items-container-editar');
    if (!container) return;
    if (!detalles || detalles.length === 0) {
        container.innerHTML = `<div class="item-card"><div class="row g-2"><div class="col-md-3"><select name="items[0][tipo_item]" class="form-select form-select-sm" required><option value="activo">Activo</option><option value="componente">Componente</option></select></div><div class="col-md-7"><input type="text" name="items[0][item_descripcion]" class="form-control form-control-sm" placeholder="Descripción del item" required></div><div class="col-md-2"><div class="input-group"><input type="number" name="items[0][cantidad]" class="form-control form-control-sm" value="1" min="1" required><button type="button" class="btn btn-sm btn-outline-danger remove-item-editar">×</button></div></div></div></div>`;
        return;
    }
    let html = '';
    detalles.forEach((item, index) => {
        html += `<div class="item-card"><input type="hidden" name="items[${index}][id]" value="${item.id || ''}"><div class="row g-2"><div class="col-md-3"><select name="items[${index}][tipo_item]" class="form-select form-select-sm" required><option value="activo" ${item.tipo_item === 'activo' ? 'selected' : ''}>Activo</option><option value="componente" ${item.tipo_item === 'componente' ? 'selected' : ''}>Componente</option></select></div><div class="col-md-7"><input type="text" name="items[${index}][item_descripcion]" class="form-control form-control-sm" value="${escapeHtml(item.item_descripcion)}" placeholder="Descripción del item" required></div><div class="col-md-2"><div class="input-group"><input type="number" name="items[${index}][cantidad]" class="form-control form-control-sm" value="${item.cantidad_solicitada}" min="1" required><button type="button" class="btn btn-sm btn-outline-danger remove-item-editar">×</button></div></div></div></div>`;
    });
    container.innerHTML = html;
}

async function cargarResponsablePorDepartamentoEditar(departamentoId, responsableIdActual) {
    try {
        const response = await fetch(`/admin/api/departamento/${departamentoId}/responsable`);
        const data = await response.json();
        const display = document.getElementById('editResponsableDisplay');
        const hiddenInput = document.getElementById('edit_responsable_id_hidden');
        if (data.responsable) {
            if (display) display.innerHTML = `<strong>${escapeHtml(data.responsable.nombre)}</strong><br><small>${escapeHtml(data.responsable.cargo || '')} - ${escapeHtml(data.responsable.telefono || '')}</small>`;
            if (hiddenInput) hiddenInput.value = responsableIdActual || data.responsable.id;
        } else {
            if (display) display.innerHTML = '<span class="text-muted">No hay responsable asignado</span>';
            if (hiddenInput) hiddenInput.value = responsableIdActual || '';
        }
    } catch (error) { console.error('Error:', error); }
}

async function cargarResponsablePorInstitucionEditar(institucionId, responsableIdActual) {
    try {
        const response = await fetch(`/admin/api/institucion/${institucionId}/responsable`);
        const data = await response.json();
        const display = document.getElementById('editResponsableDisplay');
        const hiddenInput = document.getElementById('edit_responsable_id_hidden');
        if (data.responsable) {
            if (display) display.innerHTML = `<strong>${escapeHtml(data.responsable.nombre)}</strong><br><small>${escapeHtml(data.responsable.cargo || '')} - ${escapeHtml(data.responsable.telefono || '')}</small>`;
            if (hiddenInput) hiddenInput.value = responsableIdActual || data.responsable.id;
        } else {
            if (display) display.innerHTML = '<span class="text-muted">No hay responsable asignado</span>';
            if (hiddenInput) hiddenInput.value = responsableIdActual || '';
        }
    } catch (error) { console.error('Error:', error); }
}

window.confirmarEliminarSolicitud = function(id) {
    solicitudAEliminar = id;
    const el = document.getElementById('deleteSolicitudNombre');
    if (el) el.textContent = `Solicitud #${id}`;
    const modal = document.getElementById('modalEliminarSolicitud');
    if (modal) new bootstrap.Modal(modal).show();
};

async function eliminarSolicitud() {
    if (!solicitudAEliminar) return;
    try {
        const response = await fetch(`/admin/solicitudes/${solicitudAEliminar}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const result = await response.json();
        if (response.ok && result.success) {
            mostrarNotificacion('success', 'Solicitud eliminada exitosamente');
            const modal = document.getElementById('modalEliminarSolicitud');
            if (modal) bootstrap.Modal.getInstance(modal)?.hide();
            cargarPagina(currentPage);
        } else {
            mostrarNotificacion('error', result.message || 'No se pudo eliminar la solicitud');
        }
        solicitudAEliminar = null;
    } catch (error) { console.error('Error:', error); mostrarNotificacion('error', 'Error de conexión'); }
}

window.aprobarSolicitud = async function(id) {
    if (!confirm('¿Aprobar esta solicitud?')) return;
    try {
        const response = await fetch(`/admin/solicitudes/${id}/approve`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const result = await response.json();
        if (response.ok && result.success) {
            mostrarNotificacion('success', result.message || 'Solicitud aprobada');
            window.location.href = '/admin/prestamos';
        } else {
            mostrarNotificacion('error', result.message || 'Error al aprobar');
        }
    } catch (error) { console.error('Error:', error); mostrarNotificacion('error', 'Error de conexión'); }
};

window.rechazarSolicitud = async function(id) {
    const motivo = prompt('Motivo del rechazo:');
    if (!motivo) return;
    try {
        const response = await fetch(`/admin/solicitudes/${id}/reject`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ motivo }) });
        const result = await response.json();
        if (response.ok && result.success) {
            mostrarNotificacion('success', result.message || 'Solicitud rechazada');
            cargarPagina(currentPage);
        } else {
            mostrarNotificacion('error', result.message || 'Error al rechazar');
        }
    } catch (error) { console.error('Error:', error); mostrarNotificacion('error', 'Error de conexión'); }
};

window.abrirModalConfirmacionCancelar = function(id) {
    solicitudACancelar = id;
    const modal = document.getElementById('modalConfirmacionCancelar');
    if (modal) new bootstrap.Modal(modal).show();
};

window.confirmarCancelar = async function() {
    if (!solicitudACancelar) return;
    try {
        const response = await fetch(`/admin/solicitudes/${solicitudACancelar}/cancel`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const result = await response.json();
        if (response.ok && result.success) {
            mostrarNotificacion('success', result.message || 'Solicitud cancelada');
            const modal = document.getElementById('modalConfirmacionCancelar');
            if (modal) bootstrap.Modal.getInstance(modal)?.hide();
            cargarPagina(currentPage);
        } else {
            mostrarNotificacion('error', result.message || 'No se pudo cancelar');
        }
        solicitudACancelar = null;
    } catch (error) { console.error('Error:', error); mostrarNotificacion('error', 'Error de conexión'); }
};

async function cargarResponsablePorDepartamento(departamentoId) {
    try {
        const response = await fetch(`/admin/api/departamento/${departamentoId}/responsable`);
        const data = await response.json();
        const display = document.getElementById('responsableDisplay');
        const hiddenInput = document.getElementById('responsable_id_hidden');
        if (data.responsable) {
            if (display) display.innerHTML = `<strong>${escapeHtml(data.responsable.nombre)}</strong><br><small>${escapeHtml(data.responsable.cargo || '')} - ${escapeHtml(data.responsable.telefono || '')}</small>`;
            if (hiddenInput) hiddenInput.value = data.responsable.id;
        } else {
            if (display) display.innerHTML = '<span class="text-muted">No hay responsable asignado a este departamento</span>';
            if (hiddenInput) hiddenInput.value = '';
        }
    } catch (error) { console.error('Error:', error); }
}

async function cargarResponsablePorInstitucion(institucionId) {
    try {
        const response = await fetch(`/admin/api/institucion/${institucionId}/responsable`);
        const data = await response.json();
        const display = document.getElementById('responsableDisplay');
        const hiddenInput = document.getElementById('responsable_id_hidden');
        if (data.responsable) {
            if (display) display.innerHTML = `<strong>${escapeHtml(data.responsable.nombre)}</strong><br><small>${escapeHtml(data.responsable.cargo || '')} - ${escapeHtml(data.responsable.telefono || '')}</small>`;
            if (hiddenInput) hiddenInput.value = data.responsable.id;
        } else {
            if (display) display.innerHTML = '<span class="text-muted">No hay responsable asignado a esta institución</span>';
            if (hiddenInput) hiddenInput.value = '';
        }
    } catch (error) { console.error('Error:', error); }
}

function initCrearEventListeners() {
    const tipoSolicitante = document.getElementById('tipoSolicitante');
    if (tipoSolicitante) {
        tipoSolicitante.addEventListener('change', function() {
            const internoFields = document.getElementById('interno-fields');
            const externoFields = document.getElementById('externo-fields');
            if (this.value === 'interno') {
                if (internoFields) internoFields.style.display = 'block';
                if (externoFields) externoFields.style.display = 'none';
            } else {
                if (internoFields) internoFields.style.display = 'none';
                if (externoFields) externoFields.style.display = 'block';
            }
        });
    }

    const departamentoSelect = document.getElementById('departamentoSelect');
    if (departamentoSelect) {
        departamentoSelect.addEventListener('change', function() {
            const nuevoField = document.getElementById('departamento-nuevo-field');
            const display = document.getElementById('responsableDisplay');
            const hiddenInput = document.getElementById('responsable_id_hidden');
            if (this.value === 'otro') {
                if (nuevoField) nuevoField.style.display = 'block';
                if (display) display.innerHTML = '<span class="text-muted">Complete los datos del nuevo departamento</span>';
                if (hiddenInput) hiddenInput.value = '';
            } else {
                if (nuevoField) nuevoField.style.display = 'none';
                if (this.value) cargarResponsablePorDepartamento(this.value);
                else {
                    if (display) display.innerHTML = '<span class="text-muted">Seleccione una opción</span>';
                    if (hiddenInput) hiddenInput.value = '';
                }
            }
        });
    }

    const institucionSelect = document.getElementById('institucionSelect');
    if (institucionSelect) {
        institucionSelect.addEventListener('change', function() {
            const nuevoField = document.getElementById('institucion-nuevo-field');
            const display = document.getElementById('responsableDisplay');
            const hiddenInput = document.getElementById('responsable_id_hidden');
            if (this.value === 'otro') {
                if (nuevoField) nuevoField.style.display = 'block';
                if (display) display.innerHTML = '<span class="text-muted">Complete los datos de la nueva institución</span>';
                if (hiddenInput) hiddenInput.value = '';
            } else {
                if (nuevoField) nuevoField.style.display = 'none';
                if (this.value) cargarResponsablePorInstitucion(this.value);
                else {
                    if (display) display.innerHTML = '<span class="text-muted">Seleccione una opción</span>';
                    if (hiddenInput) hiddenInput.value = '';
                }
            }
        });
    }

    const addItemModal = document.getElementById('add-item-modal');
    if (addItemModal) {
        addItemModal.addEventListener('click', function() {
            const container = document.getElementById('items-container-modal');
            if (!container) return;
            const newCard = document.createElement('div');
            newCard.className = 'item-card';
            newCard.innerHTML = `<div class="row g-2"><div class="col-md-3"><select name="items[${itemCount}][tipo_item]" class="form-select form-select-sm" required><option value="activo">Activo</option><option value="componente">Componente</option></select></div><div class="col-md-7"><input type="text" name="items[${itemCount}][item_descripcion]" class="form-control form-control-sm" placeholder="Descripción del item" required></div><div class="col-md-2"><div class="input-group"><input type="number" name="items[${itemCount}][cantidad]" class="form-control form-control-sm" value="1" min="1" required><button type="button" class="btn btn-sm btn-outline-danger remove-item-modal">×</button></div></div></div>`;
            container.appendChild(newCard);
            itemCount++;
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item-modal') || e.target.closest('.remove-item-modal')) {
            const btn = e.target.classList.contains('remove-item-modal') ? e.target : e.target.closest('.remove-item-modal');
            const card = btn.closest('.item-card');
            const cards = document.querySelectorAll('#items-container-modal .item-card');
            if (cards.length > 1) card.remove();
            else mostrarNotificacion('error', 'Debe haber al menos un item');
        }
        if (e.target.classList.contains('remove-item-editar') || e.target.closest('.remove-item-editar')) {
            const btn = e.target.classList.contains('remove-item-editar') ? e.target : e.target.closest('.remove-item-editar');
            const card = btn.closest('.item-card');
            const cards = document.querySelectorAll('#items-container-editar .item-card');
            if (cards.length > 1) card.remove();
            else mostrarNotificacion('error', 'Debe haber al menos un item');
        }
    });

    const addItemEditar = document.getElementById('add-item-editar');
    if (addItemEditar) {
        addItemEditar.addEventListener('click', function() {
            const container = document.getElementById('items-container-editar');
            if (!container) return;
            const currentItems = container.querySelectorAll('.item-card').length;
            const newCard = document.createElement('div');
            newCard.className = 'item-card';
            newCard.innerHTML = `<div class="row g-2"><div class="col-md-3"><select name="items[${currentItems}][tipo_item]" class="form-select form-select-sm" required><option value="activo">Activo</option><option value="componente">Componente</option></select></div><div class="col-md-7"><input type="text" name="items[${currentItems}][item_descripcion]" class="form-control form-control-sm" placeholder="Descripción del item" required></div><div class="col-md-2"><div class="input-group"><input type="number" name="items[${currentItems}][cantidad]" class="form-control form-control-sm" value="1" min="1" required><button type="button" class="btn btn-sm btn-outline-danger remove-item-editar">×</button></div></div></div>`;
            container.appendChild(newCard);
        });
    }
}

// ==================== ENVÍO DE FORMULARIOS ====================
document.getElementById('formCrearSolicitud')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    let submitBtn = this.querySelector('button[type="submit"]') || this.querySelector('.btn-primary-dark');
    const originalText = submitBtn ? submitBtn.innerHTML : 'Enviando...';
    if (submitBtn) { submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...'; submitBtn.disabled = true; }
    const formData = new FormData(this);
    const responsableId = formData.get('responsable_id');
    if (!responsableId) {
        mostrarNotificacion('error', 'Debe seleccionar un responsable');
        if (submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
        return;
    }
    let hasItems = false;
    for (let pair of formData.entries()) {
        if (pair[0].includes('item_descripcion') && pair[1] && pair[1].trim() !== '') { hasItems = true; break; }
    }
    if (!hasItems) {
        mostrarNotificacion('error', 'Debe agregar al menos un item');
        if (submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
        return;
    }
    try {
        const response = await fetch('/admin/solicitudes/store', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: formData });
        const result = await response.json();
        if (response.ok && result.success) {
            mostrarNotificacion('success', 'Solicitud creada exitosamente');
            const modal = document.getElementById('modalCrear');
            if (modal) bootstrap.Modal.getInstance(modal)?.hide();
            cargarPagina(1);
        } else {
            mostrarNotificacion('error', result.message || 'No se pudo crear la solicitud');
        }
    } catch (error) { console.error('Error:', error); mostrarNotificacion('error', 'Error de conexión'); }
    finally { if (submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; } }
});

// ==================== FORMULARIO DE EDICIÓN ====================
document.getElementById('formEditarSolicitud')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    let submitBtn = this.querySelector('button[type="submit"]') || this.querySelector('.btn-primary-dark');
    const originalText = submitBtn ? submitBtn.innerHTML : 'Actualizando...';
    if (submitBtn) {
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Actualizando...';
        submitBtn.disabled = true;
    }

    const id = document.getElementById('editId')?.value;
    if (!id) { mostrarNotificacion('error', 'ID de solicitud no encontrado'); return; }
    const formData = new FormData(this);

    const responsableId = formData.get('responsable_id');
    if (!responsableId) {
        mostrarNotificacion('error', 'Debe seleccionar un responsable');
        if (submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
        return;
    }

    let hasItems = false;
    for (let pair of formData.entries()) {
        if (pair[0].includes('item_descripcion') && pair[1] && pair[1].trim() !== '') { hasItems = true; break; }
    }
    if (!hasItems) {
        mostrarNotificacion('error', 'Debe agregar al menos un item');
        if (submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
        return;
    }

    try {
        const response = await fetch(`/admin/solicitudes/${id}/update`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
            mostrarNotificacion('success', result.message || 'Solicitud actualizada');
            const modal = document.getElementById('modalEditar');
            if (modal) bootstrap.Modal.getInstance(modal)?.hide();
            cargarPagina(currentPage);
        } else {
            mostrarNotificacion('error', result.message || 'No se pudo actualizar');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
    } finally {

        if (submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
    }
});

function initEventListeners() {
    if (searchInput) searchInput.addEventListener('input', aplicarFiltrosConDebounce);
    if (estadoFilter) estadoFilter.addEventListener('change', aplicarFiltros);
    if (prioridadFilter) prioridadFilter.addEventListener('change', aplicarFiltros);
    if (limpiarBtn) {
        limpiarBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (estadoFilter) estadoFilter.value = '';
            if (prioridadFilter) prioridadFilter.value = '';
            aplicarFiltros();
        });
    }
    const btnConfirmarCancelar = document.getElementById('btnConfirmarCancelar');
    if (btnConfirmarCancelar) btnConfirmarCancelar.addEventListener('click', window.confirmarCancelar);
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminarSolicitud');
    if (btnConfirmarEliminar) btnConfirmarEliminar.addEventListener('click', eliminarSolicitud);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de solicitudes inicializado');
    initEventListeners();
    initCrearEventListeners();
    cargarPagina(1);
});
// ============================================================
// PAGINACIÓN - EXPONER FUNCIÓN GLOBAL
// ============================================================
window.cambiarPagina = function(page) {
    cargarPagina(page);
};
