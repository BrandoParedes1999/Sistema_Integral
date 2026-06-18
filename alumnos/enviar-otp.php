<?php
session_start();
require_once '../config/config.php';
require_once '../config/mail-config.php';

if (MAIL_MODE === 'prod') {
    // Intentar ruta manual primero, luego Composer
    if (file_exists('../vendor/PHPMailer/src/PHPMailer.php')) {
        require_once '../vendor/PHPMailer/src/Exception.php';
        require_once '../vendor/PHPMailer/src/PHPMailer.php';
        require_once '../vendor/PHPMailer/src/SMTP.php';
    } elseif (file_exists('../vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
        require_once '../vendor/phpmailer/phpmailer/src/Exception.php';
        require_once '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once '../vendor/phpmailer/phpmailer/src/SMTP.php';
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'PHPMailer no encontrado en el servidor']);
        exit;
    }
}

header('Content-Type: application/json; charset=utf-8');

// ── Rate limiting: 3 envíos por IP cada 10 minutos ───────────────────────
$ip     = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ahora  = time();
$ventana = 600;
$maxEnvios = 3;

if (!isset($_SESSION['otp_rl'])) $_SESSION['otp_rl'] = [];
foreach ($_SESSION['otp_rl'] as $k => $d) {
    if ($ahora - $d['primero'] >= $ventana) unset($_SESSION['otp_rl'][$k]);
}
if (!isset($_SESSION['otp_rl'][$ip])) {
    $_SESSION['otp_rl'][$ip] = ['count' => 0, 'primero' => $ahora];
}
if ($_SESSION['otp_rl'][$ip]['count'] >= $maxEnvios) {
    $espera = $ventana - ($ahora - $_SESSION['otp_rl'][$ip]['primero']);
    http_response_code(429);
    echo json_encode(['error' => 'Demasiados intentos. Espera ' . ceil($espera/60) . ' min.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$matricula = strtoupper(trim($_POST['matricula'] ?? ''));
if (!$matricula) {
    echo json_encode(['error' => 'La matrícula es obligatoria']);
    exit;
}

// ── Buscar alumno ─────────────────────────────────────────────────────────
$conn = getDBConnection();
$conn->set_charset('utf8mb4');
$stmt = $conn->prepare("SELECT matricula_alum, nombres_alum, correo_alum FROM alumnos WHERE matricula_alum = ?");
$stmt->bind_param('s', $matricula);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$alumno) {
    echo json_encode(['error' => 'Matrícula no encontrada']);
    exit;
}
if (empty($alumno['correo_alum'])) {
    echo json_encode(['error' => 'No tienes correo institucional registrado. Contacta al administrador.']);
    exit;
}

// ── Generar OTP ───────────────────────────────────────────────────────────
$codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$_SESSION['otp'] = [
    'hash'      => password_hash($codigo, PASSWORD_DEFAULT),
    'matricula' => $matricula,
    'expires'   => $ahora + 600,  // 10 minutos
    'intentos'  => 0,
];
$_SESSION['otp_rl'][$ip]['count']++;

// Ocultar correo parcialmente: abc***@unacar.edu.mx
$correo = $alumno['correo_alum'];
$partes  = explode('@', $correo);
$visible = substr($partes[0], 0, 3) . str_repeat('*', max(0, strlen($partes[0]) - 3));
$correoMasked = $visible . '@' . ($partes[1] ?? '');

// ── Enviar o mostrar según modo ───────────────────────────────────────────
if (MAIL_MODE === 'dev') {
    // En desarrollo: devolver el código directamente
    echo json_encode([
        'success'      => true,
        'dev_codigo'   => $codigo,   // solo visible en modo dev
        'correo'       => $correoMasked,
        'mensaje'      => '[MODO DEV] Código generado (no se envió email)',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Modo producción: enviar email con PHPMailer ────────────────────────────
try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->SMTPOptions = [
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
    ];

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($correo, $alumno['nombres_alum']);
    $mail->isHTML(true);
    $mail->Subject = 'Tu código de acceso – UniSalud UNACAR';
    $mail->Body = "
    <div style='font-family:Arial,sans-serif;max-width:480px;margin:auto;border:1px solid #e0e0e0;border-radius:10px;overflow:hidden'>
      <div style='background:#002855;padding:20px;text-align:center'>
        <h2 style='color:#c4a006;margin:0'>UniSalud UNACAR</h2>
      </div>
      <div style='padding:30px'>
        <p>Hola, <strong>{$alumno['nombres_alum']}</strong></p>
        <p>Tu código de acceso al portal es:</p>
        <div style='background:#f4f6f9;border-radius:8px;padding:20px;text-align:center;margin:20px 0'>
          <span style='font-size:36px;font-weight:bold;letter-spacing:10px;color:#002855'>{$codigo}</span>
        </div>
        <p style='color:#888;font-size:13px'>Este código expira en <strong>10 minutos</strong>. No lo compartas con nadie.</p>
      </div>
    </div>";

    $mail->send();

    echo json_encode([
        'success' => true,
        'correo'  => $correoMasked,
        'mensaje' => 'Código enviado a tu correo institucional',
    ], JSON_UNESCAPED_UNICODE);

} catch (\Exception $e) {
    $detalle = isset($mail) ? $mail->ErrorInfo : $e->getMessage();
    error_log('OTP mail error: ' . $detalle);
    echo json_encode(['error' => 'Error SMTP: ' . $detalle]);
}
