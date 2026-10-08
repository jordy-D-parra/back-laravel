import 'bootstrap';

document.addEventListener('DOMContentLoaded', function () {

    // ===========================
    // REFERENCIAS
    // ===========================
    const modalUsuario = document.getElementById('modalUsuario');
    const formUsuario = document.getElementById('formUsuario');
    const modalTitulo = document.getElementById('modalUsuarioTitulo');
    const btnGuardar = document.getElementById('btnGuardarUsuario');

    // ===========================
    // ESTADO DE PAGINACIÓN
    // ===========================
    const ITEMS_POR_PAGINA = 8;
    let usuariosData = [];
    let usuariosFiltrados = [];
    let paginaActual = 1;

    // ===========================
    // FUNCIÓN: MOSTRAR NOTIFICACIÓN
    // ===========================
    function mostrarNotificacion(tipo, mensaje) {
        const colores = {
            success: '#1e7e34',
            error: '#c5221f',
            warning: '#f6c23e',
            info: '#1e3c72'
        };

        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            background: ${colores[tipo] || colores.success}; color: white;
            padding: 14px 20px; border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            font-weight: 500; font-size: 0.9rem;
            animation: slideInRight 0.3s ease-out; max-width: 400px;
            cursor: pointer;
        `;
        toast.textContent = mensaje;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // ===========================
    // FUNCIÓN: MOSTRAR HASH DE CONTRASEÑA
    // ===========================
    function mostrarHashContraseña(hash) {
        const container = document.getElementById('passwordHashContainer');
        const valueSpan = document.getElementById('passwordHashValue');
        if (container && valueSpan) {
            valueSpan.textContent = hash;
            container.style.display = 'block';
        }
    }

    function ocultarHashContraseña() {
        const container = document.getElementById('passwordHashContainer');
        if (container) {
            container.style.display = 'none';
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
    // HELPERS DE PAGINACIÓN
    // ===========================
    function renderPaginacion(totalPaginas, pageActual, totalRegistros) {
        if (totalPaginas <= 1) return '';

        let html = `<div class="pagination-bar">
            <div class="pagination-info">Mostrando ${((pageActual - 1) * ITEMS_POR_PAGINA) + 1} a ${Math.min(pageActual * ITEMS_POR_PAGINA, totalRegistros)} de ${totalRegistros} registros</div>
            <div class="pagination-btns">`;

        html += `<button class="pagination-btn ${pageActual === 1 ? 'disabled' : ''}"
                    onclick="window.cambiarPaginaUsuarios(${pageActual - 1})" ${pageActual === 1 ? 'disabled' : ''}>«</button>`;

        let inicio = Math.max(1, pageActual - 2);
        let fin = Math.min(totalPaginas, pageActual + 2);

        if (inicio > 1) {
            html += `<button class="pagination-btn" onclick="window.cambiarPaginaUsuarios(1)">1</button>`;
            if (inicio > 2) html += `<span class="pagination-ellipsis">...</span>`;
        }

        for (let i = inicio; i <= fin; i++) {
            html += `<button class="pagination-btn ${i === pageActual ? 'active' : ''}"
                        onclick="window.cambiarPaginaUsuarios(${i})">${i}</button>`;
        }

        if (fin < totalPaginas) {
            if (fin < totalPaginas - 1) html += `<span class="pagination-ellipsis">...</span>`;
            html += `<button class="pagination-btn" onclick="window.cambiarPaginaUsuarios(${totalPaginas})">${totalPaginas}</button>`;
        }

        html += `<button class="pagination-btn ${pageActual === totalPaginas ? 'disabled' : ''}"
                    onclick="window.cambiarPaginaUsuarios(${pageActual + 1})" ${pageActual === totalPaginas ? 'disabled' : ''}>»</button>`;

        html += `</div></div>`;
        return html;
    }

    window.cambiarPaginaUsuarios = function(nuevaPagina) {
        const totalPaginas = Math.ceil(usuariosFiltrados.length / ITEMS_POR_PAGINA);
        if (nuevaPagina < 1 || nuevaPagina > totalPaginas) return;

        paginaActual = nuevaPagina;
        renderizarTablaUsuarios();
        document.querySelector('.table-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // ===========================
    // RENDERIZAR TABLA
    // ===========================
    function renderizarTablaUsuarios() {
        const tbody = document.getElementById('tablaUsuarios');
        if (!tbody) return;

        const total = usuariosFiltrados.length;
        const totalPaginas = Math.ceil(total / ITEMS_POR_PAGINA);

        if (paginaActual > totalPaginas && totalPaginas > 0) {
            paginaActual = totalPaginas;
        }

        const inicio = (paginaActual - 1) * ITEMS_POR_PAGINA;
        const paginaData = usuariosFiltrados.slice(inicio, inicio + ITEMS_POR_PAGINA);

        if (total === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" class="mb-2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        </svg>
                        <p>No se encontraron usuarios</p>
                    </td>
                </tr>`;
            const pag = document.getElementById('paginacionUsuarios');
            if (pag) pag.innerHTML = '';
            return;
        }

        let html = '';
        for (const u of paginaData) {
            const trabajador = u.trabajador || {};
            const rolNombre = u.rol ? u.rol.nombre : 'sin_rol';
            const rolClass = 'badge-role-' + rolNombre;

            html += `
                <tr>
                    <td><span class="fw-medium" style="color: var(--primary-dark);">${escapeHtml(u.usuario)}</span></td>
                    <td>${escapeHtml(trabajador.nombre || '')} ${escapeHtml(trabajador.apellido || '')}</td>
                    <td><small>${escapeHtml(trabajador.cedula || '-')}</small></td>
                    <td>
                        <span class="badge-role ${rolClass}">${escapeHtml(rolNombre.charAt(0).toUpperCase() + rolNombre.slice(1))}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-toggle-status ${u.status === 'activo' ? 'badge-status-activo' : 'badge-status-inactivo'} border-0"
                                data-id="${u.id}" style="font-size: 0.75rem;">
                            ${u.status === 'activo' ? 'Activo' : 'Inactivo'}
                        </button>
                    </td>
                    <td><small>${u.ultimo_login ? new Date(u.ultimo_login).toLocaleString('es-VE') : 'Nunca'}</small></td>
                    <td>
                        ${u.must_change_password
                            ? '<span class="badge-status-inactivo" style="font-size: 0.7rem; padding: 3px 8px; border-radius: 12px;">Pendiente</span>'
                            : '<span class="badge-status-activo" style="font-size: 0.7rem; padding: 3px 8px; border-radius: 12px;">OK</span>'}
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-action btn-outline-primary-dark btn-ver-usuario"
                                    data-id="${u.id}" title="Ver detalle">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>

                            <button class="btn btn-sm btn-action btn-outline-primary-dark btn-editar-usuario"
                                    data-id="${u.id}"
                                    data-usuario="${escapeHtml(u.usuario)}"
                                    data-rol-id="${u.rol_id || ''}"
                                    data-status="${u.status}" title="Editar">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>

                            <button class="btn btn-sm btn-action btn-outline-primary-dark btn-reset-password"
                                    data-id="${u.id}"
                                    data-usuario="${escapeHtml(u.usuario)}" title="Resetear contraseña">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </button>

                            <button class="btn btn-sm btn-action btn-outline-danger btn-eliminar-usuario"
                                    data-id="${u.id}"
                                    data-usuario="${escapeHtml(u.usuario)}" title="Eliminar">
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

        // Rebindear eventos
        bindEventosBotones();

        const paginacionContainer = document.getElementById('paginacionUsuarios');
        if (paginacionContainer) {
            paginacionContainer.innerHTML = renderPaginacion(totalPaginas, paginaActual, total);
        }
    }

    // ===========================
    // BINDEAR EVENTOS A BOTONES DINÁMICOS
    // ===========================
    function bindEventosBotones() {
        // EDITAR
        document.querySelectorAll('.btn-editar-usuario').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const usuario = this.dataset.usuario;
                const rolId = this.dataset.rolId;
                const status = this.dataset.status;

                modalTitulo.textContent = 'Editar Usuario';
                btnGuardar.textContent = 'Actualizar Usuario';
                document.getElementById('usuarioMethod').value = 'PUT';
                document.getElementById('usuarioId').value = id;
                formUsuario.action = '/admin/usuarios/' + id;
                document.getElementById('usuarioNombre').value = usuario;
                document.getElementById('usuarioRolId').value = rolId;
                document.getElementById('usuarioStatus').value = status;

                const divTrabajadorSelect = document.getElementById('divTrabajadorSelect');
                if (divTrabajadorSelect) divTrabajadorSelect.style.display = 'none';

                const divTrabajadorInfo = document.getElementById('divTrabajadorInfo');
                if (divTrabajadorInfo) divTrabajadorInfo.style.display = 'block';

                formUsuario.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                ocultarHashContraseña();

                const bsModal = new bootstrap.Modal(modalUsuario);
                bsModal.show();
            });
        });

        // VER DETALLE
        document.querySelectorAll('.btn-ver-usuario').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                fetch('/admin/usuarios/' + id + '/detalle')
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('detailUsuario').textContent = data.usuario || '-';
                        document.getElementById('detailRol').textContent = data.rol || '-';
                        document.getElementById('detailStatus').textContent = data.status || '-';
                        document.getElementById('detailUltimoLogin').textContent = data.ultimo_login || 'Nunca';
                        document.getElementById('detailCreado').textContent = data.created_at || '-';

                        const t = data.trabajador || {};
                        document.getElementById('detailCedula').textContent = t.cedula || '-';
                        document.getElementById('detailNombre').textContent = t.nombre_completo || '-';
                        document.getElementById('detailDepartamento').textContent = t.departamento || '-';
                        document.getElementById('detailCargo').textContent = t.cargo || '-';
                        document.getElementById('detailEspecialidad').textContent = t.especialidad || 'No asignada';
                        document.getElementById('detailTelefono').textContent = t.telefono || 'No registrado';
                        document.getElementById('detailEmail').textContent = t.email || 'No registrado';

                        const modalDetail = document.getElementById('modalDetail');
                        if (modalDetail) {
                            const bsModal = new bootstrap.Modal(modalDetail);
                            bsModal.show();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        mostrarNotificacion('error', 'Error al cargar los detalles');
                    });
            });
        });

        // RESETEAR CONTRASEÑA
        document.querySelectorAll('.btn-reset-password').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const usuario = this.dataset.usuario || 'Usuario';

                if (confirm(`¿Estás seguro de resetear la contraseña de "${usuario}"?`)) {
                    fetch('/admin/usuarios/' + id + '/reset-password', {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const nuevaPassword = data.new_password || 'N/A';
                            const nombreUsuario = data.usuario || usuario;
                            mostrarModalContraseña(nombreUsuario, nuevaPassword);
                            mostrarNotificacion('success', 'Contraseña reseteada exitosamente');
                        } else {
                            mostrarNotificacion('error', data.message || 'Error al resetear contraseña');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        mostrarNotificacion('error', 'Error de conexión');
                    });
                }
            });
        });

        // ELIMINAR
        document.querySelectorAll('.btn-eliminar-usuario').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const usuario = this.dataset.usuario;

                document.getElementById('deleteUserName').textContent = usuario;
                document.getElementById('formDelete').action = '/admin/usuarios/' + id;

                const modalConfirmDelete = document.getElementById('modalConfirmDelete');
                if (modalConfirmDelete) {
                    const bsModal = new bootstrap.Modal(modalConfirmDelete);
                    bsModal.show();
                }
            });
        });

        // TOGGLE STATUS
        document.querySelectorAll('.btn-toggle-status').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                if (confirm('¿Cambiar el estado de este usuario?')) {
                    fetch('/admin/usuarios/' + id + '/toggle-status', {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            mostrarNotificacion('success', data.message);
                            // Actualizar en el array local
                            const user = usuariosData.find(u => u.id == id);
                            if (user) {
                                user.status = user.status === 'activo' ? 'inactivo' : 'activo';
                            }
                            const filtro = usuariosFiltrados.find(u => u.id == id);
                            if (filtro) {
                                filtro.status = filtro.status === 'activo' ? 'inactivo' : 'activo';
                            }
                            renderizarTablaUsuarios();
                        } else {
                            mostrarNotificacion('error', data.message || 'Error al cambiar estado');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        mostrarNotificacion('error', 'Error de conexión');
                    });
                }
            });
        });
    }

    // ============================================================
    // MODAL CONTRASEÑA
    // ============================================================
    function mostrarModalContraseña(usuario, password) {
        const passwordLimpia = String(password).trim();
        const usuarioLimpio = String(usuario).trim();

        let modalExistente = document.getElementById('modalPasswordDisplay');
        if (modalExistente) modalExistente.remove();

        const usuarioEscapado = usuarioLimpio.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const passwordEscapado = passwordLimpia.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

        const modalHTML = `
            <div class="modal fade" id="modalPasswordDisplay" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg">
                        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <h5 class="modal-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:8px; vertical-align:middle;">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                Contraseña Temporal
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body px-4 py-4">
                            <p class="small text-muted mb-1">Usuario:</p>
                            <p class="fw-bold mb-3" style="color: #1e3c72; font-size: 1rem;">${usuarioEscapado}</p>

                            <p class="small text-muted mb-2">Contraseña temporal:</p>
                            <div id="passwordDisplayBox" style="
                                background: #f8f9fc;
                                border: 2px dashed #1e3c72;
                                border-radius: 10px;
                                padding: 18px 20px;
                                font-family: 'Courier New', monospace;
                                font-size: 1.5rem;
                                font-weight: 700;
                                color: #1e3c72;
                                letter-spacing: 2px;
                                word-break: break-all;
                                text-align: center;
                                user-select: all;
                                cursor: pointer;
                            " title="Haz clic para copiar">
                                ${passwordEscapado}
                            </div>

                            <div class="mt-3">
                                <button type="button" class="btn btn-primary w-100" id="btnCopiarPassword" style="background: #1e3c72; border: none; border-radius: 10px; padding: 10px;">
                                    Copiar contraseña
                                </button>
                            </div>

                            <div class="alert alert-warning mt-3 mb-0" style="font-size: 0.8rem; border-radius: 10px;">
                                <strong>⚠️ Importante:</strong> Copie esta contraseña ahora. No se volverá a mostrar.
                            </div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 justify-content-center">
                            <button type="button" class="btn btn-primary-dark w-100" data-bs-dismiss="modal" style="color: #fff;">
                                Entendido, cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        const modalElement = document.getElementById('modalPasswordDisplay');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();

        const btnCopiar = document.getElementById('btnCopiarPassword');
        const displayBox = document.getElementById('passwordDisplayBox');

        function copiarAlPortapapeles() {
            const textoACopiar = passwordLimpia;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textoACopiar).then(mostrarFeedbackCopiado).catch(() => copiarFallback(textoACopiar));
            } else {
                copiarFallback(textoACopiar);
            }
        }

        function copiarFallback(texto) {
            const tempInput = document.createElement('textarea');
            tempInput.value = texto;
            tempInput.style.position = 'fixed';
            tempInput.style.top = '-9999px';
            document.body.appendChild(tempInput);
            tempInput.focus();
            tempInput.select();
            try {
                document.execCommand('copy');
                mostrarFeedbackCopiado();
            } catch (err) {
                alert('No se pudo copiar. Selecciona el texto manualmente.');
            }
            document.body.removeChild(tempInput);
        }

        function mostrarFeedbackCopiado() {
            btnCopiar.innerHTML = '✅ ¡Copiada!';
            btnCopiar.style.background = '#22c55e';
            setTimeout(() => {
                btnCopiar.innerHTML = 'Copiar contraseña';
                btnCopiar.style.background = '#1e3c72';
            }, 2000);
        }

        btnCopiar.addEventListener('click', copiarAlPortapapeles);
        displayBox.addEventListener('click', copiarAlPortapapeles);

        modalElement.addEventListener('hidden.bs.modal', function () {
            modalElement.remove();
        });
    }

    // ===========================
    // CARGAR DATOS DESDE BLADE
    // ===========================
    function cargarDatosDesdeBlade() {
        if (window.usuariosIniciales && Array.isArray(window.usuariosIniciales)) {
            usuariosData = window.usuariosIniciales;
            usuariosFiltrados = [...usuariosData];
            paginaActual = 1;
            renderizarTablaUsuarios();
        }
    }

    // ===========================
    // BÚSQUEDA DE USUARIOS (CLIENTE)
    // ===========================
    const buscarUsuarioInput = document.getElementById('buscarUsuario');
    let timeoutUsuarioBusqueda = null;

    if (buscarUsuarioInput) {
        buscarUsuarioInput.addEventListener('input', function () {
            const termino = this.value.trim().toLowerCase();
            clearTimeout(timeoutUsuarioBusqueda);
            timeoutUsuarioBusqueda = setTimeout(function () {
                if (termino === '') {
                    usuariosFiltrados = [...usuariosData];
                } else {
                    usuariosFiltrados = usuariosData.filter(u => {
                        const t = u.trabajador || {};
                        return (u.usuario || '').toLowerCase().includes(termino) ||
                               (t.nombre || '').toLowerCase().includes(termino) ||
                               (t.apellido || '').toLowerCase().includes(termino) ||
                               (t.cedula || '').toLowerCase().includes(termino);
                    });
                }
                paginaActual = 1;
                renderizarTablaUsuarios();
            }, 300);
        });
    }

    // ===========================
    // BUSCADOR DE CÉDULA (MODAL)
    // ===========================
    const cedulaSearch = document.getElementById('usuarioCedulaSearch');
    const resultsDiv = document.getElementById('cedulaSearchResults');
    const infoDiv = document.getElementById('infoTrabajadorEncontrado');
    const trabajadorIdInput = document.getElementById('usuarioTrabajadorId');
    const nombreInput = document.getElementById('usuarioNombre');
    const usuarioSugerido = document.getElementById('usuarioSugerido');

    let timeoutCedulaBusqueda = null;

    if (cedulaSearch) {
        cedulaSearch.addEventListener('input', function () {
            const cedula = this.value.trim();
            clearTimeout(timeoutCedulaBusqueda);

            if (!cedula || cedula.length < 3) {
                if (resultsDiv) resultsDiv.style.display = 'none';
                if (infoDiv) infoDiv.style.display = 'none';
                if (trabajadorIdInput) trabajadorIdInput.value = '';
                const warning = document.getElementById('trabajadorTieneUsuarioWarning');
                if (warning) warning.remove();
                return;
            }

            if (resultsDiv) {
                resultsDiv.style.display = 'block';
                resultsDiv.innerHTML = '<div class="text-muted small">Buscando trabajador...</div>';
            }

            timeoutCedulaBusqueda = setTimeout(() => {
                buscarTrabajadorPorCedula(cedula);
            }, 400);
        });
    }

    function buscarTrabajadorPorCedula(cedula) {
        fetch(`/admin/trabajadores/buscar-cedula/${encodeURIComponent(cedula)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const warningExistente = document.getElementById('trabajadorTieneUsuarioWarning');
            if (warningExistente) warningExistente.remove();

            if (data.encontrado) {
                const trabajador = data.trabajador;

                if (infoDiv) {
                    const nombreEl = document.getElementById('trabajadorEncontradoNombre');
                    const cargoEl = document.getElementById('trabajadorEncontradoCargo');
                    const deptoEl = document.getElementById('trabajadorEncontradoDepartamento');

                    if (nombreEl) nombreEl.textContent = `${trabajador.nombre} ${trabajador.apellido}`;
                    if (cargoEl) cargoEl.textContent = `Cargo: ${trabajador.cargo || 'No especificado'}`;
                    if (deptoEl) deptoEl.textContent = `Departamento: ${trabajador.departamento || 'No especificado'}`;
                    infoDiv.style.display = 'block';
                }

                if (resultsDiv) resultsDiv.style.display = 'none';
                if (trabajadorIdInput) trabajadorIdInput.value = trabajador.id;

                if (usuarioSugerido && nombreInput) {
                    const sugerido = `${trabajador.nombre.toLowerCase()}.${trabajador.apellido.toLowerCase()}`;
                    usuarioSugerido.textContent = sugerido;
                    if (!nombreInput.value) {
                        nombreInput.value = sugerido;
                    }
                }

                if (data.tiene_usuario) {
                    const warningDiv = document.createElement('div');
                    warningDiv.id = 'trabajadorTieneUsuarioWarning';
                    warningDiv.className = 'alert alert-warning mt-2 py-2';
                    warningDiv.style.cssText = 'font-size:0.85rem; border-radius:8px;';
                    warningDiv.innerHTML = `⚠️ Este trabajador ya tiene un usuario asignado.`;
                    if (infoDiv && infoDiv.parentNode) {
                        infoDiv.parentNode.insertBefore(warningDiv, infoDiv.nextSibling);
                    }
                }
            } else {
                if (infoDiv) infoDiv.style.display = 'none';
                if (trabajadorIdInput) trabajadorIdInput.value = '';
                if (resultsDiv) {
                    resultsDiv.innerHTML = '<div class="text-danger small">No se encontró ningún trabajador con esta cédula</div>';
                    resultsDiv.style.display = 'block';
                }
            }
        })
        .catch(error => {
            console.error('Error al buscar trabajador:', error);
            if (resultsDiv) {
                resultsDiv.innerHTML = '<div class="text-danger small">Error al buscar. Intente de nuevo.</div>';
                resultsDiv.style.display = 'block';
            }
        });
    }

    // ===========================
    // NUEVO USUARIO
    // ===========================
    const btnNuevoUsuario = document.querySelector('[data-bs-target="#modalUsuario"]');
    if (btnNuevoUsuario) {
        btnNuevoUsuario.addEventListener('click', function () {
            formUsuario.reset();
            document.getElementById('usuarioMethod').value = 'POST';
            formUsuario.action = '/admin/usuarios';
            document.getElementById('usuarioId').value = '';
            modalTitulo.textContent = 'Nuevo Usuario';
            btnGuardar.textContent = 'Guardar Usuario';

            if (cedulaSearch) cedulaSearch.value = '';
            if (resultsDiv) resultsDiv.style.display = 'none';
            if (infoDiv) infoDiv.style.display = 'none';
            if (trabajadorIdInput) trabajadorIdInput.value = '';
            const warning = document.getElementById('trabajadorTieneUsuarioWarning');
            if (warning) warning.remove();

            formUsuario.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            if (usuarioSugerido) usuarioSugerido.textContent = '';

            const divTrabajadorSelect = document.getElementById('divTrabajadorSelect');
            if (divTrabajadorSelect) {
                divTrabajadorSelect.style.display = 'block';
                const cedulaSearchGroup = document.querySelector('#divTrabajadorSelect .input-group');
                if (cedulaSearchGroup) cedulaSearchGroup.style.display = 'flex';
            }

            const divTrabajadorInfo = document.getElementById('divTrabajadorInfo');
            if (divTrabajadorInfo) divTrabajadorInfo.style.display = 'none';

            ocultarHashContraseña();
        });
    }

    // ===========================
    // ENVÍO FORMULARIO
    // ===========================
    if (formUsuario) {
        formUsuario.addEventListener('submit', function (e) {
            e.preventDefault();

            const method = document.getElementById('usuarioMethod').value;
            const id = document.getElementById('usuarioId').value;
            const trabajadorId = document.getElementById('usuarioTrabajadorId')?.value;
            const nombre = document.getElementById('usuarioNombre').value.trim();
            const rolId = document.getElementById('usuarioRolId').value;

            let isValid = true;

            if (method === 'POST' && !trabajadorId) {
                mostrarNotificacion('error', 'Debe buscar y seleccionar un trabajador');
                isValid = false;
            }

            if (!nombre) {
                document.getElementById('usuarioNombre').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('usuarioNombre').classList.remove('is-invalid');
            }

            if (!rolId) {
                document.getElementById('usuarioRolId').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('usuarioRolId').classList.remove('is-invalid');
            }

            if (!isValid) return;

            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';
            btnGuardar.disabled = true;

            const formData = new FormData(this);
            const url = method === 'PUT' ? `/admin/usuarios/${id}` : '/admin/usuarios';

            if (method === 'PUT') {
                formData.append('_method', 'PUT');
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(modalUsuario);
                    if (modal) modal.hide();

                    mostrarNotificacion('success', data.message || 'Usuario guardado exitosamente');

                    if (data.new_password && data.new_usuario) {
                        setTimeout(() => {
                            mostrarModalContraseña(data.new_usuario, data.new_password);
                        }, 500);
                    }

                    setTimeout(() => location.reload(), 3000);
                } else {
                    mostrarNotificacion('error', data.message || 'Error al guardar usuario');
                    if (data.errors) {
                        for (const [key, errors] of Object.entries(data.errors)) {
                            for (const error of errors) {
                                mostrarNotificacion('error', `${key}: ${error}`);
                            }
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('error', 'Error de conexión al servidor');
            })
            .finally(() => {
                btnGuardar.innerHTML = 'Guardar Usuario';
                btnGuardar.disabled = false;
            });
        });
    }

    // ===========================
    // URL PARAMS (auto-abrir modal)
    // ===========================
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('crear') === '1') {
        const btnNuevoUsuario2 = document.querySelector('[data-bs-target="#modalUsuario"]');
        if (btnNuevoUsuario2) {
            setTimeout(() => {
                btnNuevoUsuario2.click();
                const cedulaParam = urlParams.get('search');
                if (cedulaParam && cedulaSearch) {
                    cedulaSearch.value = cedulaParam;
                    cedulaSearch.dispatchEvent(new Event('input'));
                }
            }, 400);
        }
    }

    // ===========================
    // AUTO-CERRAR ALERTAS
    // ===========================
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });

    // ===========================
    // INICIALIZACIÓN
    // ===========================
    cargarDatosDesdeBlade();
    bindEventosBotones();

    console.log('✅ Módulo de usuarios inicializado con paginación');
});