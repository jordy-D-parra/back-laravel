// resources/js/admin-entidades.js
// ✅ VERSIÓN CORREGIDA - Incluye búsqueda en tiempo real

// ===========================
// Panel de Entidades Unificado
// ===========================

document.addEventListener('DOMContentLoaded', function() {

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let elementoAEliminar = null;

    // ===========================
    // Funciones helper de URLs
    // ===========================
    function getUrl(tipo, id) {
        const plurales = {
            'institucion': 'instituciones',
            'departamento': 'departamentos',
            'responsable': 'responsables'
        };
        const plural = plurales[tipo] || (tipo + 's');
        return `/admin/${plural}/${id}`;
    }

    function getUrlBase(tipo) {
        const plurales = {
            'institucion': 'instituciones',
            'departamento': 'departamentos',
            'responsable': 'responsables'
        };
        const plural = plurales[tipo] || (tipo + 's');
        return `/admin/${plural}`;
    }

    // ===========================
    // Función debounce (definida globalmente)
    // ===========================
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // ===========================
    // Fix: Cerrar modal y limpiar backdrops
    // ===========================
    function cerrarModalDetalle() {
        const modalDetalle = document.getElementById('modalDetalle');
        if (modalDetalle && modalDetalle.classList.contains('show')) {
            const bsModal = bootstrap.Modal.getInstance(modalDetalle);
            if (bsModal) bsModal.hide();
        }
        setTimeout(() => {
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }, 300);
    }

    const modalDetalleEl = document.getElementById('modalDetalle');
    if (modalDetalleEl) {
        modalDetalleEl.addEventListener('hidden.bs.modal', function() {
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }

    // ===========================
    // SVG Iconos
    // ===========================
    const iconoVer = '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    const iconoEditar = '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
    const iconoToggle = '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>';
    const iconoEliminar = '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
    const iconoVerSmall = '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:12px;height:12px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    const iconoEditarSmall = '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:12px;height:12px"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
    const iconoInstitucion = '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width:14px;height:14px"><rect x="4" y="8" width="16" height="12" rx="1"/><path d="M8 20V8M16 20V8M4 12h16"/></svg>';
    const iconoDepartamento = '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width:14px;height:14px"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M8 8h8M8 12h6M8 16h4"/></svg>';
    const iconoResponsable = '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width:14px;height:14px"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>';
    const iconoCheck = '<svg viewBox="0 0 24 24" stroke="#1e7e34" stroke-width="2" fill="none" style="width:12px;height:12px"><polyline points="20 6 9 17 4 12"/></svg>';
    const iconoX = '<svg viewBox="0 0 24 24" stroke="#c5221f" stroke-width="2" fill="none" style="width:12px;height:12px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
    const iconoNuevo = '<svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>';

    // ===========================
    // Utilidades
    // ===========================
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function resaltarTexto(texto, buscar) {
        if (!buscar || !texto) return escapeHtml(texto);
        const escaped = escapeHtml(texto);
        const regex = new RegExp(`(${buscar.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return escaped.replace(regex, '<span class="highlight">$1</span>');
    }

    function mostrarCarga(contenedorId) {
        const contenedor = document.getElementById(contenedorId);
        if (!contenedor) return;
        contenedor.innerHTML = `
            <div class="loading-spinner">
                <svg class="spinner-icon" viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:20px;height:20px">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
                Cargando...
            </div>`;
    }

    function mostrarToast(mensaje, tipo = 'success') {
        const colores = {
            success: '#1e7e34',
            error: '#c5221f',
            warning: '#f6c23e',
            info: '#1e3c72'
        };
        const iconos = {
            success: '<svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:18px;height:18px"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
            error: '<svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:18px;height:18px"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            warning: '<svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:18px;height:18px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info: '<svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:18px;height:18px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
        };
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; top: 20px; right: 20px;
            background: ${colores[tipo] || colores.success}; color: white;
            padding: 14px 20px; border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2); z-index: 9999;
            display: flex; align-items: center; gap: 10px;
            font-weight: 500; font-size: 0.9rem;
            animation: slideInRight 0.3s ease-out; max-width: 400px;
        `;
        toast.innerHTML = `${iconos[tipo] || iconos.success} ${mensaje}`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ===========================
    // Helper: habilitar/deshabilitar campos del representante
    // ===========================
    window.setCamposRepresentanteEstado = function(deshabilitar) {
        const campos = [
            'depto_representante_nombre',
            'depto_representante_documento',
            'depto_representante_telefono',
            'depto_representante_email',
            'depto_representante_cargo',
            'depto_representante_direccion'
        ];
        campos.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.disabled = deshabilitar;
        });
    };

    window.limpiarCamposRepresentante = function() {
        const campos = [
            'depto_representante_nombre',
            'depto_representante_documento',
            'depto_representante_telefono',
            'depto_representante_email',
            'depto_representante_direccion'
        ];
        campos.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const cargoEl = document.getElementById('depto_representante_cargo');
        if (cargoEl) cargoEl.value = 'Jefe de Departamento';
    };

    window.resetCamposOcultosResponsable = function() {
        const inputUsarResp = document.getElementById('usar_responsable_institucion_input');
        const inputRespId = document.getElementById('responsable_id_input');
        if (inputUsarResp) inputUsarResp.value = '0';
        if (inputRespId) inputRespId.value = '';
    };

    // ===========================
    // Validaciones de campos
    // ===========================
    function validarDocumento(input) {
        let valor = input.value.replace(/[^0-9]/g, '');
        if (valor.length > 8) valor = valor.substring(0, 8);
        if (valor.length > 0) valor = 'V-' + valor;
        input.value = valor;
    }

    function validarTelefono(input) {
        let valor = input.value.replace(/[^0-9]/g, '');
        if (valor.length > 11) valor = valor.substring(0, 11);
        if (valor.length >= 4) valor = valor.substring(0, 4) + '-' + valor.substring(4);
        input.value = valor;
    }

    function validarSoloLetras(input) {
        input.value = input.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    }

    function validarEmail(input) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (input.value.trim() === '') {
            input.classList.remove('is-invalid', 'is-valid');
            return;
        }
        if (emailRegex.test(input.value)) {
            input.classList.add('is-valid');
            input.classList.remove('is-invalid');
        } else {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
        }
    }

    document.querySelectorAll('input[name="representante_documento"], input[name="documento"]').forEach(input => {
        input.addEventListener('input', () => validarDocumento(input));
        input.addEventListener('blur', function() {
            this.classList.toggle('is-invalid', this.value.length < 10);
            this.classList.toggle('is-valid', this.value.length >= 10);
        });
    });

    document.querySelectorAll('input[name="representante_telefono"], input[name="telefono"]').forEach(input => {
        input.addEventListener('input', () => validarTelefono(input));
        input.addEventListener('blur', function() {
            this.classList.toggle('is-invalid', this.value.length < 10);
            this.classList.toggle('is-valid', this.value.length >= 10);
        });
    });

    document.querySelectorAll('input[name="representante_nombre"], input[name="nombre"], #inst_nombre, #depto_nombre').forEach(input => {
        input.addEventListener('input', () => validarSoloLetras(input));
    });

    document.querySelectorAll('input[type="email"], input[name="representante_email"], input[name="email"]').forEach(input => {
        input.addEventListener('blur', () => validarEmail(input));
    });

    // ===========================
    // CARGAR INSTITUCIONES EN LA TABLA
    // ===========================
    function cargarInstituciones() {
        const buscar = document.getElementById('buscarInstituciones')?.value || '';
        const estado = document.getElementById('filtroEstadoInstituciones')?.value || '';
        const tabla = document.getElementById('tablaInstituciones');

        if (tabla) {
            tabla.innerHTML = '<div class="loading-spinner"><svg class="spinner-icon" viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:20px;height:20px"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>Cargando...</div>';
        }

        fetch(`/admin/instituciones?todos=1&buscar=${encodeURIComponent(buscar)}&estado=${estado}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                renderTablaInstituciones(response, buscar);
            } else {
                if (tabla) {
                    tabla.innerHTML = '<p class="text-center py-4 text-danger">Error al cargar las instituciones</p>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (tabla) {
                tabla.innerHTML = '<p class="text-center py-4 text-danger">Error de conexión al servidor</p>';
            }
        });
    }

    // ===========================
    // RENDERIZAR TABLA DE INSTITUCIONES
    // ===========================
    function renderTablaInstituciones(response, buscar) {
        const data = response.data || [];
        const tabla = document.getElementById('tablaInstituciones');

        if (!tabla) return;

        if (!data || data.length === 0) {
            tabla.innerHTML = `
                <table class="table table-hover align-middle mb-0">
                    <thead><tr>
                        <th>Nombre</th><th>Representante</th><th>Ubicación</th><th>Deptos.</th><th>Resp.</th><th>Estado</th><th style="width:140px">Acciones</th>
                    </tr></thead>
                    <tbody>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron instituciones</td></tr>
                    </tbody>
                </table>
            `;
            return;
        }

        let html = `
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th>Nombre</th><th>Representante</th><th>Ubicación</th><th>Deptos.</th><th>Resp.</th><th>Estado</th><th style="width:140px">Acciones</th>
                </tr></thead>
                <tbody>
        `;

        data.forEach(item => {
            const ubicacionCompleta = item.ubicacion_completa ||
                (item.parroquia ? item.parroquia.nombre + ', ' : '') +
                (item.municipio ? item.municipio.nombre + ', ' : '') +
                (item.estado ? item.estado.nombre : 'No especificada');

            html += `
                <tr>
                    <td>
                        <div class="fw-medium" style="color:#1e3c72">${resaltarTexto(item.nombre, buscar)}</div>
                        ${item.informacion ? `<small class="text-muted">${escapeHtml(item.informacion.substring(0, 40))}...</small>` : ''}
                    </td>
                    <td>${resaltarTexto(item.representante, buscar) || '—'}</td>
                    <td><small>${escapeHtml(ubicacionCompleta)}</small></td>
                    <td><span class="badge-activo">${item.departamentos_count || 0}</span></td>
                    <td><span class="badge-activo">${item.responsables_count || 0}</span></td>
                    <td><span class="badge ${item.activo ? 'badge-activo' : 'badge-inactivo'}">${item.activo ? 'Activa' : 'Inactiva'}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="verDetalle('institucion', ${item.id})" title="Ver detalle">${iconoVer}</button>
                            <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="editarInstitucion(${item.id})" title="Editar">${iconoEditar}</button>
                            <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="toggleEstado('institucion', ${item.id})" title="Cambiar estado">${iconoToggle}</button>
                            <button class="btn btn-action btn-outline-danger btn-sm" onclick="confirmarEliminar('institucion', ${item.id}, '${escapeHtml(item.nombre).replace(/'/g, "\\'")}', true)" title="Eliminar">${iconoEliminar}</button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        tabla.innerHTML = html;
    }

    // ===========================
    // CARGAR DEPARTAMENTOS EN LA TABLA
    // ===========================
    function cargarDepartamentos() {
        const buscar = document.getElementById('buscarDepartamentos')?.value || '';
        const institucionId = document.getElementById('filtroInstitucionDepartamentos')?.value || '';
        const tabla = document.getElementById('tablaDepartamentos');

        if (!tabla) return;

        tabla.innerHTML = '<div class="loading-spinner"><svg class="spinner-icon" viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:20px;height:20px"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>Cargando...</div>';

        let url = `/admin/departamentos?buscar=${encodeURIComponent(buscar)}`;
        if (institucionId) {
            url += `&institucion_id=${institucionId}`;
        }

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            renderTablaDepartamentos(data);
        })
        .catch(error => {
            console.error('Error:', error);
            tabla.innerHTML = '<p class="text-center py-4 text-danger">Error al cargar los departamentos</p>';
        });
    }

    function renderTablaDepartamentos(data) {
        const tabla = document.getElementById('tablaDepartamentos');
        if (!tabla) return;

        const departamentos = data.data || [];

        if (departamentos.length === 0) {
            tabla.innerHTML = `
                <table class="table table-hover align-middle mb-0">
                    <thead><tr>
                        <th>Nombre</th><th>Institución</th><th>Representante</th><th>Responsables</th><th>Estado</th><th style="width:140px">Acciones</th>
                    </tr></thead>
                    <tbody>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron departamentos</td></tr>
                    </tbody>
                </table>
            `;
            return;
        }

        let html = `
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th>Nombre</th><th>Institución</th><th>Representante</th><th>Responsables</th><th>Estado</th><th style="width:140px">Acciones</th>
                </tr></thead>
                <tbody>
        `;

        departamentos.forEach(item => {
            html += `
                <tr>
                    <td><span class="fw-medium" style="color:#1e3c72">${escapeHtml(item.nombre)}</span></td>
                    <td>${escapeHtml(item.institucion?.nombre || 'Sin institución')}</td>
                    <td>${escapeHtml(item.representante || '—')}</td>
                    <td><span class="badge-activo">${item.responsables_count || 0}</span></td>
                    <td><span class="badge ${item.activo ? 'badge-activo' : 'badge-inactivo'}">${item.activo ? 'Activo' : 'Inactivo'}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="verDetalle('departamento', ${item.id})" title="Ver detalle">${iconoVer}</button>
                            <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="editarDepartamento(${item.id})" title="Editar">${iconoEditar}</button>
                            <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="toggleEstado('departamento', ${item.id})" title="Cambiar estado">${iconoToggle}</button>
                            <button class="btn btn-action btn-outline-danger btn-sm" onclick="confirmarEliminar('departamento', ${item.id}, '${escapeHtml(item.nombre).replace(/'/g, "\\'")}', false)" title="Eliminar">${iconoEliminar}</button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        tabla.innerHTML = html;
    }

    // ===========================
    // CARGAR RESPONSABLES EN LA TABLA
    // ===========================
    function cargarResponsables() {
        const buscar = document.getElementById('buscarResponsables')?.value || '';
        const institucionId = document.getElementById('filtroInstitucionResponsables')?.value || '';
        const tabla = document.getElementById('tablaResponsables');

        if (!tabla) return;

        tabla.innerHTML = '<div class="loading-spinner"><svg class="spinner-icon" viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:20px;height:20px"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>Cargando...</div>';

        let url = `/admin/responsables?buscar=${encodeURIComponent(buscar)}`;
        if (institucionId) {
            url += `&institucion_id=${institucionId}`;
        }

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            renderTablaResponsables(data);
        })
        .catch(error => {
            console.error('Error:', error);
            tabla.innerHTML = '<p class="text-center py-4 text-danger">Error al cargar los responsables</p>';
        });
    }

    function renderTablaResponsables(data) {
        const tabla = document.getElementById('tablaResponsables');
        if (!tabla) return;

        const responsables = data.data || [];

        if (responsables.length === 0) {
            tabla.innerHTML = `
                <table class="table table-hover align-middle mb-0">
                    <thead><tr>
                        <th>Nombre</th><th>Documento</th><th>Cargo</th><th>Institución</th><th>Departamento</th><th>Estado</th><th style="width:140px">Acciones</th>
                    </tr></thead>
                    <tbody>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron responsables</td></tr>
                    </tbody>
                </table>
            `;
            return;
        }

        let html = `
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th>Nombre</th><th>Documento</th><th>Cargo</th><th>Institución</th><th>Departamento</th><th>Estado</th><th style="width:140px">Acciones</th>
                </tr></thead>
                <tbody>
        `;

        responsables.forEach(item => {
            html += `
                <tr>
                    <td><span class="fw-medium" style="color:#1e3c72">${escapeHtml(item.nombre)}</span></td>
                    <td>${escapeHtml(item.documento || '—')}</td>
                    <td>${escapeHtml(item.cargo || '—')}</td>
                    <td>${escapeHtml(item.institucion?.nombre || '—')}</td>
                    <td>${escapeHtml(item.departamento?.nombre || 'Sin depto.')}</td>
                    <td><span class="badge ${item.activo ? 'badge-activo' : 'badge-inactivo'}">${item.activo ? 'Activo' : 'Inactivo'}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="verDetalle('responsable', ${item.id})" title="Ver detalle">${iconoVer}</button>
                            <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="editarResponsable(${item.id})" title="Editar">${iconoEditar}</button>
                            <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="toggleEstado('responsable', ${item.id})" title="Cambiar estado">${iconoToggle}</button>
                            <button class="btn btn-action btn-outline-danger btn-sm" onclick="confirmarEliminar('responsable', ${item.id}, '${escapeHtml(item.nombre).replace(/'/g, "\\'")}', false)" title="Eliminar">${iconoEliminar}</button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        tabla.innerHTML = html;
    }

    // ===========================
    // Cargar instituciones en selects
    // ===========================
    function cargarInstitucionesEnSelect() {
        fetch('/admin/instituciones?todos=1', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(response => {
                const instituciones = response.data || response;
                if (!instituciones) return;

                const selects = [
                    { id: 'depto_institucion_id', placeholder: 'Seleccionar institución...' },
                    { id: 'resp_institucion_id', placeholder: 'Seleccionar institución...' },
                    { id: 'filtroInstitucionDepartamentos', placeholder: 'Todas las instituciones' },
                    { id: 'filtroInstitucionResponsables', placeholder: 'Todas las instituciones' }
                ];

                selects.forEach(({ id, placeholder }) => {
                    const select = document.getElementById(id);
                    if (!select) return;
                    const valorActual = select.value;
                    select.innerHTML = `<option value="">${placeholder}</option>`;
                    if (instituciones.length > 0) {
                        instituciones.forEach(inst => {
                            select.innerHTML += `<option value="${inst.id}">${escapeHtml(inst.nombre)}</option>`;
                        });
                    }
                    select.value = valorActual;
                });
            })
            .catch(e => console.error('Error cargando instituciones:', e));
    }

    // ===========================
    // UBICACIONES - SELECTS ANIDADOS
    // ===========================

    // Función para cargar estados
    function cargarEstados(selectId, selectedId = null) {
        const select = document.getElementById(selectId);
        if (!select) return;

        select.innerHTML = '<option value="">Cargando estados...</option>';
        select.disabled = true;

        fetch('/admin/ubicaciones/estados', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                select.innerHTML = '<option value="">Seleccionar estado...</option>';
                data.data.forEach(estado => {
                    const option = document.createElement('option');
                    option.value = estado.id;
                    option.textContent = estado.nombre;
                    select.appendChild(option);
                });
                if (selectedId) {
                    select.value = selectedId;
                }
                select.disabled = false;
            } else {
                select.innerHTML = '<option value="">Error al cargar estados</option>';
            }
        })
        .catch(error => {
            console.error('Error cargando estados:', error);
            select.innerHTML = '<option value="">Error al cargar estados</option>';
        });
    }

    // Función para cargar municipios según estado seleccionado
    function cargarMunicipios(estadoId, selectId, selectedId = null) {
        const select = document.getElementById(selectId);
        if (!select) return;

        if (!estadoId) {
            select.innerHTML = '<option value="">Seleccionar estado primero</option>';
            select.disabled = true;
            const parroquiaSelect = document.getElementById('parroquia_id');
            if (parroquiaSelect) {
                parroquiaSelect.innerHTML = '<option value="">Seleccionar municipio primero</option>';
                parroquiaSelect.disabled = true;
            }
            return;
        }

        select.innerHTML = '<option value="">Cargando municipios...</option>';
        select.disabled = true;

        fetch(`/admin/ubicaciones/estados/${estadoId}/municipios`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                select.innerHTML = '<option value="">Seleccionar municipio...</option>';
                data.data.forEach(municipio => {
                    const option = document.createElement('option');
                    option.value = municipio.id;
                    option.textContent = municipio.nombre;
                    select.appendChild(option);
                });
                if (selectedId) {
                    select.value = selectedId;
                    if (selectedId) {
                        cargarParroquias(selectedId, 'parroquia_id');
                    }
                }
                select.disabled = false;
            } else {
                select.innerHTML = '<option value="">Error al cargar municipios</option>';
            }
        })
        .catch(error => {
            console.error('Error cargando municipios:', error);
            select.innerHTML = '<option value="">Error al cargar municipios</option>';
        });
    }

    // Función para cargar parroquias según municipio seleccionado
    function cargarParroquias(municipioId, selectId, selectedId = null) {
        const select = document.getElementById(selectId);
        if (!select) return;

        if (!municipioId) {
            select.innerHTML = '<option value="">Seleccionar municipio primero</option>';
            select.disabled = true;
            return;
        }

        select.innerHTML = '<option value="">Cargando parroquias...</option>';
        select.disabled = true;

        fetch(`/admin/ubicaciones/municipios/${municipioId}/parroquias`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                select.innerHTML = '<option value="">Seleccionar parroquia...</option>';
                data.data.forEach(parroquia => {
                    const option = document.createElement('option');
                    option.value = parroquia.id;
                    option.textContent = parroquia.nombre;
                    select.appendChild(option);
                });
                if (selectedId) {
                    select.value = selectedId;
                }
                select.disabled = false;
            } else {
                select.innerHTML = '<option value="">Error al cargar parroquias</option>';
            }
        })
        .catch(error => {
            console.error('Error cargando parroquias:', error);
            select.innerHTML = '<option value="">Error al cargar parroquias</option>';
        });
    }

    // Función para cargar ubicación en edición
    function cargarUbicacionEdicion(estadoId, municipioId, parroquiaId) {
        if (estadoId) {
            cargarEstados('estado_id', estadoId);
            setTimeout(() => {
                if (municipioId) {
                    cargarMunicipios(estadoId, 'municipio_id', municipioId);
                    setTimeout(() => {
                        if (parroquiaId) {
                            cargarParroquias(municipioId, 'parroquia_id', parroquiaId);
                        }
                    }, 300);
                }
            }, 300);
        } else {
            cargarEstados('estado_id');
        }
    }

    // Event listeners para selects anidados
    function initUbicacionSelects() {
        const estadoSelect = document.getElementById('estado_id');
        const municipioSelect = document.getElementById('municipio_id');
        const parroquiaSelect = document.getElementById('parroquia_id');

        if (estadoSelect) {
            const newEstadoSelect = estadoSelect.cloneNode(true);
            estadoSelect.parentNode.replaceChild(newEstadoSelect, estadoSelect);

            newEstadoSelect.addEventListener('change', function() {
                const estadoId = this.value;
                if (municipioSelect) {
                    cargarMunicipios(estadoId, 'municipio_id');
                }
                if (parroquiaSelect) {
                    parroquiaSelect.innerHTML = '<option value="">Seleccionar municipio primero</option>';
                    parroquiaSelect.disabled = true;
                }
            });
        }

        if (municipioSelect) {
            const newMunicipioSelect = municipioSelect.cloneNode(true);
            municipioSelect.parentNode.replaceChild(newMunicipioSelect, municipioSelect);

            newMunicipioSelect.addEventListener('change', function() {
                const municipioId = this.value;
                cargarParroquias(municipioId, 'parroquia_id');
            });
        }
    }

    // ===========================
    // Limpiar formulario de institución
    // ===========================
    window.limpiarFormInstitucion = function() {
        if (typeof pasoActualInst !== 'undefined') {
            pasoActualInst = 1;
            const step1 = document.getElementById('stepInst1');
            const step2 = document.getElementById('stepInst2');
            if (step1) step1.style.display = 'block';
            if (step2) step2.style.display = 'none';
            if (typeof actualizarIndicadoresInst === 'function') actualizarIndicadoresInst();
            if (typeof actualizarBotonesInst === 'function') actualizarBotonesInst();
        }

        const campos = [
            'inst_representante_nombre',
            'inst_representante_documento',
            'inst_representante_telefono',
            'inst_representante_email',
            'inst_representante_cargo',
            'inst_representante_direccion'
        ];
        campos.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.value = '';
                el.disabled = false;
            }
        });

        const form = document.getElementById('formInstitucion');
        if (form) form.reset();
        const cargo = document.getElementById('inst_representante_cargo');
        if (cargo) cargo.value = 'Representante';
        const nombre = document.getElementById('inst_nombre');
        if (nombre) nombre.classList.remove('is-valid', 'is-invalid');

        const estadoSelect = document.getElementById('estado_id');
        const municipioSelect = document.getElementById('municipio_id');
        const parroquiaSelect = document.getElementById('parroquia_id');

        if (estadoSelect) {
            estadoSelect.value = '';
            cargarEstados('estado_id');
        }
        if (municipioSelect) {
            municipioSelect.innerHTML = '<option value="">Seleccionar estado primero</option>';
            municipioSelect.disabled = true;
        }
        if (parroquiaSelect) {
            parroquiaSelect.innerHTML = '<option value="">Seleccionar municipio primero</option>';
            parroquiaSelect.disabled = true;
        }
    };

    // ===========================
    // Funciones globales (modales)
    // ===========================
    window.abrirModalInstitucion = function() {
        cerrarModalDetalle();
        document.getElementById('modalInstitucionLabel').textContent = 'Nueva Institución';
        document.getElementById('formMethodInstitucion').value = 'POST';
        window.limpiarFormInstitucion();
        document.getElementById('institucionId').value = '';
        setTimeout(() => {
            cargarEstados('estado_id');
        }, 300);
        new bootstrap.Modal(document.getElementById('modalInstitucion')).show();
    };

    window.editarInstitucion = function(id) {
        cerrarModalDetalle();
        fetch(getUrl('institucion', id), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    const d = result.data;
                    document.getElementById('modalInstitucionLabel').textContent = 'Editar Institución';
                    document.getElementById('formMethodInstitucion').value = 'PUT';
                    document.getElementById('institucionId').value = d.id;
                    document.getElementById('inst_nombre').value = d.nombre || '';
                    document.getElementById('inst_informacion').value = d.informacion || '';

                    if (d.responsables && d.responsables.length > 0) {
                        const rep = d.responsables.find(r => r.cargo === 'Representante' && !r.departamento_id) || d.responsables[0];
                        document.getElementById('inst_representante_nombre').value = rep.nombre || '';
                        document.getElementById('inst_representante_documento').value = rep.documento || '';
                        document.getElementById('inst_representante_telefono').value = rep.telefono || '';
                        document.getElementById('inst_representante_email').value = rep.email || '';
                        document.getElementById('inst_representante_cargo').value = rep.cargo || 'Representante';
                        document.getElementById('inst_representante_direccion').value = rep.direccion || '';
                    }

                    if (typeof pasoActualInst !== 'undefined') {
                        pasoActualInst = 2;
                        const step1 = document.getElementById('stepInst1');
                        const step2 = document.getElementById('stepInst2');
                        if (step1) step1.style.display = 'none';
                        if (step2) step2.style.display = 'block';
                        if (typeof actualizarIndicadoresInst === 'function') actualizarIndicadoresInst();
                        if (typeof actualizarBotonesInst === 'function') actualizarBotonesInst();
                    }

                    const modal = new bootstrap.Modal(document.getElementById('modalInstitucion'));
                    modal.show();

                    modal._element.addEventListener('shown.bs.modal', function onShown() {
                        modal._element.removeEventListener('shown.bs.modal', onShown);
                        if (d.estado_id || d.municipio_id || d.parroquia_id) {
                            cargarUbicacionEdicion(d.estado_id, d.municipio_id, d.parroquia_id);
                        } else {
                            cargarEstados('estado_id');
                        }
                    });
                }
            });
    };

    window.abrirModalDepartamento = function() {
        cerrarModalDetalle();
        document.getElementById('modalDepartamentoLabel').textContent = 'Nuevo Departamento';
        document.getElementById('formMethodDepartamento').value = 'POST';
        window.limpiarFormDepartamento();
        document.getElementById('departamentoId').value = '';
        cargarInstitucionesEnSelect();
        new bootstrap.Modal(document.getElementById('modalDepartamento')).show();
    };

    window.editarDepartamento = function(id) {
        cerrarModalDetalle();
        fetch(getUrl('departamento', id), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    const d = result.data;
                    document.getElementById('modalDepartamentoLabel').textContent = 'Editar Departamento';
                    document.getElementById('formMethodDepartamento').value = 'PUT';
                    document.getElementById('departamentoId').value = d.id;

                    cargarInstitucionesEnSelect();

                    setTimeout(() => {
                        if (d.institucion_id) {
                            document.getElementById('sinInstitucion').checked = false;
                            document.getElementById('contenedorInstitucionDepto').style.display = 'block';
                            document.getElementById('contenedorCheckRepresentante').style.display = 'block';
                            document.getElementById('depto_institucion_id').value = d.institucion_id;
                        } else {
                            document.getElementById('sinInstitucion').checked = true;
                            document.getElementById('contenedorInstitucionDepto').style.display = 'none';
                            document.getElementById('contenedorCheckRepresentante').style.display = 'none';
                            document.getElementById('depto_institucion_id').value = '';
                        }

                        document.getElementById('depto_nombre').value = d.nombre || '';
                        document.getElementById('depto_ubicacion').value = d.ubicacion || '';
                        document.getElementById('depto_informacion').value = d.informacion || '';
                        document.getElementById('usarRepresentanteInstitucion').checked = false;

                        window.setCamposRepresentanteEstado(false);
                        window.resetCamposOcultosResponsable();

                        if (d.responsables && d.responsables.length > 0) {
                            const rep = d.responsables.find(r => r.cargo === 'Jefe de Departamento') || d.responsables[0];
                            document.getElementById('depto_representante_nombre').value = rep.nombre || '';
                            document.getElementById('depto_representante_documento').value = rep.documento || '';
                            document.getElementById('depto_representante_telefono').value = rep.telefono || '';
                            document.getElementById('depto_representante_email').value = rep.email || '';
                            document.getElementById('depto_representante_cargo').value = rep.cargo || 'Jefe de Departamento';
                            document.getElementById('depto_representante_direccion').value = rep.direccion || '';
                        }
                    }, 300);

                    new bootstrap.Modal(document.getElementById('modalDepartamento')).show();
                }
            });
    };

    window.abrirModalResponsable = function() {
        cerrarModalDetalle();
        document.getElementById('modalResponsableLabel').textContent = 'Nuevo Responsable';
        document.getElementById('formMethodResponsable').value = 'POST';
        window.limpiarFormResponsable();
        document.getElementById('responsableId').value = '';
        const origen = document.getElementById('responsableOrigen');
        if (origen) origen.value = 'directo';
        cargarInstitucionesEnSelect();
        new bootstrap.Modal(document.getElementById('modalResponsable')).show();
    };

    window.editarResponsable = function(id) {
        cerrarModalDetalle();
        fetch(getUrl('responsable', id), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    const d = result.data;
                    document.getElementById('modalResponsableLabel').textContent = 'Editar Responsable';
                    document.getElementById('formMethodResponsable').value = 'PUT';
                    document.getElementById('responsableId').value = d.id;
                    document.getElementById('resp_nombre').value = d.nombre || '';
                    document.getElementById('resp_documento').value = d.documento || '';
                    document.getElementById('resp_cargo').value = d.cargo || '';
                    document.getElementById('resp_telefono').value = d.telefono || '';
                    document.getElementById('resp_email').value = d.email || '';
                    document.getElementById('resp_direccion').value = d.direccion || '';

                    cargarInstitucionesEnSelect();
                    setTimeout(() => {
                        document.getElementById('resp_institucion_id').value = d.institucion_id || '';
                        cargarDepartamentosDeInstitucion();
                        setTimeout(() => {
                            document.getElementById('resp_departamento_id').value = d.departamento_id || '';
                        }, 300);
                    }, 300);

                    new bootstrap.Modal(document.getElementById('modalResponsable')).show();
                }
            });
    };

    // ===========================
    // Institución rápida desde departamento
    // ===========================
    window.abrirModalInstitucionDesdeDepartamento = function() {
        const form = document.getElementById('formInstitucionRapida');
        if (form) form.reset();
        const modal = new bootstrap.Modal(document.getElementById('modalInstitucionRapida'));
        modal.show();
        setTimeout(() => {
            const input = document.getElementById('inst_rapida_nombre');
            if (input) input.focus();
        }, 300);
    };

    // ===========================
    // Responsable desde institución
    // ===========================
    window.abrirModalResponsableDesdeInstitucion = function() {
        const form = document.getElementById('formResponsable');
        if (form) form.reset();

        const origen = document.getElementById('responsableOrigen');
        if (origen) origen.value = 'institucion';

        const selectInst = document.getElementById('resp_institucion_id');
        const instActual = document.getElementById('inst_nombre')?.value || '';
        if (selectInst && instActual) {
            const options = Array.from(selectInst.options);
            const match = options.find(opt => opt.text === instActual || opt.text.includes(instActual));
            if (match) selectInst.value = match.value;
        }

        const modal = new bootstrap.Modal(document.getElementById('modalResponsable'));
        modal.show();

        setTimeout(() => {
            const input = document.getElementById('resp_nombre');
            if (input) input.focus();
        }, 300);
    };

    // ===========================
    // Toggle estado
    // ===========================
    window.toggleEstado = function(tipo, id) {
        fetch(getUrl(tipo, id) + '/toggle-status', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                mostrarToast(result.message || 'Estado actualizado', 'success');
                if (tipo === 'institucion') cargarInstituciones();
                else if (tipo === 'departamento') cargarDepartamentos();
                else cargarResponsables();
            }
        });
    };

    // ===========================
    // Confirmar eliminar
    // ===========================
    window.confirmarEliminar = function(tipo, id, nombre, tieneDependencias) {
        elementoAEliminar = { tipo, id };
        document.getElementById('deleteNombre').textContent = nombre;

        const warning = document.getElementById('deleteWarning');
        const advertencia = document.getElementById('deleteAdvertencia');
        const btnEliminar = document.getElementById('btnConfirmarEliminar');

        if (warning) warning.style.display = 'none';
        if (advertencia) advertencia.style.display = 'none';
        if (btnEliminar) {
            btnEliminar.style.display = 'inline-block';
            btnEliminar.textContent = 'Eliminar';
            btnEliminar.className = 'btn btn-danger';
            btnEliminar.removeAttribute('data-confirmado');
        }

        if (tipo === 'institucion' && advertencia) {
            advertencia.style.display = 'block';
            advertencia.textContent = 'Se eliminarán también todos los departamentos y responsables asociados.';
        } else if (tipo === 'departamento' && advertencia) {
            advertencia.style.display = 'block';
            advertencia.textContent = 'Se eliminará el responsable asociado a este departamento.';
        }

        new bootstrap.Modal(document.getElementById('modalEliminar')).show();
    };

    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmarEliminar) {
        btnConfirmarEliminar.addEventListener('click', function() {
            if (!elementoAEliminar) return;
            const btn = this;

            if (btn.getAttribute('data-confirmado') !== 'true') {
                btn.textContent = '¿Confirmar?';
                btn.className = 'btn btn-warning';
                btn.setAttribute('data-confirmado', 'true');
                setTimeout(() => {
                    btn.textContent = 'Eliminar';
                    btn.className = 'btn btn-danger';
                    btn.removeAttribute('data-confirmado');
                }, 3000);
                return;
            }

            const { tipo, id } = elementoAEliminar;
            fetch(getUrl(tipo, id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(result => {
                bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
                if (result.success) {
                    mostrarToast(result.message || 'Eliminado exitosamente', 'success');
                    if (tipo === 'institucion') { cargarInstituciones(); cargarDepartamentos(); cargarResponsables(); }
                    else if (tipo === 'departamento') cargarDepartamentos();
                    else cargarResponsables();
                } else {
                    mostrarToast(result.message || 'Error al eliminar', 'error');
                }
                btn.textContent = 'Eliminar';
                btn.className = 'btn btn-danger';
                btn.removeAttribute('data-confirmado');
            });
        });
    }

    const modalEliminar = document.getElementById('modalEliminar');
    if (modalEliminar) {
        modalEliminar.addEventListener('hidden.bs.modal', function() {
            const btn = document.getElementById('btnConfirmarEliminar');
            if (btn) {
                btn.textContent = 'Eliminar';
                btn.className = 'btn btn-danger';
                btn.removeAttribute('data-confirmado');
            }
            elementoAEliminar = null;
        });
    }

    // ===========================
    // Guardar formularios
    // ===========================
    const formInstitucion = document.getElementById('formInstitucion');
    if (formInstitucion) {
        formInstitucion.addEventListener('submit', function(e) {
            e.preventDefault();

            const estadoId = document.getElementById('estado_id')?.value;
            const municipioId = document.getElementById('municipio_id')?.value;
            const parroquiaId = document.getElementById('parroquia_id')?.value;

            if (!estadoId) {
                mostrarToast('Seleccione un estado', 'warning');
                document.getElementById('estado_id')?.focus();
                return;
            }
            if (!municipioId) {
                mostrarToast('Seleccione un municipio', 'warning');
                document.getElementById('municipio_id')?.focus();
                return;
            }
            if (!parroquiaId) {
                mostrarToast('Seleccione una parroquia', 'warning');
                document.getElementById('parroquia_id')?.focus();
                return;
            }

            const formData = new FormData(this);
            const method = document.getElementById('formMethodInstitucion')?.value || 'POST';
            const id = document.getElementById('institucionId')?.value || '';
            let url = getUrlBase('institucion');
            if (method === 'PUT' && id) {
                url = getUrl('institucion', id);
                formData.append('_method', 'PUT');
            }

            formData.set('estado_id', estadoId);
            formData.set('municipio_id', municipioId);
            formData.set('parroquia_id', parroquiaId);

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalInstitucion')).hide();
                    mostrarToast(result.message || 'Guardado exitosamente', 'success');
                    cargarInstituciones();
                    cargarDepartamentos();
                    cargarInstitucionesEnSelect();
                } else {
                    if (result.errors) {
                        let mensaje = 'Error de validación:\n';
                        Object.values(result.errors).forEach(error => {
                            mensaje += '- ' + error.join('\n') + '\n';
                        });
                        mostrarToast(mensaje, 'error');
                    } else {
                        mostrarToast(result.message || 'Error al guardar', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarToast('Error de conexión al guardar', 'error');
            });
        });
    }

    // ===========================
    // Formulario Institución Rápida
    // ===========================
    const formInstitucionRapida = document.getElementById('formInstitucionRapida');
    if (formInstitucionRapida) {
        formInstitucionRapida.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';
            submitBtn.disabled = true;

            const formData = new FormData(this);

            fetch('/admin/instituciones', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const nuevaInstitucion = result.data;
                    bootstrap.Modal.getInstance(document.getElementById('modalInstitucionRapida')).hide();
                    mostrarToast('Institución "' + nuevaInstitucion.nombre + '" creada exitosamente', 'success');
                    actualizarSelectInstituciones(nuevaInstitucion);
                    cargarInstitucionesEnSelect();
                } else {
                    if (result.errors) {
                        let mensaje = 'Error de validación:\n';
                        Object.values(result.errors).forEach(error => {
                            mensaje += '- ' + error.join('\n') + '\n';
                        });
                        mostrarToast(mensaje, 'error');
                    } else {
                        mostrarToast(result.message || 'Error al crear la institución', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarToast('Error de conexión al crear la institución', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    function actualizarSelectInstituciones(nuevaInstitucion) {
        const select = document.getElementById('depto_institucion_id');
        if (!select) return;

        const existe = Array.from(select.options).some(opt => opt.value == nuevaInstitucion.id);
        if (!existe) {
            const option = document.createElement('option');
            option.value = nuevaInstitucion.id;
            option.textContent = nuevaInstitucion.nombre;
            select.appendChild(option);
        }

        select.value = nuevaInstitucion.id;
        const event = new Event('change');
        select.dispatchEvent(event);

        const radioOtra = document.querySelector('input[name="tipo_institucion"][value="otra"]');
        if (radioOtra && !radioOtra.checked) {
            const cardOtra = document.getElementById('cardOtra');
            if (cardOtra) cardOtra.click();
        }

        const contenedorOtra = document.getElementById('contenedorOtraInstitucion');
        if (contenedorOtra) contenedorOtra.style.display = 'block';
    }

    // ===========================
    // Formulario Departamento
    // ===========================
    const formDepartamento = document.getElementById('formDepartamento');
    if (formDepartamento) {
        formDepartamento.addEventListener('submit', function(e) {
            e.preventDefault();

            const camposDeshabilitados = this.querySelectorAll('input:disabled, textarea:disabled, select:disabled');
            camposDeshabilitados.forEach(campo => campo.disabled = false);

            const formData = new FormData(this);
            const method = document.getElementById('formMethodDepartamento')?.value || 'POST';
            const id = document.getElementById('departamentoId')?.value || '';
            let url = getUrlBase('departamento');
            if (method === 'PUT' && id) {
                url = getUrl('departamento', id);
                formData.append('_method', 'PUT');
            }

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalDepartamento')).hide();
                    mostrarToast(result.message || 'Guardado exitosamente', 'success');
                    cargarDepartamentos();
                } else {
                    mostrarToast(result.message || 'Error al guardar', 'error');
                }
            })
            .finally(() => {
                const usarRep = document.getElementById('usarRepresentanteInstitucion');
                if (usarRep && usarRep.checked) {
                    window.setCamposRepresentanteEstado(true);
                }
            });
        });
    }

    // ===========================
    // Formulario Responsable
    // ===========================
    const formResponsable = document.getElementById('formResponsable');
    if (formResponsable) {
        formResponsable.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const method = document.getElementById('formMethodResponsable')?.value || 'POST';
            const id = document.getElementById('responsableId')?.value || '';
            let url = getUrlBase('responsable');
            if (method === 'PUT' && id) {
                url = getUrl('responsable', id);
                formData.append('_method', 'PUT');
            }

            const origen = document.getElementById('responsableOrigen')?.value || 'directo';

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalResponsable')).hide();
                    mostrarToast(result.message || 'Guardado exitosamente', 'success');

                    if (origen === 'institucion') {
                        cargarInstitucionesEnSelect();
                        cargarResponsables();
                    } else {
                        cargarResponsables();
                    }
                } else {
                    mostrarToast(result.message || 'Error al guardar', 'error');
                }
            });
        });
    }

    // ===========================
    // Navegación desde detalle
    // ===========================
    window.navegarDesdeDetalle = function(tipo, id) {
        cerrarModalDetalle();
        setTimeout(() => { verDetalle(tipo, id); }, 300);
    };

    window.editarDesdeDetalle = function(tipo, id) {
        cerrarModalDetalle();
        setTimeout(() => {
            if (tipo === 'institucion') editarInstitucion(id);
            else if (tipo === 'departamento') editarDepartamento(id);
            else if (tipo === 'responsable') editarResponsable(id);
        }, 300);
    };

    // ===========================
    // Detalle enriquecido
    // ===========================
    window.verDetalle = function(tipo, id) {
        fetch(getUrl(tipo, id), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    const d = result.data;
                    let html = '';
                    if (tipo === 'institucion') {
                        const ubicacionCompleta = d.ubicacion_completa || 'No especificada';

                        html = `<div class="detail-header"><h5>${escapeHtml(d.nombre)}</h5><span class="badge ${d.activo ? 'badge-activo' : 'badge-inactivo'}">${d.activo ? 'Activa' : 'Inactiva'}</span></div>
                            <div class="detail-grid">
                                <div class="detail-item"><div class="detail-label">Representante</div><div class="detail-value">${escapeHtml(d.representante) || '—'}</div></div>
                                <div class="detail-item"><div class="detail-label">Ubicación</div><div class="detail-value">${escapeHtml(ubicacionCompleta)}</div></div>
                                <div class="detail-item" style="grid-column:1/-1"><div class="detail-label">Información</div><div class="detail-value">${escapeHtml(d.informacion) || 'Sin información'}</div></div>
                            </div>
                            <div class="detail-section"><div class="detail-section-title">${iconoDepartamento} Departamentos <span class="badge-count">${d.departamentos_count || 0}</span></div>`;
                        if (d.departamentos && d.departamentos.length > 0) {
                            html += '<ul class="detail-list">';
                            d.departamentos.forEach(depto => {
                                html += `<li class="detail-list-item"><div class="item-info"><span class="item-name">${depto.activo ? iconoCheck : iconoX} ${escapeHtml(depto.nombre)}</span><span class="item-sub">${escapeHtml(depto.representante) || 'Sin representante'} · ${depto.responsables_count || 0} resp.</span></div><div class="item-actions"><button class="btn-item-action" onclick="navegarDesdeDetalle('departamento',${depto.id})" title="Ver">${iconoVerSmall}</button><button class="btn-item-action" onclick="editarDesdeDetalle('departamento',${depto.id})" title="Editar">${iconoEditarSmall}</button></div></li>`;
                            });
                            html += '</ul>';
                        } else { html += '<div class="detail-empty">Sin departamentos</div>'; }
                        html += `</div><div class="detail-section"><div class="detail-section-title">${iconoResponsable} Responsables <span class="badge-count">${d.responsables_count || 0}</span></div>`;
                        if (d.responsables && d.responsables.length > 0) {
                            html += '<ul class="detail-list">';
                            d.responsables.forEach(resp => {
                                html += `<li class="detail-list-item"><div class="item-info"><span class="item-name">${escapeHtml(resp.nombre)}</span><span class="item-sub">${escapeHtml(resp.cargo) || 'Sin cargo'} · ${resp.departamento ? escapeHtml(resp.departamento.nombre) : 'Sin depto.'}</span></div><div class="item-actions"><button class="btn-item-action" onclick="navegarDesdeDetalle('responsable',${resp.id})" title="Ver">${iconoVerSmall}</button><button class="btn-item-action" onclick="editarDesdeDetalle('responsable',${resp.id})" title="Editar">${iconoEditarSmall}</button></div></li>`;
                            });
                            html += '</ul>';
                        } else { html += '<div class="detail-empty">Sin responsables</div>'; }
                        html += `</div><div class="detail-actions-bar"><button class="btn btn-sm btn-outline-primary-dark" onclick="editarDesdeDetalle('institucion',${d.id})">${iconoEditar} Editar</button><button style="color:#fff;" class="btn btn-sm btn-primary-dark" onclick="bootstrap.Modal.getInstance(document.getElementById('modalDetalle')).hide(); abrirModalDepartamento();">${iconoNuevo} Nuevo Depto.</button><button style="color:#fff;" class="btn btn-sm btn-primary-dark" onclick="bootstrap.Modal.getInstance(document.getElementById('modalDetalle')).hide(); abrirModalResponsable();">${iconoNuevo} Nuevo Resp.</button></div>`;
                    } else if (tipo === 'departamento') {
                        html = `<div class="detail-header"><h5>${escapeHtml(d.nombre)}</h5><span class="badge ${d.activo ? 'badge-activo' : 'badge-inactivo'}">${d.activo ? 'Activo' : 'Inactivo'}</span></div>
                            <div class="detail-grid">
                                <div class="detail-item"><div class="detail-label">Institución</div><div class="detail-value">${d.institucion ? escapeHtml(d.institucion.nombre) : 'Sin institución'}</div></div>
                                <div class="detail-item"><div class="detail-label">Representante</div><div class="detail-value">${escapeHtml(d.representante) || '—'}</div></div>
                                <div class="detail-item"><div class="detail-label">Ubicación</div><div class="detail-value">${escapeHtml(d.ubicacion) || '—'}</div></div>
                                <div class="detail-item" style="grid-column:1/-1"><div class="detail-label">Información</div><div class="detail-value">${escapeHtml(d.informacion) || 'Sin información'}</div></div>
                            </div>
                            <div class="detail-section"><div class="detail-section-title">${iconoResponsable} Responsables <span class="badge-count">${d.responsables_count || 0}</span></div>`;
                        if (d.responsables && d.responsables.length > 0) {
                            html += '<ul class="detail-list">';
                            d.responsables.forEach(resp => {
                                html += `<li class="detail-list-item"><div class="item-info"><span class="item-name">${escapeHtml(resp.nombre)}</span><span class="item-sub">${escapeHtml(resp.cargo) || 'Sin cargo'} · ${escapeHtml(resp.telefono) || 'Sin tel.'}</span></div><div class="item-actions"><button class="btn-item-action" onclick="navegarDesdeDetalle('responsable',${resp.id})" title="Ver">${iconoVerSmall}</button><button class="btn-item-action" onclick="editarDesdeDetalle('responsable',${resp.id})" title="Editar">${iconoEditarSmall}</button></div></li>`;
                            });
                            html += '</ul>';
                        } else { html += '<div class="detail-empty">Sin responsables</div>'; }
                        html += `</div><div class="detail-actions-bar"><button class="btn btn-sm btn-outline-primary-dark" onclick="editarDesdeDetalle('departamento',${d.id})">${iconoEditar} Editar</button><button style="color:white;" class="btn btn-sm btn-primary-dark" onclick="bootstrap.Modal.getInstance(document.getElementById('modalDetalle')).hide(); abrirModalResponsable();">${iconoNuevo} Nuevo Resp.</button></div>`;
                    } else if (tipo === 'responsable') {
                        html = `<div class="detail-header"><h5>${escapeHtml(d.nombre)}</h5><span class="badge ${d.activo ? 'badge-activo' : 'badge-inactivo'}">${d.activo ? 'Activo' : 'Inactivo'}</span></div>
                            <div class="detail-grid">
                                <div class="detail-item"><div class="detail-label">Documento</div><div class="detail-value">${escapeHtml(d.documento) || '—'}</div></div>
                                <div class="detail-item"><div class="detail-label">Cargo</div><div class="detail-value">${escapeHtml(d.cargo) || '—'}</div></div>
                                <div class="detail-item"><div class="detail-label">Teléfono</div><div class="detail-value">${escapeHtml(d.telefono) || '—'}</div></div>
                                <div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">${escapeHtml(d.email) || '—'}</div></div>
                                <div class="detail-item"><div class="detail-label">Institución</div><div class="detail-value">${d.institucion ? escapeHtml(d.institucion.nombre) : '—'}</div></div>
                                <div class="detail-item"><div class="detail-label">Departamento</div><div class="detail-value">${d.departamento ? escapeHtml(d.departamento.nombre) : 'Sin depto.'}</div></div>
                                <div class="detail-item" style="grid-column:1/-1"><div class="detail-label">Dirección</div><div class="detail-value">${escapeHtml(d.direccion) || '—'}</div></div>
                            </div>
                            <div class="detail-actions-bar">
                                <button class="btn btn-sm btn-outline-primary-dark" onclick="editarDesdeDetalle('responsable',${d.id})">${iconoEditar} Editar</button>
                                ${d.institucion ? `<button class="btn btn-sm btn-outline-primary-dark" onclick="navegarDesdeDetalle('institucion',${d.institucion.id})">${iconoInstitucion} Ver Institución</button>` : ''}
                                ${d.departamento ? `<button class="btn btn-sm btn-outline-primary-dark" onclick="navegarDesdeDetalle('departamento',${d.departamento.id})">${iconoDepartamento} Ver Departamento</button>` : ''}
                            </div>`;
                    }
                    document.getElementById('modalDetalleLabel').textContent = 'Detalle de ' + tipo;
                    document.getElementById('detalleContenido').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('modalDetalle')).show();
                }
            })
            .catch(error => console.error('Error:', error));
    };

    // ===========================
    // Vista de Árbol Jerárquico
    // ===========================
    function cargarArbol() {
        const buscar = document.getElementById('buscarArbol')?.value?.toLowerCase() || '';
        const contenedor = document.getElementById('arbolContenedor');
        if (!contenedor) return;

        contenedor.innerHTML = `<div class="loading-spinner"><svg class="spinner-icon" viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:20px;height:20px"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>Cargando árbol...</div>`;

        fetch('/admin/instituciones?todos=1', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(response => {
                const instituciones = response.data || response;
                contenedor.innerHTML = '<div class="arbol-container"></div>';
                const arbolContainer = contenedor.querySelector('.arbol-container');
                if (instituciones && instituciones.length > 0) {
                    let cargadas = 0;
                    instituciones.forEach(inst => {
                        fetch(getUrl('institucion', inst.id), { headers: { 'Accept': 'application/json' } })
                            .then(r => r.json())
                            .then(result => {
                                if (result.success) {
                                    const d = result.data;
                                    const coincide = !buscar ||
                                        d.nombre.toLowerCase().includes(buscar) ||
                                        (d.departamentos && d.departamentos.some(depto => depto.nombre.toLowerCase().includes(buscar))) ||
                                        (d.responsables && d.responsables.some(resp => resp.nombre.toLowerCase().includes(buscar)));
                                    if (coincide || !buscar) arbolContainer.innerHTML += renderNodoInstitucion(d);
                                }
                                cargadas++;
                                if (cargadas === instituciones.length && arbolContainer.innerHTML === '') {
                                    arbolContainer.innerHTML = '<p class="text-center py-4 text-muted">No se encontraron resultados</p>';
                                }
                            });
                    });
                } else {
                    contenedor.innerHTML = '<p class="text-center py-4 text-muted">No hay instituciones registradas</p>';
                }
            })
            .catch(e => {
                console.error('Error:', e);
                contenedor.innerHTML = '<p class="text-center py-4 text-danger">Error al cargar el árbol</p>';
            });
    }

    function renderNodoInstitucion(d) {
        const deptosCount = d.departamentos ? d.departamentos.length : 0;
        const respCount = d.responsables ? d.responsables.length : 0;
        const respDirectos = d.responsables ? d.responsables.filter(r => !r.departamento_id) : [];
        const ubicacionStr = d.ubicacion_completa || '';

        let html = `<div class="arbol-nodo arbol-raiz"><div class="arbol-nodo-header" onclick="toggleNodo(this)"><span class="arbol-toggle">▼</span><span class="arbol-icon">${iconoInstitucion}</span><span class="arbol-nombre">${escapeHtml(d.nombre)}</span><span class="arbol-sub">${escapeHtml(ubicacionStr)}</span><span class="arbol-badge badge-activo">${deptosCount} deptos.</span><span class="arbol-badge badge-activo">${respCount} resp.</span><span class="badge ${d.activo ? 'badge-activo' : 'badge-inactivo'} ms-2">${d.activo ? 'Activa' : 'Inactiva'}</span><div class="arbol-acciones"><button class="btn-item-action" onclick="event.stopPropagation(); verDetalle('institucion',${d.id})" title="Ver">${iconoVerSmall}</button><button class="btn-item-action" onclick="event.stopPropagation(); editarInstitucion(${d.id})" title="Editar">${iconoEditarSmall}</button></div></div><div class="arbol-hijos">`;
        if (d.departamentos && d.departamentos.length > 0) {
            d.departamentos.forEach(depto => {
                const respDepto = d.responsables ? d.responsables.filter(r => r.departamento_id === depto.id) : [];
                html += `<div class="arbol-nodo arbol-rama"><div class="arbol-nodo-header" onclick="toggleNodo(this)"><span class="arbol-toggle">▼</span><span class="arbol-icon">${iconoDepartamento}</span><span class="arbol-nombre">${escapeHtml(depto.nombre)}</span><span class="arbol-badge badge-activo">${respDepto.length} resp.</span><span class="badge ${depto.activo ? 'badge-activo' : 'badge-inactivo'} ms-2">${depto.activo ? 'Activo' : 'Inactivo'}</span><div class="arbol-acciones"><button class="btn-item-action" onclick="event.stopPropagation(); verDetalle('departamento',${depto.id})" title="Ver">${iconoVerSmall}</button><button class="btn-item-action" onclick="event.stopPropagation(); editarDepartamento(${depto.id})" title="Editar">${iconoEditarSmall}</button></div></div><div class="arbol-hijos">`;
                respDepto.forEach(resp => {
                    html += `<div class="arbol-nodo arbol-hoja"><div class="arbol-nodo-header"><span class="arbol-toggle" style="visibility:hidden">▼</span><span class="arbol-icon">${iconoResponsable}</span><span class="arbol-nombre">${escapeHtml(resp.nombre)}</span><span class="arbol-sub">${escapeHtml(resp.cargo) || ''}</span><div class="arbol-acciones"><button class="btn-item-action" onclick="event.stopPropagation(); verDetalle('responsable',${resp.id})" title="Ver">${iconoVerSmall}</button><button class="btn-item-action" onclick="event.stopPropagation(); editarResponsable(${resp.id})" title="Editar">${iconoEditarSmall}</button></div></div></div>`;
                });
                html += '</div></div>';
            });
        }
        if (respDirectos.length > 0) {
            html += `<div class="arbol-nodo arbol-rama-directa"><div class="arbol-nodo-header" onclick="toggleNodo(this)"><span class="arbol-toggle">▼</span><span class="arbol-icon">${iconoResponsable}</span><span class="arbol-nombre">Responsables directos</span><span class="arbol-badge badge-activo">${respDirectos.length}</span></div><div class="arbol-hijos">`;
            respDirectos.forEach(resp => {
                html += `<div class="arbol-nodo arbol-hoja"><div class="arbol-nodo-header"><span class="arbol-toggle" style="visibility:hidden">▼</span><span class="arbol-icon">${iconoResponsable}</span><span class="arbol-nombre">${escapeHtml(resp.nombre)}</span><span class="arbol-sub">${escapeHtml(resp.cargo) || ''}</span><div class="arbol-acciones"><button class="btn-item-action" onclick="event.stopPropagation(); verDetalle('responsable',${resp.id})" title="Ver">${iconoVerSmall}</button><button class="btn-item-action" onclick="event.stopPropagation(); editarResponsable(${resp.id})" title="Editar">${iconoEditarSmall}</button></div></div></div>`;
            });
            html += '</div></div>';
        }
        html += '</div></div>';
        return html;
    }

    window.toggleNodo = function(header) {
        const hijos = header.nextElementSibling;
        const toggle = header.querySelector('.arbol-toggle');
        if (hijos && hijos.classList.contains('arbol-hijos')) {
            hijos.classList.toggle('collapsed');
            if (toggle) toggle.classList.toggle('collapsed');
        }
    };

    window.expandirTodo = function() {
        document.querySelectorAll('.arbol-hijos').forEach(h => h.classList.remove('collapsed'));
        document.querySelectorAll('.arbol-toggle').forEach(t => t.classList.remove('collapsed'));
    };

    window.colapsarTodo = function() {
        document.querySelectorAll('.arbol-hijos').forEach(h => h.classList.add('collapsed'));
        document.querySelectorAll('.arbol-toggle').forEach(t => t.classList.add('collapsed'));
    };

    // ===========================
    // Validar paso institución
    // ===========================
    function validarPasoInst(paso) {
        switch(paso) {
            case 1:
                const nombre = document.getElementById('inst_nombre');
                const informacion = document.getElementById('inst_informacion');

                if (!nombre || !nombre.value.trim()) {
                    mostrarToast('Ingrese el nombre de la institución', 'warning');
                    if (nombre) nombre.focus();
                    return false;
                }

                if (nombre.value.trim().length < 3) {
                    mostrarToast('El nombre debe tener al menos 3 caracteres', 'warning');
                    if (nombre) nombre.focus();
                    return false;
                }

                if (!informacion || !informacion.value.trim()) {
                    mostrarToast('Ingrese información de la institución', 'warning');
                    if (informacion) informacion.focus();
                    return false;
                }

                const estadoId = document.getElementById('estado_id')?.value;
                const municipioId = document.getElementById('municipio_id')?.value;
                const parroquiaId = document.getElementById('parroquia_id')?.value;

                if (!estadoId) {
                    mostrarToast('Seleccione un estado', 'warning');
                    document.getElementById('estado_id')?.focus();
                    return false;
                }

                if (!municipioId) {
                    mostrarToast('Seleccione un municipio', 'warning');
                    document.getElementById('municipio_id')?.focus();
                    return false;
                }

                if (!parroquiaId) {
                    mostrarToast('Seleccione una parroquia', 'warning');
                    document.getElementById('parroquia_id')?.focus();
                    return false;
                }

                return true;

            case 2:
                const nombreRep = document.getElementById('inst_representante_nombre');
                const documento = document.getElementById('inst_representante_documento');
                const telefono = document.getElementById('inst_representante_telefono');
                const cargo = document.getElementById('inst_representante_cargo');

                if (!nombreRep || !nombreRep.value.trim()) {
                    mostrarToast('Ingrese el nombre del representante', 'warning');
                    if (nombreRep) nombreRep.focus();
                    return false;
                }

                if (!documento || !documento.value.trim()) {
                    mostrarToast('Ingrese el documento del representante', 'warning');
                    if (documento) documento.focus();
                    return false;
                }

                if (documento.value.trim().length < 8) {
                    mostrarToast('El documento debe tener al menos 8 caracteres', 'warning');
                    if (documento) documento.focus();
                    return false;
                }

                if (!telefono || !telefono.value.trim()) {
                    mostrarToast('Ingrese el teléfono del representante', 'warning');
                    if (telefono) telefono.focus();
                    return false;
                }

                if (!cargo || !cargo.value.trim()) {
                    mostrarToast('Ingrese el cargo del representante', 'warning');
                    if (cargo) cargo.focus();
                    return false;
                }

                return true;

            default:
                return true;
        }
    }

    // ===========================
    // EVENTOS RESPONSABLE - DEPARTAMENTOS POR INSTITUCIÓN
    // ===========================
    const respInstitucionSelect = document.getElementById('resp_institucion_id');
    if (respInstitucionSelect) {
        respInstitucionSelect.addEventListener('change', function() {
            const selectDepto = document.getElementById('resp_departamento_id');
            if (!selectDepto) return;

            const id = this.value;
            selectDepto.innerHTML = '<option value="">Sin departamento</option>';

            if (!id) return;

            fetch(`/admin/departamentos/por-institucion/${id}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(result => {
                if (result.success && result.data) {
                    result.data.forEach(depto => {
                        selectDepto.innerHTML += `<option value="${depto.id}">${escapeHtml(depto.nombre)}</option>`;
                    });
                }
            })
            .catch(e => console.error('Error:', e));
        });
    }

    // ===========================
    // WIZARD DE 3 PASOS - DEPARTAMENTOS
    // ===========================

    let pasoActual = 1;
    const totalPasos = 3;

    window.cambiarPaso = function(direccion) {
        const nuevoPaso = pasoActual + direccion;

        if (nuevoPaso < 1 || nuevoPaso > totalPasos) return;

        if (direccion === 1 && !validarPaso(pasoActual)) {
            return;
        }

        const stepActual = document.getElementById(`step${pasoActual}`);
        if (stepActual) stepActual.style.display = 'none';

        pasoActual = nuevoPaso;

        const stepNuevo = document.getElementById(`step${pasoActual}`);
        if (stepNuevo) stepNuevo.style.display = 'block';

        actualizarIndicadores();
        actualizarBotones();
    };

    function validarPaso(paso) {
        switch(paso) {
            case 1:
                const tipoInstitucion = document.querySelector('input[name="tipo_institucion"]:checked');
                if (!tipoInstitucion) {
                    mostrarToast('Seleccione un tipo de institución', 'warning');
                    return false;
                }

                if (tipoInstitucion.value === 'otra') {
                    const selectInst = document.getElementById('depto_institucion_id');
                    if (!selectInst || !selectInst.value) {
                        mostrarToast('Seleccione una institución', 'warning');
                        return false;
                    }
                }
                return true;

            case 2:
                const nombre = document.getElementById('depto_nombre');
                const ubicacion = document.getElementById('depto_ubicacion');
                const informacion = document.getElementById('depto_informacion');

                if (!nombre || !nombre.value.trim()) {
                    mostrarToast('Ingrese el nombre del departamento', 'warning');
                    if (nombre) nombre.focus();
                    return false;
                }

                if (nombre.value.trim().length < 3) {
                    mostrarToast('El nombre debe tener al menos 3 caracteres', 'warning');
                    if (nombre) nombre.focus();
                    return false;
                }

                if (!ubicacion || !ubicacion.value.trim()) {
                    mostrarToast('Ingrese la ubicación', 'warning');
                    if (ubicacion) ubicacion.focus();
                    return false;
                }

                if (!informacion || !informacion.value.trim()) {
                    mostrarToast('Ingrese información del departamento', 'warning');
                    if (informacion) informacion.focus();
                    return false;
                }

                return true;

            case 3:
                const nombreRep = document.getElementById('depto_representante_nombre');
                const documento = document.getElementById('depto_representante_documento');
                const telefono = document.getElementById('depto_representante_telefono');
                const cargo = document.getElementById('depto_representante_cargo');

                if (!nombreRep || !nombreRep.value.trim()) {
                    mostrarToast('Ingrese el nombre del representante', 'warning');
                    if (nombreRep) nombreRep.focus();
                    return false;
                }

                if (!documento || !documento.value.trim()) {
                    mostrarToast('Ingrese el documento del representante', 'warning');
                    if (documento) documento.focus();
                    return false;
                }

                if (documento.value.trim().length < 8) {
                    mostrarToast('El documento debe tener al menos 8 caracteres', 'warning');
                    if (documento) documento.focus();
                    return false;
                }

                if (!telefono || !telefono.value.trim()) {
                    mostrarToast('Ingrese el teléfono del representante', 'warning');
                    if (telefono) telefono.focus();
                    return false;
                }

                if (!cargo || !cargo.value.trim()) {
                    mostrarToast('Ingrese el cargo del representante', 'warning');
                    if (cargo) cargo.focus();
                    return false;
                }

                return true;

            default:
                return true;
        }
    }

    function actualizarIndicadores() {
        for (let i = 1; i <= totalPasos; i++) {
            const circle = document.querySelector(`#stepLabel${i} .step-circle`);
            if (!circle) continue;
            circle.classList.remove('active', 'completed');

            if (i < pasoActual) {
                circle.classList.add('completed');
                circle.textContent = '✓';
            } else if (i === pasoActual) {
                circle.classList.add('active');
                circle.textContent = i;
            } else {
                circle.textContent = i;
            }
        }

        for (let i = 1; i <= totalPasos; i++) {
            const label = document.getElementById(`stepLabel${i}`);
            if (!label) continue;
            if (i <= pasoActual) {
                label.style.color = '#1e3c72';
            } else {
                label.style.color = '#adb5bd';
            }
        }

        const progress = ((pasoActual - 1) / (totalPasos - 1)) * 100;
        const progressBar = document.getElementById('progressBar');
        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }

        const indicator = document.getElementById('stepIndicator');
        if (indicator) {
            indicator.textContent = `Paso ${pasoActual} de ${totalPasos}`;
        }
    }

    function actualizarBotones() {
        const btnAnterior = document.getElementById('btnAnterior');
        const btnSiguiente = document.getElementById('btnSiguiente');
        const btnGuardar = document.getElementById('btnGuardar');

        if (btnAnterior) {
            btnAnterior.style.display = pasoActual === 1 ? 'none' : 'inline-block';
        }

        if (pasoActual === totalPasos) {
            if (btnSiguiente) btnSiguiente.style.display = 'none';
            if (btnGuardar) btnGuardar.style.display = 'inline-block';
        } else {
            if (btnSiguiente) btnSiguiente.style.display = 'inline-block';
            if (btnGuardar) btnGuardar.style.display = 'none';
        }
    }

    // ===========================
    // WIZARD DE 2 PASOS - INSTITUCIÓN
    // ===========================

    let pasoActualInst = 1;
    const totalPasosInst = 2;

    window.cambiarPasoInst = function(direccion) {
        const nuevoPaso = pasoActualInst + direccion;

        if (nuevoPaso < 1 || nuevoPaso > totalPasosInst) return;

        if (direccion === 1 && !validarPasoInst(pasoActualInst)) {
            return;
        }

        const stepActual = document.getElementById(`stepInst${pasoActualInst}`);
        if (stepActual) stepActual.style.display = 'none';

        pasoActualInst = nuevoPaso;

        const stepNuevo = document.getElementById(`stepInst${pasoActualInst}`);
        if (stepNuevo) stepNuevo.style.display = 'block';

        actualizarIndicadoresInst();
        actualizarBotonesInst();
    };

    function actualizarIndicadoresInst() {
        for (let i = 1; i <= totalPasosInst; i++) {
            const circle = document.querySelector(`#stepLabelInst${i} .step-circle`);
            if (!circle) continue;
            circle.classList.remove('active', 'completed');

            if (i < pasoActualInst) {
                circle.classList.add('completed');
                circle.textContent = '✓';
            } else if (i === pasoActualInst) {
                circle.classList.add('active');
                circle.textContent = i;
            } else {
                circle.textContent = i;
            }
        }

        for (let i = 1; i <= totalPasosInst; i++) {
            const label = document.getElementById(`stepLabelInst${i}`);
            if (!label) continue;
            if (i <= pasoActualInst) {
                label.style.color = '#1e3c72';
            } else {
                label.style.color = '#adb5bd';
            }
        }

        const progress = ((pasoActualInst - 1) / (totalPasosInst - 1)) * 100;
        const progressBar = document.getElementById('progressBarInst');
        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }

        const indicator = document.getElementById('stepIndicatorInst');
        if (indicator) {
            indicator.textContent = `Paso ${pasoActualInst} de ${totalPasosInst}`;
        }
    }

    function actualizarBotonesInst() {
        const btnAnterior = document.getElementById('btnAnteriorInst');
        const btnSiguiente = document.getElementById('btnSiguienteInst');
        const btnGuardar = document.getElementById('btnGuardarInst');

        if (btnAnterior) {
            btnAnterior.style.display = pasoActualInst === 1 ? 'none' : 'inline-block';
        }

        if (pasoActualInst === totalPasosInst) {
            if (btnSiguiente) btnSiguiente.style.display = 'none';
            if (btnGuardar) btnGuardar.style.display = 'inline-block';
        } else {
            if (btnSiguiente) btnSiguiente.style.display = 'inline-block';
            if (btnGuardar) btnGuardar.style.display = 'none';
        }
    }

    // ===========================
    // EVENTOS DE TARJETAS DE INSTITUCIÓN (DEPARTAMENTO)
    // ===========================
    function initWizardEvents() {
        const cardGobernacion = document.getElementById('cardGobernacion');
        const cardOtra = document.getElementById('cardOtra');
        const radioGobernacion = document.querySelector('input[name="tipo_institucion"][value="gobernacion"]');
        const radioOtra = document.querySelector('input[name="tipo_institucion"][value="otra"]');
        const contenedorOtra = document.getElementById('contenedorOtraInstitucion');
        const selectInstitucion = document.getElementById('depto_institucion_id');

        if (!cardGobernacion || !cardOtra) return;

        cardGobernacion.addEventListener('click', function() {
            if (radioGobernacion) radioGobernacion.checked = true;
            this.classList.add('active');
            cardOtra.classList.remove('active');
            this.style.borderColor = '#1e3c72';
            cardOtra.style.borderColor = '#dee2e6';
            if (contenedorOtra) contenedorOtra.style.display = 'none';

            if (selectInstitucion) {
                const gobOption = Array.from(selectInstitucion.options).find(opt => opt.text.includes('Gobernación'));
                if (gobOption) selectInstitucion.value = gobOption.value;
            }
        });

        cardOtra.addEventListener('click', function() {
            if (radioOtra) radioOtra.checked = true;
            this.classList.add('active');
            cardGobernacion.classList.remove('active');
            this.style.borderColor = '#1e3c72';
            cardGobernacion.style.borderColor = '#dee2e6';
            if (contenedorOtra) contenedorOtra.style.display = 'block';

            if (selectInstitucion && selectInstitucion.value) {
                const selectedOpt = selectInstitucion.options[selectInstitucion.selectedIndex];
                if (selectedOpt && selectedOpt.text.includes('Gobernación')) {
                    selectInstitucion.value = '';
                }
            }
        });

        if (selectInstitucion) {
            selectInstitucion.addEventListener('change', function() {
                if (this.value) {
                    const selectedOpt = this.options[this.selectedIndex];
                    if (selectedOpt && selectedOpt.text.includes('Gobernación')) {
                        if (cardGobernacion) cardGobernacion.click();
                    } else {
                        if (!radioOtra || !radioOtra.checked) {
                            if (cardOtra) cardOtra.click();
                        }
                    }
                }
            });
        }
    }

    // ===========================
    // INICIALIZACIÓN
    // ===========================
    function init() {
        console.log('Inicializando módulo de entidades...');

        // Inicializar wizard events
        initWizardEvents();

        // Inicializar selects de ubicación
        initUbicacionSelects();

        // Cargar instituciones al inicio
        cargarInstituciones();

        // Cargar departamentos al inicio
        cargarDepartamentos();

        // Cargar responsables al inicio
        cargarResponsables();

        // Cargar instituciones en selects
        cargarInstitucionesEnSelect();

        // Cargar árbol
        cargarArbol();

        // ============================================
        // FILTROS EN TIEMPO REAL
        // ============================================

        // Event listeners para búsqueda y filtros de instituciones
        const buscarInstituciones = document.getElementById('buscarInstituciones');
        if (buscarInstituciones) {
            buscarInstituciones.addEventListener('input', debounce(cargarInstituciones, 300));
            buscarInstituciones.addEventListener('search', cargarInstituciones);
        }

        const filtroEstadoInstituciones = document.getElementById('filtroEstadoInstituciones');
        if (filtroEstadoInstituciones) {
            filtroEstadoInstituciones.addEventListener('change', cargarInstituciones);
        }

        // Event listeners para búsqueda y filtros de departamentos
        const buscarDepartamentos = document.getElementById('buscarDepartamentos');
        if (buscarDepartamentos) {
            buscarDepartamentos.addEventListener('input', debounce(cargarDepartamentos, 300));
            buscarDepartamentos.addEventListener('search', cargarDepartamentos);
        }

        const filtroInstitucionDepartamentos = document.getElementById('filtroInstitucionDepartamentos');
        if (filtroInstitucionDepartamentos) {
            filtroInstitucionDepartamentos.addEventListener('change', cargarDepartamentos);
        }

        // Event listeners para búsqueda y filtros de responsables
        const buscarResponsables = document.getElementById('buscarResponsables');
        if (buscarResponsables) {
            buscarResponsables.addEventListener('input', debounce(cargarResponsables, 300));
            buscarResponsables.addEventListener('search', cargarResponsables);
        }

        const filtroInstitucionResponsables = document.getElementById('filtroInstitucionResponsables');
        if (filtroInstitucionResponsables) {
            filtroInstitucionResponsables.addEventListener('change', cargarResponsables);
        }

        // Event listeners para el árbol
        const buscarArbol = document.getElementById('buscarArbol');
        if (buscarArbol) {
            buscarArbol.addEventListener('input', debounce(cargarArbol, 300));
            buscarArbol.addEventListener('search', cargarArbol);
        }

        // ============================================
        // RECARGAR AL CAMBIAR DE TAB
        // ============================================
        document.querySelectorAll('#entidadesTabs .nav-link').forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                var target = e.target.getAttribute('data-bs-target');
                if (target === '#instituciones') {
                    cargarInstituciones();
                } else if (target === '#departamentos') {
                    cargarDepartamentos();
                } else if (target === '#responsables') {
                    cargarResponsables();
                } else if (target === '#arbol') {
                    cargarArbol();
                }
            });
        });

        // Event listeners para los modales
        const modalDepartamento = document.getElementById('modalDepartamento');
        if (modalDepartamento) {
            modalDepartamento.addEventListener('shown.bs.modal', function() {
                window.limpiarFormDepartamento();
            });
        }

        const modalInstitucion = document.getElementById('modalInstitucion');
        if (modalInstitucion) {
            modalInstitucion.addEventListener('shown.bs.modal', function() {
                window.limpiarFormInstitucion();
                setTimeout(() => {
                    cargarEstados('estado_id');
                }, 300);
            });
        }
    }

    // ===========================
    // EXPONER FUNCIONES GLOBALMENTE
    // ===========================
    window.cambiarPaso = window.cambiarPaso || cambiarPaso;
    window.validarPaso = validarPaso;
    window.actualizarIndicadores = actualizarIndicadores;
    window.actualizarBotones = actualizarBotones;
    window.initWizardEvents = initWizardEvents;
    window.cambiarPasoInst = window.cambiarPasoInst || cambiarPasoInst;
    window.validarPasoInst = validarPasoInst;
    window.actualizarIndicadoresInst = actualizarIndicadoresInst;
    window.actualizarBotonesInst = actualizarBotonesInst;

    // Exponer funciones de ubicación globalmente
    window.cargarEstados = cargarEstados;
    window.cargarMunicipios = cargarMunicipios;
    window.cargarParroquias = cargarParroquias;
    window.cargarUbicacionEdicion = cargarUbicacionEdicion;

    // Exponer funciones de carga de tablas globalmente
    window.cargarInstituciones = cargarInstituciones;
    window.cargarDepartamentos = cargarDepartamentos;
    window.cargarResponsables = cargarResponsables;
    window.cargarArbol = cargarArbol;

    // Ejecutar inicialización
    init();
});