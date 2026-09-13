<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #e63946; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #e63946; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #dee2e6; padding: 6px; text-align: left; }
        th { background-color: #e63946; color: white; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 5px; }
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
                <th>Equipo</th>
                <th>Reportado por</th>
                <th>Fecha Ingreso</th>
                <th>Técnico</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fichas as $ficha)
            <tr>
                <td>{{ $ficha->id }}</td>
                <td>{{ $ficha->activo->serial ?? $ficha->observaciones }}</td>
                <td>{{ $ficha->usuarioReporta->name ?? 'N/A' }}</td>
                <td>{{ $ficha->created_at->format('d/m/Y') }}</td>
                <td>{{ $ficha->tecnico->name ?? 'No asignado' }}</td>
                <td>{{ ucfirst($ficha->estado) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte de fichas de soporte técnico</p>
    </div>
</body>
</html>
