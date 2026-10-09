<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} - SOP-{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 12mm 12mm 15mm 12mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Times New Roman', serif;
            font-size: 9.5pt;
            color: #1a1a1a;
            line-height: 1.35;
        }

        /* ============ HEADER ============ */
        .pdf-header {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #1e3c72;
            margin-bottom: 12px;
        }
        .pdf-header td {
            vertical-align: middle;
            padding-bottom: 6px;
        }
        .pdf-header .td-logo { width: 90px; text-align: center; }
        .pdf-header .td-logo img { height: 48px; width: auto; }
        .pdf-header .td-logo .logo-label {
            display: block;
            font-size: 5.5pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            margin-top: 1px;
            line-height: 1.05;
        }
        .pdf-header .td-center { text-align: center; padding: 0 8px; }
        .pdf-header .titulo-pais {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .pdf-header .titulo-gobierno {
            font-size: 10pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .pdf-header .titulo-depto {
            font-size: 8pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
        }
        .pdf-header .titulo-reporte {
            font-size: 11pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 3px 18px;
            border-top: 1.5px solid #1e3c72;
            border-bottom: 1.5px solid #1e3c72;
            display: inline-block;
            margin-top: 6px;
        }

        /* ============ META ============ */
        .pdf-meta {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            color: #475569;
            margin-bottom: 10px;
        }
        .pdf-meta td { padding: 2px 0; vertical-align: top; }
        .pdf-meta .td-right { text-align: right; }
        .pdf-meta strong { color: #1e3c72; }

        /* ============ HERO ============ */
        .hero-card {
            background: #f8fafc;
            border-left: 4px solid #1e3c72;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 12px;
        }
        .hero-table { width: 100%; border-collapse: collapse; }
        .hero-table td { vertical-align: middle; }
        .hero-codigo {
            font-size: 16pt;
            font-weight: bold;
            color: #1e3c72;
            letter-spacing: 0.5px;
        }
        .hero-desc {
            font-size: 9pt;
            color: #64748b;
            margin-top: 2px;
        }
        .hero-badges { text-align: right; }
        .badge-estado {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #ffffff;
            margin-left: 4px;
        }

        /* ============ SECCIONES ============ */
        .pdf-section-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1.5px solid #1e3c72;
            padding-bottom: 3px;
            margin: 14px 0 8px 0;
        }

        /* ============ DATA GRID ============ */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .data-table td {
            padding: 6px 10px;
            border: 1px solid #e9ecef;
            vertical-align: top;
        }
        .data-table .label {
            font-size: 7pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            width: 22%;
            background: #f8fafc;
        }
        .data-table .value {
            font-weight: 500;
            color: #1a1a1a;
        }
        .data-table .value.muted {
            color: #94a3b8;
            font-style: italic;
            font-weight: normal;
        }

        /* ============ TABLA DE COMPONENTES ============ */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 4px;
        }
        .items-table thead th {
            background: #1e3c72;
            color: #ffffff;
            padding: 6px 8px;
            text-align: left;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #1e3c72;
        }
        .items-table tbody td {
            padding: 6px 8px;
            border: 1px solid #e9ecef;
            vertical-align: top;
        }
        .items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .items-table .text-center { text-align: center; }

        .item-badge {
            display: inline-block;
            padding: 1.5px 7px;
            border-radius: 10px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            white-space: nowrap;
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #3b82f6;
        }
        .item-badge.success { background: #ecfdf5; color: #065f46; border-color: #10b981; }
        .item-badge.warning { background: #fef9e7; color: #92400e; border-color: #f59e0b; }
        .item-badge.danger  { background: #fef2f2; color: #991b1b; border-color: #ef4444; }
        .item-badge.gray    { background: #f8fafc; color: #475569; border-color: #cbd5e1; }

        /* ============ TEXTOS LARGOS ============ */
        .observaciones-box {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 9pt;
            color: #334155;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
            min-height: 40px;
        }
        .observaciones-box.vacio {
            background: #f8fafc;
            border-left-color: #cbd5e1;
            color: #94a3b8;
            font-style: italic;
        }

        .diagnostico-box {
            background: #f8fafc;
            border-left: 4px solid #1e3c72;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 9pt;
            color: #334155;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .trabajo-box {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 9pt;
            color: #334155;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* ============ FIRMAS ============ */
        .firmas-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .firmas-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 30px;
        }
        .firmas-table .linea {
            border-top: 1.3px solid #000;
            width: 100%;
            height: 1px;
            margin-bottom: 4px;
        }
        .firmas-table .nombre {
            font-weight: bold;
            font-size: 8.5pt;
            color: #1e3c72;
            text-transform: uppercase;
        }
        .firmas-table .cargo {
            font-size: 7pt;
            color: #555;
            text-transform: uppercase;
            margin-top: 1px;
        }
        .firmas-table .rol {
            font-size: 7pt;
            font-weight: bold;
            color: #1e3c72;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* ============ FOOTER ============ */
        .pdf-footer {
            position: fixed;
            bottom: -8mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 6.5pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }

        /* ============ BOTONES (solo en pantalla) ============ */
        .btn-print {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
            background: #1e3c72;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print:hover { background: #2a5298; }
        .btn-print svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2; }
        .btn-cerrar {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 999;
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-cerrar:hover { background: #5a6268; }
        @media print {
            .btn-print, .btn-cerrar { display: none !important; }
        }
    </style>
</head>
<body>

    {{-- ============ BOTONES FLOTANTES ============ --}}
    <button class="btn-print" onclick="window.print()">
        <svg viewBox="0 0 24 24">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M18 9H6"/>
            <rect x="4" y="12" width="16" height="10" rx="1"/>
            <line x1="8" y1="17" x2="16" y2="17"/>
        </svg>
        Imprimir / Guardar
    </button>
    <button class="btn-cerrar" onclick="window.close()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
        Cerrar
    </button>

    {{-- ============ HEADER ============ --}}
    @php
        $codigo = 'SOP-' . str_pad($record->id, 6, '0', STR_PAD_LEFT);
        $estado = strtolower($record->estado ?? 'en_proceso');

        $colorEstado = match($estado) {
            'en_proceso' => '#f59e0b',
            'aceptada'   => '#3b82f6',
            'rechazada'  => '#ef4444',
            'finalizado' => '#10b981',
            'cancelada'  => '#64748b',
            default      => '#1e3c72',
        };

        $estadoLabel = match($estado) {
            'en_proceso' => 'En Proceso',
            'aceptada'   => 'Aceptada',
            'rechazada'  => 'Rechazada',
            'finalizado' => 'Finalizado',
            'cancelada'  => 'Cancelada',
            default      => ucfirst($estado),
        };

        $activo = $record->activo;
        $serial = $activo?->serial ?? 'Equipo Externo';
        $marca = $activo?->modelo?->marca?->nombre ?? 'N/A';
        $modeloNombre = $activo?->modelo?->nombre ?? 'N/A';
        $categoria = $activo?->modelo?->categoria?->nombre ?? 'N/A';
        $institucion = $activo?->institucion?->nombre ?? 'No especificada';
        $responsable = $activo?->responsable?->nombre ?? 'No asignado';

        $tecnicoNombre = $record->tecnico_nombre ?? ($record->tecnico?->trabajador
            ? trim($record->tecnico->trabajador->nombre . ' ' . $record->tecnico->trabajador->apellido)
            : ($record->tecnico?->usuario ?? 'No asignado'));

        $usuarioReporta = $record->usuario_reporta_nombre ?? 'No especificado';
    @endphp

    <table class="pdf-header">
        <tr>
            <td class="td-logo">
                @if(file_exists(public_path('images/gobierno.jpeg')))
                    <img src="{{ public_path('images/gobierno.jpeg') }}" alt="Gobierno">
                @endif
                <span class="logo-label">Gobierno<br>Bolivariano</span>
            </td>
            <td class="td-center">
                <div class="titulo-pais">Republica Bolivariana de Venezuela</div>
                <div class="titulo-gobierno">Gobernacion del Estado Yaracuy</div>
                <div class="titulo-depto">Direccion de Informatica</div>
                <div class="titulo-reporte">Ficha Tecnica de Soporte</div>
            </td>
            <td class="td-logo">
                @if(file_exists(public_path('images/escudo-yaracuy.jpeg')))
                    <img src="{{ public_path('images/escudo-yaracuy.jpeg') }}" alt="Escudo">
                @endif
                <span class="logo-label">Gobernacion<br>del Estado Yaracuy</span>
            </td>
        </tr>
    </table>

    {{-- ============ META ============ --}}
    <table class="pdf-meta">
        <tr>
            <td><strong>Documento:</strong> FICHA-SOP-{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}</td>
            <td class="td-right"><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Generado por:</strong> {{ auth()->user()->nombre_completo ?? auth()->user()->usuario ?? 'Sistema' }}</td>
            <td class="td-right"><strong>Hash:</strong> {{ strtoupper(substr(sha1($record->id . '|' . $codigo . '|' . now()->timestamp), 0, 10)) }}</td>
        </tr>
    </table>

    {{-- ============ HERO ============ --}}
    <div class="hero-card">
        <table class="hero-table">
            <tr>
                <td>
                    <div class="hero-codigo">{{ $codigo }}</div>
                    <div class="hero-desc">
                        {{ $serial }} &nbsp;·&nbsp; {{ $marca }} {{ $modeloNombre }}
                    </div>
                </td>
                <td class="hero-badges">
                    <span class="badge-estado" style="background: {{ $colorEstado }};">
                        {{ $estadoLabel }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ============ DATOS DEL EQUIPO ============ --}}
    <div class="pdf-section-title">Datos del Equipo</div>

    <table class="data-table">
        <tr>
            <td class="label">Serial</td>
            <td class="value">{{ $serial }}</td>
            <td class="label">Categoria</td>
            <td class="value">{{ $categoria }}</td>
        </tr>
        <tr>
            <td class="label">Marca</td>
            <td class="value">{{ $marca }}</td>
            <td class="label">Modelo</td>
            <td class="value">{{ $modeloNombre }}</td>
        </tr>
        <tr>
            <td class="label">Institucion</td>
            <td class="value">{{ $institucion }}</td>
            <td class="label">Responsable</td>
            <td class="value">{{ $responsable }}</td>
        </tr>
    </table>

    {{-- ============ DATOS DEL SERVICIO ============ --}}
    <div class="pdf-section-title">Datos del Servicio Tecnico</div>

    <table class="data-table">
        <tr>
            <td class="label">Tecnico</td>
            <td class="value {{ empty($tecnicoNombre) || $tecnicoNombre === 'No asignado' ? 'muted' : '' }}">
                {{ $tecnicoNombre }}
            </td>
            <td class="label">Reporta</td>
            <td class="value">{{ $usuarioReporta }}</td>
        </tr>
        <tr>
            <td class="label">Fecha Ingreso</td>
            <td class="value">
                {{ $record->fecha_ingreso ? \Carbon\Carbon::parse($record->fecha_ingreso)->format('d/m/Y H:i') : 'No registrada' }}
            </td>
            <td class="label">Fecha Requerida</td>
            <td class="value {{ $record->fecha_requerida_entrega ? '' : 'muted' }}">
                {{ $record->fecha_requerida_entrega ? \Carbon\Carbon::parse($record->fecha_requerida_entrega)->format('d/m/Y') : 'No definida' }}
            </td>
        </tr>
        <tr>
            <td class="label">Fecha Aceptacion</td>
            <td class="value {{ $record->fecha_aceptacion ? '' : 'muted' }}">
                {{ $record->fecha_aceptacion ? \Carbon\Carbon::parse($record->fecha_aceptacion)->format('d/m/Y H:i') : 'No aceptada aun' }}
            </td>
            <td class="label">Fecha Salida</td>
            <td class="value {{ $record->fecha_salida ? '' : 'muted' }}">
                {{ $record->fecha_salida ? \Carbon\Carbon::parse($record->fecha_salida)->format('d/m/Y H:i') : 'En proceso' }}
            </td>
        </tr>
        @if($record->fecha_rechazo)
        <tr>
            <td class="label">Fecha Rechazo</td>
            <td class="value">
                {{ \Carbon\Carbon::parse($record->fecha_rechazo)->format('d/m/Y H:i') }}
            </td>
            <td class="label">Motivo Rechazo</td>
            <td class="value">{{ $record->motivo_rechazo ?: 'No especificado' }}</td>
        </tr>
        @endif
    </table>

    {{-- ============ DIAGNOSTICO ============ --}}
    <div class="pdf-section-title">Diagnostico Inicial</div>
    <div class="diagnostico-box {{ empty($record->diagnostico) ? 'vacio' : '' }}">
        {{ $record->diagnostico ?: 'Sin diagnostico registrado.' }}
    </div>

    {{-- ============ TRABAJO REALIZADO ============ --}}
    @if($record->trabajo_realizado)
        <div class="pdf-section-title">Trabajo Realizado</div>
        <div class="trabajo-box">
            {{ $record->trabajo_realizado }}
        </div>
    @endif

    {{-- ============ OBSERVACIONES ============ --}}
    <div class="pdf-section-title">Observaciones</div>
    <div class="observaciones-box {{ empty($record->observaciones) ? 'vacio' : '' }}">
        {{ $record->observaciones ?: 'Sin observaciones registradas.' }}
    </div>

    {{-- ============ COMPONENTES REVISADOS ============ --}}
    <div class="pdf-section-title">
        Componentes Revisados ({{ $record->detalles?->count() ?? 0 }})
    </div>

    @if($record->detalles && $record->detalles->count() > 0)
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Componente</th>
                    <th style="width: 18%;">Estado Ingreso</th>
                    <th style="width: 18%;">Estado Salida</th>
                    <th style="width: 19%;">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($record->detalles as $det)
                    @php
                        $estadoIngreso = $det->estado_ingreso ?? 'no_aplica';
                        $estadoSalida = $det->estado_salida ?? 'pendiente';

                        $badgeIngreso = match($estadoIngreso) {
                            'funcionando' => 'success',
                            'dañado'      => 'danger',
                            'desgastado'  => 'warning',
                            'no_aplica'   => 'gray',
                            default       => 'gray',
                        };

                        $badgeSalida = match($estadoSalida) {
                            'funcionando' => 'success',
                            'reparado'    => 'success',
                            'reemplazado' => 'warning',
                            'dañado'      => 'danger',
                            'no_aplica'   => 'gray',
                            'pendiente'   => 'warning',
                            default       => 'gray',
                        };
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $det->componente_nombre ?: ($det->componente?->tipo ?? 'Componente') }}</strong>
                            @if($det->componente)
                                <br><small style="color:#64748b;">{{ $det->componente->marca ?? '' }} {{ $det->componente->modelo ?? '' }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="item-badge {{ $badgeIngreso }}">
                                {{ ucfirst(str_replace('_', ' ', $estadoIngreso)) }}
                            </span>
                        </td>
                        <td>
                            <span class="item-badge {{ $badgeSalida }}">
                                {{ ucfirst(str_replace('_', ' ', $estadoSalida)) }}
                            </span>
                        </td>
                        <td>{{ $det->observaciones ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="observaciones-box vacio">
            Esta ficha no tiene componentes registrados.
        </div>
    @endif

    {{-- ============ FIRMAS ============ --}}
    <table class="firmas-table">
        <tr>
            <td>
                <div class="linea"></div>
                <div class="nombre">{{ $tecnicoNombre }}</div>
                <div class="cargo">Tecnico de Soporte</div>
                <div class="rol">Realiza</div>
            </td>
            <td>
                <div class="linea"></div>
                <div class="nombre">Departamento de Informatica</div>
                <div class="cargo">Gobernacion del Estado Yaracuy</div>
                <div class="rol">Revisa</div>
            </td>
        </tr>
    </table>

    {{-- ============ FOOTER ============ --}}
    <div class="pdf-footer">
        Sistema de Gestion de Inventario Tecnologico - Gobernacion del Estado Yaracuy
        <br>
        FICHA-SOP-{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }} &middot; Generado el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>