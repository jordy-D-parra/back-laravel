<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} - SOL-{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}</title>
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

        /* ============ TABLA ITEMS ============ */
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
        .item-badge.componente {
            background: #f5f3ff;
            color: #5b21b6;
            border-color: #8b5cf6;
        }

        .cantidad-badge {
            display: inline-block;
            min-width: 24px;
            padding: 2px 8px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #334155;
            font-weight: bold;
            font-size: 8pt;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        /* ============ OBSERVACIONES ============ */
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

        .justificacion-box {
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
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print:hover {
            background: #2a5298;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 60, 114, 0.3);
        }
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
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-cerrar:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
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
                <div class="titulo-reporte">Ficha Tecnica de Solicitud</div>
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
    @php
        $codigoSol = 'SOL-' . str_pad($record->id, 6, '0', STR_PAD_LEFT);
    @endphp
    <table class="pdf-meta">
        <tr>
            <td><strong>Documento:</strong> FICHA-SOL-{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}</td>
            <td class="td-right"><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Generado por:</strong> {{ auth()->user()->nombre_completo ?? auth()->user()->usuario ?? 'Sistema' }}</td>
            <td class="td-right"><strong>Hash:</strong> {{ strtoupper(substr(sha1($record->id . '|' . $codigoSol . '|' . now()->timestamp), 0, 10)) }}</td>
        </tr>
    </table>

    {{-- ============ HERO ============ --}}
    @php
        $estado = strtolower($record->estado_solicitud ?? 'pendiente');
        $prioridad = strtolower($record->prioridad ?? 'normal');

        $colorEstado = match($estado) {
            'pendiente' => '#f59e0b',
            'aprobada'  => '#10b981',
            'rechazada' => '#ef4444',
            'cancelada' => '#64748b',
            'entregada' => '#3b82f6',
            default     => '#1e3c72',
        };

        $colorPrioridad = match($prioridad) {
            'urgente' => '#ef4444',
            'alta'    => '#f97316',
            'normal'  => '#10b981',
            'baja'    => '#64748b',
            default   => '#64748b',
        };

        $nombreEntidad = 'No especificada';
        if ($record->tipo_solicitante === 'interno' && $record->departamento) {
            $nombreEntidad = $record->departamento->nombre;
        } elseif ($record->tipo_solicitante === 'externo' && $record->institucion) {
            $nombreEntidad = $record->institucion->nombre;
        }

        $tipoSolicitante = $record->tipo_solicitante === 'interno' ? 'Interno' : 'Externo';
    @endphp

    <div class="hero-card">
        <table class="hero-table">
            <tr>
                <td>
                    <div class="hero-codigo">{{ $codigoSol }}</div>
                    <div class="hero-desc">
                        {{ $tipoSolicitante }} &nbsp;·&nbsp; {{ $nombreEntidad }}
                    </div>
                </td>
                <td class="hero-badges">
                    <span class="badge-estado" style="background: {{ $colorPrioridad }};">
                        {{ strtoupper($prioridad) }}
                    </span>
                    <span class="badge-estado" style="background: {{ $colorEstado }};">
                        {{ strtoupper($estado) }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ============ DATOS DEL SOLICITANTE ============ --}}
    <div class="pdf-section-title">Datos del Solicitante</div>

    @php
        $usuario = $record->usuario;
        $trabajador = $usuario?->trabajador;
        $nombreUsuario = $trabajador
            ? trim(($trabajador->nombre ?? '') . ' ' . ($trabajador->apellido ?? ''))
            : ($usuario?->usuario ?? 'No especificado');
        $cedula = $trabajador?->cedula ?? 'No registrada';
        $email = $trabajador?->email ?? 'No registrado';
        $cargo = $trabajador?->cargo ?? null;
    @endphp

    <table class="data-table">
        <tr>
            <td class="label">Solicitante</td>
            <td class="value" colspan="3">
                {{ $nombreUsuario }}
                @if($cargo) <span style="color:#64748b;">· {{ $cargo }}</span> @endif
            </td>
        </tr>
        <tr>
            <td class="label">Cedula</td>
            <td class="value {{ $cedula === 'No registrada' ? 'muted' : '' }}">{{ $cedula }}</td>
            <td class="label">Email</td>
            <td class="value {{ $email === 'No registrado' ? 'muted' : '' }}">{{ $email }}</td>
        </tr>
        <tr>
            <td class="label">Tipo Solicitante</td>
            <td class="value">{{ $tipoSolicitante }}</td>
            <td class="label">{{ $record->tipo_solicitante === 'interno' ? 'Departamento' : 'Institucion' }}</td>
            <td class="value">{{ $nombreEntidad }}</td>
        </tr>
    </table>

    {{-- ============ RESPONSABLE ============ --}}
    <div class="pdf-section-title">Persona Responsable</div>

    @php
        $resp = $record->responsable;
        $ubicacionGeografica = 'No especificada';
        $partes = [];
        if ($record->parroquia) $partes[] = $record->parroquia->nombre;
        if ($record->municipio) $partes[] = $record->municipio->nombre;
        if ($record->estado)    $partes[] = $record->estado->nombre;
        if (count($partes)) $ubicacionGeografica = implode(', ', $partes);
    @endphp

    <table class="data-table">
        <tr>
            <td class="label">Nombre</td>
            <td class="value {{ $resp ? '' : 'muted' }}">
                {{ $resp?->nombre ?? 'Sin responsable asignado' }}
            </td>
            <td class="label">Cargo</td>
            <td class="value {{ ($resp && $resp->cargo) ? '' : 'muted' }}">
                {{ $resp?->cargo ?: 'No especificado' }}
            </td>
        </tr>
        @if($resp)
        <tr>
            <td class="label">Documento</td>
            <td class="value {{ $resp->documento ? '' : 'muted' }}">
                {{ $resp->documento ?: 'No registrado' }}
            </td>
            <td class="label">Contacto</td>
            <td class="value {{ ($resp->telefono || $resp->email) ? '' : 'muted' }}">
                @if($resp->telefono) 📞 {{ $resp->telefono }} @endif
                @if($resp->telefono && $resp->email) &nbsp;|&nbsp; @endif
                @if($resp->email) ✉ {{ $resp->email }} @endif
                @if(!$resp->telefono && !$resp->email) Sin datos de contacto @endif
            </td>
        </tr>
        @endif
    </table>

    {{-- ============ FECHAS CLAVE ============ --}}
    <div class="pdf-section-title">Fechas Clave</div>

    <table class="data-table">
        <tr>
            <td class="label">Fecha Solicitud</td>
            <td class="value">
                {{ $record->fecha_solicitud ? \Carbon\Carbon::parse($record->fecha_solicitud)->format('d/m/Y') : 'No registrada' }}
            </td>
            <td class="label">Fecha Requerida</td>
            <td class="value {{ $record->fecha_requerida ? '' : 'muted' }}">
                {{ $record->fecha_requerida ? \Carbon\Carbon::parse($record->fecha_requerida)->format('d/m/Y') : 'No definida' }}
            </td>
        </tr>
        <tr>
            <td class="label">Fecha Fin Estimada</td>
            <td class="value {{ $record->fecha_fin_estimada ? '' : 'muted' }}">
                {{ $record->fecha_fin_estimada ? \Carbon\Carbon::parse($record->fecha_fin_estimada)->format('d/m/Y') : 'No definida' }}
            </td>
            <td class="label">Fecha Aprobacion</td>
            <td class="value {{ $record->fecha_aprobacion ? '' : 'muted' }}">
                {{ $record->fecha_aprobacion ? \Carbon\Carbon::parse($record->fecha_aprobacion)->format('d/m/Y H:i') : 'No aprobada aun' }}
            </td>
        </tr>
    </table>

    {{-- ============ UBICACION DEL EVENTO (solo si hay datos) ============ --}}
    @if($record->lugar_evento || $record->estado_id || $record->municipio_id || $record->parroquia_id)
        <div class="pdf-section-title">Ubicacion del Evento</div>

        <table class="data-table">
            @if($record->lugar_evento)
            <tr>
                <td class="label">Lugar</td>
                <td class="value" colspan="3">{{ $record->lugar_evento }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Ubicacion Geografica</td>
                <td class="value {{ $ubicacionGeografica === 'No especificada' ? 'muted' : '' }}" colspan="3">
                    {{ $ubicacionGeografica }}
                </td>
            </tr>
        </table>
    @endif

    {{-- ============ JUSTIFICACION ============ --}}
    <div class="pdf-section-title">Justificacion</div>
    <div class="justificacion-box">
        {{ $record->justificacion ?: 'Sin justificacion registrada.' }}
    </div>

    {{-- ============ OBSERVACIONES ============ --}}
    <div class="pdf-section-title">Observaciones</div>
    <div class="observaciones-box {{ empty($record->observaciones) ? 'vacio' : '' }}">
        {{ $record->observaciones ?: 'Sin observaciones registradas.' }}
    </div>

    {{-- ============ ITEMS SOLICITADOS ============ --}}
    <div class="pdf-section-title">
        Items Solicitados ({{ $record->detalles->count() }})
    </div>

    @if($record->detalles->count() > 0)
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Tipo</th>
                    <th style="width: 48%;">Descripcion</th>
                    <th style="width: 10%;" class="text-center">Cantidad</th>
                    <th style="width: 30%;">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($record->detalles as $item)
                    @php
                        $esActivo = $item->tipo_item === 'activo';
                        $descripcion = $item->descripcion_personalizada;
                        if (!$descripcion && $esActivo && $item->activo) {
                            $descripcion = 'Activo: ' . $item->activo->serial;
                        } elseif (!$descripcion && !$esActivo && $item->componente) {
                            $descripcion = 'Componente: ' . $item->componente->tipo;
                        }
                        $descripcion = $descripcion ?: 'Sin descripcion';
                    @endphp
                    <tr>
                        <td>
                            <span class="item-badge {{ $esActivo ? '' : 'componente' }}">
                                {{ $esActivo ? 'Activo' : 'Componente' }}
                            </span>
                        </td>
                        <td>{{ $descripcion }}</td>
                        <td class="text-center">
                            <span class="cantidad-badge">{{ $item->cantidad_solicitada }}</span>
                        </td>
                        <td>
                            {{ $item->observaciones ?: '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="observaciones-box vacio">
            Esta solicitud no tiene items registrados.
        </div>
    @endif

    {{-- ============ FIRMAS ============ --}}
    <table class="firmas-table">
        <tr>
            <td>
                <div class="linea"></div>
                <div class="nombre">{{ $nombreUsuario }}</div>
                <div class="cargo">{{ $cargo ?: 'Solicitante' }}</div>
                <div class="rol">Solicita</div>
            </td>
            <td>
                <div class="linea"></div>
                <div class="nombre">Departamento de Informatica</div>
                <div class="cargo">Gobernacion del Estado Yaracuy</div>
                <div class="rol">Aprueba</div>
            </td>
        </tr>
    </table>

    {{-- ============ FOOTER ============ --}}
    <div class="pdf-footer">
        Sistema de Gestion de Inventario Tecnologico - Gobernacion del Estado Yaracuy
        <br>
        FICHA-SOL-{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }} &middot; Generado el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>