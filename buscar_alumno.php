<?php
$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}

// Permitir GET y POST
$matricula = $_GET["matricula"] ?? $_POST["matricula"] ?? '';

if (empty($matricula)) {
    die(json_encode(["error" => "Matrícula no proporcionada"]));
}

$sql = "SELECT 
            a.matricula_alum, a.nombres_alum, a.ape_materno_alum, a.ape_paterno_alum, 
            a.edad_alum, a.sexo, a.correo_alum, a.fe_nacimiento_alum, 
            c.nombre_carrera, f.nombre_facultad
        FROM alumnos a
        LEFT JOIN carrera c ON a.id_carrera = c.id_carrera
        LEFT JOIN facultad f ON a.id_facultad = f.id_facultad
        WHERE a.matricula_alum = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matricula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "Alumno no encontrado"]);
}

$stmt->close();
$conn->close();
?>