<?php
session_start();
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] ?? '') !== 'Administrador') {
    header('Location: ../login.php'); exit();
}

require_once '../config/config.php';
$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$msg = ''; $msgType = '';

// ── DELETE ─────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action']) && $_POST['_action'] === 'delete') {
    $delId = (int)($_POST['id'] ?? 0);
    if ($delId && $delId !== (int)$_SESSION['usuario']) {
        $d = $conn->prepare("DELETE FROM administradores WHERE id = ?");
        $d->bind_param('i', $delId);
        $msg     = $d->execute() ? 'Cuenta eliminada.' : 'Error al eliminar.';
        $msgType = $d->execute() ? 'ok' : 'err';
        $d->close();
        $msg = 'Cuenta eliminada correctamente.'; $msgType = 'ok';
    } else {
        $msg = 'No puedes eliminar tu propia cuenta.'; $msgType = 'err';
    }
}

// ── CREATE ──────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action']) && $_POST['_action'] === 'create') {
    $usuario_n     = trim($_POST['usuario']       ?? '');
    $pw_raw        = $_POST['contrasena']          ?? '';
    $nombre_admi   = trim($_POST['nombre_admi']    ?? '');
    $apellidos_admi= trim($_POST['apellidos_admi'] ?? '');
    $roles_validos = ['Administrador', 'Capturista'];
    $rol_n         = in_array($_POST['rol'] ?? '', $roles_validos) ? $_POST['rol'] : 'Capturista';

    if (!preg_match('/^\d{4}$/', $usuario_n)) {
        $msg = 'El usuario debe ser un número de exactamente 4 dígitos.'; $msgType = 'err';
    } elseif (strlen($pw_raw) < 8) {
        $msg = 'La contraseña debe tener al menos 8 caracteres.'; $msgType = 'err';
    } elseif (!$nombre_admi || !$apellidos_admi) {
        $msg = 'El nombre y apellidos son obligatorios.'; $msgType = 'err';
    } else {
        $contrasena = password_hash($pw_raw, PASSWORD_BCRYPT);
        $chk = $conn->prepare("SELECT id FROM administradores WHERE usuario = ?");
        $chk->bind_param('s', $usuario_n);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $msg = 'Ese número de usuario ya existe.'; $msgType = 'err';
        } else {
            $ins = $conn->prepare("INSERT INTO administradores (usuario, contraseña, nombre_admi, apellidos_admi, rol) VALUES (?,?,?,?,?)");
            $ins->bind_param('sssss', $usuario_n, $contrasena, $nombre_admi, $apellidos_admi, $rol_n);
            $msg     = $ins->execute() ? 'Cuenta creada correctamente.' : 'Error al crear la cuenta: '.$conn->error;
            $msgType = $ins->execute() ? 'ok' : 'err';
            if ($ins->affected_rows > 0 || $ins->insert_id > 0) { $msg = 'Cuenta creada correctamente.'; $msgType = 'ok'; }
            $ins->close();
        }
        $chk->close();
    }
}

// ── LIST ────────────────────────────────────────────────────────────────────
$cuentas = [];
$res = $conn->query("SELECT id, usuario, nombre_admi, apellidos_admi, rol, intentos_fallidos FROM administradores ORDER BY rol ASC, nombre_admi ASC");
while ($r = $res->fetch_assoc()) { $cuentas[] = $r; }
$conn->close();
?>
<!doctype html>
<html lang="es">
<head>
  <title>Cuentas · UniSalud</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="../alumnos/imagenes/unisalud-sf.png">
  <link rel="stylesheet" href="../css/menu.css">
  <style>
    body { font-family:'Inter',sans-serif; background:#f1f5f9; }
    .page { max-width:960px; margin:0 auto; padding:1.75rem 1.25rem 3rem; }
    .card-box { background:#fff; border-radius:14px; box-shadow:0 4px 14px rgba(0,0,0,.07); padding:1.5rem; margin-bottom:1.5rem; }
    .page-title { font-size:1.2rem; font-weight:700; color:#003da5; margin-bottom:.15rem; }
    .page-sub   { font-size:.82rem; color:#6b7280; }
    .tbl { width:100%; border-collapse:collapse; font-size:.85rem; }
    .tbl th { background:#f8fafc; font-weight:600; color:#374151; padding:.65rem .9rem; border-bottom:2px solid #e2e8f0; }
    .tbl td { padding:.6rem .9rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .tbl tr:hover td { background:#f8fafc; }
    .rol-badge { display:inline-block; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:99px; }
    .rol-admin { background:#e8effc; color:#003da5; }
    .rol-cap   { background:#ecfdf5; color:#065f46; }
    .av { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#003da5,#1a6bdd); color:#fff; font-size:.85rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; }
    .btn-del { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:7px; padding:.3rem .75rem; font-size:.78rem; font-weight:600; cursor:pointer; }
    .btn-del:hover { background:#fee2e2; }
    .form-label-sm { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.3rem; display:block; }
    .form-ctrl { width:100%; border:1.5px solid #e2e8f0; border-radius:8px; padding:.5rem .8rem; font-size:.88rem; font-family:inherit; }
    .form-ctrl:focus { outline:none; border-color:#003da5; }
    .btn-save { background:#003da5; color:#fff; border:none; border-radius:8px; padding:.6rem 1.4rem; font-size:.88rem; font-weight:600; cursor:pointer; }
    .btn-save:hover { background:#002a70; }
    .msg-ok  { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; border-radius:8px; padding:.7rem 1rem; margin-bottom:1rem; font-size:.85rem; }
    .msg-err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:8px; padding:.7rem 1rem; margin-bottom:1rem; font-size:.85rem; }
    .sec-hd { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; margin-bottom:.85rem; }
    .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    @media(max-width:520px){ .grid-2{grid-template-columns:1fr;} }
  </style>
</head>
<body>
<div id="admin-sidebar-container"></div>
<div class="page">

  <?php if ($msg): ?>
    <div class="<?= $msgType === 'ok' ? 'msg-ok' : 'msg-err' ?>"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <!-- Listado de cuentas -->
  <div class="card-box">
    <div class="page-title"><i class="bi bi-people-fill"></i> Cuentas del Sistema</div>
    <div class="page-sub" style="margin-bottom:1.25rem;">Administra los usuarios con acceso al panel de administración</div>

    <table class="tbl">
      <thead>
        <tr><th></th><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Intentos fallidos</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($cuentas as $c):
          $ini = strtoupper(substr($c['nombre_admi'],0,1).substr($c['apellidos_admi'],0,1));
          $esMiCuenta = ($c['usuario'] == $_SESSION['usuario']);
        ?>
        <tr>
          <td><div class="av"><?= htmlspecialchars($ini) ?></div></td>
          <td><strong><?= htmlspecialchars($c['usuario']) ?></strong></td>
          <td><?= htmlspecialchars($c['nombre_admi'].' '.$c['apellidos_admi']) ?></td>
          <td>
            <span class="rol-badge <?= $c['rol']==='Administrador'?'rol-admin':'rol-cap' ?>">
              <?= htmlspecialchars($c['rol']) ?>
            </span>
          </td>
          <td><?= (int)$c['intentos_fallidos'] ?></td>
          <td>
            <?php if (!$esMiCuenta): ?>
            <form method="POST" onsubmit="return confirm('¿Eliminar la cuenta de <?= htmlspecialchars($c['nombre_admi']) ?>?')">
              <input type="hidden" name="_action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button type="submit" class="btn-del"><i class="bi bi-trash"></i> Eliminar</button>
            </form>
            <?php else: ?>
            <span style="font-size:.75rem;color:#9ca3af;">Tu cuenta</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Crear cuenta -->
  <div class="card-box">
    <div class="page-title"><i class="bi bi-person-plus-fill"></i> Crear Nueva Cuenta</div>
    <div class="page-sub" style="margin-bottom:1.5rem;">Los capturistas solo pueden acceder a Datos Físicos e Historial Clínico</div>

    <form method="POST">
      <input type="hidden" name="_action" value="create">
      <div class="grid-2" style="margin-bottom:1rem;">
        <div>
          <label class="form-label-sm">Usuario (4 dígitos numéricos)</label>
          <input type="text" name="usuario" class="form-ctrl" maxlength="4" pattern="\d{4}" placeholder="Ej. 1234" required>
        </div>
        <div>
          <label class="form-label-sm">Contraseña (mín. 8 caracteres)</label>
          <input type="password" name="contrasena" class="form-ctrl" minlength="8" required>
        </div>
        <div>
          <label class="form-label-sm">Nombre</label>
          <input type="text" name="nombre_admi" class="form-ctrl" maxlength="100" required>
        </div>
        <div>
          <label class="form-label-sm">Apellidos</label>
          <input type="text" name="apellidos_admi" class="form-ctrl" maxlength="80" required>
        </div>
      </div>
      <div style="margin-bottom:1.25rem;">
        <label class="form-label-sm">Rol</label>
        <div style="display:flex;gap:1.5rem;margin-top:.4rem;">
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.88rem;">
            <input type="radio" name="rol" value="Capturista" checked> Capturista
            <span style="font-size:.75rem;color:#6b7280;">(solo Datos Físicos e Historial)</span>
          </label>
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.88rem;">
            <input type="radio" name="rol" value="Administrador"> Administrador
            <span style="font-size:.75rem;color:#6b7280;">(acceso completo)</span>
          </label>
        </div>
      </div>
      <button type="submit" class="btn-save"><i class="bi bi-person-plus"></i> Crear cuenta</button>
    </form>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/admin-layout.js"></script>
</body>
</html>
