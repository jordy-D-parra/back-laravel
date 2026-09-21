<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Retiro - {{ $data['numero_acta'] }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 8mm 10mm 8mm 10mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.25;
            color: #1a1a1a;
        }

        /* ============ HEADER CON LOGOS ============ */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e3c72;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
            padding-bottom: 5px;
        }
        .header-table .td-logo {
            width: 90px;
            text-align: center;
        }
        .header-table .td-logo img {
            height: 45px;
            width: auto;
        }
        .header-table .td-logo .logo-label {
            display: block;
            font-size: 5.5pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            margin-top: 1px;
            line-height: 1.05;
        }
        .header-table .td-center {
            text-align: center;
            padding: 0 6px;
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
            margin: 4px 0 3px 0;
        }
        .titulo-acta span {
            display: inline-block;
            font-size: 11.5pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 2px 16px;
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
            margin-bottom: 6px;
        }
        .numero-fecha-table .td-left { text-align: left; }
        .numero-fecha-table .td-right { text-align: right; }

        /* ============ CUERPO ============ */
        .cuerpo {
            text-align: justify;
            font-size: 9.5pt;
            line-height: 1.4;
            margin-bottom: 6px;
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
            margin: 5px 0 6px 0;
            font-size: 9pt;
        }
        .datos-equipo td {
            padding: 2px 8px;
            border-bottom: 1px dashed #e9ecef;
        }
        .datos-equipo tr:last-child td {
            border-bottom: none;
        }
        .datos-equipo .label {
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            font-size: 8pt;
            width: 110px;
        }
        .datos-equipo .valor {
            font-weight: 500;
            color: #1a1a1a;
        }

        /* ============ TABLA DE ITEMS ============ */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0 6px 0;
            font-size: 9pt;
        }
        .items-table thead th {
            background: #1e3c72;
            color: white;
            padding: 3px 8px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #1e3c72;
        }
        .items-table tbody td {
            padding: 3px 8px;
            border: 1px solid #e9ecef;
        }
        .items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .items-table .text-center {
            text-align: center;
        }

        /* ============ COMPROMISO ============ */
        .compromiso {
            font-size: 9pt;
            text-align: justify;
            line-height: 1.35;
            margin-bottom: 6px;
        }

        /* ============ FIRMAS ============ */
        .firmas-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .firmas-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 25px;
        }
        .firmas-table .linea {
            border-top: 1.3px solid #000;
            width: 100%;
            height: 1px;
            margin-bottom: 3px;
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
            margin-top: 2px;
        }

        /* ============ FOOTER ============ */
        .footer {
            margin-top: 12px;
            padding-top: 4px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 6.5pt;
            color: #adb5bd;
        }

    </style>
</head>
<body>

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
        Quien suscribe, <span class="destacado">{{ $data['encargado_nombre'] }}</span>,
        en su carácter de <span class="destacado">{{ $data['encargado_cargo'] }}</span>
        de la Dirección de Informática de la Gobernación del Estado Yaracuy,
        hace constar que el/la ciudadano(a)
        <span class="destacado">{{ $data['responsable_nombre'] }}</span>,
        titular de la Cédula de Identidad
        <span class="destacado">{{ $data['responsable_documento'] }}</span>,
        en su carácter de <span class="destacado">{{ $data['responsable_cargo'] }}</span>
        de la entidad <span class="destacado">{{ $data['institucion'] }}</span>,
        ha retirado los siguientes equipos, cuyas características se detallan a continuación:
    </div>

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
                <td>{{ ucfirst($item['tipo_item']) }}</td>
                <td>{{ $item['descripcion'] }}</td>
                <td class="text-center">{{ $item['cantidad'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
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
            {{-- FIRMA IZQUIERDA: ADMINISTRADOR / ENCARGADO DE INFORMÁTICA --}}
            <td>
                <div class="linea"></div>
                <div class="nombre">{{ strtoupper($data['encargado_nombre']) }}</div>
                <div class="cargo">{{ strtoupper($data['encargado_cargo']) }}</div>
                <div class="rol">Entrega</div>
            </td>
            {{-- FIRMA DERECHA: RESPONSABLE --}}
            <td>
                <div class="linea"></div>
                <div class="nombre">{{ strtoupper($data['responsable_nombre']) }}</div>
                <div class="cargo">{{ strtoupper($data['responsable_cargo']) }}</div>
                <div class="rol">Recibe</div>
            </td>
        </tr>
    </table>

    {{-- ============ FOOTER ============ --}}
    <div class="footer">
        Documento generado por el Sistema de Gestión de Inventario Tecnológico - Gobernación del Estado Yaracuy
    </div>

</body>
</html>