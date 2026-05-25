<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: ../login.php'); exit(); }

$nombre = $_SESSION['nombre_admi'] ?? 'Capturista';
$rol    = $_SESSION['rol'] ?? '';

// Administradores van al dashboard completo
if ($rol === 'Administrador') {
    header('Location: menu.php');
    exit();
}
?>
<!doctype html>
<html lang="es">
<head>
  <title>Inicio · UniSalud</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="../alumnos/imagenes/unisalud-sf.png">
  <link rel="stylesheet" href="../css/menu.css">
  <style>
    body { font-family:'Inter',sans-serif; background:#f1f5f9; }
    .page { max-width:780px; margin:0 auto; padding:2.5rem 1.25rem 3rem; }

    .welcome-card {
      background:linear-gradient(135deg,#003da5 0%,#1a6bdd 100%);
      border-radius:20px; padding:2rem 2.25rem; color:#fff;
      margin-bottom:2rem; display:flex; align-items:center; gap:1.5rem;
    }
    .welcome-avatar {
      width:64px; height:64px; border-radius:50%;
      background:rgba(255,255,255,.18); font-size:1.6rem; font-weight:700;
      display:flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .welcome-text h2 { font-size:1.3rem; font-weight:700; margin:0 0 .25rem; }
    .welcome-text p  { font-size:.88rem; opacity:.85; margin:0; }

    .action-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
    @media(max-width:540px){ .action-grid{ grid-template-columns:1fr; } }

    .action-card {
      background:#fff; border-radius:16px; box-shadow:0 4px 14px rgba(0,0,0,.07);
      padding:2rem 1.75rem; text-decoration:none; color:inherit;
      display:flex; flex-direction:column; gap:1rem;
      border:2px solid transparent; transition:border .2s, box-shadow .2s, transform .15s;
    }
    .action-card:hover {
      border-color:#003da5; box-shadow:0 8px 24px rgba(0,61,165,.15);
      transform:translateY(-2px); color:inherit;
    }
    .action-icon {
      width:56px; height:56px; border-radius:14px;
      display:flex; align-items:center; justify-content:center;
      font-size:1.6rem;
    }
    .action-icon.blue  { background:#e8effc; color:#003da5; }
    .action-icon.green { background:#ecfdf5; color:#10b981; }
    .action-title { font-size:1.05rem; font-weight:700; color:#111827; margin:0; }
    .action-desc  { font-size:.82rem; color:#6b7280; margin:0; line-height:1.5; }
    .action-arrow { margin-top:auto; font-size:.82rem; font-weight:600; color:#003da5; display:flex; align-items:center; gap:.3rem; }
  </style>
</head>
<body>
<div id="admin-sidebar-container"></div>

<div class="page">
  <?php
  $partes    = explode(' ', trim($nombre));
  $iniciales = strtoupper(substr($partes[0],0,1).(isset($partes[1])?substr($partes[1],0,1):''));
  ?>
  <div class="welcome-card">
    <div class="welcome-avatar"><?= htmlspecialchars($iniciales ?: '?') ?></div>
    <div class="welcome-text">
      <h2>Bienvenido, <?= htmlspecialchars($nombre) ?></h2>
      <p>Panel de Capturista · UniSalud UNACAR<br>Selecciona el módulo para comenzar la captura.</p>
    </div>
  </div>

  <div class="action-grid">
    <a href="../datos-fisicos/datos_fisicos.html" class="action-card">
      <div class="action-icon blue"><i class="bi bi-activity"></i></div>
      <div>
        <p class="action-title">Datos Físicos</p>
        <p class="action-desc">Registra peso, talla, IMC, presión arterial, glucosa y más indicadores clínicos del estudiante.</p>
      </div>
      <div class="action-arrow"><i class="bi bi-arrow-right-circle-fill"></i> Ir al módulo</div>
    </a>

    <a href="../historial/historial_clinico.html" class="action-card">
      <div class="action-icon green"><i class="bi bi-folder2-open"></i></div>
      <div>
        <p class="action-title">Historial Clínico</p>
        <p class="action-desc">Registra antecedentes patológicos, enfermedades hereditarias y condiciones de salud del estudiante.</p>
      </div>
      <div class="action-arrow"><i class="bi bi-arrow-right-circle-fill"></i> Ir al módulo</div>
    </a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/admin-layout.js"></script>
</body>
</html>
