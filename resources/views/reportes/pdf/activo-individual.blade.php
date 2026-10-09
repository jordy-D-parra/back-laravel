<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} - {{ $record->serial }}</title>
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
        .pdf-header .td-logo {
            width: 90px;
            text-align: center;
        }
        .pdf-header .td-logo img {
            height: 48px;
            width: auto;
        }
        .pdf-header .td-logo .logo-label {
            display: block;
            font-size: 5.5pt;
            font-weight: bold;
            color: #1e3c72;
            text-transform: uppercase;
            margin-top: 1px;
            line-height: 1.05;
        }
        .pdf-header .td-center {
            text-align: center;
            padding: 0 8px;
        }
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

        /* ============ HERO (Serial + Modelo + Estatus) ============ */
        .hero-card {
            background: #f8fafc;
            border-left: 4px solid #1e3c72;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 12px;
        }
        .hero-table { width: 100%; border-collapse: collapse; }
        .hero-table td { vertical-align: middle; }
        .hero-serial {
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
        .hero-estado {
            text-align: right;
        }
        .badge-estado {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #ffffff;
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

        /* ============ TABLA COMPONENTES ============ */
        .comp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 4px;
        }
        .comp-table thead th {
            background: #1e3c72;
            color: #ffffff;
            padding: 5px 6px;
            text-align: left;
            font-size: 6.8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #1e3c72;
        }
        .comp-table tbody td {
            padding: 5px 6px;
            border: 1px solid #e9ecef;
            vertical-align: top;
        }
        .comp-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .comp-badge {
            display: inline-block;
            padding: 1.5px 7px;
            border-radius: 10px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            white-space: nowrap;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
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

        /* ============ FIRMAS (solo espacio, sin firmas digitales) ============ */
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

        /* ============ BOTON IMPRIMIR (solo en pantalla) ============ */
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
        .btn-print svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }
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
            body { padding: 0; }
        }
    </style>
</head>
<body>

    {{-- ============ BOTONES FLOTANTES (solo en pantalla) ============ --}}
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

    {{-- ============ HEADER INSTITUCIONAL ============ --}}
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
                <div class="titulo-reporte">Ficha Tecnica de Activo</div>
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
            <td><strong>Documento:</strong> FICHA-ACT-{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}</td>
            <td class="td-right"><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Generado por:</strong> {{ auth()->user()->nombre_completo ?? auth()->user()->usuario ?? 'Sistema' }}</td>
            <td class="td-right"><strong>Hash:</strong> {{ strtoupper(substr(sha1($record->id . '|' . $record->serial . '|' . now()->timestamp), 0, 10)) }}</td>
        </tr>
    </table>

    {{-- ============ HERO: SERIAL + MODELO + ESTATUS ============ --}}
    @php
        $estatusDesc = $record->estatus?->descripcion ?? 'Sin estatus';
        $colorEstado = match($estatusDesc) {
            'Disponible'    => '#10b981',
            'Prestado'      => '#f59e0b',
            'En reparación' => '#ef4444',
            'En bodega'     => '#64748b',
            'Desechado'     => '#991b1b',
            default         => '#1e3c72',
        };
        $marcaNombre = $record->modelo?->marca?->nombre ?? '—';
        $modeloNombre = $record->modelo?->nombre ?? '—';
        $categoriaNombre = $record->modelo?->categoria?->nombre ?? '—';
    @endphp

    <div class="hero-card">
        <table class="hero-table">
            <tr>
                <td>
                    <div class="hero-serial">{{ $record->serial }}</div>
                    <div class="hero-desc">
                        {{ $marcaNombre }} {{ $modeloNombre }} &nbsp;·&nbsp; {{ $categoriaNombre }}
                    </div>
                </td>
                <td class="hero-estado">
                    <span class="badge-estado" style="background: {{ $colorEstado }};">
                        {{ $estatusDesc }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ============ SECCION: DATOS GENERALES ============ --}}
    <div class="pdf-section-title">Datos Generales del Activo</div>

    <table class="data-table">
        <tr>
            <td class="label">Serial</td>
            <td class="value">{{ $record->serial }}</td>
            <td class="label">Categoria</td>
            <td class="value">{{ $categoriaNombre }}</td>
        </tr>
        <tr>
            <td class="label">Marca</td>
            <td class="value">{{ $marcaNombre }}</td>
            <td class="label">Modelo</td>
            <td class="value">{{ $modeloNombre }}</td>
        </tr>
        <tr>
            <td class="label">Estatus</td>
            <td class="value">{{ $estatusDesc }}</td>
            <td class="label">Agrupacion</td>
            <td class="value {{ empty($record->agrupacion) ? 'muted' : '' }}">
                {{ $record->agrupacion ?: 'Sin agrupacion' }}
            </td>
        </tr>
        <tr>
            <td class="label">Ubicacion</td>
            <td class="value {{ empty($record->ubicacion) ? 'muted' : '' }}">
                {{ $record->ubicacion ?: 'No especificada' }}
            </td>
            <td class="label">Vida Util</td>
            <td class="value {{ empty($record->vida_util_anos) ? 'muted' : '' }}">
                {{ $record->vida_util_anos ? $record->vida_util_anos . ' años' : 'No especificada' }}
            </td>
        </tr>
        <tr>
            <td class="label">Fecha Adquisicion</td>
            <td class="value {{ empty($record->fecha_adquisicion) ? 'muted' : '' }}">
                {{ $record->fecha_adquisicion ? $record->fecha_adquisicion->format('d/m/Y') : 'No registrada' }}
            </td>
            <td class="label">Fin de Garantia</td>
            <td class="value {{ empty($record->fecha_fin_garantia) ? 'muted' : '' }}">
                {{ $record->fecha_fin_garantia ? $record->fecha_fin_garantia->format('d/m/Y') : 'No registrada' }}
                @if($record->fecha_fin_garantia)
                    @if($record->fecha_fin_garantia->isPast())
                        <span class="comp-badge" style="background:#fee2e2; color:#991b1b; border-color:#ef4444;">Vencida</span>
                    @else
                        <span class="comp-badge" style="background:#ecfdf5; color:#065f46; border-color:#10b981;">Vigente</span>
                    @endif
                @endif
            </td>
        </tr>
    </table>

    {{-- ============ SECCION: UBICACION Y RESPONSABILIDAD ============ --}}
    <div class="pdf-section-title">Ubicacion y Responsabilidad</div>

    @php
        $institucion = $record->institucion;
        $depto = $record->departamento;
        $resp = $record->responsable;

        $ubicacionGeografica = 'No especificada';
        if ($institucion) {
            $partes = [];
            if ($institucion->parroquia) $partes[] = $institucion->parroquia->nombre;
            if ($institucion->municipio) $partes[] = $institucion->municipio->nombre;
            if ($institucion->estado)    $partes[] = $institucion->estado->nombre;
            if (count($partes)) $ubicacionGeografica = implode(', ', $partes);
        }
    @endphp

    <table class="data-table">
        <tr>
            <td class="label">Institucion</td>
            <td class="value" colspan="3">
                {{ $institucion?->nombre ?? 'No especificada' }}
            </td>
        </tr>
        <tr>
            <td class="label">Departamento</td>
            <td class="value {{ $depto ? '' : 'muted' }}">
                {{ $depto?->nombre ?? 'Sin departamento asignado' }}
            </td>
            <td class="label">Ubicacion Geografica</td>
            <td class="value {{ $ubicacionGeografica === 'No especificada' ? 'muted' : '' }}">
                {{ $ubicacionGeografica }}
            </td>
        </tr>
        <tr>
            <td class="label">Responsable</td>
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

    {{-- ============ SECCION: COMPONENTES INSTALADOS ============ --}}
    <div class="pdf-section-title">
        Componentes Instalados ({{ $record->componentes->count() }})
    </div>

    @if($record->componentes->count() > 0)
        <table class="comp-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Tipo</th>
                    <th style="width: 14%;">Marca</th>
                    <th style="width: 16%;">Modelo</th>
                    <th style="width: 16%;">Serial</th>
                    <th style="width: 12%;">Capacidad</th>
                    <th style="width: 12%;">Estado</th>
                    <th style="width: 12%;">Ubicacion</th>
                </tr>
            </thead>
            <tbody>
                @foreach($record->componentes as $comp)
                    @php
                        $estadoComp = match($comp->estado) {
                            'instalado'     => 'Instalado',
                            'en_bodega'     => 'En Bodega',
                            'prestado'      => 'Prestado',
                            'en_reparacion' => 'En Reparacion',
                            'desechado'     => 'Desechado',
                            default         => ucfirst($comp->estado),
                        };
                    @endphp
                    <tr>
                        <td><strong>{{ $comp->tipo }}</strong></td>
                        <td>{{ $comp->marca ?? '—' }}</td>
                        <td>{{ $comp->modelo ?? '—' }}</td>
                        <td>{{ $comp->serial ?? '—' }}</td>
                        <td>{{ $comp->capacidad ?? '—' }}</td>
                        <td><span class="comp-badge">{{ $estadoComp }}</span></td>
                        <td>{{ $comp->ubicacion ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="observaciones-box vacio">
            Este activo no tiene componentes instalados registrados.
        </div>
    @endif

    {{-- ============ SECCION: OBSERVACIONES ============ --}}
    <div class="pdf-section-title">Observaciones</div>

    <div class="observaciones-box {{ empty($record->observaciones) ? 'vacio' : '' }}">
        {{ $record->observaciones ?: 'Sin observaciones registradas.' }}
    </div>

    {{-- ============ FIRMAS (solo espacio para firmar) ============ --}}
    <table class="firmas-table">
        <tr>
            <td>
                <div class="linea"></div>
                <div class="nombre">Departamento de Informatica</div>
                <div class="cargo">Elaborado por</div>
                <div class="rol">Elabora</div>
            </td>
            <td>
                <div class="linea"></div>
                <div class="nombre">Supervisor</div>
                <div class="cargo">Gobernacion del Estado Yaracuy</div>
                <div class="rol">Revisa</div>
            </td>
        </tr>
    </table>

    {{-- ============ FOOTER ============ --}}
    <div class="pdf-footer">
        Sistema de Gestion de Inventario Tecnologico - Gobernacion del Estado Yaracuy
        <br>
        FICHA-ACT-{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }} &middot; Generado el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>