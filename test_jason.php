<?php
// Archivo de prueba para verificar que PHP devuelve JSON correcto
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

// Verificar rutas de archivos
$checks = [
    'PHPMailer existe' => file_exists('PHPMailer/src/PHPMailer.php'),
    'FPDF existe' => file_exists('fpdf/fpdf.php'),
    'Carpeta temporal' => sys_get_temp_dir(),
    'Carpeta temporal escribible' => is_writable(sys_get_temp_dir()),
    'PHP version' => phpversion(),
];

ob_end_clean();
header('Content-Type: application/json; charset=UTF-8');

echo json_encode([
    'status' => 'ok',
    'checks' => $checks
], JSON_PRETTY_PRINT);
?>