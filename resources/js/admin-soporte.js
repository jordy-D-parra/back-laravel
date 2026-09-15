// resources/js/admin-soporte.js
// ✅ VERSIÓN COMPLETA: Fichas + Correos de Soporte + Wizard

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

// Buscador de técnicos
let timeoutTecnicoBusqueda = null;
let tecnicoSeleccionado = null;
let timeoutExtTecnicoBusqueda = null;
let extTecnicoSeleccionado = null;

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
// BUSCADOR DE ACTIVOS
// ============================================================
function cargarActivosParaBuscador() {
    fetch('/admin/activos', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                todosActivos = response.data;
                console.log('Activos cargados:', todosActivos.length);
            }
        })
        .catch(error => console.error('Error cargando activos:', error));
}

function buscarActivos() {
    const input = document.getElementById('activoBuscarInput');
    const dropdown = document.getElementById('activoDropdown');
    if (!input || !dropdown) return;
    const buscar = input.value.toLowerCase().trim();

    if (!buscar || buscar.length < 2) {
        dropdown.style.display = 'none';
        return;
    }

    const filtrados = todosActivos.filter(a => {
        const texto = (a.serial + ' ' + (a.modelo?.nombre || '') + ' ' + (a.modelo?.marca?.nombre || '')).toLowerCase();
        const estaEnProceso = activosEnProceso.includes(a.id);
        return texto.indexOf(buscar) >= 0 && !estaEnProceso;
    }).slice(0, 10);

    if (filtrados.length === 0) {
        dropdown.innerHTML = '<div class="list-group-item text-muted small">No se encontraron activos disponibles</div>';
    } else {
        dropdown.innerHTML = filtrados.map(a => {
            const estado = a.estatus?.descripcion || 'N/A';
            const serial = a.serial || 'Sin serial';
            const modelo = a.modelo?.nombre || 'N/A';
            const marca = a.modelo?.marca?.nombre || 'N/A';
            const id = a.id;

            return `<div class="list-group-item list-group-item-action" data-activo-id="${id}" onclick="window.seleccionarActivo(${id}, '${escapeHtml(serial)}', '${escapeHtml(modelo)}', '${escapeHtml(marca)}', '${escapeHtml(estado)}')">
                <div class="activo-serial"><strong>${escapeHtml(serial)}</strong></div>
                <div class="activo-info">${escapeHtml(marca)} ${escapeHtml(modelo)} - Estado: ${escapeHtml(estado)}</div>
            </div>`;
        }).join('');
    }
    dropdown.style.display = 'block';
}

window.seleccionarActivo = function (id, serial, modelo, marca, estado) {
    const activoIdInput = document.getElementById('fichaActivoId');
    const activoBuscarInput = document.getElementById('activoBuscarInput');
    const dropdown = document.getElementById('activoDropdown');
    const infoDiv = document.getElementById('activoSeleccionadoInfo');
    const texto = document.getElementById('activoSeleccionadoTexto');
    const estadoBadge = document.getElementById('activoSeleccionadoEstado');
    const submitBtn = document.getElementById('btnGuardarFicha');

    if (activoIdInput) activoIdInput.value = id;
    if (activoBuscarInput) {
        activoBuscarInput.value = serial;
        activoBuscarInput.classList.remove('is-invalid');
        activoBuscarInput.classList.add('is-valid');
    }
    if (dropdown) dropdown.style.display = 'none';
    if (infoDiv) {
        infoDiv.style.display = 'block';
        if (texto) texto.textContent = `${serial} - ${marca} ${modelo}`;
        if (estadoBadge) estadoBadge.textContent = estado;
    }
    if (submitBtn) submitBtn.disabled = false;
};

window.limpiarActivoSeleccionado = function () {
    const activoIdInput = document.getElementById('fichaActivoId');
    const activoBuscarInput = document.getElementById('activoBuscarInput');
    const infoDiv = document.getElementById('activoSeleccionadoInfo');
    const submitBtn = document.getElementById('btnGuardarFicha');

    if (activoIdInput) activoIdInput.value = '';
    if (activoBuscarInput) {
        activoBuscarInput.value = '';
        activoBuscarInput.classList.remove('is-valid');
    }
    if (infoDiv) infoDiv.style.display = 'none';
    if (submitBtn) submitBtn.disabled = true;
};

// ============================================================
// BUSCADOR DE TÉCNICOS (ficha manual)
// ============================================================
function buscarTecnicoPorCedula(cedula) {
    const url = '/admin/api/tecnicos?search=' + encodeURIComponent(cedula);
    fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(tecnicos => {
            const searchResults = document.getElementById('tecnicoSearchResults');
            const infoTecnico = document.getElementById('tecnicoEncontrado');
            if (!searchResults) return;

            if (!tecnicos || tecnicos.length === 0) {
                searchResults.innerHTML = `<div class="p-2 text-muted small">No se encontraron técnicos con esta búsqueda.</div>`;
                searchResults.style.display = 'block';
                if (infoTecnico) infoTecnico.style.display = 'none';
                document.getElementById('fichaTecnicoId').value = '';
                document.getElementById('fichaTecnicoNombre').value = '';
                return;
            }

            if (tecnicos.length === 1) {
                window.seleccionarTecnico(tecnicos[0]);
                searchResults.style.display = 'none';
                return;
            }

            let html = '';
            tecnicos.forEach(t => {
                const trabajador = t.trabajador || {};
                const nombre = trabajador.nombre && trabajador.apellido
                    ? `${trabajador.nombre} ${trabajador.apellido}`.trim()
                    : t.usuario || 'Sin nombre';
                const cedulaTecnico = trabajador.cedula || 'Sin cédula';
                const usuario = t.usuario || 'Sin usuario';
                const id = t.id;

                html += `<div class="p-2 border-bottom" style="cursor:pointer;" onclick="window.seleccionarTecnicoPorId(${id})">
                    <strong>${escapeHtml(nombre)}</strong>
                    <br><small class="text-muted">${escapeHtml(cedulaTecnico)} - Usuario: ${escapeHtml(usuario)}</small>
                </div>`;
            });
            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
            if (infoTecnico) infoTecnico.style.display = 'none';
        })
        .catch(error => {
            console.error('Error al buscar técnico:', error);
            const searchResults = document.getElementById('tecnicoSearchResults');
            if (searchResults) {
                searchResults.innerHTML = `<div class="p-2 text-danger small">Error al buscar: ${error.message}</div>`;
                searchResults.style.display = 'block';
            }
        });
}

window.seleccionarTecnicoPorId = function (id) {
    fetch('/admin/api/tecnicos/' + id, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(response => {
            if (response.success && response.data) {
                window.seleccionarTecnico(response.data);
            }
        })
        .catch(error => console.error('Error al obtener técnico:', error));
};

window.seleccionarTecnico = function (tecnico) {
    const trabajador = tecnico.trabajador || {};
    const nombre = trabajador.nombre && trabajador.apellido
        ? `${trabajador.nombre} ${trabajador.apellido}`.trim()
        : tecnico.usuario || 'Sin nombre';
    const cedula = trabajador.cedula || 'Sin cédula';
    const usuario = tecnico.usuario || 'Sin usuario';

    document.getElementById('fichaTecnicoId').value = tecnico.id;
    document.getElementById('fichaTecnicoNombre').value = nombre;
    document.getElementById('tecnicoBuscarInput').value = nombre;
    document.getElementById('tecnicoSearchResults').style.display = 'none';

    const infoTecnico = document.getElementById('tecnicoEncontrado');
    if (infoTecnico) {
        document.getElementById('tecnicoEncontradoNombre').textContent = nombre;
        document.getElementById('tecnicoEncontradoCedula').textContent = `Cédula: ${cedula}`;
        document.getElementById('tecnicoEncontradoUsuario').textContent = `Usuario: ${usuario}`;
        infoTecnico.style.display = 'block';
    }
    tecnicoSeleccionado = tecnico;
};

window.limpiarTecnicoSeleccionado = function () {
    document.getElementById('fichaTecnicoId').value = '';
    document.getElementById('fichaTecnicoNombre').value = '';
    document.getElementById('tecnicoBuscarInput').value = '';
    document.getElementById('tecnicoEncontrado').style.display = 'none';
    document.getElementById('tecnicoSearchResults').style.display = 'none';
    tecnicoSeleccionado = null;
};

// ============================================================
// BUSCADOR DE TÉCNICOS (equipo externo)
// ============================================================
function buscarExtTecnicoPorCedula(cedula) {
    const url = '/admin/api/tecnicos?search=' + encodeURIComponent(cedula);
    fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(tecnicos => {
            const searchResults = document.getElementById('extTecnicoSearchResults');
            const infoTecnico = document.getElementById('extTecnicoEncontrado');
            if (!searchResults) return;

            if (!tecnicos || tecnicos.length === 0) {
                searchResults.innerHTML = `<div class="p-2 text-muted small">No se encontraron técnicos con esta búsqueda.</div>`;
                searchResults.style.display = 'block';
                if (infoTecnico) infoTecnico.style.display = 'none';
                document.getElementById('ext_fichaTecnicoId').value = '';
                document.getElementById('ext_fichaTecnicoNombre').value = '';
                return;
            }

            if (tecnicos.length === 1) {
                window.seleccionarExtTecnico(tecnicos[0]);
                searchResults.style.display = 'none';
                return;
            }

            let html = '';
            tecnicos.forEach(t => {
                const trabajador = t.trabajador || {};
                const nombre = trabajador.nombre && trabajador.apellido
                    ? `${trabajador.nombre} ${trabajador.apellido}`.trim()
                    : t.usuario || 'Sin nombre';
                const cedulaTecnico = trabajador.cedula || 'Sin cédula';
                const usuario = t.usuario || 'Sin usuario';
                const id = t.id;

                html += `<div class="p-2 border-bottom" style="cursor:pointer;" onclick="window.seleccionarExtTecnicoPorId(${id})">
                    <strong>${escapeHtml(nombre)}</strong>
                    <br><small class="text-muted">${escapeHtml(cedulaTecnico)} - Usuario: ${escapeHtml(usuario)}</small>
                </div>`;
            });
            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
            if (infoTecnico) infoTecnico.style.display = 'none';
        })
        .catch(error => {
            console.error('Error al buscar técnico externo:', error);
            const searchResults = document.getElementById('extTecnicoSearchResults');
            if (searchResults) {
                searchResults.innerHTML = `<div class="p-2 text-danger small">Error al buscar: ${error.message}</div>`;
                searchResults.style.display = 'block';
            }
        });
}

window.seleccionarExtTecnicoPorId = function (id) {
    fetch('/admin/api/tecnicos/' + id, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(response => {
            if (response.success && response.data) {
                window.seleccionarExtTecnico(response.data);
            }
        })
        .catch(error => console.error('Error al obtener técnico externo:', error));
};

window.seleccionarExtTecnico = function (tecnico) {
    const trabajador = tecnico.trabajador || {};
    const nombre = trabajador.nombre && trabajador.apellido
        ? `${trabajador.nombre} ${trabajador.apellido}`.trim()
        : tecnico.usuario || 'Sin nombre';
    const cedula = trabajador.cedula || 'Sin cédula';
    const usuario = tecnico.usuario || 'Sin usuario';

    document.getElementById('ext_fichaTecnicoId').value = tecnico.id;
    document.getElementById('ext_fichaTecnicoNombre').value = nombre;
    document.getElementById('ext_tecnicoBuscarInput').value = nombre;
    document.getElementById('extTecnicoSearchResults').style.display = 'none';

    const infoTecnico = document.getElementById('extTecnicoEncontrado');
    if (infoTecnico) {
        document.getElementById('extTecnicoEncontradoNombre').textContent = nombre;
        document.getElementById('extTecnicoEncontradoCedula').textContent = `Cédula: ${cedula}`;
        document.getElementById('extTecnicoEncontradoUsuario').textContent = `Usuario: ${usuario}`;
        infoTecnico.style.display = 'block';
    }
    extTecnicoSeleccionado = tecnico;
};

window.limpiarExtTecnicoSeleccionado = function () {
    const el = document.getElementById('ext_fichaTecnicoId');
    if (el) el.value = '';
    const el2 = document.getElementById('ext_fichaTecnicoNombre');
    if (el2) el2.value = '';
    const el3 = document.getElementById('ext_tecnicoBuscarInput');
    if (el3) el3.value = '';
    const el4 = document.getElementById('extTecnicoEncontrado');
    if (el4) el4.style.display = 'none';
    const el5 = document.getElementById('extTecnicoSearchResults');
    if (el5) el5.style.display = 'none';
    extTecnicoSeleccionado = null;
};

// ============================================================
// MODALES DE CREACIÓN (ficha manual y equipo externo)
// ============================================================
window.abrirModalCrearFicha = function () {
    const form = document.getElementById('formCrearFicha');
    if (form) form.reset();

    window.limpiarActivoSeleccionado();
    window.limpiarTecnicoSeleccionado();

    const errorDiv = document.getElementById('activoErrorMensaje');
    if (errorDiv) errorDiv.style.display = 'none';

    const submitBtn = document.getElementById('btnGuardarFicha');
    if (submitBtn) submitBtn.disabled = true;

    new bootstrap.Modal(document.getElementById('modalCrearFicha')).show();

    if (todosActivos.length === 0) cargarActivosParaBuscador();
};

window.abrirModalEquipoExterno = function () {
    const form = document.getElementById('formEquipoExterno');
    if (form) form.reset();

    window.limpiarExtTecnicoSeleccionado();
    window.limpiarActivoSeleccionado();

    const fechaInput = document.getElementById('ext_fecha_adquisicion');
    if (fechaInput) fechaInput.value = new Date().toISOString().split('T')[0];

    configurarValidacionSerial();
    cargarCategoriasExterno();
    cargarInstitucionesExterno();

    new bootstrap.Modal(document.getElementById('modalEquipoExterno')).show();
};

// Validación de serial: se ejecuta UNA SOLA VEZ (no en cada apertura)
function configurarValidacionSerial() {
    const serialInput = document.getElementById('ext_serial');
    if (!serialInput || serialInput.dataset.bound === '1') return;
    serialInput.dataset.bound = '1';

    serialInput.addEventListener('blur', function () {
        const serial = this.value.trim();
        let feedback = document.getElementById('ext_serial_feedback');

        if (!feedback) {
            feedback = document.createElement('small');
            feedback.id = 'ext_serial_feedback';
            feedback.className = 'text-muted d-block mt-1';
            feedback.style.fontSize = '0.75rem';
            this.parentNode.appendChild(feedback);
        }

        if (serial.length < 3) {
            feedback.textContent = 'Ingrese al menos 3 caracteres para verificar';
            feedback.className = 'text-muted d-block mt-1';
            feedback.style.fontSize = '0.75rem';
            this.classList.remove('is-valid', 'is-invalid');
            return;
        }

        feedback.textContent = 'Verificando serial...';
        feedback.className = 'text-muted d-block mt-1';
        feedback.style.fontSize = '0.75rem';

        fetch(`/admin/activos?buscar=${encodeURIComponent(serial)}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const existe = data.data.some(a => a.serial === serial);
                if (existe) {
                    feedback.textContent = '⚠️ Este serial ya está registrado en el sistema.';
                    feedback.className = 'text-danger d-block mt-1';
                    feedback.style.fontSize = '0.75rem';
                    serialInput.classList.add('is-invalid');
                    serialInput.classList.remove('is-valid');
                } else {
                    feedback.textContent = '✓ Serial disponible';
                    feedback.className = 'text-success d-block mt-1';
                    feedback.style.fontSize = '0.75rem';
                    serialInput.classList.add('is-valid');
                    serialInput.classList.remove('is-invalid');
                }
            }
        })
        .catch(() => {
            feedback.textContent = 'Error al verificar serial';
            feedback.className = 'text-danger d-block mt-1';
            feedback.style.fontSize = '0.75rem';
        });
    });
}

// ============================================================
// SELECTS DE EQUIPO EXTERNO
// ============================================================
function cargarCategoriasExterno() {
    fetch('/admin/equipos/categorias-list', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                const select = document.getElementById('ext_categoria_id');
                if (select) {
                    select.innerHTML = '<option value="">Seleccionar categoría...</option>';
                    response.data.forEach(cat => {
                        select.innerHTML += `<option value="${cat.id}">${escapeHtml(cat.nombre)}</option>`;
                    });
                }
            }
        })
        .catch(error => console.error('Error cargando categorías:', error));
}

function cargarInstitucionesExterno() {
    fetch('/admin/instituciones?todos=1', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(response => {
            const instituciones = response.data || response;
            const select = document.getElementById('ext_institucion_id');
            if (select && instituciones) {
                select.innerHTML = '<option value="">Seleccionar institución...</option>';
                instituciones.forEach(inst => {
                    select.innerHTML += `<option value="${inst.id}">${escapeHtml(inst.nombre)}</option>`;
                });
            }
        })
        .catch(error => console.error('Error cargando instituciones:', error));
}

function cargarResponsablesExterno(institucionId) {
    const select = document.getElementById('ext_responsable_id');
    if (!select) return;
    if (!institucionId) {
        select.innerHTML = '<option value="">Seleccionar responsable...</option>';
        return;
    }
    fetch(`/admin/responsables?institucion_id=${institucionId}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                select.innerHTML = '<option value="">Seleccionar responsable...</option>';
                response.data.forEach(resp => {
                    select.innerHTML += `<option value="${resp.id}">${escapeHtml(resp.nombre)} - ${escapeHtml(resp.cargo || 'Sin cargo')}</option>`;
                });
            }
        })
        .catch(error => console.error('Error cargando responsables:', error));
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
        if (datos.fecha_requerida || correoSoporteActual.fecha_requerida_entrega) {
            const el = document.getElementById('wzFechaRequeridaSoporte');
            if (el) el.value = datos.fecha_requerida || correoSoporteActual.fecha_requerida_entrega;
        }
        if (correoSoporteActual.from_name) {
            const el = document.getElementById('wzUsuarioReporta');
            if (el) el.value = correoSoporteActual.from_name;
        }

        if (correoSoporteActual.procesado) {
            document.getElementById('botonIniciarWizardSoporte').innerHTML = `
                <div class="alert alert-success">
                    ✅ Este correo ya fue convertido en la ficha de soporte
                    #${correoSoporteActual.ficha_soporte_id}
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
    irPasoSoporte(1);
};

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

    // ========== BUSCADOR DE ACTIVOS ==========
    const activoInput = document.getElementById('activoBuscarInput');
    if (activoInput) {
        activoInput.addEventListener('input', buscarActivos);
        activoInput.addEventListener('focus', function () {
            if (this.value.length >= 2) buscarActivos();
        });
        activoInput.addEventListener('blur', function () {
            setTimeout(() => {
                const dropdown = document.getElementById('activoDropdown');
                if (dropdown) dropdown.style.display = 'none';
            }, 300);
        });
    }

    // ========== BUSCADOR DE TÉCNICOS ==========
    const tecnicoInput = document.getElementById('tecnicoBuscarInput');
    if (tecnicoInput) {
        tecnicoInput.addEventListener('input', function () {
            const cedula = this.value.trim();
            clearTimeout(timeoutTecnicoBusqueda);
            if (cedula.length < 2) {
                const el1 = document.getElementById('tecnicoSearchResults');
                if (el1) el1.style.display = 'none';
                const el2 = document.getElementById('tecnicoEncontrado');
                if (el2) el2.style.display = 'none';
                const el3 = document.getElementById('fichaTecnicoId');
                if (el3) el3.value = '';
                const el4 = document.getElementById('fichaTecnicoNombre');
                if (el4) el4.value = '';
                return;
            }
            timeoutTecnicoBusqueda = setTimeout(() => buscarTecnicoPorCedula(cedula), 400);
        });
    }

    // ========== BUSCADOR DE TÉCNICOS (equipo externo) ==========
    const extTecnicoInput = document.getElementById('ext_tecnicoBuscarInput');
    if (extTecnicoInput) {
        extTecnicoInput.addEventListener('input', function () {
            const cedula = this.value.trim();
            clearTimeout(timeoutExtTecnicoBusqueda);
            if (cedula.length < 2) {
                const el1 = document.getElementById('extTecnicoSearchResults');
                if (el1) el1.style.display = 'none';
                const el2 = document.getElementById('extTecnicoEncontrado');
                if (el2) el2.style.display = 'none';
                const el3 = document.getElementById('ext_fichaTecnicoId');
                if (el3) el3.value = '';
                const el4 = document.getElementById('ext_fichaTecnicoNombre');
                if (el4) el4.value = '';
                return;
            }
            timeoutExtTecnicoBusqueda = setTimeout(() => buscarExtTecnicoPorCedula(cedula), 400);
        });
    }

    // ========== RESPONSABLES AL CAMBIAR INSTITUCIÓN ==========
    const instSelect = document.getElementById('ext_institucion_id');
    if (instSelect) {
        instSelect.addEventListener('change', function () {
            cargarResponsablesExterno(this.value);
        });
    }

    // ========== CARGA INICIAL ==========
    cargarPagina(1);
    cargarActivosParaBuscador();
    actualizarBadgeCorreosSoporte();

    // ========== SUBMIT FORM CREAR FICHA ==========
    const formCrearFicha = document.getElementById('formCrearFicha');
    if (formCrearFicha) {
        formCrearFicha.addEventListener('submit', async function (e) {
            e.preventDefault();
            const activoId = document.getElementById('fichaActivoId').value;
            if (!activoId) {
                mostrarNotificacion('error', 'Debe seleccionar un activo válido');
                document.getElementById('activoBuscarInput').classList.add('is-invalid');
                return;
            }
            if (activosEnProceso.includes(parseInt(activoId))) {
                mostrarNotificacion('error', 'Este activo ya tiene una ficha de soporte en proceso');
                return;
            }
            const submitBtn = document.getElementById('btnGuardarFicha');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creando...';
            submitBtn.disabled = true;
            const formData = new FormData(this);
            const tecnicoNombre = document.getElementById('fichaTecnicoNombre').value;
            if (tecnicoNombre) formData.set('tecnico_nombre', tecnicoNombre);
            try {
                const response = await fetch('/admin/soporte', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    mostrarNotificacion('success', result.message || 'Ficha creada exitosamente');
                    bootstrap.Modal.getInstance(document.getElementById('modalCrearFicha')).hide();
                    cargarPagina(1);
                    actualizarEstadisticas();
                    cargarActivosParaBuscador();
                } else {
                    mostrarNotificacion('error', result.message || 'Error al crear la ficha');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarNotificacion('error', 'Error de conexión al servidor');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    // ========== SUBMIT FORM EQUIPO EXTERNO ==========
    const formEquipoExterno = document.getElementById('formEquipoExterno');
    if (formEquipoExterno) {
        formEquipoExterno.addEventListener('submit', async function (e) {
            e.preventDefault();
            const submitBtn = document.getElementById('btnGuardarEquipoExterno');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Registrando...';
            submitBtn.disabled = true;
            const formData = new FormData(this);
            try {
                const response = await fetch('/admin/soporte/equipo-externo', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    mostrarNotificacion('success', result.message || 'Equipo registrado');
                    bootstrap.Modal.getInstance(document.getElementById('modalEquipoExterno')).hide();
                    cargarPagina(1);
                    cargarActivosParaBuscador();
                } else {
                    mostrarNotificacion('error', result.message || 'Error al registrar el equipo');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarNotificacion('error', 'Error de conexión al servidor');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }

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
                    cargarActivosParaBuscador();
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
// VER DETALLE DE FICHA
// ============================================================
window.verDetalle = async function (id) {
    const modalBody = document.getElementById('detalleContenido');
    modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando detalles...</p></div>';
    new bootstrap.Modal(document.getElementById('modalDetalle')).show();

    try {
        const response = await fetch(`/admin/soporte/${id}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
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
                                        ${det.estado_salida
                                            ? `<span class="badge ${det.estado_salida === 'funcionando' ? 'bg-success' : (det.estado_salida === 'reemplazado' ? 'bg-warning' : 'bg-danger')} text-white">Salida: ${escapeHtml(det.estado_salida)}</span>`
                                            : `<span class="badge bg-info">Ingreso: ${escapeHtml(det.estado_ingreso || 'N/A')}</span>`}
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
                    ${f.fecha_requerida_entrega ? `
                        <div class="mb-3">
                            <label class="text-muted small">Fecha Requerida de Entrega</label>
                            <div class="fw-semibold">${new Date(f.fecha_requerida_entrega).toLocaleDateString()}</div>
                        </div>
                    ` : ''}
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

// ============================================================
// CONFIRMAR ELIMINAR (abrir modal)
// ============================================================
window.confirmarEliminar = function (id) {
    fichaAEliminar = id;
    document.getElementById('deleteNombre').textContent = `Ficha #${id}`;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
};

console.log('✅ Código de soporte técnico cargado completamente');