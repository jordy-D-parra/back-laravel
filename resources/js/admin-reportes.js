/**
 * Módulo de Reportes - 3 secciones (Inventario, Solicitudes, Soporte)
 * Estilo unificado con el sistema
 */
const Reportes = (() => {
    'use strict';

    let chartInv = null;
    let chartSol = null;
    let chartSop = null;

    // ============ UTILIDADES ============
    const fmtFecha = (f) => {
        if (!f) return '---';
        const d = new Date(f);
        if (isNaN(d.getTime())) return f;
        return d.toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    const fmtFechaHora = (f) => {
        if (!f) return '---';
        const d = new Date(f);
        if (isNaN(d.getTime())) return f;
        return d.toLocaleDateString('es-VE', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    };

    const esc = (t) => {
        if (!t) return '';
        const d = document.createElement('div');
        d.textContent = t;
        return d.innerHTML;
    };

    const prefijo = (seccion) => {
        if (seccion === 'inventario') return 'inv';
        if (seccion === 'solicitudes') return 'sol';
        return 'sop';
    };

    const toggleFiltros = (seccion) => {
        const p = prefijo(seccion);
        const tipo = document.getElementById(`${p}_tipo_filtro`).value;

        ['dia', 'mes', 'rango'].forEach(t => {
            document.querySelectorAll(`.filtro-${p}-${t}`).forEach(el => {
                el.style.display = (tipo === t) ? '' : 'none';
            });
        });
    };

    const getParams = (seccion) => {
        const p = prefijo(seccion);
        const tipo = document.getElementById(`${p}_tipo_filtro`).value;
        const params = { tipo_filtro: tipo };

        if (tipo === 'dia') {
            params.fecha = document.getElementById(`${p}_fecha`).value;
        } else if (tipo === 'mes') {
            params.mes = document.getElementById(`${p}_mes`).value;
            params.anio = document.getElementById(`${p}_anio`).value;
        } else if (tipo === 'rango') {
            params.fecha_desde = document.getElementById(`${p}_fecha_desde`).value;
            params.fecha_hasta = document.getElementById(`${p}_fecha_hasta`).value;
        }

        return params;
    };

    const renderEmpty = (msg) => `
        <div class="empty-reporte">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p>${msg}</p>
        </div>`;

    const renderLoading = () => `
        <div class="loading-reporte">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando reporte...</p>
        </div>`;

    // ============ CARGA PRINCIPAL ============
    const cargar = async (seccion) => {
        const p = prefijo(seccion);
        const cont = document.getElementById(`${p}_contenido`);
        if (!cont) return;

        cont.innerHTML = renderLoading();

        const params = new URLSearchParams(getParams(seccion));

        try {
            const r = await fetch(`/admin/reportes/${seccion}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await r.json();

            if (!data.success) {
                cont.innerHTML = renderEmpty(data.message || 'Error al cargar');
                return;
            }

            if (seccion === 'inventario') renderInventario(data.data);
            else if (seccion === 'solicitudes') renderSolicitudes(data.data);
            else renderSoporte(data.data);

        } catch (e) {
            console.error(e);
            cont.innerHTML = renderEmpty('Error de conexión');
        }
    };

    // ============ EXPORTAR A PDF ============
    const exportarPdf = (seccion) => {
        const params = new URLSearchParams(getParams(seccion));
        params.set('seccion', seccion);

        const url = `/admin/reportes/exportar-pdf?${params.toString()}`;

        // Abrir en nueva pestaña (vista imprimible)
        const ventana = window.open(url, '_blank', 'width=1100,height=800,scrollbars=yes');

        if (!ventana) {
            alert('Por favor, permita ventanas emergentes para generar el PDF.');
        }
    };

    // ============ RENDER: INVENTARIO ============
    const renderInventario = (d) => {
        const cont = document.getElementById('inv_contenido');
        const s = d.stats;

        let html = `
        <div class="stats-mini">
            <div class="stat-mini-card"><div class="num">${s.total_activos}</div><div class="lbl">Activos</div></div>
            <div class="stat-mini-card green"><div class="num">${s.disponibles}</div><div class="lbl">Disponibles</div></div>
            <div class="stat-mini-card orange"><div class="num">${s.prestados}</div><div class="lbl">Prestados</div></div>
            <div class="stat-mini-card red"><div class="num">${s.reparacion}</div><div class="lbl">En Reparación</div></div>
            <div class="stat-mini-card blue"><div class="num">${s.total_componentes}</div><div class="lbl">Componentes</div></div>
            <div class="stat-mini-card purple"><div class="num">${s.bodega}</div><div class="lbl">En Bodega</div></div>
        </div>`;

        html += `<div class="chart-container"><canvas id="chartInv"></canvas></div>`;

        html += `<h6 class="fw-bold mb-2" style="color:#1e3c72;">Activos (${d.activos.length})</h6>`;
        if (d.activos.length === 0) {
            html += renderEmpty('No hay activos en este rango');
        } else {
            html += `
            <div class="table-responsive mb-3">
                <table class="table tabla-reporte">
                    <thead>
                        <tr>
                            <th>Serial</th><th>Modelo</th><th>Marca</th><th>Categoría</th>
                            <th>Estado</th><th>Institución</th><th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${d.activos.map(a => `
                            <tr>
                                <td><strong style="color:#1e3c72;">${esc(a.serial)}</strong></td>
                                <td>${esc(a.modelo?.nombre || 'N/A')}</td>
                                <td>${esc(a.modelo?.marca?.nombre || 'N/A')}</td>
                                <td>${esc(a.modelo?.categoria?.nombre || 'N/A')}</td>
                                <td><span class="badge bg-${a.estatus?.color_badge || 'secondary'} badge-estado-mini">${esc(a.estatus?.descripcion || 'N/A')}</span></td>
                                <td>${esc(a.institucion?.nombre || 'N/A')}</td>
                                <td>${fmtFecha(a.created_at)}</td>
                            </tr>`).join('')}
                    </tbody>
                </table>
            </div>`;
        }

        html += `<h6 class="fw-bold mb-2" style="color:#1e3c72;">Componentes (${d.componentes.length})</h6>`;
        if (d.componentes.length === 0) {
            html += renderEmpty('No hay componentes en este rango');
        } else {
            html += `
            <div class="table-responsive">
                <table class="table tabla-reporte">
                    <thead>
                        <tr>
                            <th>Tipo</th><th>Marca</th><th>Modelo</th><th>Serial</th>
                            <th>Estado</th><th>Activo</th><th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${d.componentes.map(c => `
                            <tr>
                                <td><strong style="color:#1e3c72;">${esc(c.tipo)}</strong></td>
                                <td>${esc(c.marca || 'N/A')}</td>
                                <td>${esc(c.modelo || 'N/A')}</td>
                                <td>${esc(c.serial || 'N/A')}</td>
                                <td><span class="badge bg-secondary badge-estado-mini">${esc(c.estado)}</span></td>
                                <td>${esc(c.activo?.serial || '---')}</td>
                                <td>${fmtFecha(c.created_at)}</td>
                            </tr>`).join('')}
                    </tbody>
                </table>
            </div>`;
        }

        cont.innerHTML = html;

        if (chartInv) chartInv.destroy();
        const labels = Object.keys(d.por_fecha);
        const values = Object.values(d.por_fecha);
        if (labels.length > 0) {
            chartInv = new Chart(document.getElementById('chartInv'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Activos registrados',
                        data: values,
                        borderColor: '#1e3c72',
                        backgroundColor: 'rgba(30,60,114,0.1)',
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: '#1e3c72'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }
    };

    // ============ RENDER: SOLICITUDES ============
    const renderSolicitudes = (d) => {
        const cont = document.getElementById('sol_contenido');
        const s = d.stats;

        let html = `
        <div class="stats-mini">
            <div class="stat-mini-card"><div class="num">${s.total}</div><div class="lbl">Total</div></div>
            <div class="stat-mini-card orange"><div class="num">${s.pendientes}</div><div class="lbl">Pendientes</div></div>
            <div class="stat-mini-card green"><div class="num">${s.aprobadas}</div><div class="lbl">Aprobadas</div></div>
            <div class="stat-mini-card red"><div class="num">${s.rechazadas}</div><div class="lbl">Rechazadas</div></div>
            <div class="stat-mini-card blue"><div class="num">${s.urgentes}</div><div class="lbl">Urgentes</div></div>
            <div class="stat-mini-card purple"><div class="num">${s.altas}</div><div class="lbl">Alta Prioridad</div></div>
        </div>`;

        html += `<div class="chart-container"><canvas id="chartSol"></canvas></div>`;

        html += `<h6 class="fw-bold mb-2" style="color:#1e3c72;">Solicitudes (${d.solicitudes.length})</h6>`;
        if (d.solicitudes.length === 0) {
            html += renderEmpty('No hay solicitudes en este rango');
        } else {
            html += `
            <div class="table-responsive">
                <table class="table tabla-reporte">
                    <thead>
                        <tr>
                            <th>#</th><th>Fecha</th><th>Entidad</th><th>Responsable</th>
                            <th>Prioridad</th><th>Estado</th><th>Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${d.solicitudes.map(sol => {
                            let entidad = 'No especificado';
                            if (sol.tipo_solicitante === 'interno' && sol.departamento) entidad = sol.departamento.nombre;
                            else if (sol.tipo_solicitante === 'externo' && sol.institucion) entidad = sol.institucion.nombre;
                            return `
                            <tr>
                                <td>${sol.id}</td>
                                <td>${fmtFecha(sol.fecha_solicitud)}</td>
                                <td>${esc(entidad)}</td>
                                <td>${esc(sol.responsable?.nombre || '---')}</td>
                                <td><span class="badge bg-info badge-estado-mini">${esc(sol.prioridad)}</span></td>
                                <td><span class="badge bg-primary badge-estado-mini">${esc(sol.estado_solicitud)}</span></td>
                                <td>${sol.detalles?.length || 0}</td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            </div>`;
        }

        cont.innerHTML = html;

        if (chartSol) chartSol.destroy();
        const labels = Object.keys(d.por_fecha);
        const values = Object.values(d.por_fecha);
        if (labels.length > 0) {
            chartSol = new Chart(document.getElementById('chartSol'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Solicitudes',
                        data: values,
                        backgroundColor: '#2a5298',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }
    };

    // ============ RENDER: SOPORTE ============
    const renderSoporte = (d) => {
        const cont = document.getElementById('sop_contenido');
        const s = d.stats;

        let html = `
        <div class="stats-mini">
            <div class="stat-mini-card"><div class="num">${s.total}</div><div class="lbl">Total</div></div>
            <div class="stat-mini-card orange"><div class="num">${s.en_proceso}</div><div class="lbl">En Proceso</div></div>
            <div class="stat-mini-card green"><div class="num">${s.finalizados}</div><div class="lbl">Finalizados</div></div>
            <div class="stat-mini-card blue"><div class="num">${s.con_tecnico}</div><div class="lbl">Con Técnico</div></div>
            <div class="stat-mini-card red"><div class="num">${s.vencidas}</div><div class="lbl">Vencidas</div></div>
            <div class="stat-mini-card purple"><div class="num">${s.sin_tecnico}</div><div class="lbl">Sin Técnico</div></div>
        </div>`;

        html += `<div class="chart-container"><canvas id="chartSop"></canvas></div>`;

        html += `<h6 class="fw-bold mb-2" style="color:#1e3c72;">Fichas de Soporte (${d.fichas.length})</h6>`;
        if (d.fichas.length === 0) {
            html += renderEmpty('No hay fichas en este rango');
        } else {
            html += `
            <div class="table-responsive">
                <table class="table tabla-reporte">
                    <thead>
                        <tr>
                            <th>#</th><th>Equipo</th><th>Técnico</th><th>Reporta</th>
                            <th>Ingreso</th><th>Salida</th><th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${d.fichas.map(f => `
                            <tr>
                                <td>${f.id}</td>
                                <td>${esc(f.activo?.serial || 'Externo')}<br><small class="text-muted">${esc(f.activo?.modelo?.nombre || '')}</small></td>
                                <td>${esc(f.tecnico_nombre || '---')}</td>
                                <td>${esc(f.usuario_reporta_nombre || '---')}</td>
                                <td>${fmtFecha(f.fecha_ingreso)}</td>
                                <td>${fmtFecha(f.fecha_salida)}</td>
                                <td><span class="badge bg-${f.estado === 'en_proceso' ? 'warning text-dark' : 'success'} badge-estado-mini">${f.estado === 'en_proceso' ? 'En Proceso' : 'Finalizado'}</span></td>
                            </tr>`).join('')}
                    </tbody>
                </table>
            </div>`;
        }

        cont.innerHTML = html;

        if (chartSop) chartSop.destroy();
        const labels = Object.keys(d.por_fecha);
        const values = Object.values(d.por_fecha);
        if (labels.length > 0) {
            chartSop = new Chart(document.getElementById('chartSop'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Fichas de soporte',
                        data: values,
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23,162,184,0.1)',
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: '#17a2b8'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }
    };

    // ============ INICIALIZACIÓN ============
    document.addEventListener('DOMContentLoaded', () => {
        ['inventario', 'solicitudes', 'soporte'].forEach(seccion => {
            const p = prefijo(seccion);
            const sel = document.getElementById(`${p}_tipo_filtro`);
            if (sel) {
                sel.addEventListener('change', () => toggleFiltros(seccion));
            }
        });

        // Carga inicial del primer tab
        cargar('inventario');

        // Lazy load al cambiar de tab
        document.getElementById('tab-solicitudes')?.addEventListener('shown.bs.tab', () => {
            const cont = document.getElementById('sol_contenido');
            if (cont && cont.querySelector('.loading-reporte')) {
                cargar('solicitudes');
            }
        });

        document.getElementById('tab-soporte')?.addEventListener('shown.bs.tab', () => {
            const cont = document.getElementById('sop_contenido');
            if (cont && cont.querySelector('.loading-reporte')) {
                cargar('soporte');
            }
        });
    });

    // ============================================================
    // ✅ EXPONER GLOBALMENTE (necesario para los onclick inline)
    // ============================================================
    window.Reportes = { cargar, exportarPdf };

    return { cargar, exportarPdf };
})();