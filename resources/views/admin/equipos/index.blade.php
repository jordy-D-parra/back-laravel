@extends('layouts.dashboard')

@section('title', 'Catálogo de Equipos')

@section('styles')
    @vite(['resources/css/admin-equipos.css'])
    @vite(['resources/css/contrast-system.css'])
    @vite(['resources/css/skeleton-loading.css'])
    @vite(['resources/css/smooth-modals.css'])
    <style>
        .bg-primary-dark {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        .modal-header.bg-primary-dark .btn-close {
            filter: brightness(0) invert(1);
        }
        .highlight {
            background: #fff3cd;
            padding: 1px 3px;
            border-radius: 3px;
        }
        .stat-icon-circle svg {
            width: 24px;
            height: 24px;
            stroke: #1e3c72;
            stroke-width: 1.8;
            fill: none;
        }
        .stat-card-mini:hover .stat-icon-circle svg {
            stroke: white;
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
                @if(auth()->user()->hasPermission('crear-marca'))
                <li>
                    <a class="dropdown-item" href="#" onclick="abrirModalMarca(); return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                            <rect x="4" y="8" width="16" height="12" rx="1"/>
                        </svg>
                        Nueva Marca
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('crear-categoria-equipo'))
                <li>
                    <a class="dropdown-item" href="#" onclick="abrirModalCategoria(); return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                        </svg>
                        Nueva Categoría
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('crear-modelo'))
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="#" onclick="abrirModalModelo(); return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                            <rect x="4" y="4" width="16" height="16" rx="2"/>
                            <line x1="9" y1="4" x2="9" y2="20"/>
                        </svg>
                        Nuevo Modelo
                    </a>
                </li>
                @endif
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="4" y="8" width="16" height="12" rx="1"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalCategorias }}</div>
                <div class="stat-label">Categorías</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalModelos }}</div>
                <div class="stat-label">Modelos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                    <line x1="9" y1="4" x2="9" y2="20"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalActivos }}</div>
                <div class="stat-label">Registros Activos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs nav-tabs-custom" id="equipoTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#marcas">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <rect x="4" y="8" width="16" height="12" rx="1"/>
                </svg>
                Marcas <span class="tab-badge">{{ $totalMarcas }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#categorias">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                </svg>
                Categorías <span class="tab-badge">{{ $totalCategorias }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#modelos">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                </svg>
                Modelos <span class="tab-badge">{{ $totalModelos }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#arbol">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <circle cx="12" cy="6" r="4"/>
                    <path d="M12 10v4M8 18h8"/>
                </svg>
                Árbol
            </button>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <!-- TAB MARCAS -->
        <div class="tab-pane fade show active" id="marcas">
            <div class="filters-bar mb-3">
                <div class="flex-grow-1">
                    <input type="text" id="buscarMarcas" class="form-control" placeholder="Buscar marca...">
                </div>
                <select id="filtroEstadoMarcas" class="form-select" style="width:130px">
                    <option value="">Todos</option>
                    <option value="activo">Activas</option>
                    <option value="inactivo">Inactivas</option>
                </select>
                @if(auth()->user()->hasPermission('crear-marca'))
                <button class="btn btn-primary-dark" onclick="abrirModalMarca()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nueva Marca
                </button>
                @endif
            </div>
            <div class="table-container">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Modelos</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaMarcas">
                        <tr><td colspan="5" class="text-center py-4 text-muted">Cargando...<\/td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB CATEGORÍAS -->
        <div class="tab-pane fade" id="categorias">
            <div class="filters-bar mb-3">
                <div class="flex-grow-1">
                    <input type="text" id="buscarCategorias" class="form-control" placeholder="Buscar categoría...">
                </div>
                <select id="filtroEstadoCategorias" class="form-select" style="width:130px">
                    <option value="">Todos</option>
                    <option value="activo">Activas</option>
                    <option value="inactivo">Inactivas</option>
                </select>
                @if(auth()->user()->hasPermission('crear-categoria-equipo'))
                <button class="btn btn-primary-dark" onclick="abrirModalCategoria()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nueva Categoría
                </button>
                @endif
            </div>
            <div class="table-container">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Modelos</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCategorias">
                        <tr><td colspan="5" class="text-center py-4 text-muted">Cargando...<\/td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB MODELOS -->
        <div class="tab-pane fade" id="modelos">
            <div class="filters-bar mb-3">
                <div class="flex-grow-1">
                    <input type="text" id="buscarModelos" class="form-control" placeholder="Buscar modelo...">
                </div>
                <select id="filtroMarcaModelos" class="form-select" style="width:160px">
                    <option value="">Todas las marcas</option>
                </select>
                <select id="filtroCategoriaModelos" class="form-select" style="width:160px">
                    <option value="">Todas las categorías</option>
                </select>
                <select id="filtroEstadoModelos" class="form-select" style="width:130px">
                    <option value="">Todos</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
                @if(auth()->user()->hasPermission('crear-modelo'))
                <button class="btn btn-primary-dark" onclick="abrirModalModelo()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo Modelo
                </button>
                @endif
            </div>
            <div class="table-container">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Modelo</th>
                            <th>Marca</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaModelos">
                        <tr><td colspan="6" class="text-center py-4 text-muted">Cargando...<\/td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB ÁRBOL -->
        <div class="tab-pane fade" id="arbol">
            <div class="filters-bar mb-3">
                <div class="flex-grow-1">
                    <input type="text" id="buscarArbol" class="form-control" placeholder="Buscar en el árbol...">
                </div>
                <button class="btn btn-outline-primary-dark" onclick="expandirTodo()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                    Expandir Todo
                </button>
                <button class="btn btn-outline-primary-dark" onclick="colapsarTodo()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                        <polyline points="6 15 12 9 18 15"/>
                    </svg>
                    Colapsar Todo
                </button>
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

<!-- MODAL CATEGORIA -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalCategoriaLabel">Nueva Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalModeloLabel">Nuevo Modelo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formModelo">
                @csrf
                <input type="hidden" id="formMethodModelo" name="_method" value="POST">
                <input type="hidden" id="modeloId" name="id">
                <div class="modal-body">
                    <!-- Datos del Modelo -->
                    <h6 class="fw-bold mb-3" style="color: #1e3c72;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                            <rect x="4" y="4" width="16" height="16" rx="2"/>
                        </svg>
                        Datos del Modelo
                    </h6>
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
                        <input type="text" class="form-control" id="modelo_nombre" name="nombre" required placeholder="Ej: Latitude 5540">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="modelo_descripcion" name="descripcion" rows="2" placeholder="Descripción general del modelo"></textarea>
                    </div>

                    <hr>

                    <!-- Componentes del Modelo -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color: #1e3c72;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            Componentes del Modelo
                        </h6>
                        @if(auth()->user()->hasPermission('editar-modelo'))
                        <button type="button" class="btn btn-sm btn-outline-primary-dark" onclick="agregarComponenteModelo()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Agregar Componente
                        </button>
                        @endif
                    </div>
                    <div id="componentesModeloContainer">
                        <div class="text-center text-muted py-3" id="sinComponentesMsg">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-2 opacity-50">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            <p class="mb-0">No hay componentes agregados.</p>
                            <p class="small">Haga clic en "Agregar Componente" para añadir uno.</p>
                        </div>
                    </div>
                    <div id="componentesExistentesContainer" style="display:none;">
                        <h6 class="fw-bold mb-2 mt-3" style="color: #1e3c72;">Componentes Registrados</h6>
                        <div id="listaComponentesExistentes"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                        </svg>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template para componente nuevo -->
<template id="templateComponenteModelo">
    <div class="componente-modelo-item border rounded p-3 mb-2 bg-light">
        <div class="row align-items-end">
            <div class="col-md-4 mb-2">
                <label class="form-label small">Tipo <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm comp-tipo" required>
                    <option value="">Seleccionar...</option>
                    <option value="RAM">RAM</option>
                    <option value="Disco">Disco</option>
                    <option value="Batería">Batería</option>
                    <option value="Cargador">Cargador</option>
                    <option value="Pantalla">Pantalla</option>
                    <option value="Teclado">Teclado</option>
                    <option value="Mouse">Mouse</option>
                    <option value="Procesador">Procesador</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Cable">Cable</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small">Descripción <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm comp-descripcion" placeholder="Memoria RAM DDR4" required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small">Capacidad Máxima</label>
                <input type="text" class="form-control form-control-sm comp-capacidad" placeholder="8GB, 512GB, 65W">
            </div>
            <div class="col-md-1 mb-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.componente-modelo-item').remove(); verificarSinComponentes()" title="Eliminar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<!-- MODAL DETALLE -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark">
                <h5 class="modal-title text-white" id="modalDetalleLabel">Detalle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
    @vite(['resources/js/validations.js'])
@endsection
