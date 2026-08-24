@extends('layouts.dashboard')

@section('title', 'Bandeja de Entrada')

@section('styles')
<style>
    /* ============================================================
       ESTILOS BANDEJA DE ENTRADA - VERSIÓN CORREGIDA
       ============================================================ */
    
    /* Reset de estilos para el header */
    .page-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
        border-radius: 16px !important;
        padding: 1.5rem 2rem !important;
        margin-bottom: 1.5rem !important;
        color: white !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 1rem !important;
    }

    .page-header h4 {
        color: white !important;
        font-weight: 700 !important;
        margin: 0 !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
    }

    .page-header h4 svg {
        stroke: white !important;
        fill: none !important;
    }

    .page-header p {
        color: rgba(255, 255, 255, 0.8) !important;
        font-size: 0.85rem !important;
        margin: 0 !important;
    }

    .page-header .btn-light {
        background: rgba(255, 255, 255, 0.15) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
        border-radius: 30px !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }

    .page-header .btn-light:hover {
        background: rgba(255, 255, 255, 0.25) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;
    }

    .page-header .btn-light svg {
        stroke: white !important;
    }

    /* Layout principal */
    .inbox-container {
        display: flex;
        gap: 0;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e9ecef;
        overflow: hidden;
        min-height: 600px;
    }

    /* Sidebar izquierdo (carpetas) */
    .inbox-sidebar {
        width: 240px;
        background: #f8f9fc;
        padding: 1.5rem 0;
        border-right: 1px solid #e9ecef;
        flex-shrink: 0;
    }

    .inbox-sidebar .sidebar-title {
        padding: 0 1.5rem 1rem 1.5rem;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #6c757d;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .inbox-sidebar .folder-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.6rem 1.5rem;
        color: #495057;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        border-left: 3px solid transparent;
        font-size: 0.9rem;
        background: transparent;
        width: 100%;
        text-align: left;
        border-right: none;
        border-top: none;
        border-bottom: none;
    }

    .inbox-sidebar .folder-item:hover {
        background: rgba(30, 60, 114, 0.05);
        color: #1e3c72;
    }

    .inbox-sidebar .folder-item.active {
        background: rgba(30, 60, 114, 0.08);
        color: #1e3c72;
        border-left-color: #1e3c72;
        font-weight: 600;
    }

    .inbox-sidebar .folder-item .folder-icon {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .inbox-sidebar .folder-item .folder-icon svg {
        width: 18px;
        height: 18px;
        stroke: currentColor;
        stroke-width: 1.8;
        fill: none;
    }

    .inbox-sidebar .folder-item .folder-name {
        flex: 1;
    }

    .inbox-sidebar .folder-item .badge-folder {
        margin-left: auto;
        background: #e9ecef;
        color: #495057;
        padding: 0.1rem 0.6rem;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        min-width: 20px;
        text-align: center;
    }

    .inbox-sidebar .folder-item .badge-folder.unread {
        background: #1e3c72;
        color: white;
    }

    /* Contenido principal */
    .inbox-content {
        flex: 1;
        padding: 1.5rem;
        min-width: 0;
        background: #ffffff;
    }

    /* Barra de herramientas */
    .inbox-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .inbox-toolbar .toolbar-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .inbox-toolbar .toolbar-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e3c72;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .inbox-toolbar .toolbar-title .count-badge {
        font-size: 0.7rem;
        font-weight: 600;
        background: #e9ecef;
        color: #495057;
        padding: 0.1rem 0.6rem;
        border-radius: 20px;
        margin-left: 0.5rem;
    }

    .inbox-toolbar .toolbar-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .inbox-toolbar .btn-toolbar {
        padding: 0.35rem 0.9rem;
        font-size: 0.8rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        background: white;
        color: #495057;
        transition: all 0.2s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .inbox-toolbar .btn-toolbar:hover {
        background: #f8f9fc;
        border-color: #1e3c72;
        color: #1e3c72;
    }

    .inbox-toolbar .btn-toolbar.primary {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        border: none;
    }

    .inbox-toolbar .btn-toolbar.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
        color: white;
    }

    /* Buscador */
    .inbox-search {
        position: relative;
        flex: 1;
        max-width: 300px;
        min-width: 150px;
    }

    .inbox-search input {
        width: 100%;
        padding: 0.45rem 0.75rem 0.45rem 2.2rem;
        border-radius: 10px;
        border: 1px solid #e9ecef;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        background: #f8f9fc;
        color: #1a1a1a;
    }

    .inbox-search input::placeholder {
        color: #adb5bd;
    }

    .inbox-search input:focus {
        outline: none;
        border-color: #1e3c72;
        box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        background: white;
    }

    .inbox-search .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .inbox-search .search-icon svg {
        stroke: #adb5bd;
    }

    /* Lista de mensajes */
    .message-list {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
    }

    .message-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
        cursor: pointer;
        gap: 0.75rem;
        background: white;
    }

    .message-item:last-child {
        border-bottom: none;
    }

    .message-item:hover {
        background: #f8f9fc;
    }

    .message-item.unread {
        background: #f0f4ff;
        border-left: 3px solid #1e3c72;
    }

    .message-item.unread:hover {
        background: #e8edf8;
    }

    .message-item .message-check {
        flex-shrink: 0;
    }

    .message-item .message-check input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #1e3c72;
    }

    .message-item .message-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-weight: 600;
        font-size: 0.85rem;
        color: white;
        text-transform: uppercase;
    }

    .message-item .message-avatar.avatar-sistema { background: #6c757d; }
    .message-item .message-avatar.avatar-solicitud { background: #28a745; }
    .message-item .message-avatar.avatar-prestamo { background: #1e3c72; }
    .message-item .message-avatar.avatar-recordatorio { background: #f59e0b; }
    .message-item .message-avatar.avatar-alerta { background: #dc3545; }

    .message-item .message-content {
        flex: 1;
        min-width: 0;
    }

    .message-item .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .message-item .message-sender {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .message-item .message-sender .badge-tipo {
        font-size: 0.6rem;
        padding: 0.1rem 0.6rem;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .message-item .message-sender .badge-tipo.tipo-sistema { background: #e9ecef; color: #495057; }
    .message-item .message-sender .badge-tipo.tipo-solicitud { background: #d4edda; color: #155724; }
    .message-item .message-sender .badge-tipo.tipo-prestamo { background: #cce5ff; color: #004085; }
    .message-item .message-sender .badge-tipo.tipo-recordatorio { background: #fff3cd; color: #856404; }
    .message-item .message-sender .badge-tipo.tipo-alerta { background: #f8d7da; color: #721c24; }

    .message-item .message-sender .dot-unread {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #1e3c72;
        display: inline-block;
        flex-shrink: 0;
    }

    .message-item .message-date {
        font-size: 0.7rem;
        color: #94a3b8;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .message-item .message-subject {
        font-weight: 500;
        color: #0f172a;
        font-size: 0.85rem;
        margin: 0.2rem 0 0.1rem 0;
    }

    .message-item .message-preview {
        font-size: 0.8rem;
        color: #64748b;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        white-space: pre-line;
        margin: 0;
        line-height: 1.4;
    }

    .message-item .message-actions {
        display: flex;
        gap: 0.25rem;
        flex-shrink: 0;
    }

    .message-item .message-actions button {
        background: none;
        border: none;
        padding: 0.2rem 0.4rem;
        cursor: pointer;
        color: #94a3b8;
        border-radius: 6px;
        transition: all 0.2s ease;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .message-item .message-actions button:hover {
        background: #e9ecef;
        color: #1e3c72;
    }

    .message-item .message-actions button.read-btn:hover {
        background: #d4edda;
        color: #155724;
    }

    .message-item .message-actions .btn-link-sm {
        padding: 0.2rem 0.6rem;
        font-size: 0.7rem;
        border-radius: 6px;
        background: rgba(30, 60, 114, 0.08);
        color: #1e3c72;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .message-item .message-actions .btn-link-sm:hover {
        background: rgba(30, 60, 114, 0.15);
    }

    /* Mensaje vacío */
    .message-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: #94a3b8;
    }

    .message-empty svg {
        width: 56px;
        height: 56px;
        stroke: #cbd5e1;
        margin-bottom: 1rem;
        fill: none;
    }

    .message-empty h5 {
        color: #0f172a;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    /* Modal de detalle de mensaje */
    .modal-message .modal-content {
        border-radius: 16px;
        border: none;
        overflow: hidden;
    }

    .modal-message .modal-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        padding: 1.25rem 1.75rem;
        border-bottom: none;
    }

    .modal-message .modal-header .modal-title {
        color: white;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-message .modal-header .modal-title svg {
        stroke: white;
        fill: none;
    }

    .modal-message .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }

    .modal-message .modal-header .btn-close:hover {
        opacity: 1;
    }

    .modal-message .modal-body {
        padding: 1.75rem;
    }

    .message-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .message-detail-sender {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .message-detail-sender .avatar-lg {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.2rem;
        color: white;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    .message-detail-sender .avatar-lg.avatar-sistema { background: #6c757d; }
    .message-detail-sender .avatar-lg.avatar-solicitud { background: #28a745; }
    .message-detail-sender .avatar-lg.avatar-prestamo { background: #1e3c72; }
    .message-detail-sender .avatar-lg.avatar-recordatorio { background: #f59e0b; }
    .message-detail-sender .avatar-lg.avatar-alerta { background: #dc3545; }

    .message-detail-sender .sender-info h6 {
        margin: 0;
        color: #0f172a;
        font-weight: 600;
        font-size: 1rem;
    }

    .message-detail-sender .sender-info .badge-tipo-lg {
        font-size: 0.65rem;
        padding: 0.15rem 0.7rem;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .message-detail-sender .sender-info .badge-tipo-lg.tipo-sistema { background: #e9ecef; color: #495057; }
    .message-detail-sender .sender-info .badge-tipo-lg.tipo-solicitud { background: #d4edda; color: #155724; }
    .message-detail-sender .sender-info .badge-tipo-lg.tipo-prestamo { background: #cce5ff; color: #004085; }
    .message-detail-sender .sender-info .badge-tipo-lg.tipo-recordatorio { background: #fff3cd; color: #856404; }
    .message-detail-sender .sender-info .badge-tipo-lg.tipo-alerta { background: #f8d7da; color: #721c24; }

    .message-detail-sender .sender-info .status-text {
        font-size: 0.75rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .message-detail-date {
        color: #94a3b8;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .message-detail-date svg {
        stroke: #94a3b8;
        fill: none;
    }

    .message-detail-subject {
        font-size: 1.1rem;
        font-weight: 600;
        color: #0f172a;
        margin: 1rem 0 0.5rem 0;
    }

    .message-detail-body {
        background: #f8f9fc;
        border-radius: 12px;
        padding: 1.25rem;
        white-space: pre-line;
        line-height: 1.7;
        color: #1a1a1a;
        font-size: 0.95rem;
        margin: 1rem 0;
        border: 1px solid #e9ecef;
    }

    .message-detail-body .body-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #94a3b8;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .message-detail-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
        flex-wrap: wrap;
    }

    .message-detail-actions .btn-primary-dark {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
        border: none !important;
        color: white !important;
        padding: 0.5rem 1.2rem !important;
        border-radius: 10px !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }

    .message-detail-actions .btn-primary-dark:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 16px rgba(30, 60, 114, 0.3) !important;
        color: white !important;
    }

    .message-detail-actions .btn-primary-dark svg {
        stroke: white !important;
        fill: none !important;
    }

    /* RESPONSIVE */
    @media (max-width: 992px) {
        .inbox-container {
            flex-direction: column;
        }

        .inbox-sidebar {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid #e9ecef;
            padding: 0.75rem 0;
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            background: #f8f9fc;
        }

        .inbox-sidebar .sidebar-title {
            display: none;
        }

        .inbox-sidebar .folder-item {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            border-left: none;
            border-bottom: 2px solid transparent;
            width: auto;
            flex: 0 0 auto;
        }

        .inbox-sidebar .folder-item.active {
            border-left: none;
            border-bottom-color: #1e3c72;
        }

        .inbox-content {
            padding: 1rem;
        }

        .inbox-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .inbox-search {
            max-width: 100%;
        }
    }

    @media (max-width: 576px) {
        .message-item {
            flex-wrap: wrap;
            padding: 0.6rem 0.75rem;
        }

        .message-item .message-check {
            order: 1;
        }

        .message-item .message-avatar {
            order: 2;
            width: 28px;
            height: 28px;
            font-size: 0.65rem;
        }

        .message-item .message-content {
            order: 3;
            width: 100%;
            margin-top: 0.3rem;
        }

        .message-item .message-actions {
            order: 4;
        }

        .message-item .message-date {
            order: 5;
            font-size: 0.65rem;
        }

        .modal-message .modal-body {
            padding: 1rem;
        }

        .message-detail-body {
            padding: 0.75rem;
            font-size: 0.85rem;
        }

        .page-header {
            padding: 1rem 1.25rem !important;
        }
    }

    /* Animaciones */
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .message-item {
        animation: slideIn 0.25s ease forwards;
    }

    .message-item:nth-child(1) { animation-delay: 0.02s; }
    .message-item:nth-child(2) { animation-delay: 0.04s; }
    .message-item:nth-child(3) { animation-delay: 0.06s; }
    .message-item:nth-child(4) { animation-delay: 0.08s; }
    .message-item:nth-child(5) { animation-delay: 0.1s; }
    .message-item:nth-child(6) { animation-delay: 0.12s; }
    .message-item:nth-child(7) { animation-delay: 0.14s; }
    .message-item:nth-child(8) { animation-delay: 0.16s; }
    .message-item:nth-child(9) { animation-delay: 0.18s; }
    .message-item:nth-child(10) { animation-delay: 0.2s; }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">

    <!-- ========== HEADER ========== -->
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2" ry="2"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                Bandeja de Entrada
            </h4>
            <p>Todos tus mensajes y notificaciones</p>
        </div>
        @if($noLeidas > 0)
        <div>
            <button onclick="marcarTodasComoLeidas()" class="btn btn-light" style="border-radius: 30px; font-weight: 500;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="me-1" style="display:inline;">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Marcar todas como leídas
            </button>
        </div>
        @endif
    </div>

    <!-- ========== BANDEJA DE ENTRADA ESTILO GMAIL ========== -->
    <div class="inbox-container">

        <!-- SIDEBAR - Carpetas -->
        <div class="inbox-sidebar">
            <div class="sidebar-title">Carpetas</div>

            <button class="folder-item {{ !request('filtro') && !request('tipo') ? 'active' : '' }}" 
                    onclick="window.location.href='{{ route('admin.notificaciones.index') }}'">
                <span class="folder-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                        <rect x="2" y="4" width="20" height="16" rx="2" ry="2"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <span class="folder-name">Todos</span>
                <span class="badge-folder">{{ $notificaciones->total() }}</span>
            </button>

            <button class="folder-item {{ request('filtro') == 'no_leidas' ? 'active' : '' }}" 
                    onclick="window.location.href='{{ route('admin.notificaciones.index', ['filtro' => 'no_leidas']) }}'">
                <span class="folder-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </span>
                <span class="folder-name">No leídas</span>
                <span class="badge-folder unread">{{ $noLeidas }}</span>
            </button>

            <button class="folder-item {{ request('tipo') == 'solicitud' ? 'active' : '' }}" 
                    onclick="window.location.href='{{ route('admin.notificaciones.index', ['tipo' => 'solicitud']) }}'">
                <span class="folder-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M22 7l-10 7L2 7"/>
                    </svg>
                </span>
                <span class="folder-name">Solicitudes</span>
                <span class="badge-folder">{{ $notificaciones->where('tipo', 'solicitud')->count() }}</span>
            </button>

            <button class="folder-item {{ request('tipo') == 'prestamo' ? 'active' : '' }}" 
                    onclick="window.location.href='{{ route('admin.notificaciones.index', ['tipo' => 'prestamo']) }}'">
                <span class="folder-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </span>
                <span class="folder-name">Préstamos</span>
                <span class="badge-folder">{{ $notificaciones->where('tipo', 'prestamo')->count() }}</span>
            </button>

            <button class="folder-item {{ request('tipo') == 'sistema' ? 'active' : '' }}" 
                    onclick="window.location.href='{{ route('admin.notificaciones.index', ['tipo' => 'sistema']) }}'">
                <span class="folder-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </span>
                <span class="folder-name">Sistema</span>
                <span class="badge-folder">{{ $notificaciones->where('tipo', 'sistema')->count() }}</span>
            </button>

            <button class="folder-item {{ request('tipo') == 'alerta' ? 'active' : '' }}" 
                    onclick="window.location.href='{{ route('admin.notificaciones.index', ['tipo' => 'alerta']) }}'">
                <span class="folder-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                </span>
                <span class="folder-name">Alertas</span>
                <span class="badge-folder">{{ $notificaciones->where('tipo', 'alerta')->count() }}</span>
            </button>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="inbox-content">

            <!-- TOOLBAR -->
            <div class="inbox-toolbar">
                <div class="toolbar-left">
                    <h5 class="toolbar-title">
                        @if(request('filtro') == 'no_leidas')
                            📬 No leídas
                        @elseif(request('tipo'))
                            {{ ucfirst(request('tipo')) }}
                        @else
                            📨 Todos los mensajes
                        @endif
                        <span class="count-badge">{{ $notificaciones->total() }}</span>
                    </h5>
                </div>
                <div class="toolbar-actions">
                    <div class="inbox-search">
                        <span class="search-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" id="searchMessages" placeholder="Buscar mensajes..." value="{{ request('search') }}">
                    </div>
                    <button class="btn-toolbar" onclick="location.reload()" title="Actualizar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"/>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- LISTA DE MENSAJES -->
            <div class="message-list" id="messageList">
                @if($notificaciones->isEmpty())
                    <div class="message-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <h5>No hay mensajes</h5>
                        <p>Tu bandeja de entrada está vacía</p>
                    </div>
                @else
                    @foreach($notificaciones as $notificacion)
                        <div class="message-item {{ $notificacion->leida ? '' : 'unread' }}" 
                             data-id="{{ $notificacion->id }}"
                             onclick="verMensaje({{ $notificacion->id }})">
                            
                            <!-- Checkbox -->
                            <div class="message-check" onclick="event.stopPropagation();">
                                <input type="checkbox" class="message-selector" data-id="{{ $notificacion->id }}">
                            </div>

                            <!-- Avatar -->
                            @php
                                $inicial = $notificacion->tipo ? strtoupper(substr($notificacion->tipo, 0, 1)) : 'S';
                                $tipoClass = 'avatar-' . ($notificacion->tipo ?: 'sistema');
                                $tipoLabel = ucfirst($notificacion->tipo ?: 'sistema');
                            @endphp
                            <div class="message-avatar {{ $tipoClass }}">
                                {{ $inicial }}
                            </div>

                            <!-- Contenido -->
                            <div class="message-content">
                                <div class="message-header">
                                    <div class="message-sender">
                                        <span>{{ $notificacion->titulo }}</span>
                                        <span class="badge-tipo tipo-{{ $notificacion->tipo ?: 'sistema' }}">{{ $tipoLabel }}</span>
                                        @if(!$notificacion->leida)
                                            <span class="dot-unread" title="No leído"></span>
                                        @endif
                                    </div>
                                    <span class="message-date">{{ $notificacion->fecha_envio->diffForHumans() }}</span>
                                </div>
                                <div class="message-preview">{{ Str::limit($notificacion->mensaje, 120) }}</div>
                            </div>

                            <!-- Acciones -->
                            <div class="message-actions" onclick="event.stopPropagation();">
                                @if(!$notificacion->leida)
                                    <button class="read-btn" onclick="marcarComoLeida({{ $notificacion->id }}, this)" title="Marcar como leída">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    </button>
                                @endif
                                @if($notificacion->url)
                                    <a href="{{ url($notificacion->url) }}" class="btn-link-sm" onclick="event.stopPropagation();">
                                        Ver
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- PAGINACIÓN -->
            @if($notificaciones->hasPages())
                <div class="mt-3">
                    {{ $notificaciones->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ========== MODAL DETALLE DE MENSAJE ========== -->
<div class="modal fade modal-message" id="modalMensaje" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mensajeModalTitulo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2" ry="2"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    Mensaje
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="mensajeModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando mensaje...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ============================================================
// BANDEJA DE ENTRADA - FUNCIONALIDAD COMPLETA
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    // Buscador en tiempo real
    const searchInput = document.getElementById('searchMessages');
    if (searchInput) {
        let timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const url = new URL(window.location.href);
                const value = this.value.trim();
                if (value) {
                    url.searchParams.set('search', value);
                } else {
                    url.searchParams.delete('search');
                }
                window.location.href = url.toString();
            }, 500);
        });
    }

    // Selección múltiple de mensajes (solo visual)
    document.querySelectorAll('.message-selector').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const messageItem = this.closest('.message-item');
            if (this.checked) {
                messageItem.style.background = '#eef3fc';
            } else {
                messageItem.style.background = messageItem.classList.contains('unread') ? '#f0f4ff' : 'white';
            }
        });
    });
});

// ============================================================
// FUNCIONES GLOBALES
// ============================================================

function mostrarToast(mensaje, tipo = 'success') {
    const colores = { success: '#1e7e34', error: '#c5221f', warning: '#f6c23e', info: '#1e3c72' };
    const iconos = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        background: ${colores[tipo] || colores.success}; color: white;
        padding: 14px 20px; border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        font-weight: 500; font-size: 0.9rem;
        animation: slideIn 0.3s ease-out; max-width: 400px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
    `;
    toast.innerHTML = `<span>${iconos[tipo] || 'ℹ️'}</span><span>${mensaje}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => { 
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

function marcarComoLeida(id, btn) {
    fetch(`/admin/notificaciones/${id}/leer`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = btn.closest('.message-item');
            if (item) {
                item.classList.remove('unread');
                item.style.background = 'white';
                // Remover el dot de no leído
                const dot = item.querySelector('.dot-unread');
                if (dot) dot.remove();
            }
            // Remover el botón de marcar como leída
            if (btn) {
                const parent = btn.closest('.message-actions');
                if (parent) {
                    btn.remove();
                    // Si no quedan acciones, ocultar el contenedor
                    if (parent.children.length === 0) {
                        parent.style.display = 'none';
                    }
                }
            }
            // Actualizar badge de la campanita
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                const current = parseInt(badge.textContent);
                if (current > 1) {
                    badge.textContent = current - 1;
                } else {
                    badge.remove();
                }
            }
            // Actualizar badge del sidebar (No leídas)
            const folderBadges = document.querySelectorAll('.badge-folder.unread');
            folderBadges.forEach(b => {
                const current = parseInt(b.textContent);
                if (current > 1) {
                    b.textContent = current - 1;
                } else {
                    b.textContent = '0';
                    b.style.background = '#e9ecef';
                    b.style.color = '#495057';
                    b.classList.remove('unread');
                }
            });
            mostrarToast('Mensaje marcado como leído', 'success');
        }
    })
    .catch(error => console.error('Error:', error));
}

function marcarTodasComoLeidas() {
    if (!confirm('¿Marcar todas las notificaciones como leídas?')) return;
    
    const btn = document.querySelector('.page-header .btn-light');
    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';
        btn.disabled = true;
    }
    
    fetch('/admin/notificaciones/marcar-todas-leidas', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarToast(data.message || 'Todas las notificaciones marcadas como leídas', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            mostrarToast(data.message || 'Error al marcar todas como leídas', 'error');
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('Error de conexión', 'error');
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

function verMensaje(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalMensaje'));
    const body = document.getElementById('mensajeModalBody');
    const title = document.getElementById('mensajeModalTitulo');
    
    body.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando mensaje...</p>
        </div>
    `;
    modal.show();

    // Primero marcamos como leída
    fetch(`/admin/notificaciones/${id}/leer`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(() => {
        // Luego obtenemos el detalle
        return fetch(`/admin/notificaciones/${id}/detalle`, {
            headers: { 'Accept': 'application/json' }
        });
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const n = data.data;
            const colores = {
                'sistema': { bg: '#6c757d', icon: '📌' },
                'solicitud': { bg: '#28a745', icon: '📋' },
                'prestamo': { bg: '#1e3c72', icon: '📦' },
                'recordatorio': { bg: '#f59e0b', icon: '⏰' },
                'alerta': { bg: '#dc3545', icon: '⚠️' }
            };
            const info = colores[n.tipo] || colores['sistema'];
            const inicial = n.tipo ? n.tipo.charAt(0).toUpperCase() : 'S';
            const tipoClass = 'avatar-' + (n.tipo || 'sistema');
            const fecha = new Date(n.fecha_envio).toLocaleString('es-ES', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
            const statusText = n.leida ? '✓ Leído' : '● No leído';
            const statusColor = n.leida ? '#28a745' : '#1e3c72';

            title.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2" ry="2"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                ${n.titulo}
            `;

            body.innerHTML = `
                <div class="message-detail-header">
                    <div class="message-detail-sender">
                        <div class="avatar-lg ${tipoClass}">
                            ${inicial}
                        </div>
                        <div class="sender-info">
                            <h6>${n.titulo}</h6>
                            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-top:0.2rem;">
                                <span class="badge-tipo-lg tipo-${n.tipo || 'sistema'}">${n.tipo || 'Sistema'}</span>
                                <span class="status-text" style="color: ${statusColor};">
                                    ${statusText}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="message-detail-date">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        ${fecha}
                    </div>
                </div>
                <div class="message-detail-body">
                    <div class="body-label">Mensaje</div>
                    ${n.mensaje.replace(/\n/g, '<br>')}
                </div>
                ${n.url ? `
                <div class="message-detail-actions">
                    <a href="${n.url}" class="btn-primary-dark">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <polyline points="18 15 18 21 6 21 6 15"/>
                            <polyline points="15 9 12 6 9 9"/>
                            <line x1="12" y1="6" x2="12" y2="18"/>
                        </svg>
                        Ver en el sistema
                    </a>
                </div>
                ` : ''}
            `;

            // Actualizar la lista visualmente (quitar el estado no leído)
            const item = document.querySelector(`.message-item[data-id="${id}"]`);
            if (item && item.classList.contains('unread')) {
                item.classList.remove('unread');
                const dot = item.querySelector('.dot-unread');
                if (dot) dot.remove();
                // Actualizar badge del sidebar
                const folderBadge = document.querySelector('.badge-folder.unread');
                if (folderBadge) {
                    const current = parseInt(folderBadge.textContent);
                    if (current > 1) {
                        folderBadge.textContent = current - 1;
                    } else {
                        folderBadge.textContent = '0';
                        folderBadge.style.background = '#e9ecef';
                        folderBadge.style.color = '#495057';
                        folderBadge.classList.remove('unread');
                    }
                }
                // Actualizar badge de la campanita
                const bellBadge = document.querySelector('.notification-badge');
                if (bellBadge) {
                    const current = parseInt(bellBadge.textContent);
                    if (current > 1) {
                        bellBadge.textContent = current - 1;
                    } else {
                        bellBadge.remove();
                    }
                }
            }
        } else {
            body.innerHTML = `
                <div class="text-center py-4 text-danger">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5" class="mb-2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p>${data.message || 'Error al cargar el mensaje'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        body.innerHTML = `
            <div class="text-center py-4 text-danger">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5" class="mb-2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <p>Error de conexión</p>
            </div>
        `;
    });
}
</script>
@endsection