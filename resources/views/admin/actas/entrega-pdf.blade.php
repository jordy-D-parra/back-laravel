<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Entrega - {{ $data['numero_acta'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 40px;
            background: #fff;
            font-size: 11pt;
            line-height: 1.5;
            color: #1a1a1a;
        }
        .acta-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 35px 40px;
            border: 2px solid #1e3c72;
            background: #fff;
            position: relative;
        }

        /* ========== MARCAS DE AGUA ========== */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            opacity: 0.04;
            font-size: 60pt;
            font-weight: 700;
            color: #1e3c72;
            letter-spacing: 10px;
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
            width: 100%;
            text-align: center;
        }

        /* ========== HEADER ========== */
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px double #1e3c72;
        }
        .header .logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-bottom: 12px;
        }
        .header .logos .logo-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .header .logos .logo-item img {
            height: 60px;
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
            height: 60px;
            background: #1e3c72;
            opacity: 0.3;
        }

        .header .titulo-pais {
            font-size: 11pt;
            font-weight: 700;
            color: #1e3c72;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .header .titulo-gobierno {
            font-size: 13pt;
            font-weight: 700;
            color: #1e3c72;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header .titulo-depto {
            font-size: 11pt;
            font-weight: 600;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .titulo-acta {
            font-size: 16pt;
            font-weight: 700;
            color: #1e3c72;
            margin-top: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
            padding: 4px 20px;
            border-top: 2px solid #1e3c72;
            border-bottom: 2px solid #1e3c72;
            display: inline-block;
        }

        /* ========== NÚMERO DE ACTA ========== */
        .numero-acta {
            text-align: center;
            font-size: 10pt;
            font-weight: 600;
            color: #1e3c72;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }
        .numero-acta span {
            background: #f0f4f8;
            padding: 2px 12px;
            border-radius: 4px;
            border: 1px solid #1e3c72;
        }

        /* ========== FECHA ========== */
        .fecha {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 20px;
            color: #333;
            font-weight: 500;
        }
        .fecha strong {
            color: #1e3c72;
        }

        /* ========== CUERPO ========== */
        .cuerpo {
            margin-bottom: 20px;
            text-align: justify;
            font-size: 11pt;
            line-height: 1.8;
            padding: 0 5px;
        }
        .cuerpo .destacado {
            font-weight: 600;
            color: #1e3c72;
        }

        /* ========== DATOS DEL EQUIPO ========== */
        .datos-equipo {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 6px;
            margin: 15px 0 18px 0;
            border-left: 4px solid #1e3c72;
            border-right: 1px solid #e9ecef;
            border-top: 1px solid #e9ecef;
            border-bottom: 1px solid #e9ecef;
        }
        .datos-equipo .item {
            display: flex;
            margin-bottom: 4px;
            padding: 3px 0;
            border-bottom: 1px dashed #e9ecef;
        }
        .datos-equipo .item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .datos-equipo .label {
            font-weight: 700;
            min-width: 120px;
            color: #1e3c72;
            text-transform: uppercase;
            font-size: 10pt;
        }
        .datos-equipo .valor {
            flex: 1;
            font-weight: 500;
        }
        .datos-equipo .valor .serial {
            font-weight: 700;
            color: #1e3c72;
            letter-spacing: 0.5px;
        }

        /* ========== OBSERVACIONES ========== */
        .observaciones {
            padding: 10px 16px;
            margin: 10px 0 20px 0;
            background: #fffbf0;
            border-radius: 4px;
            border-left: 4px solid #f6c23e;
            border-right: 1px solid #e9ecef;
            border-top: 1px solid #e9ecef;
            border-bottom: 1px solid #e9ecef;
            font-style: italic;
        }
        .observaciones strong {
            font-style: normal;
            color: #1e3c72;
        }

        /* ========== FIRMAS ========== */
        .firmas {
            display: flex;
            justify-content: space-between;
            margin-top: 35px;
            padding-top: 25px;
            border-top: 2px solid #1e3c72;
        }
        .firma-box {
            text-align: center;
            flex: 1;
            padding: 0 10px;
        }
        .firma-box .linea {
            border-top: 1.5px solid #000;
            width: 80%;
            margin: 35px auto 8px auto;
        }
        .firma-box .nombre {
            font-weight: 700;
            font-size: 11pt;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .firma-box .cargo {
            font-size: 9pt;
            color: #555;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .firma-box .rol {
            font-size: 9pt;
            font-weight: 700;
            color: #1e3c72;
            margin-top: 6px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* ========== FOOTER ========== */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 8pt;
            color: #6c757d;
            line-height: 1.8;
        }
        .footer .footer-marca {
            font-size: 7pt;
            color: #adb5bd;
            letter-spacing: 0.5px;
        }

        /* ========== BOTONES DE IMPRESIÓN ========== */
        .no-print {
            display: block !important;
        }
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
            .no-print {
                display: none !important;
            }
            body {
                padding: 15px;
                background: #fff;
            }
            .acta-container {
                border: none;
                padding: 10px 15px;
            }
            .firma-box .linea {
                margin-top: 25px;
            }
            .watermark {
                opacity: 0.03;
            }
            .btn-print, .btn-cerrar {
                display: none !important;
            }
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
                font-size: 10pt;
            }
            .acta-container {
                padding: 15px;
            }
            .header .logos {
                flex-wrap: wrap;
                gap: 15px;
            }
            .header .logos .logo-item img {
                height: 40px;
            }
            .header .logos .separator {
                display: none;
            }
            .firmas {
                flex-direction: column;
                gap: 20px;
            }
            .firma-box .linea {
                width: 60%;
            }
            .datos-equipo .item {
                flex-direction: column;
            }
            .datos-equipo .label {
                min-width: auto;
            }
            .btn-print, .btn-cerrar {
                padding: 8px 16px;
                font-size: 12px;
                bottom: 10px;
            }
            .btn-cerrar {
                left: 10px;
            }
            .btn-print {
                right: 10px;
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
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            Cerrar
        </button>
    </div>

    <!-- ============================================ -->
    <!-- ACTA DE ENTREGA                              -->
    <!-- ============================================ -->
    <div class="acta-container">

        <!-- Marca de agua -->
        <div class="watermark">ACTA DE ENTREGA</div>

        <!-- HEADER -->
        <div class="header">
            <div class="logos">
                <!-- Logo Gobierno Nacional -->
                <div class="logo-item">
                    <img style="width: 140px; height: auto;" src="{{ asset('images/gobierno.jpeg') }}" alt="Gobierno Nacional" onerror="this.style.display='none'">
                </div>

                <div class="separator"></div>

                <!-- Logo Gobernación Yaracuy -->
                <div class="logo-item">
                    <img src="{{ asset('images/escudo-yaracuy.jpeg') }}" alt="Gobernación de Yaracuy">
                    <span class="logo-label">Gobernación del Estado Yaracuy</span>
                </div>
            </div>

            <div class="titulo-pais">República Bolivariana de Venezuela</div>
            <div class="titulo-gobierno">Gobernación del Estado Yaracuy</div>
            <div class="titulo-depto">Dirección de Informática</div>

            <div class="titulo-acta">Acta de Entrega</div>
        </div>

        <!-- NÚMERO DE ACTA -->
        <div class="numero-acta">
            <span>Nº {{ $data['numero_acta'] }}</span>
        </div>

        <!-- FECHA -->
        <div class="fecha">
            <strong>San Felipe, {{ $data['fecha'] }}</strong>
        </div>

        <!-- CUERPO -->
        <div class="cuerpo">
            <p>
                Mediante el presente instrumento, se hace formal entrega a la entidad
                <span class="destacado">{{ $data['institucion'] }}</span>,
                de <strong>un (01) equipo</strong> perteneciente a la misma, cuyas características
                técnicas se detallan a continuación:
            </p>
        </div>

        <!-- DATOS DEL EQUIPO -->
        <div class="datos-equipo">
            <div class="item">
                <span class="label">MARCA:</span>
                <span class="valor">{{ $data['marca'] }}</span>
            </div>
            <div class="item">
                <span class="label">MODELO:</span>
                <span class="valor">{{ $data['modelo'] }}</span>
            </div>
            <div class="item">
                <span class="label">NÚMERO DE SERIE:</span>
                <span class="valor"><span class="serial">{{ $data['serial'] }}</span></span>
            </div>
            <div class="item">
                <span class="label">ACCESORIOS:</span>
                <span class="valor">{{ $data['accesorios'] }}</span>
            </div>
        </div>

        <!-- OBSERVACIONES -->
        @if(!empty($data['observaciones']))
        <div class="observaciones">
            <strong>Observaciones:</strong> {{ $data['observaciones'] }}
        </div>
        @endif

        <!-- FIRMAS -->
        <div class="firmas">
            <div class="firma-box">
                <div class="linea"></div>
                <div class="nombre">{{ strtoupper($data['responsable_entrega']) }}</div>
                <div class="cargo">{{ strtoupper($data['responsable_entrega_cargo']) }}</div>
                <div class="rol">ENTREGA</div>
            </div>
            <div class="firma-box">
                <div class="linea"></div>
                <div class="nombre">{{ strtoupper($data['responsable_recibe']) }}</div>
                <div class="cargo">{{ strtoupper($data['responsable_recibe_cargo']) }}</div>
                <div class="rol">RECIBE</div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <div class="footer-marca">
                Documento generado por el Sistema de Gestión de Inventario Tecnológico - Gobernación de Yaracuy
            </div>
        </div>

    </div>

</body>
</html>
