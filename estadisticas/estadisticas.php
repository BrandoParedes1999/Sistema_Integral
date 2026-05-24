<?php
header('Content-Type: application/json');
 $conn = new mysqli("pdb1042.awardspace.net", "4528622_pisi", "sklike5522", "4528622_pisi");

// Verifica la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';

if ($filtro === 'comparar_facultades') {
    $query = "SELECT 
                f.nombre_facultad,
                COUNT(DISTINCT e.matricula_alum) AS total_alumnos,
                SUM(CASE WHEN e.estado_saludable = 'Saludable' THEN 1 ELSE 0 END) AS saludables,
                SUM(CASE WHEN e.estado_saludable = 'No Saludable' THEN 1 ELSE 0 END) AS no_saludables
              FROM estilo_de_vida e
              JOIN alumnos a ON e.matricula_alum = a.matricula_alum
              JOIN facultad f ON a.id_facultad = f.id_facultad
              GROUP BY f.id_facultad, f.nombre_facultad";
    
    $result = $conn->query($query);
    $labels = [];
    $total_alumnos = [];
    $saludables = [];
    $no_saludables = [];
    
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['nombre_facultad'];
        $total_alumnos[] = $row['total_alumnos'];
        $saludables[] = $row['saludables'];
        $no_saludables[] = $row['no_saludables'];
    }
    
    $data = [
        'labels' => $labels,
        'total_alumnos' => $total_alumnos,
        'saludables' => $saludables,
        'no_saludables' => $no_saludables
    ];
    
} else if ($filtro === 'comparar_sexo') {
    $query = "SELECT 
                SUM(CASE WHEN e.estado_saludable = 'Saludable' AND a.sexo = 'MASCULINO' THEN 1 ELSE 0 END) AS hombres_saludables,
                SUM(CASE WHEN e.estado_saludable = 'No Saludable' AND a.sexo = 'MASCULINO' THEN 1 ELSE 0 END) AS hombres_no_saludables,
                SUM(CASE WHEN e.estado_saludable = 'Saludable' AND a.sexo = 'FEMENINO' THEN 1 ELSE 0 END) AS mujeres_saludables,
                SUM(CASE WHEN e.estado_saludable = 'No Saludable' AND a.sexo = 'FEMENINO' THEN 1 ELSE 0 END) AS mujeres_no_saludables
              FROM estilo_de_vida e
              JOIN alumnos a ON e.matricula_alum = a.matricula_alum";
    
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    
} else {
    $where = '';
    if ($filtro === 'masculino') {
        $where = "WHERE a.sexo = 'MASCULINO'";
    } elseif ($filtro === 'femenino') {
        $where = "WHERE a.sexo = 'FEMENINO'";
    }

    $query = "SELECT 
                SUM(CASE WHEN e.estado_saludable = 'Saludable' THEN 1 ELSE 0 END) AS saludables,
                SUM(CASE WHEN e.estado_saludable = 'No Saludable' THEN 1 ELSE 0 END) AS noSaludables
              FROM estilo_de_vida e
              JOIN alumnos a ON e.matricula_alum = a.matricula_alum
              $where";
    
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
}

echo json_encode($data);
$conn->close();
?>