@extends('layouts.dashboard')

@section('title', 'Mis Solicitudes de Préstamo')

@section('content')
<div class="container-fluid px-4">
    <!-- Tarjeta de bienvenida / cabecera -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h2 class="text-white mb-2 d-flex align-items-center gap-2">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
                                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                                    <path d="M8 8h8M8 12h6M8 16h4"/>
                                </svg>
                                Mis Solicitudes de Préstamo
                            </h2>
                            <p class="text-white-50 mb-0">Gestiona tus solicitudes de préstamo de equipos</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="abrirBandejaCorreos()" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 10px; position: relative;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <path d="M22 7l-10 7L2 7"/>
                                </svg>
                                Correos
                                <span id="notificacionCorreos" style="background: #dc3545; color: white; font-size: 11px; padding: 2px 6px; border-radius: 20px; position: absolute; top: -8px; right: -8px; display: none;">0</span>
                            </button>
                            <button onclick="abrirModalCrear()" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 10px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Nueva Solicitud
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-success border-0 rounded-4" style="background: #d4edda; color: #155724; border-left: 4px solid #28a745;">
                    <div class="d-flex align-items-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-danger border-0 rounded-4" style="background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545;">
                    <div class="d-flex align-items-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4M12 16h.01"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Panel de filtros -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Buscar</label>
                            <div class="position-relative">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                <input type="text" id="searchInput" class="form-control" placeholder="Buscar por institución o justificación..." style="padding-left: 36px; border-radius: 10px; background: white;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Estado</label>
                            <select id="estadoFilter" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobada">Aprobada</option>
                                <option value="rechazada">Rechazada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Prioridad</label>
                            <select id="prioridadFilter" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todas</option>
                                <option value="baja">Baja</option>
                                <option value="normal">Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Desde</label>
                            <input type="date" id="fechaDesde" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Hasta</label>
                            <input type="date" id="fechaHasta" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-1">
                            <button id="limpiarFiltros" class="btn w-100" style="background: #6c757d; color: white; border-radius: 10px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 4px;">
                                    <path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/>
                                </svg>
                                Limpiar
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted small">Mostrando <span id="resultadosCount">0</span> solicitudes de <span id="totalRegistrosCount">0</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de listado con Infinity Scroll -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-0 overflow-auto" style="max-height: 70vh;" id="scrollContainer">
                    <table class="table table-hover mb-0" style="min-width: 1000px;">
                        <thead style="background: #f8f9fc; position: sticky; top: 0; z-index: 10;">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold">Fecha Solicitud</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Entidad</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Responsable</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Fecha Requerida</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Fecha Fin</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Prioridad</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Estado</th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">Items</th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody" style="background: white;"></tbody>
                    </table>
                    <div id="loadingIndicator" style="text-align: center; padding: 20px; display: none;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Cargando más solicitudes...</p>
                    </div>
                    <div id="noMoreData" style="text-align: center; padding: 20px; display: none;">
                        <p class="text-muted">✨ No hay más solicitudes para mostrar ✨</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sistema de Notificaciones -->
<div id="notification-container" style="position: fixed; top: 80px; right: 20px; z-index: 99999; width: 350px;"></div>

<!-- MODAL VER DETALLES DE LA SOLICITUD -->
<div id="modalDetalles" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10002; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content rounded-4 border-0" style="background: white; max-height: 85vh; overflow-y: auto;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Detalles de la Solicitud
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalDetalles()"></button>
            </div>
            <div class="modal-body p-4" id="modalDetallesBody" style="background: white;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando...</p>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" onclick="cerrarModalDetalles()" class="btn btn-light px-4" style="border-radius: 10px;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CREAR (normal) -->
<div id="modalCrear" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 1000px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Nueva Solicitud de Préstamo
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalCrear()"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto; background: white;">
                <form id="formCrearSolicitud" action="{{ route('solicitudes.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo Solicitante</label>
                            <select name="tipo_solicitante" id="tipoSolicitante" required class="form-select" style="border-radius: 10px; background: white;">
                                <option value="interno">Interno (Departamento/Área)</option>
                                <option value="externo">Externo (Institución)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prioridad</label>
                            <select name="prioridad" required class="form-select" style="border-radius: 10px; background: white;">
                                <option value="baja">Baja</option>
                                <option value="normal">Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>

                        <div id="interno-fields">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Departamento / Área</label>
                                <select name="departamento_id" id="departamentoSelect" class="form-select" style="border-radius: 10px; background: white;">
                                    <option value="">Seleccione un departamento</option>
                                    @foreach($departamentos ?? [] as $departamento)
                                        <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                                    @endforeach
                                    <option value="otro">+ Otro (No está en la lista)</option>
                                </select>
                            </div>
                            <div id="departamento-nuevo-field" style="display: none;" class="mt-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Nuevo Departamento</label>
                                    <input type="text" name="nuevo_departamento" id="nuevoDepartamento" class="form-control" placeholder="Ej: Recursos Humanos" style="border-radius: 10px; background: white;">
                                    <small class="text-muted">Se registrará automáticamente</small>
                                </div>
                            </div>
                        </div>

                        <div id="externo-fields" style="display: none;">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Institución</label>
                                <select name="institucion_id" id="institucionSelect" class="form-select" style="border-radius: 10px; background: white;">
                                    <option value="">Seleccione una institución</option>
                                    @foreach($instituciones ?? [] as $institucion)
                                        <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                                    @endforeach
                                    <option value="otro">+ Otra (No está en la lista)</option>
                                </select>
                            </div>
                            <div id="institucion-nuevo-field" style="display: none;" class="mt-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Nueva Institución</label>
                                    <input type="text" name="nueva_institucion" id="nuevaInstitucion" class="form-control" placeholder="Ej: Universidad Nacional" style="border-radius: 10px; background: white;">
                                    <small class="text-muted">Se registrará automáticamente</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Persona Responsable</label>
                            <select name="responsable_id" id="responsableSelect" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Seleccione un responsable</option>
                                @foreach($responsables ?? [] as $responsable)
                                    <option value="{{ $responsable->id }}">{{ $responsable->nombre }} - {{ $responsable->departamento ?? $responsable->tipo }}</option>
                                @endforeach
                                <option value="otro">+ Otro (No está en la lista)</option>
                            </select>
                        </div>
                        <div id="responsable-nuevo-field" style="display: none;" class="mt-2">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nuevo Responsable</label>
                                <input type="text" name="nuevo_responsable" id="nuevoResponsable" class="form-control" placeholder="Nombre completo" style="border-radius: 10px; background: white;">
                                <small class="text-muted">Se registrará automáticamente</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Requerida</label>
                            <input type="date" name="fecha_requerida" required class="form-control" style="border-radius: 10px; background: white;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Fin Estimada</label>
                            <input type="date" name="fecha_fin_estimada" required class="form-control" style="border-radius: 10px; background: white;">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Justificación</label>
                            <textarea name="justificacion" rows="3" required class="form-control" style="border-radius: 10px; background: white;"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <textarea name="observaciones" rows="2" class="form-control" style="border-radius: 10px; background: white;"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Oficio Adjunto (PDF)</label>
                            <input type="file" name="oficio_adjunto" accept=".pdf,.doc,.docx" class="form-control" style="border-radius: 10px; background: white;">
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold" style="color: #1e3c72;">Items Solicitados</h6>
                                <button type="button" id="add-item-modal" class="btn btn-sm btn-light" style="border-radius: 8px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                                    Agregar Item
                                </button>
                            </div>
                            <div id="items-container-modal">
                                <div class="item-card-modal p-3 mb-3" style="background: #f8f9fc; border: 1px solid #e9ecef; border-radius: 12px;">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="small text-muted">Tipo</label>
                                            <select name="items[0][tipo_item]" required class="form-select form-select-sm" style="border-radius: 8px; background: white;">
                                                <option value="activo">Activo</option>
                                                <option value="periferico">Periférico</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Descripción del Item</label>
                                            <input type="text" name="items[0][item_descripcion]" required placeholder="Ej: Laptop HP, Mouse, Cargador, etc." class="form-control form-control-sm" style="border-radius: 8px; background: white;">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small text-muted">Cantidad</label>
                                            <input type="number" name="items[0][cantidad]" min="1" value="1" required class="form-control form-control-sm" style="border-radius: 8px; background: white;">
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12 text-end">
                                            <button type="button" class="remove-item-modal btn btn-sm text-danger" style="font-size: 14px;">× Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalCrear()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="submit" id="submitSolicitudBtn" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Enviar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR -->
<div id="modalEditar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10003; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 1000px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                    </svg>
                    Editar Solicitud
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalEditar()"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto; background: white;">
                <form id="formEditarSolicitud" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="editId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo Solicitante</label>
                            <select name="tipo_solicitante" id="editTipoSolicitante" required class="form-select" style="border-radius: 10px; background: white;">
                                <option value="interno">Interno (Departamento/Área)</option>
                                <option value="externo">Externo (Institución)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prioridad</label>
                            <select name="prioridad" id="editPrioridad" required class="form-select" style="border-radius: 10px; background: white;">
                                <option value="baja">Baja</option>
                                <option value="normal">Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>

                        <div class="col-md-12" id="editInternoFields">
                            <label class="form-label fw-semibold">Departamento / Área</label>
                            <select name="departamento_id" id="editDepartamentoId" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Seleccione un departamento</option>
                                @foreach($departamentos ?? [] as $departamento)
                                    <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12" id="editExternoFields" style="display: none;">
                            <label class="form-label fw-semibold">Institución</label>
                            <select name="institucion_id" id="editInstitucionId" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Seleccione una institución</option>
                                @foreach($instituciones ?? [] as $institucion)
                                    <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Persona Responsable</label>
                            <select name="responsable_id" id="editResponsableId" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Seleccione un responsable</option>
                                @foreach($responsables ?? [] as $responsable)
                                    <option value="{{ $responsable->id }}">{{ $responsable->nombre }} - {{ $responsable->departamento ?? $responsable->tipo }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Requerida</label>
                            <input type="date" name="fecha_requerida" id="editFechaRequerida" required class="form-control" style="border-radius: 10px; background: white;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Fin Estimada</label>
                            <input type="date" name="fecha_fin_estimada" id="editFechaFin" required class="form-control" style="border-radius: 10px; background: white;">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Justificación</label>
                            <textarea name="justificacion" id="editJustificacion" rows="3" required class="form-control" style="border-radius: 10px; background: white;"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <textarea name="observaciones" id="editObservaciones" rows="2" class="form-control" style="border-radius: 10px; background: white;"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalEditar()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="submit" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Actualizar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL BANDEJA DE CORREOS -->
<div id="modalBandeja" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1300px;">
        <div class="modal-content rounded-4 border-0" style="height: 85vh; background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <div>
                    <h5 class="modal-title text-white d-flex align-items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M22 7l-10 7L2 7"/>
                        </svg>
                        Bandeja de Correos
                    </h5>
                    <p class="text-white-50 small mb-0">Correos recibidos para solicitudes</p>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="revisarCorreosManual()" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white; border-radius: 8px;">Revisar ahora</button>
                    <button type="button" class="btn-close btn-close-white" onclick="cerrarBandejaCorreos()"></button>
                </div>
            </div>
            <div class="modal-body p-0 d-flex" style="overflow: hidden; background: white;">
                <div class="col-md-4 border-end" style="overflow-y: auto; background: #fafbfc;">
                    <div id="listaCorreosContainer" class="p-2"></div>
                </div>
                <div class="col-md-8 d-flex flex-column" style="overflow-y: auto; background: white;">
                    <div id="previewCorreo" class="p-4 border-bottom" style="background: #f8f9fc;">
                        <div class="text-center text-muted py-5">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="M22 7l-10 7L2 7"/>
                            </svg>
                            <p class="mt-3">Selecciona un correo para ver su contenido</p>
                        </div>
                    </div>
                    <div class="p-4 overflow-auto" style="background: white;">
                        <h6 class="fw-bold mb-3" style="color: #1e3c72;">Crear solicitud desde este correo</h6>
                        <form id="formSolicitudCorreo" action="{{ route('solicitudes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="correo_origen" id="correoOrigen">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Tipo Solicitante</label>
                                    <select name="tipo_solicitante" id="formTipo" required class="form-select form-select-sm" style="border-radius: 8px; background: white;">
                                        <option value="interno">Interno</option>
                                        <option value="externo">Externo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Prioridad</label>
                                    <select name="prioridad" id="formPrioridad" required class="form-select form-select-sm" style="border-radius: 8px; background: white;">
                                        <option value="baja">Baja</option>
                                        <option value="normal">Normal</option>
                                        <option value="alta">Alta</option>
                                        <option value="urgente">Urgente</option>
                                    </select>
                                </div>

                                <div id="formInternoFields">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold small">Departamento / Área</label>
                                        <select name="departamento_id" id="formDepartamentoSelect" class="form-select form-select-sm" style="border-radius: 8px; background: white;">
                                            <option value="">Seleccione un departamento</option>
                                            @foreach($departamentos ?? [] as $departamento)
                                                <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                                            @endforeach
                                            <option value="otro">+ Otro (No está en la lista)</option>
                                        </select>
                                    </div>
                                    <div id="formDepartamentoNuevoField" style="display: none; margin-top: 8px;">
                                        <label class="form-label fw-semibold small">Nuevo Departamento</label>
                                        <input type="text" name="nuevo_departamento" id="formNuevoDepartamento" class="form-control form-control-sm" placeholder="Ej: Recursos Humanos" style="border-radius: 8px; background: white;">
                                    </div>
                                </div>

                                <div id="formExternoFields" style="display: none;">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold small">Institución</label>
                                        <select name="institucion_id" id="formInstitucionSelect" class="form-select form-select-sm" style="border-radius: 8px; background: white;">
                                            <option value="">Seleccione una institución</option>
                                            @foreach($instituciones ?? [] as $institucion)
                                                <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                                            @endforeach
                                            <option value="otro">+ Otra (No está en la lista)</option>
                                        </select>
                                    </div>
                                    <div id="formInstitucionNuevoField" style="display: none; margin-top: 8px;">
                                        <label class="form-label fw-semibold small">Nueva Institución</label>
                                        <input type="text" name="nueva_institucion" id="formNuevaInstitucion" class="form-control form-control-sm" placeholder="Ej: Universidad Nacional" style="border-radius: 8px; background: white;">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold small">Persona Responsable</label>
                                    <select name="responsable_id" id="formResponsableSelect" class="form-select form-select-sm" style="border-radius: 8px; background: white;">
                                        <option value="">Seleccione un responsable</option>
                                        @foreach($responsables ?? [] as $responsable)
                                            <option value="{{ $responsable->id }}">{{ $responsable->nombre }} - {{ $responsable->departamento ?? $responsable->tipo }}</option>
                                        @endforeach
                                        <option value="otro">+ Otro (No está en la lista)</option>
                                    </select>
                                </div>
                                <div id="formResponsableNuevoField" style="display: none;">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold small">Nuevo Responsable</label>
                                        <input type="text" name="nuevo_responsable" id="formNuevoResponsable" class="form-control form-control-sm" placeholder="Nombre completo" style="border-radius: 8px; background: white;">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Fecha Requerida</label>
                                    <input type="date" name="fecha_requerida" id="formFechaRequerida" required class="form-control form-control-sm" style="border-radius: 8px; background: white;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Fecha Fin Estimada</label>
                                    <input type="date" name="fecha_fin_estimada" id="formFechaFin" required class="form-control form-control-sm" style="border-radius: 8px; background: white;">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Justificación</label>
                                    <textarea name="justificacion" id="formJustificacion" rows="2" required class="form-control form-control-sm" style="border-radius: 8px; background: white;"></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Observaciones</label>
                                    <textarea name="observaciones" id="formObservaciones" rows="2" class="form-control form-control-sm" style="border-radius: 8px; background: white;"></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Oficio Adjunto</label>
                                    <input type="file" name="oficio_adjunto" id="formAdjunto" accept=".pdf,.doc,.docx" class="form-control form-control-sm" style="border-radius: 8px; background: white;">
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="fw-bold" style="color: #1e3c72;">Items Solicitados</small>
                                        <button type="button" id="add-item-correo" class="btn btn-sm btn-light" style="border-radius: 8px;">+ Agregar</button>
                                    </div>
                                    <div id="items-container-correo">
                                        <div class="item-card-correo p-2 mb-2" style="background: #f8f9fc; border-radius: 8px;">
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-4">
                                                    <select name="items[0][tipo_item]" required class="form-select form-select-sm" style="border-radius: 6px; background: white;">
                                                        <option value="activo">Activo</option>
                                                        <option value="periferico">Periférico</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" name="items[0][item_descripcion]" required placeholder="Ej: Laptop HP, Mouse, Cargador" class="form-control form-control-sm" style="border-radius: 6px; background: white;">
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="number" name="items[0][cantidad]" min="1" value="1" required class="form-control form-control-sm" style="border-radius: 6px; background: white;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 border-top d-flex justify-content-end gap-2">
                                <button type="button" onclick="cerrarBandejaCorreos()" class="btn btn-sm btn-light px-3" style="border-radius: 8px;">Cancelar</button>
                                <button type="submit" class="btn btn-sm px-3 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 8px;">Crear Solicitud</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table tbody tr {
        transition: all 0.2s ease;
        animation: fadeInUp 0.25s ease;
        background: white;
    }
    .table tbody tr:hover {
        background-color: #f8f9fc !important;
        transform: scale(1.01);
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes highlightRow {
        0% { background-color: rgba(40,167,69,0.3); }
        100% { background-color: white; }
    }
    .highlight-new-row { animation: highlightRow 1.5s ease; }

    .badge-prioridad, .badge-estado, .badge-fecha {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .fecha-solicitud-badge { background: #e9ecef; color: #1a1a2e; font-weight: 600; }
    .fecha-requerida-badge { background: #fff3cd; color: #856404; font-weight: 600; }
    .fecha-requerida-vencida { background: #f8d7da; color: #721c24; font-weight: 600; }
    .fecha-fin-badge { background: #d1e7dd; color: #0f5132; font-weight: 600; }

    .correo-item {
        transition: all 0.2s ease;
        cursor: pointer;
        padding: 12px;
        border-radius: 12px;
        background: white;
        margin-bottom: 8px;
        border: 1px solid #e9ecef;
    }
    .correo-item:hover {
        background: #f8f9fc;
        transform: translateX(4px);
        border-color: #1e3c72;
    }

    .btn:active { transform: scale(0.98); }
    .card { background: white !important; }

    .notification-toast {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        margin-bottom: 12px;
        overflow: hidden;
        animation: slideInRight 0.3s ease forwards;
    }
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100px); }
    }
    .notification-toast.success { border-left: 4px solid #28a745; }
    .notification-toast.error { border-left: 4px solid #dc3545; }
    .notification-toast.warning { border-left: 4px solid #ffc107; }
    .notification-toast.info { border-left: 4px solid #17a2b8; }

    .btn-accion-ver { background: rgba(23,162,184,0.1); color: #0c5c6e; border: 1px solid rgba(23,162,184,0.3); }
    .btn-accion-ver:hover { background: #17a2b8; color: white; border-color: #17a2b8; }
    .btn-accion-editar { background: rgba(255,193,7,0.1); color: #8a6300; border: 1px solid rgba(255,193,7,0.3); }
    .btn-accion-editar:hover { background: #ffc107; color: #1e3c72; border-color: #ffc107; }
    .btn-accion-cancelar { background: rgba(220,53,69,0.1); color: #8b1a24; border: 1px solid rgba(220,53,69,0.3); }
    .btn-accion-cancelar:hover { background: #dc3545; color: white; border-color: #dc3545; }
</style>

<script>
// ==================== DATOS INICIALES ====================
let todasLasSolicitudes = @json($solicitudes->items());
let solicitudesFiltradas = [...todasLasSolicitudes];
let currentPage = 1;
let totalRegistros = @json($solicitudes->total());
let timeoutBusqueda = null;
let ultimoIdCreado = null;

let activos = @json($activos ?? []);
let perifericos = @json($perifericos ?? []);
let instituciones = @json($instituciones ?? []);
let departamentos = @json($departamentos ?? []);
let responsables = @json($responsables ?? []);

// Variables para infinity scroll
let paginaActualScroll = @json($solicitudes->currentPage());
let ultimaPagina = @json($solicitudes->lastPage());
let cargandoMas = false;
let hayMasDatos = paginaActualScroll < ultimaPagina;

let correosPendientes = [
    { id: 1, from: "juan.perez@empresa.com", subject: "Solicitud de prestamo - Proyecto A", date: "2024-01-15", body: "Necesito equipos para el proyecto A. Fecha requerida: 20/01/2024. Prioridad: alta", extracted: { prioridad: "alta", fecha_requerida: "2024-01-20", justificacion: "Proyecto A" } },
    { id: 2, from: "maria.garcia@empresa.com", subject: "URGENTE: Equipos para reunion", date: "2024-01-16", body: "Se requiere con urgencia computadoras", extracted: { prioridad: "urgente", fecha_requerida: "2024-01-18", justificacion: "Reunion con clientes" } }
];

// ==================== INFINITY SCROLL ====================
async function cargarMasSolicitudes() {
    if (cargandoMas) return;
    if (!hayMasDatos) return;

    cargandoMas = true;
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) loadingIndicator.style.display = 'block';

    try {
        const nextPage = paginaActualScroll + 1;

        const response = await fetch(`/solicitudes?page=${nextPage}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        });

        if (!response.ok) throw new Error('Error al cargar más datos');

        const data = await response.json();

        if (data.data && data.data.length > 0) {
            todasLasSolicitudes = [...todasLasSolicitudes, ...data.data];
            solicitudesFiltradas = [...todasLasSolicitudes];
            paginaActualScroll = data.current_page;
            ultimaPagina = data.last_page;
            hayMasDatos = paginaActualScroll < ultimaPagina;

            document.getElementById('resultadosCount').textContent = solicitudesFiltradas.length;
            document.getElementById('totalRegistrosCount').textContent = totalRegistros;

            renderizarTabla();

            if (!hayMasDatos) {
                const noMoreData = document.getElementById('noMoreData');
                if (noMoreData) noMoreData.style.display = 'block';
            }
        } else {
            hayMasDatos = false;
            const noMoreData = document.getElementById('noMoreData');
            if (noMoreData) noMoreData.style.display = 'block';
        }

    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error', 'No se pudieron cargar más solicitudes');
    } finally {
        cargandoMas = false;
        if (loadingIndicator) loadingIndicator.style.display = 'none';
    }
}

function initInfinityScroll() {
    const scrollContainer = document.getElementById('scrollContainer');
    if (!scrollContainer) return;

    scrollContainer.addEventListener('scroll', function() {
        const scrollTop = this.scrollTop;
        const scrollHeight = this.scrollHeight;
        const clientHeight = this.clientHeight;

        if (scrollTop + clientHeight >= scrollHeight - 150) {
            if (!cargandoMas && hayMasDatos) {
                cargarMasSolicitudes();
            }
        }
    });
}

// ==================== NOTIFICACIONES ====================
function mostrarNotificacion(tipo, titulo, mensaje, datos = null) {
    const container = document.getElementById('notification-container');
    if (!container) return;
    const notificacion = document.createElement('div');
    notificacion.className = `notification-toast ${tipo}`;
    let icono = '';
    if (tipo === 'success') icono = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>';
    else if (tipo === 'error') icono = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>';
    else icono = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#17a2b8" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
    let datosHtml = '';
    if (datos) { datosHtml = '<div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #e9ecef; font-size: 11px;">' + Object.entries(datos).map(([k,v]) => `<div><strong>${k}:</strong> ${v}</div>`).join('') + '</div>'; }
    notificacion.innerHTML = `<div style="padding: 16px; display: flex; gap: 12px;"><div style="flex-shrink: 0;">${icono}</div><div style="flex: 1;"><div style="font-weight: 600; margin-bottom: 4px;">${titulo}</div><div style="font-size: 13px; color: #495057;">${mensaje}</div>${datosHtml}</div><button onclick="this.closest('.notification-toast').remove()" style="background: none; border: none; cursor: pointer; font-size: 18px;">×</button></div>`;
    container.appendChild(notificacion);
    setTimeout(() => { if(notificacion && notificacion.parentNode) { notificacion.style.animation = 'slideOutRight 0.3s forwards'; setTimeout(() => notificacion.remove(), 300); } }, 8000);
}

function isFechaVencida(fechaRequerida, estado) { if (estado !== 'pendiente') return false; const hoy = new Date(); hoy.setHours(0,0,0,0); return new Date(fechaRequerida) < hoy; }
function escapeHtml(text) { if (!text) return ''; const div = document.createElement('div'); div.appendChild(document.createTextNode(text)); return div.innerHTML; }

// ==================== VER DETALLES (CORREGIDO) ====================
function verDetalles(id) {
    const modal = document.getElementById('modalDetalles');
    const modalBody = document.getElementById('modalDetallesBody');
    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando...</p></div>';

    // USAR LA NUEVA RUTA que devuelve JSON
    fetch(`/solicitudes/${id}/detalles`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                modalBody.innerHTML = `<div class="text-center text-danger py-4">${data.error}</div>`;
                return;
            }

            // Generar HTML de items correctamente
            let itemsHtml = '';
            if (data.detalles && data.detalles.length > 0) {
                itemsHtml = `
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                for (let i = 0; i < data.detalles.length; i++) {
                    itemsHtml += `
                        <tr>
                            <td>${data.detalles[i].tipo_item === 'activo' ? 'Activo' : 'Periférico'}</td>
                            <td>${escapeHtml(data.detalles[i].item_descripcion)}</td>
                            <td class="text-center"><strong>${data.detalles[i].cantidad_solicitada}</strong></td>
                        </tr>
                    `;
                }
                itemsHtml += '</tbody>}</div>';
            } else {
                itemsHtml = '<div class="alert alert-secondary mt-3">No hay items registrados en esta solicitud</div>';
            }

            let estadoColor = data.estado_solicitud === 'pendiente' ? '#b26a00' : data.estado_solicitud === 'aprobada' ? '#1b5e20' : data.estado_solicitud === 'rechazada' ? '#c62828' : '#4e342e';
            let prioridadColor = data.prioridad === 'urgente' ? '#c62828' : data.prioridad === 'alta' ? '#e65100' : data.prioridad === 'normal' ? '#1b5e20' : '#4a2e00';

            let nombreEntidad = 'No especificado';
            if (data.tipo_solicitante === 'interno' && data.departamento) nombreEntidad = data.departamento.nombre;
            else if (data.tipo_solicitante === 'externo' && data.institucion) nombreEntidad = data.institucion.nombre;
            const nombreResponsable = data.responsable ? data.responsable.nombre : 'No especificado';

            const fechaSolicitud = data.fecha_solicitud ? new Date(data.fecha_solicitud).toLocaleDateString() : 'No definida';
            const fechaRequerida = data.fecha_requerida ? new Date(data.fecha_requerida).toLocaleDateString() : 'No definida';
            const fechaFin = data.fecha_fin_estimada ? new Date(data.fecha_fin_estimada).toLocaleDateString() : 'No definida';

            const html = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Fecha Solicitud</label>
                            <div class="fw-semibold">${fechaSolicitud}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Tipo Solicitante</label>
                            <div class="fw-semibold">${data.tipo_solicitante === 'interno' ? 'Interno' : 'Externo'}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Prioridad</label>
                            <div><span class="badge-prioridad" style="background: ${prioridadColor}15; color: ${prioridadColor};">${data.prioridad}</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Estado</label>
                            <div><span class="badge-estado" style="background: ${estadoColor}15; color: ${estadoColor};">${data.estado_solicitud}</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Entidad</label>
                            <div class="fw-semibold">${escapeHtml(nombreEntidad)}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Responsable</label>
                            <div class="fw-semibold">${escapeHtml(nombreResponsable)}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Fecha Requerida</label>
                            <div>${fechaRequerida}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Fecha Fin Estimada</label>
                            <div>${fechaFin}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="text-muted small">Justificación</label>
                            <div class="p-2 bg-light rounded" style="background: #f8f9fc;">${escapeHtml(data.justificacion || 'No especificada')}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="text-muted small">Observaciones</label>
                            <div class="p-2 bg-light rounded" style="background: #f8f9fc;">${escapeHtml(data.observaciones || 'No hay observaciones')}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small fw-semibold mb-2">Items Solicitados</label>
                        ${itemsHtml}
                    </div>
                </div>
            `;
            modalBody.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = '<div class="text-center text-danger py-4">Error al cargar los detalles</div>';
        });
}

function cerrarModalDetalles() {
    document.getElementById('modalDetalles').style.display = 'none';
}

// ==================== RENDERIZAR TABLA ====================
function renderizarTabla() {
    const tbody = document.getElementById('tablaBody');
    const resultadosCount = document.getElementById('resultadosCount');
    resultadosCount.innerText = solicitudesFiltradas.length;
    document.getElementById('totalRegistrosCount').innerText = totalRegistros;

    if (solicitudesFiltradas.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted">No hay solicitudes<br><small>Las solicitudes que crees aparecerán aquí</small></td></tr>`;
        return;
    }

    let html = '';
    for (const s of solicitudesFiltradas) {
        let prioridadColor = s.prioridad === 'urgente' ? '#c62828' : s.prioridad === 'alta' ? '#e65100' : s.prioridad === 'normal' ? '#1b5e20' : '#4a2e00';
        let prioridadBg = s.prioridad === 'urgente' ? '#ffebee' : s.prioridad === 'alta' ? '#fff3e0' : s.prioridad === 'normal' ? '#e8f5e9' : '#fff8e1';
        let estadoColor = s.estado_solicitud === 'pendiente' ? '#b26a00' : s.estado_solicitud === 'aprobada' ? '#1b5e20' : s.estado_solicitud === 'rechazada' ? '#c62828' : '#4e342e';
        let estadoBg = s.estado_solicitud === 'pendiente' ? '#fff8e1' : s.estado_solicitud === 'aprobada' ? '#e8f5e9' : s.estado_solicitud === 'rechazada' ? '#ffebee' : '#eceff1';
        const fechaVencida = isFechaVencida(s.fecha_requerida, s.estado_solicitud);
        const fechaReqClass = fechaVencida ? 'fecha-requerida-vencida' : 'fecha-requerida-badge';
        let prioridadTexto = s.prioridad === 'urgente' ? 'Urgente' : s.prioridad === 'alta' ? 'Alta' : s.prioridad === 'normal' ? 'Normal' : 'Baja';
        let estadoTexto = s.estado_solicitud === 'pendiente' ? 'Pendiente' : s.estado_solicitud === 'aprobada' ? 'Aprobada' : s.estado_solicitud === 'rechazada' ? 'Rechazada' : 'Cancelada';
        const fechaSolicitud = s.fecha_solicitud ? new Date(s.fecha_solicitud).toLocaleDateString() : 'No registrada';
        const fechaRequerida = s.fecha_requerida ? new Date(s.fecha_requerida).toLocaleDateString() : 'No definida';
        const fechaFin = s.fecha_fin_estimada ? new Date(s.fecha_fin_estimada).toLocaleDateString() : 'No definida';
        let nombreEntidad = 'No especificado';
        if (s.tipo_solicitante === 'interno' && s.departamento) nombreEntidad = s.departamento.nombre;
        else if (s.tipo_solicitante === 'externo' && s.institucion) nombreEntidad = s.institucion.nombre;
        const nombreResponsable = s.responsable ? s.responsable.nombre : 'No especificado';

        html += `<tr data-id="${s.id}">
            <td class="px-4 py-3"><span class="badge-fecha fecha-solicitud-badge"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> ${fechaSolicitud}</span><\/td>
            <td class="px-4 py-3"><span class="badge-fecha" style="background: ${s.tipo_solicitante === 'interno' ? '#e3f2fd' : '#f3e5f5'}; color: ${s.tipo_solicitante === 'interno' ? '#0d47a1' : '#4a148c'}; font-weight: 600;"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${s.tipo_solicitante === 'interno' ? '<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>' : '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'}</svg> ${escapeHtml(nombreEntidad)}</span><\/td>
            <td class="px-4 py-3"><span class="badge-fecha" style="background: #e8f5e9; color: #1b5e20; font-weight: 600;"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> ${escapeHtml(nombreResponsable)}</span><\/td>
            <td class="px-4 py-3"><span class="badge-fecha ${fechaReqClass}"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> ${fechaRequerida} ${fechaVencida ? '(Vencida)' : ''}</span><\/td>
            <td class="px-4 py-3"><span class="badge-fecha fecha-fin-badge"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> ${fechaFin}</span><\/td>
            <td class="px-4 py-3"><span class="badge-prioridad" style="background: ${prioridadBg}; color: ${prioridadColor}; font-weight: 600;"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg> ${prioridadTexto}</span><\/td>
            <td class="px-4 py-3"><span class="badge-estado" style="background: ${estadoBg}; color: ${estadoColor}; font-weight: 600;">${estadoTexto}</span><\/td>
            <td class="px-4 py-3 text-center"><span class="badge-fecha" style="background: #e9ecef; color: #1a1a2e; font-weight: 600;">${s.detalles?.length || 0}</span><\/td>
            <td class="px-4 py-3 text-center"><div class="d-flex gap-2 justify-content-center">
                <button onclick="verDetalles(${s.id})" class="btn btn-sm btn-accion-ver" style="border-radius: 8px; padding: 6px 12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><circle cx="12" cy="12" r="3"/></svg> Ver</button>
                <button onclick="editarSolicitud(${s.id})" class="btn btn-sm btn-accion-editar" style="border-radius: 8px; padding: 6px 12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/><polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/></svg> Editar</button>
                ${s.estado_solicitud === 'pendiente' ? `<form action="/solicitudes/${s.id}/cancel" method="POST" style="margin: 0;" onsubmit="return confirm('¿Cancelar solicitud?')">@csrf<button type="submit" class="btn btn-sm btn-accion-cancelar" style="border-radius: 8px; padding: 6px 12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/></svg> Cancelar</button></form>` : ''}
            <\/div><\/td>
        <\/tr>`;
    }
    tbody.innerHTML = html;
}

function aplicarFiltros() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const estado = document.getElementById('estadoFilter').value;
    const prioridad = document.getElementById('prioridadFilter').value;
    const fechaDesde = document.getElementById('fechaDesde').value;
    const fechaHasta = document.getElementById('fechaHasta').value;

    solicitudesFiltradas = todasLasSolicitudes.filter(s => {
        let nombreEntidad = '';
        if (s.tipo_solicitante === 'interno' && s.departamento) nombreEntidad = s.departamento.nombre;
        else if (s.tipo_solicitante === 'externo' && s.institucion) nombreEntidad = s.institucion.nombre;

        if (searchTerm && !nombreEntidad.toLowerCase().includes(searchTerm) && !(s.justificacion || '').toLowerCase().includes(searchTerm)) return false;
        if (estado && s.estado_solicitud !== estado) return false;
        if (prioridad && s.prioridad !== prioridad) return false;
        if (fechaDesde && new Date(s.fecha_requerida) < new Date(fechaDesde)) return false;
        if (fechaHasta && new Date(s.fecha_requerida) > new Date(fechaHasta)) return false;
        return true;
    });

    renderizarTabla();
}

function aplicarFiltrosConDebounce() { clearTimeout(timeoutBusqueda); timeoutBusqueda = setTimeout(() => { aplicarFiltros(); }, 300); }

// ==================== MODALES PRINCIPALES ====================
function abrirModalCrear() { document.getElementById('formCrearSolicitud').reset(); document.getElementById('tipoSolicitante').value = 'interno'; actualizarCamposSolicitante(); document.getElementById('modalCrear').style.display = 'flex'; }
function cerrarModalCrear() { document.getElementById('modalCrear').style.display = 'none'; }

function abrirBandejaCorreos() {
    document.getElementById('modalBandeja').style.display = 'flex';
    cargarListaCorreos();
    actualizarCamposFormularioCorreo();
}
function cerrarBandejaCorreos() { document.getElementById('modalBandeja').style.display = 'none'; }

function editarSolicitud(id) {
    const solicitud = todasLasSolicitudes.find(s => s.id === id);
    if (!solicitud) return;
    document.getElementById('editId').value = solicitud.id;
    document.getElementById('editTipoSolicitante').value = solicitud.tipo_solicitante;
    document.getElementById('editPrioridad').value = solicitud.prioridad;
    document.getElementById('editFechaRequerida').value = solicitud.fecha_requerida;
    document.getElementById('editFechaFin').value = solicitud.fecha_fin_estimada;
    document.getElementById('editJustificacion').value = solicitud.justificacion;
    document.getElementById('editObservaciones').value = solicitud.observaciones || '';
    if (solicitud.departamento_id) document.getElementById('editDepartamentoId').value = solicitud.departamento_id;
    if (solicitud.institucion_id) document.getElementById('editInstitucionId').value = solicitud.institucion_id;
    if (solicitud.responsable_id) document.getElementById('editResponsableId').value = solicitud.responsable_id;
    const editInternoFields = document.getElementById('editInternoFields');
    const editExternoFields = document.getElementById('editExternoFields');
    if (solicitud.tipo_solicitante === 'interno') { editInternoFields.style.display = 'block'; editExternoFields.style.display = 'none'; }
    else { editInternoFields.style.display = 'none'; editExternoFields.style.display = 'block'; }
    document.getElementById('modalEditar').style.display = 'flex';
    document.getElementById('formEditarSolicitud').action = `/solicitudes/${id}/update`;
}

function cerrarModalEditar() { document.getElementById('modalEditar').style.display = 'none'; }

document.getElementById('formEditarSolicitud')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = document.getElementById('editId').value;
    try {
        const response = await fetch(`/solicitudes/${id}/update`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: formData
        });
        const result = await response.json();
        if (response.ok && result.success) { mostrarNotificacion('success', 'Solicitud Actualizada', 'La solicitud ha sido actualizada exitosamente'); cerrarModalEditar(); setTimeout(() => window.location.reload(), 1500); }
        else { mostrarNotificacion('error', 'Error', result.message || 'No se pudo actualizar'); }
    } catch (error) { mostrarNotificacion('error', 'Error', 'Error de conexión'); }
});

// ==================== FUNCIONES PARA MODAL NORMAL ====================
function actualizarCamposSolicitante() {
    const tipo = document.getElementById('tipoSolicitante').value;
    const internoFields = document.getElementById('interno-fields');
    const externoFields = document.getElementById('externo-fields');
    if (tipo === 'interno') { internoFields.style.display = 'block'; externoFields.style.display = 'none'; }
    else { internoFields.style.display = 'none'; externoFields.style.display = 'block'; }
}

function manejarDepartamentoOtro() {
    const select = document.getElementById('departamentoSelect');
    const nuevoField = document.getElementById('departamento-nuevo-field');
    const nuevoInput = document.getElementById('nuevoDepartamento');
    if (select.value === 'otro') { nuevoField.style.display = 'block'; nuevoInput.required = true; }
    else { nuevoField.style.display = 'none'; nuevoInput.required = false; nuevoInput.value = ''; }
}

function manejarInstitucionOtro() {
    const select = document.getElementById('institucionSelect');
    const nuevoField = document.getElementById('institucion-nuevo-field');
    const nuevoInput = document.getElementById('nuevaInstitucion');
    if (select.value === 'otro') { nuevoField.style.display = 'block'; nuevoInput.required = true; }
    else { nuevoField.style.display = 'none'; nuevoInput.required = false; nuevoInput.value = ''; }
}

function manejarResponsableOtro() {
    const select = document.getElementById('responsableSelect');
    const nuevoField = document.getElementById('responsable-nuevo-field');
    const nuevoInput = document.getElementById('nuevoResponsable');
    if (select.value === 'otro') { nuevoField.style.display = 'flex'; nuevoInput.required = true; }
    else { nuevoField.style.display = 'none'; nuevoInput.required = false; nuevoInput.value = ''; }
}

// ==================== FUNCIONES PARA MODAL BANDEJA DE CORREOS ====================
function actualizarCamposFormularioCorreo() {
    const tipo = document.getElementById('formTipo')?.value;
    const internoFields = document.getElementById('formInternoFields');
    const externoFields = document.getElementById('formExternoFields');
    if (tipo === 'interno') {
        if (internoFields) internoFields.style.display = 'block';
        if (externoFields) externoFields.style.display = 'none';
    } else {
        if (internoFields) internoFields.style.display = 'none';
        if (externoFields) externoFields.style.display = 'block';
    }
}

function manejarFormDepartamentoOtro() {
    const select = document.getElementById('formDepartamentoSelect');
    const nuevoField = document.getElementById('formDepartamentoNuevoField');
    const nuevoInput = document.getElementById('formNuevoDepartamento');
    if (select && select.value === 'otro') {
        if (nuevoField) nuevoField.style.display = 'block';
        if (nuevoInput) nuevoInput.required = true;
    } else {
        if (nuevoField) nuevoField.style.display = 'none';
        if (nuevoInput) { nuevoInput.required = false; nuevoInput.value = ''; }
    }
}

function manejarFormInstitucionOtro() {
    const select = document.getElementById('formInstitucionSelect');
    const nuevoField = document.getElementById('formInstitucionNuevoField');
    const nuevoInput = document.getElementById('formNuevaInstitucion');
    if (select && select.value === 'otro') {
        if (nuevoField) nuevoField.style.display = 'block';
        if (nuevoInput) nuevoInput.required = true;
    } else {
        if (nuevoField) nuevoField.style.display = 'none';
        if (nuevoInput) { nuevoInput.required = false; nuevoInput.value = ''; }
    }
}

function manejarFormResponsableOtro() {
    const select = document.getElementById('formResponsableSelect');
    const nuevoField = document.getElementById('formResponsableNuevoField');
    const nuevoInput = document.getElementById('formNuevoResponsable');
    if (select && select.value === 'otro') {
        if (nuevoField) nuevoField.style.display = 'block';
        if (nuevoInput) nuevoInput.required = true;
    } else {
        if (nuevoField) nuevoField.style.display = 'none';
        if (nuevoInput) { nuevoInput.required = false; nuevoInput.value = ''; }
    }
}

document.getElementById('formTipo')?.addEventListener('change', actualizarCamposFormularioCorreo);
document.getElementById('formDepartamentoSelect')?.addEventListener('change', manejarFormDepartamentoOtro);
document.getElementById('formInstitucionSelect')?.addEventListener('change', manejarFormInstitucionOtro);
document.getElementById('formResponsableSelect')?.addEventListener('change', manejarFormResponsableOtro);

// ==================== FUNCIONES DE CORREOS ====================
function cargarListaCorreos() {
    const container = document.getElementById('listaCorreosContainer');
    if (correosPendientes.length === 0) { container.innerHTML = `<div class="text-center py-5 text-muted">No hay correos pendientes</div>`; return; }
    let html = '';
    for (const correo of correosPendientes) { html += `<div class="correo-item" onclick="seleccionarCorreo(${correo.id})"><div class="d-flex align-items-center gap-2 mb-2"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d"><rect x="2" y="4" width="16" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg><strong class="flex-grow-1 small">${correo.from}</strong><small class="text-muted">${correo.date}</small></div><div class="small fw-semibold">${correo.subject}</div><div class="small text-muted mt-1">${correo.body.substring(0, 60)}...</div></div>`; }
    container.innerHTML = html;
}

function seleccionarCorreo(id) {
    const correo = correosPendientes.find(c => c.id === id);
    if (!correo) return;
    document.getElementById('previewCorreo').innerHTML = `<div class="border rounded-3 p-3 bg-light"><div class="mb-2"><strong>De:</strong> ${correo.from}</div><div class="mb-2"><strong>Asunto:</strong> ${correo.subject}</div><div class="mb-2"><strong>Fecha:</strong> ${correo.date}</div><div class="mt-3 p-2 rounded-2 bg-white">${correo.body}</div></div>`;
    document.getElementById('correoOrigen').value = correo.from;
    document.getElementById('formPrioridad').value = correo.extracted.prioridad;
    document.getElementById('formFechaRequerida').value = correo.extracted.fecha_requerida;
    document.getElementById('formJustificacion').value = correo.extracted.justificacion;
    const fechaReq = new Date(correo.extracted.fecha_requerida);
    fechaReq.setDate(fechaReq.getDate() + 7);
    document.getElementById('formFechaFin').value = fechaReq.toISOString().split('T')[0];
}

function revisarCorreosManual() {
    const btn = event.target;
    btn.textContent = 'Revisando...';
    btn.disabled = true;
    setTimeout(() => {
        const nuevoCorreo = { id: correosPendientes.length + 1, from: "nuevo@empresa.com", subject: "Nueva solicitud recibida", date: new Date().toISOString().split('T')[0], body: "Solicito equipos para TI", extracted: { prioridad: "normal", fecha_requerida: new Date(Date.now() + 3*24*60*60*1000).toISOString().split('T')[0], justificacion: "Solicitud desde TI" } };
        correosPendientes.push(nuevoCorreo);
        btn.textContent = 'Revisar ahora';
        btn.disabled = false;
        cargarListaCorreos();
        document.getElementById('notificacionCorreos').style.display = 'inline-block';
        document.getElementById('notificacionCorreos').textContent = correosPendientes.length;
    }, 1500);
}

// ==================== ITEMS DINÁMICOS ====================
let itemCountModal = 1;
document.getElementById('add-item-modal')?.addEventListener('click', function() {
    const container = document.getElementById('items-container-modal');
    const newCard = document.createElement('div');
    newCard.className = 'item-card-modal p-3 mb-3';
    newCard.style.cssText = 'background: #f8f9fc; border: 1px solid #e9ecef; border-radius: 12px;';
    newCard.innerHTML = `<div class="row g-2 align-items-end"><div class="col-md-4"><select name="items[${itemCountModal}][tipo_item]" required class="form-select form-select-sm" style="border-radius: 8px; background: white;"><option value="activo">Activo</option><option value="periferico">Periférico</option></select></div><div class="col-md-6"><input type="text" name="items[${itemCountModal}][item_descripcion]" required placeholder="Ej: Laptop HP, Mouse" class="form-control form-control-sm" style="border-radius: 8px; background: white;"></div><div class="col-md-2"><input type="number" name="items[${itemCountModal}][cantidad]" min="1" value="1" required class="form-control form-control-sm" style="border-radius: 8px; background: white;"></div></div><div class="row mt-2"><div class="col-12 text-end"><button type="button" class="remove-item-modal btn btn-sm text-danger" style="font-size: 14px;">× Eliminar</button></div></div>`;
    container.appendChild(newCard);
    itemCountModal++;
});

let itemCountCorreo = 1;
document.getElementById('add-item-correo')?.addEventListener('click', function() {
    const container = document.getElementById('items-container-correo');
    const newCard = document.createElement('div');
    newCard.className = 'item-card-correo p-2 mb-2';
    newCard.style.cssText = 'background: #f8f9fc; border-radius: 8px;';
    newCard.innerHTML = `<div class="row g-2 align-items-end"><div class="col-md-4"><select name="items[${itemCountCorreo}][tipo_item]" required class="form-select form-select-sm" style="border-radius: 6px; background: white;"><option value="activo">Activo</option><option value="periferico">Periférico</option></select></div><div class="col-md-6"><input type="text" name="items[${itemCountCorreo}][item_descripcion]" required placeholder="Ej: Laptop HP, Mouse" class="form-control form-control-sm" style="border-radius: 6px; background: white;"></div><div class="col-md-2"><input type="number" name="items[${itemCountCorreo}][cantidad]" min="1" value="1" required class="form-control form-control-sm" style="border-radius: 6px; background: white;"></div></div><div class="text-end mt-2"><button type="button" class="remove-item-correo btn btn-sm text-danger" style="font-size: 14px;">× Eliminar</button></div>`;
    container.appendChild(newCard);
    itemCountCorreo++;
});

document.addEventListener('click', e => {
    if(e.target.classList.contains('remove-item-modal') || e.target.parentElement?.classList?.contains('remove-item-modal')) {
        const btn = e.target.classList.contains('remove-item-modal') ? e.target : e.target.parentElement;
        const card = btn.closest('.item-card-modal');
        if(document.querySelectorAll('#items-container-modal .item-card-modal').length > 1) card.remove();
        else alert('Debe haber al menos un item');
    }
    if(e.target.classList.contains('remove-item-correo') || e.target.parentElement?.classList?.contains('remove-item-correo')) {
        const btn = e.target.classList.contains('remove-item-correo') ? e.target : e.target.parentElement;
        const card = btn.closest('.item-card-correo');
        if(document.querySelectorAll('#items-container-correo .item-card-correo').length > 1) card.remove();
        else alert('Debe haber al menos un item');
    }
});

document.getElementById('modalCrear')?.addEventListener('click', e => { if(e.target === this) cerrarModalCrear(); });
document.getElementById('modalBandeja')?.addEventListener('click', e => { if(e.target === this) cerrarBandejaCorreos(); });
document.getElementById('modalEditar')?.addEventListener('click', e => { if(e.target === this) cerrarModalEditar(); });
document.getElementById('modalDetalles')?.addEventListener('click', e => { if(e.target === this) cerrarModalDetalles(); });

// ==================== ENVÍO DE FORMULARIOS ====================
document.getElementById('formCrearSolicitud')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const items = document.querySelectorAll('#items-container-modal .item-card-modal');
    if (items.length === 0) { mostrarNotificacion('error', 'Error', 'Debe agregar al menos un item'); return; }
    let itemsValidos = true;
    items.forEach(item => { if(!item.querySelector('select[name$="[tipo_item]"]')?.value || !item.querySelector('input[name$="[item_descripcion]"]')?.value || item.querySelector('input[name$="[cantidad]"]')?.value < 1) itemsValidos = false; });
    if (!itemsValidos) { mostrarNotificacion('error', 'Error', 'Complete todos los datos de los items'); return; }
    const submitBtn = document.getElementById('submitSolicitudBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';
    submitBtn.disabled = true;
    const formData = new FormData(this);
    try {
        const response = await fetch(this.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: formData });
        const result = await response.json();
        if (response.ok && result.success) { ultimoIdCreado = result.solicitud_id; mostrarNotificacion('success', 'Solicitud Creada', 'Solicitud registrada exitosamente', { ID: result.solicitud_id || 'N/A', Items: items.length }); cerrarModalCrear(); setTimeout(() => window.location.reload(), 1500); }
        else { mostrarNotificacion('error', 'Error', result.message || 'No se pudo crear'); }
    } catch (error) { mostrarNotificacion('error', 'Error', 'Error de conexión'); }
    finally { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
});

document.getElementById('formSolicitudCorreo')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const items = document.querySelectorAll('#items-container-correo .item-card-correo');
    if (items.length === 0) { mostrarNotificacion('error', 'Error', 'Debe agregar al menos un item'); return; }
    let itemsValidos = true;
    items.forEach(item => { if(!item.querySelector('select[name$="[tipo_item]"]')?.value || !item.querySelector('input[name$="[item_descripcion]"]')?.value || item.querySelector('input[name$="[cantidad]"]')?.value < 1) itemsValidos = false; });
    if (!itemsValidos) { mostrarNotificacion('error', 'Error', 'Complete todos los datos de los items'); return; }
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';
    submitBtn.disabled = true;
    const formData = new FormData(this);
    try {
        const response = await fetch(this.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: formData });
        const result = await response.json();
        if (response.ok && result.success) { mostrarNotificacion('success', 'Solicitud Creada', 'Solicitud registrada exitosamente', { ID: result.solicitud_id || 'N/A', Items: items.length }); cerrarBandejaCorreos(); setTimeout(() => window.location.reload(), 1500); }
        else { mostrarNotificacion('error', 'Error', result.message || 'No se pudo crear'); }
    } catch (error) { mostrarNotificacion('error', 'Error', 'Error de conexión'); }
    finally { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
});

// ==================== EVENT LISTENERS ====================
document.getElementById('searchInput')?.addEventListener('input', aplicarFiltrosConDebounce);
document.getElementById('estadoFilter')?.addEventListener('change', aplicarFiltros);
document.getElementById('prioridadFilter')?.addEventListener('change', aplicarFiltros);
document.getElementById('fechaDesde')?.addEventListener('change', aplicarFiltros);
document.getElementById('fechaHasta')?.addEventListener('change', aplicarFiltros);
document.getElementById('limpiarFiltros')?.addEventListener('click', () => {
    document.getElementById('searchInput').value = '';
    document.getElementById('estadoFilter').value = '';
    document.getElementById('prioridadFilter').value = '';
    document.getElementById('fechaDesde').value = '';
    document.getElementById('fechaHasta').value = '';
    solicitudesFiltradas = [...todasLasSolicitudes];
    renderizarTabla();
});

document.getElementById('tipoSolicitante')?.addEventListener('change', actualizarCamposSolicitante);
document.getElementById('departamentoSelect')?.addEventListener('change', manejarDepartamentoOtro);
document.getElementById('institucionSelect')?.addEventListener('change', manejarInstitucionOtro);
document.getElementById('responsableSelect')?.addEventListener('change', manejarResponsableOtro);

// ==================== INICIALIZAR ====================
renderizarTabla();
actualizarCamposSolicitante();
initInfinityScroll();

// Actualizar contadores iniciales
document.getElementById('resultadosCount').textContent = todasLasSolicitudes.length;
document.getElementById('totalRegistrosCount').textContent = totalRegistros;

setTimeout(() => { if(correosPendientes.length > 0) { document.getElementById('notificacionCorreos').style.display = 'inline-block'; document.getElementById('notificacionCorreos').textContent = correosPendientes.length; } }, 500);
</script>
@endsection
