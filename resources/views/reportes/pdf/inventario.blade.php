<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2e7d32; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #2e7d32; font-size: 20px; }
        .header p { margin: 5px 0; color: #6c757d; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #dee2e6; padding: 6px; text-align: left; }
        th { background-color: #2e7d32; color: white; font-size: 11px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 5px; }
        .resumen { margin-top: 15px; padding: 8px; background-color: #f8f9fa; border-radius: 5px; display: flex; flex-wrap: wrap; justify-content: space-between; font-size: 10px; }
        .section-title { background-color: #e9ecef; padding: 6px; margin-top: 15px; font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>Generado: {{ $fecha_generacion->format('d/m/Y H:i:s') }} | Por: {{ $usuario_genero }}</p>
    </div>

    <div class="resumen">
        <div><strong>Total Activos:</strong> {{ $resumen['total_activos'] }}</div>
        <div><strong>Total Periféricos:</strong> {{ $resumen['total_perifericos'] }}</div>
        <div><strong>Disponibles:</strong> {{ $resumen['activos_disponibles'] }}</div>
        <div><strong>Prestados:</strong> {{ $resumen['activos_prestados'] }}</div>
        <div><strong>Valor Total:</strong> ${{ number_format($resumen['valor_total'], 2) }}</div>
    </div>

    <div class="section-title">📟 ACTIVOS</div>
    <table>
        <thead>
            <tr>
                <th>Serial</th>
                <th>Tipo</th>
                <th>Marca/Modelo</th>
                <th>Cantidad</th>
                <th>Ubicación</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activos as $activo)
            <tr>
                <td>{{ $activo->serial }}</td>
                <td>{{ $activo->tipo_equipo }}</td>
                <td>{{ $activo->marca_modelo }}</td>
                <td>{{ $activo->cantidad }}</td>
                <td>{{ $activo->ubicacion ?? '-' }}</td>
                <td>${{ number_format($activo->valor_compra, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">🖱️ PERIFÉRICOS</div>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Marca</th>
                <th>Serial</th>
                <th>Total</th>
                <th>Disponible</th>
                <th>Ubicación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($perifericos as $periferico)
            <tr>
                <td>{{ $periferico->nombre }}</td>
                <td>{{ $periferico->tipo ?? '-' }}</td>
                <td>{{ $periferico->marca ?? '-' }}</td>
                <td>{{ $periferico->serial ?? '-' }}</td>
                <td>{{ $periferico->cantidad_total }}</td>
                <td>{{ $periferico->cantidad_disponible }}</td>
                <td>{{ $periferico->ubicacion ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Gestión de Préstamos</p>
    </div>
</body>
</html>
