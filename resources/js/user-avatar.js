// resources/js/user-avatar.js

document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('userAvatarBtn');
    const dropdown = document.getElementById('userAvatarDropdown');
    const fotoInput = document.getElementById('perfilFotoInput');
    const modalEl = document.getElementById('modalPerfil');

    if (!btn || !dropdown) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ============ TOGGLE DROPDOWN ============
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && e.target !== btn) {
            dropdown.classList.remove('show');
        }
    });

    // ============ ABRIR MODAL PERFIL ============
    window.abrirModalPerfil = function (e) {
        if (e) e.preventDefault();
        dropdown.classList.remove('show');

        // Cargar datos frescos
        fetch('/perfil/data', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const d = res.data;
                document.getElementById('perfil_nombre').value = d.nombre || '';
                document.getElementById('perfil_apellido').value = d.apellido || '';
                document.getElementById('perfil_cedula').value = d.cedula || 'N/A';
                document.getElementById('perfil_usuario').value = d.usuario || '';
                document.getElementById('perfil_email').value = d.email || '';
                document.getElementById('perfil_telefono').value = d.telefono || '';
                document.getElementById('perfil_departamento').value = d.departamento || '';
                document.getElementById('perfil_cargo').value = d.cargo || '';
                document.getElementById('perfil_especialidad').value = d.especialidad || '';
                document.getElementById('perfil_rol').value = d.rol || 'Sin rol';

                document.getElementById('perfilFotoImg').src = d.foto_perfil_url;
                document.getElementById('btnEliminarFoto').style.display = d.tiene_foto ? 'inline-flex' : 'none';

                // Resetear a la pestaña de información
                const tabInfo = document.getElementById('tab-info');
                if (tabInfo) {
                    new bootstrap.Tab(tabInfo).show();
                }

                // Abrir modal
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        })
        .catch(err => console.error('Error al cargar perfil:', err));
    };

    // ============ SUBIR FOTO ============
    fotoInput?.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            mostrarToastPerfil('La imagen no debe superar 2MB', 'error');
            this.value = '';
            return;
        }

        const formData = new FormData();
        formData.append('foto', file);

        // Preview inmediato
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('perfilFotoImg').src = e.target.result;
        };
        reader.readAsDataURL(file);

        fetch('/perfil/foto', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.querySelectorAll('#userAvatarImg, #userAvatarImg2, #perfilFotoImg')
                    .forEach(img => img.src = res.foto_url + '?t=' + Date.now());

                document.getElementById('btnEliminarFoto').style.display = 'inline-flex';
                mostrarToastPerfil(res.message, 'success');
            } else {
                mostrarToastPerfil(res.message || 'Error al subir la foto', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            mostrarToastPerfil('Error de conexión', 'error');
        })
        .finally(() => {
            fotoInput.value = '';
        });
    });

    // ============ ELIMINAR FOTO ============
    document.getElementById('btnEliminarFoto')?.addEventListener('click', function () {
        if (!confirm('¿Eliminar tu foto de perfil?')) return;

        fetch('/perfil/foto', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.querySelectorAll('#userAvatarImg, #userAvatarImg2, #perfilFotoImg')
                    .forEach(img => img.src = res.foto_url);

                document.getElementById('btnEliminarFoto').style.display = 'none';
                mostrarToastPerfil(res.message, 'success');
            } else {
                mostrarToastPerfil(res.message || 'Error', 'error');
            }
        })
        .catch(() => mostrarToastPerfil('Error de conexión', 'error'));
    });

    // ============ GUARDAR INFO ============
    document.getElementById('formPerfilInfo')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = document.getElementById('btnGuardarInfo');
        const original = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

        const formData = new FormData(this);

        fetch('/perfil/info', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                mostrarToastPerfil(res.message, 'success');

                // Actualizar nombre visible en el topbar
                const userInfoName = document.querySelector('.user-name');
                if (userInfoName && res.data?.nombre_completo) {
                    userInfoName.textContent = res.data.nombre_completo;
                }

                // Actualizar dropdown header
                const headerName = document.querySelector('.avatar-dropdown-info strong');
                if (headerName && res.data?.nombre_completo) {
                    headerName.textContent = res.data.nombre_completo;
                }
            } else {
                let msg = res.message || 'Error al guardar';
                if (res.errors) {
                    msg = Object.values(res.errors).flat().join('\n');
                }
                mostrarToastPerfil(msg, 'error');
            }
        })
        .catch(() => mostrarToastPerfil('Error de conexión', 'error'))
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = original;
        });
    });

    // ============ CAMBIAR CONTRASEÑA ============
    document.getElementById('formPerfilPassword')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Cambiando...';

        const formData = new FormData(this);

        fetch('/perfil/password', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                mostrarToastPerfil(res.message, 'success');
                this.reset();
            } else {
                let msg = res.message || 'Error al cambiar';
                if (res.errors) msg = Object.values(res.errors).flat().join('\n');
                mostrarToastPerfil(msg, 'error');
            }
        })
        .catch(() => mostrarToastPerfil('Error de conexión', 'error'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = original;
        });
    });

    // ============ TOAST ============
    function mostrarToastPerfil(mensaje, tipo = 'success') {
        const colores = { success: '#1e7e34', error: '#c5221f', warning: '#f6c23e', info: '#1e3c72' };
        const iconos = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 99999;
            background: ${colores[tipo] || colores.success}; color: white;
            padding: 14px 20px; border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            font-weight: 500; font-size: 0.9rem;
            display: flex; align-items: center; gap: 10px;
            max-width: 400px; white-space: pre-line;
            animation: slideInRight 0.3s ease-out;
        `;
        toast.innerHTML = `<span>${iconos[tipo]}</span><span>${mensaje}</span>`;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s, transform 0.3s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
});