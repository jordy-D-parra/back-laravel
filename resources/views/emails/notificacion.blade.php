<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notificacion->titulo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f5f7fa;
            padding: 20px;
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
        .email-body .greeting strong {
            color: #1e3c72;
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
        .notification-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #6c757d;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }
        .notification-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
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
        .btn-primary {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            margin-top: 12px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(30, 60, 114, 0.3);
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
            <h1>📬 Sistema de Inventario</h1>
            <p>Gobernación del Estado Yaracuy</p>
        </div>

        <div class="email-body">
            <p class="greeting">
                Hola, <strong>{{ $destinatario }}</strong>
            </p>

            <div class="notification-card">
                <div class="titulo">{{ $notificacion->titulo }}</div>
                <div class="mensaje">{{ $notificacion->mensaje }}</div>

                <div class="notification-meta">
                    <span>📅 {{ $notificacion->fecha_envio->format('d/m/Y H:i') }}</span>
                    <span>🏷️ {{ ucfirst($notificacion->tipo) }}</span>
                </div>
            </div>

            @if($notificacion->url)
                <a href="{{ url($notificacion->url) }}" class="btn-primary">
                    Ver en el Sistema
                </a>
            @endif

            <p style="margin-top: 20px; color: #6c757d; font-size: 14px;">
                Este es un mensaje automático del Sistema de Gestión de Inventario.
            </p>
        </div>

        <div class="email-footer">
            <p>
                <span class="footer-logo">Sistema de Gestión de Inventario Tecnológico</span>
                <br>
                Gobernación del Estado Yaracuy - San Felipe, Edo. Yaracuy
            </p>
        </div>
    </div>
</body>
</html>