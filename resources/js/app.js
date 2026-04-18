import './bootstrap';
import * as bootstrap from 'bootstrap';

// Hacer Bootstrap disponible globalmente (para usarlo en cualquier parte)
window.bootstrap = bootstrap;

// Inicializar automáticamente los tooltips
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Inicializar popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
});
