@extends('layouts.dashboard')

@section('title', 'Gestión de Entidades')

@section('styles')
    @vite(['resources/css/admin-entidades.css'])
    <style>
        /* Estilos adicionales específicos para mejorar la vista */
        .tab-pane {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
        }

        .empty-state svg {
            stroke: #cbd5e1;
        }

        .empty-state h6 {
            color: #0f172a;
            margin-top: 1rem;
        }

        .empty-state p {
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .badge-institucion {
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 500;
        }

        .badge-institucion-primary { background: #dbeafe; color: #2563eb; }
        .badge-institucion-success { background: #dcfce7; color: #16a34a; }
        .badge-institucion-info { background: #cffafe; color: #0891b2; }
        .badge-institucion-warning { background: #fef3c7; color: #d97706; }
        .badge-institucion-danger { background: #fee2e2; color: #dc2626; }
        .badge-institucion-secondary { background: #f1f5f9; color: #64748b; }

        /* Mejora en el árbol */
        .arbol-raiz > .arbol-nodo-header .arbol-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .arbol-raiz > .arbol-nodo-header .badge-activo {
            background: rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        .arbol-raiz > .arbol-nodo-header .badge-inactivo {
            background: rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .arbol-raiz > .arbol-nodo-header .arbol-sub {
            color: rgba(255, 255, 255, 0.7);
        }

        .arbol-rama > .arbol-nodo-header {
            border-left: 3px solid var(--primary-dark);
        }

        .arbol-rama-directa > .arbol-nodo-header {
            border-left: 3px dashed var(--primary-dark);
        }

        .arbol-hoja > .arbol-nodo-header {
            border-left: 2px solid #cbd5e1;
        }

        /* Mejora en los badges de la tabla */
        .badge-count-table {
            background: var(--primary-lighter);
            color: var(--primary-dark);
            padding: 0.15rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Animación para el toggle */
        .arbol-toggle {
            transition: transform 0.3s ease;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Header con fondo degradado -->
    <div class="d-flex justify-content-between align-items-center mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 16px; padding: 1.5rem 2rem; color: white;">
        <div>
            <h4 style="color: white; font-weight: 700; margin: 0;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right: 10px;">
                    <rect x="4" y="8" width="16" height="12" rx="1"/>
                    <path d="M8 20V8M16 20V8M4 12h16"/>
                </svg>
                Gestión de Entidades
            </h4>
            <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem; margin: 0;">Instituciones, departamentos y responsables</p>
        </div>

    </div>

    <!-- Stats Cards -->
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
                <span class="tab-badge">{{ $totalInstituciones }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="departamentos-tab" data-bs-toggle="tab" data-bs-target="#departamentos" type="button" role="tab">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                    <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M8 8h8M8 12h6M8 16h4"/>
                </svg>
                Departamentos
                <span class="tab-badge">{{ $totalDepartamentos }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="responsables-tab" data-bs-toggle="tab" data-bs-target="#responsables" type="button" role="tab">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" style="width: 18px; height: 18px;">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
                Responsables
                <span class="tab-badge">{{ $totalResponsables }}</span>
            </button>
        </li>

    </ul>

    <div class="tab-content" id="entidadesTabContent">

        <!-- TAB INSTITUCIONES -->
        <div class="tab-pane fade show active" id="instituciones" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2 flex-wrap">
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-white border-end-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0" id="buscarInstituciones" placeholder="Buscar institución..." style="border-left: none;">
                    </div>

                </div>
                @if(auth()->user()->hasPermission('crear-institucion'))
                <button class="btn btn-primary-dark btn-sm" onclick="abrirModalInstitucion()" style="color:#f1f5f9">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" fill="none" style="width: 16px; height: 16px;">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nueva Institución
                </button>
                @endif
            </div>
            <div class="table-container" id="tablaInstituciones">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>

        <!-- TAB DEPARTAMENTOS -->
        <div class="tab-pane fade" id="departamentos" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2 flex-wrap">
                    <div class="input-group" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0" id="buscarDepartamentos" placeholder="Buscar departamento...">
                    </div>

                </div>
                @if(auth()->user()->hasPermission('crear-departamento'))
                <button class="btn btn-primary-dark btn-sm" onclick="abrirModalDepartamento()" style="color:#f1f5f9">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" fill="none" style="width: 16px; height: 16px;">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo Departamento
                </button>
                @endif
            </div>
            <div class="table-container" id="tablaDepartamentos">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>

        <!-- TAB RESPONSABLES -->
        <div class="tab-pane fade" id="responsables" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2 flex-wrap">
                    <div class="input-group" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0" id="buscarResponsables" placeholder="Buscar responsable...">
                    </div>

                </div>
                @if(auth()->user()->hasPermission('crear-responsable'))
                <button class="btn btn-primary-dark btn-sm" onclick="abrirModalResponsable()" style="color:#f1f5f9">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" fill="none" style="width: 16px; height: 16px;">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo Responsable
                </button>
                @endif
            </div>
            <div class="table-container" id="tablaResponsables">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>

        <!-- TAB ÁRBOL -->
        <div class="tab-pane fade" id="arbol" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2 flex-wrap">
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-white border-end-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0" id="buscarArbol" placeholder="Buscar en el árbol...">
                    </div>
                    <button class="btn btn-outline-primary-dark btn-sm" onclick="expandirTodo()">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width: 14px; height: 14px;">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                        Expandir Todo
                    </button>
                    <button class="btn btn-outline-primary-dark btn-sm" onclick="colapsarTodo()">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width: 14px; height: 14px;">
                            <polyline points="6 15 12 9 18 15"/>
                        </svg>
                        Colapsar Todo
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
<!-- MODAL INSTITUCIÓN - WIZARD DE 2 PASOS       -->
<!-- ============================================ -->
<div class="modal fade" id="modalInstitucion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title" id="modalInstitucionLabel" style="color: #fff">
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;">
                            <rect x="4" y="8" width="16" height="12" rx="1"/>
                            <path d="M8 20V8M16 20V8M4 12h16"/>
                        </svg>
                        Nueva Institución
                    </h5>
                    <span class="badge bg-light text-dark" id="stepIndicatorInst">Paso 1 de 2</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formInstitucion" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethodInstitucion">
                <input type="hidden" name="id" id="institucionId">

                <div class="modal-body p-0">
                    <!-- Barra de progreso -->
                    <div class="px-4 pt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="step-label" id="stepLabelInst1" style="color:#1e3c72;">
                                <span class="step-circle active">1</span> Institución
                            </span>
                            <span class="step-label" id="stepLabelInst2" style="color:#adb5bd;">
                                <span class="step-circle">2</span> Representante
                            </span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" id="progressBarInst" role="progressbar" style="width: 50%;"></div>
                        </div>
                    </div>

                    <!-- PASO 1: INSTITUCIÓN -->
                    <div class="step-content px-4 pt-4" id="stepInst1">
                        <div class="text-center mb-4">
                            <h6 style="color:#1e3c72; font-weight:600;">
                                <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:24px;height:24px;display:inline;margin-right:8px;">
                                    <rect x="4" y="8" width="16" height="12" rx="1"/>
                                    <path d="M8 20V8M16 20V8M4 12h16"/>
                                </svg>
                                Datos de la Institución
                            </h6>
                            <p class="text-muted small">Complete la información básica de la institución</p>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="inst_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="inst_nombre" name="nombre" required maxlength="200" placeholder="Ej: Instituto de Salud, Alcaldía...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="inst_informacion" class="form-label">Información <span class="text-danger">*</span></label>
                                <textarea class="form-control form-control-lg" id="inst_informacion" name="informacion" rows="3" required maxlength="500" placeholder="Breve descripción..."></textarea>
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="ubicacion-selects mt-3">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:20px;height:20px;">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <h6 class="fw-bold mb-0" style="color:#1e3c72;">Ubicación Geográfica</h6>
                                <span class="badge bg-secondary ms-2">Obligatorio</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="estado_id" class="form-label">Estado <span class="text-danger">*</span></label>
                                    <select class="form-select" id="estado_id" name="estado_id" required>
                                        <option value="">Seleccionar estado...</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="municipio_id" class="form-label">Municipio <span class="text-danger">*</span></label>
                                    <select class="form-select" id="municipio_id" name="municipio_id" disabled required>
                                        <option value="">Seleccionar estado primero</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="parroquia_id" class="form-label">Parroquia <span class="text-danger">*</span></label>
                                    <select class="form-select" id="parroquia_id" name="parroquia_id" disabled required>
                                        <option value="">Seleccionar municipio primero</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PASO 2: REPRESENTANTE -->
                    <div class="step-content px-4 pt-4" id="stepInst2" style="display: none;">
                        <div class="text-center mb-4">
                            <h6 style="color:#1e3c72; font-weight:600;">
                                <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:24px;height:24px;display:inline;margin-right:8px;">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                </svg>
                                Datos del Representante
                            </h6>
                            <p class="text-muted small">Complete los datos del responsable de la institución</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="inst_representante_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="inst_representante_nombre" name="representante_nombre" required maxlength="150" placeholder="Nombre del representante">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="inst_representante_cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="inst_representante_cargo" name="representante_cargo" required maxlength="100" value="Representante">
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
                            <textarea class="form-control" id="inst_representante_direccion" name="representante_direccion" rows="2" maxlength="300" placeholder="Dirección del representante..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary-dark" id="btnAnteriorInst" style="display: none;" onclick="cambiarPasoInst(-1)">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:4px;">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                        Anterior
                    </button>
                    <button type="button" class="btn btn-primary-dark" id="btnSiguienteInst" onclick="cambiarPasoInst(1)">
                        Siguiente
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-left:4px;">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarInst" style="display: none;">
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:4px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Guardar Institución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL DEPARTAMENTO - WIZARD DE 3 PASOS      -->
<!-- ============================================ -->
<div class="modal fade" id="modalDepartamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title" id="modalDepartamentoLabel">
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M8 8h8M8 12h6M8 16h4"/>
                        </svg>
                        Nuevo Departamento
                    </h5>
                    <span class="badge bg-light text-dark" id="stepIndicator">Paso 1 de 3</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formDepartamento" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethodDepartamento">
                <input type="hidden" name="id" id="departamentoId">
                <input type="hidden" name="usar_responsable_institucion" id="usar_responsable_institucion_input" value="0">
                <input type="hidden" name="responsable_id" id="responsable_id_input" value="">

                <div class="modal-body p-0">
                    <!-- Barra de progreso -->
                    <div class="px-4 pt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="step-label" id="stepLabel1" style="color:#1e3c72;">
                                <span class="step-circle active">1</span> Institución
                            </span>
                            <span class="step-label" id="stepLabel2" style="color:#adb5bd;">
                                <span class="step-circle">2</span> Departamento
                            </span>
                            <span class="step-label" id="stepLabel3" style="color:#adb5bd;">
                                <span class="step-circle">3</span> Representante
                            </span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 33%;"></div>
                        </div>
                    </div>

                    <!-- PASO 1: INSTITUCIÓN -->
                    <div class="step-content px-4 pt-4" id="step1">
                        <div class="text-center mb-4">
                            <h6 style="color:#1e3c72; font-weight:600;">
                                <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:24px;height:24px;display:inline;margin-right:8px;">
                                    <rect x="4" y="8" width="16" height="12" rx="1"/>
                                    <path d="M8 20V8M16 20V8M4 12h16"/>
                                </svg>
                                ¿A qué institución pertenece el departamento?
                            </h6>
                            <p class="text-muted small">Seleccione una opción para continuar</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card institucion-card h-100 active" id="cardGobernacion">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="1.8" fill="none" style="width:48px;height:48px;">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/>
                                                <polyline points="9 22 9 12 15 12 15 22"/>
                                            </svg>
                                        </div>
                                        <h5 style="color:#1e3c72; font-weight:600;">Gobernación</h5>
                                        <p class="text-muted small">Departamento de la Gobernación del Estado Yaracuy</p>
                                        <span class="badge bg-warning text-dark">⭐ Predeterminada</span>
                                        <div class="mt-3">
                                            <input type="radio" name="tipo_institucion" value="gobernacion" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card institucion-card h-100" id="cardOtra">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <svg viewBox="0 0 24 24" stroke="#6c757d" stroke-width="1.8" fill="none" style="width:48px;height:48px;">
                                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                                <path d="M8 8h8M8 12h6M8 16h4"/>
                                            </svg>
                                        </div>
                                        <h5 style="color:#495057;">Otra Institución</h5>
                                        <p class="text-muted small">Hospital, Escuela, Alcaldía, etc.</p>
                                        <div class="mt-3">
                                            <input type="radio" name="tipo_institucion" value="otra">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4" id="contenedorOtraInstitucion" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Seleccionar Institución</label>
                                <button type="button" class="btn btn-sm btn-outline-primary-dark" onclick="abrirModalInstitucionDesdeDepartamento()">
                                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" fill="none" style="width:14px;height:14px;display:inline;margin-right:4px;">
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    Nueva
                                </button>
                            </div>
                            <select class="form-select" id="depto_institucion_id" name="institucion_id">
                                <option value="">Seleccionar institución...</option>
                                @foreach($instituciones as $inst)
                                    <option value="{{ $inst->id }}" {{ $inst->nombre == 'Gobernación del Estado Yaracuy' ? 'selected' : '' }}>
                                        {{ $inst->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- PASO 2: DEPARTAMENTO -->
                    <div class="step-content px-4 pt-4" id="step2" style="display: none;">
                        <div class="text-center mb-4">
                            <h6 style="color:#1e3c72; font-weight:600;">
                                <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:24px;height:24px;display:inline;margin-right:8px;">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <path d="M8 8h8M8 12h6M8 16h4"/>
                                </svg>
                                Datos del Departamento
                            </h6>
                            <p class="text-muted small">Complete la información del departamento</p>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="depto_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="depto_nombre" name="nombre" required maxlength="100" placeholder="Ej: Informática, Recursos Humanos...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="depto_ubicacion" class="form-label">Ubicación <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="depto_ubicacion" name="ubicacion" required maxlength="200" placeholder="Ej: Sede Principal, Piso 3...">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="depto_informacion" class="form-label">Información <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="depto_informacion" name="informacion" rows="3" required maxlength="500" placeholder="Describa brevemente el departamento..."></textarea>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sinInstitucion" onchange="toggleInstitucionDepartamento()">
                            <label class="form-check-label" for="sinInstitucion" style="font-size: 0.85rem;">
                                <span class="text-danger">⚠️</span> Sin institución (departamento independiente)
                            </label>
                        </div>
                    </div>

                    <!-- PASO 3: REPRESENTANTE -->
                    <div class="step-content px-4 pt-4" id="step3" style="display: none;">
                        <div class="text-center mb-4">
                            <h6 style="color:#1e3c72; font-weight:600;">
                                <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:24px;height:24px;display:inline;margin-right:8px;">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                </svg>
                                Datos del Representante
                            </h6>
                            <p class="text-muted small">Complete los datos del responsable del departamento</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="depto_representante_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="depto_representante_nombre" name="representante_nombre" required maxlength="150" placeholder="Nombre del responsable">
                                <div class="form-check mt-2" id="contenedorCheckRepresentante">
                                    <input class="form-check-input" type="checkbox" id="usarRepresentanteInstitucion" onchange="toggleRepresentanteInstitucion()">
                                    <label class="form-check-label" for="usarRepresentanteInstitucion" style="font-size: 0.85rem;">
                                        Usar representante de la institución
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="depto_representante_cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="depto_representante_cargo" name="representante_cargo" required maxlength="100" value="Jefe de Departamento">
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
                            <textarea class="form-control" id="depto_representante_direccion" name="representante_direccion" rows="2" maxlength="300" placeholder="Dirección del responsable..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary-dark" id="btnAnterior" style="display: none;" onclick="cambiarPaso(-1)">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:4px;">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                        Anterior
                    </button>
                    <button type="button" class="btn btn-primary-dark" id="btnSiguiente" onclick="cambiarPaso(1)">
                        Siguiente
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-left:4px;">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardar" style="display: none;">
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:4px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Guardar Departamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL RESPONSABLE                           -->
<!-- ============================================ -->
<div class="modal fade" id="modalResponsable" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalResponsableLabel">
                    <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;display:inline;margin-right:8px;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    Nuevo Responsable
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formResponsable" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethodResponsable">
                <input type="hidden" name="id" id="responsableId">
                <input type="hidden" name="origen" id="responsableOrigen" value="directo">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="resp_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="resp_nombre" name="nombre" required maxlength="150" placeholder="Nombre completo">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="resp_cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="resp_cargo" name="cargo" required maxlength="100" placeholder="Ej: Director, Jefe, Coordinador...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="resp_documento" class="form-label">Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="resp_documento" name="documento" required maxlength="50" placeholder="V-12345678">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="resp_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="resp_telefono" name="telefono" required maxlength="20" placeholder="0412-1234567">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="resp_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="resp_email" name="email" maxlength="100" placeholder="correo@ejemplo.com">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="resp_institucion_id" class="form-label">Institución <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="resp_institucion_id" name="institucion_id" required>
                                <option value="">Seleccionar institución...</option>
                                @foreach($instituciones as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="resp_departamento_id" class="form-label">Departamento (opcional)</label>
                            <select class="form-select form-select-lg" id="resp_departamento_id" name="departamento_id">
                                <option value="">Sin departamento</option>
                            </select>
                            <small class="text-muted">Seleccione primero una institución para ver sus departamentos</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="resp_direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="resp_direccion" name="direccion" rows="2" maxlength="300" placeholder="Dirección del responsable..."></textarea>
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
                    <button type="submit" class="btn btn-primary-dark">
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:4px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL INSTITUCIÓN RÁPIDA                    -->
<!-- ============================================ -->
<div class="modal fade" id="modalInstitucionRapida" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;display:inline;margin-right:8px;">
                        <rect x="4" y="8" width="16" height="12" rx="1"/>
                        <path d="M8 20V8M16 20V8M4 12h16"/>
                    </svg>
                    Nueva Institución
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formInstitucionRapida" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:0.85rem; border-radius: 10px;">
                        <svg viewBox="0 0 24 24" stroke="#856404" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:6px;">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4M12 8h.01"/>
                        </svg>
                        <strong>Importante:</strong> El representante se debe registrar después desde la sección de responsables.
                    </div>
                    <div class="mb-3">
                        <label for="inst_rapida_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="inst_rapida_nombre" name="nombre" required maxlength="200" placeholder="Ej: Instituto de Salud, Alcaldía...">
                    </div>
                    <div class="mb-3">
                        <label for="inst_rapida_informacion" class="form-label">Información <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="inst_rapida_informacion" name="informacion" rows="2" required maxlength="500" placeholder="Breve descripción de la institución..."></textarea>
                    </div>
                    <!-- Ubicación -->
                    <div class="ubicacion-selects mt-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:16px;height:16px;">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span class="fw-bold" style="color:#1e3c72;">Ubicación</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label for="rapida_estado_id" class="form-label small fw-bold">Estado <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="rapida_estado_id" name="estado_id" required>
                                    <option value="">Seleccionar...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="rapida_municipio_id" class="form-label small fw-bold">Municipio <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="rapida_municipio_id" name="municipio_id" disabled required>
                                    <option value="">Seleccionar estado</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="rapida_parroquia_id" class="form-label small fw-bold">Parroquia <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="rapida_parroquia_id" name="parroquia_id" disabled required>
                                    <option value="">Seleccionar municipio</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:4px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Guardar Institución
                    </button>
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
    <script>
        // Inicializar selects de ubicación para la institución rápida
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar estados para la institución rápida
            const rapidaEstado = document.getElementById('rapida_estado_id');
            const rapidaMunicipio = document.getElementById('rapida_municipio_id');
            const rapidaParroquia = document.getElementById('rapida_parroquia_id');

            if (rapidaEstado) {
                fetch('/admin/ubicaciones/estados', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        rapidaEstado.innerHTML = '<option value="">Seleccionar estado...</option>';
                        data.data.forEach(estado => {
                            rapidaEstado.innerHTML += `<option value="${estado.id}">${estado.nombre}</option>`;
                        });
                        rapidaEstado.disabled = false;
                    }
                });

                rapidaEstado.addEventListener('change', function() {
                    const estadoId = this.value;
                    if (!estadoId) {
                        rapidaMunicipio.innerHTML = '<option value="">Seleccionar estado primero</option>';
                        rapidaMunicipio.disabled = true;
                        rapidaParroquia.innerHTML = '<option value="">Seleccionar municipio primero</option>';
                        rapidaParroquia.disabled = true;
                        return;
                    }

                    rapidaMunicipio.innerHTML = '<option value="">Cargando municipios...</option>';
                    rapidaMunicipio.disabled = true;

                    fetch(`/admin/ubicaciones/estados/${estadoId}/municipios`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            rapidaMunicipio.innerHTML = '<option value="">Seleccionar municipio...</option>';
                            data.data.forEach(municipio => {
                                rapidaMunicipio.innerHTML += `<option value="${municipio.id}">${municipio.nombre}</option>`;
                            });
                            rapidaMunicipio.disabled = false;
                        }
                    });
                });

                rapidaMunicipio.addEventListener('change', function() {
                    const municipioId = this.value;
                    if (!municipioId) {
                        rapidaParroquia.innerHTML = '<option value="">Seleccionar municipio primero</option>';
                        rapidaParroquia.disabled = true;
                        return;
                    }

                    rapidaParroquia.innerHTML = '<option value="">Cargando parroquias...</option>';
                    rapidaParroquia.disabled = true;

                    fetch(`/admin/ubicaciones/municipios/${municipioId}/parroquias`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            rapidaParroquia.innerHTML = '<option value="">Seleccionar parroquia...</option>';
                            data.data.forEach(parroquia => {
                                rapidaParroquia.innerHTML += `<option value="${parroquia.id}">${parroquia.nombre}</option>`;
                            });
                            rapidaParroquia.disabled = false;
                        }
                    });
                });
            }
        });
    </script>
@endsection
