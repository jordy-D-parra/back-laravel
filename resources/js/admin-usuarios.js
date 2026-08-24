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
    // BÚSQUEDA DE USUARIOS EN TIEMPO REAL
    // ===========================
    const buscarUsuarioInput = document.getElementById('buscarUsuario');
    let timeoutUsuarioBusqueda = null;

    if (buscarUsuarioInput) {
        buscarUsuarioInput.addEventListener('input', function() {
            const termino = this.value.trim();
            clearTimeout(timeoutUsuarioBusqueda);
            timeoutUsuarioBusqueda = setTimeout(function() {
                const url = new URL(window.location.href);
                if (termino === '') {
                    url.searchParams.delete('search');
                } else {
                    url.searchParams.set('search', termino);
                }
                window.location.href = url.toString();
            }, 400);
        });
    }

    // ===========================
    // BUSCADOR DE CÉDULA PARA USUARIOS (MODAL)
    // ===========================
    const cedulaSearch = document.getElementById('usuarioCedulaSearch');
    const resultsDiv = document.getElementById('cedulaSearchResults');
    const infoDiv = document.getElementById('infoTrabajadorEncontrado');
    const trabajadorIdInput = document.getElementById('usuarioTrabajadorId');
    const nombreInput = document.getElementById('usuarioNombre');
    const usuarioSugerido = document.getElementById('usuarioSugerido');

    let timeoutCedulaBusqueda = null;

    if (cedulaSearch) {
        cedulaSearch.addEventListener('input', function() {
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
                    warningDiv.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2" style="display:inline;margin-right:6px;vertical-align:middle;">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4M12 8h.01"/>
                        </svg>
                        Este trabajador ya tiene un usuario asignado.
                    `;
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
    // ABRIR MODAL NUEVO USUARIO
    // ===========================
    const btnNuevoUsuario = document.querySelector('[data-bs-target="#modalUsuario"]');
    if (btnNuevoUsuario) {
        btnNuevoUsuario.addEventListener('click', function() {
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
        });
    }

    // ===========================
    // EDITAR USUARIO
    // ===========================
    document.querySelectorAll('.btn-editar-usuario').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const usuario = this.dataset.usuario;
            const email = this.dataset.email || '';
            const rolId = this.dataset.rolId;
            const status = this.dataset.status;

            modalTitulo.textContent = 'Editar Usuario';
            btnGuardar.textContent = 'Actualizar Usuario';
            document.getElementById('usuarioMethod').value = 'PUT';
            document.getElementById('usuarioId').value = id;
            formUsuario.action = '/admin/usuarios/' + id;

            document.getElementById('usuarioNombre').value = usuario;
            document.getElementById('usuarioEmail').value = email;
            document.getElementById('usuarioRolId').value = rolId;
            document.getElementById('usuarioStatus').value = status;

            const divTrabajadorSelect = document.getElementById('divTrabajadorSelect');
            if (divTrabajadorSelect) divTrabajadorSelect.style.display = 'none';
            
            const divTrabajadorInfo = document.getElementById('divTrabajadorInfo');
            if (divTrabajadorInfo) divTrabajadorInfo.style.display = 'block';

            formUsuario.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            const bsModal = new bootstrap.Modal(modalUsuario);
            bsModal.show();
        });
    });

    // ===========================
    // VER DETALLE USUARIO
    // ===========================
    document.querySelectorAll('.btn-ver-usuario').forEach(btn => {
        btn.addEventListener('click', function() {
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

    // ===========================
    // RESETEAR CONTRASEÑA
    // ===========================
    document.querySelectorAll('.btn-reset-password').forEach(btn => {
        btn.addEventListener('click', function() {
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

    // ===========================
    // FUNCIÓN: MOSTRAR MODAL CONTRASEÑA
    // ===========================
    function mostrarModalContraseña(usuario, password) {
        let modalExistente = document.getElementById('modalPasswordDisplay');
        if (modalExistente) {
            modalExistente.remove();
        }

        const modalHTML = `
            <div class="modal fade" id="modalPasswordDisplay" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content shadow-lg">
                        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <h5 class="modal-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:8px;">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                Contraseña Reseteada
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body px-4 text-center">
                            <p class="small text-muted mb-2">Usuario: <strong>${usuario}</strong></p>
                            <div class="password-display" style="
                                background: #f8f9fc;
                                border: 2px dashed #1e3c72;
                                border-radius: 10px;
                                padding: 16px 20px;
                                font-family: 'Courier New', monospace;
                                font-size: 1.4rem;
                                font-weight: 700;
                                color: #1e3c72;
                                letter-spacing: 2px;
                                word-break: break-all;
                                user-select: all;
                                cursor: pointer;
                            " onclick="copiarContraseña('${password}')">
                                ${password}
                            </div>
                            <p class="small text-warning mt-3 mb-0">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2" style="display:inline; margin-right:4px; vertical-align:middle;">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 16v-4M12 8h.01"/>
                                </svg>
                                Copie esta contraseña. No se volverá a mostrar.
                            </p>
                            <p class="small text-muted">El usuario deberá cambiarla en su primer inicio de sesión.</p>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 justify-content-center">
                            <button type="button" class="btn btn-primary-dark w-100" data-bs-dismiss="modal" style="color:#fff;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:6px;">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
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

        modalElement.addEventListener('hidden.bs.modal', function() {
            modalElement.remove();
        });
    }

    // ===========================
    // FUNCIÓN: COPIAR CONTRASEÑA
    // ===========================
    window.copiarContraseña = function(password) {
        const tempInput = document.createElement('input');
        tempInput.value = password;
        document.body.appendChild(tempInput);
        tempInput.select();
        try {
            document.execCommand('copy');
            mostrarNotificacion('success', 'Contraseña copiada al portapapeles');
        } catch (err) {
            const display = document.querySelector('.password-display');
            if (display) {
                const range = document.createRange();
                range.selectNode(display);
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
                mostrarNotificacion('info', 'Seleccione la contraseña y copie manualmente');
            }
        }
        tempInput.remove();
    };

    // ===========================
    // ELIMINAR USUARIO
    // ===========================
    document.querySelectorAll('.btn-eliminar-usuario').forEach(btn => {
        btn.addEventListener('click', function() {
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

    // ===========================
    // TOGGLE STATUS
    // ===========================
    document.querySelectorAll('.btn-toggle-status').forEach(btn => {
        btn.addEventListener('click', function() {
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
                        setTimeout(() => location.reload(), 1000);
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

    // ===========================
    // VALIDACIÓN Y ENVÍO DEL FORMULARIO
    // ===========================
    if (formUsuario) {
        formUsuario.addEventListener('submit', function(e) {
            e.preventDefault();

            const method = document.getElementById('usuarioMethod').value;
            const id = document.getElementById('usuarioId').value;
            const trabajadorId = document.getElementById('usuarioTrabajadorId').value;
            const nombre = document.getElementById('usuarioNombre').value.trim();
            const email = document.getElementById('usuarioEmail').value.trim();
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

            if (!email || !email.includes('@')) {
                document.getElementById('usuarioEmail').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('usuarioEmail').classList.remove('is-invalid');
            }

            if (!rolId) {
                document.getElementById('usuarioRolId').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('usuarioRolId').classList.remove('is-invalid');
            }

            if (!isValid) {
                return;
            }

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
                    mostrarNotificacion('success', data.message);
                    setTimeout(() => location.reload(), 1000);
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
    // AUTO-CERRAR ALERTAS
    // ===========================
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.click();
            }
        }, 5000);
    });

    console.log('✅ Módulo de usuarios inicializado correctamente');
});