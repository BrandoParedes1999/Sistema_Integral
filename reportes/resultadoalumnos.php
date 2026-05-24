<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo "<tr><td colspan='8' class='text-center'>No autorizado</td></tr>";
    exit();
}

require_once '../config/config.php';
$conn = getDBConnection();
$conn->set_charset("utf8mb4");

$filtroFacultad = isset($_GET['facultad']) ? (int)$_GET['facultad']    : 0;
$filtroSexo     = isset($_GET['sexo'])     ? trim($_GET['sexo'])        : '';
$filtroTexto    = isset($_GET['busqueda']) ? trim($_GET['busqueda'])    : '';
$pagina         = isset($_GET['pagina'])   ? max(1, (int)$_GET['pagina']) : 1;
$limite         = 20;
$offset         = ($pagina - 1) * $limite;

$sql    = "SELECT a.matricula_alum, a.nombres_alum, a.ape_paterno_alum, a.ape_materno_alum,
                  a.sexo, f.nombre_facultad,
                  (SELECT COUNT(*) FROM dass WHERE dass.matricula_alum = a.matricula_alum) AS tiene_dass,
                  (SELECT COUNT(*) FROM estilo_de_vida WHERE estilo_de_vida.matricula_alum = a.matricula_alum) AS tiene_estilo_vida,
                  (SELECT COUNT(*) FROM datos_fisicos_alumnos WHERE datos_fisicos_alumnos.matricula_alum = a.matricula_alum) AS tiene_datos_fisicos
           FROM alumnos a
           JOIN facultad f ON a.id_facultad = f.id_facultad
           WHERE 1=1";
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

$sql .= " ORDER BY a.matricula_alum ASC LIMIT ? OFFSET ?";
$params[] = $limite;
$params[] = $offset;
$types   .= 'ii';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Generar filas de la tabla
if ($result && $result->num_rows > 0) { 
    while ($row = $result->fetch_assoc()) {
        $nombreCompleto = htmlspecialchars($row['nombres_alum'] . " " . $row['ape_paterno_alum'] . " " . $row['ape_materno_alum']);
        $matricula = htmlspecialchars($row['matricula_alum']);
        $facultad = htmlspecialchars($row['nombre_facultad']);
        $sexo = htmlspecialchars($row['sexo']);
        
        // Crear badges para los formularios completados
        if ($row['tiene_dass'] > 0) {
            $iconoDass = '<span class="badge-evaluacion completado" title="Completado">
                            <i class="bi bi-check-circle-fill"></i>
                          </span>';
        } else {
            $iconoDass = '<span class="badge-evaluacion pendiente" title="Pendiente">
                            <i class="bi bi-x-circle-fill"></i>
                          </span>';
        }
        
        if ($row['tiene_estilo_vida'] > 0) {
            $iconoEstiloVida = '<span class="badge-evaluacion completado" title="Completado">
                                  <i class="bi bi-check-circle-fill"></i>
                                </span>';
        } else {
            $iconoEstiloVida = '<span class="badge-evaluacion pendiente" title="Pendiente">
                                  <i class="bi bi-x-circle-fill"></i>
                                </span>';
        }
        
        if ($row['tiene_datos_fisicos'] > 0) {
            $iconoDatosFisicos = '<span class="badge-evaluacion completado" title="Completado">
                                    <i class="bi bi-check-circle-fill"></i>
                                  </span>';
        } else {
            $iconoDatosFisicos = '<span class="badge-evaluacion pendiente" title="Pendiente">
                                    <i class="bi bi-x-circle-fill"></i>
                                  </span>';
        }
        
        // Verificar si existe el PDF de resultado
        $carpetaMatricula = "reportes_salud/" . $matricula;
        $rutaPdfUltimo = $carpetaMatricula . "/reporte_" . $matricula . "_ultimo.pdf";
        
        if (file_exists($rutaPdfUltimo)) {
            // Si existe el PDF "último"
            $botonResultado = "<a href='$rutaPdfUltimo' class='btn-resultado' target='_blank' title='Ver reporte PDF'>
                                <i class='bi bi-file-earmark-pdf-fill'></i> Ver PDF
                               </a>";
        } elseif (is_dir($carpetaMatricula)) {
            // Si no existe "último", buscar el más reciente por fecha
            $archivos = glob($carpetaMatricula . "/reporte_*.pdf");
            if (!empty($archivos)) {
                // Ordenar por fecha de modificación (más reciente primero)
                usort($archivos, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $rutaPdfReciente = $archivos[0];
                $botonResultado = "<a href='$rutaPdfReciente' class='btn-resultado' target='_blank' title='Ver reporte PDF'>
                                    <i class='bi bi-file-earmark-pdf-fill'></i> Ver PDF
                                   </a>";
            } else {
                $botonResultado = '<span class="sin-resultado">
                                    <i class="bi bi-file-earmark-x"></i> Sin resultado
                                   </span>';
            }
        } else {
            $botonResultado = '<span class="sin-resultado">
                                <i class="bi bi-file-earmark-x"></i> Sin resultado
                               </span>';
        }
        
        echo "<tr>";
        echo "<td><strong>{$matricula}</strong></td>";
        echo "<td>{$nombreCompleto}</td>";
        echo "<td>{$facultad}</td>";
        echo "<td>{$sexo}</td>";
        echo "<td>{$iconoDass}</td>";
        echo "<td>{$iconoEstiloVida}</td>";
        echo "<td>{$iconoDatosFisicos}</td>";
        echo "<td>{$botonResultado}</td>";
        echo "</tr>";
    }
} else {
    echo "<tr>
            <td colspan='8' class='text-center py-4'>
                <div class='empty-state'>
                    <i class='bi bi-inbox'></i>
                    <p>No se encontraron alumnos con los criterios de búsqueda</p>
                </div>
            </td>
          </tr>";
}

$stmt->close();
$conn->close();
?>