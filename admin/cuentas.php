<?php
session_start();
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] ?? '') !== 'Administrador') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/config.php';
$conn = getDBConnection();
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$mensaje = '';
$error   = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = intval($_POST['usuario'] ?? 0);
    if (strlen((string)$usuario) !== 4) {
        $error = "El usuario debe ser un número de exactamente 4 dígitos.";
    } else {
        $pw_raw = $_POST['contrasena'] ?? '';
        if (strlen($pw_raw) < 8) {
            $error = "La contraseña debe tener al menos 8 caracteres.";
        } else {
            $contrasena    = password_hash($pw_raw, PASSWORD_BCRYPT);
            $nombre_admi   = trim($_POST['nombre_admi']    ?? '');
            $apellidos_admi = trim($_POST['apellidos_admi'] ?? '');
            $roles_validos = ['Administrador', 'Capturista'];
            $rol           = in_array($_POST['rol'] ?? '', $roles_validos) ? $_POST['rol'] : 'Capturista';

            // Validar y leer foto
            $foto = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($_FILES['foto']['tmp_name']);
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($mime, $allowed)) {
                    $error = "Tipo de archivo no permitido. Solo se aceptan imágenes (JPEG, PNG, GIF, WEBP).";
                } else {
                    $foto = file_get_contents($_FILES['foto']['tmp_name']);
                }
            }

            if (!$error) {
                $sql  = "INSERT INTO administradores (usuario, contraseña, nombre_admi, apellidos_admi, rol, foto)
                         VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssss", $usuario, $contrasena, $nombre_admi, $apellidos_admi, $rol, $foto);
                if ($stmt->execute()) {
                    $mensaje = "Cuenta creada exitosamente.";
                } else {
                    $error = "Error al crear la cuenta.";
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Cuenta – Administración</title>
    <link rel="icon" type="image/png" href="../alumnos/imagenes/unisalud-sf.png">
</head>
<body>
    <h2>Crear Nueva Cuenta de Administrador</h2>
    <?php if ($mensaje): ?><p style="color:green;"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>
    <?php if ($error):   ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="cuentas.php" enctype="multipart/form-data">
        <label for="usuario">Usuario (4 dígitos):</label><br>
        <input type="number" id="usuario" name="usuario" min="1000" max="9999" required><br><br>

        <label for="contrasena">Contraseña (mín. 8 caracteres):</label><br>
        <input type="password" id="contrasena" name="contrasena" minlength="8" maxlength="200" required><br><br>

        <label for="nombre_admi">Nombre:</label><br>
        <input type="text" id="nombre_admi" name="nombre_admi" maxlength="100" required><br><br>

        <label for="apellidos_admi">Apellidos:</label><br>
        <input type="text" id="apellidos_admi" name="apellidos_admi" maxlength="80" required><br><br>

        <label>Rol:</label><br>
        <select name="rol" id="rol">
            <option value="Administrador">Administrador</option>
            <option value="Capturista">Capturista</option>
        </select><br><br>

        <label for="foto">Foto (JPEG/PNG):</label><br>
        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/gif,image/webp"><br><br>

        <input type="submit" value="Crear Cuenta">
    </form>
</body>
</html>
