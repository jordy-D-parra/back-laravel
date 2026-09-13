<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'Reporte de Inventario' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            padding: 20px;
            color: #1a1a1a;
            background: #fff;
        }

        /* ========== BOTONES ========== */
        .no-print {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .no-print .btn-print {
            background: #1e3c72;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .no-print .btn-print:hover {
            background: #2a5298;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
        }
        .no-print .btn-print svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: white;
            stroke-width: 2;
        }
        .no-print .btn-cerrar {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 10px;
        }
        .no-print .btn-cerrar:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .no-print .btn-cerrar svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: white;
            stroke-width: 2;
        }

        /* ========== HEADER ========== */
        .header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #1e3c72;
            margin-bottom: 20px;
        }
        .header .logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-bottom: 10px;
        }
        .header .logos .logo-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .header .logos .logo-item img {
            height: 50px;
            width: auto;
            object-fit: contain;
        }
        .header .logos .logo-item .logo-label {
            font-size: 7pt;
            font-weight: 600;
            color: #1e3c72;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header .logos .separator {
            width: 2px;
            height: 50px;
            background: #1e3c72;
            opacity: 0.3;
        }
        .header h1 {
            color: #1e3c72;
            font-size: 16pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 5px;
        }
        .header .fecha {
            color: #6c757d;
            font-size: 9pt;
            margin-top: 4px;
        }

        /* ========== ESTADÍSTICAS ========== */
        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px 15px;
            background: #f8f9fc;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }
        .stats .stat-item {
            text-align: center;
        }
        .stats .stat-number {
            font-weight: 700;
            font-size: 14pt;
            color: #1e3c72;
        }
        .stats .stat-number.disponible { color: #28a745; }
        .stats .stat-number.prestado { color: #f59e0b; }
        .stats .stat-number.reparacion { color: #dc3545; }
        .stats .stat-label {
            font-size: 7pt;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ========== FILTROS ========== */
        .filtros-info {
            font-size: 7pt;
            color: #6c757d;
            margin-bottom: 10px;
            padding: 5px 10px;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }

        /* ========== TABLA ========== */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 10px;
        }
        table thead th {
            background: #1e3c72;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.5px;
            border: 1px solid #1e3c72;
        }
        table tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e9ecef;
            border-left: 1px solid #e9ecef;
            border-right: 1px solid #e9ecef;
        }
        table tbody tr:nth-child(even) {
            background: #f8f9fc;
        }
        table tbody tr:hover {
            background: #eef3fc;
        }

        .badge-estado {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 6pt;
            font-weight: 600;
            display: inline-block;
            text-transform: uppercase;
        }
        .badge-estado.disponible { background: #d4edda; color: #155724; }
        .badge-estado.prestado { background: #fff3cd; color: #856404; }
        .badge-estado.reparacion { background: #f8d7da; color: #721c24; }
        .badge-estado.bodega { background: #e2e3e5; color: #383d41; }
        .badge-estado.desechado { background: #f8d7da; color: #721c24; }
        .badge-estado.instalado { background: #cce5ff; color: #004085; }

        /* ========== FOOTER ========== */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 7pt;
            color: #6c757d;
            line-height: 1.8;
        }
        .footer .footer-marca {
            font-size: 6pt;
            color: #adb5bd;
            letter-spacing: 0.5px;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .stats {
                flex-wrap: wrap;
                gap: 10px;
            }
            .stats .stat-item {
                flex: 1 1 45%;
            }
            table {
                font-size: 7pt;
            }
            table thead th,
            table tbody td {
                padding: 4px 6px;
            }
        }

        @media (max-width: 576px) {
            .stats .stat-item {
                flex: 1 1 100%;
            }
            .header .logos {
                flex-wrap: wrap;
                gap: 15px;
            }
            .header .logos .logo-item img {
                height: 35px;
            }
            .header .logos .separator {
                display: none;
            }
        }

        /* ========== IMPRESIÓN ========== */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 10px;
                background: #fff;
            }
            .header .logos .logo-item img {
                height: 40px;
            }
            table thead th {
                background: #1e3c72 !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .badge-estado {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .stats {
                background: #f8f9fc !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .filtros-info {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- ============================================ -->
    <!-- BOTONES DE ACCIÓN (solo en pantalla)          -->
    <!-- ============================================ -->
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            <svg viewBox="0 0 24 24">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M18 9H6"/>
                <rect x="4" y="12" width="16" height="10" rx="1"/>
                <line x1="8" y1="17" x2="16" y2="17"/>
                <line x1="8" y1="21" x2="12" y2="21"/>
                <line x1="16" y1="21" x2="16" y2="21"/>
            </svg>
            Imprimir / Guardar PDF
        </button>
        <button class="btn-cerrar" onclick="window.close()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            Cerrar
        </button>
    </div>

    <!-- ============================================ -->
    <!-- REPORTE                                      -->
    <!-- ============================================ -->
    <div class="header">
        <div class="logos">
            <div class="logo-item">
                <img src="{{ asset('images/gobierno.jpeg') }}" alt="Gobierno Nacional" onerror="this.style.display='none'">
            </div>
            <div class="separator"></div>
            <div class="logo-item">
                <img src="{{ asset('images/escudo-yaracuy.jpeg') }}" alt="Gobernación de Yaracuy">
                <span class="logo-label">Gobernación del Estado Yaracuy</span>
            </div>
        </div>
        <h1>{{ $titulo ?? 'Reporte de Inventario' }}</h1>
        <div class="fecha">Generado: {{ $fecha_generacion ?? now()->format('d/m/Y H:i') }}</div>
    </div>

    <!-- ========== ESTADÍSTICAS ========== -->
    <div class="stats">
        <div class="stat-item">
            <div class="stat-number">{{ $total ?? 0 }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-item">
            <div class="stat-number disponible">{{ $disponibles ?? 0 }}</div>
            <div class="stat-label">Disponibles</div>
        </div>
        <div class="stat-item">
            <div class="stat-number prestado">{{ $prestados ?? 0 }}</div>
            <div class="stat-label">Prestados</div>
        </div>
        <div class="stat-item">
            <div class="stat-number reparacion">{{ $reparacion ?? 0 }}</div>
            <div class="stat-label">En Reparación / Bodega</div>
        </div>
    </div>

    <!-- ========== FILTROS APLICADOS ========== -->
    @if(!empty($filtros))
    <div class="filtros-info">
        <strong>Filtros aplicados:</strong>
        @if(!empty($filtros['buscar'])) Buscar: "{{ $filtros['buscar'] }}" | @endif
        @if(!empty($filtros['estado'])) Estado: {{ $filtros['estado'] }} | @endif
        @if(!empty($filtros['categoria'])) Categoría: {{ $filtros['categoria'] }} @endif
    </div>
    @endif

    <!-- ========== TABLA ========== -->
    @php
        $esComponente = ($tipo ?? 'activos') === 'componentes';
    @endphp

    <table>
        <thead>
            <tr>
                @if($esComponente)
                    <th>Tipo</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Serial</th>
                    <th>Capacidad</th>
                    <th>Estado</th>
                    <th>Ubicación</th>
                    <th>Activo</th>
                    <th>Responsable</th>
                @else
                    <th>Serial</th>
                    <th>Modelo</th>
                    <th>Marca</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Ubicación</th>
                    <th>Responsable</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($componentes ?? $activos ?? [] as $item)
            @php
                if ($esComponente) {
                    $estado = $item->estado ?? 'N/A';
                    $estadoLabel = match($estado) {
                        'en_bodega' => 'En Bodega',
                        'instalado' => 'Instalado',
                        'prestado' => 'Prestado',
                        'en_reparacion' => 'En Reparación',
                        'desechado' => 'Desechado',
                        default => $estado
                    };
                    $estadoClass = match($estado) {
                        'en_bodega' => 'bodega',
                        'instalado' => 'instalado',
                        'prestado' => 'prestado',
                        'en_reparacion' => 'reparacion',
                        'desechado' => 'desechado',
                        default => ''
                    };
                } else {
                    $estado = $item->estatus?->descripcion ?? 'N/A';
                    $estadoClass = match($estado) {
                        'Disponible' => 'disponible',
                        'Prestado' => 'prestado',
                        'En reparación' => 'reparacion',
                        'En bodega' => 'bodega',
                        'Desechado' => 'desechado',
                        default => ''
                    };
                }
            @endphp
            <tr>
                @if($esComponente)
                    <td><strong>{{ $item->tipo }}</strong></td>
                    <td>{{ $item->marca ?? 'N/A' }}</td>
                    <td>{{ $item->modelo ?? 'N/A' }}</td>
                    <td>{{ $item->serial ?? 'N/A' }}</td>
                    <td>{{ $item->capacidad ?? 'N/A' }}</td>
                    <td><span class="badge-estado {{ $estadoClass }}">{{ $estadoLabel }}</span></td>
                    <td>{{ $item->ubicacion ?? ($item->activo?->ubicacion ?? 'No especificada') }}</td>
                    <td>{{ $item->activo?->serial ?? 'No instalado' }}</td>
                    <td>{{ $item->responsable?->nombre ?? 'No asignado' }}</td>
                @else
                    <td><strong>{{ $item->serial }}</strong></td>
                    <td>{{ $item->modelo?->nombre ?? 'N/A' }}</td>
                    <td>{{ $item->modelo?->marca?->nombre ?? 'N/A' }}</td>
                    <td>{{ $item->modelo?->categoria?->nombre ?? 'N/A' }}</td>
                    <td><span class="badge-estado {{ $estadoClass }}">{{ $estado }}</span></td>
                    <td>{{ $item->ubicacion ?? 'No especificada' }}</td>
                    <td>{{ $item->responsable?->nombre ?? 'No asignado' }}</td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ $esComponente ? 9 : 7 }}" style="text-align: center; padding: 20px; color: #6c757d;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5" style="display: block; margin: 0 auto 10px;">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                    No hay registros que coincidan con los filtros seleccionados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ========== FOOTER ========== -->
    <div class="footer">
        <div class="footer-marca">
            Sistema de Gestión de Inventario Tecnológico - Gobernación del Estado Yaracuy
        </div>
        <div>
            Documento generado el {{ $fecha_generacion ?? now()->format('d/m/Y H:i') }}
        </div>
        <div style="font-size: 6pt; color: #adb5bd; margin-top: 4px;">
            Este documento es una representación fiel de los datos registrados en el sistema al momento de su generación.
        </div>
    </div>

</body>
</html>