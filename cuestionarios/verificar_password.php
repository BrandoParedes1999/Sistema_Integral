<?php
require_once '../config/config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no iniciada']);
    exit();
}

$usuario = $_SESSION['usuario'];
$inputPassword = $_POST['password'] ?? '';

$conn = getDBConnection();

if ($conn->connect_errno) {
    echo json_encode(['success' => false, 'mensaje' => 'Error de conexión']);
    exit();
}

$sql = "SELECT contraseña FROM administradores WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($passwordHash);
$stmt->fetch();
$stmt->close();
$conn->close();

if (password_verify($inputPassword, $passwordHash)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Contraseña incorrecta']);
}
?>
