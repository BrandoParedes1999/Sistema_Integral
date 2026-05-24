<?php
// Conexión a la base de datos
$conn = new mysqli("pdb1042.awardspace.net", "4528622_pisi", "sklike5522", "4528622_pisi");

// Verificar conexión
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error de conexión', 'total' => 0]);
    exit();
}

// Establecer charset
$conn->set_charset("utf8");

// Obtener filtros desde JavaScript
$filtroFacultad = isset($_GET['facultad']) ? (int)$_GET['facultad'] : "";
$filtroSexo = isset($_GET['sexo']) ? $_GET['sexo'] : "";
$filtroTexto = isset($_GET['busqueda']) ? $_GET['busqueda'] : "";

// Construir la consulta de conteo
$sql = "SELECT COUNT(*) as total 
        FROM alumnos a 
        JOIN facultad f ON a.id_facultad = f.id_facultad 
        WHERE 1=1";

// Aplicar filtros dinámicamente
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

// Ejecutar la consulta
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total = $row['total'];
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'total' => $total,
        'paginas' => ceil($total / 20) // 20 registros por página
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Error al contar registros',
        'total' => 0
    ]);
}

// Cerrar conexión
$conn->close();
?>