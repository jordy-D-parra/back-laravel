<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1a1a2e; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1a1a2e; }
        .stats-grid { display: flex; flex-wrap: wrap; justify-content: space-between; margin: 20px 0; }
        .stat-card { background-color: #f8f9fa; border-radius: 8px; padding: 10px; text-align: center; width: 30%; margin-bottom: 10px; }
        .stat-number { font-size: 24px; font-weight: bold; color: #4361ee; }
        .stat-label { font-size: 10px; color: #6c757d; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #dee2e6; padding: 6px; text-align: left; }
        th { background-color: #1a1a2e; color: white; }
        .section-title { background-color: #e9ecef; padding: 6px; margin-top: 15px; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>Generado: {{ $fecha_generacion->format('d/m/Y H:i:s') }} | Por: {{ $usuario_genero }}</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_solicitudes'] }}</div>
            <div class="stat-label">Total Solicitudes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_prestamos'] }}</div>
            <div class="stat-label">Total Préstamos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_usuarios'] }}</div>
            <div class="stat-label">Usuarios Activos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_equipos'] }}</div>
            <div class="stat-label">Total Equipos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['solicitudes_pendientes'] }}</div>
            <div class="stat-label">Solicitudes Pendientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['prestamos_activos'] }}</div>
            <div class="stat-label">Préstamos Activos</div>
        </div>
    </div>

    <div class="section-title">📊 Solicitudes por Mes (Últimos 12 meses)</div>
    <table>
        <thead><tr><th>Período</th><th>Cantidad</th></tr></thead>
        <tbody>
            @foreach($solicitudesPorMes as $item)
            <tr><td>{{ $item->año }}-{{ str_pad($item->mes, 2, '0', STR_PAD_LEFT) }}</td><td>{{ $item->total }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">🏆 Top 10 Solicitantes</div>
    <table>
        <thead><tr><th>Usuario</th><th>Solicitudes</th></tr></thead>
        <tbody>
            @foreach($topSolicitantes as $item)
            <tr><td>{{ $item->solicitante->name ?? 'N/A' }}</td><td>{{ $item->total }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Resumen ejecutivo del Sistema de Gestión de Préstamos</p>
    </div>
</body>
</html>