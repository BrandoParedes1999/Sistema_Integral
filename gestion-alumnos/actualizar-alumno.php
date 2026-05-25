<?php
session_start();
if (!isset($_SESSION['usuario'])) { http_response_code(401); echo json_encode(['error' => 'No autorizado']); exit(); }
require_once '../config/config.php';
header('Content-Type: application/json');

$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$mat        = trim($_POST['matricula']    ?? '');
$nombres    = trim($_POST['nombres']      ?? '');
$apepa      = trim($_POST['ape_paterno']  ?? '');
$apema      = trim($_POST['ape_materno']  ?? '');
$correo     = trim($_POST['correo']       ?? '');
$sexo       = trim($_POST['sexo']         ?? '');
$generacion = trim($_POST['generacion']   ?? '');
$nss        = trim($_POST['nss']          ?? '');
$emergencia = trim($_POST['emergencia']   ?? '');
$tipo_sangre= trim($_POST['tipo_sangre']  ?? '');

if (!$mat)    { echo json_encode(['error' => 'Matrícula requerida']); exit(); }
if (!$nombres || !$apepa || !$apema) { echo json_encode(['error' => 'Nombre y apellidos son requeridos']); exit(); }
if ($correo && !filter_var($correo, FILTER_VALIDATE_EMAIL)) { echo json_encode(['error' => 'Correo electrónico inválido']); exit(); }
if ($emergencia && !preg_match('/^\d{10}$/', $emergencia)) { echo json_encode(['error' => 'Teléfono de emergencia debe tener 10 dígitos']); exit(); }
if ($nss && !preg_match('/^\d{11}$/', $nss)) { echo json_encode(['error' => 'NSS debe tener 11 dígitos']); exit(); }
if ($sexo && !in_array($sexo, ['MASCULINO', 'FEMENINO'])) { echo json_encode(['error' => 'Sexo inválido']); exit(); }

$stmt = $conn->prepare(
    "UPDATE alumnos
     SET nombres_alum=?, ape_paterno_alum=?, ape_materno_alum=?,
         correo_alum=?, sexo=?, generacion=?, nss=?, emergencia=?, tipo_sangre=?
     WHERE matricula_alum=?"
);
$stmt->bind_param('ssssssssss',
    $nombres, $apepa, $apema, $correo, $sexo, $generacion, $nss, $emergencia, $tipo_sangre, $mat
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);
} else {
    echo json_encode(['error' => 'Error al actualizar: ' . $conn->error]);
}
$stmt->close();
$conn->close();
?>
