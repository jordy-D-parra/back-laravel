<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Básico</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
    <div class="payaso2">
        <div class="payaso2-img">
            <img src="./vista/imagen/logo%20de%20lA%20upel.png" width="210px" height="42px" alt="Logo UPEL" onerror="this.style.display='none'">
        </div>
        <div class="payaso2-h1">
            <h1>TITULO</h1>
        </div>
        <div class="payaso2-img2">
            <img src="./vista/imagen/cintillo.png" width="240px" alt="Cintillo UPEL" onerror="this.style.display='none'">
        </div>
    </div>
    <div class="flex-container">
        <form id="form" class="form" method="post" action="./controlador/login.php">
            <div class="form-content">
                <div>
                    <p class="form-h2">INICIAR SESIÓN</p>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="server-error">
                            <?php
                            $err = $_GET['error'];
                            if ($err == '1') echo 'Usuario y contraseña son obligatorios.';
                            elseif ($err == '2') echo 'El usuario debe ser numérico (cédula).';
                            elseif ($err == '3') echo 'Usuario o contraseña incorrectos.';
                            else echo 'Error en el inicio de sesión.';
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-section">
                    <input type="text" class="form-input" id="Usuario" name="usuario" placeholder="Cédula" pattern="[0-9]+" title="Solo se permiten números" required autocomplete="username">
                </div>
                <div class="form-section">
                    <div class="password-container">
                        <input type="password" class="form-input" id="pasword" name="password" placeholder="Contraseña" required autocomplete="current-password">
                        <button type="button" class="password-toggle btn btn-outline-secondary" onclick="togglePassword('pasword', this)">
                            Ver
                        </button>
                    </div>
                </div>
                <div class="form-section">
                    <button type="submit" id="submit" class="form-entrar">Entrar</button>
                </div>
                <div class="form-links">
                    <a href="./vista/registar-usuario.php">Registro</a>
                    <a href="./vista/recovery-password.html">¿Olvidó su Contraseña?</a>
                </div>

                <div class="form-img">
                    <img src="./vista/imagen/LOGO.jpg" alt="Logo UPEL" width="120px" onerror="this.style.display='none'">
                </div>
            </div>
        </form>
    </div>
    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
