@extends('layouts.dashboard')

@section('title', 'Catálogo de Equipos')

@section('styles')
    @vite(['resources/css/admin-equipos.css'])
    <style>
        /* ========== DROPDOWN DE NUEVO ========== */
        .dropdown-nuevo .dropdown-menu {
            border-radius: 12px;
            border: 1px solid #e9ecef;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            padding: 0.5rem;
            min-width: 260px;
            margin-top: 0.5rem;
        }
        .dropdown-nuevo .dropdown-item {
            border-radius: 8px;
            padding: 0.6rem 0.9rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: #1e3c72;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
        }
        .dropdown-nuevo .dropdown-item:hover {
            background: #eef3fc;
            color: #1e3c72;
            transform: translateX(2px);
        }
        .dropdown-nuevo .dropdown-item svg {
            stroke: #1e3c72;
            flex-shrink: 0;
        }
        .dropdown-nuevo .dropdown-item.destacado {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
        }
        .dropdown-nuevo .dropdown-item.destacado svg {
            stroke: white;
        }
        .dropdown-nuevo .dropdown-item.destacado:hover {
            background: linear-gradient(135deg, #152a50 0%, #1e3c72 100%);
            color: white;
        }
        .dropdown-nuevo .dropdown-item .item-texto {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        .dropdown-nuevo .dropdown-item .item-desc {
            font-size: 0.7rem;
            font-weight: 400;
            opacity: 0.75;
            margin-top: 2px;
        }
        .dropdown-nuevo .dropdown-divider {
            margin: 0.4rem 0;
            border-color: #e9ecef;
        }
        .dropdown-nuevo .btn-light {
            border-radius: 30px;
            font-weight: 600;
            padding: 0.55rem 1.3rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .dropdown-nuevo .btn-light:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        /* ========== FILTROS ========== */
        .filters-bar {
            background: white;
            border: 1px solid var(--border-light);
            border-top: none;
            padding: 0.75rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .filters-bar .d-flex {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
        }

        /* ========== RESTO DE ESTILOS ========== */
        .bg-primary-dark {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        .modal-header.bg-primary-dark .btn-close {
            filter: brightness(0) invert(1);
        }
        .highlight {
            background: #fef3c7;
            padding: 1px 3px;
            border-radius: 3px;
        }
        .stat-icon-circle svg {
            width: 24px;
            height: 24px;
            stroke: #1e3c72;
            stroke-width: 1.8;
            fill: none;
        }
        .stat-card-mini:hover .stat-icon-circle svg {
            stroke: white;
        }
        .badge-activo { color: #0f172a !important; }
        .badge-inactivo { color: #0f172a !important; }

        .btn-primary-dark {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 0.45rem 1.2rem;
            border-radius: 10px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary-dark:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(30, 60, 114, 0.3);
            color: white;
        }
        .btn-outline-primary-dark {
            background: transparent;
            border: 1.5px solid #1e3c72;
            color: #1e3c72;
            font-weight: 500;
            padding: 0.4rem 1rem;
            border-radius: 10px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-outline-primary-dark:hover {
            background: #1e3c72;
            color: white;
            transform: translateY(-2px);
        }
        .btn-outline-danger {
            background: transparent;
            border: 1.5px solid #c5221f;
            color: #c5221f;
            font-weight: 500;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            font-size: 0.75rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-outline-danger:hover {
            background: #c5221f;
            color: white;
            transform: translateY(-2px);
        }
        .btn-outline-secondary {
            background: transparent;
            border: 1.5px solid #6c757d;
            color: #6c757d;
            font-weight: 500;
            padding: 0.4rem 1rem;
            border-radius: 10px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
        }
        .btn-sm {
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            border-radius: 8px;
        }
        .table-container {
            background: white;
            border-radius: 0 0 0.75rem 0.75rem;
            border: 1px solid #e9ecef;
            border-top: none;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .table th {
            background: #f8f9fc;
            color: #1e3c72;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 2px solid #1e3c72;
            padding: 0.9rem 0.75rem;
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
            padding: 0.8rem 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }
        .table tbody tr:hover {
            background-color: #eef3fc;
            transition: background 0.15s ease;
        }
        .detail-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        .detail-card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: #f8f9fc;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
        }
        .detail-card-body { padding: 1.25rem; }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label {
            font-size: 0.6rem;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: 0.8px;
            font-weight: 600;
        }
        .detail-value {
            font-weight: 500;
            color: #1a1a1a;
            font-size: 0.9rem;
        }
        .modal-content {
            border-radius: 1rem;
            border: none;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        .modal-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 1.25rem 1.75rem;
            border-bottom: none;
        }
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }
        .modal-header .btn-close:hover { opacity: 1; }
        .modal-title {
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .modal-title svg { stroke: white; }
        .modal-body {
            padding: 1.75rem;
            max-height: 70vh;
            overflow-y: auto;
        }
        .modal-body::-webkit-scrollbar { width: 6px; }
        .modal-body::-webkit-scrollbar-track {
            background: #f8f9fc;
            border-radius: 3px;
        }
        .modal-body::-webkit-scrollbar-thumb {
            background: #1e3c72;
            border-radius: 3px;
        }
        .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 1rem 1.75rem;
            background: #f8f9fc;
        }
        .modal-footer .btn {
            border-radius: 10px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .modal-footer .btn:hover { transform: translateY(-1px); }
        .form-label {
            font-weight: 600;
            font-size: 0.8rem;
            color: #1e3c72;
            margin-bottom: 0.3rem;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e9ecef;
            padding: 0.55rem 0.85rem;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.12);
        }

        /* ========== STATS ========== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        .stat-card-mini {
            background: white;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .stat-card-mini::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            background: rgba(30, 60, 114, 0.03);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        .stat-card-mini:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            border-color: #eef3fc;
        }
        .stat-info { position: relative; z-index: 1; }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e3c72;
            line-height: 1.2;
        }
        .stat-label {
            font-size: 0.7rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 500;
        }
        .stat-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef3fc;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }
        .stat-card-mini:hover .stat-icon-circle {
            background: #1e3c72;
            transform: scale(1.05) rotate(-5deg);
        }
        .stat-card-mini:hover .stat-icon-circle svg { stroke: white; }

        /* ========== TABS ========== */
        .nav-tabs-custom {
            display: flex;
            gap: 0.25rem;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 0;
            background: white;
            padding: 0 0.5rem;
            border-radius: 0.75rem 0.75rem 0 0;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            padding: 0.85rem 1.5rem;
            color: #6c757d;
            font-weight: 500;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 0;
            background: transparent;
            transition: all 0.3s ease;
            position: relative;
        }
        .nav-tabs-custom .nav-link:hover {
            color: #1e3c72;
            background: #eef3fc;
        }
        .nav-tabs-custom .nav-link.active {
            color: #1e3c72;
            font-weight: 600;
            background: transparent;
        }
        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #1e3c72, #2a5298);
            border-radius: 3px 3px 0 0;
        }
        .nav-tabs-custom .nav-link .tab-badge {
            background: #eef3fc;
            color: #1e3c72;
            padding: 0.1rem 0.5rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            margin-left: 4px;
        }
        .nav-tabs-custom .nav-link.active .tab-badge {
            background: #1e3c72;
            color: white;
        }
        .badge-activo {
            background: #dcfce7;
            color: #0f172a !important;
            border: none;
            font-size: 0.7rem;
            font-weight: 500;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-activo::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #22c55e;
            display: inline-block;
        }
        .badge-inactivo {
            background: #fee2e2;
            color: #0f172a !important;
            border: none;
            font-size: 0.7rem;
            font-weight: 500;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-inactivo::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #ef4444;
            display: inline-block;
        }

        /* ========== WIZARD ========== */
        .step-circle {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            font-weight: 700;
            margin-right: 6px;
            transition: all 0.3s ease;
        }
        .step-circle.active {
            background: #1e3c72;
            color: white;
            box-shadow: 0 0 0 4px rgba(30, 60, 114, 0.15);
        }
        .step-circle.completed {
            background: #1e7e34;
            color: white;
        }
        .step-content {
            min-height: 280px;
            animation: fadeInUp 0.4s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .step-label {
            font-size: 0.8rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .institucion-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid #e9ecef;
        }
        .institucion-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .institucion-card.active {
            border-color: #1e3c72;
            background: #eef3fc;
        }
        .progress {
            height: 4px;
            border-radius: 4px;
            background: #e9ecef;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, #1e3c72, #2a5298);
            transition: width 0.5s ease;
        }

        @media (max-width: 992px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }
        @media (max-width: 768px) {
            .stats-row { grid-template-columns: 1fr; }
            .nav-tabs-custom .nav-link {
                padding: 0.5rem 1rem;
                font-size: 0.75rem;
            }
            .filters-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .filters-bar .d-flex { flex-direction: column; }
            .filters-bar .input-group { max-width: 100% !important; }
            .modal-body { padding: 1.25rem; }
            .modal-header { padding: 1rem 1.25rem; }
            .table-container { overflow-x: auto; }
            .table { min-width: 600px; }
            .detail-card-header { flex-wrap: wrap; }
        }
        @media (max-width: 576px) {
            .stats-row { grid-template-columns: 1fr; }
            .stat-number { font-size: 1.5rem; }
            .table thead { display: none; }
            .table tbody td {
                display: block;
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            .table tbody td::before {
                content: attr(data-label);
                position: absolute;
                left: 0.75rem;
                width: 45%;
                text-align: left;
                font-weight: 600;
                color: #1e3c72;
                font-size: 0.7rem;
            }
            .table tbody tr {
                margin-bottom: 1rem;
                display: block;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 0.5rem 0;
                background: white;
            }
            .table tbody td:last-child { border-bottom: none; }
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
<div class="container-fluid px-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 16px; padding: 1.5rem 2rem; color: white;">
        <div>
            <h4 style="color: white; font-weight: 700; margin: 0;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display:inline; margin-right: 10px;">
                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                    <line x1="9" y1="4" x2="9" y2="20"/>
                    <line x1="15" y1="4" x2="15" y2="20"/>
                </svg>
                Catálogo de Equipos
            </h4>
            <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem; margin: 0;">Gestión de marcas, categorías y modelos</p>
        </div>

        <!-- Dropdown "Nuevo" CON wizard -->
        <div class="dropdown dropdown-nuevo">
            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2.5" style="display:inline; vertical-align: middle; margin-right: 4px;">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nuevo
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item destacado" href="#" data-action="wizard">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                        <div class="item-texto">
                            <strong>Equipo Completo</strong>
                            <span class="item-desc">Marca + Categoría + Modelo</span>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="#" data-action="marca">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="8" width="16" height="12" rx="1"/>
                        </svg>
                        <div class="item-texto">
                            <strong>Nueva Marca</strong>
                            <span class="item-desc">Fabricante (Dell, HP, Logitech...)</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-action="categoria">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                        </svg>
                        <div class="item-texto">
                            <strong>Nueva Categoría</strong>
                            <span class="item-desc">Tipo de producto (Laptop, Mouse...)</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-action="modelo">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="4" width="16" height="16" rx="2"/>
                            <line x1="9" y1="4" x2="9" y2="20"/>
                        </svg>
                        <div class="item-texto">
                            <strong>Nuevo Modelo</strong>
                            <span class="item-desc">Marca + Categoría + Nombre</span>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row mb-4">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalMarcas }}</div>
                <div class="stat-label">Total Marcas</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="4" y="8" width="16" height="12" rx="1"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalCategorias }}</div>
                <div class="stat-label">Categorías</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalModelos }}</div>
                <div class="stat-label">Modelos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                    <line x1="9" y1="4" x2="9" y2="20"/>
                </svg>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalActivos }}</div>
                <div class="stat-label">Registros Activos</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs-custom" id="equipoTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#marcas" data-tab="marcas">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <rect x="4" y="8" width="16" height="12" rx="1"/>
                </svg>
                Marcas <span class="tab-badge">{{ $totalMarcas }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#categorias" data-tab="categorias">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                </svg>
                Categorías <span class="tab-badge">{{ $totalCategorias }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#modelos" data-tab="modelos">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                </svg>
                Modelos <span class="tab-badge">{{ $totalModelos }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- TAB MARCAS -->
        <div class="tab-pane fade show active" id="marcas">
            <div class="filters-bar">
                <div class="d-flex gap-2 flex-wrap" style="flex:1;">
                    <div class="input-group" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0" id="buscarMarcas" placeholder="Buscar marca...">
                    </div>
                </div>
            </div>
            <div class="table-container">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Modelos</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaMarcas">
                        <tr><td colspan="5" class="text-center py-4 text-muted">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB CATEGORÍAS (sin marca) -->
        <div class="tab-pane fade" id="categorias">
            <div class="filters-bar">
                <div class="d-flex gap-2 flex-wrap" style="flex:1;">
                    <div class="input-group" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0" id="buscarCategorias" placeholder="Buscar categoría...">
                    </div>
                </div>
            </div>
            <div class="table-container">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Modelos</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCategorias">
                        <tr><td colspan="5" class="text-center py-4 text-muted">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB MODELOS -->
        <div class="tab-pane fade" id="modelos">
            <div class="filters-bar">
                <div class="d-flex gap-2 flex-wrap" style="flex:1;">
                    <div class="input-group" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0" id="buscarModelos" placeholder="Buscar modelo...">
                    </div>
                </div>
            </div>
            <div class="table-container">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Modelo</th>
                            <th>Marca</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaModelos">
                        <tr><td colspan="6" class="text-center py-4 text-muted">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- ============================================================
     MODAL MARCA
     ============================================================ -->
<div class="modal fade" id="modalMarca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMarcaLabel">
                    <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;">
                        <rect x="4" y="8" width="16" height="12" rx="1"/>
                    </svg>
                    Nueva Marca
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMarca">
                @csrf
                <input type="hidden" id="formMethodMarca" name="_method" value="POST">
                <input type="hidden" id="marcaId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="marca_nombre" name="nombre" required placeholder="Ej: Dell, HP, Lenovo...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="marca_descripcion" name="descripcion" rows="3" placeholder="Descripción opcional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL CATEGORÍA (sin marca)
     ============================================================ -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCategoriaLabel">
                    <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                    </svg>
                    Nueva Categoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCategoria">
                @csrf
                <input type="hidden" id="formMethodCategoria" name="_method" value="POST">
                <input type="hidden" id="categoriaId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoria_nombre" name="nombre" required placeholder="Ej: Laptop, Monitor, Mouse, Teclado...">
                        <small class="text-muted">La categoría es global y sirve para todas las marcas.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="categoria_descripcion" name="descripcion" rows="3" placeholder="Descripción opcional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL MODELO (pide Marca + Categoría)
     ============================================================ -->
<div class="modal fade" id="modalModelo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalModeloLabel">
                    <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;">
                        <rect x="4" y="4" width="16" height="16" rx="2"/>
                    </svg>
                    Nuevo Modelo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formModelo">
                @csrf
                <input type="hidden" id="formMethodModelo" name="_method" value="POST">
                <input type="hidden" id="modeloId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marca <span class="text-danger">*</span></label>
                            <select class="form-select" id="modelo_marca_id" name="marca_id" required>
                                <option value="">Seleccionar marca...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select" id="modelo_categoria_id" name="categoria_id" required>
                                <option value="">Seleccionar categoría...</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del Modelo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modelo_nombre" name="nombre" required placeholder="Ej: Latitude 5540, M90, P2422H...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="modelo_descripcion" name="descripcion" rows="2" placeholder="Descripción general del modelo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="me-1">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                        </svg>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL WIZARD EQUIPO COMPLETO (3 pasos)
     ============================================================ -->
<div class="modal fade" id="modalWizardEquipo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Registrar Equipo Completo
                </h5>
                <span class="badge bg-light text-dark" id="wizardStepIndicator">Paso 1 de 3</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Barra de progreso -->
                <div class="px-2 mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="step-label" id="wizardLabel1" style="color:#1e3c72;">
                            <span class="step-circle active">1</span> Marca
                        </span>
                        <span class="step-label" id="wizardLabel2" style="color:#adb5bd;">
                            <span class="step-circle">2</span> Categoría
                        </span>
                        <span class="step-label" id="wizardLabel3" style="color:#adb5bd;">
                            <span class="step-circle">3</span> Modelo
                        </span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" id="wizardProgressBar" role="progressbar" style="width: 33%;"></div>
                    </div>
                </div>

                <!-- PASO 1: MARCA -->
                <div class="step-content" id="wizardStep1">
                    <div class="text-center mb-4">
                        <h6 style="color:#1e3c72; font-weight:600;">Seleccionar o Crear Marca</h6>
                        <p class="text-muted small">Puedes elegir una marca existente o crear una nueva</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card institucion-card h-100 active" id="cardMarcaExistente">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="1.8" fill="none" style="width:48px;height:48px;">
                                            <rect x="4" y="8" width="16" height="12" rx="1"/>
                                        </svg>
                                    </div>
                                    <h5 style="color:#1e3c72; font-weight:600;">Seleccionar Existente</h5>
                                    <p class="text-muted small">Usar una marca ya registrada</p>
                                    <div class="mt-2"><input type="radio" name="wizard_marca_tipo" value="existente" checked></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card institucion-card h-100" id="cardMarcaNueva">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <svg viewBox="0 0 24 24" stroke="#6c757d" stroke-width="1.8" fill="none" style="width:48px;height:48px;">
                                            <line x1="12" y1="5" x2="12" y2="19"/>
                                            <line x1="5" y1="12" x2="19" y2="12"/>
                                        </svg>
                                    </div>
                                    <h5 style="color:#495057;">Crear Nueva</h5>
                                    <p class="text-muted small">Registrar una marca no listada</p>
                                    <div class="mt-2"><input type="radio" name="wizard_marca_tipo" value="nueva"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3" id="wizardMarcaSelectContainer">
                        <label class="form-label fw-bold">Seleccionar Marca</label>
                        <select class="form-select" id="wizardMarcaSelect">
                            <option value="">Cargando marcas...</option>
                        </select>
                    </div>
                    <div class="mt-3" id="wizardMarcaNuevaContainer" style="display: none;">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="form-label fw-bold">Nombre de la Marca <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="wizardMarcaNombre" placeholder="Ej: Dell, HP, Lenovo...">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control" id="wizardMarcaDescripcion" rows="2" placeholder="Descripción opcional"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" id="wizardBtnSoloMarca">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Solo crear marca
                        </button>
                        <button type="button" class="btn btn-primary-dark" id="wizardBtnIrPaso2">
                            Siguiente
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="margin-left:4px;">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- PASO 2: CATEGORÍA -->
                <div class="step-content" id="wizardStep2" style="display: none;">
                    <div class="text-center mb-4">
                        <h6 style="color:#1e3c72; font-weight:600;">Seleccionar o Crear Categoría</h6>
                        <p class="text-muted small">La categoría es global (aplica para todas las marcas)</p>
                        <div class="alert alert-info py-2 mt-2" style="font-size:0.85rem;">
                            <strong>Marca:</strong> <span id="wizardMarcaSeleccionadaLabel">(Ninguna)</span>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card institucion-card h-100 active" id="cardCategoriaExistente">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <svg viewBox="0 0 24 24" stroke="#1e3c72" stroke-width="1.8" fill="none" style="width:48px;height:48px;">
                                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                                        </svg>
                                    </div>
                                    <h5 style="color:#1e3c72; font-weight:600;">Seleccionar Existente</h5>
                                    <p class="text-muted small">Usar una categoría ya registrada</p>
                                    <div class="mt-2"><input type="radio" name="wizard_categoria_tipo" value="existente" checked></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card institucion-card h-100" id="cardCategoriaNueva">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <svg viewBox="0 0 24 24" stroke="#6c757d" stroke-width="1.8" fill="none" style="width:48px;height:48px;">
                                            <line x1="12" y1="5" x2="12" y2="19"/>
                                            <line x1="5" y1="12" x2="19" y2="12"/>
                                        </svg>
                                    </div>
                                    <h5 style="color:#495057;">Crear Nueva</h5>
                                    <p class="text-muted small">Registrar una categoría no listada</p>
                                    <div class="mt-2"><input type="radio" name="wizard_categoria_tipo" value="nueva"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3" id="wizardCategoriaSelectContainer">
                        <label class="form-label fw-bold">Seleccionar Categoría</label>
                        <select class="form-select" id="wizardCategoriaSelect">
                            <option value="">Cargando categorías...</option>
                        </select>
                    </div>
                    <div class="mt-3" id="wizardCategoriaNuevaContainer" style="display: none;">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="form-label fw-bold">Nombre de la Categoría <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="wizardCategoriaNombre" placeholder="Ej: Laptop, Impresora, Monitor...">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control" id="wizardCategoriaDescripcion" rows="2" placeholder="Descripción opcional"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between">
                        <div>
                            <button type="button" class="btn btn-outline-secondary" id="wizardBtnAtras1">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                                    <polyline points="15 18 9 12 15 6"/>
                                </svg>
                                Atrás
                            </button>
                            <button type="button" class="btn btn-outline-secondary ms-2" id="wizardBtnSoloCategoria">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Solo crear categoría
                            </button>
                        </div>
                        <button type="button" class="btn btn-primary-dark" id="wizardBtnIrPaso3">
                            Siguiente
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="margin-left:4px;">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- PASO 3: MODELO -->
                <div class="step-content" id="wizardStep3" style="display: none;">
                    <div class="text-center mb-4">
                        <h6 style="color:#1e3c72; font-weight:600;">Registrar el Modelo</h6>
                        <p class="text-muted small">Complete los datos del modelo para finalizar</p>
                        <div class="alert alert-success py-2 mt-2" style="font-size:0.85rem;">
                            <strong>Marca:</strong> <span id="wizardMarcaFinalLabel">(Ninguna)</span> &nbsp;|&nbsp;
                            <strong>Categoría:</strong> <span id="wizardCategoriaFinalLabel">(Ninguna)</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Marca</label>
                            <input type="text" class="form-control" id="wizardModeloMarca" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Categoría</label>
                            <input type="text" class="form-control" id="wizardModeloCategoria" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Nombre del Modelo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="wizardModeloNombre" placeholder="Ej: Latitude 5540, EliteBook 840...">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea class="form-control" id="wizardModeloDescripcion" rows="2" placeholder="Descripción del modelo..."></textarea>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" id="wizardBtnAtras2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                            Atrás
                        </button>
                        <button type="button" class="btn btn-success" id="wizardBtnGuardarModelo">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="margin-right:4px;">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                            </svg>
                            Guardar Equipo Completo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETALLE -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleLabel">Detalle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">Cargando...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar?</p>
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

@endsection

@section('scripts')
    @vite(['resources/js/admin-equipos.js'])
@endsection