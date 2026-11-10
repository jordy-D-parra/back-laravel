/**
 * Login - Funcionalidad del formulario
 */

document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss de alertas después de 5 segundos
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            } else {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    });

    // Validación básica del campo cédula (formato venezolano)
    const cedulaInput = document.getElementById('cedula');
    if (cedulaInput) {
        cedulaInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/[^0-9VvEe-]/g, '');
            // Autocompletar prefijo V- si solo escribe números
            if (/^\d+$/.test(value) && value.length >= 7) {
                value = 'V-' + value;
            }
            e.target.value = value;
        });

        // Convertir a mayúsculas al salir del campo
        cedulaInput.addEventListener('blur', function (e) {
            e.target.value = e.target.value.toUpperCase();
        });
    }

    // Efecto de focus en input groups
    const inputGroups = document.querySelectorAll('.input-group');
    inputGroups.forEach(group => {
        const input = group.querySelector('input');
        const span = group.querySelector('.input-group-text');

        if (input && span) {
            input.addEventListener('focus', () => {
                span.style.borderColor = '#1e3c72';
                span.style.transition = 'border-color 0.3s ease';
            });

            input.addEventListener('blur', () => {
                span.style.borderColor = '';
            });
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('togglePassword');
    const input  = document.getElementById('password');
    const eye    = document.getElementById('iconEye');
    const eyeOff = document.getElementById('iconEyeSlash');

    if (!toggle || !input) return;

    toggle.addEventListener('click', () => {
        const oculto = input.type === 'password';
        input.type = oculto ? 'text' : 'password';
        eye.classList.toggle('d-none', oculto);
        eyeOff.classList.toggle('d-none', !oculto);
        toggle.setAttribute('aria-label', oculto ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
});