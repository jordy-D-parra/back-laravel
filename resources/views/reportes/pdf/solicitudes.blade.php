<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4361ee; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #4361ee; }
        .header p { margin: 5px 0; color: #6c757d; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        th { background-color: #4361ee; color: white; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 5px; }
        .badge-pendiente { color: #856404; background-color: #fff3cd; padding: 2px 6px; border-radius: 4px; }
        .badge-aprobada { color: #155724; background-color: #d4edda; padding: 2px 6px; border-radius: 4px; }
        .badge-rechazada { color: #721c24; background-color: #f8d7da; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>{{ $subtitulo }}</p>
        <p>Generado: {{ $fecha_generacion->format('d/m/Y H:i:s') }} | Por: {{ $usuario_genero }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Solicitante</th>
                <th>Fecha Solicitud</th>
                <th>Fecha Requerida</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Institución</th>
            </tr>
        </thead>
        <tbody>
            @foreach($solicitudes as $solicitud)
            <tr>
                <td>{{ $solicitud->id }}</td>
                <td>{{ $solicitud->solicitante->name ?? 'N/A' }}</td>
                <td>{{ $solicitud->fecha_solicitud->format('d/m/Y') }}</td>
                <td>{{ $solicitud->fecha_requerida ? date('d/m/Y', strtotime($solicitud->fecha_requerida)) : '-' }}</td>
                <td>{{ ucfirst($solicitud->prioridad) }}</td>
                <td>{{ ucfirst($solicitud->estado_solicitud) }}</td>
                <td>{{ $solicitud->institucion->nombre ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Gestión de Préstamos</p>
    </div>
</body>
</html>
