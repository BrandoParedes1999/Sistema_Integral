<?php
// test_email.php - Script para probar envío de correos
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

echo "<h2>Test de Envío de Correo</h2>";
echo "<pre>";

// Correo de prueba
$destinatario_test = '191263@mail.unacar.mx'; 

try {
    $mail = new PHPMailer(true);
    
    // Debug completo
    $mail->SMTPDebug = 3; // Máximo detalle
    $mail->Debugoutput = 'html';
    
    $mail->isSMTP();
    $mail->Host = 'mail.sistema-integral-de-salud-unacar.com.mx';
    $mail->SMTPAuth = true;
    $mail->Username = 'noreply@sistema-integral-de-salud-unacar.com.mx';
    $mail->Password = 'sklike5522';
    $mail->SMTPSecure = ''; // Sin encriptación
    $mail->Port = 25; // Puerto 25 (el único disponible)
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 60;
    
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->setFrom('noreply@sistema-integral-de-salud-unacar.com.mx', 'Test UNACAR');
    $mail->addAddress($destinatario_test);
    
    $mail->isHTML(true);
    $mail->Subject = 'Correo de Prueba - ' . date('Y-m-d H:i:s');
    $mail->Body = '<h1>Test exitoso</h1><p>Si recibes este correo, la configuración funciona correctamente.</p>';
    $mail->AltBody = 'Test exitoso. Si recibes este correo, la configuración funciona.';

    echo "\n=== Intentando enviar correo ===\n";
    
    if ($mail->send()) {
        echo "\n✅ <strong>CORREO ENVIADO EXITOSAMENTE</strong>\n";
        echo "Destinatario: $destinatario_test\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ <strong>ERROR:</strong> {$mail->ErrorInfo}\n";
    echo "\nExcepción: " . $e->getMessage() . "\n";
}

echo "</pre>";

// Test de conectividad básica
echo "<h3>Test de Conectividad</h3>";
echo "<pre>";

$host = 'mail.sistema-integral-de-salud-unacar.com.mx';
$puertos = [25, 465, 587, 2525];

foreach ($puertos as $puerto) {
    $connection = @fsockopen($host, $puerto, $errno, $errstr, 10);
    if ($connection) {
        echo "✅ Puerto $puerto: ABIERTO\n";
        fclose($connection);
    } else {
        echo "❌ Puerto $puerto: CERRADO ($errstr)\n";
    }
}

echo "</pre>";
?>