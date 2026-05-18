<div class="modal fade" id="modalModelo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white">Nuevo Modelo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formModelo">
                @csrf
                <input type="hidden" id="formMethodModelo" name="_method" value="POST">
                <input type="hidden" id="modeloId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marca <span class="text-danger">*</span></label>
                            <select class="form-select" id="modelo_marca_id" name="marca_id" required>
                                <option value="">Seleccionar marca...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select" id="modelo_categoria_id" name="categoria_id" required>
                                <option value="">Seleccionar categoría...</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del Modelo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modelo_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="modelo_descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Especificaciones Técnicas</label>
                        <textarea class="form-control" id="modelo_especificaciones" name="especificaciones" rows="3" placeholder="Procesador, RAM, Almacenamiento, etc..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>