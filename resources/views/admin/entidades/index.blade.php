@extends('layouts.dashboard')

@section('title', 'Gestión de Entidades')

@section('styles')
    @vite(['resources/css/admin-entidades.css'])
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="color: #1e3c72; font-weight: 700; margin: 0;">Gestión de Entidades</h4>
            <p style="color: #6c757d; font-size: 0.85rem; margin: 0;">Instituciones, departamentos y responsables</p>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalInstituciones }}</div>
                <div class="stat-label">Instituciones</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 22px; height: 22px;">
                    <rect x="4" y="8" width="16" height="12" rx="1"/><path d="M8 20V8M16 20V8M4 12h16"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalDepartamentos }}</div>
                <div class="stat-label">Departamentos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 22px; height: 22px;">
                    <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M8 8h8M8 12h6M8 16h4"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalResponsables }}</div>
                <div class="stat-label">Responsables</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 22px; height: 22px;">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalActivas }}</div>
                <div class="stat-label">Activas</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 22px; height: 22px;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs-custom" id="entidadesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="instituciones-tab" data-bs-toggle="tab" data-bs-target="#instituciones" type="button" role="tab">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                    <rect x="4" y="8" width="16" height="12" rx="1"/><path d="M8 20V8M16 20V8M4 12h16"/>
                </svg>
                Instituciones
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="departamentos-tab" data-bs-toggle="tab" data-bs-target="#departamentos" type="button" role="tab">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                    <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M8 8h8M8 12h6M8 16h4"/>
                </svg>
                Departamentos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="responsables-tab" data-bs-toggle="tab" data-bs-target="#responsables" type="button" role="tab">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
                Responsables
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="arbol-tab" data-bs-toggle="tab" data-bs-target="#arbol" type="button" role="tab">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                Árbol
            </button>
        </li>
    </ul>

    <div class="tab-content" id="entidadesTabContent">

        <!-- Instituciones -->
        <div class="tab-pane fade show active" id="instituciones" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="buscarInstituciones" placeholder="Buscar institución..." style="max-width: 280px;">
                    <select class="form-select form-select-sm" id="filtroEstadoInstituciones" style="max-width: 150px;">
                        <option value="">Todos</option>
                        <option value="activo">Activas</option>
                        <option value="inactivo">Inactivas</option>
                    </select>
                </div>
                @if(auth()->user()->hasPermission('crear-institucion'))
                <button class="btn btn-primary-dark btn-sm" onclick="abrirModalInstitucion()">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width: 16px; height: 16px; margin-right: 4px;">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nueva
                </button>
                @endif
            </div>
            <div class="table-container" id="tablaInstituciones">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>

        <!-- Departamentos -->
        <div class="tab-pane fade" id="departamentos" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="buscarDepartamentos" placeholder="Buscar departamento..." style="max-width: 250px;">
                    <select class="form-select form-select-sm" id="filtroInstitucionDepartamentos" style="max-width: 220px;">
                        <option value="">Todas las instituciones</option>
                        @foreach($instituciones as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->hasPermission('crear-departamento'))
                <button class="btn btn-primary-dark btn-sm" onclick="abrirModalDepartamento()">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width: 16px; height: 16px; margin-right: 4px;">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo
                </button>
                @endif
            </div>
            <div class="table-container" id="tablaDepartamentos">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>

        <!-- Responsables -->
        <div class="tab-pane fade" id="responsables" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="buscarResponsables" placeholder="Buscar responsable..." style="max-width: 250px;">
                    <select class="form-select form-select-sm" id="filtroInstitucionResponsables" style="max-width: 200px;">
                        <option value="">Todas las instituciones</option>
                        @foreach($instituciones as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->hasPermission('crear-responsable'))
                <button class="btn btn-primary-dark btn-sm" onclick="abrirModalResponsable()" style="display: none;">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width: 16px; height: 16px; margin-right: 4px;">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo
                </button>
                @endif
            </div>
            <div class="table-container" id="tablaResponsables">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>

        <!-- Árbol -->
        <div class="tab-pane fade" id="arbol" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="buscarArbol" placeholder="Buscar en el árbol..." style="max-width: 280px;">
                    <button class="btn btn-outline-primary-dark btn-sm" onclick="expandirTodo()">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width: 14px; height: 14px; margin-right: 4px;">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                        Expandir
                    </button>
                    <button class="btn btn-outline-primary-dark btn-sm" onclick="colapsarTodo()">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width: 14px; height: 14px; margin-right: 4px;">
                            <polyline points="6 15 12 9 18 15"/>
                        </svg>
                        Colapsar
                    </button>
                </div>
            </div>
            <div class="table-container" id="arbolContenedor">
                <p class="text-center py-4 text-muted">Cargando árbol...</p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODALES -->
<!-- ============================================ -->

<!-- Modal Institución -->
<div class="modal fade" id="modalInstitucion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formInstitucion" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethodInstitucion">
                <input type="hidden" name="id" id="institucionId">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstitucionLabel">Nueva Institución</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Datos de la Institución -->
                    <h6 style="color:#1e3c72; font-weight:600; margin-bottom:1rem; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem;">Datos de la Institución</h6>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="inst_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inst_nombre" name="nombre" required maxlength="200">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="inst_ubicacion" class="form-label">Ubicación <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inst_ubicacion" name="ubicacion" required maxlength="200">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inst_informacion" class="form-label">Información <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="inst_informacion" name="informacion" rows="2" required maxlength="500"></textarea>
                    </div>

                    <hr>

                    <!-- Datos del Representante -->
                    <h6 style="color:#1e3c72; font-weight:600; margin-bottom:1rem; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem;">Datos del Representante <small class="text-muted">(se creará como responsable)</small></h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="inst_representante_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inst_representante_nombre" name="representante_nombre" required maxlength="150">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="inst_representante_cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inst_representante_cargo" name="representante_cargo" required maxlength="100" value="Representante">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="inst_representante_documento" class="form-label">Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inst_representante_documento" name="representante_documento" required maxlength="50" placeholder="V-12345678">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="inst_representante_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inst_representante_telefono" name="representante_telefono" required maxlength="20" placeholder="0412-1234567">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="inst_representante_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="inst_representante_email" name="representante_email" maxlength="100" placeholder="correo@ejemplo.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inst_representante_direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="inst_representante_direccion" name="representante_direccion" rows="2" maxlength="300"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary-dark" onclick="limpiarFormInstitucion()">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px;margin-right:4px;">
                            <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                        </svg>
                        Limpiar
                    </button>
                    <button type="submit" class="btn btn-primary-dark">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Departamento (CORREGIDO) -->
<div class="modal fade" id="modalDepartamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formDepartamento" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethodDepartamento">
                <input type="hidden" name="id" id="departamentoId">
                <!-- Campo oculto para indicar que se usa el representante de la institución -->
                <input type="hidden" name="usar_representante_institucion" id="usarRepresentanteInstitucionHidden" value="0">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalDepartamentoLabel">Nuevo Departamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Datos del Departamento -->
                    <h6 style="color:#1e3c72; font-weight:600; margin-bottom:1rem; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem;">Datos del Departamento</h6>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sinInstitucion" onchange="toggleInstitucionDepartamento()">
                            <label class="form-check-label" for="sinInstitucion" style="font-size: 0.85rem;">Sin institución (departamento independiente)</label>
                        </div>
                    </div>
                    <div class="mb-3" id="contenedorInstitucionDepto">
                        <label for="depto_institucion_id" class="form-label">Institución</label>
                        <select class="form-select" id="depto_institucion_id" name="institucion_id" onchange="cargarRepresentanteInstitucion()">
                            <option value="">Seleccionar institución...</option>
                            @foreach($instituciones as $inst)
                                <option value="{{ $inst->id }}" data-representante-nombre="{{ $inst->representante }}" data-institucion-id="{{ $inst->id }}">{{ $inst->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="depto_nombre" class="form-label">Nombre <span class="text-danger">*</span> <span class="validacion-icono" id="iconoNombreDepto" style="display:none;"></span></label>
                            <input type="text" class="form-control" id="depto_nombre" name="nombre" required maxlength="100" oninput="validarNombreDepto()">
                            <div class="valid-feedback" id="feedbackNombreDeptoOk" style="display:none;">Nombre disponible</div>
                            <div class="invalid-feedback" id="feedbackNombreDeptoError" style="display:none;">Este nombre ya existe</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="depto_ubicacion" class="form-label">Ubicación <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="depto_ubicacion" name="ubicacion" required maxlength="200">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="depto_informacion" class="form-label">Información <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="depto_informacion" name="informacion" rows="2" required maxlength="500"></textarea>
                    </div>

                    <hr>

                    <!-- Datos del Representante -->
                    <h6 style="color:#1e3c72; font-weight:600; margin-bottom:1rem; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem;">Datos del Representante <small class="text-muted">(se creará como responsable)</small></h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="depto_representante_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="depto_representante_nombre" name="representante_nombre" required maxlength="150">
                                <button type="button" class="btn btn-outline-secondary" id="btnCopiarRepresentante" style="display: none;" title="Copiar datos del representante de la institución">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="form-check mt-2" id="contenedorCheckRepresentante" style="display:none;">
                                <input class="form-check-input" type="checkbox" id="usarRepresentanteInstitucion" onchange="toggleRepresentanteInstitucion()">
                                <label class="form-check-label" for="usarRepresentanteInstitucion" style="font-size: 0.85rem;">Usar representante de la institución</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="depto_representante_cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="depto_representante_cargo" name="representante_cargo" required maxlength="100" value="Jefe de Departamento">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="depto_representante_documento" class="form-label">Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="depto_representante_documento" name="representante_documento" required maxlength="50" placeholder="V-12345678">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="depto_representante_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="depto_representante_telefono" name="representante_telefono" required maxlength="20" placeholder="0412-1234567">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="depto_representante_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="depto_representante_email" name="representante_email" maxlength="100" placeholder="correo@ejemplo.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="depto_representante_direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="depto_representante_direccion" name="representante_direccion" rows="2" maxlength="300"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary-dark" onclick="limpiarFormDepartamento()">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px;margin-right:4px;">
                            <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                        </svg>
                        Limpiar
                    </button>
                    <button type="submit" class="btn btn-primary-dark">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Responsable -->
<div class="modal fade" id="modalResponsable" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formResponsable" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethodResponsable">
                <input type="hidden" name="id" id="responsableId">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalResponsableLabel">Nuevo Responsable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="resp_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="resp_nombre" name="nombre" required maxlength="150">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="resp_cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="resp_cargo" name="cargo" required maxlength="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="resp_documento" class="form-label">Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="resp_documento" name="documento" required maxlength="50" placeholder="V-12345678">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="resp_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="resp_telefono" name="telefono" required maxlength="20" placeholder="0412-1234567">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="resp_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="resp_email" name="email" maxlength="100" placeholder="correo@ejemplo.com">
                    </div>
                    <div class="mb-3">
                        <label for="resp_institucion_id" class="form-label">Institución <span class="text-danger">*</span></label>
                        <select class="form-select" id="resp_institucion_id" name="institucion_id" required>
                            <option value="">Seleccionar institución...</option>
                            @foreach($instituciones as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="resp_departamento_id" class="form-label">Departamento</label>
                        <select class="form-select" id="resp_departamento_id" name="departamento_id">
                            <option value="">Sin departamento</option>
                        </select>
                        <small class="text-muted">Seleccione primero una institución para ver sus departamentos</small>
                    </div>
                    <div class="mb-3">
                        <label for="resp_direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="resp_direccion" name="direccion" rows="2" maxlength="300"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary-dark" onclick="limpiarFormResponsable()">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:14px;height:14px;margin-right:4px;">
                            <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                        </svg>
                        Limpiar
                    </button>
                    <button type="submit" class="btn btn-primary-dark">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleLabel">Detalle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="delete-warning" id="deleteWarning" style="display: none;"></div>
                <p>¿Está seguro de eliminar <strong id="deleteNombre"></strong>?</p>
                <p id="deleteAdvertencia" style="display: none; font-size: 0.85rem; color: #c5221f;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    @vite(['resources/js/admin-entidades.js'])
@endsection
