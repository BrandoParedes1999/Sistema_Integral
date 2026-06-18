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

$mat         = $_SESSION['alumno']['matricula'];
$actual      = trim($_POST['password_actual']   ?? '');
$nueva       = trim($_POST['password_nueva']    ?? '');
$confirmacion = trim($_POST['password_confirmar'] ?? '');

if (empty($nueva) || empty($confirmacion)) {
    echo json_encode(['error' => 'Ingresa la nueva contraseña y su confirmación']);
    exit();
}
if (strlen($nueva) < 8) {
    echo json_encode(['error' => 'La nueva contraseña debe tener al menos 8 caracteres']);
    exit();
}
if ($nueva !== $confirmacion) {
    echo json_encode(['error' => 'La nueva contraseña y su confirmación no coinciden']);
    exit();
}

$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$stmt = $conn->prepare("SELECT password FROM alumnos WHERE matricula_alum = ?");
$stmt->bind_param('s', $mat);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$tienePassword = !empty($row['password']);

if ($tienePassword) {
    // User already has a password — require current password verification
    if (empty($actual)) {
        $conn->close();
        echo json_encode(['error' => 'Ingresa tu contraseña actual']);
        exit();
    }
    if (!password_verify($actual, $row['password'])) {
        $conn->close();
        echo json_encode(['error' => 'La contraseña actual es incorrecta']);
        exit();
    }
}
// If $tienePassword is false, skip verification — allow first-time password setup

$hash = password_hash($nueva, PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE alumnos SET password = ? WHERE matricula_alum = ?");
$stmt->bind_param('ss', $hash, $mat);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
} else {
    echo json_encode(['error' => 'Error al actualizar la contraseña']);
}
?>
