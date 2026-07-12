import 'bootstrap';

document.addEventListener('DOMContentLoaded', function () {

    // ===========================
    // Referencias a elementos
    // ===========================
    const modalUsuario = document.getElementById('modalUsuario');
    const formUsuario = document.getElementById('formUsuario');
    const modalTitulo = document.getElementById('modalUsuarioTitulo');
    const methodInput = document.getElementById('usuarioMethod');
    const btnGuardar = document.getElementById('btnGuardarUsuario');
    const divTrabajadorSelect = document.getElementById('divTrabajadorSelect');
    const divTrabajadorInfo = document.getElementById('divTrabajadorInfo');
    const alertaPassword = document.getElementById('alertaPassword');

    const modalConfirmDelete = document.getElementById('modalConfirmDelete');
    const formDelete = document.getElementById('formDelete');
    const deleteUserName = document.getElementById('deleteUserName');

    const modalDetail = document.getElementById('modalDetail');

    // Elementos de busqueda por cedula
    const cedulaSearch = document.getElementById('usuarioCedulaSearch');
    const cedulaResults = document.getElementById('cedulaSearchResults');
    const trabajadorIdInput = document.getElementById('usuarioTrabajadorId');
    const usuarioNombreInput = document.getElementById('usuarioNombre');

    let trabajadorSeleccionado = null;
    let timeoutBusqueda = null;

    // ===========================
    // Nuevo Usuario
    // ===========================
    const btnNuevo = document.querySelector('[data-bs-target="#modalUsuario"]');
    if (btnNuevo) {
        btnNuevo.addEventListener('click', function () {
            modalTitulo.textContent = 'Nuevo Usuario';
            btnGuardar.textContent = 'Crear Usuario';
            methodInput.value = 'POST';
            formUsuario.action = '/admin/usuarios';

            divTrabajadorSelect.style.display = 'block';
            divTrabajadorInfo.style.display = 'none';
            alertaPassword.style.display = 'block';
            trabajadorSeleccionado = null;

            if (usuarioNombreInput) usuarioNombreInput.value = '';
            if (trabajadorIdInput) trabajadorIdInput.value = '';
            if (cedulaSearch) cedulaSearch.value = '';
            if (cedulaResults) {
                cedulaResults.innerHTML = '';
                cedulaResults.style.display = 'none';
            }

            const usuarioSugerido = document.getElementById('usuarioSugerido');
            if (usuarioSugerido) usuarioSugerido.textContent = '';

            const infoTrabajador = document.getElementById('infoTrabajadorEncontrado');
            if (infoTrabajador) infoTrabajador.style.display = 'none';

            const rolSelect = document.getElementById('usuarioRolId');
            if (rolSelect) rolSelect.value = '';

            const statusSelect = document.getElementById('usuarioStatus');
            if (statusSelect) statusSelect.value = 'activo';

            formUsuario.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        });
    }

    // ===========================
    // Editar Usuario
    // ===========================
    document.querySelectorAll('.btn-editar-usuario').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;

            modalTitulo.textContent = 'Editar Usuario';
            btnGuardar.textContent = 'Actualizar Usuario';
            methodInput.value = 'PUT';
            formUsuario.action = '/admin/usuarios/' + id;

            divTrabajadorSelect.style.display = 'none';
            divTrabajadorInfo.style.display = 'block';
            alertaPassword.style.display = 'none';

            if (usuarioNombreInput) usuarioNombreInput.value = this.dataset.usuario || '';

            const rolSelect = document.getElementById('usuarioRolId');
            if (rolSelect) rolSelect.value = this.dataset.rolId || '';

            const statusSelect = document.getElementById('usuarioStatus');
            if (statusSelect) statusSelect.value = this.dataset.status || 'activo';

            formUsuario.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            const bsModal = new bootstrap.Modal(modalUsuario);
            bsModal.show();
        });
    });

    // ===========================
    // Busqueda de trabajador por cedula
    // ===========================
    if (cedulaSearch) {
        cedulaSearch.addEventListener('input', function () {
            const cedula = this.value.trim();

            if (timeoutBusqueda) clearTimeout(timeoutBusqueda);

            if (cedula.length < 2) {
                if (cedulaResults) {
                    cedulaResults.innerHTML = '';
                    cedulaResults.style.display = 'none';
                }
                const infoTrabajador = document.getElementById('infoTrabajadorEncontrado');
                if (infoTrabajador) infoTrabajador.style.display = 'none';
                if (trabajadorIdInput) trabajadorIdInput.value = '';
                trabajadorSeleccionado = null;
                if (usuarioNombreInput) usuarioNombreInput.value = '';
                const usuarioSugerido = document.getElementById('usuarioSugerido');
                if (usuarioSugerido) usuarioSugerido.textContent = '';
                return;
            }

            timeoutBusqueda = setTimeout(() => buscarTrabajador(cedula), 400);
        });
    }

    function buscarTrabajador(cedula) {
        fetch('/admin/trabajadores/buscar-cedula/' + encodeURIComponent(cedula))
            .then(response => response.json())
            .then(data => {
                const infoTrabajador = document.getElementById('infoTrabajadorEncontrado');
                const usuarioSugerido = document.getElementById('usuarioSugerido');

                if (!data.encontrado) {
                    if (cedulaResults) {
                        cedulaResults.innerHTML = `<div class="p-2 text-muted small">No se encontro trabajador con esta cedula. <a href="/admin/trabajadores" target="_blank">Registrarlo ahora</a></div>`;
                        cedulaResults.style.display = 'block';
                    }
                    if (infoTrabajador) infoTrabajador.style.display = 'none';
                    if (trabajadorIdInput) trabajadorIdInput.value = '';
                    trabajadorSeleccionado = null;
                    return;
                }

                if (data.tiene_usuario) {
                    if (cedulaResults) {
                        cedulaResults.innerHTML = `<div class="p-2 text-warning small">Este trabajador ya tiene un usuario asignado.</div>`;
                        cedulaResults.style.display = 'block';
                    }
                    if (infoTrabajador) infoTrabajador.style.display = 'none';
                    if (trabajadorIdInput) trabajadorIdInput.value = '';
                    trabajadorSeleccionado = null;
                    return;
                }

                const t = data.trabajador;
                trabajadorSeleccionado = t;
                if (trabajadorIdInput) trabajadorIdInput.value = t.id;

                const sugerido = (t.nombre.charAt(0).toLowerCase() + t.apellido.toLowerCase())
                    .replace(/\s+/g, '')
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                if (usuarioNombreInput) usuarioNombreInput.value = sugerido;
                if (usuarioSugerido) usuarioSugerido.textContent = sugerido;

                if (infoTrabajador) infoTrabajador.style.display = 'block';
                const nombreEl = document.getElementById('trabajadorEncontradoNombre');
                const cargoEl = document.getElementById('trabajadorEncontradoCargo');
                const deptoEl = document.getElementById('trabajadorEncontradoDepartamento');
                if (nombreEl) nombreEl.textContent = t.nombre + ' ' + t.apellido;
                if (cargoEl) cargoEl.textContent = t.cargo;
                if (deptoEl) deptoEl.textContent = t.departamento;

                if (cedulaResults) cedulaResults.style.display = 'none';
            })
            .catch(error => console.error('Error al buscar trabajador:', error));
    }

    // ===========================
    // Toggle Status
    // ===========================
    document.querySelectorAll('.btn-toggle-status').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const formToggle = document.getElementById('formToggleStatus');
            if (formToggle) {
                formToggle.action = '/admin/usuarios/' + id + '/toggle-status';
                formToggle.submit();
            }
        });
    });

    // ===========================
    // Reset Password
    // ===========================
    document.querySelectorAll('.btn-reset-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const formReset = document.getElementById('formResetPassword');
            if (formReset) {
                formReset.action = '/admin/usuarios/' + id + '/reset-password';
                formReset.submit();
            }
        });
    });

    // ===========================
    // Eliminar Usuario
    // ===========================
    document.querySelectorAll('.btn-eliminar-usuario').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const nombre = this.dataset.usuario;
            if (deleteUserName) deleteUserName.textContent = nombre;
            if (formDelete) formDelete.action = '/admin/usuarios/' + id;
            if (modalConfirmDelete) {
                const bsModal = new bootstrap.Modal(modalConfirmDelete);
                bsModal.show();
            }
        });
    });

    // ===========================
    // Ver Detalle Usuario
    // ===========================
    document.querySelectorAll('.btn-ver-usuario').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            fetch('/admin/usuarios/' + id + '/detalle')
                .then(response => response.json())
                .then(data => {
                    const setText = (idEl, value) => {
                        const el = document.getElementById(idEl);
                        if (el) el.textContent = value;
                    };
                    setText('detailCedula', data.trabajador.cedula);
                    setText('detailNombre', data.trabajador.nombre_completo);
                    setText('detailDepartamento', data.trabajador.departamento);
                    setText('detailCargo', data.trabajador.cargo);
                    setText('detailEspecialidad', data.trabajador.especialidad);
                    setText('detailTelefono', data.trabajador.telefono);
                    setText('detailUsuario', data.usuario);
                    setText('detailRol', data.rol);
                    setText('detailStatus', data.status === 'activo' ? 'Activo' : 'Inactivo');
                    setText('detailUltimoLogin', data.ultimo_login);
                    setText('detailCreado', data.created_at);
                    if (modalDetail) {
                        const bsModal = new bootstrap.Modal(modalDetail);
                        bsModal.show();
                    }
                });
        });
    });

    // ===========================
    // Mostrar modal de password temporal y controlar cierre
    // ===========================
    const modalPasswordEl = document.getElementById('modalPassword');
    if (modalPasswordEl) {
        const passwordDisplay = document.getElementById('passwordDisplay');
        if (passwordDisplay && passwordDisplay.textContent.trim() !== '') {
            let bsModalPassword = window.bootstrap.Modal.getOrCreateInstance(modalPasswordEl);
            if (!bsModalPassword) {
                bsModalPassword = new window.bootstrap.Modal(modalPasswordEl, {
                    backdrop: 'static',
                    keyboard: false
                });
            }
            bsModalPassword.show();

            const btnClose = document.getElementById('btnClosePasswordModal');
            if (btnClose) {
                btnClose.addEventListener('click', function () {
                    bsModalPassword.hide();
                });
            }
        }
    }

});
