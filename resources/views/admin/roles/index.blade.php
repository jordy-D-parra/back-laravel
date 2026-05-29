@extends('layouts.dashboard')

@section('title', 'Gestión de Roles y Permisos')

@section('styles')
    @vite(['resources/css/admin-usuarios.css'])
    @vite(['resources/css/admin-roles.css'])
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #1e3c72;">Gestión de Roles y Permisos</h3>
            <p class="text-muted mb-0">Crear roles y asignar permisos específicos a cada rol</p>
        </div>
        <button class="btn btn-primary-dark" onclick="abrirModalRol()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo Rol
        </button>
    </div>

    <!-- Tabla de roles -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Descripción</th>
                        <th>Usuarios</th>
                        <th>Permisos</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaRoles">
                    <tr><td colspan="5" class="text-center py-4 text-muted">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: Crear/Editar Rol -->
<div class="modal fade" id="modalRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalRolLabel">Nuevo Rol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRol">
                @csrf
                <input type="hidden" id="formMethodRol" name="_method" value="POST">
                <input type="hidden" id="rolId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="rol_nombre" name="nombre" required placeholder="Ej: Auditor, Supervisor, Visitante...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="rol_descripcion" name="descripcion" placeholder="Descripción del rol">
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold mb-3" style="color: #1e3c72;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        </svg>
                        Permisos del Rol
                    </h6>
                    <p class="text-muted small mb-3">Seleccione los permisos que tendrá este rol:</p>

                    <div id="permisosContainer">
                        <div class="text-center py-4 text-muted">Cargando permisos...</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">Guardar Rol y Permisos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Detalle del Rol -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalDetalleLabel">Detalle del Rol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">
                <div class="text-center py-4 text-muted">Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Confirmar Eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de eliminar el rol <strong id="deleteRolNombre"></strong>?</p>
                <p class="small text-danger" id="deleteWarning"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['resources/js/admin-roles.js'])
@endsection
