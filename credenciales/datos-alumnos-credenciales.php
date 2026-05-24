<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo "<tr><td colspan='9' class='text-center'>No autorizado</td></tr>";
    exit();
}

require_once '../config/config.php';
$conn = getDBConnection();
$conn->set_charset("utf8mb4");

$pagina      = isset($_GET['pagina'])   ? max(1, (int)$_GET['pagina'])   : 1;
$busqueda    = isset($_GET['busqueda']) ? trim($_GET['busqueda'])         : '';
$facultad    = isset($_GET['facultad']) ? (int)$_GET['facultad']         : 0;
$sexo        = isset($_GET['sexo'])     ? trim($_GET['sexo'])             : '';
$rowsPerPage = 20;
$offset      = ($pagina - 1) * $rowsPerPage;

$sql    = "SELECT a.matricula_alum,
                  CONCAT(a.nombres_alum, ' ', a.ape_paterno_alum, ' ', a.ape_materno_alum) AS nombre_completo,
                  f.nombre_facultad, a.sexo, a.nss, a.tipo_sangre, a.enfermedades, a.emergencia
           FROM alumnos a
           INNER JOIN facultad f ON a.id_facultad = f.id_facultad
           WHERE 1=1";
$params = [];
$types  = '';

if ($busqueda !== '') {
    $like     = '%' . $busqueda . '%';
    $sql     .= " AND (a.matricula_alum LIKE ? OR a.nombres_alum LIKE ? OR a.ape_paterno_alum LIKE ? OR a.ape_materno_alum LIKE ?)";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'ssss';
}
if ($facultad > 0) {
    $sql    .= " AND a.id_facultad = ?";
    $params[] = $facultad;
    $types   .= 'i';
}
if ($sexo !== '') {
    $sql    .= " AND a.sexo = ?";
    $params[] = $sexo;
    $types   .= 's';
}

$sql .= " ORDER BY a.matricula_alum ASC LIMIT ? OFFSET ?";
$params[] = $rowsPerPage;
$params[] = $offset;
$types   .= 'ii';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($fila['matricula_alum']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['nombre_completo']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['nombre_facultad']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['sexo']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['nss']        ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($fila['tipo_sangre'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($fila['enfermedades'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($fila['emergencia']   ?: 'N/A') . "</td>";
        echo "<td><a href='generar-credencial.php?matricula=" . urlencode($fila['matricula_alum']) . "' target='_blank' class='btn btn-sm btn-primary'><i class='bi bi-credit-card-2-front me-1'></i>Ver</a></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9' class='text-center'>NO HAY ALUMNOS</td></tr>";
}

$stmt->close();
$conn->close();
?>
