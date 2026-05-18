@extends('layouts.dashboard')

@section('title', 'Catálogo de Equipos')

@section('styles')
    @vite(['resources/css/admin-equipos.css'])
    <style>
        .bg-primary-dark {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        .modal-header.bg-primary-dark .btn-close {
            filter: brightness(0) invert(1);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #1e3c72;">Catálogo de Equipos</h3>
            <p class="text-muted mb-0">Gestión de marcas, categorías y modelos</p>
        </div>
        <div class="dropdown">
            <button class="btn btn-primary-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nuevo Registro
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="abrirModalMarca(); return false;">🏢 Nueva Marca</a></li>
                <li><a class="dropdown-item" href="#" onclick="abrirModalCategoria(); return false;">📁 Nueva Categoría</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="abrirModalModelo(); return false;">📱 Nuevo Modelo</a></li>
            </ul>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-row mb-4">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalMarcas }}</div>
                <div class="stat-label">Total Marcas</div>
            </div>
            <div class="stat-icon-circle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="4" y="8" width="16" height="12" rx="1"/>
                    <path d="M8 20V8M16 20V8M4 12h16"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalCategorias }}</div>
                <div class="stat-label">Categorías</div>
            </div>
            <div class="stat-icon-circle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M8 8h8M8 12h6M8 16h4"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalModelos }}</div>
                <div class="stat-label">Modelos</div>
            </div>
            <div class="stat-icon-circle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                    <line x1="9" y1="4" x2="9" y2="20"/>
                    <line x1="15" y1="4" x2="15" y2="20"/>
                    <line x1="4" y1="9" x2="20" y2="9"/>
                    <line x1="4" y1="15" x2="20" y2="15"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalActivos }}</div>
                <div class="stat-label">Registros Activos</div>
            </div>
            <div class="stat-icon-circle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs nav-tabs-custom" id="equipoTab" role="tablist">
        <li class="nav-item"><button class="nav-link active" id="marcas-tab" data-bs-toggle="tab" data-bs-target="#marcas" type="button">🏢 Marcas <span class="tab-badge">{{ $totalMarcas }}</span></button></li>
        <li class="nav-item"><button class="nav-link" id="categorias-tab" data-bs-toggle="tab" data-bs-target="#categorias" type="button">📁 Categorías <span class="tab-badge">{{ $totalCategorias }}</span></button></li>
        <li class="nav-item"><button class="nav-link" id="modelos-tab" data-bs-toggle="tab" data-bs-target="#modelos" type="button">📱 Modelos <span class="tab-badge">{{ $totalModelos }}</span></button></li>
        <li class="nav-item"><button class="nav-link" id="arbol-tab" data-bs-toggle="tab" data-bs-target="#arbol" type="button">🌳 Árbol de Equipos</button></li>
    </ul>

    <div class="tab-content mt-3">
        <!-- TAB MARCAS -->
        <div class="tab-pane fade show active" id="marcas">
            <div class="filters-bar mb-3">
                <div class="flex-grow-1"><input type="text" id="buscarMarcas" class="form-control" placeholder="Buscar marca..."></div>
                <select id="filtroEstadoMarcas" class="form-select" style="width:130px"><option value="">Todos</option><option value="activo">Activas</option><option value="inactivo">Inactivas</option></select>
                <button class="btn btn-primary-dark" onclick="abrirModalMarca()">+ Nueva Marca</button>
            </div>
            <div class="table-container">
                <table class="table table-hover">
                    <thead><tr><th>Nombre</th><th>Descripción</th><th>Modelos</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody id="tablaMarcas"><tr><td colspan="5" class="text-center">Cargando...</td></tr></tbody>
                </table>
            </div>
        </div>

        <!-- TAB CATEGORÍAS -->
        <div class="tab-pane fade" id="categorias">
            <div class="filters-bar mb-3">
                <div class="flex-grow-1"><input type="text" id="buscarCategorias" class="form-control" placeholder="Buscar categoría..."></div>
                <select id="filtroEstadoCategorias" class="form-select" style="width:130px"><option value="">Todos</option><option value="activo">Activas</option><option value="inactivo">Inactivas</option></select>
                <button class="btn btn-primary-dark" onclick="abrirModalCategoria()">+ Nueva Categoría</button>
            </div>
            <div class="table-container">
                <table class="table table-hover">
                    <thead><tr><th>Nombre</th><th>Descripción</th><th>Modelos</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody id="tablaCategorias"><tr><td colspan="5" class="text-center">Cargando...</td></tr></tbody>
                </table>
            </div>
        </div>

        <!-- TAB MODELOS -->
        <div class="tab-pane fade" id="modelos">
            <div class="filters-bar mb-3">
                <div class="flex-grow-1"><input type="text" id="buscarModelos" class="form-control" placeholder="Buscar modelo..."></div>
                <select id="filtroMarcaModelos" class="form-select" style="width:160px"><option value="">Todas las marcas</option></select>
                <select id="filtroCategoriaModelos" class="form-select" style="width:160px"><option value="">Todas las categorías</option></select>
                <select id="filtroEstadoModelos" class="form-select" style="width:130px"><option value="">Todos</option><option value="activo">Activos</option><option value="inactivo">Inactivos</option></select>
                <button class="btn btn-primary-dark" onclick="abrirModalModelo()">+ Nuevo Modelo</button>
            </div>
            <div class="table-container">
                <table class="table table-hover">
                    <thead><tr><th>Modelo</th><th>Marca</th><th>Categoría</th><th>Descripción</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody id="tablaModelos"><tr><td colspan="6" class="text-center">Cargando...</td></tr></tbody>
                </table>
            </div>
        </div>

        <!-- TAB ÁRBOL -->
        <div class="tab-pane fade" id="arbol">
            <div class="filters-bar mb-3">
                <div class="flex-grow-1"><input type="text" id="buscarArbol" class="form-control" placeholder="Buscar en el árbol..."></div>
                <button class="btn btn-outline-primary-dark" onclick="expandirTodo()">Expandir Todo</button>
                <button class="btn btn-outline-primary-dark" onclick="colapsarTodo()">Colapsar Todo</button>
            </div>
            <div class="arbol-contenedor" id="arbolContenedor">Cargando...</div>
        </div>
    </div>
</div>

<!-- MODAL MARCA -->
<div class="modal fade" id="modalMarca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalMarcaLabel">Nueva Marca</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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

<!-- MODAL CATEGORIA -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalCategoriaLabel">Nueva Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCategoria">
                @csrf
                <input type="hidden" id="formMethodCategoria" name="_method" value="POST">
                <input type="hidden" id="categoriaId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoria_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="categoria_descripcion" name="descripcion" rows="3"></textarea>
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

<!-- MODAL MODELO -->
<div class="modal fade" id="modalModelo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalModeloLabel">Nuevo Modelo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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

<!-- MODAL DETALLE -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalDetalleLabel">Detalle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detalleContenido">Cargando...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar?</p>
                <p class="fw-bold text-danger" id="deleteNombre"></p>
                <p class="small text-muted">Esta acción no se puede deshacer.</p>
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
    @vite(['resources/js/admin-equipos.js'])
    
    <script>
        // Debug: Verificar que Bootstrap está disponible
        console.log('Bootstrap disponible:', typeof bootstrap !== 'undefined');
        console.log('Modal disponible:', typeof bootstrap.Modal !== 'undefined');
        
        // Función de prueba para verificar modales
        function testModal() {
            var modalElement = document.getElementById('modalMarca');
            if (modalElement) {
                var modal = new bootstrap.Modal(modalElement);
                modal.show();
                console.log('Modal abierto desde test');
            } else {
                console.error('Modal no encontrado');
            }
        }
        
        // Exponer funciones globalmente
        window.abrirModalMarca = function(id) {
            console.log('abrirModalMarca llamado');
            var modalElement = document.getElementById('modalMarca');
            if (!modalElement) {
                console.error('Modal elemento no encontrado');
                return;
            }
            var modal = new bootstrap.Modal(modalElement);
            document.getElementById('formMarca').reset();
            document.getElementById('formMethodMarca').value = 'POST';
            document.getElementById('marcaId').value = '';
            document.getElementById('modalMarcaLabel').textContent = 'Nueva Marca';
            modal.show();
        };
        
        window.abrirModalCategoria = function(id) {
            console.log('abrirModalCategoria llamado');
            var modalElement = document.getElementById('modalCategoria');
            if (!modalElement) return;
            var modal = new bootstrap.Modal(modalElement);
            document.getElementById('formCategoria').reset();
            document.getElementById('formMethodCategoria').value = 'POST';
            document.getElementById('categoriaId').value = '';
            document.getElementById('modalCategoriaLabel').textContent = 'Nueva Categoría';
            modal.show();
        };
        
        window.abrirModalModelo = function(id) {
            console.log('abrirModalModelo llamado');
            var modalElement = document.getElementById('modalModelo');
            if (!modalElement) return;
            var modal = new bootstrap.Modal(modalElement);
            document.getElementById('formModelo').reset();
            document.getElementById('formMethodModelo').value = 'POST';
            document.getElementById('modeloId').value = '';
            document.getElementById('modalModeloLabel').textContent = 'Nuevo Modelo';
            
            // Cargar selects
            if (typeof cargarSelectsModelo === 'function') {
                cargarSelectsModelo();
            }
            modal.show();
        };
        
        // Probar modal al cargar (opcional)
        setTimeout(function() {
            console.log('Sistema de modales listo');
        }, 1000);
    </script>
@endsection