@extends('layouts.dashboard')

@section('title', 'Fichas de Soporte Técnico')

@section('styles')
@vite(['resources/css/admin-soporte.css'])
@endsection

@section('content')
<div class="container-fluid px-4">

    {{-- ========== HEADER ========== --}}
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Fichas de Soporte Técnico
            </h4>
            <p>Gestión de mantenimiento y reparaciones</p>
        </div>
        <div class="dropdown">
            <button class="btn btn-primary-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1" style="display:inline-block;">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nueva Ficha
            </button>
            <ul class="dropdown-menu">
                @if(auth()->user()->hasPermission('crear-ficha-soporte'))
                <li>
                    <a class="dropdown-item" href="#" onclick="window.abrirModalCrearFicha(); return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2" style="display:inline-block;">
                            <rect x="2" y="6" width="20" height="12" rx="2"/>
                        </svg>
                        Crear Ficha Soporte
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="window.abrirModalEquipoExterno(); return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2" style="display:inline-block;">
                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                            <line x1="9" y1="4" x2="9" y2="20"/>
                            <line x1="15" y1="4" x2="15" y2="20"/>
                        </svg>
                        Registrar Equipo Externo
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>

    {{-- ========== TARJETAS DE ESTADÍSTICAS ========== --}}
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsTotal">{{ $totalFichas ?? 0 }}</div>
                <div class="stat-label">Total Fichas</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsEnProceso">{{ $enProceso ?? 0 }}</div>
                <div class="stat-label">En Proceso</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(246, 194, 62, 0.1); color: #f6c23e;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsFinalizados">{{ $finalizados ?? 0 }}</div>
                <div class="stat-label">Finalizados</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(30, 126, 52, 0.1); color: #1e7e34;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number" id="statsEquiposReparacion">{{ $equiposReparacion ?? 0 }}</div>
                <div class="stat-label">En Reparación</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ========== TABS ========== --}}
    <ul class="nav nav-tabs-custom mb-3" id="soporteTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-fichas" data-bs-toggle="tab" data-bs-target="#panel-fichas" type="button" role="tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px;">
                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                </svg>
                Fichas de Soporte
            </button>
        </li>
        @if(auth()->user()->hasPermission('ver-fichas-soporte'))
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-correos" data-bs-toggle="tab" data-bs-target="#panel-correos" type="button" role="tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px;">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M22 7l-10 7L2 7"/>
                </svg>
                Correo de Soporte
                <span class="tab-correos-badge" id="tabCorreosBadge" style="{{ ($correosNoProcesados ?? 0) > 0 ? '' : 'display:none;' }}">
                    {{ $correosNoProcesados ?? 0 }}
                </span>
            </button>
        </li>
        @endif
    </ul>

    <div class="tab-content">

        {{-- ============================================ --}}
        {{-- PANEL 1: FICHAS DE SOPORTE --}}
        {{-- ============================================ --}}
        <div class="tab-pane fade show active" id="panel-fichas" role="tabpanel">

            <div class="filters-bar">
                <div class="filtro-busqueda">
                    <div class="input-group">
                        <span class="input-group-text">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control" id="buscarFichas" placeholder="Buscar por activo, técnico, reportante...">
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <select class="form-select form-select-sm" id="filtroEstadoFichas" style="width: 160px;">
                        <option value="">Todos los estados</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="finalizado">Finalizados</option>
                    </select>
                    <button class="btn btn-outline-primary-dark btn-sm" id="limpiarFiltros">
                        Limpiar
                    </button>
                </div>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Activo</th>
                                <th>Técnico</th>
                                <th>Reporta</th>
                                <th>Ingreso</th>
                                <th>F. Requerida</th>
                                <th>Salida</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaFichas">
                            @forelse($fichas as $ficha)
                            <tr>
                                <td>
                                    @if($ficha->activo)
                                        <span class="fw-medium" style="color:#1e3c72;">
                                            {{ $ficha->activo->serial }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $ficha->activo->modelo?->nombre ?? 'N/A' }}
                                        </small>
                                    @else
                                        <span class="text-muted">Equipo externo</span>
                                    @endif
                                </td>
                                <td>{{ $ficha->tecnico_nombre ?? '---' }}</td>
                                <td>{{ $ficha->usuario_reporta_nombre ?? '---' }}</td>
                                <td>
                                    <small>{{ $ficha->fecha_ingreso?->format('d/m/Y') ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @if($ficha->fecha_requerida_entrega)
                                        @php
                                            $dias = $ficha->dias_restantes;
                                            $clase = 'badge-fecha-vigente';
                                            $texto = $ficha->fecha_requerida_entrega->format('d/m/Y');
                                            if ($ficha->esta_vencida) {
                                                $clase = 'badge-fecha-vencida';
                                                $texto .= ' (Vencida)';
                                            } elseif ($dias !== null && $dias <= 3) {
                                                $clase = 'badge-fecha-proxima';
                                                $texto .= " ({$dias} d)";
                                            }
                                        @endphp
                                        <span class="badge-fecha-entrega {{ $clase }}">
                                            {{ $texto }}
                                        </span>
                                    @else
                                        <span class="badge-fecha-entrega badge-fecha-sin">Sin fecha</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $ficha->fecha_salida?->format('d/m/Y') ?? '---' }}</small>
                                </td>
                                <td>
                                    @if($ficha->estado === 'en_proceso')
                                        <span class="badge-estado-en-proceso">En Proceso</span>
                                    @else
                                        <span class="badge-estado-finalizado">Finalizado</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn-action btn-outline-primary-dark" onclick="verDetalle({{ $ficha->id }})" title="Ver detalle">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M12 8v4"/>
                                            <path d="M12 16h.01"/>
                                        </svg>
                                    </button>
                                    @if($ficha->estado === 'en_proceso')
                                    <button type="button" class="btn-cerrar-ficha ms-1" onclick="abrirModalCerrarFicha({{ $ficha->id }})" title="Cerrar ficha">
                                        ✓ Cerrar
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasPermission('eliminar-ficha-soporte'))
                                    <button type="button" class="btn-action text-danger ms-1" onclick="confirmarEliminar({{ $ficha->id }})" title="Eliminar">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" class="mb-2">
                                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                                    </svg>
                                    <p>No hay fichas de soporte registradas</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAGINACIÓN --}}
            @if($fichas->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <div class="text-muted small" id="paginationInfo">
                    Mostrando {{ $fichas->firstItem() }} a {{ $fichas->lastItem() }}
                    de {{ $fichas->total() }} registros
                </div>
                <nav>
                    {{ $fichas->links() }}
                </nav>
            </div>
            @endif

        </div>

        {{-- ============================================ --}}
        {{-- PANEL 2: CORREO DE SOPORTE --}}
        {{-- ============================================ --}}
        @if(auth()->user()->hasPermission('ver-fichas-soporte'))
        <div class="tab-pane fade" id="panel-correos" role="tabpanel">
            <div class="filters-bar">
                <div class="input-group" style="max-width: 400px;">
                    <span class="input-group-text">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </span>
                    <input type="text" class="form-control" id="buscarCorreo" placeholder="Buscar por remitente, asunto...">
                </div>
                <select class="form-select" id="filtroCorreo" style="max-width: 200px;">
                    <option value="">Todos</option>
                    <option value="no_leidos">No leídos</option>
                    <option value="no_procesados">Sin procesar</option>
                    <option value="procesados">Procesados</option>
                </select>
                <button class="btn btn-primary-dark" onclick="revisarCorreos()" id="btnRevisarCorreos">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:4px;">
                        <polyline points="23 4 23 10 17 10"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                    Revisar ahora
                </button>
            </div>
            <div id="listaCorreos" class="p-3" style="background: white; border-radius: 12px;">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando correos...</p>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: CREAR FICHA MANUAL --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalCrearFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearFichaLabel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:8px;">
                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                    </svg>
                    Nueva Ficha de Soporte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCrearFicha">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Buscar Activo <span class="text-danger">*</span></label>
                        <div class="activo-buscar-container">
                            <input type="text" class="form-control" id="activoBuscarInput" placeholder="Escriba el serial, modelo o marca del activo..." autocomplete="off">
                            <input type="hidden" id="fichaActivoId" name="activo_id" value="">
                            <div class="activo-dropdown" id="activoDropdown"></div>
                        </div>
                        <div class="activo-seleccionado-info" id="activoSeleccionadoInfo" style="display: none;">
                            <span class="fw-medium" id="activoSeleccionadoTexto"></span>
                            <span class="badge bg-success ms-2" id="activoSeleccionadoEstado"></span>
                            <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="window.limpiarActivoSeleccionado()">✕</button>
                        </div>
                        <small class="text-muted">Solo se muestran activos disponibles</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Técnico Responsable</label>
                        <div class="tecnico-search-container">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"/>
                                        <path d="M21 21l-4.35-4.35"/>
                                    </svg>
                                </span>
                                <input type="text" class="form-control" id="tecnicoBuscarInput" placeholder="Buscar por cédula, nombre o usuario..." autocomplete="off">
                            </div>
                            <div id="tecnicoSearchResults" class="tecnico-search-results" style="display:none;"></div>
                            <div id="tecnicoEncontrado" class="tecnico-encontrado" style="display:none;">
                                <div class="d-flex align-items-start gap-2">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" class="mt-1 flex-shrink-0">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <div>
                                        <p class="mb-1 nombre" id="tecnicoEncontradoNombre"></p>
                                        <p class="mb-0 info" id="tecnicoEncontradoCedula"></p>
                                        <p class="mb-0 info" id="tecnicoEncontradoUsuario"></p>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="fichaTecnicoId" name="tecnico_id" value="">
                            <input type="hidden" id="fichaTecnicoNombre" name="tecnico_nombre" value="">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Usuario que Reporta <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fichaUsuarioReporta" name="usuario_reporta_nombre" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Diagnóstico</label>
                        <textarea name="diagnostico" id="fichaDiagnostico" rows="3" class="form-control" placeholder="Describa el problema del equipo..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="fichaObservaciones" rows="2" class="form-control" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarFicha">Guardar Ficha</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: EQUIPO EXTERNO --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalEquipoExterno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEquipoExternoLabel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:8px;">
                        <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                        <line x1="9" y1="4" x2="9" y2="20"/>
                        <line x1="15" y1="4" x2="15" y2="20"/>
                    </svg>
                    Registrar Equipo Externo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEquipoExterno">
                @csrf
                <div class="modal-body">
                    <div class="equipo-externo-card">
                        <div class="card-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; margin-right:8px;">
                                <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                                <line x1="9" y1="4" x2="9" y2="20"/>
                                <line x1="15" y1="4" x2="15" y2="20"/>
                            </svg>
                            Datos del Equipo Externo
                        </div>
                        <p class="text-muted small">Complete los datos del equipo que ingresa a reparación. Se creará automáticamente en el inventario.</p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Serial <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ext_serial" name="serial" required>
                                <small id="ext_serial_feedback" class="text-muted">Ingrese el número de serie para verificar si ya existe</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Modelo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ext_modelo_nombre" name="modelo_nombre" required placeholder="Ej: Dell Latitude 5540">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Marca <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ext_marca" name="marca" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select" id="ext_categoria_id" name="categoria_id" required>
                                    <option value="">Seleccionar categoría...</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Institución <span class="text-danger">*</span></label>
                                <select class="form-select" id="ext_institucion_id" name="institucion_id" required>
                                    <option value="">Seleccionar institución...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Responsable <span class="text-danger">*</span></label>
                                <select class="form-select" id="ext_responsable_id" name="responsable_id" required>
                                    <option value="">Seleccionar responsable...</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ubicación</label>
                                <input type="text" class="form-control" id="ext_ubicacion" name="ubicacion" placeholder="Laboratorio 2">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Adquisición</label>
                                <input type="date" class="form-control" id="ext_fecha_adquisicion" name="fecha_adquisicion">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="ext_observaciones" name="observaciones" rows="2" placeholder="Observaciones del equipo..."></textarea>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6 style="color:#1e3c72; font-weight:600; margin-bottom:1rem; border-bottom:1px solid #e9ecef; padding-bottom:0.5rem;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; margin-right:6px;">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            Datos de la Ficha de Soporte
                        </h6>

                        <div class="mb-3">
                            <label class="form-label">Técnico Responsable</label>
                            <div class="tecnico-search-container">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2">
                                            <circle cx="11" cy="11" r="8"/>
                                            <path d="M21 21l-4.35-4.35"/>
                                        </svg>
                                    </span>
                                    <input type="text" class="form-control" id="ext_tecnicoBuscarInput" placeholder="Buscar por cédula, nombre o usuario..." autocomplete="off">
                                </div>
                                <div id="extTecnicoSearchResults" class="tecnico-search-results" style="display:none;"></div>
                                <div id="extTecnicoEncontrado" class="tecnico-encontrado" style="display:none;">
                                    <div class="d-flex align-items-start gap-2">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" class="mt-1 flex-shrink-0">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                            <circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        <div>
                                            <p class="mb-1 nombre" id="extTecnicoEncontradoNombre"></p>
                                            <p class="mb-0 info" id="extTecnicoEncontradoCedula"></p>
                                            <p class="mb-0 info" id="extTecnicoEncontradoUsuario"></p>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="ext_fichaTecnicoId" name="tecnico_id" value="">
                                <input type="hidden" id="ext_fichaTecnicoNombre" name="tecnico_nombre" value="">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Usuario que Reporta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ext_usuario_reporta" name="usuario_reporta_nombre" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Diagnóstico</label>
                            <textarea name="diagnostico" id="ext_diagnostico" rows="3" class="form-control" placeholder="Describa el problema del equipo..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones_ficha" id="ext_observaciones_ficha" rows="2" class="form-control" placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarEquipoExterno">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:4px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Registrar Equipo y Crear Ficha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: CERRAR FICHA --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalCerrarFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCerrarFichaLabel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline-block; margin-right:8px;">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Cerrar Ficha de Soporte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCerrarFicha">
                @csrf
                <input type="hidden" id="cerrarFichaId" name="ficha_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Trabajo Realizado</label>
                        <textarea name="trabajo_realizado" id="cerrarTrabajoRealizado" rows="3" class="form-control" placeholder="Describa el trabajo realizado..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones Finales</label>
                        <textarea name="observaciones_finales" id="cerrarObservacionesFinales" rows="2" class="form-control" placeholder="Observaciones finales..."></textarea>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-3" style="color: #1e3c72;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block; margin-right:6px;">
                            <rect x="2" y="6" width="20" height="12" rx="2"/>
                        </svg>
                        Estado de Componentes
                    </h6>
                    <div id="componentesContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cerrar-ficha">Finalizar Ficha</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: DETALLE --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleLabel">Detalle de Ficha</h5>
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

{{-- ============================================================ --}}
{{-- MODAL: ELIMINAR --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar esta ficha?</p>
                <p class="fw-bold text-danger" id="deleteNombre"></p>
                <p class="small text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: CORREO + WIZARD DE CONVERSIÓN --}}
{{-- ============================================================ --}}
@include('admin.soporte.partials.modal-correo-wizard')

{{-- Notificaciones --}}
<div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: 320px;"></div>

@endsection

@section('scripts')
@vite(['resources/js/admin-soporte.js'])
<script>
window.userPermissions = @json(auth()->user()->rol->permisos->pluck('nombre'));
function authUserHasPermission(p) { return window.userPermissions.includes(p); }

document.addEventListener('DOMContentLoaded', function () {
    const serialInput = document.getElementById('ext_serial');
    const feedback = document.getElementById('ext_serial_feedback');

    if (serialInput && feedback) {
        serialInput.addEventListener('blur', function () {
            const serial = this.value.trim();
            if (serial.length < 3) {
                feedback.textContent = 'Ingrese al menos 3 caracteres para verificar';
                feedback.className = 'text-muted';
                this.classList.remove('is-valid', 'is-invalid');
                return;
            }

            feedback.textContent = 'Verificando serial...';
            feedback.className = 'text-muted';

            fetch(`/admin/activos?buscar=${encodeURIComponent(serial)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const existe = data.data.some(a => a.serial === serial);
                    if (existe) {
                        feedback.textContent = '⚠️ Este serial ya está registrado en el sistema.';
                        feedback.className = 'text-danger';
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    } else {
                        feedback.textContent = '✓ Serial disponible';
                        feedback.className = 'text-success';
                        this.classList.add('is-valid');
                        this.classList.remove('is-invalid');
                    }
                }
            })
            .catch(() => {
                feedback.textContent = 'Error al verificar serial';
                feedback.className = 'text-danger';
            });
        });

        serialInput.addEventListener('input', function () {
            this.classList.remove('is-valid', 'is-invalid');
            const fb = document.getElementById('ext_serial_feedback');
            if (fb) {
                fb.textContent = 'Ingrese el número de serie para verificar si ya existe';
                fb.className = 'text-muted';
            }
        });
    }
});
</script>
@endsection