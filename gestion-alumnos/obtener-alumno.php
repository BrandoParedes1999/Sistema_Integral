<?php
session_start();
if (!isset($_SESSION['usuario'])) { http_response_code(401); echo json_encode(['error' => 'No autorizado']); exit(); }
require_once '../config/config.php';
header('Content-Type: application/json');

$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$mat = trim($_GET['matricula'] ?? '');
if (!$mat) { echo json_encode(['error' => 'Matrícula requerida']); exit(); }

$stmt = $conn->prepare(
    "SELECT a.matricula_alum, a.nombres_alum, a.ape_paterno_alum, a.ape_materno_alum,
            a.correo_alum, a.sexo, a.generacion, a.nss, a.emergencia, a.tipo_sangre,
            a.id_facultad, a.id_carrera,
            f.nombre_facultad, c.nombre_carrera
     FROM alumnos a
     LEFT JOIN facultad f ON a.id_facultad = f.id_facultad
     LEFT JOIN carrera  c ON a.id_carrera  = c.id_carrera
     WHERE a.matricula_alum = ?"
);
$stmt->bind_param('s', $mat);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$row) { echo json_encode(['error' => 'Alumno no encontrado']); exit(); }
echo json_encode($row, JSON_UNESCAPED_UNICODE);
?>
