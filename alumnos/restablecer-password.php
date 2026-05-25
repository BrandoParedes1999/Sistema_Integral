<?php
session_start();
require_once '../config/config.php';

$token    = trim($_GET['token'] ?? '');
$msg      = '';
$msgType  = '';
$valido   = false;
$matricula= '';
$exito    = false;

if (!$token) {
    $msg = 'Enlace inválido o expirado.';
    $msgType = 'error';
} else {
    $conn = getDBConnection();
    $conn->set_charset('utf8mb4');

    $conn->query("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        matricula VARCHAR(20) NOT NULL,
        token VARCHAR(64) NOT NULL,
        expira DATETIME NOT NULL,
        INDEX idx_token (token),
        INDEX idx_mat   (matricula)
    )");

    $stmt = $conn->prepare("SELECT matricula, expira FROM password_resets WHERE token = ? LIMIT 1");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$fila || strtotime($fila['expira']) < time()) {
        $msg     = 'El enlace ha expirado o ya fue utilizado. Solicita uno nuevo.';
        $msgType = 'error';
    } else {
        $valido    = true;
        $matricula = $fila['matricula'];
    }

    if ($valido && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nueva    = $_POST['password']    ?? '';
        $confirma = $_POST['confirmar']   ?? '';

        if (strlen($nueva) < 8) {
            $msg     = 'La contraseña debe tener al menos 8 caracteres.';
            $msgType = 'error';
            $valido  = false;
        } elseif ($nueva !== $confirma) {
            $msg     = 'Las contraseñas no coinciden.';
            $msgType = 'error';
            $valido  = false;
        } else {
            $hash = password_hash($nueva, PASSWORD_BCRYPT);
            $upd  = $conn->prepare("UPDATE alumnos SET password = ? WHERE matricula_alum = ?");
            $upd->bind_param('ss', $hash, $matricula);
            $upd->execute();
            $upd->close();

            // Invalidate token
            $del = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $del->bind_param('s', $token);
            $del->execute();
            $del->close();

            $exito = true;
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nueva Contraseña · UniSalud</title>
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
    .msg-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.875rem;}
    .msg-ok   {background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.875rem;}
    .form-group{margin-bottom:1.25rem;}
    label{display:block;font-weight:500;font-size:.875rem;margin-bottom:.4rem;color:#374151;}
    .input-wrap{position:relative;}
    input[type=password]{width:100%;border:1.5px solid #d1d5db;border-radius:8px;padding:.65rem 2.5rem .65rem .9rem;font-size:.9rem;font-family:inherit;transition:border .2s;}
    input[type=password]:focus{outline:none;border-color:#003da5;}
    .toggle-pw{position:absolute;right:.7rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1rem;color:#9ca3af;}
    .hint{font-size:.75rem;color:#9ca3af;margin-top:.3rem;}
    .strength{height:4px;border-radius:2px;margin-top:.4rem;transition:background .3s,width .3s;background:#e2e8f0;}
    .btn{width:100%;background:#003da5;color:#fff;border:none;border-radius:8px;padding:.75rem;font-size:.95rem;font-weight:600;cursor:pointer;font-family:inherit;text-align:center;display:block;text-decoration:none;}
    .btn:hover{background:#002a70;}
    .btn:disabled{background:#9ca3af;cursor:not-allowed;}
    .back{display:block;text-align:center;margin-top:1rem;font-size:.85rem;color:#6b7280;text-decoration:none;}
    .back:hover{color:#003da5;}
  </style>
</head>
<body>
<div class="card">
  <div class="logo-wrap">
    <img src="imagenes/unisalud-sf.png" alt="UniSalud">
    <h1>Nueva contraseña</h1>
    <p class="sub">Elige una contraseña segura para tu cuenta</p>
  </div>

  <?php if ($exito): ?>
    <div class="msg-ok">
      <strong>¡Contraseña actualizada!</strong> Ya puedes iniciar sesión con tu nueva contraseña.
    </div>
    <a href="login-alumno.html" class="btn">Iniciar sesión</a>

  <?php elseif (!$valido && $msg): ?>
    <div class="msg-error"><?= htmlspecialchars($msg) ?></div>
    <a href="recuperar-password.php" class="btn">Solicitar nuevo enlace</a>

  <?php else: ?>
    <?php if ($msg): ?>
      <div class="<?= $msgType === 'error' ? 'msg-error' : 'msg-ok' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="POST" novalidate id="resetForm">
      <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($token) ?>">
      <div class="form-group">
        <label for="password">Nueva contraseña</label>
        <div class="input-wrap">
          <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required autocomplete="new-password" oninput="checkStrength(this.value)">
          <button type="button" class="toggle-pw" onclick="togglePw('password',this)">👁</button>
        </div>
        <div class="strength" id="strengthBar"></div>
        <div class="hint" id="strengthText">Escribe tu nueva contraseña</div>
      </div>
      <div class="form-group">
        <label for="confirmar">Confirmar contraseña</label>
        <div class="input-wrap">
          <input type="password" id="confirmar" name="confirmar" placeholder="Repite la contraseña" required autocomplete="new-password">
          <button type="button" class="toggle-pw" onclick="togglePw('confirmar',this)">👁</button>
        </div>
      </div>
      <button type="submit" class="btn" id="btnSubmit">Guardar contraseña</button>
    </form>
  <?php endif; ?>

  <a href="login-alumno.html" class="back">← Volver al inicio de sesión</a>
</div>
<script>
function togglePw(id, btn) {
  const inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? '👁' : '🙈';
}
function checkStrength(val) {
  const bar  = document.getElementById('strengthBar');
  const txt  = document.getElementById('strengthText');
  let score  = 0;
  if (val.length >= 8)  score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const colors = ['#e2e8f0','#ef4444','#f59e0b','#3b82f6','#10b981'];
  const labels = ['','Muy débil','Débil','Aceptable','Fuerte'];
  bar.style.background = colors[score];
  bar.style.width = (score * 25) + '%';
  txt.textContent = val.length === 0 ? 'Escribe tu nueva contraseña' : labels[score] || 'Fuerte';
}
</script>
</body>
</html>
