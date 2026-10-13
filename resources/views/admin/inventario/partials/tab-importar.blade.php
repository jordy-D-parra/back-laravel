{{-- ============================================================ --}}
{{-- TAB: IMPORTAR ACTIVOS DESDE EXCEL                            --}}
{{-- Se renderiza dentro del tab "Importar" del módulo Inventario --}}
{{-- ============================================================ --}}

<div id="importarApp" class="importar-app">

    {{-- ============================================================ --}}
    {{-- ESTADO 1: INICIAL (subir archivo)                            --}}
    {{-- ============================================================ --}}
    <div id="importarEstadoInicial" class="importar-estado">

        <div class="importar-hero">
            <div class="importar-hero-icono">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
            </div>
            <div class="importar-hero-texto">
                <h4>Importar activos desde Excel</h4>
                <p>Sube un archivo Excel con los activos y el sistema los registrará automáticamente.</p>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- PASO 1: Descargar plantilla                  --}}
        {{-- ============================================ --}}
        <div class="importar-paso">
            <div class="importar-paso-numero">1</div>
            <div class="importar-paso-contenido">
                <div class="importar-paso-titulo">Descarga la plantilla</div>
                <div class="importar-paso-descripcion">
                    Usa la plantilla oficial para asegurarte de que las columnas coincidan con lo que espera el sistema.
                </div>
                <a href="{{ route('admin.importar.plantilla') }}"
                   class="btn btn-outline-primary-dark btn-sm mt-2">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; margin-right:4px;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Descargar plantilla
                </a>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- PASO 2: Subir archivo                        --}}
        {{-- ============================================ --}}
        <div class="importar-paso">
            <div class="importar-paso-numero">2</div>
            <div class="importar-paso-contenido">
                <div class="importar-paso-titulo">Sube tu archivo Excel</div>
                <div class="importar-paso-descripcion">
                    Arrastra el archivo aquí o haz clic para seleccionarlo. Máximo 10 MB · .xlsx o .xls
                </div>

                <form id="importarFormSubida" enctype="multipart/form-data" class="importar-form">
                    @csrf
                    <label for="importarArchivo" class="importar-dropzone" id="importarDropzone">
                        <input type="file"
                               id="importarArchivo"
                               name="archivo"
                               accept=".xlsx,.xls"
                               class="importar-input-file">
                        <div class="importar-dropzone-icono">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="12" y1="18" x2="12" y2="12"/>
                                <line x1="9" y1="15" x2="12" y2="12"/>
                                <line x1="15" y1="15" x2="12" y2="12"/>
                            </svg>
                        </div>
                        <div class="importar-dropzone-texto">
                            <strong id="importarDropzoneTitulo">Arrastra tu Excel aquí</strong>
                            <span id="importarDropzoneSubtitulo">o haz clic para seleccionarlo</span>
                        </div>
                    </label>
                </form>

                <div id="importarInfoArchivo" class="importar-info-archivo" style="display: none;">
                    <div class="importar-info-archivo-icono">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>
                    <div class="importar-info-archivo-detalles">
                        <strong id="importarNombreArchivo">archivo.xlsx</strong>
                        <span id="importarTamanoArchivo">0 KB</span>
                    </div>
                    <button type="button" class="importar-info-archivo-quitar" id="importarBtnQuitar" title="Quitar archivo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <div class="importar-acciones mt-3">
                    <button type="button"
                            class="btn btn-primary-dark"
                            id="importarBtnAnalizar"
                            style="color: #fff;"
                            disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; margin-right:6px;">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                        Analizar archivo
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- ESTADO 2: ANALIZANDO                                         --}}
    {{-- ============================================================ --}}
    <div id="importarEstadoAnalizando" class="importar-estado" style="display: none;">
        <div class="importar-loading-card">
            <div class="importar-loading-spinner"></div>
            <h5 class="importar-loading-titulo">Analizando archivo...</h5>
            <p class="importar-loading-descripcion">
                Detectando columnas, entidades no reconocidas y validando el formato.
            </p>
            <div class="importar-loading-nombre" id="importarLoadingNombre"></div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ESTADO 3: PREVIEW (mapeo + entidades no reconocidas)         --}}
    {{-- ============================================================ --}}
    <div id="importarEstadoPreview" class="importar-estado" style="display: none;">

        <div class="importar-preview-header">
            <div class="importar-preview-header-icono">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="importar-preview-header-texto">
                <h5 id="importarPreviewNombre">archivo.xlsx</h5>
                <p>
                    <span id="importarPreviewTotalFilas">0</span> filas detectadas
                    <span id="importarPreviewAlertaCritica" class="importar-preview-alerta-critica" style="display: none;">
                        · Faltan campos obligatorios
                    </span>
                </p>
            </div>
        </div>

        {{-- Sección A: Mapeo de columnas --}}
        <div class="importar-seccion">
            <div class="importar-seccion-titulo">
                <div class="importar-seccion-titulo-icono">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <line x1="3" y1="9" x2="21" y2="9"/>
                        <line x1="9" y1="21" x2="9" y2="9"/>
                    </svg>
                </div>
                <div>
                    <h6>Mapeo de columnas</h6>
                    <p>Confirma qué columna del Excel corresponde a cada campo. Puedes corregirlo.</p>
                </div>
            </div>

            <div class="importar-tabla-mapeo-wrapper">
                <table class="importar-tabla-mapeo">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Columna del Excel</th>
                            <th style="width: 45%;">Se mapeará a</th>
                            <th style="width: 20%; text-align: center;">Confianza</th>
                        </tr>
                    </thead>
                    <tbody id="importarTablaMapeoBody">
                        {{-- Dinámico --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sección B: Entidades no reconocidas --}}
        <div class="importar-seccion" id="importarSeccionEntidades" style="display: none;">
            <div class="importar-seccion-titulo">
                <div class="importar-seccion-titulo-icono importar-seccion-titulo-icono-warn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <div>
                    <h6>Entidades no reconocidas</h6>
                    <p>Decide qué hacer con estos valores que no existen todavía en el sistema.</p>
                </div>
            </div>

            <div id="importarEntidadesContainer">
                {{-- Dinámico: secciones por tipo de entidad --}}
            </div>
        </div>

        {{-- Acciones del preview --}}
        <div class="importar-preview-acciones">
            <button type="button" class="btn btn-outline-primary-dark" id="importarBtnCancelar">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:4px;">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Cancelar
            </button>
            <button type="button" class="btn btn-primary-dark" id="importarBtnProcesar" style="color: #fff;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; margin-right:6px;">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Importar ahora
            </button>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- ESTADO 4: PROCESANDO                                         --}}
    {{-- ============================================================ --}}
    <div id="importarEstadoProcesando" class="importar-estado" style="display: none;">
        <div class="importar-loading-card">
            <div class="importar-loading-spinner"></div>
            <h5 class="importar-loading-titulo">Importando activos...</h5>
            <p class="importar-loading-descripcion">
                Procesando las filas. Esto puede tardar unos segundos.
            </p>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ESTADO 5: RESULTADO                                          --}}
    {{-- ============================================================ --}}
    <div id="importarEstadoResultado" class="importar-estado" style="display: none;">

        {{-- Resumen de totales --}}
        <div class="importar-resumen">
            <div class="importar-resumen-item importar-resumen-exito">
                <div class="importar-resumen-numero" id="importarResumenExitos">0</div>
                <div class="importar-resumen-label">Importados</div>
            </div>
            <div class="importar-resumen-item importar-resumen-error">
                <div class="importar-resumen-numero" id="importarResumenErrores">0</div>
                <div class="importar-resumen-label">Con error</div>
            </div>
            <div class="importar-resumen-item importar-resumen-advertencia">
                <div class="importar-resumen-numero" id="importarResumenAdvertencias">0</div>
                <div class="importar-resumen-label">Advertencias</div>
            </div>
            <div class="importar-resumen-item">
                <div class="importar-resumen-numero" id="importarResumenTiempo">0s</div>
                <div class="importar-resumen-label">Tiempo</div>
            </div>
        </div>

        {{-- Errores --}}
        <div class="importar-seccion" id="importarSeccionErrores" style="display: none;">
            <div class="importar-seccion-titulo">
                <div class="importar-seccion-titulo-icono importar-seccion-titulo-icono-error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                </div>
                <div>
                    <h6>Filas con error</h6>
                    <p>Estas filas no pudieron importarse. Corrígelas y vuelve a subir el archivo, o edítalas después en el sistema.</p>
                </div>
            </div>

            <div class="importar-tabla-errores-wrapper">
                <table class="importar-tabla-errores">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Fila</th>
                            <th style="width: 15%;">Columna</th>
                            <th style="width: 25%;">Valor</th>
                            <th style="width: 50%;">Motivo</th>
                        </tr>
                    </thead>
                    <tbody id="importarTablaErroresBody">
                        {{-- Dinámico --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Advertencias --}}
        <div class="importar-seccion" id="importarSeccionAdvertencias" style="display: none;">
            <div class="importar-seccion-titulo">
                <div class="importar-seccion-titulo-icono importar-seccion-titulo-icono-warn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <div>
                    <h6>Advertencias</h6>
                    <p>Estos activos se importaron con campos incompletos. Puedes editarlos para completarlos.</p>
                </div>
            </div>

            <div class="importar-tabla-errores-wrapper">
                <table class="importar-tabla-errores">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Fila</th>
                            <th style="width: 25%;">Serial</th>
                            <th style="width: 45%;">Motivo</th>
                            <th style="width: 20%; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="importarTablaAdvertenciasBody">
                        {{-- Dinámico --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Acciones finales --}}
        <div class="importar-preview-acciones">
            <button type="button" class="btn btn-outline-primary-dark" id="importarBtnVolver">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:4px;">
                    <polyline points="1 4 1 10 7 10"/>
                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                </svg>
                Importar otro archivo
            </button>
            <a href="{{ route('admin.inventario.index') }}" class="btn btn-primary-dark" style="color: #fff;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline; margin-right:6px;">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                </svg>
                Ver inventario
            </a>
        </div>

    </div>

</div>

{{-- ============================================================ --}}
{{-- TEMPLATES (para renderizado dinámico en JS)                  --}}
{{-- ============================================================ --}}

{{-- Template: Fila de mapeo --}}
<template id="tplImportarFilaMapeo">
    <tr>
        <td class="importar-mapeo-columna"></td>
        <td>
            <select class="form-select form-select-sm importar-mapeo-select"></select>
        </td>
        <td class="text-center">
            <span class="importar-mapeo-confianza"></span>
        </td>
    </tr>
</template>

{{-- Template: Sección de entidad no reconocida --}}
<template id="tplImportarEntidadSeccion">
    <div class="importar-entidad-seccion" data-tipo="">
        <div class="importar-entidad-titulo">
            <span class="importar-entidad-titulo-icono"></span>
            <span class="importar-entidad-titulo-texto"></span>
            <span class="importar-entidad-titulo-count"></span>
        </div>
        <div class="importar-entidad-items"></div>
    </div>
</template>

{{-- Template: Item de entidad no reconocida --}}
<template id="tplImportarEntidadItem">
    <div class="importar-entidad-item" data-nombre="">
        <div class="importar-entidad-item-nombre"></div>
        <div class="importar-entidad-item-acciones">
            <label class="importar-radio">
                <input type="radio" name="" value="crear">
                <span>Crear</span>
            </label>
            <label class="importar-radio">
                <input type="radio" name="" value="vincular">
                <span>Vincular</span>
            </label>
            <label class="importar-radio">
                <input type="radio" name="" value="ignorar">
                <span>Ignorar</span>
            </label>
        </div>
        <div class="importar-entidad-item-vincular" style="display: none;">
            <select class="form-select form-select-sm importar-vincular-select">
                <option value="">Selecciona una entidad existente...</option>
            </select>
        </div>
    </div>
</template>

{{-- Template: Fila de error --}}
<template id="tplImportarFilaError">
    <tr>
        <td class="importar-error-fila"></td>
        <td class="importar-error-columna"></td>
        <td class="importar-error-valor"></td>
        <td class="importar-error-motivo"></td>
    </tr>
</template>

{{-- Template: Fila de advertencia --}}
<template id="tplImportarFilaAdvertencia">
    <tr>
        <td class="importar-advertencia-fila"></td>
        <td class="importar-advertencia-serial"></td>
        <td class="importar-advertencia-motivo"></td>
        <td class="text-center">
            <a href="#" class="importar-advertencia-link" target="_blank">Editar</a>
        </td>
    </tr>
</template>