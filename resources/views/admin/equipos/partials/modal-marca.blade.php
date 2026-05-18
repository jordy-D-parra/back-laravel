<div class="modal fade" id="modalMarca" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white">Nueva Marca</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMarca">
                @csrf
                <input type="hidden" id="formMethodMarca" name="_method" value="POST">
                <input type="hidden" id="marcaId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="marca_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="marca_descripcion" name="descripcion" rows="3"></textarea>
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