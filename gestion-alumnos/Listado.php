<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo "<tr><td colspan='6' class='text-center'>No autorizado</td></tr>";
    exit();
}

require_once '../config/config.php';
$conn = getDBConnection();
$conn->set_charset("utf8mb4");

$filtroFacultad = isset($_GET['facultad']) ? (int)$_GET['facultad']      : 0;
$filtroSexo     = isset($_GET['sexo'])     ? trim($_GET['sexo'])          : '';
$filtroTexto    = isset($_GET['busqueda']) ? trim($_GET['busqueda'])      : '';
$pagina         = isset($_GET['pagina'])   ? max(1, (int)$_GET['pagina']) : 1;
$obtenerTotal   = isset($_GET['total']);
$limite         = 20;
$offset         = ($pagina - 1) * $limite;

// Shared WHERE clause built with prepared-statement placeholders
$where  = " WHERE 1=1";
$params = [];
$types  = '';

if ($filtroFacultad > 0) {
    $where   .= " AND a.id_facultad = ?";
    $params[] = $filtroFacultad;
    $types   .= 'i';
}
if ($filtroSexo !== '') {
    $where   .= " AND a.sexo = ?";
    $params[] = $filtroSexo;
    $types   .= 's';
}
if ($filtroTexto !== '') {
    $like     = '%' . $filtroTexto . '%';
    $where   .= " AND (a.matricula_alum LIKE ? OR a.nombres_alum LIKE ? OR a.ape_paterno_alum LIKE ? OR a.ape_materno_alum LIKE ?)";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'ssss';
}

if ($obtenerTotal) {
    $sql  = "SELECT COUNT(*) AS total FROM alumnos a JOIN facultad f ON a.id_facultad = f.id_facultad" . $where;
    $stmt = $conn->prepare($sql);
    if ($params) { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode([
        'total_registros'      => $total,
        'total_paginas'        => ceil($total / $limite),
        'registros_por_pagina' => $limite,
        'pagina_actual'        => $pagina
    ]);
    exit();
}

// Data query
$dataParams  = $params;
$dataTypes   = $types;
$dataParams[] = $limite;
$dataParams[] = $offset;
$dataTypes   .= 'ii';

$sql  = "SELECT a.matricula_alum, a.nombres_alum, a.ape_paterno_alum, a.ape_materno_alum,
                a.sexo, f.nombre_facultad
         FROM alumnos a JOIN facultad f ON a.id_facultad = f.id_facultad" . $where . " ORDER BY a.matricula_alum ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($dataParams) { $stmt->bind_param($dataTypes, ...$dataParams); }
$stmt->execute();
$result   = $stmt->get_result();
$contador = $offset + 1;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nombre   = htmlspecialchars($row['nombres_alum'] . ' ' . $row['ape_paterno_alum'] . ' ' . $row['ape_materno_alum']);
        $matricula = htmlspecialchars($row['matricula_alum']);
        $facultad  = htmlspecialchars($row['nombre_facultad']);
        $sexo      = htmlspecialchars($row['sexo']);
        echo "<tr>
                <td>{$contador}</td>
                <td>{$matricula}</td>
                <td>{$nombre}</td>
                <td>{$facultad}</td>
                <td>{$sexo}</td>
                <td><a href='expediente.html?matricula_alum={$matricula}' class='btn-accion btn-ver'><i class='bi bi-eye'></i> Ver Perfil</a></td>
              </tr>";
        $contador++;
    }
} else {
    echo "<tr><td colspan='6' class='text-center py-4'><div class='empty-state'><i class='bi bi-inbox'></i><p>No se encontraron alumnos con los criterios de búsqueda</p></div></td></tr>";
}

$stmt->close();
$conn->close();
?>
