@extends('layouts.dashboard')

@section('title', 'Gestión de Entidades')

@section('styles')
@vite(['resources/css/admin-entidades.css'])
<style>
    .tab-pane { animation: fadeIn 0.3s ease; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .loading-spinner {
        display: flex; align-items: center; justify-content: center;
        padding: 2rem; gap: 8px; color: #6c757d; font-size: 0.85rem;
    }
    .spinner-icon { animation: spin 0.8s linear infinite; width: 18px; height: 18px; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .highlight { background: #fef3c7; padding: 0 2px; border-radius: 3px; font-weight: 600; }

    /* ===== WIZARD ===== */
    .step-circle {
        display: inline-block; width: 30px; height: 30px; line-height: 30px;
        text-align: center; border-radius: 50%; background: #e9ecef; color: #6c757d;
        font-weight: 700; font-size: 0.8rem; margin-right: 6px; transition: all 0.3s ease;
    }
    .step-circle.active {
        background: #1e3c72; color: white;
        box-shadow: 0 0 0 4px rgba(30, 60, 114, 0.15);
    }
    .step-circle.completed { background: #1e7e34; color: white; }
    .step-content { min-height: 280px; animation: fadeInUp 0.4s ease; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .step-label { font-size: 0.8rem; font-weight: 500; transition: color 0.3s ease; }
    .progress { height: 4px; border-radius: 4px; background: #e9ecef; overflow: hidden; }
    .progress-bar {
        height: 100%; border-radius: 4px;
        background: linear-gradient(90deg, #1e3c72, #2a5298);
        transition: width 0.5s ease;
    }
    .resumen-item {
        display: flex; justify-content: space-between;
        padding: 0.6rem 0; border-bottom: 1px dashed #e9ecef;
    }
    .resumen-item:last-child { border-bottom: none; }
    .resumen-item .label {
        font-weight: 600; color: #1e3c72; font-size: 0.8rem;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .resumen-item .value { color: #1a1a1a; font-weight: 500; }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">

    {{-- Header con botón Wizard --}}
    <div class="d-flex justify-content-between align-items-center mb-4"
         style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 16px; padding: 1.5rem 2rem; color: white;">
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
        @if(auth()->user()->hasPermission('crear-institucion'))
        <button class="btn btn-light" onclick="abrirWizardEntidad()" style="border-radius: 30px; font-weight: 600;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2.5" class="me-1" style="display:inline;">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            Nuevo Registro (Wizard)
        </button>
        @endif
    </div>

    {{-- Stats Cards --}}
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

    {{-- Tabs (sin botones de crear) --}}
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

        {{-- TAB INSTITUCIONES --}}
        <div class="tab-pane fade show active" id="instituciones" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2 flex-wrap">
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-white border-end-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0" id="buscarInstituciones" placeholder="Buscar institución...">
                    </div>
                </div>
            </div>
            <div class="table-container" id="tablaInstituciones">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>

        {{-- TAB DEPARTAMENTOS --}}
        <div class="tab-pane fade" id="departamentos" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2 flex-wrap">
                    <div class="input-group" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0" id="buscarDepartamentos" placeholder="Buscar departamento...">
                    </div>
                </div>
            </div>
            <div class="table-container" id="tablaDepartamentos">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>

        {{-- TAB RESPONSABLES --}}
        <div class="tab-pane fade" id="responsables" role="tabpanel">
            <div class="tab-header-bar">
                <div class="d-flex gap-2 flex-wrap">
                    <div class="input-group" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0" id="buscarResponsables" placeholder="Buscar responsable...">
                    </div>
                </div>
            </div>
            <div class="table-container" id="tablaResponsables">
                <p class="text-center py-4 text-muted">Cargando...</p>
            </div>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- WIZARD MAESTRO: INSTITUCIÓN → DEPARTAMENTO → RESPONSABLE --}}
{{-- ============================================ --}}
<div class="modal fade" id="modalWizardEntidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;display:inline;margin-right:8px;">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Registrar Entidad (Wizard)
                </h5>
                <span class="badge bg-light text-dark" id="wizardStepIndicator">Paso 1 de 3</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">

                {{-- Barra de progreso --}}
                <div class="px-2 mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="step-label" id="wizardLabel1" style="color:#1e3c72;">
                            <span class="step-circle active">1</span> Institución
                        </span>
                        <span class="step-label" id="wizardLabel2" style="color:#adb5bd;">
                            <span class="step-circle">2</span> Departamento
                        </span>
                        <span class="step-label" id="wizardLabel3" style="color:#adb5bd;">
                            <span class="step-circle">3</span> Responsable
                        </span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" id="wizardProgressBar" role="progressbar" style="width: 33%;"></div>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- PASO 1: INSTITUCIÓN --}}
                {{-- ============================================ --}}
                <div class="step-content" id="wizardStep1">
                    <div class="text-center mb-4">
                        <h6 style="color:#1e3c72; font-weight:600;">
                            <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:24px;height:24px;display:inline;margin-right:8px;">
                                <rect x="4" y="8" width="16" height="12" rx="1"/>
                                <path d="M8 20V8M16 20V8M4 12h16"/>
                            </svg>
                            Datos de la Institución
                        </h6>
                        <p class="text-muted small">Complete la información básica y la ubicación</p>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="wz_inst_nombre" placeholder="Ej: Instituto de Salud, Alcaldía..." maxlength="200">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Información <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="wz_inst_informacion" rows="3" maxlength="500" placeholder="Breve descripción"></textarea>
                        </div>
                    </div>

                    <div class="ubicacion-selects mt-2">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:20px;height:20px;">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <h6 class="fw-bold mb-0" style="color:#1e3c72;">Ubicación Geográfica</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Estado <span class="text-danger">*</span></label>
                                <select class="form-select" id="wz_estado_id">
                                    <option value="">Seleccionar estado...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Municipio <span class="text-danger">*</span></label>
                                <select class="form-select" id="wz_municipio_id" disabled>
                                    <option value="">Seleccionar estado primero</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Parroquia <span class="text-danger">*</span></label>
                                <select class="form-select" id="wz_parroquia_id" disabled>
                                    <option value="">Seleccionar municipio primero</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-primary-dark" onclick="irPasoWizard(2)">
                            Siguiente
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="margin-left:4px;">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- PASO 2: DEPARTAMENTO --}}
                {{-- ============================================ --}}
                <div class="step-content" id="wizardStep2" style="display: none;">
                    <div class="text-center mb-4">
                        <h6 style="color:#1e3c72; font-weight:600;">
                            <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:24px;height:24px;display:inline;margin-right:8px;">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="M8 8h8M8 12h6M8 16h4"/>
                            </svg>
                            Datos del Departamento
                        </h6>
                        <p class="text-muted small">Departamento que pertenecerá a la institución</p>
                        <div class="alert alert-info py-2 mt-2" style="font-size:0.85rem;">
                            <strong>Institución:</strong> <span id="wizardInstitucionLabel">(Ninguna)</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Nombre del Departamento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="wz_depto_nombre" placeholder="Ej: Informática, Recursos Humanos..." maxlength="100">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Ubicación <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="wz_depto_ubicacion" placeholder="Ej: Piso 3" maxlength="200">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Información <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="wz_depto_informacion" rows="2" maxlength="500" placeholder="Descripción del departamento..."></textarea>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-primary-dark" onclick="irPasoWizard(1)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                            Anterior
                        </button>
                        <button type="button" class="btn btn-primary-dark" onclick="irPasoWizard(3)">
                            Siguiente
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="margin-left:4px;">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- PASO 3: RESPONSABLE --}}
                {{-- ============================================ --}}
                <div class="step-content" id="wizardStep3" style="display: none;">
                    <div class="text-center mb-4">
                        <h6 style="color:#1e3c72; font-weight:600;">
                            <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:24px;height:24px;display:inline;margin-right:8px;">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                            </svg>
                            Datos del Responsable
                        </h6>
                        <p class="text-muted small">Responsable del departamento registrado</p>
                        <div class="alert alert-info py-2 mt-2" style="font-size:0.85rem;">
                            <strong>Institución:</strong> <span id="wizardInstitucionLabel2">-</span><br>
                            <strong>Departamento:</strong> <span id="wizardDepartamentoLabel2">-</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="wz_resp_nombre" placeholder="Nombre completo" maxlength="150">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Cargo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="wz_resp_cargo" value="Jefe de Departamento" maxlength="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="wz_resp_documento" placeholder="V-12345678" maxlength="50">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="wz_resp_telefono" placeholder="0412-1234567" maxlength="20">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" id="wz_resp_email" placeholder="correo@ejemplo.com" maxlength="100">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Dirección</label>
                        <textarea class="form-control" id="wz_resp_direccion" rows="2" maxlength="300" placeholder="Dirección del responsable..."></textarea>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-primary-dark" onclick="irPasoWizard(2)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                            Anterior
                        </button>
                        <button type="button" class="btn btn-success" id="wizardBtnGuardar">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="margin-right:4px;">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                            </svg>
                            Guardar Todo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETALLE --}}
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

{{-- MODAL ELIMINAR --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
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