/**
 * SISTEMA DE VALIDACIONES INTELIGENTES
 * Solo valida lo que evita errores, no lo que molesta
 */

(function() {
    'use strict';

    // ==================== VALIDACIONES EN TIEMPO REAL ====================

    // Solo números (cédula, teléfono)
    document.addEventListener('input', function(e) {
        if (e.target.matches('[data-validate="digits"]')) {
            var value = e.target.value;
            var cleaned = value.replace(/\D/g, '');
            if (value !== cleaned) {
                e.target.value = cleaned;
                showFieldFeedback(e.target, 'Solo se permiten números', 'warning');
            } else {
                clearFieldFeedback(e.target);
            }
        }

        // Solo letras y espacios (nombres)
        if (e.target.matches('[data-validate="letters"]')) {
            var value = e.target.value;
            var cleaned = value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            if (value !== cleaned) {
                e.target.value = cleaned;
                showFieldFeedback(e.target, 'Solo se permiten letras', 'warning');
            } else {
                clearFieldFeedback(e.target);
            }
        }

        // Formato email
        if (e.target.matches('[data-validate="email"]')) {
            var value = e.target.value;
            if (value.length > 0 && !isValidEmail(value)) {
                showFieldFeedback(e.target, 'Formato de email inválido', 'warning');
            } else {
                clearFieldFeedback(e.target);
            }
        }
    });

    // ==================== VALIDACIONES AL ENVIAR FORMULARIOS ====================

    document.addEventListener('submit', function(e) {
        var form = e.target;

        // Validar campos requeridos
        var requireds = form.querySelectorAll('[required]');
        var hasError = false;

        requireds.forEach(function(field) {
            if (!field.value.trim()) {
                showFieldFeedback(field, 'Este campo es requerido', 'error');
                hasError = true;
            }
        });

        // Validar selects con opción por defecto
        var selects = form.querySelectorAll('select[required]');
        selects.forEach(function(select) {
            if (!select.value || select.value === '') {
                showFieldFeedback(select, 'Debe seleccionar una opción', 'error');
                hasError = true;
            }
        });

        if (hasError) {
            e.preventDefault();
            // Scroll al primer error
            var firstError = form.querySelector('.field-error');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // ==================== FUNCIONES AUXILIARES ====================

    function showFieldFeedback(field, message, type) {
        // Remover feedback anterior
        clearFieldFeedback(field);

        // Marcar campo
        field.classList.add('field-' + type);

        // Crear mensaje
        var feedback = document.createElement('div');
        feedback.className = 'field-feedback field-feedback-' + type;
        feedback.textContent = message;
        field.parentNode.appendChild(feedback);

        // Auto-limpiar después de 3 segundos
        setTimeout(function() {
            clearFieldFeedback(field);
        }, 3000);
    }

    function clearFieldFeedback(field) {
        field.classList.remove('field-error', 'field-warning', 'field-success');
        var feedback = field.parentNode.querySelector('.field-feedback');
        if (feedback) feedback.remove();
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // ==================== ESTILOS DINÁMICOS ====================

    var style = document.createElement('style');
    style.textContent =
        '.field-error { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220,53,69,0.15) !important; }' +
        '.field-warning { border-color: #ffc107 !important; box-shadow: 0 0 0 0.2rem rgba(255,193,7,0.15) !important; }' +
        '.field-success { border-color: #28a745 !important; box-shadow: 0 0 0 0.2rem rgba(40,167,69,0.15) !important; }' +
        '.field-feedback { font-size: 0.75rem; margin-top: 4px; }' +
        '.field-feedback-error { color: #dc3545; }' +
        '.field-feedback-warning { color: #856404; }' +
        '.field-feedback-success { color: #28a745; }';
    document.head.appendChild(style);

})();
