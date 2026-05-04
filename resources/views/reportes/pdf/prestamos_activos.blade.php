<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2a9d8f; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #2a9d8f; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        th { background-color: #2a9d8f; color: white; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 5px; }
        .resumen { margin-top: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; }
        .vencido { color: #e63946; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>Generado: {{ $fecha_generacion->format('d/m/Y H:i:s') }} | Por: {{ $usuario_genero }}</p>
    </div>

    <div class="resumen">
        <strong>Resumen:</strong> Total de préstamos activos: {{ $total }} | Vencidos: {{ $vencidos->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Solicitante</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Retorno Estimada</th>
                <th>Estado</th>
                <th>Items</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prestamos as $prestamo)
            <tr>
                <td>{{ $prestamo->id }}</td>
                <td>{{ $prestamo->solicitud->solicitante->name ?? 'N/A' }}</td>
                <td>{{ date('d/m/Y', strtotime($prestamo->fecha_prestamo)) }}</td>
                <td class="{{ $prestamo->fecha_retorno_estimada < date('Y-m-d') ? 'vencido' : '' }}">
                    {{ date('d/m/Y', strtotime($prestamo->fecha_retorno_estimada)) }}
                    @if($prestamo->fecha_retorno_estimada < date('Y-m-d'))
                        (VENCIDO)
                    @endif
                </td>
                <td>{{ ucfirst($prestamo->estado_prestamo) }}</td>
                <td>{{ $prestamo->detalles->sum('cantidad') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Gestión de Préstamos</p>
    </div>
</body>
</html>