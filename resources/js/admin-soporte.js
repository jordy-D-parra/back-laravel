// resources/js/admin-soporte.js
// ✅ VERSIÓN LIMPIA: Solo Fichas + Correos + Wizard de conversión
// ✅ Se eliminó todo el código de "crear ficha manual" y "equipo externo"
// ✅ El flujo de creación de fichas es ÚNICAMENTE por correo (wizard)

// ============================================================
// VARIABLES GLOBALES
// ============================================================
let fichasData = [];
let currentPage = 1;
let lastPage = 1;
let perPage = 10;
let totalRegistros = 0;
let timeoutBusqueda = null;
let fichaAEliminar = null;
let activosEnProceso = [];
let todosActivos = [];

// Correos de soporte
let correoSoporteActual = null;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

let filtros = {
    search: '',
    estado: ''
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

function formatearFechaHora(fecha) {
    if (!fecha) return '-';
    const d = new Date(fecha);
    if (isNaN(d.getTime())) return fecha;
    return d.toLocaleDateString('es-ES', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

// ✅ Normaliza cualquier fecha al formato Y-m-d que acepta input[type=date]
function normalizarFechaParaInput(fechaStr) {
    if (!fechaStr) return '';

    if (/^\d{4}-\d{2}-\d{2}$/.test(fechaStr)) return fechaStr;

    const match = fechaStr.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
    if (match) {
        const dia = match[1].padStart(2, '0');
        const mes = match[2].padStart(2, '0');
        const anio = match[3];
        return `${anio}-${mes}-${dia}`;
    }

    const d = new Date(fechaStr);
    if (!isNaN(d.getTime())) {
        return d.toISOString().split('T')[0];
    }

    return '';
}

function mostrarNotificacion(tipo, mensaje) {
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; width: 320px;';
        document.body.appendChild(container);
    }

    const colores = { success: '#28a745', error: '#dc3545', warning: '#ffc107', info: '#17a2b8' };
    const iconos = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

    const toast = document.createElement('div');
    toast.style.cssText = `
        background: ${colores[tipo] || colores.info}; color: white;
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

// Estilos de animación
(function inyectarEstilos() {
    if (document.getElementById('soporte-animaciones')) return;
    const style = document.createElement('style');
    style.id = 'soporte-animaciones';
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
})();

// ============================================================
// FICHAS: ESTADÍSTICAS
// ============================================================
function actualizarEstadisticas() {
    const total = fichasData.length;
    const enProceso = fichasData.filter(f => f.estado === 'en_proceso').length;
    const finalizados = fichasData.filter(f => f.estado === 'finalizado').length;

    const statsTotal = document.getElementById('statsTotal');
    if (statsTotal) statsTotal.textContent = total;

    const statsEnProceso = document.getElementById('statsEnProceso');
    if (statsEnProceso) statsEnProceso.textContent = enProceso;

    const statsFinalizados = document.getElementById('statsFinalizados');
    if (statsFinalizados) statsFinalizados.textContent = finalizados;

    const statsEquiposReparacion = document.getElementById('statsEquiposReparacion');
    if (statsEquiposReparacion) statsEquiposReparacion.textContent = enProceso;

    activosEnProceso = fichasData.filter(f => f.estado === 'en_proceso').map(f => f.activo_id);
}

// ============================================================
// FICHAS: RENDERIZADO
// ============================================================
function renderizarTabla() {
    const tbody = document.getElementById('tablaFichas');
    if (!tbody) return;

    if (fichasData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">No hay fichas de soporte registradas</td></tr>`;
        return;
    }

    let html = '';
    for (const f of fichasData) {
        const fechaIngreso = f.fecha_ingreso ? new Date(f.fecha_ingreso).toLocaleDateString() : 'N/A';
        const fechaSalida = f.fecha_salida ? new Date(f.fecha_salida).toLocaleDateString() : '---';
        const activoInfo = f.activo ? `${f.activo.serial} - ${f.activo.modelo?.nombre || 'N/A'}` : 'N/A';
        const estadoClass = f.estado === 'en_proceso' ? 'badge-estado-en-proceso' : 'badge-estado-finalizado';
        const estadoText = f.estado === 'en_proceso' ? 'En Proceso' : 'Finalizado';

        html += `<tr>
            <td class="px-3 py-2">${escapeHtml(activoInfo)}</td>
            <td class="px-3 py-2">${escapeHtml(f.tecnico_nombre || '---')}</td>
            <td class="px-3 py-2">${escapeHtml(f.usuario_reporta_nombre || '---')}</td>
            <td class="px-3 py-2">${fechaIngreso}</td>
            <td class="px-3 py-2">${fechaSalida}</td>
            <td class="px-3 py-2"><span class="${estadoClass}">${estadoText}</span></td>
            <td class="px-3 py-2 text-end">
                <button type="button" class="btn-action btn-outline-primary-dark" onclick="verDetalle(${f.id})" title="Ver detalle">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                </button>`;

        if (f.estado === 'en_proceso') {
            html += `
                <button type="button" class="btn-cerrar-ficha ms-1" onclick="abrirModalCerrarFicha(${f.id})" title="Cerrar ficha">
                    ✓ Cerrar
                </button>`;
        }

        html += `
                <button type="button" class="btn-action text-danger ms-1" onclick="confirmarEliminar(${f.id})" title="Eliminar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                </button>
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

    const infoDiv = document.getElementById('paginationInfo');
    if (infoDiv) {
        infoDiv.innerHTML = `Mostrando ${fichasData.length} de ${totalRegistros} registros`;
    }
}

window.cambiarPagina = function (page) {
    if (page < 1 || page > lastPage) return;
    currentPage = page;
    cargarPagina(currentPage);
};

// ============================================================
// FICHAS: CARGA DE DATOS
// ============================================================
async function cargarPagina(page) {
    try {
        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            buscar: filtros.search,
            estado: filtros.estado
        });

        const response = await fetch(`/admin/soporte?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) throw new Error('Error al cargar datos');
        const data = await response.json();

        fichasData = data.data || [];
        currentPage = data.current_page || 1;
        lastPage = data.last_page || 1;
        perPage = data.per_page || 10;
        totalRegistros = data.total || 0;

        renderizarTabla();
        actualizarEstadisticas();
        renderizarPaginacion();

    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'No se pudieron cargar las fichas');
        const tabla = document.getElementById('tablaFichas');
        if (tabla) {
            tabla.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Error al cargar los datos</td></tr>';
        }
    }
}

function aplicarFiltros() {
    const buscarInput = document.getElementById('buscarFichas');
    const estadoSelect = document.getElementById('filtroEstadoFichas');
    filtros = {
        search: buscarInput?.value || '',
        estado: estadoSelect?.value || ''
    };
    currentPage = 1;
    cargarPagina(1);
}

function aplicarFiltrosConDebounce() {
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(() => aplicarFiltros(), 300);
}

// ============================================================
// ⭐ CORREOS DE SOPORTE TÉCNICO ⭐
// ============================================================
window.cargarCorreosSoporte = async function () {
    const container = document.getElementById('listaCorreos');
    if (!container) {
        console.warn('No se encontró #listaCorreos');
        return;
    }

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
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        console.log('Correos recibidos:', data);
        renderizarCorreosSoporte(data.data || []);

    } catch (error) {
        console.error('Error al cargar correos:', error);
        container.innerHTML = `
            <div class="text-center py-5 text-danger">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <p class="mt-2">Error al cargar correos: ${escapeHtml(error.message)}</p>
                <button class="btn btn-sm btn-primary-dark mt-2" onclick="cargarCorreosSoporte()">Reintentar</button>
            </div>
        `;
    }
};

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

window.actualizarBadgeCorreosSoporte = async function () {
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
};

window.revisarCorreos = async function () {
    const btn = document.getElementById('btnRevisarCorreos');
    if (!btn) return;
    const original = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Revisando...';
    btn.disabled = true;

    try {
        const response = await fetch('/admin/soporte/correos/revisar', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
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

window.abrirCorreoSoporte = async function (id) {
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

        correoSoporteActual = data.data;

        document.getElementById('correoFromSoporte').textContent =
            `${correoSoporteActual.from_name || ''} <${correoSoporteActual.from_email}>`;
        document.getElementById('correoFechaSoporte').textContent =
            formatearFechaHora(correoSoporteActual.received_at);
        document.getElementById('correoAsuntoSoporte').textContent =
            correoSoporteActual.subject || '(Sin asunto)';
        document.getElementById('correoCuerpoSoporte').textContent =
            correoSoporteActual.body_text || '(Sin contenido)';

        // Prellenar campos con datos extraídos
        const datos = correoSoporteActual.datos_extraidos || {};
        if (datos.problema) {
            const el = document.getElementById('wzDiagnostico');
            if (el) el.value = datos.problema;
        }

        // ✅ Normalizar la fecha al formato Y-m-d
        const fechaRaw = datos.fecha_requerida || correoSoporteActual.fecha_requerida_entrega;
        if (fechaRaw) {
            const el = document.getElementById('wzFechaRequeridaSoporte');
            if (el) {
                const fechaNormalizada = normalizarFechaParaInput(fechaRaw);
                if (fechaNormalizada) {
                    el.value = fechaNormalizada;
                }
            }
        }

        if (correoSoporteActual.from_name) {
            const el = document.getElementById('wzUsuarioReporta');
            if (el) el.value = correoSoporteActual.from_name;
        }

        if (correoSoporteActual.procesado) {
            document.getElementById('botonIniciarWizardSoporte').innerHTML = `
                <div class="alert alert-success">
                    ✅ Este correo ya fue convertido en la ficha de soporte #${correoSoporteActual.ficha_soporte_id}
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
// WIZARD DE CONVERSIÓN DE CORREO → FICHA
// ============================================================
window.iniciarWizardSoporte = function () {
    if (!correoSoporteActual) return;

    document.getElementById('infoCorreoSoporte').style.display = 'none';
    document.getElementById('botonIniciarWizardSoporte').style.display = 'none';
    document.getElementById('wizardContainerSoporte').style.display = 'block';
    document.getElementById('wizardCorreoSoporteId').value = correoSoporteActual.id;

    // Pre-llenar campos desde datos_extraidos
    const datos = correoSoporteActual.datos_extraidos || {};
    if (datos.problema) {
        const el = document.getElementById('wzDiagnostico');
        if (el) el.value = datos.problema;
    }

    // ✅ Normalizar la fecha al formato Y-m-d
    const fechaRaw = datos.fecha_requerida || correoSoporteActual.fecha_requerida_entrega;
    if (fechaRaw) {
        const el = document.getElementById('wzFechaRequeridaSoporte');
        if (el) {
            const fechaNormalizada = normalizarFechaParaInput(fechaRaw);
            if (fechaNormalizada) {
                el.value = fechaNormalizada;
            }
        }
    }

    if (correoSoporteActual.from_name) {
        const el = document.getElementById('wzUsuarioReporta');
        if (el) el.value = correoSoporteActual.from_name;
    }

    // Pre-llenar institución y responsable si están disponibles
    if (datos.serial) {
        fetch(`/admin/activos?buscar=${encodeURIComponent(datos.serial)}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(response => {
            if (response.success && response.data && response.data.length > 0) {
                const activo = response.data[0];
                const selectActivo = document.getElementById('wzActivoId');
                if (selectActivo) {
                    if (selectActivo.options.length <= 1) {
                        cargarActivosEnWizard().then(() => {
                            selectActivo.value = activo.id;
                        });
                    } else {
                        selectActivo.value = activo.id;
                    }
                }
                const radioExistente = document.getElementById('equipoExistente');
                if (radioExistente) {
                    radioExistente.checked = true;
                    document.getElementById('equipoExistenteFields').style.display = 'block';
                    document.getElementById('equipoNuevoFields').style.display = 'none';
                }
            } else {
                const radioNuevo = document.getElementById('equipoNuevo');
                if (radioNuevo) {
                    radioNuevo.checked = true;
                    document.getElementById('equipoExistenteFields').style.display = 'none';
                    document.getElementById('equipoNuevoFields').style.display = 'block';
                }
                if (datos.marca) {
                    const el = document.querySelector('input[name="nuevo_equipo[marca]"]');
                    if (el) el.value = datos.marca;
                }
                if (datos.modelo) {
                    const el = document.querySelector('input[name="nuevo_equipo[modelo_nombre]"]');
                    if (el) el.value = datos.modelo;
                }
                if (datos.serial) {
                    const el = document.querySelector('input[name="nuevo_equipo[serial]"]');
                    if (el) el.value = datos.serial;
                }
            }
        })
        .catch(error => console.error('Error al buscar activo:', error));
    }

    irPasoSoporte(1);
};

function cargarActivosEnWizard() {
    return new Promise((resolve) => {
        const select = document.getElementById('wzActivoId');
        if (!select || select.options.length > 1) {
            resolve();
            return;
        }

        fetch('/admin/activos', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                select.innerHTML = '<option value="">Seleccionar activo...</option>';
                response.data.forEach(act => {
                    select.innerHTML += `<option value="${act.id}">${escapeHtml(act.serial)} - ${escapeHtml(act.modelo?.marca?.nombre || '')} ${escapeHtml(act.modelo?.nombre || '')}</option>`;
                });
            }
            resolve();
        })
        .catch(() => resolve());
    });
}

window.irPasoSoporte = function (paso) {
    if (paso < 1 || paso > 3) return;
    if (paso === 2 && !validarPasoSoporte1()) return;
    if (paso === 3 && !validarPasoSoporte2()) return;

    document.querySelectorAll('.wizard-step-soporte').forEach(el => el.style.display = 'none');
    document.getElementById('stepSop' + paso).style.display = 'block';

    [1, 2, 3].forEach(i => {
        const circle = document.getElementById('stepSop' + i + 'Circle');
        if (!circle) return;
        circle.classList.remove('active', 'completed');
        if (i < paso) { circle.classList.add('completed'); circle.textContent = '✓'; }
        else if (i === paso) { circle.classList.add('active'); circle.textContent = i; }
        else { circle.textContent = i; }
    });

    const progress = document.getElementById('wizardProgressSoporte');
    if (progress) progress.style.width = (((paso - 1) / 2) * 100 + 33) + '%';

    if (paso === 3) generarResumenSoporte();
};

function validarPasoSoporte1() {
    const tipo = document.querySelector('input[name="tipo_equipo"]:checked')?.value;
    if (tipo === 'existente') {
        const activo = document.getElementById('wzActivoId').value;
        if (!activo) {
            mostrarNotificacion('warning', 'Seleccione un activo del inventario');
            return false;
        }
    } else {
        const serial = document.querySelector('input[name="nuevo_equipo[serial]"]').value;
        const marca = document.querySelector('input[name="nuevo_equipo[marca]"]').value;
        const modelo = document.querySelector('input[name="nuevo_equipo[modelo_nombre]"]').value;
        const categoria = document.querySelector('select[name="nuevo_equipo[categoria_id]"]').value;
        const institucion = document.querySelector('select[name="nuevo_equipo[institucion_id]"]').value;
        const responsable = document.querySelector('select[name="nuevo_equipo[responsable_id]"]').value;

        if (!serial || !marca || !modelo || !categoria || !institucion || !responsable) {
            mostrarNotificacion('warning', 'Complete todos los campos del equipo nuevo');
            return false;
        }
    }
    return true;
}

function validarPasoSoporte2() {
    const usuario = document.getElementById('wzUsuarioReporta').value.trim();
    const diagnostico = document.getElementById('wzDiagnostico').value.trim();
    const fecha = document.getElementById('wzFechaRequeridaSoporte').value;

    if (!usuario) {
        mostrarNotificacion('warning', 'Ingrese el nombre de quien reporta');
        return false;
    }
    if (!diagnostico || diagnostico.length < 10) {
        mostrarNotificacion('warning', 'El diagnóstico debe tener al menos 10 caracteres');
        return false;
    }
    if (!fecha) {
        mostrarNotificacion('warning', 'Seleccione la fecha requerida de entrega');
        return false;
    }
    return true;
}

function generarResumenSoporte() {
    const tipo = document.querySelector('input[name="tipo_equipo"]:checked')?.value;
    const lista = document.getElementById('resumenFichaSoporte');
    if (!lista) return;

    let items = [];
    if (tipo === 'existente') {
        const sel = document.getElementById('wzActivoId');
        if (sel && sel.selectedIndex >= 0) {
            items.push(`<li><strong>Equipo:</strong> ${sel.options[sel.selectedIndex].text}</li>`);
        }
    } else {
        const serial = document.querySelector('input[name="nuevo_equipo[serial]"]').value;
        const marca = document.querySelector('input[name="nuevo_equipo[marca]"]').value;
        items.push(`<li><strong>Equipo nuevo:</strong> ${marca} (${serial})</li>`);
    }

    const tecnico = document.getElementById('wzTecnicoSoporte');
    if (tecnico && tecnico.selectedIndex >= 0) {
        items.push(`<li><strong>Técnico:</strong> ${tecnico.options[tecnico.selectedIndex].text}</li>`);
    }

    items.push(`<li><strong>Reporta:</strong> ${document.getElementById('wzUsuarioReporta').value}</li>`);
    items.push(`<li><strong>Diagnóstico:</strong> ${document.getElementById('wzDiagnostico').value}</li>`);
    items.push(`<li><strong>Fecha requerida:</strong> ${document.getElementById('wzFechaRequeridaSoporte').value}</li>`);

    lista.innerHTML = items.join('');
}

// ============================================================
// ⭐ INICIALIZACIÓN (DOMContentLoaded) ⭐
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    console.log('✅ Módulo de fichas de soporte inicializado');

    // ========== FILTROS DE FICHAS ==========
    const buscarInput = document.getElementById('buscarFichas');
    const estadoSelect = document.getElementById('filtroEstadoFichas');
    const limpiarBtn = document.getElementById('limpiarFiltros');

    if (buscarInput) buscarInput.addEventListener('input', aplicarFiltrosConDebounce);
    if (estadoSelect) estadoSelect.addEventListener('change', aplicarFiltros);
    if (limpiarBtn) {
        limpiarBtn.addEventListener('click', function () {
            if (buscarInput) buscarInput.value = '';
            if (estadoSelect) estadoSelect.value = '';
            aplicarFiltros();
        });
    }

    // ========== CARGA INICIAL ==========
    cargarPagina(1);
    actualizarBadgeCorreosSoporte();

    // ========== SUBMIT FORM CERRAR FICHA ==========
    const formCerrarFicha = document.getElementById('formCerrarFicha');
    if (formCerrarFicha) {
        formCerrarFicha.addEventListener('submit', async function (e) {
            e.preventDefault();
            const id = document.getElementById('cerrarFichaId').value;
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Finalizando...';
            submitBtn.disabled = true;

            try {
                const response = await fetch(`/admin/soporte/${id}/close`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: new FormData(this)
                });
                const result = await response.json();

                if (response.ok && result.success) {
                    mostrarNotificacion('success', result.message || 'Ficha finalizada');
                    bootstrap.Modal.getInstance(document.getElementById('modalCerrarFicha')).hide();
                    cargarPagina(currentPage);
                    actualizarEstadisticas();
                } else {
                    mostrarNotificacion('error', result.message || 'Error al finalizar la ficha');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarNotificacion('error', 'Error de conexión');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    // ========== SUBMIT FORM WIZARD SOPORTE ==========
    const formWizardSoporte = document.getElementById('formWizardSoporte');
    if (formWizardSoporte) {
        formWizardSoporte.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!validarPasoSoporte1()) {
                irPasoSoporte(1);
                return;
            }
            if (!validarPasoSoporte2()) {
                irPasoSoporte(2);
                return;
            }

            const btn = document.getElementById('btnGuardarWizardSoporte');
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creando ficha...';

            const correoId = document.getElementById('wizardCorreoSoporteId').value;

            try {
                const response = await fetch(`/admin/soporte/correos/${correoId}/convertir`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: new FormData(this)
                });
                const data = await response.json();

                if (data.success) {
                    mostrarNotificacion('success', data.message || 'Ficha de soporte creada');
                    bootstrap.Modal.getInstance(document.getElementById('modalCorreoSoporte'))?.hide();
                    cargarCorreosSoporte();
                    actualizarBadgeCorreosSoporte();
                    cargarPagina(1);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    let msg = data.message || 'Error al crear la ficha';
                    if (data.errors) msg += '\n\n' + Object.values(data.errors).flat().join('\n');
                    mostrarNotificacion('error', msg);
                }
            } catch (error) {
                mostrarNotificacion('error', 'Error de conexión: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = original;
            }
        });
    }

    // ========== CONFIRMAR ELIMINAR ==========
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmarEliminar) {
        btnConfirmarEliminar.addEventListener('click', async function () {
            if (!fichaAEliminar) return;
            try {
                const response = await fetch(`/admin/soporte/${fichaAEliminar}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const result = await response.json();
                bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
                if (response.ok && result.success) {
                    mostrarNotificacion('success', result.message);
                    cargarPagina(currentPage);
                } else {
                    mostrarNotificacion('error', result.message || 'Error al eliminar');
                }
                fichaAEliminar = null;
            } catch (error) {
                console.error('Error:', error);
                mostrarNotificacion('error', 'Error de conexión');
                fichaAEliminar = null;
            }
        });
    }

    // ========== TAB DE CORREOS (Lazy Loading) ==========
    const tabCorreos = document.getElementById('tab-correos');
    if (tabCorreos) {
        tabCorreos.addEventListener('shown.bs.tab', function () {
            console.log('Tab de correos activado, cargando correos...');
            cargarCorreosSoporte();
            actualizarBadgeCorreosSoporte();
        });
    }

    // ========== BUSCADOR DE CORREOS ==========
    const buscarCorreo = document.getElementById('buscarCorreo');
    if (buscarCorreo) {
        let t;
        buscarCorreo.addEventListener('input', function () {
            clearTimeout(t);
            t = setTimeout(cargarCorreosSoporte, 400);
        });
    }

    const filtroCorreo = document.getElementById('filtroCorreo');
    if (filtroCorreo) {
        filtroCorreo.addEventListener('change', cargarCorreosSoporte);
    }

    // ========== TOGGLE EQUIPO EXISTENTE/NUEVO (Wizard) ==========
    document.querySelectorAll('input[name="tipo_equipo"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const valor = this.value;
            const elExist = document.getElementById('equipoExistenteFields');
            const elNuevo = document.getElementById('equipoNuevoFields');
            if (elExist) elExist.style.display = valor === 'existente' ? 'block' : 'none';
            if (elNuevo) elNuevo.style.display = valor === 'nuevo' ? 'block' : 'none';
        });
    });

    // ========== REFRESCO AUTOMÁTICO DEL BADGE ==========
    setInterval(actualizarBadgeCorreosSoporte, 30000);
});

// ============================================================
// CERRAR FICHA (abrir modal)
// ============================================================
window.abrirModalCerrarFicha = async function (id) {
    try {
        const response = await fetch(`/admin/soporte/${id}/componentes`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        const result = await response.json();

        if (result.success && result.data) {
            document.getElementById('cerrarFichaId').value = id;
            let componentesHtml = '';
            for (const det of result.data) {
                componentesHtml += `
                    <div class="componente-row border rounded p-3 mb-3" style="background: #f8f9fc;">
                        <input type="hidden" name="detalles[${det.id}][id]" value="${det.id}">
                        <div class="fw-bold mb-2" style="color: #1e3c72;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:5px;">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            ${escapeHtml(det.componente_nombre)}
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Estado Salida</label>
                                <select name="detalles[${det.id}][estado_salida]" class="form-select form-select-sm">
                                    <option value="funcionando">✅ Funcionando</option>
                                    <option value="dañado">⚠️ Dañado</option>
                                    <option value="reemplazado">🔄 Reemplazado</option>
                                    <option value="reparado">🔧 Reparado</option>
                                    <option value="no_aplica">❌ No Aplica</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Observaciones</label>
                                <input type="text" name="detalles[${det.id}][observaciones]" class="form-control form-control-sm" placeholder="Observaciones del componente...">
                            </div>
                        </div>
                    </div>
                `;
            }
            document.getElementById('componentesContainer').innerHTML = componentesHtml;
            new bootstrap.Modal(document.getElementById('modalCerrarFicha')).show();
        } else {
            mostrarNotificacion('error', 'No se pudieron cargar los componentes');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión al cargar componentes');
    }
};

// ============================================================
// VER DETALLE DE FICHA (MEJORADO)
// ============================================================
window.verDetalle = async function (id) {
    const modalBody = document.getElementById('detalleContenido');
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Cargando detalles de la ficha...</p>
        </div>
    `;
    new bootstrap.Modal(document.getElementById('modalDetalle')).show();

    try {
        const response = await fetch(`/admin/soporte/${id}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        const result = await response.json();

        if (!result.success || !result.data) {
            modalBody.innerHTML = `
                <div class="text-center text-danger py-5">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <p class="mt-2">Error al cargar el detalle de la ficha</p>
                </div>
            `;
            return;
        }

        const f = result.data;

        const fmtFecha = (fecha) => {
            if (!fecha) return null;
            const d = new Date(fecha);
            if (isNaN(d.getTime())) return fecha;
            return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
        };
        const fmtFechaHora = (fecha) => {
            if (!fecha) return null;
            const d = new Date(fecha);
            if (isNaN(d.getTime())) return fecha;
            return d.toLocaleDateString('es-ES', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        };

        const fechaIngreso = fmtFechaHora(f.fecha_ingreso) || 'No registrada';
        const fechaSalida = fmtFechaHora(f.fecha_salida) || 'En proceso';
        const fechaRequerida = fmtFecha(f.fecha_requerida_entrega);

        const esEnProceso = f.estado === 'en_proceso';
        const estadoBg = esEnProceso ? 'linear-gradient(135deg, #f6c23e, #f4b619)' : 'linear-gradient(135deg, #1e7e34, #28a745)';
        const estadoIcono = esEnProceso ? '🔧' : '✅';
        const estadoTexto = esEnProceso ? 'En Proceso' : 'Finalizado';

        const serial = f.activo?.serial || 'N/A';
        const modelo = f.activo?.modelo?.nombre || 'N/A';
        const marca = f.activo?.modelo?.marca?.nombre || 'N/A';
        const institucion = f.activo?.institucion?.nombre || 'No especificada';
        const responsable = f.activo?.responsable?.nombre || 'No especificado';

        let componentesHtml = '';
        if (f.detalles && f.detalles.length > 0) {
            componentesHtml = f.detalles.map(det => {
                const estadoSalida = det.estado_salida;
                let badgeClass = 'bg-secondary';
                let badgeIcon = '❓';
                if (estadoSalida === 'funcionando') { badgeClass = 'bg-success'; badgeIcon = '✅'; }
                else if (estadoSalida === 'reemplazado') { badgeClass = 'bg-warning text-dark'; badgeIcon = '🔄'; }
                else if (estadoSalida === 'reparado') { badgeClass = 'bg-info text-dark'; badgeIcon = '🔧'; }
                else if (estadoSalida === 'dañado') { badgeClass = 'bg-danger'; badgeIcon = '⚠️'; }
                else if (estadoSalida === 'no_aplica') { badgeClass = 'bg-secondary'; badgeIcon = '❌'; }

                return `
                    <div class="componente-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="componente-nombre">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" style="display:inline; margin-right:6px;">
                                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                                </svg>
                                ${escapeHtml(det.componente_nombre || 'Componente')}
                            </div>
                            ${estadoSalida
                                ? `<span class="badge ${badgeClass}" style="font-size:0.72rem;">${badgeIcon} ${escapeHtml(estadoSalida)}</span>`
                                : `<span class="badge bg-info text-dark" style="font-size:0.72rem;">📥 Ingreso: ${escapeHtml(det.estado_ingreso || 'N/A')}</span>`}
                        </div>
                        ${det.observaciones ? `
                            <div class="componente-obs">
                                <strong>Observaciones:</strong> ${escapeHtml(det.observaciones)}
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        } else {
            componentesHtml = `
                <div class="text-center text-muted py-3" style="font-size:0.85rem;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5">
                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                    </svg>
                    <p class="mb-0 mt-2">No hay componentes registrados para esta ficha</p>
                </div>
            `;
        }

        const html = `
            <style>
                .ficha-detalle-wrap { font-family: inherit; }
                .ficha-header {
                    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
                    margin: -1rem -1rem 1.5rem -1rem;
                    padding: 1.5rem;
                    border-radius: 12px 12px 0 0;
                    color: white;
                    position: relative;
                    overflow: hidden;
                }
                .ficha-header::after {
                    content: '';
                    position: absolute;
                    top: -50%;
                    right: -10%;
                    width: 200px;
                    height: 200px;
                    background: rgba(255,255,255,0.05);
                    border-radius: 50%;
                }
                .ficha-header h4 {
                    font-size: 1.35rem;
                    font-weight: 700;
                    margin: 0;
                }
                .ficha-header .subtitulo {
                    font-size: 0.85rem;
                    opacity: 0.85;
                    margin-top: 4px;
                }
                .estado-badge {
                    background: ${estadoBg};
                    color: white;
                    padding: 0.45rem 1rem;
                    border-radius: 30px;
                    font-size: 0.8rem;
                    font-weight: 600;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                    white-space: nowrap;
                }
                .info-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                    gap: 1rem;
                    margin-bottom: 1.5rem;
                }
                .info-card {
                    background: #f8f9fc;
                    border-radius: 10px;
                    padding: 0.85rem 1rem;
                    border-left: 4px solid #1e3c72;
                    transition: all 0.2s ease;
                }
                .info-card:hover {
                    background: #eef2ff;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 10px rgba(30,60,114,0.08);
                }
                .info-card .info-label {
                    font-size: 0.7rem;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    color: #6c757d;
                    font-weight: 700;
                    margin-bottom: 4px;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                .info-card .info-value {
                    font-size: 0.92rem;
                    font-weight: 600;
                    color: #2c3e50;
                    word-break: break-word;
                }
                .seccion-titulo {
                    font-size: 0.95rem;
                    font-weight: 700;
                    color: #1e3c72;
                    margin-bottom: 0.75rem;
                    padding-bottom: 0.5rem;
                    border-bottom: 2px solid #e9ecef;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .seccion-titulo svg { flex-shrink: 0; }
                .texto-bloque {
                    background: #f8f9fc;
                    border-radius: 8px;
                    padding: 0.85rem 1rem;
                    font-size: 0.9rem;
                    color: #2c3e50;
                    line-height: 1.55;
                    white-space: pre-wrap;
                    word-break: break-word;
                    min-height: 44px;
                    border-left: 3px solid #dee2e6;
                }
                .texto-bloque.vacio {
                    color: #adb5bd;
                    font-style: italic;
                }
                .texto-bloque.diagnostico { border-left-color: #f6c23e; }
                .texto-bloque.trabajo { border-left-color: #1e7e34; }
                .texto-bloque.observaciones { border-left-color: #17a2b8; }
                .componentes-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                    gap: 0.75rem;
                }
                .componente-card {
                    background: #f8f9fc;
                    border: 1px solid #e9ecef;
                    border-radius: 10px;
                    padding: 0.85rem 1rem;
                    transition: all 0.2s ease;
                }
                .componente-card:hover {
                    border-color: #1e3c72;
                    box-shadow: 0 4px 10px rgba(30,60,114,0.08);
                }
                .componente-nombre {
                    font-weight: 600;
                    color: #1e3c72;
                    font-size: 0.88rem;
                }
                .componente-obs {
                    font-size: 0.8rem;
                    color: #6c757d;
                    margin-top: 6px;
                    padding-top: 6px;
                    border-top: 1px dashed #dee2e6;
                }
                .fecha-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 0.3rem 0.75rem;
                    border-radius: 20px;
                    font-size: 0.78rem;
                    font-weight: 600;
                }
                .fecha-badge.ingreso { background: #e7f1ff; color: #1e3c72; }
                .fecha-badge.salida { background: #e8f5e9; color: #1e7e34; }
                .fecha-badge.requerida { background: #fff8e1; color: #a67c00; }
                .fecha-badge.pendiente { background: #f0f0f0; color: #6c757d; }
            </style>

            <div class="ficha-detalle-wrap">
                <div class="ficha-header">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h4>
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                                Ficha de Soporte #${f.id}
                            </h4>
                            <div class="subtitulo">
                                <strong>${escapeHtml(serial)}</strong> · ${escapeHtml(marca)} ${escapeHtml(modelo)}
                            </div>
                        </div>
                        <span class="estado-badge">${estadoIcono} ${estadoTexto}</span>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Técnico Asignado
                        </div>
                        <div class="info-value">${escapeHtml(f.tecnico_nombre || 'No asignado')}</div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Usuario Reporta
                        </div>
                        <div class="info-value">${escapeHtml(f.usuario_reporta_nombre || 'No especificado')}</div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M3 21h18"/>
                                <path d="M5 21V7l8-4v18"/>
                                <path d="M19 21V11l-6-4"/>
                            </svg>
                            Institución
                        </div>
                        <div class="info-value">${escapeHtml(institucion)}</div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Responsable
                        </div>
                        <div class="info-value">${escapeHtml(responsable)}</div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="seccion-titulo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Fechas del Proceso
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="fecha-badge ingreso">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="23 4 23 10 17 10"/>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                            </svg>
                            Ingreso: ${fechaIngreso}
                        </span>
                        <span class="fecha-badge ${f.fecha_salida ? 'salida' : 'pendiente'}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            Salida: ${fechaSalida}
                        </span>
                        ${fechaRequerida ? `
                            <span class="fecha-badge requerida">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                Requerida: ${fechaRequerida}
                            </span>
                        ` : ''}
                    </div>
                </div>

                <div class="mb-4">
                    <div class="seccion-titulo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                        Diagnóstico Inicial
                    </div>
                    <div class="texto-bloque diagnostico ${!f.diagnostico ? 'vacio' : ''}">
                        ${escapeHtml(f.diagnostico || 'No se registró diagnóstico inicial.')}
                    </div>
                </div>

                ${f.trabajo_realizado ? `
                    <div class="mb-4">
                        <div class="seccion-titulo">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                            </svg>
                            Trabajo Realizado
                        </div>
                        <div class="texto-bloque trabajo">
                            ${escapeHtml(f.trabajo_realizado)}
                        </div>
                    </div>
                ` : ''}

                <div class="mb-4">
                    <div class="seccion-titulo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        Observaciones
                    </div>
                    <div class="texto-bloque observaciones ${!f.observaciones ? 'vacio' : ''}">
                        ${escapeHtml(f.observaciones || 'Sin observaciones registradas.')}
                    </div>
                </div>

                <div class="mb-2">
                    <div class="seccion-titulo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                            <rect x="2" y="6" width="20" height="12" rx="2"/>
                            <line x1="6" y1="10" x2="6" y2="14"/>
                            <line x1="10" y1="10" x2="10" y2="14"/>
                            <line x1="14" y1="10" x2="14" y2="14"/>
                            <line x1="18" y1="10" x2="18" y2="14"/>
                        </svg>
                        Componentes Revisados
                        <span class="badge bg-secondary ms-2" style="font-size:0.7rem;">
                            ${f.detalles?.length || 0}
                        </span>
                    </div>
                    <div class="componentes-grid">
                        ${componentesHtml}
                    </div>
                </div>
            </div>
        `;

        document.getElementById('modalDetalleLabel').textContent = 'Detalle de Ficha de Soporte';
        modalBody.innerHTML = html;

    } catch (error) {
        console.error('Error:', error);
        modalBody.innerHTML = `
            <div class="text-center text-danger py-5">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <p class="mt-2">Error de conexión al cargar el detalle</p>
            </div>
        `;
    }
};

// ============================================================
// CONFIRMAR ELIMINAR (abrir modal)
// ============================================================
window.confirmarEliminar = function (id) {
    fichaAEliminar = id;
    document.getElementById('deleteNombre').textContent = `Ficha #${id}`;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
};

console.log('✅ Código de soporte técnico cargado completamente');