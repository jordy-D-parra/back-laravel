{{-- MODAL: CORREO + WIZARD DE CONVERSIÓN A FICHA DE SOPORTE --}}
<div class="modal fade" id="modalCorreoSoporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 20px; overflow: hidden;">

            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title text-white">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:8px;">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Correo de Soporte Técnico
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding: 2rem; max-height: 80vh; overflow-y: auto;">

                {{-- ============ INFO DEL CORREO ============ --}}
                <div id="infoCorreoSoporte" class="mb-4">
                    <div class="alert alert-info">
                        <div class="row g-2">
                            <div class="col-md-6"><strong>De:</strong> <span id="correoFromSoporte"></span></div>
                            <div class="col-md-6"><strong>Fecha:</strong> <span id="correoFechaSoporte"></span></div>
                            <div class="col-12 mt-2"><strong>Asunto:</strong> <span id="correoAsuntoSoporte"></span></div>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded"
                         style="white-space: pre-wrap; max-height: 200px; overflow-y: auto;"
                         id="correoCuerpoSoporte"></div>
                </div>

                {{-- Botón para iniciar wizard --}}
                <div id="botonIniciarWizardSoporte" class="text-center py-3"></div>

                {{-- ============ WIZARD ============ --}}
                <div id="wizardContainerSoporte" style="display: none;">

                    {{-- Indicador de pasos --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <span><span class="step-circle active" id="stepSop1Circle">1</span> Equipo</span>
                            <span><span class="step-circle" id="stepSop2Circle">2</span> Institución</span>
                            <span><span class="step-circle" id="stepSop3Circle">3</span> Técnico y Fechas</span>
                            <span><span class="step-circle" id="stepSop4Circle">4</span> Confirmar</span>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar" id="wizardProgressSoporte"
                                 style="width: 25%; background: linear-gradient(90deg, #1e3c72, #2a5298);"></div>
                        </div>
                    </div>

                    <form id="formWizardSoporte" novalidate>
                        @csrf
                        <input type="hidden" id="wizardCorreoSoporteId">

                        {{-- ============ PASO 1: EQUIPO ============ --}}
                        <div class="wizard-step-soporte active" id="stepSop1">
                            <h6 class="fw-bold mb-3" style="color: #1e3c72;">Paso 1: Equipo a Reparar</h6>

                            <div class="mb-3">
                                <label class="form-label">¿El equipo está en el inventario?</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_equipo"
                                               id="equipoExistente" value="existente" checked>
                                        <label class="form-check-label" for="equipoExistente">
                                            <strong>Existente</strong> (ya está en el sistema)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_equipo"
                                               id="equipoNuevo" value="nuevo">
                                        <label class="form-check-label" for="equipoNuevo">
                                            <strong>Nuevo</strong> (equipo externo)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Equipo existente --}}
                            <div id="equipoExistenteFields">
                                <div class="mb-3">
                                    <label class="form-label">Seleccionar Activo del Inventario</label>
                                    <select name="activo_id" id="wzActivoId" class="form-select">
                                        <option value="">Seleccionar activo...</option>
                                        @foreach(\App\Models\Activo::with('modelo.marca')->orderBy('serial')->limit(300)->get() as $act)
                                            <option value="{{ $act->id }}">
                                                {{ $act->serial }} -
                                                {{ $act->modelo?->marca?->nombre }} {{ $act->modelo?->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Equipo nuevo: SOLO datos básicos, marca/modelo/categoría --}}
                            <div id="equipoNuevoFields" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Serial *</label>
                                        <input type="text" name="nuevo_equipo[serial]" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Marca *</label>
                                        <input type="text" name="nuevo_equipo[marca]" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Modelo *</label>
                                        <input type="text" name="nuevo_equipo[modelo_nombre]" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Categoría *</label>
                                        <select name="nuevo_equipo[categoria_id]" class="form-select">
                                            <option value="">Seleccionar...</option>
                                            @foreach(\App\Models\Categoria::where('activo', true)->get() as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-primary-dark" onclick="irPasoSoporte(2)">
                                    Siguiente →
                                </button>
                            </div>
                        </div>

                        {{-- ============ PASO 2: INSTITUCIÓN Y RESPONSABLE ============ --}}
                        <div class="wizard-step-soporte" id="stepSop2" style="display: none;">
                            <h6 class="fw-bold mb-3" style="color: #1e3c72;">Paso 2: Institución y Responsable</h6>

                            {{-- Aviso si es equipo existente --}}
                            <div id="avisoEquipoExistente" class="alert alert-info py-2" style="display: none; font-size: 0.85rem;">
                                ℹ️ El equipo ya está en el inventario. La institución y el responsable se heredarán automáticamente del activo seleccionado.
                            </div>

                            {{-- Solo se muestran si es equipo NUEVO --}}
                            <div id="camposInstitucionNuevo">
                                <div class="mb-3">
                                    <label class="form-label">Institución *</label>
                                    <select name="nuevo_equipo[institucion_id]" id="wzInstitucionSoporte" class="form-select">
                                        <option value="">Seleccionar institución...</option>
                                        @foreach(\App\Models\Institucion::where('activo', true)->orderBy('nombre')->get() as $inst)
                                            <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Departamento (opcional)</label>
                                    <select name="nuevo_equipo[departamento_id]" id="wzDepartamentoSoporte" class="form-select">
                                        <option value="">Sin departamento</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Responsable *</label>
                                    <select name="nuevo_equipo[responsable_id]" id="wzResponsableSoporte" class="form-select">
                                        <option value="">Seleccionar institución primero</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ubicación</label>
                                    <input type="text" name="nuevo_equipo[ubicacion]" class="form-control"
                                           value="Taller de reparación" placeholder="Taller de reparación">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-primary-dark" onclick="irPasoSoporte(1)">
                                    ← Anterior
                                </button>
                                <button type="button" class="btn btn-primary-dark" onclick="irPasoSoporte(3)">
                                    Siguiente →
                                </button>
                            </div>
                        </div>

                        {{-- ============ PASO 3: TÉCNICO Y FECHAS ============ --}}
                        <div class="wizard-step-soporte" id="stepSop3" style="display: none;">
                            <h6 class="fw-bold mb-3" style="color: #1e3c72;">Paso 3: Técnico y Fechas</h6>

                            <div class="mb-3">
                                <label class="form-label">Técnico Responsable</label>
                                <select name="tecnico_id" id="wzTecnicoSoporte" class="form-select">
                                    <option value="">Sin asignar</option>
                                    @foreach(\App\Models\Usuario::whereHas('rol', fn($q) => $q->whereIn('nombre', ['admin', 'tecnico', 'ingeniero']))->with('trabajador')->get() as $tec)
                                        <option value="{{ $tec->id }}">
                                            {{ $tec->trabajador?->nombre }} {{ $tec->trabajador?->apellido }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Usuario que Reporta *</label>
                                <input type="text" name="usuario_reporta_nombre" id="wzUsuarioReporta"
                                       class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Diagnóstico / Problema *</label>
                                <textarea name="diagnostico" id="wzDiagnostico" rows="4"
                                          class="form-control" required minlength="10"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fecha Requerida de Entrega *</label>
                                <input type="date" name="fecha_requerida_entrega"
                                       id="wzFechaRequeridaSoporte" class="form-control"
                                       required min="{{ date('Y-m-d') }}">
                                <small class="text-muted">Fecha en la que el equipo debe estar listo</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" id="wzObservacionesSoporte" rows="2"
                                          class="form-control"></textarea>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-primary-dark" onclick="irPasoSoporte(2)">
                                    ← Anterior
                                </button>
                                <button type="button" class="btn btn-primary-dark" onclick="irPasoSoporte(4)">
                                    Siguiente →
                                </button>
                            </div>
                        </div>

                        {{-- ============ PASO 4: CONFIRMAR ============ --}}
                        <div class="wizard-step-soporte" id="stepSop4" style="display: none;">
                            <h6 class="fw-bold mb-3" style="color: #1e3c72;">Paso 4: Confirmar y Crear Ficha</h6>

                            {{-- ✅ Selector de estado inicial --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Estado Inicial de la Ficha <span class="text-danger">*</span></label>
                                <select name="estado_inicial" id="wzEstadoInicial" class="form-select" required>
                                    <option value="en_proceso" selected>En Proceso (recomendado)</option>
                                    <option value="aceptada">Aceptada (notifica inmediatamente al responsable)</option>
                                    <option value="rechazada">Rechazada</option>
                                </select>
                                <small class="text-muted">
                                    Si elige <strong>Aceptada</strong>, el responsable recibirá un correo notificándole que su solicitud fue aceptada.
                                    Si elige <strong>En Proceso</strong>, recibirá un correo informándole que su solicitud está siendo evaluada.
                                </small>
                            </div>

                            <div class="alert alert-success">
                                <h6 class="fw-bold">Resumen de la Ficha de Soporte</h6>
                                <ul class="mb-0" id="resumenFichaSoporte">
                                    <li>Cargando resumen...</li>
                                </ul>
                            </div>

                            <div class="alert alert-info">
                                <strong>📬 Se enviarán notificaciones a:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Al <strong>responsable del equipo</strong> (según institución/departamento del activo)</li>
                                    <li>Al <strong>técnico asignado</strong> con los detalles de la ficha</li>
                                    <li>A los <strong>administradores</strong> para su seguimiento</li>
                                </ul>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-primary-dark" onclick="irPasoSoporte(3)">
                                    ← Anterior
                                </button>
                                <button type="submit" class="btn btn-success" id="btnGuardarWizardSoporte">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right:6px;">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                        <polyline points="17 21 17 13 7 13 7 21"/>
                                    </svg>
                                    Crear Ficha de Soporte
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>