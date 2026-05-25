<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['alumno'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

require_once '../config/config.php';

$mat       = $_SESSION['alumno']['matricula'];
$correo    = trim($_POST['correo']    ?? '');
$emergencia = trim($_POST['emergencia'] ?? '');
$nss       = trim($_POST['nss']       ?? '');

if (empty($correo) || empty($emergencia)) {
    echo json_encode(['error' => 'Correo y teléfono de emergencia son obligatorios']);
    exit();
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'El correo electrónico no es válido']);
    exit();
}
if (!preg_match('/^\d{10}$/', $emergencia)) {
    echo json_encode(['error' => 'El teléfono de emergencia debe tener exactamente 10 dígitos']);
    exit();
}
if ($nss !== '' && !preg_match('/^\d{11}$/', $nss)) {
    echo json_encode(['error' => 'El NSS debe tener 11 dígitos o dejarse vacío']);
    exit();
}

$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$stmt = $conn->prepare("UPDATE alumnos SET correo_alum = ?, emergencia = ?, nss = ? WHERE matricula_alum = ?");
$stmt->bind_param('ssss', $correo, $emergencia, $nss, $mat);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

if ($ok) {
    $_SESSION['alumno']['correo'] = $correo;
    echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);
} else {
    echo json_encode(['error' => 'Error al actualizar los datos']);
}
?>
