// resources/js/admin-entidades.js

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let elementoAEliminar = null;

    // ===========================
    // HELPERS
    // ===========================
    function getUrl(tipo, id) {
        const plurales = { 'institucion': 'instituciones', 'departamento': 'departamentos', 'responsable': 'responsables' };
        return `/admin/${plurales[tipo] || (tipo + 's')}/${id}`;
    }

    function getUrlBase(tipo) {
        const plurales = { 'institucion': 'instituciones', 'departamento': 'departamentos', 'responsable': 'responsables' };
        return `/admin/${plurales[tipo] || (tipo + 's')}`;
    }

    function debounce(fn, wait) {
        let t;
        return function(...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); };
    }

    function escapeHtml(text) {
        if (!text) return '';
        const d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function resaltarTexto(texto, buscar) {
        if (!buscar || !texto) return escapeHtml(texto);
        const escaped = escapeHtml(texto);
        const regex = new RegExp(`(${buscar.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return escaped.replace(regex, '<span class="highlight">$1</span>');
    }

    function mostrarToast(mensaje, tipo = 'success') {
        const colores = { success: '#1e7e34', error: '#c5221f', warning: '#f6c23e', info: '#1e3c72' };
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; top: 20px; right: 20px;
            background: ${colores[tipo] || colores.success}; color: white;
            padding: 14px 20px; border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2); z-index: 99999;
            font-weight: 500; font-size: 0.9rem;
            max-width: 400px; white-space: pre-line;
        `;
        toast.textContent = mensaje;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 3500);
    }

    // ===========================
    // CARGAR INSTITUCIONES
    // ===========================
    function cargarInstituciones() {
        const buscar = document.getElementById('buscarInstituciones')?.value || '';
        const tabla = document.getElementById('tablaInstituciones');
        if (tabla) {
            tabla.innerHTML = `<div class="loading-spinner">
                <svg class="spinner-icon" viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:20px;height:20px;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                Cargando...</div>`;
        }

        fetch(`/admin/instituciones?todos=1&buscar=${encodeURIComponent(buscar)}&_t=${Date.now()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        })
        .then(r => r.json())
        .then(response => {
            const data = response.data || [];
            renderTablaInstituciones(data, buscar);
        })
        .catch(err => {
            console.error(err);
            if (tabla) tabla.innerHTML = '<p class="text-center py-4 text-danger">Error al cargar</p>';
        });
    }

    function renderTablaInstituciones(data, buscar) {
        const tabla = document.getElementById('tablaInstituciones');
        if (!tabla) return;

        if (!data || data.length === 0) {
            tabla.innerHTML = `<table class="table table-hover align-middle mb-0">
                <thead><tr><th>Nombre</th><th>Representante</th><th>Ubicación</th><th>Deptos.</th><th>Resp.</th><th>Estado</th><th style="width:180px">Acciones</th></tr></thead>
                <tbody><tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron instituciones</td></tr></tbody>
            </table>`;
            return;
        }

        let html = `<table class="table table-hover align-middle mb-0">
            <thead><tr><th>Nombre</th><th>Representante</th><th>Ubicación</th><th>Deptos.</th><th>Resp.</th><th>Estado</th><th style="width:180px">Acciones</th></tr></thead><tbody>`;

        data.forEach(item => {
            const ubicacion = item.ubicacion_completa ||
                ((item.parroquia ? item.parroquia.nombre + ', ' : '') +
                 (item.municipio ? item.municipio.nombre + ', ' : '') +
                 (item.estado ? item.estado.nombre : 'No especificada'));

            html += `<tr>
                <td><div class="fw-medium" style="color:#1e3c72">${resaltarTexto(item.nombre, buscar)}</div>
                    ${item.informacion ? `<small class="text-muted">${escapeHtml(item.informacion.substring(0, 40))}...</small>` : ''}
                </td>
                <td>${resaltarTexto(item.representante, buscar) || '---'}</td>
                <td><small>${escapeHtml(ubicacion)}</small></td>
                <td><span class="badge-activo">${item.departamentos_count || 0}</span></td>
                <td><span class="badge-activo">${item.responsables_count || 0}</span></td>
                <td><span class="badge ${item.activo ? 'badge-activo' : 'badge-inactivo'}">${item.activo ? 'Activa' : 'Inactiva'}</span></td>
                <td><div class="d-flex gap-1">
                    <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="verDetalle('institucion', ${item.id})" title="Ver">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="toggleEstado('institucion', ${item.id})" title="Estado">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    </button>
                    <button class="btn btn-action btn-outline-danger btn-sm" onclick="confirmarEliminar('institucion', ${item.id}, '${escapeHtml(item.nombre).replace(/'/g, "\\'")}', true)" title="Eliminar">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div></td>
            </tr>`;
        });

        html += '</tbody></table>';
        tabla.innerHTML = html;
    }

    // ===========================
    // CARGAR DEPARTAMENTOS
    // ===========================
    function cargarDepartamentos() {
        const buscar = document.getElementById('buscarDepartamentos')?.value || '';
        const tabla = document.getElementById('tablaDepartamentos');

        if (tabla) {
            tabla.innerHTML = `<div class="loading-spinner">
                <svg class="spinner-icon" viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:20px;height:20px;">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
                Cargando...
            </div>`;
        }

        fetch(`/admin/departamentos?buscar=${encodeURIComponent(buscar)}&_t=${Date.now()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        })
        .then(r => r.json())
        .then(data => {
            let departamentos = [];
            if (Array.isArray(data)) {
                departamentos = data;
            } else if (data && Array.isArray(data.data)) {
                departamentos = data.data;
            } else {
                departamentos = [];
            }

            renderTablaDepartamentos(departamentos, buscar);
        })
        .catch(err => {
            console.error('Error al cargar departamentos:', err);
            if (tabla) tabla.innerHTML = '<p class="text-center py-4 text-danger">Error al cargar departamentos</p>';
        });
    }

    function renderTablaDepartamentos(data, buscar) {
        const tabla = document.getElementById('tablaDepartamentos');
        if (!tabla) return;

        const departamentos = Array.isArray(data) ? data : (data.data || []);

        if (departamentos.length === 0) {
            tabla.innerHTML = `<table class="table table-hover align-middle mb-0">
                <thead><tr><th>Nombre</th><th>Institución</th><th>Representante</th><th>Responsables</th><th>Estado</th><th style="width:180px">Acciones</th></tr></thead>
                <tbody><tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron departamentos</td></tr></tbody>
            </table>`;
            return;
        }

        let html = `<table class="table table-hover align-middle mb-0">
            <thead><tr><th>Nombre</th><th>Institución</th><th>Representante</th><th>Responsables</th><th>Estado</th><th style="width:180px">Acciones</th></tr></thead><tbody>`;

        departamentos.forEach(item => {
            html += `<tr>
                <td><span class="fw-medium" style="color:#1e3c72">${resaltarTexto(item.nombre, buscar)}</span></td>
                <td>${escapeHtml(item.institucion?.nombre || 'Sin institución')}</td>
                <td>${resaltarTexto(item.representante, buscar) || '---'}</td>
                <td><span class="badge-activo">${item.responsables_count || 0}</span></td>
                <td><span class="badge ${item.activo ? 'badge-activo' : 'badge-inactivo'}">${item.activo ? 'Activo' : 'Inactivo'}</span></td>
                <td><div class="d-flex gap-1">
                    <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="verDetalle('departamento', ${item.id})">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="toggleEstado('departamento', ${item.id})">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    </button>
                    <button class="btn btn-action btn-outline-danger btn-sm" onclick="confirmarEliminar('departamento', ${item.id}, '${escapeHtml(item.nombre).replace(/'/g, "\\'")}', false)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div></td>
            </tr>`;
        });

        html += '</tbody></table>';
        tabla.innerHTML = html;
    }

    // ===========================
    // CARGAR RESPONSABLES
    // ===========================
    function cargarResponsables() {
        const buscar = document.getElementById('buscarResponsables')?.value || '';
        const tabla = document.getElementById('tablaResponsables');
        if (tabla) {
            tabla.innerHTML = `<div class="loading-spinner"><svg class="spinner-icon" viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:20px;height:20px;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>Cargando...</div>`;
        }

        fetch(`/admin/responsables?buscar=${encodeURIComponent(buscar)}&_t=${Date.now()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        })
        .then(r => r.json())
        .then(data => {
            const responsables = data.data || data || [];
            renderTablaResponsables(responsables, buscar);
        })
        .catch(err => {
            console.error(err);
            if (tabla) tabla.innerHTML = '<p class="text-center py-4 text-danger">Error al cargar</p>';
        });
    }

    function renderTablaResponsables(data, buscar) {
        const tabla = document.getElementById('tablaResponsables');
        if (!tabla) return;

        const responsables = Array.isArray(data) ? data : (data.data || []);

        if (responsables.length === 0) {
            tabla.innerHTML = `<table class="table table-hover align-middle mb-0">
                <thead><tr><th>Nombre</th><th>Documento</th><th>Cargo</th><th>Institución</th><th>Departamento</th><th>Estado</th><th style="width:180px">Acciones</th></tr></thead>
                <tbody><tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron responsables</td></tr></tbody>
            </table>`;
            return;
        }

        let html = `<table class="table table-hover align-middle mb-0">
            <thead><tr><th>Nombre</th><th>Documento</th><th>Cargo</th><th>Institución</th><th>Departamento</th><th>Estado</th><th style="width:180px">Acciones</th></tr></thead><tbody>`;

        responsables.forEach(item => {
            html += `<tr>
                <td><span class="fw-medium" style="color:#1e3c72">${resaltarTexto(item.nombre, buscar)}</span></td>
                <td>${escapeHtml(item.documento || '---')}</td>
                <td>${escapeHtml(item.cargo || '---')}</td>
                <td>${escapeHtml(item.institucion?.nombre || '---')}</td>
                <td>${escapeHtml(item.departamento?.nombre || 'Sin depto.')}</td>
                <td><span class="badge ${item.activo ? 'badge-activo' : 'badge-inactivo'}">${item.activo ? 'Activo' : 'Inactivo'}</span></td>
                <td><div class="d-flex gap-1">
                    <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="verDetalle('responsable', ${item.id})">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button class="btn btn-action btn-outline-primary-dark btn-sm" onclick="toggleEstado('responsable', ${item.id})">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    </button>
                    <button class="btn btn-action btn-outline-danger btn-sm" onclick="confirmarEliminar('responsable', ${item.id}, '${escapeHtml(item.nombre).replace(/'/g, "\\'")}', false)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div></td>
            </tr>`;
        });

        html += '</tbody></table>';
        tabla.innerHTML = html;
    }

    // ===========================
    // SELECTS ANIDADOS
    // ===========================
    function cargarEstados(selectId, selectedId = null) {
        const select = document.getElementById(selectId);
        if (!select) return;
        select.innerHTML = '<option value="">Cargando...</option>';
        select.disabled = true;

        fetch('/admin/ubicaciones/estados', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                select.innerHTML = '<option value="">Seleccionar estado...</option>';
                data.data.forEach(e => select.innerHTML += `<option value="${e.id}">${e.nombre}</option>`);
                if (selectedId) select.value = selectedId;
                select.disabled = false;
            }
        });
    }

    function cargarMunicipios(estadoId, selectId, selectedId = null) {
        const select = document.getElementById(selectId);
        if (!select) return;
        if (!estadoId) { select.innerHTML = '<option value="">Seleccionar estado primero</option>'; select.disabled = true; return; }
        select.innerHTML = '<option value="">Cargando...</option>';
        select.disabled = true;

        fetch(`/admin/ubicaciones/estados/${estadoId}/municipios`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                select.innerHTML = '<option value="">Seleccionar municipio...</option>';
                data.data.forEach(m => select.innerHTML += `<option value="${m.id}">${m.nombre}</option>`);
                if (selectedId) select.value = selectedId;
                select.disabled = false;
            }
        });
    }

    function cargarParroquias(municipioId, selectId, selectedId = null) {
        const select = document.getElementById(selectId);
        if (!select) return;
        if (!municipioId) { select.innerHTML = '<option value="">Seleccionar municipio primero</option>'; select.disabled = true; return; }
        select.innerHTML = '<option value="">Cargando...</option>';
        select.disabled = true;

        fetch(`/admin/ubicaciones/municipios/${municipioId}/parroquias`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                select.innerHTML = '<option value="">Seleccionar parroquia...</option>';
                data.data.forEach(p => select.innerHTML += `<option value="${p.id}">${p.nombre}</option>`);
                if (selectedId) select.value = selectedId;
                select.disabled = false;
            }
        });
    }

    // ===========================
    // WIZARD MAESTRO (Institución → Departamento → Responsable)
    // ===========================
    let pasoWizard = 1;
    const TOTAL_PASOS = 3;

    window.abrirWizardEntidad = function() {
        pasoWizard = 1;

        // Limpiar PASO 1
        document.getElementById('wz_inst_nombre').value = '';
        document.getElementById('wz_inst_informacion').value = '';
        document.getElementById('wz_estado_id').innerHTML = '<option value="">Seleccionar estado...</option>';
        document.getElementById('wz_municipio_id').innerHTML = '<option value="">Seleccionar estado primero</option>';
        document.getElementById('wz_municipio_id').disabled = true;
        document.getElementById('wz_parroquia_id').innerHTML = '<option value="">Seleccionar municipio primero</option>';
        document.getElementById('wz_parroquia_id').disabled = true;

        // Limpiar PASO 2
        document.getElementById('wz_depto_nombre').value = '';
        document.getElementById('wz_depto_ubicacion').value = '';
        document.getElementById('wz_depto_informacion').value = '';

        // Limpiar PASO 3
        document.getElementById('wz_resp_nombre').value = '';
        document.getElementById('wz_resp_cargo').value = 'Jefe de Departamento';
        document.getElementById('wz_resp_documento').value = '';
        document.getElementById('wz_resp_telefono').value = '';
        document.getElementById('wz_resp_email').value = '';
        document.getElementById('wz_resp_direccion').value = '';

        // Mostrar paso 1
        document.getElementById('wizardStep1').style.display = 'block';
        document.getElementById('wizardStep2').style.display = 'none';
        document.getElementById('wizardStep3').style.display = 'none';

        actualizarIndicadoresWizard();
        cargarEstados('wz_estado_id');

        const modal = new bootstrap.Modal(document.getElementById('modalWizardEntidad'));
        modal.show();
    };

    window.irPasoWizard = function(paso) {
        if (paso < 1 || paso > TOTAL_PASOS) return;
        if (paso > pasoWizard && !validarPasoWizard(pasoWizard)) return;

        document.getElementById(`wizardStep${pasoWizard}`).style.display = 'none';
        pasoWizard = paso;
        document.getElementById(`wizardStep${pasoWizard}`).style.display = 'block';

        actualizarIndicadoresWizard();
        actualizarEtiquetasWizard();
    };

    function validarPasoWizard(paso) {
        if (paso === 1) {
            const nombre = document.getElementById('wz_inst_nombre').value.trim();
            const informacion = document.getElementById('wz_inst_informacion').value.trim();
            const estadoId = document.getElementById('wz_estado_id').value;
            const municipioId = document.getElementById('wz_municipio_id').value;
            const parroquiaId = document.getElementById('wz_parroquia_id').value;

            if (!nombre) { mostrarToast('Ingrese el nombre de la institución', 'warning'); document.getElementById('wz_inst_nombre').focus(); return false; }
            if (!informacion) { mostrarToast('Ingrese información de la institución', 'warning'); document.getElementById('wz_inst_informacion').focus(); return false; }
            if (!estadoId) { mostrarToast('Seleccione un estado', 'warning'); document.getElementById('wz_estado_id').focus(); return false; }
            if (!municipioId) { mostrarToast('Seleccione un municipio', 'warning'); document.getElementById('wz_municipio_id').focus(); return false; }
            if (!parroquiaId) { mostrarToast('Seleccione una parroquia', 'warning'); document.getElementById('wz_parroquia_id').focus(); return false; }
            return true;
        }

        if (paso === 2) {
            const nombre = document.getElementById('wz_depto_nombre').value.trim();
            const ubicacion = document.getElementById('wz_depto_ubicacion').value.trim();
            const informacion = document.getElementById('wz_depto_informacion').value.trim();

            if (!nombre) { mostrarToast('Ingrese el nombre del departamento', 'warning'); document.getElementById('wz_depto_nombre').focus(); return false; }
            if (!ubicacion) { mostrarToast('Ingrese la ubicación del departamento', 'warning'); document.getElementById('wz_depto_ubicacion').focus(); return false; }
            if (!informacion) { mostrarToast('Ingrese información del departamento', 'warning'); document.getElementById('wz_depto_informacion').focus(); return false; }
            return true;
        }

        if (paso === 3) {
            const nombre = document.getElementById('wz_resp_nombre').value.trim();
            const documento = document.getElementById('wz_resp_documento').value.trim();
            const telefono = document.getElementById('wz_resp_telefono').value.trim();
            const cargo = document.getElementById('wz_resp_cargo').value.trim();

            if (!nombre) { mostrarToast('Ingrese el nombre del responsable', 'warning'); document.getElementById('wz_resp_nombre').focus(); return false; }
            if (!documento) { mostrarToast('Ingrese el documento', 'warning'); document.getElementById('wz_resp_documento').focus(); return false; }
            if (!telefono) { mostrarToast('Ingrese el teléfono', 'warning'); document.getElementById('wz_resp_telefono').focus(); return false; }
            if (!cargo) { mostrarToast('Ingrese el cargo', 'warning'); document.getElementById('wz_resp_cargo').focus(); return false; }
            return true;
        }
        return true;
    }

    function actualizarIndicadoresWizard() {
        for (let i = 1; i <= TOTAL_PASOS; i++) {
            const circle = document.querySelector(`#wizardLabel${i} .step-circle`);
            const label = document.getElementById(`wizardLabel${i}`);
            if (!circle) continue;

            circle.classList.remove('active', 'completed');
            if (i < pasoWizard) {
                circle.classList.add('completed');
                circle.textContent = '✓';
                if (label) label.style.color = '#1e7e34';
            } else if (i === pasoWizard) {
                circle.classList.add('active');
                circle.textContent = i;
                if (label) label.style.color = '#1e3c72';
            } else {
                circle.textContent = i;
                if (label) label.style.color = '#adb5bd';
            }
        }

        const progress = ((pasoWizard - 1) / (TOTAL_PASOS - 1)) * 100;
        const progressBar = document.getElementById('wizardProgressBar');
        if (progressBar) progressBar.style.width = `${progress}%`;

        const indicator = document.getElementById('wizardStepIndicator');
        if (indicator) indicator.textContent = `Paso ${pasoWizard} de ${TOTAL_PASOS}`;
    }

    function actualizarEtiquetasWizard() {
        const instNombre = document.getElementById('wz_inst_nombre').value || '(Ninguna)';
        const deptoNombre = document.getElementById('wz_depto_nombre').value || '(Ninguno)';

        const labelInst = document.getElementById('wizardInstitucionLabel');
        if (labelInst) labelInst.textContent = instNombre;

        const labelInst2 = document.getElementById('wizardInstitucionLabel2');
        if (labelInst2) labelInst2.textContent = instNombre;

        const labelDepto2 = document.getElementById('wizardDepartamentoLabel2');
        if (labelDepto2) labelDepto2.textContent = deptoNombre;
    }

    // Event listeners para selects anidados del wizard
    document.getElementById('wz_estado_id')?.addEventListener('change', function() {
        cargarMunicipios(this.value, 'wz_municipio_id');
        const parroquiaSelect = document.getElementById('wz_parroquia_id');
        parroquiaSelect.innerHTML = '<option value="">Seleccionar municipio primero</option>';
        parroquiaSelect.disabled = true;
    });

    document.getElementById('wz_municipio_id')?.addEventListener('change', function() {
        cargarParroquias(this.value, 'wz_parroquia_id');
    });

    // Actualizar etiquetas al escribir en inputs
    document.getElementById('wz_inst_nombre')?.addEventListener('input', actualizarEtiquetasWizard);
    document.getElementById('wz_depto_nombre')?.addEventListener('input', actualizarEtiquetasWizard);

    // ===========================
    // GUARDAR WIZARD (3 pasos en cadena)
    // ===========================
    document.getElementById('wizardBtnGuardar')?.addEventListener('click', async function() {
        // Validar paso 3
        if (!validarPasoWizard(3)) return;

        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        btn.disabled = true;

        try {
            // ===== 1. CREAR INSTITUCIÓN (con su responsable institucional) =====
            const formDataInst = new FormData();
            formDataInst.append('_token', csrfToken);
            formDataInst.append('nombre', document.getElementById('wz_inst_nombre').value);
            formDataInst.append('informacion', document.getElementById('wz_inst_informacion').value);
            formDataInst.append('estado_id', document.getElementById('wz_estado_id').value);
            formDataInst.append('municipio_id', document.getElementById('wz_municipio_id').value);
            formDataInst.append('parroquia_id', document.getElementById('wz_parroquia_id').value);
            formDataInst.append('representante_nombre', document.getElementById('wz_resp_nombre').value);
            formDataInst.append('representante_documento', document.getElementById('wz_resp_documento').value);
            formDataInst.append('representante_telefono', document.getElementById('wz_resp_telefono').value);
            formDataInst.append('representante_email', document.getElementById('wz_resp_email').value);
            formDataInst.append('representante_cargo', document.getElementById('wz_resp_cargo').value);
            formDataInst.append('representante_direccion', document.getElementById('wz_resp_direccion').value);

            const responseInst = await fetch('/admin/instituciones', {
                method: 'POST',
                body: formDataInst,
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });

            const resultInst = await responseInst.json();

            if (!resultInst.success) {
                if (resultInst.errors) {
                    let msg = 'Errores institución:\n';
                    Object.values(resultInst.errors).forEach(err => { msg += '- ' + err.join('\n') + '\n'; });
                    mostrarToast(msg, 'error');
                } else {
                    mostrarToast(resultInst.message || 'Error al crear institución', 'error');
                }
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }

            const institucionId = resultInst.data?.id || resultInst.data?.institucion?.id;

            if (!institucionId) {
                mostrarToast('No se pudo obtener el ID de la institución', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }

            // ===== 2. OBTENER EL RESPONSABLE INSTITUCIONAL RECIÉN CREADO =====
            let responsableInstitucionalId = null;
            try {
                const respResponse = await fetch(`/admin/responsables?institucion_id=${institucionId}&todos=1&_t=${Date.now()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Cache-Control': 'no-cache' }
                });
                const respData = await respResponse.json();
                const responsables = respData.data || respData || [];

                const respInstitucional = responsables.find(r =>
                    r.departamento_id === null || r.departamento_id === undefined
                );

                if (respInstitucional) {
                    responsableInstitucionalId = respInstitucional.id;
                }
            } catch (e) {
                console.warn('No se pudo obtener el responsable institucional:', e);
            }

            // ===== 3. CREAR DEPARTAMENTO =====
            const formDataDepto = new FormData();
            formDataDepto.append('_token', csrfToken);
            formDataDepto.append('institucion_id', institucionId);
            formDataDepto.append('nombre', document.getElementById('wz_depto_nombre').value);
            formDataDepto.append('ubicacion', document.getElementById('wz_depto_ubicacion').value);
            formDataDepto.append('informacion', document.getElementById('wz_depto_informacion').value);

            if (responsableInstitucionalId) {
                formDataDepto.append('usar_responsable_institucion', '1');
                formDataDepto.append('responsable_id', responsableInstitucionalId);
            }

            formDataDepto.append('representante_nombre', document.getElementById('wz_resp_nombre').value);
            formDataDepto.append('representante_documento', document.getElementById('wz_resp_documento').value);
            formDataDepto.append('representante_telefono', document.getElementById('wz_resp_telefono').value);
            formDataDepto.append('representante_email', document.getElementById('wz_resp_email').value);
            formDataDepto.append('representante_cargo', document.getElementById('wz_resp_cargo').value);
            formDataDepto.append('representante_direccion', document.getElementById('wz_resp_direccion').value);

            const responseDepto = await fetch('/admin/departamentos', {
                method: 'POST',
                body: formDataDepto,
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });

            const resultDepto = await responseDepto.json();

            if (!resultDepto.success) {
                mostrarToast(resultDepto.message || 'Error al crear departamento', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }

            // ===== ÉXITO =====
            bootstrap.Modal.getInstance(document.getElementById('modalWizardEntidad')).hide();
            mostrarToast('✅ Entidad registrada: Institución + Departamento + Responsable', 'success');

            // ✅ Recargar tablas (con un pequeño delay para asegurar persistencia)
            setTimeout(() => {
                cargarInstituciones();
                cargarDepartamentos();
                cargarResponsables();
            }, 200);

            // Resetear wizard
            pasoWizard = 1;

        } catch (err) {
            console.error(err);
            mostrarToast('Error de conexión: ' + err.message, 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });

    // ===========================
    // TOGGLE ESTADO
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
            } else {
                mostrarToast(result.message || 'Error', 'error');
            }
        });
    };

    // ===========================
    // CONFIRMAR ELIMINAR
    // ===========================
    window.confirmarEliminar = function(tipo, id, nombre, tieneDependencias) {
        elementoAEliminar = { tipo, id };
        document.getElementById('deleteNombre').textContent = nombre;

        const adv = document.getElementById('deleteAdvertencia');
        if (adv) {
            if (tipo === 'institucion') {
                adv.style.display = 'block';
                adv.textContent = 'Se eliminarán también los departamentos y responsables asociados.';
            } else if (tipo === 'departamento') {
                adv.style.display = 'block';
                adv.textContent = 'Se eliminará el responsable asociado.';
            } else {
                adv.style.display = 'none';
            }
        }

        new bootstrap.Modal(document.getElementById('modalEliminar')).show();
    };

    document.getElementById('btnConfirmarEliminar')?.addEventListener('click', function() {
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
                mostrarToast(result.message || 'Eliminado', 'success');
                if (tipo === 'institucion') { cargarInstituciones(); cargarDepartamentos(); cargarResponsables(); }
                else if (tipo === 'departamento') cargarDepartamentos();
                else cargarResponsables();
            } else {
                mostrarToast(result.message || 'Error', 'error');
            }
            btn.textContent = 'Eliminar';
            btn.className = 'btn btn-danger';
            btn.removeAttribute('data-confirmado');
        });
    });

    // ===========================
    // VER DETALLE
    // ===========================
    window.verDetalle = function(tipo, id) {
        fetch(getUrl(tipo, id), { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(result => {
            const d = result.data || result;
            if (!d) return;

            let html = '';

            if (tipo === 'institucion') {
                const ubic = d.ubicacion_completa || 'No especificada';
                html = `
                    <div class="detail-header"><h5>${escapeHtml(d.nombre)}</h5>
                        <span class="badge ${d.activo ? 'badge-activo' : 'badge-inactivo'}">${d.activo ? 'Activa' : 'Inactiva'}</span>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item"><div class="detail-label">Representante</div><div class="detail-value">${escapeHtml(d.representante) || '---'}</div></div>
                        <div class="detail-item"><div class="detail-label">Ubicación</div><div class="detail-value">${escapeHtml(ubic)}</div></div>
                        <div class="detail-item" style="grid-column:1/-1"><div class="detail-label">Información</div><div class="detail-value">${escapeHtml(d.informacion) || 'Sin información'}</div></div>
                    </div>`;
            } else if (tipo === 'departamento') {
                html = `
                    <div class="detail-header"><h5>${escapeHtml(d.nombre)}</h5>
                        <span class="badge ${d.activo ? 'badge-activo' : 'badge-inactivo'}">${d.activo ? 'Activo' : 'Inactivo'}</span>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item"><div class="detail-label">Institución</div><div class="detail-value">${d.institucion ? escapeHtml(d.institucion.nombre) : 'Sin institución'}</div></div>
                        <div class="detail-item"><div class="detail-label">Representante</div><div class="detail-value">${escapeHtml(d.representante) || '---'}</div></div>
                        <div class="detail-item"><div class="detail-label">Ubicación</div><div class="detail-value">${escapeHtml(d.ubicacion) || '---'}</div></div>
                    </div>`;
            } else {
                html = `
                    <div class="detail-header"><h5>${escapeHtml(d.nombre)}</h5>
                        <span class="badge ${d.activo ? 'badge-activo' : 'badge-inactivo'}">${d.activo ? 'Activo' : 'Inactivo'}</span>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item"><div class="detail-label">Documento</div><div class="detail-value">${escapeHtml(d.documento) || '---'}</div></div>
                        <div class="detail-item"><div class="detail-label">Cargo</div><div class="detail-value">${escapeHtml(d.cargo) || '---'}</div></div>
                        <div class="detail-item"><div class="detail-label">Teléfono</div><div class="detail-value">${escapeHtml(d.telefono) || '---'}</div></div>
                        <div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">${escapeHtml(d.email) || '---'}</div></div>
                    </div>`;
            }

            document.getElementById('detalleContenido').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        });
    };

    // ===========================
    // INICIALIZACIÓN
    // ===========================
    console.log('Inicializando módulo de entidades con Wizard...');

    cargarInstituciones();
    cargarDepartamentos();
    cargarResponsables();

    document.getElementById('buscarInstituciones')?.addEventListener('input', debounce(cargarInstituciones, 300));
    document.getElementById('buscarDepartamentos')?.addEventListener('input', debounce(cargarDepartamentos, 300));
    document.getElementById('buscarResponsables')?.addEventListener('input', debounce(cargarResponsables, 300));

    document.querySelectorAll('#entidadesTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target === '#instituciones') cargarInstituciones();
            else if (target === '#departamentos') cargarDepartamentos();
            else if (target === '#responsables') cargarResponsables();
        });
    });
});