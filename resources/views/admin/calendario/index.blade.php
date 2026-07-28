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
            background: rgba(30, 60, 114, 0.05);
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

        .timeline-item .progress-bar {
            margin-top: 0.5rem;
            height: 4px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .timeline-item .progress-bar .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.6s ease;
        }

        .timeline-item .progress-bar .progress-fill.success { background: #22c55e; }
        .timeline-item .progress-bar .progress-fill.warning { background: #f59e0b; }
        .timeline-item .progress-bar .progress-fill.danger { background: #ef4444; }

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

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1200px) {
            .kanban-board {
                grid-template-columns: repeat(3, 1fr);
            }
        }

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

            .kanban-board {
                grid-template-columns: 1fr;
                min-width: unset;
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

        /* ========== ESTILOS PARA EL MODAL ========== */
        .bg-primary-dark {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
        }

        .btn-close-white {
            filter: brightness(0) invert(1);
        }

        .btn-primary-dark {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary-dark:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(30, 60, 114, 0.3);
            color: white;
        }

        .btn-outline-primary-dark {
            border: 1.5px solid #1e3c72;
            color: #1e3c72;
            background: transparent;
            padding: 0.4rem 1rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-primary-dark:hover {
            background: #1e3c72;
            color: white;
            transform: translateY(-2px);
        }

        /* ========== MODAL DETALLE ========== */
        .modal-detalle-moderno .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .modal-detalle-moderno .modal-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 1.25rem 1.75rem;
            border-bottom: none;
        }

        .modal-detalle-moderno .modal-header .modal-title {
            color: white;
            font-weight: 700;
            font-size: 1.15rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-detalle-moderno .modal-header .modal-title svg {
            stroke: white;
        }

        .modal-detalle-moderno .modal-body {
            padding: 1.75rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-detalle-moderno .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-detalle-moderno .modal-body::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .modal-detalle-moderno .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .modal-detalle-moderno .modal-footer {
            padding: 1rem 1.75rem;
            border-top: 1px solid #f1f5f9;
            background: #fafbfc;
        }

        .detalle-header-modal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            border: 1px solid #e9ecef;
        }

        .detalle-header-modal .detalle-codigo {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e3c72;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detalle-header-modal .detalle-codigo svg {
            stroke: #1e3c72;
        }

        .detalle-header-modal .detalle-estado-badge {
            padding: 0.3rem 0.9rem;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .detalle-header-modal .detalle-estado-badge svg {
            width: 14px;
            height: 14px;
        }

        .detalle-header-modal .detalle-estado-badge.entregado { background: #dcfce7; color: #16a34a; }
        .detalle-header-modal .detalle-estado-badge.aprobado { background: #dbeafe; color: #2563eb; }
        .detalle-header-modal .detalle-estado-badge.extendido { background: #fef3c7; color: #d97706; }
        .detalle-header-modal .detalle-estado-badge.devuelto { background: #e0e7ff; color: #4f46e5; }
        .detalle-header-modal .detalle-estado-badge.pendiente { background: #f1f5f9; color: #64748b; }
        .detalle-header-modal .detalle-estado-badge.vencido { background: #fee2e2; color: #dc2626; }
        .detalle-header-modal .detalle-estado-badge.rechazado { background: #fee2e2; color: #dc2626; }
        .detalle-header-modal .detalle-estado-badge.cancelado { background: #f1f5f9; color: #64748b; }

        .detalle-grid-modal {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .detalle-grid-modal .detalle-item {
            padding: 0.6rem 0.8rem;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #f1f5f9;
            transition: all 0.2s ease;
        }

        .detalle-grid-modal .detalle-item:hover {
            background: #f1f5f9;
        }

        .detalle-grid-modal .detalle-item .detalle-label {
            font-size: 0.6rem;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .detalle-grid-modal .detalle-item .detalle-label svg {
            width: 12px;
            height: 12px;
            stroke: #94a3b8;
        }

        .detalle-grid-modal .detalle-item .detalle-value {
            font-weight: 500;
            color: #0f172a;
            font-size: 0.85rem;
            margin-top: 0.15rem;
            word-break: break-word;
        }

        .detalle-section-modal {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }

        .detalle-section-modal .detalle-section-title {
            font-weight: 600;
            color: #1e3c72;
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detalle-section-modal .detalle-section-title .badge-count {
            background: #1e3c72;
            color: white;
            border-radius: 20px;
            padding: 0.1rem 0.5rem;
            font-size: 0.6rem;
            font-weight: 600;
        }

        .detalle-section-modal .item-list-modal {
            background: #f8fafc;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            border: 1px solid #e9ecef;
        }

        .detalle-section-modal .item-row-modal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.4rem 0;
            font-size: 0.8rem;
            border-bottom: 1px solid #e9ecef;
        }

        .detalle-section-modal .item-row-modal:last-child {
            border-bottom: none;
        }

        .detalle-section-modal .item-row-modal .item-nombre {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: #0f172a;
        }

        .detalle-section-modal .item-row-modal .item-nombre svg {
            width: 14px;
            height: 14px;
            stroke: #64748b;
        }

        .detalle-section-modal .item-row-modal .item-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.7rem;
            color: #64748b;
        }

        .detalle-section-modal .item-row-modal .item-info .badge-estado-item {
            padding: 0.1rem 0.5rem;
            border-radius: 12px;
            font-size: 0.6rem;
            font-weight: 600;
        }

        .badge-estado-item.bueno { background: #dcfce7; color: #16a34a; }
        .badge-estado-item.regular { background: #fef3c7; color: #d97706; }
        .badge-estado-item.malo { background: #fee2e2; color: #dc2626; }
        .badge-estado-item.pendiente { background: #f1f5f9; color: #64748b; }

        .detalle-observaciones-modal {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            font-size: 0.8rem;
            color: #334155;
            line-height: 1.5;
        }

        .detalle-observaciones-modal .obs-label {
            font-size: 0.6rem;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            margin-bottom: 0.3rem;
        }

        .detalle-observaciones-modal .obs-label svg {
            width: 12px;
            height: 12px;
            stroke: #94a3b8;
        }

        @media (max-width: 768px) {
            .detalle-grid-modal {
                grid-template-columns: 1fr;
            }

            .detalle-header-modal {
                flex-direction: column;
                align-items: flex-start;
            }

            .modal-detalle-moderno .modal-body {
                padding: 1.25rem;
            }

            .modal-detalle-moderno .modal-header {
                padding: 1rem 1.25rem;
            }

            .detalle-section-modal .item-row-modal {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
                padding: 0.5rem 0;
            }

            .detalle-section-modal .item-row-modal .item-info {
                flex-wrap: wrap;
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
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-number">{{ $totalPrestamos ?? 0 }}</div>
                <div class="stat-label">Total Préstamos</div>
                <span class="stat-trend up">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display:inline;vertical-align:middle;margin-right:2px;">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                        <polyline points="17 6 23 6 23 12"/>
                    </svg>
                    Activo
                </span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-number">{{ $prestamosActivos ?? 0 }}</div>
                <div class="stat-label">Préstamos Activos</div>
                <span class="stat-trend up">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display:inline;vertical-align:middle;margin-right:2px;">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                        <polyline points="17 6 23 6 23 12"/>
                    </svg>
                    En curso
                </span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
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
                <span class="stat-trend {{ ($prestamosVencidos ?? 0) > 0 ? 'down' : 'up' }}">
                    {{ ($prestamosVencidos ?? 0) > 0 ? 'Requieren atención' : 'Todo en orden' }}
                </span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">
                <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
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
                    <span class="legend-item">
                        <span class="legend-color" style="background: #22c55e;"></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Entregado
                    </span>
                    <span class="legend-item">
                        <span class="legend-color" style="background: #1e3c72;"></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Aprobado
                    </span>
                    <span class="legend-item">
                        <span class="legend-color" style="background: #f59e0b;"></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Extendido
                    </span>
                    <span class="legend-item">
                        <span class="legend-color" style="background: #ef4444;"></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        Vencido
                    </span>
                    <span class="legend-item">
                        <span class="legend-color" style="background: #6c757d;"></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2.5">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M22 7l-10 7L2 7"/>
                        </svg>
                        Solicitud
                    </span>
                </div>
                <div>
                    <span class="text-muted small" style="color: #fff">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" class="me-1" style="display:inline;">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4M12 8h.01"/>
                        </svg>
                        Haz clic en un evento para ver detalles
                    </span>
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
                            <span class="ms-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                    <line x1="12" y1="9" x2="12" y2="13"/>
                                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                            </span>
                            @endif
                        </span>
                    </div>
                    <div class="card-subtitle">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:4px;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        {{ $evento->fecha_prestamo->format('d/m/Y') }}
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;margin:0 4px;">
                            <polyline points="5 12 19 12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                        {{ $evento->fecha_devolucion_esperada->format('d/m/Y') }}
                        @if(!$evento->fecha_devolucion_real)
                            • <span class="text-muted">{{ $evento->dias_restantes }} días restantes</span>
                        @else
                            • <span class="text-success">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" style="display:inline;margin-right:2px;">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Devuelto el {{ $evento->fecha_devolucion_real->format('d/m/Y') }}
                            </span>
                        @endif
                    </div>
                    <div class="card-footer">
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:4px;">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            {{ $evento->responsableReceptor?->nombre ?? 'N/A' }}
                        </span>
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:4px;">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            {{ $evento->detalles->count() }} items
                        </span>
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
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" class="me-1" style="display:inline;">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Crear Préstamo
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
                <span class="text-muted small">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" class="me-1" style="display:inline;">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4M12 8h.01"/>
                    </svg>
                    Haz clic en una tarjeta para ver los detalles del préstamo
                </span>
            </div>
            <div class="kanban-board">
                <!-- Columna: Pendiente -->
                <div class="kanban-column pendiente">
                    <div class="kanban-column-header">
                        <span class="column-title">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Pendiente
                        </span>
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
                        <span class="column-title">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Aprobado
                        </span>
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
                        <span class="column-title">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <path d="M2 17l10 5 10-5"/>
                                <path d="M2 12l10 5 10-5"/>
                            </svg>
                            Entregado
                        </span>
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
                        <span class="column-title">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Extendido
                        </span>
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
                        <span class="column-title">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Devuelto
                        </span>
                        <span class="column-count">{{ $kanbanData['devuelto']->count() }}</span>
                    </div>
                    <div class="kanban-cards">
                        @forelse($kanbanData['devuelto'] as $item)
                        <div class="kanban-card" onclick="verDetallePrestamo({{ $item->id }})">
                            <div class="card-title">{{ $item->codigo }}</div>
                            <div class="card-subtitle">{{ $item->destino_nombre }}</div>
                            <div class="card-footer">
                                <span>Responsable: {{ $item->responsableReceptor?->nombre ?? 'N/A' }}</span>
                                <span class="card-badge completado">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;margin-right:2px;">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Completado
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
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL DETALLE PRÉSTAMO ========== -->
<div class="modal fade modal-detalle-moderno" id="modalDetallePrestamo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetallePrestamoTitle">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    Detalle del Préstamo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetallePrestamoBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Cargando detalles del préstamo...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Cerrar
                </button>
                <a href="#" id="modalDetallePrestamoActionBtn" class="btn btn-primary-dark" target="_blank" style="color:#fff">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="me-1">
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

    // ========== FUNCIÓN PARA FORMATEAR FECHA ==========
    function formatearFecha(fecha) {
        if (!fecha) return '—';
        if (typeof fecha === 'string') {
            var d = new Date(fecha);
            if (!isNaN(d.getTime())) {
                return d.toLocaleDateString('es-VE', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }
            return fecha;
        }
        if (fecha instanceof Date) {
            return fecha.toLocaleDateString('es-VE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
        return '—';
    }

    // ========== VER DETALLE PRÉSTAMO ==========
    function verDetallePrestamo(id) {
        var modal = new bootstrap.Modal(document.getElementById('modalDetallePrestamo'));
        document.getElementById('modalDetallePrestamoTitle').textContent = 'Detalle del Préstamo';
        document.getElementById('modalDetallePrestamoBody').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted">Cargando detalles del préstamo...</p>
            </div>
        `;
        modal.show();

        fetch('/admin/prestamos/' + id, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                var d = data.data;
                var html = '';

                // Estado
                var estadoClasses = {
                    'entregado': 'entregado',
                    'aprobado': 'aprobado',
                    'extendido': 'extendido',
                    'devuelto': 'devuelto',
                    'pendiente': 'pendiente',
                    'vencido': 'vencido',
                    'rechazado': 'rechazado',
                    'cancelado': 'cancelado'
                };
                var estadoClass = estadoClasses[d.estado] || 'pendiente';
                var estadoLabel = d.estado.charAt(0).toUpperCase() + d.estado.slice(1);

                // Formatear fechas con la función
                var fechaPrestamo = formatearFecha(d.fecha_prestamo);
                var fechaDevolucionEsperada = formatearFecha(d.fecha_devolucion_esperada);
                var fechaDevolucionReal = d.fecha_devolucion_real ? formatearFecha(d.fecha_devolucion_real) : null;

                html = `
                    <!-- Header con código y estado -->
                    <div class="detalle-header-modal">
                        <div class="detalle-codigo">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                            ${escapeHtml(d.codigo)}
                        </div>
                        <span class="detalle-estado-badge ${estadoClass}">
                            ${estadoClass === 'entregado' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>' : ''}
                            ${estadoClass === 'aprobado' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>' : ''}
                            ${estadoClass === 'devuelto' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>' : ''}
                            ${estadoClass === 'pendiente' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' : ''}
                            ${estadoClass === 'extendido' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' : ''}
                            ${estadoClass === 'vencido' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>' : ''}
                            ${estadoClass === 'rechazado' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' : ''}
                            ${estadoClass === 'cancelado' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' : ''}
                            ${estadoLabel}
                            ${d.esta_vencido ? ' ⚠️' : ''}
                        </span>
                    </div>

                    <!-- Grid de información -->
                    <div class="detalle-grid-modal">
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Responsable Receptor
                            </div>
                            <div class="detalle-value">${escapeHtml(d.responsable_receptor?.nombre || '—')}</div>
                        </div>
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Responsable Emisor
                            </div>
                            <div class="detalle-value">${escapeHtml(d.responsable_emisor?.nombre || '—')}</div>
                        </div>
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="8" width="16" height="12" rx="1"/><path d="M8 20V8M16 20V8M4 12h16"/></svg>
                                Destino
                            </div>
                            <div class="detalle-value">${escapeHtml(d.destino_nombre || '—')}</div>
                        </div>
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"/><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg>
                                Tipo
                            </div>
                            <div class="detalle-value">${escapeHtml(d.tipo_prestamo || '—')}</div>
                        </div>
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Fecha Préstamo
                            </div>
                            <div class="detalle-value">${fechaPrestamo}</div>
                        </div>
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                ${fechaDevolucionReal ? 'Fecha Devolución Real' : 'Fecha Devolución Esperada'}
                            </div>
                            <div class="detalle-value ${fechaDevolucionReal ? 'text-success' : (d.dias_restantes !== undefined && d.dias_restantes < 3 ? 'text-danger' : '')}">
                                ${fechaDevolucionReal ? fechaDevolucionReal : fechaDevolucionEsperada}
                                ${!fechaDevolucionReal && d.dias_restantes !== undefined ? ` <small class="text-muted">(${d.dias_restantes} días restantes)</small>` : ''}
                            </div>
                        </div>
                    </div>
                `;

                if (d.observaciones) {
                    html += `
                        <div class="detalle-observaciones-modal">
                            <div class="obs-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M22 7l-10 7L2 7"/></svg>
                                Observaciones
                            </div>
                            ${escapeHtml(d.observaciones)}
                        </div>
                    `;
                }

                if (d.detalles && d.detalles.length > 0) {
                    html += `
                        <div class="detalle-section-modal">
                            <div class="detalle-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                                </svg>
                                Items del Préstamo
                                <span class="badge-count">${d.detalles.length}</span>
                            </div>
                            <div class="item-list-modal">
                                ${d.detalles.map(function(item) {
                                    var nombreItem = item.nombre_item || item.prestable?.serial || 'Item #' + item.id;
                                    var estadoEntrega = item.estado_entrega || '—';
                                    var estadoClass = 'pendiente';
                                    if (estadoEntrega === 'Entregado en buen estado') estadoClass = 'bueno';
                                    else if (estadoEntrega === 'Entregado con detalles') estadoClass = 'regular';
                                    else if (estadoEntrega === 'Entregado con daños') estadoClass = 'malo';
                                    else estadoClass = 'pendiente';
                                    return `<div class="item-row-modal">
                                        <div class="item-nombre">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            ${escapeHtml(nombreItem)}
                                        </div>
                                        <div class="item-info">
                                            <span>Cant: ${item.cantidad || 1}</span>
                                            <span class="badge-estado-item ${estadoClass}">${escapeHtml(estadoEntrega)}</span>
                                        </div>
                                    </div>`;
                                }).join('')}
                            </div>
                        </div>
                    `;
                }

                document.getElementById('modalDetallePrestamoBody').innerHTML = html;
                document.getElementById('modalDetallePrestamoActionBtn').href = '{{ route("admin.prestamos.index") }}?search=' + encodeURIComponent(d.codigo);
            } else {
                document.getElementById('modalDetallePrestamoBody').innerHTML = `
                    <div class="text-center text-danger py-5">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.5" class="mb-3">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <p>${data.message || 'Error al cargar detalles'}</p>
                    </div>
                `;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            document.getElementById('modalDetallePrestamoBody').innerHTML = `
                <div class="text-center text-danger py-5">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.5" class="mb-3">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p>Error al cargar los detalles del préstamo</p>
                </div>
            `;
        });
    }

    // ========== VER DETALLE SOLICITUD ==========
    function verDetalleSolicitud(id) {
        var modal = new bootstrap.Modal(document.getElementById('modalDetallePrestamo'));
        document.getElementById('modalDetallePrestamoTitle').textContent = 'Detalle de la Solicitud';
        document.getElementById('modalDetallePrestamoBody').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted">Cargando detalles de la solicitud...</p>
            </div>
        `;
        modal.show();

        fetch('/admin/solicitudes/' + id + '/detalles', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success || data.id) {
                var d = data;
                var html = '';

                var estadoClasses = {
                    'pendiente': 'pendiente',
                    'aprobada': 'aprobado',
                    'rechazada': 'rechazado',
                    'cancelada': 'cancelado'
                };
                var estadoClass = estadoClasses[d.estado_solicitud] || 'pendiente';
                var estadoLabel = d.estado_solicitud.charAt(0).toUpperCase() + d.estado_solicitud.slice(1);

                var prioridadClasses = {
                    'urgente': 'danger',
                    'alta': 'warning',
                    'normal': 'info',
                    'baja': 'secondary'
                };
                var prioridadClass = prioridadClasses[d.prioridad] || 'secondary';
                var prioridadLabel = d.prioridad.charAt(0).toUpperCase() + d.prioridad.slice(1);

                var nombreEntidad = 'No especificado';
                if (d.tipo_solicitante === 'interno' && d.departamento) nombreEntidad = d.departamento.nombre;
                else if (d.tipo_solicitante === 'externo' && d.institucion) nombreEntidad = d.institucion.nombre;

                var fechaSolicitud = formatearFecha(d.fecha_solicitud);
                var fechaRequerida = formatearFecha(d.fecha_requerida);
                var fechaFin = formatearFecha(d.fecha_fin_estimada);

                html = `
                    <div class="detalle-header-modal">
                        <div class="detalle-codigo">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="M22 7l-10 7L2 7"/>
                            </svg>
                            Solicitud #${d.id}
                        </div>
                        <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                            <span class="detalle-estado-badge ${estadoClass}">
                                ${estadoClass === 'aprobado' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>' : ''}
                                ${estadoClass === 'pendiente' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' : ''}
                                ${estadoClass === 'rechazado' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' : ''}
                                ${estadoClass === 'cancelado' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' : ''}
                                ${estadoLabel}
                            </span>
                            <span class="badge bg-${prioridadClass} text-white" style="font-size:0.65rem; padding:0.3rem 0.7rem; border-radius:30px;">
                                ${prioridadLabel}
                            </span>
                        </div>
                    </div>

                    <div class="detalle-grid-modal">
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>
                                Tipo Solicitante
                            </div>
                            <div class="detalle-value">${d.tipo_solicitante === 'interno' ? 'Interno' : 'Externo'}</div>
                        </div>
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="8" width="16" height="12" rx="1"/><path d="M8 20V8M16 20V8M4 12h16"/></svg>
                                Entidad
                            </div>
                            <div class="detalle-value">${escapeHtml(nombreEntidad)}</div>
                        </div>
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Responsable
                            </div>
                            <div class="detalle-value">${escapeHtml(d.responsable?.nombre || '—')}</div>
                        </div>
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Fecha Solicitud
                            </div>
                            <div class="detalle-value">${fechaSolicitud}</div>
                        </div>
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Fecha Requerida
                            </div>
                            <div class="detalle-value">${fechaRequerida}</div>
                        </div>
                        <div class="detalle-item">
                            <div class="detalle-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Fecha Fin Estimada
                            </div>
                            <div class="detalle-value">${fechaFin}</div>
                        </div>
                    </div>
                `;

                if (d.justificacion) {
                    html += `
                        <div class="detalle-observaciones-modal">
                            <div class="obs-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M22 7l-10 7L2 7"/></svg>
                                Justificación
                            </div>
                            ${escapeHtml(d.justificacion)}
                        </div>
                    `;
                }

                if (d.detalles && d.detalles.length > 0) {
                    html += `
                        <div class="detalle-section-modal">
                            <div class="detalle-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                                </svg>
                                Items Solicitados
                                <span class="badge-count">${d.detalles.length}</span>
                            </div>
                            <div class="item-list-modal">
                                ${d.detalles.map(function(item) {
                                    var descripcion = item.item_descripcion || item.descripcion_personalizada || 'Item';
                                    var tipoIcon = item.tipo_item === 'activo'
                                        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"/><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg>'
                                        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/></svg>';
                                    return `<div class="item-row-modal">
                                        <div class="item-nombre">
                                            ${tipoIcon}
                                            ${escapeHtml(descripcion)}
                                        </div>
                                        <div class="item-info">
                                            <span>Cantidad: ${item.cantidad_solicitada || 1}</span>
                                            <span class="badge bg-${item.tipo_item === 'activo' ? 'primary' : 'secondary'} text-white" style="font-size:0.55rem; padding:0.1rem 0.5rem; border-radius:12px;">${item.tipo_item === 'activo' ? 'Activo' : 'Componente'}</span>
                                        </div>
                                    </div>`;
                                }).join('')}
                            </div>
                        </div>
                    `;
                }

                document.getElementById('modalDetallePrestamoBody').innerHTML = html;
                document.getElementById('modalDetallePrestamoActionBtn').href = '{{ route("admin.solicitudes.index") }}';
            } else {
                document.getElementById('modalDetallePrestamoBody').innerHTML = `
                    <div class="text-center text-danger py-5">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.5" class="mb-3">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <p>${data.message || 'Error al cargar detalles'}</p>
                    </div>
                `;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            document.getElementById('modalDetallePrestamoBody').innerHTML = `
                <div class="text-center text-danger py-5">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.5" class="mb-3">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p>Error al cargar los detalles de la solicitud</p>
                </div>
            `;
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
