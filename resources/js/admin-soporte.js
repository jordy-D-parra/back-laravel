// resources/js/admin-soporte.js

document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // VARIABLES GLOBALES
    // ============================================================
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let fichasData = [];
    let currentPage = 1;
    let lastPage = 1;
    let timeoutBusqueda = null;
    let fichaAEliminar = null;
    let correoActualSoporte = null;
    let itemWizardSoporteCount = 0;
    let intervaloNotificaciones = null;

    // ============================================================
    // HELPERS
    // ============================================================
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatearFecha(fecha) {
        if (!fecha) return '---';
        if (fecha instanceof Date) {
            return fecha.toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }
        let str = String(fecha).trim();
        if (!str) return '---';
        let dateStr = str.includes('T') ? str : `${str}T00:00:00`;
        let d = new Date(dateStr);
        if (isNaN(d.getTime())) return '---';
        return d.toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatearFechaHora(fecha) {
        if (!fecha) return '---';
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

        const colores = { success: '#1e7e34', error: '#c5221f', warning: '#f6c23e', info: '#1e3c72' };
        const iconos = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: ${colores[tipo] || colores.info}; color: white;
            border-radius: 12px; padding: 14px 18px; margin-bottom: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            display: flex; align-items: center; gap: 12px;
            font-size: 0.9rem; font-weight: 500;
            animation: slideInRight 0.3s ease-out;
            cursor: pointer; white-space: pre-line; line-height: 1.4;
        `;
        toast.innerHTML = `
            <span style="flex-shrink:0; display:flex;">${iconos[tipo] || iconos.info}</span>
            <span style="flex:1;">${escapeHtml(mensaje)}</span>
        `;

        toast.addEventListener('click', () => {
            toast.style.transition = 'all 0.3s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        });

        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.transition = 'all 0.3s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // ============================================================
    // CARGA DE FICHAS
    // ============================================================
    function cargarFichas(page) {
        page = page || 1;

        const buscar = document.getElementById('buscarFichas')?.value || '';
        const estado = document.getElementById('filtroEstadoFichas')?.value || '';

        const params = new URLSearchParams({ page, buscar, estado });

        const tbody = document.getElementById('tablaFichas');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Cargando fichas...</p>
            </td></tr>`;
        }

        fetch(`/admin/soporte?${params.toString()}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const fichas = data.data || [];
            fichasData = fichas;
            currentPage = data.current_page || 1;
            lastPage = data.last_page || 1;
            renderizarFichas(fichas);
            renderizarPaginacionFichas();
        })
        .catch(error => {
            console.error('Error al cargar fichas:', error);
            if (tbody) tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Error al cargar fichas: ${escapeHtml(error.message)}</td></tr>`;
        });
    }

    function renderizarFichas(fichas) {
        const tbody = document.getElementById('tablaFichas');
        if (!tbody) return;

        if (!fichas || fichas.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" class="mb-2">
                            <rect x="2" y="6" width="20" height="12" rx="2"/>
                        </svg>
                        <p>No hay fichas de soporte registradas</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';

        fichas.forEach(f => {
            const activo = f.activo || {};
            const modelo = activo.modelo || {};
            const estado = f.estado || 'en_proceso';
            const estadoLabel = estado === 'en_proceso' ? 'En Proceso' :
                                estado === 'finalizado' ? 'Finalizado' :
                                estado === 'aceptada' ? 'Aceptada' :
                                estado === 'rechazada' ? 'Rechazada' :
                                estado === 'cancelada' ? 'Cancelada' : estado;

            const estadoBadge = estado === 'en_proceso' ? 'badge-estado-en-proceso' :
                                estado === 'finalizado' ? 'badge-estado-finalizado' :
                                estado === 'aceptada' ? 'badge-estado-pendiente' :
                                estado === 'rechazada' ? 'badge-estado-rechazado' :
                                estado === 'cancelada' ? 'badge-estado-cancelado' : 'badge-estado';

            let badgeFecha = '<span class="badge-fecha-entrega badge-fecha-sin">Sin fecha</span>';
            if (f.fecha_requerida_entrega) {
                const fechaStr = formatearFecha(f.fecha_requerida_entrega);
                const estaVencida = new Date(f.fecha_requerida_entrega) < new Date() && estado !== 'finalizado';
                let dias = null;
                if (!estaVencida) {
                    const hoy = new Date();
                    hoy.setHours(0,0,0,0);
                    const fReq = new Date(f.fecha_requerida_entrega);
                    fReq.setHours(0,0,0,0);
                    dias = Math.round((fReq - hoy) / (1000 * 60 * 60 * 24));
                }
                let clase = 'badge-fecha-vigente';
                let texto = fechaStr;
                if (estaVencida) {
                    clase = 'badge-fecha-vencida';
                    texto += ' (Vencida)';
                } else if (dias !== null && dias <= 3) {
                    clase = 'badge-fecha-proxima';
                    texto += ` (${dias} d)`;
                }
                badgeFecha = `<span class="badge-fecha-entrega ${clase}">${texto}</span>`;
            }

            // ✅ Técnico: mostrar nombre, o "No asignado" si no hay
            const tecnicoTexto = f.tecnico_nombre
                ? escapeHtml(f.tecnico_nombre)
                : (f.tecnico_id
                    ? '<span class="text-warning">Sin nombre</span>'
                    : '<span class="text-muted">No asignado</span>');

            html += `
                <tr>
                    <td data-label="Activo">
                        ${activo.serial ? `
                            <span class="fw-medium" style="color:#1e3c72;">${escapeHtml(activo.serial)}</span>
                            <br>
                            <small class="text-muted">${escapeHtml(modelo.nombre || 'N/A')}</small>
                        ` : '<span class="text-muted">Equipo externo</span>'}
                    </td>
                    <td data-label="Técnico">${tecnicoTexto}</td>
                    <td data-label="Reporta">${escapeHtml(f.usuario_reporta_nombre || '---')}</td>
                    <td data-label="Ingreso">
                        ${f.fecha_ingreso ? `
                            <small class="fw-medium">${formatearFecha(f.fecha_ingreso)}</small>
                        ` : '<small class="text-muted">---</small>'}
                    </td>
                    <td data-label="F. Requerida">${badgeFecha}</td>
                    <td data-label="Salida">
                        ${f.fecha_salida ? `
                            <small class="fw-medium" style="color:#1e7e34;">${formatearFecha(f.fecha_salida)}</small>
                        ` : '<small class="text-muted">---</small>'}
                    </td>
                    <td data-label="Estado">
                        <span class="badge-estado ${estadoBadge}">${estadoLabel}</span>
                    </td>
                    <td data-label="Acciones" class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <button type="button" class="btn-action" onclick="verDetalle(${f.id})" title="Ver detalle">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 8v4"/>
                                    <path d="M12 16h.01"/>
                                </svg>
                            </button>
                            <button type="button" class="btn-action btn-reporte" onclick="generarReporteSoporte(${f.id})" title="Ficha técnica (PDF)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                    <line x1="10" y1="9" x2="8" y2="9"/>
                                </svg>
                            </button>
                            ${(estado === 'en_proceso' || estado === 'aceptada') ? `
                                <button type="button" class="btn-cerrar-ficha" onclick="abrirModalCerrarFicha(${f.id})" title="Cerrar ficha">
                                    ✓ Cerrar
                                </button>
                            ` : ''}
                            <button type="button" class="btn-action text-danger" onclick="confirmarEliminar(${f.id})" title="Eliminar">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function renderizarPaginacionFichas() {
        const container = document.getElementById('paginacionFichas');
        if (!container) return;

        const start = Math.max(1, currentPage - 2);
        const end = Math.min(lastPage, currentPage + 2);

        let html = `<div class="pagination-info">
            ${lastPage > 0 ? `Página ${currentPage} de ${lastPage}` : 'Sin registros'}
        </div>
        <div class="pagination-btns">
            <button class="pagination-btn ${currentPage === 1 ? 'disabled' : ''}"
                    onclick="cambiarPagina(${currentPage - 1})"
                    ${currentPage === 1 ? 'disabled' : ''}>«</button>
        `;

        if (start > 1) {
            html += `<button class="pagination-btn" onclick="cambiarPagina(1)">1</button>`;
            if (start > 2) html += `<span class="pagination-ellipsis">...</span>`;
        }

        for (let i = start; i <= end; i++) {
            html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}"
                            onclick="cambiarPagina(${i})">${i}</button>`;
        }

        if (end < lastPage) {
            if (end < lastPage - 1) html += `<span class="pagination-ellipsis">...</span>`;
            html += `<button class="pagination-btn" onclick="cambiarPagina(${lastPage})">${lastPage}</button>`;
        }

        html += `<button class="pagination-btn ${currentPage === lastPage ? 'disabled' : ''}"
                        onclick="cambiarPagina(${currentPage + 1})"
                        ${currentPage === lastPage ? 'disabled' : ''}>»</button>
        </div>`;

        container.innerHTML = html;
    }

    window.cambiarPagina = function(page) {
        if (page < 1 || page > lastPage) return;
        currentPage = page;
        cargarFichas(currentPage);
        document.querySelector('#tablaFichas')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // ============================================================
    // DETALLE DE FICHA
    // ============================================================
    window.verDetalle = function(id) {
        const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
        const body = document.getElementById('detalleContenido');

        body.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3 text-muted">Cargando detalles...</p></div>`;
        modal.show();

        fetch(`/admin/soporte/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const f = data.data || data;
            body.innerHTML = `
                <div class="ficha-detalle-wrap">
                    <div class="ficha-header">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <h4>Ficha #${f.id}</h4>
                                <div class="subtitulo">${escapeHtml(f.activo?.serial || 'Equipo Externo')} · ${escapeHtml(f.activo?.modelo?.marca?.nombre || '')} ${escapeHtml(f.activo?.modelo?.nombre || '')}</div>
                            </div>
                            <span class="estado-badge" style="background: ${f.estado === 'en_proceso' ? '#f59e0b' : f.estado === 'finalizado' ? '#1e7e34' : '#6c757d'};">
                                ${f.estado === 'en_proceso' ? 'En Proceso' : f.estado === 'finalizado' ? 'Finalizado' : f.estado}
                            </span>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-label">Técnico</div>
                            <div class="info-value">${escapeHtml(f.tecnico_nombre || 'No asignado')}</div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">Reporta</div>
                            <div class="info-value">${escapeHtml(f.usuario_reporta_nombre || 'No especificado')}</div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">Institución</div>
                            <div class="info-value">${escapeHtml(f.activo?.institucion?.nombre || 'N/A')}</div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">Fecha Ingreso</div>
                            <div class="info-value">${formatearFecha(f.fecha_ingreso)}</div>
                        </div>
                    </div>

                    <div class="seccion-titulo">Diagnóstico</div>
                    <div class="texto-bloque diagnostico">${escapeHtml(f.diagnostico || 'Sin diagnóstico registrado.')}</div>

                    <div class="seccion-titulo">Trabajo Realizado</div>
                    <div class="texto-bloque trabajo">${escapeHtml(f.trabajo_realizado || 'Sin trabajo registrado.')}</div>

                    <div class="seccion-titulo">Observaciones</div>
                    <div class="texto-bloque observaciones">${escapeHtml(f.observaciones || 'Sin observaciones.')}</div>

                    <div class="seccion-titulo">Componentes Revisados</div>
                    <div class="componentes-grid">
                        ${(f.detalles || []).map(d => `
                            <div class="componente-card">
                                <div class="componente-nombre">${escapeHtml(d.componente_nombre || d.componente?.tipo || 'Componente')}</div>
                                <div class="componente-obs">
                                    Estado Ingreso: <strong>${escapeHtml(d.estado_ingreso || 'N/A')}</strong><br>
                                    Estado Salida: <strong>${escapeHtml(d.estado_salida || 'N/A')}</strong>
                                    ${d.observaciones ? `<br>${escapeHtml(d.observaciones)}` : ''}
                                </div>
                            </div>
                        `).join('') || '<p class="text-muted">No hay componentes registrados.</p>'}
                    </div>
                </div>
            `;
        })
        .catch(err => {
            console.error(err);
            body.innerHTML = `<div class="alert alert-danger">Error al cargar los detalles.</div>`;
        });
    };

    // ============================================================
    // GENERAR REPORTE INDIVIDUAL
    // ============================================================
    window.generarReporteSoporte = function(id) {
        const url = `/admin/reportes/ficha/soporte-individual/${id}`;
        const ventana = window.open(url, '_blank');
        if (!ventana) {
            mostrarNotificacion('warning', 'Por favor, permita ventanas emergentes para generar el reporte.');
        }
    };

    // ============================================================
    // CERRAR FICHA
    // ============================================================
    window.abrirModalCerrarFicha = function(id) {
        document.getElementById('cerrarFichaId').value = id;
        document.getElementById('cerrarTrabajoRealizado').value = '';
        document.getElementById('cerrarObservacionesFinales').value = '';
        document.getElementById('componentesContainer').innerHTML = '<div class="text-center py-3 text-muted">Cargando componentes...</div>';

        new bootstrap.Modal(document.getElementById('modalCerrarFicha')).show();

        fetch(`/admin/soporte/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const f = data.data || data;
            const detalles = f.detalles || [];

            if (detalles.length === 0) {
                document.getElementById('componentesContainer').innerHTML = '<p class="text-muted">No hay componentes registrados.</p>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Componente</th><th>Estado Ingreso</th><th>Estado Salida</th><th>Observaciones</th></tr></thead><tbody>';
            detalles.forEach(d => {
                html += `
                    <tr>
                        <td><strong>${escapeHtml(d.componente_nombre || d.componente?.tipo || 'Componente')}</strong></td>
                        <td>${escapeHtml(d.estado_ingreso || 'N/A')}</td>
                        <td>
                            <select class="form-select form-select-sm" name="componentes[${d.id}][estado_salida]">
                                <option value="funcionando">Funcionando</option>
                                <option value="reparado">Reparado</option>
                                <option value="reemplazado">Reemplazado</option>
                                <option value="dañado">Dañado</option>
                                <option value="no_aplica">No aplica</option>
                            </select>
                        </td>
                        <td><input type="text" class="form-control form-control-sm" name="componentes[${d.id}][observaciones]" value="${escapeHtml(d.observaciones || '')}"></td>
                    </tr>
                `;
            });
            html += '</tbody></table></div>';
            document.getElementById('componentesContainer').innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            document.getElementById('componentesContainer').innerHTML = '<p class="text-danger">Error al cargar componentes.</p>';
        });
    };

    document.getElementById('formCerrarFicha')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('cerrarFichaId').value;
        const btn = this.querySelector('button[type="submit"]');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Finalizando...';

        fetch(`/admin/soporte/${id}/cerrar`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: new FormData(this)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalCerrarFicha')).hide();
                mostrarNotificacion('success', data.message || 'Ficha cerrada exitosamente');
                cargarFichas(currentPage);
            } else {
                mostrarNotificacion('error', data.message || 'Error al cerrar la ficha');
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error de conexión: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = original;
        });
    });

    // ============================================================
    // ELIMINAR FICHA
    // ============================================================
    window.confirmarEliminar = function(id) {
        fichaAEliminar = id;
        document.getElementById('deleteNombre').textContent = `#${id}`;
        new bootstrap.Modal(document.getElementById('modalEliminar')).show();
    };

    document.getElementById('btnConfirmarEliminar')?.addEventListener('click', function() {
        if (!fichaAEliminar) return;

        const btn = this;
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Eliminando...';

        fetch(`/admin/soporte/${fichaAEliminar}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
                mostrarNotificacion('success', data.message || 'Ficha eliminada');
                cargarFichas(currentPage);
            } else {
                mostrarNotificacion('error', data.message || 'Error al eliminar');
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error de conexión: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = original;
        });
    });

    // ============================================================
    // CREAR FICHA MANUAL
    // ============================================================
    const inputActivo = document.getElementById('activoBuscarInput');
    const dropdownActivo = document.getElementById('activoDropdown');
    const infoActivo = document.getElementById('activoSeleccionadoInfo');

    if (inputActivo) {
        inputActivo.addEventListener('input', debounce(function() {
            const buscar = this.value.trim();
            if (buscar.length < 2) {
                dropdownActivo.style.display = 'none';
                return;
            }

            fetch(`/admin/activos?buscar=${encodeURIComponent(buscar)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                const activos = data.data || [];
                if (activos.length === 0) {
                    dropdownActivo.innerHTML = '<div class="list-group-item text-muted">No se encontraron activos</div>';
                } else {
                    dropdownActivo.innerHTML = activos.slice(0, 10).map(a => `
                        <a href="#" class="list-group-item list-group-item-action" onclick="seleccionarActivoFicha(${a.id}, '${escapeHtml(a.serial)}', '${escapeHtml((a.modelo?.marca?.nombre || '') + ' ' + (a.modelo?.nombre || ''))}'); return false;">
                            <strong>${escapeHtml(a.serial)}</strong>
                            <small class="d-block text-muted">${escapeHtml(a.modelo?.marca?.nombre || '')} ${escapeHtml(a.modelo?.nombre || '')}</small>
                        </a>
                    `).join('');
                }
                dropdownActivo.style.display = 'block';
            })
            .catch(err => console.error(err));
        }, 300));
    }

    window.seleccionarActivoFicha = function(id, serial, equipo) {
        document.getElementById('fichaActivoId').value = id;
        document.getElementById('activoBuscarInput').value = `${serial} - ${equipo}`;
        dropdownActivo.style.display = 'none';
        infoActivo.innerHTML = `✅ <strong>${escapeHtml(serial)}</strong> - ${escapeHtml(equipo)}
            <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="window.limpiarActivoSeleccionado()">✕</button>`;
        infoActivo.style.display = 'block';
    };

    window.limpiarActivoSeleccionado = function() {
        document.getElementById('fichaActivoId').value = '';
        document.getElementById('activoBuscarInput').value = '';
        infoActivo.style.display = 'none';
    };

    document.getElementById('formCrearFicha')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creando...';

        fetch('/admin/soporte', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: new FormData(this)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalCrearFicha')).hide();
                mostrarNotificacion('success', data.message || 'Ficha creada exitosamente');
                cargarFichas(1);
            } else {
                let msg = data.message || 'Error al crear la ficha';
                if (data.errors) msg += '\n' + Object.values(data.errors).flat().join('\n');
                mostrarNotificacion('error', msg);
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error de conexión: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = original;
        });
    });

    // ============================================================
    // ✅ BUSCADOR DE TÉCNICOS CON CUPOS DISPONIBLES
    // ============================================================
    const tecnicoBuscarInput = document.getElementById('tecnicoBuscarInput');
    const tecnicoSearchResults = document.getElementById('tecnicoSearchResults');

    if (tecnicoBuscarInput) {
        tecnicoBuscarInput.addEventListener('input', debounce(function() {
            const buscar = this.value.trim();

            if (buscar.length < 2) {
                tecnicoSearchResults.style.display = 'none';
                return;
            }

            tecnicoSearchResults.style.display = 'block';
            tecnicoSearchResults.innerHTML = '<div class="p-2 text-muted small">Buscando técnicos...</div>';

            fetch(`/admin/api/tecnicos?search=${encodeURIComponent(buscar)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(tecnicos => {
                if (!tecnicos || tecnicos.length === 0) {
                    tecnicoSearchResults.innerHTML = '<div class="p-2 text-muted small">No se encontraron técnicos</div>';
                    return;
                }

                tecnicoSearchResults.innerHTML = '';

                tecnicos.forEach(t => {
                    const trabajador = t.trabajador;
                    const nombreCompleto = trabajador
                        ? `${trabajador.nombre} ${trabajador.apellido}`.trim()
                        : t.usuario;

                    const cupos = t.cupos_disponibles !== undefined ? t.cupos_disponibles : 5;
                    const disponible = cupos > 0;

                    const item = document.createElement('div');
                    item.className = 'p-2 ' + (disponible ? '' : 'text-muted');
                    item.style.cssText = `border-bottom: 1px solid #e9ecef; cursor: ${disponible ? 'pointer' : 'not-allowed'}; ${disponible ? '' : 'opacity: 0.6;'}`;

                    item.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${escapeHtml(nombreCompleto)}</strong><br>
                                <small class="text-muted">C.I: ${escapeHtml(trabajador?.cedula || 'N/A')} · ${escapeHtml(t.usuario)}</small>
                            </div>
                            <span class="badge ${disponible ? 'bg-success' : 'bg-danger'}">
                                ${disponible ? cupos + ' cupo(s)' : 'Sin cupo'}
                            </span>
                        </div>
                    `;

                    if (disponible) {
                        item.addEventListener('click', () => seleccionarTecnicoFicha(t, nombreCompleto));
                    }

                    tecnicoSearchResults.appendChild(item);
                });
            })
            .catch(err => {
                console.error(err);
                tecnicoSearchResults.innerHTML = '<div class="p-2 text-danger small">Error al buscar técnicos</div>';
            });
        }, 300));
    }

    // ✅ Seleccionar técnico: guarda ID Y NOMBRE
    function seleccionarTecnicoFicha(tecnico, nombreCompleto) {
        document.getElementById('fichaTecnicoId').value = tecnico.id;
        document.getElementById('fichaTecnicoNombre').value = nombreCompleto;

        document.getElementById('tecnicoEncontradoNombre').textContent = nombreCompleto;
        document.getElementById('tecnicoEncontradoCedula').textContent =
            `C.I: ${tecnico.trabajador?.cedula || 'N/A'}`;
        document.getElementById('tecnicoEncontradoUsuario').textContent =
            `Usuario: ${tecnico.usuario}`;

        document.getElementById('tecnicoEncontrado').style.display = 'block';
        tecnicoSearchResults.style.display = 'none';
        tecnicoBuscarInput.value = '';
    }

    // ============================================================
    // EVENTOS DE FILTROS
    // ============================================================
    document.getElementById('buscarFichas')?.addEventListener('input', debounce(() => {
        currentPage = 1;
        cargarFichas(1);
    }, 300));

    document.getElementById('filtroEstadoFichas')?.addEventListener('change', () => {
        currentPage = 1;
        cargarFichas(1);
    });

    document.getElementById('limpiarFiltros')?.addEventListener('click', function() {
        const buscar = document.getElementById('buscarFichas');
        const estado = document.getElementById('filtroEstadoFichas');
        if (buscar) buscar.value = '';
        if (estado) estado.value = '';
        currentPage = 1;
        cargarFichas(1);
    });

    // ============================================================
    // ✅ TÉCNICOS ASIGNADOS
    // ============================================================
    const tabTecnicosAsignados = document.getElementById('tab-tecnicos-asignados');
    const containerTecnicos = document.getElementById('tecnicos-asignados-container');

    if (tabTecnicosAsignados) {
        tabTecnicosAsignados.addEventListener('shown.bs.tab', function() {
            cargarTecnicosAsignados();
        });
    }

    async function cargarTecnicosAsignados() {
        if (!containerTecnicos) return;

        containerTecnicos.innerHTML = `
            <div class="text-center py-5 text-muted">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2">Cargando técnicos y equipos asignados...</p>
            </div>
        `;

        try {
           const response = await fetch("/admin/soporte/tecnicos-asignados", {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al cargar los datos.');
            }

            renderizarTecnicosAsignados(data.data);

        } catch (error) {
            console.error('Error al cargar técnicos asignados:', error);
            containerTecnicos.innerHTML = `<div class="alert alert-danger">Error: ${escapeHtml(error.message)}</div>`;
        }
    }

    function renderizarTecnicosAsignados(tecnicos) {
        if (!tecnicos || tecnicos.length === 0) {
            containerTecnicos.innerHTML = `
                <div class="sin-tecnicos">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <h5>No hay técnicos con equipos en reparación</h5>
                    <p>Todos los equipos han sido reparados o no tienen técnico asignado.</p>
                </div>
            `;
            return;
        }

        let html = '';
        tecnicos.forEach(item => {
            html += `
                <div class="card-tecnico-asignado">
                    <div class="tecnico-header-card">
                        <div class="tecnico-info">
                            <div class="tecnico-avatar">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <span class="tecnico-nombre">${escapeHtml(item.tecnico.nombre)}</span>
                        </div>
                        <span class="tecnico-badge">
                            ${item.equipos.length} equipo(s) en reparación
                        </span>
                    </div>
                    <div class="tecnico-tabla-wrapper">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Ficha</th>
                                    <th>Equipo</th>
                                    <th>Serial</th>
                                    <th>Institución</th>
                                    <th>Fecha Ingreso</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${item.equipos.map(equipo => `
                                    <tr>
                                        <td><span class="badge-ficha-id">#${equipo.ficha_id}</span></td>
                                        <td>${escapeHtml(equipo.equipo_nombre)}</td>
                                        <td><strong style="color:#1e3c72;">${escapeHtml(equipo.equipo_serial)}</strong></td>
                                        <td>${escapeHtml(equipo.institucion)}</td>
                                        <td>${escapeHtml(equipo.fecha_ingreso)}</td>
                                        <td class="text-center">
                                            <button class="btn-action btn-outline-primary-dark" onclick='mostrarDetalleEquipo(${JSON.stringify(equipo).replace(/'/g, "&apos;")})' title="Mostrar detalles">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                                Mostrar
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        });

        containerTecnicos.innerHTML = html;
    }

    window.mostrarDetalleEquipo = function(equipo) {
        const modalBody = document.getElementById('detalleEquipoReparacionBody');
        const modalLabel = document.getElementById('detalleEquipoReparacionLabel');

        modalLabel.textContent = `Detalle del Equipo: ${equipo.equipo_serial}`;

        modalBody.innerHTML = `
            <div class="container-fluid">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100" style="border-left: 4px solid #1e3c72;">
                            <h6 class="fw-bold mb-3" style="color:#1e3c72;">Información del Equipo</h6>
                            <p class="mb-2"><strong>Equipo:</strong> ${escapeHtml(equipo.equipo_nombre)}</p>
                            <p class="mb-2"><strong>Serial:</strong> ${escapeHtml(equipo.equipo_serial)}</p>
                            <p class="mb-0"><strong>Institución:</strong> ${escapeHtml(equipo.institucion)}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100" style="border-left: 4px solid #f59e0b;">
                            <h6 class="fw-bold mb-3" style="color:#1e3c72;">Información de la Reparación</h6>
                            <p class="mb-2"><strong>Ficha #:</strong> ${equipo.ficha_id}</p>
                            <p class="mb-0"><strong>Fecha de Ingreso:</strong> ${escapeHtml(equipo.fecha_ingreso)}</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3" style="border-left: 4px solid #17a2b8;">
                            <h6 class="fw-bold mb-3" style="color:#1e3c72;">Diagnóstico / Problema</h6>
                            <p class="mb-0" style="white-space: pre-wrap;">${escapeHtml(equipo.diagnostico || 'No especificado')}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('modalDetalleEquipoReparacion'));
        modal.show();
    };

    // ============================================================
    // CORREOS
    // ============================================================
    async function cargarCorreosSoporte() {
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
            const response = await fetch(`/admin/soporte/correos/lista?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            renderizarCorreosSoporte(data.data || []);
        } catch (error) {
            console.error('Error al cargar correos:', error);
            container.innerHTML = `
                <div class="text-center py-5 text-danger">
                    <p>Error al cargar correos: ${escapeHtml(error.message)}</p>
                    <button class="btn btn-sm btn-primary-dark mt-2" onclick="cargarCorreosSoporte()">Reintentar</button>
                </div>
            `;
        }
    }

    function renderizarCorreosSoporte(correos) {
        const container = document.getElementById('listaCorreos');
        if (!container) return;

        if (!correos.length) {
            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M22 7l-10 7L2 7"/>
                    </svg>
                    <p class="mt-3">No hay correos de soporte recibidos</p>
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
                <div class="${clases.join(' ')}" onclick="abrirCorreoSoporte(${c.id})">
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

    async function actualizarBadgeCorreosSoporte() {
        try {
            const response = await fetch('/admin/soporte/correos/contador', {
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

    window.revisarCorreos = async function() {
        const btn = document.getElementById('btnRevisarCorreos');
        if (!btn) return;
        const original = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Revisando...';
        btn.disabled = true;

        try {
            const response = await fetch('/admin/soporte/correos/revisar', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const data = await response.json();
            mostrarNotificacion('info', data.message || 'Revisión completada');
            cargarCorreosSoporte();
            actualizarBadgeCorreosSoporte();
        } catch (error) {
            mostrarNotificacion('error', 'Error al revisar correos: ' + error.message);
        } finally {
            btn.innerHTML = original;
            btn.disabled = false;
        }
    };

    window.abrirCorreoSoporte = async function(id) {
        try {
            const response = await fetch(`/admin/soporte/correos/${id}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            if (!data.success) {
                mostrarNotificacion('error', 'Error al cargar el correo');
                return;
            }

            correoActualSoporte = data.data;

            document.getElementById('correoFromSoporte').textContent =
                `${correoActualSoporte.from_name || ''} <${correoActualSoporte.from_email}>`;
            document.getElementById('correoFechaSoporte').textContent =
                formatearFechaHora(correoActualSoporte.received_at);
            document.getElementById('correoAsuntoSoporte').textContent =
                correoActualSoporte.subject || '(Sin asunto)';
            document.getElementById('correoCuerpoSoporte').textContent =
                correoActualSoporte.body_text || '(Sin contenido)';

            if (correoActualSoporte.procesado) {
                document.getElementById('botonIniciarWizardSoporte').innerHTML = `
                    <div class="alert alert-success">
                        ✅ Este correo ya fue convertido en la ficha de soporte #${correoActualSoporte.ficha_soporte_id}
                    </div>
                `;
            } else {
                document.getElementById('botonIniciarWizardSoporte').innerHTML = `
                    <button class="btn btn-success btn-lg" onclick="iniciarWizardSoporte()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:6px;">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                        Convertir en Ficha de Soporte (Wizard)
                    </button>
                `;
            }

            document.getElementById('wizardContainerSoporte').style.display = 'none';
            document.getElementById('infoCorreoSoporte').style.display = 'block';
            document.getElementById('botonIniciarWizardSoporte').style.display = 'block';

            new bootstrap.Modal(document.getElementById('modalCorreoSoporte')).show();
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error al cargar el correo: ' + error.message);
        }
    };

    // ============================================================
    // WIZARD DE CORREO A FICHA
    // ============================================================
    window.iniciarWizardSoporte = function() {
        if (!correoActualSoporte) return;

        document.getElementById('infoCorreoSoporte').style.display = 'none';
        document.getElementById('botonIniciarWizardSoporte').style.display = 'none';
        document.getElementById('wizardContainerSoporte').style.display = 'block';
        document.getElementById('wizardCorreoSoporteId').value = correoActualSoporte.id;

        const datos = correoActualSoporte.datos_extraidos || {};

        if (datos.problema) document.getElementById('wzDiagnostico').value = datos.problema;
        if (datos.fecha_requerida) document.getElementById('wzFechaRequeridaSoporte').value = datos.fecha_requerida;

        irPasoSoporte(1);
    };

    window.irPasoSoporte = function(paso) {
        document.querySelectorAll('.wizard-step-soporte').forEach(el => el.classList.remove('active'));
        const stepEl = document.getElementById('stepSop' + paso);
        if (stepEl) stepEl.classList.add('active');

        [1, 2, 3, 4].forEach(i => {
            const circle = document.getElementById('stepSop' + i + 'Circle');
            if (!circle) return;
            circle.classList.remove('active', 'completed');
            if (i < paso) { circle.classList.add('completed'); circle.textContent = '✓'; }
            else if (i === paso) { circle.classList.add('active'); circle.textContent = i; }
            else { circle.textContent = i; }
        });

        const progreso = ((paso - 1) / 3) * 100 + 25;
        const bar = document.getElementById('wizardProgressSoporte');
        if (bar) bar.style.width = Math.min(progreso, 100) + '%';
    };

    // ============================================================
    // INICIALIZACIÓN
    // ============================================================
    cargarFichas(1);
    actualizarBadgeCorreosSoporte();

    document.getElementById('buscarCorreo')?.addEventListener('input', debounce(cargarCorreosSoporte, 400));
    document.getElementById('filtroCorreo')?.addEventListener('change', cargarCorreosSoporte);

    document.getElementById('tab-correos')?.addEventListener('shown.bs.tab', function() {
        cargarCorreosSoporte();
        actualizarBadgeCorreosSoporte();
    });

    intervaloNotificaciones = setInterval(actualizarBadgeCorreosSoporte, 30000);

    console.log('✅ Módulo de soporte técnico inicializado correctamente');
});

// ============================================================
// EXPORTACIÓN CONTEXTUAL - SOPORTE
// ============================================================
window.exportarSoporte = function (formato) {
    if (formato !== 'pdf' && formato !== 'xlsx') {
        console.warn('Formato no soportado:', formato);
        return;
    }

    const params = new URLSearchParams();
    const buscar = document.getElementById('buscarFichas')?.value?.trim() || '';
    if (buscar) params.append('buscar', buscar);
    const estado = document.getElementById('filtroEstadoFichas')?.value || '';
    if (estado) params.append('estado', estado);

    const qs = params.toString();
    const url = `/admin/reportes/soporte-listado/export/${formato}` + (qs ? '?' + qs : '');
    const ventana = window.open(url, '_blank');
    if (!ventana) {
        alert('Por favor, permita ventanas emergentes para exportar el reporte.');
    }
};