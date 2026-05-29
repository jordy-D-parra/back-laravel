// resources/js/admin-soporte.js

let fichasData = [];
let currentPage = 1;
let lastPage = 1;
let perPage = 10;
let totalRegistros = 0;
let timeoutBusqueda = null;
let fichaAEliminar = null;
let activosEnProceso = []; // Array para almacenar IDs de activos con ficha en proceso

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

let filtros = {
    search: '',
    estado: ''
};

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
    const toast = document.createElement('div');
    toast.style.cssText = `background: ${colores[tipo]}; color: white; border-radius: 10px; padding: 12px 16px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 12px; cursor: pointer; animation: slideIn 0.3s ease-out; z-index: 10000;`;
    toast.innerHTML = `<span style="flex:1">${mensaje}</span><span style="opacity:0.7; cursor:pointer;" onclick="this.parentElement.remove()">✕</span>`;
    container.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) toast.remove();
    }, 4000);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(style);

function actualizarEstadisticas() {
    const total = fichasData.length;
    const enProceso = fichasData.filter(f => f.estado === 'en_proceso').length;
    const finalizados = fichasData.filter(f => f.estado === 'finalizado').length;

    document.getElementById('statsTotal') && (document.getElementById('statsTotal').textContent = total);
    document.getElementById('statsEnProceso') && (document.getElementById('statsEnProceso').textContent = enProceso);
    document.getElementById('statsFinalizados') && (document.getElementById('statsFinalizados').textContent = finalizados);
    
    const enReparacion = fichasData.filter(f => f.estado === 'en_proceso').length;
    document.getElementById('statsEquiposReparacion') && (document.getElementById('statsEquiposReparacion').textContent = enReparacion);
    
    // Actualizar lista de activos en proceso
    activosEnProceso = fichasData.filter(f => f.estado === 'en_proceso').map(f => f.activo_id);
}

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
        const fechaSalida = f.fecha_salida ? new Date(f.fecha_salida).toLocaleDateString() : '—';
        const activoInfo = f.activo ? `${f.activo.serial} - ${f.activo.modelo?.nombre || 'N/A'}` : 'N/A';
        const estadoClass = f.estado === 'en_proceso' ? 'badge-estado-en-proceso' : 'badge-estado-finalizado';
        const estadoText = f.estado === 'en_proceso' ? 'En Proceso' : 'Finalizado';

        html += `<tr>
            <td class="px-3 py-2">${escapeHtml(activoInfo)}</td>
            <td class="px-3 py-2">${escapeHtml(f.tecnico_nombre || '—')}</td>
            <td class="px-3 py-2">${escapeHtml(f.usuario_reporta_nombre || '—')}</td>
            <td class="px-3 py-2">${fechaIngreso}</td>
            <td class="px-3 py-2">${fechaSalida}</td>
            <td class="px-3 py-2"><span class="${estadoClass}">${estadoText}</span></td>
            <td class="px-3 py-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-primary-dark" onclick="verDetalle(${f.id})" title="Ver detalle">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                </button>`;
        
        if (f.estado === 'en_proceso') {
            html += `
                <button type="button" class="btn btn-sm btn-cerrar-ficha ms-1" onclick="abrirModalCerrarFicha(${f.id})" title="Cerrar ficha">
                    ✓ Cerrar
                </button>`;
        }
        
        html += `
                <button type="button" class="btn btn-sm btn-outline-danger ms-1" onclick="confirmarEliminar(${f.id})" title="Eliminar">
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
    
    const infoDiv = document.getElementById('paginationInfo');
    if (infoDiv) {
        infoDiv.innerHTML = `Mostrando ${fichasData.length} de ${totalRegistros} registros`;
    }
}

function cambiarPagina(page) {
    if (page < 1 || page > lastPage) return;
    currentPage = page;
    cargarPagina(currentPage);
}

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
            }
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
        document.getElementById('tablaFichas').innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Error al cargar los datos</td></tr>';
    }
}

function aplicarFiltros() {
    filtros = {
        search: document.getElementById('buscarFichas')?.value || '',
        estado: document.getElementById('filtroEstadoFichas')?.value || ''
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

// ==================== VALIDACIÓN DE ACTIVO EN PROCESO ====================
function verificarActivoEnProceso(activoId) {
    return activosEnProceso.includes(parseInt(activoId));
}

// ==================== CREAR FICHA ====================
window.abrirModalCrearFicha = function() {
    document.getElementById('formCrearFicha').reset();
    // Limpiar mensajes de error anteriores
    const errorDiv = document.getElementById('activoErrorMensaje');
    if (errorDiv) errorDiv.style.display = 'none';
    new bootstrap.Modal(document.getElementById('modalCrearFicha')).show();
};

// Validar cuando se selecciona un activo en el formulario
document.getElementById('fichaActivoId')?.addEventListener('change', function() {
    const activoId = this.value;
    const errorDiv = document.getElementById('activoErrorMensaje');
    const submitBtn = document.querySelector('#formCrearFicha button[type="submit"]');
    
    if (activoId && verificarActivoEnProceso(activoId)) {
        if (errorDiv) {
            errorDiv.style.display = 'block';
            errorDiv.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert" style="font-size: 0.8rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 5px;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <strong>¡Activo no disponible!</strong> Este equipo ya tiene una ficha de soporte en proceso.
                    <button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>
                </div>
            `;
        }
        if (submitBtn) submitBtn.disabled = true;
        this.classList.add('is-invalid');
    } else {
        if (errorDiv) errorDiv.style.display = 'none';
        if (submitBtn) submitBtn.disabled = false;
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
    }
});

document.getElementById('formCrearFicha')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const activoId = document.getElementById('fichaActivoId').value;
    
    // Validación antes de enviar
    if (verificarActivoEnProceso(activoId)) {
        mostrarNotificacion('error', 'Este activo ya tiene una ficha de soporte en proceso. No puede crear otra.');
        return;
    }

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creando...';
    submitBtn.disabled = true;

    const formData = new FormData(this);

    try {
        const response = await fetch('/admin/soporte', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
            mostrarNotificacion('success', result.message || 'Ficha creada exitosamente');
            bootstrap.Modal.getInstance(document.getElementById('modalCrearFicha')).hide();
            cargarPagina(1);
        } else {
            const errorMsg = result.message || 'Error al crear la ficha';
            mostrarNotificacion('error', errorMsg);
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión al servidor');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// ==================== CERRAR FICHA ====================
window.abrirModalCerrarFicha = async function(id) {
    try {
        const response = await fetch(`/admin/soporte/${id}/componentes`);
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

document.getElementById('formCerrarFicha')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('cerrarFichaId').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Finalizando...';
    submitBtn.disabled = true;

    const formData = new FormData(this);

    try {
        const response = await fetch(`/admin/soporte/${id}/close`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
            mostrarNotificacion('success', result.message || 'Ficha finalizada exitosamente');
            bootstrap.Modal.getInstance(document.getElementById('modalCerrarFicha')).hide();
            cargarPagina(currentPage);
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

// ==================== VER DETALLE ====================
window.verDetalle = async function(id) {
    const modalBody = document.getElementById('detalleContenido');
    modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando detalles...</p></div>';
    new bootstrap.Modal(document.getElementById('modalDetalle')).show();

    try {
        const response = await fetch(`/admin/soporte/${id}`);
        const result = await response.json();

        if (result.success && result.data) {
            const f = result.data;
            
            const fechaIngreso = f.fecha_ingreso ? new Date(f.fecha_ingreso).toLocaleString() : 'No registrada';
            const fechaSalida = f.fecha_salida ? new Date(f.fecha_salida).toLocaleString() : 'En proceso';
            
            const estadoColor = f.estado === 'en_proceso' ? '#fd7e14' : '#28a745';
            const estadoIcono = f.estado === 'en_proceso' ? '🔧' : '✅';
            
            let detallesHtml = '';
            if (f.detalles && f.detalles.length > 0) {
                detallesHtml = `
                    <div class="detalle-seccion mb-3">
                        <h6 class="fw-bold" style="color: #1e3c72;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            Componentes Revisados (${f.detalles.length})
                        </h6>
                        <div class="row">
                            ${f.detalles.map(det => `
                                <div class="col-md-6 mb-2">
                                    <div class="border rounded p-2" style="background: #f8f9fc;">
                                        <strong>${escapeHtml(det.componente_nombre)}</strong><br>
                                        ${det.estado_salida ? 
                                            `<span class="badge ${det.estado_salida === 'funcionando' ? 'bg-success' : (det.estado_salida === 'reemplazado' ? 'bg-warning' : 'bg-danger')} text-white">
                                                Salida: ${escapeHtml(det.estado_salida)}
                                            </span>` : 
                                            `<span class="badge bg-info">Ingreso: ${escapeHtml(det.estado_ingreso || 'N/A')}</span>`
                                        }
                                        ${det.observaciones ? `<br><small class="text-muted">${escapeHtml(det.observaciones)}</small>` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            const html = `
                <div>
                    <div style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); margin: -1rem -1rem 1.5rem -1rem; padding: 1.5rem; border-radius: 12px 12px 0 0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 text-white">Ficha de Soporte #${f.id}</h4>
                                <p class="mb-0 text-white-50 mt-1">
                                    ${escapeHtml(f.activo?.serial || 'N/A')} - ${escapeHtml(f.activo?.modelo?.nombre || 'N/A')}
                                </p>
                            </div>
                            <span style="background: ${estadoColor}; color: white; padding: 0.5rem 1rem; border-radius: 30px;">
                                ${estadoIcono} ${f.estado === 'en_proceso' ? 'En Proceso' : 'Finalizado'}
                            </span>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Técnico</label>
                                <div class="fw-semibold">${escapeHtml(f.tecnico_nombre || 'No asignado')}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Usuario Reporta</label>
                                <div class="fw-semibold">${escapeHtml(f.usuario_reporta_nombre || 'No especificado')}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Fecha Ingreso</label>
                                <div class="fw-semibold">${fechaIngreso}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Fecha Salida</label>
                                <div class="fw-semibold">${fechaSalida}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Diagnóstico Inicial</label>
                        <div class="p-2 bg-light rounded">${escapeHtml(f.diagnostico || 'No registrado')}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Trabajo Realizado</label>
                        <div class="p-2 bg-light rounded">${escapeHtml(f.trabajo_realizado || 'No registrado')}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Observaciones</label>
                        <div class="p-2 bg-light rounded">${escapeHtml(f.observaciones || 'Sin observaciones')}</div>
                    </div>
                    
                    ${detallesHtml}
                </div>
            `;
            
            document.getElementById('modalDetalleLabel').textContent = 'Detalle de Ficha de Soporte';
            modalBody.innerHTML = html;
        } else {
            modalBody.innerHTML = '<div class="text-center text-danger py-4">Error al cargar detalle</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="text-center text-danger py-4">Error de conexión</div>';
    }
};

// ==================== ELIMINAR ====================
window.confirmarEliminar = function(id) {
    fichaAEliminar = id;
    document.getElementById('deleteNombre').textContent = `Ficha #${id}`;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
};

document.getElementById('btnConfirmarEliminar')?.addEventListener('click', async function() {
    if (!fichaAEliminar) return;

    try {
        const response = await fetch(`/admin/soporte/${fichaAEliminar}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
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

// ==================== LIMPIAR FILTROS ====================
document.getElementById('limpiarFiltros')?.addEventListener('click', function() {
    document.getElementById('buscarFichas').value = '';
    document.getElementById('filtroEstadoFichas').value = '';
    aplicarFiltros();
});

// ==================== EVENT LISTENERS ====================
function initEventListeners() {
    document.getElementById('buscarFichas')?.addEventListener('input', aplicarFiltrosConDebounce);
    document.getElementById('filtroEstadoFichas')?.addEventListener('change', aplicarFiltros);
}

// ==================== INICIALIZACIÓN ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de fichas de soporte inicializado');
    initEventListeners();
    cargarPagina(1);
});