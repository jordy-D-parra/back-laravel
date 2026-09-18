<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Retiro</title>
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
        .email-body .greeting {
            font-size: 16px;
            color: #1a1a1a;
            margin-bottom: 20px;
        }
        .notification-card {
            background: #f8f9fc;
            border-radius: 12px;
            padding: 20px 24px;
            border-left: 4px solid #1e3c72;
            margin-bottom: 24px;
        }
        .notification-card .titulo {
            font-size: 18px;
            font-weight: 600;
            color: #1e3c72;
            margin-bottom: 8px;
        }
        .notification-card .mensaje {
            color: #495057;
            line-height: 1.6;
            white-space: pre-line;
        }
        .instrucciones {
            background: #fff3cd;
            border-radius: 12px;
            padding: 16px 20px;
            border-left: 4px solid #ffc107;
            margin-bottom: 24px;
        }
        .instrucciones p {
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
        .email-footer .footer-logo {
            font-weight: 600;
            color: #1e3c72;
        }
        @media (max-width: 600px) {
            .email-body { padding: 24px; }
            .email-header { padding: 20px; }
            .email-footer { padding: 16px 20px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>📄 Acta de Retiro de Equipo</h1>
            <p>Gobernación del Estado Yaracuy - Dirección de Informática</p>
        </div>
        <div class="email-body">
            <p class="greeting">Hola, <strong>{{ $solicitud->responsable->nombre ?? 'Responsable' }}</strong></p>

            <div class="notification-card">
                <div class="titulo">✅ Solicitud Aprobada</div>
                <div class="mensaje">
                    {{ $mensaje }}
                </div>
            </div>

            <div class="instrucciones">
                <p>
                    <strong>📌 Instrucciones:</strong><br>
                    1. Descargue el Acta de Retiro adjunta a este correo.<br>
                    2. Imprímala y fírmela.<br>
                    3. Preséntese en el Departamento de Informática con el acta firmada y su cédula de identidad.<br>
                    4. Recibirá los equipos detallados en el acta.
                </p>
            </div>

            <p style="margin-top: 20px; color: #6c757d; font-size: 14px;">
                Este es un mensaje automático del Sistema de Gestión de Inventario.
                Por favor, no responda a este correo.
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