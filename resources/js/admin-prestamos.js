document.addEventListener(`DOMContentLoaded`, function() {
    // ============================================================
    // VARIABLES GLOBALES
    // ============================================================
    const csrfToken = document.querySelector(`meta[name="csrf-token"]`)?.getAttribute(`content`) || ``;
    let items = [];
    let solicitudDetalles = [];
    let tipoFiltroInventario = `ambos`;
    let currentTab = `solicitudes`;

    // ============================================================
    // FUNCIONES UTILITARIAS
    // ============================================================
    function escapeHtml(text) {
        if (!text) return ``;
        const div = document.createElement(`div`);
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(date) {
        if (!date) return `—`;
        if (date instanceof Date) return date.toLocaleDateString(`es-VE`, { day: `2-digit`, month: `2-digit`, year: `numeric` });
        let str = String(date).trim();
        if (!str) return `—`;
        let dateStr = str.includes(`T`) ? str : `${str}T00:00:00`;
        let d = new Date(dateStr);
        if (isNaN(d.getTime())) return `—`;
        return d.toLocaleDateString(`es-VE`, { day: `2-digit`, month: `2-digit`, year: `numeric` });
    }

    function showToast(message, type = `success`) {
        const colors = { success: `#1e7e34`, error: `#c5221f`, warning: `#f6c23e`, info: `#1e3c72` };
        const icons = { success: `✓`, error: `✕`, warning: `⚠`, info: `ℹ` };
        const toast = document.createElement(`div`);
        toast.style.cssText = `
            position: fixed; top: 20px; right: 20px;
            background: ${colors[type] || colors.success}; color: white;
            padding: 14px 20px; border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2); z-index: 9999;
            font-weight: 500; font-size: 0.9rem;
            animation: slideInRight 0.3s ease-out; max-width: 400px;
            cursor: pointer;
        `;
        toast.textContent = `${icons[type] || `✓`} ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = `slideOutRight 0.3s ease-in`;
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    function showLoading(tableId) {
        const el = document.getElementById(tableId);
        if (el) {
            el.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border" style="color: #1e3c72;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted mt-2">Cargando...</p>
                </div>
            `;
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function normalizePrestableType(type) {
        if (!type) return '';
        if (type === 'activo') return 'App\\Models\\Activo';
        if (type === 'componente') return 'App\\Models\\Componente';
        if (type === 'App\\Models\\Activo' || type === 'App\\Models\\Componente') {
            return type;
        }
        if (type && type.includes('Activo')) return 'App\\Models\\Activo';
        if (type && type.includes('Componente')) return 'App\\Models\\Componente';
        return type;
    }

    // ============================================================
    // FUNCIONES DE ESTADO Y TIMELINE
    // ============================================================
    function getEstadoInfo(estado) {
        const estados = {
            'pendiente': {
                color: '#f6c23e',
                icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
                label: 'Pendiente',
                descripcion: 'Esperando aprobación',
                step: 1,
                timelineClass: 'pending'
            },
            'aprobado': {
                color: '#2a5298',
                icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><polyline points="20 6 9 17 4 12"/></svg>',
                label: 'Aprobado',
                descripcion: 'Préstamo autorizado',
                step: 2,
                timelineClass: 'active'
            },
            'entregado': {
                color: '#1e7e34',
                icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>',
                label: 'Entregado',
                descripcion: 'Equipo en posesión del solicitante',
                step: 3,
                timelineClass: 'completed'
            },
            'extendido': {
                color: '#f6c23e',
                icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
                label: 'Extendido',
                descripcion: 'Plazo de devolución extendido',
                step: 3,
                timelineClass: 'active'
            },
            'devuelto': {
                color: '#6c757d',
                icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><polyline points="20 6 9 17 4 12"/></svg>',
                label: 'Devuelto',
                descripcion: 'Préstamo finalizado',
                step: 4,
                timelineClass: 'completed'
            },
            'cancelado': {
                color: '#6c757d',
                icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
                label: 'Cancelado',
                descripcion: 'Préstamo cancelado',
                step: 0,
                timelineClass: 'rejected'
            },
            'rechazado': {
                color: '#c5221f',
                icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
                label: 'Rechazado',
                descripcion: 'Préstamo no aprobado',
                step: 0,
                timelineClass: 'rejected'
            },
            'vencido': {
                color: '#c5221f',
                icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
                label: 'Vencido',
                descripcion: 'Fecha de devolución pasada',
                step: 3,
                timelineClass: 'overdue'
            },
        };
        return estados[estado] || estados['pendiente'];
    }

    function renderTimeline(estado, fechas) {
        const steps = [
            { key: 'solicitud', label: 'Solicitud', icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>' },
            { key: 'aprobacion', label: 'Aprobación', icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><polyline points="20 6 9 17 4 12"/></svg>' },
            { key: 'entrega', label: 'Entrega', icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>' },
            { key: 'devolucion', label: 'Devolución', icon: '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><polyline points="20 6 9 17 4 12"/></svg>' },
        ];

        const estadoInfo = getEstadoInfo(estado);
        const currentStep = estadoInfo.step || 0;

        let html = '<div class="prestamo-timeline">';
        steps.forEach((step, index) => {
            const stepNumber = index + 1;
            let stepClass = 'pending';
            let dateText = '';

            if (stepNumber <= currentStep) {
                stepClass = 'completed';
            } else if (stepNumber === currentStep + 1 && estado !== 'devuelto' && estado !== 'cancelado' && estado !== 'rechazado') {
                stepClass = 'active';
            }

            if (step.key === 'solicitud' && fechas.fecha_solicitud) {
                dateText = formatDate(fechas.fecha_solicitud);
            } else if (step.key === 'aprobacion' && fechas.fecha_aprobacion) {
                dateText = formatDate(fechas.fecha_aprobacion);
            } else if (step.key === 'entrega' && fechas.fecha_entrega) {
                dateText = formatDate(fechas.fecha_entrega);
            } else if (step.key === 'devolucion' && fechas.fecha_devolucion) {
                dateText = formatDate(fechas.fecha_devolucion);
            } else if (step.key === 'devolucion' && estado === 'devuelto' && fechas.fecha_devolucion_real) {
                dateText = formatDate(fechas.fecha_devolucion_real);
            }

            html += `
                <div class="step ${stepClass}">
                    <div class="step-icon">
                        ${step.icon}
                        <span class="step-number">${stepNumber}</span>
                    </div>
                    <div class="step-label">${step.label}</div>
                    <div class="step-date">${dateText || '—'}</div>
                </div>
            `;
        });
        html += '</div>';
        return html;
    }

    // ============================================================
    // FUNCIONES DE CARGA DE DATOS POR TAB
    // ============================================================
    function cargarSolicitudes() {
        showLoading(`tablaSolicitudes`);
        let buscar = document.getElementById(`buscarSolicitudes`)?.value || ``;
        let url = `/admin/solicitudes/pendientes-prestamo`;
        if (buscar) url += `?buscar=${encodeURIComponent(buscar)}`;
        fetch(url, { headers: { Accept: `application/json` } })
            .then(response => response.json())
            .then(data => {
                renderSolicitudes(data.data || data);
            })
            .catch(error => {
                console.error(`Error:`, error);
                document.getElementById(`tablaSolicitudes`).innerHTML =
                    `<p class="text-center py-4 text-danger">Error al cargar solicitudes</p>`;
            });
    }

    function cargarPrestamosActivos() {
        showLoading(`tablaActivos`);
        let buscar = document.getElementById(`buscarActivos`)?.value || ``;
        let tipo = document.getElementById(`filtroTipoActivos`)?.value || ``;
        let estado = document.getElementById(`filtroEstadoActivos`)?.value || ``;

        let url = `/admin/prestamos/listar?`;
        if (estado === 'vencido') {
            url += `estado=vencido`;
        } else if (estado === 'aprobado') {
            url += `estado=aprobado`;
        } else if (estado === 'entregado') {
            url += `estado=entregado`;
        } else if (estado === 'extendido') {
            url += `estado=extendido`;
        } else {
            url += `estado=aprobado,entregado,extendido`;
        }
        if (buscar) url += `&buscar=${encodeURIComponent(buscar)}`;
        if (tipo) url += `&tipo=${tipo}`;

        fetch(url, { headers: { Accept: `application/json` } })
            .then(response => response.json())
            .then(data => {
                renderPrestamos(data, `tablaActivos`, `activos`);
            })
            .catch(error => {
                console.error(`Error:`, error);
                document.getElementById(`tablaActivos`).innerHTML =
                    `<p class="text-center py-4 text-danger">Error al cargar préstamos</p>`;
            });
    }

    function cargarPrestamosFinalizados() {
        showLoading(`tablaFinalizados`);
        let buscar = document.getElementById(`buscarFinalizados`)?.value || ``;
        let estado = document.getElementById(`filtroEstadoFinalizados`)?.value || ``;
        let fechaDesde = document.getElementById(`filtroFechaDesde`)?.value || ``;
        let fechaHasta = document.getElementById(`filtroFechaHasta`)?.value || ``;
        let url = `/admin/prestamos/listar?`;
        if (estado) url += `estado=${estado}&`;
        else url += `estado=devuelto,cancelado&`;
        if (buscar) url += `buscar=${encodeURIComponent(buscar)}&`;
        if (fechaDesde) url += `fecha_desde=${fechaDesde}&`;
        if (fechaHasta) url += `fecha_hasta=${fechaHasta}&`;
        fetch(url, { headers: { Accept: `application/json` } })
            .then(response => response.json())
            .then(data => {
                renderPrestamos(data, `tablaFinalizados`, `finalizados`);
            })
            .catch(error => console.error(`Error:`, error));
    }

    // ============================================================
    // FUNCIONES DE RENDERIZADO
    // ============================================================
    function renderSolicitudes(data, searchTerm = ``) {
        const table = document.getElementById(`tablaSolicitudes`);
        if (!table) return;
        const solicitudes = data || [];
        if (solicitudes.length === 0) {
            table.innerHTML = `
                <table class="table table-hover align-middle mb-0">
                    <thead><tr>
                        <th>Código</th><th>Solicitante</th><th>Departamento</th><th>Items</th>
                        <th>Fecha</th><th>Estado</th><th style="width:180px">Acciones</th>
                    </tr></thead>
                    <tbody>
                        <tr><td colspan="7" class="text-center py-4 text-muted">
                            No hay solicitudes pendientes ni aprobadas pendientes de préstamo
                        </td></tr>
                    </tbody>
                </table>
            `;
            return;
        }

        let html = `
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th>Código</th><th>Solicitante</th><th>Departamento</th><th>Items</th>
                    <th>Fecha</th><th>Estado</th><th style="width:180px">Acciones</th>
                </tr></thead>
                <tbody>
        `;

        solicitudes.forEach(sol => {
            const estado = sol.estado_solicitud || `pendiente`;
            const badgeClass = estado === `aprobada` ? `badge-estado-aprobada` :
                               estado === `pendiente` ? `badge-estado-pendiente` : `badge-estado-otro`;
            const puedePrestar = estado === `aprobada` || estado === `pendiente`;
            const botonPrestar = puedePrestar ?
                `<button class="btn-action text-success" onclick="prestarDesdeSolicitud(${sol.id})" title="Realizar préstamo">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><polyline points="20 6 9 17 4 12"/></svg>
                    Prestar
                </button>` :
                `<span class="text-muted">No disponible</span>`;

            let nombreSolicitante = '—';
            if (sol.usuario?.nombre) {
                nombreSolicitante = sol.usuario.nombre;
            } else if (sol.trabajador?.nombre) {
                nombreSolicitante = sol.trabajador.nombre;
            } else if (sol.solicitante_nombre) {
                nombreSolicitante = sol.solicitante_nombre;
            } else if (sol.responsable?.nombre) {
                nombreSolicitante = sol.responsable.nombre;
            }

            let nombreDepto = '—';
            if (sol.departamento?.nombre) {
                nombreDepto = sol.departamento.nombre;
            } else if (sol.trabajador?.departamento?.nombre) {
                nombreDepto = sol.trabajador.departamento.nombre;
            }

            html += `
                <tr>
                    <td><span class="fw-medium" style="color:#1e3c72">${escapeHtml(sol.codigo || `#${sol.id}`)}</span></td>
                    <td>${escapeHtml(nombreSolicitante)}</td>
                    <td>${escapeHtml(nombreDepto)}</td>
                    <td><span class="badge-estado badge-estado-entregado">${sol.detalles_count ?? sol.detalles?.length ?? 0} items</span></td>
                    <td>${formatDate(sol.fecha_solicitud || sol.created_at)}</td>
                    <td><span class="badge-estado ${badgeClass}">${escapeHtml(estado)}</span></td>
                    <td>${botonPrestar}</td>
                </tr>
            `;
        });

        html += `</tbody></table>`;
        table.innerHTML = html;
    }

    function renderPrestamos(data, tableId, tipo) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const prestamos = data.data || [];
        const esFinalizados = tipo === `finalizados`;

        if (prestamos.length === 0) {
            const mensajes = {
                activos: `No hay préstamos activos`,
                finalizados: `No hay préstamos finalizados`
            };
            const columnas = esFinalizados ? 9 : 10;
            table.innerHTML = `
                <table class="table table-hover align-middle mb-0">
                    <thead><tr>
                        <th>Código</th><th>Destino</th><th>Solicitud</th><th>Responsable</th>
                        <th>Tipo</th><th>F. Préstamo</th><th>F. Devolución</th><th>Estado</th>
                        ${esFinalizados ? `<th style="width:60px">Ver</th>` : `<th style="width:140px">Acciones</th>`}
                    </tr></thead>
                    <tbody>
                        <tr><td colspan="${columnas}" class="text-center py-4 text-muted">${mensajes[tipo] || `No se encontraron préstamos`}</td></tr>
                    </tbody>
                </table>
            `;
            return;
        }

        let html = `
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th>Código</th><th>Destino</th><th>Solicitud</th><th>Responsable</th>
                    <th>Tipo</th><th>F. Préstamo</th><th>F. Devolución</th><th>Estado</th>
                    ${esFinalizados ? `<th style="width:60px">Ver</th>` : `<th style="width:140px">Acciones</th>`}
                </tr></thead>
                <tbody>
        `;

        prestamos.forEach(prestamo => {
            const estado = prestamo.estado || `pendiente`;
            const badgeClass = `badge-estado-${estado}`;
            const vencido = prestamo.esta_vencido ? `vencido` : ``;
            const solicitudCodigo = prestamo.solicitud_codigo ?
                `<span class="badge-estado badge-estado-info">${escapeHtml(prestamo.solicitud_codigo)}</span>` :
                `—`;
            const destino = prestamo.destino_nombre || `—`;

            html += `
                <tr class="${vencido}">
                    <td><span class="fw-medium" style="color:#1e3c72">${escapeHtml(prestamo.codigo)}</span></td>
                    <td>${escapeHtml(destino)}</td>
                    <td>${solicitudCodigo}</td>
                    <td>${escapeHtml(prestamo.responsable_receptor?.nombre || `—`)}</td>
                    <td>${escapeHtml(prestamo.tipo_prestamo)}</td>
                    <td>${formatDate(prestamo.fecha_prestamo)}</td>
                    <td>${formatDate(prestamo.fecha_devolucion_esperada)}</td>
                    <td><span class="badge-estado ${badgeClass}">${escapeHtml(estado)}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn-action text-info" onclick="verDetallePrestamo(${prestamo.id})" title="Ver detalle">
                                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
            `;

            if (!esFinalizados) {
                if (estado === `pendiente`) {
                    html += `
                        <button class="btn-action text-success" onclick="abrirModalAprobacion(${prestamo.id})" title="Aprobar">
                            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><polyline points="20 6 9 17 4 12"/></svg>
                            Aprobar
                        </button>
                        <button class="btn-action text-danger" onclick="abrirModalRechazo(${prestamo.id})" title="Rechazar">
                            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Rechazar
                        </button>
                    `;
                } else if (estado === `aprobado`) {
                    html += `
                        <button class="btn-action text-primary" onclick="abrirModalEntrega(${prestamo.id})" title="Registrar entrega">
                            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                            Entregar
                        </button>
                        <button class="btn-action text-danger" onclick="abrirModalCancelar(${prestamo.id})" title="Cancelar">
                            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Cancelar
                        </button>
                    `;
                } else if (estado === `entregado` || estado === `extendido`) {
                    html += `
                        <button class="btn-action text-success" onclick="abrirModalDevolucion(${prestamo.id})" title="Devolver">
                            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><polyline points="20 6 9 17 4 12"/></svg>
                            Devolver
                        </button>
                        <button class="btn-action text-warning" onclick="abrirModalExtension(${prestamo.id})" title="Extender">
                            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Extender
                        </button>
                    `;
                }
            }

            html += `
                        </div>
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table>`;
        table.innerHTML = html;
    }

    // ============================================================
    // FUNCIONES DEL MODAL DE PRÉSTAMO
    // ============================================================
    window.prestarDesdeSolicitud = function(id) {
        fetch(`/admin/solicitudes/${id}/detalles`, { headers: { Accept: `application/json` } })
            .then(response => response.json())
            .then(data => {
                if (!data.success && !data.id) {
                    showToast(`Error al cargar la solicitud`, `error`);
                    return;
                }

                const solicitud = data;
                items = [];
                solicitudDetalles = [];

                document.getElementById(`modalPrestamoLabel`).textContent = `Nuevo Préstamo (Desde Solicitud)`;
                document.getElementById(`prestamoId`).value = ``;
                document.getElementById(`solicitudIdInput`).value = id;
                document.getElementById(`solicitudCodigo`).value = solicitud.codigo || `SOL-${id}`;
                document.getElementById(`tipoPrestamo`).value = `mixto`;
                document.getElementById(`fechaPrestamo`).value = new Date().toISOString().split(`T`)[0];
                document.getElementById(`fechaDevolucionEsperada`).value = ``;
                document.getElementById(`observaciones`).value = ``;
                document.getElementById(`condiciones`).value = ``;
                document.getElementById(`btnGuardarPrestamo`).disabled = false;

                const tipoDestino = document.getElementById(`tipoDestino`);
                if (solicitud.departamento_id) {
                    tipoDestino.value = `departamento`;
                    document.getElementById(`departamentoId`).value = solicitud.departamento_id;
                    document.getElementById(`institucionId`).value = ``;
                } else if (solicitud.institucion_id) {
                    tipoDestino.value = `institucion`;
                    document.getElementById(`institucionId`).value = solicitud.institucion_id;
                    document.getElementById(`departamentoId`).value = ``;
                }
                tipoDestino.disabled = true;
                cambiarTipoDestino();

                const receptorIdInput = document.getElementById(`responsableReceptorId`);
                const display = document.getElementById(`responsableReceptorDisplay`);

                if (solicitud.responsable_id && solicitud.responsable) {
                    receptorIdInput.value = solicitud.responsable_id;
                    display.innerHTML = `
                        <div class="resp-nombre">${escapeHtml(solicitud.responsable.nombre)}</div>
                        <div class="resp-info">${escapeHtml(solicitud.responsable.cargo || ``)} · ${escapeHtml(solicitud.responsable.telefono || `Sin tel.`)}</div>
                    `;
                } else {
                    cargarResponsableDestino();
                }

                mostrarInfoSolicitud(solicitud);

                solicitudDetalles = solicitud.detalles || [];
                const tipoItems = solicitudDetalles.length > 0 ?
                    (solicitudDetalles.every(d => d.tipo_item === `activo`) ? `activo` :
                     solicitudDetalles.every(d => d.tipo_item === `componente`) ? `componente` : `ambos`) :
                    `ambos`;
                tipoFiltroInventario = tipoItems;
                document.getElementById(`buscarItem`).placeholder =
                    tipoItems === `ambos` ? `Buscar activos o componentes...` :
                    tipoItems === `activo` ? `Buscar activos...` : `Buscar componentes...`;
                document.getElementById(`filtroTipoInventario`).value = tipoItems;

                document.getElementById(`resultadosBusqueda`).style.display = `none`;
                document.getElementById(`buscarItem`).value = ``;
                renderItems();

                const modal = new bootstrap.Modal(document.getElementById(`modalPrestamo`));
                modal.show();
                buscarItemsPrestamo();
            })
            .catch(() => showToast(`Error al cargar la solicitud`, `error`));
    };

    function mostrarInfoSolicitud(solicitud) {
        const section = document.getElementById(`solicitudInfoSection`);
        if (!section) return;
        section.style.display = `block`;

        document.getElementById(`solicitudTipoSolicitante`).textContent =
            solicitud.tipo_solicitante === `interno` ? `Interno` : `Externo`;
        document.getElementById(`solicitudEntidad`).textContent =
            solicitud.departamento?.nombre || solicitud.institucion?.nombre || `No especificada`;
        document.getElementById(`solicitudResponsable`).textContent =
            solicitud.responsable?.nombre || `No asignado`;
        document.getElementById(`solicitudPrioridad`).textContent =
            solicitud.prioridad || `—`;
        document.getElementById(`solicitudFechaRequerida`).textContent =
            formatDate(solicitud.fecha_requerida);
        document.getElementById(`solicitudFechaFin`).textContent =
            formatDate(solicitud.fecha_fin_estimada);
        document.getElementById(`solicitudJustificacion`).textContent =
            solicitud.justificacion || `—`;
        document.getElementById(`solicitudObservaciones`).textContent =
            solicitud.observaciones || `—`;

        const container = document.getElementById(`solicitudItemsContainer`);
        if (!container) return;

        const detalles = solicitud.detalles || [];

        if (detalles.length === 0) {
            container.innerHTML = `<li class="list-group-item py-2 text-muted">No hay items solicitados</li>`;
            return;
        }

        let html = '';
        detalles.forEach(d => {
            let descripcion = d.descripcion_personalizada || d.item_descripcion || d.descripcion_item || 'Descripción no disponible';
            let cantidad = d.cantidad_solicitada || d.cantidad || 1;
            let tipo = d.tipo_item === 'activo' ? 'Activo' : 'Componente';

            html += `
                <li class="list-group-item py-2">
                    <strong>${tipo}</strong>: ${escapeHtml(descripcion)}
                    <br><span class="text-muted small">Cantidad: ${cantidad}</span>
                </li>
            `;
        });

        container.innerHTML = html;
    }

    window.abrirModalNuevoPrestamo = function() {
        items = [];
        solicitudDetalles = [];

        document.getElementById(`solicitudInfoSection`).style.display = `none`;
        document.getElementById(`modalPrestamoLabel`).textContent = `Nuevo Préstamo`;
        document.getElementById(`prestamoId`).value = ``;
        document.getElementById(`solicitudIdInput`).value = ``;
        document.getElementById(`solicitudCodigo`).value = ``;
        document.getElementById(`tipoPrestamo`).value = `equipo`;
        document.getElementById(`fechaPrestamo`).value = new Date().toISOString().split(`T`)[0];
        document.getElementById(`fechaDevolucionEsperada`).value = ``;
        document.getElementById(`observaciones`).value = ``;
        document.getElementById(`condiciones`).value = ``;
        document.getElementById(`btnGuardarPrestamo`).disabled = false;

        const tipoDestino = document.getElementById(`tipoDestino`);
        tipoDestino.disabled = false;
        tipoDestino.value = `departamento`;
        cambiarTipoDestino();

        document.getElementById(`responsableReceptorDisplay`).innerHTML =
            `<span class="text-muted">Seleccione un destino primero</span>`;
        document.getElementById(`responsableReceptorId`).value = ``;

        renderItems();
        document.getElementById(`buscarItem`).value = ``;
        document.getElementById(`resultadosBusqueda`).style.display = `none`;
        document.getElementById(`filtroTipoInventario`).value = `ambos`;

        const modal = new bootstrap.Modal(document.getElementById(`modalPrestamo`));
        modal.show();
        buscarItemsPrestamo();
    };

    // ============================================================
    // FUNCIONES DE DESTINO Y RESPONSABLE
    // ============================================================
    window.cambiarTipoDestino = function() {
        const tipoDestinoEl = document.getElementById(`tipoDestino`);
        if (!tipoDestinoEl) return;

        const e = tipoDestinoEl.value;
        const contenedorDepto = document.getElementById(`contenedorDepartamento`);
        const contenedorInst = document.getElementById(`contenedorInstitucion`);

        if (contenedorDepto) {
            contenedorDepto.style.display = e === `departamento` ? `block` : `none`;
        }
        if (contenedorInst) {
            contenedorInst.style.display = e === `institucion` ? `block` : `none`;
        }

        const deptoId = document.getElementById(`departamentoId`);
        const instId = document.getElementById(`institucionId`);
        if (deptoId && e === `institucion`) deptoId.value = ``;
        if (instId && e === `departamento`) instId.value = ``;

        const display = document.getElementById(`responsableReceptorDisplay`);
        const hidden = document.getElementById(`responsableReceptorId`);
        if (display) display.innerHTML = `<span class="text-muted">Seleccione un destino primero</span>`;
        if (hidden) hidden.value = ``;
    };

    window.cargarResponsableDestino = function() {
        const tipoDestinoEl = document.getElementById(`tipoDestino`);
        if (!tipoDestinoEl) return;

        const e = tipoDestinoEl.value;
        const deptoEl = document.getElementById(`departamentoId`);
        const instEl = document.getElementById(`institucionId`);

        let id = null;
        if (e === `departamento` && deptoEl) id = deptoEl.value;
        else if (e === `institucion` && instEl) id = instEl.value;

        const display = document.getElementById(`responsableReceptorDisplay`);
        const hidden = document.getElementById(`responsableReceptorId`);

        if (!id) {
            if (display) display.innerHTML = `<span class="text-muted">Seleccione un destino primero</span>`;
            if (hidden) hidden.value = ``;
            return;
        }

        if (display) display.innerHTML = `<span class="text-muted">Cargando responsable...</span>`;

        const url = e === `departamento` ?
            `/admin/api/departamento/${id}/responsable` :
            `/admin/api/institucion/${id}/responsable`;

        fetch(url, { headers: { Accept: `application/json` } })
            .then(response => response.json())
            .then(data => {
                if (data.responsable && display) {
                    display.innerHTML = `
                        <div class="resp-nombre">${escapeHtml(data.responsable.nombre)}</div>
                        <div class="resp-info">${escapeHtml(data.responsable.cargo || ``)} · ${escapeHtml(data.responsable.telefono || `Sin tel.`)}</div>
                    `;
                    if (hidden) hidden.value = data.responsable.id;
                } else if (display) {
                    display.innerHTML = `<span class="text-warning">No se encontró responsable</span>`;
                    if (hidden) hidden.value = ``;
                }
            })
            .catch(() => {
                if (display) display.innerHTML = `<span class="text-danger">Error al cargar responsable</span>`;
                if (hidden) hidden.value = ``;
            });
    };

    // ============================================================
    // FUNCIONES DE BÚSQUEDA DE ITEMS
    // ============================================================
    window.buscarItemsPrestamo = debounce(function() {
        const buscar = document.getElementById(`buscarItem`)?.value?.trim() || ``;
        const tipo = document.getElementById(`filtroTipoInventario`)?.value || `ambos`;
        const resultados = document.getElementById(`resultadosBusqueda`);

        if (!resultados) return;

        if (buscar.length < 1) {
            resultados.style.display = `none`;
            return;
        }

        const url = `/admin/prestamos/buscar-items?buscar=${encodeURIComponent(buscar)}&tipo=${encodeURIComponent(tipo)}`;

        fetch(url, { headers: { Accept: `application/json` } })
            .then(response => response.json())
            .then(data => {
                const itemsList = data.data || [];
                if (itemsList.length === 0) {
                    resultados.innerHTML = `<div class="list-group-item text-muted">No se encontraron resultados</div>`;
                    resultados.style.display = `block`;
                    return;
                }

                let html = `<div class="list-group-item text-muted small" style="background:#f8f9fc; font-weight:600;">Resultados (${itemsList.length})</div>`;
                itemsList.forEach(item => {
                    const tipoLabel = item.tipo === `activo` ? `Activo` : `Componente`;
                    const badgeClass = item.tipo === `activo` ? `item-badge-activo` : `item-badge-componente`;
                    const nombre = escapeHtml(item.nombre || `${tipoLabel} sin nombre`);
                    const marca = escapeHtml(item.marca || `Sin marca`);
                    const modelo = escapeHtml(item.modelo || `Sin modelo`);
                    const categoria = escapeHtml(item.categoria || ``);
                    const serial = escapeHtml(item.serial || `Sin serial`);

                    let prestableType = normalizePrestableType(item.prestable_type || '');

                    const itemId = item.id;
                    const itemType = prestableType;
                    const itemName = String(item.nombre || `${tipoLabel} sin nombre`).replace(/"/g, `&quot;`);
                    const itemCode = String(item.serial || `Sin serial`).replace(/"/g, `&quot;`);

                    let infoAdicional = '';
                    if (marca && marca !== 'Sin marca') infoAdicional += marca;
                    if (modelo && modelo !== 'Sin modelo') {
                        if (infoAdicional) infoAdicional += ' · ';
                        infoAdicional += modelo;
                    }
                    if (categoria) {
                        if (infoAdicional) infoAdicional += ' · ';
                        infoAdicional += categoria;
                    }
                    if (!infoAdicional) infoAdicional = serial;

                    html += `
                        <div class="result-item list-group-item list-group-item-action"
                             data-item-id="${itemId}"
                             data-item-type="${itemType}"
                             data-item-name="${itemName}"
                             data-item-code="${itemCode}"
                             onclick="seleccionarItem(${itemId}, '${itemType}', '${itemName}', '${itemCode}')"
                             style="border-left: 3px solid ${item.tipo === `activo` ? `#1e3c72` : `#0d6efd`}; padding: 0.75rem 0.9rem;">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-semibold">${nombre}</div>
                                    <div class="small text-muted">
                                        ${infoAdicional}
                                        ${serial !== 'Sin serial' ? ` · <span class="fw-mono">${serial}</span>` : ''}
                                    </div>
                                </div>
                                <span class="badge ${badgeClass}" style="font-size:0.72rem; text-transform:capitalize;">${tipoLabel}</span>
                            </div>
                        </div>
                    `;
                });
                resultados.innerHTML = html;
                resultados.style.display = `block`;
            })
            .catch(error => {
                console.error(`Error buscando items:`, error);
                resultados.innerHTML = `<div class="list-group-item text-danger">Error al buscar. Intente de nuevo.</div>`;
                resultados.style.display = `block`;
            });
    }, 300);

    window.seleccionarItem = function(id, type, name, code) {
        let prestableType = normalizePrestableType(type);
        const tipoNormalizado = prestableType.includes('Activo') ? 'activo' : 'componente';

        if (items.some(item => String(item.prestable_id) === String(id) && item.tipo_item === tipoNormalizado)) {
            showToast(`Este item ya está agregado`, `warning`);
            return;
        }

        items.push({
            prestable_type: prestableType,
            prestable_id: Number(id),
            nombre: name,
            codigo: code,
            tipo_item: tipoNormalizado,
            cantidad: 1,
            estado_entrega: `En buen estado`,
            observaciones: ``,
            _highlighted: true
        });

        document.getElementById(`buscarItem`).value = ``;
        document.getElementById(`resultadosBusqueda`).style.display = `none`;
        renderItems();
        showToast(`Item agregado al préstamo`, `success`);

        setTimeout(() => {
            const item = items.find(i => String(i.prestable_id) === String(id) && i.tipo_item === tipoNormalizado);
            if (item) {
                delete item._highlighted;
                renderItems();
            }
        }, 1100);
    };

    window.eliminarItem = function(index) {
        if (index >= 0 && index < items.length) {
            items.splice(index, 1);
            renderItems();
        }
    };

    window.agregarItem = function() {
        document.getElementById(`buscarItem`)?.focus();
    };

    // ============================================================
    // RENDERIZAR ITEMS DEL PRÉSTAMO
    // ============================================================
    function renderItems() {
        const container = document.getElementById(`itemsContainer`);
        if (!container) return;

        if (items.length === 0) {
            container.innerHTML = `<p class="text-muted text-center py-3">No hay items agregados</p>`;
            return;
        }

        let html = ``;
        items.forEach((item, index) => {
            const tipoLabel = item.tipo_item === `activo` ? `Activo` : `Componente`;
            const badgeClass = item.tipo_item === `activo` ? `item-badge-activo` : `item-badge-componente`;
            const highlightClass = item._highlighted ? ` item-card--added` : ``;

            let prestableType = normalizePrestableType(item.prestable_type || '');

            html += `
                <div class="item-card${highlightClass}" style="border:1px solid #e3e8f0; border-left:4px solid ${item.tipo_item === `activo` ? `#1e3c72` : `#0d6efd`}; border-radius:10px; padding:0.8rem 0.9rem; margin-bottom:0.6rem; display:flex; justify-content:space-between; align-items:flex-start; gap:0.7rem; background:#fff;">
                    <input type="hidden" name="items[${index}][prestable_type]" value="${prestableType}">
                    <input type="hidden" name="items[${index}][prestable_id]" value="${item.prestable_id}">
                    <input type="hidden" name="items[${index}][cantidad]" value="${item.cantidad}">
                    <input type="hidden" name="items[${index}][estado_entrega]" value="${escapeHtml(item.estado_entrega || `En buen estado`)}">
                    <input type="hidden" name="items[${index}][observaciones]" value="${escapeHtml(item.observaciones || ``)}">

                    <div class="item-info">
                        <div class="item-nombre" style="font-weight:600; color:#1e3c72;">
                            <span class="${badgeClass}">${tipoLabel}</span>
                            ${escapeHtml(item.nombre || `Sin nombre`)}
                        </div>
                        <div class="item-detalle" style="font-size:0.82rem; color:#5f6b7a; margin-top:0.25rem;">
                            ${escapeHtml(item.codigo || `Sin código`)} · Cant: ${item.cantidad}
                        </div>
                    </div>
                    <button type="button" class="btn-action text-danger" onclick="eliminarItem(${index})" style="padding:0.35rem 0.45rem;">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:12px;height:12px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // ============================================================
    // EVENTOS DEL FORMULARIO DE PRÉSTAMO
    // ============================================================
    document.getElementById(`formPrestamo`)?.addEventListener(`submit`, function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            this.reportValidity();
            return;
        }

        if (items.length === 0) {
            showToast(`Debe agregar al menos un item`, `warning`);
            return;
        }

        const receptorId = document.getElementById(`responsableReceptorId`)?.value;
        if (!receptorId) {
            showToast(`Debe seleccionar un responsable que recibe`, `warning`);
            return;
        }

        const emisorId = document.getElementById(`responsableEmisorId`)?.value;
        if (!emisorId) {
            showToast(`Debe seleccionar un responsable que entrega`, `warning`);
            return;
        }

        const submitBtn = document.getElementById(`btnGuardarPrestamo`);
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Guardando...`;
        }

        const formData = new FormData();
        formData.append(`_token`, csrfToken);
        formData.append(`tipo_prestamo`, document.getElementById(`tipoPrestamo`)?.value || `equipo`);
        formData.append(`departamento_id`, document.getElementById(`departamentoId`)?.value || ``);
        formData.append(`institucion_id`, document.getElementById(`institucionId`)?.value || ``);
        formData.append(`responsable_receptor_id`, receptorId);
        formData.append(`responsable_emisor_id`, emisorId);
        formData.append(`fecha_prestamo`, document.getElementById(`fechaPrestamo`)?.value || ``);
        formData.append(`fecha_devolucion_esperada`, document.getElementById(`fechaDevolucionEsperada`)?.value || ``);
        formData.append(`observaciones`, document.getElementById(`observaciones`)?.value || ``);
        formData.append(`condiciones`, document.getElementById(`condiciones`)?.value || ``);

        const solicitudId = document.getElementById(`solicitudIdInput`)?.value;
        if (solicitudId) {
            formData.append(`solicitud_id`, solicitudId);
            formData.append(`estado`, `aprobado`);
        }

        items.forEach((item, index) => {
            let prestableType = normalizePrestableType(item.prestable_type || '');
            formData.append(`items[${index}][prestable_type]`, prestableType);
            formData.append(`items[${index}][prestable_id]`, item.prestable_id);
            formData.append(`items[${index}][cantidad]`, item.cantidad);
            formData.append(`items[${index}][estado_entrega]`, item.estado_entrega || `En buen estado`);
            formData.append(`items[${index}][observaciones]`, item.observaciones || ``);
        });

        fetch(`/admin/prestamos`, {
            method: `POST`,
            body: formData,
            headers: { Accept: `application/json` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById(`modalPrestamo`));
                if (modal) modal.hide();
                showToast(data.message || `Préstamo creado exitosamente`, `success`);
                cargarSolicitudes();
                cargarPrestamosActivos();
                cargarPrestamosFinalizados();
                items = [];
            } else {
                showToast(data.message || `Error al crear el préstamo`, `error`);
                console.error('Error detallado:', data);
            }
        })
        .catch(error => {
            console.error('Error de conexión:', error);
            showToast(`Error de conexión al servidor`, `error`);
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `Guardar Préstamo`;
            }
        });
    });

    // ============================================================
    // FUNCIONES DE ACCIONES DE PRÉSTAMOS (Modales)
    // ============================================================
    window.verDetallePrestamo = function(id) {
        fetch(`/admin/prestamos/${id}`, { headers: { Accept: `application/json` } })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    showToast(`Error al cargar el detalle: ${data.message || 'Error desconocido'}`, `error`);
                    return;
                }

                const p = data.data;
                const estadoInfo = getEstadoInfo(p.estado);

                const fechas = {
                    fecha_solicitud: p.solicitud?.created_at || null,
                    fecha_aprobacion: p.created_at || null,
                    fecha_entrega: p.fecha_prestamo || null,
                    fecha_devolucion: p.fecha_devolucion_real || p.fecha_devolucion_esperada || null,
                    fecha_devolucion_real: p.fecha_devolucion_real || null,
                };

                const timelineHtml = renderTimeline(p.estado, fechas);

                let html = `
                    ${timelineHtml}

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 style="color:#1e3c72;">${escapeHtml(p.codigo)}</h5>
                        <span class="badge-estado badge-estado-${p.estado}">
                            ${estadoInfo.icon} ${escapeHtml(estadoInfo.label)}
                        </span>
                    </div>

                    <div class="alert ${p.esta_vencido ? 'alert-danger' : 'alert-info'} mb-3">
                        <strong>${estadoInfo.icon} ${estadoInfo.descripcion}</strong>
                        ${p.esta_vencido ? ' ⚠️ Préstamo vencido!' : ''}
                        ${p.estado === 'entregado' || p.estado === 'extendido' ?
                            `<br><small>Días restantes: <strong>${p.dias_restantes}</strong> días</small>` : ''}
                    </div>

                    <div class="detalle-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                        <div class="detalle-item"><div class="label" style="color:#6c757d;font-size:0.75rem;text-transform:uppercase;">Tipo</div><div class="value">${escapeHtml(p.tipo_prestamo)}</div></div>
                        <div class="detalle-item"><div class="label" style="color:#6c757d;font-size:0.75rem;text-transform:uppercase;">Destino</div><div class="value">${escapeHtml(p.destino_nombre)}</div></div>
                        <div class="detalle-item"><div class="label" style="color:#6c757d;font-size:0.75rem;text-transform:uppercase;">Solicitud</div><div class="value">${escapeHtml(p.solicitud_codigo || `—`)}</div></div>
                        <div class="detalle-item"><div class="label" style="color:#6c757d;font-size:0.75rem;text-transform:uppercase;">Estado Solicitud</div><div class="value">${escapeHtml(p.solicitud?.estado_solicitud || `—`)}</div></div>
                        <div class="detalle-item"><div class="label" style="color:#6c757d;font-size:0.75rem;text-transform:uppercase;">F. Préstamo</div><div class="value">${formatDate(p.fecha_prestamo)}</div></div>
                        <div class="detalle-item"><div class="label" style="color:#6c757d;font-size:0.75rem;text-transform:uppercase;">F. Dev. Esperada</div><div class="value">${formatDate(p.fecha_devolucion_esperada)}</div></div>
                        ${p.fecha_devolucion_real ? `
                        <div class="detalle-item"><div class="label" style="color:#6c757d;font-size:0.75rem;text-transform:uppercase;">F. Dev. Real</div><div class="value">${formatDate(p.fecha_devolucion_real)}</div></div>
                        ` : ``}
                        ${p.total_extensiones > 0 ? `
                        <div class="detalle-item"><div class="label" style="color:#6c757d;font-size:0.75rem;text-transform:uppercase;">Extensiones</div><div class="value">${p.total_extensiones}</div></div>
                        ` : ``}
                    </div>
                    <div class="detalle-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-top:1rem;">
                        <div class="detalle-item"><div class="label" style="color:#6c757d;font-size:0.75rem;text-transform:uppercase;">Recibe</div><div class="value">${escapeHtml(p.responsable_receptor?.nombre || `—`)}</div></div>
                        <div class="detalle-item"><div class="label" style="color:#6c757d;font-size:0.75rem;text-transform:uppercase;">Entrega</div><div class="value">${escapeHtml(p.responsable_emisor?.nombre || `—`)}</div></div>
                    </div>
                `;

                if (p.detalles && p.detalles.length > 0) {
                    html += `<div class="detalle-section" style="margin-top:1rem;padding-top:1rem;border-top:1px solid #e9ecef;">
                        <h6 style="color:#1e3c72;font-weight:600;">Items (${p.detalles.length})</h6>
                        <div class="items-list-container">`;
                    p.detalles.forEach(d => {
                        const nombreItem = d.nombre_item || d.prestable?.serial || d.prestable?.tipo || `Item`;
                        const estadoEntrega = d.estado_entrega || '—';
                        const estadoDevolucion = d.estado_devolucion || 'Pendiente';
                        const devuelto = d.estado_devolucion && d.estado_devolucion !== 'Pendiente de devolución';

                        html += `
                            <div class="item-checkbox">
                                <span>${devuelto ? '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><polyline points="20 6 9 17 4 12"/></svg>' : '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>'}</span>
                                <div class="item-info">
                                    <div class="item-name">${escapeHtml(nombreItem)}</div>
                                    <div class="item-detail">
                                        Cant: ${d.cantidad} ·
                                        Estado entrega: ${escapeHtml(estadoEntrega)} ·
                                        Devolución: ${escapeHtml(estadoDevolucion)}
                                    </div>
                                </div>
                                <span class="item-badge-estado ${devuelto ? 'devuelto' : 'entregado'}">
                                    ${devuelto ? 'Devuelto' : 'Pendiente'}
                                </span>
                            </div>
                        `;
                    });
                    html += `</div></div>`;
                }

                if (p.observaciones) {
                    html += `<div class="detalle-section" style="margin-top:1rem;padding-top:1rem;border-top:1px solid #e9ecef;">
                        <h6 style="color:#1e3c72;font-weight:600;">Observaciones</h6>
                        <p>${escapeHtml(p.observaciones)}</p>
                    </div>`;
                }
                if (p.condiciones) {
                    html += `<div class="detalle-section" style="margin-top:1rem;padding-top:1rem;border-top:1px solid #e9ecef;">
                        <h6 style="color:#1e3c72;font-weight:600;">Condiciones</h6>
                        <p>${escapeHtml(p.condiciones)}</p>
                    </div>`;
                }

                if (p.extensiones && p.extensiones.length > 0) {
                    html += `<div class="detalle-section" style="margin-top:1rem;padding-top:1rem;border-top:1px solid #e9ecef;">
                        <h6 style="color:#1e3c72;font-weight:600;">Historial de Extensiones</h6>`;
                    p.extensiones.forEach(ext => {
                        html += `
                            <div class="border rounded p-2 mb-2" style="background:#f8f9fc;">
                                <div class="d-flex justify-content-between">
                                    <span><strong>${ext.tipo === 'completa' ? 'Completa' : 'Parcial'}</strong></span>
                                    <span class="text-muted small">${formatDate(ext.created_at)}</span>
                                </div>
                                <div class="small">
                                    <span class="text-muted">De:</span> ${formatDate(ext.fecha_anterior)}
                                    <span class="text-muted">→</span>
                                    <span class="text-muted">A:</span> ${formatDate(ext.fecha_nueva)}
                                </div>
                                <div class="small text-muted">Motivo: ${escapeHtml(ext.motivo)}</div>
                                <div class="small text-muted">Aprobado por: ${escapeHtml(ext.aprobado_por?.nombre || ext.aprobado_por?.usuario || '—')}</div>
                            </div>
                        `;
                    });
                    html += `</div>`;
                }

                document.getElementById(`detallePrestamoContenido`).innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById(`modalDetallePrestamo`));
                modal.show();
            })
            .catch(error => {
                console.error('Error al cargar detalle:', error);
                showToast(`Error al cargar detalle: ${error.message}`, `error`);
                document.getElementById(`detallePrestamoContenido`).innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <p>Error al cargar los detalles del préstamo</p>
                        <p class="text-muted small">${escapeHtml(error.message)}</p>
                    </div>
                `;
            });
    };

    window.abrirModalAprobacion = function(id) {
        document.getElementById(`aprobacionPrestamoId`).value = id;
        document.getElementById(`observacionesAprobacion`).value = ``;
        new bootstrap.Modal(document.getElementById(`modalAprobacion`)).show();
    };

    window.abrirModalRechazo = function(id) {
        document.getElementById(`rechazoPrestamoId`).value = id;
        document.getElementById(`motivoRechazo`).value = ``;
        new bootstrap.Modal(document.getElementById(`modalRechazo`)).show();
    };

    window.abrirModalEntrega = function(id) {
        document.getElementById(`entregaPrestamoId`).value = id;
        document.getElementById(`observacionesEntrega`).value = ``;
        document.getElementById(`fechaEntregaPrestamo`).value = new Date().toISOString().split(`T`)[0];
        document.getElementById(`fechaEntregaDevolucion`).value =
            new Date(Date.now() + 7 * 86400000).toISOString().split(`T`)[0];

        fetch(`/admin/prestamos/${id}`, { headers: { Accept: `application/json` } })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const p = data.data;
                    if (p.fecha_prestamo) document.getElementById(`fechaEntregaPrestamo`).value = p.fecha_prestamo;
                    if (p.fecha_devolucion_esperada) document.getElementById(`fechaEntregaDevolucion`).value = p.fecha_devolucion_esperada;
                }
            })
            .catch(() => {})
            .finally(() => {
                new bootstrap.Modal(document.getElementById(`modalEntrega`)).show();
            });
    };

    window.abrirModalDevolucion = function(id) {
        document.getElementById(`devolucionPrestamoId`).value = id;
        document.getElementById(`fechaDevolucionReal`).value = new Date().toISOString().split(`T`)[0];
        document.getElementById(`observacionesDevolucion`).value = ``;
        document.getElementById(`itemsDevolucionContainer`).innerHTML =
            `<div class="text-center py-3 text-muted">Cargando items...</div>`;

        fetch(`/admin/prestamos/${id}`, { headers: { Accept: `application/json` } })
            .then(response => response.json())
            .then(data => {
                if (!data.success || !data.data) {
                    document.getElementById(`itemsDevolucionContainer`).innerHTML =
                        `<div class="text-danger text-center py-3">No se pudieron cargar los datos del préstamo.</div>`;
                    return;
                }

                const p = data.data;

                document.getElementById(`devolucionCodigo`).textContent = p.codigo;
                document.getElementById(`devolucionDestino`).textContent = p.destino_nombre || '—';
                document.getElementById(`devolucionResponsable`).textContent = p.responsable_receptor?.nombre || '—';

                const detalles = p.detalles || [];

                if (detalles.length === 0) {
                    document.getElementById(`itemsDevolucionContainer`).innerHTML =
                        `<div class="text-center py-3 text-muted">No hay items para devolver.</div>`;
                    return;
                }

                let html = `
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th style="width:40px;"></th>
                                    <th>Item</th>
                                    <th>Cant.</th>
                                    <th>Estado entrega</th>
                                    <th style="width:150px;">Estado devolución</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                detalles.forEach((d, index) => {
                    const nombreItem = d.nombre_item || d.prestable?.serial || d.prestable?.tipo || `Item ${index + 1}`;
                    const checked = d.estado_devolucion && d.estado_devolucion !== 'Pendiente de devolución' ? 'checked' : '';

                    html += `
                        <tr>
                            <td>
                                <input class="form-check-input" type="checkbox" id="dev-${d.id}"
                                       name="items[${d.id}][devuelto]" value="1" ${checked}>
                            </td>
                            <td>
                                <label class="form-check-label fw-medium" for="dev-${d.id}">
                                    ${escapeHtml(nombreItem)}
                                </label>
                                <input type="hidden" name="items[${d.id}][id]" value="${d.id}">
                            </td>
                            <td>${d.cantidad}</td>
                            <td><span class="badge bg-info">${escapeHtml(d.estado_entrega || '—')}</span></td>
                            <td>
                                <select class="form-select form-select-sm" name="items[${d.id}][estado_devolucion]">
                                    <option value="Devuelto en buen estado" ${checked ? 'selected' : ''}>Devuelto en buen estado</option>
                                    <option value="Devuelto con daños">Devuelto con daños</option>
                                    <option value="No devuelto">No devuelto</option>
                                    <option value="Reemplazado">Reemplazado</option>
                                    <option value="Pendiente de devolución">Pendiente</option>
                                </select>
                            </td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                document.getElementById(`itemsDevolucionContainer`).innerHTML = html;
            })
            .catch(() => {
                document.getElementById(`itemsDevolucionContainer`).innerHTML =
                    `<div class="text-danger text-center py-3">Error al cargar los items del préstamo.</div>`;
            })
            .finally(() => {
                new bootstrap.Modal(document.getElementById(`modalDevolucion`)).show();
            });
    };

    window.abrirModalExtension = function(id) {
        document.getElementById(`extensionPrestamoId`).value = id;
        document.getElementById(`fechaNuevaExtension`).value = ``;
        document.getElementById(`motivoExtension`).value = ``;
        document.getElementById(`tipoExtensionCompleta`).checked = true;
        document.getElementById(`itemsExtensionContainer`).style.display = `none`;
        document.getElementById(`itemsExtensionList`).innerHTML =
            `<div class="text-center py-3 text-muted">Seleccione un tipo de extensión.</div>`;

        fetch(`/admin/prestamos/${id}`, { headers: { Accept: `application/json` } })
            .then(response => response.json())
            .then(data => {
                if (!data.success || !data.data) {
                    document.getElementById(`itemsExtensionList`).innerHTML =
                        `<div class="text-danger text-center py-3">No se pudieron cargar los datos del préstamo.</div>`;
                    return;
                }

                const p = data.data;

                document.getElementById(`extensionCodigo`).textContent = p.codigo;
                document.getElementById(`extensionDestino`).textContent = p.destino_nombre || '—';
                document.getElementById(`extensionFechaActual`).textContent = formatDate(p.fecha_devolucion_esperada);
                document.getElementById(`extensionEstado`).textContent = p.estado;
                document.getElementById(`extensionEstado`).className = `badge bg-${p.estado === 'entregado' ? 'success' : 'warning'}`;

                const fechaActual = p.fecha_devolucion_esperada || new Date().toISOString().split('T')[0];
                const fechaMin = new Date(fechaActual);
                fechaMin.setDate(fechaMin.getDate() + 1);
                document.getElementById(`fechaNuevaExtension`).min = fechaMin.toISOString().split('T')[0];

                const detalles = p.detalles || [];

                if (detalles.length === 0) {
                    document.getElementById(`itemsExtensionList`).innerHTML =
                        `<div class="text-center py-3 text-muted">No hay items para extender.</div>`;
                    return;
                }

                let html = `
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th style="width:40px;"></th>
                                    <th>Item</th>
                                    <th>Cant.</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                detalles.forEach((d, index) => {
                    const nombreItem = d.nombre_item || d.prestable?.serial || d.prestable?.tipo || `Item ${index + 1}`;
                    const estadoItem = d.estado_devolucion && d.estado_devolucion !== 'Pendiente de devolución' ? 'Devuelto' : 'Pendiente';

                    html += `
                        <tr>
                            <td>
                                <input class="form-check-input" type="checkbox" id="ext-${d.id}"
                                       name="items_ids[]" value="${d.id}" ${estadoItem === 'Pendiente' ? 'checked' : ''}>
                            </td>
                            <td>
                                <label class="form-check-label" for="ext-${d.id}">
                                    ${escapeHtml(nombreItem)}
                                </label>
                            </td>
                            <td>${d.cantidad}</td>
                            <td><span class="badge ${estadoItem === 'Pendiente' ? 'bg-warning' : 'bg-secondary'}">${estadoItem}</span></td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                document.getElementById(`itemsExtensionList`).innerHTML = html;
            })
            .catch(() => {
                document.getElementById(`itemsExtensionList`).innerHTML =
                    `<div class="text-danger text-center py-3">Error al cargar los items del préstamo.</div>`;
            })
            .finally(() => {
                new bootstrap.Modal(document.getElementById(`modalExtension`)).show();
            });
    };

    window.abrirModalCancelar = function(id) {
        document.getElementById(`cancelarPrestamoId`).value = id;
        document.getElementById(`motivoCancelacion`).value = ``;
        new bootstrap.Modal(document.getElementById(`modalCancelar`)).show();
    };

    window.toggleTipoExtension = function() {
        const tipoCompleta = document.getElementById(`tipoExtensionCompleta`);
        const container = document.getElementById(`itemsExtensionContainer`);
        if (container) {
            container.style.display = tipoCompleta && tipoCompleta.checked ? `none` : `block`;
        }
    };

    // ============================================================
    // EVENTOS DE FORMULARIOS DE ACCIONES
    // ============================================================
    document.getElementById(`formAprobacion`)?.addEventListener(`submit`, function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.reportValidity(); return; }
        const id = document.getElementById(`aprobacionPrestamoId`).value;
        const formData = new FormData(this);
        fetch(`/admin/prestamos/${id}/aprobar`, {
            method: `POST`,
            body: formData,
            headers: { Accept: `application/json` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById(`modalAprobacion`)).hide();
                showToast(data.message || `Préstamo aprobado`, `success`);
                cargarSolicitudes();
                cargarPrestamosActivos();
            } else {
                showToast(data.message || `Error al aprobar`, `error`);
            }
        })
        .catch(() => showToast(`Error de conexión`, `error`));
    });

    document.getElementById(`formRechazo`)?.addEventListener(`submit`, function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.reportValidity(); return; }
        const id = document.getElementById(`rechazoPrestamoId`).value;
        const formData = new FormData(this);
        fetch(`/admin/prestamos/${id}/rechazar`, {
            method: `POST`,
            body: formData,
            headers: { Accept: `application/json` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById(`modalRechazo`)).hide();
                showToast(data.message || `Préstamo rechazado`, `success`);
                cargarSolicitudes();
                cargarPrestamosActivos();
            } else {
                showToast(data.message || `Error al rechazar`, `error`);
            }
        })
        .catch(() => showToast(`Error de conexión`, `error`));
    });

    document.getElementById(`formEntrega`)?.addEventListener(`submit`, function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.reportValidity(); return; }
        const id = document.getElementById(`entregaPrestamoId`).value;
        const formData = new FormData(this);
        const submitBtn = this.querySelector(`button[type="submit"]`);
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = `Procesando...`; }
        fetch(`/admin/prestamos/${id}/entregar`, {
            method: `POST`,
            body: formData,
            headers: { Accept: `application/json` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById(`modalEntrega`)).hide();
                showToast(data.message || `Entrega registrada`, `success`);
                cargarSolicitudes();
                cargarPrestamosActivos();
            } else {
                showToast(data.message || `Error al registrar entrega`, `error`);
            }
        })
        .catch(() => showToast(`Error de conexión`, `error`))
        .finally(() => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = `Registrar Entrega`; }
        });
    });

    document.getElementById(`formDevolucion`)?.addEventListener(`submit`, function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.reportValidity(); return; }
        const id = document.getElementById(`devolucionPrestamoId`).value;
        const formData = new FormData(this);
        const submitBtn = this.querySelector(`button[type="submit"]`);
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = `Procesando...`; }
        fetch(`/admin/prestamos/${id}/devolver`, {
            method: `POST`,
            body: formData,
            headers: { Accept: `application/json` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById(`modalDevolucion`)).hide();
                showToast(data.message || `Devolución registrada`, `success`);
                cargarPrestamosActivos();
                cargarPrestamosFinalizados();
            } else {
                showToast(data.message || `Error al registrar devolución`, `error`);
            }
        })
        .catch(() => showToast(`Error de conexión`, `error`))
        .finally(() => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = `Registrar Devolución`; }
        });
    });

    document.getElementById(`formExtension`)?.addEventListener(`submit`, function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.reportValidity(); return; }

        const tipo = document.querySelector('input[name="tipo"]:checked')?.value;
        if (tipo === 'parcial') {
            const checked = document.querySelectorAll('#itemsExtensionList input[type="checkbox"]:checked');
            if (checked.length === 0) {
                showToast(`Debe seleccionar al menos un item para extender`, `warning`);
                return;
            }
        }

        const id = document.getElementById(`extensionPrestamoId`).value;
        const formData = new FormData(this);
        const submitBtn = this.querySelector(`button[type="submit"]`);
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = `Procesando...`; }
        fetch(`/admin/prestamos/${id}/extender`, {
            method: `POST`,
            body: formData,
            headers: { Accept: `application/json` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById(`modalExtension`)).hide();
                showToast(data.message || `Préstamo extendido`, `success`);
                cargarPrestamosActivos();
            } else {
                showToast(data.message || `Error al extender`, `error`);
            }
        })
        .catch(() => showToast(`Error de conexión`, `error`))
        .finally(() => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = `Extender Préstamo`; }
        });
    });

    document.getElementById(`formCancelar`)?.addEventListener(`submit`, function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.reportValidity(); return; }
        const id = document.getElementById(`cancelarPrestamoId`).value;
        const formData = new FormData(this);
        const submitBtn = this.querySelector(`button[type="submit"]`);
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = `Procesando...`; }
        fetch(`/admin/prestamos/${id}/cancelar`, {
            method: `POST`,
            body: formData,
            headers: { Accept: `application/json` }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById(`modalCancelar`)).hide();
                showToast(data.message || `Préstamo cancelado`, `success`);
                cargarSolicitudes();
                cargarPrestamosActivos();
            } else {
                showToast(data.message || `Error al cancelar`, `error`);
            }
        })
        .catch(() => showToast(`Error de conexión`, `error`))
        .finally(() => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = `Cancelar Préstamo`; }
        });
    });

    // ============================================================
    // EVENTOS DE TABS
    // ============================================================
    const tabs = {
        'solicitudes-tab': { tab: 'solicitudes', fn: cargarSolicitudes },
        'activos-tab': { tab: 'activos', fn: cargarPrestamosActivos },
        'finalizados-tab': { tab: 'finalizados', fn: cargarPrestamosFinalizados }
    };

    Object.keys(tabs).forEach(tabId => {
        const tabEl = document.getElementById(tabId);
        if (tabEl) {
            tabEl.addEventListener('shown.bs.tab', function() {
                const info = tabs[tabId];
                if (info) {
                    currentTab = info.tab;
                    info.fn();
                }
            });
        }
    });

    // ============================================================
    // EVENTOS DE FILTROS
    // ============================================================
    document.getElementById(`buscarSolicitudes`)?.addEventListener(`input`, debounce(cargarSolicitudes, 300));
    document.getElementById(`buscarActivos`)?.addEventListener(`input`, debounce(cargarPrestamosActivos, 300));
    document.getElementById(`filtroTipoActivos`)?.addEventListener(`change`, cargarPrestamosActivos);
    document.getElementById(`filtroEstadoActivos`)?.addEventListener(`change`, cargarPrestamosActivos);
    document.getElementById(`buscarFinalizados`)?.addEventListener(`input`, debounce(cargarPrestamosFinalizados, 300));
    document.getElementById(`filtroEstadoFinalizados`)?.addEventListener(`change`, cargarPrestamosFinalizados);
    document.getElementById(`filtroFechaDesde`)?.addEventListener(`change`, cargarPrestamosFinalizados);
    document.getElementById(`filtroFechaHasta`)?.addEventListener(`change`, cargarPrestamosFinalizados);

    // ============================================================
    // CERRAR RESULTADOS AL HACER CLICK FUERA
    // ============================================================
    document.addEventListener(`click`, function(e) {
        const resultados = document.getElementById(`resultadosBusqueda`);
        const buscar = document.getElementById(`buscarItem`);
        if (resultados && buscar && !buscar.contains(e.target) && !resultados.contains(e.target)) {
            resultados.style.display = `none`;
        }
    });

    // ============================================================
    // CARGA INICIAL - Cargar todas las tablas
    // ============================================================
    cargarSolicitudes();
    cargarPrestamosActivos();
    cargarPrestamosFinalizados();
});
