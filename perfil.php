<?php
header("Content-Type: application/json");

// Conectar a la base de datos
 $conn = new mysqli("pdb1042.awardspace.net", "4528622_pisi", "sklike5522", "4528622_pisi");

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// Obtener matrícula de la URL
$matricula = isset($_GET['matricula_alum']) ? $_GET['matricula_alum'] : "";

if (!$matricula) {
    echo json_encode(["error" => "Matrícula no proporcionada"]);
    exit;
}

// Consulta para obtener datos generales del alumno
$sql = "SELECT alumnos.matricula_alum, alumnos.nombres_alum, alumnos.ape_paterno_alum, 
        alumnos.ape_materno_alum, alumnos.edad_alum, alumnos.correo_alum, alumnos.sexo,
        carrera.nombre_carrera, facultad.nombre_facultad,
        COALESCE(estilo_de_vida.id_cuestionario, NULL) AS estilo_vida_realizado,
        COALESCE(dass.id_cuestionario, NULL) AS dass_realizado
        FROM alumnos
        INNER JOIN carrera ON alumnos.id_carrera = carrera.id_carrera
        INNER JOIN facultad ON alumnos.id_facultad = facultad.id_facultad
        LEFT JOIN estilo_de_vida ON alumnos.matricula_alum = estilo_de_vida.matricula_alum
        LEFT JOIN dass ON alumnos.matricula_alum = dass.matricula_alum
        WHERE alumnos.matricula_alum = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matricula);
$stmt->execute();
$result = $stmt->get_result();

// Consulta para obtener las fechas de los exámenes físicos
$sql_fechas = "SELECT fecha FROM datos_fisicos_alumnos WHERE matricula_alum = ? ORDER BY fecha DESC";
$stmt_fechas = $conn->prepare($sql_fechas);
$stmt_fechas->bind_param("s", $matricula);
$stmt_fechas->execute();
$result_fechas = $stmt_fechas->get_result();

$fechas = [];
while ($row = $result_fechas->fetch_assoc()) {
    $fechas[] = $row['fecha'];  // Guardamos las fechas en un array
}

// Consulta para obtener los datos físicos más recientes del alumno
$sql_datos_fisicos = "SELECT peso, talla, imc, clasificacion_imc, glucosa, colesterol, trigliceridos, tension_arterial 
                      FROM datos_fisicos_alumnos 
                      WHERE matricula_alum = ? 
                      ORDER BY fecha DESC LIMIT 1";
                      
$stmt_datos_fisicos = $conn->prepare($sql_datos_fisicos);
$stmt_datos_fisicos->bind_param("s", $matricula);
$stmt_datos_fisicos->execute();
$result_datos_fisicos = $stmt_datos_fisicos->get_result();



$datos_fisicos = $result_datos_fisicos->fetch_assoc() ?: [
    "peso" => null,
    "talla" => null,
    "imc" => null,
    "clasificacion_imc" => null,
    "glucosa" => null,
    "colesterol" => null,
    "trigliceridos" => null,
    "tension_arterial" => null
];

if ($result->num_rows > 0) {
    $alumno = $result->fetch_assoc();
    echo json_encode([
        "matricula_alum" => $alumno["matricula_alum"],
        "nombres_alum" => $alumno["nombres_alum"],
        "ape_paterno_alum" => $alumno["ape_paterno_alum"],
        "ape_materno_alum" => $alumno["ape_materno_alum"],
        "sexo" => $alumno["sexo"],
        "edad_alum" => $alumno["edad_alum"],
        "correo_alum" => $alumno["correo_alum"],
        "nombre_carrera" => $alumno["nombre_carrera"],
        "nombre_facultad" => $alumno["nombre_facultad"],
        "estilo_vida_realizado" => $alumno["estilo_vida_realizado"] ? 1 : 0, // Convertir NULL en 0 si no ha respondido
        "dass_realizado" => $alumno["dass_realizado"] ? 1 : 0,
        "fechas" => $fechas,  // Fechas de exámenes físicos
        "datos_fisicos" => $datos_fisicos  // Últimos datos físicos registrados
    ]);
} else {
    echo json_encode(["error" => "Alumno no encontrado"]);
}

$conn->close();
?>