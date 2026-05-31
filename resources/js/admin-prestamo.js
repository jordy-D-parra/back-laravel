// resources/js/admin-prestamo.js — Gestión de préstamos (solicitudes + registro)

let solicitudesData = [];
let currentPage = 1;
let lastPage = 1;
let perPage = 10;
let totalRegistros = 0;
let timeoutBusqueda = null;
let prestamoAEliminar = null;

const searchInput = document.getElementById('buscarPrestamos');
const estadoFilter = document.getElementById('filtroEstadoPrestamo');
const prioridadFilter = document.getElementById('filtroPrioridadPrestamo');
const limpiarBtn = document.getElementById('limpiarFiltrosPrestamo');

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

let filtros = {
    search: '',
    estado: '',
    prioridad: '',
};

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
    toast.style.cssText = `background: white; border-left: 4px solid ${colores[tipo]}; border-radius: 8px; padding: 12px 16px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); cursor: pointer;`;
    toast.textContent = mensaje;
    toast.onclick = () => toast.remove();
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

function actualizarEstadisticas() {
    const total = totalRegistros || solicitudesData.length;
    const pendientes = solicitudesData.filter((s) => s.estado_solicitud === 'pendiente').length;
    const aprobadas = solicitudesData.filter((s) => s.estado_solicitud === 'aprobada').length;
    const rechazadas = solicitudesData.filter((s) => s.estado_solicitud === 'rechazada').length;

    const set = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    set('statsTotal', total);
    set('statsPendientes', pendientes);
    set('statsAprobadas', aprobadas);
    set('statsRechazadas', rechazadas);
}

function renderizarTabla() {
    const tbody = document.getElementById('tablaPrestamos');
    if (!tbody) return;

    if (solicitudesData.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="10" class="text-center py-4 text-muted">No hay solicitudes de préstamo registradas</td></tr>';
        return;
    }

    let html = '';
    let contador = (currentPage - 1) * perPage + 1;

    for (const s of solicitudesData) {
        const fechaSolicitud = s.fecha_solicitud
            ? new Date(s.fecha_solicitud).toLocaleDateString()
            : 'N/A';
        const fechaRequerida = s.fecha_requerida
            ? new Date(s.fecha_requerida).toLocaleDateString()
            : 'N/A';

        let nombreEntidad = 'No especificado';
        if (s.tipo_solicitante === 'interno' && s.departamento) {
            nombreEntidad = s.departamento.nombre;
        } else if (s.tipo_solicitante === 'externo' && s.institucion) {
            nombreEntidad = s.institucion.nombre;
        }

        const nombreResponsable = s.responsable ? s.responsable.nombre : 'No especificado';
        const prioridadClass = `badge-prioridad-${s.prioridad}`;
        const estadoClass = `badge-estado-${s.estado_solicitud}`;
        const numPrestamos = s.prestamos?.length || 0;

        let acciones = `<button class="btn-accion btn-ver" onclick="verDetallesSolicitud(${s.id})" type="button">Ver</button>`;

        if (
            s.estado_solicitud === 'aprobada' &&
            typeof authUserHasPermission === 'function' &&
            authUserHasPermission('crear-prestamo')
        ) {
            acciones += ` <button class="btn-accion btn-registrar" onclick="registrarPrestamos(${s.id})" type="button">Registrar</button>`;
        }

        if (
            typeof authUserHasPermission === 'function' &&
            authUserHasPermission('crear-prestamo')
        ) {
            acciones += ` <button class="btn-accion btn-editar" onclick="abrirModalPrestamo(${s.id})" type="button">Préstamo</button>`;
        }

        html += `<tr>
            <td>${contador++}</td>
            <td>${fechaSolicitud}</td>
            <td>${escapeHtml(nombreEntidad)}</td>
            <td>${escapeHtml(nombreResponsable)}</td>
            <td>${fechaRequerida}</td>
            <td><span class="badge-prioridad ${prioridadClass}">${s.prioridad}</span></td>
            <td><span class="badge-estado ${estadoClass}">${s.estado_solicitud}</span></td>
            <td class="text-center">${s.detalles?.length || 0}</td>
            <td class="text-center">${numPrestamos}</td>
            <td class="text-end">${acciones}</td>
        </tr>`;
    }

    tbody.innerHTML = html;
}

function renderizarPaginacion() {
    const container = document.getElementById('paginationContainer');
    if (!container) return;

    if (lastPage <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="cambiarPagina(${currentPage - 1}); return false;">«</a></li>`;

    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(lastPage, currentPage + 2);

    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a></li>`;
    }

    html += `<li class="page-item ${currentPage === lastPage ? 'disabled' : ''}"><a class="page-link" href="#" onclick="cambiarPagina(${currentPage + 1}); return false;">»</a></li>`;
    container.innerHTML = html;
}

window.cambiarPagina = function (page) {
    if (page < 1 || page > lastPage) return;
    currentPage = page;
    cargarPagina(page);
};

async function cargarPagina(page) {
    const tbody = document.getElementById('tablaPrestamos');
    if (tbody) {
        tbody.innerHTML =
            '<tr><td colspan="10" class="text-center py-4 text-muted">Cargando...</td></tr>';
    }

    try {
        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: filtros.search,
            estado: filtros.estado,
            prioridad: filtros.prioridad,
        });

        const response = await fetch(`/admin/prestamo?${params.toString()}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) throw new Error('Error al cargar solicitudes');

        const data = await response.json();
        solicitudesData = data.data || [];
        currentPage = data.current_page || 1;
        lastPage = data.last_page || 1;
        perPage = data.per_page || 10;
        totalRegistros = data.total || 0;

        renderizarTabla();
        actualizarEstadisticas();
        renderizarPaginacion();

        const rc = document.getElementById('resultadosCount');
        const tc = document.getElementById('totalRegistrosCount');
        if (rc) rc.textContent = solicitudesData.length;
        if (tc) tc.textContent = totalRegistros;
    } catch (error) {
        console.error(error);
        mostrarNotificacion('error', 'No se pudieron cargar las solicitudes de préstamo');
        if (tbody) {
            tbody.innerHTML =
                '<tr><td colspan="10" class="text-center py-4 text-danger">Error al cargar datos</td></tr>';
        }
    }
}

function aplicarFiltros() {
    filtros = {
        search: searchInput?.value || '',
        estado: estadoFilter?.value || '',
        prioridad: prioridadFilter?.value || '',
    };
    currentPage = 1;
    cargarPagina(1);
}

function aplicarFiltrosConDebounce() {
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(aplicarFiltros, 300);
}

window.verDetallesSolicitud = async function (id) {
    const modalElement = document.getElementById('modalDetalle');
    const modalBody = document.getElementById('detalleContenido');
    const modal = new bootstrap.Modal(modalElement);

    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    modal.show();

    try {
        const response = await fetch(`/admin/solicitudes/${id}/detalles`);
        const data = await response.json();

        if (data.error) {
            modalBody.innerHTML = `<div class="text-danger text-center py-4">${escapeHtml(data.error)}</div>`;
            return;
        }

        let itemsHtml = '';
        if (data.detalles?.length) {
            itemsHtml =
                '<table class="table table-sm mt-2"><thead><tr><th>Tipo</th><th>Descripción</th><th class="text-center">Cant.</th></tr></thead><tbody>';
            for (const item of data.detalles) {
                itemsHtml += `<tr>
                    <td>${item.tipo_item === 'activo' ? 'Activo' : 'Componente'}</td>
                    <td>${escapeHtml(item.item_descripcion)}</td>
                    <td class="text-center">${item.cantidad_solicitada}</td>
                </tr>`;
            }
            itemsHtml += '</tbody></table>';
        } else {
            itemsHtml = '<p class="text-muted">Sin ítems</p>';
        }

        let nombreEntidad = 'No especificado';
        if (data.tipo_solicitante === 'interno' && data.departamento) {
            nombreEntidad = data.departamento.nombre;
        } else if (data.tipo_solicitante === 'externo' && data.institucion) {
            nombreEntidad = data.institucion.nombre;
        }

        modalBody.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6"><small class="text-muted">Entidad</small><div class="fw-semibold">${escapeHtml(nombreEntidad)}</div></div>
                <div class="col-md-6"><small class="text-muted">Responsable</small><div class="fw-semibold">${escapeHtml(data.responsable?.nombre || 'N/A')}</div></div>
                <div class="col-md-4"><small class="text-muted">Prioridad</small><div><span class="badge-prioridad badge-prioridad-${data.prioridad}">${data.prioridad}</span></div></div>
                <div class="col-md-4"><small class="text-muted">Estado</small><div><span class="badge-estado badge-estado-${data.estado_solicitud}">${data.estado_solicitud}</span></div></div>
                <div class="col-md-4"><small class="text-muted">Fecha requerida</small><div>${data.fecha_requerida ? new Date(data.fecha_requerida).toLocaleDateString() : 'N/A'}</div></div>
                <div class="col-12"><small class="text-muted">Justificación</small><div>${escapeHtml(data.justificacion)}</div></div>
                <div class="col-12"><small class="text-muted">Ítems solicitados</small>${itemsHtml}</div>
            </div>`;
    } catch (e) {
        modalBody.innerHTML = '<div class="text-danger text-center py-4">Error al cargar detalles</div>';
    }
};

window.registrarPrestamos = async function (solicitudId) {
    if (!confirm('¿Registrar préstamos para los activos de esta solicitud aprobada?')) return;

    try {
        const response = await fetch(`/admin/prestamo/solicitud/${solicitudId}/registrar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            mostrarNotificacion('error', data.message || 'No se pudo registrar');
            return;
        }

        mostrarNotificacion('success', data.message);
        cargarPagina(currentPage);
    } catch (e) {
        mostrarNotificacion('error', 'Error de conexión');
    }
};

window.abrirModalPrestamo = async function (solicitudId = null) {
    const modal = new bootstrap.Modal(document.getElementById('modalPrestamo'));
    document.getElementById('formPrestamo').reset();
    document.getElementById('prestamoId').value = '';
    document.getElementById('prestamo_solicitud_id').value = solicitudId || '';

    try {
        const res = await fetch('/admin/prestamo/datos-form');
        const datos = await res.json();

        const selResp = document.getElementById('prestamo_responsable_id');
        const selAct = document.getElementById('prestamo_activo_id');
        selResp.innerHTML = '<option value="">Seleccionar...</option>';
        selAct.innerHTML = '<option value="">Seleccionar...</option>';

        for (const r of datos.responsables || []) {
            selResp.innerHTML += `<option value="${r.id}">${escapeHtml(r.nombre)}</option>`;
        }
        for (const a of datos.activos || []) {
            selAct.innerHTML += `<option value="${a.id}">${escapeHtml(a.serial || 'Activo #' + a.id)}</option>`;
        }

        if (solicitudId) {
            const sol = solicitudesData.find((s) => s.id === solicitudId);
            if (sol?.responsable_id) {
                selResp.value = sol.responsable_id;
            }
            if (sol?.fecha_requerida) {
                document.getElementById('prestamo_fecha_salida').value = sol.fecha_requerida.split('T')[0];
            }
            if (sol?.fecha_fin_estimada) {
                document.getElementById('prestamo_fecha_devolucion').value = sol.fecha_fin_estimada.split('T')[0];
            }
        }
    } catch (e) {
        console.error(e);
    }

    modal.show();
};

async function cargarDatosForm() {
    const form = document.getElementById('formPrestamo');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/admin/prestamo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                mostrarNotificacion('error', data.message || 'Error al guardar');
                return;
            }

            mostrarNotificacion('success', 'Préstamo guardado');
            bootstrap.Modal.getInstance(document.getElementById('modalPrestamo'))?.hide();
            cargarPagina(currentPage);
        } catch (err) {
            mostrarNotificacion('error', 'Error al guardar préstamo');
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    searchInput?.addEventListener('input', aplicarFiltrosConDebounce);
    estadoFilter?.addEventListener('change', aplicarFiltros);
    prioridadFilter?.addEventListener('change', aplicarFiltros);
    limpiarBtn?.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        if (estadoFilter) estadoFilter.value = '';
        if (prioridadFilter) prioridadFilter.value = '';
        aplicarFiltros();
    });

    cargarDatosForm();
    cargarPagina(1);
});
