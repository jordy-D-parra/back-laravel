<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Rechazada</title>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            margin: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .email-header {
            background: linear-gradient(135deg, #c5221f 0%, #a01d1a 100%);
            padding: 30px 40px;
            text-align: center;
        }
        .email-header h1 {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }
        .email-header p {
            color: rgba(255,255,255,0.8);
            margin: 8px 0 0;
            font-size: 14px;
        }
        .email-body {
            padding: 40px;
        }
        .greeting {
            font-size: 16px;
            color: #1a1a1a;
            margin-bottom: 20px;
        }
        .notification-card {
            background: #f8f9fc;
            border-radius: 12px;
            padding: 20px 24px;
            border-left: 4px solid #c5221f;
            margin-bottom: 24px;
        }
        .notification-card .titulo {
            font-size: 18px;
            font-weight: 600;
            color: #c5221f;
            margin-bottom: 8px;
        }
        .notification-card .mensaje {
            color: #495057;
            line-height: 1.6;
        }
        .info-section {
            background: #f8f9fc;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 13px;
        }
        .info-value {
            color: #1a1a1a;
            font-size: 13px;
            font-weight: 500;
            text-align: right;
        }
        .motivo-box {
            background: #fff3cd;
            border-radius: 12px;
            padding: 16px 20px;
            border-left: 4px solid #ffc107;
            margin-bottom: 24px;
        }
        .motivo-box p {
            margin: 0;
            font-size: 14px;
            color: #856404;
            line-height: 1.6;
        }
        .email-footer {
            padding: 20px 40px;
            background: #f8f9fc;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }
        .email-footer p {
            color: #6c757d;
            font-size: 13px;
            margin: 0;
        }
        .footer-logo {
            font-weight: 600;
            color: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>❌ Solicitud Rechazada</h1>
            <p>Gobernación del Estado Yaracuy - Dirección de Informática</p>
        </div>
        <div class="email-body">
            <p class="greeting">Hola, <strong>{{ $solicitud->usuario->trabajador->nombre ?? 'Usuario' }}</strong></p>
            
            <div class="notification-card">
                <div class="titulo">Solicitud No Aprobada</div>
                <div class="mensaje">
                    Lamentamos informarte que tu solicitud de préstamo ha sido <strong>RECHAZADA</strong>.
                </div>
            </div>

            <div class="info-section">
                <div class="info-item">
                    <span class="info-label">Número de Solicitud:</span>
                    <span class="info-value">SOL-{{ str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha de Solicitud:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Prioridad:</span>
                    <span class="info-value">{{ ucfirst($solicitud->prioridad) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Entidad:</span>
                    <span class="info-value">
                        {{ $solicitud->tipo_solicitante === 'interno' 
                            ? ($solicitud->departamento->nombre ?? 'Departamento') 
                            : ($solicitud->institucion->nombre ?? 'Institución') }}
                    </span>
                </div>
            </div>

            <div class="motivo-box">
                <p><strong>Motivo del rechazo:</strong> {{ $motivo }}</p>
            </div>

            <p style="margin-top: 20px; color: #6c757d; font-size: 14px;">
                Si tienes dudas o crees que esto es un error, por favor contacta al Departamento de Informática.
            </p>
        </div>
        <div class="email-footer">
            <p>
                <span class="footer-logo">Sistema de Gestión de Inventario Tecnológico</span><br>
                Gobernación del Estado Yaracuy - San Felipe, Edo. Yaracuy
            </p>
        </div>
    </div>
</body>
</html>