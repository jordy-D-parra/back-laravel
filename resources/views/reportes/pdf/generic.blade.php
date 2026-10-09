<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        @page {
            size: letter {{ $codigo && str_contains($codigo, "x") ? "landscape" : "portrait" }};
            margin: 12mm 10mm 15mm 10mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", "Times New Roman", serif;
            font-size: 9pt;
            color: #1a1a1a;
            line-height: 1.35;
        }

        /* ============ HEADER ============ */
        .pdf-header {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #1e3c72;
            margin-bottom: 10px;
        }
        .pdf-header td {
            vertical-align: middle;
            padding-bottom: 6px;
        }
        .pdf-header .td-logo {
            width: 90px;
            text-align: center;
        }
        .pdf-header .td-logo img {
            height: 48px;
            width: auto;
        }
        .pdf-header .td-logo .logo-label {
            display: block;
            font-size: 5.5pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            margin-top: 1px;
            line-height: 1.05;
        }
        .pdf-header .td-center {
            text-align: center;
            padding: 0 8px;
        }
        .pdf-header .titulo-pais {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .pdf-header .titulo-gobierno {
            font-size: 10pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .pdf-header .titulo-depto {
            font-size: 8pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
        }
        .pdf-header .titulo-reporte {
            font-size: 11pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 3px 18px;
            border-top: 1.5px solid #1e3c72;
            border-bottom: 1.5px solid #1e3c72;
            display: inline-block;
            margin-top: 6px;
        }

        /* ============ META ============ */
        .pdf-meta {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            color: #475569;
            margin-bottom: 8px;
        }
        .pdf-meta td {
            padding: 2px 0;
            vertical-align: top;
        }
        .pdf-meta .td-right { text-align: right; }
        .pdf-meta strong { color: #1e3c72; }

        /* ============ FILTROS ============ */
        .pdf-filtros {
            background: #f8fafc;
            border-left: 3px solid #1e3c72;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 8pt;
            color: #475569;
            margin-bottom: 10px;
        }
        .pdf-filtros strong { color: #1e3c72; }

        /* ============ STATS ============ */
        .pdf-stats {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px 0;
            margin-bottom: 10px;
        }
        .pdf-stats td {
            background: #f8fafc;
            border-radius: 6px;
            padding: 8px 4px;
            text-align: center;
            border-left: 3px solid #1e3c72;
        }
        .pdf-stats .num {
            font-size: 13pt;
            font-weight: bold;
            color: #1e3c72;
            display: block;
            line-height: 1.1;
        }
        .pdf-stats .lbl {
            font-size: 6.5pt;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        /* ============ TABLA ============ */
        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 10px;
        }
        .pdf-table thead th {
            background: #1e3c72;
            color: white;
            padding: 5px 6px;
            text-align: left;
            font-size: 6.8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #1e3c72;
        }
        .pdf-table tbody td {
            padding: 4px 6px;
            border: 1px solid #e9ecef;
            vertical-align: top;
        }
        .pdf-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .pdf-table .text-center { text-align: center; }
        .pdf-table .text-end { text-align: right; }
        .pdf-table .fw-bold { font-weight: bold; }

        /* ============ BADGES (sin colores, solo gris neutro) ============ */
        .pdf-badge {
            display: inline-block;
            padding: 1.5px 6px;
            border-radius: 8px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }
        .pdf-badge-gray { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        /* ============ EMPTY ============ */
        .pdf-empty {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
            background: #f8fafc;
            border-radius: 6px;
            font-size: 9pt;
        }

        /* ============ FIRMAS ============ */
        .pdf-firmas {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .pdf-firmas td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 30px;
        }
        .pdf-firmas .linea {
            border-top: 1.3px solid #000;
            width: 100%;
            height: 1px;
            margin-bottom: 3px;
        }
        .pdf-firmas .nombre {
            font-weight: bold;
            font-size: 8.5pt;
            color: #1e3c72;
            text-transform: uppercase;
        }
        .pdf-firmas .cargo {
            font-size: 7pt;
            color: #555;
            text-transform: uppercase;
            margin-top: 1px;
        }
        .pdf-firmas .rol {
            font-size: 7pt;
            font-weight: bold;
            color: #1e3c72;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* ============ FOOTER ============ */
        .pdf-footer {
            position: fixed;
            bottom: -8mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 6.5pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 8px; }
    </style>
</head>
<body>

    {{-- ============ HEADER INSTITUCIONAL ============ --}}
    <table class="pdf-header">
        <tr>
            <td class="td-logo">
                @if(file_exists(public_path("images/gobierno.jpeg")))
                    <img src="{{ public_path("images/gobierno.jpeg") }}" alt="Gobierno">
                @endif
                <span class="logo-label">Gobierno<br>Bolivariano</span>
            </td>
            <td class="td-center">
                <div class="titulo-pais">Republica Bolivariana de Venezuela</div>
                <div class="titulo-gobierno">Gobernacion del Estado Yaracuy</div>
                <div class="titulo-depto">Direccion de Informatica</div>
                <div class="titulo-reporte">{{ $titulo }}</div>
            </td>
            <td class="td-logo">
                @if(file_exists(public_path("images/escudo-yaracuy.jpeg")))
                    <img src="{{ public_path("images/escudo-yaracuy.jpeg") }}" alt="Escudo">
                @endif
                <span class="logo-label">Gobernacion<br>del Estado Yaracuy</span>
            </td>
        </tr>
    </table>

    {{-- ============ META BOX ============ --}}
    <table class="pdf-meta">
        <tr>
            <td><strong>Documento:</strong> {{ $codigo }}</td>
            <td class="td-right"><strong>Generado:</strong> {{ $fecha_generacion }}</td>
        </tr>
        <tr>
            <td><strong>Generado por:</strong> {{ $usuario }}</td>
            <td class="td-right"><strong>Hash:</strong> {{ $hash }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Total de registros:</strong> {{ $total }}</td>
        </tr>
    </table>

    {{-- ============ FILTROS APLICADOS ============ --}}
    @if(!empty($filtrosAplicados))
        <div class="pdf-filtros">
            <strong>Filtros aplicados:</strong> {{ $filtrosAplicados }}
        </div>
    @endif

    {{-- ============ STATS ============ --}}
    @if(!empty($summary))
        <table class="pdf-stats">
            <tr>
                @foreach($summary as $label => $value)
                    <td>
                        <span class="num">{{ is_numeric($value) ? number_format($value, 0, ",", ".") : $value }}</span>
                        <span class="lbl">{{ $label }}</span>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    {{-- ============ TABLA DE DATOS ============ --}}
    @if($filas->count() > 0)
        <table class="pdf-table">
            <thead>
                <tr>
                    @foreach($columnas as $col)
                        <th style="width: {{ $col["width"] ?? "auto" }}; text-align: {{ ($col["align"] ?? "left") === "center" ? "center" : (($col["align"] ?? "left") === "right" ? "right" : "left") }};">
                            {{ $col["label"] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($filas as $fila)
                    <tr>
                        @foreach($columnas as $col)
                            @php
                                $valor = data_get($fila, $col["key"]);
                                $formato = $col["format"] ?? null;
                                $align = $col["align"] ?? "left";
                            @endphp
                            <td class="text-{{ $align === "center" ? "center" : ($align === "right" ? "end" : "left") }}">
                                @if($formato === "date" && $valor)
                                    {{ \Carbon\Carbon::parse($valor)->format("d/m/Y") }}
                                @elseif($formato === "money" && is_numeric($valor))
                                    {{ number_format($valor, 2, ",", ".") }}
                                @elseif($formato === "badge" && $valor)
                                    <span class="pdf-badge pdf-badge-gray">{{ $valor }}</span>
                                @else
                                    {{ $valor ?? "-" }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="pdf-empty">
            No se encontraron registros con los filtros seleccionados.
        </div>
    @endif

    {{-- ============ FIRMAS ============ --}}
    @if(!empty($firmas))
        <table class="pdf-firmas">
            <tr>
                @foreach($firmas as $firma)
                    <td>
                        <div class="linea"></div>
                        <div class="nombre">{{ strtoupper($firma["nombre"] ?? "") }}</div>
                        <div class="cargo">{{ strtoupper($firma["cargo"] ?? "") }}</div>
                        <div class="rol">{{ strtoupper($firma["role"] ?? "") }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    {{-- ============ FOOTER ============ --}}
    <div class="pdf-footer">
        Sistema de Gestion de Inventario Tecnologico - Gobernacion del Estado Yaracuy
        <br>
        Documento {{ $codigo }} &middot; Generado el {{ $fecha_generacion }}
    </div>

</body>
</html>