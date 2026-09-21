<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Retiro - {{ $data['numero_acta'] }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #1a1a1a;
            padding: 5mm;
        }

        /* ============ CONTENEDOR ============ */
        .acta-container {
            border: 2px solid #1e3c72;
            padding: 14px 18px 18px 18px;
            position: relative;
            background: #fff;
            width: 100%;
        }

        /* ============ MARCA DE AGUA ============ */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            opacity: 0.04;
            font-size: 60pt;
            font-weight: 700;
            color: #1e3c72;
            letter-spacing: 10px;
            white-space: nowrap;
            width: 100%;
            text-align: center;
            pointer-events: none;
            user-select: none;
            z-index: 0;
        }

        /* ============ HEADER CON LOGOS ============ */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #1e3c72;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        .header-table td {
            vertical-align: middle;
            padding-bottom: 6px;
        }
        .header-table .td-logo {
            width: 110px;
            text-align: center;
        }
        .header-table .td-logo img {
            height: 52px;
            width: auto;
        }
        .header-table .td-logo .logo-label {
            display: block;
            font-size: 5.5pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            margin-top: 2px;
            line-height: 1.05;
        }
        .header-table .td-center {
            text-align: center;
            padding: 0 8px;
        }
        .header-table .td-center .titulo-pais {
            font-size: 9pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .header-table .td-center .titulo-gobierno {
            font-size: 10.5pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-table .td-center .titulo-depto {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ============ TÍTULO DEL ACTA ============ */
        .titulo-acta {
            text-align: center;
            margin: 6px 0 4px 0;
            position: relative;
            z-index: 1;
        }
        .titulo-acta span {
            display: inline-block;
            font-size: 12pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 3px 20px;
            border-top: 1.5px solid #1e3c72;
            border-bottom: 1.5px solid #1e3c72;
        }

        /* ============ NÚMERO Y FECHA ============ */
        .numero-fecha-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            color: #1e3c72;
            font-weight: bold;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        .numero-fecha-table .td-left { text-align: left; }
        .numero-fecha-table .td-right { text-align: right; }

        /* ============ CUERPO ============ */
        .cuerpo {
            text-align: justify;
            font-size: 9.5pt;
            line-height: 1.45;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        .cuerpo .destacado {
            font-weight: bold;
            color: #1e3c72;
        }

        /* ============ DATOS DEL EQUIPO ============ */
        .datos-equipo {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border-left: 3px solid #1e3c72;
            border-top: 1px solid #e9ecef;
            border-right: 1px solid #e9ecef;
            border-bottom: 1px solid #e9ecef;
            margin: 6px 0 8px 0;
            font-size: 9pt;
            position: relative;
            z-index: 1;
        }
        .datos-equipo td {
            padding: 3px 9px;
            border-bottom: 1px dashed #e9ecef;
        }
        .datos-equipo tr:last-child td { border-bottom: none; }
        .datos-equipo .label {
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            font-size: 8pt;
            width: 130px;
        }
        .datos-equipo .valor {
            font-weight: 500;
            color: #1a1a1a;
        }
        .datos-equipo .valor .serial {
            font-weight: 700;
            color: #1e3c72;
            letter-spacing: 0.5px;
        }

        /* ============ TABLA DE ITEMS ============ */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 8px 0;
            font-size: 9pt;
            position: relative;
            z-index: 1;
        }
        .items-table thead th {
            background: #1e3c72;
            color: white;
            padding: 4px 9px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #1e3c72;
            letter-spacing: 0.4px;
        }
        .items-table tbody td {
            padding: 4px 9px;
            border: 1px solid #e9ecef;
        }
        .items-table tbody tr:nth-child(even) { background: #f8fafc; }
        .items-table .text-center { text-align: center; }

        /* ============ OBSERVACIONES ============ */
        .observaciones {
            padding: 6px 12px;
            margin: 6px 0 8px 0;
            background: #fffbf0;
            border-radius: 4px;
            border-left: 4px solid #f6c23e;
            border-right: 1px solid #e9ecef;
            border-top: 1px solid #e9ecef;
            border-bottom: 1px solid #e9ecef;
            font-style: italic;
            font-size: 9pt;
            position: relative;
            z-index: 1;
        }
        .observaciones strong {
            font-style: normal;
            color: #1e3c72;
        }

        /* ============ COMPROMISO ============ */
        .compromiso {
            font-size: 9pt;
            text-align: justify;
            line-height: 1.4;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        /* ============ FIRMAS ============ */
        .firmas-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            position: relative;
            z-index: 1;
        }
        .firmas-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 30px;
        }
        .firmas-table .linea {
            border-top: 1.3px solid #000;
            width: 100%;
            height: 1px;
            margin-bottom: 4px;
        }
        .firmas-table .nombre {
            font-weight: bold;
            font-size: 9pt;
            color: #1e3c72;
            text-transform: uppercase;
        }
        .firmas-table .cargo {
            font-size: 7.5pt;
            color: #555;
            text-transform: uppercase;
            margin-top: 1px;
        }
        .firmas-table .rol {
            font-size: 7.5pt;
            font-weight: bold;
            color: #1e3c72;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 3px;
        }

        /* ============ FOOTER ============ */
        .footer {
            margin-top: 14px;
            padding-top: 5px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 6.5pt;
            color: #adb5bd;
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>

<div class="acta-container">

    {{-- Marca de agua --}}
    <div class="watermark">ACTA DE RETIRO</div>

    {{-- ============ HEADER CON LOGOS ============ --}}
    <table class="header-table">
        <tr>
            <td class="td-logo">
                <img src="{{ public_path('images/gobierno.jpeg') }}" alt="Gobierno">
                <span class="logo-label">Gobierno<br>Bolivariano</span>
            </td>
            <td class="td-center">
                <div class="titulo-pais">República Bolivariana de Venezuela</div>
                <div class="titulo-gobierno">Gobernación del Estado Yaracuy</div>
                <div class="titulo-depto">Dirección de Informática</div>
            </td>
            <td class="td-logo">
                <img src="{{ public_path('images/escudo-yaracuy.jpeg') }}" alt="Escudo">
                <span class="logo-label">Gobernación<br>del Estado Yaracuy</span>
            </td>
        </tr>
    </table>

    {{-- ============ TÍTULO ============ --}}
    <div class="titulo-acta">
        <span>Acta de Retiro</span>
    </div>

    {{-- ============ NÚMERO Y FECHA ============ --}}
    <table class="numero-fecha-table">
        <tr>
            <td class="td-left">Nº {{ $data['numero_acta'] }}</td>
            <td class="td-right">San Felipe, {{ $data['fecha'] }}</td>
        </tr>
    </table>

    {{-- ============ CUERPO ============ --}}
    <div class="cuerpo">
        Quien suscribe, <span class="destacado">{{ $data['encargado_nombre'] ?? 'Director de Informática' }}</span>,
        en su carácter de <span class="destacado">{{ $data['encargado_cargo'] ?? 'Director de Informática' }}</span>
        de la Dirección de Informática de la Gobernación del Estado Yaracuy,
        hace constar que el/la ciudadano(a)
        <span class="destacado">{{ $data['responsable_nombre'] ?? 'No especificado' }}</span>,
        titular de la Cédula de Identidad
        <span class="destacado">{{ $data['responsable_documento'] ?? 'N/A' }}</span>,
        en su carácter de <span class="destacado">{{ $data['responsable_cargo'] ?? 'Responsable' }}</span>
        de la entidad <span class="destacado">{{ $data['institucion'] ?? 'No especificada' }}</span>,
        ha retirado los siguientes equipos, cuyas características se detallan a continuación:
    </div>

    {{-- ============ DATOS DEL EQUIPO PRINCIPAL ============ --}}
    <table class="datos-equipo">
        <tr>
            <td class="label">EQUIPO PRINCIPAL:</td>
            <td class="valor">{{ $data['equipo_nombre'] ?? 'No especificado' }}</td>
        </tr>
        <tr>
            <td class="label">MARCA:</td>
            <td class="valor">{{ $data['marca'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">MODELO:</td>
            <td class="valor">{{ $data['modelo'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">NÚMERO DE SERIE:</td>
            <td class="valor"><span class="serial">{{ $data['serial'] ?? 'N/A' }}</span></td>
        </tr>
        <tr>
            <td class="label">ACCESORIOS:</td>
            <td class="valor">{{ $data['accesorios'] ?? 'Sin accesorios adicionales' }}</td>
        </tr>
        <tr>
            <td class="label">FECHA SOLICITUD:</td>
            <td class="valor">{{ $data['fecha_solicitud'] ?? 'N/A' }}</td>
        </tr>
    </table>

    {{-- ============ TABLA DE ITEMS ============ --}}
    @if(!empty($data['items']))
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 20%;">Tipo</th>
                <th style="width: 65%;">Descripción</th>
                <th style="width: 15%;" class="text-center">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['items'] as $item)
            <tr>
                <td>{{ ucfirst($item['tipo_item'] ?? 'Item') }}</td>
                <td>{{ $item['descripcion'] ?? 'Sin descripción' }}</td>
                <td class="text-center">{{ $item['cantidad'] ?? 1 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ============ OBSERVACIONES ============ --}}
    @if(!empty($data['observaciones']))
    <div class="observaciones">
        <strong>Observaciones:</strong> {{ $data['observaciones'] }}
    </div>
    @endif

    {{-- ============ COMPROMISO ============ --}}
    <div class="compromiso">
        El/la responsable arriba mencionado(a) se compromete a hacer uso adecuado
        de los equipos recibidos, así como a devolverlos en las mismas condiciones
        en que fueron entregados, en la fecha acordada. Cualquier daño, pérdida o
        deterioro será responsabilidad del firmante.
    </div>

    {{-- ============ FIRMAS ============ --}}
    <table class="firmas-table">
        <tr>
            {{-- FIRMA IZQUIERDA: ENCARGADO / INFORMÁTICA --}}
            <td>
                <div class="linea"></div>
                <div class="nombre">{{ strtoupper($data['encargado_nombre'] ?? 'Director de Informática') }}</div>
                <div class="cargo">{{ strtoupper($data['encargado_cargo'] ?? 'Director de Informática') }}</div>
                <div class="rol">Entrega</div>
            </td>

            {{-- FIRMA DERECHA: RESPONSABLE --}}
            <td>
                <div class="linea"></div>
                <div class="nombre">{{ strtoupper($data['responsable_nombre'] ?? 'No especificado') }}</div>
                <div class="cargo">{{ strtoupper($data['responsable_cargo'] ?? 'Responsable') }}</div>
                <div class="rol">Recibe</div>
            </td>
        </tr>
    </table>

    {{-- ============ FOOTER ============ --}}
    <div class="footer">
        Documento generado por el Sistema de Gestión de Inventario Tecnológico -
        Gobernación del Estado Yaracuy
    </div>

</div>

</body>
</html>