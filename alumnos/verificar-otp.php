<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Método no permitido']); exit;
}

$matricula = strtoupper(trim($_POST['matricula'] ?? ''));
$codigo    = trim($_POST['codigo'] ?? '');

if (!$matricula || !$codigo) {
    echo json_encode(['error' => 'Datos incompletos']); exit;
}

// ── Validar OTP en sesión ─────────────────────────────────────────────────
$otp = $_SESSION['otp'] ?? null;

if (!$otp) {
    echo json_encode(['error' => 'No hay código activo. Solicita uno nuevo.']); exit;
}
if ($otp['matricula'] !== $matricula) {
    echo json_encode(['error' => 'Matrícula no coincide con el código enviado.']); exit;
}
if (time() > $otp['expires']) {
    unset($_SESSION['otp']);
    echo json_encode(['error' => 'El código expiró. Solicita uno nuevo.']); exit;
}

// Máximo 5 intentos por código
if ($otp['intentos'] >= 5) {
    unset($_SESSION['otp']);
    echo json_encode(['error' => 'Demasiados intentos fallidos. Solicita un nuevo código.']); exit;
}

if (!password_verify($codigo, $otp['hash'])) {
    $_SESSION['otp']['intentos']++;
    $restantes = 5 - $_SESSION['otp']['intentos'];
    echo json_encode(['error' => "Código incorrecto. $restantes intentos restantes."]); exit;
}

// ── Código correcto: crear sesión del alumno ──────────────────────────────
unset($_SESSION['otp']);

$conn = getDBConnection();
$conn->set_charset('utf8mb4');
$stmt = $conn->prepare(
    "SELECT a.*, c.nombre_carrera, f.nombre_facultad
     FROM alumnos a
     LEFT JOIN carrera  c ON a.id_carrera  = c.id_carrera
     LEFT JOIN facultad f ON a.id_facultad = f.id_facultad
     WHERE a.matricula_alum = ?"
);
$stmt->bind_param('s', $matricula);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$alumno) {
    echo json_encode(['error' => 'Alumno no encontrado']); exit;
}

session_regenerate_id(true);
$_SESSION['alumno'] = [
    'matricula'    => $alumno['matricula_alum'],
    'nombre'       => $alumno['nombres_alum'],
    'apepa'        => $alumno['ape_paterno_alum'],
    'apema'        => $alumno['ape_materno_alum'],
    'edad'         => $alumno['edad_alum'],
    'sexo'         => $alumno['sexo'],
    'correo'       => $alumno['correo_alum'],
    'fecha'        => $alumno['fe_nacimiento_alum'],
    'id_carrera'   => $alumno['id_carrera'],
    'id_facultad'  => $alumno['id_facultad'],
    'generacion'   => $alumno['generacion'],
    'nom_carrera'  => $alumno['nombre_carrera'],
    'nom_facultad' => $alumno['nombre_facultad'],
];
$_SESSION['loggedin']      = true;
$_SESSION['tipo_usuario']  = 'alumno';

echo json_encode([
    'success'        => true,
    'nombre_completo'=> $alumno['nombres_alum'] . ' ' . $alumno['ape_paterno_alum'],
    'redirect'       => 'inicio.php',
], JSON_UNESCAPED_UNICODE);
