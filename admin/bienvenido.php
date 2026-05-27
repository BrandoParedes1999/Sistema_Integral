<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: ../login.php'); exit(); }
$destino = ($_SESSION['rol'] === 'Administrador') ? 'menu.php' : 'capturista.php';
?>
<!doctype html>
<html lang="es">
<head>
  <title>Cargando · UniSalud</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="../alumnos/imagenes/unisalud-sf.png">
  <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Inter',sans-serif;height:100vh;display:flex;align-items:center;justify-content:center;background:#f1f5f9;}
    .wrap{text-align:center;}
    .logo{width:90px;animation:pulse 1.4s ease-in-out infinite;}
    @keyframes pulse{0%,100%{transform:scale(1);opacity:1;}50%{transform:scale(1.08);opacity:.8;}}
    p{margin-top:1rem;font-size:.9rem;color:#6b7280;font-family:Inter,sans-serif;}
  </style>
</head>
<body>
<div class="wrap">
  <img src="../alumnos/imagenes/unisalud-sf.png" alt="UniSalud" class="logo">
  <p>Iniciando sesión…</p>
</div>
<script>
  setTimeout(function(){ window.location.href = "<?= htmlspecialchars($destino) ?>"; }, 1200);
</script>
</body>
</html>
