<?php
session_start();
if (!isset($_SESSION['alumno'])) { header('Location: login-alumno.html'); exit; }

$mat = $_SESSION['alumno']['matricula'];
$mat_sanitizada = preg_replace('/[^a-zA-Z0-9_\-]/', '', $mat);
$carpeta = dirname(__DIR__) . '/datos-fisicos/reportes_salud/' . $mat_sanitizada . '/';

if (!is_dir($carpeta)) {
    http_response_code(404);
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">
    <title>Sin reporte</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>body{font-family:Poppins,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f1f5f9;}
    .box{background:#fff;border-radius:16px;padding:2.5rem;text-align:center;max-width:420px;box-shadow:0 4px 20px rgba(0,0,0,.1);}
    h2{color:#003da5;margin-bottom:.5rem;}p{color:#6b7280;font-size:.9rem;}</style></head>
    <body><div class="box"><h2>Sin reporte disponible</h2>
    <p>Aún no se ha generado un reporte de salud para tu matrícula. Acude a la clínica universitaria para que el personal registre tus datos.</p>
    <a href="inicio.php" style="display:inline-block;margin-top:1.25rem;background:#003da5;color:#fff;padding:.55rem 1.5rem;border-radius:9px;text-decoration:none;font-weight:600;">Regresar</a>
    </div></body></html>';
    exit;
}

$archivos = glob($carpeta . '*.pdf');
if (empty($archivos)) {
    http_response_code(404);
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Sin reporte</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>body{font-family:Poppins,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f1f5f9;}
    .box{background:#fff;border-radius:16px;padding:2.5rem;text-align:center;max-width:420px;box-shadow:0 4px 20px rgba(0,0,0,.1);}
    h2{color:#003da5;margin-bottom:.5rem;}p{color:#6b7280;font-size:.9rem;}</style></head>
    <body><div class="box"><h2>No se encontró el reporte</h2>
    <p>El archivo PDF no está disponible en este momento. Contacta al personal de la clínica.</p>
    <a href="inicio.php" style="display:inline-block;margin-top:1.25rem;background:#003da5;color:#fff;padding:.55rem 1.5rem;border-radius:9px;text-decoration:none;font-weight:600;">Regresar</a>
    </div></body></html>';
    exit;
}

// Archivo más reciente primero
usort($archivos, fn($a, $b) => filemtime($b) - filemtime($a));
$pdf = $archivos[0];

$modo = trim($_GET['modo'] ?? 'inline');
$disposition = ($modo === 'descargar') ? 'attachment' : 'inline';

header('Content-Type: application/pdf');
header('Content-Disposition: ' . $disposition . '; filename="reporte_salud_' . $mat_sanitizada . '.pdf"');
header('Content-Length: ' . filesize($pdf));
header('Cache-Control: private, no-cache');
readfile($pdf);
