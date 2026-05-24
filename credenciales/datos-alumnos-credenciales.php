<?php
session_start();

$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$facultad = isset($_GET['facultad']) ? trim($_GET['facultad']) : '';
$sexo = isset($_GET['sexo']) ? trim($_GET['sexo']) : '';

$rowsPerPage = 20;
$offset = ($pagina - 1) * $rowsPerPage;

$sql = "SELECT 
            a.matricula_alum,
            CONCAT(a.nombres_alum, ' ', a.ape_paterno_alum, ' ', a.ape_materno_alum) AS nombre_completo,
            f.nombre_facultad,
            a.sexo,
            a.nss,
            a.tipo_sangre,
            a.enfermedades,
            a.emergencia
        FROM alumnos a
        INNER JOIN facultad f ON a.id_facultad = f.id_facultad
        WHERE 1=1";

if (!empty($busqueda)) {
    $busqueda_escape = $conn->real_escape_string($busqueda);
    $sql .= " AND (a.matricula_alum LIKE '%$busqueda_escape%' 
              OR a.nombres_alum LIKE '%$busqueda_escape%'
              OR a.ape_paterno_alum LIKE '%$busqueda_escape%'
              OR a.ape_materno_alum LIKE '%$busqueda_escape%')";
}

if (!empty($facultad)) {
    $facultad_escape = $conn->real_escape_string($facultad);
    $sql .= " AND a.id_facultad = '$facultad_escape'";
}

if (!empty($sexo)) {
    $sexo_escape = $conn->real_escape_string($sexo);
    $sql .= " AND a.sexo = '$sexo_escape'";
}

$sql .= " ORDER BY a.matricula_alum ASC LIMIT $rowsPerPage OFFSET $offset";

$resultado = $conn->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($fila['matricula_alum']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['nombre_completo']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['nombre_facultad']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['sexo']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['nss'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($fila['tipo_sangre'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($fila['enfermedades'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($fila['emergencia'] ?: 'N/A') . "</td>";
        echo "<td>";
        echo "<a href='generar-credencial.php?matricula=" . urlencode($fila['matricula_alum']) . "' target='_blank' class='btn btn-sm btn-primary'>";
        echo "<i class='bi bi-credit-card-2-front me-1'></i>Ver";
        echo "</a>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9' class='text-center'>NO HAY ALUMNOS</td></tr>";
}

$conn->close();
?>