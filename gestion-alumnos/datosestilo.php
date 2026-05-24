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
    -- Columnas de la tabla principal (estilo_de_vida)
    ev.id_cuestionario,
    ev.matricula_alum,
    ev.fecha,
    ev.total AS total_general,
    ev.estado_saludable,

    -- Columnas de Nutrición
    n.p1, n.p5, n.p14, n.p19, n.p26, n.p35,
    n.total_nutricion,
    n.saludable AS saludable_nutricion,

    -- Columnas de Ejercicio
    ej.p4, ej.p13, ej.p22, ej.p30, ej.p38,
    ej.total_ejercicio,
    ej.saludable_ejercicio,

    -- Columnas de Responsabilidad en Salud
    s.p2, s.p7, s.p15, s.p20, s.p28, s.p32, s.p33, s.p42, s.p43, s.p46,
    s.total_salud,
    s.saludable_salud,

    -- Columnas de Soporte Interpersonal
    si.p10, si.p18, si.p24, si.p25, si.p31, si.p39, si.p47,
    si.total_soporte,
    si.saludable_soporte,

    -- Columnas de Manejo de Estrés
    me.p6, me.p11, me.p27, me.p36, me.p40, me.p41, me.p45,
    me.total_manejoestres,
    me.saludable_manejo,

    -- Columnas de Autoactualización
    aa.p3, aa.p8, aa.p9, aa.p12, aa.p16, aa.p17, aa.p21, aa.p23, aa.p29, aa.p34, aa.p37, aa.p44, aa.p48,
    aa.total_autoactualizacion,
    aa.saludable_autoactualizacion

FROM
    estilo_de_vida AS ev
INNER JOIN
    nutricion AS n ON ev.id_cuestionario = n.id_cuestionario
INNER JOIN
    ejercicio AS ej ON ev.id_cuestionario = ej.id_cuestionario
INNER JOIN
    salud AS s ON ev.id_cuestionario = s.id_cuestionario
INNER JOIN
    soporte_interpersonal AS si ON ev.id_cuestionario = si.id_cuestionario
INNER JOIN
    manejo_de_estres AS me ON ev.id_cuestionario = me.id_cuestionario
INNER JOIN
    autoactualizacion AS aa ON ev.id_cuestionario = aa.id_cuestionario
WHERE ev.matricula_alum = ?"; 

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