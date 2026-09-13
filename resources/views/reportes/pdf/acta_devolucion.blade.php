<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acta de Devolución #{{ $prestamo->id }}</title>
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
        .observaciones { margin-top: 20px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #e63946; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SISTEMA DE GESTIÓN DE PRÉSTAMOS</h1>
        <p>ACTA DE DEVOLUCIÓN DE EQUIPOS</p>
        <p>N° {{ str_pad($prestamo->id, 6, '0', STR_PAD_LEFT) }}</p>
    </div>

    <div class="info-section">
        <h3>📋 DATOS DEL PRÉSTAMO ORIGINAL</h3>
        <table>
            <tr><td style="width: 35%;"><strong>Fecha de Préstamo:</strong></td><td>{{ date('d/m/Y', strtotime($prestamo->fecha_prestamo)) }}</td></tr>
            <tr><td><strong>Fecha Estimada Devolución:</strong></td><td>{{ date('d/m/Y', strtotime($prestamo->fecha_retorno_estimada)) }}</td></tr>
            <tr><td><strong>Fecha Real Devolución:</strong></td><td>{{ date('d/m/Y', strtotime($prestamo->fecha_retorno_real ?? now())) }}</td></tr>
        </table>
    </div>

    <div class="info-section">
        <h3>👤 SOLICITANTE</h3>
        <table>
            <tr><td style="width: 35%;"><strong>Nombre:</strong></td><td>{{ $prestamo->solicitud->solicitante->name ?? 'N/A' }}</td></tr>
            <tr><td><strong>Cédula:</strong></td><td>{{ $prestamo->solicitud->solicitante->cedula ?? 'N/A' }}</td></tr>
        </table>
    </div>

    <div class="info-section">
        <h3>📦 EQUIPOS DEVUELTOS</h3>
        <table>
            <thead>
                <tr><th>Tipo</th><th>Equipo</th><th>Cantidad</th><th>Estado</th></tr>
            </thead>
            <tbody>
                @foreach($prestamo->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->tipo_item == 'activo' ? '💻 Activo' : '🖱️ Periférico' }}</td>
                    <td>
                        @if($detalle->tipo_item == 'activo')
                            {{ $detalle->activo->marca_modelo ?? 'N/A' }}
                        @else
                            {{ $detalle->periferico->nombre ?? 'N/A' }}
                        @endif
                    </td>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>{{ $detalle->devuelto ? '✅ Devuelto' : '⏳ Pendiente' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(isset($prestamo->observaciones_devolucion))
    <div class="observaciones">
        <strong>📝 Observaciones de la Devolución:</strong><br>
        {{ $prestamo->observaciones_devolucion }}
    </div>
    @endif

    <div class="signature">
        <div>
            <div class="signature-line">_________________________</div>
            <p>Firma del Solicitante</p>
        </div>
        <div>
            <div class="signature-line">_________________________</div>
            <p>Firma del Técnico que Recibe</p>
        </div>
    </div>

    <div class="footer">
        <p>Documento que certifica la devolución de los equipos en buenas condiciones.</p>
    </div>
</body>
</html>
