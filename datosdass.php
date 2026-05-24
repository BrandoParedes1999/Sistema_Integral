<?php

header("Content-Type: application/json");

$matricula = isset($_GET['matricula_alum']) ? $_GET['matricula_alum'] : "";

if (!$matricula) {
    http_response_code(400);
    echo json_encode(["error" => "El parámetro 'matricula_alum' es requerido."]);
    exit;
}

// Conexión a la base de datos (tus datos de conexión)
$conn = new mysqli("pdb1042.awardspace.net", "4528622_pisi", "sklike5522", "4528622_pisi");

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Error de conexión a la base de datos: " . $conn->connect_error]));
}

$sql = "SELECT
    d.id_cuestionario,
    d.matricula_alum,
    d.total_general,
    dep.p3, dep.p5, dep.p10, dep.p13, dep.p16, dep.p17, dep.p21,
    dep.total_depresion,
    dep.severidad AS severidad_depresion,
    a.p2, a.p4, a.p7, a.p9, a.p15, a.p19, a.p20,
    a.total_ansiedad,
    a.severidad AS severidad_ansiedad,
    e.p1, e.p6, e.p8, e.p11, e.p12, e.p14, e.p18,
    e.total_estres,
    e.severidad AS severidad_estres
FROM
    dass AS d
INNER JOIN
    dass_depresion AS dep ON d.id_cuestionario = dep.id_cuestionario
INNER JOIN
    dass_ansiedad AS a ON d.id_cuestionario = a.id_cuestionario
INNER JOIN
    dass_estres AS e ON d.id_cuestionario = e.id_cuestionario
WHERE d.matricula_alum = ?"; 

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matricula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $datos = $result->fetch_assoc();
    echo json_encode($datos);
} else {
    http_response_code(404); 
    echo json_encode(["error" => "No se encontraron datos para la matrícula proporcionada."]);
}

// Cerrar la sentencia y la conexión
$stmt->close();
$conn->close();
?>