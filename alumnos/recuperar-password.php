<?php
session_start();
require_once '../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
require '../vendor/PHPMailer/src/Exception.php';
require '../vendor/PHPMailer/src/PHPMailer.php';
require '../vendor/PHPMailer/src/SMTP.php';

$msg     = '';
$msgType = '';
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = strtoupper(trim($_POST['matricula'] ?? ''));
    $correo    = strtolower(trim($_POST['correo']    ?? ''));

    if (!$matricula || !$correo) {
        $msg     = 'Ingresa tu matrícula y correo institucional.';
        $msgType = 'error';
    } else {
        $conn = getDBConnection();
        $conn->set_charset('utf8mb4');

        $conn->query("CREATE TABLE IF NOT EXISTS password_resets (
            id        INT AUTO_INCREMENT PRIMARY KEY,
            matricula VARCHAR(20) NOT NULL,
            token     VARCHAR(64) NOT NULL,
            expira    DATETIME    NOT NULL,
            INDEX idx_token (token),
            INDEX idx_mat   (matricula)
        )");

        $stmt = $conn->prepare("SELECT matricula_alum, correo_alum, nombres_alum, ape_paterno_alum FROM alumnos WHERE matricula_alum = ?");
        $stmt->bind_param('s', $matricula);
        $stmt->execute();
        $alumno = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($alumno && strtolower($alumno['correo_alum']) === $correo) {
            $del = $conn->prepare("DELETE FROM password_resets WHERE matricula = ?");
            $del->bind_param('s', $matricula);
            $del->execute();
            $del->close();

            $token  = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $ins = $conn->prepare("INSERT INTO password_resets (matricula, token, expira) VALUES (?, ?, ?)");
            $ins->bind_param('sss', $matricula, $token, $expira);
            $ins->execute();
            $ins->close();

            $nombre   = $alumno['nombres_alum'] . ' ' . $alumno['ape_paterno_alum'];
            $proto    = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $resetUrl = $proto . '://' . $_SERVER['HTTP_HOST']
                      . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')
                      . '/restablecer-password.php?token=' . $token;

            try {
                $mail = new PHPMailer(true);
                $mail->SMTPDebug   = 0;
                $mail->Debugoutput = function ($s, $l) { error_log("PHPMailer[$l]: $s"); };
                $mail->isSMTP();
                $mail->Host        = 'mail.sistema-integral-de-salud-unacar.com.mx';
                $mail->SMTPAuth    = true;
                $mail->Username    = 'noreply@sistema-integral-de-salud-unacar.com.mx';
                $mail->Password    = 'sklike5522';
                $mail->Port        = 25;
                $mail->SMTPSecure  = '';
                $mail->SMTPAutoTLS = false;
                $mail->CharSet     = 'UTF-8';
                $mail->Encoding    = 'base64';
                $mail->Timeout     = 60;
                $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
                $mail->setFrom('noreply@sistema-integral-de-salud-unacar.com.mx', 'UniSalud UNACAR');
                $mail->addAddress($correo, $nombre);
                $mail->isHTML(true);
                $mail->Subject = 'Recuperación de contraseña — UniSalud UNACAR';
                $mail->Body    = "
                <div style='font-family:Inter,sans-serif;max-width:520px;margin:0 auto;'>
                  <div style='background:#003da5;padding:1.5rem;border-radius:12px 12px 0 0;text-align:center;'>
                    <h2 style='color:#fff;margin:0;font-size:1.2rem;'>Recuperación de Contraseña</h2>
                    <p style='color:#93c5fd;margin:.25rem 0 0;font-size:.85rem;'>UniSalud · Sistema Integral de Salud UNACAR</p>
                  </div>
                  <div style='background:#fff;padding:1.75rem;border:1px solid #e2e8f0;border-radius:0 0 12px 12px;'>
                    <p>Hola <strong>" . htmlspecialchars($nombre) . "</strong>,</p>
                    <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta (<strong>" . htmlspecialchars($matricula) . "</strong>).</p>
                    <p>Haz clic en el botón para crear una nueva contraseña. Este enlace es válido por <strong>30 minutos</strong>.</p>
                    <div style='text-align:center;margin:1.5rem 0;'>
                      <a href='" . htmlspecialchars($resetUrl) . "' style='background:#003da5;color:#fff;padding:.75rem 2rem;border-radius:8px;text-decoration:none;font-weight:600;font-size:.95rem;display:inline-block;'>
                        Restablecer contraseña
                      </a>
                    </div>
                    <p style='font-size:.8rem;color:#6b7280;'>Si no solicitaste este cambio, ignora este correo. Tu contraseña no será modificada.</p>
                  </div>
                </div>";
                $mail->AltBody = "Hola {$nombre},\n\nRestablece tu contraseña (válido 30 min):\n{$resetUrl}\n\nSi no lo solicitaste, ignora este mensaje.";
                $mail->send();
            } catch (MailException $e) {
                error_log("Error enviando email recuperación: " . $e->getMessage());
            }
        }

        $conn->close();
        $enviado = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar Contraseña · UniSalud</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="imagenes/unisalud-sf.png">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Inter',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f1f5f9;padding:1rem;}
    .card{max-width:420px;width:100%;background:#fff;border-radius:16px;padding:2rem;box-shadow:0 8px 30px rgba(0,0,0,.1);}
    .logo-wrap{text-align:center;margin-bottom:1.5rem;}
    .logo-wrap img{height:52px;margin-bottom:.75rem;}
    h1{font-size:1.2rem;font-weight:700;color:#111827;margin:0;}
    .sub{font-size:.82rem;color:#6b7280;margin:.35rem 0 0;}
    .msg-error  {background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.875rem;}
    .msg-ok     {background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.875rem;}
    .form-group{margin-bottom:1.25rem;}
    label{display:block;font-weight:500;font-size:.875rem;margin-bottom:.4rem;color:#374151;}
    input{width:100%;border:1.5px solid #d1d5db;border-radius:8px;padding:.65rem .9rem;font-size:.9rem;font-family:inherit;transition:border .2s;}
    input:focus{outline:none;border-color:#003da5;}
    .btn{width:100%;background:#003da5;color:#fff;border:none;border-radius:8px;padding:.75rem;font-size:.95rem;font-weight:600;cursor:pointer;font-family:inherit;text-align:center;display:block;text-decoration:none;}
    .btn:hover{background:#002a70;}
    .back{display:block;text-align:center;margin-top:1rem;font-size:.85rem;color:#6b7280;text-decoration:none;}
    .back:hover{color:#003da5;}
  </style>
</head>
<body>
<div class="card">
  <div class="logo-wrap">
    <img src="imagenes/unisalud-sf.png" alt="UniSalud">
    <h1>Recuperar contraseña</h1>
    <p class="sub">Ingresa tu matrícula y correo para recibir el enlace de restablecimiento</p>
  </div>

  <?php if ($enviado): ?>
    <div class="msg-ok">
      <strong>Enlace enviado.</strong> Si tu matrícula y correo coinciden con los registrados, recibirás un mensaje en los próximos minutos.<br>
      <small style="color:#047857;">Revisa también la carpeta de spam.</small>
    </div>
    <a href="login-alumno.html" class="btn">Volver al inicio de sesión</a>

  <?php else: ?>
    <?php if ($msg): ?>
      <div class="<?= $msgType === 'error' ? 'msg-error' : 'msg-ok' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="POST" novalidate>
      <div class="form-group">
        <label for="matricula">Matrícula</label>
        <input type="text" id="matricula" name="matricula" placeholder="Ej. 180711" required autocomplete="username">
      </div>
      <div class="form-group">
        <label for="correo">Correo institucional</label>
        <input type="email" id="correo" name="correo" placeholder="alumno@unacar.mx" required>
      </div>
      <button type="submit" class="btn">Enviar enlace de recuperación</button>
    </form>
  <?php endif; ?>

  <a href="login-alumno.html" class="back">← Volver al inicio de sesión</a>
</div>
</body>
</html>
