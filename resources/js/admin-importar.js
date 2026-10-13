/**
 * ============================================================
 * MÓDULO DE IMPORTACIÓN DE ACTIVOS DESDE EXCEL
 * ============================================================
 *
 * Flujo:
 *   1. Usuario sube archivo → POST /admin/importar/analizar
 *   2. Sistema devuelve mapeo + entidades no reconocidas
 *   3. Usuario confirma/corrige → POST /admin/importar/procesar
 *   4. Sistema devuelve resultado (éxitos, errores, advertencias)
 *
 * Características:
 *   - Bloqueo de navegación mientras hay proceso activo
 *   - Persistencia del session_id en localStorage
 *   - Drag & drop de archivos
 *   - Renderizado dinámico de entidades no reconocidas
 *   - Manejo de errores con toasts
 */

(function () {
    'use strict';

    // ============================================================
    // CONSTANTES
    // ============================================================
    const STORAGE_KEY      = 'importar_activos_session';
    const ESTADO_INICIAL   = 'inicial';
    const ESTADO_ANALIZANDO = 'analizando';
    const ESTADO_PREVIEW   = 'preview';
    const ESTADO_PROCESANDO = 'procesando';
    const ESTADO_RESULTADO = 'resultado';

    const URLS = {
        analizar:  '/admin/importar/analizar',
        procesar:  '/admin/importar/procesar',
        cancelar:  (sessionId) => `/admin/importar/cancelar/${sessionId}`,
    };

    // ============================================================
    // ESTADO GLOBAL
    // ============================================================
    const state = {
        archivo: null,
        sessionId: null,
        totalFilas: 0,
        nombreArchivo: '',
        mapeo: [],                 // [{ columna_excel, campo, confianza, indice }]
        camposDisponibles: [],     // [{ valor, etiqueta }]
        entidadesNoReconocidas: {}, // { instituciones: [...], marcas: [...], ... }
        decisiones: {},             // { instituciones: { "X": { accion, id } }, ... }
        resultado: null,
        importacionActiva: false,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
    };

    // ============================================================
    // REFERENCIAS DOM (se inicializan en init)
    // ============================================================
    let el = {};

    // ============================================================
    // INICIALIZACIÓN
    // ============================================================
    document.addEventListener('DOMContentLoaded', function () {
        // Solo inicializar si el partial del tab está presente en el DOM
        const app = document.getElementById('importarApp');
        if (!app) return;

        cachearElementos();
        registrarEventos();
        verificarSesionPersistida();

        console.log('[Importar] Módulo inicializado');
    });

    function cachearElementos() {
        el = {
            app: document.getElementById('importarApp'),

            // Estados
            estadoInicial:    document.getElementById('importarEstadoInicial'),
            estadoAnalizando: document.getElementById('importarEstadoAnalizando'),
            estadoPreview:    document.getElementById('importarEstadoPreview'),
            estadoProcesando: document.getElementById('importarEstadoProcesando'),
            estadoResultado:  document.getElementById('importarEstadoResultado'),

            // Subida
            formSubida:       document.getElementById('importarFormSubida'),
            archivoInput:     document.getElementById('importarArchivo'),
            dropzone:         document.getElementById('importarDropzone'),
            dropzoneTitulo:   document.getElementById('importarDropzoneTitulo'),
            dropzoneSub:      document.getElementById('importarDropzoneSubtitulo'),
            infoArchivo:      document.getElementById('importarInfoArchivo'),
            nombreArchivo:    document.getElementById('importarNombreArchivo'),
            tamanoArchivo:    document.getElementById('importarTamanoArchivo'),
            btnQuitar:        document.getElementById('importarBtnQuitar'),
            btnAnalizar:      document.getElementById('importarBtnAnalizar'),
            loadingNombre:    document.getElementById('importarLoadingNombre'),

            // Preview
            previewNombre:    document.getElementById('importarPreviewNombre'),
            previewTotal:     document.getElementById('importarPreviewTotalFilas'),
            previewAlerta:    document.getElementById('importarPreviewAlertaCritica'),
            tablaMapeoBody:   document.getElementById('importarTablaMapeoBody'),
            seccionEntidades: document.getElementById('importarSeccionEntidades'),
            entidadesContainer: document.getElementById('importarEntidadesContainer'),
            btnCancelar:      document.getElementById('importarBtnCancelar'),
            btnProcesar:      document.getElementById('importarBtnProcesar'),

            // Resultado
            resumenExitos:       document.getElementById('importarResumenExitos'),
            resumenErrores:      document.getElementById('importarResumenErrores'),
            resumenAdvertencias: document.getElementById('importarResumenAdvertencias'),
            resumenTiempo:       document.getElementById('importarResumenTiempo'),
            seccionErrores:      document.getElementById('importarSeccionErrores'),
            seccionAdvertencias: document.getElementById('importarSeccionAdvertencias'),
            tablaErroresBody:    document.getElementById('importarTablaErroresBody'),
            tablaAdvertenciasBody: document.getElementById('importarTablaAdvertenciasBody'),
            btnVolver:           document.getElementById('importarBtnVolver'),

            // Templates
            tplFilaMapeo:       document.getElementById('tplImportarFilaMapeo'),
            tplEntidadSeccion:  document.getElementById('tplImportarEntidadSeccion'),
            tplEntidadItem:     document.getElementById('tplImportarEntidadItem'),
            tplFilaError:       document.getElementById('tplImportarFilaError'),
            tplFilaAdvertencia: document.getElementById('tplImportarFilaAdvertencia'),
        };
    }

    // ============================================================
    // REGISTRO DE EVENTOS
    // ============================================================
    function registrarEventos() {
        if (!el.app) return;

        // ----- Subida de archivo -----
        el.archivoInput?.addEventListener('change', onArchivoSeleccionado);
        el.btnQuitar?.addEventListener('click', onQuitarArchivo);
        el.btnAnalizar?.addEventListener('click', onAnalizar);

        // ----- Drag & drop -----
        if (el.dropzone) {
            ['dragenter', 'dragover'].forEach(evt => {
                el.dropzone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    el.dropzone.classList.add('importar-dropzone-arrastrando');
                });
            });

            ['dragleave', 'drop'].forEach(evt => {
                el.dropzone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    el.dropzone.classList.remove('importar-dropzone-arrastrando');
                });
            });

            el.dropzone.addEventListener('drop', (e) => {
                const files = e.dataTransfer?.files;
                if (files && files.length > 0) {
                    el.archivoInput.files = files;
                    onArchivoSeleccionado();
                }
            });
        }

        // ----- Preview -----
        el.btnCancelar?.addEventListener('click', onCancelar);
        el.btnProcesar?.addEventListener('click', onProcesar);

        // ----- Resultado -----
        el.btnVolver?.addEventListener('click', onVolverAlInicio);

        // ----- Bloqueo de navegación -----
        window.addEventListener('beforeunload', onBeforeUnload);

        // ----- Cambio de tab -----
        document.querySelectorAll('#inventarioTabs .nav-link').forEach(tab => {
            tab.addEventListener('click', onIntentoCambioTab);
        });
    }

    // ============================================================
    // BLOQUEO DE NAVEGACIÓN
    // ============================================================
    function onBeforeUnload(e) {
        if (state.importacionActiva) {
            e.preventDefault();
            e.returnValue = 'Hay una importación en proceso. Si sales, perderás el progreso.';
            return e.returnValue;
        }
    }

    function onIntentoCambioTab(e) {
        const targetId = e.target.id;
        if (state.importacionActiva && targetId !== 'importar-tab') {
            const confirmar = window.confirm(
                'Hay una importación en proceso. ¿Deseas salir y perder el progreso?'
            );
            if (!confirmar) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
            cancelarImportacion();
        }
    }

    // ============================================================
    // MANEJO DEL ARCHIVO
    // ============================================================
    function onArchivoSeleccionado() {
        const file = el.archivoInput?.files?.[0];
        if (!file) return;

        // Validar extensión
        const extension = file.name.split('.').pop().toLowerCase();
        if (!['xlsx', 'xls'].includes(extension)) {
            mostrarToast('Solo se permiten archivos .xlsx o .xls', 'error');
            el.archivoInput.value = '';
            return;
        }

        // Validar tamaño (10 MB)
        if (file.size > 10 * 1024 * 1024) {
            mostrarToast('El archivo no puede superar los 10 MB', 'error');
            el.archivoInput.value = '';
            return;
        }

        // Guardar en state
        state.archivo = file;
        state.nombreArchivo = file.name;

        // Mostrar info
        if (el.nombreArchivo) el.nombreArchivo.textContent = file.name;
        if (el.tamanoArchivo) el.tamanoArchivo.textContent = formatearTamano(file.size);
        if (el.infoArchivo) el.infoArchivo.style.display = 'flex';

        // Ocultar dropzone
        if (el.dropzone) el.dropzone.style.display = 'none';

        // Habilitar botón
        if (el.btnAnalizar) el.btnAnalizar.disabled = false;
    }

    function onQuitarArchivo() {
        state.archivo = null;
        state.nombreArchivo = '';

        if (el.archivoInput) el.archivoInput.value = '';
        if (el.infoArchivo) el.infoArchivo.style.display = 'none';
        if (el.dropzone) el.dropzone.style.display = 'flex';
        if (el.btnAnalizar) el.btnAnalizar.disabled = true;
    }

    function formatearTamano(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    // ============================================================
    // ANALIZAR
    // ============================================================
    async function onAnalizar() {
        if (!state.archivo) {
            mostrarToast('Debes seleccionar un archivo primero', 'warning');
            return;
        }

        cambiarEstado(ESTADO_ANALIZANDO);
        if (el.loadingNombre) el.loadingNombre.textContent = state.archivo.name;

        try {
            const formData = new FormData();
            formData.append('archivo', state.archivo);
            formData.append('_token', state.csrfToken);

            const response = await fetch(URLS.analizar, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': state.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Error al analizar el archivo');
            }

            // Guardar datos
            state.sessionId = data.session_id;
            state.totalFilas = data.data.total_filas;
            state.mapeo = data.data.mapeo;
            state.camposDisponibles = data.data.campos_disponibles;
            state.entidadesNoReconocidas = data.data.entidades_no_reconocidas;
            state.decisiones = {};
            state.nombreArchivo = data.data.nombre_archivo || state.nombreArchivo;
            state.importacionActiva = true;

            // Persistir session_id
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify({
                    sessionId: state.sessionId,
                    nombreArchivo: state.nombreArchivo,
                    totalFilas: state.totalFilas,
                }));
            } catch (e) { /* ignore */ }

            // Renderizar preview
            renderPreview(data.data);

            cambiarEstado(ESTADO_PREVIEW);

            mostrarToast(`Archivo analizado: ${data.data.total_filas} filas detectadas`, 'success');

        } catch (error) {
            console.error('[Importar] Error al analizar:', error);
            mostrarToast(error.message || 'Error al analizar el archivo', 'error');
            cambiarEstado(ESTADO_INICIAL);
        }
    }

    // ============================================================
    // RENDER PREVIEW
    // ============================================================
    function renderPreview(data) {
        // ----- Header -----
        if (el.previewNombre) el.previewNombre.textContent = data.nombre_archivo || state.nombreArchivo;
        if (el.previewTotal) el.previewTotal.textContent = data.total_filas || 0;

        // Alerta de campos críticos
        const faltantes = data.campos_criticos_faltantes || [];
        if (faltantes.length > 0 && el.previewAlerta) {
            el.previewAlerta.style.display = 'inline';
            el.previewAlerta.textContent = `· Faltan campos obligatorios: ${faltantes.join(', ')}`;
        } else if (el.previewAlerta) {
            el.previewAlerta.style.display = 'none';
        }

        // ----- Mapeo -----
        renderTablaMapeo(data.mapeo, data.campos_disponibles);

        // ----- Entidades no reconocidas -----
        renderEntidades(data.entidades_no_reconocidas);
    }

    function renderTablaMapeo(mapeo, camposDisponibles) {
        if (!el.tablaMapeoBody || !el.tplFilaMapeo) return;

        el.tablaMapeoBody.innerHTML = '';

        const camposCriticos = ['serial'];

        mapeo.forEach((item) => {
            const tpl = el.tplFilaMapeo.content.cloneNode(true);
            const tr = tpl.querySelector('tr');

            // Columna Excel
            tr.querySelector('.importar-mapeo-columna').textContent = item.columna_excel;

            // Select
            const select = tr.querySelector('.importar-mapeo-select');
            select.dataset.columna = item.columna_excel;
            select.dataset.indice = item.indice ?? '';

            // Opciones
            camposDisponibles.forEach((opcion) => {
                const opt = document.createElement('option');
                opt.value = opcion.valor;
                opt.textContent = opcion.etiqueta;
                if (opcion.valor === (item.campo || '')) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });

            // Marcar visualmente si es crítico y está vacío
            if (camposCriticos.includes(item.campo) && !item.campo) {
                select.classList.add('importar-select-critico');
            }

            // Evento de cambio
            select.addEventListener('change', () => {
                if (select.value === '') {
                    select.classList.remove('importar-select-critico');
                } else {
                    select.classList.remove('importar-select-critico');
                }
            });

            // Confianza
            const confianza = tr.querySelector('.importar-mapeo-confianza');
            const nivel = item.confianza || 'nula';
            confianza.className = `importar-mapeo-confianza importar-confianza-${nivel}`;
            confianza.textContent = nivel.charAt(0).toUpperCase() + nivel.slice(1);

            el.tablaMapeoBody.appendChild(tpl);
        });
    }

    // ============================================================
    // RENDER ENTIDADES NO RECONOCIDAS
    // ============================================================
    function renderEntidades(entidades) {
        if (!el.entidadesContainer || !el.seccionEntidades) return;

        // Mapeo de tipos a etiquetas legibles
        const TIPOS = {
            instituciones: { icono: '🏢', label: 'Instituciones' },
            departamentos: { icono: '📁', label: 'Departamentos' },
            responsables:  { icono: '👤', label: 'Responsables' },
            marcas:        { icono: '🏷️', label: 'Marcas' },
            categorias:    { icono: '🗂️', label: 'Categorías' },
        };

        // Limpiar
        el.entidadesContainer.innerHTML = '';
        state.decisiones = {};

        let hayAlguna = false;

        Object.keys(TIPOS).forEach((tipo) => {
            const lista = entidades[tipo] || [];

            if (lista.length === 0) return;

            hayAlguna = true;
            const info = TIPOS[tipo];

            // Crear sección
            const seccion = document.createElement('div');
            seccion.className = 'importar-entidad-seccion';
            seccion.dataset.tipo = tipo;

            // Título
            const titulo = document.createElement('div');
            titulo.className = 'importar-entidad-titulo';
            titulo.innerHTML = `
                <span class="importar-entidad-titulo-icono">${info.icono}</span>
                <span class="importar-entidad-titulo-texto">${info.label}</span>
                <span class="importar-entidad-titulo-count">${lista.length}</span>
            `;
            seccion.appendChild(titulo);

            // Items
            const itemsContainer = document.createElement('div');
            itemsContainer.className = 'importar-entidad-items';

            lista.forEach((nombre, index) => {
                const itemEl = crearItemEntidad(tipo, nombre, index);
                itemsContainer.appendChild(itemEl);
            });

            seccion.appendChild(itemsContainer);

            // Acciones masivas
            const masivas = document.createElement('div');
            masivas.className = 'importar-acciones-masivas';
            masivas.innerHTML = `
                <button type="button" class="btn btn-outline-primary-dark" data-masiva="crear" data-tipo="${tipo}">
                    Crear todas
                </button>
                <button type="button" class="btn btn-outline-primary-dark" data-masiva="vincular" data-tipo="${tipo}">
                    Vincular todas
                </button>
                <button type="button" class="btn btn-outline-primary-dark" data-masiva="ignorar" data-tipo="${tipo}">
                    Ignorar todas
                </button>
            `;
            seccion.appendChild(masivas);

            el.entidadesContainer.appendChild(seccion);
        });

        // Mostrar u ocultar sección
        if (hayAlguna) {
            el.seccionEntidades.style.display = 'block';
            registrarEventosEntidades();
        } else {
            el.seccionEntidades.style.display = 'none';
        }
    }

    function crearItemEntidad(tipo, nombre, index) {
        const item = document.createElement('div');
        item.className = 'importar-entidad-item';
        item.dataset.nombre = nombre;
        item.dataset.tipo = tipo;

        const radioName = `entidad_${tipo}_${index}`;

        // Determinar acciones permitidas según tipo
        // instituciones, departamentos, responsables: NO se pueden crear desde el import
        // marcas y categorias: sí se pueden crear
        const puedeCrear = tipo === 'marcas' || tipo === 'categorias';

        // Valor por defecto: 'ignorar' para no-creables, 'crear' para creables
        const accionDefault = puedeCrear ? 'crear' : 'ignorar';

        item.innerHTML = `
            <div class="importar-entidad-item-nombre">${escapeHtml(nombre)}</div>
            <div class="importar-entidad-item-acciones">
                ${puedeCrear ? `
                    <label class="importar-radio">
                        <input type="radio" name="${radioName}" value="crear" ${accionDefault === 'crear' ? 'checked' : ''}>
                        <span>Crear</span>
                    </label>
                ` : ''}
                <label class="importar-radio">
                    <input type="radio" name="${radioName}" value="vincular" ${accionDefault === 'vincular' ? 'checked' : ''}>
                    <span>Vincular</span>
                </label>
                <label class="importar-radio">
                    <input type="radio" name="${radioName}" value="ignorar" ${accionDefault === 'ignorar' ? 'checked' : ''}>
                    <span>Ignorar</span>
                </label>
            </div>
            <div class="importar-entidad-item-vincular" style="display: none;">
                <select class="form-select form-select-sm importar-vincular-select">
                    <option value="">Selecciona una entidad existente...</option>
                </select>
            </div>
        `;

        // Guardar decisión inicial
        if (!state.decisiones[tipo]) state.decisiones[tipo] = {};
        state.decisiones[tipo][nombre] = { accion: accionDefault };

        // Cargar entidades existentes para el select de vinculación
        if (tipo === 'instituciones') {
            cargarInstitucionesEnSelect(item, nombre);
        } else if (tipo === 'marcas') {
            cargarMarcasEnSelect(item, nombre);
        } else if (tipo === 'categorias') {
            cargarCategoriasEnSelect(item, nombre);
        } else if (tipo === 'departamentos') {
            cargarDepartamentosEnSelect(item, nombre);
        } else if (tipo === 'responsables') {
            cargarResponsablesEnSelect(item, nombre);
        }

        return item;
    }

    function registrarEventosEntidades() {
        if (!el.entidadesContainer) return;

        // Radios por item
        el.entidadesContainer.querySelectorAll('.importar-entidad-item').forEach((item) => {
            const nombre = item.dataset.nombre;
            const tipo = item.dataset.tipo;
            const vincularDiv = item.querySelector('.importar-entidad-item-vincular');
            const select = item.querySelector('.importar-vincular-select');

            item.querySelectorAll('input[type="radio"]').forEach((radio) => {
                radio.addEventListener('change', () => {
                    if (!state.decisiones[tipo]) state.decisiones[tipo] = {};
                    state.decisiones[tipo][nombre] = {
                        accion: radio.value,
                        id: null,
                    };

                    if (radio.value === 'vincular') {
                        vincularDiv.style.display = 'block';
                    } else {
                        vincularDiv.style.display = 'none';
                        state.decisiones[tipo][nombre].id = null;
                    }
                });
            });

            select?.addEventListener('change', () => {
                if (state.decisiones[tipo] && state.decisiones[tipo][nombre]) {
                    state.decisiones[tipo][nombre].id = select.value ? parseInt(select.value) : null;
                }
            });
        });

        // Acciones masivas
        el.entidadesContainer.querySelectorAll('[data-masiva]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const accion = btn.dataset.masiva;
                const tipo = btn.dataset.tipo;
                const seccion = el.entidadesContainer.querySelector(`.importar-entidad-seccion[data-tipo="${tipo}"]`);
                if (!seccion) return;

                seccion.querySelectorAll('.importar-entidad-item').forEach((item) => {
                    const nombre = item.dataset.nombre;
                    const radio = item.querySelector(`input[type="radio"][value="${accion}"]`);
                    if (radio) {
                        radio.checked = true;
                        radio.dispatchEvent(new Event('change'));
                    }
                });
            });
        });
    }

    // ============================================================
    // CARGA DE ENTIDADES EXISTENTES (para el select de vincular)
    // ============================================================
    async function cargarInstitucionesEnSelect(item, nombreActual) {
        const select = item.querySelector('.importar-vincular-select');
        if (!select) return;

        try {
            const response = await fetch('/admin/instituciones?todos=1', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await response.json();
            const lista = data.data || data || [];

            lista.forEach((inst) => {
                const opt = document.createElement('option');
                opt.value = inst.id;
                opt.textContent = inst.nombre;
                select.appendChild(opt);
            });
        } catch (e) {
            console.error('[Importar] Error al cargar instituciones:', e);
        }
    }

    async function cargarMarcasEnSelect(item) {
        const select = item.querySelector('.importar-vincular-select');
        if (!select) return;

        try {
            const response = await fetch('/admin/equipos/marcas-list', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            const data = await response.json();
            const lista = data.data || [];

            lista.forEach((marca) => {
                const opt = document.createElement('option');
                opt.value = marca.id;
                opt.textContent = marca.nombre;
                select.appendChild(opt);
            });
        } catch (e) {
            console.error('[Importar] Error al cargar marcas:', e);
        }
    }

    async function cargarCategoriasEnSelect(item) {
        const select = item.querySelector('.importar-vincular-select');
        if (!select) return;

        try {
            const response = await fetch('/admin/equipos/categorias-list', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            const data = await response.json();
            const lista = data.data || [];

            lista.forEach((cat) => {
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.nombre;
                select.appendChild(opt);
            });
        } catch (e) {
            console.error('[Importar] Error al cargar categorías:', e);
        }
    }

    async function cargarDepartamentosEnSelect(item) {
        const select = item.querySelector('.importar-vincular-select');
        if (!select) return;

        try {
            const response = await fetch('/admin/departamentos?todos=1', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await response.json();
            const lista = data.data || data || [];

            lista.forEach((depto) => {
                const opt = document.createElement('option');
                opt.value = depto.id;
                opt.textContent = depto.nombre + (depto.institucion ? ` (${depto.institucion.nombre})` : '');
                select.appendChild(opt);
            });
        } catch (e) {
            console.error('[Importar] Error al cargar departamentos:', e);
        }
    }

    async function cargarResponsablesEnSelect(item) {
        const select = item.querySelector('.importar-vincular-select');
        if (!select) return;

        try {
            const response = await fetch('/admin/responsables?todos=1', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await response.json();
            const lista = data.data || data || [];

            lista.forEach((resp) => {
                const opt = document.createElement('option');
                opt.value = resp.id;
                opt.textContent = resp.nombre;
                select.appendChild(opt);
            });
        } catch (e) {
            console.error('[Importar] Error al cargar responsables:', e);
        }
    }

    // ============================================================
    // PROCESAR IMPORTACIÓN
    // ============================================================
    async function onProcesar() {
        // Validar que el mapeo tenga serial
        const mapeoActual = recolectarMapeoActual();
        const tieneSerial = mapeoActual.some((m) => m.campo === 'serial');

        if (!tieneSerial) {
            mostrarToast('Debes mapear al menos la columna "Serial" para continuar', 'warning');
            return;
        }

        cambiarEstado(ESTADO_PROCESANDO);

        try {
            const payload = {
                _token: state.csrfToken,
                session_id: state.sessionId,
                mapeo: mapeoActual,
                decisiones: state.decisiones,
                modo: 'crear_entidades',
            };

            const response = await fetch(URLS.procesar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': state.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Error al procesar la importación');
            }

            state.resultado = data.data;
            state.importacionActiva = false;

            // Limpiar persistencia
            try {
                localStorage.removeItem(STORAGE_KEY);
            } catch (e) { /* ignore */ }

            renderResultado(data.data);
            cambiarEstado(ESTADO_RESULTADO);

            mostrarToast(data.message || 'Importación finalizada', 'success');

        } catch (error) {
            console.error('[Importar] Error al procesar:', error);
            mostrarToast(error.message || 'Error al procesar la importación', 'error');
            cambiarEstado(ESTADO_PREVIEW);
        }
    }

    function recolectarMapeoActual() {
        const resultado = [];

        if (!el.tablaMapeoBody) return resultado;

        el.tablaMapeoBody.querySelectorAll('tr').forEach((tr) => {
            const select = tr.querySelector('.importar-mapeo-select');
            const columna = tr.querySelector('.importar-mapeo-columna')?.textContent || '';

            if (!select) return;

            const campo = select.value;
            if (!campo) return; // ignorar columnas sin mapeo

            resultado.push({
                columna_excel: columna,
                campo: campo,
                confianza: 'alta',
                indice: parseInt(select.dataset.indice) || null,
            });
        });

        return resultado;
    }

    // ============================================================
    // RENDER RESULTADO
    // ============================================================
    function renderResultado(data) {
        // ----- Resumen -----
        if (el.resumenExitos) el.resumenExitos.textContent = data.exitos ?? 0;
        if (el.resumenErrores) el.resumenErrores.textContent = data.total_errores ?? 0;
        if (el.resumenAdvertencias) el.resumenAdvertencias.textContent = data.total_advertencias ?? 0;
        if (el.resumenTiempo) el.resumenTiempo.textContent = (data.tiempo_segundos ?? 0) + 's';

        // ----- Errores -----
        renderErrores(data.errores || []);

        // ----- Advertencias -----
        renderAdvertencias(data.advertencias || []);
    }

    function renderErrores(errores) {
        if (!el.seccionErrores || !el.tablaErroresBody) return;

        if (errores.length === 0) {
            el.seccionErrores.style.display = 'none';
            return;
        }

        el.tablaErroresBody.innerHTML = '';

        errores.forEach((err) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="importar-error-fila">${err.fila ?? '-'}</td>
                <td class="importar-error-columna">${escapeHtml(err.columna ?? '-')}</td>
                <td class="importar-error-valor">${escapeHtml(String(err.valor ?? '-'))}</td>
                <td class="importar-error-motivo">${escapeHtml(err.motivo ?? '-')}</td>
            `;
            el.tablaErroresBody.appendChild(tr);
        });

        el.seccionErrores.style.display = 'block';
    }

    function renderAdvertencias(advertencias) {
        if (!el.seccionAdvertencias || !el.tablaAdvertenciasBody) return;

        if (advertencias.length === 0) {
            el.seccionAdvertencias.style.display = 'none';
            return;
        }

        el.tablaAdvertenciasBody.innerHTML = '';

        advertencias.forEach((adv) => {
            const tr = document.createElement('tr');

            const activoId = adv.activo_id ?? null;
            const linkEditar = activoId
                ? `<a href="/admin/inventario?activo=${activoId}" class="importar-advertencia-link" target="_blank">Editar</a>`
                : '<span class="text-muted small">—</span>';

            tr.innerHTML = `
                <td class="importar-error-fila">${adv.fila ?? '-'}</td>
                <td class="importar-advertencia-serial">${escapeHtml(adv.serial ?? '-')}</td>
                <td class="importar-advertencia-motivo">${escapeHtml(adv.motivo ?? '-')}</td>
                <td class="text-center">${linkEditar}</td>
            `;
            el.tablaAdvertenciasBody.appendChild(tr);
        });

        el.seccionAdvertencias.style.display = 'block';
    }

    // ============================================================
    // ACCIONES SECUNDARIAS
    // ============================================================
    async function onCancelar() {
        if (!state.sessionId) {
            volverAEstadoInicial();
            return;
        }

        const confirmar = window.confirm(
            '¿Cancelar la importación actual? Se perderá el progreso.'
        );
        if (!confirmar) return;

        await cancelarImportacion();
        volverAEstadoInicial();
    }

    async function cancelarImportacion() {
        if (!state.sessionId) return;

        try {
            await fetch(URLS.cancelar(state.sessionId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': state.csrfToken,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
        } catch (e) {
            console.warn('[Importar] Error al cancelar:', e);
        } finally {
            state.importacionActiva = false;
            state.sessionId = null;
            try {
                localStorage.removeItem(STORAGE_KEY);
            } catch (e) { /* ignore */ }
        }
    }

    function onVolverAlInicio() {
        volverAEstadoInicial();
    }

    function volverAEstadoInicial() {
        state.archivo = null;
        state.sessionId = null;
        state.totalFilas = 0;
        state.nombreArchivo = '';
        state.mapeo = [];
        state.entidadesNoReconocidas = {};
        state.decisiones = {};
        state.resultado = null;
        state.importacionActiva = false;

        // Reset UI
        if (el.archivoInput) el.archivoInput.value = '';
        if (el.infoArchivo) el.infoArchivo.style.display = 'none';
        if (el.dropzone) el.dropzone.style.display = 'flex';
        if (el.btnAnalizar) el.btnAnalizar.disabled = true;
        if (el.tablaMapeoBody) el.tablaMapeoBody.innerHTML = '';
        if (el.entidadesContainer) el.entidadesContainer.innerHTML = '';
        if (el.tablaErroresBody) el.tablaErroresBody.innerHTML = '';
        if (el.tablaAdvertenciasBody) el.tablaAdvertenciasBody.innerHTML = '';

        cambiarEstado(ESTADO_INICIAL);
    }

    // ============================================================
    // PERSISTENCIA (verificar sesión previa al cargar)
    // ============================================================
    function verificarSesionPersistida() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return;

            const datos = JSON.parse(raw);
            if (!datos.sessionId) return;

            // Hay una sesión anterior. Ofrecer reanudar o descartar.
            const reanudar = window.confirm(
                `Tienes una importación anterior sin terminar (${datos.nombreArchivo || 'archivo'}, ${datos.totalFilas || 0} filas).\n\n` +
                '¿Deseas descartarla y empezar de nuevo?\n\n' +
                '(Si cancelas, se mantendrá en segundo plano hasta que recargues).'
            );

            if (reanudar) {
                // Limpiar en el servidor
                fetch(`/admin/importar/cancelar/${datos.sessionId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': state.csrfToken,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                }).catch(() => {});

                localStorage.removeItem(STORAGE_KEY);
            }
        } catch (e) {
            console.warn('[Importar] Error al verificar sesión persistida:', e);
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    // ============================================================
    // CAMBIO DE ESTADO (UI)
    // ============================================================
    function cambiarEstado(nuevoEstado) {
        // Ocultar todos
        if (el.estadoInicial)    el.estadoInicial.style.display = 'none';
        if (el.estadoAnalizando) el.estadoAnalizando.style.display = 'none';
        if (el.estadoPreview)    el.estadoPreview.style.display = 'none';
        if (el.estadoProcesando) el.estadoProcesando.style.display = 'none';
        if (el.estadoResultado)  el.estadoResultado.style.display = 'none';

        // Mostrar el correcto
        switch (nuevoEstado) {
            case ESTADO_INICIAL:    if (el.estadoInicial)    el.estadoInicial.style.display = 'block'; break;
            case ESTADO_ANALIZANDO: if (el.estadoAnalizando) el.estadoAnalizando.style.display = 'block'; break;
            case ESTADO_PREVIEW:    if (el.estadoPreview)    el.estadoPreview.style.display = 'block'; break;
            case ESTADO_PROCESANDO: if (el.estadoProcesando) el.estadoProcesando.style.display = 'block'; break;
            case ESTADO_RESULTADO:  if (el.estadoResultado)  el.estadoResultado.style.display = 'block'; break;
        }
    }

    // ============================================================
    // UTILIDADES
    // ============================================================
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    function mostrarToast(mensaje, tipo) {
        tipo = tipo || 'success';

        const colores = {
            success: '#1e7e34',
            error:   '#c5221f',
            warning: '#f6c23e',
            info:    '#1e3c72',
        };

        const iconos = {
            success: '✅',
            error:   '❌',
            warning: '⚠️',
            info:    'ℹ️',
        };

        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 99999;
            background: ${colores[tipo]}; color: white;
            padding: 14px 20px; border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            font-weight: 500; font-size: 0.9rem;
            display: flex; align-items: center; gap: 10px;
            max-width: 420px; white-space: pre-line;
            animation: importar-toast-in 0.3s ease-out;
            cursor: pointer;
        `;
        toast.innerHTML = `<span style="font-size:1.1rem;">${iconos[tipo] || ''}</span><span style="flex:1;">${escapeHtml(mensaje)}</span>`;

        toast.addEventListener('click', () => {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        });

        document.body.appendChild(toast);

        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.transition = 'opacity 0.3s';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }
        }, 4500);
    }

    // Inyectar la animación del toast
    (function inyectarAnimacion() {
        if (document.getElementById('importar-animaciones')) return;

        const style = document.createElement('style');
        style.id = 'importar-animaciones';
        style.textContent = `
            @keyframes importar-toast-in {
                from { transform: translateX(100%); opacity: 0; }
                to   { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    })();

})();