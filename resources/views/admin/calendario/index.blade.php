@extends('layouts.dashboard')

@section('title', 'Calendario de Actividades')

@section('styles')
<style>
/* ========== VARIABLES ========== */
:root {
    --primary-color: #1e3c72;
    --primary-light: #2a5298;
    --success: #22c55e;
    --danger: #ef4444;
    --warning: #f59e0b;
    --info: #3b82f6;
    --purple: #8b5cf6;
    --gray-bg: #f8fafc;
    --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    --radius: 16px;
}

/* ========== LAYOUT ========== */
.calendario-moderno {
    padding: 0;
}

/* ========== HEADER ========== */
.calendario-header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-radius: var(--radius);
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.calendario-header h3 {
    margin: 0;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.calendario-header p {
    margin: 0;
    opacity: 0.8;
    font-size: 0.9rem;
}

.calendario-header .btn-outline-light {
    color: white;
    border-color: rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.calendario-header .btn-outline-light:hover {
    background: white;
    color: #1e3c72;
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

/* ========== STATS ========== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: var(--radius);
    padding: 1.25rem 1.5rem;
    box-shadow: var(--card-shadow);
    border: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon svg {
    width: 24px;
    height: 24px;
    stroke: currentColor;
    stroke-width: 1.8;
    fill: none;
}

.stat-icon.blue { background: rgba(30, 60, 114, 0.1); color: #1e3c72; }
.stat-icon.green { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.stat-icon.red { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.stat-icon.yellow { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

.stat-info .stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

.stat-info .stat-label {
    font-size: 0.7rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

/* ========== FILTROS ========== */
.filtros-rapidos {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: center;
}

.filtros-rapidos .btn-filtro {
    padding: 0.3rem 1rem;
    border-radius: 30px;
    border: 1px solid #e2e8f0;
    background: white;
    font-size: 0.8rem;
    color: #64748b;
    transition: all 0.2s ease;
    cursor: pointer;
}

.filtros-rapidos .btn-filtro:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}

.filtros-rapidos .btn-filtro.active {
    background: #1e3c72;
    color: white;
    border-color: #1e3c72;
}

.filtros-rapidos .btn-filtro.danger.active {
    background: #ef4444;
    border-color: #ef4444;
}

.filtros-rapidos .btn-filtro.success.active {
    background: #22c55e;
    border-color: #22c55e;
}

/* ========== TIMELINE ========== */
.timeline-container {
    background: white;
    border-radius: var(--radius);
    padding: 1.5rem;
    box-shadow: var(--card-shadow);
    border: 1px solid #e9ecef;
    max-height: 700px;
    overflow-y: auto;
}

.timeline-container::-webkit-scrollbar {
    width: 6px;
}

.timeline-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.timeline-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.timeline-item {
    position: relative;
    padding-left: 2.5rem;
    padding-bottom: 1.5rem;
    animation: slideIn 0.3s ease forwards;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #e2e8f0, #cbd5e1);
}

.timeline-item:last-child::before {
    bottom: 50%;
}

.timeline-item .timeline-dot {
    position: absolute;
    left: 0;
    top: 4px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 3px #1e3c72;
    background: #1e3c72;
    z-index: 1;
}

.timeline-item .timeline-dot.success { box-shadow: 0 0 0 3px #22c55e; background: #22c55e; }
.timeline-item .timeline-dot.danger { box-shadow: 0 0 0 3px #ef4444; background: #ef4444; }
.timeline-item .timeline-dot.warning { box-shadow: 0 0 0 3px #f59e0b; background: #f59e0b; }
.timeline-item .timeline-dot.info { box-shadow: 0 0 0 3px #3b82f6; background: #3b82f6; }
.timeline-item .timeline-dot.secondary { box-shadow: 0 0 0 3px #6c757d; background: #6c757d; }
.timeline-item .timeline-dot.purple { box-shadow: 0 0 0 3px #8b5cf6; background: #8b5cf6; }

.timeline-item .timeline-date {
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.timeline-item .timeline-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
    cursor: pointer;
}

.timeline-item .timeline-card:hover {
    background: #f1f5f9;
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.timeline-item .timeline-card .card-header-custom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.timeline-item .timeline-card .card-title {
    font-weight: 600;
    color: #0f172a;
    font-size: 0.9rem;
}

.timeline-item .timeline-card .card-subtitle {
    font-size: 0.8rem;
    color: #64748b;
}

.timeline-item .timeline-card .badge-estado {
    font-size: 0.6rem;
    padding: 2px 10px;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    flex-shrink: 0;
}

.badge-estado.entregado { background: #dcfce7; color: #16a34a; }
.badge-estado.aprobado { background: #dbeafe; color: #2563eb; }
.badge-estado.extendido { background: #ede9fe; color: #7c3aed; }
.badge-estado.devuelto { background: #e0e7ff; color: #4f46e5; }
.badge-estado.pendiente { background: #f1f5f9; color: #64748b; }
.badge-estado.vencido { background: #fee2e2; color: #dc2626; }
.badge-estado.cancelado { background: #f1f5f9; color: #64748b; }
.badge-estado.rechazado { background: #fee2e2; color: #dc2626; }

.timeline-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}

.timeline-empty svg {
    width: 48px;
    height: 48px;
    stroke: #cbd5e1;
    margin-bottom: 1rem;
}

.timeline-empty h5 {
    color: #0f172a;
    margin-bottom: 0.5rem;
}

/* ========== MODAL DETALLE DÍA ========== */
#modalDiaEventos .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

#modalDiaEventos .modal-header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    padding: 1.25rem 1.75rem;
    border: none;
}

#modalDiaEventos .modal-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
    color: white;
}

#modalDiaEventos .modal-body {
    padding: 1.5rem;
    max-height: 70vh;
    overflow-y: auto;
    background: #f8fafc;
}

#modalDiaEventos .modal-footer {
    background: white;
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}

#modalDiaEventos .btn-cerrar-modal {
    background: #1e3c72;
    color: white;
    border-radius: 10px;
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    border: none;
    transition: all 0.2s ease;
}

#modalDiaEventos .btn-cerrar-modal:hover {
    background: #2a5298;
    transform: translateY(-1px);
}

.evento-card-modal {
    background: white;
    border-left: 4px solid #1e3c72;
    border-radius: 10px;
    padding: 0.85rem 1rem;
    margin-bottom: 0.6rem;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
}

.evento-card-modal:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(30, 60, 114, 0.12);
    background: #f8fafc;
}

.evento-card-modal .evento-titulo {
    font-weight: 700;
    color: #1e3c72;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.evento-card-modal .evento-meta {
    font-size: 0.78rem;
    color: #64748b;
    margin-top: 0.25rem;
}

.evento-badge-estado {
    padding: 0.15rem 0.6rem;
    border-radius: 12px;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.modal-seccion-titulo {
    color: #1e3c72;
    font-weight: 700;
    font-size: 0.85rem;
    margin: 1rem 0 0.75rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.modal-seccion-titulo:first-child {
    margin-top: 0;
}

/* ========== MODAL DETALLE PRÉSTAMO ========== */
#modalDetallePrestamo .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
}

#modalDetallePrestamo .modal-header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    border: none;
}

#modalDetallePrestamo .modal-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
    color: white;
}

/* ========== ANIMACIONES ========== */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* ========== RESPONSIVE ========== */
@media (max-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .calendario-header {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .filtros-rapidos .btn-filtro {
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
    }

    .timeline-item {
        padding-left: 1.8rem;
    }

    .timeline-item .timeline-dot {
        width: 14px;
        height: 14px;
        left: -2px;
        top: 2px;
    }
}

@media (max-width: 576px) {
    .calendario-header {
        padding: 1rem;
    }

    .timeline-container {
        padding: 0.75rem;
    }
}
</style>
@endsection

@section('content')
<div class="calendario-moderno">

    <!-- ========== HEADER ========== -->
    <div class="calendario-header">
        <div>
            <h3>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Calendario de Actividades
            </h3>
            <p>Visualiza préstamos y solicitudes en línea de tiempo</p>
        </div>
        <div>
            <a href="{{ route('admin.prestamos.index') }}" class="btn btn-outline-light">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
                Ir a Préstamos
            </a>
        </div>
    </div>

    <!-- ========== STATS ========== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-number">{{ $totalPrestamos ?? 0 }}</div>
                <div class="stat-label">Total Préstamos</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-number">{{ $prestamosActivos ?? 0 }}</div>
                <div class="stat-label">Préstamos Activos</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">
                <svg viewBox="0 0 24 24">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-number" style="color: {{ ($prestamosVencidos ?? 0) > 0 ? '#ef4444' : '#0f172a' }};">
                    {{ $prestamosVencidos ?? 0 }}
                </div>
                <div class="stat-label">Préstamos Vencidos</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon yellow">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-number">{{ $solicitudesPendientes ?? 0 }}</div>
                <div class="stat-label">Solicitudes Pendientes</div>
            </div>
        </div>
    </div>

    <!-- ========== FILTROS ========== -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="filtros-rapidos">
            <button class="btn-filtro active" data-filtro="todos" onclick="filtrarVista('todos', this)">Todos</button>
            <button class="btn-filtro" data-filtro="prestamos" onclick="filtrarVista('prestamos', this)">Préstamos</button>
            <button class="btn-filtro" data-filtro="solicitudes" onclick="filtrarVista('solicitudes', this)">Solicitudes</button>
            <button class="btn-filtro danger" data-filtro="vencidos" onclick="filtrarVista('vencidos', this)">Vencidos</button>
            <button class="btn-filtro success" data-filtro="devueltos" onclick="filtrarVista('devueltos', this)">Devueltos</button>
        </div>
    </div>

    <!-- ========== VISTA TIMELINE ========== -->
    <div id="timelineView">
        <div class="timeline-container" id="timelineContainer">
            <div class="timeline-empty">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3">Cargando eventos...</p>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL DETALLE DÍA ========== -->
<div class="modal fade" id="modalDiaEventos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Eventos del Día
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDiaEventosBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn-cerrar-modal" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL DETALLE PRÉSTAMO / SOLICITUD ========== -->
<div class="modal fade" id="modalDetallePrestamo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetallePrestamoTitle">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    Detalle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetallePrestamoBody" style="padding: 1.5rem;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Cargando...</p>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e9ecef;">
                <button type="button" class="btn" style="background:#f1f5f9; color:#475569; border-radius:10px; padding: 0.5rem 1.25rem;" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="modalDetallePrestamoActionBtn" class="btn" style="background:#1e3c72; color:white; border-radius:10px; padding: 0.5rem 1.25rem;" target="_blank">
                    Ver en el sistema
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ============================================================
// ESTADO GLOBAL
// ============================================================
let todosLosEventos = [];
let eventosPorFecha = {};
let filtroActual = 'todos';

// ============================================================
// INICIALIZACIÓN
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    cargarEventos();
});

async function cargarEventos() {
    const container = document.getElementById('timelineContainer');
    if (container) {
        container.innerHTML = `
            <div class="timeline-empty">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3">Cargando eventos...</p>
            </div>
        `;
    }

    try {
        const response = await fetch('{{ route("calendario.eventos") }}', {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const eventos = await response.json();

        todosLosEventos = eventos.map(ev => ({
            id: ev.id,
            title: ev.title,
            start: ev.startStr ? ev.startStr.split('T')[0] : (ev.start || null),
            end: ev.endStr ? ev.endStr.split('T')[0] : (ev.end || null),
            tipo: ev.extendedProps?.tipo || 'prestamo',
            estado: (ev.extendedProps?.estado || '').toLowerCase(),
            destino: ev.extendedProps?.destino || '',
            responsable: ev.extendedProps?.responsable || '',
            esta_vencido: ev.extendedProps?.esta_vencido || false,
            dias_restantes: ev.extendedProps?.dias_restantes,
            prioridad: (ev.extendedProps?.prioridad || '').toLowerCase(),
            entidad: ev.extendedProps?.entidad || '',
            codigo: ev.extendedProps?.codigo || ev.title
        }));

        // Normalizar start si viene null
        todosLosEventos = todosLosEventos.filter(ev => ev.start);

        agruparEventosPorFecha();
        renderizarTimeline();

    } catch (error) {
        console.error('Error al cargar eventos:', error);
        if (container) {
            container.innerHTML = `
                <div class="timeline-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <h5>Error al cargar los eventos</h5>
                    <p>${escapeHtml(error.message)}</p>
                </div>
            `;
        }
    }
}

// ============================================================
// HELPERS DE FECHA
// ============================================================
function formatearFecha(fecha) {
    if (!fecha) return '---';
    let str = String(fecha).trim();
    if (!str) return '---';
    let dateStr = str.includes('T') ? str : `${str}T00:00:00`;
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return str;
    return d.toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatearFechaLarga(fechaStr) {
    if (!fechaStr) return '---';
    const d = new Date(fechaStr + 'T00:00:00');
    if (isNaN(d.getTime())) return fechaStr;
    return d.toLocaleDateString('es-ES', {
        weekday: 'long', day: '2-digit', month: 'long', year: 'numeric'
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================================
// AGRUPAR EVENTOS POR FECHA
// ============================================================
function agruparEventosPorFecha() {
    eventosPorFecha = {};
    todosLosEventos.forEach(ev => {
        if (!ev.start) return;
        if (!eventosPorFecha[ev.start]) {
            eventosPorFecha[ev.start] = [];
        }
        eventosPorFecha[ev.start].push(ev);
    });
}

// ============================================================
// DETERMINAR CLASE DE EVENTO
// ============================================================
function obtenerClaseEvento(ev) {
    if (ev.tipo === 'solicitud') {
        if (ev.estado === 'aprobada') return 'aprobado';
        if (ev.estado === 'rechazada' || ev.estado === 'cancelada') return 'devuelto';
        return 'solicitud';
    }

    if (ev.esta_vencido) return 'vencido';
    if (ev.estado === 'devuelto') return 'devuelto';
    if (ev.estado === 'extendido') return 'extendido';
    if (ev.estado === 'aprobado') return 'aprobado';
    if (ev.estado === 'entregado') return 'prestamo';
    if (ev.estado === 'pendiente') return 'solicitud';
    if (ev.estado === 'cancelado' || ev.estado === 'rechazado') return 'devuelto';

    return 'prestamo';
}

// ============================================================
// APLICAR FILTRO
// ============================================================
function aplicarFiltro(eventos) {
    if (filtroActual === 'todos') return eventos;
    if (filtroActual === 'prestamos') return eventos.filter(e => e.tipo === 'prestamo');
    if (filtroActual === 'solicitudes') return eventos.filter(e => e.tipo === 'solicitud');
    if (filtroActual === 'vencidos') return eventos.filter(e => e.esta_vencido === true);
    if (filtroActual === 'devueltos') return eventos.filter(e => e.tipo === 'prestamo' && e.estado === 'devuelto');
    return eventos;
}

// ============================================================
// FILTRO DE VISTA
// ============================================================
function filtrarVista(filtro, btn) {
    filtroActual = filtro;

    document.querySelectorAll('.btn-filtro').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    renderizarTimeline();
}

// ============================================================
// TIMELINE
// ============================================================
function renderizarTimeline() {
    const container = document.getElementById('timelineContainer');
    if (!container) return;

    let eventos = aplicarFiltro(todosLosEventos);
    eventos = [...eventos].sort((a, b) => (b.start || '').localeCompare(a.start || ''));

    if (eventos.length === 0) {
        container.innerHTML = `
            <div class="timeline-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <h5>No hay eventos para mostrar</h5>
                <p>Prueba con otro filtro o registra nuevos préstamos</p>
            </div>
        `;
        return;
    }

    const grupos = {};
    eventos.forEach(ev => {
        if (!grupos[ev.start]) grupos[ev.start] = [];
        grupos[ev.start].push(ev);
    });

    const fechas = Object.keys(grupos).sort((a, b) => b.localeCompare(a));

    let html = '';
    fechas.forEach(fecha => {
        const evs = grupos[fecha];
        const fechaLarga = formatearFechaLarga(fecha);

        html += `
            <div class="timeline-item">
                <div class="timeline-dot ${obtenerClaseTimeline(evs)}"></div>
                <div class="timeline-date">
                    ${fechaLarga}
                    <span class="badge bg-secondary ms-2" style="font-size:0.65rem;">${evs.length} evento(s)</span>
                </div>
        `;

        evs.forEach(ev => {
            const clase = obtenerClaseEvento(ev);
            const badgeClass = clase === 'prestamo' ? 'entregado' :
                               clase === 'aprobado' ? 'aprobado' :
                               clase === 'extendido' ? 'extendido' :
                               clase === 'vencido' ? 'vencido' :
                               clase === 'devuelto' ? 'devuelto' : 'pendiente';

            const tipoLabel = ev.tipo === 'prestamo' ? 'Préstamo' : 'Solicitud';
            const estadoLabel = ev.esta_vencido ? 'Vencido' : (ev.estado || 'pendiente');

            html += `
                <div class="timeline-card" onclick="abrirDetalleDesdeTimeline('${ev.id}')">
                    <div class="card-header-custom">
                        <div>
                            <span class="card-title">${escapeHtml(ev.codigo || ev.title)}</span>
                            <span class="card-subtitle ms-2">• ${tipoLabel}</span>
                        </div>
                        <span class="badge-estado ${badgeClass}">${estadoLabel}</span>
                    </div>
                    <div class="card-subtitle mt-1">
                        ${ev.destino ? `📍 ${escapeHtml(ev.destino)}` : ''}
                        ${ev.entidad ? `🏢 ${escapeHtml(ev.entidad)}` : ''}
                        ${ev.responsable ? ` • 👤 ${escapeHtml(ev.responsable)}` : ''}
                    </div>
                </div>
            `;
        });

        html += `</div>`;
    });

    container.innerHTML = html;
}

function obtenerClaseTimeline(evs) {
    if (evs.some(e => e.esta_vencido)) return 'danger';
    if (evs.some(e => obtenerClaseEvento(e) === 'aprobado')) return 'success';
    if (evs.some(e => obtenerClaseEvento(e) === 'extendido')) return 'purple';
    if (evs.some(e => obtenerClaseEvento(e) === 'prestamo')) return '';
    if (evs.some(e => obtenerClaseEvento(e) === 'solicitud')) return 'warning';
    return 'secondary';
}

// ============================================================
// MODAL DETALLE DÍA (por si se necesita desde el timeline)
// ============================================================
function abrirModalDia(fechaKey, eventos) {
    const fechaFormateada = formatearFechaLarga(fechaKey);

    const prestamos = eventos.filter(e => e.tipo === 'prestamo');
    const solicitudes = eventos.filter(e => e.tipo === 'solicitud');

    let html = `
        <div style="margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 2px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h5 style="margin: 0; color: #1e3c72; font-weight: 700; text-transform: capitalize; font-size: 1.1rem;">
                    ${fechaFormateada}
                </h5>
                <small style="color: #64748b;">${eventos.length} evento(s) en total</small>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                ${prestamos.length > 0 ? `<span style="background: #1e3c72; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.72rem; font-weight: 600;">📦 ${prestamos.length} préstamo(s)</span>` : ''}
                ${solicitudes.length > 0 ? `<span style="background: #f59e0b; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.72rem; font-weight: 600;">📋 ${solicitudes.length} solicitud(es)</span>` : ''}
            </div>
        </div>
    `;

    if (prestamos.length > 0) {
        html += `
            <div class="modal-seccion-titulo">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
                Préstamos (${prestamos.length})
            </div>
        `;

        prestamos.forEach(p => {
            const clase = obtenerClaseEvento(p);
            const colorMap = {
                'prestamo': '#1e3c72',
                'aprobado': '#22c55e',
                'extendido': '#8b5cf6',
                'vencido': '#ef4444',
                'devuelto': '#6c757d',
                'solicitud': '#f59e0b'
            };
            const color = colorMap[clase] || '#1e3c72';
            const estadoTexto = p.esta_vencido ? '⚠️ Vencido' : (p.estado || 'pendiente');

            html += `
                <div class="evento-card-modal prestamo" style="border-left-color: ${color};"
                     onclick="cerrarModalDia(); verDetallePrestamo('${String(p.id).replace('prestamo-', '')}')">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div class="evento-titulo">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="2.5">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                            </svg>
                            ${escapeHtml(p.codigo || p.title)}
                        </div>
                        <span class="evento-badge-estado" style="background: ${color}20; color: ${color};">
                            ${estadoTexto}
                        </span>
                    </div>
                    ${p.destino ? `<div class="evento-meta">📍 ${escapeHtml(p.destino)}</div>` : ''}
                    ${p.responsable ? `<div class="evento-meta">👤 ${escapeHtml(p.responsable)}</div>` : ''}
                    ${p.dias_restantes !== undefined && p.dias_restantes !== null ? `<div class="evento-meta" style="color:${p.esta_vencido ? '#ef4444' : '#64748b'};">⏱️ ${p.esta_vencido ? 'Vencido' : p.dias_restantes + ' días restantes'}</div>` : ''}
                </div>
            `;
        });
    }

    if (solicitudes.length > 0) {
        html += `
            <div class="modal-seccion-titulo">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M22 7l-10 7L2 7"/>
                </svg>
                Solicitudes (${solicitudes.length})
            </div>
        `;

        solicitudes.forEach(s => {
            const prioridadColor = s.prioridad === 'urgente' ? '#ef4444' :
                                   s.prioridad === 'alta' ? '#f59e0b' :
                                   s.prioridad === 'normal' ? '#22c55e' : '#6c757d';

            html += `
                <div class="evento-card-modal solicitud" style="border-left-color: ${prioridadColor};"
                     onclick="cerrarModalDia(); verDetalleSolicitud('${String(s.id).replace('solicitud-', '')}')">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div class="evento-titulo" style="color:#b45309;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="M22 7l-10 7L2 7"/>
                            </svg>
                            ${escapeHtml(s.codigo || s.title)}
                        </div>
                        <span class="evento-badge-estado" style="background: ${prioridadColor}20; color: ${prioridadColor};">
                            ${s.prioridad || 'normal'}
                        </span>
                    </div>
                    ${s.entidad ? `<div class="evento-meta">🏢 ${escapeHtml(s.entidad)}</div>` : ''}
                    <div class="evento-meta">Estado: <strong>${escapeHtml(s.estado || 'pendiente')}</strong></div>
                </div>
            `;
        });
    }

    if (eventos.length === 0) {
        html += `
            <div style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 0.75rem;">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4M12 16h.01"/>
                </svg>
                <p style="margin: 0;">No hay eventos para este día</p>
            </div>
        `;
    }

    document.getElementById('modalDiaEventosBody').innerHTML = html;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDiaEventos')).show();
}

function cerrarModalDia() {
    const modalEl = document.getElementById('modalDiaEventos');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
}

// ============================================================
// ABRIR DETALLE DESDE TIMELINE
// ============================================================
function abrirDetalleDesdeTimeline(eventId) {
    if (!eventId) return;

    const idStr = String(eventId);

    if (idStr.startsWith('prestamo-')) {
        verDetallePrestamo(idStr.replace('prestamo-', ''));
        return;
    }

    if (idStr.startsWith('solicitud-')) {
        verDetalleSolicitud(idStr.replace('solicitud-', ''));
        return;
    }

    // Fallback: buscar el evento y deducir su tipo
    const ev = todosLosEventos.find(e => String(e.id) === idStr);
    if (ev) {
        if (ev.tipo === 'prestamo') {
            verDetallePrestamo(idStr.replace('prestamo-', ''));
        } else {
            verDetalleSolicitud(idStr.replace('solicitud-', ''));
        }
    }
}

// ============================================================
// DETALLE PRÉSTAMO
// ============================================================
function verDetallePrestamo(id) {
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetallePrestamo'));

    document.getElementById('modalDetallePrestamoTitle').innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline;margin-right:6px;">
            <rect x="2" y="3" width="20" height="14" rx="2"/>
            <line x1="8" y1="21" x2="16" y2="21"/>
            <line x1="12" y1="17" x2="12" y2="21"/>
        </svg>
        Detalle del Préstamo
    `;
    document.getElementById('modalDetallePrestamoBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-muted">Cargando detalles...</p>
        </div>
    `;
    modal.show();

    fetch('/admin/prestamos/' + id, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            document.getElementById('modalDetallePrestamoBody').innerHTML = `
                <div class="text-center text-danger py-5">
                    <p>${data.message || 'Error al cargar detalles'}</p>
                </div>
            `;
            return;
        }

        const d = data.data;
        const fechaPrestamo = formatearFecha(d.fecha_prestamo);
        const fechaDevolucionEsperada = formatearFecha(d.fecha_devolucion_esperada);
        const fechaDevolucionReal = d.fecha_devolucion_real ? formatearFecha(d.fecha_devolucion_real) : null;

        let html = `
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; padding:1rem; background:#f8fafc; border-radius:12px; border:1px solid #e9ecef; margin-bottom:1rem;">
                <div>
                    <div style="font-weight:700; color:#1e3c72; font-size:1.1rem;">${escapeHtml(d.codigo)}</div>
                    <div style="font-size:0.8rem; color:#64748b;">${escapeHtml(d.tipo_prestamo || '')}</div>
                </div>
                <span style="background: ${d.esta_vencido ? '#fee2e2' : '#dbeafe'}; color: ${d.esta_vencido ? '#991b1b' : '#1e40af'}; padding:0.35rem 0.9rem; border-radius:20px; font-size:0.75rem; font-weight:700; text-transform:uppercase;">
                    ${d.esta_vencido ? '⚠️ Vencido' : escapeHtml(d.estado)}
                </span>
            </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:0.75rem;">
                <div style="padding:0.75rem 1rem; background:white; border-radius:10px; border:1px solid #e9ecef;">
                    <div style="font-size:0.65rem; text-transform:uppercase; color:#94a3b8; font-weight:700; letter-spacing:0.5px; margin-bottom:4px;">Responsable Receptor</div>
                    <div style="font-weight:600; color:#0f172a;">${escapeHtml(d.responsable_receptor?.nombre || '---')}</div>
                </div>
                <div style="padding:0.75rem 1rem; background:white; border-radius:10px; border:1px solid #e9ecef;">
                    <div style="font-size:0.65rem; text-transform:uppercase; color:#94a3b8; font-weight:700; letter-spacing:0.5px; margin-bottom:4px;">Destino</div>
                    <div style="font-weight:600; color:#0f172a;">${escapeHtml(d.destino_nombre || '---')}</div>
                </div>
                <div style="padding:0.75rem 1rem; background:white; border-radius:10px; border:1px solid #e9ecef;">
                    <div style="font-size:0.65rem; text-transform:uppercase; color:#94a3b8; font-weight:700; letter-spacing:0.5px; margin-bottom:4px;">Fecha Préstamo</div>
                    <div style="font-weight:600; color:#0f172a;">${fechaPrestamo}</div>
                </div>
                <div style="padding:0.75rem 1rem; background:white; border-radius:10px; border:1px solid #e9ecef;">
                    <div style="font-size:0.65rem; text-transform:uppercase; color:#94a3b8; font-weight:700; letter-spacing:0.5px; margin-bottom:4px;">${fechaDevolucionReal ? 'Fecha Devolución Real' : 'Fecha Devolución Esperada'}</div>
                    <div style="font-weight:600; color:#0f172a;">${fechaDevolucionReal || fechaDevolucionEsperada}</div>
                </div>
            </div>
        `;

        if (d.observaciones) {
            html += `
                <div style="margin-top:1rem; padding:0.85rem 1rem; background:#fffbf0; border-radius:10px; border-left:4px solid #f59e0b;">
                    <div style="font-size:0.7rem; text-transform:uppercase; color:#94a3b8; font-weight:700; margin-bottom:4px;">Observaciones</div>
                    <div style="color:#334155;">${escapeHtml(d.observaciones)}</div>
                </div>
            `;
        }

        document.getElementById('modalDetallePrestamoBody').innerHTML = html;
        document.getElementById('modalDetallePrestamoActionBtn').href = '{{ route("admin.prestamos.index") }}?search=' + encodeURIComponent(d.codigo);
    })
    .catch(err => {
        console.error(err);
        document.getElementById('modalDetallePrestamoBody').innerHTML = `
            <div class="text-center text-danger py-5">
                <p>Error al cargar los detalles</p>
            </div>
        `;
    });
}

// ============================================================
// DETALLE SOLICITUD
// ============================================================
function verDetalleSolicitud(id) {
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetallePrestamo'));

    document.getElementById('modalDetallePrestamoTitle').innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline;margin-right:6px;">
            <rect x="2" y="4" width="20" height="16" rx="2"/>
            <path d="M22 7l-10 7L2 7"/>
        </svg>
        Detalle de la Solicitud
    `;
    document.getElementById('modalDetallePrestamoBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-muted">Cargando detalles...</p>
        </div>
    `;
    modal.show();

    fetch('/admin/solicitudes/' + id + '/detalles', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const d = data.solicitud || data;

        if (!d || (!d.id && !data.success)) {
            document.getElementById('modalDetallePrestamoBody').innerHTML = `
                <div class="text-center text-danger py-5">
                    <p>${data.message || data.error || 'Error al cargar detalles'}</p>
                </div>
            `;
            return;
        }

        const fechaSolicitud = formatearFecha(d.fecha_solicitud);
        const fechaRequerida = formatearFecha(d.fecha_requerida);

        let nombreEntidad = 'No especificado';
        if (d.tipo_solicitante === 'interno' && d.departamento) nombreEntidad = d.departamento.nombre;
        else if (d.tipo_solicitante === 'externo' && d.institucion) nombreEntidad = d.institucion.nombre;

        const prioridadColor = d.prioridad === 'urgente' ? '#ef4444' :
                              d.prioridad === 'alta' ? '#f59e0b' :
                              d.prioridad === 'normal' ? '#22c55e' : '#6c757d';

        let html = `
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; padding:1rem; background:#f8fafc; border-radius:12px; border:1px solid #e9ecef; margin-bottom:1rem;">
                <div>
                    <div style="font-weight:700; color:#1e3c72; font-size:1.1rem;">Solicitud #${d.id}</div>
                    <div style="font-size:0.8rem; color:#64748b;">${escapeHtml(d.estado_solicitud || 'pendiente')}</div>
                </div>
                <span style="background: ${prioridadColor}20; color: ${prioridadColor}; padding:0.35rem 0.9rem; border-radius:20px; font-size:0.75rem; font-weight:700; text-transform:uppercase;">
                    ${escapeHtml(d.prioridad || 'normal')}
                </span>
            </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:0.75rem;">
                <div style="padding:0.75rem 1rem; background:white; border-radius:10px; border:1px solid #e9ecef;">
                    <div style="font-size:0.65rem; text-transform:uppercase; color:#94a3b8; font-weight:700; letter-spacing:0.5px; margin-bottom:4px;">Entidad</div>
                    <div style="font-weight:600; color:#0f172a;">${escapeHtml(nombreEntidad)}</div>
                </div>
                <div style="padding:0.75rem 1rem; background:white; border-radius:10px; border:1px solid #e9ecef;">
                    <div style="font-size:0.65rem; text-transform:uppercase; color:#94a3b8; font-weight:700; letter-spacing:0.5px; margin-bottom:4px;">Responsable</div>
                    <div style="font-weight:600; color:#0f172a;">${escapeHtml(d.responsable?.nombre || '---')}</div>
                </div>
                <div style="padding:0.75rem 1rem; background:white; border-radius:10px; border:1px solid #e9ecef;">
                    <div style="font-size:0.65rem; text-transform:uppercase; color:#94a3b8; font-weight:700; letter-spacing:0.5px; margin-bottom:4px;">Fecha Solicitud</div>
                    <div style="font-weight:600; color:#0f172a;">${fechaSolicitud}</div>
                </div>
                <div style="padding:0.75rem 1rem; background:white; border-radius:10px; border:1px solid #e9ecef;">
                    <div style="font-size:0.65rem; text-transform:uppercase; color:#94a3b8; font-weight:700; letter-spacing:0.5px; margin-bottom:4px;">Fecha Requerida</div>
                    <div style="font-weight:600; color:#0f172a;">${fechaRequerida}</div>
                </div>
            </div>
        `;

        if (d.justificacion) {
            html += `
                <div style="margin-top:1rem; padding:0.85rem 1rem; background:#fffbf0; border-radius:10px; border-left:4px solid #f59e0b;">
                    <div style="font-size:0.7rem; text-transform:uppercase; color:#94a3b8; font-weight:700; margin-bottom:4px;">Justificación</div>
                    <div style="color:#334155;">${escapeHtml(d.justificacion)}</div>
                </div>
            `;
        }

        document.getElementById('modalDetallePrestamoBody').innerHTML = html;
        document.getElementById('modalDetallePrestamoActionBtn').href = '{{ route("admin.solicitudes.index") }}';
    })
    .catch(err => {
        console.error(err);
        document.getElementById('modalDetallePrestamoBody').innerHTML = `
            <div class="text-center text-danger py-5">
                <p>Error al cargar los detalles</p>
            </div>
        `;
    });
}

// ============================================================
// EXPONER FUNCIONES GLOBALES
// ============================================================
window.filtrarVista = filtrarVista;
window.abrirDetalleDesdeTimeline = abrirDetalleDesdeTimeline;
window.verDetallePrestamo = verDetallePrestamo;
window.verDetalleSolicitud = verDetalleSolicitud;
window.cerrarModalDia = cerrarModalDia;
window.abrirModalDia = abrirModalDia;
</script>
@endsection