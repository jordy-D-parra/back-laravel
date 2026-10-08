// resources/js/admin-trabajadores.js
import 'bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    // ===========================
    // REFERENCIAS
    // ===========================
    const modalTrabajador = document.getElementById('modalTrabajador');
    const formTrabajador = document.getElementById('formTrabajador');
    const modalTitulo = document.getElementById('modalTrabajadorTitulo');
    const methodInput = document.getElementById('trabajadorMethod');
    const btnGuardar = document.getElementById('btnGuardarTrabajador');

    const modalConfirmDelete = document.getElementById('modalConfirmDelete');
    const formDelete = document.getElementById('formDelete');
    const deleteTrabajadorNombre = document.getElementById('deleteTrabajadorNombre');
    const deleteWarningUsuario = document.getElementById('deleteWarningUsuario');
    const modalDetail = document.getElementById('modalDetail');

    // ===========================
    // ESTADO DE PAGINACIÓN
    // ===========================
    const ITEMS_POR_PAGINA = 8;
    let trabajadoresData = [];
    let trabajadoresFiltrados = [];
    let paginaActual = 1;

    // ===========================
    // HELPERS DE PAGINACIÓN
    // ===========================
    function renderPaginacion(totalPaginas, pageActual, totalRegistros) {
        if (totalPaginas <= 1) return '';

        let html = `<div class="pagination-bar">
            <div class="pagination-info">Mostrando ${((pageActual - 1) * ITEMS_POR_PAGINA) + 1} a ${Math.min(pageActual * ITEMS_POR_PAGINA, totalRegistros)} de ${totalRegistros} registros</div>
            <div class="pagination-btns">`;

        // Botón anterior
        html += `<button class="pagination-btn ${pageActual === 1 ? 'disabled' : ''}"
                    onclick="window.cambiarPaginaTrabajadores(${pageActual - 1})" ${pageActual === 1 ? 'disabled' : ''}>«</button>`;

        // Números de página
        let inicio = Math.max(1, pageActual - 2);
        let fin = Math.min(totalPaginas, pageActual + 2);

        if (inicio > 1) {
            html += `<button class="pagination-btn" onclick="window.cambiarPaginaTrabajadores(1)">1</button>`;
            if (inicio > 2) html += `<span class="pagination-ellipsis">...</span>`;
        }

        for (let i = inicio; i <= fin; i++) {
            html += `<button class="pagination-btn ${i === pageActual ? 'active' : ''}"
                        onclick="window.cambiarPaginaTrabajadores(${i})">${i}</button>`;
        }

        if (fin < totalPaginas) {
            if (fin < totalPaginas - 1) html += `<span class="pagination-ellipsis">...</span>`;
            html += `<button class="pagination-btn" onclick="window.cambiarPaginaTrabajadores(${totalPaginas})">${totalPaginas}</button>`;
        }

        // Botón siguiente
        html += `<button class="pagination-btn ${pageActual === totalPaginas ? 'disabled' : ''}"
                    onclick="window.cambiarPaginaTrabajadores(${pageActual + 1})" ${pageActual === totalPaginas ? 'disabled' : ''}>»</button>`;

        html += `</div></div>`;
        return html;
    }

    window.cambiarPaginaTrabajadores = function(nuevaPagina) {
        const totalPaginas = Math.ceil(trabajadoresFiltrados.length / ITEMS_POR_PAGINA);
        if (nuevaPagina < 1 || nuevaPagina > totalPaginas) return;

        paginaActual = nuevaPagina;
        renderizarTablaTrabajadores();

        document.querySelector('.table-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // ===========================
    // RENDERIZAR TABLA
    // ===========================
    function renderizarTablaTrabajadores() {
        const tbody = document.getElementById('tablaTrabajadores');
        if (!tbody) return;

        const total = trabajadoresFiltrados.length;
        const totalPaginas = Math.ceil(total / ITEMS_POR_PAGINA);

        // Si la página actual quedó fuera de rango (por filtro)
        if (paginaActual > totalPaginas && totalPaginas > 0) {
            paginaActual = totalPaginas;
        }

        const inicio = (paginaActual - 1) * ITEMS_POR_PAGINA;
        const paginaData = trabajadoresFiltrados.slice(inicio, inicio + ITEMS_POR_PAGINA);

        if (total === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" class="mb-2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                        <p>No se encontraron trabajadores</p>
                    </td>
                </tr>`;
            const pag = document.getElementById('paginacionTrabajadores');
            if (pag) pag.innerHTML = '';
            return;
        }

        let html = '';
        for (const t of paginaData) {
            html += `
                <tr>
                    <td><small>${escapeHtml(t.cedula)}</small></td>
                    <td><span class="fw-medium" style="color: var(--primary-dark);">${escapeHtml(t.nombre)} ${escapeHtml(t.apellido)}</span></td>
                    <td>${escapeHtml(t.cargo || '-')}</td>
                    <td>${escapeHtml(t.departamento || '-')}</td>
                    <td>${escapeHtml(t.especialidad || '-')}</td>
                    <td>
                        ${t.usuario
                            ? `<span class="badge-usuario-si">${escapeHtml(t.usuario.usuario)}</span>`
                            : `<span class="badge-usuario-no">Sin usuario</span>`}
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-action btn-outline-primary-dark btn-ver-trabajador"
                                data-id="${t.id}" title="Ver detalle">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>

                            <button class="btn btn-sm btn-action btn-outline-primary-dark btn-editar-trabajador"
                                data-id="${t.id}"
                                data-cedula="${escapeHtml(t.cedula)}"
                                data-nombre="${escapeHtml(t.nombre)}"
                                data-apellido="${escapeHtml(t.apellido)}"
                                data-email="${escapeHtml(t.email || '')}"
                                data-departamento="${escapeHtml(t.departamento || '')}"
                                data-cargo="${escapeHtml(t.cargo || '')}"
                                data-especialidad="${escapeHtml(t.especialidad || '')}"
                                data-telefono="${escapeHtml(t.telefono || '')}"
                                title="Editar">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>

                            ${!t.usuario
                                ? `<a href="/admin/usuarios?search=${encodeURIComponent(t.cedula)}&crear=1"
                                    class="btn btn-sm btn-action btn-outline-primary-dark" title="Crear usuario">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </a>`
                                : ''}

                            <button class="btn btn-sm btn-action btn-outline-danger btn-eliminar-trabajador"
                                data-id="${t.id}"
                                data-nombre="${escapeHtml(t.nombre)} ${escapeHtml(t.apellido)}"
                                data-tiene-usuario="${t.usuario ? '1' : '0'}"
                                title="Eliminar">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#c5221f" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
        tbody.innerHTML = html;

        // Inicializar eventos de los botones recién creados
        bindEventosBotones();

        // Renderizar paginación
        const paginacionContainer = document.getElementById('paginacionTrabajadores');
        if (paginacionContainer) {
            paginacionContainer.innerHTML = renderPaginacion(totalPaginas, paginaActual, total);
        }
    }

    // ===========================
    // HELPERS
    // ===========================
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ===========================
    // CARGA INICIAL (desde el DOM ya renderizado por Blade)
    // ===========================
    function cargarDatosDesdeBlade() {
        // Los datos vienen como JSON desde el Blade
        if (window.trabajadoresIniciales && Array.isArray(window.trabajadoresIniciales)) {
            trabajadoresData = window.trabajadoresIniciales;
            trabajadoresFiltrados = [...trabajadoresData];
            paginaActual = 1;
            renderizarTablaTrabajadores();
        }
    }

    // ===========================
    // VALIDACIÓN DE CÉDULA
    // ===========================
    const cedulaInput = document.getElementById('trabajadorCedula');
    const cedulaFeedback = document.getElementById('cedulaFeedback');

    if (cedulaInput) {
        cedulaInput.addEventListener('input', function () {
            let value = this.value.replace(/[^0-9VvEe-]/g, '').toUpperCase();
            if (/^\d{7,8}$/.test(value)) {
                value = 'V-' + value;
            }
            this.value = value;

            const regexCedula = /^[VEJPG]-\d{7,8}$/;
            if (value.length === 0) {
                this.classList.remove('cedula-valid', 'cedula-invalid');
                if (cedulaFeedback) cedulaFeedback.textContent = '';
            } else if (regexCedula.test(value)) {
                this.classList.add('cedula-valid');
                this.classList.remove('cedula-invalid');
                if (cedulaFeedback) {
                    cedulaFeedback.textContent = 'Formato válido';
                    cedulaFeedback.style.color = '#1e7e34';
                }
            } else {
                this.classList.add('cedula-invalid');
                this.classList.remove('cedula-valid');
                if (cedulaFeedback) {
                    cedulaFeedback.textContent = 'Formato: V-12345678';
                    cedulaFeedback.style.color = '#c5221f';
                }
            }
        });
    }

    // ===========================
    // NUEVO TRABAJADOR
    // ===========================
    const btnNuevo = document.querySelector('[data-bs-target="#modalTrabajador"]');
    if (btnNuevo) {
        btnNuevo.addEventListener('click', function () {
            modalTitulo.textContent = 'Nuevo Trabajador';
            btnGuardar.textContent = 'Guardar Trabajador';
            methodInput.value = 'POST';
            formTrabajador.action = '/admin/trabajadores';

            document.getElementById('trabajadorCedula').value = '';
            document.getElementById('trabajadorNombre').value = '';
            document.getElementById('trabajadorApellido').value = '';
            document.getElementById('trabajadorEmail').value = '';
            document.getElementById('trabajadorDepartamento').value = 'Informática';
            document.getElementById('trabajadorCargo').value = '';
            document.getElementById('trabajadorEspecialidad').value = '';
            document.getElementById('trabajadorTelefono').value = '';

            if (cedulaFeedback) cedulaFeedback.textContent = '';
            if (cedulaInput) cedulaInput.classList.remove('cedula-valid', 'cedula-invalid');

            formTrabajador.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        });
    }

    // ===========================
    // BIND DE BOTONES (reeditable cada vez que se re-renderiza)
    // ===========================
    function bindEventosBotones() {
        // EDITAR
        document.querySelectorAll('.btn-editar-trabajador').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;

                if (!id || id === '' || id === 'undefined') {
                    if (window.mostrarNotificacion) {
                        window.mostrarNotificacion('error', 'No se pudo obtener el ID del trabajador.');
                    } else {
                        alert('Error: no se pudo obtener el ID del trabajador.');
                    }
                    return;
                }

                modalTitulo.textContent = 'Editar Trabajador';
                btnGuardar.textContent = 'Actualizar Trabajador';
                methodInput.value = 'PUT';
                formTrabajador.action = '/admin/trabajadores/' + id;

                document.getElementById('trabajadorCedula').value = this.dataset.cedula || '';
                document.getElementById('trabajadorNombre').value = this.dataset.nombre || '';
                document.getElementById('trabajadorApellido').value = this.dataset.apellido || '';
                document.getElementById('trabajadorEmail').value = this.dataset.email || '';
                document.getElementById('trabajadorDepartamento').value = this.dataset.departamento || 'Informática';
                document.getElementById('trabajadorCargo').value = this.dataset.cargo || '';
                document.getElementById('trabajadorEspecialidad').value = this.dataset.especialidad || '';
                document.getElementById('trabajadorTelefono').value = this.dataset.telefono || '';

                if (cedulaFeedback) cedulaFeedback.textContent = '';
                if (cedulaInput) {
                    cedulaInput.classList.add('cedula-valid');
                    cedulaInput.classList.remove('cedula-invalid');
                }

                formTrabajador.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                const bsModal = new bootstrap.Modal(modalTrabajador);
                bsModal.show();
            });
        });

        // ELIMINAR
        document.querySelectorAll('.btn-eliminar-trabajador').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const nombre = this.dataset.nombre;
                const tieneUsuario = this.dataset.tieneUsuario === '1';

                if (!id || id === '' || id === 'undefined') {
                    if (window.mostrarNotificacion) {
                        window.mostrarNotificacion('error', 'No se pudo obtener el ID del trabajador.');
                    } else {
                        alert('Error: no se pudo obtener el ID del trabajador.');
                    }
                    return;
                }

                if (deleteTrabajadorNombre) deleteTrabajadorNombre.textContent = nombre;

                if (tieneUsuario) {
                    if (deleteWarningUsuario) deleteWarningUsuario.style.display = 'block';
                    if (formDelete) formDelete.style.display = 'none';
                } else {
                    if (deleteWarningUsuario) deleteWarningUsuario.style.display = 'none';
                    if (formDelete) {
                        formDelete.style.display = 'block';
                        formDelete.action = '/admin/trabajadores/' + id;
                    }
                }

                if (modalConfirmDelete) {
                    const bsModal = new bootstrap.Modal(modalConfirmDelete);
                    bsModal.show();
                }
            });
        });

        // VER DETALLE
        document.querySelectorAll('.btn-ver-trabajador').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;

                if (!id || id === '' || id === 'undefined') {
                    if (window.mostrarNotificacion) {
                        window.mostrarNotificacion('error', 'No se pudo obtener el ID del trabajador.');
                    }
                    return;
                }

                fetch('/admin/trabajadores/' + id + '/detalle')
                    .then(response => response.json())
                    .then(data => {
                        const t = data.trabajador;
                        const setText = (idEl, value) => {
                            const el = document.getElementById(idEl);
                            if (el) el.textContent = value || '-';
                        };

                        setText('dtCedula', t.cedula);
                        setText('dtNombre', t.nombre_completo);
                        setText('dtEmail', t.email);
                        setText('dtTelefono', t.telefono);
                        setText('dtDepartamento', t.departamento);
                        setText('dtCargo', t.cargo);
                        setText('dtEspecialidad', t.especialidad);
                        setText('dtCreado', t.created_at);

                        const infoUsuario = document.getElementById('dtInfoUsuario');
                        const btnCrearUsuario = document.getElementById('btnCrearUsuarioDesdeDetalle');
                        const sinUsuario = document.getElementById('dtSinUsuario');

                        if (t.tiene_usuario && t.usuario) {
                            if (infoUsuario) {
                                infoUsuario.innerHTML = `
                                    <div class="detail-item"><div class="detail-label">Usuario</div><div class="detail-value">${t.usuario.nombre}</div></div>
                                    <div class="detail-item"><div class="detail-label">Rol</div><div class="detail-value">${t.usuario.rol}</div></div>
                                    <div class="detail-item"><div class="detail-label">Estado</div><div class="detail-value">${t.usuario.status}</div></div>
                                    <div class="detail-item"><div class="detail-label">Último Ingreso</div><div class="detail-value">${t.usuario.ultimo_login}</div></div>
                                `;
                                infoUsuario.style.display = 'block';
                            }
                            if (sinUsuario) sinUsuario.style.display = 'none';
                            if (btnCrearUsuario) btnCrearUsuario.style.display = 'none';
                        } else {
                            if (infoUsuario) infoUsuario.style.display = 'none';
                            if (sinUsuario) sinUsuario.style.display = 'block';
                            if (btnCrearUsuario) {
                                btnCrearUsuario.style.display = 'block';
                                btnCrearUsuario.onclick = function () {
                                    window.location.href = '/admin/usuarios?search=' + encodeURIComponent(t.cedula) + '&crear=1';
                                };
                            }
                        }

                        if (modalDetail) {
                            const bsModal = new bootstrap.Modal(modalDetail);
                            bsModal.show();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (window.mostrarNotificacion) {
                            window.mostrarNotificacion('error', 'Error al cargar el detalle del trabajador.');
                        }
                    });
            });
        });
    }

    // ===========================
    // BÚSQUEDA EN TIEMPO REAL (CLIENTE)
    // ===========================
    const buscarInput = document.getElementById('buscarTrabajador');
    let timeoutBusqueda = null;

    if (buscarInput) {
        buscarInput.addEventListener('input', function () {
            const termino = this.value.trim().toLowerCase();
            clearTimeout(timeoutBusqueda);
            timeoutBusqueda = setTimeout(function () {
                if (termino === '') {
                    trabajadoresFiltrados = [...trabajadoresData];
                } else {
                    trabajadoresFiltrados = trabajadoresData.filter(t => {
                        return (t.cedula || '').toLowerCase().includes(termino) ||
                               (t.nombre || '').toLowerCase().includes(termino) ||
                               (t.apellido || '').toLowerCase().includes(termino) ||
                               (t.cargo || '').toLowerCase().includes(termino) ||
                               (t.departamento || '').toLowerCase().includes(termino) ||
                               (t.email || '').toLowerCase().includes(termino);
                    });
                }
                paginaActual = 1;
                renderizarTablaTrabajadores();
            }, 300);
        });
    }

    // ===========================
    // URL PARAMS (auto-abrir modal si viene ?crear=1)
    // ===========================
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('crear') === '1') {
        const btnNuevoUsuario = document.querySelector('[data-bs-target="#modalUsuario"]');
        if (btnNuevoUsuario) {
            setTimeout(() => btnNuevoUsuario.click(), 500);
        }
    }

    // ===========================
    // INICIALIZACIÓN
    // ===========================
    cargarDatosDesdeBlade();

    console.log('✅ Módulo de trabajadores inicializado con paginación');
});