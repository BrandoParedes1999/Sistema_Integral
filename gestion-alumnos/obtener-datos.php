<?php
session_start();
if (!isset($_SESSION['usuario'])) { http_response_code(401); echo json_encode([]); exit(); }
require_once '../config/config.php';
header('Content-Type: application/json; charset=utf-8');

$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$tipo = $_GET['tipo'] ?? '';

if ($tipo === 'facultades') {
    $result = $conn->query("SELECT id_facultad, nombre_facultad FROM facultad ORDER BY nombre_facultad ASC");
    $datos = [];
    while ($fila = $result->fetch_assoc()) { $datos[] = $fila; }
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);

} elseif ($tipo === 'carreras' && isset($_GET['idfacultad'])) {
    $idfacultad = (int)$_GET['idfacultad'];
    $stmt = $conn->prepare("SELECT id_carrera, nombre_carrera FROM carrera WHERE id_facultad = ? ORDER BY nombre_carrera ASC");
    $stmt->bind_param('i', $idfacultad);
    $stmt->execute();
    $datos = [];
    $res = $stmt->get_result();
    while ($fila = $res->fetch_assoc()) { $datos[] = $fila; }
    $stmt->close();
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);

} else {
    echo json_encode([]);
}
$conn->close();
?>
