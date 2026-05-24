<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

echo "<h2>Prueba de Configuración SMTP</h2>";

// Configuraciones a probar
$configs = [
    [
        'name' => 'Configuración 1: mail.dominio con STARTTLS',
        'host' => 'mail.sistema-integral-de-salud-unacar.com.mx',
        'port' => 587,
        'secure' => PHPMailer::ENCRYPTION_STARTTLS
    ],
    [
        'name' => 'Configuración 2: mail.dominio con SSL',
        'host' => 'mail.sistema-integral-de-salud-unacar.com.mx',
        'port' => 465,
        'secure' => PHPMailer::ENCRYPTION_SMTPS
    ],
    [
        'name' => 'Configuración 3: mboxhosting.com con STARTTLS',
        'host' => 'mboxhosting.com',
        'port' => 587,
        'secure' => PHPMailer::ENCRYPTION_STARTTLS
    ],
    [
        'name' => 'Configuración 4: mboxhosting.com con SSL',
        'host' => 'mboxhosting.com',
        'port' => 465,
        'secure' => PHPMailer::ENCRYPTION_SMTPS
    ],
    [
        'name' => 'Configuración 5: servidor sin cifrado',
        'host' => 'mail.sistema-integral-de-salud-unacar.com.mx',
        'port' => 25,
        'secure' => false
    ]
];

foreach ($configs as $config) {
    echo "<hr><h3>{$config['name']}</h3>";
    
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@sistema-integral-de-salud-unacar.com.mx';
        $mail->Password = 'sklike5522';
        $mail->Port = $config['port'];
        $mail->Timeout = 10;
        
        if ($config['secure']) {
            $mail->SMTPSecure = $config['secure'];
        }
        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Intentar conectar
        if ($mail->smtpConnect()) {
            echo "✓ <strong style='color:green'>CONEXIÓN EXITOSA</strong><br>";
            echo "Host: {$config['host']}<br>";
            echo "Puerto: {$config['port']}<br>";
            echo "Cifrado: " . ($config['secure'] ?: 'ninguno') . "<br>";
            $mail->smtpClose();
        } else {
            echo "✗ <strong style='color:red'>No se pudo conectar</strong><br>";
        }
        
    } catch (Exception $e) {
        echo "✗ <strong style='color:red'>ERROR:</strong> {$mail->ErrorInfo}<br>";
    }
}

echo "<hr><h3>Información del servidor</h3>";
echo "Función fsockopen disponible: " . (function_exists('fsockopen') ? '✓ Sí' : '✗ No') . "<br>";
echo "Función openssl disponible: " . (extension_loaded('openssl') ? '✓ Sí' : '✗ No') . "<br>";
echo "Función socket disponible: " . (extension_loaded('socket') ? '✓ Sí' : '✗ No') . "<br>";
?>