@extends('layouts.dashboard')

@section('title', 'Calendario de Actividades')

@section('styles')
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    
    <style>
        /* ========== VARIABLES ========== */
        :root {
            --primary-color: #1e3c72;
            --primary-light: #2a5298;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
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
        }

        .calendario-header .btn-outline-light:hover {
            background: white;
            color: #1e3c72;
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
            cursor: default;
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
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .stat-icon.blue { background: rgba(30, 60, 114, 0.1); }
        .stat-icon.green { background: rgba(34, 197, 94, 0.1); }
        .stat-icon.red { background: rgba(239, 68, 68, 0.1); }
        .stat-icon.yellow { background: rgba(245, 158, 11, 0.1); }

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

        .stat-trend {
            font-size: 0.65rem;
            padding: 2px 8px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 2px;
        }

        .stat-trend.up { background: #dcfce7; color: #16a34a; }
        .stat-trend.down { background: #fee2e2; color: #dc2626; }
        .stat-trend.warning { background: #fef3c7; color: #d97706; }

        /* ========== VISTA TOGGLE ========== */
        .view-toggle {
            display: flex;
            gap: 0.5rem;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 0.25rem;
        }

        .view-toggle .btn {
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.2rem;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            background: transparent;
            color: #64748b;
        }

        .view-toggle .btn:hover {
            color: #0f172a;
        }

        .view-toggle .btn.active {
            background: white;
            color: #1e3c72;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .view-toggle .btn svg {
            width: 16px;
            height: 16px;
            margin-right: 6px;
            vertical-align: middle;
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

        /* ========== CALENDARIO ========== */
        .calendar-container {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid #e9ecef;
        }

        .fc .fc-toolbar-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e3c72;
        }

        .fc .fc-button-primary {
            background-color: #1e3c72 !important;
            border-color: #1e3c72 !important;
        }

        .fc .fc-button-primary:hover {
            background-color: #14305a !important;
            border-color: #14305a !important;
        }

        .fc .fc-button-primary:not(:disabled):active,
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #0f2444 !important;
            border-color: #0f2444 !important;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 0.7rem;
            transition: transform 0.2s;
            margin-bottom: 1px;
        }

        .fc-event:hover {
            transform: scale(1.02);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .fc-daygrid-day-number {
            color: #1e3c72;
            font-weight: 500;
        }

        .fc-day-today {
            background-color: rgba(30, 60, 114, 0.05) !important;
        }

        .fc-col-header-cell {
            background-color: #f8fafc;
            color: #1e3c72;
            font-weight: 600;
            padding: 0.5rem 0;
        }

        .fc-daygrid-day-frame {
            min-height: 70px;
        }

        .fc-daygrid-day-events {
            min-height: 20px;
        }

        .fc .fc-more-popover {
            z-index: 9999 !important;
        }

        /* ========== LEGENDA ========== */
        .calendar-legend {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .calendar-legend .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: #64748b;
        }

        .calendar-legend .legend-color {
            width: 14px;
            height: 14px;
            border-radius: 4px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* ========== TIMELINE ========== */
        #timelineView {
            display: none;
        }

        .timeline-container {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid #e9ecef;
            max-height: 600px;
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

        .timeline-item .timeline-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
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
        .badge-estado.extendido { background: #fef3c7; color: #d97706; }
        .badge-estado.devuelto { background: #e0e7ff; color: #4f46e5; }
        .badge-estado.pendiente { background: #f1f5f9; color: #64748b; }
        .badge-estado.vencido { background: #fee2e2; color: #dc2626; }

        /* ========== KANBAN ========== */
        #kanbanView {
            display: none;
        }

        .kanban-container {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid #e9ecef;
            overflow-x: auto;
        }

        .kanban-board {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            min-width: 800px;
        }

        .kanban-column {
            background: #f8fafc;
            border-radius: 12px;
            padding: 0.75rem;
            min-height: 300px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .kanban-column:hover {
            border-color: #cbd5e1;
        }

        .kanban-column-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 0.75rem;
        }

        .kanban-column-header .column-title {
            font-weight: 600;
            font-size: 0.85rem;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .kanban-column-header .column-count {
            background: #e9ecef;
            padding: 0.15rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
        }

        .kanban-column.pendiente .column-title { color: #64748b; }
        .kanban-column.aprobado .column-title { color: #2563eb; }
        .kanban-column.entregado .column-title { color: #16a34a; }
        .kanban-column.extendido .column-title { color: #d97706; }
        .kanban-column.devuelto .column-title { color: #4f46e5; }

        .kanban-column.pendiente { border-top: 3px solid #64748b; }
        .kanban-column.aprobado { border-top: 3px solid #2563eb; }
        .kanban-column.entregado { border-top: 3px solid #16a34a; }
        .kanban-column.extendido { border-top: 3px solid #d97706; }
        .kanban-column.devuelto { border-top: 3px solid #4f46e5; }

        .kanban-card {
            background: white;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border: 1px solid #e9ecef;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .kanban-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px) scale(1.01);
            border-color: #1e3c72;
        }

        .kanban-card:active {
            transform: scale(0.98);
        }

        .kanban-card .card-title {
            font-weight: 600;
            font-size: 0.85rem;
            color: #0f172a;
        }

        .kanban-card .card-subtitle {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .kanban-card .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #f1f5f9;
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .kanban-card .card-badge {
            font-size: 0.6rem;
            padding: 1px 8px;
            border-radius: 20px;
            font-weight: 600;
        }

        .kanban-card .card-badge.vencido { background: #fee2e2; color: #dc2626; }
        .kanban-card .card-badge.normal { background: #dbeafe; color: #2563eb; }
        .kanban-card .card-badge.completado { background: #dcfce7; color: #16a34a; }

        .kanban-empty {
            text-align: center;
            padding: 2rem 0.5rem;
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .kanban-empty svg {
            width: 32px;
            height: 32px;
            stroke: #cbd5e1;
            margin-bottom: 0.5rem;
        }

        /* ========== MODAL DETALLE ========== */
        .modal-detalle-prestamo .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .modal-detalle-prestamo .detail-item {
            padding: 0.5rem 0;
        }

        .modal-detalle-prestamo .detail-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .modal-detalle-prestamo .detail-value {
            font-weight: 500;
            color: #1a1a1a;
            font-size: 0.9rem;
        }

        .modal-detalle-prestamo .detail-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }

        .modal-detalle-prestamo .detail-section-title {
            font-weight: 600;
            color: #1e3c72;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .modal-detalle-prestamo .item-list {
            background: #f8fafc;
            border-radius: 8px;
            padding: 0.75rem;
        }

        .modal-detalle-prestamo .item-row {
            display: flex;
            justify-content: space-between;
            padding: 0.3rem 0;
            font-size: 0.85rem;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-detalle-prestamo .item-row:last-child {
            border-bottom: none;
        }

        /* ========== RESPONSIVE KANBAN ========== */
        @media (max-width: 1200px) {
            .kanban-board {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .kanban-board {
                grid-template-columns: 1fr;
                min-width: unset;
            }

            .modal-detalle-prestamo .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ========== RESPONSIVE GENERAL ========== */
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

            .view-toggle .btn {
                padding: 0.3rem 0.8rem;
                font-size: 0.75rem;
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

            .timeline-item .timeline-card .card-header {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 576px) {
            .calendario-header {
                padding: 1rem;
            }

            .calendar-container,
            .timeline-container,
            .kanban-container {
                padding: 0.75rem;
            }
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

        .timeline-item {
            animation: slideIn 0.3s ease forwards;
        }

        .timeline-item:nth-child(1) { animation-delay: 0.05s; }
        .timeline-item:nth-child(2) { animation-delay: 0.1s; }
        .timeline-item:nth-child(3) { animation-delay: 0.15s; }
        .timeline-item:nth-child(4) { animation-delay: 0.2s; }
        .timeline-item:nth-child(5) { animation-delay: 0.25s; }
        .timeline-item:nth-child(6) { animation-delay: 0.3s; }
        .timeline-item:nth-child(7) { animation-delay: 0.35s; }
        .timeline-item:nth-child(8) { animation-delay: 0.4s; }
        .timeline-item:nth-child(9) { animation-delay: 0.45s; }
        .timeline-item:nth-child(10) { animation-delay: 0.5s; }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .kanban-card {
            animation: cardAppear 0.3s ease forwards;
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
            <p>Visualiza y gestiona todos los préstamos y solicitudes</p>
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
            <div class="stat-icon blue">📊</div>
            <div class="stat-info">
                <div class="stat-number">{{ $totalPrestamos ?? 0 }}</div>
                <div class="stat-label">Total Préstamos</div>
                <span class="stat-trend up">↑ Activo</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">✅</div>
            <div class="stat-info">
                <div class="stat-number">{{ $prestamosActivos ?? 0 }}</div>
                <div class="stat-label">Préstamos Activos</div>
                <span class="stat-trend up">↑ En curso</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">🚨</div>
            <div class="stat-info">
                <div class="stat-number" style="color: {{ ($prestamosVencidos ?? 0) > 0 ? '#ef4444' : '#0f172a' }};">
                    {{ $prestamosVencidos ?? 0 }}
                </div>
                <div class="stat-label">Préstamos Vencidos</div>
                <span class="stat-trend {{ ($prestamosVencidos ?? 0) > 0 ? 'down' : 'up' }}">
                    {{ ($prestamosVencidos ?? 0) > 0 ? 'Requieren atención' : 'Todo en orden' }}
                </span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">⏳</div>
            <div class="stat-info">
                <div class="stat-number">{{ $solicitudesPendientes ?? 0 }}</div>
                <div class="stat-label">Solicitudes Pendientes</div>
                <span class="stat-trend warning">Por revisar</span>
            </div>
        </div>
    </div>

    <!-- ========== CONTROLES ========== -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="filtros-rapidos">
            <button class="btn-filtro active" data-filtro="todos" onclick="filtrarTimeline('todos', this)">Todos</button>
            <button class="btn-filtro" data-filtro="activos" onclick="filtrarTimeline('activos', this)">Activos</button>
            <button class="btn-filtro danger" data-filtro="vencidos" onclick="filtrarTimeline('vencidos', this)">Vencidos</button>
            <button class="btn-filtro success" data-filtro="devueltos" onclick="filtrarTimeline('devueltos', this)">Devueltos</button>
            <button class="btn-filtro" data-filtro="proximos" onclick="filtrarTimeline('proximos', this)">Próximos</button>
        </div>
        <div class="view-toggle">
            <button class="btn active" id="viewCalendarBtn" onclick="cambiarVista('calendar')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Calendario
            </button>
            <button class="btn" id="viewTimelineBtn" onclick="cambiarVista('timeline')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="4" y1="6" x2="20" y2="6"/>
                    <line x1="4" y1="12" x2="20" y2="12"/>
                    <line x1="4" y1="18" x2="20" y2="18"/>
                    <circle cx="6" cy="6" r="2"/>
                    <circle cx="12" cy="12" r="2"/>
                    <circle cx="18" cy="18" r="2"/>
                </svg>
                Timeline
            </button>
            <button class="btn" id="viewKanbanBtn" onclick="cambiarVista('kanban')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="3" y1="9" x2="21" y2="9"/>
                    <line x1="9" y1="21" x2="9" y2="9"/>
                </svg>
                Kanban
            </button>
        </div>
    </div>

    <!-- ========== VISTA CALENDARIO ========== -->
    <div id="calendarView">
        <div class="calendar-container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div class="calendar-legend">
                    <span class="legend-item"><span class="legend-color" style="background: #22c55e;"></span> Entregado</span>
                    <span class="legend-item"><span class="legend-color" style="background: #1e3c72;"></span> Aprobado</span>
                    <span class="legend-item"><span class="legend-color" style="background: #f59e0b;"></span> Extendido</span>
                    <span class="legend-item"><span class="legend-color" style="background: #ef4444;"></span> Vencido</span>
                    <span class="legend-item"><span class="legend-color" style="background: #6c757d;"></span> Solicitud</span>
                </div>
                <div>
                    <span class="text-muted small">Haz clic en un evento para ver detalles</span>
                </div>
            </div>
            <div id="calendar"></div>
        </div>
    </div>

    <!-- ========== VISTA TIMELINE ========== -->
    <div id="timelineView">
        <div class="timeline-container" id="timelineContainer">
            @forelse($eventosPorFecha as $fecha => $grupo)
            <div class="timeline-item" data-estado="{{ $grupo['eventos']->pluck('estado')->implode(',') }}">
                <div class="timeline-dot 
                    @if($grupo['eventos']->contains('esta_vencido', true)) danger
                    @elseif($grupo['eventos']->contains('estado', 'entregado')) success
                    @elseif($grupo['eventos']->contains('estado', 'extendido')) warning
                    @elseif($grupo['eventos']->contains('estado', 'devuelto')) info
                    @else secondary
                    @endif
                "></div>
                <div class="timeline-date">
                    {{ $grupo['fecha']->format('l, d \\d\\e F \\d\\e Y') }}
                    <span class="badge bg-secondary ms-2">{{ $grupo['eventos']->count() }} eventos</span>
                </div>
                @foreach($grupo['eventos'] as $evento)
                <div class="timeline-card" onclick="verDetallePrestamo({{ $evento->id }})">
                    <div class="card-header">
                        <div>
                            <span class="card-title">{{ $evento->codigo }}</span>
                            <span class="card-subtitle ms-2">• {{ $evento->destino_nombre }}</span>
                        </div>
                        <span class="badge-estado {{ $evento->estado }}">
                            {{ $evento->estado }}
                            @if($evento->esta_vencido)
                            <span class="ms-1">⚠️</span>
                            @endif
                        </span>
                    </div>
                    <div class="card-subtitle">
                        📅 {{ $evento->fecha_prestamo->format('d/m/Y') }} 
                        → {{ $evento->fecha_devolucion_esperada->format('d/m/Y') }}
                        @if(!$evento->fecha_devolucion_real)
                            • <span class="text-muted">{{ $evento->dias_restantes }} días restantes</span>
                        @else
                            • <span class="text-success">Devuelto el {{ $evento->fecha_devolucion_real->format('d/m/Y') }}</span>
                        @endif
                    </div>
                    <div class="card-footer">
                        <span>👤 {{ $evento->responsableReceptor?->nombre ?? 'N/A' }}</span>
                        <span>📦 {{ $evento->detalles->count() }} items</span>
                    </div>
                    @if(!$evento->fecha_devolucion_real)
                    <div class="progress-bar">
                        @php
                            $total = max(1, $evento->fecha_prestamo->diffInDays($evento->fecha_devolucion_esperada));
                            $transcurridos = $evento->fecha_prestamo->diffInDays(now());
                            $porcentaje = min(100, ($transcurridos / $total) * 100);
                            $clase = $evento->esta_vencido ? 'danger' : ($porcentaje > 80 ? 'warning' : 'success');
                        @endphp
                        <div class="progress-fill {{ $clase }}" style="width: {{ $porcentaje }}%;"></div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @empty
            <div class="timeline-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <h5>No hay eventos para mostrar</h5>
                <p>Comienza a registrar préstamos para verlos aquí</p>
                <a href="{{ route('admin.prestamos.index') }}" class="btn btn-primary-dark mt-2">
                    + Crear Préstamo
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- ========== VISTA KANBAN ========== -->
    <div id="kanbanView">
        <div class="kanban-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0" style="color: #1e3c72;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="3" y1="9" x2="21" y2="9"/>
                        <line x1="9" y1="21" x2="9" y2="9"/>
                    </svg>
                    Tablero Kanban
                </h5>
                <span class="text-muted small">Haz clic en una tarjeta para ver los detalles del préstamo</span>
            </div>
            <div class="kanban-board">
                <!-- Columna: Pendiente -->
                <div class="kanban-column pendiente">
                    <div class="kanban-column-header">
                        <span class="column-title">Pendiente</span>
                        <span class="column-count">{{ $kanbanData['pendiente']->count() }}</span>
                    </div>
                    <div class="kanban-cards">
                        @forelse($kanbanData['pendiente'] as $item)
                        <div class="kanban-card" onclick="verDetallePrestamo({{ $item->id }})">
                            <div class="card-title">{{ $item->codigo }}</div>
                            <div class="card-subtitle">{{ $item->destino_nombre }}</div>
                            <div class="card-footer">
                                <span>Responsable: {{ $item->responsableReceptor?->nombre ?? 'N/A' }}</span>
                                <span class="card-badge normal">{{ $item->fecha_prestamo->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="kanban-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <p>Sin préstamos</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Columna: Aprobado -->
                <div class="kanban-column aprobado">
                    <div class="kanban-column-header">
                        <span class="column-title">Aprobado</span>
                        <span class="column-count">{{ $kanbanData['aprobado']->count() }}</span>
                    </div>
                    <div class="kanban-cards">
                        @forelse($kanbanData['aprobado'] as $item)
                        <div class="kanban-card" onclick="verDetallePrestamo({{ $item->id }})">
                            <div class="card-title">{{ $item->codigo }}</div>
                            <div class="card-subtitle">{{ $item->destino_nombre }}</div>
                            <div class="card-footer">
                                <span>Responsable: {{ $item->responsableReceptor?->nombre ?? 'N/A' }}</span>
                                <span class="card-badge normal">{{ $item->fecha_prestamo->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="kanban-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <p>Sin préstamos</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Columna: Entregado -->
                <div class="kanban-column entregado">
                    <div class="kanban-column-header">
                        <span class="column-title">Entregado</span>
                        <span class="column-count">{{ $kanbanData['entregado']->count() }}</span>
                    </div>
                    <div class="kanban-cards">
                        @forelse($kanbanData['entregado'] as $item)
                        <div class="kanban-card" onclick="verDetallePrestamo({{ $item->id }})">
                            <div class="card-title">{{ $item->codigo }}</div>
                            <div class="card-subtitle">{{ $item->destino_nombre }}</div>
                            <div class="card-footer">
                                <span>Responsable: {{ $item->responsableReceptor?->nombre ?? 'N/A' }}</span>
                                <span class="card-badge {{ $item->esta_vencido ? 'vencido' : 'normal' }}">
                                    {{ $item->esta_vencido ? 'Vencido' : $item->dias_restantes.' días' }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="kanban-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <p>Sin préstamos</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Columna: Extendido -->
                <div class="kanban-column extendido">
                    <div class="kanban-column-header">
                        <span class="column-title">Extendido</span>
                        <span class="column-count">{{ $kanbanData['extendido']->count() }}</span>
                    </div>
                    <div class="kanban-cards">
                        @forelse($kanbanData['extendido'] as $item)
                        <div class="kanban-card" onclick="verDetallePrestamo({{ $item->id }})">
                            <div class="card-title">{{ $item->codigo }}</div>
                            <div class="card-subtitle">{{ $item->destino_nombre }}</div>
                            <div class="card-footer">
                                <span>Responsable: {{ $item->responsableReceptor?->nombre ?? 'N/A' }}</span>
                                <span class="card-badge {{ $item->esta_vencido ? 'vencido' : 'normal' }}">
                                    {{ $item->esta_vencido ? 'Vencido' : $item->dias_restantes.' días' }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="kanban-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <p>Sin préstamos</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Columna: Devuelto -->
                <div class="kanban-column devuelto">
                    <div class="kanban-column-header">
                        <span class="column-title">Devuelto</span>
                        <span class="column-count">{{ $kanbanData['devuelto']->count() }}</span>
                    </div>
                    <div class="kanban-cards">
                        @forelse($kanbanData['devuelto'] as $item)
                        <div class="kanban-card" onclick="verDetallePrestamo({{ $item->id }})">
                            <div class="card-title">{{ $item->codigo }}</div>
                            <div class="card-subtitle">{{ $item->destino_nombre }}</div>
                            <div class="card-footer">
                                <span>Responsable: {{ $item->responsableReceptor?->nombre ?? 'N/A' }}</span>
                                <span class="card-badge completado">Completado</span>
                            </div>
                        </div>
                        @empty
                        <div class="kanban-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <p>Sin préstamos</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL DETALLE PRÉSTAMO ========== -->
<div class="modal fade modal-detalle-prestamo" id="modalDetallePrestamo" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark text-white">
                <h5 class="modal-title" id="modalDetallePrestamoTitle">Detalle del Préstamo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetallePrestamoBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando detalles...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="modalDetallePrestamoActionBtn" class="btn btn-primary-dark" target="_blank">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    Ver en Préstamos
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>

<script>
    let calendar = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar calendario
        var calendarEl = document.getElementById('calendar');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana',
                day: 'Día'
            },
            initialView: 'dayGridMonth',
            height: 'auto',
            events: '{{ route("calendario.eventos") }}',
            eventTimeFormat: { hour: '2-digit', minute: '2-digit' },
            dayMaxEvents: 5,
            moreLinkText: function(num) {
                return '+ ver ' + num + ' más';
            },
            eventDidMount: function(info) {
                var tooltip = new bootstrap.Tooltip(info.el, {
                    title: function() {
                        var props = info.event.extendedProps;
                        var html = '<div style="padding: 8px; min-width: 150px;">';
                        html += '<strong>' + info.event.title + '</strong><br>';
                        html += 'Estado: ' + (props.estado || '—') + '<br>';
                        if (props.destino) {
                            html += 'Destino: ' + props.destino + '<br>';
                        }
                        if (props.dias_restantes !== undefined) {
                            if (props.esta_vencido) {
                                html += '<span class="text-danger">Vencido</span>';
                            } else {
                                html += props.dias_restantes + ' días restantes';
                            }
                        }
                        html += '</div>';
                        return html;
                    },
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body',
                    html: true
                });
            },
            eventClick: function(info) {
                var props = info.event.extendedProps;
                if (props.tipo === 'prestamo') {
                    var id = info.event.id.replace('prestamo-', '');
                    verDetallePrestamo(id);
                } else if (props.tipo === 'solicitud') {
                    var id = info.event.id.replace('solicitud-', '');
                    verDetalleSolicitud(id);
                }
            }
        });
        
        calendar.render();
    });

    // ========== VER DETALLE PRÉSTAMO ==========
    function verDetallePrestamo(id) {
        var modal = new bootstrap.Modal(document.getElementById('modalDetallePrestamo'));
        document.getElementById('modalDetallePrestamoTitle').textContent = 'Detalle del Préstamo';
        document.getElementById('modalDetallePrestamoBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando detalles...</p></div>';
        modal.show();

        fetch('/admin/prestamos/' + id, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                var d = data.data;
                var html = '';

                // Estado badge
                var estadoBadge = '';
                var estadoClass = '';
                if (d.estado === 'entregado') { estadoBadge = 'Entregado'; estadoClass = 'success'; }
                else if (d.estado === 'aprobado') { estadoBadge = 'Aprobado'; estadoClass = 'primary'; }
                else if (d.estado === 'extendido') { estadoBadge = 'Extendido'; estadoClass = 'warning'; }
                else if (d.estado === 'devuelto') { estadoBadge = 'Devuelto'; estadoClass = 'info'; }
                else if (d.estado === 'pendiente') { estadoBadge = 'Pendiente'; estadoClass = 'secondary'; }
                else if (d.estado === 'vencido') { estadoBadge = 'Vencido'; estadoClass = 'danger'; }
                else { estadoBadge = d.estado; estadoClass = 'secondary'; }

                // Formatear fechas si existen
                var fechaPrestamo = d.fecha_prestamo || '—';
                var fechaDevolucionEsperada = d.fecha_devolucion_esperada || '—';
                var fechaDevolucionReal = d.fecha_devolucion_real || null;

                html = `
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="fw-bold" style="color: #1e3c72;">${escapeHtml(d.codigo)}</h6>
                            <span class="badge bg-${estadoClass}">${estadoBadge}</span>
                            ${d.esta_vencido ? '<span class="badge bg-danger ms-1">Vencido</span>' : ''}
                        </div>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Destino</div>
                            <div class="detail-value">${escapeHtml(d.destino_nombre || '—')}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Tipo</div>
                            <div class="detail-value">${escapeHtml(d.tipo_prestamo || '—')}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Fecha Préstamo</div>
                            <div class="detail-value">${escapeHtml(fechaPrestamo)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Fecha Devolución Esperada</div>
                            <div class="detail-value">${escapeHtml(fechaDevolucionEsperada)}</div>
                        </div>
                        ${fechaDevolucionReal ? `
                        <div class="detail-item">
                            <div class="detail-label">Fecha Devolución Real</div>
                            <div class="detail-value">${escapeHtml(fechaDevolucionReal)}</div>
                        </div>
                        ` : `
                        <div class="detail-item">
                            <div class="detail-label">Días Restantes</div>
                            <div class="detail-value ${d.dias_restantes !== undefined && d.dias_restantes < 3 ? 'text-danger' : ''}">${d.dias_restantes !== undefined ? d.dias_restantes + ' días' : '—'}</div>
                        </div>
                        `}
                        <div class="detail-item">
                            <div class="detail-label">Responsable Receptor</div>
                            <div class="detail-value">${escapeHtml(d.responsable_receptor?.nombre || '—')}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Responsable Emisor</div>
                            <div class="detail-value">${escapeHtml(d.responsable_emisor?.nombre || '—')}</div>
                        </div>
                    </div>
                `;

                if (d.observaciones) {
                    html += `
                        <div class="detail-section">
                            <div class="detail-section-title">Observaciones</div>
                            <p class="mb-0">${escapeHtml(d.observaciones)}</p>
                        </div>
                    `;
                }

                if (d.detalles && d.detalles.length > 0) {
                    html += `
                        <div class="detail-section">
                            <div class="detail-section-title">Items del Préstamo (${d.detalles.length})</div>
                            <div class="item-list">
                                ${d.detalles.map(function(item) {
                                    var nombreItem = item.nombre_item || item.prestable?.serial || 'Item #' + item.id;
                                    return `<div class="item-row">
                                        <span>${escapeHtml(nombreItem)}</span>
                                        <span>Cantidad: ${item.cantidad || 1} | Estado: ${escapeHtml(item.estado_entrega || '—')}</span>
                                    </div>`;
                                }).join('')}
                            </div>
                        </div>
                    `;
                }

                document.getElementById('modalDetallePrestamoBody').innerHTML = html;
                document.getElementById('modalDetallePrestamoActionBtn').href = '{{ route("admin.prestamos.index") }}?search=' + encodeURIComponent(d.codigo);
            } else {
                document.getElementById('modalDetallePrestamoBody').innerHTML = '<div class="text-center text-danger py-4">' + (data.message || 'Error al cargar detalles') + '</div>';
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            document.getElementById('modalDetallePrestamoBody').innerHTML = '<div class="text-center text-danger py-4">Error al cargar los detalles</div>';
        });
    }

    // ========== VER DETALLE SOLICITUD ==========
    function verDetalleSolicitud(id) {
        var modal = new bootstrap.Modal(document.getElementById('modalDetallePrestamo'));
        document.getElementById('modalDetallePrestamoTitle').textContent = 'Detalle de la Solicitud';
        document.getElementById('modalDetallePrestamoBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando detalles...</p></div>';
        modal.show();

        fetch('/admin/solicitudes/' + id + '/detalles', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success || data.id) {
                var d = data;
                var html = '';

                var estadoBadge = '';
                var estadoClass = '';
                if (d.estado_solicitud === 'pendiente') { estadoBadge = 'Pendiente'; estadoClass = 'warning'; }
                else if (d.estado_solicitud === 'aprobada') { estadoBadge = 'Aprobada'; estadoClass = 'success'; }
                else if (d.estado_solicitud === 'rechazada') { estadoBadge = 'Rechazada'; estadoClass = 'danger'; }
                else if (d.estado_solicitud === 'cancelada') { estadoBadge = 'Cancelada'; estadoClass = 'secondary'; }
                else { estadoBadge = d.estado_solicitud; estadoClass = 'secondary'; }

                var prioridadBadge = '';
                if (d.prioridad === 'urgente') prioridadBadge = '<span class="badge bg-danger">Urgente</span>';
                else if (d.prioridad === 'alta') prioridadBadge = '<span class="badge bg-warning text-dark">Alta</span>';
                else if (d.prioridad === 'normal') prioridadBadge = '<span class="badge bg-info">Normal</span>';
                else prioridadBadge = '<span class="badge bg-secondary">Baja</span>';

                var nombreEntidad = 'No especificado';
                if (d.tipo_solicitante === 'interno' && d.departamento) nombreEntidad = d.departamento.nombre;
                else if (d.tipo_solicitante === 'externo' && d.institucion) nombreEntidad = d.institucion.nombre;

                var fechaSolicitud = d.fecha_solicitud || '—';
                var fechaRequerida = d.fecha_requerida || '—';
                var fechaFin = d.fecha_fin_estimada || '—';

                html = `
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="fw-bold" style="color: #1e3c72;">Solicitud #${d.id}</h6>
                            <span class="badge bg-${estadoClass}">${estadoBadge}</span>
                            ${prioridadBadge}
                        </div>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Tipo Solicitante</div>
                            <div class="detail-value">${d.tipo_solicitante === 'interno' ? 'Interno' : 'Externo'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Entidad</div>
                            <div class="detail-value">${escapeHtml(nombreEntidad)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Responsable</div>
                            <div class="detail-value">${escapeHtml(d.responsable?.nombre || '—')}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Fecha Solicitud</div>
                            <div class="detail-value">${escapeHtml(fechaSolicitud)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Fecha Requerida</div>
                            <div class="detail-value">${escapeHtml(fechaRequerida)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Fecha Fin Estimada</div>
                            <div class="detail-value">${escapeHtml(fechaFin)}</div>
                        </div>
                    </div>
                `;

                if (d.justificacion) {
                    html += `
                        <div class="detail-section">
                            <div class="detail-section-title">Justificación</div>
                            <p class="mb-0">${escapeHtml(d.justificacion)}</p>
                        </div>
                    `;
                }

                if (d.detalles && d.detalles.length > 0) {
                    html += `
                        <div class="detail-section">
                            <div class="detail-section-title">Items Solicitados (${d.detalles.length})</div>
                            <div class="item-list">
                                ${d.detalles.map(function(item) {
                                    var descripcion = item.item_descripcion || item.descripcion_personalizada || 'Item';
                                    return `<div class="item-row">
                                        <span>${escapeHtml(descripcion)}</span>
                                        <span>Cantidad: ${item.cantidad_solicitada || item.cantidad || 1}</span>
                                    </div>`;
                                }).join('')}
                            </div>
                        </div>
                    `;
                }

                document.getElementById('modalDetallePrestamoBody').innerHTML = html;
                document.getElementById('modalDetallePrestamoActionBtn').href = '{{ route("admin.solicitudes.index") }}';
            } else {
                document.getElementById('modalDetallePrestamoBody').innerHTML = '<div class="text-center text-danger py-4">' + (data.message || 'Error al cargar detalles') + '</div>';
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            document.getElementById('modalDetallePrestamoBody').innerHTML = '<div class="text-center text-danger py-4">Error al cargar los detalles</div>';
        });
    }

    // ========== CAMBIAR VISTA ==========
    function cambiarVista(vista) {
        document.getElementById('calendarView').style.display = 'none';
        document.getElementById('timelineView').style.display = 'none';
        document.getElementById('kanbanView').style.display = 'none';
        
        document.getElementById('viewCalendarBtn').classList.remove('active');
        document.getElementById('viewTimelineBtn').classList.remove('active');
        document.getElementById('viewKanbanBtn').classList.remove('active');
        
        if (vista === 'calendar') {
            document.getElementById('calendarView').style.display = 'block';
            document.getElementById('viewCalendarBtn').classList.add('active');
            if (calendar) {
                calendar.render();
            }
        } else if (vista === 'timeline') {
            document.getElementById('timelineView').style.display = 'block';
            document.getElementById('viewTimelineBtn').classList.add('active');
            aplicarFiltroTimeline();
        } else if (vista === 'kanban') {
            document.getElementById('kanbanView').style.display = 'block';
            document.getElementById('viewKanbanBtn').classList.add('active');
        }
    }

    // ========== FILTRAR TIMELINE ==========
    let filtroActual = 'todos';

    function filtrarTimeline(filtro, btn) {
        filtroActual = filtro;
        document.querySelectorAll('.btn-filtro').forEach(el => el.classList.remove('active'));
        if (btn) btn.classList.add('active');
        aplicarFiltroTimeline();
    }

    function aplicarFiltroTimeline() {
        const items = document.querySelectorAll('.timeline-item');
        let contador = 0;

        items.forEach(item => {
            const eventos = item.querySelectorAll('.timeline-card');
            let mostrar = false;

            eventos.forEach(evento => {
                const estado = evento.querySelector('.badge-estado');
                const estadoText = estado ? estado.textContent.trim().toLowerCase() : '';
                const tieneVencido = estadoText.includes('vencido') || estadoText.includes('⚠️');

                switch(filtroActual) {
                    case 'todos':
                        mostrar = true;
                        break;
                    case 'activos':
                        if (estadoText.includes('entregado') || estadoText.includes('extendido')) {
                            mostrar = true;
                        }
                        break;
                    case 'vencidos':
                        if (tieneVencido) {
                            mostrar = true;
                        }
                        break;
                    case 'devueltos':
                        if (estadoText.includes('devuelto')) {
                            mostrar = true;
                        }
                        break;
                    case 'proximos':
                        if (estadoText.includes('entregado') || estadoText.includes('extendido')) {
                            if (!tieneVencido) {
                                mostrar = true;
                            }
                        }
                        break;
                    default:
                        mostrar = true;
                }
            });

            if (mostrar) {
                item.style.display = '';
                contador++;
            } else {
                item.style.display = 'none';
            }
        });

        const container = document.getElementById('timelineContainer');
        let emptyMsg = container.querySelector('.timeline-empty-result');
        if (contador === 0) {
            if (!emptyMsg) {
                emptyMsg = document.createElement('div');
                emptyMsg.className = 'timeline-empty timeline-empty-result';
                emptyMsg.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <h5>No hay eventos para este filtro</h5>
                    <p>Prueba con otro filtro</p>
                `;
                container.appendChild(emptyMsg);
            }
            emptyMsg.style.display = '';
        } else if (emptyMsg) {
            emptyMsg.style.display = 'none';
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ========== INICIALIZAR FILTROS ==========
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            aplicarFiltroTimeline();
        }, 300);
    });
</script>