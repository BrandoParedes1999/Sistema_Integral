<?php
require_once '../config/config.php';
header('Content-Type: application/json; charset=utf-8');

$matricula = trim($_GET['m'] ?? '');
if (!$matricula) { echo json_encode(['error' => 'Matrícula no especificada']); exit; }

$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$stmt = $conn->prepare(
    "SELECT a.matricula_alum,
            a.nombres_alum, a.ape_paterno_alum, a.ape_materno_alum,
            a.tipo_sangre, a.fe_nacimiento_alum,
            a.enfermedades, a.nss, a.emergencia
     FROM alumnos a
     WHERE a.matricula_alum = ?"
);
$stmt->bind_param('s', $matricula);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$alumno) { echo json_encode(['error' => 'Alumno no encontrado']); exit; }

echo json_encode($alumno, JSON_UNESCAPED_UNICODE);
