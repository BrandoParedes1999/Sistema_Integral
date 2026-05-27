<?php
session_start();
if (!isset($_SESSION['usuario'])) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit(); }
require_once '../config/config.php';
header('Content-Type: application/json; charset=utf-8');

$matricula = trim($_GET['matricula_alum'] ?? '');
if (!$matricula) { echo json_encode(['error' => 'Matrícula requerida']); exit(); }

$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$stmt = $conn->prepare(
    "SELECT fecha, peso, talla, imc, glucosa, colesterol, trigliceridos,
            porcentaje_masa_grasa, sistolica, diastolica
     FROM datos_fisicos_alumnos
     WHERE matricula_alum = ?
     ORDER BY fecha ASC
     LIMIT 24"
);
$stmt->bind_param('s', $matricula);
$stmt->execute();
$result = $stmt->get_result();

$datos = [];
while ($r = $result->fetch_assoc()) {
    $datos[] = [
        'fecha'       => $r['fecha'],
        'peso'        => $r['peso'] !== null ? (float)$r['peso'] : null,
        'imc'         => $r['imc']  !== null ? (float)$r['imc']  : null,
        'glucosa'     => $r['glucosa']     !== null ? (float)$r['glucosa']     : null,
        'colesterol'  => $r['colesterol']  !== null ? (float)$r['colesterol']  : null,
        'trigliceridos'=> $r['trigliceridos'] !== null ? (float)$r['trigliceridos'] : null,
        'masa_grasa'  => $r['porcentaje_masa_grasa'] !== null ? (float)$r['porcentaje_masa_grasa'] : null,
        'sistolica'   => $r['sistolica']  !== null ? (float)$r['sistolica']  : null,
        'diastolica'  => $r['diastolica'] !== null ? (float)$r['diastolica'] : null,
    ];
}
$stmt->close();
$conn->close();
echo json_encode($datos, JSON_UNESCAPED_UNICODE);
