<?php
session_start();
if (!isset($_SESSION['usuario'])) { http_response_code(401); echo json_encode(['error' => 'No autorizado']); exit(); }

require_once '../config/config.php';
$conn = getDBConnection();
$conn->set_charset("utf8mb4");

header('Content-Type: application/json');

$tipo = $_GET['tipo'] ?? '';

if ($tipo === 'facultades') {
    $sql = "SELECT id_facultad, nombre_facultad FROM facultad ORDER BY nombre_facultad ASC";
} elseif ($tipo === 'carreras' && isset($_GET['idfacultad'])) {
    $idfacultad = intval($_GET['idfacultad']);
    $sql = "SELECT id_carrera, nombre_carrera FROM carrera WHERE id_facultad = $idfacultad";
} else {
    echo json_encode([]);
    exit;
}

$result = $conn->query($sql);
$datos  = [];
if ($result) {
    while ($fila = $result->fetch_assoc()) {
        $datos[] = $fila;
    }
}
echo json_encode($datos);
$conn->close();
?>
