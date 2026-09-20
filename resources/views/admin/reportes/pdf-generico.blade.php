<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 10pt; padding: 20px; color: #1a1a1a; }
        .header { text-align: center; padding-bottom: 15px; border-bottom: 2px solid #1e3c72; margin-bottom: 20px; }
        .header .logos { display: flex; justify-content: center; align-items: center; gap: 30px; margin-bottom: 10px; }
        .header .logos img { height: 50px; }
        .header h1 { color: #1e3c72; font-size: 16pt; text-transform: uppercase; letter-spacing: 2px; }
        .header .fecha { color: #6c757d; font-size: 9pt; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 8pt; margin-top: 10px; }
        table thead th {
            background: #1e3c72; color: white; padding: 6px 8px;
            text-align: left; font-weight: 600; text-transform: uppercase;
            font-size: 7pt; border: 1px solid #1e3c72;
        }
        table tbody td { padding: 5px 8px; border: 1px solid #e9ecef; }
        table tbody tr:nth-child(even) { background: #f8f9fc; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e9ecef; text-align: center; font-size: 7pt; color: #6c757d; }
        .no-print { text-align: center; margin-bottom: 20px; padding: 10px; }
        .btn-print { background: #1e3c72; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
    </div>

    <div class="header">
        <div class="logos">
            <img src="{{ asset('images/gobierno.jpeg') }}" onerror="this.style.display='none'">
            <img src="{{ asset('images/escudo-yaracuy.jpeg') }}" alt="Yaracuy">
        </div>
        <div style="font-size: 11pt; font-weight: 700; color: #1e3c72;">REPÚBLICA BOLIVARIANA DE VENEZUELA</div>
        <div style="font-size: 13pt; font-weight: 700; color: #1e3c72;">GOBERNACIÓN DEL ESTADO YARACUY</div>
        <h1>{{ $titulo }}</h1>
        <div class="fecha">Generado: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    @if($seccion === 'inventario')
        <h3>Activos ({{ count($data['activos']) }})</h3>
        <table>
            <thead><tr><th>Serial</th><th>Modelo</th><th>Marca</th><th>Categoría</th><th>Estado</th><th>Institución</th></tr></thead>
            <tbody>
                @foreach($data['activos'] as $a)
                    <tr>
                        <td>{{ $a['serial'] }}</td>
                        <td>{{ $a['modelo']['nombre'] ?? 'N/A' }}</td>
                        <td>{{ $a['modelo']['marca']['nombre'] ?? 'N/A' }}</td>
                        <td>{{ $a['modelo']['categoria']['nombre'] ?? 'N/A' }}</td>
                        <td>{{ $a['estatus']['descripcion'] ?? 'N/A' }}</td>
                        <td>{{ $a['institucion']['nombre'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($seccion === 'solicitudes')
        <h3>Solicitudes ({{ count($data['solicitudes']) }})</h3>
        <table>
            <thead><tr><th>#</th><th>Fecha</th><th>Entidad</th><th>Responsable</th><th>Prioridad</th><th>Estado</th></tr></thead>
            <tbody>
                @foreach($data['solicitudes'] as $s)
                    <tr>
                        <td>{{ $s['id'] }}</td>
                        <td>{{ $s['fecha_solicitud'] }}</td>
                        <td>{{ $s['departamento']['nombre'] ?? $s['institucion']['nombre'] ?? '---' }}</td>
                        <td>{{ $s['responsable']['nombre'] ?? '---' }}</td>
                        <td>{{ $s['prioridad'] }}</td>
                        <td>{{ $s['estado_solicitud'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <h3>Fichas de Soporte ({{ count($data['fichas']) }})</h3>
        <table>
            <thead><tr><th>#</th><th>Equipo</th><th>Técnico</th><th>Reporta</th><th>Ingreso</th><th>Estado</th></tr></thead>
            <tbody>
                @foreach($data['fichas'] as $f)
                    <tr>
                        <td>{{ $f['id'] }}</td>
                        <td>{{ $f['activo']['serial'] ?? 'Externo' }}</td>
                        <td>{{ $f['tecnico_nombre'] ?? '---' }}</td>
                        <td>{{ $f['usuario_reporta_nombre'] ?? '---' }}</td>
                        <td>{{ $f['fecha_ingreso'] }}</td>
                        <td>{{ $f['estado'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Sistema de Gestión de Inventario Tecnológico - Gobernación del Estado Yaracuy
    </div>
</body>
</html>