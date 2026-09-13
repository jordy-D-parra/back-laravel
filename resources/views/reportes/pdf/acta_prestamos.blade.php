<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acta de Préstamo #{{ $prestamo->id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #1a1a2e; font-size: 18px; }
        .header p { margin: 5px 0; color: #6c757d; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin: 20px 0; text-decoration: underline; }
        .info-section { margin-bottom: 20px; }
        .info-section h3 { background-color: #f8f9fa; padding: 8px; margin: 0 0 10px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; }
        .signature { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature div { text-align: center; width: 45%; }
        .signature-line { border-top: 1px solid #000; margin-top: 30px; padding-top: 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SISTEMA DE GESTIÓN DE PRÉSTAMOS</h1>
        <p>ACTA DE PRÉSTAMO DE EQUIPOS</p>
        <p>N° {{ str_pad($prestamo->id, 6, '0', STR_PAD_LEFT) }}</p>
    </div>

    <div class="info-section">
        <h3>📋 DATOS DEL PRÉSTAMO</h3>
        <table>
            <tr><td style="width: 30%;"><strong>Fecha de Préstamo:</strong></td><td>{{ date('d/m/Y', strtotime($prestamo->fecha_prestamo)) }}</td></tr>
            <tr><td><strong>Hora de Préstamo:</strong></td><td>{{ $prestamo->hora_prestamo }}</td></tr>
            <tr><td><strong>Fecha Estimada Devolución:</strong></td><td>{{ date('d/m/Y', strtotime($prestamo->fecha_retorno_estimada)) }}</td></tr>
            <tr><td><strong>Tipo de Préstamo:</strong></td><td>{{ ucfirst($prestamo->tipo_prestamo) }}</td></tr>
        </table>
    </div>

    <div class="info-section">
        <h3>👤 SOLICITANTE</h3>
        <table>
            <tr><td style="width: 30%;"><strong>Nombre:</strong></td><td>{{ $prestamo->solicitud->solicitante->name ?? 'N/A' }}</td></tr>
            <tr><td><strong>Cédula:</strong></td><td>{{ $prestamo->solicitud->solicitante->cedula ?? 'N/A' }}</td></tr>
            <tr><td><strong>Departamento:</strong></td><td>{{ $prestamo->solicitud->solicitante->departamento ?? 'N/A' }}</td></tr>
            <tr><td><strong>Institución:</strong></td><td>{{ $prestamo->solicitud->institucion->nombre ?? 'N/A' }}</td></tr>
        </table>
    </div>

    <div class="info-section">
        <h3>📦 EQUIPOS PRESTADOS</h3>
        <table>
            <thead>
                <tr><th>Tipo</th><th>Equipo</th><th>Cantidad</th></tr>
            </thead>
            <tbody>
                @foreach($prestamo->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->tipo_item == 'activo' ? '💻 Activo' : '🖱️ Periférico' }}</td>
                    <td>
                        @if($detalle->tipo_item == 'activo')
                            {{ $detalle->activo->marca_modelo ?? 'N/A' }} ({{ $detalle->activo->serial ?? 'N/A' }})
                        @else
                            {{ $detalle->periferico->nombre ?? 'N/A' }}
                        @endif
                    </td>
                    <td>{{ $detalle->cantidad }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="info-section">
        <h3>👨‍🔧 RESPONSABLES</h3>
        <table>
            <tr><td style="width: 30%;"><strong>Técnico que entrega:</strong></td><td>{{ $prestamo->tecnico->name ?? 'N/A' }}</td></tr>
            <tr><td><strong>Responsable que recibe:</strong></td><td>{{ $prestamo->responsable->nombre ?? 'N/A' }}</td></tr>
        </table>
    </div>

    <div class="signature">
        <div>
            <div class="signature-line">_________________________</div>
            <p>Firma del Solicitante</p>
            <p>{{ $prestamo->solicitud->solicitante->name ?? 'N/A' }}</p>
        </div>
        <div>
            <div class="signature-line">_________________________</div>
            <p>Firma del Técnico</p>
            <p>{{ $prestamo->tecnico->name ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="footer">
        <p>Este documento es un comprobante del préstamo de equipos. Debe ser presentado al momento de la devolución.</p>
    </div>
</body>
</html>
