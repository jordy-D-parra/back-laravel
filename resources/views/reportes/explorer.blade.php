@extends("layouts.dashboard")

@section("title", "Explorador de Reportes")

@section("styles")
    @vite(["resources/css/admin-reportes-explorer.css"])
@endsection

@section("content")
<div class="reportes-explorer" id="reportesExplorer">

    {{-- ============ SIDEBAR DE CATEGORIAS ============ --}}
    <aside class="reportes-sidebar" id="reportesSidebar">
        <div class="sidebar-header">
            <h3>Reportes</h3>
            <input type="text" class="sidebar-search" id="sidebarSearch" placeholder="Buscar reporte...">
        </div>

        <nav class="sidebar-nav" id="sidebarNav">
            @foreach($catalogo as $categoria => $items)
                <div class="categoria-grupo" data-categoria="{{ $categoria }}">
                    <div class="categoria-titulo">{{ ucfirst($categoria) }}</div>
                    @foreach($items as $key => $report)
                        <button type="button"
                                class="reporte-item"
                                data-key="{{ $key }}"
                                data-title="{{ strtolower($report["title"]) }}"
                                data-description="{{ strtolower($report["description"]) }}">
                            <span class="reporte-icon">{{ $report["icon"] }}</span>
                            <span class="reporte-label">{{ $report["title"] }}</span>
                        </button>
                    @endforeach
                </div>
            @endforeach
        </nav>
    </aside>

    {{-- ============ PANEL PRINCIPAL ============ --}}
    <main class="reportes-panel">

        {{-- ===== ESTADO INICIAL (sin reporte seleccionado) ===== --}}
        <div id="panelVacio" class="panel-vacio">
            <div class="vacio-icon">📊</div>
            <h3>Selecciona un reporte</h3>
            <p>Elige un reporte del menu lateral para visualizarlo y exportarlo en PDF, Excel o CSV.</p>
        </div>

        {{-- ===== CONTENIDO DEL REPORTE ===== --}}
        <div id="panelContenido" class="panel-contenido" style="display:none;">

            {{-- Header del reporte --}}
            <header class="panel-header">
                <div class="panel-header-info">
                    <h2 id="reporteTitulo">Cargando...</h2>
                    <p id="reporteDesc" class="text-muted"></p>
                    <small id="reporteCodigo" class="text-muted"></small>
                </div>
                <div class="panel-header-actions">
                    <a href="#" id="btnExportPdf" class="btn-export btn-pdf" target="_blank" title="Exportar a PDF">
                        <span>PDF</span>
                    </a>
                    <a href="#" id="btnExportXlsx" class="btn-export btn-xlsx" target="_blank" title="Exportar a Excel">
                        <span>Excel</span>
                    </a>
                    <a href="#" id="btnExportCsv" class="btn-export btn-csv" target="_blank" title="Exportar a CSV">
                        <span>CSV</span>
                    </a>
                </div>
            </header>

            {{-- Filtros dinamicos --}}
            <section class="panel-filtros" id="panelFiltros"></section>

            {{-- Stats --}}
            <section class="panel-stats" id="panelStats"></section>

            {{-- Tabla --}}
            <section class="panel-tabla" id="panelTabla">
                <div class="panel-loading">
                    <div class="spinner"></div>
                    <p>Cargando datos...</p>
                </div>
            </section>

            {{-- Paginacion --}}
            <section class="panel-paginacion" id="panelPaginacion"></section>

        </div>
    </main>
</div>

{{-- Datos inyectados desde el backend --}}
<script>
    window.__REPORTES__ = {
        fuentes: @json($fuentes),
        urls: {
            data:   "{{ route("admin.reportes.data", ["key" => "__KEY__"]) }}",
            export: "{{ route("admin.reportes.export", ["key" => "__KEY__", "format" => "__FORMAT__"]) }}"
        }
    };
</script>
@endsection

@section("scripts")
    @vite(["resources/js/admin-reportes-explorer.js"])
@endsection
