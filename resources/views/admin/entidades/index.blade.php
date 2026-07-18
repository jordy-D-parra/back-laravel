@extends('layouts.dashboard')

@section('title', 'Gestión de Entidades')

@section('styles')
    @vite(['resources/css/admin-entidades.css'])
    <style>
        .institucion-card {
            transition: all 0.3s ease;
            border-radius: 16px;
            overflow: hidden;
        }

        .institucion-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(30, 60, 114, 0.15);
        }

        .institucion-card.active {
            border-color: #1e3c72 !important;
            background: #f0f4ff !important;
        }

        .institucion-card input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .step-circle {
            display: inline-block;
            width: 28px;
            height: 28px;
            line-height: 28px;
            text-align: center;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            font-weight: 700;
            font-size: 0.8rem;
            margin-right: 6px;
            transition: all 0.3s ease;
        }

        .step-circle.active {
            background: #1e3c72;
            color: white;
        }

        .step-circle.completed {
            background: #1e7e34;
            color: white;
        }

        .step-label {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .step-content {
            min-height: 300px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .progress {
            border-radius: 2px;
        }

        .progress-bar {
            transition: width 0.5s ease;
        }

        .modal-header-gradient {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .modal-header-gradient .btn-close {
            filter: brightness(0) invert(1);
        }

        @media (max-width: 768px) {
            .institucion-card {
                margin-bottom: 1rem;
            }
            .step-label {
                font-size: 0.7rem !important;
            }
            .step-circle {
                width: 24px;
                height: 24px;
                line-height: 24px;
                font-size: 0.7rem;
            }
            .modal-footer {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            .modal-footer .btn {
                flex: 1;
                min-width: 80px;
            }
        }
    </style>
@endsection

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="color: #1e3c72; font-weight: 700; margin: 0;">Gestión de Entidades</h4>
            <p style="color: #6c757d; font-size: 0.85rem; margin: 0;">Instituciones, departamentos y responsables</p>
        </div>
    </div>

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
                <button class="btn btn-primary-dark btn-sm" onclick="abrirModalResponsable()">
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
<!-- MODAL INSTITUCIÓN - WIZARD DE 2 PASOS       -->
<!-- ============================================ -->
<div class="modal fade" id="modalInstitucion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-gradient">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title" id="modalInstitucionLabel">
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;display:inline;margin-right:8px;">
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
                            <span class="step-label" id="stepLabelInst1" style="font-size:0.8rem; font-weight:600; color:#1e3c72;">
                                <span class="step-circle active">1</span> Institución
                            </span>
                            <span class="step-label" id="stepLabelInst2" style="font-size:0.8rem; font-weight:600; color:#adb5bd;">
                                <span class="step-circle">2</span> Representante <span class="text-danger">*</span>
                            </span>
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar" id="progressBarInst" role="progressbar" style="width: 50%; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);"></div>
                        </div>
                    </div>

                    <!-- ==================== PASO 1: INSTITUCIÓN ==================== -->
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
                                <label for="inst_nombre" class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="inst_nombre" name="nombre" required maxlength="200" placeholder="Ej: Instituto de Salud, Alcaldía...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="inst_ubicacion" class="form-label fw-bold">Ubicación <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="inst_ubicacion" name="ubicacion" required maxlength="200" placeholder="Ej: San Felipe, Yaracuy">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="inst_informacion" class="form-label fw-bold">Información <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="inst_informacion" name="informacion" rows="3" required maxlength="500" placeholder="Breve descripción de la institución..."></textarea>
                        </div>
                    </div>

                    <!-- ==================== PASO 2: REPRESENTANTE (OBLIGATORIO) ==================== -->
                    <div class="step-content px-4 pt-4" id="stepInst2" style="display: none;">
                        <div class="text-center mb-4">
                            <h6 style="color:#1e3c72; font-weight:600;">
                                <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:24px;height:24px;display:inline;margin-right:8px;">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                </svg>
                                Datos del Representante <span class="text-danger">*</span>
                            </h6>
                            <p class="text-muted small">Complete los datos del responsable de la institución</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="inst_representante_nombre" class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="inst_representante_nombre" name="representante_nombre" required maxlength="150" placeholder="Nombre del representante">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="inst_representante_cargo" class="form-label fw-bold">Cargo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="inst_representante_cargo" name="representante_cargo" required maxlength="100" value="Representante" placeholder="Cargo del representante">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="inst_representante_documento" class="form-label fw-bold">Documento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="inst_representante_documento" name="representante_documento" required maxlength="50" placeholder="V-12345678">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="inst_representante_telefono" class="form-label fw-bold">Teléfono <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="inst_representante_telefono" name="representante_telefono" required maxlength="20" placeholder="0412-1234567">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="inst_representante_email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="inst_representante_email" name="representante_email" maxlength="100" placeholder="correo@ejemplo.com">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="inst_representante_direccion" class="form-label fw-bold">Dirección</label>
                            <textarea class="form-control" id="inst_representante_direccion" name="representante_direccion" rows="2" maxlength="300" placeholder="Dirección del representante..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer con navegación -->
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" id="btnAnteriorInst" style="display: none;" onclick="cambiarPasoInst(-1)">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:4px;">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                        Anterior
                    </button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                    <button type="button" class="btn btn-primary-dark" id="btnSiguienteInst" onclick="cambiarPasoInst(1)">
                        Siguiente
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-left:4px;">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>

                    <button type="submit" class="btn btn-success" id="btnGuardarInst" style="display: none;">
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:4px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Guardar Institución y Representante
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
            <div class="modal-header modal-header-gradient">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title" id="modalDepartamentoLabel">
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;display:inline;margin-right:8px;">
                            <rect x="4" y="8" width="16" height="12" rx="1"/>
                            <path d="M8 20V8M16 20V8M4 12h16"/>
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
                            <span class="step-label" id="stepLabel1" style="font-size:0.8rem; font-weight:600; color:#1e3c72;">
                                <span class="step-circle active">1</span> Institución
                            </span>
                            <span class="step-label" id="stepLabel2" style="font-size:0.8rem; font-weight:600; color:#adb5bd;">
                                <span class="step-circle">2</span> Departamento
                            </span>
                            <span class="step-label" id="stepLabel3" style="font-size:0.8rem; font-weight:600; color:#adb5bd;">
                                <span class="step-circle">3</span> Representante
                            </span>
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 33%; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);"></div>
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
                                <div class="card institucion-card h-100 active" id="cardGobernacion" style="cursor:pointer; border: 2px solid #1e3c72; background: #f0f4ff;">
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
                                <div class="card institucion-card h-100" id="cardOtra" style="cursor:pointer; border: 2px solid #dee2e6;">
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
                                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:4px;">
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    Nueva Institución
                                </button>
                            </div>
                            <select class="form-select form-select-lg" id="depto_institucion_id" name="institucion_id">
                                <option value="">Seleccionar institución...</option>
                                @foreach($instituciones as $inst)
                                    <option value="{{ $inst->id }}" {{ $inst->nombre == 'Gobernación del Estado Yaracuy' ? 'selected' : '' }}>
                                        {{ $inst->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Seleccione la institución a la que pertenece el departamento</small>
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
                                <label for="depto_nombre" class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="depto_nombre" name="nombre" required maxlength="100" placeholder="Ej: Informática, Recursos Humanos..." oninput="validarNombreDepto()">
                                <div class="valid-feedback" id="feedbackNombreDeptoOk" style="display:none;">✅ Nombre disponible</div>
                                <div class="invalid-feedback" id="feedbackNombreDeptoError" style="display:none;">⚠️ Este nombre ya existe en esta institución</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="depto_ubicacion" class="form-label fw-bold">Ubicación <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="depto_ubicacion" name="ubicacion" required maxlength="200" placeholder="Ej: Sede Principal, Piso 3...">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="depto_informacion" class="form-label fw-bold">Información <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="depto_informacion" name="informacion" rows="3" required maxlength="500" placeholder="Describa brevemente el departamento..."></textarea>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sinInstitucion" onchange="toggleInstitucionDepartamento()">
                            <label class="form-check-label" for="sinInstitucion" style="font-size: 0.9rem;">
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
                                <label for="depto_representante_nombre" class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="depto_representante_nombre" name="representante_nombre" required maxlength="150" placeholder="Nombre del responsable">
                                <div class="form-check mt-2" id="contenedorCheckRepresentante">
                                    <input class="form-check-input" type="checkbox" id="usarRepresentanteInstitucion" onchange="toggleRepresentanteInstitucion()">
                                    <label class="form-check-label" for="usarRepresentanteInstitucion" style="font-size: 0.85rem;">
                                        <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="2" fill="none" style="width:14px;height:14px;display:inline;margin-right:4px;">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        Usar representante de la institución
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="depto_representante_cargo" class="form-label fw-bold">Cargo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="depto_representante_cargo" name="representante_cargo" required maxlength="100" value="Jefe de Departamento">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="depto_representante_documento" class="form-label fw-bold">Documento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="depto_representante_documento" name="representante_documento" required maxlength="50" placeholder="V-12345678">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="depto_representante_telefono" class="form-label fw-bold">Teléfono <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="depto_representante_telefono" name="representante_telefono" required maxlength="20" placeholder="0412-1234567">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="depto_representante_email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="depto_representante_email" name="representante_email" maxlength="100" placeholder="correo@ejemplo.com">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="depto_representante_direccion" class="form-label fw-bold">Dirección</label>
                            <textarea class="form-control" id="depto_representante_direccion" name="representante_direccion" rows="2" maxlength="300" placeholder="Dirección del responsable..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" id="btnAnterior" style="display: none;" onclick="cambiarPaso(-1)">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:4px;">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                        Anterior
                    </button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                    <button type="button" class="btn btn-primary-dark" id="btnSiguiente" onclick="cambiarPaso(1)">
                        Siguiente
                        <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-left:4px;">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>

                    <button type="submit" class="btn btn-success" id="btnGuardar" style="display: none;">
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
            <div class="modal-header modal-header-gradient">
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
                            <label for="resp_nombre" class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="resp_nombre" name="nombre" required maxlength="150" placeholder="Nombre completo">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="resp_cargo" class="form-label fw-bold">Cargo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="resp_cargo" name="cargo" required maxlength="100" placeholder="Ej: Director, Jefe, Coordinador...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="resp_documento" class="form-label fw-bold">Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="resp_documento" name="documento" required maxlength="50" placeholder="V-12345678">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="resp_telefono" class="form-label fw-bold">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="resp_telefono" name="telefono" required maxlength="20" placeholder="0412-1234567">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="resp_email" class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" id="resp_email" name="email" maxlength="100" placeholder="correo@ejemplo.com">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="resp_institucion_id" class="form-label fw-bold">Institución <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="resp_institucion_id" name="institucion_id" required>
                                <option value="">Seleccionar institución...</option>
                                @foreach($instituciones as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="resp_departamento_id" class="form-label fw-bold">Departamento (opcional)</label>
                            <select class="form-select form-select-lg" id="resp_departamento_id" name="departamento_id">
                                <option value="">Sin departamento</option>
                            </select>
                            <small class="text-muted">Seleccione primero una institución para ver sus departamentos</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="resp_direccion" class="form-label fw-bold">Dirección</label>
                        <textarea class="form-control" id="resp_direccion" name="direccion" rows="2" maxlength="300" placeholder="Dirección del responsable..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0">
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
<!-- MODAL INSTITUCIÓN RÁPIDA (desde departamento) -->
<!-- ============================================ -->
<div class="modal fade" id="modalInstitucionRapida" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-gradient">
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
                    <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:0.85rem;">
                        <svg viewBox="0 0 24 24" stroke="#856404" stroke-width="2" fill="none" style="width:16px;height:16px;display:inline;margin-right:6px;">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4M12 8h.01"/>
                        </svg>
                        <strong>Importante:</strong> El representante de la institución se debe registrar después desde la sección de responsables.
                    </div>
                    <div class="mb-3">
                        <label for="inst_rapida_nombre" class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="inst_rapida_nombre" name="nombre" required maxlength="200" placeholder="Ej: Instituto de Salud, Alcaldía...">
                    </div>
                    <div class="mb-3">
                        <label for="inst_rapida_ubicacion" class="form-label fw-bold">Ubicación <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inst_rapida_ubicacion" name="ubicacion" required maxlength="200" placeholder="Ej: San Felipe, Yaracuy">
                    </div>
                    <div class="mb-3">
                        <label for="inst_rapida_informacion" class="form-label fw-bold">Información <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="inst_rapida_informacion" name="informacion" rows="2" required maxlength="500" placeholder="Breve descripción de la institución..."></textarea>
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
@endsection
