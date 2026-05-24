<?php
session_start();
date_default_timezone_set('America/Mexico_City');
// Registrar hora de salida si hay ingreso
require_once 'session_security.php';
if (isset($_SESSION['registro_ingreso'])) {
    $registroId = $_SESSION['registro_ingreso'];

    $conn = new mysqli("pdb1042.awardspace.net", "4528622_pisi", "sklike5522", "4528622_pisi");

    if (!$conn->connect_errno) {
        $fechaSalida = date('Y-m-d H:i:s');
        $sqlUpdate = "UPDATE registro_ingresos SET fecha_salida = ? WHERE id = ?";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bind_param("si", $fechaSalida, $registroId);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}

// 🔥 1. Limpiar todas las variables de sesión
$_SESSION = [];

// 🔥 2. Destruir la sesión en el servidor
session_destroy();

// 🔥 3. Borrar la cookie de sesión en el navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 🔥 4. Redirigir seguro al login
header("Location: login.php");
exit();
?>
