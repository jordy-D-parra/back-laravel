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

// Filtros (solo 3)
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

    const colores = { success: '#28a745', error: '#dc3545', warning: '#ffc107' };
    const toast = document.createElement('div');
    toast.style.cssText = `position: fixed; top: 20px; right: 20px; background: white; border-left: 4px solid ${colores[tipo]}; border-radius: 8px; padding: 12px 16px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000; display: flex; align-items: center; gap: 10px; cursor: pointer;`;
    toast.innerHTML = `<span style="color: ${colores[tipo]}">${tipo === 'success' ? '✓' : '✗'}</span><span>${mensaje}</span>`;
    toast.onclick = () => toast.remove();
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
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

// ==================== ESTADÍSTICAS ====================
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

        // Clases para badges
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
                <button class="btn-accion btn-ver" onclick="verDetalles(${s.id})" style="background: none; border: none; color: #17a2b8; cursor: pointer;">Ver</button>
                ${s.estado_solicitud === 'pendiente' ? `<button class="btn-accion btn-editar" onclick="editarSolicitud(${s.id})" style="background: none; border: none; color: #ffc107; cursor: pointer;">Editar</button>` : ''}
                ${s.estado_solicitud === 'pendiente' ? `<button class="btn-accion btn-cancelar" onclick="abrirModalConfirmacionCancelar(${s.id})" style="background: none; border: none; color: #dc3545; cursor: pointer;">Cancelar</button>` : ''}
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
        document.getElementById('perPageSelect') && (document.getElementById('perPageSelect').value = perPage);
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

// ==================== EDITAR ====================
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

    if (solicitud.departamento_id) document.getElementById('editDepartamentoId').value = solicitud.departamento_id;
    if (solicitud.institucion_id) document.getElementById('editInstitucionId').value = solicitud.institucion_id;

    const editInternoFields = document.getElementById('editInternoFields');
    const editExternoFields = document.getElementById('editExternoFields');
    if (solicitud.tipo_solicitante === 'interno') {
        editInternoFields.style.display = 'block';
        editExternoFields.style.display = 'none';
    } else {
        editInternoFields.style.display = 'none';
        editExternoFields.style.display = 'block';
    }

    const display = document.getElementById('editResponsableDisplay');
    if (solicitud.responsable) {
        display.innerHTML = `<strong>${escapeHtml(solicitud.responsable.nombre)}</strong><br><small class="text-muted">${escapeHtml(solicitud.responsable.cargo || '')}</small>`;
    } else {
        display.innerHTML = '<span class="text-muted">No hay responsable asignado</span>';
    }

    new bootstrap.Modal(document.getElementById('modalEditar')).show();
};

// ==================== CANCELAR ====================
window.abrirModalConfirmacionCancelar = function(id) {
    solicitudACancelar = id;
    new bootstrap.Modal(document.getElementById('modalConfirmacionCancelar')).show();
};

function cerrarModalConfirmacion() {
    bootstrap.Modal.getInstance(document.getElementById('modalConfirmacionCancelar')).hide();
    solicitudACancelar = null;
}

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
            cerrarModalConfirmacion();
            cargarPagina(currentPage);
        } else {
            mostrarNotificacion('error', result.message || 'No se pudo cancelar');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
    }
};

// ==================== CREAR MODAL ====================
window.abrirModalCrear = function() {
    document.getElementById('formCrearSolicitud').reset();
    document.getElementById('tipoSolicitante').value = 'interno';
    document.getElementById('interno-fields').style.display = 'block';
    document.getElementById('externo-fields').style.display = 'none';
    document.getElementById('responsableDisplay').innerHTML = '<span class="text-muted">Selecciona una institución o departamento</span>';
    itemCount = 1;
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

// ==================== ENVÍO DE FORMULARIOS ====================
document.getElementById('formCrearSolicitud')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitSolicitudBtn');
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

// ==================== ITEMS DINÁMICOS ====================
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
    perPageSelect?.addEventListener('change', (e) => {
        perPage = parseInt(e.target.value);
        cargarPagina(1);
    });

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

    document.getElementById('departamentoSelect')?.addEventListener('change', function() {
        const nuevoField = document.getElementById('departamento-nuevo-field');
        nuevoField.style.display = this.value === 'otro' ? 'block' : 'none';
    });

    document.getElementById('institucionSelect')?.addEventListener('change', function() {
        const nuevoField = document.getElementById('institucion-nuevo-field');
        nuevoField.style.display = this.value === 'otro' ? 'block' : 'none';
    });

    document.getElementById('btnConfirmarCancelar')?.addEventListener('click', window.confirmarCancelar);
}

// ==================== INICIALIZACIÓN ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de solicitudes inicializado');
    initEventListeners();
    cargarPagina(1);
});
