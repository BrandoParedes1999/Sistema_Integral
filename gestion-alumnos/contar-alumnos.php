<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado', 'total' => 0]);
    exit();
}

require_once '../config/config.php';
header('Content-Type: application/json');

$conn = getDBConnection();
$conn->set_charset("utf8mb4");

$filtroFacultad = isset($_GET['facultad']) ? (int)$_GET['facultad'] : 0;
$filtroSexo     = isset($_GET['sexo'])     ? trim($_GET['sexo'])     : '';
$filtroTexto    = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

$sql    = "SELECT COUNT(*) AS total FROM alumnos a JOIN facultad f ON a.id_facultad = f.id_facultad WHERE 1=1";
$params = [];
$types  = '';

if ($filtroFacultad > 0) {
    $sql    .= " AND a.id_facultad = ?";
    $params[] = $filtroFacultad;
    $types   .= 'i';
}
if ($filtroSexo !== '') {
    $sql    .= " AND a.sexo = ?";
    $params[] = $filtroSexo;
    $types   .= 's';
}
if ($filtroTexto !== '') {
    $like     = '%' . $filtroTexto . '%';
    $sql     .= " AND (a.matricula_alum LIKE ? OR a.nombres_alum LIKE ? OR a.ape_paterno_alum LIKE ? OR a.ape_materno_alum LIKE ?)";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'ssss';
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'total' => $total, 'paginas' => ceil($total / 20)]);
?>
