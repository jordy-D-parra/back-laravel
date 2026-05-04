<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #17a2b8; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #17a2b8; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        th { background-color: #17a2b8; color: white; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 5px; }
        .section-title { background-color: #e9ecef; padding: 6px; margin-top: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>Generado: {{ $fecha_generacion->format('d/m/Y H:i:s') }} | Por: {{ $usuario_genero }}</p>
    </div>

    <div class="section-title">💻 ACTIVOS DISPONIBLES</div>
    <table>
        <thead>
            <tr>
                <th>Serial</th>
                <th>Tipo</th>
                <th>Marca/Modelo</th>
                <th>Cantidad</th>
                <th>Ubicación</th>
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
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">🖱️ PERIFÉRICOS DISPONIBLES</div>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Marca</th>
                <th>Disponibles</th>
                <th>Ubicación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($perifericos as $periferico)
            <tr>
                <td>{{ $periferico->nombre }}</td>
                <td>{{ $periferico->tipo ?? '-' }}</td>
                <td>{{ $periferico->marca ?? '-' }}</td>
                <td>{{ $periferico->cantidad_disponible }}</td>
                <td>{{ $periferico->ubicacion ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Equipos listos para préstamo inmediato</p>
    </div>
</body>
</html>
