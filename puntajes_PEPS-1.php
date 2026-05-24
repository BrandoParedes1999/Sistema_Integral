<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "pisi");

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

$matricula = isset($_GET['matricula_alum']) ? $_GET['matricula_alum'] : "";
if (!$matricula) {
    echo json_encode(["error" => "Matrícula no proporcionada"]);
    exit;
}

$sql = "SELECT 
            n.total_nutricion, e.total_ejercicio, s.total_salud, 
            si.total_soporte, me.total_manejoestres, a.total_autoactualizacion 
        FROM estilo_de_vida ev
        LEFT JOIN nutricion n ON ev.id_cuestionario = n.id_cuestionario
        LEFT JOIN ejercicio e ON ev.id_cuestionario = e.id_cuestionario
        LEFT JOIN salud s ON ev.id_cuestionario = s.id_cuestionario
        LEFT JOIN soporte_interpersonal si ON ev.id_cuestionario = si.id_cuestionario
        LEFT JOIN manejo_de_estres me ON ev.id_cuestionario = me.id_cuestionario
        LEFT JOIN autoactualizacion a ON ev.id_cuestionario = a.id_cuestionario
        WHERE ev.matricula_alum = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matricula); // Cambia a "i" si matricula_alum es entero
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode($data);
} else {
    echo json_encode(["error" => "No se encontraron datos"]);
}

$conn->close();
?>