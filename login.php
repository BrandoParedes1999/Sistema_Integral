<?php

date_default_timezone_set('America/Mexico_City');
$bloqueos = [120, 300, 1800, 3600, 86400];  // 2min, 5min, 30min, 1h, 24h

$usuarioError = '';
$contraError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    require_once 'config/config.php';
    $database = new Database();
    $conn = $database->connect();

    // ✅ Sincronizar zona horaria de MySQL con PHP
    $conn->query("SET time_zone = '-06:00'");

    // Filtrar datos de entrada
    $usuario = htmlspecialchars($_POST['usuario'], ENT_QUOTES, 'UTF-8');
    $contra = htmlspecialchars($_POST['contra'], ENT_QUOTES, 'UTF-8');

    // Validar que no estén vacíos
    if (empty($usuario) || empty($contra)) {
        $usuarioError = "⚠️ Debe ingresar usuario y contraseña.";
    } else {
        // Obtener datos del usuario
        $stmt = $conn->prepare("SELECT usuario, contraseña, nombre_admi, apellidos_admi, rol, intentos_fallidos, ultimo_intento FROM administradores WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $queryusu = $stmt->get_result();

        if ($queryusu->num_rows === 1) {
            $resultado = $queryusu->fetch_assoc();

            $intentos = $resultado['intentos_fallidos'];
            $ultimoIntento = strtotime($resultado['ultimo_intento']);
            $tiempoActual = time();

            // Verificar bloqueo permanente
            if ($intentos >= (count($bloqueos) * 3)) {
                $usuarioError = "⚠️ Su cuenta ha sido bloqueada permanentemente. Contacte al administrador.";
            } 
            // Verificar si hay bloqueo temporal activo
            elseif ($intentos >= 3 && ($intentos % 3) == 0) {
                $indiceBloqueo = min(floor($intentos / 3) - 1, count($bloqueos) - 1);
                $tiempoEspera = $bloqueos[$indiceBloqueo];

                if (($tiempoActual - $ultimoIntento) < $tiempoEspera) {
                    // Aún está bloqueado
                    $tiempoRestante = $tiempoEspera - ($tiempoActual - $ultimoIntento);
                    $usuarioError = "⏳ Cuenta bloqueada. Intente nuevamente en " . gmdate("H:i:s", $tiempoRestante);
                } else {
                    // Bloqueo expiró, resetear el contador del bloque actual
                    $intentos = ($indiceBloqueo + 1) * 3;
                    $fechaActual = date("Y-m-d H:i:s");
                    $stmt_update = $conn->prepare("UPDATE administradores SET intentos_fallidos = ?, ultimo_intento = ? WHERE usuario = ?");
                    $stmt_update->bind_param("iss", $intentos, $fechaActual, $usuario);
                    $stmt_update->execute();
                    $stmt_update->close();
                }
            }

            // Si no hay error de bloqueo, verificar contraseña
            if (empty($usuarioError)) {
                if (password_verify($contra, $resultado['contraseña'])) {
                    // ✅ LOGIN EXITOSO

                    $fechaActual = date("Y-m-d H:i:s");

                    // 1. Cerrar sesiones anteriores
                    $sqlLimpieza = "UPDATE registro_ingresos SET fecha_salida = ? WHERE usuario = ? AND fecha_salida IS NULL";
                    $stmtLimp = $conn->prepare($sqlLimpieza);
                    $stmtLimp->bind_param("ss", $fechaActual, $usuario);
                    $stmtLimp->execute();
                    $stmtLimp->close();

                    // 2. Registrar nuevo ingreso
                    $nombre_completo = trim($resultado['nombre_admi'] . " " . $resultado['apellidos_admi']);
                    $rol = $resultado['rol'];

                    $sqlIngreso = "INSERT INTO registro_ingresos (usuario, nombre_completo, rol, fecha_ingreso) VALUES (?, ?, ?, ?)";
                    $stmtIngreso = $conn->prepare($sqlIngreso);
                    $stmtIngreso->bind_param("ssss", $usuario, $nombre_completo, $rol, $fechaActual);
                    $stmtIngreso->execute();
                    
                    // Guardar ID del registro
                    $registro_id = $stmtIngreso->insert_id;
                    $stmtIngreso->close();

                    // 3. Iniciar sesión
                    session_start();
                    session_regenerate_id(true);
                    $_SESSION['usuario'] = $usuario;
                    $_SESSION['rol'] = $rol;
                    $_SESSION['nombre_admi'] = $resultado['nombre_admi'];
                    $_SESSION['registro_ingreso'] = $registro_id; // ✅ Guardar ID

                    // 4. Reiniciar intentos fallidos
                    $stmt_update = $conn->prepare("UPDATE administradores SET intentos_fallidos = 0, ultimo_intento = ? WHERE usuario = ?");
                    $stmt_update->bind_param("ss", $fechaActual, $usuario);
                    $stmt_update->execute();
                    $stmt_update->close();

                    $conn->close();
                    header('Location: admin/bienvenido.php');
                    exit();
                } else {
                    // ❌ CONTRASEÑA INCORRECTA
                    $intentos++;
                    $fechaActual = date("Y-m-d H:i:s");
                    $stmt_update = $conn->prepare("UPDATE administradores SET intentos_fallidos = ?, ultimo_intento = ? WHERE usuario = ?");
                    $stmt_update->bind_param("iss", $intentos, $fechaActual, $usuario);
                    $stmt_update->execute();
                    $stmt_update->close();

                    // Calcular intentos restantes en el bloque actual
                    $intentosRestantes = 3 - ($intentos % 3);
                    if ($intentosRestantes > 0) {
                        $contraError = "🔒 Contraseña incorrecta. Intentos restantes: $intentosRestantes.";
                    } else {
                        $contraError = "⚠️ Has agotado los intentos. Tu cuenta está bloqueada temporalmente.";
                    }
                }
            }
        } else {
            $usuarioError = "❌ Usuario no encontrado.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>UniSalud – Administración</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" type="image/png" href="alumnos/imagenes/unisalud-sf.png">
</head>

<body>
    <div class="container">
        <div class="header-bar">
            <h4>UniSalud &ndash; Administración</h4>
        </div>
        <img src="alumnos/imagenes/unisalud-sf.png" alt="UniSalud" class="delfin-logo" style="object-fit:contain;">
        <form method="post" action="login.php">
            <div class="input-container">
                <label>Ingrese Usuario:</label>
                <input type="text" placeholder="Usuario" name="usuario" required>
                <?php if ($usuarioError): ?>
                    <p class="error-msg" id="usuarioError"><?php echo htmlspecialchars($usuarioError); ?></p>
                <?php endif; ?>
            </div>

            <div class="password-container">
                <label>Contraseña:</label>
                <input type="password" name="contra" placeholder="Ingrese contraseña" id="password" required>
                <span class="toggle-password" onclick="togglePassword()">👁️</span>
                <?php if ($contraError): ?>
                    <p class="error-msg" id="contraError"><?php echo htmlspecialchars($contraError); ?></p>
                <?php endif; ?>
            </div>
            <button type="submit">Ingresar al Sistema</button>
        </form>
        <!--
        <p class="footer-text">
            ¿Olvidaste tu <a href="nueva_pagina.php" class="forgot-password-link">Contraseña?</a> 
        </p>-->
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById("password");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        }

        function hideErrorMessages() {
            var usuarioError = document.getElementById('usuarioError');
            var contraError = document.getElementById('contraError');
            var inputContainer = document.querySelector('.input-container');
            var passwordContainer = document.querySelector('.password-container');

            if (usuarioError) {
                inputContainer.classList.add('error-active');
                usuarioError.style.display = 'block';
                setTimeout(function () {
                    usuarioError.style.display = 'none';
                    inputContainer.classList.remove('error-active');
                }, 7000);
            }

            if (contraError) {
                passwordContainer.classList.add('error-active');
                contraError.style.display = 'block';
                setTimeout(function () {
                    contraError.style.display = 'none';
                    passwordContainer.classList.remove('error-active');
                }, 7000);
            }
        }

        window.onload = hideErrorMessages;
    </script>
</body>

</html>