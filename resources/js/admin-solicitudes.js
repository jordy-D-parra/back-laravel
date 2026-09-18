// resources/js/admin-solicitudes.js
// VERSIÓN COMPLETA CORREGIDA - Paginación + Aprobar + Editar funcionales + Modal rediseñado

document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    // VARIABLES GLOBALES
    // ============================================================
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const SVG_ICONS = {
        ver: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`,
        editar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
        cancelar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
        aprobar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>`,
        rechazar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
        eliminar: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>`
    };

    let solicitudesData = [];
    let currentPage = 1;
    let lastPage = 1;
    let perPage = 10;
    let totalRegistros = 0;
    let timeoutBusqueda = null;
    let solicitudAEliminar = null;
    let solicitudACancelar = null;
    let itemCount = 1;
    let intervaloNotificaciones = null;
    let correoActual = null;
    let itemWizardCount = 0;

    const searchInput = document.getElementById('searchInput');

    let filtros = {
        search: '',
        estado: '',
        prioridad: ''
    };

    // ============================================================
    // UTILIDADES
    // ============================================================
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatearFecha(fecha) {
        if (!fecha) return '-';
        const d = new Date(fecha);
        if (isNaN(d.getTime())) return fecha;
        return d.toLocaleDateString('es-ES', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        });
    }

    function formatearFechaHora(fecha) {
        if (!fecha) return '-';
        const d = new Date(fecha);
        if (isNaN(d.getTime())) return fecha;
        return d.toLocaleDateString('es-ES', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function mostrarNotificacion(tipo, mensaje) {
        const container = document.getElementById('notification-container');
        if (!container) return;

        const colores = { success: '#28a745', error: '#dc3545', warning: '#ffc107', info: '#17a2b8' };
        const iconos = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: ${colores[tipo]}; color: white;
            border-radius: 10px; padding: 12px 16px; margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex; align-items: center; gap: 12px;
            cursor: pointer; animation: slideIn 0.3s ease-out;
            white-space: pre-line; font-size: 0.9rem;
        `;
        toast.innerHTML = `<span style="font-size:1.2rem;">${iconos[tipo] || 'ℹ️'}</span><span style="flex:1">${mensaje}</span>`;

        container.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // ============================================================
    // RENDERIZAR TABLA
    // ============================================================
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
            const fechaSolicitud = formatearFecha(s.fecha_solicitud || s.created_at);
            const fechaRequerida = formatearFecha(s.fecha_requerida);

            let nombreEntidad = 'No especificado';
            if (s.tipo_solicitante === 'interno' && s.departamento) nombreEntidad = s.departamento.nombre;
            else if (s.tipo_solicitante === 'externo' && s.institucion) nombreEntidad = s.institucion.nombre;
            else if (s.entidad_nombre) nombreEntidad = s.entidad_nombre;

            const nombreResponsable = s.responsable ? s.responsable.nombre : (s.responsable_nombre || 'No especificado');
            const prioridad = (s.prioridad || 'normal').toLowerCase();
            const estado = (s.estado_solicitud || s.estado || 'pendiente').toLowerCase();
            const esNoLeida = s.leida_por_admin === false || s.leida_por_admin === 0;
            const badgeNueva = esNoLeida ? '<span class="badge-nueva">NUEVA</span>' : '';
            const itemsCount = s.detalles?.length ?? s.items_count ?? (s.items ? s.items.length : 0);

            const puedeEditar = window.authUserHasPermission('editar-solicitud') && estado === 'pendiente';
            const puedeAprobar = window.authUserHasPermission('aprobar-solicitudes') && estado === 'pendiente';
            const puedeCancelar = window.authUserHasPermission('cancelar-solicitud') && estado === 'pendiente';
            const puedeEliminar = window.authUserHasPermission('aprobar-solicitudes');

            html += `<tr class="${esNoLeida ? 'solicitud-no-leida' : ''}" data-id="${s.id}">
                <td class="px-3 py-2">
                    ${contador++}
                    ${badgeNueva}
                </td>
                <td class="px-3 py-2">${fechaSolicitud}</td>
                <td class="px-3 py-2">${escapeHtml(nombreEntidad)}</td>
                <td class="px-3 py-2">${escapeHtml(nombreResponsable)}</td>
                <td class="px-3 py-2">${fechaRequerida}</td>
                <td class="px-3 py-2"><span class="badge-prioridad badge-prioridad-${prioridad}">${prioridad}</span></td>
                <td class="px-3 py-2"><span class="badge-estado badge-estado-${estado}">${estado}</span></td>
                <td class="px-3 py-2 text-center">${itemsCount}</td>
                <td class="px-3 py-2 text-end">
                    <button class="btn-action btn-ver" onclick="verDetalles(${s.id})" title="Ver">${SVG_ICONS.ver}</button>
                    ${puedeEditar ? `<button class="btn-action btn-editar" onclick="editarSolicitud(${s.id})" title="Editar">${SVG_ICONS.editar}</button>` : ''}
                    ${puedeCancelar ? `<button class="btn-action btn-cancelar" onclick="abrirModalConfirmacionCancelar(${s.id})" title="Cancelar">${SVG_ICONS.cancelar}</button>` : ''}
                    ${puedeAprobar ? `<button class="btn-action btn-aprobar" onclick="abrirModalAprobarSolicitud(${s.id})" title="Aprobar">${SVG_ICONS.aprobar}</button>` : ''}
                    ${puedeAprobar ? `<button class="btn-action btn-rechazar" onclick="rechazarSolicitud(${s.id})" title="Rechazar">${SVG_ICONS.rechazar}</button>` : ''}
                    ${puedeEliminar ? `<button class="btn-action btn-eliminar" onclick="confirmarEliminarSolicitud(${s.id})" title="Eliminar">${SVG_ICONS.eliminar}</button>` : ''}
                </td>
            </tr>`;
        }

        tbody.innerHTML = html;
    }

    // ============================================================
    // PAGINACIÓN
    // ============================================================
    function renderizarPaginacion() {
        const container = document.getElementById('paginationContainer');
        if (!container) return;

        if (lastPage <= 1) { container.innerHTML = ''; return; }

        let html = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${currentPage - 1})">«</a>
        </li>`;

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(lastPage, currentPage + 2);

        if (startPage > 1) {
            html += `<li class="page-item ${currentPage === 1 ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(1)">1</a></li>`;
            if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${i})">${i}</a>
            </li>`;
        }

        if (endPage < lastPage) {
            if (endPage < lastPage - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            html += `<li class="page-item ${currentPage === lastPage ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${lastPage})">${lastPage}</a></li>`;
        }

        html += `<li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${currentPage + 1})">»</a>
        </li>`;

        container.innerHTML = html;
    }

    async function cargarPagina(page) {
        page = page || 1;
        const tbody = document.getElementById('tablaBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span class="ms-2 text-muted">Cargando...</span>
            </td></tr>`;
        }

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

            if (data.data && Array.isArray(data.data)) {
                solicitudesData = data.data;
                currentPage = data.current_page || 1;
                lastPage = data.last_page || 1;
                perPage = data.per_page || 10;
                totalRegistros = data.total || 0;
            } else if (Array.isArray(data)) {
                solicitudesData = data;
                currentPage = 1;
                lastPage = 1;
                totalRegistros = data.length;
            } else if (data.solicitudes) {
                solicitudesData = data.solicitudes;
                currentPage = data.current_page || 1;
                lastPage = data.last_page || 1;
                totalRegistros = data.total || data.solicitudes.length;
            } else {
                solicitudesData = [];
                currentPage = 1;
                lastPage = 1;
                totalRegistros = 0;
            }

            renderizarTabla();
            actualizarEstadisticas();
            renderizarPaginacion();

            const elResultados = document.getElementById('resultadosCount');
            const elTotal = document.getElementById('totalRegistrosCount');
            if (elResultados) elResultados.textContent = solicitudesData.length;
            if (elTotal) elTotal.textContent = totalRegistros;

        } catch (error) {
            console.error('Error:', error);
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-danger">
                    Error al cargar: ${escapeHtml(error.message)}
                </td></tr>`;
            }
            mostrarNotificacion('error', 'No se pudieron cargar las solicitudes');
        }
    }

    window.cambiarPagina = function(page) {
        if (page < 1 || page > lastPage) return;
        if (page === currentPage) return;
        currentPage = page;
        cargarPagina(currentPage);
        document.querySelector('.table-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    window.cargarPagina = cargarPagina;

    function actualizarEstadisticas() {
        const total = totalRegistros || solicitudesData.length;
        let pendientes = 0, aprobadas = 0, rechazadas = 0;

        solicitudesData.forEach(s => {
            const e = (s.estado_solicitud || s.estado || 'pendiente').toLowerCase();
            if (e === 'pendiente') pendientes++;
            else if (e === 'aprobada') aprobadas++;
            else if (e === 'rechazada') rechazadas++;
        });

        const elTotal = document.getElementById('statsTotal');
        const elPendientes = document.getElementById('statsPendientes');
        const elAprobadas = document.getElementById('statsAprobadas');
        const elRechazadas = document.getElementById('statsRechazadas');

        if (elTotal) elTotal.textContent = total;
        if (elPendientes) elPendientes.textContent = pendientes;
        if (elAprobadas) elAprobadas.textContent = aprobadas;
        if (elRechazadas) elRechazadas.textContent = rechazadas;
    }

    function aplicarFiltros() {
        filtros = {
            search: searchInput?.value || '',
            estado: '',
            prioridad: ''
        };
        currentPage = 1;
        cargarPagina(1);
    }

    function aplicarFiltrosConDebounce() {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(() => aplicarFiltros(), 300);
    }

    // ============================================================
    // RESPONSABLE
    // ============================================================
    function initResponsableEvents() {
        const deptoSelect = document.getElementById('departamentoSelect');
        if (deptoSelect && !deptoSelect.dataset.bound) {
            deptoSelect.dataset.bound = '1';
            deptoSelect.addEventListener('change', function() {
                const id = this.value;
                const display = document.getElementById('responsableDisplay');
                const hiddenInput = document.getElementById('responsable_id_hidden');
                if (id && id !== 'otro' && id !== '') {
                    cargarResponsablePorDepartamento(id);
                } else {
                    if (display) display.innerHTML = '<span class="text-muted">Seleccione una opción</span>';
                    if (hiddenInput) hiddenInput.value = '';
                }
            });
        }

        const instSelect = document.getElementById('institucionSelect');
        if (instSelect && !instSelect.dataset.bound) {
            instSelect.dataset.bound = '1';
            instSelect.addEventListener('change', function() {
                const id = this.value;
                const display = document.getElementById('responsableDisplay');
                const hiddenInput = document.getElementById('responsable_id_hidden');
                if (id && id !== 'otro' && id !== '') {
                    cargarResponsablePorInstitucion(id);
                } else {
                    if (display) display.innerHTML = '<span class="text-muted">Seleccione una opción</span>';
                    if (hiddenInput) hiddenInput.value = '';
                }
            });
        }
    }

    async function cargarResponsablePorDepartamento(departamentoId) {
        try {
            const response = await fetch(`/api/departamento/${departamentoId}/responsable`);
            const data = await response.json();
            const display = document.getElementById('responsableDisplay');
            const hiddenInput = document.getElementById('responsable_id_hidden');

            if (data.responsable) {
                if (display) {
                    display.innerHTML = `
                        <div>
                            <strong style="color:#1e3c72;">${escapeHtml(data.responsable.nombre)}</strong>
                            <br><small class="text-muted">${escapeHtml(data.responsable.cargo || 'Sin cargo')}</small>
                            <br><small class="text-muted">📞 ${escapeHtml(data.responsable.telefono || 'Sin teléfono')}</small>
                        </div>
                    `;
                }
                if (hiddenInput) hiddenInput.value = data.responsable.id;
            } else {
                if (display) display.innerHTML = '<span class="text-warning">No hay responsable asignado a este departamento</span>';
                if (hiddenInput) hiddenInput.value = '';
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
            const hiddenInput = document.getElementById('responsable_id_hidden');

            if (data.responsable) {
                if (display) {
                    display.innerHTML = `
                        <div>
                            <strong style="color:#1e3c72;">${escapeHtml(data.responsable.nombre)}</strong>
                            <br><small class="text-muted">${escapeHtml(data.responsable.cargo || 'Sin cargo')}</small>
                            <br><small class="text-muted">📞 ${escapeHtml(data.responsable.telefono || 'Sin teléfono')}</small>
                        </div>
                    `;
                }
                if (hiddenInput) hiddenInput.value = data.responsable.id;
            } else {
                if (display) display.innerHTML = '<span class="text-warning">No hay responsable asignado a esta institución</span>';
                if (hiddenInput) hiddenInput.value = '';
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // ============================================================
    // MODAL CREAR
    // ============================================================
    window.abrirModalCrear = function() {
        const form = document.getElementById('formCrearSolicitud');
        if (form) form.reset();
        document.getElementById('tipoSolicitante').value = 'interno';
        document.getElementById('interno-fields').style.display = 'block';
        document.getElementById('externo-fields').style.display = 'none';
        document.getElementById('responsableDisplay').innerHTML = '<span class="text-muted">Seleccione un departamento o institución</span>';
        document.getElementById('responsable_id_hidden').value = '';
        itemCount = 1;
        const modal = new bootstrap.Modal(document.getElementById('modalCrear'));
        modal.show();
        setTimeout(() => initResponsableEvents(), 100);
    };

    // ============================================================
    // VER DETALLES (REDISEÑADO)
    // ============================================================
    window.verDetalles = async function(id) {
        const modalElement = document.getElementById('modalDetalles');
        if (!modalElement) return;

        const modalBody = document.getElementById('modalDetallesBody');
        const modalSubtitulo = document.getElementById('detalleSubtitulo');
        const modal = new bootstrap.Modal(modalElement);

        // Estado de carga
        if (modalSubtitulo) modalSubtitulo.textContent = 'Cargando información...';
        if (modalBody) {
            modalBody.innerHTML = `
                <div class="detalle-loading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Cargando detalles de la solicitud...</p>
                </div>
            `;
        }
        modal.show();

        try {
            const response = await fetch(`/admin/solicitudes/${id}/detalles`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            if (!modalBody) return;
            if (data.error) {
                modalBody.innerHTML = `
                    <div class="alert alert-danger d-flex align-items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <div>${escapeHtml(data.error)}</div>
                    </div>
                `;
                return;
            }

            // ============ HELPERS LOCALES ============
            const estado = (data.estado_solicitud || 'pendiente').toLowerCase();
            const prioridad = (data.prioridad || 'normal').toLowerCase();
            const codigo = data.codigo || ('SOL-' + String(data.id).padStart(6, '0'));

            const tipoSolicitante = data.tipo_solicitante === 'interno' ? 'Interno' : 'Externo';
            let nombreEntidad = 'No especificado';
            if (data.tipo_solicitante === 'interno' && data.departamento) {
                nombreEntidad = data.departamento.nombre;
            } else if (data.tipo_solicitante === 'externo' && data.institucion) {
                nombreEntidad = data.institucion.nombre;
            }

            const nombreResponsable = data.responsable?.nombre || 'No especificado';
            const cargoResponsable = data.responsable?.cargo || '';
            const telefonoResponsable = data.responsable?.telefono || '';
            const emailResponsable = data.responsable?.email || '';

            const usuarioNombre = data.usuario?.trabajador
                ? `${data.usuario.trabajador.nombre || ''} ${data.usuario.trabajador.apellido || ''}`.trim()
                : (data.usuario?.usuario || 'No especificado');
            const usuarioEmail = data.usuario?.trabajador?.email || '';

            // Actualizar subtítulo del header
            if (modalSubtitulo) {
                modalSubtitulo.textContent = `${codigo} · Creada el ${formatearFecha(data.fecha_solicitud)}`;
            }

            // ============ HTML DE ITEMS ============
            let itemsHtml = '';
            if (data.detalles && data.detalles.length > 0) {
                itemsHtml = `
                    <table class="detalle-items-table">
                        <thead>
                            <tr>
                                <th style="width: 130px;">Tipo</th>
                                <th>Descripción</th>
                                <th class="text-center" style="width: 100px;">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                data.detalles.forEach(item => {
                    const tipoClase = item.tipo_item === 'activo' ? 'activo' : 'componente';
                    const tipoLabel = item.tipo_item === 'activo' ? 'Activo' : 'Componente';
                    itemsHtml += `
                        <tr>
                            <td><span class="detalle-item-tipo ${tipoClase}">${tipoLabel}</span></td>
                            <td>${escapeHtml(item.item_descripcion || '-')}</td>
                            <td class="text-center"><span class="detalle-item-cantidad">${item.cantidad_solicitada || 0}</span></td>
                        </tr>
                    `;
                });
                itemsHtml += `</tbody></table>`;
            } else {
                itemsHtml = `
                    <div class="detalle-empty">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                        </svg>
                        <p class="mb-0">No hay items registrados en esta solicitud</p>
                    </div>
                `;
            }

            // ============ HTML COMPLETO ============
            const html = `
                <div class="detalle-hero-card">
                    <div class="detalle-hero-left">
                        <div class="detalle-hero-icon">#${data.id}</div>
                        <div class="detalle-hero-info">
                            <h3>${escapeHtml(codigo)}</h3>
                            <p>Creada el ${formatearFecha(data.fecha_solicitud)}</p>
                        </div>
                    </div>
                    <div class="detalle-hero-badges">
                        <span class="detalle-badge prioridad-${prioridad}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 2L2 22h20L12 2z"/>
                            </svg>
                            ${escapeHtml(prioridad)}
                        </span>
                        <span class="detalle-badge estado-${estado}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                            ${escapeHtml(estado)}
                        </span>
                    </div>
                </div>

                <div class="detalle-section">
                    <div class="detalle-section-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Información del Solicitante
                    </div>
                    <div class="detalle-info-grid">
                        <div class="detalle-info-card">
                            <div class="detalle-info-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                Solicitante
                            </div>
                            <div class="detalle-info-value">${escapeHtml(usuarioNombre || 'No especificado')}</div>
                        </div>
                        <div class="detalle-info-card">
                            <div class="detalle-info-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                                Email
                            </div>
                            <div class="detalle-info-value ${usuarioEmail ? '' : 'muted'}">${escapeHtml(usuarioEmail || 'No registrado')}</div>
                        </div>
                        <div class="detalle-info-card">
                            <div class="detalle-info-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <rect x="3" y="4" width="18" height="16" rx="2"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                Tipo Solicitante
                            </div>
                            <div class="detalle-info-value">${escapeHtml(tipoSolicitante)}</div>
                        </div>
                        <div class="detalle-info-card">
                            <div class="detalle-info-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M3 21h18"/>
                                    <path d="M5 21V7l8-4v18"/>
                                    <path d="M19 21V11l-6-4"/>
                                </svg>
                                Entidad
                            </div>
                            <div class="detalle-info-value">${escapeHtml(nombreEntidad)}</div>
                        </div>
                        <div class="detalle-info-card">
                            <div class="detalle-info-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                Responsable
                            </div>
                            <div class="detalle-info-value">
                                ${escapeHtml(nombreResponsable)}
                                ${cargoResponsable ? `<div style="font-size:0.78rem; color:#64748b; font-weight:500; margin-top:2px;">${escapeHtml(cargoResponsable)}</div>` : ''}
                            </div>
                        </div>
                        <div class="detalle-info-card">
                            <div class="detalle-info-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                Contacto Responsable
                            </div>
                            <div class="detalle-info-value ${(telefonoResponsable || emailResponsable) ? '' : 'muted'}">
                                ${telefonoResponsable ? `📞 ${escapeHtml(telefonoResponsable)}` : ''}
                                ${telefonoResponsable && emailResponsable ? '<br>' : ''}
                                ${emailResponsable ? `✉️ ${escapeHtml(emailResponsable)}` : ''}
                                ${(!telefonoResponsable && !emailResponsable) ? 'Sin datos de contacto' : ''}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detalle-section">
                    <div class="detalle-section-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Fechas Clave
                    </div>
                    <div class="detalle-timeline">
                        <div class="detalle-timeline-item fecha-solicitud">
                            <div class="detalle-timeline-date">${formatearFecha(data.fecha_solicitud)}</div>
                            <div class="detalle-timeline-label">Fecha de solicitud</div>
                        </div>
                        <div class="detalle-timeline-item fecha-requerida">
                            <div class="detalle-timeline-date">${formatearFecha(data.fecha_requerida)}</div>
                            <div class="detalle-timeline-label">Fecha requerida</div>
                        </div>
                        <div class="detalle-timeline-item fecha-fin">
                            <div class="detalle-timeline-date">${formatearFecha(data.fecha_fin_estimada)}</div>
                            <div class="detalle-timeline-label">Fecha fin estimada</div>
                        </div>
                    </div>
                </div>

                <div class="detalle-section">
                    <div class="detalle-section-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        Justificación
                    </div>
                    <div class="detalle-justificacion">${escapeHtml(data.justificacion || 'No especificada')}</div>
                </div>

                ${data.observaciones ? `
                <div class="detalle-section">
                    <div class="detalle-section-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        Observaciones
                    </div>
                    <div class="detalle-justificacion" style="border-left-color: #f59e0b;">${escapeHtml(data.observaciones)}</div>
                </div>
                ` : ''}

                <div class="detalle-section">
                    <div class="detalle-section-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                        Items Solicitados (${data.detalles?.length || 0})
                    </div>
                    ${itemsHtml}
                </div>
            `;

            modalBody.innerHTML = html;

        } catch (error) {
            console.error('Error:', error);
            if (modalBody) {
                modalBody.innerHTML = `
                    <div class="alert alert-danger d-flex align-items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <div>Error al cargar los detalles: ${escapeHtml(error.message)}</div>
                    </div>
                `;
            }
        }
    };

    // ============================================================
    // MODAL APROBAR
    // ============================================================
    window.abrirModalAprobarSolicitud = function(id) {
        const form = document.getElementById('formAprobarSolicitud');
        if (form) form.reset();
        document.getElementById('aprobarObservaciones').value = '';
        document.getElementById('aprobarFechaAdvertencia').style.display = 'none';
        document.getElementById('aprobarAlertaStock').style.display = 'none';

        fetch(`/admin/solicitudes/${id}/detalles`, {
            headers: { Accept: 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data && (data.id || data.success)) {
                const d = data;
                document.getElementById('aprobarCodigo').textContent = d.codigo || '#' + d.id;
                document.getElementById('aprobarPrioridad').textContent = d.prioridad || 'Normal';
                document.getElementById('aprobarSolicitante').textContent =
                    d.usuario?.trabajador?.nombre || d.usuario?.usuario || d.responsable?.nombre || 'No especificado';

                if (d.fecha_requerida) {
                    const fechaFormateada = String(d.fecha_requerida).substring(0, 10);
                    document.getElementById('aprobarFechaRequerida').value = fechaFormateada;
                    verificarFechaPasada(fechaFormateada);
                }
                if (d.fecha_fin_estimada) {
                    document.getElementById('aprobarFechaFin').value = String(d.fecha_fin_estimada).substring(0, 10);
                }
                document.getElementById('aprobarSolicitudId').value = d.id;

                const modal = new bootstrap.Modal(document.getElementById('modalAprobarSolicitud'));
                modal.show();
            } else {
                mostrarNotificacion('error', 'No se pudieron cargar los datos de la solicitud');
            }
        })
        .catch(error => {
            console.error('Error al cargar solicitud:', error);
            mostrarNotificacion('error', 'Error al cargar los datos de la solicitud');
        });
    };

    function verificarFechaPasada(fecha) {
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        const fechaSeleccionada = new Date(fecha + 'T00:00:00');
        const advertencia = document.getElementById('aprobarFechaAdvertencia');
        if (fechaSeleccionada < hoy) {
            if (advertencia) advertencia.style.display = 'block';
        } else {
            if (advertencia) advertencia.style.display = 'none';
        }
    }

    // ============================================================
    // EDITAR SOLICITUD
    // ============================================================
    window.editarSolicitud = async function(id) {
        try {
            const response = await fetch(`/admin/solicitudes/${id}/detalles`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Error al obtener la solicitud');

            const data = await response.json();
            const s = data.solicitud || data;

            document.getElementById('editarSolicitudId').value = s.id;
            document.getElementById('editarPrioridad').value = s.prioridad || 'normal';
            document.getElementById('editarEstado').value = s.estado_solicitud || s.estado || 'pendiente';

            const fechaReq = s.fecha_requerida ? String(s.fecha_requerida).substring(0, 10) : '';
            const fechaFin = s.fecha_fin_estimada ? String(s.fecha_fin_estimada).substring(0, 10) : '';
            document.getElementById('editarFechaRequerida').value = fechaReq;
            document.getElementById('editarFechaFin').value = fechaFin;
            document.getElementById('editarJustificacion').value = s.justificacion || '';
            document.getElementById('editarObservaciones').value = s.observaciones || '';

            new bootstrap.Modal(document.getElementById('modalEditarSolicitud')).show();
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error al cargar la solicitud: ' + error.message);
        }
    };

    // ============================================================
    // RECHAZAR SOLICITUD
    // ============================================================
    window.rechazarSolicitud = async function(id) {
        const motivo = prompt('Motivo del rechazo:');
        if (!motivo) return;

        try {
            const response = await fetch(`/admin/solicitudes/${id}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ motivo })
            });
            const result = await response.json();
            if (response.ok && result.success) {
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

    // ============================================================
    // CANCELAR SOLICITUD
    // ============================================================
    window.abrirModalConfirmacionCancelar = function(id) {
        solicitudACancelar = id;
        const modal = document.getElementById('modalConfirmacionCancelar');
        if (modal) new bootstrap.Modal(modal).show();
    };

    window.confirmarCancelar = async function() {
        if (!solicitudACancelar) return;
        try {
            const response = await fetch(`/admin/solicitudes/${solicitudACancelar}/cancel`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
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
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error de conexión');
        }
    };

    // ============================================================
    // ELIMINAR SOLICITUD
    // ============================================================
    window.confirmarEliminarSolicitud = function(id) {
        solicitudAEliminar = id;
        const el = document.getElementById('deleteSolicitudNombre');
        if (el) el.textContent = `#${id}`;
        const modal = document.getElementById('modalEliminarSolicitud');
        if (modal) new bootstrap.Modal(modal).show();
    };

    window.eliminarSolicitud = async function() {
        if (!solicitudAEliminar) return;
        try {
            const response = await fetch(`/admin/solicitudes/${solicitudAEliminar}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (response.ok && result.success) {
                mostrarNotificacion('success', 'Solicitud eliminada exitosamente');
                const modal = document.getElementById('modalEliminarSolicitud');
                if (modal) bootstrap.Modal.getInstance(modal)?.hide();
                cargarPagina(currentPage);
            } else {
                mostrarNotificacion('error', result.message || 'No se pudo eliminar');
            }
            solicitudAEliminar = null;
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error de conexión');
        }
    };

    // ============================================================
    // EVENTOS
    // ============================================================
    if (searchInput) searchInput.addEventListener('input', aplicarFiltrosConDebounce);

    const btnConfirmarCancelar = document.getElementById('btnConfirmarCancelar');
    if (btnConfirmarCancelar) btnConfirmarCancelar.addEventListener('click', window.confirmarCancelar);

    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminarSolicitud');
    if (btnConfirmarEliminar) btnConfirmarEliminar.addEventListener('click', window.eliminarSolicitud);

    const tipoSolicitante = document.getElementById('tipoSolicitante');
    if (tipoSolicitante) {
        tipoSolicitante.addEventListener('change', function() {
            const internoFields = document.getElementById('interno-fields');
            const externoFields = document.getElementById('externo-fields');
            if (this.value === 'interno') {
                internoFields.style.display = 'block';
                externoFields.style.display = 'none';
            } else {
                internoFields.style.display = 'none';
                externoFields.style.display = 'block';
            }
            document.getElementById('responsableDisplay').innerHTML = '<span class="text-muted">Seleccione una opción</span>';
            document.getElementById('responsable_id_hidden').value = '';
        });
    }

    // Agregar item al modal crear
    document.getElementById('add-item-modal')?.addEventListener('click', function() {
        const container = document.getElementById('items-container-modal');
        const idx = container.querySelectorAll('.item-card').length;
        const html = `
            <div class="item-card mt-2">
                <div class="row g-2">
                    <div class="col-md-3">
                        <select name="items[${idx}][tipo_item]" class="form-select form-select-sm" required>
                            <option value="activo">Activo</option>
                            <option value="componente">Componente</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" name="items[${idx}][item_descripcion]" class="form-control form-control-sm" placeholder="Descripción" required>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <input type="number" name="items[${idx}][cantidad]" class="form-control form-control-sm" value="1" min="1" required>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-modal">×</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    });

    document.getElementById('items-container-modal')?.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item-modal')) {
            const cards = this.querySelectorAll('.item-card');
            if (cards.length > 1) {
                e.target.closest('.item-card').remove();
            } else {
                mostrarNotificacion('warning', 'Debe haber al menos un item');
            }
        }
    });

    // ============================================================
    // ENVÍO FORM CREAR
    // ============================================================
    document.getElementById('formCrearSolicitud')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';

        fetch('/admin/solicitudes', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: new FormData(this)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('success', data.message || 'Solicitud creada');
                bootstrap.Modal.getInstance(document.getElementById('modalCrear'))?.hide();
                cargarPagina(1);
            } else {
                let msg = data.message || 'Error al crear';
                if (data.errors) msg += ': ' + Object.values(data.errors).flat().join(', ');
                mostrarNotificacion('error', msg);
            }
        })
        .catch(error => mostrarNotificacion('error', 'Error de conexión: ' + error.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = original;
        });
    });

    // ============================================================
    // ENVÍO FORM APROBAR
    // ============================================================
    document.getElementById('formAprobarSolicitud')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('aprobarSolicitudId').value;
        if (!id) { mostrarNotificacion('error', 'Error: No se encontró el ID de la solicitud'); return; }

        const submitBtn = document.getElementById('btnAprobarSolicitud');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';
        submitBtn.disabled = true;

        fetch(`/admin/solicitudes/${id}/approve`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalAprobarSolicitud'))?.hide();
                let mensaje = data.message || 'Solicitud aprobada exitosamente';
                if (data.fecha_pasada) mensaje += ' ⚠️ La fecha requerida ya pasó.';
                mostrarNotificacion('success', mensaje);
                cargarPagina(currentPage);
            } else if (data.auto_rechazada) {
                bootstrap.Modal.getInstance(document.getElementById('modalAprobarSolicitud'))?.hide();

                const listaFaltantes = document.getElementById('listaFaltantes');
                let htmlFaltantes = '';
                if (data.faltantes) {
                    data.faltantes.forEach(f => {
                        const tipo = f.tipo === 'activo' ? 'Equipo' : 'Componente';
                        htmlFaltantes += `
                            <div class="item-faltante">
                                <div class="nombre">${tipo}: ${escapeHtml(f.nombre)}</div>
                                <div class="detalles">Solicitado: ${f.solicitado} | Disponible: ${f.disponible}${f.serial && f.serial !== 'N/A' ? ' | Serial: ' + f.serial : ''}</div>
                            </div>
                        `;
                    });
                }
                listaFaltantes.innerHTML = htmlFaltantes || '<p class="text-muted">No hay detalles disponibles.</p>';

                new bootstrap.Modal(document.getElementById('modalStockError')).show();
                mostrarNotificacion('warning', 'Solicitud rechazada automáticamente por falta de stock');
                cargarPagina(currentPage);
            } else {
                mostrarNotificacion('error', data.message || 'Error al aprobar la solicitud');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error de conexión al servidor');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // ============================================================
    // ENVÍO FORM EDITAR
    // ============================================================
    document.getElementById('formEditarSolicitud')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnGuardarEdicion');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';

        const formData = new FormData(this);
        const id = document.getElementById('editarSolicitudId').value;

        formData.append('_method', 'PUT');

        fetch(`/admin/solicitudes/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('success', data.message || 'Solicitud actualizada');
                bootstrap.Modal.getInstance(document.getElementById('modalEditarSolicitud'))?.hide();
                cargarPagina(currentPage);
            } else {
                let msg = data.message || 'Error al actualizar';
                if (data.errors) msg += ': ' + Object.values(data.errors).flat().join(', ');
                mostrarNotificacion('error', msg);
            }
        })
        .catch(error => mostrarNotificacion('error', 'Error de conexión: ' + error.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = original;
        });
    });

    // ============================================================
    // CORREOS
    // ============================================================
    async function cargarCorreos() {
        const buscar = document.getElementById('buscarCorreo')?.value || '';
        const filtro = document.getElementById('filtroCorreo')?.value || '';

        const params = new URLSearchParams();
        if (buscar) params.append('buscar', buscar);
        if (filtro) params.append('filtro', filtro);

        try {
            const response = await fetch(`/admin/solicitudes/correos/lista?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            renderizarCorreos(data.data || []);
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function renderizarCorreos(correos) {
        const container = document.getElementById('listaCorreos');
        if (!container) return;

        if (!correos.length) {
            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M22 7l-10 7L2 7"/>
                    </svg>
                    <p class="mt-3">No hay correos recibidos</p>
                    <p class="small">Haz clic en "Revisar ahora" para buscar nuevos correos</p>
                </div>
            `;
            return;
        }

        let html = '';
        correos.forEach(c => {
            const clases = ['correo-item'];
            if (!c.leido) clases.push('no-leido');
            if (c.procesado) clases.push('procesado');
            const badge = !c.leido ? '<span class="badge-nueva">NUEVO</span>' : '';

            html += `
                <div class="${clases.join(' ')}" onclick="abrirCorreo(${c.id})">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1">
                            <div class="fw-bold">${escapeHtml(c.from_name || c.from_email)} ${badge}</div>
                            <div class="text-muted small">${escapeHtml(c.from_email)}</div>
                            <div class="fw-semibold mt-1">${escapeHtml(c.subject || '(Sin asunto)')}</div>
                            <div class="text-muted small mt-1">${escapeHtml((c.body_text || '').substring(0, 150))}...</div>
                        </div>
                        <div class="text-end ms-3">
                            <div class="small text-muted">${formatearFechaHora(c.received_at)}</div>
                        </div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    async function actualizarBadgeCorreos() {
        try {
            const response = await fetch('/admin/solicitudes/correos/contador', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                const badge = document.getElementById('tabCorreosBadge');
                if (badge && data.no_procesados > 0) {
                    badge.textContent = data.no_procesados;
                    badge.style.display = 'inline-block';
                } else if (badge) {
                    badge.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    window.revisarCorreos = async function() {
        const btn = document.getElementById('btnRevisarCorreos');
        if (!btn) return;
        const original = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Revisando...';
        btn.disabled = true;

        try {
            const response = await fetch('/admin/solicitudes/correos/revisar', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await response.json();
            mostrarNotificacion('info', data.message || 'Revisión completada');
            cargarCorreos();
            actualizarBadgeCorreos();
        } catch (error) {
            mostrarNotificacion('error', 'Error al revisar correos: ' + error.message);
        } finally {
            btn.innerHTML = original;
            btn.disabled = false;
        }
    };

    window.abrirCorreo = async function(id) {
        try {
            const response = await fetch(`/admin/solicitudes/correos/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (!data.success) { mostrarNotificacion('error', 'Error al cargar el correo'); return; }

            correoActual = data.data;

            document.getElementById('correoFrom').textContent = `${correoActual.from_name || ''} <${correoActual.from_email}>`;
            document.getElementById('correoFecha').textContent = formatearFechaHora(correoActual.received_at);
            document.getElementById('correoAsunto').textContent = correoActual.subject || '(Sin asunto)';
            document.getElementById('correoCuerpo').textContent = correoActual.body_text || '(Sin contenido)';

            if (correoActual.procesado) {
                document.getElementById('botonIniciarWizard').innerHTML = `
                    <div class="alert alert-success">
                        ✅ Este correo ya fue convertido en la solicitud #${correoActual.solicitud_id}
                    </div>
                `;
            } else {
                document.getElementById('botonIniciarWizard').innerHTML = `
                    <button class="btn btn-success btn-lg" onclick="iniciarWizard()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:6px;">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                        Convertir en Solicitud (Wizard)
                    </button>
                `;
            }

            document.getElementById('wizardContainer').style.display = 'none';
            document.getElementById('infoCorreo').style.display = 'block';
            document.getElementById('botonIniciarWizard').style.display = 'block';

            new bootstrap.Modal(document.getElementById('modalCorreo')).show();
        } catch (error) {
            console.error('Error:', error);
        }
    };

    window.iniciarWizard = function() {
        if (!correoActual) return;

        document.getElementById('infoCorreo').style.display = 'none';
        document.getElementById('botonIniciarWizard').style.display = 'none';
        document.getElementById('wizardContainer').style.display = 'block';
        document.getElementById('wizardCorreoId').value = correoActual.id;

        const datos = correoActual.datos_extraidos || {};

        if (datos.prioridad) document.getElementById('wzPrioridad').value = datos.prioridad;
        if (datos.fecha_requerida) document.getElementById('wzFechaRequerida').value = datos.fecha_requerida;
        if (datos.fecha_fin_estimada) document.getElementById('wzFechaFin').value = datos.fecha_fin_estimada;
        if (datos.justificacion) document.getElementById('wzJustificacion').value = datos.justificacion;

        const itemsContainer = document.getElementById('wzItemsContainer');
        itemsContainer.innerHTML = '';
        itemWizardCount = 0;

        if (datos.items && datos.items.length > 0) {
            datos.items.forEach(item => {
                agregarItemWizard(item.descripcion, item.cantidad, item.tipo_item);
            });
        } else {
            agregarItemWizard();
        }

        irPaso(1);
    };

    window.agregarItemWizard = function(descripcion = '', cantidad = 1, tipo = 'activo') {
        const container = document.getElementById('wzItemsContainer');
        if (!container) return;
        const idx = itemWizardCount++;
        const html = `
            <div class="item-wizard-card p-3 mb-2" style="background:#f8f9fc; border-radius:8px; border:1px solid #e9ecef;">
                <div class="row g-2 align-items-center">
                    <div class="col-md-2">
                        <select name="items[${idx}][tipo_item]" class="form-select form-select-sm">
                            <option value="activo" ${tipo === 'activo' ? 'selected' : ''}>Activo</option>
                            <option value="componente" ${tipo === 'componente' ? 'selected' : ''}>Componente</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" name="items[${idx}][item_descripcion]" class="form-control form-control-sm"
                               placeholder="Descripción del item" value="${escapeHtml(descripcion)}" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="items[${idx}][cantidad]" class="form-control form-control-sm"
                               value="${cantidad}" min="1" required>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.item-wizard-card').remove()">×</button>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    };

    window.irPaso = function(paso) {
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
        const stepEl = document.getElementById('step' + paso);
        if (stepEl) stepEl.classList.add('active');

        [1, 2, 3].forEach(i => {
            const circle = document.getElementById('step' + i + 'Circle');
            if (!circle) return;
            circle.classList.remove('active', 'completed');
            if (i < paso) { circle.classList.add('completed'); circle.textContent = '✓'; }
            else if (i === paso) { circle.classList.add('active'); circle.textContent = i; }
            else { circle.textContent = i; }
        });

        const progreso = ((paso - 1) / 2) * 100 + 33;
        const bar = document.getElementById('wizardProgress');
        if (bar) bar.style.width = Math.min(progreso, 100) + '%';
    };

    document.getElementById('wzTipoSolicitante')?.addEventListener('change', function() {
        if (this.value === 'interno') {
            document.getElementById('wzInternoFields').style.display = 'block';
            document.getElementById('wzExternoFields').style.display = 'none';
        } else {
            document.getElementById('wzInternoFields').style.display = 'none';
            document.getElementById('wzExternoFields').style.display = 'block';
        }
    });

    document.getElementById('formWizard')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const correoId = document.getElementById('wizardCorreoId').value;
        const submitBtn = document.getElementById('btnGuardarWizard');
        const original = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';

        try {
            const response = await fetch(`/admin/solicitudes/correos/${correoId}/convertir`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: new FormData(this)
            });
            const data = await response.json();

            if (data.success) {
                mostrarNotificacion('success', data.message || 'Solicitud creada desde correo');
                bootstrap.Modal.getInstance(document.getElementById('modalCorreo'))?.hide();
                cargarCorreos();
                actualizarBadgeCorreos();
                cargarPagina(1);
            } else {
                let msg = data.message || 'Error al crear la solicitud';
                if (data.errors) msg += '\n\n' + Object.values(data.errors).flat().join('\n');
                mostrarNotificacion('error', msg);
            }
        } catch (error) {
            mostrarNotificacion('error', 'Error de conexión: ' + error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = original;
        }
    });

    // ============================================================
    // INICIALIZACIÓN
    // ============================================================
    initResponsableEvents();
    cargarPagina(1);
    actualizarBadgeCorreos();

    document.getElementById('buscarCorreo')?.addEventListener('input', (() => {
        let t;
        return function() {
            clearTimeout(t);
            t = setTimeout(cargarCorreos, 400);
        };
    })());
    document.getElementById('filtroCorreo')?.addEventListener('change', cargarCorreos);

    document.getElementById('tab-correos')?.addEventListener('shown.bs.tab', function() {
        cargarCorreos();
        actualizarBadgeCorreos();
    });

    intervaloNotificaciones = setInterval(actualizarBadgeCorreos, 30000);

    console.log('✅ Módulo de solicitudes inicializado correctamente');


    // ============================================================
// CORREOS DE SOLICITUDES (SOLO TIPO SOLICITUD)
// ============================================================
async function cargarCorreosSolicitudes() {
    const container = document.getElementById('listaCorreos');
    if (!container) return;

    const buscar = document.getElementById('buscarCorreo')?.value || '';
    const filtro = document.getElementById('filtroCorreo')?.value || '';

    const params = new URLSearchParams();
    if (buscar) params.append('buscar', buscar);
    if (filtro) params.append('filtro', filtro);

    container.innerHTML = `
        <div class="text-center py-5 text-muted">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando correos...</p>
        </div>
    `;

    try {
        const response = await fetch(`/admin/solicitudes/correos/lista?${params}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = await response.json();
        renderizarCorreosSolicitudes(data.data || []);
    } catch (error) {
        console.error('Error al cargar correos:', error);
        container.innerHTML = `
            <div class="text-center py-5 text-danger">
                <p>Error al cargar correos: ${escapeHtml(error.message)}</p>
                <button class="btn btn-sm btn-primary-dark mt-2" onclick="cargarCorreosSolicitudes()">Reintentar</button>
            </div>
        `;
    }
}

function renderizarCorreosSolicitudes(correos) {
    const container = document.getElementById('listaCorreos');
    if (!container) return;

    if (!correos.length) {
        container.innerHTML = `
            <div class="text-center py-5 text-muted">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M22 7l-10 7L2 7"/>
                </svg>
                <p class="mt-3">No hay correos de solicitudes recibidos</p>
                <p class="small">Haz clic en "Revisar ahora" para buscar nuevos correos</p>
            </div>
        `;
        return;
    }

    let html = '';
    correos.forEach(c => {
        const clases = ['correo-item'];
        if (!c.leido) clases.push('no-leido');
        if (c.procesado) clases.push('procesado');

        const badge = !c.leido ? '<span class="badge-nueva">NUEVO</span>' : '';

        html += `
            <div class="${clases.join(' ')}" onclick="abrirCorreoSolicitud(${c.id})">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1">
                        <div class="fw-bold">${escapeHtml(c.from_name || c.from_email)} ${badge}</div>
                        <div class="text-muted small">${escapeHtml(c.from_email)}</div>
                        <div class="fw-semibold mt-1">${escapeHtml(c.subject || '(Sin asunto)')}</div>
                        <div class="text-muted small mt-1">${escapeHtml((c.body_text || '').substring(0, 150))}...</div>
                    </div>
                    <div class="text-end ms-3">
                        <div class="small text-muted">${formatearFechaHora(c.received_at)}</div>
                    </div>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

async function actualizarBadgeCorreosSolicitudes() {
    try {
        const response = await fetch('/admin/solicitudes/correos/contador', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        const data = await response.json();

        if (data.success) {
            const badge = document.getElementById('tabCorreosBadge');
            if (badge && data.no_procesados > 0) {
                badge.textContent = data.no_procesados;
                badge.style.display = 'inline-block';
            } else if (badge) {
                badge.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error al actualizar badge:', error);
    }
}

window.revisarCorreos = async function () {
    const btn = document.getElementById('btnRevisarCorreos');
    if (!btn) return;

    const original = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Revisando...';
    btn.disabled = true;

    try {
        const response = await fetch('/admin/solicitudes/correos/revisar', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        const data = await response.json();
        mostrarNotificacion('info', data.message || 'Revisión completada');
        cargarCorreosSolicitudes();
        actualizarBadgeCorreosSolicitudes();
    } catch (error) {
        mostrarNotificacion('error', 'Error al revisar correos: ' + error.message);
    } finally {
        btn.innerHTML = original;
        btn.disabled = false;
    }
};

window.abrirCorreoSolicitud = async function (id) {
    try {
        const response = await fetch(`/admin/solicitudes/correos/${id}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = await response.json();
        if (!data.success) {
            mostrarNotificacion('error', 'Error al cargar el correo');
            return;
        }

        correoActual = data.data;

        document.getElementById('correoFrom').textContent =
            `${correoActual.from_name || ''} <${correoActual.from_email}>`;
        document.getElementById('correoFecha').textContent = formatearFechaHora(correoActual.received_at);
        document.getElementById('correoAsunto').textContent = correoActual.subject || '(Sin asunto)';
        document.getElementById('correoCuerpo').textContent = correoActual.body_text || '(Sin contenido)';

        if (correoActual.procesado) {
            document.getElementById('botonIniciarWizard').innerHTML = `
                <div class="alert alert-success">
                    ✅ Este correo ya fue convertido en la solicitud #${correoActual.solicitud_id}
                </div>
            `;
        } else {
            document.getElementById('botonIniciarWizard').innerHTML = `
                <button class="btn btn-success btn-lg" onclick="iniciarWizard()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:6px;">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Convertir en Solicitud (Wizard)
                </button>
            `;
        }

        document.getElementById('wizardContainer').style.display = 'none';
        document.getElementById('infoCorreo').style.display = 'block';
        document.getElementById('botonIniciarWizard').style.display = 'block';

        new bootstrap.Modal(document.getElementById('modalCorreo')).show();
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error al cargar el correo: ' + error.message);
    }
};

// Inicialización al cargar el tab de correos
document.getElementById('tab-correos')?.addEventListener('shown.bs.tab', function () {
    cargarCorreosSolicitudes();
    actualizarBadgeCorreosSolicitudes();
});

// Buscador de correos
document.getElementById('buscarCorreo')?.addEventListener('input', (() => {
    let t;
    return function () {
        clearTimeout(t);
        t = setTimeout(cargarCorreosSolicitudes, 400);
    };
})());

document.getElementById('filtroCorreo')?.addEventListener('change', cargarCorreosSolicitudes);

// Actualizar badge periódicamente
setInterval(actualizarBadgeCorreosSolicitudes, 30000);
});