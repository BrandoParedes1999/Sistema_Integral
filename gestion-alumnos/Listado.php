<?php
// Conexión a la base de datos
$conn = new mysqli("pdb1042.awardspace.net", "4528622_pisi", "sklike5522", "4528622_pisi");

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener filtros desde JavaScript
$filtroFacultad = isset($_GET['facultad']) ? (int)$_GET['facultad'] : "";
$filtroSexo = isset($_GET['sexo']) ? $_GET['sexo'] : "";
$filtroTexto = isset($_GET['busqueda']) ? $_GET['busqueda'] : "";
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$obtenerTotal = isset($_GET['total']) ? true : false;

$limite = 20; // Número de alumnos por página
$offset = ($pagina - 1) * $limite;

// Construir la consulta base
$sqlBase = "FROM alumnos a 
            JOIN facultad f ON a.id_facultad = f.id_facultad 
            WHERE 1=1";

// Aplicar filtros dinámicamente
$filtros = "";

if (!empty($filtroFacultad)) {
    $filtros .= " AND a.id_facultad = " . intval($filtroFacultad);
}

if (!empty($filtroSexo)) {
    $filtroSexo = $conn->real_escape_string($filtroSexo);
    $filtros .= " AND a.sexo = '$filtroSexo'";
}

if (!empty($filtroTexto)) {
    $filtroTexto = $conn->real_escape_string($filtroTexto);
    $filtros .= " AND (a.matricula_alum LIKE '%$filtroTexto%' 
                  OR a.nombres_alum LIKE '%$filtroTexto%' 
                  OR a.ape_paterno_alum LIKE '%$filtroTexto%' 
                  OR a.ape_materno_alum LIKE '%$filtroTexto%')";
}

// Si se solicita el total de registros
if ($obtenerTotal) {
    $sqlTotal = "SELECT COUNT(*) as total " . $sqlBase . $filtros;
    $resultTotal = $conn->query($sqlTotal);
    
    if ($resultTotal) {
        $rowTotal = $resultTotal->fetch_assoc();
        $totalRegistros = $rowTotal['total'];
        $totalPaginas = ceil($totalRegistros / $limite);
        
        echo json_encode([
            'total_registros' => $totalRegistros,
            'total_paginas' => $totalPaginas,
            'registros_por_pagina' => $limite,
            'pagina_actual' => $pagina
        ]);
    } else {
        echo json_encode([
            'error' => 'Error al obtener el total de registros'
        ]);
    }
} else {
    // Consulta para obtener los datos con paginación
    $sql = "SELECT a.matricula_alum, a.nombres_alum, a.ape_paterno_alum, a.ape_materno_alum, 
            a.sexo, f.nombre_facultad " . $sqlBase . $filtros;
    
    // Ordenar por matrícula para consistencia
    $sql .= " ORDER BY a.matricula_alum ASC";
    
    // Paginación
    $sql .= " LIMIT $limite OFFSET $offset";

    // Ejecutar la consulta
    $result = $conn->query($sql);
    $contador = $offset + 1;

    // Generar filas de la tabla
    if ($result && $result->num_rows > 0) { 
        while ($row = $result->fetch_assoc()) {
            $nombreCompleto = htmlspecialchars($row['nombres_alum'] . " " . $row['ape_paterno_alum'] . " " . $row['ape_materno_alum']);
            $matricula = htmlspecialchars($row['matricula_alum']);
            $facultad = htmlspecialchars($row['nombre_facultad']);
            $sexo = htmlspecialchars($row['sexo']);
            
            echo "<tr>";
            echo "<td>{$contador}</td>";
            echo "<td>{$matricula}</td>";
            echo "<td>{$nombreCompleto}</td>";
            echo "<td>{$facultad}</td>";
            echo "<td>{$sexo}</td>";
            echo "<td>
                    <a href='expediente.html?matricula_alum={$matricula}' class='btn-accion btn-ver'>
                        <i class='bi bi-eye'></i> Ver Perfil
                    </a>
                  </td>";
            echo "</tr>";
            $contador++;
        }
    } else {
        echo "<tr><td colspan='6' class='text-center py-4'>
                <div class='empty-state'>
                    <i class='bi bi-inbox'></i>
                    <p>No se encontraron alumnos con los criterios de búsqueda</p>
                </div>
              </td></tr>";
    }
}

// Cerrar conexión
$conn->close();
?>
