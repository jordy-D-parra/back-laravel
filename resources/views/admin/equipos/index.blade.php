@extends('layouts.dashboard')

@section('title', 'Catálogo de Equipos')

@section('styles')
    @vite(['resources/css/admin-equipos.css'])
    <style>
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
        .badge-activo {
            color: #0f172a !important;
        }
        .badge-inactivo {
            color: #0f172a !important;
        }
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
        .detail-card-body {
            padding: 1.25rem;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
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
        .detail-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .detail-chip {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            background: #f8f9fc;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #1e3c72;
            transition: all 0.2s ease;
        }
        .detail-chip:hover {
            border-color: #1e3c72;
            box-shadow: 0 2px 8px rgba(30, 60, 114, 0.1);
        }
        .detail-chip small {
            font-size: 0.65rem;
            color: #6c757d;
            margin-top: 2px;
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
            transition: opacity 0.2s;
        }
        .modal-header .btn-close:hover {
            opacity: 1;
        }
        .modal-title {
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .modal-title svg {
            stroke: white;
        }
        .modal-body {
            padding: 1.75rem;
            max-height: 70vh;
            overflow-y: auto;
        }
        .modal-body::-webkit-scrollbar {
            width: 6px;
        }
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
        .modal-footer .btn:hover {
            transform: translateY(-1px);
        }
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
        .componente-modelo-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: #f8f9fc;
        }
        .componente-existente-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
        }
        .arbol-nodo {
            margin-bottom: 0.25rem;
        }
        .arbol-nodo-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 0.8rem;
            background: #f8f9fc;
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 8px;
            flex-wrap: wrap;
        }
        .arbol-nodo-header:hover {
            background: #eef3fc;
        }
        .arbol-raiz > .arbol-nodo-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            font-weight: 500;
        }
        .arbol-raiz > .arbol-nodo-header:hover {
            background: linear-gradient(135deg, #152a50 0%, #1e3c72 100%);
        }
        .arbol-raiz > .arbol-nodo-header .arbol-toggle,
        .arbol-raiz > .arbol-nodo-header .arbol-icon svg {
            color: white;
            stroke: white;
        }
        .arbol-raiz > .arbol-nodo-header .arbol-nombre {
            color: white;
        }
        .arbol-raiz > .arbol-nodo-header .arbol-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .arbol-toggle {
            font-size: 0.7rem;
            transition: transform 0.25s ease;
            color: #1e3c72;
            width: 18px;
            text-align: center;
            font-weight: bold;
        }
        .arbol-toggle.collapsed {
            transform: rotate(-90deg);
        }
        .arbol-icon svg {
            stroke: #1e3c72;
            width: 16px;
            height: 16px;
        }
        .arbol-nombre {
            font-weight: 500;
            color: #1e3c72;
            flex: 1;
            font-size: 0.85rem;
        }
        .arbol-sub {
            font-size: 0.7rem;
            color: #6c757d;
            margin-left: 0.25rem;
        }
        .arbol-badge {
            padding: 0.15rem 0.6rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            background: #eef3fc;
            color: #1e3c72;
        }
        .arbol-acciones {
            display: flex;
            gap: 0.25rem;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .arbol-nodo-header:hover .arbol-acciones {
            opacity: 1;
        }
        .arbol-hijos {
            margin-left: 1.5rem;
            padding-left: 0.75rem;
            border-left: 2px solid #e9ecef;
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.25s ease, padding 0.2s;
            max-height: 2000px;
            opacity: 1;
        }
        .arbol-hijos.collapsed {
            max-height: 0;
            opacity: 0;
            padding-left: 0;
            border-left-color: transparent;
            margin-left: 0;
        }
        .arbol-hoja > .arbol-nodo-header {
            border-left: 2px solid #cbd5e1;
        }
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .pagination-info {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .pagination-btns {
            display: flex;
            gap: 4px;
        }
        .pagination-btn {
            width: 34px;
            height: 34px;
            border: 1px solid #e9ecef;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            color: #1e3c72;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pagination-btn:hover:not(.disabled):not(.active) {
            background: #1e3c72;
            color: white;
            border-color: #1e3c72;
        }
        .pagination-btn.active {
            background: #1e3c72;
            color: white;
            border-color: #1e3c72;
            font-weight: 600;
        }
        .pagination-btn.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        .pagination-ellipsis {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
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
        .stat-info {
            position: relative;
            z-index: 1;
        }
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
        .stat-card-mini:hover .stat-icon-circle svg {
            stroke: white;
        }
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
        @media (max-width: 992px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }
        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
            .nav-tabs-custom .nav-link {
                padding: 0.5rem 1rem;
                font-size: 0.75rem;
            }
            .filters-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .filters-bar .d-flex {
                flex-direction: column;
            }
            .filters-bar .input-group {
                max-width: 100% !important;
            }
            .modal-body {
                padding: 1.25rem;
            }
            .modal-header {
                padding: 1rem 1.25rem;
            }
            .table-container {
                overflow-x: auto;
            }
            .table {
                min-width: 600px;
            }
            .arbol-hijos {
                margin-left: 0.75rem;
                padding-left: 0.5rem;
            }
            .arbol-nodo-header {
                flex-wrap: wrap;
                gap: 0.3rem;
            }
            .arbol-acciones {
                opacity: 1;
                margin-left: auto;
            }
            .detail-card-header {
                flex-wrap: wrap;
            }
        }
        @media (max-width: 576px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
            .stat-number {
                font-size: 1.5rem;
            }
            .table thead {
                display: none;
            }
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
            .table tbody td:last-child {
                border-bottom: none;
            }
            .modal-footer {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            .modal-footer .btn {
                flex: 1;
                min-width: 80px;
            }
            .pagination-bar {
                flex-direction: column;
                align-items: center;
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
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#marcas">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <rect x="4" y="8" width="16" height="12" rx="1"/>
                </svg>
                Marcas <span class="tab-badge">{{ $totalMarcas }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#categorias">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                </svg>
                Categorías <span class="tab-badge">{{ $totalCategorias }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#modelos">
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
                        <input type="text" class="form-control border-start-0" id="buscarMarcas" placeholder="Buscar marca..." oninput="window.buscarMarcas()">
                    </div>
                </div>
                @if(auth()->user()->hasPermission('crear-marca'))
                <button class="btn btn-primary-dark" onclick="abrirModalMarca()" style="color:#fff;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nueva Marca
                </button>
                @endif
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

        <!-- TAB CATEGORÍAS -->
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
                        <input type="text" class="form-control border-start-0" id="buscarCategorias" placeholder="Buscar categoría..." oninput="window.buscarCategorias()">
                    </div>
                </div>
                @if(auth()->user()->hasPermission('crear-categoria-equipo'))
                <button class="btn btn-primary-dark" onclick="abrirModalCategoria()" style="color:#fff;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nueva Categoría
                </button>
                @endif
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
                        <input type="text" class="form-control border-start-0" id="buscarModelos" placeholder="Buscar modelo..." oninput="window.buscarModelos()">
                    </div>
                </div>
                @if(auth()->user()->hasPermission('crear-modelo'))
                <button class="btn btn-primary-dark" onclick="abrirModalModelo()" style="color:#fff;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo Modelo
                </button>
                @endif
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

<!-- MODAL MARCA -->
<div class="modal fade" id="modalMarca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMarcaLabel">
                    <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;display:inline;margin-right:8px;">
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
                        <input type="text" class="form-control" id="marca_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="marca_descripcion" name="descripcion" rows="3"></textarea>
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

<!-- MODAL CATEGORIA -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCategoriaLabel">
                    <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;display:inline;margin-right:8px;">
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
                        <input type="text" class="form-control" id="categoria_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="categoria_descripcion" name="descripcion" rows="3"></textarea>
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

<!-- MODAL MODELO -->
<div class="modal fade" id="modalModelo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalModeloLabel">
                    <svg viewBox="0 0 24 24" stroke="white" stroke-width="2" fill="none" style="width:20px;height:20px;display:inline;margin-right:8px;">
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
                    <h6 class="fw-bold mb-3" style="color: #1e3c72;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                            <rect x="4" y="4" width="16" height="16" rx="2"/>
                        </svg>
                        Datos del Modelo
                    </h6>
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
                        <input type="text" class="form-control" id="modelo_nombre" name="nombre" required placeholder="Ej: Latitude 5540">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="modelo_descripcion" name="descripcion" rows="2" placeholder="Descripción general del modelo"></textarea>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color: #1e3c72;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            Componentes del Modelo
                        </h6>
                        @if(auth()->user()->hasPermission('editar-modelo'))
                        <button type="button" class="btn btn-sm btn-outline-primary-dark" onclick="agregarComponenteModelo()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Agregar Componente
                        </button>
                        @endif
                    </div>
                    <div id="componentesModeloContainer">
                        <div class="text-center text-muted py-3" id="sinComponentesMsg">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-2 opacity-50">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            <p class="mb-0">No hay componentes agregados.</p>
                            <p class="small">Haga clic en "Agregar Componente" para añadir uno.</p>
                        </div>
                    </div>
                    <div id="componentesExistentesContainer" style="display:none;">
                        <h6 class="fw-bold mb-2 mt-3" style="color: #1e3c72;">Componentes Registrados</h6>
                        <div id="listaComponentesExistentes"></div>
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

<!-- Template para componente nuevo -->
<template id="templateComponenteModelo">
    <div class="componente-modelo-item border rounded p-3 mb-2 bg-light">
        <div class="row align-items-end">
            <div class="col-md-4 mb-2">
                <label class="form-label small">Tipo <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm comp-tipo" required>
                    <option value="">Seleccionar...</option>
                    <option value="RAM">RAM</option>
                    <option value="Disco">Disco</option>
                    <option value="Batería">Batería</option>
                    <option value="Cargador">Cargador</option>
                    <option value="Pantalla">Pantalla</option>
                    <option value="Teclado">Teclado</option>
                    <option value="Mouse">Mouse</option>
                    <option value="Procesador">Procesador</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Cable">Cable</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small">Descripción <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm comp-descripcion" placeholder="Memoria RAM DDR4" required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small">Capacidad Máxima</label>
                <input type="text" class="form-control form-control-sm comp-capacidad" placeholder="8GB, 512GB, 65W">
            </div>
            <div class="col-md-1 mb-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.componente-modelo-item').remove(); verificarSinComponentes()" title="Eliminar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

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
<script>
// ============================================================
// ADMIN EQUIPOS - BÚSQUEDA EN TIEMPO REAL FUNCIONAL
// ============================================================

// Función global para búsqueda de marcas
window.buscarMarcas = function() {
    const buscar = document.getElementById('buscarMarcas')?.value || '';
    const tbody = document.getElementById('tablaMarcas');

    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>';
    }

    fetch('/admin/equipos/marcas?buscar=' + encodeURIComponent(buscar), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(response) { return response.json(); })
    .then(function(response) {
        if (response.success) {
            renderizarMarcas(response.data, buscar);
        } else {
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error al cargar marcas</td></tr>';
            }
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error de conexión</td></tr>';
        }
    });
};

// Función global para búsqueda de categorías
window.buscarCategorias = function() {
    const buscar = document.getElementById('buscarCategorias')?.value || '';
    const tbody = document.getElementById('tablaCategorias');

    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>';
    }

    fetch('/admin/equipos/categorias?buscar=' + encodeURIComponent(buscar), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(response) { return response.json(); })
    .then(function(response) {
        if (response.success) {
            renderizarCategorias(response.data, buscar);
        } else {
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error al cargar categorías</td></tr>';
            }
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error de conexión</td></tr>';
        }
    });
};

// Función global para búsqueda de modelos
window.buscarModelos = function() {
    const buscar = document.getElementById('buscarModelos')?.value || '';
    const tbody = document.getElementById('tablaModelos');

    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>';
    }

    fetch('/admin/equipos/modelos?buscar=' + encodeURIComponent(buscar), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(response) { return response.json(); })
    .then(function(response) {
        if (response.success) {
            renderizarModelos(response.data, buscar);
        } else {
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Error al cargar modelos</td></tr>';
            }
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Error de conexión</td></tr>';
        }
    });
};

function renderizarMarcas(data, buscar) {
    const tbody = document.getElementById('tablaMarcas');
    if (!tbody) return;

    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No se encontraron marcas</td></tr>';
        return;
    }

    var html = '';
    for (var i = 0; i < data.length; i++) {
        var marca = data[i];
        var nombre = marca.nombre;
        if (buscar) {
            var regex = new RegExp('(' + buscar.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
            nombre = nombre.replace(regex, '<span class="highlight">$1</span>');
        }

        html += `
            <tr>
                <td><span class="fw-medium" style="color:#1e3c72">${nombre}</span></td>
                <td>${marca.descripcion ? marca.descripcion.substring(0, 50) : '—'}</td>
                <td><span class="badge bg-info text-dark">${marca.modelos_count || 0}</span></td>
                <td><span class="badge ${marca.activo ? 'bg-success' : 'bg-danger'}">${marca.activo ? 'Activa' : 'Inactiva'}</span></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary-dark" onclick="verMarca(${marca.id})" title="Ver">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-primary-dark" onclick="editarMarca(${marca.id})" title="Editar">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-primary-dark" onclick="toggleMarca(${marca.id})" title="Cambiar estado">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('marca', ${marca.id}, '${marca.nombre.replace(/'/g, "\\'")}', ${marca.modelos_count || 0})" title="Eliminar">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                    </button>
                </td>
            </tr>
        `;
    }
    tbody.innerHTML = html;
}

function renderizarCategorias(data, buscar) {
    const tbody = document.getElementById('tablaCategorias');
    if (!tbody) return;

    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No se encontraron categorías</td></tr>';
        return;
    }

    var html = '';
    for (var i = 0; i < data.length; i++) {
        var cat = data[i];
        var nombre = cat.nombre;
        if (buscar) {
            var regex = new RegExp('(' + buscar.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
            nombre = nombre.replace(regex, '<span class="highlight">$1</span>');
        }

        html += `
            <tr>
                <td><span class="fw-medium" style="color:#1e3c72">${nombre}</span></td>
                <td>${cat.descripcion ? cat.descripcion.substring(0, 50) : '—'}</td>
                <td><span class="badge bg-info text-dark">${cat.modelos_count || 0}</span></td>
                <td><span class="badge ${cat.activo ? 'bg-success' : 'bg-danger'}">${cat.activo ? 'Activa' : 'Inactiva'}</span></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary-dark" onclick="verCategoria(${cat.id})" title="Ver">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-primary-dark" onclick="editarCategoria(${cat.id})" title="Editar">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-primary-dark" onclick="toggleCategoria(${cat.id})" title="Cambiar estado">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('categoria', ${cat.id}, '${cat.nombre.replace(/'/g, "\\'")}', ${cat.modelos_count || 0})" title="Eliminar">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                    </button>
                </td>
            </tr>
        `;
    }
    tbody.innerHTML = html;
}

function renderizarModelos(data, buscar) {
    const tbody = document.getElementById('tablaModelos');
    if (!tbody) return;

    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron modelos</td></tr>';
        return;
    }

    var html = '';
    for (var i = 0; i < data.length; i++) {
        var m = data[i];
        var nombre = m.nombre;
        if (buscar) {
            var regex = new RegExp('(' + buscar.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
            nombre = nombre.replace(regex, '<span class="highlight">$1</span>');
        }

        html += `
            <tr>
                <td><span class="fw-medium" style="color:#1e3c72">${nombre}</span></td>
                <td>${m.marca ? m.marca.nombre : 'N/A'}</td>
                <td>${m.categoria ? m.categoria.nombre : 'N/A'}</td>
                <td>${m.descripcion ? m.descripcion.substring(0, 40) : '—'}</td>
                <td><span class="badge ${m.activo ? 'bg-success' : 'bg-danger'}">${m.activo ? 'Activo' : 'Inactivo'}</span></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary-dark" onclick="verModelo(${m.id})" title="Ver">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-primary-dark" onclick="editarModelo(${m.id})" title="Editar">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-primary-dark" onclick="toggleModelo(${m.id})" title="Cambiar estado">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('modelo', ${m.id}, '${m.nombre.replace(/'/g, "\\'")}', 0)" title="Eliminar">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                    </button>
                </td>
            </tr>
        `;
    }
    tbody.innerHTML = html;
}

// ============================================================
// FUNCIONES DE ACCIÓN
// ============================================================
window.verMarca = function(id) {
    fetch('/admin/equipos/marcas/' + id, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var d = response.data;
            var html = `
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-icon bg-primary-dark">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <rect x="4" y="8" width="16" height="12" rx="1"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="mb-0">${d.nombre}</h5>
                            <span class="badge ${d.activo ? 'bg-success' : 'bg-danger'}">${d.activo ? 'Activa' : 'Inactiva'}</span>
                        </div>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-row">
                            <span class="detail-label">Descripción</span>
                            <span class="detail-value">${d.descripcion || 'Sin descripción'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Modelos</span>
                            <span class="detail-value badge bg-info text-dark">${d.modelos_count || 0}</span>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('modalDetalleLabel').textContent = 'Detalle de Marca';
            document.getElementById('detalleContenido').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        }
    });
};

window.editarMarca = function(id) {
    var modal = new bootstrap.Modal(document.getElementById('modalMarca'));
    document.getElementById('modalMarcaLabel').textContent = 'Editar Marca';
    document.getElementById('formMethodMarca').value = 'PUT';
    document.getElementById('marcaId').value = id;

    fetch('/admin/equipos/marcas/' + id, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            document.getElementById('marca_nombre').value = response.data.nombre;
            document.getElementById('marca_descripcion').value = response.data.descripcion || '';
        }
    });
    modal.show();
};

window.toggleMarca = function(id) {
    fetch('/admin/equipos/marcas/' + id + '/toggle', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            window.buscarMarcas();
        }
    });
};

window.verCategoria = function(id) {
    fetch('/admin/equipos/categorias/' + id, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var d = response.data;
            var html = `
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-icon bg-primary-dark">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="mb-0">${d.nombre}</h5>
                            <span class="badge ${d.activo ? 'bg-success' : 'bg-danger'}">${d.activo ? 'Activa' : 'Inactiva'}</span>
                        </div>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-row">
                            <span class="detail-label">Descripción</span>
                            <span class="detail-value">${d.descripcion || 'Sin descripción'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Modelos</span>
                            <span class="detail-value badge bg-info text-dark">${d.modelos_count || 0}</span>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('modalDetalleLabel').textContent = 'Detalle de Categoría';
            document.getElementById('detalleContenido').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        }
    });
};

window.editarCategoria = function(id) {
    var modal = new bootstrap.Modal(document.getElementById('modalCategoria'));
    document.getElementById('modalCategoriaLabel').textContent = 'Editar Categoría';
    document.getElementById('formMethodCategoria').value = 'PUT';
    document.getElementById('categoriaId').value = id;

    fetch('/admin/equipos/categorias/' + id, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            document.getElementById('categoria_nombre').value = response.data.nombre;
            document.getElementById('categoria_descripcion').value = response.data.descripcion || '';
        }
    });
    modal.show();
};

window.toggleCategoria = function(id) {
    fetch('/admin/equipos/categorias/' + id + '/toggle', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            window.buscarCategorias();
        }
    });
};

window.verModelo = function(id) {
    fetch('/admin/equipos/modelos/' + id, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var d = response.data;
            var html = `
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-icon bg-primary-dark">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <rect x="4" y="4" width="16" height="16" rx="2"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="mb-0">${d.nombre}</h5>
                            <span class="badge ${d.activo ? 'bg-success' : 'bg-danger'}">${d.activo ? 'Activo' : 'Inactivo'}</span>
                        </div>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-row">
                            <span class="detail-label">Marca</span>
                            <span class="detail-value">${d.marca ? d.marca.nombre : 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Categoría</span>
                            <span class="detail-value">${d.categoria ? d.categoria.nombre : 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Descripción</span>
                            <span class="detail-value">${d.descripcion || 'Sin descripción'}</span>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('modalDetalleLabel').textContent = 'Detalle de Modelo';
            document.getElementById('detalleContenido').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        }
    });
};

window.editarModelo = function(id) {
    var modal = new bootstrap.Modal(document.getElementById('modalModelo'));
    document.getElementById('modalModeloLabel').textContent = 'Editar Modelo';
    document.getElementById('formMethodModelo').value = 'PUT';
    document.getElementById('modeloId').value = id;

    fetch('/admin/equipos/modelos/' + id, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var d = response.data;
            setTimeout(function() {
                document.getElementById('modelo_marca_id').value = d.marca_id;
                document.getElementById('modelo_categoria_id').value = d.categoria_id;
            }, 300);
            document.getElementById('modelo_nombre').value = d.nombre;
            document.getElementById('modelo_descripcion').value = d.descripcion || '';
        }
    });
    modal.show();
};

window.toggleModelo = function(id) {
    fetch('/admin/equipos/modelos/' + id + '/toggle', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            window.buscarModelos();
        }
    });
};

window.confirmarEliminar = function(tipo, id, nombre, tieneDependencias) {
    if (tipo !== 'modelo' && tieneDependencias > 0) {
        alert('No se puede eliminar porque tiene elementos asociados');
        return;
    }
    document.getElementById('deleteNombre').textContent = nombre;
    window.elementoAEliminar = { tipo: tipo, id: id };
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
};

// Confirmar eliminación
document.getElementById('btnConfirmarEliminar')?.addEventListener('click', function() {
    if (!window.elementoAEliminar) return;
    var tipo = window.elementoAEliminar.tipo;
    var id = window.elementoAEliminar.id;
    var url = '';
    if (tipo === 'marca') url = '/admin/equipos/marcas/' + id;
    else if (tipo === 'categoria') url = '/admin/equipos/categorias/' + id;
    else if (tipo === 'modelo') url = '/admin/equipos/modelos/' + id;

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
        if (response.success) {
            if (tipo === 'marca') window.buscarMarcas();
            else if (tipo === 'categoria') window.buscarCategorias();
            else if (tipo === 'modelo') window.buscarModelos();
        }
        window.elementoAEliminar = null;
    });
});

// ============================================================
// FORMULARIOS
// ============================================================
document.getElementById('formMarca')?.addEventListener('submit', function(e) {
    e.preventDefault();
    var id = document.getElementById('marcaId').value;
    var method = document.getElementById('formMethodMarca').value;
    var url = method === 'PUT' ? '/admin/equipos/marcas/' + id : '/admin/equipos/marcas';
    var formData = new FormData(this);
    if (method === 'PUT') formData.append('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalMarca')).hide();
            window.buscarMarcas();
        }
    });
});

document.getElementById('formCategoria')?.addEventListener('submit', function(e) {
    e.preventDefault();
    var id = document.getElementById('categoriaId').value;
    var method = document.getElementById('formMethodCategoria').value;
    var url = method === 'PUT' ? '/admin/equipos/categorias/' + id : '/admin/equipos/categorias';
    var formData = new FormData(this);
    if (method === 'PUT') formData.append('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalCategoria')).hide();
            window.buscarCategorias();
        }
    });
});

document.getElementById('formModelo')?.addEventListener('submit', function(e) {
    e.preventDefault();
    var id = document.getElementById('modeloId').value;
    var method = document.getElementById('formMethodModelo').value;
    var url = method === 'PUT' ? '/admin/equipos/modelos/' + id : '/admin/equipos/modelos';
    var formData = new FormData(this);
    if (method === 'PUT') formData.append('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalModelo')).hide();
            window.buscarModelos();
        }
    });
});

// ============================================================
// MODALES - ABRIR
// ============================================================
function abrirModalMarca(id) {
    var modal = new bootstrap.Modal(document.getElementById('modalMarca'));
    var form = document.getElementById('formMarca');
    form.reset();

    if (id) {
        document.getElementById('modalMarcaLabel').textContent = 'Editar Marca';
        document.getElementById('formMethodMarca').value = 'PUT';
        document.getElementById('marcaId').value = id;

        fetch('/admin/equipos/marcas/' + id, { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(response) {
            if (response.success) {
                document.getElementById('marca_nombre').value = response.data.nombre;
                document.getElementById('marca_descripcion').value = response.data.descripcion || '';
            }
        });
    } else {
        document.getElementById('modalMarcaLabel').textContent = 'Nueva Marca';
        document.getElementById('formMethodMarca').value = 'POST';
        document.getElementById('marcaId').value = '';
    }

    modal.show();
}

function abrirModalCategoria(id) {
    var modal = new bootstrap.Modal(document.getElementById('modalCategoria'));
    var form = document.getElementById('formCategoria');
    form.reset();

    if (id) {
        document.getElementById('modalCategoriaLabel').textContent = 'Editar Categoría';
        document.getElementById('formMethodCategoria').value = 'PUT';
        document.getElementById('categoriaId').value = id;

        fetch('/admin/equipos/categorias/' + id, { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(response) {
            if (response.success) {
                document.getElementById('categoria_nombre').value = response.data.nombre;
                document.getElementById('categoria_descripcion').value = response.data.descripcion || '';
            }
        });
    } else {
        document.getElementById('modalCategoriaLabel').textContent = 'Nueva Categoría';
        document.getElementById('formMethodCategoria').value = 'POST';
        document.getElementById('categoriaId').value = '';
    }

    modal.show();
}

function abrirModalModelo(id) {
    var modal = new bootstrap.Modal(document.getElementById('modalModelo'));
    var form = document.getElementById('formModelo');
    form.reset();

    document.getElementById('formMethodModelo').value = 'POST';
    document.getElementById('modeloId').value = '';
    document.getElementById('modalModeloLabel').textContent = 'Nuevo Modelo';

    cargarSelectsModelo();

    if (id) {
        document.getElementById('modalModeloLabel').textContent = 'Editar Modelo';
        document.getElementById('formMethodModelo').value = 'PUT';
        document.getElementById('modeloId').value = id;

        fetch('/admin/equipos/modelos/' + id, { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(response) {
            if (response.success) {
                var d = response.data;
                setTimeout(function() {
                    document.getElementById('modelo_marca_id').value = d.marca_id;
                    document.getElementById('modelo_categoria_id').value = d.categoria_id;
                }, 300);
                document.getElementById('modelo_nombre').value = d.nombre;
                document.getElementById('modelo_descripcion').value = d.descripcion || '';
                cargarComponentesExistentes(id);
            }
        });
    }

    modal.show();
}

// Exponer funciones globales
window.abrirModalMarca = abrirModalMarca;
window.abrirModalCategoria = abrirModalCategoria;
window.abrirModalModelo = abrirModalModelo;

// ============================================================
// COMPONENTES DEL MODELO
// ============================================================
function agregarComponenteModelo() {
    var template = document.getElementById('templateComponenteModelo');
    if (!template) return;

    var clone = template.content.cloneNode(true);
    var container = document.getElementById('componentesModeloContainer');
    if (!container) return;

    container.appendChild(clone);

    var sinMsg = document.getElementById('sinComponentesMsg');
    if (sinMsg) sinMsg.style.display = 'none';
}

function verificarSinComponentes() {
    var container = document.getElementById('componentesModeloContainer');
    if (!container) return;

    var items = container.querySelectorAll('.componente-modelo-item');
    var sinMsg = document.getElementById('sinComponentesMsg');

    if (items.length === 0 && sinMsg) {
        sinMsg.style.display = 'block';
    }
}

function recolectarComponentes() {
    var componentes = [];
    var items = document.querySelectorAll('#componentesModeloContainer .componente-modelo-item');

    items.forEach(function(item) {
        var tipo = item.querySelector('.comp-tipo')?.value || '';
        var descripcion = item.querySelector('.comp-descripcion')?.value || '';

        if (tipo && descripcion) {
            componentes.push({
                tipo: tipo,
                descripcion: descripcion,
                capacidad: item.querySelector('.comp-capacidad')?.value || null,
                requerido: true
            });
        }
    });

    return componentes;
}

function cargarComponentesExistentes(modeloId) {
    fetch('/admin/equipos/modelos/' + modeloId + '/componentes', {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success && response.data && response.data.length > 0) {
            var existentes = document.getElementById('componentesExistentesContainer');
            if (existentes) existentes.style.display = 'block';

            var lista = document.getElementById('listaComponentesExistentes');
            if (lista) {
                var html = '';
                for (var i = 0; i < response.data.length; i++) {
                    var c = response.data[i];
                    html += `
                        <div class="componente-existente-item border rounded p-2 mb-2 d-flex justify-content-between align-items-center bg-white">
                            <div>
                                <strong>${c.tipo}</strong> - ${c.descripcion}
                                ${c.capacidad ? '<span class="badge bg-secondary ms-2">' + c.capacidad + '</span>' : ''}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarComponenteExistente(${c.id}, this)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                            </button>
                        </div>
                    `;
                }
                lista.innerHTML = html;
            }
        }
    });
}

function eliminarComponenteExistente(componenteId, btn) {
    var modeloId = document.getElementById('modeloId')?.value || '';
    if (!modeloId) return;

    if (!confirm('¿Eliminar este componente?')) return;

    fetch('/admin/equipos/modelos/' + modeloId + '/componentes/' + componenteId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            btn.closest('.componente-existente-item').remove();

            var lista = document.getElementById('listaComponentesExistentes');
            if (lista && lista.children.length === 0) {
                var existentes = document.getElementById('componentesExistentesContainer');
                if (existentes) existentes.style.display = 'none';
            }
        }
    });
}

function guardarComponentesModelo(modeloId, componentes) {
    if (!componentes || componentes.length === 0) {
        return Promise.resolve();
    }

    var promesas = [];
    for (var i = 0; i < componentes.length; i++) {
        var comp = componentes[i];
        promesas.push(
            fetch('/admin/equipos/modelos/' + modeloId + '/componentes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(comp)
            })
            .then(function(r) { return r.json(); })
        );
    }

    return Promise.all(promesas);
}

function guardarModelo() {
    var id = document.getElementById('modeloId').value;
    var method = document.getElementById('formMethodModelo').value;
    var url = method === 'PUT' ? '/admin/equipos/modelos/' + id : '/admin/equipos/modelos';
    var formData = new FormData(document.getElementById('formModelo'));
    if (method === 'PUT') formData.append('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var modeloId = id || (response.data && response.data.id ? response.data.id : null);
            var componentes = recolectarComponentes();

            if (modeloId && componentes.length > 0) {
                guardarComponentesModelo(modeloId, componentes)
                    .then(function() {
                        bootstrap.Modal.getInstance(document.getElementById('modalModelo')).hide();
                        window.buscarModelos();
                    });
            } else if (modeloId) {
                bootstrap.Modal.getInstance(document.getElementById('modalModelo')).hide();
                window.buscarModelos();
            }
        }
    });
}

// ============================================================
// SELECTS PARA MODELOS
// ============================================================
function cargarSelectsModelo() {
    // Marcas
    fetch('/admin/equipos/marcas-list', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var select = document.getElementById('modelo_marca_id');
            if (select) {
                select.innerHTML = '<option value="">Seleccionar marca...</option>';
                for (var i = 0; i < response.data.length; i++) {
                    var marca = response.data[i];
                    select.innerHTML += '<option value="' + marca.id + '">' + marca.nombre + '</option>';
                }
            }
        }
    });

    // Categorías
    fetch('/admin/equipos/categorias-list', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        if (response.success) {
            var select = document.getElementById('modelo_categoria_id');
            if (select) {
                select.innerHTML = '<option value="">Seleccionar categoría...</option>';
                for (var i = 0; i < response.data.length; i++) {
                    var cat = response.data[i];
                    select.innerHTML += '<option value="' + cat.id + '">' + cat.nombre + '</option>';
                }
            }
        }
    });
}

// ============================================================
// ÁRBOL JERÁRQUICO
// ============================================================
function cargarArbol() {
    var buscar = document.getElementById('buscarArbol')?.value?.toLowerCase() || '';
    var contenedor = document.getElementById('arbolContenedor');

    contenedor.innerHTML = '<div class="text-center py-4 text-muted">Cargando árbol...</div>';

    fetch('/admin/equipos/marcas', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        var marcas = response.data || [];
        contenedor.innerHTML = '<div class="arbol-container"></div>';
        var arbolContainer = contenedor.querySelector('.arbol-container');

        if (marcas.length > 0) {
            var cargadas = 0;

            for (var i = 0; i < marcas.length; i++) {
                var marca = marcas[i];
                (function(m) {
                    fetch('/admin/equipos/marcas/' + m.id, { headers: { 'Accept': 'application/json' } })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        if (result.success) {
                            var d = result.data;
                            var coincide = !buscar || d.nombre.toLowerCase().indexOf(buscar) >= 0;

                            if (coincide || !buscar) {
                                arbolContainer.innerHTML += renderNodoMarca(d);
                            }
                        }

                        cargadas++;
                        if (cargadas === marcas.length && arbolContainer.innerHTML === '') {
                            arbolContainer.innerHTML = '<p class="text-center py-4 text-muted">No se encontraron resultados</p>';
                        }
                    });
                })(marca);
            }
        } else {
            contenedor.innerHTML = '<p class="text-center py-4 text-muted">No hay marcas registradas</p>';
        }
    })
    .catch(function(error) {
        console.error('Error cargando árbol:', error);
        contenedor.innerHTML = '<p class="text-center py-4 text-danger">Error al cargar el árbol</p>';
    });
}

function renderNodoMarca(marca) {
    var html = `
        <div class="arbol-nodo arbol-raiz">
            <div class="arbol-nodo-header" onclick="toggleNodo(this)">
                <span class="arbol-toggle">▼</span>
                <span class="arbol-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="8" width="16" height="12" rx="1"/>
                    </svg>
                </span>
                <span class="arbol-nombre">${marca.nombre}</span>
                <span class="arbol-badge">${marca.modelos_count || 0} mod.</span>
            </div>
            <div class="arbol-hijos">
    `;

    if (marca.modelos && marca.modelos.length > 0) {
        for (var i = 0; i < marca.modelos.length; i++) {
            var modelo = marca.modelos[i];
            html += `
                <div class="arbol-nodo arbol-hoja">
                    <div class="arbol-nodo-header">
                        <span class="arbol-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="4" width="16" height="16" rx="2"/>
                            </svg>
                        </span>
                        <span class="arbol-nombre">${modelo.nombre}</span>
                        <span class="arbol-sub">${modelo.categoria ? modelo.categoria.nombre : ''}</span>
                    </div>
                </div>
            `;
        }
    } else {
        html += '<div class="ps-4 text-muted small">Sin modelos</div>';
    }

    html += `</div></div>`;
    return html;
}

function toggleNodo(header) {
    var hijos = header.nextElementSibling;
    var toggle = header.querySelector('.arbol-toggle');

    if (hijos) {
        hijos.classList.toggle('collapsed');
        toggle.classList.toggle('collapsed');
    }
}

function expandirTodo() {
    var hijos = document.querySelectorAll('.arbol-hijos');
    for (var i = 0; i < hijos.length; i++) {
        hijos[i].classList.remove('collapsed');
    }
}

function colapsarTodo() {
    var hijos = document.querySelectorAll('.arbol-hijos');
    for (var i = 0; i < hijos.length; i++) {
        hijos[i].classList.add('collapsed');
    }
}

// Exponer funciones
window.agregarComponenteModelo = agregarComponenteModelo;
window.verificarSinComponentes = verificarSinComponentes;
window.recolectarComponentes = recolectarComponentes;
window.cargarSelectsModelo = cargarSelectsModelo;
window.cargarArbol = cargarArbol;
window.renderNodoMarca = renderNodoMarca;
window.toggleNodo = toggleNodo;
window.expandirTodo = expandirTodo;
window.colapsarTodo = colapsarTodo;
window.eliminarComponenteExistente = eliminarComponenteExistente;
window.guardarComponentesModelo = guardarComponentesModelo;
window.guardarModelo = guardarModelo;

// ============================================================
// CARGA INICIAL
// ============================================================
window.buscarMarcas();
window.buscarCategorias();
window.buscarModelos();
cargarArbol();
cargarSelectsModelo();

console.log('✅ Módulo de equipos listo - Búsqueda en tiempo real funcionando');
</script>
@endsection