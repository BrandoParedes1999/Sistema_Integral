<?php
// Configuración de la base de datos
$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";
// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar formulario si se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = intval($_POST['usuario']); // Asegurar que sea int
    if (strlen((string)$usuario) != 4) {
        die("El usuario debe ser un número de exactamente 4 dígitos.");
    }
    
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT); // Hash BCrypt
    $nombre_admi = $conn->real_escape_string($_POST['nombre_admi']);
    $apellidos_admi = $conn->real_escape_string($_POST['apellidos_admi']);
    $rol = $conn->real_escape_string($_POST['rol']);
    
    // Manejar foto (subida de archivo)
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto = file_get_contents($_FILES['foto']['tmp_name']);
    }
    
    // Insertar en la tabla
    $sql = "INSERT INTO administradores (usuario, contraseña, nombre_admi, apellidos_admi, rol, foto) 
            VALUES ($usuario, '$contrasena', '$nombre_admi', '$apellidos_admi', '$rol', ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $foto); // 's' para longblob como string binario
    if ($stmt->execute()) {
        echo "Cuenta creada exitosamente.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Creador de Cuentas Administradores</title>
</head>
<body>
    <h2>Crear Nueva Cuenta de Administrador</h2>
    <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>" enctype="multipart/form-data">
        <label for="usuario">Usuario (4 números):</label><br>
        <input type="number" id="usuario" name="usuario" min="1000" max="999999" required><br><br>
        
        <label for="contrasena">Contraseña:</label><br>
        <input type="password" id="contrasena" name="contrasena" maxlength="200" required><br><br>
        
        <label for="nombre_admi">Nombre Admin:</label><br>
        <input type="text" id="nombre_admi" name="nombre_admi" maxlength="100" required><br><br>
        
        <label for="apellidos_admi">Apellidos Admin:</label><br>
        <input type="text" id="apellidos_admi" name="apellidos_admi" maxlength="80" required><br><br>
        
        <label>Elige un Rol</label><br>
        <select name="rol" id="rol">
            <option value="Admininistrador">Administrador</option>
            <option value="Capturista">Capturista</option>
        </select>
        
        <label for="foto">Foto (archivo):</label><br>
        <input type="file" id="foto" name="foto" accept="image/*"><br><br>
        
        <input type="submit" value="Crear Cuenta">
    </form>
</body>
</html>