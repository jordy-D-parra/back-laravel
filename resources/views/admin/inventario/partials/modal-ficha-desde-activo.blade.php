{{-- Modal: Crear ficha de soporte desde cambio de estatus de activo --}}
<div class="modal fade" id="modalFichaDesdeActivo" tabindex="-1" aria-labelledby="modalFichaDesdeActivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalFichaDesdeActivoLabel">
                    Crear ficha de soporte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">

                {{-- Alerta de ficha existente (oculta por defecto) --}}
                <div id="fichaExistenteAlerta" class="alert alert-warning d-none" role="alert">
                    <strong>Este activo ya tiene una ficha abierta.</strong>
                    <div id="fichaExistenteTexto" class="mt-1"></div>
                    <div class="mt-2 d-flex gap-2">
                        <a id="btnVerFichaExistente" href="#" class="btn btn-sm btn-outline-primary" target="_blank">
                            Ver ficha existente
                        </a>
                        <button type="button" id="btnCrearDeTodasFormas" class="btn btn-sm btn-outline-secondary">
                            Crear nueva de todas formas
                        </button>
                    </div>
                </div>

                {{-- Datos del activo (solo lectura) --}}
                <div class="mb-3">
                    <h6 class="text-muted small text-uppercase mb-2">Datos del activo</h6>
                    <div class="row g-2">
                        <div class="col-md-4"><small class="text-muted d-block">Serial</small><strong id="prefillSerial">—</strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Marca</small><strong id="prefillMarca">—</strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Modelo</small><strong id="prefillModelo">—</strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Categoría</small><strong id="prefillCategoria">—</strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Ubicación</small><strong id="prefillUbicacion">—</strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Institución</small><strong id="prefillInstitucion">—</strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Responsable</small><strong id="prefillResponsable">—</strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Reportado por</small><strong id="prefillReportadoPor">—</strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Fecha</small><strong id="prefillFecha">—</strong></div>
                    </div>
                </div>

                <hr>

                {{-- Campos editables --}}
                <form id="formFichaDesdeActivo">
                    <input type="hidden" id="fichaActivoId" name="activo_id">

                    <div class="mb-3">
                        <label for="fichaDiagnostico" class="form-label">
                            Descripción del daño / diagnóstico <span class="text-danger">*</span>
                        </label>
                        <textarea id="fichaDiagnostico" name="diagnostico" class="form-control" rows="3"
                                  minlength="10" required
                                  placeholder="Describe el problema reportado (mínimo 10 caracteres)..."></textarea>
                        <div class="form-text">Este texto quedará como diagnóstico inicial de la ficha.</div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label for="fichaTecnicoId" class="form-label">Técnico asignado (opcional)</label>
                            <select id="fichaTecnicoId" name="tecnico_id" class="form-select">
                                <option value="">Sin asignar</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fichaFechaRequerida" class="form-label">Fecha requerida de entrega (opcional)</label>
                            <input type="date" id="fichaFechaRequerida" name="fecha_requerida_entrega" class="form-control">
                        </div>
                    </div>
                </form>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Omitir
                </button>
                <button type="button" id="btnCrearFichaDesdeActivo" class="btn btn-primary">
                    Crear ficha
                </button>
            </div>

        </div>
    </div>
</div>