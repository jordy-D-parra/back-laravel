import 'bootstrap';

document.addEventListener('DOMContentLoaded', function () {

    // ===========================
    // Referencias
    // ===========================
    const modalTrabajador = document.getElementById('modalTrabajador');
    const formTrabajador = document.getElementById('formTrabajador');
    const modalTitulo = document.getElementById('modalTrabajadorTitulo');
    const methodInput = document.getElementById('trabajadorMethod');
    const btnGuardar = document.getElementById('btnGuardarTrabajador');

    const modalConfirmDelete = document.getElementById('modalConfirmDelete');
    const formDelete = document.getElementById('formDelete');
    const deleteTrabajadorNombre = document.getElementById('deleteTrabajadorNombre');
    const deleteTieneUsuario = document.getElementById('deleteTieneUsuario');
    const deleteWarningUsuario = document.getElementById('deleteWarningUsuario');

    const modalDetail = document.getElementById('modalDetail');

    // ===========================
    // Validacion de cedula venezolana en tiempo real
    // ===========================
    const cedulaInput = document.getElementById('trabajadorCedula');
    const cedulaFeedback = document.getElementById('cedulaFeedback');

    if (cedulaInput) {
        cedulaInput.addEventListener('input', function () {
            let value = this.value.replace(/[^0-9VvEe-]/g, '').toUpperCase();

            // Autocompletar V- si solo escribe numeros
            if (/^\d{7,8}$/.test(value)) {
                value = 'V-' + value;
            }

            this.value = value;

            // Validar formato
            const regexCedula = /^[VEJPG]-\d{7,8}$/;
            if (value.length === 0) {
                this.classList.remove('cedula-valid', 'cedula-invalid');
                if (cedulaFeedback) cedulaFeedback.textContent = '';
            } else if (regexCedula.test(value)) {
                this.classList.add('cedula-valid');
                this.classList.remove('cedula-invalid');
                if (cedulaFeedback) {
                    cedulaFeedback.textContent = 'Formato valido';
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
    // Nuevo Trabajador
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
            document.getElementById('trabajadorDepartamento').value = 'Informatica';
            document.getElementById('trabajadorCargo').value = '';
            document.getElementById('trabajadorEspecialidad').value = '';
            document.getElementById('trabajadorTelefono').value = '';

            if (cedulaFeedback) cedulaFeedback.textContent = '';
            cedulaInput.classList.remove('cedula-valid', 'cedula-invalid');

            formTrabajador.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        });
    }

    // ===========================
    // Editar Trabajador
    // ===========================
    document.querySelectorAll('.btn-editar-trabajador').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;

            modalTitulo.textContent = 'Editar Trabajador';
            btnGuardar.textContent = 'Actualizar Trabajador';
            methodInput.value = 'PUT';
            formTrabajador.action = '/admin/trabajadores/' + id;

            document.getElementById('trabajadorCedula').value = this.dataset.cedula || '';
            document.getElementById('trabajadorNombre').value = this.dataset.nombre || '';
            document.getElementById('trabajadorApellido').value = this.dataset.apellido || '';
            document.getElementById('trabajadorDepartamento').value = this.dataset.departamento || 'Informatica';
            document.getElementById('trabajadorCargo').value = this.dataset.cargo || '';
            document.getElementById('trabajadorEspecialidad').value = this.dataset.especialidad || '';
            document.getElementById('trabajadorTelefono').value = this.dataset.telefono || '';

            if (cedulaFeedback) cedulaFeedback.textContent = '';
            cedulaInput.classList.add('cedula-valid');
            cedulaInput.classList.remove('cedula-invalid');

            formTrabajador.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            const bsModal = new bootstrap.Modal(modalTrabajador);
            bsModal.show();
        });
    });

    // ===========================
    // Eliminar Trabajador
    // ===========================
    document.querySelectorAll('.btn-eliminar-trabajador').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const nombre = this.dataset.nombre;
            const tieneUsuario = this.dataset.tieneUsuario === '1';

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

    // ===========================
    // Crear usuario desde trabajador
    // ===========================
    document.querySelectorAll('.btn-crear-usuario').forEach(btn => {
        btn.addEventListener('click', function () {
            const cedula = this.dataset.cedula;
            // Redirigir a la pagina de usuarios con la cedula como parametro
            window.location.href = '/admin/usuarios?search=' + encodeURIComponent(cedula) + '&crear=1';
        });
    });

    // Si viene de crear usuario, abrir modal automaticamente
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('crear') === '1') {
        const btnNuevoUsuario = document.querySelector('[data-bs-target="#modalUsuario"]');
        if (btnNuevoUsuario) {
            setTimeout(() => btnNuevoUsuario.click(), 500);
        }
    }

    // ===========================
    // Ver Detalle Trabajador
    // ===========================
    document.querySelectorAll('.btn-ver-trabajador').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;

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
                    setText('dtDepartamento', t.departamento);
                    setText('dtCargo', t.cargo);
                    setText('dtEspecialidad', t.especialidad);
                    setText('dtTelefono', t.telefono);
                    setText('dtCreado', t.created_at);

                    const infoUsuario = document.getElementById('dtInfoUsuario');
                    const btnCrearUsuario = document.getElementById('btnCrearUsuarioDesdeDetalle');

                    if (t.tiene_usuario && t.usuario) {
                        if (infoUsuario) {
                            infoUsuario.innerHTML = `
                                <div class="detail-item"><div class="detail-label">Usuario</div><div class="detail-value">${t.usuario.nombre}</div></div>
                                <div class="detail-item"><div class="detail-label">Rol</div><div class="detail-value">${t.usuario.rol}</div></div>
                                <div class="detail-item"><div class="detail-label">Estado</div><div class="detail-value">${t.usuario.status}</div></div>
                                <div class="detail-item"><div class="detail-label">Ultimo Ingreso</div><div class="detail-value">${t.usuario.ultimo_login}</div></div>
                            `;
                            infoUsuario.style.display = 'block';
                        }
                        if (btnCrearUsuario) btnCrearUsuario.style.display = 'none';
                    } else {
                        if (infoUsuario) infoUsuario.style.display = 'none';
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
                });
        });
    });

});
