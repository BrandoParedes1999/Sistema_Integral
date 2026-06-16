<?php
// ── Configuración de correo ───────────────────────────────────────────────
// Cambia MAIL_MODE a 'prod' cuando despliegues en producción
define('MAIL_MODE', 'dev');   // 'dev' = muestra código en pantalla | 'prod' = envía email

define('MAIL_HOST',     'mail.sistema-integral-de-salud-unacar.com.mx');
define('MAIL_PORT',     587);
define('MAIL_USER',     'noreply@sistema-integral-de-salud-unacar.com.mx');
define('MAIL_PASS',     'sklike5522');
define('MAIL_FROM',     'noreply@sistema-integral-de-salud-unacar.com.mx');
define('MAIL_FROM_NAME','UniSalud UNACAR');
