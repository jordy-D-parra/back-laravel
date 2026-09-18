<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Retiro - {{ $data['numero_acta'] ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 40px;
            background: #fff;
            font-size: 11pt;
            line-height: 1.5;
            color: #1a1a1a;
        }
        .acta-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 35px 40px;
            border: 2px solid #1e3c72;
            background: #fff;
            position: relative;
        }
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
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
            width: 100%;
            text-align: center;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px double #1e3c72;
        }
        .header .logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-bottom: 12px;
        }
        .header .logos .logo-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .header .logos .logo-item img {
            height: 60px;
            width: auto;
            object-fit: contain;
        }
        .header .logos .logo-item .logo-label {
            font-size: 7pt;
            font-weight: 600;
            color: #1e3c72;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header .logos .separator {
            width: 2px;
            height: 60px;
            background: #1e3c72;
            opacity: 0.3;
        }
        .header .titulo-pais {
            font-size: 11pt;
            font-weight: 700;
            color: #1e3c72;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .header .titulo-gobierno {
            font-size: 13pt;
            font-weight: 700;
            color: #1e3c72;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header .titulo-depto {
            font-size: 11pt;
            font-weight: 600;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .titulo-acta {
            font-size: 16pt;
            font-weight: 700;
            color: #1e3c72;
            margin-top: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
            padding: 4px 20px;
            border-top: 2px solid #1e3c72;
            border-bottom: 2px solid #1e3c72;
            display: inline-block;
        }
        .numero-acta {
            text-align: center;
            font-size: 10pt;
            font-weight: 600;
            color: #1e3c72;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }
        .numero-acta span {
            background: #f0f4f8;
            padding: 2px 12px;
            border-radius: 4px;
            border: 1px solid #1e3c72;
        }
        .fecha {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 20px;
            color: #333;
            font-weight: 500;
        }
        .fecha strong {
            color: #1e3c72;
        }
        .cuerpo {
            margin-bottom: 20px;
            text-align: justify;
            font-size: 11pt;
            line-height: 1.8;
            padding: 0 5px;
        }
        .cuerpo .destacado {
            font-weight: 600;
            color: #1e3c72;
        }
        .datos-solicitud {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 6px;
            margin: 15px 0 18px 0;
            border-left: 4px solid #1e3c72;
            border-right: 1px solid #e9ecef;
            border-top: 1px solid #e9ecef;
            border-bottom: 1px solid #e9ecef;
        }
        .datos-solicitud .item {
            display: flex;
            margin-bottom: 4px;
            padding: 3px 0;
            border-bottom: 1px dashed #e9ecef;
        }
        .datos-solicitud .item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .datos-solicitud .label {
            font-weight: 700;
            min-width: 160px;
            color: #1e3c72;
            text-transform: uppercase;
            font-size: 10pt;
        }
        .datos-solicitud .valor {
            flex: 1;
            font-weight: 500;
        }
        .items-lista {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }
        .items-lista th {
            background: #1e3c72;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
        }
        .items-lista td {
            padding: 6px 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .items-lista tr:nth-child(even) {
            background: #f8fafc;
        }
        .firmas {
            display: flex;
            justify-content: space-between;
            margin-top: 35px;
            padding-top: 25px;
            border-top: 2px solid #1e3c72;
        }
        .firma-box {
            text-align: center;
            flex: 1;
            padding: 0 10px;
        }
        .firma-box .linea {
            border-top: 1.5px solid #000;
            width: 80%;
            margin: 35px auto 8px auto;
        }
        .firma-box .nombre {
            font-weight: 700;
            font-size: 11pt;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .firma-box .cargo {
            font-size: 9pt;
            color: #555;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .firma-box .rol {
            font-size: 9pt;
            font-weight: 700;
            color: #1e3c72;
            margin-top: 6px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 8pt;
            color: #6c757d;
            line-height: 1.8;
        }
        .footer .footer-marca {
            font-size: 7pt;
            color: #adb5bd;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="acta-container">
        <div class="watermark">ACTA DE RETIRO</div>

        <div class="header">
            <div class="logos">
                <div class="logo-item">
                    <img src="{{ asset('images/gobierno.jpeg') }}" alt="Gobierno Nacional" onerror="this.style.display='none'">
                </div>
                <div class="separator"></div>
                <div class="logo-item">
                    <img src="{{ asset('images/escudo-yaracuy.jpeg') }}" alt="Gobernación de Yaracuy">
                    <span class="logo-label">Gobernación del Estado Yaracuy</span>
                </div>
            </div>
            <div class="titulo-pais">República Bolivariana de Venezuela</div>
            <div class="titulo-gobierno">Gobernación del Estado Yaracuy</div>
            <div class="titulo-depto">Dirección de Informática</div>
            <div class="titulo-acta">Acta de Retiro</div>
        </div>

        <div class="numero-acta">
            <span>Nº {{ $data['numero_acta'] ?? 'N/A' }}</span>
        </div>

        <div class="fecha">
            <strong>San Felipe, {{ $data['fecha'] ?? date('d/m/Y') }}</strong>
        </div>

        <div class="cuerpo">
            <p>
                Mediante el presente instrumento, se certifica que el/la ciudadano(a)
                <span class="destacado">{{ $data['responsable_nombre'] ?? 'No especificado' }}</span>,
                en su carácter de <span class="destacado">{{ $data['responsable_cargo'] ?? 'Responsable' }}</span>
                de la entidad <span class="destacado">{{ $data['institucion'] ?? 'No especificada' }}</span>,
                ha retirado los siguientes equipos para su uso, los cuales se detallan a continuación:
            </p>
        </div>

        <div class="datos-solicitud">
            <div class="item">
                <span class="label">Solicitud Nº:</span>
                <span class="valor">{{ $data['codigo_solicitud'] ?? 'N/A' }}</span>
            </div>
            <div class="item">
                <span class="label">Fecha de Retiro:</span>
                <span class="valor">{{ $data['fecha'] ?? date('d/m/Y') }}</span>
            </div>
            <div class="item">
                <span class="label">Entidad:</span>
                <span class="valor">{{ $data['institucion'] ?? 'No especificada' }}</span>
            </div>
            <div class="item">
                <span class="label">Responsable:</span>
                <span class="valor">{{ $data['responsable_nombre'] ?? 'No especificado' }}</span>
            </div>
            <div class="item">
                <span class="label">Cédula:</span>
                <span class="valor">{{ $data['responsable_documento'] ?? 'N/A' }}</span>
            </div>
            <div class="item">
                <span class="label">Teléfono:</span>
                <span class="valor">{{ $data['responsable_telefono'] ?? 'N/A' }}</span>
            </div>
        </div>

        <table class="items-lista">
            <thead>
                <tr>
                    <th style="width: 15%;">Tipo</th>
                    <th style="width: 65%;">Descripción</th>
                    <th style="width: 20%; text-align: center;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['items'] as $item)
                <tr>
                    <td>{{ ucfirst($item['tipo_item']) }}</td>
                    <td>{{ $item['descripcion'] }}</td>
                    <td style="text-align: center;">{{ $item['cantidad'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="cuerpo">
            <p>
                El/la responsable abajo firmante se compromete a hacer uso adecuado de los equipos
                recibidos, así como a devolverlos en las mismas condiciones en que fueron entregados
                en la fecha acordada. Cualquier daño o pérdida será responsabilidad del firmante.
            </p>
        </div>

        <div class="firmas">
            <div class="firma-box">
                <div class="linea"></div>
                <div class="nombre">{{ strtoupper($data['responsable_nombre'] ?? 'No especificado') }}</div>
                <div class="cargo">{{ strtoupper($data['responsable_cargo'] ?? '') }}</div>
                <div class="rol">RETIRA</div>
            </div>
            <div class="firma-box">
                <div class="linea"></div>
                <div class="nombre">{{ strtoupper($data['encargado_nombre'] ?? 'Departamento de Informática') }}</div>
                <div class="cargo">{{ strtoupper($data['encargado_cargo'] ?? 'Director de Informática') }}</div>
                <div class="rol">ENTREGA</div>
            </div>
        </div>

        <div class="footer">
            <div class="footer-marca">
                Documento generado por el Sistema de Gestión de Inventario Tecnológico - Gobernación de Yaracuy
            </div>
        </div>
    </div>
</body>
</html>