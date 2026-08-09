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
    const deleteTieneUsuario = document.getElementById('deleteTieneUsuario');
    const deleteWarningUsuario = document.getElementById('deleteWarningUsuario');

    const modalDetail = document.getElementById('modalDetail');

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
    // BÚSQUEDA EN TIEMPO REAL
    // ===========================
    const buscarInput = document.getElementById('buscarTrabajador');
    const tablaBody = document.getElementById('tablaTrabajadores');
    let timeoutBusqueda = null;

    if (buscarInput) {
        buscarInput.addEventListener('input', function () {
            const termino = this.value.trim();

            clearTimeout(timeoutBusqueda);
            timeoutBusqueda = setTimeout(() => {
                buscarTrabajadores(termino);
            }, 300);
        });
    }

    function buscarTrabajadores(termino) {
        const url = new URL(window.location.href);
        url.searchParams.set('search', termino);
        window.location.href = url.toString();
    }

    // ===========================
    // VALIDACIÓN DE CÉDULA
    // ===========================
    const cedulaInput = document.getElementById('trabajadorCedula');
    const cedulaFeedback = document.getElementById('cedulaFeedback');

    if (cedulaInput) {
        cedulaInput.addEventListener('input', function () {
            let value = this.value.replace(/[^0-9VvEe-]/g, '').toUpperCase();

            // Autocompletar V- si solo escribe números
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
                    cedulaFeedback.textContent = '✅ Formato válido';
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
            document.getElementById('trabajadorDepartamento').value = 'Informática';
            document.getElementById('trabajadorCargo').value = '';
            document.getElementById('trabajadorEspecialidad').value = '';
            document.getElementById('trabajadorTelefono').value = '';

            if (cedulaFeedback) cedulaFeedback.textContent = '';
            cedulaInput.classList.remove('cedula-valid', 'cedula-invalid');

            formTrabajador.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        });
    }

    // ===========================
    // EDITAR TRABAJADOR
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
            document.getElementById('trabajadorDepartamento').value = this.dataset.departamento || 'Informática';
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
    // ELIMINAR TRABAJADOR
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
    // CREAR USUARIO DESDE TRABAJADOR
    // ===========================
    document.querySelectorAll('.btn-crear-usuario').forEach(btn => {
        btn.addEventListener('click', function () {
            const cedula = this.dataset.cedula;
            window.location.href = '/admin/usuarios?search=' + encodeURIComponent(cedula) + '&crear=1';
        });
    });

    // ===========================
    // VER DETALLE
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
                                <div class="detail-item"><div class="detail-label">Último Ingreso</div><div class="detail-value">${t.usuario.ultimo_login}</div></div>
                            `;
                            infoUsuario.style.display = 'block';
                        }
                        if (btnCrearUsuario) btnCrearUsuario.style.display = 'none';
                        document.getElementById('dtSinUsuario').style.display = 'none';
                    } else {
                        if (infoUsuario) infoUsuario.style.display = 'none';
                        document.getElementById('dtSinUsuario').style.display = 'block';
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
                    mostrarNotificacion('error', 'Error al cargar los detalles');
                });
        });
    });

    // ===========================
    // VALIDACIÓN DEL FORMULARIO
    // ===========================
    if (formTrabajador) {
        formTrabajador.addEventListener('submit', function (e) {
            const cedula = document.getElementById('trabajadorCedula');
            const nombre = document.getElementById('trabajadorNombre');
            const apellido = document.getElementById('trabajadorApellido');
            const departamento = document.getElementById('trabajadorDepartamento');
            const cargo = document.getElementById('trabajadorCargo');

            let isValid = true;

            // Validar cédula
            const regexCedula = /^[VEJPG]-\d{7,8}$/;
            if (!cedula.value.trim() || !regexCedula.test(cedula.value.trim())) {
                cedula.classList.add('is-invalid');
                isValid = false;
            } else {
                cedula.classList.remove('is-invalid');
            }

            // Validar campos obligatorios
            if (!nombre.value.trim()) {
                nombre.classList.add('is-invalid');
                isValid = false;
            } else {
                nombre.classList.remove('is-invalid');
            }

            if (!apellido.value.trim()) {
                apellido.classList.add('is-invalid');
                isValid = false;
            } else {
                apellido.classList.remove('is-invalid');
            }

            if (!departamento.value.trim()) {
                departamento.classList.add('is-invalid');
                isValid = false;
            } else {
                departamento.classList.remove('is-invalid');
            }

            if (!cargo.value.trim()) {
                cargo.classList.add('is-invalid');
                isValid = false;
            } else {
                cargo.classList.remove('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                mostrarNotificacion('error', 'Por favor, complete todos los campos obligatorios correctamente');
                return false;
            }

            // Mostrar loading en el botón
            if (btnGuardar) {
                btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';
                btnGuardar.disabled = true;
            }

            return true;
        });
    }

    // ===========================
    // RESTAURAR BOTÓN AL CERRAR MODAL
    // ===========================
    if (modalTrabajador) {
        modalTrabajador.addEventListener('hidden.bs.modal', function () {
            if (btnGuardar) {
                btnGuardar.innerHTML = 'Guardar Trabajador';
                btnGuardar.disabled = false;
            }
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

    console.log('✅ Módulo de trabajadores inicializado correctamente');
});
// ===========================
// BÚSQUEDA EN TIEMPO REAL
// ===========================
document.addEventListener('DOMContentLoaded', function() {
    const buscarInput = document.getElementById('buscarUsuario');
    let timeoutBusqueda = null;

    if (buscarInput) {
        console.log('✅ Buscador de usuarios inicializado');

        buscarInput.addEventListener('input', function() {
            const termino = this.value.trim();

            // Limpiar el timeout anterior
            clearTimeout(timeoutBusqueda);

            // Esperar 300ms después de que el usuario deje de escribir
            timeoutBusqueda = setTimeout(function() {
                const url = new URL(window.location.href);
                
                // Si el término está vacío, eliminar el parámetro search
                if (termino === '') {
                    url.searchParams.delete('search');
                } else {
                    url.searchParams.set('search', termino);
                }
                
                // Mantener la página actual
                window.location.href = url.toString();
            }, 400);
        });

        // Si hay un valor en el input, enfocar al final
        if (buscarInput.value) {
            buscarInput.focus();
            buscarInput.setSelectionRange(buscarInput.value.length, buscarInput.value.length);
        }
    } else {
        console.log('⚠️ No se encontró el elemento #buscarUsuario');
    }
});