<?php
require_once '../config/config.php';
$conn = getDBConnection();
$conn->set_charset("utf8mb4");

$filtroFacultad = isset($_GET['facultad']) ? (int)$_GET['facultad'] : "";
$filtroSexo     = isset($_GET['sexo'])     ? $_GET['sexo']           : "";
$filtroTexto    = isset($_GET['busqueda']) ? $_GET['busqueda']        : "";

$sql = "SELECT COUNT(*) as total
        FROM alumnos a
        JOIN facultad f ON a.id_facultad = f.id_facultad
        WHERE 1=1";

if (!empty($filtroFacultad)) {
    $sql .= " AND a.id_facultad = " . intval($filtroFacultad);
}
if (!empty($filtroSexo)) {
    $filtroSexo = $conn->real_escape_string($filtroSexo);
    $sql .= " AND a.sexo = '$filtroSexo'";
}
if (!empty($filtroTexto)) {
    $filtroTexto = $conn->real_escape_string($filtroTexto);
    $sql .= " AND (a.matricula_alum LIKE '%$filtroTexto%'
              OR a.nombres_alum LIKE '%$filtroTexto%'
              OR a.ape_paterno_alum LIKE '%$filtroTexto%'
              OR a.ape_materno_alum LIKE '%$filtroTexto%')";
}

$result = $conn->query($sql);
header('Content-Type: application/json');

if ($result) {
    $row   = $result->fetch_assoc();
    $total = (int)$row['total'];
    echo json_encode(['success' => true, 'total' => $total, 'paginas' => ceil($total / 20)]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al contar registros', 'total' => 0]);
}

$conn->close();
?>
