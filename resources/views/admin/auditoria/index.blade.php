@extends('layouts.dashboard')

@section('title', 'Bitácora de Auditoría')

@section('styles')
    @vite(['resources/css/admin-auditoria.css'])
@endsection

@section('content')
<div class="container-fluid px-4">

    {{-- ========== HEADER ========== --}}
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                </svg>
                Bitácora de Auditoría
            </h4>
            <p>Registro detallado de todas las acciones realizadas en el sistema</p>
        </div>
        <div>
            <span class="badge bg-light text-dark me-2">Total: {{ $totalRegistros }}</span>
            <span class="badge bg-info text-white me-2">Hoy: {{ $hoy }}</span>
            <span class="badge bg-primary text-white me-2">Semana: {{ $semana }}</span>
            <span class="badge bg-success text-white">Mes: {{ $mes }}</span>
        </div>
    </div>

    {{-- ========== FILTROS ========== --}}
    <div class="filters-bar">
        <div class="filtro-busqueda">
            <div class="input-group">
                <span class="input-group-text">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </span>
                <input type="text" class="form-control" id="buscarAuditoria"
                       placeholder="Buscar por usuario, módulo, acción, IP..."
                       value="{{ request('buscar') }}">
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <select class="form-select form-select-sm" id="filtroModulo" style="width:150px;">
                <option value="">Todos los módulos</option>
                @foreach($modulos as $modulo)
                    <option value="{{ $modulo }}" {{ request('modulo') == $modulo ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $modulo)) }}</option>
                @endforeach
            </select>
            <select class="form-select form-select-sm" id="filtroAccion" style="width:150px;">
                <option value="">Todas las acciones</option>
                @foreach($acciones as $accion)
                    <option value="{{ $accion }}" {{ request('accion') == $accion ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $accion)) }}</option>
                @endforeach
            </select>
            <input type="date" class="form-control form-control-sm" id="filtroFechaDesde" style="width:150px;" value="{{ request('fecha_desde') }}">
            <input type="date" class="form-control form-control-sm" id="filtroFechaHasta" style="width:150px;" value="{{ request('fecha_hasta') }}">
            <button class="btn btn-sm btn-outline-primary-dark" id="limpiarFiltros">Limpiar</button>
        </div>
    </div>

    {{-- ========== TABLA ========== --}}
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th style="width:170px;">Fecha</th>
                        <th style="width:130px;">Usuario</th>
                        <th style="width:130px;">Módulo</th>
                        <th style="width:110px;">Acción</th>
                        <th>Descripción</th>
                        <th style="width:130px;">IP</th>
                        <th style="width:60px;">Detalle</th>
                    </tr>
                </thead>
                <tbody id="tablaAuditoria">
                    @forelse($auditoria as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($auditoria->currentPage() - 1) * $auditoria->perPage() }}</td>
                            <td data-label="Fecha">
                                <small>{{ $item->created_at->format('d/m/Y H:i:s') }}</small>
                            </td>
                            <td data-label="Usuario">
                                <span class="fw-medium" style="color: var(--primary-dark);">
                                    {{ $item->usuario_nombre ?? 'Sistema' }}
                                </span>
                            </td>
                            <td data-label="Módulo">
                                <span class="badge-modulo">{{ ucfirst(str_replace('_', ' ', $item->modulo)) }}</span>
                            </td>
                            <td data-label="Acción">
                                <span class="badge-accion {{ $item->accion }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->accion)) }}
                                </span>
                            </td>
                            <td data-label="Descripción">
                                <span class="small">{{ Str::limit($item->descripcion, 100) }}</span>
                            </td>
                            <td data-label="IP"><small>{{ $item->ip_address ?? '---' }}</small></td>
                            <td data-label="Detalle">
                                <button class="btn btn-sm btn-action btn-outline-primary-dark ver-detalle"
                                        data-id="{{ $item->id }}" title="Ver detalle">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" class="mb-2">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                </svg>
                                <p>No hay registros de auditoría</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========== PAGINACIÓN ========== --}}
    <div class="pagination-bar">
        <div class="pagination-info">
            Mostrando {{ $auditoria->firstItem() ?? 0 }} a {{ $auditoria->lastItem() ?? 0 }} de {{ $auditoria->total() }} registros
        </div>
        {{ $auditoria->links() }}
    </div>
</div>

{{-- ========== MODAL DETALLE ========== --}}
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            {{-- HEADER --}}
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="detalle-header-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    </div>
                    <div>
                        <h5 class="modal-title">Detalle de Auditoría</h5>
                        <span class="modal-subtitle">Información completa del registro</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body" id="detalleContenido">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 2.5rem; height: 2.5rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Cargando detalles del registro...</p>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right: 6px; vertical-align: middle;">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== BÚSQUEDA EN TIEMPO REAL ==========
    const buscarInput = document.getElementById('buscarAuditoria');
    const filtroModulo = document.getElementById('filtroModulo');
    const filtroAccion = document.getElementById('filtroAccion');
    const filtroFechaDesde = document.getElementById('filtroFechaDesde');
    const filtroFechaHasta = document.getElementById('filtroFechaHasta');
    const limpiarBtn = document.getElementById('limpiarFiltros');
    let timeoutBusqueda = null;

    function aplicarFiltros() {
        const params = new URLSearchParams();
        if (buscarInput.value.trim()) params.set('buscar', buscarInput.value.trim());
        if (filtroModulo.value) params.set('modulo', filtroModulo.value);
        if (filtroAccion.value) params.set('accion', filtroAccion.value);
        if (filtroFechaDesde.value) params.set('fecha_desde', filtroFechaDesde.value);
        if (filtroFechaHasta.value) params.set('fecha_hasta', filtroFechaHasta.value);

        const queryString = params.toString();
        window.location.href = window.location.pathname + (queryString ? '?' + queryString : '');
    }

    function aplicarFiltrosConDebounce() {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(aplicarFiltros, 400);
    }

    if (buscarInput) buscarInput.addEventListener('input', aplicarFiltrosConDebounce);
    if (filtroModulo) filtroModulo.addEventListener('change', aplicarFiltros);
    if (filtroAccion) filtroAccion.addEventListener('change', aplicarFiltros);
    if (filtroFechaDesde) filtroFechaDesde.addEventListener('change', aplicarFiltros);
    if (filtroFechaHasta) filtroFechaHasta.addEventListener('change', aplicarFiltros);
    if (limpiarBtn) limpiarBtn.addEventListener('click', function() {
        window.location.href = window.location.pathname;
    });

    // ========== VER DETALLE ==========
    document.querySelectorAll('.ver-detalle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
            const body = document.getElementById('detalleContenido');

            body.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" style="width: 2.5rem; height: 2.5rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted" style="font-size: 0.9rem;">Cargando detalles...</p>
                </div>
            `;
            modal.show();

            fetch(`/admin/auditoria/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        body.innerHTML = renderDetalle(data.data);
                    } else {
                        body.innerHTML = renderError(data.message || 'Error al cargar los detalles');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    body.innerHTML = renderError(error.message || 'Intente nuevamente');
                });
        });
    });

    // ========== HELPERS DE RENDERIZADO ==========
    function renderDetalle(d) {
        const camposHtml = renderCampos(d.campos);
        const descripcionHtml = d.descripcion ? `
            <div class="detalle-descripcion-usuario">
                <div class="desc-label">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <path d="M22 7l-10 7L2 7"/>
                    </svg>
                    Descripción de la Acción
                </div>
                <div class="desc-texto">${d.descripcion}</div>
            </div>
        ` : '';

        const userAgentHtml = d.user_agent ? `
            <div class="detalle-user-agent">
                <span class="ua-label">🖥️ User Agent:</span>
                ${d.user_agent}
            </div>
        ` : '';

        return `
            <div class="detalle-auditoria-moderno">
                <div class="detalle-header-card">
                    <div class="header-left">
                        <span class="badge-id">#${d.id}</span>
                        <span class="badge-accion-detalle" style="background: ${d.accion_color}20; color: ${d.accion_color}; border: 1px solid ${d.accion_color}40;">
                            ${d.accion_icono} ${d.accion_texto}
                        </span>
                        <span class="badge-modulo-detalle">${d.modulo}</span>
                    </div>
                    <div class="header-right">
                        <span class="badge-ip">🌐 ${d.ip_address || '---'}</span>
                        <span class="fecha-detalle">🕐 ${d.fecha}</span>
                    </div>
                </div>

                <div class="detalle-grid-moderno">
                    ${renderInfoItem('Usuario', `${d.usuario_nombre}${d.usuario ? ` <span class="text-muted-small">(${d.usuario.usuario})</span>` : ''}`, 'user')}
                    ${renderInfoItem('Fecha y Hora', `${d.fecha} <span class="text-muted-small">(${d.fecha_humana})</span>`, 'calendar')}
                    ${renderInfoItem('Tabla Afectada', d.tabla_afectada || '---', 'table')}
                    ${renderInfoItem('Registro ID', d.registro_id || '---', 'id')}
                </div>

                ${descripcionHtml}
                ${camposHtml}
                ${userAgentHtml}
            </div>
        `;
    }

    function renderInfoItem(label, value, icon) {
        const iconos = {
            user: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
            calendar: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
            table: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="8" width="16" height="12" rx="1"/><path d="M8 20V8M16 20V8M4 12h16"/></svg>`,
            id: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"/><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg>`
        };
        return `
            <div class="detalle-item-moderno">
                <div class="detalle-label">${iconos[icon] || ''}${label}</div>
                <div class="detalle-value">${value}</div>
            </div>
        `;
    }

    function renderCampos(campos) {
        if (!campos || campos.length === 0) {
            return renderVacio('No hay datos adicionales para mostrar');
        }

        const conCambios = campos.filter(c => c.cambio === true);
        const sinCambios = campos.filter(c => c.cambio === false);

        if (conCambios.length > 0) return renderTablaCambios(conCambios);
        if (sinCambios.length > 0) return renderTablaDatos(sinCambios);
        return renderVacio('No hay datos adicionales para mostrar');
    }

    function renderTablaCambios(campos) {
        return `
            <div class="detalle-cambios-container">
                <div class="cambios-header">
                    <div class="cambios-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"/>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                        </svg>
                        Cambios Realizados
                        <span class="badge-count">${campos.length}</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-cambios">
                        <thead>
                            <tr>
                                <th style="width:25%;">Campo</th>
                                <th style="width:30%;">Valor Anterior</th>
                                <th style="width:30%;">Nuevo Valor</th>
                                <th style="width:15%;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${campos.map(c => `
                                <tr>
                                    <td data-label="Campo"><strong>${c.etiqueta}</strong></td>
                                    <td data-label="Valor Anterior"><span class="valor-antiguo">${c.valor_original}</span></td>
                                    <td data-label="Nuevo Valor"><span class="valor-nuevo">${c.valor_nuevo}</span></td>
                                    <td data-label="Estado"><span class="badge-cambio modificado">Modificado</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    function renderTablaDatos(campos) {
        return `
            <div class="detalle-cambios-container">
                <div class="cambios-header">
                    <div class="cambios-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Datos Registrados
                        <span class="badge-count">${campos.length}</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-cambios">
                        <thead>
                            <tr>
                                <th style="width:40%;">Campo</th>
                                <th style="width:60%;">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${campos.map(c => `
                                <tr>
                                    <td data-label="Campo"><strong>${c.etiqueta}</strong></td>
                                    <td data-label="Valor"><span class="valor-nuevo">${c.valor_nuevo}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    function renderVacio(mensaje) {
        return `
            <div class="detalle-vacio">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <p>${mensaje}</p>
            </div>
        `;
    }

    function renderError(mensaje) {
        return `
            <div class="text-center text-danger py-4">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5" class="mb-2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <p style="font-size: 1rem; font-weight: 500;">${mensaje}</p>
            </div>
        `;
    }
});
</script>
@endsection